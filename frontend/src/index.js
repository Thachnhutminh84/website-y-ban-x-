import { fetchCategories, fetchNews } from "./api.js";
import { formatDate, renderMessage, renderShell } from "./shared.js";

const app = document.querySelector("#app");

function articleCard(article) {
  const imageMarkup = article.image
    ? `<img class="news-card__image" src="${article.image}" alt="${article.title}" />`
    : `<div class="news-card__image news-card__image--empty"></div>`;

  return `
    <article class="news-card">
      ${imageMarkup}
      <div class="news-card__body">
        <span class="news-card__badge">${article.category.name}</span>
        <h3>${article.title}</h3>
        <p class="news-card__meta">Ngay dang ${formatDate(article.publishedAt)}</p>
        <p>${article.summary}</p>
        <a class="primary-link" href="/news-detail.html?slug=${encodeURIComponent(article.slug)}">Xem chi tiet</a>
      </div>
    </article>
  `;
}

async function render() {
  const params = new URLSearchParams(window.location.search);
  const activeCategory = params.get("category") || "";

  app.innerHTML = renderShell({
    title: "He thong tin tuc tach rieng frontend/backend",
    subtitle: "Frontend chay rieng, backend Node.js + MySQL, du lieu tin tuc lay qua REST API.",
    content: `
      <section class="panel">
        <div class="panel__header">
          <div>
            <span class="section-label">Danh muc</span>
            <h2>Bo loc tin tuc</h2>
          </div>
        </div>
        <div id="category-filters" class="filter-row"></div>
      </section>
      <section class="panel">
        <div class="panel__header">
          <div>
            <span class="section-label">Tin moi</span>
            <h2>Danh sach bai viet</h2>
          </div>
        </div>
        <div id="news-list" class="news-grid">
          ${renderMessage("Dang tai du lieu...")}
        </div>
      </section>
    `
  });

  try {
    const [categories, news] = await Promise.all([
      fetchCategories(),
      fetchNews(activeCategory)
    ]);

    const filterContainer = document.querySelector("#category-filters");
    const listContainer = document.querySelector("#news-list");

    filterContainer.innerHTML = [
      `<a class="filter-pill ${activeCategory === "" ? "is-active" : ""}" href="/index.html">Tat ca</a>`,
      ...categories.map((category) => `
        <a class="filter-pill ${activeCategory === category.slug ? "is-active" : ""}" href="/index.html?category=${category.slug}">
          ${category.name}
        </a>
      `)
    ].join("");

    listContainer.innerHTML = news.length
      ? news.map(articleCard).join("")
      : renderMessage("Chua co bai viet nao trong danh muc nay.", "warning");
  } catch (error) {
    document.querySelector("#news-list").innerHTML = renderMessage(error.message, "error");
  }
}

render();
