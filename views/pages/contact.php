<?php if (!empty($message)): ?>
    <div class="contenedor">
        <div class="mensaje <?php echo isset($messageType) ? 'mensaje-' . $messageType : ''; ?> mensaje-temporal">
            <?php echo htmlspecialchars($message); ?>
        </div>
    </div>
<?php endif; ?>

<!-- Contacto -->
<section class="contact-page">
    <div class="contenedor">
        <div class="section-header">
            <h1>Contacto Institucional</h1>
            <p>Figger Energy SAS - Unidad Gubernamental de Monitoreo de Minería Ilegal</p>
        </div>

        <div class="contact-main">
            <div class="contact-info-full">
                <div class="info-grid">
                    <div class="info-card">
                        <div class="card-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <h3>Sede Principal</h3>
                        <p><strong>Dirección:</strong><br>
                        Calle 1, Carrera 1, Edificio 1<br>
                        Macondo, Colombia</p>
                        <p><strong>Horarios de Atención:</strong><br>
                        Lunes a Viernes: 8:00 AM - 5:00 PM<br>
                        Sábados: 9:00 AM - 1:00 PM</p>
                    </div>

                    <div class="info-card">
                        <div class="card-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <h3>Líneas de Contacto</h3>
                        <p><strong>Línea Nacional:</strong><br>
                        +57 0180000001</p>
                        <p><strong>Línea de Emergencias:</strong><br>
                        +57 0180000002</p>
                        <p><strong>WhatsApp Institucional:</strong><br>
                        +57 300 000 0001</p>
                    </div>

                    <div class="info-card">
                        <div class="card-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h3>Correos Institucionales</h3>
                        <p><strong>General:</strong><br>
                        contacto@figgerenergy.gov.co</p>
                        <p><strong>Reportes de Minería Ilegal:</strong><br>
                        reportes@figgerenergy.gov.co</p>
                        <p><strong>Información Pública:</strong><br>
                        transparencia@figgerenergy.gov.co</p>
                    </div>

                    <div class="info-card">
                        <div class="card-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3>Departamentos</h3>
                        <p><strong>Dirección General:</strong><br>
                        director@figgerenergy.gov.co</p>
                        <p><strong>Operaciones de Campo:</strong><br>
                        operaciones@figgerenergy.gov.co</p>
                        <p><strong>Análisis y Monitoreo:</strong><br>
                        analisis@figgerenergy.gov.co</p>
                    </div>
                </div>
            </div>

            <div class="contact-form-section">
                <div class="form-container">
                    <h2>Formulario de Contacto</h2>
                    <p>Complete el siguiente formulario para comunicarse con nosotros</p>

                    <form action="<?php echo getBaseUrl(); ?>contact" method="POST" class="contact-form">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nombre">Nombre Completo *</label>
                                <input type="text" id="nombre" name="nombre" required 
                                       value="<?php echo isset($formData['nombre']) ? htmlspecialchars($formData['nombre']) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label for="email">Correo Electrónico *</label>
                                <input type="email" id="email" name="email" required 
                                       value="<?php echo isset($formData['email']) ? htmlspecialchars($formData['email']) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="telefono">Teléfono</label>
                                <input type="tel" id="telefono" name="telefono" 
                                       value="<?php echo isset($formData['telefono']) ? htmlspecialchars($formData['telefono']) : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label for="organizacion">Organización/Empresa</label>
                                <input type="text" id="organizacion" name="organizacion" 
                                       value="<?php echo isset($formData['organizacion']) ? htmlspecialchars($formData['organizacion']) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="departamento">Departamento de Interés</label>
                            <select id="departamento" name="departamento">
                                <option value="">Seleccione un departamento...</option>
                                <option value="direccion_general" <?php echo (isset($formData['departamento']) && $formData['departamento'] === 'direccion_general') ? 'selected' : ''; ?>>Dirección General</option>
                                <option value="operaciones" <?php echo (isset($formData['departamento']) && $formData['departamento'] === 'operaciones') ? 'selected' : ''; ?>>Operaciones de Campo</option>
                                <option value="analisis" <?php echo (isset($formData['departamento']) && $formData['departamento'] === 'analisis') ? 'selected' : ''; ?>>Análisis y Monitoreo</option>
                                <option value="legal" <?php echo (isset($formData['departamento']) && $formData['departamento'] === 'legal') ? 'selected' : ''; ?>>Asuntos Legales</option>
                                <option value="prensa" <?php echo (isset($formData['departamento']) && $formData['departamento'] === 'prensa') ? 'selected' : ''; ?>>Prensa y Comunicaciones</option>
                                <option value="sistemas" <?php echo (isset($formData['departamento']) && $formData['departamento'] === 'sistemas') ? 'selected' : ''; ?>>Sistemas y Tecnología</option>
                                <option value="otro" <?php echo (isset($formData['departamento']) && $formData['departamento'] === 'otro') ? 'selected' : ''; ?>>Otro</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="asunto">Asunto *</label>
                            <input type="text" id="asunto" name="asunto" required 
                                   value="<?php echo isset($formData['asunto']) ? htmlspecialchars($formData['asunto']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="mensaje">Mensaje *</label>
                            <textarea id="mensaje" name="mensaje" rows="6" required 
                                      placeholder="Describa detalladamente su consulta o solicitud..."><?php echo isset($formData['mensaje']) ? htmlspecialchars($formData['mensaje']) : ''; ?></textarea>
                        </div>

                        <div class="form-group checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="autorizacion_datos" required 
                                       <?php echo (isset($formData['autorizacion_datos']) && $formData['autorizacion_datos']) ? 'checked' : ''; ?>>
                                <span class="checkmark"></span>
                                Autorizo el tratamiento de mis datos personales de acuerdo con la Ley 1581 de 2012 y la Política de Privacidad de Figger Energy SAS *
                            </label>
                        </div>

                        <div class="form-group checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="copia_respuesta" 
                                       <?php echo (isset($formData['copia_respuesta']) && $formData['copia_respuesta']) ? 'checked' : ''; ?>>
                                <span class="checkmark"></span>
                                Deseo recibir una copia de este mensaje en mi correo electrónico
                            </label>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="boton boton-primario">
                                <i class="fas fa-paper-plane"></i>
                                Enviar Mensaje
                            </button>
                            <button type="reset" class="boton boton-secundario">
                                <i class="fas fa-undo"></i>
                                Limpiar Formulario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Información Adicional -->
        <div class="additional-info">
            <div class="info-section">
                <h3><i class="fas fa-clock"></i> Tiempos de Respuesta</h3>
                <ul>
                    <li><strong>Consultas Generales:</strong> 1-2 días hábiles</li>
                    <li><strong>Reportes de Minería Ilegal:</strong> Inmediato (24/7)</li>
                    <li><strong>Solicitudes de Información:</strong> 3-5 días hábiles</li>
                    <li><strong>Emergencias:</strong> Respuesta inmediata</li>
                </ul>
            </div>

            <div class="info-section">
                <h3><i class="fas fa-shield-alt"></i> Confidencialidad</h3>
                <p>Toda la información proporcionada es tratada con la máxima confidencialidad de acuerdo con las normas ISO 27001 y la legislación colombiana de protección de datos personales.</p>
                
                <p>Los reportes de actividades sospechosas son manejados con protocolos especiales de seguridad para proteger la identidad de los informantes.</p>
            </div>

            <div class="info-section">
                <h3><i class="fas fa-file-alt"></i> Documentos y Formularios</h3>
                <ul>
                    <li><a href="#" class="enlace-documento">Formato de Denuncia de Minería Ilegal</a></li>
                    <li><a href="#" class="enlace-documento">Política de Privacidad y Protección de Datos</a></li>
                    <li><a href="#" class="enlace-documento">Manual de Procedimientos para Ciudadanos</a></li>
                    <li><a href="#" class="enlace-documento">Directorio Institucional Completo</a></li>
                </ul>
            </div>
        </div>
    </div>
</section>
