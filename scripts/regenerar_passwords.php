<?php
/**
 * Script para regenerar contraseñas de usuarios demo
 * Figger Energy SAS - Mantenimiento de Base de Datos
 */

require_once '../includes/db.php';

// Usuarios demo con sus contraseñas reales
$usuarios_demo = [
    'admin@figgerenergy.gov.co' => 'admin123',
    'empleado@figgerenergy.gov.co' => 'empleado123', 
    'auditor@figgerenergy.gov.co' => 'auditor123'
];

echo "=== REGENERADOR DE CONTRASEÑAS DEMO ===\n";
echo "Figger Energy SAS - Sistema de Gestión\n\n";

$conexion = conectarDB();

foreach ($usuarios_demo as $email => $password_real) {
    // Generar nuevo hash seguro
    $password_hash = password_hash($password_real, PASSWORD_DEFAULT);
    
    // Actualizar en base de datos
    $stmt = $conexion->prepare("UPDATE usuarios SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $password_hash, $email);
    
    if ($stmt->execute()) {
        echo "✅ Contraseña actualizada para: $email\n";
        echo "   Contraseña real: $password_real\n";
        echo "   Hash generado: " . substr($password_hash, 0, 30) . "...\n\n";
    } else {
        echo "❌ Error actualizando: $email\n";
        echo "   Error: " . $stmt->error . "\n\n";
    }
    
    $stmt->close();
}

cerrarDB($conexion);

echo "=== PROCESO COMPLETADO ===\n";
echo "\nNotas importantes:\n";
echo "- Las contraseñas en phpMyAdmin aparecen como hashes (normal)\n";
echo "- Usa las contraseñas reales para hacer login en el sistema\n";
echo "- Los hashes garantizan la seguridad de las credenciales\n";
?>
