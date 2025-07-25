<?php
/**
 * Dashboard Empleado - Figger Energy SAS
 * Panel de control para usuarios con rol empleado
 */

session_start();

// Verificar si está logueado y tiene rol de empleado
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'empleado') {
    header("Location: login.php");
    exit();
}

$titulo_pagina = "Dashboard Empleado";
include 'includes/db.php';

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
        SUM(CASE WHEN estado = 'investigando' THEN 1 ELSE 0 END) as investigando
    FROM alertas_mineria 
    WHERE usuario_asignado = ?
");
$stmt_stats->bind_param("i", $usuario_id);
$stmt_stats->execute();
$estadisticas = $stmt_stats->get_result()->fetch_assoc();

// Actividades recientes del empleado
$stmt_actividades = $conexion->prepare("
    SELECT accion, descripcion, fecha_actividad 
    FROM actividades 
    WHERE usuario_id = ? 
    ORDER BY fecha_actividad DESC 
    LIMIT 10
");
$stmt_actividades->bind_param("i", $usuario_id);
$stmt_actividades->execute();
$mis_actividades = $stmt_actividades->get_result()->fetch_all(MYSQLI_ASSOC);

// Alertas disponibles para asignar (sin asignar)
$stmt_disponibles = $conexion->prepare("
    SELECT * FROM alertas_mineria 
    WHERE usuario_asignado IS NULL AND estado = 'activa'
    ORDER BY nivel_riesgo DESC, fecha_deteccion ASC
    LIMIT 5
");
$stmt_disponibles->execute();
$alertas_disponibles = $stmt_disponibles->get_result()->fetch_all(MYSQLI_ASSOC);

cerrarDB($conexion);

include 'includes/header.php';
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
                <p><strong>Total asignadas:</strong> <?php echo $estadisticas['total_asignadas']; ?> alertas</p>
                <p><strong>Resueltas:</strong> <?php echo $estadisticas['resueltas']; ?> alertas</p>
                <p><strong>En investigación:</strong> <?php echo $estadisticas['investigando']; ?> alertas</p>
                <p><strong>Activas:</strong> <?php echo $estadisticas['activas']; ?> alertas</p>
                <?php if ($estadisticas['total_asignadas'] > 0): ?>
                    <p><strong>Efectividad:</strong> <?php echo round(($estadisticas['resueltas'] / $estadisticas['total_asignadas']) * 100, 1); ?>%</p>
                <?php endif; ?>
            </div>

            <div class="tarjeta">
                <h3>🎯 Tareas Prioritarias</h3>
                <p><strong>Alertas críticas activas:</strong> 
                    <?php echo count(array_filter($mis_alertas, function($a) { return $a['nivel_riesgo'] == 'critico' && $a['estado'] != 'resuelta'; })); ?>
                </p>
                <p><strong>Alertas de alto riesgo:</strong> 
                    <?php echo count(array_filter($mis_alertas, function($a) { return $a['nivel_riesgo'] == 'alto' && $a['estado'] != 'resuelta'; })); ?>
                </p>
                <p><strong>Pendientes de verificación:</strong> 
                    <?php echo count(array_filter($mis_alertas, function($a) { return $a['estado'] == 'activa'; })); ?>
                </p>
                <p><strong>Próximas a vencer:</strong> <span style="color: orange;">2 alertas</span></p>
            </div>

            <div class="tarjeta">
                <h3>⚡ Acciones Rápidas</h3>
                <p><a href="#mis-alertas" class="boton">Ver Mis Alertas</a></p>
                <p><a href="#disponibles" class="boton">Alertas Disponibles</a></p>
                <p><a href="#" onclick="alert('Función en desarrollo')" class="boton">Reportar en Campo</a></p>
                <p><a href="contacto.php" class="boton">Contactar Coordinación</a></p>
            </div>

            <div class="tarjeta">
                <h3>📋 Estado del Día</h3>
                <p><strong>Hora actual:</strong> <?php echo date('H:i:s'); ?></p>
                <p><strong>Alertas por revisar:</strong> <?php echo count(array_filter($mis_alertas, function($a) { return $a['estado'] == 'activa'; })); ?></p>
                <p><strong>Ubicación asignada:</strong> Región Central</p>
                <p><strong>Estado operativo:</strong> <span style="color: green;">Disponible</span></p>
            </div>
        </div>

        <!-- Mis Alertas Asignadas -->
        <div id="mis-alertas" class="tarjeta mb-2">
            <h3>🚨 Mis Alertas Asignadas</h3>
            <p>Alertas de minería ilegal bajo tu responsabilidad</p>
            
            <?php if (empty($mis_alertas)): ?>
                <p style="text-align: center; color: #666; padding: 2rem;">
                    No tienes alertas asignadas actualmente. Revisa las alertas disponibles para tomar casos.
                </p>
            <?php else: ?>
                <table class="tabla">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ubicación</th>
                            <th>Tipo</th>
                            <th>Nivel de Riesgo</th>
                            <th>Estado</th>
                            <th>Fecha Detección</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mis_alertas as $alerta): ?>
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
                            <td>
                                <span style="color: <?php 
                                    echo $alerta['estado'] == 'resuelta' ? 'green' : 
                                        ($alerta['estado'] == 'investigando' ? 'orange' : 'red'); 
                                ?>;">
                                    <?php echo ucfirst($alerta['estado']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($alerta['fecha_deteccion'])); ?></td>
                            <td>
                                <button onclick="verDetalleAlerta(<?php echo $alerta['id']; ?>)" class="boton" style="font-size: 0.8rem;">Ver</button>
                                <?php if ($alerta['estado'] != 'resuelta'): ?>
                                    <button onclick="actualizarAlerta(<?php echo $alerta['id']; ?>)" class="boton" style="font-size: 0.8rem;">Actualizar</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Alertas Disponibles -->
        <div id="disponibles" class="tarjeta mb-2">
            <h3>📋 Alertas Disponibles para Asignar</h3>
            <p>Nuevas alertas que puedes tomar para investigación</p>
            
            <?php if (empty($alertas_disponibles)): ?>
                <p style="text-align: center; color: #666; padding: 2rem;">
                    No hay alertas disponibles para asignar en este momento.
                </p>
            <?php else: ?>
                <table class="tabla">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ubicación</th>
                            <th>Tipo</th>
                            <th>Nivel de Riesgo</th>
                            <th>Fecha Detección</th>
                            <th>Descripción</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alertas_disponibles as $alerta): ?>
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
                            <td><?php echo date('d/m/Y H:i', strtotime($alerta['fecha_deteccion'])); ?></td>
                            <td><?php echo substr(htmlspecialchars($alerta['descripcion'] ?? ''), 0, 50) . '...'; ?></td>
                            <td>
                                <button onclick="tomarAlerta(<?php echo $alerta['id']; ?>)" class="boton boton-primario" style="font-size: 0.8rem;">Tomar</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Mis Actividades -->
        <div class="tarjeta mb-2">
            <h3>📋 Mis Actividades Recientes</h3>
            <p>Historial de acciones realizadas en el sistema</p>
            
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Acción</th>
                        <th>Descripción</th>
                        <th>Fecha y Hora</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mis_actividades as $actividad): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($actividad['accion']); ?></td>
                        <td><?php echo htmlspecialchars($actividad['descripcion']); ?></td>
                        <td><?php echo date('d/m/Y H:i:s', strtotime($actividad['fecha_actividad'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Herramientas de Trabajo -->
        <div class="dashboard-grid">
            <div class="tarjeta">
                <h3>🛠️ Herramientas de Campo</h3>
                <ul style="text-align: left;">
                    <li><a href="#" onclick="alert('Función en desarrollo')">Formulario de inspección</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Reporte fotográfico</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">GPS y coordenadas</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Lista de verificación</a></li>
                </ul>
            </div>

            <div class="tarjeta">
                <h3>📈 Mis Reportes</h3>
                <ul style="text-align: left;">
                    <li><a href="#" onclick="alert('Función en desarrollo')">Reporte semanal</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Estadísticas personales</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Casos cerrados</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Tiempo de respuesta</a></li>
                </ul>
            </div>

            <div class="tarjeta">
                <h3>📞 Contactos de Emergencia</h3>
                <p><strong>Coordinación Central:</strong><br>+57 (1) 123-4567</p>
                <p><strong>Emergencias 24/7:</strong><br>+57 (1) 987-6543</p>
                <p><strong>Soporte Técnico:</strong><br>soporte@figgerenergy.gov.co</p>
                <p><strong>Radio de Campo:</strong><br>Canal 5 - Frecuencia 462.675</p>
            </div>

            <div class="tarjeta">
                <h3>ℹ️ Información Operativa</h3>
                <p><strong>Zona asignada:</strong> Región Central</p>
                <p><strong>Supervisor:</strong> Ana Coordinadora</p>
                <p><strong>Equipo:</strong> Equipo Alpha-2</p>
                <p><strong>Próxima capacitación:</strong> 15/08/2025</p>
            </div>
        </div>
    </div>
</main>

<script>
// Funciones específicas para el dashboard de empleado
function verDetalleAlerta(id) {
    alert('Función en desarrollo: Ver detalle de alerta ID ' + id);
    // Aquí se implementaría la función para ver detalles de la alerta
}

function actualizarAlerta(id) {
    if (confirm('¿Desea actualizar el estado de esta alerta?')) {
        alert('Función en desarrollo: Actualizar alerta ID ' + id);
        // Aquí se implementaría la función para actualizar la alerta
    }
}

function tomarAlerta(id) {
    if (confirm('¿Desea tomar esta alerta para investigación?')) {
        alert('Función en desarrollo: Tomar alerta ID ' + id);
        // Aquí se implementaría la función para asignar la alerta al empleado
        // Se haría una petición AJAX al servidor para actualizar la base de datos
    }
}
</script>

<?php include 'includes/footer.php'; ?>
