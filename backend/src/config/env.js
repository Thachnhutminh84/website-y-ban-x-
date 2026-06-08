const path = require("path");
const dotenv = require("dotenv");

const backendRoot = path.resolve(__dirname, "../..");
dotenv.config({ path: path.join(backendRoot, ".env"), quiet: true });

const defaultPort = Number(process.env.PORT || 4000);

module.exports = {
  port: defaultPort,
  appUrl: process.env.APP_URL || `http://localhost:${defaultPort}`,
  frontendOrigin: process.env.FRONTEND_ORIGIN || "http://localhost:5173",
  db: {
    host: process.env.DB_HOST || "localhost",
    port: Number(process.env.DB_PORT || 3306),
    user: process.env.DB_USER || "root",
    password: process.env.DB_PASSWORD || "",
    database: process.env.DB_NAME || "ubnd_longhiep"
  },
  backendRoot,
  projectRoot: path.resolve(backendRoot, "..")
};
