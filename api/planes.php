<?php
// ============================================================
//  api/planes.php  —  Planes nutricionales
//  GET  -> listar planes del paciente actual
//  POST -> nutricionista/admin crea un plan
//  PUT  -> actualizar plan
//  DELETE -> eliminar plan
// ============================================================
session_start();
require_once __DIR__ . '/../config.php';

$metodo = $_SERVER['REQUEST_METHOD'];
$body   = json_decode(file_get_contents('php://input'), true) ?? [];

match($metodo) {
    'GET'    => listar(),
    'POST'   => crear($body),
    'PUT'    => actualizar($body),
    'DELETE' => eliminar($body),
    default  => responderJSON(['error' => 'Metodo no permitido'], 405)
};

function listar(): void {
    $usuario    = requireAuth();
    $db         = getDB();
    $pacienteId = intval($_GET['paciente_id'] ?? $usuario['id']);

    // Solo nutricionistas/admin pueden ver planes de otros pacientes
    if ($pacienteId !== intval($usuario['id']) &&
        !in_array($usuario['rol'], ['Nutricionista','Administrador'])) {
        responderJSON(['error' => 'Acceso denegado'], 403);
    }

    $stmt = $db->prepare("
        SELECT p.id, p.titulo, p.descripcion, p.calorias, p.proteinas,
               p.carbohidratos, p.grasas, p.duracion_semanas,
               p.estado, p.fecha_inicio,
               u.nombre AS nutricionista
        FROM planes p
        JOIN nutricionistas n ON n.id = p.nutricionista_id
        JOIN usuarios u       ON u.id = n.usuario_id
        WHERE p.paciente_id = ?
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$pacienteId]);
    responderJSON($stmt->fetchAll());
}

function crear(array $body): void {
    $usuario = requireAuth();

    // Solo nutricionistas y admins pueden crear planes
    if (!in_array($usuario['rol'], ['Nutricionista','Administrador'])) {
        responderJSON(['error' => 'Solo nutricionistas pueden crear planes'], 403);
    }

    $pacienteId    = intval($body['paciente_id']      ?? 0);
    $titulo        = trim($body['titulo']             ?? '');
    $descripcion   = trim($body['descripcion']        ?? '');
    $calorias      = intval($body['calorias']         ?? 0)  ?: null;
    $proteinas     = intval($body['proteinas']        ?? 0)  ?: null;
    $carbohidratos = intval($body['carbohidratos']    ?? 0)  ?: null;
    $grasas        = intval($body['grasas']           ?? 0)  ?: null;
    $duracion      = intval($body['duracion_semanas'] ?? 4);
    $fechaInicio   = $body['fecha_inicio']            ?? date('Y-m-d');
    $estado        = $body['estado']                  ?? 'activo';

    if (!$pacienteId || !$titulo) {
        responderJSON(['error' => 'Paciente y titulo son obligatorios'], 400);
    }

    $db = getDB();

    // Obtener el id de la tabla nutricionistas (no el de usuarios)
    $nStmt = $db->prepare("SELECT id FROM nutricionistas WHERE usuario_id = ?");
    $nStmt->execute([$usuario['id']]);
    $nutri = $nStmt->fetch();

    // Admin puede crear planes en nombre de cualquier nutricionista
    if (!$nutri && $usuario['rol'] === 'Administrador') {
        $nutriId = intval($body['nutricionista_id'] ?? 1);
    } elseif (!$nutri) {
        responderJSON(['error' => 'Nutricionista no encontrado'], 404);
    } else {
        $nutriId = $nutri['id'];
    }

    $stmt = $db->prepare("
        INSERT INTO planes
          (paciente_id, nutricionista_id, titulo, descripcion, calorias,
           proteinas, carbohidratos, grasas, duracion_semanas, estado, fecha_inicio)
        VALUES (?,?,?,?,?,?,?,?,?,?,?)
    ");
    $stmt->execute([$pacienteId, $nutriId, $titulo, $descripcion ?: null,
                    $calorias, $proteinas, $carbohidratos, $grasas,
                    $duracion, $estado, $fechaInicio]);

    responderJSON(['ok' => true, 'id' => $db->lastInsertId(), 'mensaje' => 'Plan creado']);
}

function actualizar(array $body): void {
    $usuario = requireAuth();
    if (!in_array($usuario['rol'], ['Nutricionista','Administrador'])) {
        responderJSON(['error' => 'Sin permiso'], 403);
    }

    $id     = intval($body['id'] ?? 0);
    $estado = $body['estado'] ?? null;
    if (!$id) responderJSON(['error' => 'ID requerido'], 400);

    $db = getDB();

    if ($estado) {
        $stmt = $db->prepare("UPDATE planes SET estado=? WHERE id=?");
        $stmt->execute([$estado, $id]);
    }
    responderJSON(['ok' => true, 'mensaje' => 'Plan actualizado']);
}

function eliminar(array $body): void {
    requireAdmin();
    $id   = intval($body['id'] ?? 0);
    $db   = getDB();
    $stmt = $db->prepare("DELETE FROM planes WHERE id=?");
    $stmt->execute([$id]);
    responderJSON(['ok' => true, 'mensaje' => 'Plan eliminado']);
}
