<?php
// ============================================================
//  api/citas.php  —  Agendar y listar citas (Sprint 3)
//  GET  -> citas del usuario actual (según rol)
//  POST -> agendar nueva cita con pago
//  PUT  -> responder a cita (aceptar / rechazar)
// ============================================================
session_start();
require_once __DIR__ . '/../config.php';

$metodo = $_SERVER['REQUEST_METHOD'];
$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$accion = $_GET['accion'] ?? '';

if ($metodo === 'PUT' || ($metodo === 'POST' && $accion === 'responder')) {
    responderCita($body);
    exit;
}

match($metodo) {
    'GET'  => listarCitas(),
    'POST' => crearCita($body),
    default => responderJSON(['error' => 'Método no permitido'], 405)
};

function listarCitas(): void {
    $usuario = requireAuth();
    $db      = getDB();

    if ($usuario['rol'] === 'Paciente') {
        // Paciente ve sus propias citas y detalles del nutricionista
        $stmt = $db->prepare("
            SELECT
                c.id, c.fecha,
                TIME_FORMAT(c.hora, '%H:%i') AS hora,
                c.precio, c.estado, c.comprobante_pago, c.metodo_pago, c.motivo_rechazo,
                u.nombre  AS nutricionista,
                u.email AS nutricionista_email,
                n.telefono AS nutricionista_telefono,
                n.whatsapp AS nutricionista_whatsapp,
                n.especialidad,
                srv.titulo AS servicio_titulo
            FROM citas c
            JOIN nutricionistas n ON n.id = c.nutricionista_id
            JOIN usuarios u       ON u.id = n.usuario_id
            LEFT JOIN servicios srv ON srv.id = c.servicio_id
            WHERE c.paciente_id = ?
            ORDER BY c.fecha DESC, c.hora DESC
        ");
        $stmt->execute([$usuario['id']]);
    } elseif ($usuario['rol'] === 'Nutricionista') {
        // Nutricionista ve citas agendadas con él
        $stmt = $db->prepare("
            SELECT
                c.id, c.fecha,
                TIME_FORMAT(c.hora, '%H:%i') AS hora,
                c.precio, c.estado, c.comprobante_pago, c.metodo_pago, c.motivo_rechazo,
                pac.nombre AS paciente,
                pac.email AS paciente_email,
                pac.celular AS paciente_celular,
                nut.nombre AS nutricionista,
                n.especialidad,
                srv.titulo AS servicio_titulo
            FROM citas c
            JOIN usuarios pac     ON pac.id = c.paciente_id
            JOIN nutricionistas n ON n.id   = c.nutricionista_id
            JOIN usuarios nut     ON nut.id = n.usuario_id
            LEFT JOIN servicios srv ON srv.id = c.servicio_id
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
                c.precio, c.estado, c.comprobante_pago, c.metodo_pago, c.motivo_rechazo,
                pac.nombre AS paciente,
                pac.email AS paciente_email,
                pac.celular AS paciente_celular,
                nut.nombre AS nutricionista,
                n.especialidad,
                srv.titulo AS servicio_titulo
            FROM citas c
            JOIN usuarios pac     ON pac.id = c.paciente_id
            JOIN nutricionistas n ON n.id   = c.nutricionista_id
            JOIN usuarios nut     ON nut.id = n.usuario_id
            LEFT JOIN servicios srv ON srv.id = c.servicio_id
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
    $servicioId      = intval($body['servicio_id'] ?? 0);
    $metodoPago      = trim($body['metodo_pago'] ?? '');
    $comprobantePago = trim($body['comprobante_pago'] ?? '') ?: null;

    if (!$nutricionistaId || !$fecha || !$hora) {
        responderJSON(['error' => 'Nutricionista, fecha y hora son obligatorios'], 400);
    }

    if ($fecha < date('Y-m-d')) {
        responderJSON(['error' => 'No puedes agendar citas en fechas pasadas'], 400);
    }

    $db = getDB();

    // Obtener precio del servicio o del perfil profesional
    if ($servicioId) {
        $sStmt = $db->prepare("SELECT precio FROM servicios WHERE id = ?");
        $sStmt->execute([$servicioId]);
        $srv = $sStmt->fetch();
        $precio = $srv ? $srv['precio'] : 120.00;
    } else {
        $n = $db->prepare("SELECT precio FROM nutricionistas WHERE id = ?");
        $n->execute([$nutricionistaId]);
        $nutri = $n->fetch();
        $precio = $nutri ? $nutri['precio'] : 120.00;
    }

    // Verificar conflicto de horario
    $check = $db->prepare("
        SELECT id FROM citas
        WHERE nutricionista_id = ? AND fecha = ? AND hora = ? AND estado IN ('confirmada', 'pendiente_confirmacion')
    ");
    $check->execute([$nutricionistaId, $fecha, $hora . ':00']);
    if ($check->fetch()) {
        responderJSON(['error' => 'Ese horario ya está ocupado o pendiente de confirmación, elige otro'], 409);
    }

    // Insertar cita en estado 'pendiente_confirmacion'
    $stmt = $db->prepare("
        INSERT INTO citas (paciente_id, nutricionista_id, fecha, hora, precio, estado, servicio_id, metodo_pago, comprobante_pago)
        VALUES (?, ?, ?, ?, ?, 'pendiente_confirmacion', ?, ?, ?)
    ");
    $stmt->execute([
        $usuario['id'],
        $nutricionistaId,
        $fecha,
        $hora . ':00',
        $precio,
        $servicioId ?: null,
        $metodoPago ?: null,
        $comprobantePago
    ]);

    responderJSON(['ok' => true, 'mensaje' => 'Solicitud de reserva enviada. El especialista evaluará el pago y los detalles.']);
}

function responderCita(array $body): void {
    $usuario = requireAuth();
    $id      = intval($body['id'] ?? 0);
    $estado  = trim($body['estado'] ?? '');
    $motivo  = trim($body['motivo_rechazo'] ?? '');

    if (!$id || !in_array($estado, ['confirmada', 'rechazada', 'cancelada'])) {
        responderJSON(['error' => 'ID y estado válidos son requeridos'], 400);
    }
    if ($estado === 'rechazada' && empty($motivo)) {
        responderJSON(['error' => 'Debes ingresar el motivo de rechazo'], 400);
    }

    $db = getDB();

    // Obtener detalles de la cita
    $stmt = $db->prepare("
        SELECT c.*, pac.nombre AS paciente_nombre, pac.email AS paciente_email,
               nut.nombre AS nutricionista_nombre, n.usuario_id AS nutri_usuario_id
        FROM citas c
        JOIN usuarios pac     ON pac.id = c.paciente_id
        JOIN nutricionistas n ON n.id = c.nutricionista_id
        JOIN usuarios nut     ON nut.id = n.usuario_id
        WHERE c.id = ?
    ");
    $stmt->execute([$id]);
    $cita = $stmt->fetch();

    if (!$cita) {
        responderJSON(['error' => 'Cita no encontrada'], 404);
    }

    // El nutricionista del servicio o el administrador pueden responder
    if ($usuario['rol'] !== 'Administrador' && $cita['nutri_usuario_id'] != $usuario['id']) {
        // Un paciente solo puede CANCELAR su propia cita
        if ($estado === 'cancelada' && $cita['paciente_id'] == $usuario['id']) {
            // Permitir cancelación
        } else {
            responderJSON(['error' => 'No tienes permiso para modificar esta cita'], 403);
        }
    }

    $upd = $db->prepare("UPDATE citas SET estado = ?, motivo_rechazo = ? WHERE id = ?");
    $upd->execute([$estado, $estado === 'rechazada' ? $motivo : null, $id]);

    // Enviar correo de notificación (mock en log)
    if ($estado === 'confirmada') {
        $asunto = "Cita Confirmada - NutriSucre";
        $cuerpo = "Estimado/a " . $cita['paciente_nombre'] . ",\n\nNos complace informarle que su solicitud de cita para el " . $cita['fecha'] . " a las " . substr($cita['hora'], 0, 5) . " con el/la especialista " . $cita['nutricionista_nombre'] . " ha sido CONFIRMADA.\n\nEl horario queda reservado para usted. En caso de dudas, puede contactar al profesional.\n\nAtentamente,\nEl Equipo de NutriSucre";
        enviarCorreoMock($cita['paciente_email'], $asunto, $cuerpo);
    } elseif ($estado === 'rechazada') {
        $asunto = "Cita Rechazada - NutriSucre";
        $cuerpo = "Estimado/a " . $cita['paciente_nombre'] . ",\n\nLamentamos informarle que su solicitud de cita para el " . $cita['fecha'] . " a las " . substr($cita['hora'], 0, 5) . " con el/la especialista " . $cita['nutricionista_nombre'] . " ha sido rechazada.\n\nMotivo del rechazo:\n" . $motivo . "\n\nPor favor, intente agendar en otro horario o verifique el comprobante de pago enviado.\n\nAtentamente,\nEl Equipo de NutriSucre";
        enviarCorreoMock($cita['paciente_email'], $asunto, $cuerpo);
    }

    responderJSON(['ok' => true, 'mensaje' => 'Reserva actualizada correctamente.']);
}
