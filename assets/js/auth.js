/**
 * Figger Energy SAS - Authentication System
 * Sistema de autenticación y autorización
 */

class AuthManager {
    constructor() {
        this.currentUser = null;
        this.sessionTimeout = 8 * 60 * 60 * 1000; // 8 horas
        this.roles = {
            admin: {
                name: 'Administrador',
                permissions: ['read', 'write', 'delete', 'manage_users', 'manage_departments', 'view_all'],
                dashboard: 'dashboard-admin.html'
            },
            empleado: {
                name: 'Empleado',
                permissions: ['read', 'update_own_profile', 'view_department'],
                dashboard: 'dashboard-empleado.html'
            },
            auditor: {
                name: 'Auditor',
                permissions: ['read', 'view_all', 'export_reports'],
                dashboard: 'dashboard-auditor.html'
            }
        };
        
        // Credenciales hardcodeadas según especificaciones
        this.validCredentials = {
            admin: {
                username: 'admin',
                password: 'admin123',
                role: 'admin',
                fullName: 'Administrador del Sistema',
                email: 'admin@figgerenergy.com',
                department: 'Administración',
                employeeId: 1
            },
            empleado: {
                username: 'empleado',
                password: 'emp123',
                role: 'empleado',
                fullName: 'Juan Carlos Pérez',
                email: 'juan.perez@figgerenergy.com',
                department: 'IT',
                employeeId: 5
            },
            auditor: {
                username: 'auditor',
                password: 'aud123',
                role: 'auditor',
                fullName: 'María González Auditor',
                email: 'maria.gonzalez@figgerenergy.com',
                department: 'Administración',
                employeeId: 10
            }
        };
        
        this.initializeAuth();
    }

    /**
     * Inicializar sistema de autenticación
     */
    initializeAuth() {
        // Verificar si hay una sesión activa
        this.loadSession();
        
        // Configurar timeout de sesión
        this.setupSessionTimeout();
        
        // Escuchar cambios de visibilidad para renovar sesión
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && this.currentUser) {
                this.renewSession();
            }
        });
    }

    /**
     * Autenticar usuario
     */
    async login(username, password) {
        try {
            // Simular delay de red
            await new Promise(resolve => setTimeout(resolve, 800));
            
            // Buscar credenciales válidas
            const userCredentials = Object.values(this.validCredentials)
                .find(cred => cred.username === username && cred.password === password);
            
            if (!userCredentials) {
                throw new Error('Credenciales inválidas. Verifique su usuario y contraseña.');
            }
            
            // Crear objeto de usuario
            const user = {
                id: userCredentials.employeeId,
                username: userCredentials.username,
                role: userCredentials.role,
                fullName: userCredentials.fullName,
                email: userCredentials.email,
                department: userCredentials.department,
                permissions: this.roles[userCredentials.role].permissions,
                loginTime: new Date().toISOString(),
                lastActivity: new Date().toISOString()
            };
            
            // Guardar sesión
            this.currentUser = user;
            this.saveSession(user);
            
            // Registrar actividad
            this.logActivity('login', 'Usuario inició sesión');
            
            return {
                success: true,
                user: user,
                redirectUrl: this.roles[user.role].dashboard
            };
            
        } catch (error) {
            this.logActivity('login_failed', `Intento de login fallido: ${username}`);
            throw error;
        }
    }

    /**
     * Cerrar sesión
     */
    logout() {
        if (this.currentUser) {
            this.logActivity('logout', 'Usuario cerró sesión');
        }
        
        this.currentUser = null;
        this.clearSession();
        
        // Redirigir a login
        if (window.location.pathname !== '/index.html' && window.location.pathname !== '/') {
            window.location.href = 'index.html';
        }
    }

    /**
     * Verificar si el usuario está autenticado
     */
    isAuthenticated() {
        return this.currentUser !== null && this.isSessionValid();
    }

    /**
     * Verificar si la sesión es válida
     */
    isSessionValid() {
        if (!this.currentUser) return false;
        
        const sessionAge = Date.now() - new Date(this.currentUser.loginTime).getTime();
        return sessionAge < this.sessionTimeout;
    }

    /**
     * Verificar permisos
     */
    hasPermission(permission) {
        if (!this.currentUser) return false;
        return this.currentUser.permissions.includes(permission);
    }

    /**
     * Verificar rol específico
     */
    hasRole(role) {
        return this.currentUser && this.currentUser.role === role;
    }

    /**
     * Obtener usuario actual
     */
    getCurrentUser() {
        return this.currentUser;
    }

    /**
     * Actualizar actividad del usuario
     */
    updateActivity() {
        if (this.currentUser) {
            this.currentUser.lastActivity = new Date().toISOString();
            this.saveSession(this.currentUser);
        }
    }

    /**
     * Renovar sesión
     */
    renewSession() {
        if (this.currentUser && this.isSessionValid()) {
            this.currentUser.lastActivity = new Date().toISOString();
            this.saveSession(this.currentUser);
        } else if (this.currentUser) {
            this.logout();
        }
    }

    /**
     * Guardar sesión en localStorage
     */
    saveSession(user) {
        try {
            const sessionData = {
                user: user,
                timestamp: Date.now()
            };
            localStorage.setItem('figger_session', JSON.stringify(sessionData));
        } catch (error) {
            console.error('Error saving session:', error);
        }
    }

    /**
     * Cargar sesión desde localStorage
     */
    loadSession() {
        try {
            const sessionData = localStorage.getItem('figger_session');
            if (sessionData) {
                const parsed = JSON.parse(sessionData);
                
                // Verificar si la sesión no ha expirado
                if (Date.now() - parsed.timestamp < this.sessionTimeout) {
                    this.currentUser = parsed.user;
                    this.updateActivity();
                } else {
                    this.clearSession();
                }
            }
        } catch (error) {
            console.error('Error loading session:', error);
            this.clearSession();
        }
    }

    /**
     * Limpiar sesión
     */
    clearSession() {
        localStorage.removeItem('figger_session');
        localStorage.removeItem('figger_activity_log');
    }

    /**
     * Configurar timeout automático de sesión
     */
    setupSessionTimeout() {
        // Renovar actividad en interacciones del usuario
        const activities = ['click', 'keydown', 'scroll', 'mousemove'];
        
        let activityTimer;
        const updateActivity = () => {
            clearTimeout(activityTimer);
            activityTimer = setTimeout(() => {
                this.updateActivity();
            }, 30000); // Actualizar cada 30 segundos de inactividad
        };
        
        activities.forEach(activity => {
            document.addEventListener(activity, updateActivity, { passive: true });
        });
        
        // Verificar sesión cada minuto
        setInterval(() => {
            if (this.currentUser && !this.isSessionValid()) {
                this.showSessionExpiredMessage();
                this.logout();
            }
        }, 60000);
    }

    /**
     * Mostrar mensaje de sesión expirada
     */
    showSessionExpiredMessage() {
        if (typeof showToast === 'function') {
            showToast('Su sesión ha expirado. Por favor, inicie sesión nuevamente.', 'warning');
        } else {
            alert('Su sesión ha expirado. Por favor, inicie sesión nuevamente.');
        }
    }

    /**
     * Registrar actividad del usuario
     */
    logActivity(action, details = '') {
        try {
            const logEntry = {
                timestamp: new Date().toISOString(),
                user: this.currentUser ? this.currentUser.username : 'anonymous',
                action: action,
                details: details,
                userAgent: navigator.userAgent,
                ip: 'client-side' // En producción obtener del backend
            };
            
            let activityLog = JSON.parse(localStorage.getItem('figger_activity_log') || '[]');
            activityLog.push(logEntry);
            
            // Mantener solo los últimos 100 registros
            if (activityLog.length > 100) {
                activityLog = activityLog.slice(-100);
            }
            
            localStorage.setItem('figger_activity_log', JSON.stringify(activityLog));
        } catch (error) {
            console.error('Error logging activity:', error);
        }
    }

    /**
     * Obtener log de actividades
     */
    getActivityLog() {
        try {
            return JSON.parse(localStorage.getItem('figger_activity_log') || '[]');
        } catch (error) {
            console.error('Error getting activity log:', error);
            return [];
        }
    }

    /**
     * Proteger página - usar en cada dashboard
     */
    protectPage(requiredPermissions = []) {
        if (!this.isAuthenticated()) {
            window.location.href = 'index.html';
            return false;
        }
        
        // Verificar permisos específicos si se requieren
        if (requiredPermissions.length > 0) {
            const hasRequiredPermission = requiredPermissions.some(permission => 
                this.hasPermission(permission)
            );
            
            if (!hasRequiredPermission) {
                this.showAccessDeniedMessage();
                this.logout();
                return false;
            }
        }
        
        return true;
    }

    /**
     * Mostrar mensaje de acceso denegado
     */
    showAccessDeniedMessage() {
        if (typeof showToast === 'function') {
            showToast('No tiene permisos para acceder a esta página.', 'error');
        } else {
            alert('No tiene permisos para acceder a esta página.');
        }
    }

    /**
     * Obtener información del rol actual
     */
    getCurrentRole() {
        if (!this.currentUser) return null;
        return this.roles[this.currentUser.role];
    }

    /**
     * Verificar si puede acceder a una funcionalidad
     */
    canAccess(feature) {
        if (!this.currentUser) return false;
        
        const rolePermissions = this.roles[this.currentUser.role];
        if (!rolePermissions) return false;
        
        // Mapeo de funcionalidades a permisos
        const featurePermissions = {
            'create_employee': ['write'],
            'edit_employee': ['write'],
            'delete_employee': ['delete'],
            'view_all_employees': ['view_all'],
            'manage_departments': ['manage_departments'],
            'manage_users': ['manage_users'],
            'export_data': ['export_reports'],
            'view_statistics': ['read']
        };
        
        const requiredPermissions = featurePermissions[feature] || [];
        return requiredPermissions.some(permission => 
            this.currentUser.permissions.includes(permission)
        );
    }

    /**
     * Generar token de sesión (simulado para frontend)
     */
    generateSessionToken() {
        const timestamp = Date.now();
        const random = Math.random().toString(36).substring(2);
        return btoa(`${this.currentUser.username}:${timestamp}:${random}`);
    }
}

/**
 * Utilidades de autenticación
 */
class AuthUtils {
    /**
     * Validar formato de credenciales
     */
    static validateCredentials(username, password) {
        const errors = {};
        
        if (!username || username.trim().length === 0) {
            errors.username = 'El usuario es requerido';
        } else if (username.trim().length < 3) {
            errors.username = 'El usuario debe tener al menos 3 caracteres';
        }
        
        if (!password || password.length === 0) {
            errors.password = 'La contraseña es requerida';
        } else if (password.length < 6) {
            errors.password = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        return {
            isValid: Object.keys(errors).length === 0,
            errors
        };
    }

    /**
     * Sanitizar entrada de usuario
     */
    static sanitizeInput(input) {
        if (typeof input !== 'string') return '';
        return input.trim().substring(0, 100);
    }

    /**
     * Obtener información del navegador para logs
     */
    static getBrowserInfo() {
        return {
            userAgent: navigator.userAgent,
            language: navigator.language,
            platform: navigator.platform,
            cookieEnabled: navigator.cookieEnabled,
            onLine: navigator.onLine
        };
    }

    /**
     * Verificar soporte de características necesarias
     */
    static checkBrowserSupport() {
        const requiredFeatures = {
            localStorage: typeof Storage !== 'undefined',
            fetch: typeof fetch !== 'undefined',
            json: typeof JSON !== 'undefined',
            promise: typeof Promise !== 'undefined'
        };
        
        const unsupported = Object.keys(requiredFeatures)
            .filter(feature => !requiredFeatures[feature]);
        
        return {
            isSupported: unsupported.length === 0,
            unsupportedFeatures: unsupported
        };
    }
}

// Instancia global del gestor de autenticación
const authManager = new AuthManager();

// Configurar listeners globales
document.addEventListener('DOMContentLoaded', () => {
    // Auto-logout en cierre de pestaña/navegador
    window.addEventListener('beforeunload', () => {
        if (authManager.currentUser) {
            authManager.logActivity('page_unload', 'Usuario cerró la página');
        }
    });
    
    // Verificar soporte del navegador
    const browserSupport = AuthUtils.checkBrowserSupport();
    if (!browserSupport.isSupported) {
        console.warn('Navegador no soportado completamente:', browserSupport.unsupportedFeatures);
    }
});

// Exportar para uso en otros módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { AuthManager, AuthUtils, authManager };
}
