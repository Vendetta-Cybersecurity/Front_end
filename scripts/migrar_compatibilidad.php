<?php
/**
 * Script de Migración de Código para Compatibilidad con BD Unificada
 * Actualiza archivos PHP para usar las nuevas tablas
 */

echo "🔄 Iniciando migración de compatibilidad...\n\n";

class CodeMigrator {
    private $replacements = [
        // Tabla login_attempts → Sistema de sesiones
        'login_attempts' => 'sesiones',
        
        // Tabla logs_acceso → actividades
        'logs_acceso' => 'actividades',
        'user_id' => 'usuario_id',
        'accion,' => 'accion,',
        'timestamp' => 'fecha_actividad',
        
        // Tabla password_resets → tokens_seguridad  
        'password_resets' => 'tokens_seguridad',
        'expires_at' => 'fecha_expiracion',
        'created_at' => 'fecha_creacion',
    ];
    
    private $files_to_migrate = [
        'php/auth.php',
        'php/config.php', 
        'php/dashboard.php',
        'php/logout.php'
    ];
    
    private $backup_dir = 'backup_migracion/';
    
    public function __construct() {
        if (!is_dir($this->backup_dir)) {
            mkdir($this->backup_dir, 0755, true);
        }
    }
    
    public function migrate() {
        echo "📋 Archivos a migrar:\n";
        foreach ($this->files_to_migrate as $file) {
            if (file_exists($file)) {
                echo "   ✓ $file\n";
            } else {
                echo "   ⚠ $file (no encontrado)\n";
            }
        }
        
        echo "\n🔄 Iniciando migración...\n\n";
        
        foreach ($this->files_to_migrate as $file) {
            if (file_exists($file)) {
                $this->migrateFile($file);
            }
        }
        
        $this->createCompatibilityLayer();
        
        echo "\n✅ Migración completada!\n";
        echo "📁 Backups guardados en: {$this->backup_dir}\n";
    }
    
    private function migrateFile($file) {
        echo "🔧 Migrando $file...\n";
        
        // Crear backup
        $backup_file = $this->backup_dir . basename($file) . '.backup';
        copy($file, $backup_file);
        
        // Leer contenido
        $content = file_get_contents($file);
        $original_content = $content;
        
        // Aplicar reemplazos específicos por archivo
        switch ($file) {
            case 'php/auth.php':
                $content = $this->migrateAuthFile($content);
                break;
                
            case 'php/config.php':
                $content = $this->migrateConfigFile($content);
                break;
                
            case 'php/dashboard.php':
                $content = $this->migrateDashboardFile($content);
                break;
                
            case 'php/logout.php':
                $content = $this->migrateLogoutFile($content);
                break;
        }
        
        // Guardar si hay cambios
        if ($content !== $original_content) {
            file_put_contents($file, $content);
            echo "   ✅ $file actualizado\n";
        } else {
            echo "   ℹ️ $file sin cambios necesarios\n";
        }
    }
    
    private function migrateAuthFile($content) {
        // Reemplazar logs_acceso con actividades
        $content = str_replace(
            'logs_acceso',
            'actividades',
            $content
        );
        
        $content = str_replace(
            'user_id',
            'usuario_id', 
            $content
        );
        
        $content = str_replace(
            'timestamp',
            'fecha_actividad',
            $content
        );
        
        // Reemplazar password_resets con tokens_seguridad
        $content = str_replace(
            'password_resets',
            'tokens_seguridad',
            $content
        );
        
        $content = str_replace(
            'expires_at',
            'fecha_expiracion',
            $content
        );
        
        $content = str_replace(
            'created_at',
            'fecha_creacion',
            $content
        );
        
        return $content;
    }
    
    private function migrateConfigFile($content) {
        // Reemplazar sistema de login_attempts con sesiones
        $old_login_check = 'SELECT COUNT(*) as count FROM login_attempts WHERE identifier = ?';
        $new_login_check = 'SELECT COUNT(*) as count FROM sesiones WHERE ip_address = ? AND ultimo_acceso > DATE_SUB(NOW(), INTERVAL 15 MINUTE)';
        
        $content = str_replace($old_login_check, $new_login_check, $content);
        
        // Reemplazar inserts de login_attempts
        $old_insert = 'INSERT INTO login_attempts (identifier, attempted_at, ip_address) VALUES (?, NOW(), ?)';
        $new_insert = 'INSERT INTO actividades (usuario_id, accion, descripcion, ip_address, fecha_actividad) VALUES (1, \'intento_login\', \'Intento de login fallido\', ?, NOW())';
        
        $content = str_replace($old_insert, $new_insert, $content);
        
        return $content;
    }
    
    private function migrateDashboardFile($content) {
        // Reemplazar consulta de logs_acceso
        $old_query = 'FROM logs_acceso la';
        $new_query = 'FROM actividades la';
        
        $content = str_replace($old_query, $new_query, $content);
        
        return $content;
    }
    
    private function migrateLogoutFile($content) {
        // Actualizar insert de logout
        $content = str_replace(
            'logs_acceso',
            'actividades',
            $content
        );
        
        $content = str_replace(
            'user_id',
            'usuario_id',
            $content
        );
        
        return $content;
    }
    
    private function createCompatibilityLayer() {
        echo "🔗 Creando capa de compatibilidad...\n";
        
        $compatibility_sql = "
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
";
        
        file_put_contents('scripts/compatibility_views.sql', $compatibility_sql);
        echo "   ✅ Archivo compatibility_views.sql creado\n";
        echo "   📝 Ejecutar este SQL después de importar la BD unificada\n";
    }
}

// Ejecutar migración
$migrator = new CodeMigrator();
$migrator->migrate();

echo "\n" . str_repeat("=", 50) . "\n";
echo "📋 PASOS SIGUIENTES:\n";
echo "1. ✅ Código PHP actualizado para nueva estructura\n";
echo "2. 🗄️ Importar database/figger_energy_complete.sql\n"; 
echo "3. 🔗 Ejecutar scripts/compatibility_views.sql\n";
echo "4. 🧪 Probar funcionamiento del sistema\n";
echo "5. 🗂️ Restaurar desde backup_migracion/ si hay problemas\n";
echo str_repeat("=", 50) . "\n";
?>
