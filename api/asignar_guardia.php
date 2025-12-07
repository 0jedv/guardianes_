<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Only admins can assign guardias
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['error' => 'Acceso denegado. Solo los administradores pueden asignar guardias.']);
    exit;
}

require_once '../conexion.php';

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$guardia_id = isset($data['guardia_id']) ? (int)$data['guardia_id'] : 0;
$profesor_id = isset($data['profesor_id']) ? (int)$data['profesor_id'] : 0;

if (!$guardia_id || !$profesor_id) {
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Get guardia details for the date
    $stmt = $pdo->prepare("SELECT fecha FROM guardias WHERE id = ?");
    $stmt->execute([$guardia_id]);
    $guardia = $stmt->fetch();
    
    if (!$guardia) {
        throw new Exception('Guardia no encontrada');
    }
    
    $año_mes = date('Y-m', strtotime($guardia['fecha']));
    
    // Insert assignment
    $sql = "INSERT INTO asignacion_guardias (guardia_id, profesor_id, fecha_asignacion, confirmada) 
            VALUES (?, ?, NOW(), 'si')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$guardia_id, $profesor_id]);
    
    // Update guardia status
    $sql = "UPDATE guardias SET estado = 'asignada' WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$guardia_id]);
    
    // Update or insert contador_guardias
    $sql = "INSERT INTO contador_guardias (profesor_id, año_mes, total_guardias)
            VALUES (?, ?, 1)
            ON DUPLICATE KEY UPDATE total_guardias = total_guardias + 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$profesor_id, $año_mes]);
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Guardia asignada correctamente'
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'error' => 'Error al asignar guardia: ' . $e->getMessage()
    ]);
}
