<?php
/**
 * Página de Login - Figger Energy SAS
 * Sistema de autenticación refactorizado
 */

session_start();

// Cargar dependencias
require_once '../../config/database.php';
require_once '../../lib/functions.php';

// Si ya está logueado, redirigir al dashboard
if (verificarAuth()) {
    header("Location: ../../" . getDashboardUrl($_SESSION['rol']));
    exit();
}

$titulo_pagina = "Iniciar Sesión";
$mensaje = '';
$tipo_mensaje = '';

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
            
            // Verificar contraseña usando hash seguro
            if (password_verify($password, $usuario['password'])) {
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
                registrarActividad($usuario['id'], 'login', 'Inicio de sesión exitoso', $conexion);
                
                // Redirigir según el rol
                header("Location: ../../" . getDashboardUrl($usuario['rol']));
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

include '../../includes/header.php';
?>

<main class="contenido-principal">
    <section class="seccion">
        <div class="contenedor">
            <div class="texto-centro mb-2">
                <h1>Iniciar Sesión</h1>
                <p>Acceso al sistema interno de Figger Energy SAS</p>
            </div>
            
            <?php echo mostrarMensaje($mensaje, $tipo_mensaje); ?>
            
            <form method="POST" action="" class="formulario">
                <div class="grupo-campo">
                    <label for="email">Email:</label>
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
                           placeholder="Su contraseña">
                </div>
                
                <div class="grupo-campo">
                    <button type="submit" class="boton boton-primario boton-completo">Iniciar Sesión</button>
                </div>
                
                <div class="texto-centro">
                    <p><a href="register.php">¿No tiene cuenta? Registrarse</a></p>
                </div>
            </form>
            
            <!-- Información de cuentas de prueba -->
            <div class="informacion-adicional">
                <h3>Cuentas de Prueba Disponibles</h3>
                <p><small>Para propósitos de demostración, puede usar las siguientes credenciales:</small></p>
                
                <div class="dashboard-grid">
                    <div class="tarjeta">
                        <h4>👨‍💼 Administrador</h4>
                        <p><strong>Email:</strong> admin@figgerenergy.gov.co</p>
                        <p><strong>Contraseña:</strong> admin123</p>
                    </div>
                    
                    <div class="tarjeta">
                        <h4>👷‍♂️ Empleado</h4>
                        <p><strong>Email:</strong> empleado@figgerenergy.gov.co</p>
                        <p><strong>Contraseña:</strong> empleado123</p>
                    </div>
                    
                    <div class="tarjeta">
                        <h4>🔍 Auditor</h4>
                        <p><strong>Email:</strong> auditor@figgerenergy.gov.co</p>
                        <p><strong>Contraseña:</strong> auditor123</p>
                    </div>
                </div>
                
                <p style="margin-top: 1rem;"><small><strong>Nota:</strong> En un entorno de producción, las contraseñas serían únicas y seguras.</small></p>
            </div>
        </div>
    </section>
</main>

<?php include '../../includes/footer.php'; ?>
