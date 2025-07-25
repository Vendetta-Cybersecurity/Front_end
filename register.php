<?php
/**
 * Página de Registro - Figger Energy SAS
 * Registro de nuevos usuarios con validación básica
 */

session_start();

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard_" . $_SESSION['rol'] . ".php");
    exit();
}

$titulo_pagina = "Registro de Usuario";
$mensaje = '';
$tipo_mensaje = '';

// Procesar formulario de registro
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'includes/db.php';
    
    $nombre = limpiarDatos($_POST['nombre']);
    $email = limpiarDatos($_POST['email']);
    $password = $_POST['password'];
    $confirmar_password = $_POST['confirmar_password'];
    $rol = limpiarDatos($_POST['rol']);
    
    // Validaciones básicas
    $errores = [];
    
    if (strlen($nombre) < 3) {
        $errores[] = 'El nombre debe tener al menos 3 caracteres.';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Formato de email inválido.';
    }
    
    if (strlen($password) < 6) {
        $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
    }
    
    if ($password !== $confirmar_password) {
        $errores[] = 'Las contraseñas no coinciden.';
    }
    
    if (!in_array($rol, ['empleado', 'auditor'])) {
        $errores[] = 'Rol seleccionado no válido.';
    }
    
    if (empty($errores)) {
        $conexion = conectarDB();
        
        // Verificar si el email ya existe
        $stmt_check = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $resultado_check = $stmt_check->get_result();
        
        if ($resultado_check->num_rows > 0) {
            $mensaje = 'Ya existe un usuario registrado con ese email.';
            $tipo_mensaje = 'error';
        } else {
            // Hashear contraseña
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insertar nuevo usuario
            $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nombre, $email, $password_hash, $rol);
            
            if ($stmt->execute()) {
                $usuario_id = $conexion->insert_id;
                
                // Registrar actividad
                $stmt_actividad = $conexion->prepare("INSERT INTO actividades (usuario_id, accion, descripcion, ip_address) VALUES (?, 'registro', 'Usuario registrado en el sistema', ?)");
                $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
                $stmt_actividad->bind_param("is", $usuario_id, $ip);
                $stmt_actividad->execute();
                
                $mensaje = 'Registro exitoso. Su cuenta está pendiente de activación por un administrador. Será notificado cuando pueda acceder al sistema.';
                $tipo_mensaje = 'exito';
                
                // Limpiar formulario
                $_POST = [];
            } else {
                $mensaje = 'Error al registrar el usuario. Intente nuevamente.';
                $tipo_mensaje = 'error';
            }
            
            $stmt->close();
        }
        
        $stmt_check->close();
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
                <h1>Registro de Usuario</h1>
                <p>Solicitud de acceso al sistema de Figger Energy SAS</p>
            </div>
            
            <?php if (!empty($mensaje)): ?>
                <div class="mensaje mensaje-<?php echo $tipo_mensaje; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>
            
            <form id="form-registro" method="POST" action="register.php" class="formulario">
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
                    <label for="email">Email Institucional:</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           required 
                           placeholder="usuario@figgerenergy.gov.co"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <small>Debe usar un email institucional válido</small>
                </div>
                
                <div class="grupo-campo">
                    <label for="password">Contraseña:</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required 
                           placeholder="Mínimo 6 caracteres">
                    <small>Use una combinación de letras, números y símbolos</small>
                </div>
                
                <div class="grupo-campo">
                    <label for="confirmar_password">Confirmar Contraseña:</label>
                    <input type="password" 
                           id="confirmar_password" 
                           name="confirmar_password" 
                           required 
                           placeholder="Repita la contraseña">
                </div>
                
                <div class="grupo-campo">
                    <label for="rol">Rol Solicitado:</label>
                    <select id="rol" name="rol" required>
                        <option value="">Seleccione un rol</option>
                        <option value="empleado" <?php echo (isset($_POST['rol']) && $_POST['rol'] == 'empleado') ? 'selected' : ''; ?>>
                            Empleado - Operaciones generales
                        </option>
                        <option value="auditor" <?php echo (isset($_POST['rol']) && $_POST['rol'] == 'auditor') ? 'selected' : ''; ?>>
                            Auditor - Supervisión y control
                        </option>
                    </select>
                    <small>El rol de Administrador solo puede ser asignado por el sistema</small>
                </div>
                
                <div class="grupo-campo">
                    <label>
                        <input type="checkbox" required> 
                        Acepto los términos y condiciones del sistema y me comprometo a usar la plataforma de manera responsable.
                    </label>
                </div>
                
                <div class="grupo-campo">
                    <button type="submit" class="boton boton-primario" style="width: 100%;">
                        Enviar Solicitud de Registro
                    </button>
                </div>
                
                <div class="texto-centro">
                    <p>¿Ya tiene cuenta? <a href="login.php">Iniciar sesión</a></p>
                    <p><a href="index.php">Volver a la página principal</a></p>
                </div>
            </form>
            
            <!-- Información sobre el proceso -->
            <div class="tarjeta mt-2">
                <h3>📋 Proceso de Registro</h3>
                <ol style="text-align: left;">
                    <li><strong>Solicitud:</strong> Complete el formulario con información verídica</li>
                    <li><strong>Verificación:</strong> Un administrador verificará su identidad y rol</li>
                    <li><strong>Activación:</strong> Recibirá una notificación cuando su cuenta esté activa</li>
                    <li><strong>Acceso:</strong> Podrá ingresar al sistema con sus credenciales</li>
                </ol>
                
                <h4>🔒 Consideraciones de Seguridad</h4>
                <ul style="text-align: left;">
                    <li>Use únicamente emails institucionales oficiales</li>
                    <li>Cree contraseñas seguras y únicas</li>
                    <li>No comparta sus credenciales con terceros</li>
                    <li>Reporte cualquier actividad sospechosa</li>
                </ul>
                
                <p style="margin-top: 1rem;"><strong>⏱️ Tiempo estimado de activación:</strong> 24-48 horas hábiles</p>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
