const express = require("express");
const cors = require("cors");
const helmet = require("helmet");
const morgan = require("morgan");
const path = require("path");
const env = require("./config/env");
const categoriesRoutes = require("./modules/categories/categories.routes");
const newsRoutes = require("./modules/news/news.routes");
const notFound = require("./middleware/not-found");
const errorHandler = require("./middleware/error-handler");

const app = express();

app.use(
  cors({
    origin: env.frontendOrigin
  })
);
app.use(helmet({ crossOriginResourcePolicy: false }));
app.use(morgan("dev"));
app.use(express.json({ limit: "50mb" }));
app.use(express.urlencoded({ extended: true, limit: "50mb" }));

app.use("/images", express.static(path.join(env.projectRoot, "images")));

app.get("/api/health", (req, res) => {
  res.json({ status: "ok" });
});

app.use("/api/categories", categoriesRoutes);
app.use("/api/news", newsRoutes);

app.use(notFound);
app.use(errorHandler);

module.exports = app;
