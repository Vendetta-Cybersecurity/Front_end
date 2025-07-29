/**
 * Cliente API base para Figger Energy SAS
 * Maneja todas las peticiones HTTP y respuestas del backend
 */

class ApiClient {
    constructor() {
        this.config = window.API_CONFIG;
        this.baseURL = this.config.BASE_URL;
        this.defaultHeaders = { ...this.config.DEFAULT_HEADERS };
    }

    /**
     * Realizar petición HTTP genérica
     */
    async request(endpoint, options = {}) {
        const url = this.buildUrl(endpoint);
        
        const config = {
            method: options.method || 'GET',
            headers: {
                ...this.defaultHeaders,
                ...this.getAuthHeaders(),
                ...options.headers
            },
            ...options
        };

        // Agregar body si existe y no es GET
        if (options.body && config.method !== 'GET') {
            if (typeof options.body === 'object') {
                config.body = JSON.stringify(options.body);
            } else {
                config.body = options.body;
            }
        }

        try {
            console.log(`API Request: ${config.method} ${url}`);
            
            const response = await this.fetchWithTimeout(url, config);
            
            console.log(`API Response: ${response.status} for ${url}`);
            
            return await this.handleResponse(response);
            
        } catch (error) {
            console.error('API Request failed:', error);
            return this.handleError(error);
        }
    }

    /**
     * Fetch con timeout
     */
    async fetchWithTimeout(url, config) {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), this.config.REQUEST_TIMEOUT);
        
        try {
            const response = await fetch(url, {
                ...config,
                signal: controller.signal
            });
            clearTimeout(timeoutId);
            return response;
        } catch (error) {
            clearTimeout(timeoutId);
            if (error.name === 'AbortError') {
                throw new Error(this.config.ERROR_MESSAGES.TIMEOUT);
            }
            throw error;
        }
    }

    /**
     * Construir URL completa
     */
    buildUrl(endpoint) {
        // Si el endpoint ya es una URL completa, usarla directamente
        if (endpoint.startsWith('http')) {
            return endpoint;
        }
        
        // Limpiar barras duplicadas
        const cleanEndpoint = endpoint.startsWith('/') ? endpoint.slice(1) : endpoint;
        const cleanBaseUrl = this.baseURL.endsWith('/') ? this.baseURL : this.baseURL + '/';
        
        return cleanBaseUrl + cleanEndpoint;
    }

    /**
     * Obtener headers de autenticación
     */
    getAuthHeaders() {
        const token = this.getStoredToken();
        if (token) {
            return {
                'Authorization': `Bearer ${token}`
            };
        }
        return {};
    }

    /**
     * Obtener token almacenado
     */
    getStoredToken() {
        return localStorage.getItem(this.config.AUTH.TOKEN_KEY);
    }

    /**
     * Manejar respuesta de la API
     */
    async handleResponse(response) {
        let data = null;
        
        // Intentar parsear JSON
        try {
            const text = await response.text();
            if (text) {
                data = JSON.parse(text);
            }
        } catch (parseError) {
            console.warn('No se pudo parsear respuesta como JSON:', parseError);
        }

        if (response.ok) {
            return {
                success: true,
                data: data?.data || data,
                message: data?.message,
                count: data?.count,
                status: response.status,
                response: data
            };
        } else {
            return this.handleHttpError(response, data);
        }
    }

    /**
     * Manejar errores HTTP
     */
    handleHttpError(response, data) {
        let errorMessage = this.config.ERROR_MESSAGES.SERVER_ERROR;
        
        switch (response.status) {
            case this.config.HTTP_STATUS.BAD_REQUEST:
                errorMessage = data?.message || this.config.ERROR_MESSAGES.VALIDATION_ERROR;
                break;
            case this.config.HTTP_STATUS.UNAUTHORIZED:
                errorMessage = this.config.ERROR_MESSAGES.UNAUTHORIZED;
                this.handleUnauthorized();
                break;
            case this.config.HTTP_STATUS.FORBIDDEN:
                errorMessage = this.config.ERROR_MESSAGES.FORBIDDEN;
                break;
            case this.config.HTTP_STATUS.NOT_FOUND:
                errorMessage = this.config.ERROR_MESSAGES.NOT_FOUND;
                break;
            case this.config.HTTP_STATUS.INTERNAL_SERVER_ERROR:
                errorMessage = data?.message || this.config.ERROR_MESSAGES.SERVER_ERROR;
                break;
        }

        return {
            success: false,
            error: true,
            message: errorMessage,
            errors: data?.errors,
            status: response.status,
            response: data
        };
    }

    /**
     * Manejar error genérico
     */
    handleError(error) {
        let errorMessage = this.config.ERROR_MESSAGES.NETWORK_ERROR;
        
        if (error.message === this.config.ERROR_MESSAGES.TIMEOUT) {
            errorMessage = this.config.ERROR_MESSAGES.TIMEOUT;
        } else if (error.message) {
            errorMessage = error.message;
        }

        return {
            success: false,
            error: true,
            message: errorMessage,
            originalError: error
        };
    }

    /**
     * Manejar error de autenticación
     */
    handleUnauthorized() {
        // Limpiar datos de sesión
        localStorage.removeItem(this.config.AUTH.TOKEN_KEY);
        localStorage.removeItem(this.config.AUTH.USER_KEY);
        
        // Redirigir a login si no estamos ya ahí
        if (!window.location.pathname.includes('login.html')) {
            setTimeout(() => {
                window.location.href = 'login.html';
            }, 1000);
        }
    }

    // Métodos HTTP específicos
    async get(endpoint, params = {}) {
        const url = this.buildUrlWithParams(endpoint, params);
        return this.request(url, { method: 'GET' });
    }

    async post(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'POST',
            body: data
        });
    }

    async put(endpoint, data = {}) {
        return this.request(endpoint, {
            method: 'PUT',
            body: data
        });
    }

    async delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    }

    /**
     * Construir URL con parámetros de query
     */
    buildUrlWithParams(endpoint, params) {
        if (Object.keys(params).length === 0) {
            return endpoint;
        }

        const url = new URL(this.buildUrl(endpoint));
        Object.keys(params).forEach(key => {
            if (params[key] !== null && params[key] !== undefined) {
                url.searchParams.append(key, params[key]);
            }
        });

        return url.toString();
    }

    /**
     * Reemplazar parámetros en endpoint (ejemplo: /users/{id}/ -> /users/123/)
     */
    replaceUrlParams(endpoint, params) {
        let url = endpoint;
        Object.keys(params).forEach(key => {
            url = url.replace(`{${key}}`, params[key]);
        });
        return url;
    }

    /**
     * Verificar estado de la API
     */
    async checkStatus() {
        try {
            const response = await this.get(this.config.ENDPOINTS.STATUS);
            console.log('API Status:', response);
            return response;
        } catch (error) {
            console.error('No se pudo conectar con la API:', error);
            return { success: false, message: 'API no disponible' };
        }
    }
}

// Crear instancia global del cliente API
window.apiClient = new ApiClient();

// Verificar estado de la API al cargar
document.addEventListener('DOMContentLoaded', async function() {
    const status = await window.apiClient.checkStatus();
    if (status.success) {
        console.log('✅ Conexión con API establecida correctamente');
    } else {
        console.warn('⚠️ No se pudo conectar con la API backend');
    }
});

console.log('✅ ApiClient inicializado correctamente');
