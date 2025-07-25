<?php
/**
 * Figger Energy SAS - Database Configuration
 * Database connection and configuration settings
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'figger_energy');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application configuration
define('APP_NAME', 'Figger Energy SAS');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost');

// Security configuration
define('HASH_ALGORITHM', 'sha256');
define('SESSION_TIMEOUT', 24 * 60 * 60); // 24 hours in seconds
define('MAX_LOGIN_ATTEMPTS', 3);
define('LOCKOUT_DURATION', 15 * 60); // 15 minutes in seconds

// Encryption key for sensitive data
define('ENCRYPTION_KEY', 'figger_energy_2025_secure_key_change_in_production');

/**
 * Database connection class using PDO
 */
class Database {
    private $pdo;
    private static $instance = null;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }
    
    /**
     * Get database instance (Singleton pattern)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get PDO connection
     */
    public function getConnection() {
        return $this->pdo;
    }
    
    /**
     * Execute query with parameters
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query failed: " . $e->getMessage());
            throw new Exception("Database query failed");
        }
    }
    
    /**
     * Get single row
     */
    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Get all rows
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Insert data and return last insert ID
     */
    public function insert($sql, $params = []) {
        $this->query($sql, $params);
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Update data and return affected rows
     */
    public function update($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Delete data and return affected rows
     */
    public function delete($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->pdo->rollback();
    }
}

/**
 * Utility functions
 */

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email address
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Hash password using secure algorithm
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536, // 64 MB
        'time_cost' => 4,       // 4 iterations
        'threads' => 3,         // 3 threads
    ]);
}

/**
 * Verify password against hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate secure random token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateToken();
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Log security events
 */
function logSecurityEvent($event, $details = []) {
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => $event,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'details' => $details
    ];
    
    $logEntry = date('Y-m-d H:i:s') . " - " . json_encode($logData) . PHP_EOL;
    file_put_contents(__DIR__ . '/../logs/security.log', $logEntry, FILE_APPEND | LOCK_EX);
}

/**
 * Rate limiting for login attempts
 */
function checkRateLimit($identifier, $maxAttempts = MAX_LOGIN_ATTEMPTS, $timeWindow = LOCKOUT_DURATION) {
    $db = Database::getInstance();
    
    // Clean old attempts
    $cutoff = date('Y-m-d H:i:s', time() - $timeWindow);
    $db->query(
        "DELETE FROM login_attempts WHERE identifier = ? AND attempted_at < ?",
        [$identifier, $cutoff]
    );
    
    // Count recent attempts
    $attempts = $db->fetch(
        "SELECT COUNT(*) as count FROM login_attempts WHERE identifier = ?",
        [$identifier]
    );
    
    return $attempts['count'] < $maxAttempts;
}

/**
 * Record login attempt
 */
function recordLoginAttempt($identifier, $success = false) {
    $db = Database::getInstance();
    
    if (!$success) {
        // Record failed attempt
        $db->insert(
            "INSERT INTO login_attempts (identifier, attempted_at, ip_address) VALUES (?, NOW(), ?)",
            [$identifier, $_SERVER['REMOTE_ADDR'] ?? 'unknown']
        );
    } else {
        // Clear attempts on successful login
        $db->delete(
            "DELETE FROM login_attempts WHERE identifier = ?",
            [$identifier]
        );
    }
}

/**
 * Encrypt sensitive data
 */
function encryptData($data) {
    $key = hash('sha256', ENCRYPTION_KEY, true);
    $iv = random_bytes(16);
    $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
    return base64_encode($iv . $encrypted);
}

/**
 * Decrypt sensitive data
 */
function decryptData($encryptedData) {
    $key = hash('sha256', ENCRYPTION_KEY, true);
    $data = base64_decode($encryptedData);
    $iv = substr($data, 0, 16);
    $encrypted = substr($data, 16);
    return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
}

/**
 * Send JSON response
 */
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Validate required fields
 */
function validateRequiredFields($data, $requiredFields) {
    $errors = [];
    
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            $errors[] = "El campo '$field' es requerido";
        }
    }
    
    return $errors;
}

/**
 * Set secure session parameters
 */
function setSecureSession() {
    // Set secure session parameters
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
    
    // Regenerate session ID for security
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        session_regenerate_id(true);
    }
}

/**
 * Check if user is authenticated
 */
function isAuthenticated() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

/**
 * Check if user has required permission
 */
function hasPermission($requiredType) {
    if (!isAuthenticated()) {
        return false;
    }
    
    $userType = $_SESSION['user_type'];
    
    // Permission hierarchy: auditor > administrativo > empleado
    $permissions = [
        'empleado' => ['empleado'],
        'administrativo' => ['empleado', 'administrativo'],
        'auditor' => ['empleado', 'administrativo', 'auditor']
    ];
    
    return in_array($requiredType, $permissions[$userType] ?? []);
}

/**
 * Require authentication
 */
function requireAuth($redirectUrl = '/login.html') {
    if (!isAuthenticated()) {
        header("Location: $redirectUrl");
        exit;
    }
}

/**
 * Require specific permission
 */
function requirePermission($requiredType, $redirectUrl = '/login.html') {
    if (!hasPermission($requiredType)) {
        header("Location: $redirectUrl");
        exit;
    }
}

// Set error reporting for development
if (DB_HOST === 'localhost') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set timezone
date_default_timezone_set('America/Bogota');

// Start secure session
setSecureSession();
?>
