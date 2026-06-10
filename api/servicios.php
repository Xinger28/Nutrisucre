<?php
// ============================================================
//  api/servicios.php  —  Sprint 2: Gestión de Servicios
//
//  ENDPOINTS:
//  GET    /api/servicios.php              → listar (según rol)
//  GET    /api/servicios.php?id=X         → detalle de uno
//  GET    /api/servicios.php?publico=1    → solo Aprobados (demandantes)
//  POST   /api/servicios.php              → HU-01: crear servicio (Ofertante)
//  PUT    /api/servicios.php              → HU-02: editar servicio (Ofertante)
//  DELETE /api/servicios.php              → HU-02: eliminar (Ofertante/Admin)
//  PUT    /api/servicios.php?accion=validar → HU-03: aprobar/rechazar (Admin)
// ============================================================
session_start();
require_once __DIR__ . '/../config.php';

$metodo = $_SERVER['REQUEST_METHOD'];
$accion = $_GET['accion'] ?? '';
$body   = json_decode(file_get_contents('php://input'), true) ?? [];

// Rutas
if ($metodo === 'GET' && isset($_GET['publico']))     { listarPublicos(); exit; }
if ($metodo === 'GET' && isset($_GET['id']))          { detalle(intval($_GET['id'])); exit; }
if ($metodo === 'PUT' && $accion === 'validar')       { validar($body); exit; }

match($metodo) {
    'GET'    => listar(),
    'POST'   => crear($body),
    'PUT'    => editar($body),
    'DELETE' => eliminar($body),
    default  => responderJSON(['error' => 'Método no permitido'], 405)
};

// ─────────────────────────────────────────
//  LISTAR — vista según rol del usuario
// ─────────────────────────────────────────
function listar(): void {
    $usuario = requireAuth();
    $db      = getDB();

    if ($usuario['rol'] === 'Administrador') {
        // Admin ve TODOS con filtro de estado opcional
        $estado = $_GET['estado'] ?? '';
        if ($estado) {
            $stmt = $db->prepare("
                SELECT s.*, u.nombre AS nutricionista_nombre
                FROM servicios s
                JOIN usuarios u ON u.id = s.nutricionista_id
                WHERE s.estado = ?
                ORDER BY
                    FIELD(s.estado,'Pendiente','Aprobado','Rechazado'),
                    s.created_at DESC
            ");
            $stmt->execute([$estado]);
        } else {
            $stmt = $db->query("
                SELECT s.*, u.nombre AS nutricionista_nombre
                FROM servicios s
                JOIN usuarios u ON u.id = s.nutricionista_id
                ORDER BY
                    FIELD(s.estado,'Pendiente','Aprobado','Rechazado'),
                    s.created_at DESC
            ");
        }

    } elseif ($usuario['rol'] === 'Nutricionista') {
        // Nutricionista ve SOLO sus propios servicios (todos los estados)
        $stmt = $db->prepare("
            SELECT s.*, u.nombre AS nutricionista_nombre
            FROM servicios s
            JOIN usuarios u ON u.id = s.nutricionista_id
            WHERE s.nutricionista_id = ?
            ORDER BY s.created_at DESC
        ");
        $stmt->execute([$usuario['id']]);

    } else {
        // Paciente solo ve los Aprobados
        $stmt = $db->query("
            SELECT s.*, u.nombre AS nutricionista_nombre
            FROM servicios s
            JOIN usuarios u ON u.id = s.nutricionista_id
            WHERE s.estado = 'Aprobado'
            ORDER BY s.precio ASC
        ");
    }

    responderJSON($stmt->fetchAll());
}

// ─────────────────────────────────────────
//  LISTAR PÚBLICOS (sin auth, solo Aprobados)
//  Para la página buscar.php
// ─────────────────────────────────────────
function listarPublicos(): void {
    $db   = getDB();
    $where = ["s.estado = 'Aprobado'"];
    $params = [];

    if (!empty($_GET['buscar'])) {
        $where[]  = '(s.titulo LIKE ? OR s.descripcion LIKE ?)';
        $params[] = '%' . $_GET['buscar'] . '%';
        $params[] = '%' . $_GET['buscar'] . '%';
    }
    if (!empty($_GET['categoria'])) {
        $where[]  = 's.categoria = ?';
        $params[] = $_GET['categoria'];
    }
    if (!empty($_GET['precio_max'])) {
        $where[]  = 's.precio <= ?';
        $params[] = floatval($_GET['precio_max']);
    }
    if (!empty($_GET['modalidad'])) {
        $where[]  = "(s.modalidad = ? OR s.modalidad = 'Ambas')";
        $params[] = $_GET['modalidad'];
    }

    $orden = $_GET['orden'] ?? 'recientes';
    $orderBy = match ($orden) {
        'precio_asc'        => 's.precio ASC',
        'precio_desc'       => 's.precio DESC',
        'mejor_calificados' => 'COALESCE(np.rating, 5.0) DESC, s.created_at DESC',
        'mas_utilizados'    => 'COALESCE(sol_count.cant_solicitudes, 0) DESC, s.created_at DESC',
        'recientes'         => 's.created_at DESC',
        default             => 's.created_at DESC'
    };

    $whereSQL = implode(' AND ', $where);
    $stmt = $db->prepare("
        SELECT s.*, u.nombre AS nutricionista_nombre,
               COALESCE(np.rating, 5.0) AS nutricionista_rating,
               COALESCE(sol_count.cant_solicitudes, 0) AS total_solicitudes
        FROM servicios s
        JOIN usuarios u ON u.id = s.nutricionista_id
        LEFT JOIN nutricionistas np ON np.usuario_id = s.nutricionista_id
        LEFT JOIN (
            SELECT servicio_id, COUNT(*) AS cant_solicitudes
            FROM solicitudes
            WHERE estado = 'Aceptada'
            GROUP BY servicio_id
        ) sol_count ON sol_count.servicio_id = s.id
        WHERE $whereSQL
        ORDER BY $orderBy
    ");
    $stmt->execute($params);
    responderJSON($stmt->fetchAll());
}

// ─────────────────────────────────────────
//  DETALLE de un servicio
// ─────────────────────────────────────────
function detalle(int $id): void {
    requireAuth();
    $db   = getDB();
    $stmt = $db->prepare("
        SELECT s.*, u.nombre AS nutricionista_nombre
        FROM servicios s
        JOIN usuarios u ON u.id = s.nutricionista_id
        WHERE s.id = ?
    ");
    $stmt->execute([$id]);
    $servicio = $stmt->fetch();
    if (!$servicio) responderJSON(['error' => 'Servicio no encontrado'], 404);
    responderJSON($servicio);
}

// ─────────────────────────────────────────
//  HU-01: CREAR servicio (Ofertante)
//  Estado inicial obligatorio: "Pendiente"
// ─────────────────────────────────────────
function crear(array $body): void {
    $usuario = requireAuth();

    // Solo nutricionistas pueden crear servicios
    if ($usuario['rol'] !== 'Nutricionista') {
        responderJSON(['error' => 'Solo los nutricionistas pueden registrar servicios'], 403);
    }

    // Validaciones de campos obligatorios
    $titulo      = trim($body['titulo']      ?? '');
    $descripcion = trim($body['descripcion'] ?? '');
    $precio      = floatval($body['precio']  ?? 0);
    $categoria   = $body['categoria']        ?? 'Otro';
    $duracion    = intval($body['duracion_semanas'] ?? 4);
    $modalidad   = $body['modalidad']        ?? 'Virtual';
    $incluye     = trim($body['incluye']     ?? '');

    // Spec: título obligatorio
    if (empty($titulo)) {
        responderJSON(['error' => 'El título del servicio es obligatorio'], 400);
    }
    // Spec: descripción obligatoria
    if (empty($descripcion)) {
        responderJSON(['error' => 'La descripción es obligatoria'], 400);
    }
    // Spec: precio debe ser mayor a 0
    if ($precio <= 0) {
        responderJSON(['error' => 'El precio debe ser mayor a 0'], 400);
    }
    // Spec: duración mínima 1 semana
    if ($duracion < 1) {
        responderJSON(['error' => 'La duración debe ser mínimo 1 semana'], 400);
    }

    $db   = getDB();
    $stmt = $db->prepare("
        INSERT INTO servicios
            (nutricionista_id, titulo, descripcion, categoria,
             precio, duracion_semanas, modalidad, incluye, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente')
    ");
    $stmt->execute([
        $usuario['id'], $titulo, $descripcion, $categoria,
        $precio, $duracion, $modalidad, $incluye ?: null
    ]);

    $nuevoId = $db->lastInsertId();
    responderJSON([
        'ok'      => true,
        'id'      => $nuevoId,
        'estado'  => 'Pendiente',
        'mensaje' => 'Servicio registrado. Quedará visible tras aprobación del administrador.'
    ], 201);
}

// ─────────────────────────────────────────
//  HU-02: EDITAR servicio (Ofertante)
//  Ediciones críticas devuelven estado a "Pendiente"
// ─────────────────────────────────────────
function editar(array $body): void {
    $usuario = requireAuth();

    $id          = intval($body['id']          ?? 0);
    $titulo      = trim($body['titulo']         ?? '');
    $descripcion = trim($body['descripcion']    ?? '');
    $precio      = floatval($body['precio']     ?? 0);
    $categoria   = $body['categoria']           ?? '';
    $duracion    = intval($body['duracion_semanas'] ?? 0);
    $modalidad   = $body['modalidad']           ?? '';
    $incluye     = trim($body['incluye']        ?? '');

    if (!$id)           responderJSON(['error' => 'ID de servicio requerido'], 400);
    if (empty($titulo)) responderJSON(['error' => 'El título es obligatorio'], 400);
    if ($precio <= 0)   responderJSON(['error' => 'El precio debe ser mayor a 0'], 400);

    $db = getDB();

    // Verificar que el servicio pertenece al nutricionista (o es admin)
    $check = $db->prepare("SELECT nutricionista_id, estado FROM servicios WHERE id = ?");
    $check->execute([$id]);
    $servicio = $check->fetch();

    if (!$servicio) {
        responderJSON(['error' => 'Servicio no encontrado'], 404);
    }
    if ($usuario['rol'] !== 'Administrador' && $servicio['nutricionista_id'] != $usuario['id']) {
        responderJSON(['error' => 'No tienes permiso para editar este servicio'], 403);
    }

    // Spec: cualquier edición de campos críticos devuelve estado a "Pendiente"
    // Campos críticos: título, descripción, precio, categoría
    $nuevoEstado = 'Pendiente';

    $stmt = $db->prepare("
        UPDATE servicios
        SET titulo = ?, descripcion = ?, categoria = ?,
            precio = ?, duracion_semanas = ?, modalidad = ?,
            incluye = ?, estado = ?,
            motivo_rechazo = NULL
        WHERE id = ?
    ");
    $stmt->execute([
        $titulo, $descripcion, $categoria,
        $precio, $duracion, $modalidad,
        $incluye ?: null, $nuevoEstado,
        $id
    ]);

    responderJSON([
        'ok'      => true,
        'estado'  => $nuevoEstado,
        'mensaje' => 'Servicio actualizado. Vuelve a estado Pendiente para revisión del administrador.'
    ]);
}

// ─────────────────────────────────────────
//  HU-02: ELIMINAR servicio
// ─────────────────────────────────────────
function eliminar(array $body): void {
    $usuario = requireAuth();
    $id      = intval($body['id'] ?? 0);

    if (!$id) responderJSON(['error' => 'ID requerido'], 400);

    $db    = getDB();
    $check = $db->prepare("SELECT nutricionista_id FROM servicios WHERE id = ?");
    $check->execute([$id]);
    $servicio = $check->fetch();

    if (!$servicio) responderJSON(['error' => 'Servicio no encontrado'], 404);

    // Solo el dueño o el admin pueden eliminar
    if ($usuario['rol'] !== 'Administrador' && $servicio['nutricionista_id'] != $usuario['id']) {
        responderJSON(['error' => 'No tienes permiso para eliminar este servicio'], 403);
    }

    $db->prepare("DELETE FROM servicios WHERE id = ?")->execute([$id]);
    responderJSON(['ok' => true, 'mensaje' => 'Servicio eliminado correctamente']);
}

// ─────────────────────────────────────────
//  HU-03: VALIDAR (Administrador)
//  Aprobar o Rechazar con motivo opcional
// ─────────────────────────────────────────
function validar(array $body): void {
    requireAdmin();

    $id     = intval($body['id']     ?? 0);
    $estado = $body['estado']        ?? '';
    $motivo = trim($body['motivo']   ?? '');

    if (!$id) responderJSON(['error' => 'ID requerido'], 400);

    // Spec: estado válido solo Aprobado o Rechazado
    if (!in_array($estado, ['Aprobado', 'Rechazado'])) {
        responderJSON(['error' => 'Estado inválido. Use Aprobado o Rechazado'], 400);
    }
    // Spec: rechazo requiere motivo
    if ($estado === 'Rechazado' && empty($motivo)) {
        responderJSON(['error' => 'Debe indicar el motivo del rechazo'], 400);
    }

    $db   = getDB();

    // Verificar si el servicio existe
    $check = $db->prepare("SELECT id FROM servicios WHERE id = ?");
    $check->execute([$id]);
    if (!$check->fetch()) {
        responderJSON(['error' => 'Servicio no encontrado'], 404);
    }

    $stmt = $db->prepare("
        UPDATE servicios
        SET estado = ?, motivo_rechazo = ?
        WHERE id = ?
    ");
    $stmt->execute([
        $estado,
        $estado === 'Rechazado' ? $motivo : null,
        $id
    ]);

    responderJSON([
        'ok'      => true,
        'estado'  => $estado,
        'mensaje' => "Servicio $estado correctamente"
    ]);
}
