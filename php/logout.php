<?php
/**
 * Figger Energy SAS - Logout Handler
 * Handles secure session termination and cleanup
 */

require_once 'config.php';

// Enable CORS for development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Set content type
header('Content-Type: application/json');

try {
    // Check if user is authenticated
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $wasAuthenticated = isAuthenticated();
    $userId = $_SESSION['user_id'] ?? null;
    $userEmail = $_SESSION['user_email'] ?? null;
    $clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    if ($wasAuthenticated && $userId) {
        // Log the logout action
        $db = Database::getInstance();
        
        try {
            $db->insert(
                "INSERT INTO logs_acceso (user_id, accion, ip_address, user_agent, resultado, timestamp) 
                 VALUES (?, 'logout', ?, ?, 'exitoso', NOW())",
                [$userId, $clientIP, $userAgent]
            );
            
            // Log security event
            logSecurityEvent('logout', [
                'user_id' => $userId,
                'email' => $userEmail,
                'ip' => $clientIP,
                'user_agent' => $userAgent,
                'session_duration' => time() - ($_SESSION['login_time'] ?? time())
            ]);
            
        } catch (Exception $e) {
            error_log("Error logging logout: " . $e->getMessage());
            // Continue with logout even if logging fails
        }
    }
    
    // Clear all session data
    $_SESSION = array();
    
    // Delete the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(
            session_name(), 
            '', 
            time() - 3600, 
            '/', 
            '', 
            false, 
            true
        );
    }
    
    // Destroy the session
    session_destroy();
    
    // Clear any remember me cookies if they exist
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }
    
    // Send success response
    sendJsonResponse([
        'success' => true,
        'message' => 'Sesión cerrada correctamente',
        'redirectUrl' => '/index.html'
    ]);
    
} catch (Exception $e) {
    error_log("Logout error: " . $e->getMessage());
    
    // Even if there's an error, try to clear the session
    session_destroy();
    
    sendJsonResponse([
        'success' => true,
        'message' => 'Sesión cerrada',
        'redirectUrl' => '/index.html'
    ]);
}

/**
 * Helper function to send JSON response
 */
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Check if user is authenticated
 */
function isAuthenticated() {
    return isset($_SESSION['user_id']) && 
           isset($_SESSION['user_email']) && 
           isset($_SESSION['user_type']);
}

/**
 * Log security events
 */
function logSecurityEvent($event, $data = []) {
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => $event,
        'data' => $data,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];
    
    error_log("SECURITY_EVENT: " . json_encode($logData));
    
    // In production, you might want to send this to a dedicated security log system
    try {
        $db = Database::getInstance();
        $db->insert(
            "INSERT INTO logs_seguridad (evento, datos, ip_address, user_agent, timestamp) 
             VALUES (?, ?, ?, ?, NOW())",
            [
                $event,
                json_encode($data),
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]
        );
    } catch (Exception $e) {
        error_log("Error logging security event: " . $e->getMessage());
    }
}
?>
