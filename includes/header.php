<?php
// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
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
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="stylesheet" href="css/estilos.css">
    
    <?php if (basename($_SERVER['PHP_SELF']) == 'index.php'): ?>
        <!-- Scripts para estadísticas dinámicas solo en página principal -->
        <script src="js/config-estadisticas.js"></script>
        <script src="js/estadisticas-dinamicas.js" defer></script>
    <?php endif; ?>
</head>
<body>
    <header class="header-principal">
        <div class="contenedor">
            <div class="header-contenido">
                <div class="logo-area">
                    <a href="index.php" class="logo-enlace">
                        <img src="images/logo.png" alt="Logo Figger Energy SAS" class="logo" onerror="this.style.display='none';">
                        <div class="logo-texto">
                            <h1>Figger Energy SAS</h1>
                            <p>Entidad Gubernamental Colombiana</p>
                        </div>
                    </a>
                </div>
                
                <nav class="navegacion-principal">
                    <ul>
                        <li><a href="index.php" <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'class="activo"' : ''; ?>>Inicio</a></li>
                        
                        <?php if (isset($_SESSION['usuario_id'])): ?>
                            <!-- Menú para usuarios autenticados -->
                            <li><a href="<?php echo 'dashboard_' . $_SESSION['rol'] . '.php'; ?>" 
                                   <?php echo strpos(basename($_SERVER['PHP_SELF']), 'dashboard') !== false ? 'class="activo"' : ''; ?>>
                                Dashboard</a></li>
                            <li><a href="contacto.php" <?php echo basename($_SERVER['PHP_SELF']) == 'contacto.php' ? 'class="activo"' : ''; ?>>Contacto</a></li>
                            <li><a href="logout.php" class="btn-logout">Cerrar Sesión</a></li>
                            <li class="usuario-info">
                                <span>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
                                <small>(<?php echo ucfirst($_SESSION['rol']); ?>)</small>
                            </li>
                        <?php else: ?>
                            <!-- Menú para usuarios no autenticados -->
                            <li><a href="contacto.php" <?php echo basename($_SERVER['PHP_SELF']) == 'contacto.php' ? 'class="activo"' : ''; ?>>Contacto</a></li>
                            <li><a href="login.php" <?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'class="activo"' : ''; ?>>Iniciar Sesión</a></li>
                            <li><a href="register.php" <?php echo basename($_SERVER['PHP_SELF']) == 'register.php' ? 'class="activo"' : ''; ?>>Registro</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
