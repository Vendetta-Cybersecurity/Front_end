/**
 * Middleware de Autenticación para Figger Energy SAS
 * Protege páginas y controla el acceso según roles y permisos
 */

class AuthMiddleware {
    constructor() {
        this.authService = null; // Se inicializará cuando esté disponible
        this.protectedPages = [
            'dashboard/index.html',
            'dashboard/empleados.html',
            'dashboard/departamentos.html',
            'dashboard/usuarios.html',
            'dashboard/reportes.html',
            'dashboard/configuracion.html'
        ];
        
        this.publicPages = [
            'index.html',
            'about.html',
            'services.html',
            'reports.html',
            'contact.html',
            'login.html',
            'privacy.html',
            'terms.html',
            'accessibility.html'
        ];
        
        this.init();
    }

    /**
     * Inicializar middleware con verificación de dependencias
     */
    init() {
        // Esperar a que el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.waitForAuthService());
        } else {
            this.waitForAuthService();
        }

        // Escuchar eventos de login/logout
        window.addEventListener('userLoggedIn', () => {
            this.handleUserLogin();
        });

        window.addEventListener('userLoggedOut', () => {
            this.handleUserLogout();
        });
    }

    /**
     * Esperar a que el AuthService esté disponible
     */
    waitForAuthService() {
        if (window.authService) {
            this.authService = window.authService;
            this.handlePageAccess();
        } else {
            // Reintentar después de un breve retraso
            setTimeout(() => this.waitForAuthService(), 100);
        }
    }

    /**
     * Manejar acceso a la página según su tipo
     */
    handlePageAccess() {
        const currentPage = this.getCurrentPage();
        const isProtected = this.isProtectedPage(currentPage);
        
        console.log(`Página actual: ${currentPage}, Es protegida: ${isProtected}`);
        
        if (isProtected) {
            // Solo ejecutar verificación completa en páginas protegidas
            this.checkPageAccess();
            this.initializePageBasedOnAuth();
        } else if (currentPage === 'login.html') {
            // Solo redirigir desde login si ya está autenticado
            this.checkLoginPageAccess();
        }
        // Para páginas públicas, no hacer nada automáticamente
    }

    /**
     * Verificar acceso a la página actual
     */
    checkPageAccess() {
        const currentPage = this.getCurrentPage();
        const isProtected = this.isProtectedPage(currentPage);
        const isAuthenticated = this.authService?.isUserAuthenticated() || false;

        console.log(`Página actual: ${currentPage}, Protegida: ${isProtected}, Autenticado: ${isAuthenticated}`);

        if (isProtected && !isAuthenticated) {
            this.redirectToLogin();
            return false;
        }

        if (currentPage === 'login.html' && isAuthenticated) {
            this.redirectToDashboard();
            return false;
        }

        return true;
    }

    /**
     * Obtener página actual
     */
    getCurrentPage() {
        const path = window.location.pathname;
        const filename = path.split('/').pop() || 'index.html';
        
        // Si estamos en una carpeta dashboard
        if (path.includes('/dashboard/')) {
            return `dashboard/${filename}`;
        }
        
        return filename;
    }

    /**
     * Verificar si una página está protegida
     */
    isProtectedPage(page) {
        return this.protectedPages.some(protectedPage => 
            page.includes(protectedPage) || protectedPage.includes(page)
        );
    }

    /**
     * Verificar acceso específico para página de login
     */
    checkLoginPageAccess() {
        const isAuthenticated = this.authService?.isUserAuthenticated() || false;
        
        if (isAuthenticated) {
            console.log('Usuario ya autenticado en página de login, redirigiendo al dashboard');
            this.redirectToDashboard();
            return false;
        }
        
        return true;
    }

    /**
     * Redirigir a login
     */
    redirectToLogin() {
        console.log('Redirigiendo a login - acceso no autorizado');
        
        // Mostrar mensaje temporal
        this.showMessage('Debe iniciar sesión para acceder a esta página', 'warning');
        
        // Redirigir después de un breve delay
        setTimeout(() => {
            window.location.href = this.getLoginUrl();
        }, 1500);
    }

    /**
     * Redirigir al dashboard
     */
    redirectToDashboard() {
        console.log('Usuario ya autenticado, redirigiendo al dashboard');
        
        setTimeout(() => {
            window.location.href = this.getDashboardUrl();
        }, 1000);
    }

    /**
     * Obtener URL de login
     */
    getLoginUrl() {
        const currentPath = window.location.pathname;
        if (currentPath.includes('/dashboard/')) {
            return '../login.html';
        }
        return 'login.html';
    }

    /**
     * Obtener URL del dashboard
     */
    getDashboardUrl() {
        const currentPath = window.location.pathname;
        if (currentPath.includes('/dashboard/')) {
            return 'index.html';
        }
        return 'dashboard/index.html';
    }

    /**
     * Inicializar página según estado de autenticación
     */
    initializePageBasedOnAuth() {
        const isAuthenticated = this.authService?.isUserAuthenticated() || false;
        
        if (isAuthenticated) {
            this.initializeAuthenticatedPage();
        } else {
            this.initializePublicPage();
        }
    }

    /**
     * Inicializar página para usuario autenticado
     */
    initializeAuthenticatedPage() {
        const user = this.authService.getCurrentUser();
        console.log('Inicializando página para usuario autenticado:', user?.username);

        // Actualizar navegación para mostrar opciones de usuario autenticado
        this.updateNavigationForAuth(user);
        
        // Inicializar servicios que requieren autenticación
        this.initializeAuthenticatedServices(user);
        
        // Mostrar información del usuario si hay un contenedor apropiado
        this.displayUserInfo(user);
    }

    /**
     * Inicializar página pública
     */
    initializePublicPage() {
        console.log('Inicializando página pública');

        // Asegurar que no se muestren elementos de usuario autenticado
        this.hideAuthenticatedElements();
    }

    /**
     * Actualizar navegación para usuario autenticado
     */
    updateNavigationForAuth(user) {
        // Buscar botón de login en la navegación
        const loginBtn = document.querySelector('a[href*="login.html"]');
        if (loginBtn) {
            // Cambiar texto y enlace
            loginBtn.textContent = `Dashboard (${user.empleado?.nombre_completo || user.username})`;
            loginBtn.href = this.getDashboardUrl();
            loginBtn.classList.add('user-authenticated');
        }

        // Agregar botón de logout si no existe
        const nav = document.querySelector('.navegacion-principal ul');
        if (nav && !document.getElementById('logout-btn')) {
            const logoutLi = document.createElement('li');
            logoutLi.innerHTML = `
                <button id="logout-btn" class="btn-logout" onclick="window.authMiddleware.logout()">
                    Cerrar Sesión
                </button>
            `;
            nav.appendChild(logoutLi);
        }
    }

    /**
     * Inicializar servicios que requieren autenticación
     */
    initializeAuthenticatedServices(user) {
        // Inicializar notificaciones
        if (window.notificacionesService && user) {
            window.notificacionesService.initializeForUser(user.id_usuario);
        }

        // Otras inicializaciones específicas para usuarios autenticados
        this.initializeNotificationDisplay();
    }

    /**
     * Mostrar información del usuario
     */
    displayUserInfo(user) {
        const userInfoContainer = document.getElementById('user-info');
        if (userInfoContainer && user) {
            userInfoContainer.innerHTML = `
                <div class="user-info-card">
                    <div class="user-avatar">
                        ${user.empleado?.foto_perfil ? 
                            `<img src="${user.empleado.foto_perfil}" alt="Foto de perfil">` : 
                            `<div class="avatar-placeholder">${user.empleado?.nombres?.charAt(0) || user.username.charAt(0)}</div>`
                        }
                    </div>
                    <div class="user-details">
                        <h4>${user.empleado?.nombre_completo || user.username}</h4>
                        <p class="user-role">${user.empleado?.rol_nombre || 'Usuario'}</p>
                        <p class="user-department">${user.empleado?.departamento_nombre || ''}</p>
                        <p class="user-email">${user.empleado?.email || ''}</p>
                    </div>
                </div>
            `;
        }
    }

    /**
     * Ocultar elementos de usuario autenticado
     */
    hideAuthenticatedElements() {
        const authenticatedElements = document.querySelectorAll('.authenticated-only');
        authenticatedElements.forEach(element => {
            element.style.display = 'none';
        });

        const logoutBtn = document.getElementById('logout-btn');
        if (logoutBtn) {
            logoutBtn.parentElement.remove();
        }
    }

    /**
     * Inicializar display de notificaciones
     */
    initializeNotificationDisplay() {
        // Crear icono de notificaciones si no existe
        const nav = document.querySelector('.navegacion-principal ul');
        if (nav && !document.getElementById('notifications-icon')) {
            const notificationsLi = document.createElement('li');
            notificationsLi.innerHTML = `
                <button id="notifications-icon" class="notifications-btn" onclick="window.authMiddleware.toggleNotifications()">
                    🔔 <span id="notification-count" class="notification-count">0</span>
                </button>
                <div id="notifications-dropdown" class="notifications-dropdown" style="display: none;">
                    <div class="notifications-header">
                        <h4>Notificaciones</h4>
                        <button onclick="window.authMiddleware.markAllNotificationsRead()">Marcar todas leídas</button>
                    </div>
                    <div id="notifications-list" class="notifications-list">
                        <p>Cargando notificaciones...</p>
                    </div>
                </div>
            `;
            
            // Insertar antes del botón de logout
            const logoutBtn = document.getElementById('logout-btn');
            if (logoutBtn) {
                nav.insertBefore(notificationsLi, logoutBtn.parentElement);
            } else {
                nav.appendChild(notificationsLi);
            }
        }

        // Escuchar actualizaciones de notificaciones
        window.addEventListener('notificationsUpdated', (event) => {
            this.updateNotificationDisplay(event.detail);
        });
    }

    /**
     * Actualizar display de notificaciones
     */
    updateNotificationDisplay(notificationData) {
        const countElement = document.getElementById('notification-count');
        if (countElement) {
            countElement.textContent = notificationData.count;
            countElement.style.display = notificationData.count > 0 ? 'inline' : 'none';
        }

        const listElement = document.getElementById('notifications-list');
        if (listElement && notificationData.notifications) {
            const formattedNotifications = window.notificacionesService.formatNotifications(
                notificationData.notifications.slice(0, 5) // Mostrar solo las 5 más recientes
            );

            if (formattedNotifications.length === 0) {
                listElement.innerHTML = '<p class="no-notifications">No hay notificaciones</p>';
            } else {
                listElement.innerHTML = formattedNotifications.map(notification => `
                    <div class="notification-item ${notification.leida ? 'read' : 'unread'}" data-id="${notification.id_notificacion}">
                        <div class="notification-icon">${notification.icono}</div>
                        <div class="notification-content">
                            <h5>${notification.titulo}</h5>
                            <p>${notification.mensaje}</p>
                            <small>${notification.tiempo_relativo}</small>
                        </div>
                        ${!notification.leida ? '<button class="mark-read-btn" onclick="window.authMiddleware.markNotificationRead(' + notification.id_notificacion + ')">✓</button>' : ''}
                    </div>
                `).join('');
            }
        }
    }

    /**
     * Toggle dropdown de notificaciones
     */
    toggleNotifications() {
        const dropdown = document.getElementById('notifications-dropdown');
        if (dropdown) {
            const isVisible = dropdown.style.display !== 'none';
            dropdown.style.display = isVisible ? 'none' : 'block';
        }
    }

    /**
     * Marcar notificación como leída
     */
    async markNotificationRead(notificationId) {
        if (window.notificacionesService) {
            const result = await window.notificacionesService.markAsRead(notificationId);
            if (result.success) {
                this.showMessage('Notificación marcada como leída', 'success');
            }
        }
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    async markAllNotificationsRead() {
        const user = this.authService?.getCurrentUser();
        if (window.notificacionesService && user) {
            const result = await window.notificacionesService.markAllAsRead(user.id_usuario);
            if (result.success) {
                this.showMessage(result.message, 'success');
                this.toggleNotifications(); // Cerrar dropdown
            }
        }
    }

    /**
     * Manejar login exitoso
     */
    handleUserLogin() {
        console.log('Usuario ha iniciado sesión, actualizando interfaz');
        
        // Recargar página si estamos en una página que necesita actualización
        const currentPage = this.getCurrentPage();
        if (currentPage === 'login.html') {
            this.redirectToDashboard();
        } else {
            // Actualizar la página actual
            this.initializeAuthenticatedPage();
        }
    }

    /**
     * Manejar logout
     */
    handleUserLogout() {
        console.log('Usuario ha cerrado sesión, actualizando interfaz');
        this.hideAuthenticatedElements();
    }

    /**
     * Logout manual
     */
    logout() {
        if (this.authService) {
            this.authService.logout();
        }
    }

    /**
     * Verificar permisos para una acción
     */
    checkPermission(permission) {
        if (!this.authService?.isUserAuthenticated()) {
            return false;
        }
        
        return this.authService.hasPermission(permission);
    }

    /**
     * Verificar rol del usuario
     */
    checkRole(role) {
        if (!this.authService?.isUserAuthenticated()) {
            return false;
        }
        
        return this.authService.hasRole(role);
    }

    /**
     * Mostrar mensaje temporal
     */
    showMessage(message, type = 'info') {
        // Crear elemento de mensaje si no existe
        let messageContainer = document.getElementById('temp-message');
        if (!messageContainer) {
            messageContainer = document.createElement('div');
            messageContainer.id = 'temp-message';
            messageContainer.className = 'temp-message';
            document.body.appendChild(messageContainer);
        }

        // Configurar mensaje
        messageContainer.textContent = message;
        messageContainer.className = `temp-message temp-message-${type} show`;

        // Ocultar después de 3 segundos
        setTimeout(() => {
            messageContainer.classList.remove('show');
        }, 3000);
    }

    /**
     * Proteger elemento basado en permisos
     */
    protectElement(element, permission) {
        if (!this.checkPermission(permission)) {
            element.style.display = 'none';
            return false;
        }
        return true;
    }

    /**
     * Proteger elemento basado en rol
     */
    protectElementByRole(element, role) {
        if (!this.checkRole(role)) {
            element.style.display = 'none';
            return false;
        }
        return true;
    }
}

// Crear instancia global del middleware
window.authMiddleware = new AuthMiddleware();

console.log('✅ AuthMiddleware inicializado correctamente');
