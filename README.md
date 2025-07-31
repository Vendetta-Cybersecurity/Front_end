# Figger Energy SAS - Sistema de Gestión Frontend

Sistema integral de gestión empresarial para Figger Energy SAS, desarrollado con tecnologías web modernas y enfoque en energía renovable.

## 🚀 Características Principales

- **🔐 Sistema de Autenticación**: Control de acceso basado en roles (Admin, Empleado, Auditor)
- **📱 Diseño Responsive**: Adaptable a móviles, tablets y desktop  
- **🎯 Gestión Completa**: CRUD de empleados y departamentos
- **📊 Dashboards Interactivos**: Visualización de datos y estadísticas
- **�� API Integration**: Comunicación con backend Django
- **🎨 Branding Corporativo**: Logo y colores de Figger Energy SAS

## 📁 Estructura del Proyecto

```
front-end/
├── index.html                   # Página de login principal
├── dashboards/
│   ├── dashboard-admin.html     # Panel administrativo
│   ├── dashboard-empleado.html  # Portal del empleado
│   └── dashboard-auditor.html   # Panel de auditoría
├── assets/
│   ├── css/                     # Estilos CSS modulares
│   ├── js/                      # Módulos JavaScript
│   └── images/                  # Logo y favicon
└── README.md
```

## 🛠️ Tecnologías

- **Frontend**: HTML5, CSS3, JavaScript ES6+
- **Arquitectura**: Vanilla JS (sin frameworks)
- **Diseño**: CSS Grid/Flexbox, Mobile-first
- **Backend**: Integración API REST

## 🎯 Roles y Acceso

### 🔑 Credenciales de Acceso
- **Administrador**: `admin` / `admin123`
- **Empleado**: `empleado` / `emp123`  
- **Auditor**: `auditor` / `aud123`

### 👑 Administrador
- ✅ Gestión completa de empleados
- ✅ Administración de departamentos
- ✅ Estadísticas y configuración

### 👤 Empleado
- ✅ Ver perfil personal
- ✅ Consultar información del departamento
- ❌ Solo lectura

### 🔍 Auditor
- ✅ Vista general del sistema
- ✅ Reportes y estadísticas
- ✅ Logs de auditoría
- ❌ Solo lectura

## ⚙️ Configuración

### API Backend
Editar `assets/js/api.js`:
```javascript
const API_CONFIG = {
    baseURL: 'http://127.0.0.1:8000/api',
    timeout: 10000,
    retryAttempts: 3
};
```

### Tema Corporativo
Variables CSS en `assets/css/main.css`:
```css
:root {
    --primary-color: #2c5f2d;      /* Verde principal */
    --secondary-color: #4a8b3a;    /* Verde secundario */
    --accent-color: #97bf47;       /* Verde acento */
}
```

## 🚀 Instalación y Uso

1. **Clonar el repositorio**:
   ```bash
   git clone https://github.com/Vendetta-Cybersecurity/Front_end.git
   cd Front_end
   ```

2. **Servir con servidor web** (opcional):
   ```bash
   python -m http.server 8080
   ```

3. **Abrir en navegador**:
   ```
   http://localhost:8080
   ```

## 🌐 Endpoints API

```javascript
GET    /api/empleados/           # Listar empleados
POST   /api/empleados/           # Crear empleado
GET    /api/departamentos/       # Listar departamentos
GET    /api/estadisticas/        # Estadísticas generales
```

## 📱 Responsive Design

- **Mobile**: < 768px
- **Tablet**: 768px - 1023px  
- **Desktop**: ≥ 1024px

## 🔧 Desarrollo

### Estructura Modular
- **CSS**: Variables, componentes, dashboard, responsive
- **JavaScript**: API client, autenticación, utilidades, componentes
- **HTML**: Login + 3 dashboards especializados

### Performance
- ✅ Lazy loading
- ✅ Debouncing en búsquedas
- ✅ Cache local
- ✅ Minificación CSS/JS

## 📄 Licencia

© 2024 Figger Energy SAS - Todos los derechos reservados

## 📞 Soporte

- **Email**: soporte@figgerenergy.com
- **GitHub**: [Issues](https://github.com/Vendetta-Cybersecurity/Front_end/issues)

---

**Sistema de Gestión Frontend v1.0.0** - Energía Renovable Sostenible 🌱⚡