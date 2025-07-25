<!-- Dashboard Admin -->
<div class="dashboard-header">
    <div class="contenedor">
        <div class="header-content">
            <div class="header-info">
                <h1>Panel de Administración</h1>
                <p>Bienvenido, <?php echo htmlspecialchars($user['nombre']); ?></p>
                <span class="user-role">Administrador del Sistema</span>
            </div>
            <div class="header-actions">
                <button class="boton boton-secundario" onclick="refreshDashboard()">
                    <i class="fas fa-sync-alt"></i>
                    Actualizar
                </button>
                <button class="boton boton-primario" onclick="showQuickActions()">
                    <i class="fas fa-plus"></i>
                    Acciones Rápidas
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
                <div class="stat-icon admin">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3 id="totalUsers"><?php echo $stats['total_users']; ?></h3>
                    <p>Usuarios Totales</p>
                    <small class="stat-change positive">+<?php echo $stats['new_users_month']; ?> este mes</small>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-info">
                    <h3 id="activeAlerts"><?php echo $stats['active_alerts']; ?></h3>
                    <p>Alertas Activas</p>
                    <small class="stat-change negative">+<?php echo $stats['new_alerts_today']; ?> hoy</small>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-info">
                    <h3 id="systemHealth">99.2%</h3>
                    <p>Salud del Sistema</p>
                    <small class="stat-change positive">+0.1% esta semana</small>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-server"></i>
                </div>
                <div class="stat-info">
                    <h3 id="storageUsed">68%</h3>
                    <p>Almacenamiento Usado</p>
                    <small class="stat-change neutral">2.1 TB disponibles</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Dashboard Content -->
<section class="dashboard-content">
    <div class="contenedor">
        <div class="dashboard-grid">
            <!-- User Management -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-users-cog"></i> Gestión de Usuarios</h3>
                    <div class="card-actions">
                        <button class="btn-icon" onclick="addNewUser()" title="Agregar Usuario">
                            <i class="fas fa-user-plus"></i>
                        </button>
                        <button class="btn-icon" onclick="exportUsers()" title="Exportar Lista">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </div>
                <div class="card-content">
                    <div class="user-summary">
                        <div class="user-type">
                            <span class="badge admin">Administradores</span>
                            <strong><?php echo $stats['admin_count']; ?></strong>
                        </div>
                        <div class="user-type">
                            <span class="badge empleado">Empleados</span>
                            <strong><?php echo $stats['empleado_count']; ?></strong>
                        </div>
                        <div class="user-type">
                            <span class="badge auditor">Auditores</span>
                            <strong><?php echo $stats['auditor_count']; ?></strong>
                        </div>
                    </div>
                    <div class="quick-actions">
                        <a href="<?php echo getBaseUrl(); ?>admin/users" class="action-link">
                            <i class="fas fa-list"></i>
                            Ver Todos los Usuarios
                        </a>
                        <a href="<?php echo getBaseUrl(); ?>admin/users/pending" class="action-link">
                            <i class="fas fa-clock"></i>
                            Solicitudes Pendientes (<?php echo $stats['pending_users']; ?>)
                        </a>
                    </div>
                </div>
            </div>

            <!-- System Monitoring -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-area"></i> Monitoreo del Sistema</h3>
                    <div class="card-actions">
                        <button class="btn-icon" onclick="refreshSystemStats()" title="Actualizar Estadísticas">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-content">
                    <div class="system-metrics">
                        <div class="metric">
                            <label>CPU</label>
                            <div class="progress-bar">
                                <div class="progress" style="width: 42%"></div>
                            </div>
                            <span>42%</span>
                        </div>
                        <div class="metric">
                            <label>Memoria</label>
                            <div class="progress-bar">
                                <div class="progress" style="width: 67%"></div>
                            </div>
                            <span>67%</span>
                        </div>
                        <div class="metric">
                            <label>Disco</label>
                            <div class="progress-bar">
                                <div class="progress" style="width: 68%"></div>
                            </div>
                            <span>68%</span>
                        </div>
                    </div>
                    <div class="quick-actions">
                        <a href="<?php echo getBaseUrl(); ?>admin/system/logs" class="action-link">
                            <i class="fas fa-file-alt"></i>
                            Ver Logs del Sistema
                        </a>
                        <a href="<?php echo getBaseUrl(); ?>admin/system/backup" class="action-link">
                            <i class="fas fa-save"></i>
                            Gestionar Respaldos
                        </a>
                    </div>
                </div>
            </div>

            <!-- Alert Management -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-bell"></i> Gestión de Alertas</h3>
                    <div class="card-actions">
                        <button class="btn-icon" onclick="createAlert()" title="Crear Alerta">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-content">
                    <div class="alert-summary">
                        <div class="alert-type">
                            <span class="alert-badge critical">Críticas</span>
                            <strong><?php echo $stats['critical_alerts']; ?></strong>
                        </div>
                        <div class="alert-type">
                            <span class="alert-badge warning">Advertencias</span>
                            <strong><?php echo $stats['warning_alerts']; ?></strong>
                        </div>
                        <div class="alert-type">
                            <span class="alert-badge info">Informativas</span>
                            <strong><?php echo $stats['info_alerts']; ?></strong>
                        </div>
                    </div>
                    <div class="quick-actions">
                        <a href="<?php echo getBaseUrl(); ?>admin/alerts" class="action-link">
                            <i class="fas fa-list"></i>
                            Ver Todas las Alertas
                        </a>
                        <a href="<?php echo getBaseUrl(); ?>admin/alerts/map" class="action-link">
                            <i class="fas fa-map"></i>
                            Mapa de Alertas
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="dashboard-card full-width">
                <div class="card-header">
                    <h3><i class="fas fa-history"></i> Actividad Reciente</h3>
                    <div class="card-actions">
                        <button class="btn-icon" onclick="loadMoreActivity()" title="Cargar Más">
                            <i class="fas fa-refresh"></i>
                        </button>
                    </div>
                </div>
                <div class="card-content">
                    <div class="activity-list" id="activityList">
                        <?php foreach ($recentActivity as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon <?php echo $activity['tipo']; ?>">
                                <i class="fas <?php echo getActivityIcon($activity['tipo']); ?>"></i>
                            </div>
                            <div class="activity-info">
                                <p><strong><?php echo htmlspecialchars($activity['usuario']); ?></strong> <?php echo htmlspecialchars($activity['descripcion']); ?></p>
                                <small class="activity-time"><?php echo timeAgo($activity['fecha_creacion']); ?></small>
                            </div>
                            <div class="activity-actions">
                                <button class="btn-icon" onclick="viewActivityDetail(<?php echo $activity['id']; ?>)" title="Ver Detalle">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Security Overview -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-shield-alt"></i> Seguridad</h3>
                    <div class="card-actions">
                        <button class="btn-icon" onclick="runSecurityScan()" title="Ejecutar Escaneo">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="card-content">
                    <div class="security-status">
                        <div class="status-item">
                            <span class="status-label">Estado ISO 27001</span>
                            <span class="status-badge compliant">Conforme</span>
                        </div>
                        <div class="status-item">
                            <span class="status-label">Último Escaneo</span>
                            <span class="status-time">Hace 2 horas</span>
                        </div>
                        <div class="status-item">
                            <span class="status-label">Vulnerabilidades</span>
                            <span class="status-badge safe">0 Críticas</span>
                        </div>
                    </div>
                    <div class="quick-actions">
                        <a href="<?php echo getBaseUrl(); ?>admin/security/logs" class="action-link">
                            <i class="fas fa-file-shield"></i>
                            Logs de Seguridad
                        </a>
                        <a href="<?php echo getBaseUrl(); ?>admin/security/policies" class="action-link">
                            <i class="fas fa-cogs"></i>
                            Políticas de Seguridad
                        </a>
                    </div>
                </div>
            </div>

            <!-- Reports -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-bar"></i> Reportes</h3>
                    <div class="card-actions">
                        <button class="btn-icon" onclick="generateReport()" title="Generar Reporte">
                            <i class="fas fa-file-pdf"></i>
                        </button>
                    </div>
                </div>
                <div class="card-content">
                    <div class="report-types">
                        <button class="report-btn" onclick="generateUserReport()">
                            <i class="fas fa-users"></i>
                            Reporte de Usuarios
                        </button>
                        <button class="report-btn" onclick="generateAlertReport()">
                            <i class="fas fa-exclamation-triangle"></i>
                            Reporte de Alertas
                        </button>
                        <button class="report-btn" onclick="generateSystemReport()">
                            <i class="fas fa-server"></i>
                            Reporte del Sistema
                        </button>
                    </div>
                    <div class="quick-actions">
                        <a href="<?php echo getBaseUrl(); ?>admin/reports" class="action-link">
                            <i class="fas fa-folder"></i>
                            Ver Todos los Reportes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Quick Actions Modal -->
<div id="quickActionsModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Acciones Rápidas</h3>
            <button type="button" class="close-modal" onclick="hideQuickActions()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-body">
            <div class="quick-actions-grid">
                <button class="quick-action-btn" onclick="addNewUser()">
                    <i class="fas fa-user-plus"></i>
                    <span>Agregar Usuario</span>
                </button>
                <button class="quick-action-btn" onclick="createAlert()">
                    <i class="fas fa-plus"></i>
                    <span>Crear Alerta</span>
                </button>
                <button class="quick-action-btn" onclick="generateReport()">
                    <i class="fas fa-file-pdf"></i>
                    <span>Generar Reporte</span>
                </button>
                <button class="quick-action-btn" onclick="runSystemBackup()">
                    <i class="fas fa-save"></i>
                    <span>Respaldo del Sistema</span>
                </button>
                <button class="quick-action-btn" onclick="viewSystemLogs()">
                    <i class="fas fa-file-alt"></i>
                    <span>Ver Logs</span>
                </button>
                <button class="quick-action-btn" onclick="managePermissions()">
                    <i class="fas fa-key"></i>
                    <span>Gestionar Permisos</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function refreshDashboard() {
    location.reload();
}

function showQuickActions() {
    document.getElementById('quickActionsModal').style.display = 'flex';
}

function hideQuickActions() {
    document.getElementById('quickActionsModal').style.display = 'none';
}

function addNewUser() {
    window.location.href = '<?php echo getBaseUrl(); ?>admin/users/new';
}

function createAlert() {
    window.location.href = '<?php echo getBaseUrl(); ?>admin/alerts/new';
}

function generateReport() {
    window.location.href = '<?php echo getBaseUrl(); ?>admin/reports/generate';
}

function refreshSystemStats() {
    // Implementation for refreshing system statistics
    console.log('Refreshing system stats...');
}

function loadMoreActivity() {
    // Implementation for loading more activity
    console.log('Loading more activity...');
}

function viewActivityDetail(id) {
    window.location.href = '<?php echo getBaseUrl(); ?>admin/activity/' + id;
}

function runSecurityScan() {
    alert('Ejecutando escaneo de seguridad...');
}

function generateUserReport() {
    window.open('<?php echo getBaseUrl(); ?>admin/reports/users', '_blank');
}

function generateAlertReport() {
    window.open('<?php echo getBaseUrl(); ?>admin/reports/alerts', '_blank');
}

function generateSystemReport() {
    window.open('<?php echo getBaseUrl(); ?>admin/reports/system', '_blank');
}

function exportUsers() {
    window.open('<?php echo getBaseUrl(); ?>admin/users/export', '_blank');
}

function runSystemBackup() {
    if (confirm('¿Está seguro de que desea ejecutar un respaldo del sistema?')) {
        alert('Respaldo iniciado. Recibirá una notificación cuando termine.');
    }
}

function viewSystemLogs() {
    window.location.href = '<?php echo getBaseUrl(); ?>admin/system/logs';
}

function managePermissions() {
    window.location.href = '<?php echo getBaseUrl(); ?>admin/permissions';
}

// Auto-refresh stats every 5 minutes
setInterval(function() {
    refreshSystemStats();
}, 300000);
</script>
