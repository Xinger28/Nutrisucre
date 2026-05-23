<?php
// ============================================================
//  api/pdf_plan.php  —  Genera PDF del plan nutricional
//  GET ?id=X  -> devuelve el plan como JSON para que JS lo imprima
//  (El PDF se genera en el navegador con jsPDF)
// ============================================================
session_start();
require_once __DIR__ . '/../config.php';

$usuario = requireAuth();
$id      = intval($_GET['id'] ?? 0);

if (!$id) responderJSON(['error' => 'ID de plan requerido'], 400);

$db   = getDB();
$stmt = $db->prepare("
    SELECT
        p.id, p.titulo, p.descripcion, p.calorias, p.proteinas,
        p.carbohidratos, p.grasas, p.duracion_semanas,
        p.estado, p.fecha_inicio, p.paciente_id,
        pac.nombre AS paciente,
        nut.nombre AS nutricionista,
        n.especialidad
    FROM planes p
    JOIN usuarios pac     ON pac.id = p.paciente_id
    JOIN nutricionistas n ON n.id   = p.nutricionista_id
    JOIN usuarios nut     ON nut.id = n.usuario_id
    WHERE p.id = ?
");
$stmt->execute([$id]);
$plan = $stmt->fetch();

if (!$plan) responderJSON(['error' => 'Plan no encontrado'], 404);

// Verificar que el usuario tiene acceso a este plan
$esPropio  = intval($plan['paciente_id']) === intval($usuario['id']);
$esProfesional = in_array($usuario['rol'], ['Nutricionista','Administrador']);

if (!$esPropio && !$esProfesional) {
    responderJSON(['error' => 'Acceso denegado'], 403);
}

responderJSON($plan);
