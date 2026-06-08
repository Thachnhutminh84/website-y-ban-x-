const db = require("../../config/db");

function mapNewsRow(row) {
  return row;
}

async function listPublishedNews({ categorySlug }) {
  const params = [];
  let categoryClause = "";

  if (categorySlug) {
    categoryClause = "AND c.slug = ?";
    params.push(categorySlug);
  }

  const [rows] = await db.query(
    `SELECT n.id, n.title, n.slug, n.summary, n.content, n.image, n.status, n.views,
            n.published_at, n.created_at, n.updated_at,
            c.id AS category_id, c.name AS category_name, c.slug AS category_slug
     FROM news n
     JOIN categories c ON c.id = n.category_id
     WHERE n.status = 'published' ${categoryClause}
     ORDER BY n.published_at DESC, n.id DESC`,
    params
  );

  return rows.map(mapNewsRow);
}

async function findPublishedNewsByIdentifier(identifier) {
  const id = Number(identifier);
  const [rows] = await db.query(
    `SELECT n.id, n.title, n.slug, n.summary, n.content, n.image, n.status, n.views,
            n.published_at, n.created_at, n.updated_at,
            c.id AS category_id, c.name AS category_name, c.slug AS category_slug
     FROM news n
     JOIN categories c ON c.id = n.category_id
     WHERE n.status = 'published' AND (n.slug = ? OR n.id = ?)
     LIMIT 1`,
    [identifier, Number.isNaN(id) ? 0 : id]
  );

  return rows[0] || null;
}

async function incrementViews(newsId) {
  await db.query("UPDATE news SET views = views + 1 WHERE id = ?", [newsId]);
}

async function findNewsById(newsId) {
  const [rows] = await db.query(
    `SELECT id, category_id, title, slug, summary, content, image, status, views,
            published_at, created_at, updated_at
     FROM news
     WHERE id = ?
     LIMIT 1`,
    [newsId]
  );

  return rows[0] || null;
}

async function slugExists(slug, excludeId) {
  let query = `SELECT id FROM news WHERE slug = ?`;
  const params = [slug];

  if (excludeId != null) {
    query += " AND id != ?";
    params.push(excludeId);
  }

  query += " LIMIT 1";

  const [rows] = await db.query(query, params);

  return rows.length > 0;
}

async function createNews(data) {
  const [result] = await db.query(
    `INSERT INTO news
      (category_id, title, slug, summary, content, image, status, published_at, created_at, updated_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())`,
    [
      data.categoryId,
      data.title,
      data.slug,
      data.summary,
      data.content,
      data.image,
      data.status,
      data.publishedAt
    ]
  );

  return findNewsById(result.insertId);
}

async function updateNews(newsId, data) {
  await db.query(
    `UPDATE news
     SET category_id = ?,
         title = ?,
         slug = ?,
         summary = ?,
         content = ?,
         image = ?,
         status = ?,
         published_at = ?,
         updated_at = NOW()
     WHERE id = ?`,
    [
      data.categoryId,
      data.title,
      data.slug,
      data.summary,
      data.content,
      data.image,
      data.status,
      data.publishedAt,
      newsId
    ]
  );

  return findNewsById(newsId);
}

async function deleteNews(newsId) {
  const [result] = await db.query("DELETE FROM news WHERE id = ?", [newsId]);
  return result.affectedRows > 0;
}

module.exports = {
  listPublishedNews,
  findPublishedNewsByIdentifier,
  incrementViews,
  findNewsById,
  slugExists,
  createNews,
  updateNews,
  deleteNews
};
