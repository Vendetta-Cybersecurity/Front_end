<?php
/**
 * Gestión de Usuarios - Figger Energy SAS
 * Manejo seguro de eliminación de usuarios con foreign keys
 */

session_start();

// Verificar que sea administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'includes/db.php';

$mensaje = '';
$tipo_mensaje = '';

// Procesar eliminación de usuario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion'])) {
    $accion = $_POST['accion'];
    $usuario_id = (int)$_POST['usuario_id'];
    
    if ($accion === 'eliminar' && $usuario_id > 0) {
        $conexion = conectarDB();
        
        // Verificar que no sea el usuario actual
        if ($usuario_id == $_SESSION['usuario_id']) {
            $mensaje = 'No puedes eliminar tu propia cuenta.';
            $tipo_mensaje = 'error';
        } else {
            // Iniciar transacción para operación atómica
            $conexion->begin_transaction();
            
            try {
                // 1. Reasignar alertas a NULL (desarar alertas)
                $stmt1 = $conexion->prepare("UPDATE alertas_mineria SET usuario_asignado = NULL WHERE usuario_asignado = ?");
                $stmt1->bind_param("i", $usuario_id);
                $stmt1->execute();
                $alertas_reasignadas = $stmt1->affected_rows;
                
                // 2. Registrar actividad de eliminación antes de eliminar las actividades
                $stmt_info = $conexion->prepare("SELECT nombre, email FROM usuarios WHERE id = ?");
                $stmt_info->bind_param("i", $usuario_id);
                $stmt_info->execute();
                $info_usuario = $stmt_info->get_result()->fetch_assoc();
                
                if ($info_usuario) {
                    $desc_eliminacion = "Usuario eliminado: {$info_usuario['nombre']} ({$info_usuario['email']})";
                    $stmt_log = $conexion->prepare("INSERT INTO actividades (usuario_id, accion, descripcion, ip_address) VALUES (?, 'usuario_eliminado', ?, ?)");
                    $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
                    $stmt_log->bind_param("iss", $_SESSION['usuario_id'], $desc_eliminacion, $ip);
                    $stmt_log->execute();
                }
                
                // 3. Eliminar actividades del usuario
                $stmt2 = $conexion->prepare("DELETE FROM actividades WHERE usuario_id = ?");
                $stmt2->bind_param("i", $usuario_id);
                $stmt2->execute();
                $actividades_eliminadas = $stmt2->affected_rows;
                
                // 4. Finalmente eliminar el usuario
                $stmt3 = $conexion->prepare("DELETE FROM usuarios WHERE id = ?");
                $stmt3->bind_param("i", $usuario_id);
                $stmt3->execute();
                
                if ($stmt3->affected_rows > 0) {
                    // Confirmar transacción
                    $conexion->commit();
                    $mensaje = "Usuario eliminado exitosamente. Se reasignaron {$alertas_reasignadas} alertas y se eliminaron {$actividades_eliminadas} actividades.";
                    $tipo_mensaje = 'exito';
                } else {
                    $conexion->rollback();
                    $mensaje = 'No se pudo eliminar el usuario. Verifique que el usuario existe.';
                    $tipo_mensaje = 'error';
                }
                
                $stmt1->close();
                $stmt2->close();
                $stmt3->close();
                if (isset($stmt_info)) $stmt_info->close();
                if (isset($stmt_log)) $stmt_log->close();
                
            } catch (Exception $e) {
                // Revertir transacción en caso de error
                $conexion->rollback();
                $mensaje = 'Error al eliminar usuario: ' . $e->getMessage();
                $tipo_mensaje = 'error';
            }
            
            cerrarDB($conexion);
        }
    } elseif ($accion === 'desactivar' && $usuario_id > 0) {
        // Opción alternativa: desactivar en lugar de eliminar
        $conexion = conectarDB();
        
        $stmt = $conexion->prepare("UPDATE usuarios SET activo = 0 WHERE id = ? AND id != ?");
        $stmt->bind_param("ii", $usuario_id, $_SESSION['usuario_id']);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            $mensaje = 'Usuario desactivado exitosamente. Sus datos se mantienen para auditoría.';
            $tipo_mensaje = 'exito';
            
            // Registrar actividad
            $stmt_act = $conexion->prepare("INSERT INTO actividades (usuario_id, accion, descripcion, ip_address) VALUES (?, 'usuario_desactivado', ?, ?)");
            $desc = "Usuario desactivado ID: $usuario_id";
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'desconocida';
            $stmt_act->bind_param("iss", $_SESSION['usuario_id'], $desc, $ip);
            $stmt_act->execute();
            $stmt_act->close();
        } else {
            $mensaje = 'No se pudo desactivar el usuario.';
            $tipo_mensaje = 'error';
        }
        
        $stmt->close();
        cerrarDB($conexion);
    }
}

// Obtener todos los usuarios para mostrar
$conexion = conectarDB();
$stmt_usuarios = $conexion->prepare("
    SELECT u.*, 
           COUNT(DISTINCT am.id) as alertas_asignadas,
           COUNT(DISTINCT a.id) as actividades_registradas
    FROM usuarios u
    LEFT JOIN alertas_mineria am ON u.id = am.usuario_asignado
    LEFT JOIN actividades a ON u.id = a.usuario_id
    GROUP BY u.id
    ORDER BY u.fecha_registro DESC
");
$stmt_usuarios->execute();
$usuarios = $stmt_usuarios->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_usuarios->close();
cerrarDB($conexion);

$titulo_pagina = "Gestión de Usuarios";
include 'includes/header.php';
?>

<main class="contenido-principal">
    <section class="dashboard-header">
        <div class="contenedor">
            <h1>Gestión de Usuarios del Sistema</h1>
            <p>Administración segura de cuentas de usuario con manejo de dependencias</p>
        </div>
    </section>

    <div class="contenedor">
        <?php if (!empty($mensaje)): ?>
            <div class="mensaje mensaje-<?php echo $tipo_mensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <!-- Información sobre eliminación segura -->
        <div class="tarjeta mb-2">
            <h3>⚠️ Información sobre Eliminación de Usuarios</h3>
            <p>Al eliminar un usuario, el sistema realizará las siguientes acciones automáticamente:</p>
            <ul style="text-align: left;">
                <li><strong>Alertas asignadas:</strong> Se reasignarán como "sin asignar" (NULL)</li>
                <li><strong>Actividades registradas:</strong> Se eliminarán del sistema</li>
                <li><strong>Registro de auditoría:</strong> Se creará un log de la eliminación</li>
                <li><strong>Operación atómica:</strong> Todo se hace en una transacción segura</li>
            </ul>
            <p><strong>Alternativa recomendada:</strong> Usar "Desactivar" en lugar de "Eliminar" para mantener historial de auditoría.</p>
        </div>

        <!-- Lista de usuarios -->
        <div class="tarjeta">
            <h3>👥 Lista de Usuarios del Sistema</h3>
            
            <table class="tabla">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Alertas</th>
                        <th>Actividades</th>
                        <th>Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?php echo $usuario['id']; ?></td>
                        <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                        <td>
                            <span class="badge"><?php echo ucfirst($usuario['rol']); ?></span>
                        </td>
                        <td>
                            <?php if ($usuario['activo']): ?>
                                <span style="color: green;">✅ Activo</span>
                            <?php else: ?>
                                <span style="color: red;">❌ Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $usuario['alertas_asignadas']; ?></td>
                        <td><?php echo $usuario['actividades_registradas']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></td>
                        <td>
                            <?php if ($usuario['id'] != $_SESSION['usuario_id']): ?>
                                <?php if ($usuario['activo']): ?>
                                    <!-- Botón de desactivar (recomendado) -->
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('¿Está seguro de desactivar este usuario? Esta es la opción recomendada.');">
                                        <input type="hidden" name="accion" value="desactivar">
                                        <input type="hidden" name="usuario_id" value="<?php echo $usuario['id']; ?>">
                                        <button type="submit" class="boton" style="background-color: orange; color: white; font-size: 0.8rem;">
                                            Desactivar
                                        </button>
                                    </form>
                                    
                                    <!-- Botón de eliminar (peligroso) -->
                                    <form method="POST" style="display: inline;" onsubmit="return confirmarEliminacion(<?php echo $usuario['alertas_asignadas']; ?>, <?php echo $usuario['actividades_registradas']; ?>);">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="usuario_id" value="<?php echo $usuario['id']; ?>">
                                        <button type="submit" class="boton" style="background-color: red; color: white; font-size: 0.8rem;">
                                            Eliminar
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <em style="color: #666;">Usuario inactivo</em>
                                <?php endif; ?>
                            <?php else: ?>
                                <em style="color: #666;">Usuario actual</em>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="texto-centro mt-2">
            <a href="dashboard_admin.php" class="boton boton-primario">Volver al Dashboard</a>
        </div>
    </div>
</main>

<script>
function confirmarEliminacion(alertas, actividades) {
    let mensaje = '⚠️ ATENCIÓN: Esta acción es IRREVERSIBLE\n\n';
    mensaje += 'Se eliminarán:\n';
    mensaje += '• El usuario y toda su información\n';
    mensaje += '• ' + actividades + ' actividades registradas\n';
    mensaje += '• Se reasignarán ' + alertas + ' alertas\n\n';
    mensaje += '¿Está completamente seguro de continuar?\n\n';
    mensaje += 'Recomendación: Use "Desactivar" en su lugar.';
    
    return confirm(mensaje);
}
</script>

<?php include 'includes/footer.php'; ?>
