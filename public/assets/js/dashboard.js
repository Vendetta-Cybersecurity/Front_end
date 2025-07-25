/**
 * FIGGER ENERGY SAS - DASHBOARD MODULE
 * Funciones específicas para páginas del dashboard
 */

'use strict';

const FiggerDashboard = {
    config: {
        refreshInterval: 30000, // 30 seconds
        chartColors: {
            primary: '#2563eb',
            secondary: '#10b981',
            warning: '#f59e0b',
            danger: '#ef4444',
            info: '#06b6d4'
        },
        dateFormat: 'DD/MM/YYYY HH:mm'
    },

    // Current user and role
    currentUser: null,
    currentRole: null,

    // Active timers and intervals
    timers: new Map(),

    // ===== INITIALIZATION =====
    init() {
        console.log('📊 Dashboard module initialized');

        // Get user info from meta tags or global vars
        this.getCurrentUser();

        // Initialize based on current page
        const currentPath = window.location.pathname;
        
        if (currentPath.includes('/dashboard')) {
            this.initializeDashboard();
        }

        // Setup global dashboard functionality
        this.setupGlobalListeners();
        this.startRefreshTimer();
    },

    /**
     * Get current user information
     */
    getCurrentUser() {
        // Try to get from meta tags first
        const userMeta = document.querySelector('meta[name="user-data"]');
        if (userMeta) {
            try {
                const userData = JSON.parse(userMeta.content);
                this.currentUser = userData;
                this.currentRole = userData.rol;
                return;
            } catch (e) {
                console.warn('Could not parse user data from meta tag');
            }
        }

        // Try to get from global variable
        if (window.currentUser) {
            this.currentUser = window.currentUser;
            this.currentRole = window.currentUser.rol;
            return;
        }

        // Try to get from session storage
        const sessionUser = sessionStorage.getItem('currentUser');
        if (sessionUser) {
            try {
                this.currentUser = JSON.parse(sessionUser);
                this.currentRole = this.currentUser.rol;
                return;
            } catch (e) {
                console.warn('Could not parse user data from session storage');
            }
        }

        console.warn('Current user data not found');
    },

    /**
     * Initialize dashboard functionality
     */
    initializeDashboard() {
        this.loadDashboardData();
        this.initializeCharts();
        this.setupRealTimeUpdates();
        this.initializeNotifications();
        this.setupQuickActions();
    },

    // ===== DATA MANAGEMENT =====
    
    /**
     * Load dashboard data
     */
    async loadDashboardData() {
        try {
            const endpoints = this.getDashboardEndpoints();
            
            // Load data for each section
            const promises = Object.entries(endpoints).map(async ([section, url]) => {
                try {
                    const response = await FiggerEnergy.utils.request(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': FiggerEnergy.config.csrfToken
                        }
                    });
                    
                    return { section, data: response };
                } catch (error) {
                    console.error(`Error loading ${section}:`, error);
                    return { section, error: error.message };
                }
            });

            const results = await Promise.all(promises);
            
            // Process results
            results.forEach(({ section, data, error }) => {
                if (error) {
                    this.showDataError(section, error);
                } else {
                    this.updateDashboardSection(section, data);
                }
            });

        } catch (error) {
            console.error('Error loading dashboard data:', error);
            FiggerEnergy.ui.showNotification('Error al cargar datos del dashboard', 'error');
        }
    },

    /**
     * Get dashboard endpoints based on user role
     */
    getDashboardEndpoints() {
        const baseEndpoints = {
            statistics: '/api/dashboard/statistics',
            recent_alerts: '/api/dashboard/recent-alerts',
            activities: '/api/dashboard/activities'
        };

        // Add role-specific endpoints
        if (this.currentRole === 'admin') {
            return {
                ...baseEndpoints,
                users: '/api/dashboard/users',
                system_status: '/api/dashboard/system-status',
                reports: '/api/dashboard/reports'
            };
        } else if (this.currentRole === 'auditor') {
            return {
                ...baseEndpoints,
                audit_queue: '/api/dashboard/audit-queue',
                compliance: '/api/dashboard/compliance'
            };
        } else {
            return {
                ...baseEndpoints,
                my_tasks: '/api/dashboard/my-tasks',
                my_reports: '/api/dashboard/my-reports'
            };
        }
    },

    /**
     * Update dashboard section with new data
     */
    updateDashboardSection(section, data) {
        switch (section) {
            case 'statistics':
                this.updateStatistics(data);
                break;
            case 'recent_alerts':
                this.updateRecentAlerts(data);
                break;
            case 'activities':
                this.updateActivities(data);
                break;
            case 'users':
                this.updateUsersTable(data);
                break;
            case 'audit_queue':
                this.updateAuditQueue(data);
                break;
            case 'my_tasks':
                this.updateMyTasks(data);
                break;
            default:
                console.log(`Updating ${section}:`, data);
        }
    },

    /**
     * Update statistics cards
     */
    updateStatistics(data) {
        const stats = data.statistics || {};
        
        Object.entries(stats).forEach(([key, value]) => {
            const element = document.querySelector(`[data-stat="${key}"]`);
            if (element) {
                this.animateNumber(element, value);
            }
        });
    },

    /**
     * Update recent alerts
     */
    updateRecentAlerts(data) {
        const container = document.querySelector('.alerts-list');
        if (!container) return;

        const alerts = data.alerts || [];
        
        if (alerts.length === 0) {
            container.innerHTML = '<div class="no-data">No hay alertas recientes</div>';
            return;
        }

        container.innerHTML = alerts.map(alert => `
            <div class="alert-item priority-${alert.prioridad}" data-alert-id="${alert.id}">
                <div class="alert-icon">
                    <i class="fas fa-${this.getAlertIcon(alert.tipo)}"></i>
                </div>
                <div class="alert-content">
                    <div class="alert-title">${alert.titulo}</div>
                    <div class="alert-location">${alert.ubicacion}</div>
                    <div class="alert-time">${this.formatDate(alert.fecha_creacion)}</div>
                </div>
                <div class="alert-status">
                    <span class="status-badge status-${alert.estado}">${alert.estado}</span>
                </div>
                <div class="alert-actions">
                    <button class="btn-action" onclick="FiggerDashboard.viewAlert(${alert.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
        `).join('');
    },

    /**
     * Update activities timeline
     */
    updateActivities(data) {
        const container = document.querySelector('.activities-timeline');
        if (!container) return;

        const activities = data.activities || [];
        
        if (activities.length === 0) {
            container.innerHTML = '<div class="no-data">No hay actividades recientes</div>';
            return;
        }

        container.innerHTML = activities.map(activity => `
            <div class="activity-item">
                <div class="activity-icon">
                    <i class="fas fa-${this.getActivityIcon(activity.tipo)}"></i>
                </div>
                <div class="activity-content">
                    <div class="activity-description">${activity.descripcion}</div>
                    <div class="activity-user">${activity.usuario_nombre}</div>
                    <div class="activity-time">${this.formatDate(activity.fecha)}</div>
                </div>
            </div>
        `).join('');
    },

    // ===== CHARTS AND VISUALIZATIONS =====

    /**
     * Initialize charts
     */
    initializeCharts() {
        // Initialize charts based on available containers
        const chartContainers = document.querySelectorAll('[data-chart]');
        
        chartContainers.forEach(container => {
            const chartType = container.dataset.chart;
            const chartData = this.getChartData(chartType);
            
            this.renderChart(container, chartType, chartData);
        });
    },

    /**
     * Get chart data based on type
     */
    getChartData(chartType) {
        // This would typically come from the API
        // For now, return mock data
        switch (chartType) {
            case 'alerts-trend':
                return {
                    labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Alertas',
                        data: [12, 19, 3, 5, 2, 3],
                        borderColor: this.config.chartColors.primary,
                        backgroundColor: this.config.chartColors.primary + '20'
                    }]
                };
            case 'compliance-status':
                return {
                    labels: ['Cumple', 'Parcial', 'No Cumple'],
                    datasets: [{
                        data: [65, 25, 10],
                        backgroundColor: [
                            this.config.chartColors.secondary,
                            this.config.chartColors.warning,
                            this.config.chartColors.danger
                        ]
                    }]
                };
            default:
                return null;
        }
    },

    /**
     * Render chart (simplified - would use Chart.js or similar in production)
     */
    renderChart(container, chartType, data) {
        if (!data) return;

        // For now, just show a placeholder
        container.innerHTML = `
            <div class="chart-placeholder">
                <i class="fas fa-chart-${chartType.includes('pie') ? 'pie' : 'line'}"></i>
                <div>Gráfico: ${chartType}</div>
            </div>
        `;
    },

    // ===== REAL-TIME UPDATES =====

    /**
     * Setup real-time updates
     */
    setupRealTimeUpdates() {
        // Clear existing timer
        if (this.timers.has('refresh')) {
            clearInterval(this.timers.get('refresh'));
        }

        // Set up periodic refresh
        const refreshTimer = setInterval(() => {
            this.loadDashboardData();
        }, this.config.refreshInterval);

        this.timers.set('refresh', refreshTimer);
    },

    /**
     * Start refresh timer
     */
    startRefreshTimer() {
        const lastUpdate = document.querySelector('.last-update');
        if (!lastUpdate) return;

        const timer = setInterval(() => {
            lastUpdate.textContent = `Última actualización: ${this.formatDate(new Date())}`;
        }, 1000);

        this.timers.set('timestamp', timer);
    },

    // ===== NOTIFICATIONS =====

    /**
     * Initialize notifications
     */
    initializeNotifications() {
        this.loadNotifications();
        this.setupNotificationMarkAsRead();
    },

    /**
     * Load notifications
     */
    async loadNotifications() {
        try {
            const response = await FiggerEnergy.utils.request('/api/notifications', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': FiggerEnergy.config.csrfToken
                }
            });

            this.updateNotificationsDropdown(response.notifications || []);
            this.updateNotificationsBadge(response.unread_count || 0);

        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    },

    /**
     * Update notifications dropdown
     */
    updateNotificationsDropdown(notifications) {
        const container = document.querySelector('.notifications-list');
        if (!container) return;

        if (notifications.length === 0) {
            container.innerHTML = '<div class="no-notifications">No hay notificaciones</div>';
            return;
        }

        container.innerHTML = notifications.map(notification => `
            <div class="notification-item ${!notification.leida ? 'unread' : ''}" data-notification-id="${notification.id}">
                <div class="notification-icon">
                    <i class="fas fa-${this.getNotificationIcon(notification.tipo)}"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-title">${notification.titulo}</div>
                    <div class="notification-message">${notification.mensaje}</div>
                    <div class="notification-time">${this.formatDate(notification.fecha_creacion)}</div>
                </div>
                ${!notification.leida ? '<div class="notification-unread"></div>' : ''}
            </div>
        `).join('');
    },

    /**
     * Update notifications badge
     */
    updateNotificationsBadge(count) {
        const badge = document.querySelector('.notifications-badge');
        if (!badge) return;

        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = 'block';
        } else {
            badge.style.display = 'none';
        }
    },

    /**
     * Setup notification mark as read
     */
    setupNotificationMarkAsRead() {
        document.addEventListener('click', async (e) => {
            const notificationItem = e.target.closest('.notification-item');
            if (!notificationItem || !notificationItem.classList.contains('unread')) return;

            const notificationId = notificationItem.dataset.notificationId;
            
            try {
                await FiggerEnergy.utils.request(`/api/notifications/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': FiggerEnergy.config.csrfToken
                    }
                });

                notificationItem.classList.remove('unread');
                const unreadIndicator = notificationItem.querySelector('.notification-unread');
                if (unreadIndicator) unreadIndicator.remove();

                // Update badge
                const currentBadge = document.querySelector('.notifications-badge');
                const currentCount = parseInt(currentBadge?.textContent || '0');
                this.updateNotificationsBadge(Math.max(0, currentCount - 1));

            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        });
    },

    // ===== QUICK ACTIONS =====

    /**
     * Setup quick actions
     */
    setupQuickActions() {
        // Export data
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="export"]')) {
                e.preventDefault();
                this.exportData(e.target.dataset.exportType);
            }
        });

        // Refresh data
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="refresh"]')) {
                e.preventDefault();
                this.loadDashboardData();
                FiggerEnergy.ui.showNotification('Datos actualizados', 'success');
            }
        });

        // Quick filters
        document.addEventListener('change', (e) => {
            if (e.target.matches('.quick-filter')) {
                this.applyQuickFilter(e.target.name, e.target.value);
            }
        });
    },

    /**
     * Export data
     */
    async exportData(type = 'excel') {
        try {
            const response = await FiggerEnergy.utils.request(`/api/export/${type}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': FiggerEnergy.config.csrfToken
                }
            });

            if (response.download_url) {
                // Trigger download
                const link = document.createElement('a');
                link.href = response.download_url;
                link.download = response.filename || `export_${Date.now()}.${type}`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                FiggerEnergy.ui.showNotification('Exportación completada', 'success');
            }

        } catch (error) {
            console.error('Error exporting data:', error);
            FiggerEnergy.ui.showNotification('Error al exportar datos', 'error');
        }
    },

    /**
     * Apply quick filter
     */
    applyQuickFilter(filterName, filterValue) {
        // Implementation would depend on specific filtering requirements
        console.log(`Applying filter: ${filterName} = ${filterValue}`);
        this.loadDashboardData();
    },

    // ===== UTILITY FUNCTIONS =====

    /**
     * Get alert icon
     */
    getAlertIcon(type) {
        const iconMap = {
            'mineria_ilegal': 'exclamation-triangle',
            'contaminacion': 'tint',
            'deforestacion': 'tree',
            'default': 'bell'
        };
        return iconMap[type] || iconMap.default;
    },

    /**
     * Get activity icon
     */
    getActivityIcon(type) {
        const iconMap = {
            'login': 'sign-in-alt',
            'logout': 'sign-out-alt',
            'create': 'plus',
            'update': 'edit',
            'delete': 'trash',
            'export': 'download',
            'default': 'circle'
        };
        return iconMap[type] || iconMap.default;
    },

    /**
     * Get notification icon
     */
    getNotificationIcon(type) {
        const iconMap = {
            'alert': 'exclamation-triangle',
            'info': 'info-circle',
            'warning': 'exclamation-circle',
            'success': 'check-circle',
            'default': 'bell'
        };
        return iconMap[type] || iconMap.default;
    },

    /**
     * Format date
     */
    formatDate(date) {
        if (!date) return '';
        
        const d = new Date(date);
        if (isNaN(d.getTime())) return '';

        return d.toLocaleString('es-CO', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    /**
     * Animate number counter
     */
    animateNumber(element, targetValue) {
        const startValue = parseInt(element.textContent) || 0;
        const difference = targetValue - startValue;
        const duration = 1000; // 1 second
        const startTime = Date.now();

        const animate = () => {
            const elapsed = Date.now() - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            const currentValue = Math.round(startValue + (difference * progress));
            element.textContent = currentValue.toLocaleString();

            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };

        animate();
    },

    /**
     * Show data error
     */
    showDataError(section, error) {
        const container = document.querySelector(`[data-section="${section}"]`);
        if (container) {
            container.innerHTML = `
                <div class="data-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>Error al cargar ${section}</div>
                    <small>${error}</small>
                </div>
            `;
        }
    },

    // ===== PUBLIC METHODS =====

    /**
     * View alert details
     */
    viewAlert(alertId) {
        window.location.href = `/alerts/${alertId}`;
    },

    /**
     * View user details
     */
    viewUser(userId) {
        window.location.href = `/users/${userId}`;
    },

    /**
     * Refresh section
     */
    refreshSection(section) {
        const endpoint = this.getDashboardEndpoints()[section];
        if (!endpoint) return;

        FiggerEnergy.utils.request(endpoint, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': FiggerEnergy.config.csrfToken
            }
        })
        .then(data => this.updateDashboardSection(section, data))
        .catch(error => this.showDataError(section, error.message));
    },

    // ===== GLOBAL LISTENERS =====
    
    setupGlobalListeners() {
        // Navigation toggle for mobile
        document.addEventListener('click', (e) => {
            if (e.target.matches('.nav-toggle') || e.target.closest('.nav-toggle')) {
                e.preventDefault();
                document.body.classList.toggle('nav-open');
            }
        });

        // Close navigation when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.sidebar') && !e.target.closest('.nav-toggle')) {
                document.body.classList.remove('nav-open');
            }
        });

        // Dropdown toggles
        document.addEventListener('click', (e) => {
            if (e.target.matches('.dropdown-toggle') || e.target.closest('.dropdown-toggle')) {
                e.preventDefault();
                e.stopPropagation();
                
                const dropdown = e.target.closest('.dropdown');
                if (dropdown) {
                    dropdown.classList.toggle('active');
                }
            }
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown.active').forEach(dropdown => {
                    dropdown.classList.remove('active');
                });
            }
        });
    },

    /**
     * Cleanup when navigating away
     */
    cleanup() {
        // Clear all timers
        this.timers.forEach(timer => clearInterval(timer));
        this.timers.clear();
    }
};

// ===== GLOBAL FUNCTIONS FOR INLINE USE =====
window.viewAlert = (id) => FiggerDashboard.viewAlert(id);
window.viewUser = (id) => FiggerDashboard.viewUser(id);
window.refreshSection = (section) => FiggerDashboard.refreshSection(section);
window.exportData = (type) => FiggerDashboard.exportData(type);

// ===== AUTO-INITIALIZATION =====
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => FiggerDashboard.init());
} else {
    FiggerDashboard.init();
}

// Cleanup on page unload
window.addEventListener('beforeunload', () => FiggerDashboard.cleanup());

// ===== EXPORT =====
window.FiggerDashboard = FiggerDashboard;
