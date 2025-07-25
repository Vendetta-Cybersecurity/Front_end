<?php
/**
 * Figger Energy SAS - Authentication Handler
 * Handles user login, logout, and session management
 */

require_once 'config.php';

// Enable CORS for development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Set content type
header('Content-Type: application/json');

/**
 * Main authentication handler
 */
try {
    $method = $_SERVER['REQUEST_METHOD'];
    $action = $_GET['action'] ?? '';
    
    switch ($method) {
        case 'POST':
            handlePostRequest($action);
            break;
        case 'GET':
            handleGetRequest($action);
            break;
        default:
            sendJsonResponse(['error' => 'Method not allowed'], 405);
    }
    
} catch (Exception $e) {
    error_log("Auth error: " . $e->getMessage());
    sendJsonResponse(['error' => 'Internal server error'], 500);
}

/**
 * Handle POST requests
 */
function handlePostRequest($action) {
    switch ($action) {
        case 'login':
            handleLogin();
            break;
        case 'logout':
            handleLogout();
            break;
        case 'forgot-password':
            handleForgotPassword();
            break;
        case 'reset-password':
            handleResetPassword();
            break;
        default:
            sendJsonResponse(['error' => 'Invalid action'], 400);
    }
}

/**
 * Handle GET requests
 */
function handleGetRequest($action) {
    switch ($action) {
        case 'check-session':
            checkSession();
            break;
        case 'user-info':
            getUserInfo();
            break;
        default:
            sendJsonResponse(['error' => 'Invalid action'], 400);
    }
}

/**
 * Handle user login
 */
function handleLogin() {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Sanitize inputs
    $email = sanitizeInput($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $remember = isset($input['remember']) && $input['remember'];
    
    // Validate inputs
    $errors = [];
    
    if (empty($email)) {
        $errors[] = 'El correo electrónico es requerido';
    } elseif (!validateEmail($email)) {
        $errors[] = 'Formato de correo electrónico inválido';
    }
    
    if (empty($password)) {
        $errors[] = 'La contraseña es requerida';
    }
    
    if (!empty($errors)) {
        logSecurityEvent('login_validation_failed', ['email' => $email, 'errors' => $errors]);
        sendJsonResponse(['error' => implode(', ', $errors)], 400);
    }
    
    // Check rate limiting
    $clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (!checkRateLimit($clientIP)) {
        logSecurityEvent('rate_limit_exceeded', ['email' => $email, 'ip' => $clientIP]);
        sendJsonResponse(['error' => 'Demasiados intentos fallidos. Intenta nuevamente más tarde.'], 429);
    }
    
    // Authenticate user
    $db = Database::getInstance();
    
    try {
        $user = $db->fetch(
            "SELECT id, email, password_hash, tipo_usuario, nombre, activo, ultimo_acceso 
             FROM usuarios WHERE email = ?",
            [$email]
        );
        
        if (!$user) {
            recordLoginAttempt($clientIP, false);
            logSecurityEvent('login_failed', ['email' => $email, 'reason' => 'user_not_found']);
            sendJsonResponse(['error' => 'Credenciales incorrectas'], 401);
        }
        
        if (!$user['activo']) {
            logSecurityEvent('login_failed', ['email' => $email, 'reason' => 'account_disabled']);
            sendJsonResponse(['error' => 'Cuenta desactivada. Contacta al administrador.'], 403);
        }
        
        if (!verifyPassword($password, $user['password_hash'])) {
            recordLoginAttempt($clientIP, false);
            logSecurityEvent('login_failed', ['email' => $email, 'reason' => 'invalid_password']);
            sendJsonResponse(['error' => 'Credenciales incorrectas'], 401);
        }
        
        // Successful login
        recordLoginAttempt($clientIP, true);
        
        // Create session
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_type'] = $user['tipo_usuario'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['login_time'] = time();
        
        // Set session lifetime
        if ($remember) {
            ini_set('session.gc_maxlifetime', 30 * 24 * 60 * 60); // 30 days
            session_set_cookie_params(30 * 24 * 60 * 60);
        } else {
            ini_set('session.gc_maxlifetime', SESSION_TIMEOUT);
            session_set_cookie_params(SESSION_TIMEOUT);
        }
        
        // Update last access
        $db->update(
            "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?",
            [$user['id']]
        );
        
        // Log successful access
        $db->insert(
            "INSERT INTO logs_acceso (user_id, accion, ip_address, user_agent, resultado, timestamp) 
             VALUES (?, 'login', ?, ?, 'exitoso', NOW())",
            [$user['id'], $clientIP, $_SERVER['HTTP_USER_AGENT'] ?? 'unknown']
        );
        
        logSecurityEvent('login_success', ['email' => $email, 'user_id' => $user['id']]);
        
        // Determine redirect URL based on user type
        $redirectUrls = [
            'empleado' => 'dashboard/empleados.html',
            'administrativo' => 'dashboard/administrativos.html',
            'auditor' => 'dashboard/auditores.html'
        ];
        
        $redirectUrl = $redirectUrls[$user['tipo_usuario']] ?? 'dashboard/empleados.html';
        
        sendJsonResponse([
            'success' => true,
            'message' => 'Inicio de sesión exitoso',
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['nombre'],
                'type' => $user['tipo_usuario']
            ],
            'redirectUrl' => $redirectUrl
        ]);
        
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        sendJsonResponse(['error' => 'Error interno del servidor'], 500);
    }
}

/**
 * Handle user logout
 */
function handleLogout() {
    if (isAuthenticated()) {
        $userId = $_SESSION['user_id'];
        $email = $_SESSION['user_email'];
        
        // Log logout
        $db = Database::getInstance();
        $db->insert(
            "INSERT INTO logs_acceso (user_id, accion, ip_address, user_agent, resultado, timestamp) 
             VALUES (?, 'logout', ?, ?, 'exitoso', NOW())",
            [$userId, $_SERVER['REMOTE_ADDR'] ?? 'unknown', $_SERVER['HTTP_USER_AGENT'] ?? 'unknown']
        );
        
        logSecurityEvent('logout', ['email' => $email, 'user_id' => $userId]);
    }
    
    // Destroy session
    session_destroy();
    
    sendJsonResponse([
        'success' => true,
        'message' => 'Sesión cerrada correctamente'
    ]);
}

/**
 * Handle forgot password request
 */
function handleForgotPassword() {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = sanitizeInput($input['email'] ?? '');
    
    if (empty($email) || !validateEmail($email)) {
        sendJsonResponse(['error' => 'Correo electrónico inválido'], 400);
    }
    
    $db = Database::getInstance();
    
    // Check if user exists
    $user = $db->fetch("SELECT id, email, nombre FROM usuarios WHERE email = ? AND activo = 1", [$email]);
    
    if (!$user) {
        // Don't reveal if email exists or not for security
        sendJsonResponse([
            'success' => true,
            'message' => 'Si el correo existe, recibirás las instrucciones de recuperación'
        ]);
        return;
    }
    
    // Generate reset token
    $token = generateToken();
    $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour
    
    // Store reset token
    $db->insert(
        "INSERT INTO password_resets (email, token, expires_at, created_at) VALUES (?, ?, ?, NOW())
         ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at), created_at = NOW()",
        [$email, hash('sha256', $token), $expires]
    );
    
    // In a real application, send email here
    // For demo, just log it
    logSecurityEvent('password_reset_requested', ['email' => $email]);
    
    sendJsonResponse([
        'success' => true,
        'message' => 'Si el correo existe, recibirás las instrucciones de recuperación',
        'demo_token' => $token // Only for demo purposes
    ]);
}

/**
 * Handle password reset
 */
function handleResetPassword() {
    $input = json_decode(file_get_contents('php://input'), true);
    $token = $input['token'] ?? '';
    $newPassword = $input['password'] ?? '';
    
    if (empty($token) || empty($newPassword)) {
        sendJsonResponse(['error' => 'Token y nueva contraseña son requeridos'], 400);
    }
    
    if (strlen($newPassword) < 8) {
        sendJsonResponse(['error' => 'La contraseña debe tener al menos 8 caracteres'], 400);
    }
    
    $db = Database::getInstance();
    
    // Verify token
    $reset = $db->fetch(
        "SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW()",
        [hash('sha256', $token)]
    );
    
    if (!$reset) {
        sendJsonResponse(['error' => 'Token inválido o expirado'], 400);
    }
    
    // Update password
    $hashedPassword = hashPassword($newPassword);
    
    $db->beginTransaction();
    try {
        $db->update(
            "UPDATE usuarios SET password_hash = ? WHERE email = ?",
            [$hashedPassword, $reset['email']]
        );
        
        $db->delete(
            "DELETE FROM password_resets WHERE email = ?",
            [$reset['email']]
        );
        
        $db->commit();
        
        logSecurityEvent('password_reset_completed', ['email' => $reset['email']]);
        
        sendJsonResponse([
            'success' => true,
            'message' => 'Contraseña actualizada correctamente'
        ]);
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
}

/**
 * Check current session
 */
function checkSession() {
    if (!isAuthenticated()) {
        sendJsonResponse(['authenticated' => false]);
    }
    
    // Check session timeout
    $loginTime = $_SESSION['login_time'] ?? 0;
    if (time() - $loginTime > SESSION_TIMEOUT) {
        session_destroy();
        sendJsonResponse(['authenticated' => false, 'reason' => 'timeout']);
    }
    
    sendJsonResponse([
        'authenticated' => true,
        'user' => [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'],
            'name' => $_SESSION['user_name'],
            'type' => $_SESSION['user_type']
        ]
    ]);
}

/**
 * Get user information
 */
function getUserInfo() {
    requireAuth();
    
    $db = Database::getInstance();
    $user = $db->fetch(
        "SELECT id, email, nombre, tipo_usuario, fecha_creacion, ultimo_acceso 
         FROM usuarios WHERE id = ?",
        [$_SESSION['user_id']]
    );
    
    if (!$user) {
        sendJsonResponse(['error' => 'Usuario no encontrado'], 404);
    }
    
    sendJsonResponse([
        'success' => true,
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['nombre'],
            'type' => $user['tipo_usuario'],
            'created_at' => $user['fecha_creacion'],
            'last_access' => $user['ultimo_acceso']
        ]
    ]);
}
?>
