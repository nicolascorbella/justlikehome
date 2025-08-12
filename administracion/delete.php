<?php
require_once 'config.php'; // Incluye rutas y configuraciones

header('Content-Type: application/json'); // Respuesta en formato JSON

// Decodificar el cuerpo JSON de la solicitud
$input = json_decode(file_get_contents('php://input'), true);

// Verificar que se recibió una acción
$action = $input['action'] ?? null;
if ($action !== 'delete') {
    echo json_encode(["error" => "Acción no válida o no especificada."]);
    exit;
}

// Validar si el ID fue enviado
$id = $input['id'] ?? null;
if (!$id) {
    echo json_encode(["error" => "ID no válido o no proporcionado."]);
    exit;
}

// Cargar el archivo JSON
$data = file_exists(JSON_FILE) ? json_decode(file_get_contents(JSON_FILE), true) : [];
$entryToDelete = null;

// Buscar el registro con el ID
foreach ($data as $key => $entry) {
    if ((string)$entry['id'] === (string)$id) { // Comparación estricta de ID
        $entryToDelete = $entry;
        unset($data[$key]);
        break;
    }
}

if (!$entryToDelete) {
    echo json_encode(["error" => "Departamento no encontrado."]);
    exit;
}

// Guardar cambios en el JSON
file_put_contents(JSON_FILE, json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

// Eliminar carpeta de imágenes asociada
$folderPath = ROOT_UPLOAD_DIR . dirname($entryToDelete['img']);
if (is_dir($folderPath)) {
    array_map('unlink', glob("$folderPath/*.*"));
    rmdir($folderPath);
}

// Respuesta exitosa
echo json_encode(["success" => "Departamento eliminado correctamente."]);
?>
