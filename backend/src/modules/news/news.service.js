const createHttpError = require("../../utils/http-error");
const slugify = require("../../utils/slugify");
const { hasRenderableContent, normalizeArticleContent, toPublicAssetUrl, decorateContentForResponse } = require("../../utils/content");
const categoryRepository = require("../categories/categories.repository");
const newsRepository = require("./news.repository");

function toStoredUploadPath(file) {
  return file ? `images/news/${file.filename}` : null;
}

function serializeNews(row) {
  return {
    id: row.id,
    title: row.title,
    slug: row.slug,
    summary: row.summary,
    content: decorateContentForResponse(row.content),
    image: toPublicAssetUrl(row.image),
    status: row.status,
    views: row.views,
    publishedAt: row.published_at,
    createdAt: row.created_at,
    updatedAt: row.updated_at,
    category: {
      id: row.category_id,
      name: row.category_name,
      slug: row.category_slug
    }
  };
}

async function buildUniqueSlug(title, excludeId = null) {
  const baseSlug = slugify(title);
  let candidate = baseSlug;
  let suffix = 1;

  while (await newsRepository.slugExists(candidate, excludeId)) {
    const suffixText = `-${suffix}`;
    candidate = `${baseSlug.slice(0, 255 - suffixText.length)}${suffixText}`;
    suffix += 1;
  }

  return candidate;
}

async function resolveCategoryId(categorySlug) {
  const category = await categoryRepository.findCategoryBySlug(categorySlug);

  if (!category) {
    throw createHttpError(400, "Invalid category");
  }

  return category.id;
}

function normalizePublishDate(value) {
  if (!value) {
    return new Date();
  }

  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) {
    throw createHttpError(400, "Invalid publishedAt value");
  }

  return parsed;
}

async function listNews({ category }) {
  const rows = await newsRepository.listPublishedNews({ categorySlug: category || null });
  return rows.map(serializeNews);
}

async function getNews(identifier) {
  const row = await newsRepository.findPublishedNewsByIdentifier(identifier);

  if (!row) {
    throw createHttpError(404, "News not found");
  }

  await newsRepository.incrementViews(row.id);
  row.views += 1;
  return serializeNews(row);
}

async function createNews(input, file) {
  const title = String(input.title || "").trim();
  const summary = String(input.summary || "").trim();
  const categorySlug = String(input.category || "").trim();
  const status = String(input.status || "published").trim() || "published";
  const rawContent = input.content || "";

  if (!title || !summary || !categorySlug) {
    throw createHttpError(400, "title, summary and category are required");
  }

  const content = await normalizeArticleContent(rawContent);
  if (!hasRenderableContent(content)) {
    throw createHttpError(400, "content is required");
  }

  const categoryId = await resolveCategoryId(categorySlug);
  const slug = await buildUniqueSlug(title);
  const image = toStoredUploadPath(file) || "images/news-default.jpg";

  const row = await newsRepository.createNews({
    categoryId,
    title,
    slug,
    summary,
    content,
    image,
    status,
    publishedAt: normalizePublishDate(input.publishedAt)
  });

  const category = await categoryRepository.findCategoryBySlug(categorySlug);

  return serializeNews({
    ...row,
    views: row.views || 0,
    created_at: row.created_at,
    updated_at: row.updated_at,
    category_id: category.id,
    category_name: category.name,
    category_slug: category.slug
  });
}

async function updateNews(newsId, input, file) {
  if (!Number.isInteger(newsId) || newsId <= 0) {
    throw createHttpError(400, "Invalid news id");
  }

  const existing = await newsRepository.findNewsById(newsId);
  if (!existing) {
    throw createHttpError(404, "News not found");
  }

  const title = String(input.title || existing.title).trim();
  const summary = String(input.summary || existing.summary).trim();
  const categorySlug = String(input.category || "").trim();
  const status = String(input.status || existing.status || "published").trim();
  const rawContent = input.content != null ? input.content : existing.content;

  if (!title || !summary || !categorySlug) {
    throw createHttpError(400, "title, summary and category are required");
  }

  const content = await normalizeArticleContent(rawContent);
  if (!hasRenderableContent(content)) {
    throw createHttpError(400, "content is required");
  }

  const categoryId = await resolveCategoryId(categorySlug);
  const slug = await buildUniqueSlug(title, newsId);
  const image = toStoredUploadPath(file) || existing.image || "images/news-default.jpg";

  const row = await newsRepository.updateNews(newsId, {
    categoryId,
    title,
    slug,
    summary,
    content,
    image,
    status,
    publishedAt: normalizePublishDate(input.publishedAt || existing.published_at)
  });

  const category = await categoryRepository.findCategoryBySlug(categorySlug);

  return serializeNews({
    ...row,
    views: existing.views || 0,
    category_id: category.id,
    category_name: category.name,
    category_slug: category.slug,
    created_at: existing.created_at,
    updated_at: row.updated_at
  });
}

async function removeNews(newsId) {
  if (!Number.isInteger(newsId) || newsId <= 0) {
    throw createHttpError(400, "Invalid news id");
  }

  const deleted = await newsRepository.deleteNews(newsId);

  if (!deleted) {
    throw createHttpError(404, "News not found");
  }
}

module.exports = {
  listNews,
  getNews,
  createNews,
  updateNews,
  removeNews
};
