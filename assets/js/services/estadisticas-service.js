/**
 * Servicio de Estadísticas para Figger Energy SAS
 * Maneja todas las operaciones relacionadas con estadísticas y reportes
 */

class EstadisticasService {
    constructor() {
        this.apiClient = window.apiClient;
        this.config = window.API_CONFIG;
    }

    /**
     * Obtener estadísticas generales del sistema
     */
    async getGenerales() {
        try {
            const response = await this.apiClient.get(this.config.ENDPOINTS.ESTADISTICAS_GENERALES);
            
            if (response.success) {
                return {
                    success: true,
                    data: response.data,
                    timestamp: response.timestamp,
                    message: 'Estadísticas generales obtenidas exitosamente'
                };
            } else {
                return {
                    success: false,
                    message: response.message || 'Error al obtener estadísticas generales',
                    data: this.getDefaultStats()
                };
            }
        } catch (error) {
            console.error('Error obteniendo estadísticas generales:', error);
            return {
                success: false,
                message: 'Error de conexión al obtener estadísticas',
                data: this.getDefaultStats()
            };
        }
    }

    /**
     * Obtener estadísticas por departamentos
     */
    async getDepartamentos() {
        try {
            const response = await this.apiClient.get(this.config.ENDPOINTS.ESTADISTICAS_DEPARTAMENTOS);
            
            if (response.success) {
                return {
                    success: true,
                    data: response.data || [],
                    count: response.count || 0,
                    message: 'Estadísticas de departamentos obtenidas exitosamente'
                };
            } else {
                return {
                    success: false,
                    message: response.message || 'Error al obtener estadísticas de departamentos',
                    data: []
                };
            }
        } catch (error) {
            console.error('Error obteniendo estadísticas de departamentos:', error);
            return {
                success: false,
                message: 'Error de conexión al obtener estadísticas',
                data: []
            };
        }
    }

    /**
     * Estadísticas por defecto (fallback)
     */
    getDefaultStats() {
        return {
            total_empleados: 0,
            empleados_activos: 0,
            total_departamentos: 0,
            total_roles: 0,
            usuarios_activos: 0,
            sesiones_activas: 0,
            notificaciones_no_leidas: 0
        };
    }

    /**
     * Formatear estadísticas para mostrar en dashboard
     */
    formatStatsForDashboard(stats) {
        if (!stats) return [];

        return [
            {
                id: 'empleados_total',
                title: 'Total Empleados',
                value: this.formatNumber(stats.total_empleados || 0),
                icon: '👥',
                color: 'blue',
                description: 'Empleados registrados en el sistema'
            },
            {
                id: 'empleados_activos',
                title: 'Empleados Activos',
                value: this.formatNumber(stats.empleados_activos || 0),
                icon: '✅',
                color: 'green',
                description: 'Empleados con estado activo'
            },
            {
                id: 'departamentos',
                title: 'Departamentos',
                value: this.formatNumber(stats.total_departamentos || 0),
                icon: '🏢',
                color: 'purple',
                description: 'Departamentos organizacionales'
            },
            {
                id: 'roles',
                title: 'Roles',
                value: this.formatNumber(stats.total_roles || 0),
                icon: '🎭',
                color: 'orange',
                description: 'Roles definidos en el sistema'
            },
            {
                id: 'usuarios_activos',
                title: 'Usuarios Activos',
                value: this.formatNumber(stats.usuarios_activos || 0),
                icon: '🔐',
                color: 'indigo',
                description: 'Usuarios con acceso al sistema'
            },
            {
                id: 'sesiones_activas',
                title: 'Sesiones Activas',
                value: this.formatNumber(stats.sesiones_activas || 0),
                icon: '🔄',
                color: 'teal',
                description: 'Usuarios conectados actualmente'
            }
        ];
    }

    /**
     * Formatear números para mostrar
     */
    formatNumber(num) {
        if (num >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        } else if (num >= 1000) {
            return (num / 1000).toFixed(1) + 'K';
        } else {
            return num.toString();
        }
    }

    /**
     * Generar datos para gráficos de departamentos
     */
    formatDepartmentStats(departmentStats) {
        if (!departmentStats || !Array.isArray(departmentStats)) return null;

        return {
            labels: departmentStats.map(dept => dept.nombre),
            datasets: [
                {
                    label: 'Empleados Activos',
                    data: departmentStats.map(dept => dept.empleados_activos || 0),
                    backgroundColor: 'rgba(44, 95, 45, 0.7)',
                    borderColor: 'rgba(44, 95, 45, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Total Empleados',
                    data: departmentStats.map(dept => dept.total_empleados || 0),
                    backgroundColor: 'rgba(74, 139, 58, 0.7)',
                    borderColor: 'rgba(74, 139, 58, 1)',
                    borderWidth: 1
                }
            ]
        };
    }

    /**
     * Calcular estadísticas en tiempo real (simulación)
     */
    async getRealTimeStats() {
        try {
            // Simular actualización de estadísticas en tiempo real
            const baseStats = await this.getGenerales();
            
            if (baseStats.success) {
                const stats = { ...baseStats.data };
                
                // Agregar fluctuaciones menores (simulación)
                const variation = Math.floor(Math.random() * 5) - 2; // -2 a +2
                stats.sesiones_activas = Math.max(0, (stats.sesiones_activas || 0) + variation);
                
                // Actualizar timestamp
                stats.last_updated = new Date().toISOString();
                
                return {
                    success: true,
                    data: stats,
                    message: 'Estadísticas en tiempo real actualizadas'
                };
            } else {
                return baseStats;
            }
        } catch (error) {
            console.error('Error obteniendo estadísticas en tiempo real:', error);
            return {
                success: false,
                message: 'Error al obtener estadísticas en tiempo real',
                data: this.getDefaultStats()
            };
        }
    }

    /**
     * Generar reporte personalizado
     */
    async generateReport(filters = {}) {
        try {
            const [generalStats, departmentStats] = await Promise.all([
                this.getGenerales(),
                this.getDepartamentos()
            ]);

            const report = {
                generated_at: new Date().toISOString(),
                generated_by: window.authService?.getCurrentUser()?.empleado?.nombre_completo || 'Sistema',
                filters_applied: filters,
                data: {
                    general: generalStats.success ? generalStats.data : this.getDefaultStats(),
                    departments: departmentStats.success ? departmentStats.data : [],
                    summary: this.generateReportSummary(generalStats.data, departmentStats.data)
                }
            };

            return {
                success: true,
                data: report,
                message: 'Reporte generado exitosamente'
            };
        } catch (error) {
            console.error('Error generando reporte:', error);
            return {
                success: false,
                message: 'Error al generar reporte'
            };
        }
    }

    /**
     * Generar resumen del reporte
     */
    generateReportSummary(generalStats, departmentStats) {
        const summary = {
            total_entities: (generalStats?.total_empleados || 0) + (generalStats?.total_departamentos || 0),
            most_populated_department: null,
            efficiency_metrics: {
                employee_per_department: 0,
                active_percentage: 0
            }
        };

        if (departmentStats && Array.isArray(departmentStats) && departmentStats.length > 0) {
            // Encontrar departamento con más empleados
            const maxDept = departmentStats.reduce((max, dept) => 
                (dept.total_empleados || 0) > (max.total_empleados || 0) ? dept : max
            );
            summary.most_populated_department = maxDept.nombre;

            // Calcular métricas de eficiencia
            const totalEmployees = departmentStats.reduce((sum, dept) => sum + (dept.total_empleados || 0), 0);
            const totalActive = departmentStats.reduce((sum, dept) => sum + (dept.empleados_activos || 0), 0);
            
            summary.efficiency_metrics.employee_per_department = Math.round(totalEmployees / departmentStats.length);
            summary.efficiency_metrics.active_percentage = totalEmployees > 0 ? Math.round((totalActive / totalEmployees) * 100) : 0;
        }

        return summary;
    }

    /**
     * Exportar estadísticas a formato CSV
     */
    exportToCSV(data, filename = 'estadisticas_figger_energy') {
        try {
            let csvContent = '';
            
            if (data.general) {
                csvContent += 'ESTADÍSTICAS GENERALES\n';
                csvContent += 'Métrica,Valor\n';
                Object.entries(data.general).forEach(([key, value]) => {
                    csvContent += `${key},${value}\n`;
                });
                csvContent += '\n';
            }

            if (data.departments && Array.isArray(data.departments)) {
                csvContent += 'ESTADÍSTICAS POR DEPARTAMENTO\n';
                csvContent += 'Nombre,Total Empleados,Empleados Activos,Empleados Inactivos\n';
                data.departments.forEach(dept => {
                    csvContent += `${dept.nombre},${dept.total_empleados},${dept.empleados_activos},${dept.empleados_inactivos}\n`;
                });
            }

            // Crear y descargar archivo
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `${filename}_${new Date().toISOString().split('T')[0]}.csv`;
            link.click();

            return {
                success: true,
                message: 'Archivo CSV descargado exitosamente'
            };
        } catch (error) {
            console.error('Error exportando a CSV:', error);
            return {
                success: false,
                message: 'Error al exportar archivo CSV'
            };
        }
    }
}

// Crear instancia global del servicio
window.estadisticasService = new EstadisticasService();

console.log('✅ EstadisticasService inicializado correctamente');
