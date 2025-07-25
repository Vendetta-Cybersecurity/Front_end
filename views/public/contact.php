<?php
/**
 * Página de Contacto - Figger Energy SAS
 * Sistema de contacto refactorizado
 */

session_start();

// Cargar dependencias
require_once '../../config/database.php';
require_once '../../lib/functions.php';

$titulo_pagina = "Contacto Institucional";
$mensaje = '';
$tipo_mensaje = '';

// Procesar formulario de contacto
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Definir reglas de validación
    $reglas = [
        'nombre' => ['requerido' => true, 'min_length' => 2, 'max_length' => 100],
        'email' => ['requerido' => true, 'email' => true],
        'telefono' => ['requerido' => true, 'min_length' => 7],
        'asunto' => ['requerido' => true, 'min_length' => 5, 'max_length' => 200],
        'mensaje' => ['requerido' => true, 'min_length' => 10],
        'tipo_consulta' => ['requerido' => true]
    ];
    
    // Validar datos
    $errores = validarFormulario($reglas, $_POST);
    
    if (empty($errores)) {
        $nombre = limpiarDatos($_POST['nombre']);
        $email = limpiarDatos($_POST['email']);
        $telefono = limpiarDatos($_POST['telefono']);
        $asunto = limpiarDatos($_POST['asunto']);
        $mensaje_contacto = limpiarDatos($_POST['mensaje']);
        $tipo_consulta = limpiarDatos($_POST['tipo_consulta']);
        
        $conexion = conectarDB();
        
        // Insertar en tabla de contactos
        $stmt = $conexion->prepare("INSERT INTO contactos (nombre, email, telefono, asunto, mensaje, tipo_consulta, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
        $stmt->bind_param("sssssss", $nombre, $email, $telefono, $asunto, $mensaje_contacto, $tipo_consulta, $ip);
        
        if ($stmt->execute()) {
            $mensaje = 'Su mensaje ha sido enviado exitosamente. Nos pondremos en contacto con usted en un plazo máximo de 48 horas hábiles.';
            $tipo_mensaje = 'exito';
            
            // Limpiar formulario
            $_POST = [];
        } else {
            $mensaje = 'Error al enviar el mensaje. Por favor intente nuevamente.';
            $tipo_mensaje = 'error';
        }
        
        $stmt->close();
        cerrarDB($conexion);
    } else {
        $mensaje = implode('<br>', $errores);
        $tipo_mensaje = 'error';
    }
}

include '../../includes/header.php';
?>

<main class="contenido-principal">
    <section class="seccion">
        <div class="contenedor">
            <div class="texto-centro mb-2">
                <h1>Contacto Institucional</h1>
                <p>Comuníquese con Figger Energy SAS para consultas, denuncias y solicitudes de información</p>
            </div>
            
            <?php echo mostrarMensaje($mensaje, $tipo_mensaje); ?>
            
            <!-- Información de Contacto -->
            <div class="dashboard-grid mb-2">
                <div class="tarjeta">
                    <h3>📞 Líneas de Atención</h3>
                    <p><strong>Línea Principal:</strong> +57 (1) 234-5678</p>
                    <p><strong>Línea de Emergencia:</strong> +57 018000001</p>
                    <p><strong>WhatsApp:</strong> +57 300-123-4567</p>
                    <p><strong>Horario:</strong> Lunes a Viernes 8:00 AM - 5:00 PM</p>
                </div>
                
                <div class="tarjeta">
                    <h3>📧 Correos Electrónicos</h3>
                    <p><strong>Información general:</strong> info@figgerenergy.gov.co</p>
                    <p><strong>Denuncias:</strong> denuncias@figgerenergy.gov.co</p>
                    <p><strong>Emergencias:</strong> emergencias@figgerenergy.gov.co</p>
                    <p><strong>Prensa:</strong> prensa@figgerenergy.gov.co</p>
                </div>
                
                <div class="tarjeta">
                    <h3>📍 Ubicación</h3>
                    <p><strong>Dirección:</strong> Carrera 7 #32-16, Piso 15</p>
                    <p><strong>Ciudad:</strong> Bogotá D.C., Colombia</p>
                    <p><strong>Código Postal:</strong> 110311</p>
                    <p><strong>Atención al público:</strong> Previa cita</p>
                </div>
                
                <div class="tarjeta">
                    <h3>⏰ Horarios de Atención</h3>
                    <p><strong>Lunes a Viernes:</strong> 8:00 AM - 5:00 PM</p>
                    <p><strong>Sábados:</strong> 8:00 AM - 12:00 PM</p>
                    <p><strong>Domingos:</strong> Cerrado</p>
                    <p><strong>Emergencias:</strong> 24/7</p>
                </div>
            </div>
            
            <!-- Formulario de Contacto -->
            <form method="POST" action="contact.php" class="formulario">
                <h2>📝 Formulario de Contacto</h2>
                
                <div class="grupo-campo">
                    <label for="tipo_consulta">Tipo de Consulta:</label>
                    <select id="tipo_consulta" name="tipo_consulta" required>
                        <option value="">Seleccione el tipo de consulta</option>
                        <option value="denuncia" <?php echo (isset($_POST['tipo_consulta']) && $_POST['tipo_consulta'] == 'denuncia') ? 'selected' : ''; ?>>🚨 Denuncia de Minería Ilegal</option>
                        <option value="informacion" <?php echo (isset($_POST['tipo_consulta']) && $_POST['tipo_consulta'] == 'informacion') ? 'selected' : ''; ?>>ℹ️ Solicitud de Información</option>
                        <option value="colaboracion" <?php echo (isset($_POST['tipo_consulta']) && $_POST['tipo_consulta'] == 'colaboracion') ? 'selected' : ''; ?>>🤝 Propuesta de Colaboración</option>
                        <option value="prensa" <?php echo (isset($_POST['tipo_consulta']) && $_POST['tipo_consulta'] == 'prensa') ? 'selected' : ''; ?>>📰 Consulta de Prensa</option>
                        <option value="pqrs" <?php echo (isset($_POST['tipo_consulta']) && $_POST['tipo_consulta'] == 'pqrs') ? 'selected' : ''; ?>>📋 PQRS</option>
                        <option value="otro" <?php echo (isset($_POST['tipo_consulta']) && $_POST['tipo_consulta'] == 'otro') ? 'selected' : ''; ?>>📌 Otro</option>
                    </select>
                </div>
                
                <div class="formulario-grid">
                    <div class="grupo-campo">
                        <label for="nombre">Nombre Completo:</label>
                        <input type="text" 
                               id="nombre" 
                               name="nombre" 
                               required 
                               placeholder="Ingrese su nombre completo"
                               value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
                    </div>
                    
                    <div class="grupo-campo">
                        <label for="email">Correo Electrónico:</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               required 
                               placeholder="ejemplo@correo.com"
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                </div>
                
                <div class="formulario-grid">
                    <div class="grupo-campo">
                        <label for="telefono">Teléfono:</label>
                        <input type="tel" 
                               id="telefono" 
                               name="telefono" 
                               required 
                               placeholder="+57 300 123 4567"
                               value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>">
                    </div>
                    
                    <div class="grupo-campo">
                        <label for="asunto">Asunto:</label>
                        <input type="text" 
                               id="asunto" 
                               name="asunto" 
                               required 
                               placeholder="Resumen del tema"
                               value="<?php echo isset($_POST['asunto']) ? htmlspecialchars($_POST['asunto']) : ''; ?>">
                    </div>
                </div>
                
                <div class="grupo-campo">
                    <label for="mensaje">Mensaje:</label>
                    <textarea id="mensaje" 
                              name="mensaje" 
                              required 
                              rows="6" 
                              placeholder="Describa detalladamente su consulta, denuncia o solicitud. Para denuncias, incluya ubicación específica, fecha y hora si es posible."><?php echo isset($_POST['mensaje']) ? htmlspecialchars($_POST['mensaje']) : ''; ?></textarea>
                    <small>Mínimo 10 caracteres. Para denuncias urgentes, use la línea de emergencia.</small>
                </div>
                
                <div class="grupo-campo">
                    <button type="submit" class="boton boton-primario boton-completo">Enviar Mensaje</button>
                </div>
            </form>
            
            <!-- Información Adicional -->
            <div class="dashboard-grid mt-2">
                <div class="tarjeta">
                    <h3>🚨 Denuncias Urgentes</h3>
                    <p>Si está presenciando actividad minera ilegal en curso, no utilice este formulario.</p>
                    <p><strong>Línea de Emergencia:</strong></p>
                    <p style="font-size: 1.2rem; color: var(--color-error);"><strong>📞 +57 018000001</strong></p>
                    <p>Disponible 24 horas, 7 días a la semana</p>
                </div>
                
                <div class="tarjeta">
                    <h3>🔒 Confidencialidad</h3>
                    <p>Toda la información proporcionada es tratada con estricta confidencialidad según la normativa vigente.</p>
                    <p>Los datos personales son protegidos según la Ley 1581 de 2012.</p>
                </div>
                
                <div class="tarjeta">
                    <h3>⏱️ Tiempos de Respuesta</h3>
                    <p><strong>Denuncias:</strong> Máximo 6 horas</p>
                    <p><strong>Información general:</strong> 24-48 horas</p>
                    <p><strong>PQRS:</strong> Máximo 15 días hábiles</p>
                    <p><strong>Prensa:</strong> 24 horas</p>
                </div>
                
                <div class="tarjeta">
                    <h3>🌐 Otros Canales</h3>
                    <p><strong>Redes Sociales:</strong></p>
                    <ul style="text-align: left;">
                        <li><a href="#" onclick="alert('Función en desarrollo')">Twitter Oficial</a></li>
                        <li><a href="#" onclick="alert('Función en desarrollo')">Facebook</a></li>
                        <li><a href="#" onclick="alert('Función en desarrollo')">LinkedIn</a></li>
                        <li><a href="#" onclick="alert('Función en desarrollo')">Canal de YouTube</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Enlaces útiles -->
            <div class="seccion-adicional">
                <h2>🔗 Enlaces de Interés</h2>
                <div class="dashboard-grid">
                    <div class="tarjeta">
                        <h4>📄 Documentación</h4>
                        <ul style="text-align: left;">
                            <li><a href="#" onclick="alert('Función en desarrollo')">Manual del Denunciante</a></li>
                            <li><a href="#" onclick="alert('Función en desarrollo')">Marco Legal</a></li>
                            <li><a href="#" onclick="alert('Función en desarrollo')">Informes Anuales</a></li>
                        </ul>
                    </div>
                    
                    <div class="tarjeta">
                        <h4>🏛️ Entidades Relacionadas</h4>
                        <ul style="text-align: left;">
                            <li><a href="#" onclick="alert('Función en desarrollo')">Ministerio de Minas</a></li>
                            <li><a href="#" onclick="alert('Función en desarrollo')">ANLA</a></li>
                            <li><a href="#" onclick="alert('Función en desarrollo')">Policía Nacional</a></li>
                        </ul>
                    </div>
                    
                    <div class="tarjeta">
                        <h4>🎓 Educación</h4>
                        <ul style="text-align: left;">
                            <li><a href="#" onclick="alert('Función en desarrollo')">Capacitaciones</a></li>
                            <li><a href="#" onclick="alert('Función en desarrollo')">Material Educativo</a></li>
                            <li><a href="#" onclick="alert('Función en desarrollo')">Webinars</a></li>
                        </ul>
                    </div>
                    
                    <div class="tarjeta">
                        <h4>👥 Participación</h4>
                        <ul style="text-align: left;">
                            <li><a href="#" onclick="alert('Función en desarrollo')">Comités Ciudadanos</a></li>
                            <li><a href="#" onclick="alert('Función en desarrollo')">PQRSF Virtual</a></li>
                            <li><a href="#" onclick="alert('Función en desarrollo')">Participación Ciudadana</a></li>
                            <li><a href="#" onclick="alert('Función en desarrollo')">Centro de Documentos</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include '../../includes/footer.php'; ?>
