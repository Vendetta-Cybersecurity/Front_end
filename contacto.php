<?php
/**
 * Página de Contacto - Figger Energy SAS
 * Formulario de contacto institucional con envío por email
 */

session_start();

$titulo_pagina = "Contacto Institucional";
$mensaje = '';
$tipo_mensaje = '';

// Procesar formulario de contacto
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'includes/db.php';
    
    $nombre = limpiarDatos($_POST['nombre']);
    $email = limpiarDatos($_POST['email']);
    $asunto = limpiarDatos($_POST['asunto']);
    $mensaje_contacto = limpiarDatos($_POST['mensaje']);
    $tipo_contacto = limpiarDatos($_POST['tipo_contacto']);
    
    // Validaciones básicas
    $errores = [];
    
    if (strlen($nombre) < 3) {
        $errores[] = 'El nombre debe tener al menos 3 caracteres.';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Formato de email inválido.';
    }
    
    if (strlen($asunto) < 5) {
        $errores[] = 'El asunto debe tener al menos 5 caracteres.';
    }
    
    if (strlen($mensaje_contacto) < 20) {
        $errores[] = 'El mensaje debe tener al menos 20 caracteres.';
    }
    
    if (empty($errores)) {
        $conexion = conectarDB();
        
        // Guardar en base de datos
        $stmt = $conexion->prepare("INSERT INTO contactos (nombre, email, asunto, mensaje, fecha_envio) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $nombre, $email, $asunto, $mensaje_contacto);
        
        if ($stmt->execute()) {
            // Intentar enviar email (función básica)
            $para = "contacto@figgerenergy.gov.co";
            $asunto_email = "[Figger Energy] " . $asunto;
            $cuerpo_email = "
Nuevo mensaje de contacto desde el sitio web

Nombre: $nombre
Email: $email
Tipo de consulta: $tipo_contacto
Asunto: $asunto

Mensaje:
$mensaje_contacto

---
Enviado desde: " . $_SERVER['HTTP_HOST'] . "
Fecha: " . date('d/m/Y H:i:s') . "
IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'desconocida');

            $headers = "From: $email\r\n";
            $headers .= "Reply-To: $email\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
            
            // Intentar enviar email (puede fallar si no hay servidor de correo configurado)
            $email_enviado = @mail($para, $asunto_email, $cuerpo_email, $headers);
            
            if ($email_enviado) {
                $mensaje = 'Su mensaje ha sido enviado correctamente. Nos pondremos en contacto con usted a la brevedad.';
            } else {
                $mensaje = 'Su mensaje ha sido registrado en nuestro sistema. Nos pondremos en contacto con usted a la brevedad.';
            }
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

include 'includes/header.php';
?>

<main class="contenido-principal">
    <section class="seccion">
        <div class="contenedor">
            <div class="texto-centro mb-2">
                <h1>Contacto Institucional</h1>
                <p>Comuníquese con Figger Energy SAS para consultas, denuncias y solicitudes de información</p>
            </div>
            
            <?php if (!empty($mensaje)): ?>
                <div class="mensaje mensaje-<?php echo $tipo_mensaje; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>
            
            <!-- Información de Contacto -->
            <div class="dashboard-grid mb-2">
                <div class="tarjeta">
                    <h3>📞 Líneas de Atención</h3>
                    <p><strong>Línea Principal:</strong><br>+57 (1) 123-4567</p>
                    <p><strong>Emergencias 24/7:</strong><br>+57 (1) 987-6543</p>
                    <p><strong>WhatsApp:</strong><br>+57 300 123 4567</p>
                    <p><strong>Horario:</strong><br>Lunes a Viernes<br>8:00 AM - 5:00 PM</p>
                </div>
                
                <div class="tarjeta">
                    <h3>📧 Emails Institucionales</h3>
                    <p><strong>Contacto General:</strong><br>contacto@figgerenergy.gov.co</p>
                    <p><strong>Denuncias:</strong><br>denuncias@figgerenergy.gov.co</p>
                    <p><strong>Prensa:</strong><br>prensa@figgerenergy.gov.co</p>
                    <p><strong>Soporte Técnico:</strong><br>soporte@figgerenergy.gov.co</p>
                </div>
                
                <div class="tarjeta">
                    <h3>🏢 Oficina Principal</h3>
                    <p><strong>Dirección:</strong><br>
                    Carrera 7 No. 24-89<br>
                    Edificio Figger Energy<br>
                    Bogotá D.C., Colombia</p>
                    <p><strong>Código Postal:</strong> 110311</p>
                    <p><strong>Ubicación:</strong> Zona Centro</p>
                </div>
                
                <div class="tarjeta">
                    <h3>🚨 Emergencias</h3>
                    <p><strong>Reporte de Minería Ilegal:</strong></p>
                    <p>📞 Línea directa: +57 (1) 987-6543</p>
                    <p>📧 emergencias@figgerenergy.gov.co</p>
                    <p><strong>Disponible 24 horas / 7 días</strong></p>
                    <p style="color: red;"><strong>Para emergencias ambientales inmediatas</strong></p>
                </div>
            </div>
            
            <!-- Formulario de Contacto -->
            <form id="form-contacto" method="POST" action="contacto.php" class="formulario">
                <h2 class="texto-centro mb-1">Enviar Mensaje</h2>
                
                <div class="grupo-campo">
                    <label for="nombre">Nombre Completo:</label>
                    <input type="text" 
                           id="nombre" 
                           name="nombre" 
                           required 
                           placeholder="Ej: Juan Carlos Pérez"
                           value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
                </div>
                
                <div class="grupo-campo">
                    <label for="email">Email de Contacto:</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           required 
                           placeholder="usuario@ejemplo.com"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="grupo-campo">
                    <label for="tipo_contacto">Tipo de Consulta:</label>
                    <select id="tipo_contacto" name="tipo_contacto" required>
                        <option value="">Seleccione el tipo de consulta</option>
                        <option value="denuncia" <?php echo (isset($_POST['tipo_contacto']) && $_POST['tipo_contacto'] == 'denuncia') ? 'selected' : ''; ?>>
                            🚨 Denuncia de Minería Ilegal
                        </option>
                        <option value="informacion" <?php echo (isset($_POST['tipo_contacto']) && $_POST['tipo_contacto'] == 'informacion') ? 'selected' : ''; ?>>
                            ℹ️ Solicitud de Información
                        </option>
                        <option value="colaboracion" <?php echo (isset($_POST['tipo_contacto']) && $_POST['tipo_contacto'] == 'colaboracion') ? 'selected' : ''; ?>>
                            🤝 Propuesta de Colaboración
                        </option>
                        <option value="prensa" <?php echo (isset($_POST['tipo_contacto']) && $_POST['tipo_contacto'] == 'prensa') ? 'selected' : ''; ?>>
                            📰 Consulta de Prensa
                        </option>
                        <option value="tecnico" <?php echo (isset($_POST['tipo_contacto']) && $_POST['tipo_contacto'] == 'tecnico') ? 'selected' : ''; ?>>
                            🔧 Soporte Técnico
                        </option>
                        <option value="otro" <?php echo (isset($_POST['tipo_contacto']) && $_POST['tipo_contacto'] == 'otro') ? 'selected' : ''; ?>>
                            📝 Otro
                        </option>
                    </select>
                </div>
                
                <div class="grupo-campo">
                    <label for="asunto">Asunto:</label>
                    <input type="text" 
                           id="asunto" 
                           name="asunto" 
                           required 
                           placeholder="Resumen del tema a tratar"
                           value="<?php echo isset($_POST['asunto']) ? htmlspecialchars($_POST['asunto']) : ''; ?>">
                </div>
                
                <div class="grupo-campo">
                    <label for="mensaje">Mensaje:</label>
                    <textarea id="mensaje" 
                              name="mensaje" 
                              required 
                              placeholder="Describa detalladamente su consulta, denuncia o solicitud..."
                              rows="6"><?php echo isset($_POST['mensaje']) ? htmlspecialchars($_POST['mensaje']) : ''; ?></textarea>
                    <small>Mínimo 20 caracteres. Sea específico y detallado en su mensaje.</small>
                </div>
                
                <div class="grupo-campo">
                    <label>
                        <input type="checkbox" required> 
                        Autorizo el tratamiento de mis datos personales conforme a la Ley 1581 de 2012 y acepto recibir respuesta a mi consulta.
                    </label>
                </div>
                
                <div class="grupo-campo">
                    <button type="submit" class="boton boton-primario" style="width: 100%;">
                        Enviar Mensaje
                    </button>
                </div>
            </form>
            
            <!-- Información Adicional -->
            <div class="dashboard-grid mt-2">
                <div class="tarjeta">
                    <h3>⏱️ Tiempos de Respuesta</h3>
                    <ul style="text-align: left;">
                        <li><strong>Emergencias:</strong> Inmediata (24/7)</li>
                        <li><strong>Denuncias:</strong> 24-48 horas</li>
                        <li><strong>Consultas generales:</strong> 3-5 días hábiles</li>
                        <li><strong>Solicitudes de información:</strong> 10-15 días hábiles</li>
                    </ul>
                </div>
                
                <div class="tarjeta">
                    <h3>📋 Información Requerida</h3>
                    <p><strong>Para denuncias de minería ilegal incluya:</strong></p>
                    <ul style="text-align: left;">
                        <li>Ubicación exacta o coordenadas</li>
                        <li>Fecha y hora de observación</li>
                        <li>Descripción detallada de la actividad</li>
                        <li>Fotografías o evidencias (si es seguro)</li>
                        <li>Sus datos de contacto para seguimiento</li>
                    </ul>
                </div>
                
                <div class="tarjeta">
                    <h3>🔒 Confidencialidad</h3>
                    <p>Sus datos están protegidos bajo:</p>
                    <ul style="text-align: left;">
                        <li>Ley 1581 de 2012 (Protección de datos)</li>
                        <li>Ley 1712 de 2014 (Transparencia)</li>
                        <li>Política de privacidad institucional</li>
                        <li>Protección de denunciantes</li>
                    </ul>
                </div>
                
                <div class="tarjeta">
                    <h3>🌐 Otros Canales</h3>
                    <ul style="text-align: left;">
                        <li><a href="#" onclick="alert('Función en desarrollo')">Portal de Transparencia</a></li>
                        <li><a href="#" onclick="alert('Función en desarrollo')">PQRSF Virtual</a></li>
                        <li><a href="#" onclick="alert('Función en desarrollo')">Participación Ciudadana</a></li>
                        <li><a href="#" onclick="alert('Función en desarrollo')">Centro de Documentos</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
