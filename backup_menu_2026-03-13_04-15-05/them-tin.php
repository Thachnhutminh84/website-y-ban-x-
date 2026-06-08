<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'auth.php';

authRequireRole(['admin', 'editor']);

$currentRole = authCurrentRole();
$displayName = authDisplayName();

$category = isset($_GET['cat']) ? $_GET['cat'] : 'su-kien';
$valid_categories = ['xay-dung-dang', 'mat-tran', 'an-ninh', 'su-kien', 'tuyen-truyen', 'giao-duc'];
if (!in_array($category, $valid_categories, true)) {
    $category = 'su-kien';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm tin tức - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css?v=2.0">
    <script src="dropdown.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.6.0/mammoth.browser.min.js"></script>
    <style>
    .content-editor {
        border: 1px solid #ddd;
        border-radius: 8px;
        min-height: 400px;
        padding: 15px;
        background: white;
        font-family: Arial, sans-serif;
        line-height: 1.6;
    }
    .content-editor:focus {
        outline: 2px solid #c41e3a;
    }
    .editor-toolbar {
        background: #f5f5f5;
        padding: 10px;
        border: 1px solid #ddd;
        border-bottom: none;
        border-radius: 8px 8px 0 0;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    .editor-btn {
        padding: 8px 12px;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
    }
    .editor-btn:hover {
        background: #e0e0e0;
    }
    .btn-import-word {
        background: #27ae60 !important;
        color: white !important;
        font-weight: 600;
    }
    .content-editor img {
        max-width: 100%;
        height: auto;
        display: block;
        margin: 20px 0;
        border-radius: 8px;
    }
    .content-editor h1, .content-editor h2, .content-editor h3 {
        margin-top: 20px;
        margin-bottom: 10px;
    }
    .content-editor p {
        margin-bottom: 15px;
    }
    .content-editor table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
        border: 2px solid #000;
        background: white;
        font-size: 14px;
    }
    .content-editor table th {
        background: #fff;
        border: 1px solid #000;
        padding: 10px 8px;
        text-align: center;
        font-weight: bold;
        color: #000;
        word-wrap: break-word;
        word-break: break-word;
    }
    .content-editor table td {
        border: 1px solid #000;
        padding: 10px 8px;
        color: #000;
        text-align: left;
        word-wrap: break-word;
        word-break: break-word;
        vertical-align: top;
    }
    .content-editor table tr {
        background: white;
    }
    .content-editor table tr:hover {
        background: white;
    }
    
    /* Fullscreen Editor */
    .editor-fullscreen {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 9999 !important;
        background: white !important;
        padding: 20px !important;
        box-sizing: border-box !important;
        overflow-y: auto !important;
        font-size: 18px !important;
        line-height: 1.8 !important;
    }
    
    .editor-fullscreen-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0,0,0,0.9);
        z-index: 9998;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .fullscreen-controls {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        display: flex;
        gap: 10px;
    }
    
    .fullscreen-btn {
        padding: 12px 20px;
        background: #c41e3a;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 16px;
        font-weight: bold;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }
    
    .fullscreen-btn:hover {
        background: #a01628;
    }
    </style>
    <link rel="stylesheet" href="word-document.css?v=1.0">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <img src="images/logo.png" alt="Logo UBND Xã Long Hiệp">
                <div class="header-text">
                    <h1>ỦY BAN NHÂN DÂN XÃ LONG HIỆP</h1>
                    <p>Phục vụ nhân dân - Xây dựng quê hương</p>
                </div>
            </div>
            <nav>
                <ul class="menu">
                    <li><a href="index.php">Trang chủ</a></li>
                    <li class="dropdown">
                        <a href="tin-tuc.php" class="active">Tin Tức - Thông Báo</a>
                        <button class="dropdown-toggle" onclick="toggleDropdown(event)">▼</button>
                        <ul class="dropdown-menu">
                            <li><a href="cong-tac-xay-dung-dang.php">Công tác xây dựng Đảng</a></li>
                            <li><a href="mat-tran-doan-the.php">Mặt trận đoàn thể</a></li>
                            <li><a href="an-ninh-trat-tu.php">An ninh trật tự</a></li>
                            <li><a href="tin-tuc-su-kien.php">Tin tức sự kiện</a></li>
                            <li><a href="thong-tin-tuyen-truyen.php">Thông tin tuyên truyền</a></li>
                            <li><a href="giao-duc-dao-tao.php">Giáo dục và đào tạo</a></li>
                        </ul>
                    </li>
                    <li><a href="phong-ban.php">Phòng Ban</a></li>
                    <li><a href="lanh-dao.php">Lãnh đạo</a></li>
                    <li><a href="thu-tuc-hanh-chinh.php">Thủ tục</a></li>
                    <li><a href="lien-he.php">Liên Hệ</a></li>
                    <li class="admin-info">
                        👤 <?php echo htmlspecialchars(authRoleLabel($currentRole), ENT_QUOTES, 'UTF-8'); ?>
                        <a href="tin-tuc.php"><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></a>
                        <a href="logout.php">Đăng xuất</a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="page-header">
            <div class="container">
                <h2>Thêm tin tức mới</h2>
                <p>Điền thông tin hoặc import từ Word để thêm tin tức vào hệ thống</p>
            </div>
        </section>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="container" style="margin-top: 20px;">
                <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; border: 1px solid #f5c6cb;">
                    <?php
                    echo htmlspecialchars($_SESSION['error']);
                    unset($_SESSION['error']);
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="container" style="margin-top: 20px;">
                <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; border: 1px solid #c3e6cb;">
                    <?php
                    echo htmlspecialchars($_SESSION['success']);
                    unset($_SESSION['success']);
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <section class="form-section">
            <div class="container">
                <form action="xu-ly-them-tin.php" method="POST" enctype="multipart/form-data" class="news-form">
                    
                    <div class="form-group">
                        <label for="category">Danh mục <span class="required">*</span></label>
                        <select id="category" name="category" required>
                            <option value="">-- Chọn danh mục --</option>
                            <option value="xay-dung-dang" <?php echo $category == 'xay-dung-dang' ? 'selected' : ''; ?>>Công tác xây dựng Đảng</option>
                            <option value="mat-tran" <?php echo $category == 'mat-tran' ? 'selected' : ''; ?>>Mặt trận đoàn thể</option>
                            <option value="an-ninh" <?php echo $category == 'an-ninh' ? 'selected' : ''; ?>>An ninh trật tự</option>
                            <option value="su-kien" <?php echo $category == 'su-kien' ? 'selected' : ''; ?>>Tin tức sự kiện</option>
                            <option value="tuyen-truyen" <?php echo $category == 'tuyen-truyen' ? 'selected' : ''; ?>>Thông tin tuyên truyền</option>
                            <option value="giao-duc" <?php echo $category == 'giao-duc' ? 'selected' : ''; ?>>Giáo dục và đào tạo</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="title">Tiêu đề <span class="required">*</span></label>
                        <input type="text" id="title" name="title" required placeholder="Nhập tiêu đề tin tức">
                    </div>

                    <div class="form-group">
                        <label for="date">Ngày đăng <span class="required">*</span></label>
                        <input type="date" id="date" name="date" required value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="image">Ảnh đại diện</label>
                        <input type="file" id="image" name="image">
                        <small>Định dạng: JPG, PNG, GIF. Kích thước tối đa: 2MB</small>
                    </div>

                    <div class="form-group">
                        <label for="summary">Tóm tắt <span class="required">*</span></label>
                        <textarea id="summary" name="summary" rows="3" required placeholder="Nhập tóm tắt ngắn gọn về tin tức"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="content">Nội dung đầy đủ <span class="required">*</span></label>
                        <div style="margin-bottom: 10px;">
                            <input type="file" id="import-word-file" accept=".docx" style="display: none;" onchange="importWordFile(event)">
                            <button type="button" onclick="document.getElementById('import-word-file').click()" class="editor-btn btn-import-word">
                                📄 Import từ Word (tự động lấy hình ảnh)
                            </button>
                            <small style="display: block; margin-top: 5px; color: #7f8c8d;">
                                Chọn file Word (.docx) - Hệ thống sẽ tự động đọc nội dung, định dạng và hình ảnh
                            </small>
                        </div>
                        <div class="editor-toolbar">
                            <button type="button" class="editor-btn" onclick="document.execCommand('bold')"><b>B</b> Đậm</button>
                            <button type="button" class="editor-btn" onclick="document.execCommand('italic')"><i>I</i> Nghiêng</button>
                            <button type="button" class="editor-btn" onclick="document.execCommand('underline')"><u>U</u> Gạch chân</button>
                            <button type="button" class="editor-btn" onclick="document.execCommand('formatBlock', false, 'h2')">H2 Tiêu đề</button>
                            <button type="button" class="editor-btn" onclick="document.execCommand('formatBlock', false, 'h3')">H3 Tiêu đề nhỏ</button>
                            <button type="button" class="editor-btn" onclick="document.execCommand('insertUnorderedList')">• Danh sách</button>
                            <button type="button" class="editor-btn" onclick="insertTable()" style="background: #e74c3c; color: white;">📊 Chèn bảng</button>
                            <input type="file" id="insert-image" accept="image/*" style="display: none;" onchange="insertImage(event)">
                            <button type="button" class="editor-btn" onclick="document.getElementById('insert-image').click()" style="background: #3498db; color: white;">📷 Chèn ảnh</button>
                            <a href="them-tin-fullscreen.php?cat=<?php echo $category; ?>" class="editor-btn" style="background: #9b59b6; color: white; text-decoration: none; display: inline-block;">🖥️ Chế độ toàn màn hình</a>
                        </div>
                        <div id="content-editor" class="content-editor" contenteditable="true"></div>
                        <textarea id="content" name="content" style="display: none;"></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">✓ Xuất bản tin tức</button>
                        <a href="tin-tuc.php?cat=<?php echo $category; ?>" class="btn-cancel">✗ Hủy bỏ</a>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>

    <script>
    // Hàm toggle fullscreen cho editor
    function toggleFullscreen() {
        const editor = document.getElementById('content-editor');
        const btn = document.getElementById('fullscreen-btn');
        
        if (editor.classList.contains('editor-fullscreen')) {
            // Thoát fullscreen
            editor.classList.remove('editor-fullscreen');
            btn.innerHTML = '🔍 Toàn màn hình';
            btn.style.background = '#9b59b6';
            
            // Xóa overlay và controls
            const overlay = document.querySelector('.editor-fullscreen-overlay');
            const controls = document.querySelector('.fullscreen-controls');
            if (overlay) overlay.remove();
            if (controls) controls.remove();
            
            // Khôi phục scroll body
            document.body.style.overflow = '';
        } else {
            // Vào fullscreen
            editor.classList.add('editor-fullscreen');
            btn.innerHTML = '❌ Thoát toàn màn hình';
            btn.style.background = '#e74c3c';
            
            // Tạo overlay
            const overlay = document.createElement('div');
            overlay.className = 'editor-fullscreen-overlay';
            document.body.appendChild(overlay);
            
            // Tạo controls
            const controls = document.createElement('div');
            controls.className = 'fullscreen-controls';
            controls.innerHTML = `
                <button class="fullscreen-btn" onclick="toggleFullscreen()">❌ Thoát toàn màn hình</button>
                <button class="fullscreen-btn" onclick="document.execCommand('bold')" style="background: #27ae60;">B Đậm</button>
                <button class="fullscreen-btn" onclick="insertTable()" style="background: #e74c3c;">📊 Bảng</button>
            `;
            document.body.appendChild(controls);
            
            // Ẩn scroll body
            document.body.style.overflow = 'hidden';
            
            // Focus vào editor
            editor.focus();
        }
    }
    
    // Hàm chèn bảng
    function insertTable() {
        const rows = prompt('Nhập số hàng (mặc định 3):', '3');
        if (rows === null) return;
        
        const cols = prompt('Nhập số cột (mặc định 3):', '3');
        if (cols === null) return;
        
        const numRows = parseInt(rows) || 3;
        const numCols = parseInt(cols) || 3;
        
        let tableHtml = '<table style="width: 100%; border-collapse: collapse; margin: 20px 0; border: 2px solid #000;">';
        
        // Tạo header row
        tableHtml += '<tr style="background: #fff;">';
        for (let i = 0; i < numCols; i++) {
            tableHtml += '<th style="border: 1px solid #000; padding: 10px 8px; text-align: center; font-weight: bold; color: #000;">Cột ' + (i + 1) + '</th>';
        }
        tableHtml += '</tr>';
        
        // Tạo data rows
        for (let i = 0; i < numRows; i++) {
            tableHtml += '<tr style="background: #fff;">';
            for (let j = 0; j < numCols; j++) {
                tableHtml += '<td style="border: 1px solid #000; padding: 10px 8px; color: #000;"></td>';
            }
            tableHtml += '</tr>';
        }
        
        tableHtml += '</table><br>';
        
        const editor = document.getElementById('content-editor');
        const div = document.createElement('div');
        div.innerHTML = tableHtml;
        editor.appendChild(div.firstChild);
        editor.appendChild(document.createElement('br'));
    }
    
    // Đồng bộ nội dung từ editor sang textarea trước khi submit
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded');
        
        const form = document.querySelector('form.news-form');
        const editor = document.getElementById('content-editor');
        const textarea = document.getElementById('content');
        
        console.log('Form:', form);
        console.log('Editor:', editor);
        console.log('Textarea:', textarea);
        
        if (form) {
            form.addEventListener('submit', function(e) {
                console.log('=== FORM SUBMIT ===');
                
                if (!editor || !textarea) {
                    console.error('Không tìm thấy editor hoặc textarea');
                    alert('Lỗi: Không tìm thấy editor');
                    e.preventDefault();
                    return false;
                }
                
                // Lấy nội dung HTML từ editor
                const content = editor.innerHTML.trim();
                
                console.log('Content length:', content.length);
                console.log('Content preview:', content.substring(0, 200));
                
                // Kiểm tra nội dung không rỗng
                if (!content || content === '<br>' || content === '<p><br></p>') {
                    e.preventDefault();
                    alert('Vui lòng nhập nội dung đầy đủ!');
                    return false;
                }
                
                // Đồng bộ vào textarea
                textarea.value = content;
                
                console.log('Đã đồng bộ content vào textarea');
                console.log('Textarea value length:', textarea.value.length);
                console.log('Form sẽ được submit...');
                
                // Cho phép form submit
                return true;
            });
            
            console.log('Event listener đã được thêm vào form');
        } else {
            console.error('Không tìm thấy form!');
        }
    });
    
    // Import file Word với mammoth.js
    function importWordFile(event) {
        const file = event.target.files[0];
        if (!file) return;

        if (!file.name.toLowerCase().endsWith('.docx')) {
            alert('He thong chi ho tro file .docx. Hay mo file Word va luu lai dang .docx roi thu lai.');
            event.target.value = '';
            return;
        }
        
        // Hiển thị loading
        const editor = document.getElementById('content-editor');
        editor.innerHTML = '<p style="text-align: center; color: #3498db; padding: 40px;">⏳ Đang đọc file Word và trích xuất hình ảnh...</p>';
        
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const arrayBuffer = e.target.result;
            
            // Sử dụng mammoth.js để chuyển đổi Word sang HTML
            mammoth.convertToHtml(
                {arrayBuffer: arrayBuffer},
                {
                    includeEmbeddedStyleMap: true,
                    ignoreEmptyParagraphs: false,
                    styleMap: [
                        "p[style-name='Title'] => h1:fresh",
                        "p[style-name='Subtitle'] => h2:fresh",
                        "p[style-name='Heading 1'] => h2:fresh",
                        "p[style-name='Heading 2'] => h3:fresh",
                        "p[style-name='Heading 3'] => h4:fresh",
                        "table => table:fresh",
                        "tr => tr",
                        "tc => td"
                    ],
                    convertImage: mammoth.images.imgElement(function(image) {
                        return image.read("base64").then(function(imageBuffer) {
                            return {
                                src: "data:" + image.contentType + ";base64," + imageBuffer
                            };
                        });
                    })
                }
            )
            .then(function(result) {
                let htmlContent = result.value;
                
                // Xử lý bảng từ Word
                const parser = new DOMParser();
                const doc = parser.parseFromString(htmlContent, 'text/html');
                
                // Cập nhật style cho tất cả các bảng
                const tables = doc.querySelectorAll('table');
                tables.forEach(function(table) {
                    table.style.width = '100%';
                    table.style.borderCollapse = 'collapse';
                    table.style.margin = '20px 0';
                    table.style.border = '2px solid #000';
                    table.style.fontSize = '14px';
                    
                    // Cập nhật style cho tất cả các ô
                    const cells = table.querySelectorAll('th, td');
                    cells.forEach(function(cell) {
                        cell.style.border = '1px solid #000';
                        cell.style.padding = '10px 8px';
                        cell.style.color = '#000';
                        cell.style.wordWrap = 'break-word';
                        cell.style.wordBreak = 'break-word';
                        cell.style.verticalAlign = 'top';
                        
                        if (cell.tagName === 'TH') {
                            cell.style.textAlign = 'center';
                            cell.style.fontWeight = 'bold';
                            cell.style.background = '#fff';
                        } else {
                            cell.style.textAlign = 'left';
                        }
                    });
                    
                    // Cập nhật style cho các hàng
                    const rows = table.querySelectorAll('tr');
                    rows.forEach(function(row) {
                        row.style.background = '#fff';
                    });
                });
                
                htmlContent = doc.body.innerHTML;
                
                const prepared = window.WordDocumentTools
                    ? window.WordDocumentTools.prepareImportedHtml(htmlContent)
                    : { html: htmlContent, title: '', summary: '', imageCount: 0 };

                editor.innerHTML = prepared.html;

                if (window.WordDocumentTools) {
                    window.WordDocumentTools.hydrateEditor(editor);
                }

                showImportConfirmation(prepared.title, prepared.imageCount, prepared.summary);
            })
            .catch(function(error) {
                console.error('Lỗi khi đọc file Word:', error);
                editor.innerHTML = '<p style="color: red;">❌ Lỗi khi đọc file Word. Vui lòng thử lại hoặc sử dụng file .docx mới hơn.</p>';
                alert('Lỗi: ' + error.message);
            });
        };
        
        reader.onerror = function() {
            alert('✗ Lỗi khi đọc file. Vui lòng thử lại!');
            editor.innerHTML = '';
        };
        
        reader.readAsArrayBuffer(file);
        
        // Reset input
        event.target.value = '';
    }
    
    // Hiển thị popup xác nhận import
    function showImportConfirmation(title, imageCount, summary) {
        const modal = document.createElement('div');
        modal.style.cssText = 'position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 9999;';
        
        const modalContent = document.createElement('div');
        modalContent.style.cssText = 'background: white; padding: 30px; border-radius: 12px; max-width: 500px; box-shadow: 0 10px 40px rgba(0,0,0,0.3);';
        
        modalContent.innerHTML = `
            <h3 style="margin: 0 0 20px 0; color: #2c3e50; font-size: 22px;">localhost cho biết</h3>
            <div style="margin-bottom: 20px; color: #34495e; line-height: 1.8;">
                <p style="margin: 5px 0;">✓ Đã import thành công!</p>
                <p style="margin: 5px 0;">- Nội dung: OK</p>
                <p style="margin: 5px 0;">- Hình ảnh: ${imageCount} ảnh</p>
                <p style="margin: 5px 0;">- Định dạng: Đã giữ nguyên</p>
            </div>
            <p style="margin: 15px 0 25px 0; color: #7f8c8d; font-size: 14px;">Bạn có thể chỉnh sửa trước khi lưu.</p>
            <div style="text-align: right;">
                <button onclick="this.closest('div[style*=fixed]').remove()" style="background: #27ae60; color: white; border: none; padding: 12px 30px; border-radius: 6px; font-size: 16px; cursor: pointer; font-weight: 600;">OK</button>
            </div>
        `;
        
        modal.appendChild(modalContent);
        document.body.appendChild(modal);
        
        // Tự động điền tiêu đề và tóm tắt nếu trống
        const titleInput = document.getElementById('title');
        const summaryInput = document.getElementById('summary');
        
        if (titleInput && !titleInput.value && title) {
            titleInput.value = title;
        }
        
        if (summaryInput && !summaryInput.value && summary) {
            summaryInput.value = summary;
        }
    }

    // Chèn hình ảnh vào editor
    function insertImage(event) {
        const file = event.target.files[0];
        if (!file) return;
        
        if (!file.type.startsWith('image/')) {
            alert('Vui lòng chọn file hình ảnh!');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.maxWidth = '100%';
            img.style.height = 'auto';
            img.style.display = 'block';
            img.style.margin = '20px 0';
            img.style.borderRadius = '8px';
            
            const editor = document.getElementById('content-editor');
            editor.appendChild(img);
            
            // Thêm dòng trống sau ảnh
            const br = document.createElement('br');
            editor.appendChild(br);
        };
        reader.readAsDataURL(file);
        
        // Reset input để có thể chọn lại cùng file
        event.target.value = '';
    }

    // Xử lý paste từ Word
    document.getElementById('content-editor').addEventListener('paste', function(e) {
        // Cho phép paste HTML từ Word
        const items = e.clipboardData.items;
        
        for (let i = 0; i < items.length; i++) {
            // Nếu paste hình ảnh
            if (items[i].type.indexOf('image') !== -1) {
                e.preventDefault();
                const blob = items[i].getAsFile();
                const reader = new FileReader();
                
                reader.onload = function(event) {
                    const img = document.createElement('img');
                    img.src = event.target.result;
                    img.style.maxWidth = '100%';
                    img.style.height = 'auto';
                    img.style.display = 'block';
                    img.style.margin = '20px 0';
                    img.style.borderRadius = '8px';
                    
                    document.getElementById('content-editor').appendChild(img);
                };
                
                reader.readAsDataURL(blob);
            }
        }
    });
    </script>
    <script src="word-document.js?v=1.0"></script>
</body>
</html>
