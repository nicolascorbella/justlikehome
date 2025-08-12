<?php
require_once 'config.php';

header('Content-Type: application/json'); // Asegura que el contenido sea JSON

// Función para generar el contenido HTML dinámicamente
function generarHTML($folderName, $imageFiles, $iframe) {
    $imagenesHTML = '';
    foreach ($imageFiles as $img) {
        $imagenesHTML .= "<img src=\"$img\" alt=\"Imagen\">";
    }

    $price = $_POST['price'] ?? '';
    $location = $_POST['location'] ?? '';
    $description = $_POST['description'] ?? '';

    return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Propiedad</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .carousel-container {
        position: relative;
        width: 100%;
        max-width: 800px;
        margin: auto;
        overflow: hidden;
        border-radius: 10px;
    }
    .carousel-slides img {
        display: none;
        width: 100%;
    }
    .carousel-control {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background-color: rgba(0,0,0,0.5);
        border: none;
        color: white;
        padding: 10px;
        cursor: pointer;
        z-index: 10;
    }
    #prevButton { left: 10px; }
    #nextButton { right: 10px; }

    .carousel-dots {
        text-align: center;
        margin-top: 10px;
    }
    .dot {
        height: 12px;
        width: 12px;
        margin: 0 4px;
        background-color: #bbb;
        border-radius: 50%;
        display: inline-block;
        cursor: pointer;
    }
    .dot.active {
        background-color: #717171;
    }

    .map-container {
        position: relative;
        width: 100%;
        padding-bottom: 56.25%;
        height: 0;
        overflow: hidden;
        border-radius: 8px;
        margin: 20px 0;
    }
    .map-container iframe {
        position: absolute;
        top: 0; left: 0;
        width: 100%;
        height: 100%;
        border: 0;
    }
  </style>
</head>
<body>

  <section class="d-flex justify-content-center align-items-center bg-light p-3">
    <a href="https://departamentoslistado.rf.gd/departamentos/"class="btn btn-outline-primary">
      <i class="bi bi-arrow-return-left me-2"></i> Volver al listado
    </a>
  </section>

  <div class="carousel-container mt-3">
    <div class="carousel-slides" id="carouselSlides">
      $imagenesHTML
    </div>
    <button class="carousel-control" id="prevButton">&#10094;</button>
    <button class="carousel-control" id="nextButton">&#10095;</button>
    <div class="carousel-dots" id="carouselDots"></div>
  </div>

  <div class="container mt-4">
    <div class="card shadow-sm border-0 mb-4">
      <div class="card-body">
        <h5 class="card-title text-primary">\$ $price</h5>
        <h6 class="card-subtitle mb-2 text-muted">$location</h6>
        <p class="card-text mt-3">$description</p>
      </div>
    </div>

    <div class="map-container">
      $iframe
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const slides = document.querySelectorAll('#carouselSlides img');
      const prevButton = document.getElementById('prevButton');
      const nextButton = document.getElementById('nextButton');
      const dotsContainer = document.getElementById('carouselDots');
      let currentIndex = 0;

      function showSlide(index) {
        slides.forEach((slide, i) => {
          slide.style.display = i === index ? 'block' : 'none';
          dotsContainer.children[i].classList.toggle('active', i === index);
        });
      }

      slides.forEach(() => {
        const dot = document.createElement('span');
        dot.classList.add('dot');
        dotsContainer.appendChild(dot);
      });

      Array.from(dotsContainer.children).forEach((dot, i) => {
        dot.addEventListener('click', () => {
          currentIndex = i;
          showSlide(currentIndex);
        });
      });

      prevButton.addEventListener('click', () => {
        currentIndex = (currentIndex - 1 + slides.length) % slides.length;
        showSlide(currentIndex);
      });

      nextButton.addEventListener('click', () => {
        currentIndex = (currentIndex + 1) % slides.length;
        showSlide(currentIndex);
      });

      showSlide(currentIndex);
    });
  </script>

</body>
</html>
HTML;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $folderName = isset($_POST['folderName']) ? $_POST['folderName'] : '';
    $folderName = strtolower(preg_replace('/[^a-z0-9]/', '', $folderName)); // Normalizar nombre

    if (!$folderName) {
        echo json_encode(["error" => "Nombre de la carpeta no válido."]);
        exit;
    }

    // Crear la carpeta destino
    $uploadDir = ROOT_UPLOAD_DIR . $folderName . '/';
    if (!file_exists($uploadDir) && !mkdir($uploadDir, 0777, true)) {
        echo json_encode(["error" => "No se pudo crear la carpeta."]);
        exit;
    }

    // Procesar las imágenes
    $counter = 1;
    $imageFiles = [];
    foreach ($_FILES['files']['tmp_name'] as $key => $tmpName) {
    $fileExtension = strtolower(pathinfo($_FILES['files']['name'][$key], PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'jfif'];


    if (!in_array($fileExtension, $allowedExtensions)) {
        continue;
    }

    // Crear nombre en .webp
    $newFileName = "img{$counter}.webp";
    $destination = $uploadDir . $newFileName;

    // Cargar imagen original
  switch ($fileExtension) {
    case 'jpg':
    case 'jpeg':
    case 'jfif': // ✅ Lo tratamos como JPEG normal
        $image = imagecreatefromjpeg($tmpName);
        break;
    case 'png':
        $image = imagecreatefrompng($tmpName);
        break;
    case 'gif':
        $image = imagecreatefromgif($tmpName);
        break;
    default:
        continue 2;
}


    // Convertir a WebP y guardar
    if ($image && imagewebp($image, $destination, 80)) {
        imagedestroy($image);
        $imageFiles[] = $newFileName;
        $counter++;
    }
}


    // Guardar datos en el JSON
    $data = file_exists(JSON_FILE) ? json_decode(file_get_contents(JSON_FILE), true) : [];
    $newId = count($data) > 0 ? end($data)['id'] + 1 : 1; // Generar el nuevo ID antes de usarlo

    // Generar la URL dinámica completa con el dominio
    $baseUrl = "https://departamentoslistados.42web.io"; // Asegúrate de no incluir una barra al final
    $folderName = str_replace('\\', '/', $folderName); // Reemplazar barras invertidas por barras normales
    $url = rtrim($baseUrl, '/') . "/departamentos/" . trim($folderName, '/') . "/index.html?id=" . $newId;

    // Crear el nuevo registro
    $newEntry = [
        "id" => $newId,
        "price" => $_POST['price'] ?? '',
        "status" => $_POST['status'] ?? '',
        "description" => $_POST['description'] ?? '',
        "ambientes" => $_POST['ambientes'] ?? '',
        "iframe" => $_POST['iframe'] ?? '',
        "location" => $_POST['location'] ?? '',
        "img" => str_replace('\\', '/', "$folderName/" . $imageFiles[0]),
        "url" => $url // Agregar la URL generada al JSON
    ];

    $data[] = $newEntry;
    file_put_contents(JSON_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

    // Crear el archivo HTML
   $htmlContent = generarHTML($folderName, $imageFiles, $_POST['iframe'] ?? '');
    if (!file_put_contents($uploadDir . "index.html", $htmlContent)) {
        echo json_encode(["error" => "No se pudo crear el archivo HTML."]);
        exit;
    }

    echo json_encode([
        "success" => "Datos y archivos subidos correctamente. Nuevo ID: {$newId}",
        "url" => $url
    ], JSON_UNESCAPED_SLASHES); // Agregar esta opción para evitar barras invertidas
    exit;
}

echo json_encode(["error" => "Método no permitido."]);
exit;

?>
