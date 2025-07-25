# 📚 DOCUMENTACIÓN COMPLETA - FIGGER ENERGY SAS
## Sistema de Monitoreo y Control de Minería Ilegal

**Versión:** 3  
**Fecha:** Julio 2025  
**Entidad:** Figger Energy SAS - Empresa Gubernamental Colombiana

---

## 📑 ÍNDICE

1. [Información General](#información-general)
2. [Instalación y Configuración](#instalación-y-configuración)
3. [Base de Datos](#base-de-datos)
4. [Seguridad y Autenticación](#seguridad-y-autenticación)
5. [Funcionalidades del Sistema](#funcionalidades-del-sistema)
6. [Gestión de Usuarios](#gestión-de-usuarios)
7. [API y Desarrollo](#api-y-desarrollo)
8. [Solución de Problemas](#solución-de-problemas)
9. [Migración y Actualizaciones](#migración-y-actualizaciones)
10. [Mantenimiento](#mantenimiento)

---

## 🏛️ INFORMACIÓN GENERAL

### Descripción del Proyecto
**Figger Energy SAS** es una empresa gubernamental colombiana especializada en el monitoreo, detección y control de actividades de minería ilegal que extraen materiales críticos para energías renovables.

### Objetivos Principales
- ✅ **Monitoreo satelital** de actividades mineras no autorizadas
- ✅ **Sistema de alertas** en tiempo real
- ✅ **Gestión de denuncias** ciudadanas
- ✅ **Seguimiento de investigaciones** y resolución de casos
- ✅ **Auditoría completa** de actividades del sistema
- ✅ **Reportes estadísticos** y análisis de tendencias

### Características Técnicas
- **Backend:** PHP 7.4+, MySQL 8.0+
- **Frontend:** HTML5, CSS3, JavaScript ES6+
- **Seguridad:** bcrypt, CSRF tokens, SQL injection protection
- **Arquitectura:** MVC con separación de responsabilidades
- **Base de Datos:** Unificada con foreign keys y triggers automáticos

### Información de Contacto
- **Dirección:** Calle 1, Carrera 1, Edificio 1, Macondo, Colombia
- **Teléfono:** +57 300 0000 000
- **Emergencias:** +57 018000001
- **Email:** contacto@figgerenergy.gov.co
- **Web:** https://www.figgerenergy.gov.co

---

## 🔧 INSTALACIÓN Y CONFIGURACIÓN

### Requisitos del Sistema
- **XAMPP** con PHP 7.4+ y MySQL 8.0+
- **Navegador web** moderno (Chrome, Firefox, Edge)
- **Espacio en disco:** Mínimo 100 MB

### Instalación Rápida (3 Pasos)

#### PASO 1: Preparar XAMPP
1. Instala **XAMPP** desde https://www.apachefriends.org/
2. Inicia **XAMPP Control Panel**
3. Ejecuta **Apache** y **MySQL** (ambos en verde)
4. Haz clic en **"Admin"** junto a MySQL (abre phpMyAdmin)

#### PASO 2: Importar Base de Datos
1. En phpMyAdmin, ve a la pestaña **"SQL"**
2. Haz clic en **"Elegir archivo"**
3. Selecciona: `database/figger_energy_complete.sql`
4. Haz clic en **"Continuar"**
5. Espera mensaje de éxito

#### PASO 3: Verificar Instalación
1. Verifica que aparezca `figger_energy_db` en la barra lateral
2. Debes ver **10 tablas** creadas
3. En tabla `usuarios` → `Examinar`: 3 usuarios con contraseñas hasheadas
4. Accede a: `http://localhost/tu-proyecto/login.php`

### Credenciales de Acceso
```bash
🔑 Administrador: admin@figgerenergy.gov.co / admin123
🔑 Empleado:      empleado@figgerenergy.gov.co / empleado123
🔑 Auditor:       auditor@figgerenergy.gov.co / auditor123
```

### Configuración de Conexión
El archivo `includes/db.php` contiene la configuración de conexión:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'figger_energy_db');
```

---

## 🗄️ BASE DE DATOS

### Estructura Unificada
La base de datos está completamente unificada en un solo archivo: `database/figger_energy_complete.sql`

#### Tablas Principales (10 tablas)
1. **usuarios** - Gestión de usuarios y roles
2. **sesiones** - Control de sesiones activas
3. **contactos** - Formularios de contacto web
4. **alertas_mineria** - Sistema principal de alertas
5. **actividades** - Log completo de auditoría
6. **notificaciones** - Notificaciones para usuarios
7. **configuracion_sistema** - Configuración dinámica
8. **archivos** - Gestión de documentos y evidencias
9. **tokens_seguridad** - Tokens de autenticación y recuperación
10. **Vistas optimizadas** - Para reportes y estadísticas

### Características Avanzadas
- ✅ **Foreign Keys** con CASCADE y SET NULL
- ✅ **Triggers automáticos** para códigos de alerta y auditoría
- ✅ **Índices optimizados** para todas las consultas frecuentes
- ✅ **Vistas especializadas** para reportes
- ✅ **UTF8MB4** para soporte completo de caracteres
- ✅ **Configuración inicial** con datos de ejemplo

### Migración desde Versiones Anteriores
Si vienes de múltiples archivos SQL dispersos:
1. **Backup actual:** `mysqldump -u root -p figger_energy_db > backup.sql`
2. **Eliminar BD:** `DROP DATABASE figger_energy_db;`
3. **Importar unificada:** `database/figger_energy_complete.sql`
4. **Verificar compatibilidad:** Usar `scripts/compatibility_views.sql` si es necesario

---

## 🔐 SEGURIDAD Y AUTENTICACIÓN

### Sistema de Contraseñas
- **Algoritmo:** bcrypt con factor de coste 10
- **Visualización:** Las contraseñas aparecen como `$2y$10$...` en phpMyAdmin (esto es normal)
- **Validación:** Usando `password_verify()` en PHP
- **Política:** Mínimo 8 caracteres con combinación de letras, números y símbolos

### Roles de Usuario
1. **Administrador (admin)**
   - Acceso completo al sistema
   - Gestión de usuarios
   - Configuración del sistema
   - Acceso a todos los reportes

2. **Empleado (empleado)**
   - Gestión de alertas asignadas
   - Investigación de casos
   - Reportes de campo
   - Actualización de estados

3. **Auditor (auditor)**
   - Revisión de casos resueltos
   - Generación de reportes
   - Auditoría de actividades
   - Supervisión de calidad

### Seguridad Implementada
- ✅ **Protección CSRF** con tokens únicos
- ✅ **Prevención SQL Injection** con prepared statements
- ✅ **Sanitización XSS** en todas las entradas
- ✅ **Sesiones seguras** con regeneración de ID
- ✅ **Control de intentos** de login con bloqueo temporal
- ✅ **Tokens seguros** para recuperación de contraseñas

### Gestión de Sesiones
- **Duración:** 2 horas de inactividad
- **Seguridad:** IP tracking y user-agent validation
- **Limpieza:** Automática de sesiones expiradas
- **Múltiples dispositivos:** Soportado con límites

---

## ⚙️ FUNCIONALIDADES DEL SISTEMA

### Sistema de Alertas de Minería Ilegal

#### Tipos de Alertas
1. **Satelital** - Detección automática por imágenes satellite
2. **Campo** - Reportes directos de personal en terreno
3. **Denuncia** - Reportes ciudadanos anónimos
4. **Automática** - Generadas por algoritmos de IA

#### Niveles de Riesgo
- 🔴 **Crítico** - Requiere acción inmediata
- 🟠 **Alto** - Prioridad alta, atención en 24h
- 🟡 **Medio** - Investigación dentro de 72h
- 🟢 **Bajo** - Verificación rutinaria

#### Estados de Alerta
- **Activa** - Recién detectada, pendiente de asignación
- **Investigando** - Asignada a empleado, en proceso
- **Verificada** - Confirmada como minería ilegal
- **Falsa** - Descartada tras investigación
- **Resuelta** - Caso cerrado con acciones tomadas

#### Funcionalidades Automáticas
- ✅ **Códigos únicos** automáticos (FE-2025-0001, FE-2025-0002...)
- ✅ **Geolocalización** con coordenadas GPS
- ✅ **Asignación automática** basada en ubicación
- ✅ **Escalamiento automático** por tiempo de resolución
- ✅ **Notificaciones** por email y push

### Dashboards Especializados

#### Dashboard Administrador
- 📊 Estadísticas generales del sistema
- 👥 Gestión completa de usuarios
- 🚨 Vista global de alertas
- 📋 Actividades recientes del sistema
- ⚙️ Configuración del sistema

#### Dashboard Empleado
- 📋 Mis alertas asignadas
- 📍 Alertas disponibles para tomar
- 📊 Mis estadísticas personales
- 📝 Registro de actividades
- 🛠️ Herramientas de campo

#### Dashboard Auditor
- 🔍 Casos para auditoría
- 📈 Estadísticas por empleado
- 📊 Análisis de rendimiento
- 📋 Actividades del sistema
- 📄 Generación de reportes

### Sistema de Contacto
- 📧 **Formulario web** con validación
- 🏷️ **Categorización** automática de consultas
- 📱 **Notificaciones** a administradores
- 📊 **Seguimiento** de estado y respuestas
- 🔄 **Integración** con sistema de tickets

---

## 👥 GESTIÓN DE USUARIOS

### Registro de Usuarios
- **Validación de email** corporativo
- **Aprobación manual** por administradores
- **Activación por tokens** seguros
- **Políticas de contraseñas** estrictas

### Eliminación Segura de Usuarios
El sistema maneja automáticamente las dependencias:
1. **Alertas asignadas** → Se reasignan como "sin asignar"
2. **Actividades registradas** → Se preservan para auditoría
3. **Sesiones activas** → Se terminan inmediatamente
4. **Tokens de seguridad** → Se invalidan automáticamente

### Foreign Keys Configuradas
```sql
✅ alertas_mineria.usuario_asignado → SET NULL
✅ actividades.usuario_id → CASCADE
✅ sesiones.usuario_id → CASCADE
✅ tokens_seguridad.usuario_id → CASCADE
```

### Gestión desde Dashboard
- **Crear usuarios** con roles específicos
- **Editar información** personal y permisos
- **Activar/Desactivar** cuentas
- **Eliminar usuarios** con manejo seguro de dependencias
- **Auditoría completa** de cambios

---

## 💻 API Y DESARROLLO

### Estructura del Proyecto
```
figger_energy/
├── app/
│   ├── controllers/        # Controladores MVC
│   ├── models/            # Modelos de datos
│   ├── middleware/        # Middleware de autenticación
│   └── services/          # Servicios de negocio
├── config/                # Configuraciones
├── database/              # Base de datos unificada
├── docs/                  # Documentación (ESTA CARPETA)
├── includes/              # Archivos de inclusión PHP
├── public/                # Archivos públicos y assets
├── scripts/               # Scripts de mantenimiento
├── storage/               # Almacenamiento (logs, cache, uploads)
└── views/                 # Vistas y templates
```

### Patrones de Desarrollo
- **MVC** para separación de responsabilidades
- **Repository Pattern** para acceso a datos
- **Dependency Injection** para servicios
- **PSR-4** para autoload de clases

### APIs Disponibles
- **Autenticación** - Login, logout, recuperación
- **Usuarios** - CRUD completo con roles
- **Alertas** - Gestión de alertas de minería
- **Actividades** - Log de auditoría
- **Configuración** - Parámetros del sistema

### Extensibilidad
El sistema está diseñado para fácil extensión:
- ✅ **Nuevos roles** de usuario
- ✅ **Tipos adicionales** de alertas
- ✅ **Integraciones** con sistemas externos
- ✅ **APIs REST** para aplicaciones móviles
- ✅ **Webhooks** para notificaciones

---

## 🛠️ SOLUCIÓN DE PROBLEMAS

### Problemas Comunes de Instalación

#### Error: "Base de datos no se puede crear"
**Solución:**
1. Verifica que MySQL esté ejecutándose en XAMPP
2. Reinicia el servicio MySQL
3. Verifica permisos de usuario root

#### Error: "Tabla no existe"
**Solución:**
1. Confirma que importaste el archivo completo
2. Verifica que no hay errores en el log de MySQL
3. Reimporta el archivo `figger_energy_complete.sql`

#### Error: "Cannot login"
**Solución:**
1. Verifica que la tabla usuarios tenga datos
2. Confirma que uses las credenciales correctas
3. Verifica la configuración de conexión en `includes/db.php`

### Problemas de Funcionamiento

#### Error: "Foreign key constraint fails"
**Solución:**
Este error ya está resuelto en la BD unificada. Si persiste:
1. Reimporta la base de datos unificada
2. Usa la interfaz de gestión en lugar de SQL directo

#### Error: "Access denied"
**Solución:**
1. Verifica tu rol de usuario
2. Contacta al administrador para verificar permisos
3. Limpia cache del navegador

#### Sesiones no funcionan
**Solución:**
1. Verifica que las cookies estén habilitadas
2. Confirma la configuración de sesiones en PHP
3. Verifica permisos de la carpeta `storage/sessions/`

### Logs y Depuración
- **Logs de PHP:** `storage/logs/php_errors.log`
- **Logs de aplicación:** `storage/logs/app.log`
- **Actividades de usuario:** Tabla `actividades` en BD
- **Debug mode:** Configurar `APP_DEBUG=true` en config

---

## 🔄 MIGRACIÓN Y ACTUALIZACIONES

### Migración desde Múltiples Archivos SQL

#### Problemas Resueltos Automáticamente
- ✅ **Tablas obsoletas** (login_attempts, logs_acceso, password_resets)
- ✅ **Referencias inconsistentes** (user_id vs usuario_id)
- ✅ **Foreign keys faltantes**
- ✅ **Triggers sin configurar**

#### Archivos Actualizados Automáticamente
```php
✅ php/auth.php        → Migrado a nuevas tablas
✅ php/config.php      → Sistema mejorado
✅ php/dashboard.php   → Logs actualizados
✅ php/logout.php      → Actividades mejoradas
```

#### Capa de Compatibilidad
Si necesitas mantener código legacy:
1. Importa la BD unificada
2. Ejecuta `scripts/compatibility_views.sql`
3. Las consultas antiguas seguirán funcionando

### Backup y Restauración
```bash
# Crear backup
mysqldump -u root -p figger_energy_db > backup_$(date +%Y%m%d).sql

# Restaurar backup
mysql -u root -p figger_energy_db < backup_20250724.sql
```

### Versionado
- **v1.0.0** - Sistema base con BD unificada
- **Futuras versiones** - Scripts de migración automática incluidos

---

## 🔧 MANTENIMIENTO

### Tareas de Mantenimiento Regular

#### Diarias
- ✅ **Verificar logs** de errores y actividades sospechosas
- ✅ **Monitorear alertas** críticas pendientes
- ✅ **Revisar sesiones** activas y limpiar expiradas

#### Semanales
- ✅ **Backup completo** de base de datos
- ✅ **Análisis de rendimiento** y optimización
- ✅ **Revisión de usuarios** inactivos
- ✅ **Actualización de configuraciones** si es necesario

#### Mensuales
- ✅ **Auditoría completa** del sistema
- ✅ **Análisis de estadísticas** y reportes
- ✅ **Optimización de base de datos**
- ✅ **Revisión de seguridad** y vulnerabilidades

### Scripts de Mantenimiento Disponibles
- `scripts/verificar_estructura_bd.php` - Verificación de integridad
- `scripts/migrar_compatibilidad.php` - Migración de código
- `scripts/regenerar_passwords.php` - Regeneración de contraseñas

### Monitoreo del Sistema
- **Actividades de usuario** - Tabla `actividades`
- **Sesiones activas** - Tabla `sesiones`
- **Rendimiento de alertas** - Vista `vista_estadisticas_usuario`
- **Estado del sistema** - Tabla `configuracion_sistema`

### Optimización de Rendimiento
- ✅ **Índices en todas las consultas** frecuentes
- ✅ **Queries optimizadas** con JOINs eficientes
- ✅ **Cache de configuración** para reducir consultas
- ✅ **Limpieza automática** de datos temporales

---

## 📞 SOPORTE Y CONTACTO

### Soporte Técnico
- **Email:** soporte@figgerenergy.gov.co
- **Teléfono:** +57 300 0000 000
- **Emergencias:** +57 018000001
- **Horario:** Lunes a Viernes, 8:00 AM - 6:00 PM

### Documentación Adicional
- **Manual de Usuario:** Disponible en el sistema
- **API Documentation:** `/docs/api/`
- **Changelog:** `/docs/changelog.md`
- **Security Policy:** `/docs/security.md`

### Reportar Problemas
1. **Recopilar información:**
   - Mensaje de error exacto
   - Pasos para reproducir
   - Logs relevantes
   - Navegador y versión

2. **Contactar soporte** con toda la información

3. **Seguimiento** por ticket asignado

---

## 📜 HISTORIAL DE VERSIONES

### v1.0.0 (Julio 2025)
- ✅ **Base de datos unificada** completamente implementada
- ✅ **Sistema de autenticación** con bcrypt y roles
- ✅ **Dashboard especializado** por rol de usuario
- ✅ **Sistema de alertas** completo con geolocalización
- ✅ **Migración automática** desde versiones anteriores
- ✅ **Documentación completa** consolidada
- ✅ **Seguridad mejorada** con foreign keys y triggers
- ✅ **Sistema de archivos** y gestión de documentos
- ✅ **Notificaciones** y configuración dinámica

### Próximas Versiones
- 📱 **API REST** para aplicaciones móviles
- 🗺️ **Mapas interactivos** con layers de información
- 📊 **Dashboard ejecutivo** con métricas avanzadas
- 🔗 **Integración** con sistemas gubernamentales
- 📧 **Sistema de emails** automáticos
- 🔄 **Workflows** automatizados para alertas

---

## 🏆 CONCLUSIÓN

Este sistema representa una solución integral para el monitoreo y control de actividades de minería ilegal en Colombia. Con una base de datos unificada, seguridad robusta y funcionalidades especializadas por rol, proporciona las herramientas necesarias para que Figger Energy SAS cumpla eficientemente su misión de proteger los recursos naturales del país.

La documentación aquí presentada cubre todos los aspectos necesarios para la instalación, configuración, uso y mantenimiento del sistema, garantizando una implementación exitosa y un funcionamiento óptimo a largo plazo.

**🚀 ¡Sistema listo para producción!**

---

*Documentación consolidada y actualizada - Julio 2025*  
*Figger Energy SAS - Protegiendo el futuro energético de Colombia*
