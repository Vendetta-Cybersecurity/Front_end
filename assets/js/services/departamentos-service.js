/**
 * Servicio de Departamentos para Figger Energy SAS
 * Maneja todas las operaciones relacionadas con departamentos
 */

class DepartamentosService {
    constructor() {
        this.apiClient = window.apiClient;
        this.config = window.API_CONFIG;
    }

    /**
     * Obtener todos los departamentos
     */
    async getAll() {
        try {
            const response = await this.apiClient.get(this.config.ENDPOINTS.DEPARTAMENTOS);
            
            if (response.success) {
                return {
                    success: true,
                    data: response.data || [],
                    count: response.count || 0,
                    message: 'Departamentos obtenidos exitosamente'
                };
            } else {
                return {
                    success: false,
                    message: response.message || 'Error al obtener departamentos',
                    data: []
                };
            }
        } catch (error) {
            console.error('Error obteniendo departamentos:', error);
            return {
                success: false,
                message: 'Error de conexión al obtener departamentos',
                data: []
            };
        }
    }

    /**
     * Obtener departamento por ID con empleados
     */
    async getById(id) {
        try {
            const endpoint = this.apiClient.replaceUrlParams(
                this.config.ENDPOINTS.DEPARTAMENTO_BY_ID, 
                { id }
            );
            
            const response = await this.apiClient.get(endpoint);
            
            if (response.success) {
                return {
                    success: true,
                    data: response.data,
                    message: 'Departamento obtenido exitosamente'
                };
            } else {
                return {
                    success: false,
                    message: response.message || 'Departamento no encontrado'
                };
            }
        } catch (error) {
            console.error('Error obteniendo departamento:', error);
            return {
                success: false,
                message: 'Error de conexión al obtener departamento'
            };
        }
    }

    /**
     * Crear nuevo departamento
     */
    async create(departamentoData) {
        try {
            // Validar datos requeridos
            const validationResult = this.validateDepartamentoData(departamentoData);
            if (!validationResult.isValid) {
                return {
                    success: false,
                    message: 'Datos inválidos',
                    errors: validationResult.errors
                };
            }

            const response = await this.apiClient.post(this.config.ENDPOINTS.DEPARTAMENTOS, departamentoData);
            
            if (response.success) {
                return {
                    success: true,
                    data: response.data,
                    message: 'Departamento creado exitosamente'
                };
            } else {
                return {
                    success: false,
                    message: response.message || 'Error al crear departamento',
                    errors: response.errors
                };
            }
        } catch (error) {
            console.error('Error creando departamento:', error);
            return {
                success: false,
                message: 'Error de conexión al crear departamento'
            };
        }
    }

    /**
     * Actualizar departamento existente
     */
    async update(id, departamentoData) {
        try {
            const endpoint = this.apiClient.replaceUrlParams(
                this.config.ENDPOINTS.DEPARTAMENTO_BY_ID, 
                { id }
            );
            
            const response = await this.apiClient.put(endpoint, departamentoData);
            
            if (response.success) {
                return {
                    success: true,
                    data: response.data,
                    message: 'Departamento actualizado exitosamente'
                };
            } else {
                return {
                    success: false,
                    message: response.message || 'Error al actualizar departamento',
                    errors: response.errors
                };
            }
        } catch (error) {
            console.error('Error actualizando departamento:', error);
            return {
                success: false,
                message: 'Error de conexión al actualizar departamento'
            };
        }
    }

    /**
     * Eliminar departamento
     */
    async delete(id) {
        try {
            const endpoint = this.apiClient.replaceUrlParams(
                this.config.ENDPOINTS.DEPARTAMENTO_BY_ID, 
                { id }
            );
            
            const response = await this.apiClient.delete(endpoint);
            
            if (response.success) {
                return {
                    success: true,
                    message: 'Departamento eliminado exitosamente'
                };
            } else {
                return {
                    success: false,
                    message: response.message || 'Error al eliminar departamento'
                };
            }
        } catch (error) {
            console.error('Error eliminando departamento:', error);
            return {
                success: false,
                message: 'Error de conexión al eliminar departamento'
            };
        }
    }

    /**
     * Validar datos de departamento
     */
    validateDepartamentoData(data) {
        const errors = {};
        
        // Campos requeridos
        if (!data.nombre || data.nombre.trim() === '') {
            errors.nombre = 'El nombre del departamento es requerido';
        }
        
        if (!data.descripcion || data.descripcion.trim() === '') {
            errors.descripcion = 'La descripción del departamento es requerida';
        }
        
        // Validaciones específicas
        if (data.nombre && data.nombre.length < 3) {
            errors.nombre = 'El nombre debe tener al menos 3 caracteres';
        }
        
        if (data.descripcion && data.descripcion.length < 10) {
            errors.descripcion = 'La descripción debe tener al menos 10 caracteres';
        }
        
        return {
            isValid: Object.keys(errors).length === 0,
            errors
        };
    }

    /**
     * Obtener departamentos para select/dropdown
     */
    async getForSelect() {
        try {
            const response = await this.getAll();
            
            if (response.success) {
                const options = response.data.map(dept => ({
                    value: dept.id_departamento,
                    label: dept.nombre,
                    data: dept
                }));
                
                return {
                    success: true,
                    data: options
                };
            } else {
                return response;
            }
        } catch (error) {
            console.error('Error obteniendo departamentos para select:', error);
            return {
                success: false,
                message: 'Error al obtener departamentos',
                data: []
            };
        }
    }

    /**
     * Obtener estadísticas de departamentos
     */
    async getStats() {
        try {
            const response = await this.apiClient.get(this.config.ENDPOINTS.ESTADISTICAS_DEPARTAMENTOS);
            
            if (response.success) {
                return {
                    success: true,
                    data: response.data || [],
                    message: 'Estadísticas obtenidas exitosamente'
                };
            } else {
                return {
                    success: false,
                    message: response.message || 'Error al obtener estadísticas',
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
}

/**
 * Servicio de Roles para Figger Energy SAS
 */
class RolesService {
    constructor() {
        this.apiClient = window.apiClient;
        this.config = window.API_CONFIG;
    }

    /**
     * Obtener todos los roles
     */
    async getAll() {
        try {
            const response = await this.apiClient.get(this.config.ENDPOINTS.ROLES);
            
            if (response.success) {
                return {
                    success: true,
                    data: response.data || [],
                    count: response.count || 0,
                    message: 'Roles obtenidos exitosamente'
                };
            } else {
                return {
                    success: false,
                    message: response.message || 'Error al obtener roles',
                    data: []
                };
            }
        } catch (error) {
            console.error('Error obteniendo roles:', error);
            return {
                success: false,
                message: 'Error de conexión al obtener roles',
                data: []
            };
        }
    }

    /**
     * Obtener roles por departamento
     */
    async getByDepartamento(idDepartamento) {
        try {
            const endpoint = this.apiClient.replaceUrlParams(
                this.config.ENDPOINTS.ROLES_BY_DEPARTAMENTO, 
                { id_departamento: idDepartamento }
            );
            
            const response = await this.apiClient.get(endpoint);
            
            if (response.success) {
                return {
                    success: true,
                    data: response.data || [],
                    count: response.count || 0,
                    message: 'Roles del departamento obtenidos exitosamente'
                };
            } else {
                return {
                    success: false,
                    message: response.message || 'Error al obtener roles del departamento',
                    data: []
                };
            }
        } catch (error) {
            console.error('Error obteniendo roles por departamento:', error);
            return {
                success: false,
                message: 'Error de conexión al obtener roles',
                data: []
            };
        }
    }

    /**
     * Obtener roles para select/dropdown
     */
    async getForSelect(idDepartamento = null) {
        try {
            let response;
            
            if (idDepartamento) {
                response = await this.getByDepartamento(idDepartamento);
            } else {
                response = await this.getAll();
            }
            
            if (response.success) {
                const options = response.data.map(rol => ({
                    value: rol.id_rol,
                    label: rol.nombre,
                    departamento: rol.departamento_nombre,
                    data: rol
                }));
                
                return {
                    success: true,
                    data: options
                };
            } else {
                return response;
            }
        } catch (error) {
            console.error('Error obteniendo roles para select:', error);
            return {
                success: false,
                message: 'Error al obtener roles',
                data: []
            };
        }
    }
}

// Crear instancias globales de los servicios
window.departamentosService = new DepartamentosService();
window.rolesService = new RolesService();

console.log('✅ DepartamentosService y RolesService inicializados correctamente');
