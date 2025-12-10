<?php
/**
 * API REST para el sistema Guardianes
 * Separa completamente el frontend del backend
 * Todas las respuestas son en formato JSON
 */

// Configuración de headers para API REST
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Iniciar sesión
session_start();

// Incluir conexión a base de datos
require_once 'conexion.php';

// Función para enviar respuesta JSON
function sendResponse($success, $data = null, $message = '', $httpCode = 200)
{
    http_response_code($httpCode);
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Función para validar sesión
function validateSession()
{
    if (!isset($_SESSION['user_id'])) {
        sendResponse(false, null, 'No autorizado. Debes iniciar sesión.', 401);
    }
}

// Función para validar que el usuario es admin
function validateAdmin()
{
    validateSession();
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        sendResponse(false, null, 'Acceso denegado. Solo administradores.', 403);
    }
}

// Obtener acción del request
$action = $_GET['action'] ?? $_POST['action'] ?? null;

if (!$action) {
    sendResponse(false, null, 'Acción no especificada', 400);
}

try {
    switch ($action) {

        // ==================== PROFESORES ====================

        case 'getProfesores':
            validateSession();

            $stmt = $pdo->query("SELECT id, nombre, apellidos, email, rol, estado 
                                 FROM profesores 
                                 WHERE estado = 'activo' 
                                 ORDER BY apellidos, nombre");
            $profesores = $stmt->fetchAll(PDO::FETCH_ASSOC);

            sendResponse(true, $profesores, 'Profesores obtenidos correctamente');
            break;

        case 'createProfesor':
            validateAdmin(); // Solo admin puede crear profesores

            // Obtener datos del body JSON
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                sendResponse(false, null, 'Datos inválidos', 400);
            }

            $nombre = $input['nombre'] ?? null;
            $apellidos = $input['apellidos'] ?? null;
            $email = $input['email'] ?? null;
            $password = $input['password'] ?? null;
            $rol = $input['rol'] ?? 'profesor';

            // Validaciones
            if (!$nombre || !$apellidos || !$email || !$password) {
                sendResponse(false, null, 'Faltan campos obligatorios: nombre, apellidos, email, password', 400);
            }

            // Validar email único
            $stmt = $pdo->prepare("SELECT id FROM profesores WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                sendResponse(false, null, 'El email ya está registrado', 409);
            }

            // Hash de la contraseña
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            // Insertar profesor
            $sql = "INSERT INTO profesores (nombre, apellidos, email, password, rol, estado) 
                    VALUES (?, ?, ?, ?, ?, 'activo')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $apellidos, $email, $passwordHash, $rol]);

            $newId = $pdo->lastInsertId();

            sendResponse(true, ['id' => $newId], 'Profesor creado correctamente', 201);
            break;

        case 'updateProfesor':
            validateAdmin();

            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['id'])) {
                sendResponse(false, null, 'Datos inválidos', 400);
            }

            $id = $input['id'];
            $nombre = $input['nombre'] ?? null;
            $apellidos = $input['apellidos'] ?? null;
            $email = $input['email'] ?? null;
            $rol = $input['rol'] ?? null;

            // Password update is optional
            $password = $input['password'] ?? null;

            if (!$nombre || !$apellidos || !$email || !$rol) {
                sendResponse(false, null, 'Faltan campos obligatorios', 400);
            }

            // Check if email belongs to another user
            $stmt = $pdo->prepare("SELECT id FROM profesores WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                sendResponse(false, null, 'El email ya está registrado por otro usuario', 409);
            }

            if ($password) {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE profesores SET nombre=?, apellidos=?, email=?, rol=?, password=? WHERE id=?";
                $params = [$nombre, $apellidos, $email, $rol, $passwordHash, $id];
            } else {
                $sql = "UPDATE profesores SET nombre=?, apellidos=?, email=?, rol=? WHERE id=?";
                $params = [$nombre, $apellidos, $email, $rol, $id];
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            sendResponse(true, null, 'Profesor actualizado correctamente');
            break;

        case 'deleteProfesor':
            validateAdmin();
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['id'])) {
                sendResponse(false, null, 'ID no proporcionado', 400);
            }
            // Check for related records (constraints usually prevent deletion, but we can set state to inactive instead or delete)
            // The prompt asked for "eliminar", typically "DELETE". 
            // If there are FK constraints, we might need soft delete. 
            // Let's force delete for now as per "eliminar" request, or maybe soft delete 'estado' = 'inactivo'.
            // However, usually deleting a user completely might break history. 
            // The prompt says "delete para borrar". So I will try DELETE.

            try {
                $stmt = $pdo->prepare("DELETE FROM profesores WHERE id = ?");
                $stmt->execute([$input['id']]);
                sendResponse(true, null, 'Profesor eliminado correctamente');
            } catch (PDOException $e) {
                // If deletion fails (likely due to FK), suggest soft delete or handle error
                sendResponse(false, null, 'No se puede eliminar el profesor porque tiene registros asociados. Intenta desactivarlo.', 409);
            }
            break;

        // ==================== AUSENCIAS ====================

        case 'getAusencias':
            validateSession();

            $isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

            if ($isAdmin) {
                // Admin ve todas las ausencias
                $sql = "SELECT a.*, p.nombre, p.apellidos 
                        FROM ausencias a
                        JOIN profesores p ON a.profesor_id = p.id
                        ORDER BY a.fecha_inicio DESC";
                $stmt = $pdo->query($sql);
            } else {
                // Profesor solo ve sus ausencias
                $sql = "SELECT a.*, p.nombre, p.apellidos 
                        FROM ausencias a
                        JOIN profesores p ON a.profesor_id = p.id
                        WHERE a.profesor_id = ?
                        ORDER BY a.fecha_inicio DESC";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_SESSION['user_id']]);
            }

            $ausencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

            sendResponse(true, $ausencias, 'Ausencias obtenidas correctamente');
            break;

        case 'createAusencia':
            validateSession();

            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                sendResponse(false, null, 'Datos inválidos', 400);
            }

            $profesor_id = $input['profesor_id'] ?? null;
            $fecha_inicio = $input['fecha_inicio'] ?? null;
            $fecha_fin = $input['fecha_fin'] ?? null;
            $motivo = $input['motivo'] ?? '';
            $tipo = $input['tipo'] ?? 'enfermedad';

            // Validaciones
            if (!$profesor_id || !$fecha_inicio || !$fecha_fin) {
                sendResponse(false, null, 'Faltan campos obligatorios', 400);
            }

            // Validar permisos: profesores solo pueden crear sus propias ausencias
            $isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
            if (!$isAdmin && $profesor_id != $_SESSION['user_id']) {
                sendResponse(false, null, 'Solo puedes registrar tus propias ausencias', 403);
            }

            // Insertar ausencia
            $sql = "INSERT INTO ausencias (profesor_id, fecha_inicio, fecha_fin, motivo, tipo) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$profesor_id, $fecha_inicio, $fecha_fin, $motivo, $tipo]);

            $ausencia_id = $pdo->lastInsertId();

            // Generar guardias automáticamente
            $sql_horarios = "SELECT h.id, h.dia_semana, h.hora_inicio, h.hora_fin
                             FROM horario h
                             WHERE h.profesor_id = ? AND h.curso_escolar = '2024-2025'";
            $stmt_horarios = $pdo->prepare($sql_horarios);
            $stmt_horarios->execute([$profesor_id]);
            $horarios = $stmt_horarios->fetchAll();

            $fecha_actual = new DateTime($fecha_inicio);
            $fecha_final = new DateTime($fecha_fin);

            $dias_semana = [
                1 => 'lunes',
                2 => 'martes',
                3 => 'miercoles',
                4 => 'jueves',
                5 => 'viernes'
            ];

            $guardias_creadas = 0;

            while ($fecha_actual <= $fecha_final) {
                $dia_num = (int) $fecha_actual->format('N');

                if ($dia_num >= 1 && $dia_num <= 5) {
                    $dia_nombre = $dias_semana[$dia_num];

                    foreach ($horarios as $horario) {
                        if ($horario['dia_semana'] == $dia_nombre) {
                            $sql_guardia = "INSERT INTO guardias (horario_id, fecha, estado) 
                                           VALUES (?, ?, 'pendiente')";
                            $stmt_guardia = $pdo->prepare($sql_guardia);
                            $stmt_guardia->execute([$horario['id'], $fecha_actual->format('Y-m-d')]);
                            $guardias_creadas++;
                        }
                    }
                }

                $fecha_actual->modify('+1 day');
            }

            sendResponse(true, [
                'ausencia_id' => $ausencia_id,
                'guardias_creadas' => $guardias_creadas
            ], 'Ausencia registrada y guardias generadas correctamente', 201);
            break;

        case 'updateAusencia':
            validateAdmin(); // Or owner? Prompt says admin. Let's restrict to admin for now as per request "rol de admin".

            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['id'])) {
                sendResponse(false, null, 'Datos inválidos', 400);
            }

            $id = $input['id'];
            $fecha_inicio = $input['fecha_inicio'] ?? null;
            $fecha_fin = $input['fecha_fin'] ?? null;
            $motivo = $input['motivo'] ?? '';
            $tipo = $input['tipo'] ?? 'enfermedad';
            $justificada = $input['justificada'] ?? 'no';

            if (!$fecha_inicio || !$fecha_fin) {
                sendResponse(false, null, 'Faltan campos obligatorios', 400);
            }

            $sql = "UPDATE ausencias SET fecha_inicio=?, fecha_fin=?, motivo=?, tipo=?, justificada=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$fecha_inicio, $fecha_fin, $motivo, $tipo, $justificada, $id]);

            sendResponse(true, null, 'Ausencia actualizada correctamente');
            break;

        case 'deleteAusencia':
            validateAdmin();
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['id'])) {
                sendResponse(false, null, 'ID no proporcionado', 400);
            }

            // Also delete related guardias? Or let database handle cascade?
            // Assuming database might not have cascade, let's just delete absence.

            $stmt = $pdo->prepare("DELETE FROM ausencias WHERE id = ?");
            $stmt->execute([$input['id']]);

            sendResponse(true, null, 'Ausencia eliminada correctamente');
            break;

        // ==================== GUARDIAS ====================

        case 'getGuardias':
            validateSession();

            $isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

            // Obtener guardias pendientes con información completa
            $sql = "SELECT 
                        g.id as guardia_id,
                        g.fecha,
                        g.estado,
                        g.profesor_sustituto_id,
                        h.dia_semana,
                        h.hora_inicio,
                        h.hora_fin,
                        h.grupo,
                        m.nombre as modulo_nombre,
                        p_ausente.id as profesor_ausente_id,
                        p_ausente.nombre as profesor_ausente_nombre,
                        p_ausente.apellidos as profesor_ausente_apellidos,
                        p_sustituto.nombre as sustituto_nombre,
                        p_sustituto.apellidos as sustituto_apellidos
                    FROM guardias g
                    JOIN horario h ON g.horario_id = h.id
                    JOIN profesores p_ausente ON h.profesor_id = p_ausente.id
                    LEFT JOIN modulos m ON h.modulo_id = m.id
                    LEFT JOIN profesores p_sustituto ON g.profesor_sustituto_id = p_sustituto.id
                    WHERE g.fecha >= CURDATE()
                    ORDER BY g.fecha, h.hora_inicio";

            $stmt = $pdo->query($sql);
            $guardias = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Obtener profesores disponibles para sustituciones
            $sql_disponibles = "SELECT id, nombre, apellidos 
                               FROM profesores 
                               WHERE estado = 'activo' 
                               ORDER BY apellidos";
            $stmt_disponibles = $pdo->query($sql_disponibles);
            $profesores_disponibles = $stmt_disponibles->fetchAll(PDO::FETCH_ASSOC);

            sendResponse(true, [
                'guardias' => $guardias,
                'profesores_disponibles' => $profesores_disponibles
            ], 'Guardias obtenidas correctamente');
            break;

        case 'asignarGuardia':
            validateAdmin(); // Solo admin puede asignar guardias

            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                sendResponse(false, null, 'Datos inválidos', 400);
            }

            $guardia_id = $input['guardia_id'] ?? null;
            $profesor_sustituto_id = $input['profesor_sustituto_id'] ?? null;

            if (!$guardia_id || !$profesor_sustituto_id) {
                sendResponse(false, null, 'Faltan campos obligatorios', 400);
            }

            // Verificar que la guardia existe y está pendiente
            $stmt = $pdo->prepare("SELECT id, estado FROM guardias WHERE id = ?");
            $stmt->execute([$guardia_id]);
            $guardia = $stmt->fetch();

            if (!$guardia) {
                sendResponse(false, null, 'Guardia no encontrada', 404);
            }

            // Actualizar guardia
            $sql = "UPDATE guardias 
                    SET profesor_sustituto_id = ?, estado = 'asignada' 
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$profesor_sustituto_id, $guardia_id]);

            sendResponse(true, null, 'Guardia asignada correctamente');
            break;

        case 'updateGuardia':
            validateAdmin();
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['id'])) {
                sendResponse(false, null, 'Datos inválidos', 400);
            }

            $id = $input['id'];
            $estado = $input['estado'] ?? 'pendiente';
            $profesor_sustituto_id = $input['profesor_sustituto_id'] ?? null;

            // If empty string or 'null', set to NULL
            if ($profesor_sustituto_id === '' || $profesor_sustituto_id === 'null') {
                $profesor_sustituto_id = null;
            }

            $sql = "UPDATE guardias SET estado=?, profesor_sustituto_id=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$estado, $profesor_sustituto_id, $id]);

            sendResponse(true, null, 'Guardia actualizada correctamente');
            break;

        case 'deleteGuardia':
            validateAdmin();
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['id'])) {
                sendResponse(false, null, 'ID no proporcionado', 400);
            }

            $stmt = $pdo->prepare("DELETE FROM guardias WHERE id = ?");
            $stmt->execute([$input['id']]);
            sendResponse(true, null, 'Guardia eliminada correctamente');
            break;

        // ==================== SESIÓN ====================

        case 'login':
            // Obtener datos del body JSON
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                sendResponse(false, null, 'Datos inválidos', 400);
            }

            $email = $input['email'] ?? null;
            $password = $input['password'] ?? null;

            if (!$email || !$password) {
                sendResponse(false, null, 'Email y contraseña son obligatorios', 400);
            }

            // Buscar usuario
            $stmt = $pdo->prepare("SELECT * FROM profesores WHERE email = ? AND estado = 'activo'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nombre'] . ' ' . $user['apellidos'];
                $_SESSION['user_role'] = $user['rol'];

                sendResponse(true, [
                    'user_id' => $user['id'],
                    'user_name' => $_SESSION['user_name'],
                    'user_role' => $user['rol'],
                    'is_admin' => $user['rol'] === 'admin'
                ], 'Login exitoso', 200);
            } else {
                sendResponse(false, null, 'Email o contraseña incorrectos', 401);
            }
            break;

        case 'getSession':
            validateSession();

            sendResponse(true, [
                'user_id' => $_SESSION['user_id'],
                'user_name' => $_SESSION['user_name'] ?? '',
                'user_role' => $_SESSION['user_role'] ?? 'profesor',
                'is_admin' => ($_SESSION['user_role'] ?? '') === 'admin'
            ], 'Sesión obtenida correctamente');
            break;

        case 'getDashboardStats':
            validateSession();

            // Obtener estadísticas
            $stmt = $pdo->query("SELECT COUNT(*) FROM profesores WHERE estado = 'activo'");
            $totalProfesores = $stmt->fetchColumn();

            $stmt = $pdo->query("SELECT COUNT(*) FROM guardias WHERE fecha = CURDATE()");
            $guardiasHoy = $stmt->fetchColumn();

            $stmt = $pdo->query("SELECT COUNT(*) FROM ausencias WHERE CURDATE() BETWEEN fecha_inicio AND fecha_fin");
            $ausenciasHoy = $stmt->fetchColumn();

            sendResponse(true, [
                'total_profesores' => $totalProfesores,
                'guardias_hoy' => $guardiasHoy,
                'ausencias_hoy' => $ausenciasHoy,
                'user_name' => $_SESSION['user_name'] ?? '',
                'user_role' => $_SESSION['user_role'] ?? 'profesor'
            ], 'Estadísticas obtenidas correctamente');
            break;

        case 'getHorarioProfesor':
            validateSession();

            // Obtener profesor_id del query string o usar el del usuario logueado
            $profesor_id = $_GET['profesor_id'] ?? $_SESSION['user_id'];

            // Solo admins pueden ver horarios de otros profesores
            if ($profesor_id != $_SESSION['user_id'] && (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin')) {
                sendResponse(false, null, 'Acceso denegado. Solo puedes ver tu propio horario.', 403);
            }

            // Obtener información del profesor
            $stmt = $pdo->prepare("SELECT nombre, apellidos, departamento FROM profesores WHERE id = ?");
            $stmt->execute([$profesor_id]);
            $profesor = $stmt->fetch();

            if (!$profesor) {
                sendResponse(false, null, 'Profesor no encontrado', 404);
            }

            // Obtener horario
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
            $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Obtener todos los profesores si es admin
            $all_teachers = [];
            if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
                $stmt = $pdo->query("SELECT id, nombre, apellidos FROM profesores WHERE estado = 'activo' ORDER BY apellidos, nombre");
                $all_teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            sendResponse(true, [
                'profesor' => $profesor,
                'horarios' => $horarios,
                'all_teachers' => $all_teachers,
                'is_admin' => isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'
            ], 'Horario obtenido correctamente');
            break;

        default:
            sendResponse(false, null, 'Acción no válida', 400);
    }

} catch (PDOException $e) {
    sendResponse(false, null, 'Error de base de datos: ' . $e->getMessage(), 500);
} catch (Exception $e) {
    sendResponse(false, null, 'Error del servidor: ' . $e->getMessage(), 500);
}
