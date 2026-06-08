import { fetchNewsDetail } from "./api.js";
import { formatDate, renderMessage, renderShell } from "./shared.js";

const app = document.querySelector("#app");

async function render() {
  const params = new URLSearchParams(window.location.search);
  const slug = params.get("slug");

  if (!slug) {
    app.innerHTML = renderShell({
      title: "Chi tiet tin tuc",
      subtitle: "Khong tim thay bai viet duoc yeu cau.",
      content: renderMessage("Thieu slug bai viet.", "error")
    });
    return;
  }

  app.innerHTML = renderShell({
    title: "Chi tiet tin tuc",
    subtitle: "Dang tai bai viet tu backend Node.js.",
    content: renderMessage("Dang tai du lieu...")
  });

  try {
    const article = await fetchNewsDetail(slug);

    app.innerHTML = renderShell({
      title: article.title,
      subtitle: `${article.category.name} | ${formatDate(article.publishedAt)} | ${article.views} luot xem`,
      actions: `<a class="ghost-button" href="/index.html?category=${article.category.slug}">Quay lai danh muc</a>`,
      content: `
        <article class="detail-panel">
          ${article.image ? `<img class="detail-cover" src="${article.image}" alt="${article.title}" />` : ""}
          <div class="detail-content">${article.content}</div>
        </article>
      `
    });
  } catch (error) {
    app.innerHTML = renderShell({
      title: "Chi tiet tin tuc",
      subtitle: "Khong the tai noi dung.",
      content: renderMessage(error.message, "error")
    });
  }
}

render();
