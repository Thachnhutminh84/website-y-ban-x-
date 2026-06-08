import "./styles.css";

export function formatDate(value) {
  if (!value) {
    return "";
  }

  return new Intl.DateTimeFormat("vi-VN", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric"
  }).format(new Date(value));
}

export function renderShell({ title, subtitle = "", actions = "", content }) {
  return `
    <div class="site-shell">
      <header class="hero">
        <div class="hero__backdrop"></div>
        <div class="hero__inner">
          <div class="hero__eyebrow">UBND Long Hiep</div>
          <div class="hero__copy">
            <h1>${title}</h1>
            <p>${subtitle}</p>
          </div>
          <div class="hero__actions">
            <a class="ghost-button" href="/index.html">Tin tuc</a>
            <a class="ghost-button" href="/admin-news-new.html">Dang bai</a>
            ${actions}
          </div>
        </div>
      </header>
      <main class="page">${content}</main>
    </div>
  `;
}

export function renderMessage(message, tone = "info") {
  return `<div class="message message--${tone}">${message}</div>`;
}
