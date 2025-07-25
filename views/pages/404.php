<!-- Error 404 -->
<section class="error-page">
    <div class="contenedor">
        <div class="error-content">
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            
            <div class="error-info">
                <h1>Error 404</h1>
                <h2>Página No Encontrada</h2>
                <p>Lo sentimos, la página que está buscando no existe o ha sido movida.</p>
                
                <div class="error-details">
                    <p><strong>Posibles causas:</strong></p>
                    <ul>
                        <li>La URL fue escrita incorrectamente</li>
                        <li>El enlace está desactualizado</li>
                        <li>La página fue movida o eliminada</li>
                        <li>No tiene permisos para acceder a este contenido</li>
                    </ul>
                </div>

                <div class="error-actions">
                    <a href="<?php echo getBaseUrl(); ?>" class="boton boton-primario">
                        <i class="fas fa-home"></i>
                        Ir al Inicio
                    </a>
                    
                    <?php if (AuthMiddleware::isAuthenticated()): ?>
                        <a href="<?php echo getBaseUrl() . 'dashboard/' . AuthMiddleware::getCurrentUser()['role']; ?>" class="boton boton-secundario">
                            <i class="fas fa-tachometer-alt"></i>
                            Ir al Dashboard
                        </a>
                    <?php else: ?>
                        <a href="<?php echo getBaseUrl(); ?>login" class="boton boton-secundario">
                            <i class="fas fa-sign-in-alt"></i>
                            Iniciar Sesión
                        </a>
                    <?php endif; ?>
                    
                    <a href="<?php echo getBaseUrl(); ?>contact" class="boton boton-terciario">
                        <i class="fas fa-envelope"></i>
                        Contacto
                    </a>
                </div>

                <div class="help-section">
                    <h3>¿Necesita ayuda?</h3>
                    <p>Si continúa experimentando problemas, puede:</p>
                    <ul>
                        <li>Contactar al departamento de sistemas: <a href="mailto:sistemas@figgerenergy.gov.co">sistemas@figgerenergy.gov.co</a></li>
                        <li>Llamar a la línea de soporte: <a href="tel:+570180000001">+57 0180000001</a></li>
                        <li>Reportar el problema a través del <a href="<?php echo getBaseUrl(); ?>contact">formulario de contacto</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Enlaces Útiles -->
        <div class="useful-links">
            <h3>Enlaces Útiles</h3>
            <div class="links-grid">
                <div class="link-category">
                    <h4>Información General</h4>
                    <ul>
                        <li><a href="<?php echo getBaseUrl(); ?>#mision">Misión Institucional</a></li>
                        <li><a href="<?php echo getBaseUrl(); ?>#operaciones">Nuestras Operaciones</a></li>
                        <li><a href="<?php echo getBaseUrl(); ?>#cumplimiento">Cumplimiento ISO 27001</a></li>
                    </ul>
                </div>
                
                <div class="link-category">
                    <h4>Servicios</h4>
                    <ul>
                        <li><a href="<?php echo getBaseUrl(); ?>#monitoreo">Estado del Monitoreo</a></li>
                        <li><a href="<?php echo getBaseUrl(); ?>contact">Reportar Actividad Ilegal</a></li>
                        <li><a href="<?php echo getBaseUrl(); ?>contact">Solicitar Información</a></li>
                    </ul>
                </div>

                <?php if (AuthMiddleware::isAuthenticated()): ?>
                <div class="link-category">
                    <h4>Sistema</h4>
                    <ul>
                        <li><a href="<?php echo getBaseUrl() . 'dashboard/' . AuthMiddleware::getCurrentUser()['role']; ?>">Mi Dashboard</a></li>
                        <li><a href="<?php echo getBaseUrl(); ?>logout">Cerrar Sesión</a></li>
                    </ul>
                </div>
                <?php else: ?>
                <div class="link-category">
                    <h4>Acceso</h4>
                    <ul>
                        <li><a href="<?php echo getBaseUrl(); ?>login">Iniciar Sesión</a></li>
                        <li><a href="<?php echo getBaseUrl(); ?>register">Registro de Usuarios</a></li>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
