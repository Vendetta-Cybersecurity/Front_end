-- Base de datos para Figger Energy SAS
-- Archivo SQL para crear la estructura básica

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS figger_energy_db 
CHARACTER SET utf8 COLLATE utf8_spanish_ci;

-- Usar la base de datos
USE figger_energy_db;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'empleado', 'auditor') NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultima_conexion TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_rol (rol)
);

-- Tabla de contactos (formulario de contacto)
CREATE TABLE contactos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    asunto VARCHAR(200) NOT NULL,
    mensaje TEXT NOT NULL,
    fecha_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    leido BOOLEAN DEFAULT FALSE,
    respondido BOOLEAN DEFAULT FALSE,
    INDEX idx_fecha (fecha_envio),
    INDEX idx_leido (leido)
);

-- Tabla de alertas de minería ilegal (datos simulados)
CREATE TABLE alertas_mineria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ubicacion VARCHAR(200) NOT NULL,
    coordenadas VARCHAR(50),
    tipo_alerta ENUM('satelital', 'campo', 'denuncia') NOT NULL,
    nivel_riesgo ENUM('bajo', 'medio', 'alto', 'critico') NOT NULL,
    descripcion TEXT,
    estado ENUM('activa', 'investigando', 'verificada', 'falsa', 'resuelta') DEFAULT 'activa',
    fecha_deteccion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_asignado INT,
    fecha_resolucion TIMESTAMP NULL,
    observaciones TEXT,
    FOREIGN KEY (usuario_asignado) REFERENCES usuarios(id),
    INDEX idx_fecha (fecha_deteccion),
    INDEX idx_estado (estado),
    INDEX idx_nivel_riesgo (nivel_riesgo)
);

-- Tabla de actividades de usuario (registro de acciones)
CREATE TABLE actividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    accion VARCHAR(100) NOT NULL,
    descripcion TEXT,
    fecha_actividad TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_fecha (fecha_actividad)
);

-- Insertar usuario administrador por defecto
INSERT INTO usuarios (nombre, email, password, rol) VALUES 
('Administrador Sistema', 'admin@figgerenergy.gov.co', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Contraseña: password123

-- Insertar usuarios de ejemplo
INSERT INTO usuarios (nombre, email, password, rol) VALUES 
('Carlos Empleado', 'empleado@figgerenergy.gov.co', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'empleado'),
('Ana Auditora', 'auditor@figgerenergy.gov.co', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'auditor');

-- Insertar alertas de ejemplo
INSERT INTO alertas_mineria (ubicacion, coordenadas, tipo_alerta, nivel_riesgo, descripcion, estado, usuario_asignado) VALUES 
('Cordillera Oriental, Boyacá', '5.5330,-73.3669', 'satelital', 'alto', 'Detección satelital de actividad de excavación no autorizada en zona protegida', 'activa', 2),
('Sierra Nevada, Magdalena', '10.8311,-74.0478', 'campo', 'critico', 'Reporte de campo confirma extracción ilegal de minerales críticos', 'investigando', 2),
('Amazonía, Caquetá', '1.8653,-75.6066', 'denuncia', 'medio', 'Denuncia ciudadana sobre posible actividad minera en territorio protegido', 'activa', 3),
('Chocó, Quibdó', '5.6947,-76.6581', 'satelital', 'alto', 'Imágenes satelitales muestran deforestación asociada a minería', 'verificada', 2),
('Antioquia, Medellín', '6.2518,-75.5636', 'campo', 'bajo', 'Verificación de rutina sin hallazgos significativos', 'resuelta', 3);

-- Insertar actividades de ejemplo
INSERT INTO actividades (usuario_id, accion, descripcion) VALUES 
(1, 'login', 'Inicio de sesión en el sistema'),
(2, 'alerta_asignada', 'Se asignó alerta de Boyacá para investigación'),
(3, 'reporte_generado', 'Generó reporte de actividades del mes'),
(1, 'usuario_creado', 'Creó nuevo usuario en el sistema'),
(2, 'alerta_actualizada', 'Actualizó estado de alerta en Magdalena');

-- Crear índices adicionales para optimización
CREATE INDEX idx_alertas_fecha_estado ON alertas_mineria(fecha_deteccion, estado);
CREATE INDEX idx_actividades_usuario_fecha ON actividades(usuario_id, fecha_actividad);

-- Crear vistas útiles para reportes
CREATE VIEW vista_alertas_activas AS
SELECT 
    a.id,
    a.ubicacion,
    a.tipo_alerta,
    a.nivel_riesgo,
    a.estado,
    a.fecha_deteccion,
    u.nombre as usuario_asignado_nombre
FROM alertas_mineria a
LEFT JOIN usuarios u ON a.usuario_asignado = u.id
WHERE a.estado IN ('activa', 'investigando', 'verificada');

CREATE VIEW vista_estadisticas_usuario AS
SELECT 
    u.id,
    u.nombre,
    u.rol,
    COUNT(al.id) as alertas_asignadas,
    COUNT(ac.id) as actividades_realizadas,
    u.ultima_conexion
FROM usuarios u
LEFT JOIN alertas_mineria al ON u.id = al.usuario_asignado
LEFT JOIN actividades ac ON u.id = ac.usuario_id
WHERE u.activo = TRUE
GROUP BY u.id, u.nombre, u.rol, u.ultima_conexion;
