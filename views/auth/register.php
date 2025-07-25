<?php
/**
 * Página de Registro - Figger Energy SAS
 * Sistema de registro refactorizado
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

$titulo_pagina = "Registro de Usuario";
$mensaje = '';
$tipo_mensaje = '';

// Procesar formulario de registro
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Definir reglas de validación
    $reglas = [
        'nombre' => ['requerido' => true, 'min_length' => 2, 'max_length' => 100],
        'email' => ['requerido' => true, 'email' => true],
        'password' => ['requerido' => true, 'min_length' => 6],
        'confirmar_password' => ['requerido' => true, 'confirmar' => 'password'],
        'rol' => ['requerido' => true]
    ];
    
    // Validar datos
    $errores = validarFormulario($reglas, $_POST);
    
    if (empty($errores)) {
        $nombre = limpiarDatos($_POST['nombre']);
        $email = limpiarDatos($_POST['email']);
        $password = $_POST['password'];
        $rol = $_POST['rol'];
        
        // Validar que el rol sea válido
        $roles_validos = ['empleado', 'auditor'];
        if (!in_array($rol, $roles_validos)) {
            $errores[] = 'Rol no válido seleccionado.';
        }
        
        if (empty($errores)) {
            $conexion = conectarDB();
            
            // Verificar si el email ya existe
            $stmt_check = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt_check->bind_param("s", $email);
            $stmt_check->execute();
            
            if ($stmt_check->get_result()->num_rows > 0) {
                $mensaje = 'El email ya está registrado en el sistema.';
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
                    registrarActividad($usuario_id, 'registro', 'Usuario registrado en el sistema', $conexion);
                    
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
        }
    }
    
    if (!empty($errores)) {
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
                <h1>Registro de Usuario</h1>
                <p>Solicitud de acceso al sistema de Figger Energy SAS</p>
            </div>
            
            <?php echo mostrarMensaje($mensaje, $tipo_mensaje); ?>
            
            <form method="POST" action="register.php" class="formulario">
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
                    <label for="email">Email Institucional:</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           required 
                           placeholder="nombre@figgerenergy.gov.co"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <small>Use su email institucional oficial</small>
                </div>
                
                <div class="grupo-campo">
                    <label for="password">Contraseña:</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required 
                           placeholder="Mínimo 6 caracteres">
                    <small>La contraseña debe tener al menos 6 caracteres</small>
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
                    <button type="submit" class="boton boton-primario boton-completo">Registrar Usuario</button>
                </div>
                
                <div class="texto-centro">
                    <p><a href="login.php">¿Ya tiene cuenta? Iniciar sesión</a></p>
                </div>
            </form>
            
            <!-- Información del proceso -->
            <div class="informacion-adicional">
                <h3>Proceso de Registro</h3>
                <div class="dashboard-grid">
                    <div class="tarjeta">
                        <h4>📝 1. Completar Formulario</h4>
                        <p>Diligencie todos los campos con información veraz y actualizada.</p>
                    </div>
                    
                    <div class="tarjeta">
                        <h4>⏳ 2. Revisión</h4>
                        <p>Su solicitud será revisada por el administrador del sistema.</p>
                    </div>
                    
                    <div class="tarjeta">
                        <h4>✅ 3. Activación</h4>
                        <p>Recibirá confirmación cuando su cuenta sea activada.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include '../../includes/footer.php'; ?>
