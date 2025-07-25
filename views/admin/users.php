<?php
/**
 * Gestión de Usuarios - Figger Energy SAS
 * Manejo refactorizado de usuarios con foreign keys
 */

// Cargar dependencias
require_once '../../config/database.php';
require_once '../../lib/functions.php';

// Verificar autenticación y rol
requiereAuth('admin', '../../views/auth/login.php');

$titulo_pagina = "Gestión de Usuarios";
$mensaje = '';
$tipo_mensaje = '';

// Procesar acciones
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
                // 1. Reasignar alertas a NULL
                $stmt1 = $conexion->prepare("UPDATE alertas_mineria SET usuario_asignado = NULL WHERE usuario_asignado = ?");
                $stmt1->bind_param("i", $usuario_id);
                $stmt1->execute();
                
                // 2. Mantener actividades (para auditoría) pero marcar como usuario eliminado
                $stmt2 = $conexion->prepare("UPDATE actividades SET descripcion = CONCAT(descripcion, ' [Usuario eliminado]') WHERE usuario_id = ?");
                $stmt2->bind_param("i", $usuario_id);
                $stmt2->execute();
                
                // 3. Eliminar el usuario
                $stmt3 = $conexion->prepare("DELETE FROM usuarios WHERE id = ?");
                $stmt3->bind_param("i", $usuario_id);
                $stmt3->execute();
                
                if ($stmt3->affected_rows > 0) {
                    // Registrar la eliminación
                    registrarActividad($_SESSION['usuario_id'], 'usuario_eliminado', "Usuario eliminado ID: $usuario_id", $conexion);
                    
                    $conexion->commit();
                    $mensaje = 'Usuario eliminado exitosamente. Sus alertas han sido reasignadas y las actividades mantenidas para auditoría.';
                    $tipo_mensaje = 'exito';
                } else {
                    $conexion->rollback();
                    $mensaje = 'No se pudo eliminar el usuario.';
                    $tipo_mensaje = 'error';
                }
                
            } catch (Exception $e) {
                $conexion->rollback();
                $mensaje = 'Error al eliminar usuario: ' . $e->getMessage();
                $tipo_mensaje = 'error';
            }
        }
        
        cerrarDB($conexion);
    }
    
    // Desactivar usuario (opción más segura)
    elseif ($accion === 'desactivar' && $usuario_id > 0) {
        $conexion = conectarDB();
        
        if ($usuario_id == $_SESSION['usuario_id']) {
            $mensaje = 'No puedes desactivar tu propia cuenta.';
            $tipo_mensaje = 'error';
        } else {
            $stmt = $conexion->prepare("UPDATE usuarios SET activo = 0 WHERE id = ?");
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $mensaje = 'Usuario desactivado exitosamente.';
                $tipo_mensaje = 'exito';
                
                registrarActividad($_SESSION['usuario_id'], 'usuario_desactivado', "Usuario desactivado ID: $usuario_id", $conexion);
            } else {
                $mensaje = 'No se pudo desactivar el usuario.';
                $tipo_mensaje = 'error';
            }
            
            $stmt->close();
        }
        
        cerrarDB($conexion);
    }
    
    // Activar usuario
    elseif ($accion === 'activar' && $usuario_id > 0) {
        $conexion = conectarDB();
        
        $stmt = $conexion->prepare("UPDATE usuarios SET activo = 1 WHERE id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            $mensaje = 'Usuario activado exitosamente.';
            $tipo_mensaje = 'exito';
            
            registrarActividad($_SESSION['usuario_id'], 'usuario_activado', "Usuario activado ID: $usuario_id", $conexion);
        } else {
            $mensaje = 'No se pudo activar el usuario.';
            $tipo_mensaje = 'error';
        }
        
        $stmt->close();
        cerrarDB($conexion);
    }
}

// Obtener todos los usuarios para mostrar
$conexion = conectarDB();
$stmt = $conexion->prepare("
    SELECT u.*, 
           COUNT(am.id) as alertas_asignadas,
           MAX(a.fecha_actividad) as ultima_actividad
    FROM usuarios u 
    LEFT JOIN alertas_mineria am ON u.id = am.usuario_asignado 
    LEFT JOIN actividades a ON u.id = a.usuario_id
    GROUP BY u.id
    ORDER BY u.fecha_registro DESC
");
$stmt->execute();
$usuarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
cerrarDB($conexion);

include '../../includes/header.php';
?>

<main class="contenido-principal">
    <section class="dashboard-header">
        <div class="contenedor">
            <h1>Gestión de Usuarios</h1>
            <p>Administración completa de usuarios del sistema Figger Energy SAS</p>
        </div>
    </section>

    <div class="contenedor">
        <?php echo mostrarMensaje($mensaje, $tipo_mensaje); ?>
        
        <!-- Estadísticas de usuarios -->
        <div class="dashboard-grid mb-2">
            <div class="tarjeta">
                <h3>📊 Estadísticas</h3>
                <?php
                $total = count($usuarios);
                $activos = count(array_filter($usuarios, function($u) { return $u['activo']; }));
                $admins = count(array_filter($usuarios, function($u) { return $u['rol'] == 'admin'; }));
                $empleados = count(array_filter($usuarios, function($u) { return $u['rol'] == 'empleado'; }));
                $auditores = count(array_filter($usuarios, function($u) { return $u['rol'] == 'auditor'; }));
                ?>
                <p><strong>Total usuarios:</strong> <?php echo $total; ?></p>
                <p><strong>Usuarios activos:</strong> <?php echo $activos; ?></p>
                <p><strong>Administradores:</strong> <?php echo $admins; ?></p>
                <p><strong>Empleados:</strong> <?php echo $empleados; ?></p>
                <p><strong>Auditores:</strong> <?php echo $auditores; ?></p>
            </div>
            
            <div class="tarjeta">
                <h3>🔧 Acciones Rápidas</h3>
                <p><a href="../../views/auth/register.php" class="boton-enlace">Registrar Nuevo Usuario</a></p>
                <p><a href="#" onclick="exportarUsuarios()" class="boton-enlace">Exportar Lista</a></p>
                <p><a href="#" onclick="alert('Función en desarrollo')" class="boton-enlace">Importar Usuarios</a></p>
            </div>
        </div>

        <!-- Tabla de usuarios -->
        <div class="seccion-dashboard">
            <h2>👥 Lista de Usuarios</h2>
            <div class="tabla-contenedor">
                <table class="tabla-datos" id="tabla-usuarios">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Alertas</th>
                            <th>Registro</th>
                            <th>Última Conexión</th>
                            <th>Última Actividad</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo $usuario['id']; ?></td>
                                <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                <td><span class="badge badge-<?php echo $usuario['rol']; ?>"><?php echo ucfirst($usuario['rol']); ?></span></td>
                                <td>
                                    <span class="badge <?php echo $usuario['activo'] ? 'badge-activo' : 'badge-inactivo'; ?>">
                                        <?php echo $usuario['activo'] ? 'Activo' : 'Inactivo'; ?>
                                    </span>
                                </td>
                                <td><?php echo $usuario['alertas_asignadas']; ?></td>
                                <td><?php echo formatearFecha($usuario['fecha_registro']); ?></td>
                                <td><?php echo formatearFecha($usuario['ultima_conexion']); ?></td>
                                <td><?php echo formatearFecha($usuario['ultima_actividad']); ?></td>
                                <td>
                                    <?php if ($usuario['id'] != $_SESSION['usuario_id']): ?>
                                        <!-- Botón para activar/desactivar -->
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="accion" value="<?php echo $usuario['activo'] ? 'desactivar' : 'activar'; ?>">
                                            <input type="hidden" name="usuario_id" value="<?php echo $usuario['id']; ?>">
                                            <button type="submit" 
                                                    class="boton-pequeno <?php echo $usuario['activo'] ? 'boton-advertencia' : 'boton-exito'; ?>"
                                                    onclick="return confirm('¿Está seguro?')">
                                                <?php echo $usuario['activo'] ? 'Desactivar' : 'Activar'; ?>
                                            </button>
                                        </form>
                                        
                                        <!-- Botón para eliminar (peligroso) -->
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <input type="hidden" name="usuario_id" value="<?php echo $usuario['id']; ?>">
                                            <button type="submit" 
                                                    class="boton-pequeno boton-peligro"
                                                    onclick="return confirm('⚠️ ATENCIÓN: Esta acción ELIMINARÁ permanentemente al usuario y reasignará sus alertas. ¿Está completamente seguro?')">
                                                Eliminar
                                            </button>
                                        </form>
                                        
                                        <!-- Botón para ver detalles -->
                                        <button onclick="verDetalleUsuario(<?php echo $usuario['id']; ?>)" class="boton-pequeno">
                                            Ver Detalles
                                        </button>
                                    <?php else: ?>
                                        <span class="texto-muted">Usuario actual</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Información importante -->
        <div class="seccion-dashboard">
            <h2>ℹ️ Información Importante</h2>
            <div class="dashboard-grid">
                <div class="tarjeta">
                    <h3>⚠️ Antes de Eliminar un Usuario</h3>
                    <ul style="text-align: left;">
                        <li>Sus alertas asignadas serán reasignadas automáticamente</li>
                        <li>El historial de actividades se mantiene para auditoría</li>
                        <li>Esta acción es <strong>irreversible</strong></li>
                        <li>Considere desactivar en lugar de eliminar</li>
                    </ul>
                </div>
                
                <div class="tarjeta">
                    <h3>✅ Recomendaciones</h3>
                    <ul style="text-align: left;">
                        <li><strong>Desactivar:</strong> Para usuarios temporalmente inactivos</li>
                        <li><strong>Eliminar:</strong> Solo para usuarios definitivamente fuera del sistema</li>
                        <li>Revisar alertas asignadas antes de acciones drásticas</li>
                        <li>Mantener comunicación con el usuario antes de cambios</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- JavaScript específico de gestión de usuarios -->
<script>
function verDetalleUsuario(id) {
    alert('Ver detalles del usuario #' + id + ' - Función en desarrollo');
    // Aquí se implementaría un modal o página de detalles
}

function exportarUsuarios() {
    alert('Exportar lista de usuarios - Función en desarrollo');
    // Aquí se implementaría la exportación a CSV/Excel
}

// Filtros y búsqueda en la tabla
document.addEventListener('DOMContentLoaded', function() {
    // Aquí se puede añadir funcionalidad de filtrado y búsqueda
    console.log('Gestión de usuarios cargada correctamente');
});
</script>

<?php include '../../includes/footer.php'; ?>
