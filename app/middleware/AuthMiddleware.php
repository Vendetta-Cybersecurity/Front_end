<?php
/**
 * Middleware de Autenticación - Figger Energy SAS
 * Control de acceso y autorización por roles
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/security.php';

class AuthMiddleware {
    
    /**
     * Verificar si el usuario está autenticado
     */
    public static function isAuthenticated() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Obtener usuario actual
     */
    public static function getCurrentUser() {
        if (!self::isAuthenticated()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'role' => $_SESSION['user_role'] ?? '',
            'login_time' => $_SESSION['login_time'] ?? null
        ];
    }
    
    /**
     * Verificar rol específico
     */
    public static function hasRole($role) {
        $user = self::getCurrentUser();
        return $user && $user['role'] === $role;
    }
    
    /**
     * Verificar si tiene alguno de los roles especificados
     */
    public static function hasAnyRole($roles) {
        $user = self::getCurrentUser();
        return $user && in_array($user['role'], $roles);
    }
    
    /**
     * Requerir autenticación
     */
    public static function requireAuth() {
        if (!self::isAuthenticated()) {
            redirect(getBaseUrl() . 'login');
        }
        
        // Verificar timeout de sesión
        if (self::isSessionExpired()) {
            self::logout();
            redirect(getBaseUrl() . 'login?timeout=1');
        }
        
        // Actualizar tiempo de actividad
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * Requerir rol específico
     */
    public static function requireRole($role) {
        self::requireAuth();
        
        if (!self::hasRole($role)) {
            http_response_code(403);
            die('Acceso denegado. No tiene permisos para acceder a esta sección.');
        }
    }
    
    /**
     * Requerir cualquiera de los roles especificados
     */
    public static function requireAnyRole($roles) {
        self::requireAuth();
        
        if (!self::hasAnyRole($roles)) {
            http_response_code(403);
            die('Acceso denegado. No tiene permisos para acceder a esta sección.');
        }
    }
    
    /**
     * Verificar si la sesión ha expirado
     */
    public static function isSessionExpired() {
        if (!isset($_SESSION['last_activity'])) {
            return true;
        }
        
        return (time() - $_SESSION['last_activity']) > SESSION_LIFETIME;
    }
    
    /**
     * Iniciar sesión de usuario
     */
    public static function login($user) {
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['rol'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        
        // Registrar actividad de login
        self::logActivity($user['id'], 'login', 'Inicio de sesión exitoso');
    }
    
    /**
     * Cerrar sesión
     */
    public static function logout() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if ($userId) {
            self::logActivity($userId, 'logout', 'Cierre de sesión');
        }
        
        Security::destroySession();
    }
    
    /**
     * Redireccionar según rol después del login
     */
    public static function redirectByRole($role) {
        $redirects = [
            'admin' => 'dashboard/admin',
            'empleado' => 'dashboard/empleado',
            'auditor' => 'dashboard/auditor'
        ];
        
        $url = $redirects[$role] ?? 'dashboard';
        redirect(getBaseUrl() . $url);
    }
    
    /**
     * Verificar si necesita redirección para usuario autenticado
     */
    public static function redirectIfAuthenticated() {
        if (self::isAuthenticated()) {
            $user = self::getCurrentUser();
            self::redirectByRole($user['role']);
        }
    }
    
    /**
     * Registrar actividad del usuario
     */
    public static function logActivity($userId, $action, $description) {
        try {
            $stmt = prepareStatement("INSERT INTO actividades (usuario_id, accion, descripcion, ip_address, fecha_actividad) VALUES (?, ?, ?, ?, NOW())");
            $ip = Security::getClientIP();
            $stmt->bind_param("isss", $userId, $action, $description, $ip);
            $stmt->execute();
            $stmt->close();
        } catch (Exception $e) {
            error_log("Error logging activity: " . $e->getMessage());
        }
    }
    
    /**
     * Middleware para páginas que requieren estar deslogueado
     */
    public static function requireGuest() {
        if (self::isAuthenticated()) {
            $user = self::getCurrentUser();
            self::redirectByRole($user['role']);
        }
    }
    
    /**
     * Obtener permisos por rol
     */
    public static function getPermissions($role) {
        $permissions = [
            'admin' => [
                'users.view', 'users.create', 'users.edit', 'users.delete',
                'alerts.view', 'alerts.create', 'alerts.edit', 'alerts.assign',
                'reports.view', 'reports.create', 'reports.export',
                'system.config', 'activities.view'
            ],
            'empleado' => [
                'alerts.view', 'alerts.update', 'alerts.take',
                'reports.view', 'reports.create',
                'activities.own'
            ],
            'auditor' => [
                'alerts.view', 'alerts.audit',
                'reports.view', 'reports.create', 'reports.export',
                'activities.view', 'users.view'
            ]
        ];
        
        return $permissions[$role] ?? [];
    }
    
    /**
     * Verificar permiso específico
     */
    public static function hasPermission($permission) {
        $user = self::getCurrentUser();
        if (!$user) return false;
        
        $permissions = self::getPermissions($user['role']);
        return in_array($permission, $permissions);
    }
}
