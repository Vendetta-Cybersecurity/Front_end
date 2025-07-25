-- Parche para Base de Datos - Figger Energy SAS
-- Manejo mejorado de foreign keys para eliminación segura de usuarios

USE figger_energy_db;

-- Opción 1: Eliminar foreign keys existentes y recrear con ON DELETE SET NULL
-- para alertas_mineria (permite reasignar alertas a NULL cuando se elimina usuario)

ALTER TABLE alertas_mineria DROP FOREIGN KEY alertas_mineria_ibfk_1;
ALTER TABLE alertas_mineria 
ADD CONSTRAINT fk_alertas_usuario 
FOREIGN KEY (usuario_asignado) REFERENCES usuarios(id) 
ON DELETE SET NULL ON UPDATE CASCADE;

-- Opción 2: Para actividades, mantener restricción pero eliminar en cascada
-- (las actividades se eliminarán cuando se elimine el usuario)

ALTER TABLE actividades DROP FOREIGN KEY actividades_ibfk_1;
ALTER TABLE actividades 
ADD CONSTRAINT fk_actividades_usuario 
FOREIGN KEY (usuario_id) REFERENCES usuarios(id) 
ON DELETE CASCADE ON UPDATE CASCADE;

-- Agregar índices para mejorar rendimiento en operaciones de eliminación
CREATE INDEX idx_alertas_usuario_asignado ON alertas_mineria(usuario_asignado);
CREATE INDEX idx_actividades_usuario_id ON actividades(usuario_id);

-- Crear función de auditoría para eliminación de usuarios
DELIMITER //

CREATE TRIGGER before_user_delete 
BEFORE DELETE ON usuarios 
FOR EACH ROW 
BEGIN
    -- Insertar registro de auditoría antes de eliminar
    INSERT INTO actividades (usuario_id, accion, descripcion, fecha_actividad, ip_address) 
    VALUES (
        OLD.id, 
        'usuario_eliminado', 
        CONCAT('Usuario eliminado: ', OLD.nombre, ' (', OLD.email, ')'),
        NOW(),
        'sistema'
    );
END//

DELIMITER ;

-- Comentarios sobre los cambios:
-- 1. alertas_mineria: ON DELETE SET NULL - Las alertas quedan sin asignar
-- 2. actividades: ON DELETE CASCADE - Las actividades se eliminan automáticamente
-- 3. Trigger: Registra la eliminación antes de que ocurra
-- 4. Índices: Mejoran el rendimiento de las operaciones

-- Para aplicar estos cambios de forma segura:
-- 1. Hacer backup de la base de datos antes de ejecutar
-- 2. Ejecutar en horario de mantenimiento
-- 3. Verificar que no hay operaciones críticas en curso

-- Verificar los cambios aplicados:
-- SHOW CREATE TABLE alertas_mineria;
-- SHOW CREATE TABLE actividades;
-- SHOW TRIGGERS;
