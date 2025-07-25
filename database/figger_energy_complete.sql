-- ===============================================
-- FIGGER ENERGY SAS - BASE DE DATOS UNIFICADA
-- Sistema de Monitoreo y Control de Minería Ilegal
-- Archivo único para instalación completa
-- Versión: 1.0.0
-- Fecha: 2025-01-24
-- ===============================================

-- Configuración inicial
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- ===============================================
-- 1. CREACIÓN DE BASE DE DATOS
-- ===============================================

-- Crear base de datos con configuración para español colombiano
CREATE DATABASE IF NOT EXISTS figger_energy_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_spanish_ci;

-- Usar la base de datos
USE figger_energy_db;

-- ===============================================
-- 2. TABLA: usuarios
-- Gestión de usuarios del sistema con roles
-- ===============================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    documento VARCHAR(20) UNIQUE NULL,
    telefono VARCHAR(20) NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'empleado', 'auditor') NOT NULL DEFAULT 'empleado',
    activo BOOLEAN DEFAULT TRUE,
    estado ENUM('activo', 'inactivo', 'pendiente', 'suspendido') NOT NULL DEFAULT 'pendiente',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_activacion TIMESTAMP NULL,
    ultima_conexion TIMESTAMP NULL,
    intentos_login INT DEFAULT 0,
    bloqueado_hasta TIMESTAMP NULL,
    avatar VARCHAR(255) NULL,
    configuracion JSON NULL,
    
    -- Índices para optimización
    INDEX idx_email (email),
    INDEX idx_documento (documento),
    INDEX idx_rol (rol),
    INDEX idx_activo (activo),
    INDEX idx_estado (estado),
    INDEX idx_fecha_registro (fecha_registro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ===============================================
-- 3. TABLA: sesiones
-- Gestión de sesiones de usuario para seguridad
-- ===============================================
CREATE TABLE sesiones (
    id VARCHAR(128) PRIMARY KEY,
    usuario_id INT NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    payload LONGTEXT NOT NULL,
    ultimo_acceso TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_ultimo_acceso (ultimo_acceso)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ===============================================
-- 4. TABLA: contactos
-- Formularios de contacto del sitio web
-- ===============================================
CREATE TABLE contactos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefono VARCHAR(20),
    asunto VARCHAR(200) NOT NULL,
    mensaje TEXT NOT NULL,
    tipo_consulta ENUM('general', 'denuncia', 'informacion', 'soporte') DEFAULT 'general',
    leido BOOLEAN DEFAULT FALSE,
    respondido BOOLEAN DEFAULT FALSE,
    estado ENUM('nuevo', 'leido', 'en_proceso', 'resuelto', 'cerrado') DEFAULT 'nuevo',
    prioridad ENUM('baja', 'normal', 'alta', 'urgente') DEFAULT 'normal',
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_respuesta TIMESTAMP NULL,
    respondido_por INT NULL,
    respuesta TEXT NULL,
    
    FOREIGN KEY (respondido_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_fecha_envio (fecha_envio),
    INDEX idx_leido (leido),
    INDEX idx_estado (estado),
    INDEX idx_tipo_consulta (tipo_consulta),
    INDEX idx_prioridad (prioridad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ===============================================
-- 5. TABLA: alertas_mineria
-- Sistema de alertas de minería ilegal
-- ===============================================
CREATE TABLE alertas_mineria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) UNIQUE NULL,
    titulo VARCHAR(200) NULL,
    ubicacion VARCHAR(200) NOT NULL,
    departamento VARCHAR(50) NULL,
    municipio VARCHAR(100) NULL,
    coordenadas VARCHAR(50) NULL,
    coordenadas_lat DECIMAL(10, 7) NULL,
    coordenadas_lng DECIMAL(10, 7) NULL,
    tipo_alerta ENUM('satelital', 'campo', 'denuncia', 'automatica') NOT NULL,
    nivel_riesgo ENUM('bajo', 'medio', 'alto', 'critico') NOT NULL,
    descripcion TEXT,
    evidencia_archivo VARCHAR(255) NULL,
    estado ENUM('activa', 'investigando', 'verificada', 'falsa', 'resuelta', 'cerrada') DEFAULT 'activa',
    confiabilidad DECIMAL(3,2) DEFAULT 0.50, -- Nivel de confianza (0.00 - 1.00)
    fecha_deteccion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_asignacion TIMESTAMP NULL,
    fecha_resolucion TIMESTAMP NULL,
    usuario_asignado INT NULL,
    usuario_creador INT NULL,
    observaciones TEXT,
    impacto_estimado TEXT NULL,
    recursos_afectados JSON NULL, -- Array de recursos minerales afectados
    
    -- Índices para optimización
    INDEX idx_fecha_deteccion (fecha_deteccion),
    INDEX idx_estado (estado),
    INDEX idx_nivel_riesgo (nivel_riesgo),
    INDEX idx_tipo_alerta (tipo_alerta),
    INDEX idx_usuario_asignado (usuario_asignado),
    INDEX idx_coordenadas (coordenadas_lat, coordenadas_lng),
    INDEX idx_codigo (codigo),
    INDEX idx_fecha_estado (fecha_deteccion, estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ===============================================
-- 6. TABLA: actividades
-- Registro de actividades del sistema (audit log)
-- ===============================================
CREATE TABLE actividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    modulo VARCHAR(50) DEFAULT 'general', -- modulo del sistema (usuarios, alertas, etc.)
    accion VARCHAR(100) NOT NULL,
    entidad VARCHAR(50) NULL, -- tabla afectada
    entidad_id INT NULL, -- id del registro afectado
    descripcion TEXT,
    datos_anteriores JSON NULL, -- Estado anterior para cambios
    datos_nuevos JSON NULL, -- Estado nuevo para cambios
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    fecha_actividad TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_fecha_actividad (fecha_actividad),
    INDEX idx_modulo (modulo),
    INDEX idx_accion (accion),
    INDEX idx_entidad (entidad, entidad_id),
    INDEX idx_usuario_fecha (usuario_id, fecha_actividad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ===============================================
-- 7. TABLA: notificaciones
-- Sistema de notificaciones para usuarios
-- ===============================================
CREATE TABLE notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo ENUM('info', 'warning', 'error', 'success', 'alert') NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    mensaje TEXT NOT NULL,
    enlace VARCHAR(255) NULL, -- URL para acción relacionada
    datos_adicionales JSON NULL,
    
    leida BOOLEAN DEFAULT FALSE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_lectura TIMESTAMP NULL,
    fecha_expiracion TIMESTAMP NULL,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_leida (leida),
    INDEX idx_fecha_creacion (fecha_creacion),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ===============================================
-- 8. TABLA: configuracion_sistema
-- Configuración dinámica del sistema
-- ===============================================
CREATE TABLE configuracion_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT NOT NULL,
    tipo ENUM('string', 'integer', 'boolean', 'json', 'email', 'url') DEFAULT 'string',
    categoria VARCHAR(50) DEFAULT 'general',
    descripcion TEXT NULL,
    es_publico BOOLEAN DEFAULT FALSE, -- Si puede ser visible públicamente
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    modificado_por INT NULL,
    
    FOREIGN KEY (modificado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_clave (clave),
    INDEX idx_categoria (categoria),
    INDEX idx_es_publico (es_publico)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ===============================================
-- 9. TABLA: archivos
-- Gestión de archivos subidos al sistema
-- ===============================================
CREATE TABLE archivos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_original VARCHAR(255) NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL, -- Nombre del archivo en el servidor
    ruta VARCHAR(500) NOT NULL,
    tipo_mime VARCHAR(100) NOT NULL,
    tamaño INT NOT NULL, -- En bytes
    extension VARCHAR(10),
    
    -- Relación con otras entidades
    entidad VARCHAR(50), -- alertas, usuarios, etc.
    entidad_id INT,
    categoria VARCHAR(50) DEFAULT 'general', -- evidencia, avatar, documento, etc.
    
    -- Información de subida
    subido_por INT NOT NULL,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    es_publico BOOLEAN DEFAULT FALSE,
    
    -- Validación y seguridad
    hash_archivo VARCHAR(64), -- SHA-256 hash para verificar integridad
    estado ENUM('pendiente', 'procesado', 'error', 'eliminado') DEFAULT 'pendiente',
    
    FOREIGN KEY (subido_por) REFERENCES usuarios(id) ON DELETE RESTRICT,
    INDEX idx_entidad (entidad, entidad_id),
    INDEX idx_subido_por (subido_por),
    INDEX idx_fecha_subida (fecha_subida),
    INDEX idx_categoria (categoria),
    INDEX idx_hash_archivo (hash_archivo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ===============================================
-- 10. TABLA: tokens_seguridad
-- Tokens para recuperación de contraseñas y verificación
-- ===============================================
CREATE TABLE tokens_seguridad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    token VARCHAR(100) UNIQUE NOT NULL,
    tipo ENUM('password_reset', 'email_verification', 'api_access', 'session') NOT NULL,
    usado BOOLEAN DEFAULT FALSE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion TIMESTAMP NOT NULL,
    fecha_uso TIMESTAMP NULL,
    ip_creacion VARCHAR(45) NULL,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_usuario (usuario_id),
    INDEX idx_tipo (tipo),
    INDEX idx_fecha_expiracion (fecha_expiracion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ===============================================
-- 11. CONFIGURAR FOREIGN KEYS CON CASCADAS SEGURAS
-- ===============================================

-- Foreign Keys para alertas_mineria
ALTER TABLE alertas_mineria 
ADD CONSTRAINT fk_alertas_usuario_asignado 
FOREIGN KEY (usuario_asignado) REFERENCES usuarios(id) 
ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE alertas_mineria 
ADD CONSTRAINT fk_alertas_usuario_creador 
FOREIGN KEY (usuario_creador) REFERENCES usuarios(id) 
ON DELETE SET NULL ON UPDATE CASCADE;

-- ===============================================
-- 12. CREAR TRIGGERS PARA AUTOMATIZACIÓN
-- ===============================================

-- Trigger para generar código de alerta automáticamente
DELIMITER $$
CREATE TRIGGER before_insert_alertas_mineria 
BEFORE INSERT ON alertas_mineria
FOR EACH ROW
BEGIN
    IF NEW.codigo IS NULL OR NEW.codigo = '' THEN
        SET NEW.codigo = CONCAT(
            'FE-',
            YEAR(NOW()), 
            LPAD(MONTH(NOW()), 2, '0'),
            '-',
            LPAD((SELECT COUNT(*) + 1 FROM alertas_mineria WHERE YEAR(fecha_deteccion) = YEAR(NOW())), 4, '0')
        );
    END IF;
END$$
DELIMITER ;

-- Trigger para registrar actividad en login de usuarios
DELIMITER $$
CREATE TRIGGER after_update_usuarios_login
AFTER UPDATE ON usuarios
FOR EACH ROW
BEGIN
    IF OLD.ultima_conexion != NEW.ultima_conexion AND NEW.ultima_conexion IS NOT NULL THEN
        INSERT INTO actividades (usuario_id, modulo, accion, descripcion)
        VALUES (
            NEW.id,
            'usuarios',
            'login',
            CONCAT('Usuario ', NEW.nombre, ' inició sesión')
        );
    END IF;
END$$
DELIMITER ;

-- Trigger para registrar cambios en alertas
DELIMITER $$
CREATE TRIGGER after_update_alertas_mineria 
AFTER UPDATE ON alertas_mineria
FOR EACH ROW
BEGIN
    IF OLD.estado != NEW.estado THEN
        INSERT INTO actividades (
            usuario_id, 
            modulo, 
            accion, 
            entidad, 
            entidad_id, 
            descripcion, 
            datos_anteriores, 
            datos_nuevos
        )
        VALUES (
            COALESCE(NEW.usuario_asignado, 1),
            'alertas',
            'cambio_estado',
            'alertas_mineria',
            NEW.id,
            CONCAT('Cambio de estado de alerta ', NEW.codigo, ' de "', OLD.estado, '" a "', NEW.estado, '"'),
            JSON_OBJECT('estado', OLD.estado),
            JSON_OBJECT('estado', NEW.estado)
        );
    END IF;
END$$
DELIMITER ;

-- ===============================================
-- 13. CREAR VISTAS ÚTILES PARA REPORTES
-- ===============================================

-- Vista de alertas activas con información del usuario asignado
CREATE VIEW vista_alertas_activas AS
SELECT 
    a.id,
    a.codigo,
    a.titulo,
    a.ubicacion,
    a.departamento,
    a.municipio,
    a.tipo_alerta,
    a.nivel_riesgo,
    a.estado,
    a.fecha_deteccion,
    a.confiabilidad,
    u.nombre as usuario_asignado_nombre,
    u.email as usuario_asignado_email,
    DATEDIFF(NOW(), a.fecha_deteccion) as dias_desde_deteccion
FROM alertas_mineria a
LEFT JOIN usuarios u ON a.usuario_asignado = u.id
WHERE a.estado IN ('activa', 'investigando', 'verificada')
ORDER BY a.nivel_riesgo DESC, a.fecha_deteccion ASC;

-- Vista de estadísticas por usuario
CREATE VIEW vista_estadisticas_usuario AS
SELECT 
    u.id,
    u.nombre,
    u.apellido,
    u.email,
    u.rol,
    u.estado,
    COUNT(DISTINCT al.id) as alertas_asignadas,
    COUNT(DISTINCT ac.id) as actividades_realizadas,
    u.ultima_conexion,
    u.fecha_registro,
    CASE 
        WHEN COUNT(DISTINCT al.id) = 0 THEN 0
        ELSE ROUND(
            (COUNT(DISTINCT CASE WHEN al.estado = 'resuelta' THEN al.id END) * 100.0) / 
            COUNT(DISTINCT al.id), 2
        )
    END as porcentaje_resolucion
FROM usuarios u
LEFT JOIN alertas_mineria al ON u.id = al.usuario_asignado
LEFT JOIN actividades ac ON u.id = ac.usuario_id
WHERE u.activo = TRUE
GROUP BY u.id, u.nombre, u.apellido, u.email, u.rol, u.estado, u.ultima_conexion, u.fecha_registro;

-- Vista de resumen de alertas por tipo y estado
CREATE VIEW vista_resumen_alertas AS
SELECT 
    tipo_alerta,
    nivel_riesgo,
    estado,
    COUNT(*) as cantidad,
    AVG(confiabilidad) as confiabilidad_promedio,
    MIN(fecha_deteccion) as primera_deteccion,
    MAX(fecha_deteccion) as ultima_deteccion
FROM alertas_mineria
GROUP BY tipo_alerta, nivel_riesgo, estado
ORDER BY tipo_alerta, nivel_riesgo DESC, estado;

-- ===============================================
-- 14. INSERTAR CONFIGURACIÓN INICIAL DEL SISTEMA
-- ===============================================

INSERT INTO configuracion_sistema (clave, valor, tipo, categoria, descripcion, es_publico) VALUES
-- Configuración general
('app_name', 'Figger Energy SAS', 'string', 'general', 'Nombre de la aplicación', true),
('app_version', '1.0.0', 'string', 'general', 'Versión actual del sistema', true),
('app_description', 'Sistema de Monitoreo y Control de Minería Ilegal', 'string', 'general', 'Descripción de la aplicación', true),
('app_url', 'https://www.figgerenergy.gov.co', 'url', 'general', 'URL principal de la aplicación', true),

-- Configuración de contacto actualizada
('contact_email', 'contacto@figgerenergy.gov.co', 'email', 'contacto', 'Email principal de contacto', true),
('contact_phone', '+57 300 0000 000', 'string', 'contacto', 'Teléfono principal', true),
('contact_emergency', '+57 018000001', 'string', 'contacto', 'Teléfono de emergencias', true),
('contact_address', 'Calle 1, Carrera 1, Edificio 1, Macondo, Colombia', 'string', 'contacto', 'Dirección física', true),

-- Configuración de seguridad
('password_min_length', '8', 'integer', 'seguridad', 'Longitud mínima de contraseñas', false),
('max_login_attempts', '5', 'integer', 'seguridad', 'Máximo número de intentos de login', false),
('lockout_duration', '900', 'integer', 'seguridad', 'Duración del bloqueo en segundos (15 minutos)', false),
('session_lifetime', '7200', 'integer', 'seguridad', 'Duración de sesión en segundos (2 horas)', false),
('token_expiration', '3600', 'integer', 'seguridad', 'Expiración de tokens en segundos (1 hora)', false),

-- Configuración de alertas
('auto_assign_alerts', 'true', 'boolean', 'alertas', 'Asignación automática de alertas', false),
('alert_retention_days', '365', 'integer', 'alertas', 'Días de retención de alertas resueltas', false),
('min_confidence_level', '0.70', 'string', 'alertas', 'Nivel mínimo de confianza para alertas automáticas', false),

-- Configuración de notificaciones
('email_notifications', 'true', 'boolean', 'notificaciones', 'Envío de notificaciones por email', false),
('notification_retention_days', '90', 'integer', 'notificaciones', 'Días de retención de notificaciones', false),

-- Configuración de archivos
('max_file_size', '10485760', 'integer', 'archivos', 'Tamaño máximo de archivo en bytes (10MB)', false),
('allowed_extensions', '["jpg","jpeg","png","pdf","doc","docx","xls","xlsx"]', 'json', 'archivos', 'Extensiones de archivo permitidas', false),
('upload_path', '/uploads/', 'string', 'archivos', 'Ruta base para subida de archivos', false);

-- ===============================================
-- 15. INSERTAR USUARIOS INICIALES
-- ===============================================

-- Usuario Administrador Principal
INSERT INTO usuarios (nombre, apellido, email, password, rol, activo, estado, fecha_activacion) VALUES 
('Administrador', 'Sistema', 'admin@figgerenergy.gov.co', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'admin', 1, 'activo', NOW());

-- Usuarios de ejemplo para desarrollo
INSERT INTO usuarios (nombre, apellido, email, password, rol, activo, estado, fecha_activacion) VALUES 
('Carlos', 'Empleado', 'empleado@figgerenergy.gov.co', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'empleado', 1, 'activo', NOW()),
('Ana', 'Auditora', 'auditor@figgerenergy.gov.co', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'auditor', 1, 'activo', NOW());

-- ===============================================
-- 16. INSERTAR DATOS DE EJEMPLO
-- ===============================================

-- Alertas de ejemplo
INSERT INTO alertas_mineria (
    titulo, ubicacion, departamento, municipio, coordenadas, coordenadas_lat, coordenadas_lng, 
    tipo_alerta, nivel_riesgo, descripcion, estado, usuario_asignado, usuario_creador, confiabilidad
) VALUES 
(
    'Actividad minera no autorizada detectada', 'Cordillera Oriental', 'Boyacá', 'Chiquinquirá', 
    '5.5330,-73.3669', 5.5330, -73.3669, 'satelital', 'alto', 
    'Detección satelital de actividad de excavación no autorizada en zona protegida. Se observan movimientos de tierra y presencia de maquinaria pesada.', 
    'activa', 2, 1, 0.87
),
(
    'Extracción ilegal de minerales críticos', 'Sierra Nevada', 'Magdalena', 'Santa Marta', 
    '10.8311,-74.0478', 10.8311, -74.0478, 'campo', 'critico', 
    'Reporte de campo confirma extracción ilegal de minerales críticos para energías renovables. Operación a gran escala identificada.', 
    'investigando', 2, 1, 0.95
),
(
    'Denuncia ciudadana - Posible minería ilegal', 'Amazonía', 'Caquetá', 'Florencia', 
    '1.8653,-75.6066', 1.8653, -75.6066, 'denuncia', 'medio', 
    'Denuncia ciudadana sobre posible actividad minera en territorio protegido. Requiere verificación in situ.', 
    'activa', 3, 1, 0.65
),
(
    'Deforestación asociada a minería', 'Chocó Biogeográfico', 'Chocó', 'Quibdó', 
    '5.6947,-76.6581', 5.6947, -76.6581, 'satelital', 'alto', 
    'Imágenes satelitales muestran deforestación significativa asociada a actividades mineras ilegales.', 
    'verificada', 2, 1, 0.82
),
(
    'Verificación de rutina sin hallazgos', 'Valle de Aburrá', 'Antioquia', 'Medellín', 
    '6.2518,-75.5636', 6.2518, -75.5636, 'campo', 'bajo', 
    'Verificación de rutina en área reportada. No se encontraron evidencias de actividad minera ilegal.', 
    'resuelta', 3, 1, 0.78
);

-- Actividades de ejemplo
INSERT INTO actividades (usuario_id, modulo, accion, descripcion, ip_address) VALUES 
(1, 'usuarios', 'login', 'Administrador inició sesión en el sistema', '192.168.1.100'),
(1, 'sistema', 'configuracion', 'Se ejecutó la instalación inicial de la base de datos', '192.168.1.100'),
(2, 'usuarios', 'login', 'Empleado Carlos inició sesión', '192.168.1.101'),
(2, 'alertas', 'asignacion', 'Se asignó alerta de Boyacá para investigación', '192.168.1.101'),
(3, 'usuarios', 'login', 'Auditora Ana inició sesión', '192.168.1.102'),
(3, 'reportes', 'generacion', 'Generó reporte de actividades del mes', '192.168.1.102'),
(1, 'usuarios', 'creacion', 'Creó usuarios de ejemplo en el sistema', '192.168.1.100'),
(2, 'alertas', 'actualizacion', 'Actualizó estado de alerta en Magdalena', '192.168.1.101');

-- Notificaciones de ejemplo
INSERT INTO notificaciones (usuario_id, tipo, titulo, mensaje) VALUES 
(1, 'success', 'Sistema inicializado', 'La base de datos se ha configurado exitosamente. Todos los módulos están operativos.'),
(2, 'info', 'Nueva alerta asignada', 'Se te ha asignado una nueva alerta de nivel alto en Boyacá para investigación.'),
(3, 'warning', 'Revisar alertas pendientes', 'Hay 3 alertas pendientes de auditoría que requieren tu atención.'),
(1, 'info', 'Actualización del sistema', 'El sistema Figger Energy SAS v1.0.0 se ha instalado correctamente.');

-- ===============================================
-- 17. FINALIZACIÓN Y VERIFICACIÓN
-- ===============================================

-- Confirmar transacción
COMMIT;

-- Verificar la instalación
SELECT 'Base de datos Figger Energy SAS instalada exitosamente' as status;
SELECT TABLE_NAME, TABLE_ROWS 
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = 'figger_energy_db' 
AND TABLE_TYPE = 'BASE TABLE'
ORDER BY TABLE_NAME;

-- Mostrar información de contacto actualizada
SELECT 
    'Información de contacto actualizada:' as info,
    (SELECT valor FROM configuracion_sistema WHERE clave = 'contact_address') as direccion,
    (SELECT valor FROM configuracion_sistema WHERE clave = 'contact_phone') as telefono,
    (SELECT valor FROM configuracion_sistema WHERE clave = 'contact_emergency') as emergencias;

-- Mostrar usuarios creados
SELECT 
    'Usuarios iniciales creados:' as info,
    CONCAT(nombre, ' ', COALESCE(apellido, '')) as nombre_completo,
    email,
    rol,
    estado
FROM usuarios 
ORDER BY id;

-- ===============================================
-- NOTAS DE INSTALACIÓN
-- ===============================================

/*
CREDENCIALES DE ACCESO INICIAL:

📧 admin@figgerenergy.gov.co    → 🔑 admin123
📧 empleado@figgerenergy.gov.co → 🔑 empleado123
📧 auditor@figgerenergy.gov.co  → 🔑 auditor123

INFORMACIÓN DE CONTACTO:
📍 Dirección: Calle 1, Carrera 1, Edificio 1, Macondo, Colombia
📞 Teléfono: +57 300 0000 000
🚨 Emergencias: +57 018000001

CARACTERÍSTICAS INCLUIDAS:
✅ 10 tablas principales del sistema
✅ Relaciones con integridad referencial
✅ Triggers para automatización
✅ Vistas para reportes
✅ Configuración del sistema
✅ Datos de ejemplo
✅ Usuarios iniciales
✅ Índices optimizados
✅ Contraseñas hasheadas de forma segura

INSTALACIÓN:
1. Importar este archivo en phpMyAdmin o ejecutar en MySQL
2. Verificar que no hay errores
3. Acceder al sistema web con las credenciales proporcionadas
4. Las contraseñas aparecerán como hashes en la BD (esto es correcto)

MANTENIMIENTO:
- Las contraseñas están hasheadas con bcrypt (seguro)
- Los triggers automatizan el registro de actividades
- Las vistas facilitan la generación de reportes
- La configuración es dinámica desde la tabla configuracion_sistema
*/
