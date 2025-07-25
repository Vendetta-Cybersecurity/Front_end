<?php
/**
 * Controlador de Autenticación - Figger Energy SAS
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/security.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Mostrar formulario de login
     */
    public function showLogin() {
        AuthMiddleware::requireGuest();
        
        $data = [
            'title' => 'Iniciar Sesión',
            'message' => $_GET['message'] ?? '',
            'timeout' => isset($_GET['timeout'])
        ];
        
        include __DIR__ . '/../../views/auth/login.php';
    }
    
    /**
     * Procesar login
     */
    public function processLogin() {
        AuthMiddleware::requireGuest();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(getBaseUrl() . 'login');
        }
        
        $email = Security::sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $ip = Security::getClientIP();
        
        // Verificar intentos de login
        if (!Security::checkLoginAttempts($ip)) {
            $this->redirectWithError('Demasiados intentos de login. Intente nuevamente en 15 minutos.');
        }
        
        // Validar datos
        if (empty($email) || empty($password)) {
            Security::recordLoginAttempt($ip, false);
            $this->redirectWithError('Email y contraseña son requeridos.');
        }
        
        // Autenticar usuario
        $user = $this->userModel->authenticate($email, $password);
        
        if (!$user) {
            Security::recordLoginAttempt($ip, false);
            $this->redirectWithError('Credenciales incorrectas.');
        }
        
        // Login exitoso
        Security::recordLoginAttempt($ip, true);
        AuthMiddleware::login($user);
        AuthMiddleware::redirectByRole($user['rol']);
    }
    
    /**
     * Mostrar formulario de registro
     */
    public function showRegister() {
        AuthMiddleware::requireGuest();
        
        $data = [
            'title' => 'Registro de Usuario',
            'message' => $_GET['message'] ?? ''
        ];
        
        include __DIR__ . '/../../views/auth/register.php';
    }
    
    /**
     * Procesar registro
     */
    public function processRegister() {
        AuthMiddleware::requireGuest();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(getBaseUrl() . 'register');
        }
        
        $data = [
            'nombre' => Security::sanitizeInput($_POST['nombre'] ?? ''),
            'email' => Security::sanitizeInput($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirmar_password' => $_POST['confirmar_password'] ?? '',
            'rol' => Security::sanitizeInput($_POST['rol'] ?? '')
        ];
        
        // Validaciones
        $errors = $this->validateRegistration($data);
        
        if (!empty($errors)) {
            $this->redirectWithError(implode('<br>', $errors));
        }
        
        // Verificar si el email ya existe
        if ($this->userModel->emailExists($data['email'])) {
            $this->redirectWithError('Ya existe un usuario con ese email.');
        }
        
        // Crear usuario
        unset($data['confirmar_password']);
        $userId = $this->userModel->create($data);
        
        if ($userId) {
            AuthMiddleware::logActivity($userId, 'registro', 'Usuario registrado en el sistema');
            redirect(getBaseUrl() . 'login?message=' . urlencode('Registro exitoso. Su cuenta está pendiente de activación.'));
        } else {
            $this->redirectWithError('Error al crear el usuario. Intente nuevamente.');
        }
    }
    
    /**
     * Cerrar sesión
     */
    public function logout() {
        AuthMiddleware::logout();
        redirect(getBaseUrl() . '?logout=1');
    }
    
    /**
     * Validar datos de registro
     */
    private function validateRegistration($data) {
        $errors = [];
        
        if (strlen($data['nombre']) < 3) {
            $errors[] = 'El nombre debe tener al menos 3 caracteres.';
        }
        
        if (!Security::validateEmail($data['email'])) {
            $errors[] = 'Email inválido.';
        }
        
        if (!Security::validatePassword($data['password'])) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres.';
        }
        
        if ($data['password'] !== $data['confirmar_password']) {
            $errors[] = 'Las contraseñas no coinciden.';
        }
        
        if (!in_array($data['rol'], ['empleado', 'auditor'])) {
            $errors[] = 'Rol seleccionado no válido.';
        }
        
        return $errors;
    }
    
    /**
     * Redireccionar con error
     */
    private function redirectWithError($message) {
        $referer = $_SERVER['HTTP_REFERER'] ?? getBaseUrl() . 'login';
        redirect($referer . '?error=' . urlencode($message));
    }
}
