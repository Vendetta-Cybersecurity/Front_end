<?php
/**
 * Logout - Figger Energy SAS
 * Cerrar sesión del usuario
 */

session_start();

// Registrar actividad de logout si hay sesión activa
if (isset($_SESSION['usuario_id'])) {
    include 'includes/db.php';
    
    $conexion = conectarDB();
    $stmt = $conexion->prepare("INSERT INTO actividades (usuario_id, accion, descripcion, ip_address) VALUES (?, 'logout', 'Cierre de sesión', ?)");
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
    $stmt->bind_param("is", $_SESSION['usuario_id'], $ip);
    $stmt->execute();
    $stmt->close();
    cerrarDB($conexion);
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

// Redirigir a la página principal con mensaje
header("Location: index.php?logout=1");
exit();
?>
