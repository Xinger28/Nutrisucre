<?php
// ============================================================
//  api/nutricionistas.php
//  Solo muestra nutricionistas APROBADOS
//  Filtros: nombre, precio_max, precio_min, rating_min, especialidad
//  GET ?id=X  → detalle completo de uno
// ============================================================
session_start();
require_once __DIR__ . '/../config.php';

$db  = getDB();
$id  = intval($_GET['id'] ?? 0);
$accion = $_GET['accion'] ?? '';

// ─── Guardar configuración de perfil y pagos (Nutricionista) ───
if ($_SERVER['REQUEST_METHOD'] === 'PUT' || ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'actualizar')) {
    $usuario = requireAuth();
    if ($usuario['rol'] !== 'Nutricionista') {
        responderJSON(['error' => 'Solo nutricionistas pueden realizar esta acción'], 403);
    }
    
    $body = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $stmt = $db->prepare("SELECT id FROM nutricionistas WHERE usuario_id = ?");
    $stmt->execute([$usuario['id']]);
    $nutri = $stmt->fetch();
    if (!$nutri) {
        responderJSON(['error' => 'Perfil de nutricionista no encontrado'], 404);
    }
    
    $nutriId = $nutri['id'];
    
    $telefono = trim($body['telefono'] ?? '');
    $whatsapp = trim($body['whatsapp'] ?? '');
    $mostrar_correo = isset($body['mostrar_correo']) ? intval($body['mostrar_correo']) : 1;
    $qr_code = trim($body['qr_code'] ?? '');
    $titular_cuenta = trim($body['titular_cuenta'] ?? '');
    $banco = trim($body['banco'] ?? '');
    $nro_cuenta = trim($body['nro_cuenta'] ?? '');
    $datos_transferencia_adicional = trim($body['datos_transferencia_adicional'] ?? '');
    
    $pago_qr_habilitado = isset($body['pago_qr_habilitado']) ? intval($body['pago_qr_habilitado']) : 0;
    $pago_transferencia_habilitado = isset($body['pago_transferencia_habilitado']) ? intval($body['pago_transferencia_habilitado']) : 0;
    $pago_deposito_habilitado = isset($body['pago_deposito_habilitado']) ? intval($body['pago_deposito_habilitado']) : 0;
    
    $foto = trim($body['foto'] ?? '');
    $precio = floatval($body['precio'] ?? 120);
    $descripcion_serv = trim($body['descripcion_serv'] ?? '');
    
    $upd = $db->prepare("
        UPDATE nutricionistas SET
            telefono = ?,
            whatsapp = ?,
            mostrar_correo = ?,
            qr_code = ?,
            titular_cuenta = ?,
            banco = ?,
            nro_cuenta = ?,
            datos_transferencia_adicional = ?,
            pago_qr_habilitado = ?,
            pago_transferencia_habilitado = ?,
            pago_deposito_habilitado = ?,
            foto = ?,
            precio = ?,
            descripcion_serv = ?
        WHERE id = ?
    ");
    $upd->execute([
        $telefono ?: null,
        $whatsapp ?: null,
        $mostrar_correo,
        $qr_code ?: null,
        $titular_cuenta ?: null,
        $banco ?: null,
        $nro_cuenta ?: null,
        $datos_transferencia_adicional ?: null,
        $pago_qr_habilitado,
        $pago_transferencia_habilitado,
        $pago_deposito_habilitado,
        $foto ?: null,
        $precio,
        $descripcion_serv ?: null,
        $nutriId
    ]);
    
    responderJSON(['ok' => true, 'mensaje' => 'Configuración de perfil y pagos guardada correctamente']);
}

// ─── Perfil del nutricionista logueado ──────────────────────────
if ($accion === 'mi_perfil') {
    $usuario = requireAuth();
    if ($usuario['rol'] !== 'Nutricionista') {
        responderJSON(['error' => 'Acceso denegado'], 403);
    }
    $stmt = $db->prepare("
        SELECT n.*, u.nombre, u.email
        FROM nutricionistas n
        JOIN usuarios u ON u.id = n.usuario_id
        WHERE n.usuario_id = ?
    ");
    $stmt->execute([$usuario['id']]);
    $nutri = $stmt->fetch();
    if (!$nutri) responderJSON(['error' => 'Perfil no encontrado'], 404);
    responderJSON($nutri);
}

// ─── Detalle de un nutricionista ─────────────────────────────
if ($id) {
    // Si el usuario es el propio nutricionista o admin, permitir ver aunque no esté aprobado
    $usuarioAuth = null;
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!empty($_SESSION['usuario'])) {
        $usuarioAuth = $_SESSION['usuario'];
    }

    $stmt = $db->prepare("
        SELECT n.*, u.nombre, u.email,
               (SELECT COUNT(*) FROM resenas r WHERE r.nutricionista_id = n.id) AS total_resenas
        FROM nutricionistas n
        JOIN usuarios u ON u.id = n.usuario_id
        WHERE n.id = ?
    ");
    $stmt->execute([$id]);
    $nutri = $stmt->fetch();
    if (!$nutri) responderJSON(['error' => 'No encontrado'], 404);

    // Si no está aprobado, verificar que el que lo pide sea admin o el propio nutri
    if ($nutri['estado_verificacion'] !== 'aprobado') {
        if (!$usuarioAuth || ($usuarioAuth['rol'] !== 'Administrador' && $nutri['usuario_id'] != $usuarioAuth['id'])) {
            responderJSON(['error' => 'No autorizado para ver este perfil pendiente'], 403);
        }
    }

    responderJSON($nutri);
}

// ─── Listado con filtros ──────────────────────────────────────
$where  = ["n.estado_verificacion = 'aprobado'"];
$params = [];

if (!empty($_GET['nombre'])) {
    $where[]  = '(u.nombre LIKE ? OR n.especialidad LIKE ?)';
    $params[] = '%' . $_GET['nombre'] . '%';
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
