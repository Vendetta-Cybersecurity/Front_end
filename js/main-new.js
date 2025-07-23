/**
 * FIGGER ENERGY - Sistema Principal JavaScript
 * Funcionalidad general para la plataforma gubernamental
 */

// ==========================================================================
// CONFIGURACIÓN GLOBAL
// ==========================================================================

const SYSTEM_CONFIG = {
    version: '2.0.0',
    environment: 'production',
    apiBaseUrl: '/api/',
    defaultLanguage: 'es',
    animationSpeed: 300,
    scrollOffset: 100
};

// Datos mock para demostración
const MOCK_GOVERNMENT_DATA = {
    stats: {
        sitiosMonitoreados: 2847,
        alertasActivas: 143,
        empleadosActivos: 89,
        reportesMensuales: 312
    },
    regions: {
        'Antioquia': { sites: 456, alerts: 23 },
        'Cundinamarca': { sites: 298, alerts: 15 },
        'Bolivar': { sites: 387, alerts: 31 },
        'Choco': { sites: 234, alerts: 19 }
    },
    alerts: [
        {
            id: 1,
            type: 'CRITICAL',
            location: 'Caucasia, Antioquia',
            description: 'Actividad minera ilegal detectada',
            timestamp: Date.now() - 3600000
        },
        {
            id: 2,
            type: 'WARNING',
            location: 'Segovia, Antioquia',
            description: 'Anomalía en patrones de excavación',
            timestamp: Date.now() - 7200000
        }
    ]
};

// ==========================================================================
// CLASE PRINCIPAL DEL SISTEMA
// ==========================================================================

class FiggerEnergySystem {
    constructor() {
        this.isInitialized = false;
        this.observers = new Map();
        this.animations = new Map();
        this.init();
    }

    async init() {
        await this.waitForDOM();
        this.setupNavigation();
        this.setupAnimations();
        this.setupCounters();
        this.setupScrollEffects();
        this.setupTooltips();
        this.setupModals();
        this.setupSecurityFeatures();
        this.loadGovernmentData();
        this.isInitialized = true;
        console.log('🏛️ Sistema Figger Energy inicializado correctamente');
    }

    waitForDOM() {
        return new Promise(resolve => {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', resolve);
            } else {
                resolve();
            }
        });
    }

    // ==========================================================================
    // NAVEGACIÓN Y MENÚS
    // ==========================================================================

    setupNavigation() {
        // Mobile menu toggle
        const navbarToggler = document.querySelector('.navbar-toggler');
        const navbarCollapse = document.querySelector('.navbar-collapse');
        
        if (navbarToggler && navbarCollapse) {
            navbarToggler.addEventListener('click', () => {
                navbarCollapse.classList.toggle('show');
            });

            // Cerrar menú al hacer click fuera
            document.addEventListener('click', (e) => {
                if (!navbarToggler.contains(e.target) && !navbarCollapse.contains(e.target)) {
                    navbarCollapse.classList.remove('show');
                }
            });
        }

        // Smooth scrolling para links internos
        this.setupSmoothScrolling();

        // Highlight active section en navbar
        this.setupActiveNavigation();
    }

    setupSmoothScrolling() {
        const navLinks = document.querySelectorAll('a[href^="#"]');
        
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                
                const targetId = link.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                
                if (targetElement) {
                    const offsetTop = targetElement.offsetTop - SYSTEM_CONFIG.scrollOffset;
                    
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });

                    // Actualizar URL sin recargar página
                    history.replaceState(null, null, `#${targetId}`);
                }
            });
        });
    }

    setupActiveNavigation() {
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.navbar-nav .nav-link[href^="#"]');

        if (sections.length === 0 || navLinks.length === 0) return;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Remover active de todos los links
                    navLinks.forEach(link => link.classList.remove('active'));
                    
                    // Agregar active al link correspondiente
                    const activeLink = document.querySelector(`.navbar-nav .nav-link[href="#${entry.target.id}"]`);
                    if (activeLink) {
                        activeLink.classList.add('active');
                    }
                }
            });
        }, {
            rootMargin: '-20% 0px -80% 0px'
        });

        sections.forEach(section => observer.observe(section));
        this.observers.set('navigation', observer);
    }

    // ==========================================================================
    // ANIMACIONES Y EFECTOS VISUALES
    // ==========================================================================

    setupAnimations() {
        // Fade-in animations
        const fadeElements = document.querySelectorAll('.fade-in, .animate-on-scroll');
        
        const fadeObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                    fadeObserver.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        fadeElements.forEach(element => {
            fadeObserver.observe(element);
        });
        this.observers.set('fade', fadeObserver);

        // Hero typing effect
        this.setupTypingEffect();

        // Parallax effects
        this.setupParallaxEffects();
    }

    setupTypingEffect() {
        const typeElements = document.querySelectorAll('[data-typewriter]');
        
        typeElements.forEach(element => {
            const text = element.getAttribute('data-typewriter') || element.textContent;
            const speed = parseInt(element.getAttribute('data-speed')) || 80;
            
            element.textContent = '';
            element.style.borderRight = '2px solid #059669';
            
            let i = 0;
            const typeInterval = setInterval(() => {
                if (i < text.length) {
                    element.textContent += text.charAt(i);
                    i++;
                } else {
                    clearInterval(typeInterval);
                    // Blinking cursor effect
                    setInterval(() => {
                        element.style.borderRightColor = 
                            element.style.borderRightColor === 'transparent' ? '#059669' : 'transparent';
                    }, 500);
                }
            }, speed);
        });
    }

    setupParallaxEffects() {
        let ticking = false;

        const updateParallax = () => {
            const scrolled = window.pageYOffset;
            
            // Hero parallax
            const hero = document.querySelector('.hero-section, .jumbotron');
            if (hero) {
                const rate = scrolled * -0.3;
                hero.style.transform = `translateY(${rate}px)`;
            }

            // Background parallax elements
            const parallaxElements = document.querySelectorAll('[data-parallax]');
            parallaxElements.forEach(element => {
                const speed = parseFloat(element.getAttribute('data-parallax')) || 0.5;
                const yPos = -(scrolled * speed);
                element.style.transform = `translateY(${yPos}px)`;
            });

            ticking = false;
        };

        const requestTick = () => {
            if (!ticking) {
                requestAnimationFrame(updateParallax);
                ticking = true;
            }
        };

        window.addEventListener('scroll', requestTick);
    }

    // ==========================================================================
    // CONTADORES Y ESTADÍSTICAS
    // ==========================================================================

    setupCounters() {
        const counters = document.querySelectorAll('.counter, [data-count]');
        
        counters.forEach(counter => {
            const counterObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.animateCounter(entry.target);
                        counterObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.5 });

            counterObserver.observe(counter);
        });
    }

    animateCounter(element) {
        const target = parseInt(element.getAttribute('data-count') || element.getAttribute('data-target')) || 0;
        const duration = parseInt(element.getAttribute('data-duration')) || 2000;
        const start = Date.now();
        const initialValue = 0;

        const updateCounter = () => {
            const now = Date.now();
            const progress = Math.min((now - start) / duration, 1);
            
            // Easing function
            const easeOutCubic = 1 - Math.pow(1 - progress, 3);
            const current = Math.floor(initialValue + (target - initialValue) * easeOutCubic);
            
            // Format number with commas
            element.textContent = current.toLocaleString('es-CO');
            
            if (progress < 1) {
                requestAnimationFrame(updateCounter);
            } else {
                element.textContent = target.toLocaleString('es-CO');
            }
        };

        updateCounter();
    }

    // ==========================================================================
    // SCROLL EFFECTS Y NAVBAR
    // ==========================================================================

    setupScrollEffects() {
        let lastScrollY = window.scrollY;
        
        const handleScroll = () => {
            const currentScrollY = window.scrollY;
            const navbar = document.querySelector('.navbar');
            
            if (navbar) {
                // Navbar transparency based on scroll
                if (currentScrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }

                // Hide/show navbar on scroll direction
                if (currentScrollY > lastScrollY && currentScrollY > 200) {
                    navbar.classList.add('navbar-hidden');
                } else {
                    navbar.classList.remove('navbar-hidden');
                }
            }

            // Back to top button
            const backToTop = document.querySelector('.back-to-top');
            if (backToTop) {
                if (currentScrollY > 300) {
                    backToTop.classList.add('visible');
                } else {
                    backToTop.classList.remove('visible');
                }
            }

            lastScrollY = currentScrollY;
        };

        // Throttle scroll events
        let ticking = false;
        window.addEventListener('scroll', () => {
            if (!ticking) {
                requestAnimationFrame(() => {
                    handleScroll();
                    ticking = false;
                });
                ticking = true;
            }
        });

        // Back to top functionality
        const backToTop = document.querySelector('.back-to-top');
        if (backToTop) {
            backToTop.addEventListener('click', () => {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }
    }

    // ==========================================================================
    // TOOLTIPS Y MODALES
    // ==========================================================================

    setupTooltips() {
        // Bootstrap tooltips if available
        if (typeof bootstrap !== 'undefined') {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
        }
    }

    setupModals() {
        // Close modals on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAllModals();
            }
        });
    }

    closeAllModals() {
        const modals = document.querySelectorAll('.modal.show');
        modals.forEach(modal => {
            if (typeof bootstrap !== 'undefined') {
                const modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance) {
                    modalInstance.hide();
                }
            }
        });
    }

    // ==========================================================================
    // CARACTERÍSTICAS DE SEGURIDAD
    // ==========================================================================

    setupSecurityFeatures() {
        // Session timeout warning
        this.setupSessionWarnings();

        // Security headers check
        this.checkSecurityHeaders();
    }

    setupSessionWarnings() {
        const session = this.getSession();
        if (!session) return;

        // Warning 5 minutes before timeout
        const timeoutWarning = setTimeout(() => {
            this.showSessionWarning();
        }, 25 * 60 * 1000); // 25 minutes

        // Auto logout on timeout
        const autoLogout = setTimeout(() => {
            if (typeof logout === 'function') {
                logout();
            }
        }, 30 * 60 * 1000); // 30 minutes

        // Clear timeouts on user activity
        ['click', 'keypress', 'scroll', 'mousemove'].forEach(event => {
            document.addEventListener(event, () => {
                clearTimeout(timeoutWarning);
                clearTimeout(autoLogout);
                this.setupSessionWarnings(); // Reset timers
            }, { once: true });
        });
    }

    showSessionWarning() {
        if (typeof bootstrap !== 'undefined') {
            // Use Bootstrap alert
            const alertHTML = `
                <div class="alert alert-warning alert-dismissible fade show position-fixed" 
                     style="top: 20px; right: 20px; z-index: 9999;">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Su sesión expirará en 5 minutos por seguridad.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', alertHTML);
        }
    }

    checkSecurityHeaders() {
        // Log security status for debugging
        console.log('🔒 Verificación de seguridad:');
        console.log('- HTTPS:', location.protocol === 'https:');
        console.log('- Secure Context:', window.isSecureContext);
    }

    // ==========================================================================
    // DATOS GUBERNAMENTALES
    // ==========================================================================

    loadGovernmentData() {
        // Update statistics
        this.updateStatistics();
        
        // Load recent alerts
        this.loadRecentAlerts();
        
        // Update regional data
        this.updateRegionalData();
    }

    updateStatistics() {
        const stats = MOCK_GOVERNMENT_DATA.stats;
        
        Object.entries(stats).forEach(([key, value]) => {
            const element = document.querySelector(`[data-stat="${key}"]`);
            if (element) {
                element.setAttribute('data-count', value);
            }
        });
    }

    loadRecentAlerts() {
        const alertsContainer = document.getElementById('recentAlerts');
        if (!alertsContainer) return;

        const alerts = MOCK_GOVERNMENT_DATA.alerts;
        
        alertsContainer.innerHTML = alerts.map(alert => `
            <div class="alert alert-${alert.type.toLowerCase() === 'critical' ? 'danger' : 'warning'} mb-2">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="alert-heading mb-1">${alert.location}</h6>
                        <p class="mb-1">${alert.description}</p>
                        <small class="text-muted">${this.formatTimestamp(alert.timestamp)}</small>
                    </div>
                    <span class="badge bg-${alert.type.toLowerCase() === 'critical' ? 'danger' : 'warning'}">
                        ${alert.type}
                    </span>
                </div>
            </div>
        `).join('');
    }

    updateRegionalData() {
        const regionContainer = document.getElementById('regionalData');
        if (!regionContainer) return;

        const regions = MOCK_GOVERNMENT_DATA.regions;
        
        regionContainer.innerHTML = Object.entries(regions).map(([region, data]) => `
            <div class="col-md-6 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title">${region}</h6>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="text-primary fw-bold">${data.sites}</div>
                                <small class="text-muted">Sitios</small>
                            </div>
                            <div class="col-6">
                                <div class="text-warning fw-bold">${data.alerts}</div>
                                <small class="text-muted">Alertas</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }

    // ==========================================================================
    // UTILIDADES
    // ==========================================================================

    getSession() {
        try {
            return JSON.parse(localStorage.getItem('figger_session'));
        } catch {
            return null;
        }
    }

    formatTimestamp(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diff = now - date;
        
        const minutes = Math.floor(diff / (1000 * 60));
        const hours = Math.floor(diff / (1000 * 60 * 60));
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        
        if (minutes < 60) return `Hace ${minutes} minutos`;
        if (hours < 24) return `Hace ${hours} horas`;
        return `Hace ${days} días`;
    }

    formatNumber(num) {
        return new Intl.NumberFormat('es-CO').format(num);
    }

    // Cleanup method
    destroy() {
        this.observers.forEach(observer => observer.disconnect());
        this.observers.clear();
        this.animations.clear();
    }
}

// ==========================================================================
// INICIALIZACIÓN GLOBAL
// ==========================================================================

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.figgerSystem = new FiggerEnergySystem();
});

// Global utilities
window.figgerUtils = {
    formatNumber: (num) => new Intl.NumberFormat('es-CO').format(num),
    formatCurrency: (amount) => new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0
    }).format(amount),
    formatDate: (date) => new Intl.DateTimeFormat('es-CO').format(new Date(date))
};

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { FiggerEnergySystem, SYSTEM_CONFIG, MOCK_GOVERNMENT_DATA };
}
