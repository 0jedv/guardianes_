<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require_once 'conexion.php';
include 'includes/header.php';

// Handle Add Teacher (Admin only)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_teacher'])) {
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        die("Acceso denegado. Solo los administradores pueden añadir profesores.");
    }
    
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $departamento = $_POST['departamento'];
    $rol = $_POST['rol'] ?? 'profesor';
    
    $sql = "INSERT INTO profesores (nombre, apellidos, email, password, rol, departamento, estado, fecha_alta) VALUES (?, ?, ?, ?, ?, ?, 'activo', CURDATE())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nombre, $apellidos, $email, $password, $rol, $departamento]);
    
    header("Location: profesores.php?success=1");
    exit;
}

// Fetch Teachers
$stmt = $pdo->query("SELECT * FROM profesores ORDER BY apellidos, nombre");
$profesores = $stmt->fetchAll();
?>

<h1>Gestión de Profesores</h1>

<?php if (isset($_GET['success'])): ?>
    <div class="alert" style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;">
        Profesor creado correctamente.
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
<div style="margin-bottom: 20px;">
    <h3>Añadir Nuevo Profesor</h3>
    <form method="POST" action="" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" autocomplete="off">
        <input type="hidden" name="add_teacher" value="1">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="nombre" required autocomplete="off">
            </div>
            <div class="form-group">
                <label>Apellidos</label>
                <input type="text" name="apellidos" required autocomplete="off">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required autocomplete="off">
            </div>
            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="password" required minlength="6" autocomplete="new-password">
            </div>
            <div class="form-group">
                <label>Departamento</label>
                <input type="text" name="departamento" autocomplete="off">
            </div>
            <div class="form-group">
                <label>Rol</label>
                <select name="rol" required style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 4px;" autocomplete="off">
                    <option value="profesor">Profesor</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn">Guardar Profesor</button>
    </form>
</div>
<?php endif; ?>

<h3>Listado de Profesores</h3>
<table>
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Apellidos</th>
            <th>Email</th>
            <th>Departamento</th>
            <th>Rol</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($profesores as $profesor): ?>
        <tr>
            <td><?php echo htmlspecialchars($profesor['nombre']); ?></td>
            <td><?php echo htmlspecialchars($profesor['apellidos']); ?></td>
            <td><?php echo htmlspecialchars($profesor['email']); ?></td>
            <td><?php echo htmlspecialchars($profesor['departamento']); ?></td>
            <td>
                <span style="padding: 4px 8px; border-radius: 4px; background: <?php echo ($profesor['rol'] ?? 'profesor') == 'admin' ? '#fff3cd' : '#e2e3e5'; ?>; color: #000;">
                    <?php echo ucfirst($profesor['rol'] ?? 'profesor'); ?>
                </span>
            </td>
            <td>
                <span style="padding: 4px 8px; border-radius: 4px; background: <?php echo $profesor['estado'] == 'activo' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $profesor['estado'] == 'activo' ? '#155724' : '#721c24'; ?>">
                    <?php echo ucfirst($profesor['estado']); ?>
                </span>
            </td>
            <td>
                <a href="#" style="color: var(--primary-color);">Editar</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include 'includes/footer.php'; ?>
