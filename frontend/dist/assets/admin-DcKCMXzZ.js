import{r as l,b as m,f as p,e as g}from"./shared-BwPBOi5h.js";const u=document.querySelector("#app");function b(){return`
    <section class="panel panel--admin">
      <div class="panel__header">
        <div>
          <span class="section-label">Admin</span>
          <h2>Them bai viet moi</h2>
        </div>
      </div>
      <form id="news-form" class="news-form">
        <div class="field-grid">
          <label class="field">
            <span>Danh muc</span>
            <select id="category" name="category" required></select>
          </label>
          <label class="field">
            <span>Ngay dang</span>
            <input type="datetime-local" id="publishedAt" name="publishedAt" />
          </label>
        </div>

        <label class="field">
          <span>Tieu de</span>
          <input type="text" id="title" name="title" required />
        </label>

        <label class="field">
          <span>Tom tat</span>
          <textarea id="summary" name="summary" rows="3" required></textarea>
        </label>

        <div class="field">
          <span>Anh dai dien</span>
          <input type="file" id="image" name="image" accept="image/*" />
        </div>

        <div class="field">
          <span>Import tu Word (.docx)</span>
          <input type="file" id="wordFile" accept=".docx" />
          <small class="field-hint">Frontend doc file .docx bang Mammoth, backend Node.js se sanitize va luu HTML.</small>
        </div>

        <div class="field">
          <span>Noi dung bai viet</span>
          <div class="editor-toolbar">
            <button type="button" data-command="bold">Bold</button>
            <button type="button" data-command="italic">Italic</button>
            <button type="button" data-command="underline">Underline</button>
            <button type="button" data-command="insertUnorderedList">List</button>
          </div>
          <div id="editor" class="editor" contenteditable="true"></div>
        </div>

        <div id="form-message"></div>

        <div class="form-footer">
          <button class="primary-button" type="submit">Xuat ban tin tuc</button>
          <a class="ghost-button" href="/index.html">Quay lai danh sach</a>
        </div>
      </form>
    </section>
  `}function r(i,s="info"){document.querySelector("#form-message").innerHTML=m(i,s)}function f(i){return new Promise((s,n)=>{const t=new FileReader;t.onload=async a=>{try{const e=await window.mammoth.convertToHtml({arrayBuffer:a.target.result},{convertImage:window.mammoth.images.imgElement(o=>o.read("base64").then(d=>({src:`data:${o.contentType};base64,${d}`})))});s(e.value)}catch(e){n(e)}},t.onerror=()=>n(new Error("Khong doc duoc file Word")),t.readAsArrayBuffer(i)})}async function h(){u.innerHTML=l({title:"Admin dang bai",subtitle:"Trang tao bai viet moi cho kien truc frontend/backend Node.js.",content:b()});const i=await p(),s=document.querySelector("#category");s.innerHTML=i.map(n=>`<option value="${n.slug}">${n.name}</option>`).join(""),document.querySelector("#publishedAt").value=new Date().toISOString().slice(0,16),document.querySelector(".editor-toolbar").addEventListener("click",n=>{const t=n.target.closest("button[data-command]");t&&document.execCommand(t.dataset.command,!1)}),document.querySelector("#wordFile").addEventListener("change",async n=>{const t=n.target.files[0];if(t){if(!t.name.toLowerCase().endsWith(".docx")){r("He thong chi ho tro file .docx.","error");return}try{r("Dang import file Word...");const a=await f(t),e=document.querySelector("#editor");e.innerHTML=a;const o=document.createElement("div");o.innerHTML=a;const d=o.querySelector("h1,h2,p"),c=o.textContent.replace(/\s+/g," ").trim();!document.querySelector("#title").value&&d&&(document.querySelector("#title").value=d.textContent.trim().slice(0,180)),!document.querySelector("#summary").value&&c&&(document.querySelector("#summary").value=c.slice(0,240)),r("Import Word thanh cong.","success")}catch(a){r(a.message,"error")}}}),document.querySelector("#news-form").addEventListener("submit",async n=>{n.preventDefault();const t=document.querySelector("#editor").innerHTML.trim();if(!t){r("Noi dung bai viet dang rong.","error");return}const a=n.currentTarget,e=new FormData;e.set("category",a.category.value),e.set("title",a.title.value.trim()),e.set("summary",a.summary.value.trim()),e.set("content",t),e.set("publishedAt",a.publishedAt.value||new Date().toISOString()),e.set("status","published"),a.image.files[0]&&e.set("image",a.image.files[0]);try{r("Dang tao bai viet...");const o=await g(e);r("Dang bai thanh cong. Dang chuyen sang trang chi tiet...","success"),window.setTimeout(()=>{window.location.href=`/news-detail.html?slug=${encodeURIComponent(o.slug)}`},800)}catch(o){r(o.message,"error")}})}h().catch(i=>{u.innerHTML=l({title:"Admin dang bai",subtitle:"Khong the khoi tao trang admin.",content:m(i.message,"error")})});
