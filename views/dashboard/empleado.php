<?php
/**
 * Dashboard Empleado - Figger Energy SAS
 * Panel de control refactorizado para empleados
 */

// Cargar dependencias
require_once '../../config/database.php';
require_once '../../lib/functions.php';

// Verificar autenticación y rol
requiereAuth('empleado', '../../views/auth/login.php');

$titulo_pagina = "Dashboard Empleado";

$conexion = conectarDB();
$usuario_id = $_SESSION['usuario_id'];

// Alertas asignadas al empleado
$stmt_mis_alertas = $conexion->prepare("
    SELECT * FROM alertas_mineria 
    WHERE usuario_asignado = ? 
    ORDER BY fecha_deteccion DESC
");
$stmt_mis_alertas->bind_param("i", $usuario_id);
$stmt_mis_alertas->execute();
$mis_alertas = $stmt_mis_alertas->get_result()->fetch_all(MYSQLI_ASSOC);

// Estadísticas del empleado
$stmt_stats = $conexion->prepare("
    SELECT 
        COUNT(*) as total_asignadas,
        SUM(CASE WHEN estado = 'resuelta' THEN 1 ELSE 0 END) as resueltas,
        SUM(CASE WHEN estado = 'activa' THEN 1 ELSE 0 END) as activas,
        SUM(CASE WHEN estado = 'en_proceso' THEN 1 ELSE 0 END) as en_proceso
    FROM alertas_mineria 
    WHERE usuario_asignado = ?
");
$stmt_stats->bind_param("i", $usuario_id);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();

// Alertas disponibles para asignar
$stmt_disponibles = $conexion->prepare("
    SELECT * FROM alertas_mineria 
    WHERE usuario_asignado IS NULL AND estado = 'activa'
    ORDER BY nivel_riesgo DESC, fecha_deteccion ASC
    LIMIT 5
");
$stmt_disponibles->execute();
$alertas_disponibles = $stmt_disponibles->get_result()->fetch_all(MYSQLI_ASSOC);

cerrarDB($conexion);

include '../../includes/header.php';
?>

<main class="contenido-principal">
    <!-- Header del Dashboard -->
    <section class="dashboard-header">
        <div class="contenedor">
            <h1>Panel de Empleado</h1>
            <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?> - Gestión de alertas y operaciones de campo</p>
        </div>
    </section>

    <div class="contenedor">
        <!-- Estadísticas del Empleado -->
        <div class="dashboard-grid">
            <div class="tarjeta">
                <h3>📊 Mis Estadísticas</h3>
                <p><strong>Total asignadas:</strong> <?php echo $stats['total_asignadas']; ?></p>
                <p><strong>Resueltas:</strong> <?php echo $stats['resueltas']; ?></p>
                <p><strong>En proceso:</strong> <?php echo $stats['en_proceso']; ?></p>
                <p><strong>Activas:</strong> <?php echo $stats['activas']; ?></p>
                <?php if ($stats['total_asignadas'] > 0): ?>
                    <p><strong>Efectividad:</strong> <?php echo round(($stats['resueltas'] / $stats['total_asignadas']) * 100, 1); ?>%</p>
                <?php endif; ?>
            </div>

            <div class="tarjeta">
                <h3>🎯 Alertas Prioritarias</h3>
                <?php 
                $prioritarias = array_filter($mis_alertas, function($a) { 
                    return $a['nivel_riesgo'] == 'alto' && $a['estado'] == 'activa'; 
                });
                ?>
                <p><strong>Alto riesgo:</strong> <?php echo count($prioritarias); ?> alertas</p>
                <p><strong>Próxima acción:</strong> 
                   <?php echo !empty($prioritarias) ? 'Revisar alerta #' . $prioritarias[0]['id'] : 'Ninguna pendiente'; ?>
                </p>
            </div>

            <div class="tarjeta">
                <h3>⏰ Actividad Reciente</h3>
                <p><strong>Última asignación:</strong> 
                   <?php echo !empty($mis_alertas) ? formatearFecha($mis_alertas[0]['fecha_deteccion']) : 'No disponible'; ?>
                </p>
                <p><strong>Estado:</strong> Activo en el sistema</p>
            </div>

            <div class="tarjeta">
                <h3>🔄 Acciones Rápidas</h3>
                <p><a href="#alertas-disponibles" class="boton-enlace">Ver Alertas Disponibles</a></p>
                <p><a href="#mis-alertas" class="boton-enlace">Mis Alertas</a></p>
                <p><a href="#" onclick="alert('Función en desarrollo')" class="boton-enlace">Nuevo Reporte</a></p>
            </div>
        </div>

        <!-- Mis Alertas Asignadas -->
        <div class="seccion-dashboard" id="mis-alertas">
            <h2>📋 Mis Alertas Asignadas</h2>
            <?php if (!empty($mis_alertas)): ?>
                <div class="tabla-contenedor">
                    <table class="tabla-datos">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ubicación</th>
                                <th>Tipo</th>
                                <th>Riesgo</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mis_alertas as $alerta): ?>
                                <tr>
                                    <td><?php echo $alerta['id']; ?></td>
                                    <td><?php echo htmlspecialchars($alerta['ubicacion']); ?></td>
                                    <td><?php echo htmlspecialchars($alerta['tipo_actividad']); ?></td>
                                    <td><span class="badge badge-riesgo-<?php echo $alerta['nivel_riesgo']; ?>"><?php echo ucfirst($alerta['nivel_riesgo']); ?></span></td>
                                    <td><span class="badge badge-<?php echo $alerta['estado']; ?>"><?php echo ucfirst($alerta['estado']); ?></span></td>
                                    <td><?php echo formatearFecha($alerta['fecha_deteccion']); ?></td>
                                    <td>
                                        <button onclick="verDetalleAlerta(<?php echo $alerta['id']; ?>)" class="boton-pequeno">Ver</button>
                                        <?php if ($alerta['estado'] == 'activa'): ?>
                                            <button onclick="cambiarEstadoAlerta(<?php echo $alerta['id']; ?>, 'en_proceso')" class="boton-pequeno boton-proceso">Procesar</button>
                                        <?php elseif ($alerta['estado'] == 'en_proceso'): ?>
                                            <button onclick="cambiarEstadoAlerta(<?php echo $alerta['id']; ?>, 'resuelta')" class="boton-pequeno boton-exito">Resolver</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="mensaje mensaje-info">
                    <p>No tienes alertas asignadas actualmente. Revisa las alertas disponibles para tomar una nueva asignación.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Alertas Disponibles -->
        <div class="seccion-dashboard" id="alertas-disponibles">
            <h2>🔍 Alertas Disponibles para Asignar</h2>
            <?php if (!empty($alertas_disponibles)): ?>
                <div class="tabla-contenedor">
                    <table class="tabla-datos">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ubicación</th>
                                <th>Tipo</th>
                                <th>Riesgo</th>
                                <th>Fecha</th>
                                <th>Descripción</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alertas_disponibles as $alerta): ?>
                                <tr>
                                    <td><?php echo $alerta['id']; ?></td>
                                    <td><?php echo htmlspecialchars($alerta['ubicacion']); ?></td>
                                    <td><?php echo htmlspecialchars($alerta['tipo_actividad']); ?></td>
                                    <td><span class="badge badge-riesgo-<?php echo $alerta['nivel_riesgo']; ?>"><?php echo ucfirst($alerta['nivel_riesgo']); ?></span></td>
                                    <td><?php echo formatearFecha($alerta['fecha_deteccion']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($alerta['descripcion'], 0, 100)) . '...'; ?></td>
                                    <td>
                                        <button onclick="asignarAlerta(<?php echo $alerta['id']; ?>)" class="boton-pequeno boton-primario">Asignarme</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="mensaje mensaje-info">
                    <p>No hay alertas disponibles para asignar en este momento.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Herramientas del Empleado -->
        <div class="dashboard-grid">
            <div class="tarjeta">
                <h3>🛠️ Herramientas de Campo</h3>
                <ul style="text-align: left;">
                    <li><a href="#" onclick="alert('Función en desarrollo')">Crear reporte de campo</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Solicitar apoyo</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Mapa de operaciones</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Manual de procedimientos</a></li>
                </ul>
            </div>

            <div class="tarjeta">
                <h3>📞 Contactos de Emergencia</h3>
                <p><strong>Línea directa:</strong> +57 018000001</p>
                <p><strong>Coordinador:</strong> coord@figgerenergy.gov.co</p>
                <p><strong>Emergencias:</strong> emergencias@figgerenergy.gov.co</p>
            </div>

            <div class="tarjeta">
                <h3>📚 Recursos</h3>
                <ul style="text-align: left;">
                    <li><a href="#" onclick="alert('Función en desarrollo')">Protocolos de seguridad</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Capacitaciones</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Base de conocimiento</a></li>
                </ul>
            </div>
        </div>
    </div>
</main>

<!-- JavaScript específico del empleado -->
<script>
function verDetalleAlerta(id) {
    alert('Ver detalle de alerta #' + id + ' - Función en desarrollo');
}

function cambiarEstadoAlerta(id, nuevoEstado) {
    if (confirm('¿Está seguro de cambiar el estado de la alerta #' + id + '?')) {
        alert('Cambiar estado a: ' + nuevoEstado + ' - Función en desarrollo');
        // Aquí se implementaría la llamada AJAX
    }
}

function asignarAlerta(id) {
    if (confirm('¿Desea asignarse la alerta #' + id + '?')) {
        alert('Asignar alerta #' + id + ' - Función en desarrollo');
        // Aquí se implementaría la llamada AJAX
    }
}
</script>

<?php include '../../includes/footer.php'; ?>
