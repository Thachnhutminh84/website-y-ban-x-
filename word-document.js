(function () {
    function cleanText(value) {
        return (value || "").replace(/\s+/g, " ").trim();
    }

    function hasBorderValue(value) {
        if (!value) return false;
        const lower = value.toLowerCase();
        if (lower.indexOf("border") === -1) return false;
        if (/\bborder\s*:\s*(none|0|transparent|inherit)\b/i.test(lower)) return false;
        if (/\bborder-color\s*:\s*transparent\b/i.test(lower)) return false;
        return true;
    }

    function tableHasVisibleBorder(table) {
        if (!table) {
            return false;
        }

        const borderAttr = cleanText(table.getAttribute("border"));
        if (borderAttr && borderAttr !== "0") {
            return true;
        }

        if (hasBorderValue(table.getAttribute("style"))) {
            return true;
        }

        return Array.from(table.querySelectorAll("th, td")).some(function (cell) {
            return hasBorderValue(cell.getAttribute("style"));
        });
    }

    function classifyTables(root) {
        root.querySelectorAll("table").forEach(function (table) {
            table.classList.remove("word-layout-table", "word-data-table", "word-header-table");
            table.classList.add(tableHasVisibleBorder(table) ? "word-data-table" : "word-layout-table");
        });
    }

    function hideHeaderTables(root) {
        const headerPattern = /CONG HOA XA HOI|UBND|UY BAN NHAN DAN|DOC LAP.*TU DO|HANH PHUC|LONG HIEP/i;
        let headerTablesHidden = 0;

        Array.from(root.querySelectorAll("table")).some(function (table) {
            if (headerTablesHidden >= 2) return true;

            const text = (table.textContent || "").replace(/\s+/g, " ").trim();
            if (headerPattern.test(text) && text.length < 200) {
                table.classList.remove("word-layout-table", "word-data-table");
                table.classList.add("word-header-table");
                headerTablesHidden++;
                return false;
            }

            const cells = table.querySelectorAll("td, th");
            if (cells.length <= 4) {
                const cellTexts = Array.from(cells).map(function (c) { return (c.textContent || "").replace(/\s+/g, " ").trim(); });
                const joined = cellTexts.join(" ");
                if (headerPattern.test(joined) && joined.length < 200) {
                    table.classList.remove("word-layout-table", "word-data-table");
                    table.classList.add("word-header-table");
                    headerTablesHidden++;
                    return false;
                }
            }

            return true;
        });
    }

    function trimEmptyBlocks(root) {
        while (root.children.length > 0) {
            const first = root.firstElementChild;
            if (!first || cleanText(first.textContent) || first.querySelector("img, table, hr")) {
                break;
            }
            root.removeChild(first);
        }

        while (root.children.length > 0) {
            const last = root.lastElementChild;
            if (!last || cleanText(last.textContent) || last.querySelector("img, table, hr")) {
                break;
            }
            root.removeChild(last);
        }
    }

    function looksLikeWordDocument(root, html) {
        const sourceHtml = html || root.innerHTML || "";
        if (sourceHtml.indexOf("word-document-content") !== -1 || sourceHtml.indexOf('data-doc-import="word"') !== -1) {
            return true;
        }

        if (root.querySelector("table")) {
            return true;
        }

        const text = cleanText(root.textContent || "");
        return /CONG HOA XA HOI CHU NGHIA|UY BAN NHAN DAN|UBND|DOC LAP - TU DO - HANH PHUC/i.test(text);
    }

    function extractMeta(root) {
        let title = "";
        const titleNode = root.querySelector("h1, h2");
        if (titleNode) {
            title = cleanText(titleNode.textContent).slice(0, 160);
        }

        if (!title) {
            const firstTextNode = Array.from(root.querySelectorAll("p, td, div")).find(function (node) {
                return cleanText(node.textContent);
            });
            if (firstTextNode) {
                title = cleanText(firstTextNode.textContent).slice(0, 160);
            }
        }

        const summarySource = cleanText(root.textContent || "");
        const summary = summarySource.slice(0, 200) + (summarySource.length > 200 ? "..." : "");

        return {
            title: title,
            summary: summary
        };
    }

    function ensureWrappedDocument(root) {
        const firstChild = root.firstElementChild;
        const existingWrapper = firstChild && firstChild.classList && firstChild.classList.contains("word-document-content")
            ? firstChild
            : null;
        if (existingWrapper) {
            classifyTables(existingWrapper);
            hideHeaderTables(existingWrapper);
            return existingWrapper;
        }

        if (!looksLikeWordDocument(root, root.innerHTML)) {
            return null;
        }

        const wrapper = document.createElement("div");
        wrapper.className = "word-document-content";
        wrapper.setAttribute("data-doc-import", "word");
        wrapper.innerHTML = root.innerHTML;
        root.innerHTML = "";
        root.appendChild(wrapper);
        classifyTables(wrapper);
        hideHeaderTables(wrapper);
        return wrapper;
    }

    function prepareImportedHtml(html) {
        const host = document.createElement("div");
        host.innerHTML = html || "";
        trimEmptyBlocks(host);
        classifyTables(host);
        hideHeaderTables(host);

        const meta = extractMeta(host);
        const wrapper = document.createElement("div");
        wrapper.className = "word-document-content";
        wrapper.setAttribute("data-doc-import", "word");
        wrapper.innerHTML = host.innerHTML;

        return {
            html: wrapper.outerHTML,
            title: meta.title,
            summary: meta.summary,
            imageCount: wrapper.querySelectorAll("img").length
        };
    }

    function hydrateEditor(editor) {
        if (!editor) {
            return;
        }

        const wrapper = ensureWrappedDocument(editor);
        if (wrapper) {
            editor.setAttribute("data-word-mode", "paper");
        } else {
            editor.removeAttribute("data-word-mode");
        }
    }

    function hydrateDetailSurface(surface) {
        if (!surface) {
            return false;
        }

        const wrapper = ensureWrappedDocument(surface);
        if (wrapper) {
            const detailContent = surface.closest(".detail-content");
            if (detailContent) {
                detailContent.classList.add("detail-content--word");
            }
            return true;
        }

        return false;
    }

    window.WordDocumentTools = {
        hydrateEditor: hydrateEditor,
        hydrateDetailSurface: hydrateDetailSurface,
        prepareImportedHtml: prepareImportedHtml
    };

    document.addEventListener("DOMContentLoaded", function () {
        const editor = document.getElementById("content-editor");
        if (editor) {
            hydrateEditor(editor);
        }

        document.querySelectorAll("[data-word-surface='detail']").forEach(function (surface) {
            hydrateDetailSurface(surface);
        });
    });
}());
