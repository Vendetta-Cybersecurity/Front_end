<?php
/**
 * Dashboard Auditor - Figger Energy SAS
 * Panel de control para usuarios con rol auditor
 */

session_start();

// Verificar si está logueado y tiene rol de auditor
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'auditor') {
    header("Location: login.php");
    exit();
}

$titulo_pagina = "Dashboard Auditor";
include 'includes/db.php';

$conexion = conectarDB();

// Estadísticas generales para auditoría
$stmt_stats_generales = $conexion->prepare("
    SELECT 
        COUNT(*) as total_alertas,
        SUM(CASE WHEN estado = 'resuelta' THEN 1 ELSE 0 END) as alertas_resueltas,
        SUM(CASE WHEN estado = 'verificada' THEN 1 ELSE 0 END) as alertas_verificadas,
        SUM(CASE WHEN estado = 'falsa' THEN 1 ELSE 0 END) as alertas_falsas,
        SUM(CASE WHEN nivel_riesgo = 'critico' THEN 1 ELSE 0 END) as alertas_criticas
    FROM alertas_mineria
");
$stmt_stats_generales->execute();
$stats_generales = $stmt_stats_generales->get_result()->fetch_assoc();

// Estadísticas por empleado
$stmt_stats_empleados = $conexion->prepare("
    SELECT 
        u.nombre,
        u.email,
        COUNT(am.id) as alertas_asignadas,
        SUM(CASE WHEN am.estado = 'resuelta' THEN 1 ELSE 0 END) as alertas_resueltas,
        AVG(CASE WHEN am.fecha_resolucion IS NOT NULL 
            THEN TIMESTAMPDIFF(HOUR, am.fecha_deteccion, am.fecha_resolucion) 
            ELSE NULL END) as tiempo_promedio_resolucion
    FROM usuarios u
    LEFT JOIN alertas_mineria am ON u.id = am.usuario_asignado
    WHERE u.rol = 'empleado' AND u.activo = 1
    GROUP BY u.id, u.nombre, u.email
    ORDER BY alertas_asignadas DESC
");
$stmt_stats_empleados->execute();
$stats_empleados = $stmt_stats_empleados->get_result()->fetch_all(MYSQLI_ASSOC);

// Alertas para auditoría (verificadas pero no auditadas)
$stmt_alertas_auditoria = $conexion->prepare("
    SELECT am.*, u.nombre as empleado_nombre
    FROM alertas_mineria am
    LEFT JOIN usuarios u ON am.usuario_asignado = u.id
    WHERE am.estado IN ('verificada', 'resuelta')
    ORDER BY am.fecha_resolucion DESC
    LIMIT 10
");
$stmt_alertas_auditoria->execute();
$alertas_auditoria = $stmt_alertas_auditoria->get_result()->fetch_all(MYSQLI_ASSOC);

// Actividades del sistema para auditar
$stmt_actividades_sistema = $conexion->prepare("
    SELECT a.*, u.nombre, u.rol
    FROM actividades a
    JOIN usuarios u ON a.usuario_id = u.id
    WHERE a.fecha_actividad >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ORDER BY a.fecha_actividad DESC
    LIMIT 20
");
$stmt_actividades_sistema->execute();
$actividades_sistema = $stmt_actividades_sistema->get_result()->fetch_all(MYSQLI_ASSOC);

// Estadísticas por tipo de alerta
$stmt_tipos_alerta = $conexion->prepare("
    SELECT 
        tipo_alerta,
        COUNT(*) as cantidad,
        AVG(CASE WHEN fecha_resolucion IS NOT NULL 
            THEN TIMESTAMPDIFF(HOUR, fecha_deteccion, fecha_resolucion) 
            ELSE NULL END) as tiempo_promedio_resolucion
    FROM alertas_mineria
    GROUP BY tipo_alerta
");
$stmt_tipos_alerta->execute();
$tipos_alerta = $stmt_tipos_alerta->get_result()->fetch_all(MYSQLI_ASSOC);

cerrarDB($conexion);

include 'includes/header.php';
?>

<main class="contenido-principal">
    <!-- Header del Dashboard -->
    <section class="dashboard-header">
        <div class="contenedor">
            <h1>Panel de Auditoría</h1>
            <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?> - Supervisión y control de calidad del sistema</p>
        </div>
    </section>

    <div class="contenedor">
        <!-- Estadísticas Generales -->
        <div class="dashboard-grid">
            <div class="tarjeta">
                <h3>📊 Estadísticas Generales</h3>
                <p><strong>Total de alertas:</strong> <?php echo $stats_generales['total_alertas']; ?></p>
                <p><strong>Alertas resueltas:</strong> <?php echo $stats_generales['alertas_resueltas']; ?></p>
                <p><strong>Alertas verificadas:</strong> <?php echo $stats_generales['alertas_verificadas']; ?></p>
                <p><strong>Falsas alarmas:</strong> <?php echo $stats_generales['alertas_falsas']; ?></p>
                <p><strong>Nivel crítico:</strong> <?php echo $stats_generales['alertas_criticas']; ?></p>
            </div>

            <div class="tarjeta">
                <h3>📈 Indicadores de Rendimiento</h3>
                <?php 
                $total = $stats_generales['total_alertas'];
                $resueltas = $stats_generales['alertas_resueltas'];
                $efectividad = $total > 0 ? round(($resueltas / $total) * 100, 1) : 0;
                $falsas = $stats_generales['alertas_falsas'];
                $precision = $total > 0 ? round((($total - $falsas) / $total) * 100, 1) : 0;
                ?>
                <p><strong>Efectividad general:</strong> <?php echo $efectividad; ?>%</p>
                <p><strong>Precisión del sistema:</strong> <?php echo $precision; ?>%</p>
                <p><strong>Alertas pendientes:</strong> <?php echo $total - $resueltas - $stats_generales['alertas_verificadas'] - $falsas; ?></p>
                <p><strong>Tiempo promedio resolución:</strong> 24 horas</p>
            </div>

            <div class="tarjeta">
                <h3>⚡ Acciones de Auditoría</h3>
                <p><a href="#empleados" class="boton">Revisar Empleados</a></p>
                <p><a href="#alertas-auditoria" class="boton">Auditar Alertas</a></p>
                <p><a href="#actividades" class="boton">Actividades del Sistema</a></p>
                <p><a href="#" onclick="alert('Función en desarrollo')" class="boton">Generar Reporte</a></p>
            </div>

            <div class="tarjeta">
                <h3>🔍 Estado de Auditoría</h3>
                <p><strong>Casos por revisar:</strong> <?php echo count($alertas_auditoria); ?></p>
                <p><strong>Período actual:</strong> <?php echo date('d/m/Y'); ?></p>
                <p><strong>Próxima auditoría:</strong> Semanal</p>
                <p><strong>Estado del sistema:</strong> <span style="color: green;">Normal</span></p>
            </div>
        </div>

        <!-- Rendimiento por Empleado -->
        <div id="empleados" class="tarjeta mb-2">
            <h3>👥 Rendimiento por Empleado</h3>
            <p>Análisis del desempeño individual de los empleados</p>
            
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Empleado</th>
                        <th>Email</th>
                        <th>Alertas Asignadas</th>
                        <th>Alertas Resueltas</th>
                        <th>Efectividad (%)</th>
                        <th>Tiempo Promedio (horas)</th>
                        <th>Calificación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats_empleados as $empleado): ?>
                    <?php 
                        $efectividad_empleado = $empleado['alertas_asignadas'] > 0 ? 
                            round(($empleado['alertas_resueltas'] / $empleado['alertas_asignadas']) * 100, 1) : 0;
                        $tiempo_promedio = $empleado['tiempo_promedio_resolucion'] ? 
                            round($empleado['tiempo_promedio_resolucion'], 1) : 'N/A';
                        
                        // Calificación basada en efectividad
                        if ($efectividad_empleado >= 90) $calificacion = '🌟 Excelente';
                        elseif ($efectividad_empleado >= 75) $calificacion = '✅ Bueno';
                        elseif ($efectividad_empleado >= 60) $calificacion = '⚠️ Regular';
                        else $calificacion = '❌ Necesita Mejora';
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($empleado['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($empleado['email']); ?></td>
                        <td><?php echo $empleado['alertas_asignadas']; ?></td>
                        <td><?php echo $empleado['alertas_resueltas']; ?></td>
                        <td><?php echo $efectividad_empleado; ?>%</td>
                        <td><?php echo $tiempo_promedio; ?></td>
                        <td><?php echo $calificacion; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Alertas para Auditoría -->
        <div id="alertas-auditoria" class="tarjeta mb-2">
            <h3>🔍 Alertas para Auditoría</h3>
            <p>Casos resueltos y verificados que requieren revisión de auditoría</p>
            
            <table class="tabla">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ubicación</th>
                        <th>Tipo</th>
                        <th>Nivel Riesgo</th>
                        <th>Estado</th>
                        <th>Empleado</th>
                        <th>Fecha Resolución</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($alertas_auditoria as $alerta): ?>
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
                        <td><?php echo htmlspecialchars($alerta['empleado_nombre'] ?? 'N/A'); ?></td>
                        <td>
                            <?php if ($alerta['fecha_resolucion']): ?>
                                <?php echo date('d/m/Y H:i', strtotime($alerta['fecha_resolucion'])); ?>
                            <?php else: ?>
                                <em>Pendiente</em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button onclick="auditarAlerta(<?php echo $alerta['id']; ?>)" class="boton" style="font-size: 0.8rem;">Auditar</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Análisis por Tipo de Alerta -->
        <div class="tarjeta mb-2">
            <h3>📊 Análisis por Tipo de Alerta</h3>
            <p>Distribución y rendimiento por categoría de alertas</p>
            
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Tipo de Alerta</th>
                        <th>Cantidad Total</th>
                        <th>Tiempo Promedio Resolución (horas)</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tipos_alerta as $tipo): ?>
                    <tr>
                        <td><?php echo ucfirst($tipo['tipo_alerta']); ?></td>
                        <td><?php echo $tipo['cantidad']; ?></td>
                        <td>
                            <?php if ($tipo['tiempo_promedio_resolucion']): ?>
                                <?php echo round($tipo['tiempo_promedio_resolucion'], 1); ?> horas
                            <?php else: ?>
                                <em>N/A</em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $tiempo = $tipo['tiempo_promedio_resolucion'];
                            if ($tiempo && $tiempo > 48) echo '⚠️ Tiempo alto';
                            elseif ($tiempo && $tiempo < 12) echo '✅ Tiempo óptimo';
                            else echo '📊 Tiempo normal';
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Actividades del Sistema -->
        <div id="actividades" class="tarjeta mb-2">
            <h3>📋 Actividades Recientes del Sistema</h3>
            <p>Registro de actividades de los últimos 7 días para auditoría</p>
            
            <table class="tabla">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Acción</th>
                        <th>Descripción</th>
                        <th>Fecha y Hora</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($actividades_sistema as $actividad): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($actividad['nombre']); ?></td>
                        <td><?php echo ucfirst($actividad['rol']); ?></td>
                        <td><?php echo htmlspecialchars($actividad['accion']); ?></td>
                        <td><?php echo htmlspecialchars($actividad['descripcion']); ?></td>
                        <td><?php echo date('d/m/Y H:i:s', strtotime($actividad['fecha_actividad'])); ?></td>
                        <td>
                            <?php
                            // Clasificar actividades
                            $acciones_criticas = ['login', 'logout', 'usuario_creado', 'alerta_asignada'];
                            if (in_array($actividad['accion'], $acciones_criticas)) {
                                echo '<span style="color: orange;">🔍 Revisar</span>';
                            } else {
                                echo '<span style="color: green;">✅ Normal</span>';
                            }
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Herramientas de Auditoría -->
        <div class="dashboard-grid">
            <div class="tarjeta">
                <h3>🛠️ Herramientas de Auditoría</h3>
                <ul style="text-align: left;">
                    <li><a href="#" onclick="alert('Función en desarrollo')">Generar reporte completo</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Exportar estadísticas</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Análisis de tendencias</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Configurar alertas de auditoría</a></li>
                </ul>
            </div>

            <div class="tarjeta">
                <h3>📈 Reportes Especializados</h3>
                <ul style="text-align: left;">
                    <li><a href="#" onclick="alert('Función en desarrollo')">Reporte mensual de auditoría</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Análisis de rendimiento</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Reporte de anomalías</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Dashboard ejecutivo</a></li>
                </ul>
            </div>

            <div class="tarjeta">
                <h3>⚙️ Configuración de Auditoría</h3>
                <ul style="text-align: left;">
                    <li><a href="#" onclick="alert('Función en desarrollo')">Parámetros de evaluación</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Umbrales de rendimiento</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Notificaciones automáticas</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Programar auditorías</a></li>
                </ul>
            </div>

            <div class="tarjeta">
                <h3>📞 Contactos de Auditoría</h3>
                <p><strong>Coordinación General:</strong><br>+57 (1) 123-4567</p>
                <p><strong>Oficina de Control Interno:</strong><br>control@figgerenergy.gov.co</p>
                <p><strong>Soporte Técnico:</strong><br>soporte@figgerenergy.gov.co</p>
                <p><strong>Emergencias:</strong><br>+57 (1) 987-6543</p>
            </div>
        </div>
    </div>
</main>

<script>
// Funciones específicas para el dashboard de auditor
function auditarAlerta(id) {
    if (confirm('¿Desea iniciar la auditoría de esta alerta?')) {
        alert('Función en desarrollo: Auditar alerta ID ' + id);
        // Aquí se implementaría la función para auditar la alerta
        // Se abriría un modal o se redirigiría a una página de auditoría detallada
    }
}

function generarReporte(tipo) {
    alert('Función en desarrollo: Generar reporte de tipo ' + tipo);
    // Aquí se implementaría la función para generar reportes
}

function exportarDatos(formato) {
    alert('Función en desarrollo: Exportar datos en formato ' + formato);
    // Aquí se implementaría la función para exportar datos
}
</script>

<?php include 'includes/footer.php'; ?>
