<?php
/**
 * Script para crear/actualizar usuarios con contraseñas correctas
 * Ejecutar una sola vez para configurar usuarios de prueba
 */

require_once 'conexion.php';

echo "<h2>Configuración de Usuarios</h2>";

// Crear/actualizar admin
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("SELECT id FROM profesores WHERE email = 'admin@guardianes.com'");
$stmt->execute();

if ($stmt->fetch()) {
    // Actualizar admin existente
    $stmt = $pdo->prepare("UPDATE profesores SET password = ?, rol = 'admin' WHERE email = 'admin@guardianes.com'");
    $stmt->execute([$admin_password]);
    echo "✅ Admin actualizado: admin@guardianes.com / admin123<br>";
} else {
    // Crear admin
    $stmt = $pdo->prepare("INSERT INTO profesores (nombre, apellidos, email, password, rol, departamento, estado, fecha_alta) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(['Admin', 'Sistema', 'admin@guardianes.com', $admin_password, 'admin', 'Administración', 'activo', date('Y-m-d')]);
    echo "✅ Admin creado: admin@guardianes.com / admin123<br>";
}

// Actualizar contraseñas de todos los profesores existentes
$profesor_password = password_hash('profesor123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE profesores SET password = ? WHERE rol = 'profesor' OR rol IS NULL");
$stmt->execute([$profesor_password]);
$count = $stmt->rowCount();

echo "✅ Contraseñas de profesores actualizadas: $count profesores<br>";

echo "<br><strong>Usuarios configurados correctamente!</strong><br>";
echo "<br><strong>Credenciales de acceso:</strong><br>";
echo "<ul>";
echo "<li>Admin: admin@guardianes.com / admin123</li>";
echo "<li>Profesores: [email del profesor] / profesor123</li>";
echo "</ul>";
echo "<p><a href='index.html'>Ir al login</a></p>";
?>