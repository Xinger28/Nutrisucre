<?php
// ============================================================
//  config.php  —  Conexión a la base de datos
//  Incluir este archivo al inicio de CADA archivo PHP
// ============================================================

// Datos de conexión (ajusta si tu XAMPP usa otro usuario/contraseña)
define('DB_HOST', 'localhost');
define('DB_NAME', 'nutrisucre');
define('DB_USER', 'root');
define('DB_PASS', '');          // En XAMPP local la contraseña suele estar vacía

// Zona horaria para Bolivia
date_default_timezone_set('America/La_Paz');

/**
 * Crea y devuelve una conexión PDO a MySQL.
 * PDO es la forma moderna y segura de conectarse en PHP.
 */
function getDB(): PDO {
    // Usamos static para no crear una nueva conexión en cada llamada
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";

            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                // Si hay un error de SQL, lanza una excepción (más fácil de detectar)
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                // Devuelve los resultados como arrays asociativos por defecto
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // Desactiva emulación de prepared statements (más seguro)
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // En producción nunca muestres el error real, pero en desarrollo sí
            http_response_code(500);
            die(json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]));
        }
    }

    return $pdo;
}

/**
 * Función helper: devuelve JSON y termina el script.
 * Todos los endpoints de la API la usan para responder.
 */
function responderJSON(mixed $datos, int $codigo = 200): void {
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    // Evita que el navegador guarde en caché las respuestas de la API
    header('Cache-Control: no-store');
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Verifica que el usuario haya iniciado sesión.
 * Si no, responde con error 401 (no autorizado).
 */
function requireAuth(): array {
    if (session_status() === PHP_SESSION_NONE) session_start();

    if (empty($_SESSION['usuario'])) {
        responderJSON(['error' => 'No autorizado. Inicia sesión primero.'], 401);
    }

    return $_SESSION['usuario'];
}

/**
 * Verifica que el usuario sea Administrador.
 */
function requireAdmin(): array {
    $usuario = requireAuth();
    if ($usuario['rol'] !== 'Administrador') {
        responderJSON(['error' => 'Acceso denegado. Solo administradores.'], 403);
    }
    return $usuario;
}
