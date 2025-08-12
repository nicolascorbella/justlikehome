<?php
function createHTMLContent($folderName, $images) {
    $imageTags = '';
    foreach ($images as $image) {
        $imageTags .= "<img src=\"$image\" alt=\"Imagen\">";
    }

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JustLikeHome - $folderName</title>
    <link rel="icon" href="../../images/logochico.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/styles.css">
</head>
<body>
    <header>
        <a href="../../aplhabeta5454/index.html"><img class="logo-header" src="../../images/logochico.png"></a>
        <nav>
            <ul class="desk-ul">
                <li><a href="../../aplhabeta5454/index.html">Inicio</a></li>
                <li><a href="../../sobre nosotros/index.html">Sobre nosotros</a></li>
                <li><a href="#">Departamentos</a></li>
                <li><a href="../../contacto/index.html">Contacto</a></li>
            </ul>
        </nav>
    </header>
    <section class="d-flex justify-content-center align-items-center bg-light">
        <button type="button" id="volverlista" class="calltoaction">
            <i class="bi bi-arrow-return-left me-2"></i> Volver al listado
        </button>
    </section>
    <main>
        <div class="galeria-dep" id="galeria-dep">
            {$imageTags}
        </div>
    </main>
    <footer>
        <div class="redes">Redes Sociales</div>
        <div class="footerline"></div>
        <a href="https://wa.link/jkkn0f" target="_blank"><img class="wpp" src="../../images/whatsapp.png" alt="WhatsApp"></a>
        <p>JustLikeHome</p>
    </footer>
</body>
</html>
HTML;
}
?>
