/**
 * Figger Energy SAS - API Client
 * Cliente para interactuar con la API del backend
 */

class APIClient {
    constructor() {
        this.baseURL = 'http://127.0.0.1:8000/api';
        this.timeout = 10000; // 10 segundos
        this.retryAttempts = 3;
        this.retryDelay = 1000; // 1 segundo
    }

    /**
     * Realizar petición HTTP con manejo de errores y reintentos
     */
    async makeRequest(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            timeout: this.timeout,
            ...options
        };

        for (let attempt = 1; attempt <= this.retryAttempts; attempt++) {
            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), this.timeout);
                
                const response = await fetch(url, {
                    ...defaultOptions,
                    signal: controller.signal
                });
                
                clearTimeout(timeoutId);

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return await response.json();
                } else {
                    return await response.text();
                }

            } catch (error) {
                console.error(`API Request attempt ${attempt} failed:`, error);
                
                if (attempt === this.retryAttempts) {
                    throw this.handleError(error);
                }
                
                // Esperar antes del siguiente intento
                await new Promise(resolve => setTimeout(resolve, this.retryDelay * attempt));
            }
        }
    }

    /**
     * Manejo centralizado de errores
     */
    handleError(error) {
        if (error.name === 'AbortError') {
            return new Error('Tiempo de espera agotado. Verifique su conexión.');
        }
        
        if (error.message.includes('NetworkError') || error.message.includes('Failed to fetch')) {
            return new Error('Error de conexión. Verifique que el servidor esté disponible.');
        }
        
        if (error.message.includes('HTTP 400')) {
            return new Error('Datos inválidos. Verifique la información enviada.');
        }
        
        if (error.message.includes('HTTP 404')) {
            return new Error('Recurso no encontrado.');
        }
        
        if (error.message.includes('HTTP 500')) {
            return new Error('Error interno del servidor. Contacte al administrador.');
        }
        
        return error;
    }

    /**
     * Métodos HTTP básicos
     */
    async get(endpoint) {
        return this.makeRequest(endpoint, { method: 'GET' });
    }

    async post(endpoint, data) {
        return this.makeRequest(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    async put(endpoint, data) {
        return this.makeRequest(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    async delete(endpoint) {
        return this.makeRequest(endpoint, { method: 'DELETE' });
    }

    // ==================== ENDPOINTS ESPECÍFICOS ====================

    /**
     * Sistema y Estado
     */
    async getStatus() {
        return this.get('/status/');
    }

    async getConfigVM() {
        return this.get('/config-vm/');
    }

    /**
     * Gestión de Departamentos
     */
    async getDepartamentos() {
        return this.get('/departamentos/');
    }

    async getDepartamento(id) {
        return this.get(`/departamentos/${id}/`);
    }

    async createDepartamento(data) {
        return this.post('/departamentos/', data);
    }

    async updateDepartamento(id, data) {
        return this.put(`/departamentos/${id}/`, data);
    }

    async deleteDepartamento(id) {
        return this.delete(`/departamentos/${id}/`);
    }

    /**
     * Gestión de Roles
     */
    async getRoles() {
        return this.get('/roles/');
    }

    async getRolesByDepartamento(idDepartamento) {
        return this.get(`/roles/departamento/${idDepartamento}/`);
    }

    /**
     * Gestión de Empleados
     */
    async getEmpleados(filters = {}) {
        const queryParams = new URLSearchParams();
        
        Object.keys(filters).forEach(key => {
            if (filters[key] !== null && filters[key] !== undefined && filters[key] !== '') {
                queryParams.append(key, filters[key]);
            }
        });
        
        const queryString = queryParams.toString();
        const endpoint = queryString ? `/empleados/?${queryString}` : '/empleados/';
        
        return this.get(endpoint);
    }

    async getEmpleado(id) {
        return this.get(`/empleados/${id}/`);
    }

    async createEmpleado(data) {
        return this.post('/empleados/', data);
    }

    async updateEmpleado(id, data) {
        return this.put(`/empleados/${id}/`, data);
    }

    async deleteEmpleado(id) {
        return this.delete(`/empleados/${id}/`);
    }

    async buscarEmpleados(termino) {
        const queryParams = new URLSearchParams({ q: termino });
        return this.get(`/empleados/buscar/?${queryParams.toString()}`);
    }

    /**
     * Sistema de Usuarios
     */
    async getUsuarios() {
        return this.get('/usuarios/');
    }

    async createUsuario(data) {
        return this.post('/usuarios/', data);
    }

    /**
     * Notificaciones
     */
    async getNotificacionesUsuario(idUsuario) {
        return this.get(`/notificaciones/usuario/${idUsuario}/`);
    }

    async marcarNotificacionLeida(idNotificacion) {
        return this.put(`/notificaciones/${idNotificacion}/marcar-leida/`, {});
    }

    /**
     * Estadísticas y Reportes
     */
    async getEstadisticasGenerales() {
        return this.get('/estadisticas/generales/');
    }

    async getEstadisticasDepartamentos() {
        return this.get('/estadisticas/departamentos/');
    }
}

/**
 * Instancia global del cliente API
 */
const apiClient = new APIClient();

/**
 * Utilidades de validación de datos
 */
class DataValidator {
    /**
     * Validar datos de empleado
     */
    static validateEmpleado(data) {
        const errors = {};

        // Número de documento
        if (!data.numero_documento || !data.numero_documento.trim()) {
            errors.numero_documento = 'El número de documento es requerido';
        } else if (!/^\d+$/.test(data.numero_documento.trim())) {
            errors.numero_documento = 'El número de documento debe contener solo números';
        }

        // Tipo de documento
        if (!data.tipo_documento) {
            errors.tipo_documento = 'El tipo de documento es requerido';
        }

        // Nombres
        if (!data.nombres || !data.nombres.trim()) {
            errors.nombres = 'Los nombres son requeridos';
        } else if (data.nombres.trim().length < 2) {
            errors.nombres = 'Los nombres deben tener al menos 2 caracteres';
        }

        // Apellidos
        if (!data.apellidos || !data.apellidos.trim()) {
            errors.apellidos = 'Los apellidos son requeridos';
        } else if (data.apellidos.trim().length < 2) {
            errors.apellidos = 'Los apellidos deben tener al menos 2 caracteres';
        }

        // Email
        if (!data.email || !data.email.trim()) {
            errors.email = 'El email es requerido';
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email.trim())) {
            errors.email = 'El formato del email es inválido';
        }

        // Teléfono (formato colombiano)
        if (data.telefono && !/^\+57\s?\d{3}\s?\d{3}\s?\d{4}$/.test(data.telefono.trim())) {
            errors.telefono = 'El teléfono debe tener formato colombiano (+57 XXX XXX XXXX)';
        }

        // Fecha de nacimiento (mayor de 18 años)
        if (data.fecha_nacimiento) {
            const fechaNacimiento = new Date(data.fecha_nacimiento);
            const hoy = new Date();
            const edad = hoy.getFullYear() - fechaNacimiento.getFullYear();
            
            if (edad < 18) {
                errors.fecha_nacimiento = 'El empleado debe ser mayor de 18 años';
            }
        }

        // Salario
        if (data.salario !== undefined && data.salario !== null) {
            const salario = parseFloat(data.salario);
            if (isNaN(salario) || salario <= 0) {
                errors.salario = 'El salario debe ser un número mayor a 0';
            }
        }

        // Departamento
        if (!data.id_departamento) {
            errors.id_departamento = 'El departamento es requerido';
        }

        // Rol
        if (!data.id_rol) {
            errors.id_rol = 'El rol es requerido';
        }

        return {
            isValid: Object.keys(errors).length === 0,
            errors
        };
    }

    /**
     * Validar datos de departamento
     */
    static validateDepartamento(data) {
        const errors = {};

        // Nombre
        if (!data.nombre || !data.nombre.trim()) {
            errors.nombre = 'El nombre del departamento es requerido';
        } else if (data.nombre.trim().length < 3) {
            errors.nombre = 'El nombre debe tener al menos 3 caracteres';
        }

        // Descripción
        if (data.descripcion && data.descripcion.length > 500) {
            errors.descripcion = 'La descripción no puede exceder 500 caracteres';
        }

        return {
            isValid: Object.keys(errors).length === 0,
            errors
        };
    }

    /**
     * Sanitizar datos de entrada
     */
    static sanitizeInput(input) {
        if (typeof input !== 'string') return input;
        
        return input
            .trim()
            .replace(/[<>]/g, '') // Remover caracteres HTML básicos
            .substring(0, 1000); // Limitar longitud
    }

    /**
     * Formatear número de teléfono colombiano
     */
    static formatPhoneNumber(phone) {
        if (!phone) return '';
        
        // Remover todos los caracteres no numéricos excepto +
        const cleaned = phone.replace(/[^\d+]/g, '');
        
        // Si no empieza con +57, agregarlo
        if (cleaned.startsWith('57') && !cleaned.startsWith('+57')) {
            return `+${cleaned}`;
        }
        
        if (!cleaned.startsWith('+57') && cleaned.length === 10) {
            return `+57${cleaned}`;
        }
        
        return cleaned;
    }

    /**
     * Formatear salario colombiano
     */
    static formatSalary(amount) {
        if (!amount) return '';
        
        const number = parseFloat(amount);
        if (isNaN(number)) return '';
        
        return new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0
        }).format(number);
    }
}

/**
 * Cache simple para datos frecuentemente accedidos
 */
class DataCache {
    constructor() {
        this.cache = new Map();
        this.ttl = 5 * 60 * 1000; // 5 minutos
    }

    set(key, data) {
        this.cache.set(key, {
            data,
            timestamp: Date.now()
        });
    }

    get(key) {
        const item = this.cache.get(key);
        
        if (!item) return null;
        
        if (Date.now() - item.timestamp > this.ttl) {
            this.cache.delete(key);
            return null;
        }
        
        return item.data;
    }

    clear() {
        this.cache.clear();
    }

    delete(key) {
        this.cache.delete(key);
    }
}

// Instancia global del cache
const dataCache = new DataCache();

// Exportar para uso en otros módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { APIClient, DataValidator, DataCache, apiClient, dataCache };
}
