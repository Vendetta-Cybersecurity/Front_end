-- ==============================================
-- FIGGER ENERGY SAS - DATOS INICIALES
-- Población inicial de la base de datos
-- Archivo: 001_initial_seed.sql
-- ==============================================

USE figger_energy_db;

-- ==============================================
-- CONFIGURACIÓN DEL SISTEMA
-- ==============================================

INSERT INTO configuracion_sistema (clave, valor, tipo, categoria, descripcion, es_publico) VALUES
-- Configuración general
('app_name', 'Figger Energy SAS', 'string', 'general', 'Nombre de la aplicación', true),
('app_version', '1.0.0', 'string', 'general', 'Versión actual del sistema', true),
('app_description', 'Sistema de Monitoreo y Control de Minería Ilegal', 'string', 'general', 'Descripción de la aplicación', true),
('app_url', 'https://www.figgerenergy.gov.co', 'url', 'general', 'URL principal de la aplicación', true),

-- Configuración de contacto
('contact_email', 'contacto@figgerenergy.gov.co', 'email', 'contacto', 'Email principal de contacto', true),
('contact_phone', '+57 (1) 234-5678', 'string', 'contacto', 'Teléfono principal', true),
('contact_address', 'Carrera 7 #26-20, Bogotá, Colombia', 'string', 'contacto', 'Dirección física', true),

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

-- ==============================================
-- USUARIOS INICIALES
-- ==============================================

-- Usuario Administrador Principal
INSERT INTO usuarios (
    nombre, apellido, email, documento, telefono, password, rol, estado, 
    fecha_activacion, configuracion
) VALUES (
    'Administrador', 
    'Sistema', 
    'admin@figgerenergy.gov.co', 
    '1234567890', 
    '+57 300 123 4567',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password123
    'admin', 
    'activo',
    CURRENT_TIMESTAMP,
    '{"theme": "light", "language": "es", "notifications": {"email": true, "browser": true}}'
);

-- Usuario Empleado de Ejemplo
INSERT INTO usuarios (
    nombre, apellido, email, documento, telefono, password, rol, estado, 
    fecha_activacion, configuracion
) VALUES (
    'Carlos Andrés', 
    'Rodríguez Pérez', 
    'carlos.rodriguez@figgerenergy.gov.co', 
    '9876543210', 
    '+57 301 234 5678',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password123
    'empleado', 
    'activo',
    CURRENT_TIMESTAMP,
    '{"theme": "light", "language": "es", "notifications": {"email": true, "browser": true}}'
);

-- Usuario Auditor de Ejemplo
INSERT INTO usuarios (
    nombre, apellido, email, documento, telefono, password, rol, estado, 
    fecha_activacion, configuracion
) VALUES (
    'Ana María', 
    'González López', 
    'ana.gonzalez@figgerenergy.gov.co', 
    '1122334455', 
    '+57 302 345 6789',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password123
    'auditor', 
    'activo',
    CURRENT_TIMESTAMP,
    '{"theme": "light", "language": "es", "notifications": {"email": true, "browser": false}}'
);

-- ==============================================
-- ALERTAS DE EJEMPLO
-- ==============================================

INSERT INTO alertas_mineria (
    codigo, titulo, descripcion, ubicacion, departamento, municipio,
    coordenadas_lat, coordenadas_lng, tipo_alerta, categoria, nivel_riesgo, 
    prioridad, estado, confiabilidad, usuario_asignado, usuario_creador,
    fuente_informacion, observaciones
) VALUES 
(
    '2024-01-0001',
    'Detección Satelital - Cordillera Oriental',
    'Anomalía detectada mediante análisis de imágenes satelitales que sugiere actividad de excavación no autorizada en zona de páramo protegido.',
    'Cordillera Oriental, Vereda El Volcán',
    'Boyacá',
    'Villa de Leyva',
    5.6311, -73.5256,
    'satelital',
    'mineria_ilegal',
    'alto',
    'alta',
    'asignada',
    0.85,
    2, -- Carlos Rodríguez
    1, -- Admin
    'Sistema Automático - Landsat 8',
    'Cambio significativo en cobertura vegetal detectado en análisis multitemporal.'
),
(
    '2024-01-0002',
    'Reporte de Campo - Sierra Nevada',
    'Verificación en terreno confirma presencia de maquinaria pesada y evidencias de extracción mineral en área restringida del Parque Nacional.',
    'Sierra Nevada de Santa Marta, Sector San Lorenzo',
    'Magdalena',
    'Santa Marta',
    11.0886, -74.0278,
    'campo',
    'mineria_ilegal',
    'critico',
    'urgente',
    'verificada',
    0.95,
    3, -- Ana González
    2, -- Carlos Rodríguez
    'Patrulla de Campo - Parques Nacionales',
    'Se encontraron 3 retroexcavadoras y aproximadamente 20 trabajadores. Área afectada: 2.5 hectáreas.'
),
(
    '2024-01-0003',
    'Denuncia Ciudadana - Amazonía',
    'Reporte ciudadano sobre ruidos de maquinaria y movimiento de tierras en horas nocturnas en zona de reserva forestal.',
    'Resguardo Indígena Monochoa',
    'Caquetá',
    'Solano',
    1.7456, -75.1123,
    'denuncia',
    'deforestacion',
    'medio',
    'normal',
    'nueva',
    0.70,
    NULL,
    1, -- Admin
    'Denuncia Anónima - Línea 123',
    'Requiere verificación mediante sobrevuelo o patrulla terrestre.'
),
(
    '2024-01-0004',
    'Monitoreo Automático - Chocó',
    'Sistema de inteligencia artificial detecta patrones compatibles con minería aluvial en cauce del río San Juan.',
    'Cuenca del Río San Juan',
    'Chocó',
    'Istmina',
    5.1600, -76.6847,
    'automatica',
    'contaminacion',
    'alto',
    'alta',
    'investigando',
    0.78,
    2, -- Carlos Rodríguez
    1, -- Admin
    'Sistema IA - Análisis Fluvial',
    'Detección de sedimentos en suspensión y cambios en el curso natural del río.'
),
(
    '2024-01-0005',
    'Verificación Exitosa - Antioquia',
    'Seguimiento de alerta previa confirma cese de actividades ilegales tras intervención de autoridades.',
    'Bajo Cauca Antioqueño',
    'Antioquia',
    'Caucasia',
    7.9889, -75.1939,
    'campo',
    'mineria_ilegal',
    'bajo',
    'baja',
    'resuelta',
    1.00,
    2, -- Carlos Rodríguez
    2, -- Carlos Rodríguez
    'Verificación Conjunta - Policía Nacional',
    'Área restaurada conforme a plan de manejo ambiental. Seguimiento mensual programado.'
);

-- ==============================================
-- ACTIVIDADES INICIALES
-- ==============================================

INSERT INTO actividades (
    usuario_id, modulo, accion, entidad, entidad_id, descripcion, 
    ip_address, user_agent
) VALUES 
(1, 'sistema', 'instalacion', NULL, NULL, 'Instalación inicial del sistema Figger Energy SAS', '127.0.0.1', 'System Installation'),
(1, 'usuarios', 'crear', 'usuarios', 2, 'Creación de usuario empleado Carlos Rodríguez', '127.0.0.1', 'System Setup'),
(1, 'usuarios', 'crear', 'usuarios', 3, 'Creación de usuario auditor Ana González', '127.0.0.1', 'System Setup'),
(1, 'alertas', 'crear', 'alertas_mineria', 1, 'Creación de alerta satelital en Boyacá', '127.0.0.1', 'System Setup'),
(2, 'alertas', 'asignar', 'alertas_mineria', 1, 'Auto-asignación de alerta 2024-01-0001', '127.0.0.1', 'System Auto-Assignment'),
(1, 'alertas', 'crear', 'alertas_mineria', 2, 'Creación de alerta de campo en Magdalena', '127.0.0.1', 'System Setup'),
(3, 'alertas', 'verificar', 'alertas_mineria', 2, 'Verificación de alerta 2024-01-0002', '127.0.0.1', 'Field Verification'),
(1, 'alertas', 'crear', 'alertas_mineria', 3, 'Registro de denuncia ciudadana en Caquetá', '127.0.0.1', 'Citizen Report'),
(1, 'alertas', 'crear', 'alertas_mineria', 4, 'Detección automática en Chocó', '127.0.0.1', 'AI Detection'),
(2, 'alertas', 'resolver', 'alertas_mineria', 5, 'Resolución exitosa de alerta en Antioquia', '127.0.0.1', 'Field Resolution');

-- ==============================================
-- NOTIFICACIONES INICIALES
-- ==============================================

INSERT INTO notificaciones (
    usuario_id, tipo, titulo, mensaje, enlace
) VALUES 
(1, 'success', 'Sistema Instalado', 'El sistema Figger Energy SAS ha sido instalado exitosamente y está listo para su uso.', '/dashboard'),
(2, 'info', 'Bienvenido al Sistema', 'Su cuenta ha sido activada. Puede comenzar a gestionar alertas de minería ilegal.', '/dashboard'),
(3, 'info', 'Cuenta de Auditor Activa', 'Su perfil de auditor está configurado. Tiene acceso a funciones de verificación y compliance.', '/dashboard'),
(2, 'alert', 'Nueva Alerta Asignada', 'Se le ha asignado la alerta 2024-01-0001 en Boyacá para investigación.', '/alerts/1'),
(3, 'warning', 'Alerta Pendiente de Verificación', 'La alerta 2024-01-0002 en Magdalena requiere verificación de auditoría.', '/alerts/2'),
(1, 'info', 'Reporte Estadístico', 'Resumen semanal: 5 alertas procesadas, 1 resuelta, 4 en investigación.', '/reports');

-- ==============================================
-- CONTACTOS DE EJEMPLO
-- ==============================================

INSERT INTO contactos (
    nombre, email, telefono, asunto, mensaje, tipo_consulta, estado, prioridad
) VALUES 
(
    'María Fernanda Castillo',
    'maria.castillo@email.com',
    '+57 310 123 4567',
    'Denuncia de Actividad Sospechosa',
    'Vengo observando desde hace una semana actividad de maquinaria pesada en horas nocturnas cerca del río que pasa por nuestra vereda. Se escuchan ruidos de motores y se ve mucho polvo durante el día. Temo que sea minería ilegal.',
    'denuncia',
    'nuevo',
    'alta'
),
(
    'Jorge Luis Ramírez',
    'jramirez@municipio.gov.co',
    '+57 311 234 5678',
    'Solicitud de Información Técnica',
    'Desde la alcaldía necesitamos información sobre los protocolos de denuncia y seguimiento de alertas de minería ilegal para capacitar a nuestro personal.',
    'informacion',
    'leido',
    'normal'
),
(
    'Comunidad Indígena Wayuu',
    'cabildo.wayuu@gmail.com',
    '+57 312 345 6789',
    'Consulta sobre Territorio Ancestral',
    'Queremos conocer el estado de las investigaciones en nuestro territorio ancestral y cómo podemos participar en el monitoreo de actividades ilegales.',
    'general',
    'en_proceso',
    'alta'
);

-- ==============================================
-- CONFIGURACIÓN DE ÍNDICES ADICIONALES
-- ==============================================

-- Optimizar consultas frecuentes
CREATE INDEX idx_alertas_estado_fecha ON alertas_mineria(estado, fecha_deteccion);
CREATE INDEX idx_alertas_departamento_estado ON alertas_mineria(departamento, estado);
CREATE INDEX idx_actividades_modulo_fecha ON actividades(modulo, fecha_actividad);
CREATE INDEX idx_notificaciones_usuario_leida ON notificaciones(usuario_id, leida);

-- ==============================================
-- VISTAS ÚTILES PARA REPORTES
-- ==============================================

-- Vista de alertas activas con información completa
CREATE VIEW vista_alertas_activas AS
SELECT 
    a.id,
    a.codigo,
    a.titulo,
    a.ubicacion,
    a.departamento,
    a.municipio,
    a.tipo_alerta,
    a.categoria,
    a.nivel_riesgo,
    a.prioridad,
    a.estado,
    a.confiabilidad,
    a.fecha_deteccion,
    a.fecha_asignacion,
    ua.nombre as usuario_asignado_nombre,
    ua.email as usuario_asignado_email,
    uc.nombre as usuario_creador_nombre,
    DATEDIFF(NOW(), a.fecha_deteccion) as dias_pendientes
FROM alertas_mineria a
LEFT JOIN usuarios ua ON a.usuario_asignado = ua.id
LEFT JOIN usuarios uc ON a.usuario_creador = uc.id
WHERE a.estado IN ('nueva', 'asignada', 'investigando', 'verificada')
ORDER BY a.prioridad DESC, a.fecha_deteccion ASC;

-- Vista de estadísticas por usuario
CREATE VIEW vista_estadisticas_usuario AS
SELECT 
    u.id,
    u.nombre,
    u.apellido,
    u.email,
    u.rol,
    u.estado,
    COUNT(DISTINCT a.id) as alertas_asignadas,
    COUNT(DISTINCT CASE WHEN a.estado = 'resuelta' THEN a.id END) as alertas_resueltas,
    COUNT(DISTINCT ac.id) as actividades_realizadas,
    u.ultima_conexion,
    DATEDIFF(NOW(), u.ultima_conexion) as dias_sin_conexion
FROM usuarios u
LEFT JOIN alertas_mineria a ON u.id = a.usuario_asignado
LEFT JOIN actividades ac ON u.id = ac.usuario_id
WHERE u.estado = 'activo'
GROUP BY u.id, u.nombre, u.apellido, u.email, u.rol, u.estado, u.ultima_conexion;

-- Vista de resumen de contactos
CREATE VIEW vista_resumen_contactos AS
SELECT 
    DATE(fecha_envio) as fecha,
    tipo_consulta,
    estado,
    COUNT(*) as cantidad,
    COUNT(CASE WHEN prioridad = 'urgente' THEN 1 END) as urgentes,
    COUNT(CASE WHEN estado = 'nuevo' THEN 1 END) as pendientes
FROM contactos
WHERE fecha_envio >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY DATE(fecha_envio), tipo_consulta, estado
ORDER BY fecha DESC;

-- ==============================================
-- COMENTARIOS FINALES
-- ==============================================

-- Este archivo de seed incluye:
-- ✓ Configuración inicial del sistema
-- ✓ Usuarios de ejemplo con diferentes roles
-- ✓ Alertas de ejemplo que cubren diferentes escenarios
-- ✓ Actividades del sistema para audit trail
-- ✓ Notificaciones iniciales
-- ✓ Contactos de ejemplo
-- ✓ Vistas optimizadas para reportes
-- ✓ Índices adicionales para rendimiento

-- CREDENCIALES DE ACCESO:
-- Email: admin@figgerenergy.gov.co | Contraseña: password123
-- Email: carlos.rodriguez@figgerenergy.gov.co | Contraseña: password123  
-- Email: ana.gonzalez@figgerenergy.gov.co | Contraseña: password123

-- Todas las contraseñas deben ser cambiadas en producción.
