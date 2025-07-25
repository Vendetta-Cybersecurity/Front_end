# Figger Energy SAS - Sistema Web Gubernamental

## 📋 Descripción

Sistema web gubernamental para el monitoreo y control de actividades de minería ilegal que extraen materiales críticos para energías renovables en Colombia.

## 🚀 Versión Refactorizada

Esta es la versión refactorizada del proyecto original, implementando las mejores prácticas de desarrollo web:

- ✅ Estructura de directorios organizada
- ✅ Principio DRY (Don't Repeat Yourself)
- ✅ Separación de responsabilidades
- ✅ Funciones reutilizables
- ✅ Seguridad mejorada
- ✅ Configuración centralizada

## 📁 Estructura del Proyecto

```
www.figgerenergy.gov.co/
├── index.php                 # Página principal
├── .htaccess               # Configuración Apache y redirecciones
├── README.md               # Documentación del proyecto
│
├── assets/                 # Recursos estáticos
│   ├── css/               # Hojas de estilo
│   │   ├── estilos.css
│   │   ├── dashboard.css
│   │   └── login.css
│   ├── js/                # Scripts JavaScript
│   │   ├── scripts.js
│   │   ├── config-estadisticas.js
│   │   └── estadisticas-dinamicas.js
│   └── images/            # Imágenes y recursos gráficos
│
├── config/                # Configuración del sistema
│   └── database.php       # Configuración de base de datos
│
├── includes/              # Archivos de inclusión común
│   ├── header.php         # Cabecera común (actualizada)
│   └── footer.php         # Pie de página común (actualizado)
│
├── lib/                   # Bibliotecas y funciones
│   └── functions.php      # Funciones comunes del sistema
│
├── views/                 # Vistas organizadas por módulo
│   ├── auth/              # Autenticación
│   │   ├── login.php      # Inicio de sesión (antes login.php)
│   │   ├── register.php   # Registro (antes register.php)
│   │   └── logout.php     # Cerrar sesión (antes logout.php)
│   │
│   ├── public/            # Páginas públicas
│   │   └── contact.php    # Contacto (antes contacto.php)
│   │
│   ├── dashboard/         # Paneles de control
│   │   ├── admin.php      # Dashboard admin (antes dashboard_admin.php)
│   │   ├── empleado.php   # Dashboard empleado (antes dashboard_empleado.php)
│   │   └── auditor.php    # Dashboard auditor (antes dashboard_auditor.php)
│   │
│   └── admin/             # Administración
│       └── users.php      # Gestión usuarios (antes gestionar_usuarios.php)
│
├── database/              # Base de datos
│   └── figger_energy_complete.sql
│
├── css/                   # [DEPRECATED] Usar assets/css/
├── js/                    # [DEPRECATED] Usar assets/js/
└── includes/
    └── db.php             # [DEPRECATED] Usar config/database.php
```

## 🔄 Cambios Principales

### Reorganización de Archivos

| Archivo Original | Nuevo Archivo | Cambio |
|-----------------|---------------|--------|
| `login.php` | `views/auth/login.php` | Movido y refactorizado |
| `register.php` | `views/auth/register.php` | Movido y refactorizado |
| `logout.php` | `views/auth/logout.php` | Movido y refactorizado |
| `contacto.php` | `views/public/contact.php` | Movido y refactorizado |
| `dashboard_admin.php` | `views/dashboard/admin.php` | Movido y refactorizado |
| `dashboard_empleado.php` | `views/dashboard/empleado.php` | Movido y refactorizado |
| `dashboard_auditor.php` | `views/dashboard/auditor.php` | Movido y refactorizado |
| `gestionar_usuarios.php` | `views/admin/users.php` | Movido y refactorizado |
| `includes/db.php` | `config/database.php` | Movido y mejorado |
| `css/` | `assets/css/` | Reorganizado |
| `js/` | `assets/js/` | Reorganizado |

### Nuevos Archivos

- `lib/functions.php` - Biblioteca de funciones comunes
- `.htaccess` - Configuración Apache y redirecciones
- `README.md` - Documentación del proyecto

### Mejoras Implementadas

#### 1. **Principio DRY (Don't Repeat Yourself)**
- ✅ Funciones comunes en `lib/functions.php`
- ✅ Validación centralizada de formularios
- ✅ Gestión unificada de mensajes
- ✅ Funciones de autenticación reutilizables
- ✅ Manejo consistente de base de datos

#### 2. **Seguridad Mejorada**
- ✅ Verificación de autenticación centralizada
- ✅ Protección contra inyección SQL mejorada
- ✅ Validación de datos de entrada
- ✅ Configuración de seguridad en .htaccess
- ✅ Headers de seguridad HTTP

#### 3. **Código Más Limpio**
- ✅ Separación de lógica y presentación
- ✅ Nombres de archivos más descriptivos
- ✅ Estructura de directorios lógica
- ✅ Comentarios y documentación mejorados

#### 4. **Mantenimiento Simplificado**
- ✅ Configuración centralizada
- ✅ Rutas dinámicas y adaptables
- ✅ Sistema de redirecciones automáticas
- ✅ Compatibilidad con URLs antiguas

## 🔧 Instalación y Configuración

### Requisitos
- Apache 2.4+
- PHP 7.4+
- MySQL 5.7+
- Módulos Apache: mod_rewrite, mod_headers

### Pasos de Instalación

1. **Clonar o copiar los archivos** al directorio web
2. **Importar la base de datos** desde `database/figger_energy_complete.sql`
3. **Configurar la base de datos** en `config/database.php`
4. **Verificar permisos** de archivos y directorios
5. **Acceder al sistema** desde el navegador

### Configuración de Base de Datos

Editar `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_contraseña');
define('DB_NAME', 'figger_energy_db');
```

## 👥 Cuentas de Prueba

| Rol | Email | Contraseña |
|-----|-------|------------|
| Administrador | admin@figgerenergy.gov.co | admin123 |
| Empleado | empleado@figgerenergy.gov.co | empleado123 |
| Auditor | auditor@figgerenergy.gov.co | auditor123 |

## 🔀 Redirecciones Automáticas

El sistema incluye redirecciones automáticas para mantener compatibilidad:

- `login.php` → `views/auth/login.php`
- `register.php` → `views/auth/register.php`
- `logout.php` → `views/auth/logout.php`
- `contacto.php` → `views/public/contact.php`
- `contact.php` → `views/public/contact.php`
- `dashboard_*.php` → `views/dashboard/*.php`
- `gestionar_usuarios.php` → `views/admin/users.php`

## 📚 Funciones Principales

### Autenticación
- `verificarAuth()` - Verificar si el usuario está autenticado
- `requiereAuth()` - Redirigir si no está autenticado
- `getDashboardUrl()` - Obtener URL del dashboard según rol

### Validación
- `validarFormulario()` - Validar datos de formularios
- `limpiarDatos()` - Limpiar datos de entrada

### Utilidades
- `mostrarMensaje()` - Mostrar mensajes al usuario
- `formatearFecha()` - Formatear fechas para mostrar
- `registrarActividad()` - Registrar actividades del usuario

### Seguridad
- `generarCSRF()` - Generar token CSRF
- `verificarCSRF()` - Verificar token CSRF

## 🛠️ Mantenimiento

### Archivos Deprecados
Los siguientes archivos se mantienen por compatibilidad pero están marcados como deprecados:

- `css/` (usar `assets/css/`)
- `js/` (usar `assets/js/`)
- `includes/db.php` (usar `config/database.php`)

### Próximas Mejoras
- [ ] Sistema de caché
- [ ] API REST
- [ ] Interfaz mobile-first
- [ ] Sistema de notificaciones
- [ ] Módulo de reportes avanzados

## 📞 Soporte

Para soporte técnico:
- Email: soporte@figgerenergy.gov.co
- Documentación: [En desarrollo]

---

**Figger Energy SAS** - Gobierno de Colombia
Versión refactorizada: 2.0.0
Fecha: 2025
