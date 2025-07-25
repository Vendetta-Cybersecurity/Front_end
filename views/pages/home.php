<?php if (!empty($logoutMessage)): ?>
    <div class="contenedor">
        <div class="mensaje mensaje-exito mensaje-temporal">
            <?php echo htmlspecialchars($logoutMessage); ?>
        </div>
    </div>
<?php endif; ?>

<!-- Hero Section -->
<section class="hero">
    <div class="contenedor">
        <h1>Figger Energy SAS</h1>
        <h2>Unidad Gubernamental de Monitoreo de Minería Ilegal</h2>
        <p>Identificación y control de minas ilegales que extraen materiales para energías renovables en territorio colombiano</p>
        <div class="hero-buttons">
            <a href="#operaciones" class="boton boton-primario">Nuestras Operaciones</a>
            <?php if (AuthMiddleware::isAuthenticated()): ?>
                <a href="<?php echo getBaseUrl() . 'dashboard/' . AuthMiddleware::getCurrentUser()['role']; ?>" class="boton boton-secundario">Ir al Dashboard</a>
            <?php else: ?>
                <a href="<?php echo getBaseUrl(); ?>login" class="boton boton-secundario">Acceso al Sistema</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Misión Institucional -->
<section id="mision" class="mission">
    <div class="contenedor">
        <div class="section-header">
            <h2>Misión Institucional</h2>
            <p>Protegiendo los recursos naturales de Colombia</p>
        </div>
        <div class="mission-content">
            <div class="mission-grid">
                <div class="mission-card">
                    <div class="card-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Misión</h3>
                    <p>Identificar, monitorear y controlar las actividades de minería ilegal que extraen materiales críticos para energías renovables, protegiendo el medio ambiente y garantizando el uso sostenible de los recursos naturales colombianos.</p>
                </div>
                <div class="mission-card">
                    <div class="card-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3>Visión</h3>
                    <p>Ser la unidad gubernamental líder en América Latina en el control de la minería ilegal de materiales para energías renovables, utilizando tecnología avanzada y cumpliendo los más altos estándares internacionales de seguridad de la información.</p>
                </div>
                <div class="mission-card">
                    <div class="card-icon">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <h3>Valores Institucionales</h3>
                    <ul>
                        <li>Transparencia gubernamental</li>
                        <li>Seguridad de la información</li>
                        <li>Protección ambiental</li>
                        <li>Cumplimiento normativo</li>
                        <li>Innovación tecnológica</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Operaciones -->
<section id="operaciones" class="operations">
    <div class="contenedor">
        <div class="section-header">
            <h2>Nuestras Operaciones</h2>
            <p>Estrategias integrales de monitoreo y control</p>
        </div>
        <div class="operations-grid">
            <div class="operation-card">
                <div class="operation-icon">
                    <i class="fas fa-satellite"></i>
                </div>
                <h3>Monitoreo Satelital</h3>
                <p>Vigilancia constante del territorio nacional mediante tecnología satelital avanzada para detectar actividades mineras ilegales.</p>
                <ul>
                    <li>Imágenes de alta resolución</li>
                    <li>Análisis geoespacial</li>
                    <li>Detección de cambios</li>
                    <li>Alertas tempranas</li>
                </ul>
            </div>
            <div class="operation-card">
                <div class="operation-icon">
                    <i class="fas fa-users-cog"></i>
                </div>
                <h3>Operaciones de Campo</h3>
                <p>Equipos especializados en terreno para verificación directa y recolección de evidencias en sitios identificados.</p>
                <ul>
                    <li>Inspecciones técnicas</li>
                    <li>Recolección de evidencias</li>
                    <li>Coordinación interinstitucional</li>
                    <li>Reportes detallados</li>
                </ul>
            </div>
            <div class="operation-card">
                <div class="operation-icon">
                    <i class="fas fa-database"></i>
                </div>
                <h3>Análisis de Datos</h3>
                <p>Procesamiento y análisis de información recolectada para generar inteligencia estratégica y reportes oficiales.</p>
                <ul>
                    <li>Big data analytics</li>
                    <li>Inteligencia artificial</li>
                    <li>Reportes automatizados</li>
                    <li>Dashboards ejecutivos</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Estado del Monitoreo -->
<section id="monitoreo" class="monitoring">
    <div class="contenedor">
        <div class="section-header">
            <h2>Estado del Monitoreo</h2>
            <p>Situación actual de nuestras operaciones</p>
        </div>
        <div class="monitoring-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <div class="stat-info">
                    <h3 data-count="2847">2,847</h3>
                    <p>Sitios Monitoreados</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-info">
                    <h3 data-count="143">143</h3>
                    <p>Alertas Activas</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3 data-count="89">89</h3>
                    <p>Empleados Activos</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-info">
                    <h3 data-count="312">312</h3>
                    <p>Reportes Mensuales</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Cumplimiento ISO 27001 -->
<section id="cumplimiento" class="compliance">
    <div class="contenedor">
        <div class="section-header">
            <h2>Cumplimiento ISO 27001</h2>
            <p>Estándares internacionales de seguridad de la información</p>
        </div>
        <div class="compliance-grid">
            <div class="compliance-card">
                <div class="compliance-icon">
                    <i class="fas fa-shield-virus"></i>
                </div>
                <h3>A.5 Políticas de Seguridad</h3>
                <p>Políticas institucionales de seguridad</p>
            </div>
            <div class="compliance-card">
                <div class="compliance-icon">
                    <i class="fas fa-database"></i>
                </div>
                <h3>A.8 Gestión de Activos</h3>
                <p>Control y gestión de activos de información</p>
            </div>
            <div class="compliance-card">
                <div class="compliance-icon">
                    <i class="fas fa-user-lock"></i>
                </div>
                <h3>A.9 Control de Acceso</h3>
                <p>Autenticación y autorización robusta</p>
            </div>
            <div class="compliance-card">
                <div class="compliance-icon">
                    <i class="fas fa-key"></i>
                </div>
                <h3>A.10 Criptografía</h3>
                <p>Protección criptográfica de datos</p>
            </div>
            <div class="compliance-card">
                <div class="compliance-icon">
                    <i class="fas fa-cogs"></i>
                </div>
                <h3>A.12 Seguridad Operacional</h3>
                <p>Operación segura de sistemas</p>
            </div>
            <div class="compliance-card">
                <div class="compliance-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <h3>A.16 Gestión de Incidentes</h3>
                <p>Manejo de incidentes de seguridad</p>
            </div>
        </div>
    </div>
</section>

<!-- Contacto -->
<section id="contacto" class="contact">
    <div class="contenedor">
        <div class="section-header">
            <h2>Contacto Institucional</h2>
            <p>Información oficial de contacto</p>
        </div>
        <div class="contact-content">
            <div class="contact-info">
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="contact-details">
                        <h4>Sede Principal</h4>
                        <p>Calle 1, Carrera 1, Edificio 1<br>Macondo, Colombia</p>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="contact-details">
                        <h4>Línea Nacional</h4>
                        <p>+57 0180000001</p>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="contact-details">
                        <h4>Email Institucional</h4>
                        <p>contacto@figgerenergy.gov.co</p>
                    </div>
                </div>
            </div>
            <div class="contact-form">
                <p><a href="<?php echo getBaseUrl(); ?>contact" class="boton boton-primario">Formulario de Contacto Completo</a></p>
            </div>
        </div>
    </div>
</section>
