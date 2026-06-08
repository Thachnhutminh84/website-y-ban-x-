import{r as l,f as o,a as d,b as i,c as m}from"./shared-BwPBOi5h.js";const g=document.querySelector("#app");function h(e){return`
    <article class="news-card">
      ${e.image?`<img class="news-card__image" src="${e.image}" alt="${e.title}" />`:'<div class="news-card__image news-card__image--empty"></div>'}
      <div class="news-card__body">
        <span class="news-card__badge">${e.category.name}</span>
        <h3>${e.title}</h3>
        <p class="news-card__meta">Ngay dang ${m(e.publishedAt)}</p>
        <p>${e.summary}</p>
        <a class="primary-link" href="/news-detail.html?slug=${encodeURIComponent(e.slug)}">Xem chi tiet</a>
      </div>
    </article>
  `}async function p(){const a=new URLSearchParams(window.location.search).get("category")||"";g.innerHTML=l({title:"He thong tin tuc tach rieng frontend/backend",subtitle:"Frontend chay rieng, backend Node.js + MySQL, du lieu tin tuc lay qua REST API.",content:`
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
          ${i("Dang tai du lieu...")}
        </div>
      </section>
    `});try{const[s,t]=await Promise.all([o(),d(a)]),c=document.querySelector("#category-filters"),r=document.querySelector("#news-list");c.innerHTML=[`<a class="filter-pill ${a===""?"is-active":""}" href="/index.html">Tat ca</a>`,...s.map(n=>`
        <a class="filter-pill ${a===n.slug?"is-active":""}" href="/index.html?category=${n.slug}">
          ${n.name}
        </a>
      `)].join(""),r.innerHTML=t.length?t.map(h).join(""):i("Chua co bai viet nao trong danh muc nay.","warning")}catch(s){document.querySelector("#news-list").innerHTML=i(s.message,"error")}}p();
