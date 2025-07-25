<?php
/**
 * Página Principal - Figger Energy SAS
 * Página de inicio refactorizada
 */

session_start();

// Cargar dependencias
require_once 'lib/functions.php';

$titulo_pagina = "Inicio";

// Verificar si el usuario acaba de hacer logout
$mensaje_logout = '';
if (isset($_GET['logout']) && $_GET['logout'] == '1') {
    $mensaje_logout = 'Ha cerrado sesión correctamente. Gracias por usar el sistema de Figger Energy SAS.';
}

include 'includes/header.php';
?>

<main class="contenido-principal">
    <?php if (!empty($mensaje_logout)): ?>
        <div class="contenedor">
            <div class="mensaje mensaje-exito mensaje-temporal" id="mensaje-logout">
                <?php echo htmlspecialchars($mensaje_logout); ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Hero Section -->
    <section class="hero">
        <div class="contenedor">
            <h1>Figger Energy SAS</h1>
            <p>Entidad Gubernamental Colombiana especializada en el monitoreo y control de actividades de minería ilegal que extraen materiales críticos para energías renovables</p>
            
            <?php if (!verificarAuth()): ?>
                <a href="views/auth/login.php" class="boton boton-primario">Acceder al Sistema</a>
                <a href="views/public/contact.php" class="boton">Contactar</a>
            <?php else: ?>
                <a href="<?php echo getDashboardUrl($_SESSION['rol']); ?>" class="boton boton-primario">Ir al Dashboard</a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Sección Sobre Nosotros -->
    <section class="seccion">
        <div class="contenedor">
            <div class="texto-centro mb-2">
                <h2>Sobre Nosotros</h2>
                <p>Conoce nuestra misión, visión y las funciones principales que desarrollamos para proteger los recursos naturales colombianos</p>
            </div>
            
            <div class="grid-caracteristicas">
                <div class="caracteristica">
                    <h3>🎯 Nuestra Misión</h3>
                    <p>Proteger los recursos minerales críticos para energías renovables mediante el monitoreo, control y prevención de actividades de minería ilegal en territorio colombiano, garantizando la sostenibilidad ambiental y el desarrollo energético del país.</p>
                </div>
                
                <div class="caracteristica">
                    <h3>👁️ Nuestra Visión</h3>
                    <p>Ser la entidad gubernamental líder en la protección de recursos minerales estratégicos, reconocida por su excelencia en tecnología de monitoreo, efectividad operacional y compromiso con la preservación del medio ambiente.</p>
                </div>
                
                <div class="caracteristica">
                    <h3>⚖️ Nuestros Valores</h3>
                    <p>Transparencia, responsabilidad ambiental, innovación tecnológica, integridad institucional y compromiso con el desarrollo sostenible de Colombia y sus comunidades.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Funciones Principales -->
    <section class="seccion seccion-alternativa">
        <div class="contenedor">
            <div class="texto-centro mb-2">
                <h2>Nuestras Funciones Principales</h2>
                <p>Desarrollamos actividades especializadas para garantizar el control efectivo de la minería ilegal</p>
            </div>
            
            <div class="grid-triangular">
                <div class="caracteristica triangular-item-1">
                    <h3>🛰️ Vigilancia Satelital</h3>
                    <p>Utilizamos tecnología satelital avanzada para detectar y monitorear actividades de minería ilegal en tiempo real, cubriendo todo el territorio nacional con especial énfasis en zonas de alta sensibilidad ambiental.</p>
                    <ul style="text-align: center; margin-top: 1rem;">
                        <li>Monitoreo 24/7 del territorio nacional</li>
                        <li>Detección temprana de actividades sospechosas</li>
                        <li>Análisis de imágenes satelitales</li>
                        <li>Sistema de alertas automático</li>
                    </ul>
                </div>
                
                <div class="caracteristica triangular-item-2">
                    <h3>🚁 Operaciones de Campo</h3>
                    <p>Realizamos verificaciones presenciales y operativos de control en zonas identificadas como de alto riesgo, coordinando con fuerzas militares y autoridades ambientales para garantizar el cumplimiento normativo.</p>
                    <ul style="text-align: center; margin-top: 1rem;">
                        <li>Inspecciones in situ</li>
                        <li>Verificación de denuncias</li>
                        <li>Coordinación interinstitucional</li>
                        <li>Operativos de control</li>
                    </ul>
                </div>
                
                <div class="caracteristica triangular-item-3">
                    <h3>🌱 Protección Ambiental</h3>
                    <p>Trabajamos en la preservación de ecosistemas críticos y la restauración de áreas afectadas por minería ilegal, promoviendo prácticas sostenibles y la protección de la biodiversidad nacional.</p>
                    <ul style="text-align: center; margin-top: 1rem;">
                        <li>Restauración de ecosistemas</li>
                        <li>Monitoreo ambiental</li>
                        <li>Programas de conservación</li>
                        <li>Educación ambiental</li>
                    </ul>
                </div>
                
                <div class="caracteristica triangular-item-4">
                    <h3>📊 Análisis de Datos</h3>
                    <p>Procesamos y analizamos grandes volúmenes de información para identificar patrones, tendencias y zonas de riesgo, generando inteligencia estratégica para la toma de decisiones gubernamentales.</p>
                    <ul style="text-align: center; margin-top: 1rem;">
                        <li>Análisis predictivo</li>
                        <li>Identificación de patrones</li>
                        <li>Reportes especializados</li>
                        <li>Inteligencia estratégica</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Estadísticas Dinámicas -->
    <section class="seccion" id="estadisticas-seccion">
        <div class="contenedor">
            <div class="texto-centro mb-2">
                <h2>Resultados e Impacto</h2>
                <p>Nuestro trabajo genera resultados medibles en la protección del territorio nacional</p>
                <div class="ultima-actualizacion">
                    <small>🔄 Última actualización: <span id="ultima-actualizacion">Cargando...</span></small>
                </div>
            </div>
            
            <div class="grid-triangular">
                <div class="caracteristica triangular-item-1">
                    <h3>🏞️ Territorio Protegido</h3>
                    <p><strong><span id="territorio-protegido">2.6 millones</span></strong> de hectáreas bajo monitoreo permanente</p>
                    <small>Incluyendo parques nacionales y reservas forestales</small>
                </div>
                
                <div class="caracteristica triangular-item-2">
                    <h3>🚨 Alertas Procesadas</h3>
                    <p><strong><span id="alertas-procesadas">1,236</span></strong> alertas verificadas en 2024</p>
                    <small>Con tiempo promedio de respuesta de <span id="tiempo-respuesta">6</span> horas</small>
                </div>
                
                <div class="caracteristica triangular-item-3">
                    <h3>🌿 Áreas Restauradas</h3>
                    <p><strong><span id="areas-restauradas">14,526</span></strong> hectáreas en proceso de restauración</p>
                    <small>Programas de reforestación y recuperación</small>
                </div>
                
                <div class="caracteristica triangular-item-4">
                    <h3>⚖️ Casos Resueltos</h3>
                    <p><strong><span id="casos-resueltos">90</span>%</strong> de efectividad en investigaciones</p>
                    <small>Coordinación con autoridades judiciales</small>
                </div>
            </div>
        </div>
    </section>

    <!-- Contacto Rápido -->
    <section class="seccion seccion-alternativa">
        <div class="contenedor">
            <div class="texto-centro">
                <h2>¿Conoce Actividad Minera Ilegal?</h2>
                <p>Reporte inmediatamente cualquier actividad sospechosa. Su denuncia es confidencial y contribuye a la protección del medio ambiente.</p>
                
                <div style="margin: 2rem 0;">
                    <h3 style="color: var(--color-error); margin-bottom: 1rem;">Línea de Emergencia 24/7</h3>
                    <p style="font-size: 1.5rem; font-weight: bold;">📞 +57 018000001</p>
                    <p>📧 emergencias@figgerenergy.gov.co</p>
                </div>
                
                <a href="views/public/contact.php" class="boton boton-primario">Formulario de Contacto</a>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
