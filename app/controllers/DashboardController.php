<?php
/**
 * Controlador de Dashboard - Figger Energy SAS
 */

require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Alert.php';

class DashboardController {
    private $userModel;
    private $alertModel;
    
    public function __construct() {
        $this->userModel = new User();
        $this->alertModel = new Alert();
    }
    
    /**
     * Dashboard Administrador
     */
    public function adminDashboard() {
        AuthMiddleware::requireRole('admin');
        
        $data = [
            'title' => 'Dashboard Administrador',
            'user' => AuthMiddleware::getCurrentUser(),
            'user_stats' => $this->userModel->getStats(),
            'alert_stats' => $this->alertModel->getStats(),
            'recent_alerts' => $this->alertModel->getRecent(5),
            'all_users' => $this->userModel->getAll(),
            'recent_activities' => $this->getRecentActivities(10)
        ];
        
        include __DIR__ . '/../../views/pages/dashboard/admin.php';
    }
    
    /**
     * Dashboard Empleado
     */
    public function empleadoDashboard() {
        AuthMiddleware::requireRole('empleado');
        
        $user = AuthMiddleware::getCurrentUser();
        
        $data = [
            'title' => 'Dashboard Empleado',
            'user' => $user,
            'my_alerts' => $this->alertModel->getByUser($user['id']),
            'available_alerts' => $this->alertModel->getAvailable(5),
            'my_stats' => $this->alertModel->getStatsByUser($user['id']),
            'my_activities' => $this->getUserActivities($user['id'], 10)
        ];
        
        include __DIR__ . '/../../views/pages/dashboard/empleado.php';
    }
    
    /**
     * Dashboard Auditor
     */
    public function auditorDashboard() {
        AuthMiddleware::requireRole('auditor');
        
        $data = [
            'title' => 'Dashboard Auditor',
            'user' => AuthMiddleware::getCurrentUser(),
            'general_stats' => $this->alertModel->getGeneralStats(),
            'employee_stats' => $this->getEmployeeStats(),
            'alerts_for_audit' => $this->alertModel->getForAudit(10),
            'alert_types_stats' => $this->alertModel->getStatsByType(),
            'system_activities' => $this->getRecentActivities(20)
        ];
        
        include __DIR__ . '/../../views/pages/dashboard/auditor.php';
    }
    
    /**
     * Obtener estadísticas de empleados para auditoría
     */
    private function getEmployeeStats() {
        $db = getDB();
        $stmt = prepareStatement("
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
        $stmt->execute();
        $result = $stmt->get_result();
        $stats = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $stats;
    }
    
    /**
     * Obtener actividades recientes del sistema
     */
    private function getRecentActivities($limit = 10) {
        $db = getDB();
        $stmt = prepareStatement("
            SELECT a.accion, a.descripcion, a.fecha_actividad, u.nombre, u.rol 
            FROM actividades a 
            JOIN usuarios u ON a.usuario_id = u.id 
            ORDER BY a.fecha_actividad DESC 
            LIMIT ?
        ");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $activities = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $activities;
    }
    
    /**
     * Obtener actividades de un usuario específico
     */
    private function getUserActivities($userId, $limit = 10) {
        $db = getDB();
        $stmt = prepareStatement("
            SELECT accion, descripcion, fecha_actividad 
            FROM actividades 
            WHERE usuario_id = ? 
            ORDER BY fecha_actividad DESC 
            LIMIT ?
        ");
        $stmt->bind_param("ii", $userId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $activities = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $activities;
    }
    
    /**
     * Tomar alerta (AJAX endpoint)
     */
    public function takeAlert() {
        AuthMiddleware::requireRole('empleado');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $alertId = intval($_POST['alert_id'] ?? 0);
        $user = AuthMiddleware::getCurrentUser();
        
        if (!$alertId) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de alerta inválido']);
            return;
        }
        
        $result = $this->alertModel->assign($alertId, $user['id']);
        
        if ($result) {
            AuthMiddleware::logActivity($user['id'], 'alerta_tomada', "Tomó la alerta ID: $alertId");
            echo json_encode(['success' => true, 'message' => 'Alerta asignada exitosamente']);
        } else {
            echo json_encode(['error' => 'Error al asignar la alerta']);
        }
    }
    
    /**
     * Actualizar estado de alerta (AJAX endpoint)
     */
    public function updateAlert() {
        AuthMiddleware::requireAnyRole(['empleado', 'admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }
        
        $alertId = intval($_POST['alert_id'] ?? 0);
        $estado = Security::sanitizeInput($_POST['estado'] ?? '');
        $observaciones = Security::sanitizeInput($_POST['observaciones'] ?? '');
        
        if (!$alertId || !$estado) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos incompletos']);
            return;
        }
        
        $updateData = ['estado' => $estado];
        if (!empty($observaciones)) {
            $updateData['observaciones'] = $observaciones;
        }
        
        $result = $this->alertModel->update($alertId, $updateData);
        
        if ($result) {
            $user = AuthMiddleware::getCurrentUser();
            AuthMiddleware::logActivity($user['id'], 'alerta_actualizada', "Actualizó alerta ID: $alertId a estado: $estado");
            echo json_encode(['success' => true, 'message' => 'Alerta actualizada exitosamente']);
        } else {
            echo json_encode(['error' => 'Error al actualizar la alerta']);
        }
    }
}
