/**
 * Servicio de Autenticación para Figger Energy SAS
 * Maneja login, logout, registro y gestión de sesiones
 */

class AuthService {
    constructor() {
        this.config = window.API_CONFIG;
        this.apiClient = window.apiClient;
        this.currentUser = null;
        this.isAuthenticated = false;
        
        // Verificar sesión existente al inicializar
        this.checkExistingSession();
    }

    /**
     * Verificar si hay una sesión activa
     */
    checkExistingSession() {
        const token = localStorage.getItem(this.config.AUTH.TOKEN_KEY);
        const userData = localStorage.getItem(this.config.AUTH.USER_KEY);
        const sessionTimestamp = localStorage.getItem('figger_session_timestamp');
        
        if (token && userData) {
            try {
                // Verificar si la sesión no ha expirado
                if (sessionTimestamp) {
                    const sessionAge = Date.now() - parseInt(sessionTimestamp);
                    if (sessionAge > this.config.AUTH.SESSION_DURATION) {
                        console.log('Sesión expirada');
                        this.clearSession();
                        return;
                    }
                }
                
                this.currentUser = JSON.parse(userData);
                this.isAuthenticated = true;
                console.log('Sesión existente encontrada para:', this.currentUser.username);
                
                // Actualizar timestamp de sesión activa
                localStorage.setItem('figger_session_timestamp', Date.now().toString());
                
            } catch (error) {
                console.error('Error al cargar sesión existente:', error);
                this.clearSession();
            }
        }
    }

    /**
     * Validar si la sesión actual sigue siendo válida
     */
    async validateSession() {
        if (!this.isAuthenticated) return false;

        try {
            // Intentar hacer una petición que requiera autenticación
            const response = await this.apiClient.get(this.config.ENDPOINTS.USUARIOS);
            
            if (response.success) {
                return true;
            } else {
                console.warn('Sesión inválida, limpiando datos');
                this.clearSession();
                return false;
            }
        } catch (error) {
            console.error('Error validando sesión:', error);
            this.clearSession();
            return false;
        }
    }

    /**
     * Realizar login
     */
    async login(credentials) {
        try {
            console.log('Intentando login para:', credentials.username);

            // Como no hay endpoint específico de login, simularemos el proceso
            // En una implementación real, habría un endpoint /api/auth/login/
            const response = await this.simulateLogin(credentials);

            if (response.success) {
                await this.handleSuccessfulLogin(response.data);
                return {
                    success: true,
                    message: 'Login exitoso',
                    user: this.currentUser
                };
            } else {
                return {
                    success: false,
                    message: response.message || 'Credenciales inválidas'
                };
            }
        } catch (error) {
            console.error('Error en login:', error);
            return {
                success: false,
                message: 'Error de conexión. Intente nuevamente.'
            };
        }
    }

    /**
     * Simular login (mientras no hay endpoint específico)
     * En la implementación real, esto sería una petición al backend
     */
    async simulateLogin(credentials) {
        // Simular validación básica
        if (!credentials.username || !credentials.password) {
            return {
                success: false,
                message: 'Usuario y contraseña son requeridos'
            };
        }

        // Simular delay de red
        await new Promise(resolve => setTimeout(resolve, 1000));

        // Por ahora, simular usuarios predefinidos basados en la documentación
        const validUsers = [
            {
                id_usuario: 1,
                username: 'admin',
                password: 'admin123',
                empleado: {
                    id_empleado: 1,
                    nombre_completo: 'Administrador Sistema',
                    email: 'admin@figgerenergy.gov.co',
                    departamento_nombre: 'Administración',
                    rol_nombre: 'CEO - Gerente General'
                },
                role: 'admin'
            },
            {
                id_usuario: 2,
                username: 'empleado',
                password: 'emp123',
                empleado: {
                    id_empleado: 2,
                    nombre_completo: 'Juan Pérez García',
                    email: 'juan.perez@figgerenergy.gov.co',
                    departamento_nombre: 'IT',
                    rol_nombre: 'Desarrollador'
                },
                role: 'empleado'
            },
            {
                id_usuario: 3,
                username: 'auditor',
                password: 'aud123',
                empleado: {
                    id_empleado: 3,
                    nombre_completo: 'María González López',
                    email: 'maria.gonzalez@figgerenergy.gov.co',
                    departamento_nombre: 'Administración',
                    rol_nombre: 'Auditor Senior'
                },
                role: 'auditor'
            }
        ];

        const user = validUsers.find(u => 
            u.username === credentials.username && 
            u.password === credentials.password
        );

        if (user) {
            // Simular token JWT
            const token = btoa(JSON.stringify({
                user_id: user.id_usuario,
                username: user.username,
                role: user.role,
                exp: Date.now() + this.config.AUTH.SESSION_DURATION
            }));

            return {
                success: true,
                data: {
                    token: token,
                    user: user
                }
            };
        } else {
            return {
                success: false,
                message: 'Credenciales incorrectas'
            };
        }
    }

    /**
     * Manejar login exitoso
     */
    async handleSuccessfulLogin(loginData) {
        // Guardar token
        localStorage.setItem(this.config.AUTH.TOKEN_KEY, loginData.token);
        
        // Guardar datos del usuario
        localStorage.setItem(this.config.AUTH.USER_KEY, JSON.stringify(loginData.user));
        
        // Guardar timestamp de la sesión
        localStorage.setItem('figger_session_timestamp', Date.now().toString());
        
        // Actualizar estado interno
        this.currentUser = loginData.user;
        this.isAuthenticated = true;
        
        console.log('Login exitoso para:', this.currentUser.username);
        console.log('Sesión iniciada en:', new Date().toLocaleString());
        
        // Disparar evento personalizado para que otros componentes reaccionen
        window.dispatchEvent(new CustomEvent('userLoggedIn', {
            detail: { user: this.currentUser }
        }));
    }

    /**
     * Realizar logout
     */
    logout() {
        console.log('Cerrando sesión para:', this.currentUser?.username);
        
        // Limpiar sesión
        this.clearSession();
        
        // Disparar evento
        window.dispatchEvent(new CustomEvent('userLoggedOut'));
        
        // Redirigir inteligentemente
        this.handleLogoutRedirect();
    }

    /**
     * Manejar redirección después del logout
     */
    handleLogoutRedirect() {
        const currentPath = window.location.pathname;
        const filename = currentPath.split('/').pop() || 'index.html';
        
        // Lista de páginas protegidas que requieren redirección
        const protectedPages = ['dashboard'];
        const isInProtectedArea = protectedPages.some(page => currentPath.includes(page));
        
        if (isInProtectedArea) {
            // Si estamos en área protegida, redirigir a login
            if (currentPath.includes('/dashboard/')) {
                window.location.href = '../login.html';
            } else {
                window.location.href = 'login.html';
            }
        } else {
            // Si estamos en página pública, solo recargar para actualizar la UI
            window.location.reload();
        }
    }

    /**
     * Limpiar datos de sesión
     */
    clearSession() {
        localStorage.removeItem(this.config.AUTH.TOKEN_KEY);
        localStorage.removeItem(this.config.AUTH.USER_KEY);
        localStorage.removeItem('figger_session_timestamp');
        this.currentUser = null;
        this.isAuthenticated = false;
        console.log('Sesión limpiada completamente');
    }

    /**
     * Obtener usuario actual
     */
    getCurrentUser() {
        return this.currentUser;
    }

    /**
     * Verificar si el usuario está autenticado
     */
    isUserAuthenticated() {
        return this.isAuthenticated && this.currentUser !== null;
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    hasRole(role) {
        if (!this.isAuthenticated || !this.currentUser) return false;
        return this.currentUser.role === role;
    }

    /**
     * Verificar si el usuario tiene permisos para una acción
     */
    hasPermission(permission) {
        if (!this.isAuthenticated || !this.currentUser) return false;
        
        // Definir permisos por rol
        const rolePermissions = {
            admin: ['read', 'write', 'delete', 'manage_users', 'manage_departments', 'manage_roles', 'view_reports', 'manage_notifications'],
            empleado: ['read', 'write', 'view_reports'],
            auditor: ['read', 'view_reports', 'audit']
        };
        
        const userPermissions = rolePermissions[this.currentUser.role] || [];
        return userPermissions.includes(permission);
    }

    /**
     * Obtener información de permisos del usuario actual
     */
    getUserPermissions() {
        if (!this.isAuthenticated) return [];
        
        const rolePermissions = {
            admin: {
                dashboard: true,
                usuarios: true,
                departamentos: true,
                empleados: true,
                roles: true,
                reportes: true,
                estadisticas: true,
                notificaciones: true,
                configuracion: true
            },
            empleado: {
                dashboard: true,
                empleados: false,
                departamentos: false,
                roles: false,
                reportes: true,
                estadisticas: true,
                notificaciones: true,
                configuracion: false
            },
            auditor: {
                dashboard: true,
                usuarios: false,
                departamentos: false,
                empleados: true,
                roles: false,
                reportes: true,
                estadisticas: true,
                notificaciones: true,
                configuracion: false
            }
        };
        
        return rolePermissions[this.currentUser?.role] || {};
    }

    /**
     * Registro de nuevo usuario (para implementación futura)
     */
    async register(userData) {
        try {
            const response = await this.apiClient.post(this.config.ENDPOINTS.USUARIOS, userData);
            
            if (response.success) {
                return {
                    success: true,
                    message: 'Usuario registrado exitosamente',
                    data: response.data
                };
            } else {
                return {
                    success: false,
                    message: response.message || 'Error al registrar usuario'
                };
            }
        } catch (error) {
            console.error('Error en registro:', error);
            return {
                success: false,
                message: 'Error de conexión. Intente nuevamente.'
            };
        }
    }

    /**
     * Cambiar contraseña (para implementación futura)
     */
    async changePassword(passwordData) {
        if (!this.isAuthenticated) {
            return {
                success: false,
                message: 'Debe estar autenticado para cambiar la contraseña'
            };
        }

        try {
            const endpoint = this.apiClient.replaceUrlParams(
                this.config.ENDPOINTS.USUARIO_BY_ID, 
                { id: this.currentUser.id_usuario }
            );
            
            const response = await this.apiClient.put(endpoint, passwordData);
            
            return {
                success: response.success,
                message: response.success ? 'Contraseña actualizada exitosamente' : response.message
            };
        } catch (error) {
            console.error('Error al cambiar contraseña:', error);
            return {
                success: false,
                message: 'Error de conexión. Intente nuevamente.'
            };
        }
    }
}

// Crear instancia global del servicio de autenticación
window.authService = new AuthService();

console.log('✅ AuthService inicializado correctamente');
