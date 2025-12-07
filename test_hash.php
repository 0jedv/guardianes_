<?php
// Script para generar y verificar hashes de contraseñas

echo "=== GENERADOR DE HASHES ===\n\n";

// Generar hash para password123
$password123_hash = password_hash('password123', PASSWORD_DEFAULT);
echo "Hash para 'password123':\n";
echo $password123_hash . "\n\n";

// Generar hash para admin123
$admin123_hash = password_hash('admin123', PASSWORD_DEFAULT);
echo "Hash para 'admin123':\n";
echo $admin123_hash . "\n\n";

// Hash que está en la base de datos
$db_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
echo "Hash en la base de datos:\n";
echo $db_hash . "\n\n";

// Verificar qué contraseña corresponde
echo "=== VERIFICACIÓN ===\n\n";

if (password_verify('password123', $db_hash)) {
    echo "✓ El hash en la BD corresponde a 'password123'\n";
} else {
    echo "✗ El hash en la BD NO corresponde a 'password123'\n";
}

if (password_verify('admin123', $db_hash)) {
    echo "✓ El hash en la BD corresponde a 'admin123'\n";
} else {
    echo "✗ El hash en la BD NO corresponde a 'admin123'\n";
}

echo "\n=== SQL PARA ACTUALIZAR ===\n\n";
echo "-- Usar este SQL en phpMyAdmin:\n";
echo "UPDATE `profesores` SET `password` = '$admin123_hash' WHERE `email` = 'admin@guardianes.com';\n";
?>
