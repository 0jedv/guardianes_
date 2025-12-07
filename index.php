<?php
session_start();
require_once 'conexion.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM profesores WHERE email = ? AND estado = 'activo'");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nombre'] . ' ' . $user['apellidos'];
        $_SESSION['user_role'] = $user['rol'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Email o contraseña incorrectos.";
    }
}

include 'includes/header.php';
?>

<div class="login-container">
    <h2>Iniciar Sesión</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <div class="form-group">
            <label for="email">Email del Profesor</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn">Entrar</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
