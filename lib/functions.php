<?php
/**
 * Funciones Comunes - Figger Energy SAS
 * Biblioteca de funciones reutilizables para evitar duplicación de código
 */

/**
 * Verificar autenticación de usuario
 * @param string|null $rol_requerido Rol específico requerido (opcional)
 * @return bool True si está autenticado, false en caso contrario
 */
function verificarAuth($rol_requerido = null) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['usuario_id'])) {
        return false;
    }
    
    if ($rol_requerido && $_SESSION['rol'] !== $rol_requerido) {
        return false;
    }
    
    return true;
}

/**
 * Redirigir si no está autenticado
 * @param string|null $rol_requerido Rol específico requerido (opcional)
 * @param string $redirect_url URL de redirección (por defecto login.php)
 */
function requiereAuth($rol_requerido = null, $redirect_url = 'login.php') {
    if (!verificarAuth($rol_requerido)) {
        header("Location: $redirect_url");
        exit();
    }
}

/**
 * Registrar actividad del usuario
 * @param int $usuario_id ID del usuario
 * @param string $accion Acción realizada
 * @param string $descripcion Descripción de la actividad
 * @param mysqli|null $conexion Conexión a BD (opcional, se crea si no se proporciona)
 */
function registrarActividad($usuario_id, $accion, $descripcion, $conexion = null) {
    $cerrar_conexion = false;
    
    if (!$conexion) {
        require_once __DIR__ . '/../config/database.php';
        $conexion = conectarDB();
        $cerrar_conexion = true;
    }
    
    $stmt = $conexion->prepare("INSERT INTO actividades (usuario_id, accion, descripcion, ip_address) VALUES (?, ?, ?, ?)");
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
    $stmt->bind_param("isss", $usuario_id, $accion, $descripcion, $ip);
    $stmt->execute();
    $stmt->close();
    
    if ($cerrar_conexion) {
        cerrarDB($conexion);
    }
}

/**
 * Mostrar mensaje temporal
 * @param string $mensaje Mensaje a mostrar
 * @param string $tipo Tipo de mensaje (exito, error, advertencia)
 * @return string HTML del mensaje
 */
function mostrarMensaje($mensaje, $tipo = 'info') {
    if (empty($mensaje)) return '';
    
    return '<div class="mensaje mensaje-' . htmlspecialchars($tipo) . ' mensaje-temporal">' 
           . htmlspecialchars($mensaje) . '</div>';
}

/**
 * Validar datos de formulario
 * @param array $campos Array con los campos y sus reglas de validación
 * @param array $datos Datos del formulario ($_POST)
 * @return array Array con errores encontrados
 */
function validarFormulario($campos, $datos) {
    $errores = [];
    
    foreach ($campos as $campo => $reglas) {
        $valor = $datos[$campo] ?? '';
        
        // Campo requerido
        if (isset($reglas['requerido']) && $reglas['requerido'] && empty($valor)) {
            $errores[] = "El campo {$campo} es requerido.";
            continue;
        }
        
        // Longitud mínima
        if (isset($reglas['min_length']) && strlen($valor) < $reglas['min_length']) {
            $errores[] = "El campo {$campo} debe tener al menos {$reglas['min_length']} caracteres.";
        }
        
        // Longitud máxima
        if (isset($reglas['max_length']) && strlen($valor) > $reglas['max_length']) {
            $errores[] = "El campo {$campo} no puede tener más de {$reglas['max_length']} caracteres.";
        }
        
        // Validar email
        if (isset($reglas['email']) && $reglas['email'] && !filter_var($valor, FILTER_VALIDATE_EMAIL)) {
            $errores[] = "El campo {$campo} debe ser un email válido.";
        }
        
        // Validar confirmación de contraseña
        if (isset($reglas['confirmar']) && $valor !== ($datos[$reglas['confirmar']] ?? '')) {
            $errores[] = "Las contraseñas no coinciden.";
        }
    }
    
    return $errores;
}

/**
 * Generar URL del dashboard según el rol
 * @param string $rol Rol del usuario
 * @return string URL del dashboard correspondiente
 */
function getDashboardUrl($rol) {
    $dashboards = [
        'admin' => 'views/dashboard/admin.php',
        'auditor' => 'views/dashboard/auditor.php',
        'empleado' => 'views/dashboard/empleado.php'
    ];
    
    return $dashboards[$rol] ?? 'index.php';
}

/**
 * Formatear fecha para mostrar
 * @param string $fecha Fecha en formato MySQL
 * @return string Fecha formateada
 */
function formatearFecha($fecha) {
    if (empty($fecha)) return 'No disponible';
    
    $timestamp = strtotime($fecha);
    return date('d/m/Y H:i', $timestamp);
}

/**
 * Generar token CSRF
 * @return string Token CSRF
 */
function generarCSRF() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Verificar token CSRF
 * @param string $token Token a verificar
 * @return bool True si el token es válido
 */
function verificarCSRF($token) {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
