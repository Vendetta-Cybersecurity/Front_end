<?php
/**
 * Figger Energy SAS - Dashboard Data Provider
 * Provides role-based data access for different user types
 */

require_once 'config.php';

// Enable CORS for development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Set content type
header('Content-Type: application/json');

// Require authentication
requireAuth();

try {
    $action = $_GET['action'] ?? '';
    $userType = $_SESSION['user_type'] ?? '';
    
    switch ($action) {
        case 'stats':
            getStats($userType);
            break;
        case 'notifications':
            getNotifications($userType);
            break;
        case 'schedule':
            getSchedule($userType);
            break;
        case 'reports':
            getReports($userType);
            break;
        case 'personnel':
            getPersonnel($userType);
            break;
        case 'projects':
            getProjects($userType);
            break;
        case 'security-logs':
            getSecurityLogs($userType);
            break;
        case 'audit-data':
            getAuditData($userType);
            break;
        case 'compliance':
            getComplianceData($userType);
            break;
        default:
            sendJsonResponse(['error' => 'Acción no válida'], 400);
    }
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    sendJsonResponse(['error' => 'Error interno del servidor'], 500);
}

/**
 * Get dashboard statistics based on user type
 */
function getStats($userType) {
    $db = Database::getInstance();
    $userId = $_SESSION['user_id'];
    
    switch ($userType) {
        case 'empleado':
            getEmployeeStats($db, $userId);
            break;
        case 'administrativo':
            getAdminStats($db, $userId);
            break;
        case 'auditor':
            getAuditorStats($db, $userId);
            break;
        default:
            sendJsonResponse(['error' => 'Tipo de usuario no válido'], 403);
    }
}

/**
 * Employee statistics
 */
function getEmployeeStats($db, $userId) {
    $stats = [
        'horas_trabajadas' => 168,
        'tareas_completadas' => 12,
        'capacitaciones_pendientes' => 3,
        'eficiencia_promedio' => 87.5,
        'proxima_capacitacion' => 'Seguridad en Alturas - 15 Nov 2024',
        'ultimo_reporte' => '2024-11-01',
        'proyectos_activos' => 4
    ];
    
    sendJsonResponse(['success' => true, 'stats' => $stats]);
}

/**
 * Administrative statistics
 */
function getAdminStats($db, $userId) {
    $stats = [
        'empleados_activos' => 45,
        'proyectos_en_curso' => 8,
        'eficiencia_general' => 91.2,
        'presupuesto_disponible' => 2500000,
        'ingresos_mes' => 15000000,
        'gastos_mes' => 8500000,
        'certificaciones_vigentes' => 12,
        'reuniones_programadas' => 7
    ];
    
    sendJsonResponse(['success' => true, 'stats' => $stats]);
}

/**
 * Auditor statistics
 */
function getAuditorStats($db, $userId) {
    $stats = [
        'auditorias_completadas' => 156,
        'no_conformidades' => 3,
        'cumplimiento_general' => 96.8,
        'riesgos_identificados' => 8,
        'acciones_correctivas' => 12,
        'documentos_revisados' => 89,
        'certificaciones_auditadas' => 15,
        'proxima_auditoria' => '2024-11-20'
    ];
    
    sendJsonResponse(['success' => true, 'stats' => $stats]);
}

/**
 * Get notifications based on user type
 */
function getNotifications($userType) {
    switch ($userType) {
        case 'empleado':
            $notifications = [
                [
                    'id' => 1,
                    'tipo' => 'capacitacion',
                    'titulo' => 'Nueva Capacitación Disponible',
                    'mensaje' => 'Seguridad en Sistemas Fotovoltaicos - Inscríbete antes del 20 Nov',
                    'fecha' => '2024-11-01 09:00:00',
                    'leido' => false
                ],
                [
                    'id' => 2,
                    'tipo' => 'tarea',
                    'titulo' => 'Reporte Mensual Pendiente',
                    'mensaje' => 'Recuerda enviar tu reporte de actividades antes del viernes',
                    'fecha' => '2024-11-02 14:30:00',
                    'leido' => false
                ],
                [
                    'id' => 3,
                    'tipo' => 'general',
                    'titulo' => 'Reunión de Equipo',
                    'mensaje' => 'Reunión programada para mañana a las 10:00 AM',
                    'fecha' => '2024-11-03 16:00:00',
                    'leido' => true
                ]
            ];
            break;
            
        case 'administrativo':
            $notifications = [
                [
                    'id' => 1,
                    'tipo' => 'urgente',
                    'titulo' => 'Aprobación Requerida',
                    'mensaje' => 'Presupuesto del Proyecto Solar Industrial requiere aprobación',
                    'fecha' => '2024-11-01 08:00:00',
                    'leido' => false
                ],
                [
                    'id' => 2,
                    'tipo' => 'reunion',
                    'titulo' => 'Junta Directiva',
                    'mensaje' => 'Presentación de resultados trimestrales - Viernes 10:00 AM',
                    'fecha' => '2024-11-02 11:00:00',
                    'leido' => false
                ],
                [
                    'id' => 3,
                    'tipo' => 'personal',
                    'titulo' => 'Nuevo Empleado',
                    'mensaje' => 'Juan Pérez se incorpora al equipo de instalaciones',
                    'fecha' => '2024-11-03 15:30:00',
                    'leido' => true
                ]
            ];
            break;
            
        case 'auditor':
            $notifications = [
                [
                    'id' => 1,
                    'tipo' => 'auditoria',
                    'titulo' => 'Auditoría ISO 45001',
                    'mensaje' => 'Programada para el 20 de noviembre - Preparar documentación',
                    'fecha' => '2024-11-01 07:00:00',
                    'leido' => false
                ],
                [
                    'id' => 2,
                    'tipo' => 'cumplimiento',
                    'titulo' => 'No Conformidad Menor',
                    'mensaje' => 'Detectada en proceso de mantenimiento - Acción correctiva requerida',
                    'fecha' => '2024-11-02 13:45:00',
                    'leido' => false
                ],
                [
                    'id' => 3,
                    'tipo' => 'reporte',
                    'titulo' => 'Informe de Compliance',
                    'mensaje' => 'Reporte mensual generado y disponible para revisión',
                    'fecha' => '2024-11-03 09:15:00',
                    'leido' => true
                ]
            ];
            break;
            
        default:
            $notifications = [];
    }
    
    sendJsonResponse(['success' => true, 'notifications' => $notifications]);
}

/**
 * Get schedule data
 */
function getSchedule($userType) {
    if ($userType !== 'empleado' && $userType !== 'administrativo') {
        sendJsonResponse(['error' => 'Acceso no autorizado'], 403);
    }
    
    $schedule = [
        [
            'fecha' => '2024-11-04',
            'eventos' => [
                ['hora' => '08:00', 'actividad' => 'Revisión técnica - Proyecto Medellín'],
                ['hora' => '10:30', 'actividad' => 'Reunión con cliente - Solar Corp'],
                ['hora' => '14:00', 'actividad' => 'Inspección de instalación']
            ]
        ],
        [
            'fecha' => '2024-11-05',
            'eventos' => [
                ['hora' => '09:00', 'actividad' => 'Capacitación seguridad'],
                ['hora' => '11:00', 'actividad' => 'Mantenimiento preventivo'],
                ['hora' => '15:30', 'actividad' => 'Elaboración de informes']
            ]
        ]
    ];
    
    sendJsonResponse(['success' => true, 'schedule' => $schedule]);
}

/**
 * Get reports data
 */
function getReports($userType) {
    if ($userType === 'empleado') {
        sendJsonResponse(['error' => 'Acceso no autorizado'], 403);
    }
    
    $reports = [
        [
            'id' => 1,
            'titulo' => 'Reporte de Producción Octubre',
            'tipo' => 'produccion',
            'fecha' => '2024-10-31',
            'autor' => 'Sistema Automatizado',
            'url' => '/reports/produccion-octubre-2024.pdf'
        ],
        [
            'id' => 2,
            'titulo' => 'Análisis de Eficiencia Energética',
            'tipo' => 'eficiencia',
            'fecha' => '2024-10-28',
            'autor' => 'Departamento Técnico',
            'url' => '/reports/eficiencia-q3-2024.pdf'
        ],
        [
            'id' => 3,
            'titulo' => 'Cumplimiento Regulatorio Q3',
            'tipo' => 'cumplimiento',
            'fecha' => '2024-10-25',
            'autor' => 'Auditoría Interna',
            'url' => '/reports/cumplimiento-q3-2024.pdf'
        ]
    ];
    
    sendJsonResponse(['success' => true, 'reports' => $reports]);
}

/**
 * Get personnel data (Admin only)
 */
function getPersonnel($userType) {
    if ($userType !== 'administrativo' && $userType !== 'auditor') {
        sendJsonResponse(['error' => 'Acceso no autorizado'], 403);
    }
    
    $personnel = [
        [
            'id' => 1,
            'nombre' => 'Carlos Rodríguez',
            'cargo' => 'Ingeniero Solar',
            'departamento' => 'Técnico',
            'estado' => 'Activo',
            'fecha_ingreso' => '2023-03-15',
            'certificaciones' => ['ISO 45001', 'Instalación FV']
        ],
        [
            'id' => 2,
            'nombre' => 'María González',
            'cargo' => 'Supervisora de Instalaciones',
            'departamento' => 'Operaciones',
            'estado' => 'Activo',
            'fecha_ingreso' => '2022-08-20',
            'certificaciones' => ['Gestión de Proyectos', 'Seguridad Industrial']
        ],
        [
            'id' => 3,
            'nombre' => 'Luis Martínez',
            'cargo' => 'Técnico de Mantenimiento',
            'departamento' => 'Mantenimiento',
            'estado' => 'Capacitación',
            'fecha_ingreso' => '2024-01-10',
            'certificaciones' => ['Seguridad Básica']
        ]
    ];
    
    sendJsonResponse(['success' => true, 'personnel' => $personnel]);
}

/**
 * Get projects data
 */
function getProjects($userType) {
    $projects = [
        [
            'id' => 1,
            'nombre' => 'Sistema Solar Industrial Medellín',
            'cliente' => 'IndustriasCorp S.A.S',
            'estado' => 'En Progreso',
            'progreso' => 65,
            'fecha_inicio' => '2024-09-01',
            'fecha_estimada' => '2024-12-15',
            'presupuesto' => 450000000,
            'responsable' => 'Carlos Rodríguez'
        ],
        [
            'id' => 2,
            'nombre' => 'Parque Solar Residencial Bogotá',
            'cliente' => 'Conjunto Residencial El Sol',
            'estado' => 'Planificación',
            'progreso' => 15,
            'fecha_inicio' => '2024-11-15',
            'fecha_estimada' => '2025-02-28',
            'presupuesto' => 280000000,
            'responsable' => 'María González'
        ]
    ];
    
    // Filter based on user type
    if ($userType === 'empleado') {
        // Employees only see projects they're assigned to
        $userId = $_SESSION['user_id'];
        $projects = array_filter($projects, function($project) use ($userId) {
            return true; // In real implementation, check assignment
        });
    }
    
    sendJsonResponse(['success' => true, 'projects' => $projects]);
}

/**
 * Get security logs (Auditor only)
 */
function getSecurityLogs($userType) {
    if ($userType !== 'auditor') {
        sendJsonResponse(['error' => 'Acceso no autorizado'], 403);
    }
    
    $db = Database::getInstance();
    
    $logs = $db->fetchAll(
        "SELECT la.*, u.nombre as usuario_nombre 
         FROM logs_acceso la 
         LEFT JOIN usuarios u ON la.user_id = u.id 
         ORDER BY la.timestamp DESC 
         LIMIT 50"
    );
    
    sendJsonResponse(['success' => true, 'logs' => $logs]);
}

/**
 * Get audit data (Auditor only)
 */
function getAuditData($userType) {
    if ($userType !== 'auditor') {
        sendJsonResponse(['error' => 'Acceso no autorizado'], 403);
    }
    
    $auditData = [
        'resumen' => [
            'auditorias_programadas' => 5,
            'auditorias_completadas' => 3,
            'no_conformidades_abiertas' => 2,
            'acciones_correctivas_pendientes' => 4
        ],
        'proximas_auditorias' => [
            [
                'fecha' => '2024-11-20',
                'tipo' => 'ISO 45001',
                'area' => 'Seguridad Industrial',
                'auditor' => 'Externa - SGS Colombia'
            ],
            [
                'fecha' => '2024-12-05',
                'tipo' => 'ISO 14001',
                'area' => 'Gestión Ambiental',
                'auditor' => 'Interna'
            ]
        ],
        'no_conformidades' => [
            [
                'id' => 'NC-2024-001',
                'descripcion' => 'Falta de señalización en área de trabajo',
                'severidad' => 'Menor',
                'estado' => 'Abierta',
                'fecha_deteccion' => '2024-10-15',
                'responsable' => 'Supervisor de Seguridad'
            ]
        ]
    ];
    
    sendJsonResponse(['success' => true, 'audit' => $auditData]);
}

/**
 * Get compliance data (Auditor only)
 */
function getComplianceData($userType) {
    if ($userType !== 'auditor') {
        sendJsonResponse(['error' => 'Acceso no autorizado'], 403);
    }
    
    $complianceData = [
        'indicadores' => [
            'cumplimiento_iso45001' => 96.8,
            'cumplimiento_iso14001' => 94.2,
            'cumplimiento_legal' => 98.5,
            'cumplimiento_retie' => 97.3
        ],
        'certificaciones' => [
            [
                'norma' => 'ISO 45001:2018',
                'estado' => 'Vigente',
                'fecha_vencimiento' => '2025-06-15',
                'ente_certificador' => 'SGS Colombia'
            ],
            [
                'norma' => 'ISO 14001:2015',
                'estado' => 'Vigente',
                'fecha_vencimiento' => '2025-03-20',
                'ente_certificador' => 'Bureau Veritas'
            ],
            [
                'norma' => 'RETIE',
                'estado' => 'Vigente',
                'fecha_vencimiento' => '2025-12-31',
                'ente_certificador' => 'CIDET'
            ]
        ],
        'riesgos' => [
            [
                'descripcion' => 'Vencimiento próximo certificación ISO 14001',
                'probabilidad' => 'Media',
                'impacto' => 'Alto',
                'plan_mitigacion' => 'Programar auditoría de renovación'
            ]
        ]
    ];
    
    sendJsonResponse(['success' => true, 'compliance' => $complianceData]);
}
?>
