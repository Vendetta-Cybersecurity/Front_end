<?php
/**
 * Punto de Entrada Principal - Figger Energy SAS
 * Router simple para manejar todas las peticiones
 */

// Configuración inicial
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';

// Configurar sesión segura
Security::configureSession();
session_start();

// Configurar headers de seguridad
Security::setSecurityHeaders();

// Router simple
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request = rtrim($request, '/');

// Remover prefijo del directorio si está en subdirectorio
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
if ($scriptName !== '/') {
    $request = substr($request, strlen($scriptName));
}

// Router básico
switch ($request) {
    case '':
    case '/':
        showHomePage();
        break;
        
    case '/login':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->processLogin();
        } else {
            $controller->showLogin();
        }
        break;
        
    case '/register':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        $controller = new AuthController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->processRegister();
        } else {
            $controller->showRegister();
        }
        break;
        
    case '/logout':
        require_once __DIR__ . '/../app/controllers/AuthController.php';
        $controller = new AuthController();
        $controller->logout();
        break;
        
    case '/dashboard/admin':
        require_once __DIR__ . '/../app/controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->adminDashboard();
        break;
        
    case '/dashboard/empleado':
        require_once __DIR__ . '/../app/controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->empleadoDashboard();
        break;
        
    case '/dashboard/auditor':
        require_once __DIR__ . '/../app/controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->auditorDashboard();
        break;
        
    case '/contact':
        showContactPage();
        break;
        
    case '/api/take-alert':
        require_once __DIR__ . '/../app/controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->takeAlert();
        break;
        
    case '/api/update-alert':
        require_once __DIR__ . '/../app/controllers/DashboardController.php';
        $controller = new DashboardController();
        $controller->updateAlert();
        break;
        
    default:
        // 404 Not Found
        http_response_code(404);
        show404Page();
        break;
}

/**
 * Mostrar página de inicio
 */
function showHomePage() {
    $title = 'Inicio';
    $currentPage = 'home';
    $showMessages = true;
    
    // Verificar si hay mensaje de logout
    $logoutMessage = '';
    if (isset($_GET['logout']) && $_GET['logout'] == '1') {
        $logoutMessage = 'Ha cerrado sesión correctamente. Gracias por usar el sistema de Figger Energy SAS.';
    }
    
    ob_start();
    include __DIR__ . '/../views/pages/home.php';
    $content = ob_get_clean();
    
    include __DIR__ . '/../views/layouts/main.php';
}

/**
 * Mostrar página de contacto
 */
function showContactPage() {
    $title = 'Contacto Institucional';
    $currentPage = 'contact';
    $showMessages = true;
    
    // Procesar formulario si es POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_once __DIR__ . '/../app/controllers/ContactController.php';
        $controller = new ContactController();
        $controller->processContact();
        return;
    }
    
    ob_start();
    include __DIR__ . '/../views/pages/contact.php';
    $content = ob_get_clean();
    
    include __DIR__ . '/../views/layouts/main.php';
}

/**
 * Mostrar página 404
 */
function show404Page() {
    $title = 'Página No Encontrada';
    $currentPage = '404';
    
    ob_start();
    include __DIR__ . '/../views/pages/404.php';
    $content = ob_get_clean();
    
    include __DIR__ . '/../views/layouts/main.php';
}
