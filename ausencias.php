<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
require_once 'conexion.php';
include 'includes/header.php';

// Handle Add Absence
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_absence'])) {
    $profesor_id = $_POST['profesor_id'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $motivo = $_POST['motivo'];
    $tipo = $_POST['tipo'];
    
    // Check permissions: teachers can only create absences for themselves
    $is_admin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    if (!$is_admin && $profesor_id != $_SESSION['user_id']) {
        die("Acceso denegado. Solo puedes registrar tus propias ausencias.");
    }
    
    $sql = "INSERT INTO ausencias (profesor_id, fecha_inicio, fecha_fin, motivo, tipo) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$profesor_id, $fecha_inicio, $fecha_fin, $motivo, $tipo]);

    
    // Get the inserted absence ID
    $ausencia_id = $pdo->lastInsertId();
    
    // Generate guardias for affected classes
    // Get all classes for this teacher between the absence dates
    $sql_horarios = "SELECT h.id, h.dia_semana, h.hora_inicio, h.hora_fin
                     FROM horario h
                     WHERE h.profesor_id = ? AND h.curso_escolar = '2024-2025'";
    $stmt_horarios = $pdo->prepare($sql_horarios);
    $stmt_horarios->execute([$profesor_id]);
    $horarios = $stmt_horarios->fetchAll();
    
    // Generate guardias for each day in the absence period
    $fecha_actual = new DateTime($fecha_inicio);
    $fecha_final = new DateTime($fecha_fin);
    
    $dias_semana = [
        1 => 'lunes',
        2 => 'martes',
        3 => 'miercoles',
        4 => 'jueves',
        5 => 'viernes'
    ];
    
    while ($fecha_actual <= $fecha_final) {
        $dia_num = (int)$fecha_actual->format('N'); // 1=Monday, 5=Friday
        
        // Only process weekdays
        if ($dia_num >= 1 && $dia_num <= 5) {
            $dia_nombre = $dias_semana[$dia_num];
            
            // Find classes for this day
            foreach ($horarios as $horario) {
                if ($horario['dia_semana'] == $dia_nombre) {
                    // Create guardia entry
                    $sql_guardia = "INSERT INTO guardias (horario_id, fecha, estado) VALUES (?, ?, 'pendiente')";
                    $stmt_guardia = $pdo->prepare($sql_guardia);
                    $stmt_guardia->execute([$horario['id'], $fecha_actual->format('Y-m-d')]);
                }
            }
        }
        
        $fecha_actual->modify('+1 day');
    }
    
    header("Location: ausencias.php?success=1");
    exit;
}

// Check if user is admin
$is_admin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

// Fetch Professors for dropdown
if ($is_admin) {
    // Admins can see all teachers
    $stmt = $pdo->query("SELECT id, nombre, apellidos FROM profesores WHERE estado = 'activo' ORDER BY apellidos");
    $profesores = $stmt->fetchAll();
} else {
    // Teachers only see themselves
    $stmt = $pdo->prepare("SELECT id, nombre, apellidos FROM profesores WHERE id = ? AND estado = 'activo'");
    $stmt->execute([$_SESSION['user_id']]);
    $profesores = $stmt->fetchAll();
}

// Fetch Absences
if ($is_admin) {
    // Admins see all absences
    $sql = "SELECT a.*, p.nombre, p.apellidos 
            FROM ausencias a
            JOIN profesores p ON a.profesor_id = p.id
            ORDER BY a.fecha_inicio DESC";
    $stmt = $pdo->query($sql);
} else {
    // Teachers only see their own absences
    $sql = "SELECT a.*, p.nombre, p.apellidos 
            FROM ausencias a
            JOIN profesores p ON a.profesor_id = p.id
            WHERE a.profesor_id = ?
            ORDER BY a.fecha_inicio DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
}
$ausencias = $stmt->fetchAll();
?>

<h1>Gestión de Ausencias</h1>

<?php if (isset($_GET['success'])): ?>
    <div class="alert" style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;">
        Ausencia registrada correctamente y guardias generadas.
    </div>
<?php endif; ?>

<div style="margin-bottom: 20px;">
    <h3>Registrar Ausencia</h3>
    <form method="POST" action="" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <input type="hidden" name="add_absence" value="1">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label>Profesor</label>
                <?php if ($is_admin): ?>
                    <select name="profesor_id" required style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="">Seleccione un profesor</option>
                        <?php foreach ($profesores as $profesor): ?>
                            <option value="<?php echo $profesor['id']; ?>">
                                <?php echo htmlspecialchars($profesor['apellidos'] . ', ' . $profesor['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <!-- Teachers can only register their own absences -->
                    <input type="hidden" name="profesor_id" value="<?php echo $_SESSION['user_id']; ?>">
                    <input type="text" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" disabled style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 4px; background-color: #f5f5f5;">
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label>Tipo</label>
                <select name="tipo" required style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="enfermedad">Enfermedad</option>
                    <option value="permiso">Permiso</option>
                    <option value="formacion">Formación</option>
                </select>
            </div>
            <div class="form-group">
                <label>Fecha Inicio</label>
                <input type="date" name="fecha_inicio" required>
            </div>
            <div class="form-group">
                <label>Fecha Fin</label>
                <input type="date" name="fecha_fin" required>
            </div>
            <div class="form-group" style="grid-column: span 2;">
                <label>Motivo</label>
                <input type="text" name="motivo">
            </div>
        </div>
        <button type="submit" class="btn">Registrar Ausencia</button>
    </form>
</div>

<h3>Historial de Ausencias</h3>
<table>
    <thead>
        <tr>
            <th>Profesor</th>
            <th>Desde</th>
            <th>Hasta</th>
            <th>Tipo</th>
            <th>Motivo</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($ausencias as $ausencia): ?>
        <tr>
            <td><?php echo htmlspecialchars($ausencia['apellidos'] . ', ' . $ausencia['nombre']); ?></td>
            <td><?php echo date('d/m/Y', strtotime($ausencia['fecha_inicio'])); ?></td>
            <td><?php echo date('d/m/Y', strtotime($ausencia['fecha_fin'])); ?></td>
            <td><?php echo ucfirst($ausencia['tipo']); ?></td>
            <td><?php echo htmlspecialchars($ausencia['motivo']); ?></td>
            <td>
                <span style="padding: 4px 8px; border-radius: 4px; background: <?php echo $ausencia['justificada'] == 'si' ? '#d4edda' : '#fff3cd'; ?>;">
                    <?php echo $ausencia['justificada'] == 'si' ? 'Justificada' : 'Pendiente'; ?>
                </span>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include 'includes/footer.php'; ?>
