<!-- Login Page -->
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="logo-container">
                <img src="<?php echo getBaseUrl(); ?>assets/images/logo.png" alt="Figger Energy SAS" class="auth-logo">
            </div>
            <h1>Acceso al Sistema</h1>
            <p>Figger Energy SAS - Sistema de Monitoreo</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="mensaje <?php echo isset($messageType) ? 'mensaje-' . $messageType : 'mensaje-error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo getBaseUrl(); ?>login" method="POST" class="auth-form" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <div class="input-group">
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo isset($formData['email']) ? htmlspecialchars($formData['email']) : ''; ?>"
                           placeholder="usuario@figgerenergy.gov.co"
                           autocomplete="email">
                </div>
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="input-group">
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <input type="password" id="password" name="password" required 
                           placeholder="Ingrese su contraseña"
                           autocomplete="current-password">
                    <button type="button" class="toggle-password" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-options">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember" <?php echo (isset($formData['remember']) && $formData['remember']) ? 'checked' : ''; ?>>
                    <span class="checkmark"></span>
                    Recordar sesión
                </label>
                
                <a href="#" class="forgot-password" onclick="showRecoveryForm()">
                    ¿Olvidó su contraseña?
                </a>
            </div>

            <button type="submit" class="boton boton-primario btn-full" id="loginBtn">
                <i class="fas fa-sign-in-alt"></i>
                Iniciar Sesión
            </button>
        </form>

        <div class="auth-footer">
            <div class="auth-links">
                <p>¿No tiene cuenta? <a href="<?php echo getBaseUrl(); ?>register">Solicitar Registro</a></p>
                <p><a href="<?php echo getBaseUrl(); ?>">Volver al Inicio</a></p>
            </div>
            
            <div class="security-info">
                <div class="security-badge">
                    <i class="fas fa-shield-alt"></i>
                    <span>Protegido con ISO 27001</span>
                </div>
                <p class="security-text">
                    Sus datos están protegidos con los más altos estándares de seguridad internacional
                </p>
            </div>
        </div>
    </div>

    <!-- Recovery Form Modal -->
    <div id="recoveryModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Recuperación de Contraseña</h3>
                <button type="button" class="close-modal" onclick="hideRecoveryForm()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="recoveryForm" class="recovery-form">
                <p>Ingrese su correo electrónico para recibir instrucciones de recuperación:</p>
                
                <div class="form-group">
                    <label for="recovery_email">Correo Electrónico</label>
                    <div class="input-group">
                        <div class="input-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <input type="email" id="recovery_email" name="recovery_email" required 
                               placeholder="usuario@figgerenergy.gov.co">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="boton boton-primario">
                        <i class="fas fa-paper-plane"></i>
                        Enviar Instrucciones
                    </button>
                    <button type="button" class="boton boton-secundario" onclick="hideRecoveryForm()">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Security Information -->
<div class="security-notice">
    <div class="contenedor">
        <div class="notice-content">
            <div class="notice-icon">
                <i class="fas fa-info-circle"></i>
            </div>
            <div class="notice-text">
                <h4>Aviso de Seguridad</h4>
                <p>Este es un sistema gubernamental. El acceso está restringido a personal autorizado. 
                Todas las actividades son monitoreadas y registradas de acuerdo con las políticas de seguridad ISO 27001.</p>
            </div>
        </div>
        
        <div class="security-features">
            <div class="feature">
                <i class="fas fa-lock"></i>
                <span>Encriptación SSL/TLS</span>
            </div>
            <div class="feature">
                <i class="fas fa-shield-virus"></i>
                <span>Autenticación Multifactor</span>
            </div>
            <div class="feature">
                <i class="fas fa-eye"></i>
                <span>Monitoreo Continuo</span>
            </div>
            <div class="feature">
                <i class="fas fa-clock"></i>
                <span>Sesiones Temporales</span>
            </div>
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

function showRecoveryForm() {
    document.getElementById('recoveryModal').style.display = 'flex';
}

function hideRecoveryForm() {
    document.getElementById('recoveryModal').style.display = 'none';
    document.getElementById('recoveryForm').reset();
}

// Handle recovery form submission
document.getElementById('recoveryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const email = document.getElementById('recovery_email').value;
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
    submitBtn.disabled = true;
    
    // Simulate API call
    setTimeout(() => {
        alert('Si el correo electrónico está registrado en nuestro sistema, recibirá las instrucciones de recuperación en los próximos minutos.');
        hideRecoveryForm();
        
        // Restore button
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }, 2000);
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

// Form validation
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    if (!email || !password) {
        e.preventDefault();
        alert('Por favor complete todos los campos requeridos.');
        return;
    }
    
    // Show loading state
    const submitBtn = document.getElementById('loginBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Iniciando sesión...';
    submitBtn.disabled = true;
});
</script>
