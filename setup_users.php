<?php
/**
 * Script para crear usuarios de prueba con passwords correctos
 * Ejecutar una sola vez para tener usuarios de prueba
 */

require_once 'conexion.php';

// Crear/actualizar admin
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("SELECT id FROM profesores WHERE email = 'admin@admin.com'");
$stmt->execute();

if ($stmt->fetch()) {
    // Actualizar admin existente
    $stmt = $pdo->prepare("UPDATE profesores SET password = ?, rol = 'admin' WHERE email = 'admin@admin.com'");
    $stmt->execute([$admin_password]);
    echo "✅ Admin actualizado: admin@admin.com / admin123<br>";
} else {
    // Crear admin
    $stmt = $pdo->prepare("INSERT INTO profesores (nombre, apellidos, email, password, rol, estado) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['Admin', 'Sistema', 'admin@admin.com', $admin_password, 'admin', 'activo']);
    echo "✅ Admin creado: admin@admin.com / admin123<br>";
}

// Crear/actualizar profesor de prueba
$profesor_password = password_hash('profesor123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("SELECT id FROM profesores WHERE email = 'profesor@test.com'");
$stmt->execute();

if ($stmt->fetch()) {
    // Actualizar profesor existente
    $stmt = $pdo->prepare("UPDATE profesores SET password = ?, rol = 'profesor' WHERE email = 'profesor@test.com'");
    $stmt->execute([$profesor_password]);
    echo "✅ Profesor actualizado: profesor@test.com / profesor123<br>";
} else {
    // Crear profesor
    $stmt = $pdo->prepare("INSERT INTO profesores (nombre, apellidos, email, password, rol, estado) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['Juan', 'Pérez', 'profesor@test.com', $profesor_password, 'profesor', 'activo']);
    echo "✅ Profesor creado: profesor@test.com / profesor123<br>";
}

// Crear otro profesor de prueba
$stmt = $pdo->prepare("SELECT id FROM profesores WHERE email = 'maria@test.com'");
$stmt->execute();

if ($stmt->fetch()) {
    $stmt = $pdo->prepare("UPDATE profesores SET password = ?, rol = 'profesor' WHERE email = 'maria@test.com'");
    $stmt->execute([$profesor_password]);
    echo "✅ Profesora actualizada: maria@test.com / profesor123<br>";
} else {
    $stmt = $pdo->prepare("INSERT INTO profesores (nombre, apellidos, email, password, rol, estado) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['María', 'García', 'maria@test.com', $profesor_password, 'profesor', 'activo']);
    echo "✅ Profesora creada: maria@test.com / profesor123<br>";
}

echo "<br><strong>Usuarios de prueba listos!</strong><br>";
echo "Admin: admin@admin.com / admin123<br>";
echo "Profesor 1: profesor@test.com / profesor123<br>";
echo "Profesor 2: maria@test.com / profesor123<br>";
?>
