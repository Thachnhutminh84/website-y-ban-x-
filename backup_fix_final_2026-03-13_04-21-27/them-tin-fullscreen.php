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
    <title>Thêm tin tức - Toàn màn hình - UBND Xã Long Hiệp</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.6.0/mammoth.browser.min.js"></script>
    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f5f5f5;
        height: 100vh;
        overflow: hidden;
    }
    
    .fullscreen-container {
        display: flex;
        height: 100vh;
    }
    
    .sidebar {
        width: 350px;
        background: white;
        border-right: 2px solid #ddd;
        padding: 20px;
        overflow-y: auto;
        box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    }
    
    .editor-area {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: white;
    }
    
    .toolbar {
        background: #f8f9fa;
        padding: 15px 20px;
        border-bottom: 2px solid #ddd;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }
    
    .toolbar-btn {
        padding: 10px 15px;
        background: white;
        border: 1px solid #ddd;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s;
    }
    
    .toolbar-btn:hover {
        background: #e9ecef;
        border-color: #adb5bd;
    }
    
    .toolbar-btn.active {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }
    
    .content-editor {
        flex: 1;
        padding: 30px;
        font-size: 18px;
        line-height: 1.8;
        border: none;
        outline: none;
        overflow-y: auto;
        background: white;
    }
    
    .content-editor:focus {
        outline: none;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 2px solid #e9ecef;
        border-radius: 6px;
        font-size: 14px;
        transition: border-color 0.2s;
    }
    
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #007bff;
    }
    
    .btn-submit {
        width: 100%;
        padding: 15px;
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 20px;
        transition: all 0.3s;
    }
    
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
    }
    
    .btn-back {
        width: 100%;
        padding: 12px;
        background: #6c757d;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        cursor: pointer;
        margin-bottom: 20px;
        text-decoration: none;
        display: block;
        text-align: center;
    }
    
    .btn-back:hover {
        background: #5a6268;
    }
    
    .import-section {
        background: #e7f3ff;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 2px dashed #007bff;
    }
    
    .import-btn {
        width: 100%;
        padding: 12px;
        background: #007bff;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
    }
    
    .import-btn:hover {
        background: #0056b3;
    }
    
    /* Table styles */
    .content-editor table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
        border: 2px solid #000;
        background: white;
        font-size: 16px;
    }
    
    .content-editor table th {
        background: #fff;
        border: 1px solid #000;
        padding: 12px 10px;
        text-align: center;
        font-weight: bold;
        color: #000;
    }
    
    .content-editor table td {
        border: 1px solid #000;
        padding: 12px 10px;
        color: #000;
        text-align: left;
        vertical-align: top;
    }
    
    .content-editor img {
        max-width: 100%;
        height: auto;
        display: block;
        margin: 20px 0;
        border-radius: 8px;
    }
    
    .content-editor h2 {
        color: #c41e3a;
        margin: 25px 0 15px 0;
        font-size: 24px;
    }
    
    .content-editor h3 {
        color: #333;
        margin: 20px 0 10px 0;
        font-size: 20px;
    }
    
    .content-editor p {
        margin-bottom: 15px;
    }
    
    .status-bar {
        background: #f8f9fa;
        padding: 10px 20px;
        border-top: 1px solid #ddd;
        font-size: 14px;
        color: #6c757d;
    }
    </style>
</head>
<body>
    <?php include 'menu-don-gian.php'; ?>
    <div class="fullscreen-container">
        <!-- Sidebar với form -->
        <div class="sidebar">
            <a href="them-tin.php" class="btn-back">← Quay lại trang thường</a>
            
            <form action="xu-ly-them-tin.php" method="POST" enctype="multipart/form-data" id="news-form">
                <div class="form-group">
                    <label for="category">Danh mục *</label>
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
                    <label for="title">Tiêu đề *</label>
                    <input type="text" id="title" name="title" required placeholder="Nhập tiêu đề tin tức">
                </div>

                <div class="form-group">
                    <label for="date">Ngày đăng *</label>
                    <input type="date" id="date" name="date" required value="<?php echo date('Y-m-d'); ?>">
                </div>

                <div class="form-group">
                    <label for="image">Ảnh đại diện</label>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>

                <div class="form-group">
                    <label for="summary">Tóm tắt *</label>
                    <textarea id="summary" name="summary" rows="3" required placeholder="Nhập tóm tắt ngắn gọn"></textarea>
                </div>
                
                <div class="import-section">
                    <h4 style="margin-bottom: 10px; color: #007bff;">📄 Import từ Word</h4>
                    <input type="file" id="import-word-file" accept=".docx" style="display: none;" onchange="importWordFile(event)">
                    <button type="button" onclick="document.getElementById('import-word-file').click()" class="import-btn">
                        Chọn file Word (.docx)
                    </button>
                    <small style="display: block; margin-top: 8px; color: #6c757d;">
                        Tự động đọc nội dung, bảng và hình ảnh
                    </small>
                </div>

                <textarea id="content" name="content" style="display: none;"></textarea>
                
                <button type="submit" class="btn-submit">✓ Xuất bản tin tức</button>
            </form>
        </div>

        <!-- Editor area -->
        <div class="editor-area">
            <div class="toolbar">
                <button type="button" class="toolbar-btn" onclick="document.execCommand('bold')"><b>B</b> Đậm</button>
                <button type="button" class="toolbar-btn" onclick="document.execCommand('italic')"><i>I</i> Nghiêng</button>
                <button type="button" class="toolbar-btn" onclick="document.execCommand('underline')"><u>U</u> Gạch chân</button>
                <button type="button" class="toolbar-btn" onclick="document.execCommand('formatBlock', false, 'h2')">H2 Tiêu đề</button>
                <button type="button" class="toolbar-btn" onclick="document.execCommand('formatBlock', false, 'h3')">H3 Tiêu đề nhỏ</button>
                <button type="button" class="toolbar-btn" onclick="document.execCommand('insertUnorderedList')">• Danh sách</button>
                <button type="button" class="toolbar-btn" onclick="insertTable()" style="background: #dc3545; color: white;">📊 Chèn bảng</button>
                <input type="file" id="insert-image" accept="image/*" style="display: none;" onchange="insertImage(event)">
                <button type="button" class="toolbar-btn" onclick="document.getElementById('insert-image').click()" style="background: #17a2b8; color: white;">📷 Chèn ảnh</button>
            </div>
            
            <div id="content-editor" class="content-editor" contenteditable="true" placeholder="Nhập nội dung tin tức tại đây...">
                <p>Bắt đầu viết nội dung tin tức của bạn...</p>
            </div>
            
            <div class="status-bar">
                <span id="word-count">Số từ: 0</span> | 
                <span>Chế độ: Toàn màn hình</span> | 
                <span>Người dùng: <?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        </div>
    </div>

    <script>
    // Đồng bộ nội dung
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('news-form');
        const editor = document.getElementById('content-editor');
        const textarea = document.getElementById('content');
        
        form.addEventListener('submit', function(e) {
            const content = editor.innerHTML.trim();
            
            if (!content || content === '<p>Bắt đầu viết nội dung tin tức của bạn...</p>') {
                e.preventDefault();
                alert('Vui lòng nhập nội dung đầy đủ!');
                return false;
            }
            
            textarea.value = content;
            return true;
        });
        
        // Đếm từ
        editor.addEventListener('input', function() {
            const text = editor.innerText || editor.textContent || '';
            const wordCount = text.trim().split(/\s+/).length;
            document.getElementById('word-count').textContent = 'Số từ: ' + wordCount;
        });
    });
    
    // Chèn bảng
    function insertTable() {
        const rows = prompt('Nhập số hàng (mặc định 3):', '3');
        if (rows === null) return;
        
        const cols = prompt('Nhập số cột (mặc định 3):', '3');
        if (cols === null) return;
        
        const numRows = parseInt(rows) || 3;
        const numCols = parseInt(cols) || 3;
        
        let tableHtml = '<table style="width: 100%; border-collapse: collapse; margin: 20px 0; border: 2px solid #000;">';
        
        // Header row
        tableHtml += '<tr>';
        for (let i = 0; i < numCols; i++) {
            tableHtml += '<th style="border: 1px solid #000; padding: 12px 10px; text-align: center; font-weight: bold; background: #fff;">Cột ' + (i + 1) + '</th>';
        }
        tableHtml += '</tr>';
        
        // Data rows
        for (let i = 0; i < numRows; i++) {
            tableHtml += '<tr>';
            for (let j = 0; j < numCols; j++) {
                tableHtml += '<td style="border: 1px solid #000; padding: 12px 10px;"></td>';
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
    
    // Chèn ảnh
    function insertImage(event) {
        const file = event.target.files[0];
        if (!file) return;
        
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
            editor.appendChild(document.createElement('br'));
        };
        reader.readAsDataURL(file);
        event.target.value = '';
    }
    
    // Import Word
    function importWordFile(event) {
        const file = event.target.files[0];
        if (!file) return;

        if (!file.name.toLowerCase().endsWith('.docx')) {
            alert('Chỉ hỗ trợ file .docx');
            event.target.value = '';
            return;
        }
        
        const editor = document.getElementById('content-editor');
        editor.innerHTML = '<p style="text-align: center; color: #007bff; padding: 40px;">⏳ Đang đọc file Word...</p>';
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const arrayBuffer = e.target.result;
            
            mammoth.convertToHtml(
                {arrayBuffer: arrayBuffer},
                {
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
                
                // Xử lý bảng
                const parser = new DOMParser();
                const doc = parser.parseFromString(htmlContent, 'text/html');
                
                const tables = doc.querySelectorAll('table');
                tables.forEach(function(table) {
                    table.style.width = '100%';
                    table.style.borderCollapse = 'collapse';
                    table.style.margin = '20px 0';
                    table.style.border = '2px solid #000';
                    
                    const cells = table.querySelectorAll('th, td');
                    cells.forEach(function(cell) {
                        cell.style.border = '1px solid #000';
                        cell.style.padding = '12px 10px';
                        cell.style.color = '#000';
                        
                        if (cell.tagName === 'TH') {
                            cell.style.textAlign = 'center';
                            cell.style.fontWeight = 'bold';
                            cell.style.background = '#fff';
                        }
                    });
                });
                
                editor.innerHTML = doc.body.innerHTML;
                
                alert('✓ Import thành công! Đã đọc nội dung và bảng từ Word.');
            })
            .catch(function(error) {
                console.error('Lỗi:', error);
                editor.innerHTML = '<p style="color: red;">❌ Lỗi khi đọc file Word.</p>';
            });
        };
        
        reader.readAsArrayBuffer(file);
        event.target.value = '';
    }
    </script>
</body>
</html>