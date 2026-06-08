import { createNews, fetchCategories } from "./api.js";
import { renderMessage, renderShell } from "./shared.js";

const app = document.querySelector("#app");

function formMarkup() {
  return `
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
  `;
}

function setMessage(message, tone = "info") {
  document.querySelector("#form-message").innerHTML = renderMessage(message, tone);
}

function importDocx(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();

    reader.onload = async (event) => {
      try {
        const result = await window.mammoth.convertToHtml({ arrayBuffer: event.target.result }, {
          convertImage: window.mammoth.images.imgElement((image) =>
            image.read("base64").then((buffer) => ({
              src: `data:${image.contentType};base64,${buffer}`
            }))
          )
        });

        resolve(result.value);
      } catch (error) {
        reject(error);
      }
    };

    reader.onerror = () => reject(new Error("Khong doc duoc file Word"));
    reader.readAsArrayBuffer(file);
  });
}

async function render() {
  app.innerHTML = renderShell({
    title: "Admin dang bai",
    subtitle: "Trang tao bai viet moi cho kien truc frontend/backend Node.js.",
    content: formMarkup()
  });

  const categories = await fetchCategories();
  const categorySelect = document.querySelector("#category");
  categorySelect.innerHTML = categories
    .map((category) => `<option value="${category.slug}">${category.name}</option>`)
    .join("");

  document.querySelector("#publishedAt").value = new Date().toISOString().slice(0, 16);

  document.querySelector(".editor-toolbar").addEventListener("click", (event) => {
    const button = event.target.closest("button[data-command]");
    if (!button) {
      return;
    }

    document.execCommand(button.dataset.command, false);
  });

  document.querySelector("#wordFile").addEventListener("change", async (event) => {
    const file = event.target.files[0];
    if (!file) {
      return;
    }

    if (!file.name.toLowerCase().endsWith(".docx")) {
      setMessage("He thong chi ho tro file .docx.", "error");
      return;
    }

    try {
      setMessage("Dang import file Word...");
      const html = await importDocx(file);
      const editor = document.querySelector("#editor");
      editor.innerHTML = html;

      const temp = document.createElement("div");
      temp.innerHTML = html;
      const firstHeading = temp.querySelector("h1,h2,p");
      const plainText = temp.textContent.replace(/\s+/g, " ").trim();

      if (!document.querySelector("#title").value && firstHeading) {
        document.querySelector("#title").value = firstHeading.textContent.trim().slice(0, 180);
      }

      if (!document.querySelector("#summary").value && plainText) {
        document.querySelector("#summary").value = plainText.slice(0, 240);
      }

      setMessage("Import Word thanh cong.", "success");
    } catch (error) {
      setMessage(error.message, "error");
    }
  });

  document.querySelector("#news-form").addEventListener("submit", async (event) => {
    event.preventDefault();

    const editorHtml = document.querySelector("#editor").innerHTML.trim();
    if (!editorHtml) {
      setMessage("Noi dung bai viet dang rong.", "error");
      return;
    }

    const form = event.currentTarget;
    const formData = new FormData();
    formData.set("category", form.category.value);
    formData.set("title", form.title.value.trim());
    formData.set("summary", form.summary.value.trim());
    formData.set("content", editorHtml);
    formData.set("publishedAt", form.publishedAt.value || new Date().toISOString());
    formData.set("status", "published");

    if (form.image.files[0]) {
      formData.set("image", form.image.files[0]);
    }

    try {
      setMessage("Dang tao bai viet...");
      const created = await createNews(formData);
      setMessage("Dang bai thanh cong. Dang chuyen sang trang chi tiet...", "success");
      window.setTimeout(() => {
        window.location.href = `/news-detail.html?slug=${encodeURIComponent(created.slug)}`;
      }, 800);
    } catch (error) {
      setMessage(error.message, "error");
    }
  });
}

render().catch((error) => {
  app.innerHTML = renderShell({
    title: "Admin dang bai",
    subtitle: "Khong the khoi tao trang admin.",
    content: renderMessage(error.message, "error")
  });
});
