const fs = require("fs");
const path = require("path");
const crypto = require("crypto");
const sanitizeHtml = require("sanitize-html");
const env = require("../config/env");

const imageMimeMap = {
  "image/jpeg": "jpg",
  "image/jpg": "jpg",
  "image/png": "png",
  "image/gif": "gif",
  "image/webp": "webp",
  "image/bmp": "bmp"
};

const allowedStyles = {
  "*": {
    color: [/^.+$/],
    "background-color": [/^.+$/],
    "font-size": [/^\d+(px|em|rem|%)$/],
    "font-weight": [/^(bold|normal|[1-9]00)$/],
    "font-style": [/^(normal|italic)$/],
    "text-decoration": [/^(none|underline|line-through)$/],
    "text-align": [/^(left|right|center|justify)$/],
    width: [/^\d+(px|%)$/],
    height: [/^\d+(px|%)$/]
  }
};

function ensureDirectory(directoryPath) {
  fs.mkdirSync(directoryPath, { recursive: true });
}

function toStoredAssetPath(fileName) {
  return `images/news-content/${fileName}`;
}

function toPublicAssetUrl(assetPath) {
  if (!assetPath) {
    return null;
  }

  if (/^https?:\/\//i.test(assetPath)) {
    return assetPath;
  }

  return `${env.appUrl}/${String(assetPath).replace(/^\/+/, "")}`;
}

function sanitizeArticleHtml(html) {
  return sanitizeHtml(String(html || ""), {
    allowedTags: sanitizeHtml.defaults.allowedTags.concat([
      "img",
      "figure",
      "figcaption",
      "table",
      "thead",
      "tbody",
      "tfoot",
      "tr",
      "th",
      "td",
      "h1",
      "h2",
      "h3",
      "h4",
      "h5",
      "h6",
      "span"
    ]),
    allowedAttributes: {
      "*": ["class", "style", "align"],
      a: ["href", "name", "target", "rel"],
      img: ["src", "alt", "title", "width", "height", "style"],
      table: ["style"],
      th: ["colspan", "rowspan", "style"],
      td: ["colspan", "rowspan", "style"]
    },
    allowedSchemes: ["http", "https", "data", "mailto"],
    allowedSchemesByTag: {
      img: ["http", "https", "data"]
    },
    allowedStyles
  });
}

function hasRenderableContent(html) {
  const plainText = String(html || "").replace(/<[^>]+>/g, " ").replace(/\s+/g, " ").trim();
  return Boolean(plainText) || /<img\b/i.test(String(html || ""));
}

async function extractInlineImages(html) {
  const targetDirectory = path.join(env.projectRoot, "images", "news-content");
  ensureDirectory(targetDirectory);

  let output = html;
  const pattern = /src=(["'])data:(image\/[a-zA-Z0-9.+-]+);base64,([^"']+)\1/gi;
  const matches = [...html.matchAll(pattern)];

  for (const match of matches) {
    const mimeType = match[2].toLowerCase();
    const extension = imageMimeMap[mimeType] || "png";
    const fileName = `news-content-${Date.now()}-${crypto.randomUUID()}.${extension}`;
    const filePath = path.join(targetDirectory, fileName);
    const fileBuffer = Buffer.from(match[3], "base64");

    await fs.promises.writeFile(filePath, fileBuffer);

    output = output.replace(match[0], `src="${toStoredAssetPath(fileName)}"`);
  }

  return output;
}

async function normalizeArticleContent(html) {
  const sanitized = sanitizeArticleHtml(html);
  return extractInlineImages(sanitized);
}

function decorateContentForResponse(html) {
  return String(html || "").replace(
    /(src|href)=(["'])(images\/[^"']+)\2/gi,
    (_, attribute, quote, assetPath) => `${attribute}=${quote}${toPublicAssetUrl(assetPath)}${quote}`
  );
}

module.exports = {
  hasRenderableContent,
  normalizeArticleContent,
  toPublicAssetUrl,
  decorateContentForResponse
};
