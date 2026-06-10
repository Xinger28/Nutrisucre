<?php
// ============================================================
//  api/usuarios.php  —  CRUD completo de usuarios
//  Solo accesible para Administradores
// ============================================================

session_start();
require_once __DIR__ . '/../config.php';

// El método HTTP nos dice qué operación hacer:
//   GET    → listar usuarios
//   POST   → crear usuario
//   PUT    → editar usuario
//   DELETE → eliminar usuario
$metodo = $_SERVER['REQUEST_METHOD'];
$body   = json_decode(file_get_contents('php://input'), true) ?? [];

match($metodo) {
    'GET'    => listar(),
    'POST'   => crear($body),
    'PUT'    => editar($body),
    'DELETE' => eliminar($body),
    default  => responderJSON(['error' => 'Método no permitido'], 405)
};

// ─────────────────────────────────────────
function listar(): void {
    $usuario = requireAuth();
    if (!in_array($usuario['rol'], ['Administrador', 'Nutricionista'])) {
        responderJSON(['error' => 'Acceso denegado. Solo administradores o nutricionistas.'], 403);
    }

    $db   = getDB();
    if ($usuario['rol'] === 'Nutricionista') {
        // Nutricionista solo puede listar pacientes
        $stmt = $db->prepare("
            SELECT id, nombre, email, rol, created_at
            FROM usuarios
            WHERE rol = 'Paciente'
            ORDER BY nombre ASC
        ");
        $stmt->execute();
    } else {
        // Admin ve todos
        $stmt = $db->query("
            SELECT id, nombre, email, rol, ci, celular, estado, created_at
            FROM usuarios
            ORDER BY created_at DESC
        ");
    }
    responderJSON($stmt->fetchAll());
}

// ─────────────────────────────────────────
//  CREAR usuario
// ─────────────────────────────────────────
function crear(array $body): void {
    requireAdmin();

    $nombre   = trim($body['nombre']   ?? '');
    $email    = strtolower(trim($body['email'] ?? ''));
    $password = $body['password'] ?? '';
    $rol      = $body['rol'] ?? 'Paciente';

    $ci       = trim($body['ci'] ?? '');
    $celular  = trim($body['celular'] ?? '');
    $estado   = $body['estado'] ?? 'activo';
    if (!in_array($estado, ['activo', 'bloqueado'])) {
        $estado = 'activo';
    }

    if (!$nombre || !$email || !$password) {
        responderJSON(['error' => 'Nombre, email y contraseña son obligatorios'], 400);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        responderJSON(['error' => 'Email inválido'], 400);
    }

    $db = getDB();

    // Verificar email único
    $check = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        responderJSON(['error' => 'Ya existe un usuario con ese email'], 409);
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $db->prepare("
        INSERT INTO usuarios (nombre, email, password, rol, ci, celular, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$nombre, $email, $hash, $rol, $ci ?: null, $celular ?: null, $estado]);
    $nuevoId = $db->lastInsertId();

    // Si el rol es Nutricionista, crear su perfil extendido automáticamente
    if ($rol === 'Nutricionista') {
        $n = $db->prepare("
            INSERT INTO nutricionistas (usuario_id, especialidad, precio, rating)
            VALUES (?, 'Nutrición General', 120.00, 5.0)
        ");
        $n->execute([$nuevoId]);
    }

    responderJSON(['ok' => true, 'id' => $nuevoId, 'mensaje' => 'Usuario creado correctamente']);
}

// ─────────────────────────────────────────
//  EDITAR usuario
// ─────────────────────────────────────────
function editar(array $body): void {
    requireAdmin();

    $id     = intval($body['id']     ?? 0);
    $nombre = trim($body['nombre']   ?? '');
    $email  = strtolower(trim($body['email'] ?? ''));
    $rol    = $body['rol'] ?? '';

    $ci       = trim($body['ci'] ?? '');
    $celular  = trim($body['celular'] ?? '');
    $estado   = $body['estado'] ?? 'activo';
    if (!in_array($estado, ['activo', 'bloqueado'])) {
        $estado = 'activo';
    }

    if (!$id || !$nombre || !$email) {
        responderJSON(['error' => 'ID, nombre y email son obligatorios'], 400);
    }

    $db = getDB();

    // Verificar que el email no lo use OTRO usuario
    $check = $db->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
    $check->execute([$email, $id]);
    if ($check->fetch()) {
        responderJSON(['error' => 'Ese email ya lo usa otro usuario'], 409);
    }

    // Si viene nueva contraseña, la hasheamos; si no, dejamos la existente
    if (!empty($body['password'])) {
        $hash = password_hash($body['password'], PASSWORD_BCRYPT);
        $stmt = $db->prepare("UPDATE usuarios SET nombre=?, email=?, password=?, rol=?, ci=?, celular=?, estado=? WHERE id=?");
        $stmt->execute([$nombre, $email, $hash, $rol, $ci ?: null, $celular ?: null, $estado, $id]);
    } else {
        $stmt = $db->prepare("UPDATE usuarios SET nombre=?, email=?, rol=?, ci=?, celular=?, estado=? WHERE id=?");
        $stmt->execute([$nombre, $email, $rol, $ci ?: null, $celular ?: null, $estado, $id]);
    }

    responderJSON(['ok' => true, 'mensaje' => 'Usuario actualizado']);
}

// ─────────────────────────────────────────
//  ELIMINAR usuario
// ─────────────────────────────────────────
function eliminar(array $body): void {
    requireAdmin();

    $id = intval($body['id'] ?? 0);
    if (!$id) {
        responderJSON(['error' => 'ID inválido'], 400);
    }

    // No permitir que el admin se elimine a sí mismo
    $usuario = requireAdmin();
    if ($usuario['id'] == $id) {
        responderJSON(['error' => 'No puedes eliminar tu propia cuenta'], 400);
    }

    $db   = getDB();
    $stmt = $db->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);

    responderJSON(['ok' => true, 'mensaje' => 'Usuario eliminado']);
}
