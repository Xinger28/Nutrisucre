<?php
// ============================================================
//  api/solicitudes.php  —  Sprint 3: Gestión de Solicitudes
//
//  ENDPOINTS:
//  GET    /api/solicitudes.php              → listar solicitudes (según rol)
//  POST   /api/solicitudes.php              → crear solicitud (Paciente)
//  PUT    /api/solicitudes.php?accion=responder → aceptar/rechazar solicitud (Nutricionista)
// ============================================================
session_start();
require_once __DIR__ . '/../config.php';

$metodo = $_SERVER['REQUEST_METHOD'];
$accion = $_GET['accion'] ?? '';
$body   = json_decode(file_get_contents('php://input'), true) ?? [];

if ($metodo === 'PUT' && $accion === 'responder') {
    responderSolicitud($body);
    exit;
}

match($metodo) {
    'GET'  => listarSolicitudes(),
    'POST' => crearSolicitud($body),
    default => responderJSON(['error' => 'Método no permitido'], 405)
};

// ─────────────────────────────────────────
//  LISTAR solicitudes por rol
// ─────────────────────────────────────────
function listarSolicitudes(): void {
    $usuario = requireAuth();
    $db      = getDB();

    if ($usuario['rol'] === 'Administrador') {
        // Admin ve TODAS las solicitudes
        $stmt = $db->query("
            SELECT s.*,
                   srv.titulo AS servicio_titulo,
                   srv.categoria AS servicio_categoria,
                   srv.precio AS servicio_precio_actual,
                   srv.duracion_semanas AS servicio_duracion,
                   srv.modalidad AS servicio_modalidad,
                   u_pac.nombre AS paciente_nombre,
                   u_pac.email AS paciente_email,
                   u_nutri.nombre AS nutricionista_nombre
            FROM solicitudes s
            JOIN servicios srv ON srv.id = s.servicio_id
            JOIN usuarios u_pac ON u_pac.id = s.paciente_id
            JOIN usuarios u_nutri ON u_nutri.id = srv.nutricionista_id
            ORDER BY s.created_at DESC
        ");
        responderJSON($stmt->fetchAll());

    } elseif ($usuario['rol'] === 'Nutricionista') {
        // Nutricionista ve las solicitudes para sus servicios
        $stmt = $db->prepare("
            SELECT s.*,
                   srv.titulo AS servicio_titulo,
                   srv.categoria AS servicio_categoria,
                   srv.precio AS servicio_precio_actual,
                   srv.duracion_semanas AS servicio_duracion,
                   srv.modalidad AS servicio_modalidad,
                   u_pac.nombre AS paciente_nombre,
                   u_pac.email AS paciente_email
            FROM solicitudes s
            JOIN servicios srv ON srv.id = s.servicio_id
            JOIN usuarios u_pac ON u_pac.id = s.paciente_id
            WHERE srv.nutricionista_id = ?
            ORDER BY s.created_at DESC
        ");
        $stmt->execute([$usuario['id']]);
        responderJSON($stmt->fetchAll());

    } else {
        // Paciente ve sus propias solicitudes
        $stmt = $db->prepare("
            SELECT s.*,
                   srv.titulo AS servicio_titulo,
                   srv.categoria AS servicio_categoria,
                   srv.precio AS servicio_precio_actual,
                   srv.duracion_semanas AS servicio_duracion,
                   srv.modalidad AS servicio_modalidad,
                   u_nutri.nombre AS nutricionista_nombre,
                   u_nutri.email AS nutricionista_email
            FROM solicitudes s
            JOIN servicios srv ON srv.id = s.servicio_id
            JOIN usuarios u_nutri ON u_nutri.id = srv.nutricionista_id
            WHERE s.paciente_id = ?
            ORDER BY s.created_at DESC
        ");
        $stmt->execute([$usuario['id']]);
        responderJSON($stmt->fetchAll());
    }
}

// ─────────────────────────────────────────
//  CREAR solicitud (Paciente)
// ─────────────────────────────────────────
function crearSolicitud(array $body): void {
    $usuario = requireAuth();

    if ($usuario['rol'] !== 'Paciente') {
        responderJSON(['error' => 'Solo los pacientes pueden realizar solicitudes de servicios'], 403);
    }

    $servicioId        = intval($body['servicio_id'] ?? 0);
    $motivoConsulta    = trim($body['motivo_consulta'] ?? '');
    $pesoActual        = !empty($body['peso_actual']) ? floatval($body['peso_actual']) : null;
    $alturaActual      = !empty($body['altura_actual']) ? floatval($body['altura_actual']) : null;
    $condicionesMed   = trim($body['condiciones_medicas'] ?? '') ?: null;

    if (!$servicioId) {
        responderJSON(['error' => 'ID de servicio es requerido'], 400);
    }
    if (empty($motivoConsulta)) {
        responderJSON(['error' => 'El motivo de la consulta es obligatorio para evaluar la solicitud'], 400);
    }

    $db = getDB();

    // Validar existencia y estado del servicio
    $srvStmt = $db->prepare("SELECT precio, estado FROM servicios WHERE id = ?");
    $srvStmt->execute([$servicioId]);
    $servicio = $srvStmt->fetch();

    if (!$servicio) {
        responderJSON(['error' => 'Servicio no encontrado'], 404);
    }
    if ($servicio['estado'] !== 'Aprobado') {
        responderJSON(['error' => 'Este servicio no se encuentra activo para recibir solicitudes'], 400);
    }

    // Evitar solicitudes duplicadas pendientes para el mismo servicio
    $checkStmt = $db->prepare("SELECT id FROM solicitudes WHERE paciente_id = ? AND servicio_id = ? AND estado = 'Pendiente'");
    $checkStmt->execute([$usuario['id'], $servicioId]);
    if ($checkStmt->fetch()) {
        responderJSON(['error' => 'Ya tienes una solicitud pendiente para este servicio'], 400);
    }

    // Guardar
    $stmt = $db->prepare("
        INSERT INTO solicitudes 
            (paciente_id, servicio_id, precio_historico, motivo_consulta, 
             peso_actual, altura_actual, condiciones_medicas, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Pendiente')
    ");
    $stmt->execute([
        $usuario['id'],
        $servicioId,
        $servicio['precio'],
        $motivoConsulta,
        $pesoActual,
        $alturaActual,
        $condicionesMed
    ]);

    responderJSON([
        'ok'      => true,
        'id'      => $db->lastInsertId(),
        'mensaje' => 'Solicitud enviada correctamente. El especialista la evaluará a la brevedad.'
    ], 201);
}

// ─────────────────────────────────────────
//  RESPONDER solicitud (Nutricionista / Admin)
// ─────────────────────────────────────────
function responderSolicitud(array $body): void {
    $usuario = requireAuth();

    $id        = intval($body['id'] ?? 0);
    $estado    = $body['estado'] ?? '';
    $respuesta = trim($body['respuesta_ofertante'] ?? '');

    if (!$id) {
        responderJSON(['error' => 'ID de solicitud requerido'], 400);
    }
    if (!in_array($estado, ['Aceptada', 'Rechazada'])) {
        responderJSON(['error' => 'Estado inválido. Debe ser Aceptada o Rechazada'], 400);
    }
    if ($estado === 'Rechazada' && empty($respuesta)) {
        responderJSON(['error' => 'Debe indicar un motivo o respuesta al rechazar la solicitud'], 400);
    }

    $db = getDB();

    // Validar solicitud y propiedad
    $stmt = $db->prepare("
        SELECT s.*, srv.nutricionista_id 
        FROM solicitudes s
        JOIN servicios srv ON srv.id = s.servicio_id
        WHERE s.id = ?
    ");
    $stmt->execute([$id]);
    $solicitud = $stmt->fetch();

    if (!$solicitud) {
        responderJSON(['error' => 'Solicitud no encontrada'], 404);
    }

    // Validar que el nutricionista es dueño del servicio o es admin
    if ($usuario['rol'] !== 'Administrador' && $solicitud['nutricionista_id'] != $usuario['id']) {
        responderJSON(['error' => 'No tienes permiso para responder a esta solicitud'], 403);
    }

    // Actualizar solicitud
    $updateStmt = $db->prepare("
        UPDATE solicitudes
        SET estado = ?, respuesta_ofertante = ?
        WHERE id = ?
    ");
    $updateStmt->execute([
        $estado,
        $respuesta ?: null,
        $id
    ]);

    responderJSON([
        'ok'      => true,
        'estado'  => $estado,
        'mensaje' => "Solicitud " . ($estado === 'Aceptada' ? 'aceptada' : 'rechazada') . " correctamente."
    ]);
}
