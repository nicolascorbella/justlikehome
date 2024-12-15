const express = require("express");
const multer = require("multer");
const fs = require("fs-extra");
const path = require("path");

const app = express();

// Middleware
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static("public"));

// Configuración de Multer (para manejar subida de archivos)
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

// Ruta para subir una carpeta
app.post("/upload-folder", upload.array("files"), (req, res) => {
  try {
    const folderName = req.body.folderName || "default";
    const filePath = path.join(__dirname, "data/departamentos.json");

    // Leer o inicializar el archivo JSON
    let listings = fs.readJsonSync(filePath, { throws: false }) || [];

    // Añadir cada archivo subido al JSON
    req.files.forEach((file) => {
      listings.push({
        id: listings.length + 1,
        img: `uploads/${folderName}/${file.filename}`,
        location: req.body.location || "Sin ubicación",
        price: req.body.price || "Sin precio",
        description: req.body.description || "Sin descripción",
      });
    });

    // Guardar JSON actualizado
    fs.writeJsonSync(filePath, listings, { spaces: 2 });

    res.status(200).json({ message: "Archivos subidos correctamente", listings });
  } catch (error) {
    console.error("Error en el servidor:", error);
    res.status(500).json({ message: "Error interno del servidor" });
  }
});

// Exportar la app (compatible con Vercel serverless functions)
module.exports = app;
