<?php
/**
 * Script de Verificación y Corrección de Estructura de Base de Datos
 * Figger Energy SAS - Validación de compatibilidad con archivo unificado
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cargar configuraciones
require_once __DIR__ . '/../includes/db.php';

class DatabaseStructureValidator {
    private $conexion;
    private $errores = [];
    private $advertencias = [];
    private $exitos = [];
    
    public function __construct() {
        $this->conexion = conectarDB();
        if (!$this->conexion) {
            die("❌ No se pudo conectar a la base de datos\n");
        }
    }
    
    /**
     * Ejecutar verificación completa
     */
    public function verificarEstructura() {
        echo "🔍 Iniciando verificación de estructura de base de datos...\n\n";
        
        $this->verificarBaseDatos();
        $this->verificarTablas();
        $this->verificarColumnas();
        $this->verificarIndices();
        $this->verificarForeignKeys();
        $this->verificarTriggers();
        $this->verificarVistas();
        $this->verificarDatosEsenciales();
        
        $this->mostrarResumen();
    }
    
    /**
     * Verificar que la base de datos existe y tiene la configuración correcta
     */
    private function verificarBaseDatos() {
        echo "📋 Verificando base de datos...\n";
        
        $result = $this->conexion->query("SELECT DATABASE() as db_name");
        $db_info = $result->fetch_assoc();
        
        if ($db_info['db_name'] === 'figger_energy_db') {
            $this->exitos[] = "Base de datos 'figger_energy_db' conectada correctamente";
        } else {
            $this->errores[] = "Base de datos incorrecta: {$db_info['db_name']}. Debería ser 'figger_energy_db'";
        }
        
        // Verificar charset
        $result = $this->conexion->query("SELECT @@character_set_database as charset, @@collation_database as collation");
        $charset_info = $result->fetch_assoc();
        
        if ($charset_info['charset'] === 'utf8mb4') {
            $this->exitos[] = "Charset UTF8MB4 configurado correctamente";
        } else {
            $this->advertencias[] = "Charset actual: {$charset_info['charset']}. Recomendado: utf8mb4";
        }
    }
    
    /**
     * Verificar que todas las tablas necesarias existen
     */
    private function verificarTablas() {
        echo "🗃️ Verificando tablas...\n";
        
        $tablas_requeridas = [
            'usuarios',
            'sesiones',
            'contactos',
            'alertas_mineria',
            'actividades',
            'notificaciones',
            'configuracion_sistema',
            'archivos',
            'tokens_seguridad'
        ];
        
        $result = $this->conexion->query("SHOW TABLES");
        $tablas_existentes = [];
        while ($row = $result->fetch_array()) {
            $tablas_existentes[] = $row[0];
        }
        
        foreach ($tablas_requeridas as $tabla) {
            if (in_array($tabla, $tablas_existentes)) {
                $this->exitos[] = "Tabla '$tabla' existe";
            } else {
                $this->errores[] = "Tabla '$tabla' NO EXISTE - Requerida por el sistema";
            }
        }
        
        // Verificar tablas adicionales que podrían causar conflictos
        $tablas_conflictivas = ['login_attempts', 'logs_acceso', 'password_resets'];
        foreach ($tablas_conflictivas as $tabla) {
            if (in_array($tabla, $tablas_existentes)) {
                $this->advertencias[] = "Tabla '$tabla' existe pero no está en la BD unificada - Posible conflicto";
            }
        }
    }
    
    /**
     * Verificar columnas críticas
     */
    private function verificarColumnas() {
        echo "📊 Verificando columnas críticas...\n";
        
        $verificaciones = [
            'usuarios' => ['id', 'nombre', 'email', 'password', 'rol', 'activo', 'estado', 'fecha_registro'],
            'alertas_mineria' => ['id', 'ubicacion', 'tipo_alerta', 'nivel_riesgo', 'estado', 'usuario_asignado', 'fecha_deteccion'],
            'actividades' => ['id', 'usuario_id', 'accion', 'descripcion', 'fecha_actividad'],
            'contactos' => ['id', 'nombre', 'email', 'mensaje', 'fecha_envio']
        ];
        
        foreach ($verificaciones as $tabla => $columnas) {
            $result = $this->conexion->query("DESCRIBE $tabla");
            if (!$result) {
                continue; // Tabla no existe, ya fue reportada
            }
            
            $columnas_existentes = [];
            while ($row = $result->fetch_assoc()) {
                $columnas_existentes[] = $row['Field'];
            }
            
            foreach ($columnas as $columna) {
                if (in_array($columna, $columnas_existentes)) {
                    $this->exitos[] = "Columna $tabla.$columna existe";
                } else {
                    $this->errores[] = "Columna $tabla.$columna NO EXISTE";
                }
            }
        }
    }
    
    /**
     * Verificar índices importantes
     */
    private function verificarIndices() {
        echo "🔍 Verificando índices...\n";
        
        $indices_criticos = [
            'usuarios' => ['email', 'rol'],
            'alertas_mineria' => ['usuario_asignado', 'estado', 'fecha_deteccion'],
            'actividades' => ['usuario_id', 'fecha_actividad']
        ];
        
        foreach ($indices_criticos as $tabla => $indices) {
            $result = $this->conexion->query("SHOW INDEX FROM $tabla");
            if (!$result) continue;
            
            $indices_existentes = [];
            while ($row = $result->fetch_assoc()) {
                $indices_existentes[] = $row['Column_name'];
            }
            
            foreach ($indices as $indice) {
                if (in_array($indice, $indices_existentes)) {
                    $this->exitos[] = "Índice en $tabla.$indice existe";
                } else {
                    $this->advertencias[] = "Índice en $tabla.$indice faltante - Puede afectar rendimiento";
                }
            }
        }
    }
    
    /**
     * Verificar foreign keys
     */
    private function verificarForeignKeys() {
        echo "🔗 Verificando foreign keys...\n";
        
        $result = $this->conexion->query("
            SELECT 
                TABLE_NAME,
                COLUMN_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME,
                DELETE_RULE,
                UPDATE_RULE
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE REFERENCED_TABLE_SCHEMA = 'figger_energy_db'
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        $foreign_keys = [];
        while ($row = $result->fetch_assoc()) {
            $foreign_keys[] = $row;
        }
        
        if (empty($foreign_keys)) {
            $this->errores[] = "NO HAY FOREIGN KEYS configuradas - Sistema vulnerable a inconsistencias";
        } else {
            foreach ($foreign_keys as $fk) {
                $this->exitos[] = "FK: {$fk['TABLE_NAME']}.{$fk['COLUMN_NAME']} -> {$fk['REFERENCED_TABLE_NAME']}.{$fk['REFERENCED_COLUMN_NAME']} ({$fk['DELETE_RULE']})";
            }
        }
    }
    
    /**
     * Verificar triggers
     */
    private function verificarTriggers() {
        echo "⚡ Verificando triggers...\n";
        
        $result = $this->conexion->query("SHOW TRIGGERS");
        $triggers = [];
        while ($row = $result->fetch_assoc()) {
            $triggers[] = $row['Trigger'];
        }
        
        $triggers_esperados = [
            'before_insert_alertas_mineria',
            'after_update_usuarios_login',
            'after_update_alertas_mineria'
        ];
        
        foreach ($triggers_esperados as $trigger) {
            if (in_array($trigger, $triggers)) {
                $this->exitos[] = "Trigger '$trigger' existe";
            } else {
                $this->advertencias[] = "Trigger '$trigger' faltante - Funcionalidad automática limitada";
            }
        }
    }
    
    /**
     * Verificar vistas
     */
    private function verificarVistas() {
        echo "👁️ Verificando vistas...\n";
        
        $result = $this->conexion->query("
            SELECT TABLE_NAME 
            FROM information_schema.VIEWS 
            WHERE TABLE_SCHEMA = 'figger_energy_db'
        ");
        
        $vistas = [];
        while ($row = $result->fetch_assoc()) {
            $vistas[] = $row['TABLE_NAME'];
        }
        
        $vistas_esperadas = [
            'vista_alertas_activas',
            'vista_estadisticas_usuario',
            'vista_resumen_alertas'
        ];
        
        foreach ($vistas_esperadas as $vista) {
            if (in_array($vista, $vistas)) {
                $this->exitos[] = "Vista '$vista' existe";
            } else {
                $this->advertencias[] = "Vista '$vista' faltante - Reportes limitados";
            }
        }
    }
    
    /**
     * Verificar datos esenciales
     */
    private function verificarDatosEsenciales() {
        echo "📦 Verificando datos esenciales...\n";
        
        // Verificar usuarios administrativos
        $result = $this->conexion->query("SELECT COUNT(*) as count FROM usuarios WHERE rol = 'admin' AND activo = 1");
        $admin_count = $result->fetch_assoc()['count'];
        
        if ($admin_count > 0) {
            $this->exitos[] = "$admin_count usuario(s) administrador(es) activos";
        } else {
            $this->errores[] = "NO HAY USUARIOS ADMINISTRADORES - Sistema inaccesible";
        }
        
        // Verificar configuración del sistema
        $result = $this->conexion->query("SELECT COUNT(*) as count FROM configuracion_sistema");
        if ($result) {
            $config_count = $result->fetch_assoc()['count'];
            if ($config_count > 0) {
                $this->exitos[] = "$config_count configuraciones del sistema cargadas";
            } else {
                $this->advertencias[] = "Configuraciones del sistema vacías";
            }
        }
    }
    
    /**
     * Mostrar resumen de la verificación
     */
    private function mostrarResumen() {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "📊 RESUMEN DE VERIFICACIÓN\n";
        echo str_repeat("=", 60) . "\n\n";
        
        echo "✅ ÉXITOS (" . count($this->exitos) . "):\n";
        foreach ($this->exitos as $exito) {
            echo "   ✓ $exito\n";
        }
        
        if (!empty($this->advertencias)) {
            echo "\n⚠️ ADVERTENCIAS (" . count($this->advertencias) . "):\n";
            foreach ($this->advertencias as $advertencia) {
                echo "   ⚠ $advertencia\n";
            }
        }
        
        if (!empty($this->errores)) {
            echo "\n❌ ERRORES CRÍTICOS (" . count($this->errores) . "):\n";
            foreach ($this->errores as $error) {
                echo "   ✗ $error\n";
            }
            
            echo "\n🔧 ACCIÓN REQUERIDA:\n";
            echo "   1. Importar el archivo figger_energy_complete.sql en phpMyAdmin\n";
            echo "   2. Ejecutar: DROP DATABASE figger_energy_db; y luego importar el SQL\n";
            echo "   3. Verificar que todas las credenciales sean correctas\n";
        }
        
        $total_problemas = count($this->errores) + count($this->advertencias);
        $total_exitos = count($this->exitos);
        
        echo "\n📈 RESULTADO FINAL:\n";
        if (count($this->errores) == 0) {
            echo "   🎉 BASE DE DATOS COMPATIBLE Y FUNCIONAL\n";
            echo "   📊 Puntuación: $total_exitos éxitos, $total_problemas problemas menores\n";
        } else {
            echo "   🚨 REQUIERE ATENCIÓN INMEDIATA\n";
            echo "   📊 Puntuación: $total_exitos éxitos, " . count($this->errores) . " errores críticos\n";
        }
        
        echo "\nℹ️ Para resolver todos los problemas, importa el archivo:\n";
        echo "   database/figger_energy_complete.sql\n\n";
    }
    
    public function __destruct() {
        if ($this->conexion) {
            cerrarDB($this->conexion);
        }
    }
}

// Ejecutar verificación si se llama directamente
if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    $validator = new DatabaseStructureValidator();
    $validator->verificarEstructura();
}
