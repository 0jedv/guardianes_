<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require_once 'conexion.php';
include 'includes/header.php';

// Get profesor_id from URL or use logged-in user's ID
$profesor_id = isset($_GET['profesor_id']) ? (int)$_GET['profesor_id'] : $_SESSION['user_id'];

// Only admins can view other teachers' schedules
if ($profesor_id != $_SESSION['user_id'] && (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin')) {
    die("Acceso denegado. Solo puedes ver tu propio horario.");
}

// Fetch teacher info
$stmt = $pdo->prepare("SELECT nombre, apellidos, departamento FROM profesores WHERE id = ?");
$stmt->execute([$profesor_id]);
$profesor = $stmt->fetch();

if (!$profesor) {
    die("Profesor no encontrado.");
}

// Fetch schedule
$sql = "SELECT h.*, a.nombre as aula, m.nombre as modulo, h.grupo
        FROM horario h
        LEFT JOIN aulas a ON h.aula_id = a.id
        LEFT JOIN modulos m ON h.modulo_id = m.id
        WHERE h.profesor_id = ? AND h.curso_escolar = '2024-2025'
        ORDER BY 
            FIELD(h.dia_semana, 'lunes', 'martes', 'miercoles', 'jueves', 'viernes'),
            h.hora_inicio";
$stmt = $pdo->prepare($sql);
$stmt->execute([$profesor_id]);
$horarios = $stmt->fetchAll();

// Organize schedule by day and time
$schedule = [];
$time_slots = [];
foreach ($horarios as $horario) {
    $dia = $horario['dia_semana'];
    $hora = substr($horario['hora_inicio'], 0, 5);
    
    if (!isset($schedule[$dia])) {
        $schedule[$dia] = [];
    }
    $schedule[$dia][$hora] = $horario;
    
    if (!in_array($hora, $time_slots)) {
        $time_slots[] = $hora;
    }
}
sort($time_slots);

$dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];

// Fetch all teachers for admin dropdown
$all_teachers = [];
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    $stmt = $pdo->query("SELECT id, nombre, apellidos FROM profesores WHERE estado = 'activo' ORDER BY apellidos, nombre");
    $all_teachers = $stmt->fetchAll();
}
?>

<h1>Horario de <?php echo htmlspecialchars($profesor['nombre'] . ' ' . $profesor['apellidos']); ?></h1>
<p><strong>Departamento:</strong> <?php echo htmlspecialchars($profesor['departamento']); ?></p>

<?php if (!empty($all_teachers)): ?>
<div style="margin-bottom: 20px;">
    <form method="GET" action="" style="display: inline-flex; gap: 10px; align-items: center;">
        <label for="profesor_id">Ver horario de:</label>
        <select name="profesor_id" id="profesor_id" onchange="this.form.submit()" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
            <?php foreach ($all_teachers as $teacher): ?>
                <option value="<?php echo $teacher['id']; ?>" <?php echo $teacher['id'] == $profesor_id ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($teacher['apellidos'] . ', ' . $teacher['nombre']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>
<?php endif; ?>

<?php if (empty($horarios)): ?>
    <div class="alert" style="background-color: #fff3cd; color: #856404; border: 1px solid #ffeaa7;">
        No hay horarios asignados para este profesor en el curso actual.
    </div>
<?php else: ?>
    <div class="schedule-grid">
        <table class="schedule-table">
            <thead>
                <tr>
                    <th>Hora</th>
                    <?php foreach ($dias as $dia): ?>
                        <th><?php echo ucfirst($dia); ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($time_slots as $hora): ?>
                <tr>
                    <td class="time-slot"><strong><?php echo $hora; ?></strong></td>
                    <?php foreach ($dias as $dia): ?>
                        <td class="schedule-cell">
                            <?php if (isset($schedule[$dia][$hora])): 
                                $clase = $schedule[$dia][$hora];
                            ?>
                                <div class="class-block">
                                    <div class="class-module"><?php echo htmlspecialchars($clase['modulo']); ?></div>
                                    <div class="class-details">
                                        <span class="class-room">📍 <?php echo htmlspecialchars($clase['aula']); ?></span>
                                        <span class="class-group">👥 <?php echo htmlspecialchars($clase['grupo']); ?></span>
                                    </div>
                                    <div class="class-time">
                                        <?php echo substr($clase['hora_inicio'], 0, 5) . ' - ' . substr($clase['hora_fin'], 0, 5); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<div style="margin-top: 20px;">
    <a href="dashboard.php" class="btn">Volver al Dashboard</a>
</div>

<?php include 'includes/footer.php'; ?>
