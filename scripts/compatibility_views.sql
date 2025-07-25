
-- =============================================
-- CAPA DE COMPATIBILIDAD PARA MIGRACIÓN
-- Vistas que emulan las tablas antiguas
-- =============================================

-- Vista de compatibilidad para login_attempts
CREATE OR REPLACE VIEW login_attempts AS
SELECT 
    CONCAT(ip_address, '_', DATE(fecha_actividad)) as identifier,
    fecha_actividad as attempted_at,
    ip_address
FROM actividades 
WHERE accion = 'intento_login' 
AND fecha_actividad > DATE_SUB(NOW(), INTERVAL 1 DAY);

-- Vista de compatibilidad para logs_acceso  
CREATE OR REPLACE VIEW logs_acceso AS
SELECT 
    id,
    usuario_id as user_id,
    accion,
    ip_address,
    descripcion as user_agent,
    'exito' as resultado,
    fecha_actividad as timestamp
FROM actividades;

-- Vista de compatibilidad para password_resets
CREATE OR REPLACE VIEW password_resets AS
SELECT 
    id,
    (SELECT email FROM usuarios WHERE id = usuario_id) as email,
    token,
    fecha_expiracion as expires_at,
    fecha_creacion as created_at
FROM tokens_seguridad 
WHERE tipo = 'password_reset';
