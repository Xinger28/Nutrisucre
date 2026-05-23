<?php
// ============================================================
//  api/resenas.php  —  Reseñas y calificaciones
//  GET  -> listar resenas de un nutricionista (para buscar.php)
//  POST -> paciente crea una reseña
// ============================================================
session_start();
require_once __DIR__ . '/../config.php';

$metodo = $_SERVER['REQUEST_METHOD'];
$body   = json_decode(file_get_contents('php://input'), true) ?? [];

match($metodo) {
    'GET'  => listar(),
    'POST' => crear($body),
    default => responderJSON(['error' => 'Metodo no permitido'], 405)
};

function listar(): void {
    requireAuth();
    $db            = getDB();
    $nutriId       = intval($_GET['nutricionista_id'] ?? 0);

    if ($nutriId) {
        // Reseñas de un nutricionista específico
        $stmt = $db->prepare("
            SELECT r.id, r.calificacion, r.comentario, r.created_at,
                   u.nombre AS paciente
            FROM resenas r
            JOIN usuarios u ON u.id = r.paciente_id
            WHERE r.nutricionista_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$nutriId]);
    } else {
        // Promedio de rating de todos los nutricionistas
        $stmt = $db->query("
            SELECT nutricionista_id,
                   ROUND(AVG(calificacion),1) AS rating_real,
                   COUNT(*) AS total_resenas
            FROM resenas
            GROUP BY nutricionista_id
        ");
    }
    responderJSON($stmt->fetchAll());
}

function crear(array $body): void {
    $usuario = requireAuth();

    $nutriId    = intval($body['nutricionista_id'] ?? 0);
    $citaId     = intval($body['cita_id']          ?? 0) ?: null;
    $califica   = intval($body['calificacion']      ?? 0);
    $comentario = trim($body['comentario']          ?? '');

    if (!$nutriId || $califica < 1 || $califica > 5) {
        responderJSON(['error' => 'Nutricionista y calificacion (1-5) son obligatorios'], 400);
    }

    $db   = getDB();
    $stmt = $db->prepare("
        INSERT INTO resenas (paciente_id, nutricionista_id, cita_id, calificacion, comentario)
        VALUES (?,?,?,?,?)
        ON DUPLICATE KEY UPDATE calificacion=VALUES(calificacion), comentario=VALUES(comentario)
    ");
    $stmt->execute([$usuario['id'], $nutriId, $citaId, $califica, $comentario ?: null]);

    // Recalcular rating promedio en la tabla nutricionistas
    $avg = $db->prepare("
        UPDATE nutricionistas
        SET rating = (SELECT ROUND(AVG(calificacion),1) FROM resenas r2 WHERE r2.nutricionista_id = ?)
        WHERE id = ?
    ");
    $avg->execute([$nutriId, $nutriId]);

    responderJSON(['ok' => true, 'mensaje' => 'Reseña guardada']);
}
