<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title . ' - ' : ''; ?>Figger Energy SAS</title>
    <meta name="description" content="Figger Energy SAS - Entidad gubernamental colombiana especializada en monitoreo y control de actividades de minería ilegal">
    <meta name="keywords" content="Figger Energy, Colombia, minería ilegal, energías renovables, monitoreo gubernamental">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo getAssetUrl('images/favicon.ico'); ?>">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?php echo getAssetUrl('css/main.css'); ?>">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo getAssetUrl('css/' . $css); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="<?php echo $bodyClass ?? ''; ?>">
    
    <!-- Security Headers -->
    <?php Security::setSecurityHeaders(); ?>
    
    <!-- Header -->
    <header class="header-principal">
        <div class="contenedor">
            <div class="header-contenido">
                <div class="logo-area">
                    <a href="<?php echo getBaseUrl(); ?>" class="logo-enlace">
                        <img src="<?php echo getAssetUrl('images/logo.png'); ?>" 
                             alt="Logo Figger Energy SAS" 
                             class="logo" 
                             onerror="this.style.display='none';">
                        <div class="logo-texto">
                            <h1>Figger Energy SAS</h1>
                            <p>Entidad Gubernamental Colombiana</p>
                        </div>
                    </a>
                </div>
                
                <nav class="navegacion-principal">
                    <ul>
                        <li><a href="<?php echo getBaseUrl(); ?>" 
                               class="<?php echo ($currentPage ?? '') === 'home' ? 'activo' : ''; ?>">Inicio</a></li>
                        
                        <?php if (AuthMiddleware::isAuthenticated()): ?>
                            <?php $user = AuthMiddleware::getCurrentUser(); ?>
                            <!-- Menú para usuarios autenticados -->
                            <li><a href="<?php echo getBaseUrl() . 'dashboard/' . $user['role']; ?>" 
                                   class="<?php echo strpos($currentPage ?? '', 'dashboard') !== false ? 'activo' : ''; ?>">
                                Dashboard</a></li>
                            <li><a href="<?php echo getBaseUrl(); ?>contact" 
                                   class="<?php echo ($currentPage ?? '') === 'contact' ? 'activo' : ''; ?>">Contacto</a></li>
                            <li><a href="<?php echo getBaseUrl(); ?>logout" class="btn-logout">Cerrar Sesión</a></li>
                            <li class="usuario-info">
                                <span>Bienvenido, <?php echo htmlspecialchars($user['name']); ?></span>
                                <small>(<?php echo ucfirst($user['role']); ?>)</small>
                            </li>
                        <?php else: ?>
                            <!-- Menú para usuarios no autenticados -->
                            <li><a href="<?php echo getBaseUrl(); ?>contact" 
                                   class="<?php echo ($currentPage ?? '') === 'contact' ? 'activo' : ''; ?>">Contacto</a></li>
                            <li><a href="<?php echo getBaseUrl(); ?>login" 
                                   class="<?php echo ($currentPage ?? '') === 'login' ? 'activo' : ''; ?>">Iniciar Sesión</a></li>
                            <li><a href="<?php echo getBaseUrl(); ?>register" 
                                   class="<?php echo ($currentPage ?? '') === 'register' ? 'activo' : ''; ?>">Registro</a></li>
                        <?php endif; ?>
                    </ul>
                    
                    <!-- Mobile menu toggle -->
                    <button class="mobile-menu-toggle" aria-label="Toggle menu">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="contenido-principal">
        <?php if (isset($showMessages) && $showMessages): ?>
            <!-- Messages -->
            <?php if (isset($_GET['error'])): ?>
                <div class="contenedor">
                    <div class="mensaje mensaje-error mensaje-temporal">
                        <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="contenedor">
                    <div class="mensaje mensaje-exito mensaje-temporal">
                        <?php echo htmlspecialchars($_GET['success']); ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['message'])): ?>
                <div class="contenedor">
                    <div class="mensaje mensaje-info mensaje-temporal">
                        <?php echo htmlspecialchars($_GET['message']); ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <!-- Page Content -->
        <?php echo $content ?? ''; ?>
    </main>

    <!-- Footer -->
    <footer class="footer-principal">
        <div class="contenedor">
            <div class="footer-contenido">
                <div class="footer-seccion">
                    <div class="footer-logo">
                        <img src="<?php echo getAssetUrl('images/logo.png'); ?>" alt="Figger Energy" onerror="this.style.display='none';">
                        <span>FIGGER ENERGY</span>
                        <small>Gobierno de Colombia</small>
                    </div>
                    <p>Unidad especializada en monitoreo y control de minería ilegal de materiales para energías renovables.</p>
                </div>
                
                <div class="footer-seccion">
                    <h4>Operaciones</h4>
                    <ul>
                        <li><a href="#operaciones">Monitoreo Satelital</a></li>
                        <li><a href="#operaciones">Operaciones de Campo</a></li>
                        <li><a href="#operaciones">Análisis de Datos</a></li>
                        <li><a href="#monitoreo">Estado Actual</a></li>
                    </ul>
                </div>
                
                <div class="footer-seccion">
                    <h4>Institucional</h4>
                    <ul>
                        <li><a href="#mision">Misión y Visión</a></li>
                        <li><a href="#cumplimiento">ISO 27001</a></li>
                        <li><a href="<?php echo getBaseUrl(); ?>contact">Contacto</a></li>
                        <?php if (AuthMiddleware::isAuthenticated()): ?>
                            <li><a href="<?php echo getBaseUrl() . 'dashboard/' . AuthMiddleware::getCurrentUser()['role']; ?>">Sistema Interno</a></li>
                        <?php else: ?>
                            <li><a href="<?php echo getBaseUrl(); ?>login">Sistema Interno</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="footer-seccion">
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="#">Políticas de Seguridad</a></li>
                        <li><a href="#">Tratamiento de Datos</a></li>
                        <li><a href="#">Marco Normativo</a></li>
                        <li><a href="#">Transparencia</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> FIGGER ENERGY - Gobierno de Colombia. Todos los derechos reservados.</p>
                <p>Cumplimiento ISO 27001 | Seguridad de la Información</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="<?php echo getAssetUrl('js/main.js'); ?>"></script>
    
    <?php if (isset($additionalJS)): ?>
        <?php foreach ($additionalJS as $js): ?>
            <script src="<?php echo getAssetUrl('js/' . $js); ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- CSRF Token for AJAX -->
    <script>
        window.csrfToken = '<?php echo Security::generateCSRFToken(); ?>';
        window.baseUrl = '<?php echo getBaseUrl(); ?>';
    </script>
</body>
</html>
