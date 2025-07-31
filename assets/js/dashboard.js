/**
 * Figger Energy SAS - Dashboard Functionality
 * Funcionalidades específicas para dashboards
 */

/**
 * Gestor principal de Dashboard
 */
class DashboardManager {
    constructor() {
        this.currentUser = null;
        this.refreshInterval = 5 * 60 * 1000; // 5 minutos
        this.autoRefreshTimer = null;
        this.widgets = new Map();
        this.notifications = [];
        
        this.init();
    }

    /**
     * Inicializar dashboard
     */
    async init() {
        try {
            // Verificar autenticación
            if (!authManager.protectPage()) {
                return;
            }
            
            this.currentUser = authManager.getCurrentUser();
            
            // Configurar UI
            this.setupUI();
            
            // Cargar datos iniciales
            await this.loadInitialData();
            
            // Configurar auto-refresh
            this.setupAutoRefresh();
            
            // Configurar eventos
            this.bindEvents();
            
            showToast(`Bienvenido, ${this.currentUser.fullName}`, 'success', 3000);
            
        } catch (error) {
            console.error('Error initializing dashboard:', error);
            showToast('Error al cargar el dashboard', 'error');
        }
    }

    /**
     * Configurar interfaz de usuario
     */
    setupUI() {
        // Actualizar información del usuario en header
        this.updateUserInfo();
        
        // Configurar navegación según rol
        this.setupNavigation();
        
        // Inicializar sidebar
        this.setupSidebar();
        
        // Configurar notificaciones
        this.setupNotifications();
    }

    /**
     * Actualizar información del usuario
     */
    updateUserInfo() {
        const userNameElements = document.querySelectorAll('.user-name');
        const userRoleElements = document.querySelectorAll('.user-role');
        const userAvatarElements = document.querySelectorAll('.user-avatar');
        
        userNameElements.forEach(el => {
            el.textContent = this.currentUser.fullName;
        });
        
        userRoleElements.forEach(el => {
            el.textContent = authManager.getCurrentRole()?.name || this.currentUser.role;
        });
        
        userAvatarElements.forEach(el => {
            el.textContent = FormatUtils.getInitials(this.currentUser.fullName);
        });
    }

    /**
     * Configurar navegación según permisos
     */
    setupNavigation() {
        const navLinks = document.querySelectorAll('[data-permission]');
        
        navLinks.forEach(link => {
            const permission = link.dataset.permission;
            if (!authManager.hasPermission(permission)) {
                link.style.display = 'none';
            }
        });
    }

    /**
     * Configurar sidebar
     */
    setupSidebar() {
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        const sidebar = document.querySelector('.dashboard-sidebar');
        
        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('open');
            });
        }
        
        // Marcar enlace activo
        const currentPath = window.location.pathname;
        const menuLinks = document.querySelectorAll('.menu-link');
        
        menuLinks.forEach(link => {
            if (link.getAttribute('href') === currentPath.split('/').pop()) {
                link.classList.add('active');
            }
        });
    }

    /**
     * Configurar sistema de notificaciones
     */
    async setupNotifications() {
        if (authManager.hasPermission('read')) {
            try {
                await this.loadNotifications();
                this.updateNotificationBadge();
            } catch (error) {
                console.error('Error loading notifications:', error);
            }
        }
    }

    /**
     * Cargar datos iniciales
     */
    async loadInitialData() {
        const loadingId = showLoading('Cargando datos del dashboard...');
        
        try {
            // Cargar estadísticas generales
            await this.loadStatistics();
            
            // Cargar datos específicos según rol
            if (authManager.hasRole('admin')) {
                await this.loadAdminData();
            } else if (authManager.hasRole('empleado')) {
                await this.loadEmployeeData();
            } else if (authManager.hasRole('auditor')) {
                await this.loadAuditorData();
            }
            
        } catch (error) {
            console.error('Error loading initial data:', error);
            showToast('Error al cargar algunos datos', 'warning');
        } finally {
            hideLoading(loadingId);
        }
    }

    /**
     * Cargar estadísticas generales
     */
    async loadStatistics() {
        try {
            const stats = await apiClient.getEstadisticasGenerales();
            this.updateStatisticsDisplay(stats);
        } catch (error) {
            console.error('Error loading statistics:', error);
        }
    }

    /**
     * Actualizar visualización de estadísticas
     */
    updateStatisticsDisplay(stats) {
        // Actualizar tarjetas de estadísticas
        const statElements = {
            'total-employees': stats.total_empleados,
            'active-employees': stats.empleados_activos,
            'total-departments': stats.total_departamentos,
            'total-notifications': stats.notificaciones_no_leidas
        };
        
        Object.keys(statElements).forEach(key => {
            const element = document.getElementById(key);
            if (element) {
                const valueElement = element.querySelector('.stat-value');
                if (valueElement) {
                    valueElement.textContent = FormatUtils.formatNumber(statElements[key]);
                }
            }
        });
        
        // Actualizar gráficos si existen
        this.updateCharts(stats);
    }

    /**
     * Cargar datos específicos para administrador
     */
    async loadAdminData() {
        try {
            const [empleados, departamentos] = await Promise.all([
                apiClient.getEmpleados(),
                apiClient.getDepartamentos()
            ]);
            
            this.renderEmployeeTable(empleados);
            this.renderDepartmentSummary(departamentos);
            
        } catch (error) {
            console.error('Error loading admin data:', error);
        }
    }

    /**
     * Cargar datos específicos para empleado
     */
    async loadEmployeeData() {
        try {
            // Cargar empleados del mismo departamento
            const empleados = await apiClient.getEmpleados({
                departamento: this.currentUser.department
            });
            
            this.renderEmployeeTable(empleados);
            
        } catch (error) {
            console.error('Error loading employee data:', error);
        }
    }

    /**
     * Cargar datos específicos para auditor
     */
    async loadAuditorData() {
        try {
            const [empleados, departamentos, estadisticasDept] = await Promise.all([
                apiClient.getEmpleados(),
                apiClient.getDepartamentos(),
                apiClient.getEstadisticasDepartamentos()
            ]);
            
            this.renderAuditView(empleados, departamentos, estadisticasDept);
            
        } catch (error) {
            console.error('Error loading auditor data:', error);
        }
    }

    /**
     * Renderizar tabla de empleados
     */
    renderEmployeeTable(empleados) {
        const tableContainer = document.getElementById('employee-table-container');
        if (!tableContainer) return;
        
        const columns = [
            { key: 'nombre_completo', title: 'Nombre', sortable: true },
            { key: 'email', title: 'Email', sortable: true },
            { key: 'departamento_nombre', title: 'Departamento', sortable: true },
            { key: 'rol_nombre', title: 'Rol', sortable: true },
            { 
                key: 'estado', 
                title: 'Estado', 
                type: 'badge',
                render: (value) => {
                    const badgeClass = value === 'activo' ? 'success' : 'secondary';
                    return `<span class="badge badge-${badgeClass}">${FormatUtils.capitalize(value)}</span>`;
                }
            }
        ];
        
        const actions = [];
        
        if (authManager.hasPermission('write')) {
            actions.push(
                {
                    name: 'edit',
                    title: 'Editar',
                    icon: '✏️',
                    class: 'btn-outline-primary',
                    handler: (row) => this.editEmployee(row)
                }
            );
        }
        
        if (authManager.hasPermission('delete')) {
            actions.push(
                {
                    name: 'delete',
                    title: 'Eliminar',
                    icon: '🗑️',
                    class: 'btn-outline-danger',
                    handler: (row) => this.deleteEmployee(row)
                }
            );
        }
        
        const dataTable = new DataTable(tableContainer, {
            data: empleados,
            columns: columns,
            actions: actions,
            searchable: true,
            sortable: true,
            pageSize: 10,
            emptyMessage: 'No se encontraron empleados'
        });
        
        this.widgets.set('employeeTable', dataTable);
    }

    /**
     * Cargar notificaciones del usuario
     */
    async loadNotifications() {
        try {
            this.notifications = await apiClient.getNotificacionesUsuario(this.currentUser.id);
            this.renderNotifications();
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    }

    /**
     * Renderizar notificaciones
     */
    renderNotifications() {
        const notificationList = document.getElementById('notification-list');
        if (!notificationList) return;
        
        if (this.notifications.length === 0) {
            notificationList.innerHTML = '<p class="text-center text-muted p-3">No hay notificaciones</p>';
            return;
        }
        
        notificationList.innerHTML = this.notifications.map(notification => `
            <div class="notification-item ${notification.leida ? '' : 'unread'}" 
                 data-id="${notification.id}">
                <div class="notification-content">
                    <p class="notification-text">${notification.mensaje}</p>
                    <small class="notification-time">
                        ${FormatUtils.formatRelativeDate(notification.fecha_creacion)}
                    </small>
                </div>
            </div>
        `).join('');
        
        // Eventos para marcar como leída
        notificationList.addEventListener('click', async (e) => {
            const notificationItem = e.target.closest('.notification-item');
            if (notificationItem && notificationItem.classList.contains('unread')) {
                const notificationId = notificationItem.dataset.id;
                try {
                    await apiClient.marcarNotificacionLeida(notificationId);
                    notificationItem.classList.remove('unread');
                    this.updateNotificationBadge();
                } catch (error) {
                    console.error('Error marking notification as read:', error);
                }
            }
        });
    }

    /**
     * Actualizar badge de notificaciones
     */
    updateNotificationBadge() {
        const badge = document.querySelector('.notification-badge');
        const unreadCount = this.notifications.filter(n => !n.leida).length;
        
        if (badge) {
            if (unreadCount > 0) {
                badge.textContent = unreadCount;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }
    }

    /**
     * Configurar auto-refresh
     */
    setupAutoRefresh() {
        if (this.autoRefreshTimer) {
            clearInterval(this.autoRefreshTimer);
        }
        
        this.autoRefreshTimer = setInterval(async () => {
            try {
                await this.refreshData();
            } catch (error) {
                console.error('Error in auto-refresh:', error);
            }
        }, this.refreshInterval);
    }

    /**
     * Refrescar datos
     */
    async refreshData() {
        try {
            await this.loadStatistics();
            await this.loadNotifications();
            this.updateNotificationBadge();
            
            authManager.updateActivity();
            
        } catch (error) {
            console.error('Error refreshing data:', error);
        }
    }

    /**
     * Configurar eventos
     */
    bindEvents() {
        // Logout
        const logoutBtn = document.querySelector('.logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.logout();
            });
        }
        
        // Refresh manual
        const refreshBtn = document.querySelector('.refresh-btn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', async (e) => {
                e.preventDefault();
                const loadingId = showLoading('Actualizando datos...');
                try {
                    await this.refreshData();
                    showToast('Datos actualizados correctamente', 'success');
                } catch (error) {
                    showToast('Error al actualizar datos', 'error');
                } finally {
                    hideLoading(loadingId);
                }
            });
        }
        
        // Botón de agregar empleado
        const addEmployeeBtn = document.querySelector('.add-employee-btn');
        if (addEmployeeBtn && authManager.hasPermission('write')) {
            addEmployeeBtn.addEventListener('click', () => this.showEmployeeForm());
        }
        
        // Notificaciones
        const notificationBell = document.querySelector('.notification-bell');
        const notificationPanel = document.querySelector('.notification-panel');
        
        if (notificationBell && notificationPanel) {
            notificationBell.addEventListener('click', () => {
                notificationPanel.classList.toggle('show');
            });
            
            // Cerrar al hacer click fuera
            document.addEventListener('click', (e) => {
                if (!notificationBell.contains(e.target) && !notificationPanel.contains(e.target)) {
                    notificationPanel.classList.remove('show');
                }
            });
        }
    }

    /**
     * Cerrar sesión
     */
    logout() {
        if (this.autoRefreshTimer) {
            clearInterval(this.autoRefreshTimer);
        }
        
        authManager.logout();
    }

    /**
     * Mostrar formulario de empleado
     */
    showEmployeeForm(employee = null) {
        const isEdit = employee !== null;
        const title = isEdit ? 'Editar Empleado' : 'Nuevo Empleado';
        
        const modal = createModal({
            title: title,
            size: 'large',
            content: this.generateEmployeeForm(employee),
            footer: `
                <button type="button" class="btn btn-secondary" onclick="hideModal()">Cancelar</button>
                <button type="button" class="btn btn-primary save-employee">
                    ${isEdit ? 'Actualizar' : 'Guardar'}
                </button>
            `
        });
        
        // Cargar datos para selects
        this.loadFormData(modal);
        
        // Evento de guardar
        const saveBtn = modal.querySelector('.save-employee');
        saveBtn.addEventListener('click', () => this.saveEmployee(modal, employee));
        
        showModal(modal);
    }

    /**
     * Generar formulario de empleado
     */
    generateEmployeeForm(employee = null) {
        const data = employee || {};
        
        return `
            <form class="employee-form">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Tipo de Documento</label>
                        <select class="form-control" name="tipo_documento" required>
                            <option value="">Seleccionar...</option>
                            <option value="CC" ${data.tipo_documento === 'CC' ? 'selected' : ''}>Cédula de Ciudadanía</option>
                            <option value="CE" ${data.tipo_documento === 'CE' ? 'selected' : ''}>Cédula de Extranjería</option>
                            <option value="PA" ${data.tipo_documento === 'PA' ? 'selected' : ''}>Pasaporte</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Número de Documento</label>
                        <input type="text" class="form-control" name="numero_documento" 
                               value="${data.numero_documento || ''}" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nombres</label>
                        <input type="text" class="form-control" name="nombres" 
                               value="${data.nombres || ''}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Apellidos</label>
                        <input type="text" class="form-control" name="apellidos" 
                               value="${data.apellidos || ''}" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" 
                               value="${data.email || ''}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" name="telefono" 
                               value="${data.telefono || ''}" placeholder="+57 XXX XXX XXXX">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Fecha de Nacimiento</label>
                        <input type="date" class="form-control" name="fecha_nacimiento" 
                               value="${data.fecha_nacimiento || ''}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Salario</label>
                        <input type="number" class="form-control" name="salario" 
                               value="${data.salario || ''}" min="0" step="1000">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Departamento</label>
                        <select class="form-control" name="id_departamento" required>
                            <option value="">Seleccionar...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Rol</label>
                        <select class="form-control" name="id_rol" required>
                            <option value="">Seleccionar...</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row single">
                    <div class="form-group">
                        <label class="form-label">Dirección</label>
                        <input type="text" class="form-control" name="direccion" 
                               value="${data.direccion || ''}">
                    </div>
                </div>
                
                <div class="form-row single">
                    <div class="form-group">
                        <label class="form-label">Ciudad</label>
                        <input type="text" class="form-control" name="ciudad" 
                               value="${data.ciudad || ''}" placeholder="Ej: Bogotá">
                    </div>
                </div>
            </form>
        `;
    }

    /**
     * Cargar datos para formularios
     */
    async loadFormData(modal) {
        try {
            const [departamentos, roles] = await Promise.all([
                apiClient.getDepartamentos(),
                apiClient.getRoles()
            ]);
            
            // Llenar select de departamentos
            const deptSelect = modal.querySelector('select[name="id_departamento"]');
            departamentos.forEach(dept => {
                const option = document.createElement('option');
                option.value = dept.id_departamento;
                option.textContent = dept.nombre;
                deptSelect.appendChild(option);
            });
            
            // Llenar select de roles
            const roleSelect = modal.querySelector('select[name="id_rol"]');
            roles.forEach(role => {
                const option = document.createElement('option');
                option.value = role.id_rol;
                option.textContent = role.nombre;
                roleSelect.appendChild(option);
            });
            
            // Evento para filtrar roles por departamento
            deptSelect.addEventListener('change', async (e) => {
                const deptId = e.target.value;
                if (deptId) {
                    try {
                        const rolesFiltered = await apiClient.getRolesByDepartamento(deptId);
                        roleSelect.innerHTML = '<option value="">Seleccionar...</option>';
                        rolesFiltered.forEach(role => {
                            const option = document.createElement('option');
                            option.value = role.id_rol;
                            option.textContent = role.nombre;
                            roleSelect.appendChild(option);
                        });
                    } catch (error) {
                        console.error('Error loading roles by department:', error);
                    }
                }
            });
            
        } catch (error) {
            console.error('Error loading form data:', error);
            showToast('Error al cargar datos del formulario', 'error');
        }
    }

    /**
     * Guardar empleado
     */
    async saveEmployee(modal, existingEmployee = null) {
        const form = modal.querySelector('.employee-form');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        // Validar datos
        const validation = DataValidator.validateEmpleado(data);
        if (!validation.isValid) {
            this.showValidationErrors(form, validation.errors);
            return;
        }
        
        const loadingId = showLoading('Guardando empleado...');
        
        try {
            if (existingEmployee) {
                await apiClient.updateEmpleado(existingEmployee.id_empleado, data);
                showToast('Empleado actualizado correctamente', 'success');
            } else {
                await apiClient.createEmpleado(data);
                showToast('Empleado creado correctamente', 'success');
            }
            
            hideModal();
            await this.refreshData();
            
        } catch (error) {
            console.error('Error saving employee:', error);
            showToast(ErrorUtils.getFriendlyMessage(error), 'error');
        } finally {
            hideLoading(loadingId);
        }
    }

    /**
     * Mostrar errores de validación
     */
    showValidationErrors(form, errors) {
        // Limpiar errores previos
        form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        
        // Mostrar nuevos errores
        Object.keys(errors).forEach(field => {
            const input = form.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('is-invalid');
                
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = errors[field];
                
                input.parentNode.appendChild(feedback);
            }
        });
    }

    /**
     * Editar empleado
     */
    editEmployee(employee) {
        this.showEmployeeForm(employee);
    }

    /**
     * Eliminar empleado
     */
    async deleteEmployee(employee) {
        const confirmed = confirm(`¿Está seguro de eliminar al empleado ${employee.nombre_completo}?`);
        
        if (confirmed) {
            const loadingId = showLoading('Eliminando empleado...');
            
            try {
                await apiClient.deleteEmpleado(employee.id_empleado);
                showToast('Empleado eliminado correctamente', 'success');
                await this.refreshData();
                
            } catch (error) {
                console.error('Error deleting employee:', error);
                showToast(ErrorUtils.getFriendlyMessage(error), 'error');
            } finally {
                hideLoading(loadingId);
            }
        }
    }

    /**
     * Actualizar gráficos (placeholder para implementación futura)
     */
    updateCharts(stats) {
        // Implementar gráficos con Chart.js o similar
        console.log('Updating charts with stats:', stats);
    }

    /**
     * Destruir dashboard
     */
    destroy() {
        if (this.autoRefreshTimer) {
            clearInterval(this.autoRefreshTimer);
        }
        
        this.widgets.clear();
    }
}

// Inicializar dashboard cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.dashboardManager = new DashboardManager();
});

// Limpiar al salir de la página
window.addEventListener('beforeunload', () => {
    if (window.dashboardManager) {
        window.dashboardManager.destroy();
    }
});

// Exportar para uso en otros módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { DashboardManager };
}
