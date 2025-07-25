/**
 * Sistema de Estadísticas Dinámicas - Figger Energy SAS
 * Actualiza automáticamente los datos de impacto cada 15 segundos
 */

class EstadisticasDinamicas {
    constructor() {
        // Usar configuración externa si está disponible
        const config = window.ESTADISTICAS_CONFIG || {};
        
        // Datos base para las estadísticas
        this.datosBase = config.datosBase || {
            territorioProtegido: 2.6, // millones de hectáreas
            alertasProcesadas: 1236,
            casosResueltos: 90, // porcentaje
            areasRestauradas: 14526, // hectáreas
            tiempoRespuesta: 6 // horas
        };

        // Rangos de variación (+ o -)
        this.rangosVariacion = config.rangosVariacion || {
            territorioProtegido: 0.1, // ±0.1 millones
            alertasProcesadas: 50,    // ±50 alertas
            casosResueltos: 3,        // ±3%
            areasRestauradas: 500,    // ±500 hectáreas
            tiempoRespuesta: 1        // ±1 hora
        };
        
        // Límites para cada estadística
        this.limites = config.limites || {
            territorioProtegido: { min: 2.4, max: 2.8 },
            alertasProcesadas: { min: 1150, max: 1350 },
            casosResueltos: { min: 85, max: 95 },
            areasRestauradas: { min: 13500, max: 16000 },
            tiempoRespuesta: { min: 4, max: 8 }
        };

        // Configuración
        this.config = config;
        this.intervaloActualizacion = this.config.modoDesarrollo ? 
            this.config.intervaloDesarrollo || 15000 : 
            this.config.intervaloActualizacion || 15000;

        // Valores actuales
        this.valoresActuales = { ...this.datosBase };
        
        // Inicializar sistema
        this.inicializar();
    }

    inicializar() {
        // Cargar valores guardados del localStorage si existen
        this.cargarValoresPrevios();
        
        // Actualizar display inicial
        this.actualizarDisplay();
        
        // Configurar timer para actualizaciones automáticas
        this.configurarActualizacionAutomatica();
        
        // Mostrar hora de última actualización
        this.actualizarHoraActualizacion();
        
        if (this.config.debug?.mostrarLogs) {
            console.log('📊 Sistema de estadísticas dinámicas iniciado');
            console.log(`🔄 Próxima actualización en ${this.intervaloActualizacion / 1000} segundos`);
            if (this.config.modoDesarrollo) {
                console.log('⚠️ MODO DESARROLLO ACTIVADO - Actualizaciones cada 15 segundos');
            }
        }
    }

    cargarValoresPrevios() {
        try {
            const clave = this.config.storage?.clave || 'figger_estadisticas';
            const valoresGuardados = localStorage.getItem(clave);
            if (valoresGuardados) {
                const datos = JSON.parse(valoresGuardados);
                
                // Verificar que los datos no sean muy antiguos
                const ultimaActualizacion = new Date(datos.timestamp);
                const ahora = new Date();
                const horasTranscurridas = (ahora - ultimaActualizacion) / (1000 * 60 * 60);
                const expiracion = this.config.storage?.expiracionHoras || 24;
                
                if (horasTranscurridas < expiracion) {
                    this.valoresActuales = datos.valores;
                    if (this.config.debug?.mostrarLogs) {
                        console.log('✅ Valores previos cargados desde localStorage');
                    }
                } else {
                    if (this.config.debug?.mostrarLogs) {
                        console.log('⏰ Datos muy antiguos, usando valores base');
                    }
                }
            }
        } catch (error) {
            if (this.config.debug?.mostrarLogs) {
                console.log('⚠️ Error cargando valores previos:', error);
            }
        }
    }

    guardarValores() {
        try {
            const clave = this.config.storage?.clave || 'figger_estadisticas';
            const datosParaGuardar = {
                valores: this.valoresActuales,
                timestamp: new Date().toISOString()
            };
            localStorage.setItem(clave, JSON.stringify(datosParaGuardar));
        } catch (error) {
            if (this.config.debug?.mostrarLogs) {
                console.log('⚠️ Error guardando valores:', error);
            }
        }
    }

    generarNuevoValor(campo) {
        const valorBase = this.datosBase[campo];
        const rango = this.rangosVariacion[campo];
        
        // Generar variación aleatoria dentro del rango
        const variacion = (Math.random() - 0.5) * 2 * rango;
        let nuevoValor = valorBase + variacion;
        
        // Usar límites de configuración
        const limites = this.limites[campo];
        if (limites) {
            if (campo === 'territorioProtegido') {
                nuevoValor = Math.max(limites.min, Math.min(limites.max, nuevoValor));
            } else {
                nuevoValor = Math.max(limites.min, Math.min(limites.max, Math.round(nuevoValor)));
            }
        }
        
        return nuevoValor;
    }

    actualizarEstadisticas() {
        if (this.config.debug?.mostrarActualizaciones) {
            console.log('🔄 Actualizando estadísticas...');
        }
        
        // Generar nuevos valores
        Object.keys(this.valoresActuales).forEach(campo => {
            this.valoresActuales[campo] = this.generarNuevoValor(campo);
        });
        
        // Guardar valores
        this.guardarValores();
        
        // Actualizar display con animación
        this.actualizarDisplayConAnimacion();
        
        // Actualizar hora
        this.actualizarHoraActualizacion();
        
        if (this.config.debug?.mostrarActualizaciones) {
            console.log('✅ Estadísticas actualizadas:', this.valoresActuales);
        }
    }

    actualizarDisplay() {
        // Territorio Protegido
        const territorioEl = document.getElementById('territorio-protegido');
        if (territorioEl) {
            territorioEl.textContent = this.valoresActuales.territorioProtegido.toFixed(1) + ' millones';
        }
        
        // Alertas Procesadas
        const alertasEl = document.getElementById('alertas-procesadas');
        if (alertasEl) {
            alertasEl.textContent = this.valoresActuales.alertasProcesadas.toLocaleString();
        }
        
        // Casos Resueltos
        const casosEl = document.getElementById('casos-resueltos');
        if (casosEl) {
            casosEl.textContent = this.valoresActuales.casosResueltos;
        }
        
        // Áreas Restauradas
        const areasEl = document.getElementById('areas-restauradas');
        if (areasEl) {
            areasEl.textContent = this.valoresActuales.areasRestauradas.toLocaleString();
        }
        
        // Tiempo de Respuesta
        const tiempoEl = document.getElementById('tiempo-respuesta');
        if (tiempoEl) {
            tiempoEl.textContent = this.valoresActuales.tiempoRespuesta;
        }
    }

    actualizarDisplayConAnimacion() {
        const habilitarAnimaciones = this.config.animaciones?.habilitarAnimaciones !== false;
        
        if (habilitarAnimaciones) {
            // Añadir clase de animación a la sección
            const seccion = document.getElementById('estadisticas-seccion');
            if (seccion) {
                seccion.classList.add('actualizando');
                
                const duracion = this.config.animaciones?.duracionActualizacion || 500;
                setTimeout(() => {
                    this.actualizarDisplay();
                    seccion.classList.remove('actualizando');
                    seccion.classList.add('actualizado');
                    
                    const duracionPulso = this.config.animaciones?.duracionPulso || 2000;
                    setTimeout(() => {
                        seccion.classList.remove('actualizado');
                    }, duracionPulso);
                }, duracion);
            } else {
                this.actualizarDisplay();
            }
        } else {
            this.actualizarDisplay();
        }
    }

    actualizarHoraActualizacion() {
        const elemento = document.getElementById('ultima-actualizacion');
        if (elemento) {
            const ahora = new Date();
            const opciones = {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            elemento.textContent = ahora.toLocaleDateString('es-CO', opciones);
        }
    }

    configurarActualizacionAutomatica() {
        // Actualizar según el intervalo configurado
        setInterval(() => {
            this.actualizarEstadisticas();
        }, this.intervaloActualizacion);
    }

    // Método público para actualización manual (para pruebas)
    actualizarManual() {
        this.actualizarEstadisticas();
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Solo inicializar en la página principal
    if (document.getElementById('estadisticas-seccion')) {
        window.estadisticasDinamicas = new EstadisticasDinamicas();
        
        // Para desarrolladores: agregar función global para pruebas
        window.actualizarEstadisticas = function() {
            window.estadisticasDinamicas.actualizarManual();
        };
        
        if (window.estadisticasDinamicas.config.debug?.mostrarTipConsola) {
            console.log('💡 Tip: Ejecuta "actualizarEstadisticas()" en la consola para ver una actualización inmediata');
        }
    }
});
