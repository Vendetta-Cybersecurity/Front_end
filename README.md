# Sistema de Gestión Frontend - Figger Energy SAS

Sistema integral de gestión de empleados y departamentos para Figger Energy SAS, desarrollado con tecnologías web modernas y enfoque en la experiencia de usuario.

## 📋 Tabla de Contenidos

- [Características](#características)
- [Tecnologías](#tecnologías)
- [Estructura del Proyecto](#estructura-del-proyecto)
- [Instalación](#instalación)
- [Configuración](#configuración)
- [Uso](#uso)
- [Roles y Permisos](#roles-y-permisos)
- [API Integration](#api-integration)
- [Desarrollo](#desarrollo)
- [Contribución](#contribución)

## ✨ Características

### 🎯 Funcionalidades Principales

- **Gestión de Empleados**: CRUD completo con validación de datos
- **Administración de Departamentos**: Organización empresarial eficiente
- **Sistema de Roles**: Control de acceso basado en roles (RBAC)
- **Dashboard Interactivo**: Visualización de datos y estadísticas en tiempo real
- **Responsive Design**: Adaptable a dispositivos móviles, tablets y desktop
- **Autenticación Segura**: Sistema de login con manejo de sesiones
- **Audit Trail**: Seguimiento de actividades para auditoría

### 🚀 Características Técnicas

- **Vanilla JavaScript**: Sin dependencias de frameworks pesados
- **CSS Modular**: Arquitectura CSS escalable y mantenible
- **API REST**: Integración completa con backend
- **Offline Support**: Funcionalidad básica sin conexión
- **Performance Optimizado**: Carga rápida y eficiente
- **Accessibility**: Cumple estándares WCAG 2.1
- **SEO Friendly**: Optimizado para motores de búsqueda

## 🛠️ Tecnologías

### Frontend
- **HTML5**: Estructura semántica moderna
- **CSS3**: Estilos avanzados con variables CSS y Grid/Flexbox
- **JavaScript ES6+**: Programación moderna con módulos
- **Web APIs**: LocalStorage, Fetch API, Intersection Observer

### Herramientas de Desarrollo
- **Git**: Control de versiones
- **VS Code**: Entorno de desarrollo recomendado
- **Chrome DevTools**: Debugging y performance

### Backend Integration
- **API REST**: Comunicación con servidor Django
- **JSON**: Formato de intercambio de datos
- **HTTP**: Protocolo de comunicación

## 📁 Estructura del Proyecto

```
front-end/
├── assets/
│   ├── css/
│   │   ├── main.css              # Estilos base y variables
│   │   ├── components.css        # Componentes UI reutilizables
│   │   ├── dashboard.css         # Estilos específicos del dashboard
│   │   └── responsive.css        # Media queries y responsive design
│   ├── js/
│   │   ├── api.js               # Cliente API y comunicación con backend
│   │   ├── auth.js              # Sistema de autenticación y autorización
│   │   ├── components.js        # Componentes JavaScript reutilizables
│   │   ├── dashboard.js         # Lógica específica del dashboard
│   │   └── utils.js             # Utilidades y funciones auxiliares
│   └── images/
│       ├── favicon.ico          # Icono del navegador
│       └── logo.png             # Logo corporativo de Figger Energy
├── index.html                   # Página de login principal
├── dashboard-admin.html         # Dashboard para administradores
├── dashboard-empleado.html      # Dashboard para empleados
├── dashboard-auditor.html       # Dashboard para auditores
└── README.md                    # Documentación del proyecto
```

### 📄 Descripción de Archivos

#### CSS Modules
- **main.css**: Variables CSS, reset, tipografía base, layouts principales
- **components.css**: Botones, formularios, tablas, modales, badges, etc.
- **dashboard.css**: Header, sidebar, widgets, estadísticas, navegación
- **responsive.css**: Breakpoints, mobile-first, touch optimizations

#### JavaScript Modules
- **api.js**: Endpoints, error handling, caching, retry logic
- **auth.js**: Login/logout, roles, sesiones, activity logging
- **components.js**: DataTable, Modal, Toast, LoadingManager
- **dashboard.js**: Dashboard initialization, data loading, events
- **utils.js**: Formateo, validación, DOM utilities, device detection

## 🚀 Instalación

### Prerequisitos

1. **Servidor Web** (opcional para desarrollo):
   ```bash
   # Opción 1: Python
   python -m http.server 8080
   
   # Opción 2: Node.js
   npx http-server
   
   # Opción 3: PHP
   php -S localhost:8080
   ```

2. **Backend API** (requerido para funcionalidad completa):
   - Servidor Django ejecutándose en `http://127.0.0.1:8000`
   - Endpoints API configurados según especificación

### Instalación Local

1. **Clonar el repositorio**:
   ```bash
   git clone [url-del-repositorio]
   cd front-end
   ```

2. **Configurar servidor web** (opcional):
   ```bash
   # Usando Python
   python -m http.server 8080
   ```

3. **Abrir en navegador**:
   ```
   http://localhost:8080
   ```

## ⚙️ Configuración

### Variables de Entorno

Editar `assets/js/api.js` para configurar:

```javascript
// Configuración API
const API_CONFIG = {
    baseURL: 'http://127.0.0.1:8000/api',  // URL del backend
    timeout: 10000,                        // Timeout en ms
    retryAttempts: 3,                      // Intentos de reintento
    cacheTimeout: 300000                   // Cache timeout (5 min)
};
```

### Credenciales de Acceso

Para desarrollo y testing (configuradas en `auth.js`):

```javascript
// Credenciales por defecto
const DEFAULT_USERS = {
    admin: { password: 'admin123', role: 'admin' },
    empleado: { password: 'emp123', role: 'empleado' },
    auditor: { password: 'aud123', role: 'auditor' }
};
```

### Personalización de Tema

Variables CSS en `assets/css/main.css`:

```css
:root {
    --primary-color: #2c5f2d;      /* Verde principal */
    --secondary-color: #4a8b3a;    /* Verde secundario */
    --accent-color: #97bf47;       /* Verde acento */
    --success-color: #22c55e;      /* Verde éxito */
    --warning-color: #f59e0b;      /* Amarillo advertencia */
    --error-color: #ef4444;        /* Rojo error */
    /* ... más variables */
}
```

### Branding Corporativo

El sistema incluye elementos de branding de Figger Energy SAS:

- **Logo**: `assets/images/logo.png` - Integrado en headers y páginas de login
- **Favicon**: `assets/images/favicon.ico` - Visible en pestañas del navegador
- **Colores**: Paleta corporativa de energía renovable (verdes)
- **Tipografía**: Fuentes profesionales y legibles
- **Responsive**: Logo optimizado para diferentes tamaños de pantalla

## 🎯 Uso

### Acceso al Sistema

1. **Abrir la aplicación** en el navegador
2. **Seleccionar tipo de usuario** o usar credenciales:
   - **Administrador**: `admin` / `admin123`
   - **Empleado**: `empleado` / `emp123`
   - **Auditor**: `auditor` / `aud123`

### Navegación

#### Dashboard Administrador
- **Gestión de Empleados**: Crear, editar, eliminar empleados
- **Administración de Departamentos**: CRUD de departamentos
- **Estadísticas**: Métricas y gráficos en tiempo real
- **Configuración del Sistema**: Parámetros generales

#### Dashboard Empleado
- **Ver Perfil**: Información personal (solo lectura)
- **Mis Datos**: Consulta de información personal
- **Notificaciones**: Avisos del sistema
- **Contacto**: Información de contacto

#### Dashboard Auditor
- **Vista General**: Resumen del sistema
- **Auditoría de Empleados**: Datos de empleados (solo lectura)
- **Estadísticas Departamentales**: Métricas por departamento
- **Reportes**: Generación de reportes de auditoría
- **Logs del Sistema**: Seguimiento de actividades

### Funcionalidades Comunes

#### Gestión de Empleados (Admin)
```javascript
// Crear empleado
const nuevoEmpleado = {
    numero_documento: "12345678",
    tipo_documento: "CC",
    nombre_completo: "Juan Pérez",
    email: "juan@empresa.com",
    telefono: "3001234567",
    departamento_id: 1,
    rol_id: 1,
    fecha_ingreso: "2024-01-15",
    salario: 3500000,
    estado: "activo"
};

await apiClient.createEmpleado(nuevoEmpleado);
```

#### Búsqueda y Filtros
- **Búsqueda en tiempo real** en tablas de datos
- **Filtros por departamento**, rol, estado
- **Ordenamiento** por columnas
- **Paginación** para grandes volúmenes de datos

#### Responsive Design
- **Mobile First**: Optimizado para móviles
- **Touch Friendly**: Controles táctiles mejorados
- **Breakpoints**:
  - Mobile: < 768px
  - Tablet: 768px - 1023px
  - Desktop: ≥ 1024px

## 👥 Roles y Permisos

### 🔑 Administrador
```javascript
Permisos: ['create', 'read', 'update', 'delete']
Acceso a:
- ✅ Gestión completa de empleados
- ✅ Administración de departamentos
- ✅ Configuración del sistema
- ✅ Estadísticas avanzadas
- ✅ Logs del sistema
```

### 👤 Empleado
```javascript
Permisos: ['read']
Acceso a:
- ✅ Ver perfil propio
- ✅ Consultar información personal
- ✅ Notificaciones del sistema
- ❌ No puede editar datos
- ❌ No acceso a otros empleados
```

### 🔍 Auditor
```javascript
Permisos: ['read', 'audit']
Acceso a:
- ✅ Ver todos los empleados (solo lectura)
- ✅ Estadísticas del sistema
- ✅ Generar reportes
- ✅ Ver logs de auditoría
- ❌ No puede crear/editar/eliminar
```

## 🔌 API Integration

### Endpoints Principales

#### Empleados
```javascript
GET    /api/empleados/           // Listar empleados
POST   /api/empleados/           // Crear empleado
GET    /api/empleados/{id}/      // Obtener empleado
PUT    /api/empleados/{id}/      // Actualizar empleado
DELETE /api/empleados/{id}/      // Eliminar empleado
```

#### Departamentos
```javascript
GET    /api/departamentos/       // Listar departamentos
POST   /api/departamentos/       // Crear departamento
GET    /api/departamentos/{id}/  // Obtener departamento
PUT    /api/departamentos/{id}/  // Actualizar departamento
DELETE /api/departamentos/{id}/  // Eliminar departamento
```

#### Estadísticas
```javascript
GET    /api/estadisticas/general/     // Estadísticas generales
GET    /api/estadisticas/departamentos/ // Stats por departamento
```

### Manejo de Errores

```javascript
// Ejemplo de manejo de errores
try {
    const empleados = await apiClient.getEmpleados();
    // Procesar datos...
} catch (error) {
    if (error.status === 404) {
        showToast('No se encontraron empleados', 'warning');
    } else if (error.status >= 500) {
        showToast('Error del servidor, intente más tarde', 'error');
    } else {
        showToast('Error inesperado', 'error');
    }
}
```

### Caching Strategy

```javascript
// Cache automático para mejorar performance
const CACHE_STRATEGIES = {
    empleados: { timeout: 300000, key: 'emp_cache' },      // 5 min
    departamentos: { timeout: 600000, key: 'dept_cache' }, // 10 min
    estadisticas: { timeout: 60000, key: 'stats_cache' }   // 1 min
};
```

## 🛠️ Desarrollo

### Estructura Modular

#### Añadir Nuevo Component

1. **Crear component en `components.js`**:
```javascript
class NuevoComponent {
    constructor(container, options = {}) {
        this.container = container;
        this.options = { ...this.defaultOptions, ...options };
        this.init();
    }

    get defaultOptions() {
        return {
            // opciones por defecto
        };
    }

    init() {
        this.render();
        this.bindEvents();
    }

    render() {
        // lógica de renderizado
    }

    bindEvents() {
        // eventos del component
    }
}
```

2. **Añadir estilos en `components.css`**:
```css
.nuevo-component {
    /* estilos base */
}

.nuevo-component__element {
    /* estilos de elementos */
}
```

#### Añadir Nueva Funcionalidad

1. **Extender API client**:
```javascript
// En api.js
async getNuevoEndpoint() {
    return await this.request('GET', '/nuevo-endpoint/');
}
```

2. **Actualizar dashboard**:
```javascript
// En dashboard.js
async loadNuevosDatos() {
    try {
        const datos = await apiClient.getNuevoEndpoint();
        this.renderNuevosDatos(datos);
    } catch (error) {
        console.error('Error:', error);
    }
}
```

### Testing

#### Manual Testing
```bash
# Lista de verificación
☐ Login con diferentes roles
☐ CRUD de empleados (admin)
☐ Responsive en diferentes dispositivos
☐ Manejo de errores de red
☐ Performance con datos grandes
☐ Accesibilidad con lector de pantalla
```

#### Browser Testing
- **Chrome** 90+
- **Firefox** 88+
- **Safari** 14+
- **Edge** 90+

### Performance Tips

#### Optimización de Carga
```javascript
// Lazy loading de imágenes
const observerOptions = {
    threshold: 0.1,
    rootMargin: '50px 0px'
};

const imageObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const img = entry.target;
            img.src = img.dataset.src;
            imageObserver.unobserve(img);
        }
    });
}, observerOptions);
```

#### Debouncing de Búsquedas
```javascript
// En components.js - DataTable
this.searchInput.addEventListener('input', 
    debounce((e) => this.handleSearch(e), 300)
);
```

## 📱 Responsive Design

### Breakpoints
```css
/* Mobile First Approach */
.container {
    width: 100%;
    padding: 0 1rem;
}

/* Tablet */
@media (min-width: 768px) {
    .container {
        max-width: 750px;
        margin: 0 auto;
    }
}

/* Desktop */
@media (min-width: 1024px) {
    .container {
        max-width: 1200px;
    }
}

/* Large Desktop */
@media (min-width: 1400px) {
    .container {
        max-width: 1320px;
    }
}
```

### Touch Optimizations
```css
/* Touch targets mínimo 44px */
.btn, .form-control, .nav-link {
    min-height: 44px;
    min-width: 44px;
}

/* Hover solo en dispositivos con cursor */
@media (hover: hover) {
    .btn:hover {
        transform: translateY(-2px);
    }
}
```

## 🔧 Troubleshooting

### Problemas Comunes

#### 1. Error de CORS
```javascript
// Solución: Configurar headers en backend
// En Django settings.py:
CORS_ALLOWED_ORIGINS = [
    "http://localhost:8080",
    "http://127.0.0.1:8080",
]
```

#### 2. API No Disponible
```javascript
// El sistema funciona en modo offline limitado
// Verificar en Console:
console.log('API Status:', await apiClient.getStatus());
```

#### 3. Performance Lenta
```javascript
// Verificar cache del browser
// Verificar tamaño de respuestas API
// Usar Network tab en DevTools
```

#### 4. Responsive Issues
```css
/* Verificar viewport meta tag */
<meta name="viewport" content="width=device-width, initial-scale=1.0">

/* Verificar unidades CSS */
width: 100%;        /* ✅ Correcto */
width: 1200px;      /* ❌ Evitar valores fijos */
```

### Debug Tools

#### Console Commands
```javascript
// Verificar autenticación
console.log('User:', authManager.getCurrentUser());

// Ver cache API
console.log('Cache:', localStorage.getItem('api_cache'));

// Test API endpoint
apiClient.getEmpleados().then(console.log);
```

#### Local Storage Inspector
```javascript
// Ver datos almacenados
Object.keys(localStorage).forEach(key => {
    console.log(key, localStorage.getItem(key));
});
```

## 🚀 Deployment

### Production Build

1. **Minificar CSS y JS**:
```bash
# Usando herramientas de minificación
npm install -g uglifycss uglify-js
uglifycss assets/css/*.css > dist/style.min.css
uglifyjs assets/js/*.js > dist/script.min.js
```

2. **Optimizar imágenes**:
```bash
# Usando imagemin
npm install -g imagemin-cli
imagemin assets/images/* --out-dir=dist/images
```

3. **Configurar servidor web**:
```nginx
# Nginx configuration
server {
    listen 80;
    server_name tu-dominio.com;
    root /path/to/front-end;
    
    location / {
        try_files $uri $uri/ /index.html;
    }
    
    location /assets/ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### Environment Variables
```javascript
// config.js para producción
const CONFIG = {
    API_BASE_URL: process.env.API_URL || 'https://api.figgerenergy.com',
    DEBUG: process.env.NODE_ENV !== 'production',
    VERSION: '1.0.0'
};
```

## 📈 Analytics y Monitoring

### Performance Monitoring
```javascript
// En utils.js
const performanceMonitor = {
    measurePageLoad() {
        window.addEventListener('load', () => {
            const navigation = performance.getEntriesByType('navigation')[0];
            console.log('Page Load Time:', navigation.loadEventEnd - navigation.loadEventStart);
        });
    },
    
    measureAPICall(endpoint) {
        const startTime = performance.now();
        return {
            end() {
                const endTime = performance.now();
                console.log(`API Call ${endpoint}:`, endTime - startTime);
            }
        };
    }
};
```

### Error Tracking
```javascript
// Global error handler
window.addEventListener('error', (event) => {
    console.error('Global Error:', {
        message: event.message,
        filename: event.filename,
        lineno: event.lineno,
        colno: event.colno,
        error: event.error
    });
    
    // Enviar a servicio de logging
    // sendErrorToService(errorInfo);
});
```

## 🤝 Contribución

### Git Workflow
```bash
# 1. Crear rama para feature
git checkout -b feature/nueva-funcionalidad

# 2. Hacer commits descriptivos
git commit -m "feat: agregar filtro de búsqueda avanzada"

# 3. Push y crear Pull Request
git push origin feature/nueva-funcionalidad
```

### Code Style
```javascript
// Usar camelCase para variables y funciones
const nombreVariable = 'valor';
function nombreFuncion() {}

// Usar PascalCase para clases
class NombreClase {}

// Usar UPPER_CASE para constantes
const API_BASE_URL = 'https://api.ejemplo.com';
```

### Commit Convention
```bash
feat: nueva funcionalidad
fix: corrección de bug
docs: actualización de documentación
style: cambios de formato
refactor: refactorización de código
test: agregar tests
chore: tareas de mantenimiento
```

## 📝 Changelog

### v1.0.0 (2024-01-15)
- ✨ **feat**: Sistema completo de gestión implementado
- ✨ **feat**: Dashboards para admin, empleado y auditor
- ✨ **feat**: Sistema de autenticación y roles
- ✨ **feat**: API client con manejo de errores
- ✨ **feat**: Responsive design completo
- ✨ **feat**: Componentes UI reutilizables
- 📚 **docs**: Documentación completa del proyecto

## 📄 Licencia

Este proyecto está desarrollado para Figger Energy SAS. Todos los derechos reservados.

## 👥 Equipo

- **Desarrollo Frontend**: [Desarrollador]
- **UI/UX Design**: [Diseñador]
- **Backend Integration**: [Backend Developer]
- **QA Testing**: [Tester]

## 📞 Soporte

Para soporte técnico o consultas:

- **Email**: soporte@figgerenergy.com
- **Teléfono**: +57 (1) 234-5678
- **Documentación**: [URL de documentación]
- **Issues**: [URL de GitHub Issues]

---

**© 2024 Figger Energy SAS - Sistema de Gestión Frontend v1.0.0**
