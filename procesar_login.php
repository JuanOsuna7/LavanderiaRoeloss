<?php
session_start();
require_once 'config.php';

// Headers de seguridad
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Método no permitido'
    ]);
    exit;
}

// Función para registrar intentos de login
function registrarIntentoLogin($usuario, $ip, $exito = false) {
    global $pdo;
    try {
        // Verificar si la tabla existe
        $stmt = $pdo->query("SHOW TABLES LIKE 'intentos_login'");
        if ($stmt->rowCount() == 0) {
            // Si la tabla no existe, simplemente registrar en log
            error_log("Intento de login - Usuario: $usuario, IP: $ip, Éxito: " . ($exito ? 'Sí' : 'No'));
            return;
        }
        
        $stmt = $pdo->prepare("INSERT INTO intentos_login (usuario, ip_address, fecha_intento, exitoso) VALUES (?, ?, NOW(), ?)");
        $stmt->execute([$usuario, $ip, $exito ? 1 : 0]);
    } catch (PDOException $e) {
        error_log("Error al registrar intento de login: " . $e->getMessage());
        // No fallar el login por este error
    }
}

// Función para verificar intentos fallidos recientes
function verificarIntentosFallidos($ip, $usuario) {
    global $pdo;
    try {
        // Verificar si la tabla existe
        $stmt = $pdo->query("SHOW TABLES LIKE 'intentos_login'");
        if ($stmt->rowCount() == 0) {
            // Si la tabla no existe, no limitar intentos
            return 0;
        }
        
        // Verificar intentos fallidos en los últimos 15 minutos
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as intentos 
            FROM intentos_login 
            WHERE (ip_address = ? OR usuario = ?) 
            AND exitoso = 0 
            AND fecha_intento > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
        ");
        $stmt->execute([$ip, $usuario]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['intentos'] ?? 0;
    } catch (PDOException $e) {
        error_log("Error al verificar intentos fallidos: " . $e->getMessage());
        return 0; // No limitar si hay error
    }
}

// Función para limpiar y validar entrada
function limpiarEntrada($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Función para generar token CSRF
function generarTokenCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Obtener IP del cliente
function obtenerIPCliente() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

try {
    // Obtener datos del formulario
    $usuario = limpiarEntrada($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';
    $ip = obtenerIPCliente();

    // Validaciones básicas
    if (empty($usuario) || empty($password)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Todos los campos son requeridos'
        ]);
        exit;
    }

    // Validación de longitud
    if (strlen($usuario) < 3) {
        echo json_encode([
            'status' => 'error',
            'message' => 'El usuario debe tener al menos 3 caracteres'
        ]);
        exit;
    }

    // Verificar intentos fallidos
    $intentosFallidos = verificarIntentosFallidos($ip, $usuario);
    if ($intentosFallidos >= 5) {
        registrarIntentoLogin($usuario, $ip, false);
        echo json_encode([
            'status' => 'error',
            'message' => 'Demasiados intentos fallidos. Intenta nuevamente en 15 minutos.'
        ]);
        exit;
    }

    // Buscar usuario en la base de datos
    $stmt = $pdo->prepare("
        SELECT 
            u.pk_usuario,
            u.correoUsu as usuario,
            u.contrasUsu as password_hash,
            u.rolUsu as rol,
            u.estatusUsu as estatus,
            u.fk_persona,
            p.nombres,
            p.aPaterno,
            p.aMaterno
        FROM usuarios u
        LEFT JOIN personas p ON u.fk_persona = p.pk_persona
        WHERE u.correoUsu = ? AND u.estatusUsu = 1
        LIMIT 1
    ");
    
    $stmt->execute([$usuario]);
    $usuarioData = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar si el usuario existe
    if (!$usuarioData) {
        registrarIntentoLogin($usuario, $ip, false);
        
        // Delay para prevenir ataques de timing
        usleep(mt_rand(100000, 300000)); // 0.1 a 0.3 segundos
        
        echo json_encode([
            'status' => 'error',
            'message' => 'Credenciales incorrectas'
        ]);
        exit;
    }

    // Verificar contraseña
    $passwordValida = false;
    
    // Verificar si la contraseña está hasheada o es texto plano (para migración)
    if (password_verify($password, $usuarioData['password_hash'])) {
        $passwordValida = true;
    } elseif ($password === $usuarioData['password_hash']) {
        // Contraseña en texto plano - actualizar a hash
        $passwordValida = true;
        $nuevoHash = password_hash($password, PASSWORD_DEFAULT);
        
        $updateStmt = $pdo->prepare("UPDATE usuarios SET contrasUsu = ? WHERE pk_usuario = ?");
        $updateStmt->execute([$nuevoHash, $usuarioData['pk_usuario']]);
    }

    if (!$passwordValida) {
        registrarIntentoLogin($usuario, $ip, false);
        
        // Delay para prevenir ataques de timing
        usleep(mt_rand(100000, 300000));
        
        echo json_encode([
            'status' => 'error',
            'message' => 'Credenciales incorrectas'
        ]);
        exit;
    }

    // Login exitoso
    registrarIntentoLogin($usuario, $ip, true);

    // Regenerar ID de sesión por seguridad
    session_regenerate_id(true);

    // Mapear roles y estatus numéricos a texto
    $roles = [
        1 => 'Administrador',
        2 => 'Gerente', 
        3 => 'Empleado',
        4 => 'Usuario'
    ];
    
    $rolTexto = $roles[$usuarioData['rol']] ?? 'Usuario';
    $nombreCompleto = '';
    
    if ($usuarioData['nombres']) {
        $nombreCompleto = trim($usuarioData['nombres'] . ' ' . $usuarioData['aPaterno'] . ' ' . $usuarioData['aMaterno']);
    } else {
        $nombreCompleto = $usuario; // Usar el usuario si no hay datos de persona
    }

    // Establecer variables de sesión
    $_SESSION['usuario_id'] = $usuarioData['pk_usuario'];
    $_SESSION['usuario_nombre'] = $usuario;
    $_SESSION['usuario_rol'] = $rolTexto;
    $_SESSION['usuario_nombre_completo'] = $nombreCompleto;
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    $_SESSION['ip_address'] = $ip;
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';

    // Generar token CSRF
    generarTokenCSRF();

    // Configurar duración de sesión (termina al cerrar navegador)
    setcookie(session_name(), session_id(), 0, '/', '', false, true);

    // Respuesta exitosa
    echo json_encode([
        'status' => 'success',
        'message' => '¡Bienvenido ' . ($usuarioData['nombres'] ?? $usuario) . '!',
        'redirect' => '../controllers/pedido_controller.php?action=list',
        'user' => [
            'id' => $usuarioData['pk_usuario'],
            'nombre' => $nombreCompleto,
            'rol' => $rolTexto
        ]
    ]);

} catch (PDOException $e) {
    error_log("Error de base de datos en login: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Error interno del servidor. Por favor, intenta nuevamente.'
    ]);
    
} catch (Exception $e) {
    error_log("Error general en login: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'message' => 'Error inesperado. Por favor, intenta nuevamente.'
    ]);
}
?>