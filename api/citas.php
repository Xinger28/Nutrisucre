<?php
// ============================================================
//  api/citas.php  —  Agendar y listar citas
//  GET  -> citas del usuario actual (paciente ve las suyas,
//          nutricionista/admin ve todas)
//  POST -> agendar nueva cita
// ============================================================
session_start();
require_once __DIR__ . '/../config.php';

$metodo = $_SERVER['REQUEST_METHOD'];
$body   = json_decode(file_get_contents('php://input'), true) ?? [];

match($metodo) {
    'GET'  => listarCitas(),
    'POST' => crearCita($body),
    default => responderJSON(['error' => 'Metodo no permitido'], 405)
};

function listarCitas(): void {
    $usuario = requireAuth();
    $db      = getDB();

    if ($usuario['rol'] === 'Paciente') {
        // Paciente solo ve sus propias citas
        $stmt = $db->prepare("
            SELECT
                c.id, c.fecha,
                TIME_FORMAT(c.hora, '%H:%i') AS hora,
                c.precio, c.estado,
                u.nombre  AS nutricionista,
                n.especialidad
            FROM citas c
            JOIN nutricionistas n ON n.id = c.nutricionista_id
            JOIN usuarios u       ON u.id = n.usuario_id
            WHERE c.paciente_id = ?
            ORDER BY c.fecha DESC, c.hora DESC
        ");
        $stmt->execute([$usuario['id']]);
    } elseif ($usuario['rol'] === 'Nutricionista') {
        // Nutricionista solo ve citas agendadas con él
        $stmt = $db->prepare("
            SELECT
                c.id, c.fecha,
                TIME_FORMAT(c.hora, '%H:%i') AS hora,
                c.precio, c.estado,
                pac.nombre AS paciente,
                nut.nombre AS nutricionista,
                n.especialidad
            FROM citas c
            JOIN usuarios pac     ON pac.id = c.paciente_id
            JOIN nutricionistas n ON n.id   = c.nutricionista_id
            JOIN usuarios nut     ON nut.id = n.usuario_id
            WHERE n.usuario_id = ?
            ORDER BY c.fecha DESC, c.hora DESC
        ");
        $stmt->execute([$usuario['id']]);
    } else {
        // Admin ve todas
        $stmt = $db->query("
            SELECT
                c.id, c.fecha,
                TIME_FORMAT(c.hora, '%H:%i') AS hora,
                c.precio, c.estado,
                pac.nombre AS paciente,
                nut.nombre AS nutricionista,
                n.especialidad
            FROM citas c
            JOIN usuarios pac     ON pac.id = c.paciente_id
            JOIN nutricionistas n ON n.id   = c.nutricionista_id
            JOIN usuarios nut     ON nut.id = n.usuario_id
            ORDER BY c.fecha DESC, c.hora DESC
        ");
    }

    responderJSON($stmt->fetchAll());
}

function crearCita(array $body): void {
    $usuario = requireAuth();

    $nutricionistaId = intval($body['nutricionista_id'] ?? 0);
    $fecha           = $body['fecha'] ?? '';
    $hora            = $body['hora']  ?? '';

    if (!$nutricionistaId || !$fecha || !$hora) {
        responderJSON(['error' => 'Nutricionista, fecha y hora son obligatorios'], 400);
    }

    if ($fecha < date('Y-m-d')) {
        responderJSON(['error' => 'No puedes agendar citas en fechas pasadas'], 400);
    }

    $db = getDB();

    // Obtener precio del nutricionista
    $n = $db->prepare("SELECT precio FROM nutricionistas WHERE id = ?");
    $n->execute([$nutricionistaId]);
    $nutri = $n->fetch();

    if (!$nutri) {
        responderJSON(['error' => 'Nutricionista no encontrado'], 404);
    }

    // Verificar conflicto de horario
    $check = $db->prepare("
        SELECT id FROM citas
        WHERE nutricionista_id = ? AND fecha = ? AND hora = ? AND estado != 'cancelada'
    ");
    $check->execute([$nutricionistaId, $fecha, $hora . ':00']);
    if ($check->fetch()) {
        responderJSON(['error' => 'Ese horario ya esta ocupado, elige otro'], 409);
    }

    $stmt = $db->prepare("
        INSERT INTO citas (paciente_id, nutricionista_id, fecha, hora, precio, estado)
        VALUES (?, ?, ?, ?, ?, 'confirmada')
    ");
    $stmt->execute([
        $usuario['id'],
        $nutricionistaId,
        $fecha,
        $hora . ':00',
        $nutri['precio']
    ]);

    responderJSON(['ok' => true, 'mensaje' => 'Cita agendada correctamente']);
}
