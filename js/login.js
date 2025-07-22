/**
 * Figger Energy SAS - Login JavaScript
 * Handles authentication and login functionality
 */

// Login attempt tracking
let loginAttempts = 0;
const maxLoginAttempts = 3;
let isBlocked = false;
let blockTimeout = null;

document.addEventListener('DOMContentLoaded', function() {
    initializeLogin();
    setupDemoCredentials();
});

/**
 * Initialize login functionality
 */
function initializeLogin() {
    const loginForm = document.getElementById('loginForm');
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
    
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', handlePasswordRecovery);
    }
    
    // Check for blocked status on page load
    checkBlockedStatus();
}

/**
 * Handle login form submission
 */
async function handleLogin(e) {
    e.preventDefault();
    
    if (isBlocked) {
        showLoginError('Cuenta bloqueada temporalmente. Intenta nuevamente más tarde.');
        return;
    }
    
    const formData = new FormData(e.target);
    const credentials = {
        email: formData.get('email'),
        password: formData.get('password'),
        remember: formData.get('remember')
    };
    
    // Validate form
    if (!validateLoginForm(credentials)) {
        return;
    }
    
    // Show loading state
    const submitButton = e.target.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';
    submitButton.disabled = true;
    
    try {
        // Simulate API call delay
        await new Promise(resolve => setTimeout(resolve, 1500));
        
        // Check credentials (demo implementation)
        const authResult = await authenticateUser(credentials);
        
        if (authResult.success) {
            showLoginSuccess();
            // Reset attempts on successful login
            loginAttempts = 0;
            updateAttemptsDisplay();
            
            // Redirect after short delay
            setTimeout(() => {
                window.location.href = authResult.redirectUrl;
            }, 1500);
        } else {
            handleFailedLogin();
        }
    } catch (error) {
        showLoginError('Error de conexión. Por favor intenta nuevamente.');
    } finally {
        // Reset button state
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    }
}

/**
 * Validate login form
 */
function validateLoginForm(credentials) {
    const errors = [];
    
    if (!credentials.email) {
        errors.push('El correo electrónico es requerido');
    } else if (!isValidEmail(credentials.email)) {
        errors.push('Por favor ingresa un correo electrónico válido');
    }
    
    if (!credentials.password) {
        errors.push('La contraseña es requerida');
    } else if (credentials.password.length < 6) {
        errors.push('La contraseña debe tener al menos 6 caracteres');
    }
    
    if (errors.length > 0) {
        showLoginError(errors.join('<br>'));
        return false;
    }
    
    return true;
}

/**
 * Authenticate user (demo implementation)
 */
async function authenticateUser(credentials) {
    // Demo credentials for testing
    const demoUsers = {
        'empleado@figgerenergy.com': {
            password: 'empleado123',
            type: 'empleado',
            redirectUrl: 'dashboard/empleados.html'
        },
        'admin@figgerenergy.com': {
            password: 'admin123',
            type: 'administrativo',
            redirectUrl: 'dashboard/administrativos.html'
        },
        'auditor@figgerenergy.com': {
            password: 'auditor123',
            type: 'auditor',
            redirectUrl: 'dashboard/auditores.html'
        }
    };
    
    const user = demoUsers[credentials.email.toLowerCase()];
    
    if (user && user.password === credentials.password) {
        // Store session data
        if (credentials.remember) {
            localStorage.setItem('figger_user', JSON.stringify({
                email: credentials.email,
                type: user.type,
                loginTime: new Date().toISOString()
            }));
        } else {
            sessionStorage.setItem('figger_user', JSON.stringify({
                email: credentials.email,
                type: user.type,
                loginTime: new Date().toISOString()
            }));
        }
        
        // Log access attempt
        logAccessAttempt(credentials.email, 'login', true);
        
        return {
            success: true,
            redirectUrl: user.redirectUrl,
            userType: user.type
        };
    }
    
    // Log failed attempt
    logAccessAttempt(credentials.email, 'login_failed', false);
    
    return {
        success: false,
        error: 'Credenciales incorrectas'
    };
}

/**
 * Handle failed login attempt
 */
function handleFailedLogin() {
    loginAttempts++;
    updateAttemptsDisplay();
    
    if (loginAttempts >= maxLoginAttempts) {
        blockAccount();
    } else {
        const remainingAttempts = maxLoginAttempts - loginAttempts;
        showLoginError(`Credenciales incorrectas. Te quedan ${remainingAttempts} intento(s).`);
    }
}

/**
 * Block account temporarily
 */
function blockAccount() {
    isBlocked = true;
    const blockDuration = 15 * 60 * 1000; // 15 minutes
    const unblockTime = Date.now() + blockDuration;
    
    localStorage.setItem('login_blocked_until', unblockTime.toString());
    
    showLoginError('Cuenta bloqueada por 15 minutos debido a múltiples intentos fallidos.');
    hideAttemptsDisplay();
    
    // Set timeout to unblock
    blockTimeout = setTimeout(() => {
        unblockAccount();
    }, blockDuration);
    
    // Update UI to show blocked state
    const submitButton = document.querySelector('.btn-login');
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="fas fa-lock"></i> Cuenta Bloqueada';
}

/**
 * Unblock account
 */
function unblockAccount() {
    isBlocked = false;
    loginAttempts = 0;
    
    localStorage.removeItem('login_blocked_until');
    
    if (blockTimeout) {
        clearTimeout(blockTimeout);
        blockTimeout = null;
    }
    
    // Reset UI
    const submitButton = document.querySelector('.btn-login');
    submitButton.disabled = false;
    submitButton.innerHTML = '<i class="fas fa-sign-in-alt"></i> Iniciar Sesión';
    
    hideLoginError();
    hideAttemptsDisplay();
    
    showLoginMessage('Tu cuenta ha sido desbloqueada. Puedes intentar iniciar sesión nuevamente.', 'info');
}

/**
 * Check if account is currently blocked
 */
function checkBlockedStatus() {
    const blockedUntil = localStorage.getItem('login_blocked_until');
    
    if (blockedUntil) {
        const unblockTime = parseInt(blockedUntil);
        const now = Date.now();
        
        if (now < unblockTime) {
            isBlocked = true;
            const remainingTime = unblockTime - now;
            
            // Set timeout to unblock when time expires
            blockTimeout = setTimeout(() => {
                unblockAccount();
            }, remainingTime);
            
            // Update UI
            const submitButton = document.querySelector('.btn-login');
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-lock"></i> Cuenta Bloqueada';
            
            const minutes = Math.ceil(remainingTime / (60 * 1000));
            showLoginError(`Cuenta bloqueada. Tiempo restante: ${minutes} minuto(s).`);
        } else {
            // Block time has expired
            unblockAccount();
        }
    }
}

/**
 * Update attempts display
 */
function updateAttemptsDisplay() {
    const attemptsEl = document.getElementById('loginAttempts');
    const attemptsText = document.getElementById('attemptsText');
    
    if (loginAttempts > 0 && loginAttempts < maxLoginAttempts) {
        const remaining = maxLoginAttempts - loginAttempts;
        attemptsText.textContent = `Intentos restantes: ${remaining}`;
        attemptsEl.style.display = 'flex';
    }
}

/**
 * Hide attempts display
 */
function hideAttemptsDisplay() {
    const attemptsEl = document.getElementById('loginAttempts');
    attemptsEl.style.display = 'none';
}

/**
 * Show login error
 */
function showLoginError(message) {
    const errorEl = document.getElementById('loginError');
    const errorMessage = document.getElementById('errorMessage');
    
    errorMessage.innerHTML = message;
    errorEl.style.display = 'flex';
    
    // Hide other messages
    hideLoginSuccess();
}

/**
 * Hide login error
 */
function hideLoginError() {
    const errorEl = document.getElementById('loginError');
    errorEl.style.display = 'none';
}

/**
 * Show login success
 */
function showLoginSuccess() {
    const successEl = document.getElementById('loginSuccess');
    successEl.style.display = 'flex';
    
    // Hide other messages
    hideLoginError();
    hideAttemptsDisplay();
}

/**
 * Hide login success
 */
function hideLoginSuccess() {
    const successEl = document.getElementById('loginSuccess');
    successEl.style.display = 'none';
}

/**
 * Show general login message
 */
function showLoginMessage(message, type = 'info') {
    const messageEl = document.createElement('div');
    messageEl.className = `login-message login-message-${type}`;
    messageEl.style.cssText = `
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 16px;
        background-color: ${type === 'info' ? '#E3F2FD' : '#F8D7DA'};
        color: ${type === 'info' ? '#1976D2' : '#721C24'};
        border: 1px solid ${type === 'info' ? '#BBDEFB' : '#F5C6CB'};
    `;
    
    messageEl.innerHTML = `
        <i class="fas fa-${type === 'info' ? 'info-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    // Insert before login form
    const loginForm = document.getElementById('loginForm');
    loginForm.parentNode.insertBefore(messageEl, loginForm);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (messageEl.parentNode) {
            messageEl.remove();
        }
    }, 5000);
}

/**
 * Toggle password visibility
 */
function togglePassword() {
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

/**
 * Show forgot password modal
 */
function showForgotPassword() {
    const modal = document.getElementById('forgotPasswordModal');
    modal.style.display = 'flex';
    
    // Focus on email input
    setTimeout(() => {
        document.getElementById('recoveryEmail').focus();
    }, 100);
}

/**
 * Close forgot password modal
 */
function closeForgotPassword() {
    const modal = document.getElementById('forgotPasswordModal');
    modal.style.display = 'none';
    
    // Reset form
    const form = document.getElementById('forgotPasswordForm');
    form.reset();
    
    // Hide success message
    const successEl = document.getElementById('recoverySuccess');
    successEl.style.display = 'none';
}

/**
 * Handle password recovery
 */
async function handlePasswordRecovery(e) {
    e.preventDefault();
    
    const email = e.target.recoveryEmail.value;
    
    if (!email || !isValidEmail(email)) {
        alert('Por favor ingresa un correo electrónico válido');
        return;
    }
    
    // Simulate API call
    const submitButton = e.target.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;
    submitButton.textContent = 'Enviando...';
    submitButton.disabled = true;
    
    try {
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        // Show success message
        const successEl = document.getElementById('recoverySuccess');
        successEl.style.display = 'block';
        
        // Hide form
        e.target.style.display = 'none';
        
        // Log recovery attempt
        logAccessAttempt(email, 'password_recovery', true);
        
    } catch (error) {
        alert('Error al enviar las instrucciones. Por favor intenta nuevamente.');
    } finally {
        submitButton.textContent = originalText;
        submitButton.disabled = false;
    }
}

/**
 * Setup demo credentials display
 */
function setupDemoCredentials() {
    const demoEl = document.getElementById('demoCredentials');
    
    if (demoEl) {
        // Auto-hide after 10 seconds
        setTimeout(() => {
            demoEl.style.opacity = '0.7';
        }, 10000);
        
        // Auto-hide after 30 seconds
        setTimeout(() => {
            hideDemoCredentials();
        }, 30000);
    }
}

/**
 * Hide demo credentials
 */
function hideDemoCredentials() {
    const demoEl = document.getElementById('demoCredentials');
    if (demoEl) {
        demoEl.style.display = 'none';
    }
}

/**
 * Email validation
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Log access attempt (demo implementation)
 */
function logAccessAttempt(email, action, success) {
    const logEntry = {
        timestamp: new Date().toISOString(),
        email: email,
        action: action,
        success: success,
        ip: '192.168.1.100', // Demo IP
        userAgent: navigator.userAgent
    };
    
    // In a real implementation, this would be sent to the server
    console.log('Access Log:', logEntry);
    
    // Store in localStorage for demo purposes
    const logs = JSON.parse(localStorage.getItem('access_logs') || '[]');
    logs.push(logEntry);
    
    // Keep only last 100 entries
    if (logs.length > 100) {
        logs.splice(0, logs.length - 100);
    }
    
    localStorage.setItem('access_logs', JSON.stringify(logs));
}

/**
 * Close modal when clicking outside
 */
document.addEventListener('click', function(e) {
    const modal = document.getElementById('forgotPasswordModal');
    if (modal && e.target === modal) {
        closeForgotPassword();
    }
});

/**
 * Handle escape key to close modal
 */
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('forgotPasswordModal');
        if (modal && modal.style.display === 'flex') {
            closeForgotPassword();
        }
    }
});

// Export functions for testing
window.LoginModule = {
    authenticateUser,
    validateLoginForm,
    togglePassword,
    showForgotPassword,
    closeForgotPassword,
    hideDemoCredentials
};
