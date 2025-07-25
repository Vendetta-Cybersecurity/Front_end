/**
 * Configuración para Estadísticas Dinámicas - Figger Energy SAS
 * Archivo de configuración separado para facilitar ajustes
 */

// Configuración de tiempo
const ESTADISTICAS_CONFIG = {
    // Intervalo de actualización en milisegundos
    // 15000 = 15 segundos
    // 30000 = 30 segundos (para pruebas)
    intervaloActualizacion: 15000, // 15 segundos en producción
    
    // Para modo de desarrollo/pruebas, cambiar a true
    modoDesarrollo: false,
    intervaloDesarrollo: 15000, // 15 segundos para pruebas
    
    // Datos base del sistema
    datosBase: {
        territorioProtegido: 2.6,    // millones de hectáreas
        alertasProcesadas: 1236,     // alertas
        casosResueltos: 90,          // porcentaje
        areasRestauradas: 14526,     // hectáreas
        tiempoRespuesta: 6           // horas
    },
    
    // Rangos de variación permitidos (+ o -)
    rangosVariacion: {
        territorioProtegido: 0.1,    // ±0.1 millones
        alertasProcesadas: 50,       // ±50 alertas
        casosResueltos: 3,           // ±3%
        areasRestauradas: 500,       // ±500 hectáreas
        tiempoRespuesta: 1           // ±1 hora
    },
    
    // Límites mínimos y máximos para cada estadística
    limites: {
        territorioProtegido: { min: 2.4, max: 2.8 },
        alertasProcesadas: { min: 1150, max: 1350 },
        casosResueltos: { min: 85, max: 95 },
        areasRestauradas: { min: 13500, max: 16000 },
        tiempoRespuesta: { min: 4, max: 8 }
    },
    
    // Configuración de animaciones
    animaciones: {
        duracionActualizacion: 500,  // ms
        duracionPulso: 2000,         // ms
        habilitarAnimaciones: true
    },
    
    // Configuración de almacenamiento
    storage: {
        clave: 'figger_estadisticas',
        expiracionHoras: 24          // Expiran datos después de 24 horas
    },
    
    // Configuración de consola/debug
    debug: {
        mostrarLogs: true,
        mostrarActualizaciones: true,
        mostrarTipConsola: true
    }
};

// Función para obtener el intervalo según el modo
function obtenerIntervaloActualizacion() {
    return ESTADISTICAS_CONFIG.modoDesarrollo 
        ? ESTADISTICAS_CONFIG.intervaloDesarrollo 
        : ESTADISTICAS_CONFIG.intervaloActualizacion;
}

// Exportar configuración para uso global
if (typeof window !== 'undefined') {
    window.ESTADISTICAS_CONFIG = ESTADISTICAS_CONFIG;
    window.obtenerIntervaloActualizacion = obtenerIntervaloActualizacion;
}
