/**
 * FIGGER ENERGY SAS - MAIN JAVASCRIPT
 * Unidad Gubernamental de Monitoreo de Minería Ilegal
 * Funciones principales y utilidades globales
 */

'use strict';

// ===== GLOBAL CONFIGURATION =====
const FiggerEnergy = {
    config: {
        baseUrl: window.location.origin + '/',
        apiUrl: window.location.origin + '/api/',
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        timeout: 30000,
        refreshInterval: 300000, // 5 minutes
    },
    
    // ===== UTILITY FUNCTIONS =====
    utils: {
        /**
         * Make AJAX requests with error handling
         */
        async request(url, options = {}) {
            const defaults = {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': FiggerEnergy.config.csrfToken
                },
                timeout: FiggerEnergy.config.timeout
            };
            
            const config = { ...defaults, ...options };
            
            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), config.timeout);
                
                const response = await fetch(url, {
                    ...config,
                    signal: controller.signal
                });
                
                clearTimeout(timeoutId);
                
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return await response.json();
                }
                
                return await response.text();
            } catch (error) {
                console.error('Request failed:', error);
                FiggerEnergy.ui.showNotification('Error de conexión: ' + error.message, 'error');
                throw error;
            }
        },
        
        /**
         * Debounce function to limit function calls
         */
        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },
        
        /**
         * Format date to local string
         */
        formatDate(date, options = {}) {
            const defaults = {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            };
            
            return new Date(date).toLocaleDateString('es-CO', { ...defaults, ...options });
        },
        
        /**
         * Format numbers with locale
         */
        formatNumber(number, options = {}) {
            return new Intl.NumberFormat('es-CO', options).format(number);
        },
        
        /**
         * Validate email format
         */
        isValidEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },
        
        /**
         * Validate Colombian ID (Cédula)
         */
        isValidCedula(cedula) {
            const cleanCedula = cedula.replace(/\D/g, '');
            return cleanCedula.length >= 7 && cleanCedula.length <= 10;
        },
        
        /**
         * Generate UUID v4
         */
        generateUUID() {
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                const r = Math.random() * 16 | 0;
                const v = c == 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
        },
        
        /**
         * Sanitize HTML content
         */
        sanitizeHtml(str) {
            const temp = document.createElement('div');
            temp.textContent = str;
            return temp.innerHTML;
        },
        
        /**
         * Convert bytes to human readable format
         */
        formatBytes(bytes, decimals = 2) {
            if (bytes === 0) return '0 Bytes';
            
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }
    },
    
    // ===== UI MANAGEMENT =====
    ui: {
        /**
         * Show notification message
         */
        showNotification(message, type = 'info', duration = 5000) {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <div class="notification-content">
                    <i class="fas ${this.getNotificationIcon(type)}"></i>
                    <span>${FiggerEnergy.utils.sanitizeHtml(message)}</span>
                </div>
                <button onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            document.body.appendChild(notification);
            
            // Auto remove
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, duration);
            
            return notification;
        },
        
        /**
         * Get icon for notification type
         */
        getNotificationIcon(type) {
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };
            return icons[type] || icons.info;
        },
        
        /**
         * Show loading state
         */
        showLoading(element, text = 'Cargando...') {
            if (typeof element === 'string') {
                element = document.querySelector(element);
            }
            
            if (!element) return;
            
            element.classList.add('loading');
            element.setAttribute('data-original-text', element.textContent);
            
            if (element.tagName === 'BUTTON') {
                element.disabled = true;
                element.innerHTML = `<i class="fas fa-spinner fa-spin"></i> ${text}`;
            }
        },
        
        /**
         * Hide loading state
         */
        hideLoading(element) {
            if (typeof element === 'string') {
                element = document.querySelector(element);
            }
            
            if (!element) return;
            
            element.classList.remove('loading');
            
            if (element.tagName === 'BUTTON') {
                element.disabled = false;
                const originalText = element.getAttribute('data-original-text');
                if (originalText) {
                    element.textContent = originalText;
                    element.removeAttribute('data-original-text');
                }
            }
        },
        
        /**
         * Show confirmation modal
         */
        confirm(message, title = 'Confirmación') {
            return new Promise((resolve) => {
                const modal = document.createElement('div');
                modal.className = 'modal';
                modal.innerHTML = `
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>${FiggerEnergy.utils.sanitizeHtml(title)}</h3>
                        </div>
                        <div class="modal-body">
                            <p>${FiggerEnergy.utils.sanitizeHtml(message)}</p>
                        </div>
                        <div class="modal-footer">
                            <button class="boton boton-secundario cancel-btn">Cancelar</button>
                            <button class="boton boton-primario confirm-btn">Confirmar</button>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(modal);
                modal.style.display = 'flex';
                
                const confirmBtn = modal.querySelector('.confirm-btn');
                const cancelBtn = modal.querySelector('.cancel-btn');
                
                confirmBtn.onclick = () => {
                    modal.remove();
                    resolve(true);
                };
                
                cancelBtn.onclick = () => {
                    modal.remove();
                    resolve(false);
                };
                
                // Close on background click
                modal.onclick = (e) => {
                    if (e.target === modal) {
                        modal.remove();
                        resolve(false);
                    }
                };
            });
        },
        
        /**
         * Show alert modal
         */
        alert(message, title = 'Aviso', type = 'info') {
            return new Promise((resolve) => {
                const modal = document.createElement('div');
                modal.className = 'modal';
                modal.innerHTML = `
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>
                                <i class="fas ${this.getNotificationIcon(type)}"></i>
                                ${FiggerEnergy.utils.sanitizeHtml(title)}
                            </h3>
                        </div>
                        <div class="modal-body">
                            <p>${FiggerEnergy.utils.sanitizeHtml(message)}</p>
                        </div>
                        <div class="modal-footer">
                            <button class="boton boton-primario ok-btn">Entendido</button>
                        </div>
                    </div>
                `;
                
                document.body.appendChild(modal);
                modal.style.display = 'flex';
                
                const okBtn = modal.querySelector('.ok-btn');
                
                okBtn.onclick = () => {
                    modal.remove();
                    resolve();
                };
                
                // Close on background click
                modal.onclick = (e) => {
                    if (e.target === modal) {
                        modal.remove();
                        resolve();
                    }
                };
            });
        },
        
        /**
         * Update counter with animation
         */
        animateCounter(element, target, duration = 1000) {
            if (typeof element === 'string') {
                element = document.querySelector(element);
            }
            
            if (!element) return;
            
            const start = parseInt(element.textContent) || 0;
            const increment = (target - start) / (duration / 16);
            let current = start;
            
            const timer = setInterval(() => {
                current += increment;
                
                if ((increment > 0 && current >= target) || (increment < 0 && current <= target)) {
                    element.textContent = FiggerEnergy.utils.formatNumber(target);
                    clearInterval(timer);
                } else {
                    element.textContent = FiggerEnergy.utils.formatNumber(Math.floor(current));
                }
            }, 16);
        }
    },
    
    // ===== FORM MANAGEMENT =====
    forms: {
        /**
         * Validate form fields
         */
        validate(form) {
            if (typeof form === 'string') {
                form = document.querySelector(form);
            }
            
            if (!form) return false;
            
            let isValid = true;
            const fields = form.querySelectorAll('[required]');
            
            fields.forEach(field => {
                const fieldGroup = field.closest('.form-group');
                if (!fieldGroup) return;
                
                // Remove previous validation classes
                fieldGroup.classList.remove('error', 'success');
                
                let fieldValid = true;
                let errorMessage = '';
                
                // Check if field is empty
                if (!field.value.trim()) {
                    fieldValid = false;
                    errorMessage = 'Este campo es obligatorio';
                }
                // Email validation
                else if (field.type === 'email' && !FiggerEnergy.utils.isValidEmail(field.value)) {
                    fieldValid = false;
                    errorMessage = 'Formato de email inválido';
                }
                // Password validation
                else if (field.type === 'password' && field.value.length < 8) {
                    fieldValid = false;
                    errorMessage = 'La contraseña debe tener al menos 8 caracteres';
                }
                // Custom validation
                else if (field.pattern && !new RegExp(field.pattern).test(field.value)) {
                    fieldValid = false;
                    errorMessage = field.title || 'Formato inválido';
                }
                
                if (fieldValid) {
                    fieldGroup.classList.add('success');
                    this.removeFieldError(fieldGroup);
                } else {
                    fieldGroup.classList.add('error');
                    this.showFieldError(fieldGroup, errorMessage);
                    isValid = false;
                }
            });
            
            return isValid;
        },
        
        /**
         * Show field error
         */
        showFieldError(fieldGroup, message) {
            this.removeFieldError(fieldGroup);
            
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
        },
        
        /**
         * Serialize form data to object
         */
        serialize(form) {
            if (typeof form === 'string') {
                form = document.querySelector(form);
            }
            
            if (!form) return {};
            
            const formData = new FormData(form);
            const data = {};
            
            for (let [key, value] of formData.entries()) {
                if (data[key]) {
                    if (!Array.isArray(data[key])) {
                        data[key] = [data[key]];
                    }
                    data[key].push(value);
                } else {
                    data[key] = value;
                }
            }
            
            return data;
        },
        
        /**
         * Submit form via AJAX
         */
        async submit(form, options = {}) {
            if (typeof form === 'string') {
                form = document.querySelector(form);
            }
            
            if (!form) return;
            
            const submitBtn = form.querySelector('[type="submit"]');
            
            try {
                // Validate form
                if (!this.validate(form)) {
                    throw new Error('Por favor corrija los errores en el formulario');
                }
                
                // Show loading
                if (submitBtn) {
                    FiggerEnergy.ui.showLoading(submitBtn, 'Enviando...');
                }
                
                // Prepare form data
                const formData = new FormData(form);
                
                // Add CSRF token
                if (FiggerEnergy.config.csrfToken) {
                    formData.append('csrf_token', FiggerEnergy.config.csrfToken);
                }
                
                // Submit form
                const response = await FiggerEnergy.utils.request(form.action || window.location.href, {
                    method: form.method || 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': FiggerEnergy.config.csrfToken
                    }
                });
                
                // Handle response
                if (response.success) {
                    FiggerEnergy.ui.showNotification(response.message || 'Operación completada exitosamente', 'success');
                    
                    if (response.redirect) {
                        setTimeout(() => {
                            window.location.href = response.redirect;
                        }, 1000);
                    } else if (options.reset !== false) {
                        form.reset();
                    }
                } else {
                    throw new Error(response.message || 'Error al procesar la solicitud');
                }
                
                return response;
                
            } catch (error) {
                console.error('Form submission error:', error);
                FiggerEnergy.ui.showNotification(error.message, 'error');
                throw error;
            } finally {
                // Hide loading
                if (submitBtn) {
                    FiggerEnergy.ui.hideLoading(submitBtn);
                }
            }
        }
    },
    
    // ===== NAVIGATION =====
    navigation: {
        /**
         * Toggle mobile menu
         */
        toggleMobileMenu() {
            const nav = document.querySelector('.main-nav');
            const toggle = document.querySelector('.mobile-menu-toggle');
            
            if (nav && toggle) {
                nav.classList.toggle('mobile-open');
                toggle.classList.toggle('active');
            }
        },
        
        /**
         * Toggle user dropdown
         */
        toggleUserDropdown() {
            const dropdown = document.querySelector('.user-dropdown');
            if (dropdown) {
                dropdown.classList.toggle('show');
            }
        },
        
        /**
         * Navigate to URL
         */
        goto(url, newWindow = false) {
            if (newWindow) {
                window.open(url, '_blank');
            } else {
                window.location.href = url;
            }
        },
        
        /**
         * Go back in history
         */
        back() {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = FiggerEnergy.config.baseUrl;
            }
        }
    },
    
    // ===== SECURITY =====
    security: {
        /**
         * Update CSRF token
         */
        updateCsrfToken() {
            const tokenMeta = document.querySelector('meta[name="csrf-token"]');
            if (tokenMeta) {
                FiggerEnergy.config.csrfToken = tokenMeta.getAttribute('content');
            }
        },
        
        /**
         * Logout user
         */
        async logout() {
            const confirmed = await FiggerEnergy.ui.confirm(
                '¿Está seguro de que desea cerrar sesión?',
                'Cerrar Sesión'
            );
            
            if (confirmed) {
                try {
                    await FiggerEnergy.utils.request(FiggerEnergy.config.baseUrl + 'logout', {
                        method: 'POST'
                    });
                    
                    window.location.href = FiggerEnergy.config.baseUrl + 'login';
                } catch (error) {
                    // Force logout on error
                    window.location.href = FiggerEnergy.config.baseUrl + 'logout';
                }
            }
        },
        
        /**
         * Check session status
         */
        async checkSession() {
            try {
                const response = await FiggerEnergy.utils.request(FiggerEnergy.config.apiUrl + 'session/check');
                
                if (!response.valid) {
                    FiggerEnergy.ui.showNotification('Su sesión ha expirado. Será redirigido al login.', 'warning');
                    setTimeout(() => {
                        window.location.href = FiggerEnergy.config.baseUrl + 'login';
                    }, 3000);
                }
                
                return response.valid;
            } catch (error) {
                console.warn('Session check failed:', error);
                return true; // Assume valid on error
            }
        }
    },
    
    // ===== INITIALIZATION =====
    init() {
        console.log('🛡️  Figger Energy SAS - System Initialized');
        
        // Update CSRF token
        this.security.updateCsrfToken();
        
        // Set up global event listeners
        this.setupEventListeners();
        
        // Start session monitoring
        this.startSessionMonitoring();
        
        // Initialize page-specific functionality
        this.initPageSpecific();
    },
    
    setupEventListeners() {
        // Mobile menu toggle
        document.addEventListener('click', (e) => {
            if (e.target.matches('.mobile-menu-toggle')) {
                e.preventDefault();
                this.navigation.toggleMobileMenu();
            }
        });
        
        // User dropdown toggle
        document.addEventListener('click', (e) => {
            if (e.target.matches('.user-menu-toggle')) {
                e.preventDefault();
                this.navigation.toggleUserDropdown();
            } else if (!e.target.closest('.user-menu')) {
                const dropdown = document.querySelector('.user-dropdown');
                if (dropdown) {
                    dropdown.classList.remove('show');
                }
            }
        });
        
        // Form submissions
        document.addEventListener('submit', async (e) => {
            if (e.target.classList.contains('ajax-form')) {
                e.preventDefault();
                await this.forms.submit(e.target);
            }
        });
        
        // Auto-hide messages
        document.addEventListener('DOMContentLoaded', () => {
            const messages = document.querySelectorAll('.mensaje-temporal');
            messages.forEach(message => {
                setTimeout(() => {
                    if (message.parentElement) {
                        message.style.opacity = '0';
                        setTimeout(() => message.remove(), 300);
                    }
                }, 5000);
            });
        });
        
        // Smooth scroll for anchor links
        document.addEventListener('click', (e) => {
            if (e.target.matches('a[href^="#"]')) {
                e.preventDefault();
                const target = document.querySelector(e.target.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
    },
    
    startSessionMonitoring() {
        // Check session every 5 minutes
        setInterval(() => {
            this.security.checkSession();
        }, this.config.refreshInterval);
    },
    
    initPageSpecific() {
        const page = document.body.getAttribute('data-page');
        
        switch (page) {
            case 'home':
                this.initHomePage();
                break;
            case 'dashboard':
                this.initDashboard();
                break;
            case 'auth':
                this.initAuthPages();
                break;
        }
    },
    
    initHomePage() {
        // Animate statistics on scroll
        const observerOptions = {
            threshold: 0.5,
            rootMargin: '0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counters = entry.target.querySelectorAll('[data-count]');
                    counters.forEach(counter => {
                        const target = parseInt(counter.getAttribute('data-count'));
                        this.ui.animateCounter(counter, target);
                    });
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);
        
        const statsSection = document.querySelector('.monitoring-stats');
        if (statsSection) {
            observer.observe(statsSection);
        }
    },
    
    initDashboard() {
        // Dashboard-specific initialization will be handled by dashboard.js
    },
    
    initAuthPages() {
        // Auth-specific initialization will be handled by auth.js
    }
};

// ===== AUTO-INITIALIZATION =====
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => FiggerEnergy.init());
} else {
    FiggerEnergy.init();
}

// ===== GLOBAL EXPORTS =====
window.FiggerEnergy = FiggerEnergy;
