-- ==============================================
-- FIGGER ENERGY SAS - DATABASE MIGRATION
-- Creación de la estructura principal de la base de datos
-- Archivo: 001_create_initial_tables.sql
-- ==============================================

-- Crear base de datos con configuración para español
CREATE DATABASE IF NOT EXISTS figger_energy_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_spanish_ci;

-- Usar la base de datos
USE figger_energy_db;

-- ==============================================
-- TABLA: usuarios
-- Gestión de usuarios del sistema con roles
-- ==============================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    documento VARCHAR(20) UNIQUE NOT NULL,
    telefono VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'empleado', 'auditor') NOT NULL DEFAULT 'empleado',
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
    INDEX idx_estado (estado),
    INDEX idx_fecha_registro (fecha_registro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ==============================================
-- TABLA: sesiones
-- Gestión de sesiones de usuario para seguridad
-- ==============================================
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

-- ==============================================
-- TABLA: contactos
-- Formularios de contacto del sitio web
-- ==============================================
CREATE TABLE contactos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefono VARCHAR(20),
    asunto VARCHAR(200) NOT NULL,
    mensaje TEXT NOT NULL,
    tipo_consulta ENUM('general', 'denuncia', 'informacion', 'soporte') DEFAULT 'general',
    estado ENUM('nuevo', 'leido', 'en_proceso', 'resuelto', 'cerrado') DEFAULT 'nuevo',
    prioridad ENUM('baja', 'normal', 'alta', 'urgente') DEFAULT 'normal',
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_respuesta TIMESTAMP NULL,
    respondido_por INT NULL,
    respuesta TEXT NULL,
    
    FOREIGN KEY (respondido_por) REFERENCES usuarios(id) ON SET NULL,
    INDEX idx_fecha_envio (fecha_envio),
    INDEX idx_estado (estado),
    INDEX idx_tipo_consulta (tipo_consulta),
    INDEX idx_prioridad (prioridad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ==============================================
-- TABLA: alertas_mineria
-- Sistema de alertas de minería ilegal
-- ==============================================
CREATE TABLE alertas_mineria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) UNIQUE NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    descripcion TEXT,
    ubicacion VARCHAR(200) NOT NULL,
    departamento VARCHAR(50) NOT NULL,
    municipio VARCHAR(100) NOT NULL,
    coordenadas_lat DECIMAL(10, 8),
    coordenadas_lng DECIMAL(11, 8),
    
    -- Clasificación de la alerta
    tipo_alerta ENUM('satelital', 'campo', 'denuncia', 'automatica') NOT NULL,
    categoria ENUM('mineria_ilegal', 'deforestacion', 'contaminacion', 'otros') NOT NULL,
    nivel_riesgo ENUM('bajo', 'medio', 'alto', 'critico') NOT NULL,
    prioridad ENUM('baja', 'normal', 'alta', 'urgente') NOT NULL DEFAULT 'normal',
    
    -- Estado y seguimiento
    estado ENUM('nueva', 'asignada', 'investigando', 'verificada', 'falsa_alarma', 'resuelta', 'cerrada') DEFAULT 'nueva',
    confiabilidad DECIMAL(3,2) DEFAULT 0.00, -- Porcentaje de confiabilidad 0.00-1.00
    
    -- Fechas importantes
    fecha_deteccion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_asignacion TIMESTAMP NULL,
    fecha_verificacion TIMESTAMP NULL,
    fecha_resolucion TIMESTAMP NULL,
    fecha_cierre TIMESTAMP NULL,
    
    -- Asignación y seguimiento
    usuario_asignado INT NULL,
    usuario_verificador INT NULL,
    usuario_creador INT NOT NULL,
    observaciones TEXT,
    evidencias JSON NULL, -- URLs de archivos de evidencia
    
    -- Metadatos adicionales
    fuente_informacion VARCHAR(100),
    numero_referencia VARCHAR(50),
    datos_adicionales JSON NULL,
    
    FOREIGN KEY (usuario_asignado) REFERENCES usuarios(id) ON SET NULL,
    FOREIGN KEY (usuario_verificador) REFERENCES usuarios(id) ON SET NULL,
    FOREIGN KEY (usuario_creador) REFERENCES usuarios(id),
    
    INDEX idx_codigo (codigo),
    INDEX idx_fecha_deteccion (fecha_deteccion),
    INDEX idx_estado (estado),
    INDEX idx_nivel_riesgo (nivel_riesgo),
    INDEX idx_tipo_alerta (tipo_alerta),
    INDEX idx_categoria (categoria),
    INDEX idx_usuario_asignado (usuario_asignado),
    INDEX idx_departamento (departamento),
    INDEX idx_coordenadas (coordenadas_lat, coordenadas_lng)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ==============================================
-- TABLA: actividades
-- Registro de actividades del sistema (audit log)
-- ==============================================
CREATE TABLE actividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    modulo VARCHAR(50) NOT NULL, -- login, alertas, usuarios, reportes, etc.
    accion VARCHAR(100) NOT NULL, -- crear, editar, eliminar, login, logout, etc.
    entidad VARCHAR(50), -- tabla o entidad afectada
    entidad_id INT, -- ID del registro afectado
    descripcion TEXT,
    datos_anteriores JSON NULL, -- Estado anterior del registro
    datos_nuevos JSON NULL, -- Estado nuevo del registro
    
    -- Información de la sesión
    ip_address VARCHAR(45),
    user_agent TEXT,
    session_id VARCHAR(128),
    
    fecha_actividad TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_fecha_actividad (fecha_actividad),
    INDEX idx_modulo (modulo),
    INDEX idx_accion (accion),
    INDEX idx_entidad (entidad, entidad_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ==============================================
-- TABLA: configuracion_sistema
-- Configuraciones globales del sistema
-- ==============================================
CREATE TABLE configuracion_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT,
    tipo ENUM('string', 'integer', 'boolean', 'json', 'email', 'url') DEFAULT 'string',
    categoria VARCHAR(50) DEFAULT 'general',
    descripcion TEXT,
    es_publico BOOLEAN DEFAULT FALSE, -- Si puede ser leído sin autenticación
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    actualizado_por INT,
    
    FOREIGN KEY (actualizado_por) REFERENCES usuarios(id) ON SET NULL,
    INDEX idx_clave (clave),
    INDEX idx_categoria (categoria),
    INDEX idx_es_publico (es_publico)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ==============================================
-- TABLA: notificaciones
-- Sistema de notificaciones para usuarios
-- ==============================================
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

-- ==============================================
-- TABLA: archivos
-- Gestión de archivos subidos al sistema
-- ==============================================
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
    
    FOREIGN KEY (subido_por) REFERENCES usuarios(id),
    INDEX idx_entidad (entidad, entidad_id),
    INDEX idx_subido_por (subido_por),
    INDEX idx_fecha_subida (fecha_subida),
    INDEX idx_categoria (categoria),
    INDEX idx_hash_archivo (hash_archivo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ==============================================
-- TABLA: tokens_seguridad
-- Tokens para recuperación de contraseñas y verificación
-- ==============================================
CREATE TABLE tokens_seguridad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    token VARCHAR(100) UNIQUE NOT NULL,
    tipo ENUM('password_reset', 'email_verification', 'api_access') NOT NULL,
    usado BOOLEAN DEFAULT FALSE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion TIMESTAMP NOT NULL,
    fecha_uso TIMESTAMP NULL,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_usuario (usuario_id),
    INDEX idx_tipo (tipo),
    INDEX idx_fecha_expiracion (fecha_expiracion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;

-- ==============================================
-- CREAR TRIGGERS PARA AUTOMATIZACIÓN
-- ==============================================

-- Trigger para generar código de alerta automáticamente
DELIMITER $$
CREATE TRIGGER before_insert_alertas_mineria 
BEFORE INSERT ON alertas_mineria
FOR EACH ROW
BEGIN
    IF NEW.codigo IS NULL OR NEW.codigo = '' THEN
        SET NEW.codigo = CONCAT(
            YEAR(NOW()), 
            '-',
            LPAD(MONTH(NOW()), 2, '0'),
            '-',
            LPAD((SELECT COALESCE(MAX(id), 0) + 1 FROM alertas_mineria), 4, '0')
        );
    END IF;
END$$
DELIMITER ;

-- Trigger para registrar actividades automáticamente en alertas
DELIMITER $$
CREATE TRIGGER after_update_alertas_mineria 
AFTER UPDATE ON alertas_mineria
FOR EACH ROW
BEGIN
    IF OLD.estado != NEW.estado THEN
        INSERT INTO actividades (usuario_id, modulo, accion, entidad, entidad_id, descripcion, datos_anteriores, datos_nuevos)
        VALUES (
            NEW.usuario_asignado,
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

-- ==============================================
-- COMENTARIOS FINALES
-- ==============================================

-- Esta migración crea la estructura base del sistema Figger Energy SAS
-- Incluye todas las tablas necesarias para:
-- - Gestión de usuarios y roles
-- - Sistema de alertas de minería ilegal
-- - Registro de actividades (audit log)
-- - Notificaciones
-- - Gestión de archivos
-- - Configuración del sistema
-- - Seguridad y tokens

-- La estructura está optimizada para rendimiento con índices apropiados
-- y incluye constraints de integridad referencial.
