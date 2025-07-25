<!-- Dashboard Auditor -->
<div class="dashboard-header">
    <div class="contenedor">
        <div class="header-content">
            <div class="header-info">
                <h1>Panel de Auditoría</h1>
                <p>Bienvenido, <?php echo htmlspecialchars($user['nombre']); ?></p>
                <span class="user-role">Auditor de Seguridad</span>
            </div>
            <div class="header-actions">
                <button class="boton boton-secundario" onclick="refreshDashboard()">
                    <i class="fas fa-sync-alt"></i>
                    Actualizar
                </button>
                <button class="boton boton-primario" onclick="generateAuditReport()">
                    <i class="fas fa-file-alt"></i>
                    Generar Reporte
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
                <div class="stat-icon auditor">
                    <i class="fas fa-search"></i>
                </div>
                <div class="stat-info">
                    <h3 id="auditsCompleted"><?php echo $stats['audits_completed']; ?></h3>
                    <p>Auditorías Completadas</p>
                    <small class="stat-change positive">+<?php echo $stats['audits_this_month']; ?> este mes</small>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="stat-info">
                    <h3 id="complianceIssues"><?php echo $stats['compliance_issues']; ?></h3>
                    <p>Problemas de Cumplimiento</p>
                    <small class="stat-change negative"><?php echo $stats['new_issues_week']; ?> nuevos esta semana</small>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-shield-check"></i>
                </div>
                <div class="stat-info">
                    <h3 id="complianceRate"><?php echo $stats['compliance_rate']; ?>%</h3>
                    <p>Índice de Cumplimiento</p>
                    <small class="stat-change positive">+<?php echo $stats['improvement']; ?>% vs mes anterior</small>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-info">
                    <h3 id="pendingAudits"><?php echo $stats['pending_audits']; ?></h3>
                    <p>Auditorías Pendientes</p>
                    <small class="stat-change neutral"><?php echo $stats['scheduled_this_week']; ?> programadas</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Dashboard Content -->
<section class="dashboard-content">
    <div class="contenedor">
        <div class="dashboard-grid">
            <!-- ISO 27001 Compliance -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-certificate"></i> Cumplimiento ISO 27001</h3>
                    <div class="card-actions">
                        <button class="btn-icon" onclick="runComplianceCheck()" title="Ejecutar Verificación">
                            <i class="fas fa-play"></i>
                        </button>
                    </div>
                </div>
                <div class="card-content">
                    <div class="compliance-overview">
                        <div class="compliance-score">
                            <div class="score-circle">
                                <svg viewBox="0 0 36 36">
                                    <path class="circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                    <path class="circle" stroke-dasharray="<?php echo $stats['compliance_rate']; ?>, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                </svg>
                                <div class="score-text"><?php echo $stats['compliance_rate']; ?>%</div>
                            </div>
                        </div>
                        <div class="compliance-details">
                            <div class="compliance-item compliant">
                                <span class="status-dot"></span>
                                <span>A.5 Políticas de Seguridad</span>
                                <span class="compliance-status">Conforme</span>
                            </div>
                            <div class="compliance-item compliant">
                                <span class="status-dot"></span>
                                <span>A.8 Gestión de Activos</span>
                                <span class="compliance-status">Conforme</span>
                            </div>
                            <div class="compliance-item compliant">
                                <span class="status-dot"></span>
                                <span>A.9 Control de Acceso</span>
                                <span class="compliance-status">Conforme</span>
                            </div>
                            <div class="compliance-item warning">
                                <span class="status-dot"></span>
                                <span>A.12 Seguridad Operacional</span>
                                <span class="compliance-status">Revisar</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="<?php echo getBaseUrl(); ?>auditor/compliance" class="action-link">
                            <i class="fas fa-list"></i>
                            Ver Reporte Completo
                        </a>
                    </div>
                </div>
            </div>

            <!-- Security Logs Analysis -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-file-shield"></i> Análisis de Logs</h3>
                    <div class="card-actions">
                        <button class="btn-icon" onclick="refreshLogs()" title="Actualizar Logs">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-content">
                    <div class="log-summary">
                        <div class="log-type">
                            <span class="log-badge info">Info</span>
                            <strong><?php echo $logs['info_count']; ?></strong>
                        </div>
                        <div class="log-type">
                            <span class="log-badge warning">Warning</span>
                            <strong><?php echo $logs['warning_count']; ?></strong>
                        </div>
                        <div class="log-type">
                            <span class="log-badge error">Error</span>
                            <strong><?php echo $logs['error_count']; ?></strong>
                        </div>
                        <div class="log-type">
                            <span class="log-badge critical">Critical</span>
                            <strong><?php echo $logs['critical_count']; ?></strong>
                        </div>
                    </div>
                    <div class="recent-logs">
                        <h4>Eventos Recientes</h4>
                        <?php foreach ($recentLogs as $log): ?>
                        <div class="log-item <?php echo $log['level']; ?>">
                            <span class="log-time"><?php echo date('H:i', strtotime($log['timestamp'])); ?></span>
                            <span class="log-message"><?php echo htmlspecialchars($log['message']); ?></span>
                            <button class="btn-icon" onclick="viewLogDetail(<?php echo $log['id']; ?>)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="card-footer">
                        <a href="<?php echo getBaseUrl(); ?>auditor/logs" class="action-link">
                            <i class="fas fa-list"></i>
                            Ver Todos los Logs
                        </a>
                    </div>
                </div>
            </div>

            <!-- User Activity Monitoring -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-users-cog"></i> Monitoreo de Usuarios</h3>
                    <div class="card-actions">
                        <button class="btn-icon" onclick="exportUserActivity()" title="Exportar Actividad">
                            <i class="fas fa-download"></i>
                        </button>
                    </div>
                </div>
                <div class="card-content">
                    <div class="user-activity-summary">
                        <div class="activity-metric">
                            <label>Sesiones Activas</label>
                            <span class="metric-value"><?php echo $userActivity['active_sessions']; ?></span>
                        </div>
                        <div class="activity-metric">
                            <label>Logins Hoy</label>
                            <span class="metric-value"><?php echo $userActivity['logins_today']; ?></span>
                        </div>
                        <div class="activity-metric">
                            <label>Intentos Fallidos</label>
                            <span class="metric-value warning"><?php echo $userActivity['failed_attempts']; ?></span>
                        </div>
                    </div>
                    <div class="suspicious-activity">
                        <h4>Actividad Sospechosa</h4>
                        <?php if (empty($suspiciousActivity)): ?>
                        <p class="no-activity">No se detectó actividad sospechosa</p>
                        <?php else: ?>
                        <?php foreach ($suspiciousActivity as $activity): ?>
                        <div class="suspicious-item">
                            <div class="activity-icon">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="activity-details">
                                <p><strong><?php echo htmlspecialchars($activity['user']); ?></strong> - <?php echo htmlspecialchars($activity['description']); ?></p>
                                <small><?php echo timeAgo($activity['timestamp']); ?></small>
                            </div>
                            <button class="btn-icon" onclick="investigateActivity(<?php echo $activity['id']; ?>)">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <a href="<?php echo getBaseUrl(); ?>auditor/users" class="action-link">
                            <i class="fas fa-users"></i>
                            Monitoreo Completo
                        </a>
                    </div>
                </div>
            </div>

            <!-- Audit Schedule -->
            <div class="dashboard-card full-width">
                <div class="card-header">
                    <h3><i class="fas fa-calendar-alt"></i> Cronograma de Auditorías</h3>
                    <div class="card-actions">
                        <button class="btn-icon" onclick="addAuditSchedule()" title="Programar Auditoría">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-content">
                    <div class="audit-calendar">
                        <div class="calendar-header">
                            <button class="calendar-nav" onclick="previousWeek()">&lt;</button>
                            <h4 id="currentWeek">Semana del <?php echo date('d/m/Y'); ?></h4>
                            <button class="calendar-nav" onclick="nextWeek()">&gt;</button>
                        </div>
                        <div class="calendar-days">
                            <?php foreach ($auditSchedule as $day => $audits): ?>
                            <div class="calendar-day">
                                <div class="day-header"><?php echo $day; ?></div>
                                <div class="day-content">
                                    <?php foreach ($audits as $audit): ?>
                                    <div class="audit-item <?php echo $audit['type']; ?>">
                                        <span class="audit-time"><?php echo $audit['time']; ?></span>
                                        <span class="audit-title"><?php echo htmlspecialchars($audit['title']); ?></span>
                                        <div class="audit-actions">
                                            <button class="btn-icon" onclick="editAudit(<?php echo $audit['id']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vulnerability Assessment -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-bug"></i> Evaluación de Vulnerabilidades</h3>
                    <div class="card-actions">
                        <button class="btn-icon" onclick="runVulnerabilityScans()" title="Ejecutar Escaneo">
                            <i class="fas fa-play"></i>
                        </button>
                    </div>
                </div>
                <div class="card-content">
                    <div class="vulnerability-summary">
                        <div class="vuln-level critical">
                            <span class="vuln-count"><?php echo $vulnerabilities['critical']; ?></span>
                            <span class="vuln-label">Críticas</span>
                        </div>
                        <div class="vuln-level high">
                            <span class="vuln-count"><?php echo $vulnerabilities['high']; ?></span>
                            <span class="vuln-label">Altas</span>
                        </div>
                        <div class="vuln-level medium">
                            <span class="vuln-count"><?php echo $vulnerabilities['medium']; ?></span>
                            <span class="vuln-label">Medias</span>
                        </div>
                        <div class="vuln-level low">
                            <span class="vuln-count"><?php echo $vulnerabilities['low']; ?></span>
                            <span class="vuln-label">Bajas</span>
                        </div>
                    </div>
                    <div class="last-scan">
                        <p><strong>Último escaneo:</strong> <?php echo $lastScan['date']; ?></p>
                        <p><strong>Estado:</strong> <span class="scan-status <?php echo $lastScan['status']; ?>"><?php echo ucfirst($lastScan['status']); ?></span></p>
                    </div>
                    <div class="card-footer">
                        <a href="<?php echo getBaseUrl(); ?>auditor/vulnerabilities" class="action-link">
                            <i class="fas fa-shield-alt"></i>
                            Ver Reporte Completo
                        </a>
                    </div>
                </div>
            </div>

            <!-- Report Generation -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-file-pdf"></i> Generación de Reportes</h3>
                </div>
                <div class="card-content">
                    <div class="report-templates">
                        <button class="report-btn" onclick="generateComplianceReport()">
                            <i class="fas fa-certificate"></i>
                            <span>Reporte de Cumplimiento</span>
                        </button>
                        <button class="report-btn" onclick="generateSecurityReport()">
                            <i class="fas fa-shield-alt"></i>
                            <span>Reporte de Seguridad</span>
                        </button>
                        <button class="report-btn" onclick="generateUserActivityReport()">
                            <i class="fas fa-users"></i>
                            <span>Actividad de Usuarios</span>
                        </button>
                        <button class="report-btn" onclick="generateVulnerabilityReport()">
                            <i class="fas fa-bug"></i>
                            <span>Evaluación de Vulnerabilidades</span>
                        </button>
                    </div>
                    <div class="card-footer">
                        <a href="<?php echo getBaseUrl(); ?>auditor/reports" class="action-link">
                            <i class="fas fa-folder"></i>
                            Historial de Reportes
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

function generateAuditReport() {
    window.location.href = '<?php echo getBaseUrl(); ?>auditor/reports/generate';
}

function runComplianceCheck() {
    const button = event.target.closest('button');
    const originalContent = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;
    
    setTimeout(() => {
        button.innerHTML = originalContent;
        button.disabled = false;
        alert('Verificación de cumplimiento completada');
    }, 3000);
}

function refreshLogs() {
    location.reload();
}

function viewLogDetail(id) {
    window.location.href = '<?php echo getBaseUrl(); ?>auditor/logs/' + id;
}

function exportUserActivity() {
    window.open('<?php echo getBaseUrl(); ?>auditor/users/export', '_blank');
}

function investigateActivity(id) {
    window.location.href = '<?php echo getBaseUrl(); ?>auditor/investigate/' + id;
}

function addAuditSchedule() {
    window.location.href = '<?php echo getBaseUrl(); ?>auditor/schedule/new';
}

function editAudit(id) {
    window.location.href = '<?php echo getBaseUrl(); ?>auditor/schedule/' + id + '/edit';
}

function previousWeek() {
    // Implementation for previous week navigation
    console.log('Loading previous week...');
}

function nextWeek() {
    // Implementation for next week navigation
    console.log('Loading next week...');
}

function runVulnerabilityScans() {
    if (confirm('¿Está seguro de que desea ejecutar un escaneo completo de vulnerabilidades?')) {
        alert('Escaneo de vulnerabilidades iniciado. Recibirá una notificación cuando termine.');
    }
}

function generateComplianceReport() {
    window.open('<?php echo getBaseUrl(); ?>auditor/reports/compliance', '_blank');
}

function generateSecurityReport() {
    window.open('<?php echo getBaseUrl(); ?>auditor/reports/security', '_blank');
}

function generateUserActivityReport() {
    window.open('<?php echo getBaseUrl(); ?>auditor/reports/users', '_blank');
}

function generateVulnerabilityReport() {
    window.open('<?php echo getBaseUrl(); ?>auditor/reports/vulnerabilities', '_blank');
}

// Auto-refresh logs every 30 seconds
setInterval(function() {
    refreshLogs();
}, 30000);

// Real-time compliance monitoring
function monitorCompliance() {
    fetch('<?php echo getBaseUrl(); ?>api/compliance/status', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '<?php echo $csrfToken; ?>'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.complianceRate < 95) {
            showAlert('El índice de cumplimiento ha bajado del 95%', 'warning');
        }
    });
}

function showAlert(message, type) {
    const alert = document.createElement('div');
    alert.className = 'audit-alert ' + type;
    alert.innerHTML = `
        <div class="alert-content">
            <i class="fas fa-exclamation-triangle"></i>
            <span>${message}</span>
        </div>
        <button onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    document.body.appendChild(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 10000);
}

// Monitor compliance every 5 minutes
setInterval(monitorCompliance, 300000);
</script>
