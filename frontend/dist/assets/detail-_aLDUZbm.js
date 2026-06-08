import{r as e,b as i,d as r,c}from"./shared-BwPBOi5h.js";const a=document.querySelector("#app");async function s(){const n=new URLSearchParams(window.location.search).get("slug");if(!n){a.innerHTML=e({title:"Chi tiet tin tuc",subtitle:"Khong tim thay bai viet duoc yeu cau.",content:i("Thieu slug bai viet.","error")});return}a.innerHTML=e({title:"Chi tiet tin tuc",subtitle:"Dang tai bai viet tu backend Node.js.",content:i("Dang tai du lieu...")});try{const t=await r(n);a.innerHTML=e({title:t.title,subtitle:`${t.category.name} | ${c(t.publishedAt)} | ${t.views} luot xem`,actions:`<a class="ghost-button" href="/index.html?category=${t.category.slug}">Quay lai danh muc</a>`,content:`
        <article class="detail-panel">
          ${t.image?`<img class="detail-cover" src="${t.image}" alt="${t.title}" />`:""}
          <div class="detail-content">${t.content}</div>
        </article>
      `})}catch(t){a.innerHTML=e({title:"Chi tiet tin tuc",subtitle:"Khong the tai noi dung.",content:i(t.message,"error")})}}s();
