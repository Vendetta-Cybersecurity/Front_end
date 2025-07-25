<?php
/**
 * Modelo de Alerta - Figger Energy SAS
 * Gestión de alertas de minería ilegal
 */

require_once __DIR__ . '/../../config/database.php';

class Alert {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Obtener alerta por ID
     */
    public function findById($id) {
        $stmt = prepareStatement("
            SELECT am.*, u.nombre as usuario_asignado_nombre 
            FROM alertas_mineria am 
            LEFT JOIN usuarios u ON am.usuario_asignado = u.id 
            WHERE am.id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $alert = $result->fetch_assoc();
        $stmt->close();
        
        return $alert;
    }
    
    /**
     * Crear nueva alerta
     */
    public function create($data) {
        $stmt = prepareStatement("
            INSERT INTO alertas_mineria 
            (ubicacion, coordenadas, tipo_alerta, nivel_riesgo, descripcion, estado, fecha_deteccion) 
            VALUES (?, ?, ?, ?, ?, 'activa', NOW())
        ");
        
        $stmt->bind_param("sssss", 
            $data['ubicacion'], 
            $data['coordenadas'], 
            $data['tipo_alerta'], 
            $data['nivel_riesgo'], 
            $data['descripcion']
        );
        
        $result = $stmt->execute();
        $alertId = $this->db->insert_id;
        $stmt->close();
        
        return $result ? $alertId : false;
    }
    
    /**
     * Actualizar alerta
     */
    public function update($id, $data) {
        $sets = [];
        $types = "";
        $values = [];
        
        $allowedFields = ['ubicacion', 'coordenadas', 'tipo_alerta', 'nivel_riesgo', 'descripcion', 'estado', 'observaciones'];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $sets[] = "$key = ?";
                $types .= "s";
                $values[] = $value;
            }
        }
        
        if (empty($sets)) return false;
        
        // Si se está resolviendo, agregar fecha de resolución
        if (isset($data['estado']) && in_array($data['estado'], ['resuelta', 'verificada', 'falsa'])) {
            $sets[] = "fecha_resolucion = NOW()";
        }
        
        $sql = "UPDATE alertas_mineria SET " . implode(", ", $sets) . " WHERE id = ?";
        $types .= "i";
        $values[] = $id;
        
        $stmt = prepareStatement($sql);
        $stmt->bind_param($types, ...$values);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Asignar alerta a usuario
     */
    public function assign($alertId, $userId) {
        $stmt = prepareStatement("UPDATE alertas_mineria SET usuario_asignado = ?, estado = 'investigando' WHERE id = ?");
        $stmt->bind_param("ii", $userId, $alertId);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Desasignar alerta
     */
    public function unassign($alertId) {
        $stmt = prepareStatement("UPDATE alertas_mineria SET usuario_asignado = NULL, estado = 'activa' WHERE id = ?");
        $stmt->bind_param("i", $alertId);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Obtener alertas por usuario
     */
    public function getByUser($userId) {
        $stmt = prepareStatement("
            SELECT * FROM alertas_mineria 
            WHERE usuario_asignado = ? 
            ORDER BY fecha_deteccion DESC
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $alerts = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $alerts;
    }
    
    /**
     * Obtener alertas disponibles (sin asignar)
     */
    public function getAvailable($limit = null) {
        $sql = "
            SELECT * FROM alertas_mineria 
            WHERE usuario_asignado IS NULL AND estado = 'activa'
            ORDER BY nivel_riesgo DESC, fecha_deteccion ASC
        ";
        
        if ($limit) {
            $sql .= " LIMIT " . intval($limit);
        }
        
        $stmt = prepareStatement($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $alerts = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $alerts;
    }
    
    /**
     * Obtener alertas recientes
     */
    public function getRecent($limit = 10) {
        $stmt = prepareStatement("
            SELECT am.*, u.nombre as usuario_asignado_nombre
            FROM alertas_mineria am
            LEFT JOIN usuarios u ON am.usuario_asignado = u.id
            ORDER BY am.fecha_deteccion DESC
            LIMIT ?
        ");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $alerts = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $alerts;
    }
    
    /**
     * Obtener alertas para auditoría
     */
    public function getForAudit($limit = 10) {
        $stmt = prepareStatement("
            SELECT am.*, u.nombre as empleado_nombre
            FROM alertas_mineria am
            LEFT JOIN usuarios u ON am.usuario_asignado = u.id
            WHERE am.estado IN ('verificada', 'resuelta')
            ORDER BY am.fecha_resolucion DESC
            LIMIT ?
        ");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $alerts = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $alerts;
    }
    
    /**
     * Obtener estadísticas de alertas
     */
    public function getStats() {
        $stmt = prepareStatement("SELECT estado, COUNT(*) as cantidad FROM alertas_mineria GROUP BY estado");
        $stmt->execute();
        $result = $stmt->get_result();
        $stats = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $stats;
    }
    
    /**
     * Obtener estadísticas por tipo
     */
    public function getStatsByType() {
        $stmt = prepareStatement("
            SELECT 
                tipo_alerta,
                COUNT(*) as cantidad,
                AVG(CASE WHEN fecha_resolucion IS NOT NULL 
                    THEN TIMESTAMPDIFF(HOUR, fecha_deteccion, fecha_resolucion) 
                    ELSE NULL END) as tiempo_promedio_resolucion
            FROM alertas_mineria
            GROUP BY tipo_alerta
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $stats = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $stats;
    }
    
    /**
     * Obtener estadísticas por usuario
     */
    public function getStatsByUser($userId) {
        $stmt = prepareStatement("
            SELECT 
                COUNT(*) as total_asignadas,
                SUM(CASE WHEN estado = 'resuelta' THEN 1 ELSE 0 END) as resueltas,
                SUM(CASE WHEN estado = 'activa' THEN 1 ELSE 0 END) as activas,
                SUM(CASE WHEN estado = 'investigando' THEN 1 ELSE 0 END) as investigando
            FROM alertas_mineria 
            WHERE usuario_asignado = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats = $result->fetch_assoc();
        $stmt->close();
        
        return $stats;
    }
    
    /**
     * Obtener estadísticas generales para auditoría
     */
    public function getGeneralStats() {
        $stmt = prepareStatement("
            SELECT 
                COUNT(*) as total_alertas,
                SUM(CASE WHEN estado = 'resuelta' THEN 1 ELSE 0 END) as alertas_resueltas,
                SUM(CASE WHEN estado = 'verificada' THEN 1 ELSE 0 END) as alertas_verificadas,
                SUM(CASE WHEN estado = 'falsa' THEN 1 ELSE 0 END) as alertas_falsas,
                SUM(CASE WHEN nivel_riesgo = 'critico' THEN 1 ELSE 0 END) as alertas_criticas
            FROM alertas_mineria
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $stats = $result->fetch_assoc();
        $stmt->close();
        
        return $stats;
    }
    
    /**
     * Eliminar alerta
     */
    public function delete($id) {
        $stmt = prepareStatement("DELETE FROM alertas_mineria WHERE id = ?");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
}
