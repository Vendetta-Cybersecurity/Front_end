<?php
/**
 * Dashboard Auditor - Figger Energy SAS
 * Panel de control refactorizado para auditores
 */

// Cargar dependencias
require_once '../../config/database.php';
require_once '../../lib/functions.php';

// Verificar autenticación y rol
requiereAuth('auditor', '../../views/auth/login.php');

$titulo_pagina = "Dashboard Auditor";

$conexion = conectarDB();

// Estadísticas generales para auditoría
$stmt_stats = $conexion->prepare("
    SELECT 
        COUNT(*) as total_alertas,
        SUM(CASE WHEN estado = 'activa' THEN 1 ELSE 0 END) as alertas_activas,
        SUM(CASE WHEN estado = 'resuelta' THEN 1 ELSE 0 END) as alertas_resueltas,
        SUM(CASE WHEN estado = 'en_proceso' THEN 1 ELSE 0 END) as alertas_proceso,
        SUM(CASE WHEN nivel_riesgo = 'alto' THEN 1 ELSE 0 END) as alto_riesgo
    FROM alertas_mineria
");
$stmt_stats->execute();
$stats_alertas = $stmt_stats->get_result()->fetch_assoc();

// Estadísticas de usuarios
$stmt_usuarios = $conexion->prepare("
    SELECT 
        COUNT(*) as total_usuarios,
        SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as usuarios_activos,
        SUM(CASE WHEN rol = 'empleado' THEN 1 ELSE 0 END) as empleados,
        SUM(CASE WHEN rol = 'admin' THEN 1 ELSE 0 END) as administradores
    FROM usuarios
");
$stmt_usuarios->execute();
$stats_usuarios = $stmt_usuarios->get_result()->fetch_assoc();

// Actividades recientes para auditoría
$stmt_actividades = $conexion->prepare("
    SELECT a.*, u.nombre as usuario_nombre, u.rol as usuario_rol
    FROM actividades a
    JOIN usuarios u ON a.usuario_id = u.id
    ORDER BY a.fecha_actividad DESC
    LIMIT 15
");
$stmt_actividades->execute();
$actividades_recientes = $stmt_actividades->get_result()->fetch_all(MYSQLI_ASSOC);

// Alertas críticas para revisión
$stmt_criticas = $conexion->prepare("
    SELECT am.*, u.nombre as usuario_asignado_nombre
    FROM alertas_mineria am
    LEFT JOIN usuarios u ON am.usuario_asignado = u.id
    WHERE am.nivel_riesgo = 'alto' OR am.estado = 'activa'
    ORDER BY 
        CASE am.nivel_riesgo 
            WHEN 'alto' THEN 1 
            WHEN 'medio' THEN 2 
            ELSE 3 
        END,
        am.fecha_deteccion DESC
    LIMIT 10
");
$stmt_criticas->execute();
$alertas_criticas = $stmt_criticas->get_result()->fetch_all(MYSQLI_ASSOC);

// Rendimiento por empleado
$stmt_rendimiento = $conexion->prepare("
    SELECT 
        u.id,
        u.nombre,
        COUNT(am.id) as alertas_asignadas,
        SUM(CASE WHEN am.estado = 'resuelta' THEN 1 ELSE 0 END) as alertas_resueltas,
        AVG(CASE 
            WHEN am.estado = 'resuelta' AND am.fecha_resolucion IS NOT NULL 
            THEN DATEDIFF(am.fecha_resolucion, am.fecha_deteccion) 
            ELSE NULL 
        END) as tiempo_promedio_resolucion
    FROM usuarios u
    LEFT JOIN alertas_mineria am ON u.id = am.usuario_asignado
    WHERE u.rol = 'empleado' AND u.activo = 1
    GROUP BY u.id, u.nombre
    ORDER BY alertas_resueltas DESC
");
$stmt_rendimiento->execute();
$rendimiento_empleados = $stmt_rendimiento->get_result()->fetch_all(MYSQLI_ASSOC);

cerrarDB($conexion);

include '../../includes/header.php';
?>

<main class="contenido-principal">
    <!-- Header del Dashboard -->
    <section class="dashboard-header">
        <div class="contenedor">
            <h1>Panel de Auditoría</h1>
            <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?> - Supervisión y control de operaciones</p>
        </div>
    </section>

    <div class="contenedor">
        <!-- Estadísticas de Auditoría -->
        <div class="dashboard-grid">
            <div class="tarjeta">
                <h3>🚨 Estado de Alertas</h3>
                <p><strong>Total:</strong> <?php echo $stats_alertas['total_alertas']; ?></p>
                <p><strong>Activas:</strong> <?php echo $stats_alertas['alertas_activas']; ?></p>
                <p><strong>En proceso:</strong> <?php echo $stats_alertas['alertas_proceso']; ?></p>
                <p><strong>Resueltas:</strong> <?php echo $stats_alertas['alertas_resueltas']; ?></p>
                <p><strong>Alto riesgo:</strong> <?php echo $stats_alertas['alto_riesgo']; ?></p>
            </div>

            <div class="tarjeta">
                <h3>👥 Estado de Usuarios</h3>
                <p><strong>Total:</strong> <?php echo $stats_usuarios['total_usuarios']; ?></p>
                <p><strong>Activos:</strong> <?php echo $stats_usuarios['usuarios_activos']; ?></p>
                <p><strong>Empleados:</strong> <?php echo $stats_usuarios['empleados']; ?></p>
                <p><strong>Administradores:</strong> <?php echo $stats_usuarios['administradores']; ?></p>
            </div>

            <div class="tarjeta">
                <h3>📊 Métricas de Rendimiento</h3>
                <?php 
                $eficiencia = $stats_alertas['total_alertas'] > 0 ? 
                    round(($stats_alertas['alertas_resueltas'] / $stats_alertas['total_alertas']) * 100, 1) : 0;
                ?>
                <p><strong>Eficiencia general:</strong> <?php echo $eficiencia; ?>%</p>
                <p><strong>Alertas pendientes:</strong> <?php echo $stats_alertas['alertas_activas'] + $stats_alertas['alertas_proceso']; ?></p>
                <p><strong>Actividad hoy:</strong> 
                   <?php echo count(array_filter($actividades_recientes, function($a) { 
                       return date('Y-m-d', strtotime($a['fecha_actividad'])) == date('Y-m-d'); 
                   })); ?>
                </p>
            </div>

            <div class="tarjeta">
                <h3>🔍 Acciones de Auditoría</h3>
                <p><a href="#alertas-criticas" class="boton-enlace">Revisar Alertas Críticas</a></p>
                <p><a href="#rendimiento" class="boton-enlace">Analizar Rendimiento</a></p>
                <p><a href="#actividades" class="boton-enlace">Ver Actividades</a></p>
                <p><a href="#" onclick="alert('Función en desarrollo')" class="boton-enlace">Generar Reporte</a></p>
            </div>
        </div>

        <!-- Alertas Críticas -->
        <div class="seccion-dashboard" id="alertas-criticas">
            <h2>⚠️ Alertas Críticas para Revisión</h2>
            <?php if (!empty($alertas_criticas)): ?>
                <div class="tabla-contenedor">
                    <table class="tabla-datos">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ubicación</th>
                                <th>Tipo</th>
                                <th>Riesgo</th>
                                <th>Estado</th>
                                <th>Asignado a</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alertas_criticas as $alerta): ?>
                                <tr class="<?php echo $alerta['nivel_riesgo'] == 'alto' ? 'fila-critica' : ''; ?>">
                                    <td><?php echo $alerta['id']; ?></td>
                                    <td><?php echo htmlspecialchars($alerta['ubicacion']); ?></td>
                                    <td><?php echo htmlspecialchars($alerta['tipo_actividad']); ?></td>
                                    <td><span class="badge badge-riesgo-<?php echo $alerta['nivel_riesgo']; ?>"><?php echo ucfirst($alerta['nivel_riesgo']); ?></span></td>
                                    <td><span class="badge badge-<?php echo $alerta['estado']; ?>"><?php echo ucfirst($alerta['estado']); ?></span></td>
                                    <td><?php echo $alerta['usuario_asignado_nombre'] ? htmlspecialchars($alerta['usuario_asignado_nombre']) : 'No asignado'; ?></td>
                                    <td><?php echo formatearFecha($alerta['fecha_deteccion']); ?></td>
                                    <td>
                                        <button onclick="auditarAlerta(<?php echo $alerta['id']; ?>)" class="boton-pequeno">Auditar</button>
                                        <?php if (!$alerta['usuario_asignado']): ?>
                                            <button onclick="reasignarAlerta(<?php echo $alerta['id']; ?>)" class="boton-pequeno boton-advertencia">Reasignar</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="mensaje mensaje-exito">
                    <p>No hay alertas críticas pendientes de revisión.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Rendimiento por Empleado -->
        <div class="seccion-dashboard" id="rendimiento">
            <h2>📈 Rendimiento de Empleados</h2>
            <div class="tabla-contenedor">
                <table class="tabla-datos">
                    <thead>
                        <tr>
                            <th>Empleado</th>
                            <th>Alertas Asignadas</th>
                            <th>Alertas Resueltas</th>
                            <th>Eficiencia</th>
                            <th>Tiempo Promedio (días)</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rendimiento_empleados as $empleado): ?>
                            <?php 
                            $eficiencia_empleado = $empleado['alertas_asignadas'] > 0 ? 
                                round(($empleado['alertas_resueltas'] / $empleado['alertas_asignadas']) * 100, 1) : 0;
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($empleado['nombre']); ?></td>
                                <td><?php echo $empleado['alertas_asignadas']; ?></td>
                                <td><?php echo $empleado['alertas_resueltas']; ?></td>
                                <td>
                                    <span class="badge <?php echo $eficiencia_empleado >= 80 ? 'badge-exito' : ($eficiencia_empleado >= 60 ? 'badge-advertencia' : 'badge-error'); ?>">
                                        <?php echo $eficiencia_empleado; ?>%
                                    </span>
                                </td>
                                <td><?php echo $empleado['tiempo_promedio_resolucion'] ? round($empleado['tiempo_promedio_resolucion'], 1) : 'N/A'; ?></td>
                                <td>
                                    <?php if ($eficiencia_empleado >= 80): ?>
                                        <span class="badge badge-exito">Excelente</span>
                                    <?php elseif ($eficiencia_empleado >= 60): ?>
                                        <span class="badge badge-advertencia">Bueno</span>
                                    <?php else: ?>
                                        <span class="badge badge-error">Necesita mejora</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Registro de Actividades -->
        <div class="seccion-dashboard" id="actividades">
            <h2>📋 Registro de Actividades del Sistema</h2>
            <div class="tabla-contenedor">
                <table class="tabla-datos">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Acción</th>
                            <th>Descripción</th>
                            <th>Fecha</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($actividades_recientes as $actividad): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($actividad['usuario_nombre']); ?></td>
                                <td><span class="badge badge-<?php echo $actividad['usuario_rol']; ?>"><?php echo ucfirst($actividad['usuario_rol']); ?></span></td>
                                <td><span class="badge badge-actividad"><?php echo htmlspecialchars($actividad['accion']); ?></span></td>
                                <td><?php echo htmlspecialchars($actividad['descripcion']); ?></td>
                                <td><?php echo formatearFecha($actividad['fecha_actividad']); ?></td>
                                <td><small><?php echo htmlspecialchars($actividad['ip_address']); ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Herramientas de Auditoría -->
        <div class="dashboard-grid">
            <div class="tarjeta">
                <h3>🔍 Herramientas de Auditoría</h3>
                <ul style="text-align: left;">
                    <li><a href="#" onclick="alert('Función en desarrollo')">Generar reporte mensual</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Auditoría de usuarios</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Análisis de patrones</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Exportar datos</a></li>
                </ul>
            </div>

            <div class="tarjeta">
                <h3>📊 Análisis Avanzado</h3>
                <ul style="text-align: left;">
                    <li><a href="#" onclick="alert('Función en desarrollo')">Tendencias temporales</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Análisis geográfico</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Correlaciones</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Predicciones</a></li>
                </ul>
            </div>

            <div class="tarjeta">
                <h3>⚙️ Configuración</h3>
                <ul style="text-align: left;">
                    <li><a href="#" onclick="alert('Función en desarrollo')">Parámetros de auditoría</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Alertas automáticas</a></li>
                    <li><a href="#" onclick="alert('Función en desarrollo')">Umbrales de rendimiento</a></li>
                </ul>
            </div>
        </div>
    </div>
</main>

<!-- JavaScript específico del auditor -->
<script>
function auditarAlerta(id) {
    alert('Iniciar auditoría de alerta #' + id + ' - Función en desarrollo');
}

function reasignarAlerta(id) {
    if (confirm('¿Desea reasignar la alerta #' + id + ' a otro empleado?')) {
        alert('Reasignar alerta #' + id + ' - Función en desarrollo');
    }
}
</script>

<?php include '../../includes/footer.php'; ?>
