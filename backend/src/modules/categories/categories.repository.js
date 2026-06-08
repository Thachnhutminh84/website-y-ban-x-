const db = require("../../config/db");

async function listActiveCategories() {
  const [rows] = await db.query(
    `SELECT id, name, slug, description, display_order
     FROM categories
     WHERE status = 'active'
     ORDER BY display_order ASC, id ASC`
  );

  return rows;
}

async function findCategoryBySlug(slug) {
  const [rows] = await db.query(
    `SELECT id, name, slug
     FROM categories
     WHERE slug = ?
     LIMIT 1`,
    [slug]
  );

  return rows[0] || null;
}

module.exports = {
  listActiveCategories,
  findCategoryBySlug
};
