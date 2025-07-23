/**
 * Figger Energy SAS - Dashboard JavaScript
 * Handles dashboard functionality for all user types
 */

// Global variables
let currentSection = 'dashboard';
let sidebarCollapsed = false;
let currentUser = null;

document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
    checkAuthentication();
    setupEventListeners();
    loadUserData();
});

/**
 * Initialize dashboard
 */
function initializeDashboard() {
    // Check if user is authenticated
    const user = getCurrentUser();
    if (!user) {
        window.location.href = '../login.html';
        return;
    }
    
    currentUser = user;
    
    // Set initial section
    showSection('dashboard');
    
    // Initialize responsive behavior
    handleResponsive();
}

/**
 * Check user authentication
 */
function checkAuthentication() {
    const user = getCurrentUser();
    if (!user) {
        // Redirect to login if not authenticated
        window.location.href = '../login.html';
        return false;
    }
    
    // Check if session is expired (24 hours)
    const loginTime = new Date(user.loginTime);
    const now = new Date();
    const diffHours = (now - loginTime) / (1000 * 60 * 60);
    
    if (diffHours > 24) {
        logout();
        return false;
    }
    
    return true;
}
    const now = new Date();
    const timeDiff = now - loginTime;
    const hoursDiff = timeDiff / (1000 * 60 * 60);
    
    if (hoursDiff > 24) {
        logout();
        return false;
    }
    
    return true;
}

/**
 * Get current user from storage
 */
function getCurrentUser() {
    let user = JSON.parse(localStorage.getItem('figger_user'));
    if (!user) {
        user = JSON.parse(sessionStorage.getItem('figger_user'));
    }
    return user;
}

/**
 * Setup event listeners
 */
function setupEventListeners() {
    // Sidebar navigation
    const navLinks = document.querySelectorAll('.sidebar-nav a');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const href = this.getAttribute('href');
            if (href.startsWith('#')) {
                const sectionName = href.substring(1);
                showSection(sectionName);
            }
        });
    });
    
    // Sidebar toggle
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }
    
    // User menu toggle
    const userButton = document.querySelector('.user-button');
    if (userButton) {
        userButton.addEventListener('click', toggleUserMenu);
    }
    
    // Close user menu when clicking outside
    document.addEventListener('click', function(e) {
        const userMenu = document.querySelector('.user-menu');
        const userDropdown = document.querySelector('.user-dropdown');
        
        if (userMenu && !userMenu.contains(e.target)) {
            userDropdown.classList.remove('show');
        }
    });
    
    // Window resize handler
    window.addEventListener('resize', handleResponsive);
}

/**
 * Show specific section
 */
function showSection(sectionName) {
    // Hide all sections
    const sections = document.querySelectorAll('.content-section');
    sections.forEach(section => {
        section.classList.remove('active');
    });
    
    // Show target section
    const targetSection = document.getElementById(`${sectionName}-section`);
    if (targetSection) {
        targetSection.classList.add('active');
    }
    
    // Update navigation
    updateNavigation(sectionName);
    
    // Update page title
    updatePageTitle(sectionName);
    
    // Load section-specific data
    loadSectionData(sectionName);
    
    currentSection = sectionName;
}

/**
 * Update navigation active state
 */
function updateNavigation(sectionName) {
    const navItems = document.querySelectorAll('.sidebar-nav li');
    navItems.forEach(item => {
        item.classList.remove('active');
    });
    
    const activeItem = document.querySelector(`.sidebar-nav a[href="#${sectionName}"]`);
    if (activeItem) {
        activeItem.parentElement.classList.add('active');
    }
}

/**
 * Update page title
 */
function updatePageTitle(sectionName) {
    const titles = {
        dashboard: 'Dashboard',
        personal: 'Gestión de Personal',
        reportes: 'Reportes Operativos',
        proyectos: 'Coordinación de Proyectos',
        produccion: 'Datos de Producción',
        horarios: 'Mis Horarios',
        notificaciones: 'Notificaciones',
        capacitaciones: 'Capacitaciones',
        seguridad: 'Seguridad Laboral',
        cumplimiento: 'Métricas de Cumplimiento',
        logs: 'Logs de Acceso',
        auditoria: 'Auditorías',
        politicas: 'Gestión de Políticas',
        perfil: 'Mi Perfil'
    };
    
    const pageTitle = document.getElementById('pageTitle');
    if (pageTitle && titles[sectionName]) {
        pageTitle.textContent = titles[sectionName];
    }
}

/**
 * Load section-specific data
 */
function loadSectionData(sectionName) {
    switch (sectionName) {
        case 'dashboard':
            loadDashboardData();
            break;
        case 'notificaciones':
            loadNotifications();
            break;
        case 'horarios':
            loadScheduleData();
            break;
        case 'capacitaciones':
            loadTrainingData();
            break;
        case 'logs':
            loadAccessLogs();
            break;
        default:
            // No specific data loading required
            break;
    }
}

/**
 * Load dashboard data
 */
function loadDashboardData() {
    // Update stats with real-time data
    updateStats();
    
    // Load recent activities
    loadRecentActivities();
}

/**
 * Update dashboard statistics
 */
function updateStats() {
    // This would typically fetch data from an API
    // For demo purposes, we'll use static data with some randomization
    
    const stats = {
        empleados: {
            horasTrabajadas: 160 + Math.floor(Math.random() * 20),
            capacitacionesPendientes: Math.floor(Math.random() * 5),
            alertasSeguridad: Math.floor(Math.random() * 3),
            certificacionesCompletadas: 5
        },
        administrativos: {
            personalActivo: 245,
            proyectosActivos: 8,
            productividad: 95,
            horasTrabajadasTotal: 9840
        },
        auditores: {
            alertasCriticas: Math.floor(Math.random() * 3),
            cumplimientoGeneral: 94,
            auditoriasCompletadas: 15,
            accesosRecientes: 1247
        }
    };
    
    // Update stat numbers
    const statNumbers = document.querySelectorAll('.stat-number');
    statNumbers.forEach((element, index) => {
        // Add animation effect
        animateNumber(element, parseInt(element.textContent), getRandomStat());
    });
}

/**
 * Animate number changes
 */
function animateNumber(element, start, end) {
    const duration = 1000;
    const increment = (end - start) / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
            current = end;
            clearInterval(timer);
        }
        element.textContent = Math.floor(current);
    }, 16);
}

/**
 * Get random stat for demo
 */
function getRandomStat() {
    return Math.floor(Math.random() * 100) + 50;
}

/**
 * Load recent activities
 */
function loadRecentActivities() {
    // Demo activities data
    const activities = [
        {
            icon: 'fas fa-user-plus',
            text: 'Nuevo empleado registrado: Carlos Mendoza - Técnico de Seguridad',
            time: 'Hace 2 horas'
        },
        {
            icon: 'fas fa-exclamation-triangle',
            text: 'Reporte de incidente: Sector B - Revisión de equipos completada',
            time: 'Hace 4 horas'
        },
        {
            icon: 'fas fa-check-circle',
            text: 'Proyecto completado: Capacitación en Prevención de Riesgos - 25 empleados',
            time: 'Ayer'
        }
    ];
    
    // This would typically update the activities section
    console.log('Recent activities loaded:', activities);
}

/**
 * Load notifications
 */
function loadNotifications() {
    // Mark notification as read when section is viewed
    const notificationBadge = document.querySelector('.notification-badge');
    if (notificationBadge) {
        notificationBadge.style.display = 'none';
    }
}

/**
 * Load schedule data
 */
function loadScheduleData() {
    // Generate dynamic schedule data
    console.log('Loading schedule data for current week');
}

/**
 * Load training data
 */
function loadTrainingData() {
    // Load user's training progress
    console.log('Loading training data');
}

/**
 * Load access logs
 */
function loadAccessLogs() {
    const logs = JSON.parse(localStorage.getItem('access_logs') || '[]');
    console.log('Access logs loaded:', logs);
}

/**
 * Toggle sidebar
 */
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    
    if (window.innerWidth <= 1024) {
        sidebar.classList.toggle('show');
    } else {
        sidebarCollapsed = !sidebarCollapsed;
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
    }
}

/**
 * Toggle user menu
 */
function toggleUserMenu() {
    const userDropdown = document.querySelector('.user-dropdown');
    userDropdown.classList.toggle('show');
}

/**
 * Handle responsive behavior
 */
function handleResponsive() {
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    
    if (window.innerWidth <= 1024) {
        // Mobile behavior
        sidebar.classList.remove('show');
        sidebarToggle.style.display = 'block';
    } else {
        // Desktop behavior
        sidebar.classList.remove('show');
        sidebar.classList.remove('collapsed');
        sidebarToggle.style.display = 'none';
    }
}

/**
 * Load user data and update UI
 */
function loadUserData() {
    if (!currentUser) return;
    
    // Update user info in header
    const userName = document.querySelector('.user-name');
    const userRole = document.querySelector('.user-role');
    
    if (userName && userRole) {
        // Demo user data based on type
        const userData = {
            empleado: {
                name: 'Juan Pérez',
                role: 'Técnico Operativo'
            },
            administrativo: {
                name: 'María González',
                role: 'Coordinadora de Operaciones'
            },
            auditor: {
                name: 'Dr. Carlos Rodríguez',
                role: 'CISO - Chief Information Security Officer'
            }
        };
        
        const userInfo = userData[currentUser.type] || { name: 'Usuario', role: 'Sin rol' };
        userName.textContent = userInfo.name;
        userRole.textContent = userInfo.role;
    }
}

/**
 * Logout function
 */
function logout() {
    // Clear session data
    localStorage.removeItem('figger_user');
    sessionStorage.removeItem('figger_user');
    
    // Log logout event
    if (currentUser) {
        logAccessAttempt(currentUser.email, 'logout', true);
    }
    
    // Show logout message
    showMessage('Sesión cerrada correctamente', 'success');
    
    // Redirect to login page - determine correct path
    setTimeout(() => {
        // Check if we're in a subdirectory
        const currentPath = window.location.pathname;
        const loginPath = currentPath.includes('/dashboard/') ? '../login.html' : 'login.html';
        window.location.href = loginPath;
    }, 1000);
}
}

/**
 * Calendar functions
 */
function previousWeek() {
    // Demo function - would update calendar data
    console.log('Previous week selected');
}

function nextWeek() {
    // Demo function - would update calendar data
    console.log('Next week selected');
}

function exportSchedule() {
    showMessage('Horario exportado correctamente', 'success');
}

/**
 * Training functions
 */
function showTrainingTab(tabName) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.training-tab-content');
    tabContents.forEach(content => {
        content.classList.remove('active');
    });
    
    // Remove active class from all tabs
    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(button => {
        button.classList.remove('active');
    });
    
    // Show selected tab content
    const targetContent = document.getElementById(`${tabName}-trainings`);
    if (targetContent) {
        targetContent.classList.add('active');
    }
    
    // Add active class to selected tab
    const targetButton = event.target;
    targetButton.classList.add('active');
}

/**
 * Notification functions
 */
function markAllRead() {
    const unreadItems = document.querySelectorAll('.notification-item.unread');
    unreadItems.forEach(item => {
        item.classList.remove('unread');
    });
    showMessage('Todas las notificaciones marcadas como leídas', 'success');
}

function markAsRead(button) {
    const notificationItem = button.closest('.notification-item');
    notificationItem.classList.remove('unread');
    button.parentElement.style.display = 'none';
}

/**
 * Profile functions
 */
function editProfile() {
    showMessage('Función de edición de perfil próximamente', 'info');
}

function changePassword() {
    showMessage('Función de cambio de contraseña próximamente', 'info');
}

/**
 * Administrative functions
 */
function addEmployee() {
    showMessage('Función de agregar empleado próximamente', 'info');
}

function exportPersonnel() {
    showMessage('Datos de personal exportados correctamente', 'success');
}

function viewEmployee(id) {
    showMessage(`Visualizando empleado ID: ${id}`, 'info');
}

function editEmployee(id) {
    showMessage(`Editando empleado ID: ${id}`, 'info');
}

function generateReport() {
    showMessage('Generando reporte...', 'info');
    setTimeout(() => {
        showMessage('Reporte generado correctamente', 'success');
    }, 2000);
}

function createProject() {
    showMessage('Función de crear proyecto próximamente', 'info');
}

/**
 * Audit functions
 */
function exportLogs() {
    showMessage('Logs exportados correctamente', 'success');
}

function createPolicy() {
    showMessage('Función de crear política próximamente', 'info');
}

/**
 * Utility functions
 */
function showMessage(message, type = 'info') {
    // Create message element
    const messageEl = document.createElement('div');
    messageEl.className = `dashboard-message dashboard-message-${type}`;
    messageEl.style.cssText = `
        position: fixed;
        top: 90px;
        right: 20px;
        background: ${getMessageColor(type)};
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        max-width: 300px;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideIn 0.3s ease;
    `;
    
    messageEl.innerHTML = `
        <i class="fas fa-${getMessageIcon(type)}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(messageEl);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (messageEl.parentElement) {
            messageEl.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => messageEl.remove(), 300);
        }
    }, 3000);
}

function getMessageIcon(type) {
    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    return icons[type] || 'info-circle';
}

function getMessageColor(type) {
    const colors = {
        success: '#4CAF50',
        error: '#F44336',
        warning: '#FF9800',
        info: '#2196F3'
    };
    return colors[type] || '#2196F3';
}

function logAccessAttempt(email, action, success) {
    const logEntry = {
        timestamp: new Date().toISOString(),
        email: email,
        action: action,
        success: success,
        ip: '192.168.1.100',
        userAgent: navigator.userAgent
    };
    
    console.log('Dashboard Log:', logEntry);
    
    const logs = JSON.parse(localStorage.getItem('access_logs') || '[]');
    logs.push(logEntry);
    
    if (logs.length > 100) {
        logs.splice(0, logs.length - 100);
    }
    
    localStorage.setItem('access_logs', JSON.stringify(logs));
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Export functions for global access
window.DashboardModule = {
    showSection,
    toggleSidebar,
    toggleUserMenu,
    logout,
    showMessage,
    markAllRead,
    markAsRead,
    editProfile,
    changePassword,
    addEmployee,
    exportPersonnel,
    viewEmployee,
    editEmployee,
    generateReport,
    createProject,
    exportLogs,
    createPolicy,
    previousWeek,
    nextWeek,
    exportSchedule,
    showTrainingTab
};
