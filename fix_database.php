<?php
/**
 * Script para verificar y arreglar el esquema de la base de datos
 */

require_once 'conexion.php';

echo "<h2>Verificación de Base de Datos</h2>";

// Verificar longitud de columna password
$stmt = $pdo->query("SHOW COLUMNS FROM profesores LIKE 'password'");
$column = $stmt->fetch();

echo "<h3>1. Columna password:</h3>";
if ($column) {
    echo "Tipo actual: " . $column['Type'] . "<br>";
    
    if (strpos($column['Type'], 'varchar(60)') !== false || strpos($column['Type'], 'varchar(100)') !== false) {
        echo "<strong style='color:red;'>⚠️ PROBLEMA: La columna password es demasiado corta!</strong><br>";
        echo "Arreglando...<br>";
        
        try {
            $pdo->exec("ALTER TABLE profesores MODIFY COLUMN password VARCHAR(255) NOT NULL");
            echo "<strong style='color:green;'>✅ Columna password actualizada a VARCHAR(255)</strong><br>";
        } catch (Exception $e) {
            echo "<strong style='color:red;'>❌ Error: " . $e->getMessage() . "</strong><br>";
        }
    } else {
        echo "<strong style='color:green;'>✅ Columna password tiene el tamaño correcto</strong><br>";
    }
} else {
    echo "<strong style='color:red;'>❌ Columna password no existe!</strong><br>";
}

// Ahora actualizar passwords
echo "<br><h3>2. Actualizando passwords:</h3>";

// Admin
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE profesores SET password = ? WHERE email = 'admin@admin.com'");
$stmt->execute([$admin_password]);
echo "✅ Admin password actualizado<br>";

// Profesores
$profesor_password = password_hash('profesor123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE profesores SET password = ? WHERE email IN ('profesor@test.com', 'maria@test.com')");
$stmt->execute([$profesor_password]);
echo "✅ Profesores passwords actualizados<br>";

// Mostrar usuarios
echo "<br><h3>3. Usuarios disponibles:</h3>";
$stmt = $pdo->query("SELECT id, nombre, apellidos, email, rol, LENGTH(password) as pass_length FROM profesores WHERE estado = 'activo'");
$users = $stmt->fetchAll();

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th><th>Long. Password</th></tr>";
foreach ($users as $user) {
    echo "<tr>";
    echo "<td>{$user['id']}</td>";
    echo "<td>{$user['nombre']} {$user['apellidos']}</td>";
    echo "<td>{$user['email']}</td>";
    echo "<td>{$user['rol']}</td>";
    echo "<td>{$user['pass_length']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<br><h3>✅ Base de datos verificada y arreglada!</h3>";
echo "<p><strong>Credenciales de acceso:</strong></p>";
echo "<ul>";
echo "<li>Admin: admin@admin.com / admin123</li>";
echo "<li>Profesor 1: profesor@test.com / profesor123</li>";
echo "<li>Profesor 2: maria@test.com / profesor123</li>";
echo "</ul>";
echo "<p><a href='index.php'>Ir al login</a></p>";
?>
