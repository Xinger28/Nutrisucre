<?php
// ============================================================
//  api/seguimiento.php  —  Registros de progreso del paciente
//  GET  -> historial del paciente actual
//  POST -> guardar nuevo registro
//  DELETE -> eliminar registro
// ============================================================
session_start();
require_once __DIR__ . '/../config.php';

$metodo = $_SERVER['REQUEST_METHOD'];
$body   = json_decode(file_get_contents('php://input'), true) ?? [];

match($metodo) {
    'GET'    => obtener(),
    'POST'   => guardar($body),
    'DELETE' => eliminar($body),
    default  => responderJSON(['error' => 'Metodo no permitido'], 405)
};

function obtener(): void {
    $usuario    = requireAuth();
    $db         = getDB();
    $pacienteId = intval($_GET['paciente_id'] ?? $usuario['id']);

    if ($pacienteId !== intval($usuario['id']) &&
        !in_array($usuario['rol'], ['Nutricionista','Administrador'])) {
        responderJSON(['error' => 'Acceso denegado'], 403);
    }

    $stmt = $db->prepare("
        SELECT id, fecha, peso, cintura, cadera, grasa, nota
        FROM seguimiento
        WHERE paciente_id = ?
        ORDER BY fecha ASC
    ");
    $stmt->execute([$pacienteId]);
    responderJSON($stmt->fetchAll());
}

function guardar(array $body): void {
    $usuario = requireAuth();

    $fecha   = $body['fecha']   ?? date('Y-m-d');
    $peso    = isset($body['peso'])    && $body['peso']    !== '' ? floatval($body['peso'])    : null;
    $cintura = isset($body['cintura']) && $body['cintura'] !== '' ? floatval($body['cintura']) : null;
    $cadera  = isset($body['cadera'])  && $body['cadera']  !== '' ? floatval($body['cadera'])  : null;
    $grasa   = isset($body['grasa'])   && $body['grasa']   !== '' ? floatval($body['grasa'])   : null;
    $nota    = trim($body['nota'] ?? '');

    if ($peso === null && $cintura === null && $cadera === null && $grasa === null) {
        responderJSON(['error' => 'Ingresa al menos una medida'], 400);
    }

    $db = getDB();

    $existe = $db->prepare("SELECT id FROM seguimiento WHERE paciente_id = ? AND fecha = ?");
    $existe->execute([$usuario['id'], $fecha]);
    $reg = $existe->fetch();

    if ($reg) {
        $stmt = $db->prepare("
            UPDATE seguimiento
            SET peso=COALESCE(?,peso), cintura=COALESCE(?,cintura),
                cadera=COALESCE(?,cadera), grasa=COALESCE(?,grasa), nota=?
            WHERE id=?
        ");
        $stmt->execute([$peso, $cintura, $cadera, $grasa, $nota ?: null, $reg['id']]);
        responderJSON(['ok' => true, 'mensaje' => 'Registro actualizado', 'accion' => 'update']);
    } else {
        $stmt = $db->prepare("
            INSERT INTO seguimiento (paciente_id, fecha, peso, cintura, cadera, grasa, nota)
            VALUES (?,?,?,?,?,?,?)
        ");
        $stmt->execute([$usuario['id'], $fecha, $peso, $cintura, $cadera, $grasa, $nota ?: null]);
        responderJSON(['ok' => true, 'mensaje' => 'Registro guardado', 'accion' => 'insert', 'id' => $db->lastInsertId()]);
    }
}

function eliminar(array $body): void {
    $usuario = requireAuth();
    $id      = intval($body['id'] ?? 0);
    if (!$id) responderJSON(['error' => 'ID invalido'], 400);

    $db   = getDB();
    $stmt = $db->prepare("DELETE FROM seguimiento WHERE id=? AND (paciente_id=? OR ?='Administrador')");
    $stmt->execute([$id, $usuario['id'], $usuario['rol']]);
    responderJSON(['ok' => true, 'mensaje' => 'Registro eliminado']);
}
