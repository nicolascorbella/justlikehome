const express = require("express");
const multer = require("multer");
const fs = require("fs-extra");
const path = require("path");

const app = express();
const PORT = 3000;

// Middleware para manejar JSON
app.use(express.json());
app.use(express.static("public"));

// Configuración de Multer para la subida de carpetas
const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    const folderName = req.body.folderName || "default";
    const folderPath = path.join(__dirname, "public/uploads", folderName);
    fs.ensureDirSync(folderPath); // Crea la carpeta si no existe
    cb(null, folderPath);
  },
  filename: (req, file, cb) => {
    cb(null, file.originalname); // Guarda con el nombre original
  },
});

const upload = multer({ storage });

// Ruta para obtener las propiedades JSON
app.get("/listings", (req, res) => {
  const filePath = path.join(__dirname, "data/listings.json");
  const listings = fs.readJsonSync(filePath, { throws: false }) || [];
  res.json(listings);
});

// Ruta para subir una carpeta y actualizar el JSON
app.post("/upload-folder", upload.array("files"), (req, res) => {
  const folderName = req.body.folderName || "default";
  const filePath = path.join(__dirname, "data/listings.json");

  // Leer o inicializar el archivo JSON
  let listings = fs.readJsonSync(filePath, { throws: false }) || [];

  // Añadir cada archivo como una nueva propiedad en el JSON
  req.files.forEach((file, index) => {
    listings.push({
      id: listings.length + 1,
      img: `uploads/${folderName}/${file.filename}`,
      location: req.body.location || "Sin ubicación",
      price: req.body.price || "Sin precio",
      description: req.body.description || "Sin descripción",
    });
  });

  // Guardar el JSON actualizado
  fs.writeJsonSync(filePath, listings, { spaces: 2 });

  res.json({
    message: "Carpeta subida correctamente y JSON actualizado.",
    listings,
  });
});

// Iniciar el servidor
app.listen(PORT, () => {
  console.log(`Servidor corriendo en http://localhost:${PORT}`);
});
