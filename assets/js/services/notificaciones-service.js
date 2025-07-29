/**
 * Servicio de Notificaciones para Figger Energy SAS
 * Maneja todas las operaciones relacionadas con notificaciones
 */

class NotificacionesService {
    constructor() {
        this.apiClient = window.apiClient;
        this.config = window.API_CONFIG;
        this.notifications = [];
        this.unreadCount = 0;
    }

    /**
     * Obtener notificaciones de un usuario
     */
    async getByUser(userId, filters = {}) {
        try {
            const endpoint = this.apiClient.replaceUrlParams(
                this.config.ENDPOINTS.NOTIFICACIONES_USUARIO, 
                { id_usuario: userId }
            );

            const params = {};
            if (filters.leida !== undefined) params.leida = filters.leida;
            if (filters.tipo) params.tipo = filters.tipo;

            const response = await this.apiClient.get(endpoint, params);
            
            if (response.success) {
                this.notifications = response.data || [];
                this.updateUnreadCount();
                
                return {
                    success: true,
                    data: this.notifications,
                    count: response.count || 0,
                    message: 'Notificaciones obtenidas exitosamente'
                };
            } else {
                return {
                    success: false,
                    message: response.message || 'Error al obtener notificaciones',
                    data: []
                };
            }
        } catch (error) {
            console.error('Error obteniendo notificaciones:', error);
            return {
                success: false,
                message: 'Error de conexión al obtener notificaciones',
                data: []
            };
        }
    }

    /**
     * Marcar notificación como leída
     */
    async markAsRead(notificationId) {
        try {
            const endpoint = this.apiClient.replaceUrlParams(
                this.config.ENDPOINTS.NOTIFICACION_MARCAR_LEIDA, 
                { id: notificationId }
            );

            const response = await this.apiClient.put(endpoint);
            
            if (response.success) {
                // Actualizar notificación local
                const notification = this.notifications.find(n => n.id_notificacion === notificationId);
                if (notification) {
                    notification.leida = true;
                    this.updateUnreadCount();
                }
                
                return {
                    success: true,
                    message: 'Notificación marcada como leída'
                };
            } else {
                return {
                    success: false,
                    message: response.message || 'Error al marcar notificación como leída'
                };
            }
        } catch (error) {
            console.error('Error marcando notificación como leída:', error);
            return {
                success: false,
                message: 'Error de conexión al marcar notificación'
            };
        }
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    async markAllAsRead(userId) {
        try {
            const unreadNotifications = this.notifications.filter(n => !n.leida);
            const promises = unreadNotifications.map(n => this.markAsRead(n.id_notificacion));
            
            const results = await Promise.all(promises);
            const successCount = results.filter(r => r.success).length;
            
            return {
                success: successCount === unreadNotifications.length,
                message: `${successCount} de ${unreadNotifications.length} notificaciones marcadas como leídas`
            };
        } catch (error) {
            console.error('Error marcando todas las notificaciones como leídas:', error);
            return {
                success: false,
                message: 'Error al marcar todas las notificaciones como leídas'
            };
        }
    }

    /**
     * Obtener solo notificaciones no leídas
     */
    async getUnread(userId) {
        const response = await this.getByUser(userId, { leida: false });
        return response;
    }

    /**
     * Obtener conteo de notificaciones no leídas
     */
    getUnreadCount() {
        return this.unreadCount;
    }

    /**
     * Actualizar conteo de notificaciones no leídas
     */
    updateUnreadCount() {
        this.unreadCount = this.notifications.filter(n => !n.leida).length;
        
        // Disparar evento para actualizar UI
        window.dispatchEvent(new CustomEvent('notificationsUpdated', {
            detail: { 
                count: this.unreadCount,
                notifications: this.notifications 
            }
        }));
    }

    /**
     * Formatear notificaciones para mostrar
     */
    formatNotifications(notifications) {
        return notifications.map(notification => ({
            ...notification,
            fecha_formateada: this.formatDate(notification.fecha_creacion),
            tiempo_relativo: this.getRelativeTime(notification.fecha_creacion),
            icono: this.getNotificationIcon(notification.tipo),
            color: this.getNotificationColor(notification.tipo)
        }));
    }

    /**
     * Obtener icono según tipo de notificación
     */
    getNotificationIcon(tipo) {
        const iconos = {
            'info': '📢',
            'warning': '⚠️',
            'error': '❌',
            'success': '✅',
            'sistema': '⚙️',
            'usuario': '👤',
            'reporte': '📊'
        };
        return iconos[tipo] || '📢';
    }

    /**
     * Obtener color según tipo de notificación
     */
    getNotificationColor(tipo) {
        const colores = {
            'info': 'blue',
            'warning': 'orange',
            'error': 'red',
            'success': 'green',
            'sistema': 'gray',
            'usuario': 'purple',
            'reporte': 'indigo'
        };
        return colores[tipo] || 'blue';
    }

    /**
     * Formatear fecha
     */
    formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('es-CO', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    /**
     * Obtener tiempo relativo (ej: "hace 2 horas")
     */
    getRelativeTime(dateString) {
        if (!dateString) return '';
        
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now - date;
        const diffMinutes = Math.floor(diffMs / (1000 * 60));
        const diffHours = Math.floor(diffMinutes / 60);
        const diffDays = Math.floor(diffHours / 24);

        if (diffMinutes < 1) {
            return 'Ahora mismo';
        } else if (diffMinutes < 60) {
            return `Hace ${diffMinutes} minuto${diffMinutes !== 1 ? 's' : ''}`;
        } else if (diffHours < 24) {
            return `Hace ${diffHours} hora${diffHours !== 1 ? 's' : ''}`;
        } else if (diffDays < 7) {
            return `Hace ${diffDays} día${diffDays !== 1 ? 's' : ''}`;
        } else {
            return this.formatDate(dateString);
        }
    }

    /**
     * Crear notificación local (para testing)
     */
    createLocalNotification(notificationData) {
        const notification = {
            id_notificacion: Date.now(),
            titulo: notificationData.titulo || 'Notificación',
            mensaje: notificationData.mensaje || '',
            tipo: notificationData.tipo || 'info',
            categoria: notificationData.categoria || 'sistema',
            leida: false,
            fecha_creacion: new Date().toISOString(),
            url_accion: notificationData.url_accion || null
        };

        this.notifications.unshift(notification);
        this.updateUnreadCount();

        return notification;
    }

    /**
     * Eliminar notificación local
     */
    removeLocalNotification(notificationId) {
        const index = this.notifications.findIndex(n => n.id_notificacion === notificationId);
        if (index > -1) {
            this.notifications.splice(index, 1);
            this.updateUnreadCount();
            return true;
        }
        return false;
    }

    /**
     * Simular notificaciones de prueba
     */
    createTestNotifications() {
        const testNotifications = [
            {
                titulo: 'Bienvenido al Sistema',
                mensaje: 'Has iniciado sesión exitosamente en Figger Energy SAS',
                tipo: 'success',
                categoria: 'sistema'
            },
            {
                titulo: 'Nuevo Reporte Disponible',
                mensaje: 'El reporte mensual de actividades está listo para revisión',
                tipo: 'info',
                categoria: 'reporte',
                url_accion: '/reports.html'
            },
            {
                titulo: 'Actualización del Sistema',
                mensaje: 'El sistema se actualizará mañana a las 2:00 AM',
                tipo: 'warning',
                categoria: 'sistema'
            }
        ];

        testNotifications.forEach(notification => {
            this.createLocalNotification(notification);
        });

        return {
            success: true,
            message: `${testNotifications.length} notificaciones de prueba creadas`,
            count: testNotifications.length
        };
    }

    /**
     * Limpiar todas las notificaciones locales
     */
    clearAllNotifications() {
        this.notifications = [];
        this.updateUnreadCount();
        
        return {
            success: true,
            message: 'Todas las notificaciones han sido eliminadas'
        };
    }

    /**
     * Inicializar notificaciones para un usuario
     */
    async initializeForUser(userId) {
        try {
            const response = await this.getByUser(userId);
            
            // Si no hay notificaciones de la API, crear algunas de prueba
            if (response.success && response.data.length === 0) {
                this.createTestNotifications();
            }
            
            return response;
        } catch (error) {
            console.error('Error inicializando notificaciones:', error);
            // Crear notificaciones de prueba si hay error
            return this.createTestNotifications();
        }
    }
}

// Crear instancia global del servicio
window.notificacionesService = new NotificacionesService();

console.log('✅ NotificacionesService inicializado correctamente');
