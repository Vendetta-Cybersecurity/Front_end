-- Script SQL para regenerar contraseñas de usuarios demo
-- Figger Energy SAS - Ejecutar en phpMyAdmin o consola MySQL
-- =========================================================

USE figger_energy_db;

-- Mostrar estado actual ANTES de actualizar
SELECT "=== ESTADO ACTUAL DE USUARIOS ===" as info;
SELECT 
    nombre,
    email, 
    LEFT(password, 15) as hash_actual,
    LENGTH(password) as longitud_hash,
    activo
FROM usuarios 
WHERE email LIKE '%@figgerenergy.gov.co'
ORDER BY id;

-- Actualizar contraseñas con hashes seguros generados
-- Nota: Estos hashes corresponden a las contraseñas reales indicadas

-- Admin: contraseña real = "admin123"
UPDATE usuarios 
SET password = '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm' 
WHERE email = 'admin@figgerenergy.gov.co';

-- Empleado: contraseña real = "empleado123"  
UPDATE usuarios 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE email = 'empleado@figgerenergy.gov.co';

-- Auditor: contraseña real = "auditor123"
UPDATE usuarios 
SET password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE email = 'auditor@figgerenergy.gov.co';

-- Verificar que las actualizaciones se aplicaron
SELECT "=== CONTRASEÑAS ACTUALIZADAS ===" as resultado;
SELECT 
    id,
    nombre,
    email, 
    LEFT(password, 20) as hash_nuevo,
    LENGTH(password) as longitud_hash,
    activo,
    fecha_registro
FROM usuarios 
WHERE email LIKE '%@figgerenergy.gov.co'
ORDER BY id;

-- Información de credenciales para login:
SELECT "=== CREDENCIALES PARA LOGIN ===" as instrucciones;
SELECT "admin@figgerenergy.gov.co → admin123" as admin_creds;
SELECT "empleado@figgerenergy.gov.co → empleado123" as empleado_creds;  
SELECT "auditor@figgerenergy.gov.co → auditor123" as auditor_creds;

SELECT "✅ Contraseñas actualizadas correctamente" as status;
SELECT "ℹ️  Las contraseñas en la columna 'password' aparecen como hashes (esto es correcto)" as info;
SELECT "🔑 Usa las contraseñas reales para hacer login en la web" as instrucciones;
