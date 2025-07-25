<?php
/**
 * Configuración de Base de Datos para Figger Energy SAS
 * Archivo de conexión MySQL básico
 */

// Configuración de la base de datos para XAMPP
define('DB_HOST', 'localhost');
define('DB_USER', 'root');              // Usuario por defecto en XAMPP
define('DB_PASS', '');                  // Sin contraseña por defecto en XAMPP
define('DB_NAME', 'figger_energy_db');

/**
 * Función para conectar a la base de datos
 * @return mysqli|false Conexión a la base de datos o false en caso de error
 */
function conectarDB() {
    $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Verificar conexión
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }
    
    // Establecer charset UTF-8
    $conexion->set_charset("utf8");
    
    return $conexion;
}

/**
 * Función para cerrar la conexión a la base de datos
 * @param mysqli $conexion Objeto de conexión a cerrar
 */
function cerrarDB($conexion) {
    if ($conexion) {
        $conexion->close();
    }
}

/**
 * Función para limpiar datos de entrada (prevención básica de inyección SQL)
 * @param string $data Datos a limpiar
 * @return string Datos limpiados
 */
function limpiarDatos($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
