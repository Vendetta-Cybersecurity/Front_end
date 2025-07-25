<?php
/**
 * Dashboard Administrador - Figger Energy SAS
 * Panel de control para usuarios con rol administrador
 */

session_start();

// Verificar si está logueado y tiene rol de administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$titulo_pagina = "Dashboard Administrador";
include 'includes/db.php';

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

include 'includes/header.php';
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
                <?php foreach ($alertas_por_estado as $stat): ?>
                    <p><strong><?php echo ucfirst($stat['estado']); ?>:</strong> <?php echo $stat['cantidad']; ?> alertas</p>
                <?php endforeach; ?>
                <p><strong>Total:</strong> <?php echo array_sum(array_column($alertas_por_estado, 'cantidad')); ?> alertas registradas</p>
            </div>

            <div class="tarjeta">
                <h3>📊 Resumen del Sistema</h3>
                <p><strong>Zona horaria:</strong> America/Bogota</p>
                <p><strong>Última actualización:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
                <p><strong>Estado del sistema:</strong> <span style="color: green;">Operativo</span></p>
                <p><strong>Nivel de alerta:</strong> <span style="color: orange;">Medio</span></p>
            </div>

            <div class="tarjeta">
                <h3>⚡ Acciones Rápidas</h3>
                <p><a href="gestionar_usuarios.php" class="boton">Gestionar Usuarios</a></p>
                <p><a href="#usuarios" class="boton">Ver Lista de Usuarios</a></p>
                <p><a href="#alertas" class="boton">Ver Todas las Alertas</a></p>
                <p><a href="contacto.php" class="boton">Revisar Contactos</a></p>
                <p><a href="#" onclick="alert('Función en desarrollo')" class="boton">Generar Reporte</a></p>
            </div>
        </div>

        <!-- Gestión de Usuarios -->
        <div id="usuarios" class="tarjeta mb-2">
            <h3>👥 Gestión de Usuarios</h3>
            <p>Administrar cuentas de usuario del sistema</p>
            
            <table class="tabla">
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
                        <td><span class="badge"><?php echo ucfirst($usuario['rol']); ?></span></td>
                        <td>
                            <?php if ($usuario['activo']): ?>
                                <span style="color: green;">Activo</span>
                            <?php else: ?>
                                <span style="color: red;">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></td>
                        <td>
                            <?php if ($usuario['ultima_conexion']): ?>
                                <?php echo date('d/m/Y H:i', strtotime($usuario['ultima_conexion'])); ?>
                            <?php else: ?>
                                <em>Nunca</em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($usuario['id'] !== $_SESSION['usuario_id']): ?>
                                <a href="gestionar_usuarios.php" class="boton" style="font-size: 0.8rem;">Gestionar</a>
                            <?php else: ?>
                                <em>Usuario actual</em>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Alertas Recientes -->
        <div id="alertas" class="tarjeta mb-2">
            <h3>🚨 Alertas Recientes</h3>
            <p>Últimas alertas de minería ilegal detectadas</p>
            
            <table class="tabla">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ubicación</th>
                        <th>Tipo</th>
                        <th>Nivel de Riesgo</th>
                        <th>Estado</th>
                        <th>Asignado a</th>
                        <th>Fecha Detección</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alertas_recientes as $alerta): ?>
                    <tr>
                        <td><?php echo $alerta['id']; ?></td>
                        <td><?php echo htmlspecialchars($alerta['ubicacion']); ?></td>
                        <td><?php echo ucfirst($alerta['tipo_alerta']); ?></td>
                        <td>
                            <span style="color: <?php 
                                echo $alerta['nivel_riesgo'] == 'critico' ? 'red' : 
                                    ($alerta['nivel_riesgo'] == 'alto' ? 'orange' : 
                                    ($alerta['nivel_riesgo'] == 'medio' ? 'goldenrod' : 'green')); 
                            ?>;">
                                <?php echo ucfirst($alerta['nivel_riesgo']); ?>
                            </span>
                        </td>
                        <td><?php echo ucfirst($alerta['estado']); ?></td>
                        <td>
                            <?php if ($alerta['usuario_asignado_nombre']): ?>
                                <?php echo htmlspecialchars($alerta['usuario_asignado_nombre']); ?>
                            <?php else: ?>
                                <em>Sin asignar</em>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($alerta['fecha_deteccion'])); ?></td>
                        <td>
                            <button onclick="alert('Función de gestión en desarrollo')" class="boton" style="font-size: 0.8rem;">Gestionar</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Actividades del Sistema -->
        <div class="tarjeta mb-2">
            <h3>📋 Actividades Recientes del Sistema</h3>
            <p>Últimas acciones realizadas por los usuarios</p>
            
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Descripción</th>
                        <th>Fecha y Hora</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ultimas_actividades as $actividad): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($actividad['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($actividad['accion']); ?></td>
                        <td><?php echo htmlspecialchars($actividad['descripcion']); ?></td>
                        <td><?php echo date('d/m/Y H:i:s', strtotime($actividad['fecha_actividad'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Herramientas de Administración -->
        <div class="dashboard-grid">
            <div class="tarjeta">
                <h3>🔧 Herramientas del Sistema</h3>
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
                <h3>⚙️ Configuración</h3>
                <ul style="text-align: left;">
                    <li><a href="#" onclick="alert('Función en desarrollo')">Preferencias del sistema</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Configurar notificaciones</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Gestionar integraciones</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Configurar seguridad</a></li>
                </ul>
            </div>

            <div class="tarjeta">
                <h3>📞 Soporte</h3>
                <p><strong>Documentación:</strong> <a href="#" onclick="alert('Función en desarrollo')">Manual de usuario</a></p>
                <p><strong>Soporte técnico:</strong> soporte@figgerenergy.gov.co</p>
                <p><strong>Emergencias:</strong> +57 (1) 987-6543</p>
                <p><strong>Versión del sistema:</strong> 1.0.0</p>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
