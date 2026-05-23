<?php
// ============================================================
//  api/auth.php  —  Login, registro y logout
//  El frontend llama a este archivo con fetch()
// ============================================================

// Iniciamos la sesión al principio (necesario para $_SESSION)
session_start();

// Incluimos la conexión y helpers
require_once __DIR__ . '/../config.php';

// Leemos el cuerpo JSON que envía el fetch del frontend
$body = json_decode(file_get_contents('php://input'), true) ?? [];

// Leemos la acción: login | register | logout | check
$accion = $_GET['accion'] ?? $body['accion'] ?? '';

// ─────────────────────────────────────────
//  Enrutador de acciones
// ─────────────────────────────────────────
match($accion) {
    'login'    => accionLogin($body),
    'register' => accionRegister($body),
    'logout'   => accionLogout(),
    'check'    => accionCheck(),
    default    => responderJSON(['error' => 'Acción no reconocida'], 400)
};

// ─────────────────────────────────────────
//  LOGIN
// ─────────────────────────────────────────
function accionLogin(array $body): void {
    $identificador = trim($body['identificador'] ?? ''); // puede ser nombre o email
    $password      = $body['password'] ?? '';

    if (!$identificador || !$password) {
        responderJSON(['error' => 'Completa todos los campos'], 400);
    }

    $db = getDB();

    // Buscamos por email O por nombre (igual que el login.html original)
    $stmt = $db->prepare("
        SELECT id, nombre, email, password, rol
        FROM usuarios
        WHERE email = :id OR nombre = :id2
        LIMIT 1
    ");
    $stmt->execute([':id' => $identificador, ':id2' => $identificador]);
    $usuario = $stmt->fetch();

    // password_verify compara el texto plano con el hash guardado en BD
    if (!$usuario || !password_verify($password, $usuario['password'])) {
        responderJSON(['error' => 'Usuario o contraseña incorrectos'], 401);
    }

    // Guardamos en sesión (sin la contraseña por seguridad)
    $_SESSION['usuario'] = [
        'id'     => $usuario['id'],
        'nombre' => $usuario['nombre'],
        'email'  => $usuario['email'],
        'rol'    => $usuario['rol'],
    ];

    responderJSON(['ok' => true, 'usuario' => $_SESSION['usuario']]);
}

// ─────────────────────────────────────────
//  REGISTRO
// ─────────────────────────────────────────
function accionRegister(array $body): void {
    $nombre   = trim($body['nombre']   ?? '');
    $email    = strtolower(trim($body['email'] ?? ''));
    $password = $body['password'] ?? '';
    $rol      = $body['rol'] ?? 'Paciente';

    // Validaciones básicas
    if (!$nombre || !$email || !$password) {
        responderJSON(['error' => 'Nombre, email y contraseña son obligatorios'], 400);
    }
    if (strlen($password) < 6) {
        responderJSON(['error' => 'La contraseña debe tener al menos 6 caracteres'], 400);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        responderJSON(['error' => 'El email no tiene un formato válido'], 400);
    }
    // Solo roles permitidos
    if (!in_array($rol, ['Paciente', 'Nutricionista'])) {
        $rol = 'Paciente';
    }

    $db = getDB();

    // Verificar que el email no exista
    $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        responderJSON(['error' => 'Ya existe una cuenta con ese correo'], 409);
    }

    // Hashear la contraseña (NUNCA guardar texto plano)
    $hash = password_hash($password, PASSWORD_BCRYPT);

    // Insertar usuario
    $stmt = $db->prepare("
        INSERT INTO usuarios (nombre, email, password, rol)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$nombre, $email, $hash, $rol]);
    $nuevoId = $db->lastInsertId();

    // Si es nutricionista, crear su registro extendido
    if ($rol === 'Nutricionista') {
        $stmt = $db->prepare("
            INSERT INTO nutricionistas (usuario_id, especialidad, precio, rating)
            VALUES (?, 'Nutrición General', 120.00, 5.0)
        ");
        $stmt->execute([$nuevoId]);
    }

    responderJSON(['ok' => true, 'mensaje' => "Cuenta creada como $rol"]);
}

// ─────────────────────────────────────────
//  LOGOUT
// ─────────────────────────────────────────
function accionLogout(): void {
    // Destruir todos los datos de sesión
    $_SESSION = [];
    session_destroy();
    responderJSON(['ok' => true]);
}

// ─────────────────────────────────────────
//  CHECK  (¿hay sesión activa?)
// ─────────────────────────────────────────
function accionCheck(): void {
    if (!empty($_SESSION['usuario'])) {
        responderJSON(['autenticado' => true, 'usuario' => $_SESSION['usuario']]);
    } else {
        responderJSON(['autenticado' => false]);
    }
}
