<?php
/**
 * Logout - Figger Energy SAS
 * Cerrar sesión del usuario refactorizado
 */

session_start();

// Registrar actividad de logout si hay sesión activa
if (isset($_SESSION['usuario_id'])) {
    require_once '../../config/database.php';
    require_once '../../lib/functions.php';
    
    registrarActividad($_SESSION['usuario_id'], 'logout', 'Cierre de sesión');
}

// Destruir todas las variables de sesión
$_SESSION = array();

// Destruir la cookie de sesión si existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la sesión
session_destroy();

// Redirigir al index con mensaje de logout
header("Location: ../../index.php?logout=1");
exit();
?>
