<?php
/**
 * Configuración de Base de Datos - Figger Energy SAS
 * Archivo de conexión y configuración MySQL
 */

// Configuración de la base de datos para XAMPP
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'figger_energy_db');

/**
 * Función para conectar a la base de datos
 * @return mysqli|false Conexión a la base de datos o false en caso de error
 */
function conectarDB() {
    $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }
    
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
 * Función para limpiar datos de entrada
 * @param string $data Datos a limpiar
 * @return string Datos limpiados
 */
function limpiarDatos($data) {
    return htmlspecialchars(trim(stripslashes($data)));
}
?>
