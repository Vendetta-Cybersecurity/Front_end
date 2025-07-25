<?php
/**
 * Configuración de Base de Datos - Figger Energy SAS
 * Archivo centralizado para conexión MySQL
 */

// Configuración de conexión para XAMPP
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'figger_energy_db');
define('DB_CHARSET', 'utf8');

/**
 * Clase Database - Singleton para manejo de conexiones
 */
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($this->connection->connect_error) {
                throw new Exception("Error de conexión: " . $this->connection->connect_error);
            }
            
            $this->connection->set_charset(DB_CHARSET);
            
        } catch (Exception $e) {
            error_log("Database Error: " . $e->getMessage());
            die("Error de conexión a la base de datos. Contacte al administrador.");
        }
    }
    
    /**
     * Obtener instancia única de la conexión
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Obtener conexión MySQLi
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Cerrar conexión
     */
    public function closeConnection() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
    
    /**
     * Prevenir clonación
     */
    private function __clone() {}
    
    /**
     * Prevenir deserialización
     */
    public function __wakeup() {}
}

/**
 * Función helper para obtener conexión rápidamente
 */
function getDB() {
    return Database::getInstance()->getConnection();
}

/**
 * Función helper para limpiar datos de entrada
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Función helper para preparar statements seguros
 */
function prepareStatement($query) {
    $db = getDB();
    $stmt = $db->prepare($query);
    
    if (!$stmt) {
        error_log("Prepare failed: " . $db->error);
        throw new Exception("Error al preparar consulta");
    }
    
    return $stmt;
}
