const express = require("express");
const upload = require("../../middleware/upload");
const controller = require("./news.controller");

const router = express.Router();

router.get("/", controller.listNews);
router.get("/:identifier", controller.getNews);
router.post("/", upload.single("image"), controller.createNews);
router.put("/:id", upload.single("image"), controller.updateNews);
router.delete("/:id", controller.deleteNews);

module.exports = router;
