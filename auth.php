<?php
/**
 * Middleware de Autenticación
 * Archivo: auth.php
 * 
 * Este archivo debe ser incluido en todas las páginas que requieren autenticación
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verificar si el usuario está autenticado
 */
function verificarAutenticacion() {
    // Verificar si las variables de sesión existen
    if (!isset($_SESSION['usuario_id']) || 
        !isset($_SESSION['usuario_nombre']) || 
        !isset($_SESSION['login_time'])) {
        return false;
    }

    // Verificar timeout de sesión (2 horas por defecto)
    $timeout = 2 * 60 * 60; // 2 horas en segundos
    if (isset($_SESSION['last_activity']) && 
        (time() - $_SESSION['last_activity'] > $timeout)) {
        return false;
    }

    // Verificar consistencia de IP (opcional, puede causar problemas con proxies)
    if (isset($_SESSION['ip_address']) && 
        $_SESSION['ip_address'] !== obtenerIPCliente() && 
        VERIFICAR_IP_CONSISTENTE) {
        return false;
    }

    // Verificar User Agent (básico)
    if (isset($_SESSION['user_agent']) && 
        $_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
        // Log de posible secuestro de sesión
        error_log("Posible secuestro de sesión detectado para usuario: " . $_SESSION['usuario_nombre']);
    }

    // Actualizar última actividad
    $_SESSION['last_activity'] = time();
    
    return true;
}

/**
 * Obtener IP del cliente
 */
function obtenerIPCliente() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * Destruir sesión completamente
 */
function destruirSesion() {
    // Unset todas las variables de sesión
    $_SESSION = array();

    // Destruir la cookie de sesión
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Destruir la sesión
    session_destroy();
}

/**
 * Redireccionar al login
 */
function redirigirALogin() {
    // Destruir sesión antes de redireccionar
    destruirSesion();
    
    // Redireccionar al login
    header('Location: login.php');
    exit;
}

/**
 * Verificar rol del usuario
 */
function verificarRol($rolesPermitidos = []) {
    if (!verificarAutenticacion()) {
        return false;
    }

    if (empty($rolesPermitidos)) {
        return true; // Cualquier usuario autenticado
    }

    $rolUsuario = $_SESSION['usuario_rol'] ?? '';
    return in_array($rolUsuario, $rolesPermitidos);
}

/**
 * Obtener información del usuario actual
 */
function obtenerUsuarioActual() {
    if (!verificarAutenticacion()) {
        return null;
    }

    return [
        'id' => $_SESSION['usuario_id'],
        'nombre' => $_SESSION['usuario_nombre'],
        'nombre_completo' => $_SESSION['usuario_nombre_completo'] ?? '',
        'rol' => $_SESSION['usuario_rol'] ?? '',
        'login_time' => $_SESSION['login_time'],
        'last_activity' => $_SESSION['last_activity']
    ];
}

/**
 * Generar token CSRF
 */
function obtenerTokenCSRF() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verificar token CSRF
 */
function verificarTokenCSRF($token) {
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}

// Configuraciones
define('VERIFICAR_IP_CONSISTENTE', false); // Cambiar a true si se desea verificar IP

// Verificación automática de autenticación
// Solo si no estamos en la página de login o procesamiento
$paginaActual = basename($_SERVER['PHP_SELF']);
$paginasPublicas = ['login.php', 'procesar_login.php'];

if (!in_array($paginaActual, $paginasPublicas)) {
    if (!verificarAutenticacion()) {
        redirigirALogin();
    }
}

/**
 * Función para mostrar mensajes de debug (solo en desarrollo)
 */
function debugSesion() {
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        echo "<!-- Debug Sesión:\n";
        echo "Usuario ID: " . ($_SESSION['usuario_id'] ?? 'No definido') . "\n";
        echo "Usuario: " . ($_SESSION['usuario_nombre'] ?? 'No definido') . "\n";
        echo "Rol: " . ($_SESSION['usuario_rol'] ?? 'No definido') . "\n";
        echo "Última actividad: " . date('Y-m-d H:i:s', $_SESSION['last_activity'] ?? 0) . "\n";
        echo "-->";
    }
}
?>