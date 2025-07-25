<?php
/**
 * Dashboard Administrador - Figger Energy SAS
 * Panel de control refactorizado para administradores
 */


// Cargar dependencias
require_once '../../config/database.php';
require_once '../../lib/functions.php';

// Verificar autenticación y rol
requiereAuth('admin', '../../views/auth/login.php');

$titulo_pagina = "Dashboard Administrador";

// Obtener estadísticas generales
$conexion = conectarDB();

// Estadísticas de usuarios
$stmt_usuarios = $conexion->prepare("SELECT rol, COUNT(*) as cantidad FROM usuarios WHERE activo = 1 GROUP BY rol");
$stmt_usuarios->execute();
$usuarios_por_rol = $stmt_usuarios->get_result()->fetch_all(MYSQLI_ASSOC);

// Estadísticas de alertas
$stmt_alertas = $conexion->prepare("SELECT estado, COUNT(*) as cantidad FROM alertas_mineria GROUP BY estado");
$stmt_alertas->execute();
$alertas_por_estado = $stmt_alertas->get_result()->fetch_all(MYSQLI_ASSOC);

// Últimas actividades
$stmt_actividades = $conexion->prepare("
    SELECT a.accion, a.descripcion, a.fecha_actividad, u.nombre 
    FROM actividades a 
    JOIN usuarios u ON a.usuario_id = u.id 
    ORDER BY a.fecha_actividad DESC 
    LIMIT 10
");
$stmt_actividades->execute();
$ultimas_actividades = $stmt_actividades->get_result()->fetch_all(MYSQLI_ASSOC);

// Alertas recientes
$stmt_alertas_recientes = $conexion->prepare("
    SELECT am.*, u.nombre as usuario_asignado_nombre
    FROM alertas_mineria am
    LEFT JOIN usuarios u ON am.usuario_asignado = u.id
    ORDER BY am.fecha_deteccion DESC
    LIMIT 5
");
$stmt_alertas_recientes->execute();
$alertas_recientes = $stmt_alertas_recientes->get_result()->fetch_all(MYSQLI_ASSOC);

// Todos los usuarios para gestión
$stmt_todos_usuarios = $conexion->prepare("SELECT * FROM usuarios ORDER BY fecha_registro DESC");
$stmt_todos_usuarios->execute();
$todos_usuarios = $stmt_todos_usuarios->get_result()->fetch_all(MYSQLI_ASSOC);

cerrarDB($conexion);

include '../../includes/header.php';
?>

<main class="contenido-principal">
    <!-- Header del Dashboard -->
    <section class="dashboard-header">
        <div class="contenedor">
            <h1>Panel de Administración</h1>
            <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?> - Control total del sistema Figger Energy SAS</p>
        </div>
    </section>

    <div class="contenedor">
        <!-- Estadísticas Rápidas -->
        <div class="dashboard-grid">
            <div class="tarjeta">
                <h3>👥 Usuarios del Sistema</h3>
                <?php foreach ($usuarios_por_rol as $stat): ?>
                    <p><strong><?php echo ucfirst($stat['rol']); ?>:</strong> <?php echo $stat['cantidad']; ?> usuarios</p>
                <?php endforeach; ?>
                <p><strong>Total:</strong> <?php echo array_sum(array_column($usuarios_por_rol, 'cantidad')); ?> usuarios activos</p>
            </div>

            <div class="tarjeta">
                <h3>🚨 Estado de Alertas</h3>
                <?php if (!empty($alertas_por_estado)): ?>
                    <?php foreach ($alertas_por_estado as $alerta): ?>
                        <p><strong><?php echo ucfirst($alerta['estado']); ?>:</strong> <?php echo $alerta['cantidad']; ?> alertas</p>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No hay alertas registradas</p>
                <?php endif; ?>
            </div>

            <div class="tarjeta">
                <h3>📊 Actividad del Sistema</h3>
                <p><strong>Actividades hoy:</strong> 
                   <?php echo count(array_filter($ultimas_actividades, function($a) { 
                       return date('Y-m-d', strtotime($a['fecha_actividad'])) == date('Y-m-d'); 
                   })); ?>
                </p>
                <p><strong>Última actividad:</strong> 
                   <?php echo !empty($ultimas_actividades) ? formatearFecha($ultimas_actividades[0]['fecha_actividad']) : 'No disponible'; ?>
                </p>
            </div>

            <div class="tarjeta">
                <h3>⚙️ Acciones Rápidas</h3>
                <p><a href="users.php" class="boton-enlace">Gestionar Usuarios</a></p>
                <p><a href="#" onclick="alert('Función en desarrollo')" class="boton-enlace">Ver Reportes</a></p>
                <p><a href="#" onclick="alert('Función en desarrollo')" class="boton-enlace">Configuración</a></p>
            </div>
        </div>

        <!-- Gestión de Usuarios -->
        <div class="seccion-dashboard">
            <h2>👥 Gestión de Usuarios</h2>
            <div class="tabla-contenedor">
                <table class="tabla-datos">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Registro</th>
                            <th>Última Conexión</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($todos_usuarios as $usuario): ?>
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
                                <td><?php echo formatearFecha($usuario['fecha_registro']); ?></td>
                                <td><?php echo formatearFecha($usuario['ultima_conexion']); ?></td>
                                <td>
                                    <a href="users.php?action=edit&id=<?php echo $usuario['id']; ?>" class="boton-pequeno">Editar</a>
                                    <?php if ($usuario['id'] != $_SESSION['usuario_id']): ?>
                                        <a href="users.php?action=toggle&id=<?php echo $usuario['id']; ?>" 
                                           class="boton-pequeno <?php echo $usuario['activo'] ? 'boton-peligro' : 'boton-exito'; ?>"
                                           onclick="return confirm('¿Está seguro?')">
                                            <?php echo $usuario['activo'] ? 'Desactivar' : 'Activar'; ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Alertas Recientes -->
        <div class="seccion-dashboard">
            <h2>🚨 Alertas Recientes</h2>
            <?php if (!empty($alertas_recientes)): ?>
                <div class="tabla-contenedor">
                    <table class="tabla-datos">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ubicación</th>
                                <th>Tipo</th>
                                <th>Nivel de Riesgo</th>
                                <th>Estado</th>
                                <th>Asignado a</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alertas_recientes as $alerta): ?>
                                <tr>
                                    <td><?php echo $alerta['id']; ?></td>
                                    <td><?php echo htmlspecialchars($alerta['ubicacion']); ?></td>
                                    <td><?php echo htmlspecialchars($alerta['tipo_actividad']); ?></td>
                                    <td><span class="badge badge-riesgo-<?php echo $alerta['nivel_riesgo']; ?>"><?php echo ucfirst($alerta['nivel_riesgo']); ?></span></td>
                                    <td><span class="badge badge-<?php echo $alerta['estado']; ?>"><?php echo ucfirst($alerta['estado']); ?></span></td>
                                    <td><?php echo $alerta['usuario_asignado_nombre'] ? htmlspecialchars($alerta['usuario_asignado_nombre']) : 'No asignado'; ?></td>
                                    <td><?php echo formatearFecha($alerta['fecha_deteccion']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>No hay alertas registradas en el sistema.</p>
            <?php endif; ?>
        </div>

        <!-- Últimas Actividades -->
        <div class="seccion-dashboard">
            <h2>📋 Registro de Actividades</h2>
            <div class="tabla-contenedor">
                <table class="tabla-datos">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Acción</th>
                            <th>Descripción</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ultimas_actividades as $actividad): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($actividad['nombre']); ?></td>
                                <td><span class="badge badge-actividad"><?php echo htmlspecialchars($actividad['accion']); ?></span></td>
                                <td><?php echo htmlspecialchars($actividad['descripcion']); ?></td>
                                <td><?php echo formatearFecha($actividad['fecha_actividad']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Herramientas de Sistema -->
        <div class="dashboard-grid">
            <div class="tarjeta">
                <h3>🔧 Administración</h3>
                <ul style="text-align: left;">
                    <li><a href="#" onclick="alert('Función en desarrollo')">Configurar sistema</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Respaldar base de datos</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Ver logs del sistema</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Gestionar permisos</a></li>
                </ul>
            </div>

            <div class="tarjeta">
                <h3>📈 Reportes</h3>
                <ul style="text-align: left;">
                    <li><a href="#" onclick="alert('Función en desarrollo')">Reporte mensual de alertas</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Estadísticas de usuarios</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Reporte de actividades</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Exportar datos</a></li>
                </ul>
            </div>

            <div class="tarjeta">
                <h3>ℹ️ Información del Sistema</h3>
                <p><strong>Estado:</strong> Operacional</p>
                <p><strong>Última actualización:</strong> <?php echo date('d/m/Y H:i'); ?></p>
                <p><strong>Versión del sistema:</strong> 1.0.0</p>
            </div>
        </div>
    </div>
</main>

<?php include '../../includes/footer.php'; ?>
