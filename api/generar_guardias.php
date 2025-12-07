<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Only admins can generate guardias
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['error' => 'Acceso denegado. Solo los administradores pueden generar guardias.']);
    exit;
}

require_once '../conexion.php';

$guardia_id = isset($_GET['guardia_id']) ? (int)$_GET['guardia_id'] : 0;

if (!$guardia_id) {
    echo json_encode(['error' => 'ID de guardia no proporcionado']);
    exit;
}

// Get guardia details
$sql = "SELECT g.*, h.dia_semana, h.hora_inicio, h.hora_fin, h.modulo_id, h.profesor_id,
        a.nombre as aula, m.nombre as modulo, p.nombre as profesor_nombre, p.apellidos as profesor_apellidos
        FROM guardias g
        JOIN horario h ON g.horario_id = h.id
        LEFT JOIN aulas a ON h.aula_id = a.id
        LEFT JOIN modulos m ON h.modulo_id = m.id
        LEFT JOIN profesores p ON h.profesor_id = p.id
        WHERE g.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$guardia_id]);
$guardia = $stmt->fetch();

if (!$guardia) {
    echo json_encode(['error' => 'Guardia no encontrada']);
    exit;
}

// Get current year-month for guardia counter
$año_mes = date('Y-m', strtotime($guardia['fecha']));

// Find available substitute teachers
// 1. Get all active teachers except the absent one
$sql_teachers = "SELECT p.id, p.nombre, p.apellidos, p.departamento
                 FROM profesores p
                 WHERE p.estado = 'activo' AND p.id != ?
                 ORDER BY p.apellidos, p.nombre";
$stmt_teachers = $pdo->prepare($sql_teachers);
$stmt_teachers->execute([$guardia['profesor_id']]);
$all_teachers = $stmt_teachers->fetchAll();

$suggestions = [];

foreach ($all_teachers as $teacher) {
    $teacher_id = $teacher['id'];
    
    // Check if teacher is available (not teaching at this time on this day)
    $sql_availability = "SELECT COUNT(*) as count
                         FROM horario h
                         WHERE h.profesor_id = ?
                         AND h.dia_semana = ?
                         AND h.curso_escolar = '2024-2025'
                         AND (
                             (h.hora_inicio <= ? AND h.hora_fin > ?) OR
                             (h.hora_inicio < ? AND h.hora_fin >= ?)
                         )";
    $stmt_availability = $pdo->prepare($sql_availability);
    $stmt_availability->execute([
        $teacher_id,
        $guardia['dia_semana'],
        $guardia['hora_inicio'],
        $guardia['hora_inicio'],
        $guardia['hora_fin'],
        $guardia['hora_fin']
    ]);
    $availability = $stmt_availability->fetch();
    $is_available = ($availability['count'] == 0);
    
    // Check qualification for the subject
    $sql_qualification = "SELECT preferencia
                          FROM profesor_modulo
                          WHERE profesor_id = ? AND modulo_id = ?";
    $stmt_qualification = $pdo->prepare($sql_qualification);
    $stmt_qualification->execute([$teacher_id, $guardia['modulo_id']]);
    $qualification = $stmt_qualification->fetch();
    
    $qualification_level = 'none';
    $qualification_score = 0;
    if ($qualification) {
        $qualification_level = $qualification['preferencia'];
        $qualification_score = ($qualification_level == 'alta') ? 3 : (($qualification_level == 'media') ? 2 : 1);
    }
    
    // Get guardia count for this month
    $sql_count = "SELECT COALESCE(total_guardias, 0) as total
                  FROM contador_guardias
                  WHERE profesor_id = ? AND año_mes = ?";
    $stmt_count = $pdo->prepare($sql_count);
    $stmt_count->execute([$teacher_id, $año_mes]);
    $count_result = $stmt_count->fetch();
    $guardia_count = $count_result ? $count_result['total'] : 0;
    
    // Calculate priority score
    // Higher score = better candidate
    $priority_score = 0;
    if ($is_available) {
        $priority_score += 100; // Availability is most important
    }
    $priority_score += $qualification_score * 10; // Qualification is second
    $priority_score -= $guardia_count; // Fewer guardias = higher priority
    
    $suggestions[] = [
        'id' => $teacher_id,
        'nombre' => $teacher['nombre'],
        'apellidos' => $teacher['apellidos'],
        'departamento' => $teacher['departamento'],
        'available' => $is_available,
        'qualified' => $qualification_level,
        'guardia_count' => $guardia_count,
        'priority_score' => $priority_score
    ];
}

// Sort by priority score (descending)
usort($suggestions, function($a, $b) {
    return $b['priority_score'] - $a['priority_score'];
});

echo json_encode([
    'success' => true,
    'guardia' => [
        'id' => $guardia['id'],
        'fecha' => date('d/m/Y', strtotime($guardia['fecha'])),
        'dia' => ucfirst($guardia['dia_semana']),
        'horario' => substr($guardia['hora_inicio'], 0, 5) . ' - ' . substr($guardia['hora_fin'], 0, 5),
        'aula' => $guardia['aula'],
        'modulo' => $guardia['modulo'],
        'profesor_ausente' => $guardia['profesor_apellidos'] . ', ' . $guardia['profesor_nombre']
    ],
    'suggestions' => $suggestions
]);
