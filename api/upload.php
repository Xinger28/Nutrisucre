<?php
// ============================================================
//  api/upload.php  —  Carga de archivos y fotos en el servidor
//  Solo accesible para usuarios autenticados
// ============================================================
session_start();
require_once __DIR__ . '/../config.php';

// Validar autenticación
$usuario = requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderJSON(['error' => 'Método no permitido'], 405);
}

// Validar que se haya subido un archivo
if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
    responderJSON(['error' => 'No se recibió ningún archivo o hubo un error en la carga.'], 400);
}

$archivo = $_FILES['archivo'];
$tipoDestino = $_POST['tipo'] ?? 'comprobantes'; // fotos | qrs | comprobantes

// Validar tipo de destino
$carpetasAceptadas = [
    'fotos'        => 'uploads/fotos/',
    'qrs'          => 'uploads/qrs/',
    'comprobantes' => 'uploads/comprobantes/'
];

if (!array_key_exists($tipoDestino, $carpetasAceptadas)) {
    responderJSON(['error' => 'Tipo de destino no válido'], 400);
}

$rutaDestino = __DIR__ . '/../' . $carpetasAceptadas[$tipoDestino];

// Crear la carpeta si no existe
if (!file_exists($rutaDestino)) {
    mkdir($rutaDestino, 0777, true);
}

// Validar extensión del archivo
$extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
$extensionesPermitidas = ['jpg', 'jpeg', 'png', 'pdf'];

if (!in_array($extension, $extensionesPermitidas)) {
    responderJSON(['error' => 'Formato de archivo no permitido. Solo se aceptan JPG, JPEG, PNG y PDF.'], 400);
}

// Validar tamaño (máximo 5MB)
$maxSize = 5 * 1024 * 1024;
if ($archivo['size'] > $maxSize) {
    responderJSON(['error' => 'El archivo supera el tamaño máximo de 5MB.'], 400);
}

// Generar nombre de archivo único
$nombreUnico = uniqid($tipoDestino . '_', true) . '.' . $extension;
$rutaCompleta = $rutaDestino . $nombreUnico;

// Mover el archivo
if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
    responderJSON([
        'ok'   => true,
        'path' => $carpetasAceptadas[$tipoDestino] . $nombreUnico,
        'url'  => $carpetasAceptadas[$tipoDestino] . $nombreUnico
    ]);
} else {
    responderJSON(['error' => 'No se pudo guardar el archivo en el servidor.'], 500);
}
