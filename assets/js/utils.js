/**
 * Figger Energy SAS - Utilities
 * Funciones de utilidad general
 */

/**
 * Utilidades para formateo de datos
 */
class FormatUtils {
    /**
     * Formatear fecha en español
     */
    static formatDate(date, format = 'full') {
        if (!date) return '';
        
        const dateObj = new Date(date);
        if (isNaN(dateObj.getTime())) return '';
        
        const options = {
            short: { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            },
            full: { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            },
            time: { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            }
        };
        
        return dateObj.toLocaleDateString('es-CO', options[format] || options.full);
    }

    /**
     * Formatear fecha relativa (hace X tiempo)
     */
    static formatRelativeDate(date) {
        if (!date) return '';
        
        const dateObj = new Date(date);
        const now = new Date();
        const diffMs = now.getTime() - dateObj.getTime();
        const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
        const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
        const diffMinutes = Math.floor(diffMs / (1000 * 60));
        
        if (diffMinutes < 1) return 'Ahora mismo';
        if (diffMinutes < 60) return `Hace ${diffMinutes} minuto${diffMinutes > 1 ? 's' : ''}`;
        if (diffHours < 24) return `Hace ${diffHours} hora${diffHours > 1 ? 's' : ''}`;
        if (diffDays < 7) return `Hace ${diffDays} día${diffDays > 1 ? 's' : ''}`;
        if (diffDays < 30) return `Hace ${Math.floor(diffDays / 7)} semana${Math.floor(diffDays / 7) > 1 ? 's' : ''}`;
        
        return this.formatDate(date, 'short');
    }

    /**
     * Formatear moneda colombiana
     */
    static formatCurrency(amount) {
        if (amount === null || amount === undefined) return '';
        
        const number = parseFloat(amount);
        if (isNaN(number)) return '';
        
        return new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0
        }).format(number);
    }

    /**
     * Formatear número con separadores de miles
     */
    static formatNumber(number) {
        if (number === null || number === undefined) return '';
        
        const num = parseFloat(number);
        if (isNaN(num)) return '';
        
        return new Intl.NumberFormat('es-CO').format(num);
    }

    /**
     * Formatear teléfono colombiano
     */
    static formatPhone(phone) {
        if (!phone) return '';
        
        // Limpiar número
        const cleaned = phone.replace(/\D/g, '');
        
        // Formato: +57 XXX XXX XXXX
        if (cleaned.length === 10) {
            return `+57 ${cleaned.substring(0, 3)} ${cleaned.substring(3, 6)} ${cleaned.substring(6)}`;
        }
        
        if (cleaned.length === 12 && cleaned.startsWith('57')) {
            const number = cleaned.substring(2);
            return `+57 ${number.substring(0, 3)} ${number.substring(3, 6)} ${number.substring(6)}`;
        }
        
        return phone;
    }

    /**
     * Capitalizar primera letra de cada palabra
     */
    static capitalize(text) {
        if (!text) return '';
        
        return text
            .toLowerCase()
            .split(' ')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
    }

    /**
     * Truncar texto con elipsis
     */
    static truncate(text, maxLength = 50) {
        if (!text) return '';
        
        if (text.length <= maxLength) return text;
        
        return text.substring(0, maxLength - 3) + '...';
    }

    /**
     * Generar iniciales de nombre
     */
    static getInitials(fullName) {
        if (!fullName) return '';
        
        return fullName
            .split(' ')
            .map(name => name.charAt(0).toUpperCase())
            .slice(0, 2)
            .join('');
    }
}

/**
 * Utilidades para manipulación del DOM
 */
class DOMUtils {
    /**
     * Crear elemento con atributos
     */
    static createElement(tag, attributes = {}, children = []) {
        const element = document.createElement(tag);
        
        Object.keys(attributes).forEach(key => {
            if (key === 'className') {
                element.className = attributes[key];
            } else if (key === 'innerHTML') {
                element.innerHTML = attributes[key];
            } else if (key === 'textContent') {
                element.textContent = attributes[key];
            } else {
                element.setAttribute(key, attributes[key]);
            }
        });
        
        children.forEach(child => {
            if (typeof child === 'string') {
                element.appendChild(document.createTextNode(child));
            } else if (child instanceof Element) {
                element.appendChild(child);
            }
        });
        
        return element;
    }

    /**
     * Mostrar/ocultar elemento con animación
     */
    static toggleElement(element, show = null) {
        if (!element) return;
        
        const isVisible = element.style.display !== 'none';
        const shouldShow = show !== null ? show : !isVisible;
        
        if (shouldShow) {
            element.style.display = '';
            element.classList.add('fade-in');
            setTimeout(() => element.classList.remove('fade-in'), 300);
        } else {
            element.style.display = 'none';
        }
    }

    /**
     * Scroll suave a elemento
     */
    static scrollToElement(element, offset = 0) {
        if (!element) return;
        
        const elementPosition = element.offsetTop - offset;
        
        window.scrollTo({
            top: elementPosition,
            behavior: 'smooth'
        });
    }

    /**
     * Verificar si elemento está visible en viewport
     */
    static isElementVisible(element) {
        if (!element) return false;
        
        const rect = element.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }

    /**
     * Debounce para eventos
     */
    static debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * Throttle para eventos
     */
    static throttle(func, limit) {
        let inThrottle;
        return function executedFunction(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
}

/**
 * Utilidades para validación
 */
class ValidationUtils {
    /**
     * Validar email
     */
    static isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    /**
     * Validar número de documento colombiano
     */
    static isValidDocument(document, type = 'CC') {
        if (!document) return false;
        
        const cleanDoc = document.replace(/\D/g, '');
        
        switch (type) {
            case 'CC': // Cédula de ciudadanía
                return cleanDoc.length >= 6 && cleanDoc.length <= 10;
            case 'CE': // Cédula de extranjería
                return cleanDoc.length >= 6 && cleanDoc.length <= 12;
            case 'PA': // Pasaporte
                return document.length >= 6 && document.length <= 12;
            default:
                return cleanDoc.length >= 6 && cleanDoc.length <= 12;
        }
    }

    /**
     * Validar teléfono colombiano
     */
    static isValidPhone(phone) {
        if (!phone) return false;
        
        const cleanPhone = phone.replace(/\D/g, '');
        
        // Celular: 10 dígitos empezando por 3
        // Fijo: 7-8 dígitos
        return (cleanPhone.length === 10 && cleanPhone.startsWith('3')) ||
               (cleanPhone.length >= 7 && cleanPhone.length <= 8);
    }

    /**
     * Validar edad mínima
     */
    static isValidAge(birthDate, minAge = 18) {
        if (!birthDate) return false;
        
        const birth = new Date(birthDate);
        const today = new Date();
        const age = today.getFullYear() - birth.getFullYear();
        const monthDiff = today.getMonth() - birth.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
            return age - 1 >= minAge;
        }
        
        return age >= minAge;
    }

    /**
     * Validar contraseña segura
     */
    static isStrongPassword(password) {
        if (!password || password.length < 8) return false;
        
        const hasUpperCase = /[A-Z]/.test(password);
        const hasLowerCase = /[a-z]/.test(password);
        const hasNumbers = /\d/.test(password);
        const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);
        
        return hasUpperCase && hasLowerCase && hasNumbers && hasSpecialChar;
    }

    /**
     * Sanitizar entrada HTML
     */
    static sanitizeHTML(input) {
        if (!input) return '';
        
        const temp = document.createElement('div');
        temp.textContent = input;
        return temp.innerHTML;
    }
}

/**
 * Utilidades para almacenamiento local
 */
class StorageUtils {
    /**
     * Guardar datos en localStorage con expiración
     */
    static setItem(key, value, expirationMinutes = null) {
        try {
            const item = {
                value: value,
                timestamp: Date.now(),
                expiration: expirationMinutes ? Date.now() + (expirationMinutes * 60 * 1000) : null
            };
            
            localStorage.setItem(key, JSON.stringify(item));
            return true;
        } catch (error) {
            console.error('Error saving to localStorage:', error);
            return false;
        }
    }

    /**
     * Obtener datos de localStorage verificando expiración
     */
    static getItem(key) {
        try {
            const itemStr = localStorage.getItem(key);
            if (!itemStr) return null;
            
            const item = JSON.parse(itemStr);
            
            // Verificar expiración
            if (item.expiration && Date.now() > item.expiration) {
                localStorage.removeItem(key);
                return null;
            }
            
            return item.value;
        } catch (error) {
            console.error('Error reading from localStorage:', error);
            return null;
        }
    }

    /**
     * Remover elemento de localStorage
     */
    static removeItem(key) {
        try {
            localStorage.removeItem(key);
            return true;
        } catch (error) {
            console.error('Error removing from localStorage:', error);
            return false;
        }
    }

    /**
     * Limpiar elementos expirados
     */
    static cleanExpired() {
        try {
            const keysToRemove = [];
            
            for (let i = 0; i < localStorage.length; i++) {
                const key = localStorage.key(i);
                const itemStr = localStorage.getItem(key);
                
                try {
                    const item = JSON.parse(itemStr);
                    if (item.expiration && Date.now() > item.expiration) {
                        keysToRemove.push(key);
                    }
                } catch (e) {
                    // No es un item con estructura esperada, ignorar
                }
            }
            
            keysToRemove.forEach(key => localStorage.removeItem(key));
            return keysToRemove.length;
        } catch (error) {
            console.error('Error cleaning expired items:', error);
            return 0;
        }
    }
}

/**
 * Utilidades para manejo de errores
 */
class ErrorUtils {
    /**
     * Obtener mensaje de error amigable
     */
    static getFriendlyMessage(error) {
        if (typeof error === 'string') return error;
        
        if (error.message) {
            // Mapear errores comunes a mensajes amigables
            const errorMappings = {
                'NetworkError': 'Error de conexión. Verifique su internet.',
                'TypeError': 'Error en los datos. Contacte al administrador.',
                'SyntaxError': 'Error de formato. Intente nuevamente.',
                'ReferenceError': 'Error interno. Contacte al administrador.',
                'Failed to fetch': 'No se pudo conectar al servidor.',
                'Unauthorized': 'No tiene permisos para realizar esta acción.',
                'Forbidden': 'Acceso denegado.',
                'Not Found': 'Recurso no encontrado.',
                'Internal Server Error': 'Error interno del servidor.'
            };
            
            for (const [key, message] of Object.entries(errorMappings)) {
                if (error.message.includes(key)) {
                    return message;
                }
            }
            
            return error.message;
        }
        
        return 'Ha ocurrido un error inesperado.';
    }

    /**
     * Registrar error en consola con contexto
     */
    static logError(error, context = '', additionalInfo = {}) {
        const errorInfo = {
            message: error.message || error,
            stack: error.stack,
            context: context,
            timestamp: new Date().toISOString(),
            userAgent: navigator.userAgent,
            url: window.location.href,
            ...additionalInfo
        };
        
        console.error('Application Error:', errorInfo);
        
        // En producción, enviar al servicio de logging
        if (window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
            // Implementar envío a servicio de logging
        }
    }
}

/**
 * Utilidades para URLs y navegación
 */
class URLUtils {
    /**
     * Obtener parámetros de la URL
     */
    static getURLParams() {
        const params = new URLSearchParams(window.location.search);
        const result = {};
        
        for (const [key, value] of params) {
            result[key] = value;
        }
        
        return result;
    }

    /**
     * Actualizar parámetro de URL sin recargar
     */
    static updateURLParam(key, value) {
        const url = new URL(window.location);
        
        if (value === null || value === undefined || value === '') {
            url.searchParams.delete(key);
        } else {
            url.searchParams.set(key, value);
        }
        
        window.history.replaceState({}, '', url);
    }

    /**
     * Construir URL con parámetros
     */
    static buildURL(baseUrl, params = {}) {
        const url = new URL(baseUrl, window.location.origin);
        
        Object.keys(params).forEach(key => {
            if (params[key] !== null && params[key] !== undefined) {
                url.searchParams.set(key, params[key]);
            }
        });
        
        return url.toString();
    }
}

/**
 * Utilidades para dispositivos y detección de características
 */
class DeviceUtils {
    /**
     * Detectar si es dispositivo móvil
     */
    static isMobile() {
        return window.innerWidth <= 767 || /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    }

    /**
     * Detectar si es tablet
     */
    static isTablet() {
        return window.innerWidth >= 768 && window.innerWidth <= 1023;
    }

    /**
     * Detectar si es desktop
     */
    static isDesktop() {
        return window.innerWidth >= 1024;
    }

    /**
     * Obtener información del dispositivo
     */
    static getDeviceInfo() {
        return {
            isMobile: this.isMobile(),
            isTablet: this.isTablet(),
            isDesktop: this.isDesktop(),
            screenWidth: window.screen.width,
            screenHeight: window.screen.height,
            viewportWidth: window.innerWidth,
            viewportHeight: window.innerHeight,
            devicePixelRatio: window.devicePixelRatio || 1,
            orientation: window.screen.orientation ? window.screen.orientation.type : 'unknown'
        };
    }

    /**
     * Detectar soporte de características
     */
    static getFeatureSupport() {
        return {
            localStorage: typeof Storage !== 'undefined',
            sessionStorage: typeof sessionStorage !== 'undefined',
            webGL: !!window.WebGLRenderingContext,
            canvas: !!document.createElement('canvas').getContext,
            touch: 'ontouchstart' in window,
            geolocation: !!navigator.geolocation,
            fileAPI: !!window.File && !!window.FileReader && !!window.FileList && !!window.Blob
        };
    }
}

// Limpiar elementos expirados al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    StorageUtils.cleanExpired();
});

// Exportar para uso en otros módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        FormatUtils,
        DOMUtils,
        ValidationUtils,
        StorageUtils,
        ErrorUtils,
        URLUtils,
        DeviceUtils
    };
}
