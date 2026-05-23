<?php
// ============================================================
//  config.php  —  Conexión a la base de datos
//
//  XAMPP local: usa los valores por defecto (localhost/root)
//  Render (producción): lee variables de entorno DB_HOST etc.
// ============================================================

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'nutrisucre');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

date_default_timezone_set('America/La_Paz');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

function responderJSON(mixed $datos, int $codigo = 200): void {
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

function requireAuth(): array {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['usuario'])) {
        responderJSON(['error' => 'No autorizado. Inicia sesión primero.'], 401);
    }
    return $_SESSION['usuario'];
}

function requireAdmin(): array {
    $usuario = requireAuth();
    if ($usuario['rol'] !== 'Administrador') {
        responderJSON(['error' => 'Acceso denegado. Solo administradores.'], 403);
    }
    return $usuario;
}
