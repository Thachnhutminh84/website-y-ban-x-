const fs = require("fs");
const path = require("path");
const multer = require("multer");
const env = require("../config/env");

const destination = path.join(env.projectRoot, "images", "news");
fs.mkdirSync(destination, { recursive: true });

const storage = multer.diskStorage({
  destination: (req, file, callback) => callback(null, destination),
  filename: (req, file, callback) => {
    const extension = path.extname(file.originalname || "").toLowerCase() || ".jpg";
    callback(null, `news-${Date.now()}-${Math.round(Math.random() * 1e9)}${extension}`);
  }
});

function fileFilter(req, file, callback) {
  if (!file.mimetype.startsWith("image/")) {
    callback(new Error("Only image uploads are allowed"));
    return;
  }

  callback(null, true);
}

module.exports = multer({
  storage,
  fileFilter,
  limits: {
    fileSize: 5 * 1024 * 1024
  }
});
