<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require_once 'conexion.php';
include 'includes/header.php';

// Fetch some stats
$stmt = $pdo->query("SELECT COUNT(*) FROM profesores WHERE estado = 'activo'");
$totalProfesores = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM guardias WHERE fecha = CURDATE()");
$guardiasHoy = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM ausencias WHERE CURDATE() BETWEEN fecha_inicio AND fecha_fin");
$ausenciasHoy = $stmt->fetchColumn();
?>

<h1>Dashboard</h1>
<h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['user_name']); ?></h2>
<h3>Tu rol asignado es "<?php echo htmlspecialchars($_SESSION['user_role']); ?>"</h3>
<div style="display: flex; gap: 20px; margin-top: 20px;">
    <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex: 1;">
        <h3>Profesores Activos</h3>
        <p style="font-size: 2rem; font-weight: bold; color: var(--primary-color);"><?php echo $totalProfesores; ?></p>
    </div>
    <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex: 1;">
        <h3>Guardias Hoy</h3>
        <p style="font-size: 2rem; font-weight: bold; color: var(--primary-color);"><?php echo $guardiasHoy; ?></p>
    </div>
    <div style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex: 1;">
        <h3>Ausencias Hoy</h3>
        <p style="font-size: 2rem; font-weight: bold; color: var(--danger-color);"><?php echo $ausenciasHoy; ?></p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
