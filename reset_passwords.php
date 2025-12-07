<?php
require_once 'conexion.php';

// Update all teachers to use 'password123' as password
$password_hash = password_hash('password123', PASSWORD_DEFAULT);

$sql = "UPDATE profesores SET password = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$password_hash]);

echo "Contraseñas actualizadas. Todos los usuarios ahora tienen la contraseña: password123\n";
echo "Hash generado: " . $password_hash . "\n";
echo "Profesores actualizados: " . $stmt->rowCount() . "\n";
?>
