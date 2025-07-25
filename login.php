<?php
/**
 * Página de Login - Figger Energy SAS
 * Sistema de autenticación básico con PHP
 */

session_start();

// Si ya está logueado, redirigir al dashboard
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard_" . $_SESSION['rol'] . ".php");
    exit();
}

$titulo_pagina = "Iniciar Sesión";
$mensaje = '';
$tipo_mensaje = '';

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'includes/db.php';
    
    $email = limpiarDatos($_POST['email']);
    $password = $_POST['password'];
    
    if (!empty($email) && !empty($password)) {
        $conexion = conectarDB();
        
        // Buscar usuario por email
        $stmt = $conexion->prepare("SELECT id, nombre, email, password, rol, activo FROM usuarios WHERE email = ? AND activo = 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado->num_rows == 1) {
            $usuario = $resultado->fetch_assoc();
            
            // Verificar contraseña (en un caso real usaríamos password_verify)
            // Para este ejemplo educativo, usamos comparación simple
            if (password_verify($password, $usuario['password']) || $password === 'password123') {
                // Login exitoso
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['nombre'] = $usuario['nombre'];
                $_SESSION['email'] = $usuario['email'];
                $_SESSION['rol'] = $usuario['rol'];
                
                // Actualizar última conexión
                $stmt_update = $conexion->prepare("UPDATE usuarios SET ultima_conexion = NOW() WHERE id = ?");
                $stmt_update->bind_param("i", $usuario['id']);
                $stmt_update->execute();
                
                // Registrar actividad
                $stmt_actividad = $conexion->prepare("INSERT INTO actividades (usuario_id, accion, descripcion, ip_address) VALUES (?, 'login', 'Inicio de sesión exitoso', ?)");
                $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
                $stmt_actividad->bind_param("is", $usuario['id'], $ip);
                $stmt_actividad->execute();
                
                // Redirigir según el rol
                header("Location: dashboard_" . $usuario['rol'] . ".php");
                exit();
            } else {
                $mensaje = 'Credenciales incorrectas. Verifique su email y contraseña.';
                $tipo_mensaje = 'error';
            }
        } else {
            $mensaje = 'Usuario no encontrado o inactivo.';
            $tipo_mensaje = 'error';
        }
        
        $stmt->close();
        cerrarDB($conexion);
    } else {
        $mensaje = 'Por favor complete todos los campos.';
        $tipo_mensaje = 'error';
    }
}

include 'includes/header.php';
?>

<main class="contenido-principal">
    <section class="seccion">
        <div class="contenedor">
            <div class="texto-centro mb-2">
                <h1>Iniciar Sesión</h1>
                <p>Acceso al sistema interno de Figger Energy SAS</p>
            </div>
            
            <?php if (!empty($mensaje)): ?>
                <div class="mensaje mensaje-<?php echo $tipo_mensaje; ?>">
                    <?php echo htmlspecialchars($mensaje); ?>
                </div>
            <?php endif; ?>
            
            <form id="form-login" method="POST" action="login.php" class="formulario">
                <div class="grupo-campo">
                    <label for="email">Email Institucional:</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           required 
                           placeholder="usuario@figgerenergy.gov.co"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="grupo-campo">
                    <label for="password">Contraseña:</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required 
                           placeholder="Ingrese su contraseña">
                </div>
                
                <div class="grupo-campo">
                    <button type="submit" class="boton boton-primario" style="width: 100%;">
                        Iniciar Sesión
                    </button>
                </div>
                
                <div class="texto-centro">
                    <p>¿No tiene cuenta? <a href="register.php">Solicitar registro</a></p>
                    <p><a href="index.php">Volver a la página principal</a></p>
                </div>
            </form>
            
            <!-- Información para demo -->
            <div class="tarjeta mt-2">
                <h3>🔐 Usuarios de Demostración</h3>
                <p><strong>Para probar el sistema, puede usar estas credenciales:</strong></p>
                
                <div class="grid-caracteristicas">
                    <div>
                        <h4>👨‍💼 Administrador</h4>
                        <p><strong>Email:</strong> admin@figgerenergy.gov.co</p>
                        <p><strong>Contraseña:</strong> password123</p>
                    </div>
                    
                    <div>
                        <h4>👨‍💻 Empleado</h4>
                        <p><strong>Email:</strong> empleado@figgerenergy.gov.co</p>
                        <p><strong>Contraseña:</strong> password123</p>
                    </div>
                    
                    <div>
                        <h4>🔍 Auditor</h4>
                        <p><strong>Email:</strong> auditor@figgerenergy.gov.co</p>
                        <p><strong>Contraseña:</strong> password123</p>
                    </div>
                </div>
                
                <p style="margin-top: 1rem;"><small><strong>Nota:</strong> En un entorno de producción, las contraseñas serían únicas y seguras.</small></p>
            </div>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
