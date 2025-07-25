/**
 * FIGGER ENERGY SAS - AUTHENTICATION MODULE
 * Funciones específicas para páginas de autenticación
 */

'use strict';

const FiggerAuth = {
    config: {
        passwordMinLength: 8,
        maxLoginAttempts: 3,
        lockoutDuration: 15 * 60 * 1000, // 15 minutes
    },

    // ===== PASSWORD VALIDATION =====
    password: {
        /**
         * Validate password strength
         */
        validate(password) {
            const requirements = {
                length: password.length >= FiggerAuth.config.passwordMinLength,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /\d/.test(password),
                special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
            };

            const strength = Object.values(requirements).filter(Boolean).length;
            
            return {
                requirements,
                strength,
                isValid: strength >= 4,
                score: (strength / 5) * 100
            };
        },

        /**
         * Update password strength indicator
         */
        updateStrengthIndicator(passwordField, indicatorContainer) {
            if (typeof passwordField === 'string') {
                passwordField = document.querySelector(passwordField);
            }
            if (typeof indicatorContainer === 'string') {
                indicatorContainer = document.querySelector(indicatorContainer);
            }

            if (!passwordField || !indicatorContainer) return;

            passwordField.addEventListener('input', (e) => {
                const password = e.target.value;
                const validation = this.validate(password);
                
                // Update requirement checkmarks
                const requirements = indicatorContainer.querySelectorAll('.password-requirements li');
                requirements.forEach((req, index) => {
                    const requirementKeys = ['length', 'uppercase', 'lowercase', 'number', 'special'];
                    const key = requirementKeys[index];
                    
                    if (validation.requirements[key]) {
                        req.classList.add('met');
                        req.classList.remove('unmet');
                    } else {
                        req.classList.add('unmet');
                        req.classList.remove('met');
                    }
                });

                // Update strength bar if exists
                const strengthBar = indicatorContainer.querySelector('.strength-bar');
                if (strengthBar) {
                    strengthBar.style.width = validation.score + '%';
                    strengthBar.className = `strength-bar strength-${this.getStrengthLevel(validation.strength)}`;
                }
            });
        },

        /**
         * Get strength level name
         */
        getStrengthLevel(strength) {
            if (strength < 2) return 'weak';
            if (strength < 4) return 'medium';
            return 'strong';
        },

        /**
         * Toggle password visibility
         */
        toggleVisibility(fieldId) {
            const field = document.getElementById(fieldId);
            const button = field?.nextElementSibling;
            const icon = button?.querySelector('i');
            
            if (!field || !icon) return;
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    },

    // ===== LOGIN MANAGEMENT =====
    login: {
        attempts: 0,
        lockedUntil: 0,

        /**
         * Initialize login form
         */
        init() {
            const loginForm = document.getElementById('loginForm');
            if (!loginForm) return;

            // Check if account is locked
            this.checkLockout();

            // Set up form validation
            this.setupValidation(loginForm);

            // Set up form submission
            loginForm.addEventListener('submit', (e) => this.handleSubmit(e));

            // Remember me functionality
            this.setupRememberMe();
        },

        /**
         * Check if account is locked out
         */
        checkLockout() {
            const lockoutData = localStorage.getItem('loginLockout');
            if (lockoutData) {
                const { until } = JSON.parse(lockoutData);
                if (Date.now() < until) {
                    const remainingTime = Math.ceil((until - Date.now()) / 1000 / 60);
                    FiggerEnergy.ui.showNotification(
                        `Cuenta bloqueada por intentos fallidos. Intente nuevamente en ${remainingTime} minutos.`,
                        'error'
                    );
                    this.disableForm();
                    return;
                }
                localStorage.removeItem('loginLockout');
            }
        },

        /**
         * Setup form validation
         */
        setupValidation(form) {
            const emailField = form.querySelector('#email');
            const passwordField = form.querySelector('#password');

            emailField?.addEventListener('blur', () => {
                this.validateEmail(emailField);
            });

            passwordField?.addEventListener('blur', () => {
                this.validatePassword(passwordField);
            });
        },

        /**
         * Validate email field
         */
        validateEmail(field) {
            const fieldGroup = field.closest('.form-group');
            if (!fieldGroup) return;

            fieldGroup.classList.remove('error', 'success');

            if (!field.value.trim()) {
                this.showFieldError(fieldGroup, 'El email es obligatorio');
                return false;
            }

            if (!FiggerEnergy.utils.isValidEmail(field.value)) {
                this.showFieldError(fieldGroup, 'Formato de email inválido');
                return false;
            }

            fieldGroup.classList.add('success');
            this.removeFieldError(fieldGroup);
            return true;
        },

        /**
         * Validate password field
         */
        validatePassword(field) {
            const fieldGroup = field.closest('.form-group');
            if (!fieldGroup) return;

            fieldGroup.classList.remove('error', 'success');

            if (!field.value.trim()) {
                this.showFieldError(fieldGroup, 'La contraseña es obligatoria');
                return false;
            }

            fieldGroup.classList.add('success');
            this.removeFieldError(fieldGroup);
            return true;
        },

        /**
         * Show field error
         */
        showFieldError(fieldGroup, message) {
            this.removeFieldError(fieldGroup);
            fieldGroup.classList.add('error');
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.textContent = message;
            fieldGroup.appendChild(errorDiv);
        },

        /**
         * Remove field error
         */
        removeFieldError(fieldGroup) {
            const existingError = fieldGroup.querySelector('.error-message');
            if (existingError) {
                existingError.remove();
            }
            fieldGroup.classList.remove('error');
        },

        /**
         * Handle form submission
         */
        async handleSubmit(e) {
            e.preventDefault();
            
            const form = e.target;
            const submitBtn = form.querySelector('[type="submit"]');
            const emailField = form.querySelector('#email');
            const passwordField = form.querySelector('#password');

            // Validate fields
            const emailValid = this.validateEmail(emailField);
            const passwordValid = this.validatePassword(passwordField);

            if (!emailValid || !passwordValid) {
                return;
            }

            try {
                // Show loading state
                FiggerEnergy.ui.showLoading(submitBtn, 'Iniciando sesión...');

                // Prepare form data
                const formData = new FormData(form);

                // Submit login request
                const response = await FiggerEnergy.utils.request(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': FiggerEnergy.config.csrfToken
                    }
                });

                if (response.success) {
                    // Reset attempts on success
                    this.attempts = 0;
                    localStorage.removeItem('loginAttempts');
                    localStorage.removeItem('loginLockout');

                    FiggerEnergy.ui.showNotification('Login exitoso. Redirigiendo...', 'success');
                    
                    // Redirect after short delay
                    setTimeout(() => {
                        window.location.href = response.redirect || '/dashboard';
                    }, 1000);
                } else {
                    throw new Error(response.message || 'Error de autenticación');
                }

            } catch (error) {
                this.handleLoginError(error.message);
            } finally {
                FiggerEnergy.ui.hideLoading(submitBtn);
            }
        },

        /**
         * Handle login error
         */
        handleLoginError(message) {
            this.attempts++;
            localStorage.setItem('loginAttempts', this.attempts.toString());

            if (this.attempts >= FiggerAuth.config.maxLoginAttempts) {
                this.lockAccount();
                FiggerEnergy.ui.showNotification(
                    'Demasiados intentos fallidos. Cuenta bloqueada temporalmente.',
                    'error'
                );
            } else {
                const remainingAttempts = FiggerAuth.config.maxLoginAttempts - this.attempts;
                FiggerEnergy.ui.showNotification(
                    `${message}. ${remainingAttempts} intentos restantes.`,
                    'error'
                );
            }
        },

        /**
         * Lock account temporarily
         */
        lockAccount() {
            const lockoutUntil = Date.now() + FiggerAuth.config.lockoutDuration;
            localStorage.setItem('loginLockout', JSON.stringify({
                until: lockoutUntil,
                attempts: this.attempts
            }));
            this.disableForm();
        },

        /**
         * Disable form
         */
        disableForm() {
            const form = document.getElementById('loginForm');
            if (form) {
                const inputs = form.querySelectorAll('input, button');
                inputs.forEach(input => input.disabled = true);
            }
        },

        /**
         * Setup remember me functionality
         */
        setupRememberMe() {
            const rememberCheckbox = document.querySelector('input[name="remember"]');
            const emailField = document.querySelector('#email');

            if (!rememberCheckbox || !emailField) return;

            // Load saved email if available
            const savedEmail = localStorage.getItem('rememberedEmail');
            if (savedEmail) {
                emailField.value = savedEmail;
                rememberCheckbox.checked = true;
            }

            // Save/remove email based on checkbox
            const form = document.getElementById('loginForm');
            form?.addEventListener('submit', () => {
                if (rememberCheckbox.checked) {
                    localStorage.setItem('rememberedEmail', emailField.value);
                } else {
                    localStorage.removeItem('rememberedEmail');
                }
            });
        }
    },

    // ===== REGISTRATION MANAGEMENT =====
    register: {
        /**
         * Initialize registration form
         */
        init() {
            const registerForm = document.getElementById('registerForm');
            if (!registerForm) return;

            this.setupValidation(registerForm);
            this.setupPasswordConfirmation();
            registerForm.addEventListener('submit', (e) => this.handleSubmit(e));
        },

        /**
         * Setup form validation
         */
        setupValidation(form) {
            // Email validation
            const emailField = form.querySelector('#email');
            emailField?.addEventListener('blur', () => {
                this.validateEmailField(emailField);
            });

            // Document validation
            const documentField = form.querySelector('#documento');
            documentField?.addEventListener('blur', () => {
                this.validateDocument(documentField);
            });

            // Phone validation
            const phoneField = form.querySelector('#telefono');
            phoneField?.addEventListener('blur', () => {
                this.validatePhone(phoneField);
            });

            // Password validation with strength indicator
            const passwordField = form.querySelector('#password');
            const passwordRequirements = form.querySelector('.password-requirements');
            if (passwordField && passwordRequirements) {
                FiggerAuth.password.updateStrengthIndicator(passwordField, passwordRequirements);
            }
        },

        /**
         * Validate email field
         */
        validateEmailField(field) {
            const fieldGroup = field.closest('.form-group');
            if (!fieldGroup) return false;

            fieldGroup.classList.remove('error', 'success');

            if (!field.value.trim()) {
                this.showFieldError(fieldGroup, 'El email es obligatorio');
                return false;
            }

            if (!FiggerEnergy.utils.isValidEmail(field.value)) {
                this.showFieldError(fieldGroup, 'Formato de email inválido');
                return false;
            }

            // Check if email belongs to organization
            if (!field.value.toLowerCase().includes('figgerenergy.gov.co')) {
                this.showFieldError(fieldGroup, 'Debe usar un email corporativo (@figgerenergy.gov.co)');
                return false;
            }

            fieldGroup.classList.add('success');
            this.removeFieldError(fieldGroup);
            return true;
        },

        /**
         * Validate document
         */
        validateDocument(field) {
            const fieldGroup = field.closest('.form-group');
            if (!fieldGroup) return false;

            fieldGroup.classList.remove('error', 'success');

            if (!field.value.trim()) {
                this.showFieldError(fieldGroup, 'El documento es obligatorio');
                return false;
            }

            if (!FiggerEnergy.utils.isValidCedula(field.value)) {
                this.showFieldError(fieldGroup, 'Número de documento inválido');
                return false;
            }

            fieldGroup.classList.add('success');
            this.removeFieldError(fieldGroup);
            return true;
        },

        /**
         * Validate phone
         */
        validatePhone(field) {
            const fieldGroup = field.closest('.form-group');
            if (!fieldGroup) return false;

            fieldGroup.classList.remove('error', 'success');

            if (!field.value.trim()) {
                this.showFieldError(fieldGroup, 'El teléfono es obligatorio');
                return false;
            }

            const phoneRegex = /^(\+57|57)?[0-9]{10}$/;
            if (!phoneRegex.test(field.value.replace(/\s/g, ''))) {
                this.showFieldError(fieldGroup, 'Formato de teléfono inválido');
                return false;
            }

            fieldGroup.classList.add('success');
            this.removeFieldError(fieldGroup);
            return true;
        },

        /**
         * Setup password confirmation
         */
        setupPasswordConfirmation() {
            const passwordField = document.querySelector('#password');
            const confirmField = document.querySelector('#password_confirm');

            if (!passwordField || !confirmField) return;

            const validatePasswordMatch = () => {
                const fieldGroup = confirmField.closest('.form-group');
                if (!fieldGroup) return;

                fieldGroup.classList.remove('error', 'success');

                if (confirmField.value && passwordField.value !== confirmField.value) {
                    this.showFieldError(fieldGroup, 'Las contraseñas no coinciden');
                    return false;
                } else if (confirmField.value) {
                    fieldGroup.classList.add('success');
                    this.removeFieldError(fieldGroup);
                    return true;
                }
            };

            passwordField.addEventListener('input', validatePasswordMatch);
            confirmField.addEventListener('input', validatePasswordMatch);
        },

        /**
         * Show field error
         */
        showFieldError(fieldGroup, message) {
            this.removeFieldError(fieldGroup);
            fieldGroup.classList.add('error');
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.textContent = message;
            fieldGroup.appendChild(errorDiv);
        },

        /**
         * Remove field error
         */
        removeFieldError(fieldGroup) {
            const existingError = fieldGroup.querySelector('.error-message');
            if (existingError) {
                existingError.remove();
            }
            fieldGroup.classList.remove('error');
        },

        /**
         * Handle form submission
         */
        async handleSubmit(e) {
            e.preventDefault();
            
            const form = e.target;
            const submitBtn = form.querySelector('[type="submit"]');

            // Validate all fields
            const emailField = form.querySelector('#email');
            const documentField = form.querySelector('#documento');
            const phoneField = form.querySelector('#telefono');
            const passwordField = form.querySelector('#password');
            const confirmField = form.querySelector('#password_confirm');

            const validations = [
                this.validateEmailField(emailField),
                this.validateDocument(documentField),
                this.validatePhone(phoneField),
            ];

            // Validate password
            if (passwordField) {
                const passwordValidation = FiggerAuth.password.validate(passwordField.value);
                if (!passwordValidation.isValid) {
                    const fieldGroup = passwordField.closest('.form-group');
                    this.showFieldError(fieldGroup, 'La contraseña no cumple con los requisitos');
                    validations.push(false);
                } else {
                    validations.push(true);
                }
            }

            // Validate password confirmation
            if (confirmField && passwordField.value !== confirmField.value) {
                const fieldGroup = confirmField.closest('.form-group');
                this.showFieldError(fieldGroup, 'Las contraseñas no coinciden');
                validations.push(false);
            } else {
                validations.push(true);
            }

            if (!validations.every(v => v)) {
                FiggerEnergy.ui.showNotification('Por favor corrija los errores en el formulario', 'error');
                return;
            }

            try {
                // Show loading state
                FiggerEnergy.ui.showLoading(submitBtn, 'Enviando solicitud...');

                // Prepare form data
                const formData = new FormData(form);

                // Submit registration request
                const response = await FiggerEnergy.utils.request(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': FiggerEnergy.config.csrfToken
                    }
                });

                if (response.success) {
                    FiggerEnergy.ui.showNotification(
                        'Solicitud enviada exitosamente. Recibirá una notificación cuando sea revisada.',
                        'success'
                    );
                    
                    // Redirect after delay
                    setTimeout(() => {
                        window.location.href = response.redirect || '/login';
                    }, 2000);
                } else {
                    throw new Error(response.message || 'Error al procesar la solicitud');
                }

            } catch (error) {
                FiggerEnergy.ui.showNotification(error.message, 'error');
            } finally {
                FiggerEnergy.ui.hideLoading(submitBtn);
            }
        }
    },

    // ===== RECOVERY MANAGEMENT =====
    recovery: {
        /**
         * Show recovery modal
         */
        show() {
            const modal = document.getElementById('recoveryModal');
            if (modal) {
                modal.style.display = 'flex';
                this.setupRecoveryForm();
            }
        },

        /**
         * Hide recovery modal
         */
        hide() {
            const modal = document.getElementById('recoveryModal');
            if (modal) {
                modal.style.display = 'none';
                const form = modal.querySelector('#recoveryForm');
                if (form) form.reset();
            }
        },

        /**
         * Setup recovery form
         */
        setupRecoveryForm() {
            const form = document.getElementById('recoveryForm');
            if (!form) return;

            form.addEventListener('submit', (e) => this.handleSubmit(e));
        },

        /**
         * Handle recovery form submission
         */
        async handleSubmit(e) {
            e.preventDefault();
            
            const form = e.target;
            const submitBtn = form.querySelector('[type="submit"]');
            const emailField = form.querySelector('#recovery_email');

            // Validate email
            if (!emailField.value.trim() || !FiggerEnergy.utils.isValidEmail(emailField.value)) {
                FiggerEnergy.ui.showNotification('Ingrese un email válido', 'error');
                return;
            }

            try {
                // Show loading state
                FiggerEnergy.ui.showLoading(submitBtn, 'Enviando...');

                // Simulate API call (replace with actual implementation)
                await new Promise(resolve => setTimeout(resolve, 2000));

                FiggerEnergy.ui.showNotification(
                    'Si el email está registrado, recibirá las instrucciones de recuperación.',
                    'success'
                );
                
                this.hide();

            } catch (error) {
                FiggerEnergy.ui.showNotification('Error al enviar las instrucciones', 'error');
            } finally {
                FiggerEnergy.ui.hideLoading(submitBtn);
            }
        }
    },

    // ===== INITIALIZATION =====
    init() {
        console.log('🔐 Auth module initialized');

        // Check current page and initialize appropriate functionality
        const currentPath = window.location.pathname;

        if (currentPath.includes('/login')) {
            this.login.init();
        } else if (currentPath.includes('/register')) {
            this.register.init();
        }

        // Global auth event listeners
        this.setupGlobalListeners();
    },

    setupGlobalListeners() {
        // Password toggle buttons
        document.addEventListener('click', (e) => {
            if (e.target.closest('.toggle-password')) {
                e.preventDefault();
                const button = e.target.closest('.toggle-password');
                const input = button.previousElementSibling;
                if (input) {
                    FiggerAuth.password.toggleVisibility(input.id);
                }
            }
        });

        // Recovery modal triggers
        document.addEventListener('click', (e) => {
            if (e.target.matches('.forgot-password, [data-action="forgot-password"]')) {
                e.preventDefault();
                this.recovery.show();
            }
        });

        // Modal close buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('.close-modal') || e.target.closest('.close-modal')) {
                e.preventDefault();
                this.recovery.hide();
            }
        });

        // Close modals on background click
        document.addEventListener('click', (e) => {
            if (e.target.matches('.modal')) {
                this.recovery.hide();
            }
        });
    }
};

// ===== GLOBAL FUNCTIONS FOR INLINE USE =====
window.togglePassword = (fieldId) => FiggerAuth.password.toggleVisibility(fieldId);
window.showRecoveryForm = () => FiggerAuth.recovery.show();
window.hideRecoveryForm = () => FiggerAuth.recovery.hide();
window.showPolicies = () => {
    const modal = document.getElementById('policiesModal');
    if (modal) modal.style.display = 'flex';
};
window.hidePolicies = () => {
    const modal = document.getElementById('policiesModal');
    if (modal) modal.style.display = 'none';
};

// ===== AUTO-INITIALIZATION =====
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => FiggerAuth.init());
} else {
    FiggerAuth.init();
}

// ===== EXPORT =====
window.FiggerAuth = FiggerAuth;
