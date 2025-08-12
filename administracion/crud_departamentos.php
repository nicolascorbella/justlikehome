<?php
// Evitar errores visibles en producción (recomendado para InfinityFree)
ini_set('display_errors', 0);
error_reporting(0);

// Encabezado para indicar JSON
header('Content-Type: application/json');

// Ruta al archivo JSON
$jsonFile = '../data.json';

// Leer departamentos
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_GET['action'] === 'read') {
    if (file_exists($jsonFile)) {
        $data = file_get_contents($jsonFile);
        $json = json_decode($data, true);

        if ($json === null) {
            echo json_encode(["error" => "JSON inválido"]);
        } else {
            echo json_encode($json);
        }
    } else {
        echo json_encode(["error" => "Archivo data.json no encontrado"]);
    }
    exit;
}


// Crear nuevo departamento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'create') {
    $json = file_get_contents('php://input');
    $departamentos = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];

    $nuevo = json_decode($json, true);
    $nuevo['id'] = time(); // ID único simple

    $departamentos[] = $nuevo;
    file_put_contents($jsonFile, json_encode($departamentos, JSON_PRETTY_PRINT));
    echo json_encode(["success" => "Departamento creado"]);
    exit;
}

// Actualizar departamento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $departamentos = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];

    foreach ($departamentos as &$dep) {
        if ($dep['id'] == $_POST['id']) {
            $dep['location'] = $_POST['location'];
            $dep['price'] = $_POST['price'];
            $dep['description'] = $_POST['description'];
            $dep['status'] = $_POST['status'];
            $dep['ambientes'] = $_POST['ambientes'];
            break;
        }
    }

    file_put_contents($jsonFile, json_encode($departamentos, JSON_PRETTY_PRINT));
    echo json_encode(["success" => "Departamento actualizado"]);
    exit;
}
?>
