$jsonFile = __DIR__ . '/data.json'; // Ruta del archivo JSON

// Leer el cuerpo de la solicitud
$jsonData = file_get_contents("php://input");
$data = json_decode($jsonData, true);

if (!isset($data['action'])) {
    echo "Error: No se especificó una acción.";
    exit;
}

// Leer el JSON existente
$jsonContent = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];

switch ($data['action']) {
    case 'save': // Guardar o reemplazar el JSON completo
        if (isset($data['json'])) {
            file_put_contents($jsonFile, json_encode($data['json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            echo "JSON guardado correctamente.";
        } else {
            echo "Error: No se proporcionó contenido JSON.";
        }
        break;

    case 'delete': // Eliminar un departamento específico
        if (isset($data['id'])) {
            $jsonContent = array_filter($jsonContent, fn($entry) => $entry['id'] !== $data['id']);
            file_put_contents($jsonFile, json_encode(array_values($jsonContent), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            echo "Departamento eliminado correctamente.";
        } else {
            echo "Error: No se proporcionó un ID válido.";
        }
        break;

    case 'update': // Actualizar un departamento específico
        if (isset($data['id']) && isset($data['updates'])) {
            foreach ($jsonContent as &$entry) {
                if ($entry['id'] === $data['id']) {
                    $entry = array_merge($entry, $data['updates']); // Actualizar con nuevos valores
                    break;
                }
            }
            file_put_contents($jsonFile, json_encode($jsonContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            echo "Departamento actualizado correctamente.";
        } else {
            echo "Error: Datos insuficientes para actualizar.";
        }
        break;

    default:
        echo "Error: Acción no válida.";
        break;
}
