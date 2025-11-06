<?php
/**
 * Archivo: logout.php
 * Maneja el cierre de sesión del usuario
 */

session_start();

// Headers de seguridad
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Registrar el logout si hay una sesión activa
if (isset($_SESSION['usuario_id'])) {
    $usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Usuario desconocido';
    
    // Log del logout
    error_log("Usuario logout: " . $usuario_nombre . " - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Desconocida'));
    
    // Opcional: Registrar en base de datos
    try {
        require_once 'config.php';
        
        // Verificar si la tabla existe antes de intentar insertar
        $stmt = $pdo->query("SHOW TABLES LIKE 'intentos_login'");
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->prepare("INSERT INTO intentos_login (usuario, ip_address, fecha_intento, exitoso, user_agent) VALUES (?, ?, NOW(), 2, ?)");
            $stmt->execute([
                $usuario_nombre,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        }
    } catch (Exception $e) {
        error_log("Error al registrar logout: " . $e->getMessage());
    }
}

// Destruir todas las variables de sesión
$_SESSION = array();

// Eliminar la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la sesión
session_destroy();

// Limpiar el buffer de salida
if (ob_get_level()) {
    ob_end_clean();
}

// Verificar si es una petición AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($isAjax) {
    // Respuesta JSON para peticiones AJAX
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'message' => 'Sesión cerrada correctamente',
        'redirect' => 'login.php'
    ]);
} else {
    // Redirección directa para peticiones normales
    header('Location: login.php?logout=1');
}

exit;
?>