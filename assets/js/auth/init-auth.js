/**
 * Script de inicialización de autenticación
 * Se encarga de verificar que todos los servicios estén cargados correctamente
 */

(function() {
    'use strict';
    
    console.log('🔧 Inicializando sistema de autenticación...');
    
    // Función para verificar autenticación
    function checkAuthentication() {
        console.log('🔍 Verificando autenticación...');
        
        // Verificar que el authService esté disponible
        if (!window.authService) {
            console.error('❌ AuthService no está disponible');
            return false;
        }
        
        // Verificar que el usuario esté autenticado
        const isAuthenticated = window.authService.isUserAuthenticated();
        const currentUser = window.authService.getCurrentUser();
        
        console.log(`🔐 Estado de autenticación: ${isAuthenticated}`);
        
        if (isAuthenticated && currentUser) {
            console.log(`👤 Usuario activo: ${currentUser.username} (${currentUser.role})`);
            
            // Verificar si la sesión no ha expirado
            const sessionTimestamp = localStorage.getItem('figger_session_timestamp');
            if (sessionTimestamp) {
                const sessionAge = Date.now() - parseInt(sessionTimestamp);
                const maxAge = window.API_CONFIG?.AUTH?.SESSION_DURATION || 8 * 60 * 60 * 1000;
                
                if (sessionAge > maxAge) {
                    console.warn('⏰ Sesión expirada, cerrando sesión...');
                    window.authService.logout();
                    return false;
                }
                
                // Actualizar timestamp de actividad
                localStorage.setItem('figger_session_timestamp', Date.now().toString());
            }
            
            return true;
        } else {
            console.log('❌ Usuario no autenticado');
            return false;
        }
    }
    
    // Función para redirigir a login si es necesario
    function redirectToLoginIfNeeded() {
        const currentPath = window.location.pathname;
        const isDashboardPage = currentPath.includes('/dashboard/');
        
        if (isDashboardPage && !checkAuthentication()) {
            console.log('🚨 Acceso no autorizado, redirigiendo a login...');
            window.location.href = '../login.html';
            return true;
        }
        
        return false;
    }
    
    // Función para mostrar información del usuario en la interfaz
    function updateUserInterface() {
        if (!window.authService || !window.authService.isUserAuthenticated()) {
            return;
        }
        
        const user = window.authService.getCurrentUser();
        if (!user) return;
        
        // Actualizar elementos de la interfaz que muestren información del usuario
        const userNameElements = document.querySelectorAll('.user-name, #user-name');
        const userRoleElements = document.querySelectorAll('.user-role, #user-role');
        const userEmailElements = document.querySelectorAll('.user-email, #user-email');
        
        userNameElements.forEach(el => {
            el.textContent = user.empleado?.nombre_completo || user.username;
        });
        
        userRoleElements.forEach(el => {
            el.textContent = user.empleado?.rol_nombre || user.role;
        });
        
        userEmailElements.forEach(el => {
            el.textContent = user.empleado?.email || '';
        });
        
        console.log('✅ Interfaz de usuario actualizada');
    }
    
    // Inicialización cuando el DOM esté listo
    function initialize() {
        console.log('🚀 Inicializando verificación de autenticación...');
        
        // Si estamos en una página del dashboard, verificar autenticación
        if (window.location.pathname.includes('/dashboard/')) {
            if (redirectToLoginIfNeeded()) {
                return; // Ya se está redirigiendo
            }
            
            // Si llegamos aquí, el usuario está autenticado
            updateUserInterface();
        }
        
        console.log('✅ Sistema de autenticación inicializado correctamente');
    }
    
    // Esperar a que todos los scripts estén cargados
    function waitForServices() {
        if (window.authService && window.API_CONFIG) {
            initialize();
        } else {
            setTimeout(waitForServices, 50);
        }
    }
    
    // Iniciar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', waitForServices);
    } else {
        waitForServices();
    }
    
    // Exponer funciones globalmente para uso manual si es necesario
    window.authInit = {
        checkAuthentication,
        updateUserInterface,
        redirectToLoginIfNeeded
    };
    
})();
