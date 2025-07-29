/**
 * Servicio de Empleados para Figger Energy SAS
 * Maneja todas las operaciones relacionadas con empleados
 */

class EmpleadosService {
    constructor() {
        this.apiClient = window.apiClient;
        this.config = window.API_CONFIG;
    }

    /**
     * Obtener todos los empleados
     */
    async getAll(filters = {}) {
        try {
            const params = {};
            
            // Agregar filtros si existen
            if (filters.estado) params.estado = filters.estado;
            if (filters.departamento) params.departamento = filters.departamento;
            if (filters.rol) params.rol = filters.rol;
            
            const response = await this.apiClient.get(this.config.ENDPOINTS.EMPLEADOS, params);
            
            if (response.success) {
                return {
                    success: true,
                    data: response.data || [],
                    count: response.count || 0,
                    message: 'Empleados obtenidos exitosamente'
                };
            } else {
                return {
                    success: false,
                    message: response.message || 'Error al obtener empleados',
                    data: []
                };
            }
        } catch (error) {
            console.error('Error obteniendo empleados:', error);
            return {
                success: false,
                message: 'Error de conexión al obtener empleados',
                data: []
            };
        }
    }

    /**
     * Obtener empleado por ID
     */
    async getById(id) {
        try {
            const endpoint = this.apiClient.replaceUrlParams(
                this.config.ENDPOINTS.EMPLEADO_BY_ID, 
                { id }
            );
            
            const response = await this.apiClient.get(endpoint);
            
            if (response.success) {
                return {
                    success: true,
                    data: response.data,
                    message: 'Empleado obtenido exitosamente'
                };
            } else {
                return {
                    success: false,
                    message: response.message || 'Empleado no encontrado'
                };
            }
        } catch (error) {
            console.error('Error obteniendo empleado:', error);
            return {
                success: false,
                message: 'Error de conexión al obtener empleado'
            };
        }
    }

    /**
     * Crear nuevo empleado
     */
    async create(empleadoData) {
        try {
            // Validar datos requeridos
            const validationResult = this.validateEmpleadoData(empleadoData);
            if (!validationResult.isValid) {
                return {
                    success: false,
                    message: 'Datos inválidos',
                    errors: validationResult.errors
                };
            }

            const response = await this.apiClient.post(this.config.ENDPOINTS.EMPLEADOS, empleadoData);
            
            if (response.success) {
                return {
                    success: true,
                    data: response.data,
                    message: 'Empleado creado exitosamente'
                };
            } else {
                return {
                    success: false,
                    message: response.message || 'Error al crear empleado',
                    errors: response.errors
                };
            }
        } catch (error) {
            console.error('Error creando empleado:', error);
            return {
                success: false,
                message: 'Error de conexión al crear empleado'
            };
        }
    }

    /**
     * Actualizar empleado existente
     */
    async update(id, empleadoData) {
        try {
            const endpoint = this.apiClient.replaceUrlParams(
                this.config.ENDPOINTS.EMPLEADO_BY_ID, 
                { id }
            );
            
            const response = await this.apiClient.put(endpoint, empleadoData);
            
            if (response.success) {
                return {
                    success: true,
                    data: response.data,
                    message: 'Empleado actualizado exitosamente'
                };
            } else {
                return {
                    success: false,
                    message: response.message || 'Error al actualizar empleado',
                    errors: response.errors
                };
            }
        } catch (error) {
            console.error('Error actualizando empleado:', error);
            return {
                success: false,
                message: 'Error de conexión al actualizar empleado'
            };
        }
    }

    /**
     * Eliminar empleado (dar de baja)
     */
    async delete(id) {
        try {
            const endpoint = this.apiClient.replaceUrlParams(
                this.config.ENDPOINTS.EMPLEADO_BY_ID, 
                { id }
            );
            
            const response = await this.apiClient.delete(endpoint);
            
            if (response.success) {
                return {
                    success: true,
                    message: 'Empleado dado de baja exitosamente'
                };
            } else {
                return {
                    success: false,
                    message: response.message || 'Error al dar de baja empleado'
                };
            }
        } catch (error) {
            console.error('Error dando de baja empleado:', error);
            return {
                success: false,
                message: 'Error de conexión al dar de baja empleado'
            };
        }
    }

    /**
     * Buscar empleados
     */
    async search(query) {
        try {
            const params = { q: query };
            const response = await this.apiClient.get(this.config.ENDPOINTS.EMPLEADOS_BUSCAR, params);
            
            if (response.success) {
                return {
                    success: true,
                    data: response.data || [],
                    count: response.count || 0,
                    message: 'Búsqueda completada exitosamente'
                };
            } else {
                return {
                    success: false,
                    message: response.message || 'Error en la búsqueda',
                    data: []
                };
            }
        } catch (error) {
            console.error('Error en búsqueda de empleados:', error);
            return {
                success: false,
                message: 'Error de conexión en la búsqueda',
                data: []
            };
        }
    }

    /**
     * Validar datos de empleado
     */
    validateEmpleadoData(data) {
        const errors = {};
        
        // Campos requeridos
        const requiredFields = [
            'numero_documento', 'tipo_documento', 'nombres', 'apellidos', 
            'email', 'telefono', 'fecha_nacimiento', 'direccion', 'ciudad',
            'id_departamento', 'id_rol', 'fecha_ingreso', 'salario'
        ];
        
        requiredFields.forEach(field => {
            if (!data[field] || data[field].toString().trim() === '') {
                errors[field] = `${field} es requerido`;
            }
        });
        
        // Validaciones específicas
        if (data.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
            errors.email = 'Email inválido';
        }
        
        if (data.numero_documento && !/^\d{8,11}$/.test(data.numero_documento)) {
            errors.numero_documento = 'Número de documento debe tener entre 8 y 11 dígitos';
        }
        
        if (data.telefono && !/^\+?57\s?[3]\d{2}\s?\d{3}\s?\d{4}$/.test(data.telefono)) {
            errors.telefono = 'Formato de teléfono inválido (+57 3XX XXX XXXX)';
        }
        
        if (data.salario && (isNaN(data.salario) || parseFloat(data.salario) <= 0)) {
            errors.salario = 'Salario debe ser un número positivo';
        }
        
        return {
            isValid: Object.keys(errors).length === 0,
            errors
        };
    }

    /**
     * Formatear datos de empleado para mostrar
     */
    formatEmpleadoData(empleado) {
        if (!empleado) return null;
        
        return {
            ...empleado,
            nombre_completo: `${empleado.nombres} ${empleado.apellidos}`,
            telefono_formateado: this.formatPhone(empleado.telefono),
            salario_formateado: this.formatCurrency(empleado.salario),
            fecha_ingreso_formateada: this.formatDate(empleado.fecha_ingreso),
            fecha_nacimiento_formateada: this.formatDate(empleado.fecha_nacimiento)
        };
    }

    /**
     * Formatear teléfono
     */
    formatPhone(phone) {
        if (!phone) return '';
        const cleaned = phone.replace(/\D/g, '');
        if (cleaned.length === 10) {
            return `+57 ${cleaned.slice(0, 3)} ${cleaned.slice(3, 6)} ${cleaned.slice(6)}`;
        }
        return phone;
    }

    /**
     * Formatear moneda
     */
    formatCurrency(amount) {
        if (!amount) return '$0';
        return new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    }

    /**
     * Formatear fecha
     */
    formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('es-CO', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    /**
     * Obtener estadísticas de empleados
     */
    async getStats() {
        try {
            const response = await this.getAll();
            
            if (response.success) {
                const empleados = response.data;
                
                const stats = {
                    total: empleados.length,
                    activos: empleados.filter(e => e.estado === 'activo').length,
                    inactivos: empleados.filter(e => e.estado === 'inactivo').length,
                    suspendidos: empleados.filter(e => e.estado === 'suspendido').length,
                    por_departamento: this.groupByDepartment(empleados),
                    por_rol: this.groupByRole(empleados)
                };
                
                return {
                    success: true,
                    data: stats
                };
            } else {
                return response;
            }
        } catch (error) {
            console.error('Error obteniendo estadísticas de empleados:', error);
            return {
                success: false,
                message: 'Error al obtener estadísticas'
            };
        }
    }

    /**
     * Agrupar empleados por departamento
     */
    groupByDepartment(empleados) {
        const groups = {};
        empleados.forEach(empleado => {
            const dept = empleado.departamento_nombre || 'Sin departamento';
            if (!groups[dept]) {
                groups[dept] = 0;
            }
            groups[dept]++;
        });
        return groups;
    }

    /**
     * Agrupar empleados por rol
     */
    groupByRole(empleados) {
        const groups = {};
        empleados.forEach(empleado => {
            const rol = empleado.rol_nombre || 'Sin rol';
            if (!groups[rol]) {
                groups[rol] = 0;
            }
            groups[rol]++;
        });
        return groups;
    }
}

// Crear instancia global del servicio
window.empleadosService = new EmpleadosService();

console.log('✅ EmpleadosService inicializado correctamente');
