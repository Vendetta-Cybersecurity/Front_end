/**
 * FIGGER ENERGY - Authentication System
 * Sistema de autenticación para unidad gubernamental
 */

// ==========================================================================
// CONFIGURACIÓN Y DATOS MOCK
// ==========================================================================

// Configuración del sistema
const CONFIG = {
    SESSION_TIMEOUT: 30 * 60 * 1000, // 30 minutos
    MAX_LOGIN_ATTEMPTS: 5,
    RATE_LIMIT_DURATION: 15 * 60 * 1000, // 15 minutos
    DEMO_MODE: true
};

// Datos mock de usuarios (simula API gubernamental)
const MOCK_USERS = [
    {
        id: 1,
        email: 'admin@figgerenergy.com',
        password: 'admin123',
        name: 'María González',
        role: 'admin',
        department: 'ADMINISTRACION',
        isActive: true,
        lastLogin: null,
        clearanceLevel: 5
    },
    {
        id: 2,
        email: 'empleado@figgerenergy.com',
        password: 'empleado123',
        name: 'Carlos Ramírez',
        role: 'employee',
        department: 'CAMPO',
        isActive: true,
        lastLogin: null,
        clearanceLevel: 2
    },
    {
        id: 3,
        email: 'auditor@figgerenergy.com',
        password: 'auditor123',
        name: 'Ana Patricia López',
        role: 'auditor',
        department: 'AUDITORIA',
        isActive: true,
        lastLogin: null,
        clearanceLevel: 4
    },
    {
        id: 4,
        email: 'administrativo@figgerenergy.com',
        password: 'admin123',
        name: 'Jorge Administrativo',
        role: 'administrative',
        department: 'ADMINISTRATIVO',
        isActive: true,
        lastLogin: null,
        clearanceLevel: 3
    }
];

// Políticas de seguridad ISO 27001
const SECURITY_POLICIES = {
    passwordPolicy: 'Mínimo 8 caracteres, incluir mayúsculas, minúsculas y números',
    dataRetention: 'Los logs se mantienen por 12 meses según normativa gubernamental',
    accessControl: 'Acceso basado en roles y nivel de autorización',
    incidentReporting: 'Reporte obligatorio de incidentes de seguridad'
};

// ==========================================================================
// SISTEMA DE AUTENTICACIÓN
// ==========================================================================

class AuthSystem {
    constructor() {
        this.initializeStorage();
        this.setupEventListeners();
        this.checkExistingSession();
    }

    initializeStorage() {
        // Inicializar localStorage si no existe
        if (!localStorage.getItem('figger_users')) {
            localStorage.setItem('figger_users', JSON.stringify(MOCK_USERS));
        }
        
        if (!localStorage.getItem('figger_login_attempts')) {
            localStorage.setItem('figger_login_attempts', JSON.stringify({}));
        }

        if (!localStorage.getItem('figger_audit_logs')) {
            localStorage.setItem('figger_audit_logs', JSON.stringify([]));
        }
    }

    setupEventListeners() {
        // Login form
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => this.handleLogin(e));
        }

        // Password toggle
        const togglePassword = document.getElementById('togglePassword');
        if (togglePassword) {
            togglePassword.addEventListener('click', () => this.togglePasswordVisibility());
        }

        // Forgot password form
        const forgotForm = document.getElementById('forgotPasswordForm');
        if (forgotForm) {
            forgotForm.addEventListener('submit', (e) => this.handleForgotPassword(e));
        }

        // Session timeout
        this.setupSessionTimeout();
    }

    async handleLogin(event) {
        event.preventDefault();
        
        const email = document.getElementById('email').value.trim().toLowerCase();
        const password = document.getElementById('password').value;
        const rememberMe = document.getElementById('rememberMe').checked;

        // Validación básica
        if (!this.validateEmail(email)) {
            this.showError('Por favor ingrese un email válido');
            return;
        }

        if (!password || password.length < 3) {
            this.showError('La contraseña es requerida');
            return;
        }

        // Verificar rate limiting
        if (!this.checkRateLimit(email)) {
            return;
        }

        // Mostrar estado de carga
        this.setLoadingState(true);

        try {
            // Simular delay de red (para demo)
            await new Promise(resolve => setTimeout(resolve, 1000));

            // Autenticar usuario
            const user = this.authenticateUser(email, password);
            
            if (user) {
                // Login exitoso
                this.handleSuccessfulLogin(user, rememberMe);
            } else {
                // Login fallido
                this.handleFailedLogin(email);
            }
        } catch (error) {
            console.error('Error durante login:', error);
            this.showError('Error interno del sistema. Intente nuevamente.');
        } finally {
            this.setLoadingState(false);
        }
    }

    authenticateUser(email, password) {
        const users = JSON.parse(localStorage.getItem('figger_users') || '[]');
        const user = users.find(u => 
            u.email.toLowerCase() === email && 
            u.password === password && 
            u.isActive
        );
        return user;
    }

    handleSuccessfulLogin(user, rememberMe) {
        // Limpiar intentos fallidos
        this.clearFailedAttempts(user.email);

        // Actualizar último login
        this.updateLastLogin(user.id);

        // Crear sesión
        const session = {
            userId: user.id,
            email: user.email,
            name: user.name,
            role: user.role,
            department: user.department,
            clearanceLevel: user.clearanceLevel,
            loginTime: Date.now(),
            rememberMe: rememberMe
        };

        localStorage.setItem('figger_session', JSON.stringify(session));

        // Audit log
        this.logSecurityEvent('LOGIN_SUCCESS', {
            userId: user.id,
            email: user.email,
            department: user.department
        });

        // Mostrar éxito
        this.showSuccess(`Bienvenido/a ${user.name}`);

        // Redireccionar según rol
        setTimeout(() => {
            this.redirectToDashboard(user.role);
        }, 1500);
    }

    handleFailedLogin(email) {
        this.recordFailedAttempt(email);
        
        // Audit log
        this.logSecurityEvent('LOGIN_FAILED', {
            email: email,
            timestamp: Date.now()
        });

        const attempts = this.getFailedAttempts(email);
        const remaining = CONFIG.MAX_LOGIN_ATTEMPTS - attempts;

        if (remaining > 0) {
            this.showError(`Credenciales incorrectas. ${remaining} intentos restantes.`);
        } else {
            this.showRateLimitError();
        }
    }

    checkRateLimit(email) {
        const attempts = this.getFailedAttempts(email);
        
        if (attempts >= CONFIG.MAX_LOGIN_ATTEMPTS) {
            const lastAttempt = this.getLastAttemptTime(email);
            const timeLeft = CONFIG.RATE_LIMIT_DURATION - (Date.now() - lastAttempt);
            
            if (timeLeft > 0) {
                this.showRateLimitError(timeLeft);
                return false;
            } else {
                // Reset attempts after rate limit period
                this.clearFailedAttempts(email);
            }
        }
        
        return true;
    }

    recordFailedAttempt(email) {
        const attempts = JSON.parse(localStorage.getItem('figger_login_attempts') || '{}');
        
        if (!attempts[email]) {
            attempts[email] = {
                count: 0,
                lastAttempt: 0
            };
        }
        
        attempts[email].count++;
        attempts[email].lastAttempt = Date.now();
        
        localStorage.setItem('figger_login_attempts', JSON.stringify(attempts));
    }

    getFailedAttempts(email) {
        const attempts = JSON.parse(localStorage.getItem('figger_login_attempts') || '{}');
        return attempts[email]?.count || 0;
    }

    getLastAttemptTime(email) {
        const attempts = JSON.parse(localStorage.getItem('figger_login_attempts') || '{}');
        return attempts[email]?.lastAttempt || 0;
    }

    clearFailedAttempts(email) {
        const attempts = JSON.parse(localStorage.getItem('figger_login_attempts') || '{}');
        delete attempts[email];
        localStorage.setItem('figger_login_attempts', JSON.stringify(attempts));
    }

    updateLastLogin(userId) {
        const users = JSON.parse(localStorage.getItem('figger_users') || '[]');
        const userIndex = users.findIndex(u => u.id === userId);
        
        if (userIndex !== -1) {
            users[userIndex].lastLogin = Date.now();
            localStorage.setItem('figger_users', JSON.stringify(users));
        }
    }

    togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.className = 'fas fa-eye-slash';
        } else {
            passwordInput.type = 'password';
            toggleIcon.className = 'fas fa-eye';
        }
    }

    handleForgotPassword(event) {
        event.preventDefault();
        
        const email = document.getElementById('recoveryEmail').value.trim().toLowerCase();
        
        if (!this.validateEmail(email)) {
            this.showError('Por favor ingrese un email válido');
            return;
        }

        // Simular envío de email (en producción sería real)
        this.logSecurityEvent('PASSWORD_RECOVERY_REQUESTED', { email });
        
        // Cerrar modal y mostrar mensaje
        const modal = bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal'));
        modal.hide();
        
        this.showInfo('Se han enviado las instrucciones de recuperación a su correo institucional.');
    }

    redirectToDashboard(role) {
        switch (role) {
            case 'admin':
                window.location.href = 'dashboard-admin.html';
                break;
            case 'empleado':
                window.location.href = 'dashboard-empleado.html';
                break;
            default:
                window.location.href = 'dashboard-empleado.html';
        }
    }

    checkExistingSession() {
        const session = this.getSession();
        
        if (session) {
            // Verificar si la sesión ha expirado
            const timeElapsed = Date.now() - session.loginTime;
            const timeout = session.rememberMe ? (7 * 24 * 60 * 60 * 1000) : CONFIG.SESSION_TIMEOUT;
            
            if (timeElapsed < timeout) {
                // Sesión válida - redireccionar
                if (window.location.pathname.includes('login.html')) {
                    this.redirectToDashboard(session.role);
                }
            } else {
                // Sesión expirada
                this.logout();
            }
        }
    }

    getSession() {
        try {
            return JSON.parse(localStorage.getItem('figger_session'));
        } catch {
            return null;
        }
    }

    logout() {
        const session = this.getSession();
        
        if (session) {
            this.logSecurityEvent('LOGOUT', {
                userId: session.userId,
                email: session.email
            });
        }
        
        localStorage.removeItem('figger_session');
        window.location.href = 'login.html';
    }

    setupSessionTimeout() {
        // Verificar sesión cada minuto
        setInterval(() => {
            const session = this.getSession();
            
            if (session && !window.location.pathname.includes('login.html')) {
                const timeElapsed = Date.now() - session.loginTime;
                const timeout = session.rememberMe ? (7 * 24 * 60 * 60 * 1000) : CONFIG.SESSION_TIMEOUT;
                
                if (timeElapsed >= timeout) {
                    this.showWarning('Su sesión ha expirado por seguridad.');
                    setTimeout(() => this.logout(), 3000);
                }
            }
        }, 60000);
    }

    logSecurityEvent(eventType, data) {
        const logs = JSON.parse(localStorage.getItem('figger_audit_logs') || '[]');
        
        const logEntry = {
            timestamp: Date.now(),
            eventType,
            data,
            userAgent: navigator.userAgent,
            ip: 'localhost', // En producción sería la IP real
            sessionId: this.getSession()?.userId || null
        };
        
        logs.push(logEntry);
        
        // Mantener solo los últimos 1000 logs
        if (logs.length > 1000) {
            logs.splice(0, logs.length - 1000);
        }
        
        localStorage.setItem('figger_audit_logs', JSON.stringify(logs));
        console.log('Security Event:', logEntry);
    }

    // Utilidades
    validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    setLoadingState(isLoading) {
        const loginBtn = document.getElementById('loginBtn');
        
        if (isLoading) {
            loginBtn.disabled = true;
            loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Iniciando sesión...';
            loginBtn.classList.add('loading');
        } else {
            loginBtn.disabled = false;
            loginBtn.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión';
            loginBtn.classList.remove('loading');
        }
    }

    showError(message) {
        this.showAlert(message, 'danger');
    }

    showSuccess(message) {
        this.showAlert(message, 'success');
    }

    showWarning(message) {
        this.showAlert(message, 'warning');
    }

    showInfo(message) {
        this.showAlert(message, 'info');
    }

    showAlert(message, type) {
        // Remover alertas existentes
        const existingAlerts = document.querySelectorAll('.alert-temporary');
        existingAlerts.forEach(alert => alert.remove());

        // Crear nueva alerta
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show alert-temporary`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        // Insertar antes del formulario
        const form = document.getElementById('loginForm');
        form.parentNode.insertBefore(alert, form);

        // Auto-remove después de 5 segundos
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }

    showRateLimitError(timeLeft = null) {
        const rateLimitAlert = document.getElementById('rateLimitAlert');
        const rateLimitMessage = document.getElementById('rateLimitMessage');
        
        if (timeLeft) {
            const minutes = Math.ceil(timeLeft / (60 * 1000));
            rateLimitMessage.textContent = `Demasiados intentos fallidos. Intente nuevamente en ${minutes} minutos.`;
        } else {
            rateLimitMessage.textContent = 'Cuenta bloqueada temporalmente por seguridad.';
        }
        
        rateLimitAlert.classList.remove('d-none');
    }
}

// ==========================================================================
// INICIALIZACIÓN
// ==========================================================================

// Inicializar sistema cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.authSystem = new AuthSystem();
});

// Función global para logout (para usar en otras páginas)
window.logout = () => {
    if (window.authSystem) {
        window.authSystem.logout();
    }
};

// Función para verificar autenticación (para usar en páginas protegidas)
window.requireAuth = () => {
    const session = localStorage.getItem('figger_session');
    if (!session) {
        window.location.href = 'login.html';
        return null;
    }
    
    try {
        return JSON.parse(session);
    } catch {
        window.location.href = 'login.html';
        return null;
    }
};

// Exportar para uso en otros módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { AuthSystem, CONFIG, MOCK_USERS, SECURITY_POLICIES };
}
