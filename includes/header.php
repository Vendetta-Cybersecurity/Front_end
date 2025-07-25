<?php
// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cargar funciones comunes
require_once __DIR__ . '/../lib/functions.php';

// Determinar la ruta base dependiendo de la ubicación del archivo
$ruta_base = '';
$archivo_actual = basename($_SERVER['PHP_SELF']);
$directorio_actual = dirname($_SERVER['PHP_SELF']);

// Si estamos en un subdirectorio, ajustar la ruta base
if (strpos($directorio_actual, 'views') !== false) {
    $ruta_base = '../';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($titulo_pagina) ? $titulo_pagina . ' - ' : ''; ?>Figger Energy SAS</title>
    <meta name="description" content="Figger Energy SAS - Entidad gubernamental colombiana especializada en monitoreo y control de actividades de minería ilegal">
    <meta name="keywords" content="Figger Energy, Colombia, minería ilegal, energías renovables, monitoreo gubernamental">
    <link rel="icon" type="image/x-icon" href="<?php echo $ruta_base; ?>favicon.ico">
    <link rel="stylesheet" href="<?php echo $ruta_base; ?>assets/css/estilos.css">
    
    <?php if ($archivo_actual == 'index.php'): ?>
        <!-- Scripts para estadísticas dinámicas solo en página principal -->
        <script src="<?php echo $ruta_base; ?>assets/js/config-estadisticas.js"></script>
        <script src="<?php echo $ruta_base; ?>assets/js/estadisticas-dinamicas.js" defer></script>
    <?php endif; ?>
</head>
<body>
    <header class="header-principal">
        <div class="contenedor">
            <div class="header-contenido">
                <div class="logo-area">
                    <a href="<?php echo $ruta_base; ?>index.php" class="logo-enlace">
                        <img src="<?php echo $ruta_base; ?>assets/images/logo.png" alt="Logo Figger Energy SAS" class="logo" onerror="this.style.display='none';">
                        <div class="logo-texto">
                            <h1>Figger Energy SAS</h1>
                            <p>Entidad Gubernamental Colombiana</p>
                        </div>
                    </a>
                </div>
                
                <nav class="navegacion-principal">
                    <ul>
                        <li><a href="<?php echo $ruta_base; ?>index.php" <?php echo $archivo_actual == 'index.php' ? 'class="activo"' : ''; ?>>Inicio</a></li>
                        
                        <?php if (isset($_SESSION['usuario_id'])): ?>
                            <!-- Menú para usuarios autenticados -->
                            <li><a href="<?php echo $ruta_base . getDashboardUrl($_SESSION['rol']); ?>" 
                                   <?php echo strpos($archivo_actual, 'dashboard') !== false || strpos($archivo_actual, 'admin') !== false || strpos($archivo_actual, 'auditor') !== false || strpos($archivo_actual, 'empleado') !== false ? 'class="activo"' : ''; ?>>
                                Dashboard</a></li>
                            <li><a href="<?php echo $ruta_base; ?>views/public/contact.php" <?php echo $archivo_actual == 'contact.php' ? 'class="activo"' : ''; ?>>Contacto</a></li>
                            <li><a href="<?php echo $ruta_base; ?>views/auth/logout.php" class="btn-logout">Cerrar Sesión</a></li>
                            <li class="usuario-info">
                                <span>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
                                <small>(<?php echo ucfirst($_SESSION['rol']); ?>)</small>
                            </li>
                        <?php else: ?>
                            <!-- Menú para usuarios no autenticados -->
                            <li><a href="<?php echo $ruta_base; ?>views/public/contact.php" <?php echo $archivo_actual == 'contact.php' ? 'class="activo"' : ''; ?>>Contacto</a></li>
                            <li><a href="<?php echo $ruta_base; ?>views/auth/login.php" <?php echo $archivo_actual == 'login.php' ? 'class="activo"' : ''; ?>>Iniciar Sesión</a></li>
                            <li><a href="<?php echo $ruta_base; ?>views/auth/register.php" <?php echo $archivo_actual == 'register.php' ? 'class="activo"' : ''; ?>>Registro</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
