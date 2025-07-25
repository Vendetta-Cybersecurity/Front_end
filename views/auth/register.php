<!-- Register Page -->
<div class="auth-container">
    <div class="auth-card register-card">
        <div class="auth-header">
            <div class="logo-container">
                <img src="<?php echo getBaseUrl(); ?>assets/images/logo.png" alt="Figger Energy SAS" class="auth-logo">
            </div>
            <h1>Registro de Usuario</h1>
            <p>Figger Energy SAS - Solicitud de Acceso al Sistema</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="mensaje <?php echo isset($messageType) ? 'mensaje-' . $messageType : 'mensaje-error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="register-info">
            <div class="info-box">
                <h3><i class="fas fa-info-circle"></i> Información Importante</h3>
                <ul>
                    <li>Solo personal autorizado puede solicitar acceso al sistema</li>
                    <li>Todas las solicitudes son revisadas por el departamento de seguridad</li>
                    <li>El proceso de aprobación puede tomar de 1 a 3 días hábiles</li>
                    <li>Recibirá una notificación por correo electrónico sobre el estado de su solicitud</li>
                </ul>
            </div>
        </div>

        <form action="<?php echo getBaseUrl(); ?>register" method="POST" class="auth-form register-form" id="registerForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <h3>Información Personal</h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="nombre">Nombre Completo *</label>
                    <div class="input-group">
                        <div class="input-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <input type="text" id="nombre" name="nombre" required 
                               value="<?php echo isset($formData['nombre']) ? htmlspecialchars($formData['nombre']) : ''; ?>"
                               placeholder="Nombres y apellidos completos">
                    </div>
                </div>

                <div class="form-group">
                    <label for="documento">Número de Documento *</label>
                    <div class="input-group">
                        <div class="input-icon">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <input type="text" id="documento" name="documento" required 
                               value="<?php echo isset($formData['documento']) ? htmlspecialchars($formData['documento']) : ''; ?>"
                               placeholder="Cédula de ciudadanía">
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">Correo Electrónico *</label>
                    <div class="input-group">
                        <div class="input-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo isset($formData['email']) ? htmlspecialchars($formData['email']) : ''; ?>"
                               placeholder="usuario@figgerenergy.gov.co">
                    </div>
                </div>

                <div class="form-group">
                    <label for="telefono">Teléfono *</label>
                    <div class="input-group">
                        <div class="input-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <input type="tel" id="telefono" name="telefono" required 
                               value="<?php echo isset($formData['telefono']) ? htmlspecialchars($formData['telefono']) : ''; ?>"
                               placeholder="+57 300 000 0000">
                    </div>
                </div>
            </div>

            <h3>Información Laboral</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="cargo">Cargo a Desempeñar *</label>
                    <div class="input-group">
                        <div class="input-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <input type="text" id="cargo" name="cargo" required 
                               value="<?php echo isset($formData['cargo']) ? htmlspecialchars($formData['cargo']) : ''; ?>"
                               placeholder="Título del puesto de trabajo">
                    </div>
                </div>

                <div class="form-group">
                    <label for="departamento">Departamento *</label>
                    <div class="input-group">
                        <div class="input-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <select id="departamento" name="departamento" required>
                            <option value="">Seleccione su departamento...</option>
                            <option value="direccion_general" <?php echo (isset($formData['departamento']) && $formData['departamento'] === 'direccion_general') ? 'selected' : ''; ?>>Dirección General</option>
                            <option value="operaciones" <?php echo (isset($formData['departamento']) && $formData['departamento'] === 'operaciones') ? 'selected' : ''; ?>>Operaciones de Campo</option>
                            <option value="analisis" <?php echo (isset($formData['departamento']) && $formData['departamento'] === 'analisis') ? 'selected' : ''; ?>>Análisis y Monitoreo</option>
                            <option value="auditoria" <?php echo (isset($formData['departamento']) && $formData['departamento'] === 'auditoria') ? 'selected' : ''; ?>>Auditoría y Control</option>
                            <option value="legal" <?php echo (isset($formData['departamento']) && $formData['departamento'] === 'legal') ? 'selected' : ''; ?>>Asuntos Legales</option>
                            <option value="sistemas" <?php echo (isset($formData['departamento']) && $formData['departamento'] === 'sistemas') ? 'selected' : ''; ?>>Sistemas y Tecnología</option>
                            <option value="recursos_humanos" <?php echo (isset($formData['departamento']) && $formData['departamento'] === 'recursos_humanos') ? 'selected' : ''; ?>>Recursos Humanos</option>
                            <option value="contabilidad" <?php echo (isset($formData['departamento']) && $formData['departamento'] === 'contabilidad') ? 'selected' : ''; ?>>Contabilidad y Finanzas</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="justificacion">Justificación del Acceso *</label>
                <div class="input-group">
                    <div class="input-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <textarea id="justificacion" name="justificacion" rows="4" required 
                              placeholder="Explique detalladamente por qué necesita acceso al sistema y qué actividades realizará..."><?php echo isset($formData['justificacion']) ? htmlspecialchars($formData['justificacion']) : ''; ?></textarea>
                </div>
            </div>

            <h3>Configuración de Acceso</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Contraseña *</label>
                    <div class="input-group">
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <input type="password" id="password" name="password" required 
                               placeholder="Mínimo 8 caracteres"
                               autocomplete="new-password">
                        <button type="button" class="toggle-password" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-requirements">
                        <small>La contraseña debe contener:</small>
                        <ul>
                            <li id="req-length">Al menos 8 caracteres</li>
                            <li id="req-upper">Una letra mayúscula</li>
                            <li id="req-lower">Una letra minúscula</li>
                            <li id="req-number">Un número</li>
                            <li id="req-special">Un carácter especial</li>
                        </ul>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirm">Confirmar Contraseña *</label>
                    <div class="input-group">
                        <div class="input-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <input type="password" id="password_confirm" name="password_confirm" required 
                               placeholder="Repita la contraseña"
                               autocomplete="new-password">
                        <button type="button" class="toggle-password" onclick="togglePassword('password_confirm')">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="supervisor_email">Email del Supervisor Directo *</label>
                <div class="input-group">
                    <div class="input-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <input type="email" id="supervisor_email" name="supervisor_email" required 
                           value="<?php echo isset($formData['supervisor_email']) ? htmlspecialchars($formData['supervisor_email']) : ''; ?>"
                           placeholder="supervisor@figgerenergy.gov.co">
                </div>
                <small class="help-text">Su supervisor recibirá una notificación para aprobar su solicitud</small>
            </div>

            <div class="form-group checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="acepta_politicas" required 
                           <?php echo (isset($formData['acepta_politicas']) && $formData['acepta_politicas']) ? 'checked' : ''; ?>>
                    <span class="checkmark"></span>
                    Acepto las <a href="#" onclick="showPolicies()">Políticas de Uso y Seguridad</a> del sistema *
                </label>
            </div>

            <div class="form-group checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="acepta_datos" required 
                           <?php echo (isset($formData['acepta_datos']) && $formData['acepta_datos']) ? 'checked' : ''; ?>>
                    <span class="checkmark"></span>
                    Autorizo el tratamiento de mis datos personales de acuerdo con la Ley 1581 de 2012 *
                </label>
            </div>

            <div class="form-group checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="acepta_monitoreo" required 
                           <?php echo (isset($formData['acepta_monitoreo']) && $formData['acepta_monitoreo']) ? 'checked' : ''; ?>>
                    <span class="checkmark"></span>
                    Entiendo que todas mis actividades en el sistema serán monitoreadas y registradas *
                </label>
            </div>

            <button type="submit" class="boton boton-primario btn-full" id="registerBtn">
                <i class="fas fa-user-plus"></i>
                Enviar Solicitud de Registro
            </button>
        </form>

        <div class="auth-footer">
            <div class="auth-links">
                <p>¿Ya tiene cuenta? <a href="<?php echo getBaseUrl(); ?>login">Iniciar Sesión</a></p>
                <p><a href="<?php echo getBaseUrl(); ?>">Volver al Inicio</a></p>
            </div>
        </div>
    </div>
</div>

<!-- Policies Modal -->
<div id="policiesModal" class="modal" style="display: none;">
    <div class="modal-content policies-modal">
        <div class="modal-header">
            <h3>Políticas de Uso y Seguridad</h3>
            <button type="button" class="close-modal" onclick="hidePolicies()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-body">
            <div class="policies-content">
                <h4>1. Uso Autorizado</h4>
                <p>Este sistema está destinado exclusivamente para uso oficial de Figger Energy SAS. El acceso no autorizado está prohibido y puede resultar en acciones legales.</p>

                <h4>2. Responsabilidades del Usuario</h4>
                <ul>
                    <li>Mantener la confidencialidad de sus credenciales de acceso</li>
                    <li>No compartir su cuenta con terceros</li>
                    <li>Reportar inmediatamente cualquier actividad sospechosa</li>
                    <li>Cumplir con todas las políticas de seguridad institucionales</li>
                </ul>

                <h4>3. Monitoreo y Auditoría</h4>
                <p>Todas las actividades en el sistema son monitoreadas, registradas y auditadas de acuerdo con las normas ISO 27001 y la legislación colombiana.</p>

                <h4>4. Protección de Datos</h4>
                <p>Los datos personales son tratados conforme a la Ley 1581 de 2012 y las políticas internas de protección de datos.</p>

                <h4>5. Sanciones</h4>
                <p>El uso indebido del sistema puede resultar en la suspensión del acceso y acciones disciplinarias o legales según corresponda.</p>
            </div>
        </div>
        
        <div class="modal-footer">
            <button type="button" class="boton boton-primario" onclick="hidePolicies()">
                Entendido
            </button>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const button = field.nextElementSibling;
    const icon = button.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function showPolicies() {
    document.getElementById('policiesModal').style.display = 'flex';
}

function hidePolicies() {
    document.getElementById('policiesModal').style.display = 'none';
}

// Password validation
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    
    const requirements = {
        'req-length': password.length >= 8,
        'req-upper': /[A-Z]/.test(password),
        'req-lower': /[a-z]/.test(password),
        'req-number': /\d/.test(password),
        'req-special': /[!@#$%^&*(),.?":{}|<>]/.test(password)
    };
    
    Object.keys(requirements).forEach(reqId => {
        const element = document.getElementById(reqId);
        if (requirements[reqId]) {
            element.classList.add('met');
            element.classList.remove('unmet');
        } else {
            element.classList.add('unmet');
            element.classList.remove('met');
        }
    });
});

// Form validation
document.getElementById('registerForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const passwordConfirm = document.getElementById('password_confirm').value;
    
    // Check if passwords match
    if (password !== passwordConfirm) {
        e.preventDefault();
        alert('Las contraseñas no coinciden.');
        return;
    }
    
    // Check password requirements
    const requirements = [
        password.length >= 8,
        /[A-Z]/.test(password),
        /[a-z]/.test(password),
        /\d/.test(password),
        /[!@#$%^&*(),.?":{}|<>]/.test(password)
    ];
    
    if (!requirements.every(req => req)) {
        e.preventDefault();
        alert('La contraseña no cumple con todos los requisitos de seguridad.');
        return;
    }
    
    // Show loading state
    const submitBtn = document.getElementById('registerBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando solicitud...';
    submitBtn.disabled = true;
});

// Auto-hide messages
document.addEventListener('DOMContentLoaded', function() {
    const messages = document.querySelectorAll('.mensaje');
    messages.forEach(function(message) {
        setTimeout(function() {
            message.style.opacity = '0';
            setTimeout(function() {
                message.remove();
            }, 300);
        }, 5000);
    });
});
</script>
