<?php
/**
 * Configuración General de la Aplicación - Figger Energy SAS
 */

// Configuración básica
define('APP_NAME', 'Figger Energy SAS');
define('APP_VERSION', '2.0.0');
define('APP_ENV', 'production');
define('APP_DEBUG', false);

// URLs y rutas
define('BASE_URL', '/');
define('ASSETS_URL', '/assets/');
define('UPLOAD_DIR', __DIR__ . '/../storage/uploads/');
define('LOG_DIR', __DIR__ . '/../storage/logs/');

// Configuración de sesión
define('SESSION_LIFETIME', 1800); // 30 minutos
define('SESSION_NAME', 'figger_session');

// Configuración de seguridad
define('CSRF_TOKEN_NAME', 'csrf_token');
define('MAX_LOGIN_ATTEMPTS', 3);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutos

// Configuración de email
define('MAIL_FROM', 'sistema@figgerenergy.gov.co');
define('MAIL_FROM_NAME', 'Sistema Figger Energy');
define('MAIL_TO_CONTACT', 'contacto@figgerenergy.gov.co');

// Configuración de archivos
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

// Configuración de logs
define('LOG_LEVEL', 'INFO');
define('LOG_MAX_FILES', 30);

// Zonas horarias
define('DEFAULT_TIMEZONE', 'America/Bogota');
date_default_timezone_set(DEFAULT_TIMEZONE);

// Configuración de idioma
define('DEFAULT_LANG', 'es');
define('SUPPORTED_LANGS', ['es', 'en']);

// Configuración de caché
define('CACHE_ENABLED', true);
define('CACHE_LIFETIME', 3600);

/**
 * Función para cargar configuración desde archivo
 */
function loadConfig($file) {
    $configFile = __DIR__ . '/' . $file . '.php';
    if (file_exists($configFile)) {
        return require $configFile;
    }
    return [];
}

/**
 * Función para obtener URL base
 */
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $protocol . '://' . $host . BASE_URL;
}

/**
 * Función para obtener URL de assets
 */
function getAssetUrl($path) {
    return getBaseUrl() . 'assets/' . ltrim($path, '/');
}

/**
 * Función para redireccionar
 */
function redirect($url, $permanent = false) {
    $code = $permanent ? 301 : 302;
    header("Location: $url", true, $code);
    exit();
}

/**
 * Función para mostrar errores (solo en desarrollo)
 */
function showErrors($show = null) {
    if ($show === null) {
        $show = APP_DEBUG;
    }
    
    if ($show) {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    } else {
        error_reporting(0);
        ini_set('display_errors', 0);
    }
}

// Inicializar configuración de errores
showErrors();
