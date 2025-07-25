<!-- Dashboard Empleado -->
<div class="dashboard-header">
    <div class="contenedor">
        <div class="header-content">
            <div class="header-info">
                <h1>Panel de Trabajo</h1>
                <p>Bienvenido, <?php echo htmlspecialchars($user['nombre']); ?></p>
                <span class="user-role">Empleado de Campo</span>
            </div>
            <div class="header-actions">
                <button class="boton boton-secundario" onclick="refreshDashboard()">
                    <i class="fas fa-sync-alt"></i>
                    Actualizar
                </button>
                <button class="boton boton-primario" onclick="reportNewAlert()">
                    <i class="fas fa-exclamation-triangle"></i>
                    Reportar Alerta
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Stats Overview -->
<section class="dashboard-stats">
    <div class="contenedor">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon empleado">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="stat-info">
                    <h3 id="myTasks"><?php echo $stats['my_tasks']; ?></h3>
                    <p>Mis Tareas</p>
                    <small class="stat-change neutral"><?php echo $stats['pending_tasks']; ?> pendientes</small>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="stat-info">
                    <h3 id="assignedAlerts"><?php echo $stats['assigned_alerts']; ?></h3>
                    <p>Alertas Asignadas</p>
                    <small class="stat-change negative"><?php echo $stats['new_alerts_today']; ?> nuevas hoy</small>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3 id="completedTasks"><?php echo $stats['completed_this_month']; ?></h3>
                    <p>Completadas (mes)</p>
                    <small class="stat-change positive">+<?php echo $stats['completed_this_week']; ?> esta semana</small>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3 id="avgResponseTime"><?php echo $stats['avg_response_time']; ?>h</h3>
                    <p>Tiempo Promedio</p>
                    <small class="stat-change positive">-0.5h vs mes anterior</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Dashboard Content -->
<section class="dashboard-content">
    <div class="contenedor">
        <div class="dashboard-grid">
            <!-- My Tasks -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-clipboard-list"></i> Mis Tareas</h3>
                    <div class="card-actions">
                        <select class="task-filter" onchange="filterTasks(this.value)">
                            <option value="all">Todas</option>
                            <option value="pending">Pendientes</option>
                            <option value="in_progress">En Progreso</option>
                            <option value="completed">Completadas</option>
                        </select>
                    </div>
                </div>
                <div class="card-content">
                    <div class="task-list" id="taskList">
                        <?php foreach ($myTasks as $task): ?>
                        <div class="task-item <?php echo $task['estado']; ?>">
                            <div class="task-priority <?php echo $task['prioridad']; ?>"></div>
                            <div class="task-info">
                                <h4><?php echo htmlspecialchars($task['titulo']); ?></h4>
                                <p><?php echo htmlspecialchars($task['descripcion']); ?></p>
                                <div class="task-meta">
                                    <span class="task-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?php echo htmlspecialchars($task['ubicacion']); ?>
                                    </span>
                                    <span class="task-deadline">
                                        <i class="fas fa-clock"></i>
                                        <?php echo date('d/m/Y H:i', strtotime($task['fecha_limite'])); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="task-actions">
                                <button class="btn-icon" onclick="viewTask(<?php echo $task['id']; ?>)" title="Ver Detalle">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <?php if ($task['estado'] === 'pendiente'): ?>
                                <button class="btn-icon" onclick="startTask(<?php echo $task['id']; ?>)" title="Iniciar Tarea">
                                    <i class="fas fa-play"></i>
                                </button>
                                <?php endif; ?>
                                <?php if ($task['estado'] === 'en_progreso'): ?>
                                <button class="btn-icon" onclick="completeTask(<?php echo $task['id']; ?>)" title="Completar">
                                    <i class="fas fa-check"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="card-footer">
                        <a href="<?php echo getBaseUrl(); ?>empleado/tasks" class="action-link">
                            <i class="fas fa-list"></i>
                            Ver Todas las Tareas
                        </a>
                    </div>
                </div>
            </div>

            <!-- Assigned Alerts -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-bell"></i> Alertas Asignadas</h3>
                    <div class="card-actions">
                        <button class="btn-icon" onclick="refreshAlerts()" title="Actualizar Alertas">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-content">
                    <div class="alert-list" id="alertList">
                        <?php foreach ($assignedAlerts as $alert): ?>
                        <div class="alert-item <?php echo $alert['prioridad']; ?>">
                            <div class="alert-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="alert-info">
                                <h4><?php echo htmlspecialchars($alert['titulo']); ?></h4>
                                <p><?php echo htmlspecialchars($alert['descripcion']); ?></p>
                                <div class="alert-meta">
                                    <span class="alert-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?php echo htmlspecialchars($alert['ubicacion']); ?>
                                    </span>
                                    <span class="alert-time">
                                        <i class="fas fa-clock"></i>
                                        <?php echo timeAgo($alert['fecha_creacion']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="alert-actions">
                                <button class="btn-icon" onclick="viewAlert(<?php echo $alert['id']; ?>)" title="Ver Detalle">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn-icon" onclick="respondToAlert(<?php echo $alert['id']; ?>)" title="Responder">
                                    <i class="fas fa-reply"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="card-footer">
                        <a href="<?php echo getBaseUrl(); ?>empleado/alerts" class="action-link">
                            <i class="fas fa-list"></i>
                            Ver Todas las Alertas
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-bolt"></i> Acciones Rápidas</h3>
                </div>
                <div class="card-content">
                    <div class="quick-actions-grid">
                        <button class="quick-action-btn" onclick="reportNewAlert()">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Reportar Alerta</span>
                        </button>
                        <button class="quick-action-btn" onclick="uploadFieldReport()">
                            <i class="fas fa-upload"></i>
                            <span>Subir Reporte</span>
                        </button>
                        <button class="quick-action-btn" onclick="viewMap()">
                            <i class="fas fa-map"></i>
                            <span>Ver Mapa</span>
                        </button>
                        <button class="quick-action-btn" onclick="contactSupport()">
                            <i class="fas fa-headset"></i>
                            <span>Soporte</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="dashboard-card full-width">
                <div class="card-header">
                    <h3><i class="fas fa-history"></i> Mi Actividad Reciente</h3>
                    <div class="card-actions">
                        <button class="btn-icon" onclick="loadMoreActivity()" title="Cargar Más">
                            <i class="fas fa-refresh"></i>
                        </button>
                    </div>
                </div>
                <div class="card-content">
                    <div class="activity-list" id="activityList">
                        <?php foreach ($myActivity as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon <?php echo $activity['tipo']; ?>">
                                <i class="fas <?php echo getActivityIcon($activity['tipo']); ?>"></i>
                            </div>
                            <div class="activity-info">
                                <p><?php echo htmlspecialchars($activity['descripcion']); ?></p>
                                <small class="activity-time"><?php echo timeAgo($activity['fecha_creacion']); ?></small>
                            </div>
                            <div class="activity-status">
                                <span class="status-badge <?php echo $activity['estado']; ?>">
                                    <?php echo ucfirst($activity['estado']); ?>
                                </span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Performance Stats -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-line"></i> Mi Rendimiento</h3>
                    <div class="card-actions">
                        <select class="period-filter" onchange="updatePerformanceStats(this.value)">
                            <option value="week">Esta Semana</option>
                            <option value="month" selected>Este Mes</option>
                            <option value="quarter">Este Trimestre</option>
                        </select>
                    </div>
                </div>
                <div class="card-content">
                    <div class="performance-metrics">
                        <div class="metric">
                            <label>Tareas Completadas</label>
                            <div class="metric-value">
                                <span class="value"><?php echo $performance['completed_tasks']; ?></span>
                                <span class="change positive">+<?php echo $performance['task_improvement']; ?>%</span>
                            </div>
                        </div>
                        <div class="metric">
                            <label>Tiempo Promedio</label>
                            <div class="metric-value">
                                <span class="value"><?php echo $performance['avg_time']; ?>h</span>
                                <span class="change positive">-<?php echo $performance['time_improvement']; ?>%</span>
                            </div>
                        </div>
                        <div class="metric">
                            <label>Calificación</label>
                            <div class="metric-value">
                                <span class="value"><?php echo $performance['rating']; ?>/5</span>
                                <div class="stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $performance['rating'] ? 'filled' : ''; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="<?php echo getBaseUrl(); ?>empleado/performance" class="action-link">
                            <i class="fas fa-chart-bar"></i>
                            Ver Reporte Completo
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tools & Resources -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-tools"></i> Herramientas</h3>
                </div>
                <div class="card-content">
                    <div class="tools-list">
                        <a href="<?php echo getBaseUrl(); ?>empleado/checklist" class="tool-item">
                            <i class="fas fa-check-square"></i>
                            <span>Lista de Verificación</span>
                        </a>
                        <a href="<?php echo getBaseUrl(); ?>empleado/gps" class="tool-item">
                            <i class="fas fa-satellite"></i>
                            <span>GPS & Navegación</span>
                        </a>
                        <a href="<?php echo getBaseUrl(); ?>empleado/forms" class="tool-item">
                            <i class="fas fa-clipboard"></i>
                            <span>Formularios</span>
                        </a>
                        <a href="<?php echo getBaseUrl(); ?>empleado/photos" class="tool-item">
                            <i class="fas fa-camera"></i>
                            <span>Galería de Fotos</span>
                        </a>
                        <a href="<?php echo getBaseUrl(); ?>empleado/manual" class="tool-item">
                            <i class="fas fa-book"></i>
                            <span>Manual de Campo</span>
                        </a>
                        <a href="<?php echo getBaseUrl(); ?>empleado/emergency" class="tool-item">
                            <i class="fas fa-phone-alt"></i>
                            <span>Contactos de Emergencia</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function refreshDashboard() {
    location.reload();
}

function reportNewAlert() {
    window.location.href = '<?php echo getBaseUrl(); ?>empleado/alerts/new';
}

function filterTasks(status) {
    const tasks = document.querySelectorAll('.task-item');
    tasks.forEach(task => {
        if (status === 'all' || task.classList.contains(status)) {
            task.style.display = 'flex';
        } else {
            task.style.display = 'none';
        }
    });
}

function viewTask(id) {
    window.location.href = '<?php echo getBaseUrl(); ?>empleado/tasks/' + id;
}

function startTask(id) {
    if (confirm('¿Está seguro de que desea iniciar esta tarea?')) {
        fetch('<?php echo getBaseUrl(); ?>api/tasks/' + id + '/start', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo $csrfToken; ?>'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error al iniciar la tarea: ' + data.message);
            }
        });
    }
}

function completeTask(id) {
    window.location.href = '<?php echo getBaseUrl(); ?>empleado/tasks/' + id + '/complete';
}

function refreshAlerts() {
    location.reload();
}

function viewAlert(id) {
    window.location.href = '<?php echo getBaseUrl(); ?>empleado/alerts/' + id;
}

function respondToAlert(id) {
    window.location.href = '<?php echo getBaseUrl(); ?>empleado/alerts/' + id + '/respond';
}

function uploadFieldReport() {
    window.location.href = '<?php echo getBaseUrl(); ?>empleado/reports/upload';
}

function viewMap() {
    window.open('<?php echo getBaseUrl(); ?>empleado/map', '_blank');
}

function contactSupport() {
    window.location.href = '<?php echo getBaseUrl(); ?>contact';
}

function loadMoreActivity() {
    // Implementation for loading more activity
    console.log('Loading more activity...');
}

function updatePerformanceStats(period) {
    // Implementation for updating performance statistics
    console.log('Updating performance stats for period:', period);
}

// Auto-refresh dashboard every 10 minutes
setInterval(function() {
    refreshDashboard();
}, 600000);

// Notification for new alerts
function checkForNewAlerts() {
    fetch('<?php echo getBaseUrl(); ?>api/alerts/check-new', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '<?php echo $csrfToken; ?>'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.hasNew) {
            showNotification('Nueva alerta asignada', 'alert');
            document.getElementById('assignedAlerts').textContent = data.totalAlerts;
        }
    });
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = 'notification ' + type;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-bell"></i>
            <span>${message}</span>
        </div>
        <button onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

// Check for new alerts every 2 minutes
setInterval(checkForNewAlerts, 120000);
</script>
