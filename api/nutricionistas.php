<?php
// ============================================================
//  api/nutricionistas.php
//  Solo muestra nutricionistas APROBADOS
//  Filtros: nombre, precio_max, precio_min, rating_min, especialidad
//  GET ?id=X  → detalle completo de uno
// ============================================================
session_start();
require_once __DIR__ . '/../config.php';

requireAuth();

$db  = getDB();
$id  = intval($_GET['id'] ?? 0);

// ─── Detalle de un nutricionista ─────────────────────────────
if ($id) {
    $stmt = $db->prepare("
        SELECT n.*, u.nombre, u.email,
               (SELECT COUNT(*) FROM resenas r WHERE r.nutricionista_id = n.id) AS total_resenas
        FROM nutricionistas n
        JOIN usuarios u ON u.id = n.usuario_id
        WHERE n.id = ? AND n.estado_verificacion = 'aprobado'
    ");
    $stmt->execute([$id]);
    $nutri = $stmt->fetch();
    if (!$nutri) responderJSON(['error' => 'No encontrado'], 404);
    responderJSON($nutri);
}

// ─── Listado con filtros ──────────────────────────────────────
$where  = ["n.estado_verificacion = 'aprobado'"];
$params = [];

if (!empty($_GET['nombre'])) {
    $where[]  = 'u.nombre LIKE ?';
    $params[] = '%' . $_GET['nombre'] . '%';
}
if (!empty($_GET['especialidad'])) {
    $where[]  = 'n.especialidad LIKE ?';
    $params[] = '%' . $_GET['especialidad'] . '%';
}
if (!empty($_GET['precio_max']) && is_numeric($_GET['precio_max'])) {
    $where[]  = 'n.precio <= ?';
    $params[] = floatval($_GET['precio_max']);
}
if (!empty($_GET['precio_min']) && is_numeric($_GET['precio_min'])) {
    $where[]  = 'n.precio >= ?';
    $params[] = floatval($_GET['precio_min']);
}
if (!empty($_GET['rating_min']) && is_numeric($_GET['rating_min'])) {
    $where[]  = 'n.rating >= ?';
    $params[] = floatval($_GET['rating_min']);
}
if (!empty($_GET['modalidad'])) {
    $where[]  = "(n.modalidad = ? OR n.modalidad = 'Ambas')";
    $params[] = $_GET['modalidad'];
}

$whereSQL = implode(' AND ', $where);

$stmt = $db->prepare("
    SELECT n.id, u.nombre, n.especialidad, n.precio, n.rating,
           n.experiencia_años, n.pacientes_exit, n.modalidad,
           n.foto, n.estado_verificacion, n.duracion_consulta,
           (SELECT COUNT(*) FROM resenas r WHERE r.nutricionista_id = n.id) AS total_resenas
    FROM nutricionistas n
    JOIN usuarios u ON u.id = n.usuario_id
    WHERE $whereSQL
    ORDER BY n.rating DESC, n.experiencia_años DESC
");
$stmt->execute($params);
responderJSON($stmt->fetchAll());
