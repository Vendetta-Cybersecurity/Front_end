/**
 * Configuración de API para Figger Energy SAS
 * Base URLs y configuración general para comunicación con backend
 */

const API_CONFIG = {
    // URLs base
    BASE_URL: 'http://127.0.0.1:8000/api/',
    ADMIN_URL: 'http://127.0.0.1:8000/admin/',
    
    // Headers por defecto
    DEFAULT_HEADERS: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
    
    // Timeouts
    REQUEST_TIMEOUT: 10000, // 10 segundos
    
    // Configuración de autenticación
    AUTH: {
        TOKEN_KEY: 'figger_auth_token',
        USER_KEY: 'figger_user_data',
        SESSION_DURATION: 8 * 60 * 60 * 1000, // 8 horas en milisegundos
        AUTO_REFRESH: true
    },
    
    // Endpoints disponibles
    ENDPOINTS: {
        // Status y configuración
        STATUS: 'status/',
        CONFIG_VM: 'config-vm/',
        
        // Departamentos
        DEPARTAMENTOS: 'departamentos/',
        DEPARTAMENTO_BY_ID: 'departamentos/{id}/',
        
        // Roles
        ROLES: 'roles/',
        ROLES_BY_DEPARTAMENTO: 'roles/departamento/{id_departamento}/',
        
        // Empleados
        EMPLEADOS: 'empleados/',
        EMPLEADO_BY_ID: 'empleados/{id}/',
        EMPLEADOS_BUSCAR: 'empleados/buscar/',
        
        // Usuarios
        USUARIOS: 'usuarios/',
        USUARIO_BY_ID: 'usuarios/{id}/',
        
        // Notificaciones
        NOTIFICACIONES_USUARIO: 'notificaciones/usuario/{id_usuario}/',
        NOTIFICACION_MARCAR_LEIDA: 'notificaciones/{id}/marcar-leida/',
        
        // Estadísticas
        ESTADISTICAS_GENERALES: 'estadisticas/generales/',
        ESTADISTICAS_DEPARTAMENTOS: 'estadisticas/departamentos/'
    },
    
    // Estados HTTP
    HTTP_STATUS: {
        OK: 200,
        CREATED: 201,
        NO_CONTENT: 204,
        BAD_REQUEST: 400,
        UNAUTHORIZED: 401,
        FORBIDDEN: 403,
        NOT_FOUND: 404,
        INTERNAL_SERVER_ERROR: 500
    },
    
    // Mensajes de error estándar
    ERROR_MESSAGES: {
        NETWORK_ERROR: 'Error de conexión. Verifique su conexión a internet.',
        SERVER_ERROR: 'Error interno del servidor. Intente nuevamente.',
        UNAUTHORIZED: 'Sesión expirada. Por favor, inicie sesión nuevamente.',
        FORBIDDEN: 'No tiene permisos para realizar esta acción.',
        NOT_FOUND: 'El recurso solicitado no fue encontrado.',
        VALIDATION_ERROR: 'Datos inválidos. Verifique la información ingresada.',
        TIMEOUT: 'La operación tardó demasiado. Intente nuevamente.'
    }
};

// Configuración para entorno de producción
const PRODUCTION_CONFIG = {
    BASE_URL: 'http://[BACKEND_VM_IP]:8000/api/',
    ADMIN_URL: 'http://[BACKEND_VM_IP]:8000/admin/'
};

// Función para detectar entorno
function getEnvironment() {
    if (window.location.hostname === 'localhost' || 
        window.location.hostname === '127.0.0.1' ||
        window.location.hostname.includes('local')) {
        return 'development';
    }
    return 'production';
}

// Configuración dinámica según entorno
function getApiConfig() {
    const env = getEnvironment();
    if (env === 'production') {
        return { ...API_CONFIG, ...PRODUCTION_CONFIG };
    }
    return API_CONFIG;
}

// Exportar configuración global
window.API_CONFIG = getApiConfig();
window.getApiConfig = getApiConfig;

console.log('API Config cargada para entorno:', getEnvironment());
