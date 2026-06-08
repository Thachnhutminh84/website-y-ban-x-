<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';

authRequireCanBo('index.php');

$currentRole = authCurrentRole();
$displayName = authDisplayName();

// Lấy ID tin tức cần sửa
$news_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($news_id <= 0) {
    header("Location: tin-tuc.php");
    exit();
}

// Đọc dữ liệu tin tức từ database
$current_news = null;

try {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("SELECT n.*, c.slug as category_slug FROM news n 
                            LEFT JOIN categories c ON n.category_id = c.id 
                            WHERE n.id = ?");
    $stmt->bind_param("i", $news_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $current_news = [
            'id' => $row['id'],
            'category' => $row['category_slug'],
            'title' => $row['title'],
            'date' => date('Y-m-d', strtotime($row['published_at'])),
            'image' => $row['image'],
            'file_path' => $row['file_path'],
            'summary' => $row['summary'],
            'content' => $row['content']
        ];
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    $_SESSION['error'] = "Lỗi database: " . $e->getMessage();
    header("Location: tin-tuc.php");
    exit();
}

// Nếu không tìm thấy tin tức
if (!$current_news) {
    $_SESSION['error'] = "Không tìm thấy tin tức!";
    header("Location: tin-tuc.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa tin tức - UBND Xã Long Hiệp</title>
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
        outline: 2px solid var(--primary);
    }
    .editor-toolbar {
        background: #f8f9fa;
        padding: 8px;
        border: 1px solid #ddd;
        border-bottom: none;
        border-radius: 8px 8px 0 0;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .toolbar-row {
        display: flex;
        gap: 4px;
        flex-wrap: wrap;
        align-items: center;
    }
    .toolbar-separator {
        color: #ccc;
        margin: 0 4px;
        font-size: 18px;
    }
    .editor-btn {
        padding: 6px 10px;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        min-width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    .editor-btn:hover {
        background: #e9ecef;
        border-color: #adb5bd;
    }
    .editor-btn:active {
        background: #dee2e6;
    }
    .editor-select {
        padding: 6px 8px;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        cursor: pointer;
        font-size: 13px;
        height: 32px;
    }
    .editor-select:hover {
        border-color: #adb5bd;
    }
    .editor-color {
        width: 32px;
        height: 32px;
        border: 1px solid #ddd;
        border-radius: 4px;
        cursor: pointer;
        padding: 2px;
    }
    .editor-color:hover {
        border-color: #adb5bd;
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
    </style>
    <link rel="stylesheet" href="word-document.css?v=1.0">
    <?php include 'header-menu.php'; ?>
<body>

    <main>
        <section class="page-header">
            <div class="container">
                <h2>Sửa tin tức</h2>
                <p>Cập nhật thông tin tin tức</p>
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
                <form action="xu-ly-sua-tin.php" method="POST" enctype="multipart/form-data" class="news-form" onsubmit="var editor = document.getElementById('content-editor'); var textarea = document.getElementById('content'); var content = editor.innerHTML.trim(); if (!content || content === '<br>') { alert('Vui lòng nhập nội dung!'); return false; } textarea.value = content; return true;">
                    <?php echo SecurityHelper::csrfField(); ?>
                    <input type="hidden" name="news_id" value="<?php echo htmlspecialchars($current_news['id']); ?>">
                    
                    <div class="form-group">
                        <label for="category">Danh mục <span class="required">*</span></label>
                        <select id="category" name="category" required>
                            <option value="">-- Chọn danh mục --</option>
                            <option value="xay-dung-dang" <?php echo $current_news['category'] == 'xay-dung-dang' ? 'selected' : ''; ?>>Công tác xây dựng Đảng</option>
                            <option value="mat-tran" <?php echo $current_news['category'] == 'mat-tran' ? 'selected' : ''; ?>>Mặt trận đoàn thể</option>
                            <option value="an-ninh" <?php echo $current_news['category'] == 'an-ninh' ? 'selected' : ''; ?>>An ninh trật tự</option>
                            <option value="su-kien" <?php echo $current_news['category'] == 'su-kien' ? 'selected' : ''; ?>>Tin tức sự kiện</option>
                            <option value="tuyen-truyen" <?php echo $current_news['category'] == 'tuyen-truyen' ? 'selected' : ''; ?>>Thông tin tuyên truyền</option>
                            <option value="giao-duc" <?php echo $current_news['category'] == 'giao-duc' ? 'selected' : ''; ?>>Giáo dục và đào tạo</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="title">Tiêu đề <span class="required">*</span></label>
                        <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($current_news['title']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="date">Ngày đăng <span class="required">*</span></label>
                        <input type="date" id="date" name="date" required value="<?php echo $current_news['date']; ?>">
                    </div>

                    <div class="form-group">
                        <label>Ảnh hiện tại:</label>
                        <?php if (!empty($current_news['image'])): ?>
                            <img src="<?php echo $current_news['image']; ?>" alt="Ảnh hiện tại" style="max-width: 300px; border-radius: 8px; margin: 10px 0;">
                        <?php endif; ?>
                        <label for="image">Thay đổi ảnh (để trống nếu không đổi)</label>
                        <input type="file" id="image" name="image">
                        <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($current_news['image']); ?>">
                        <small>Định dạng: JPG, PNG, GIF. Kích thước tối đa: 2MB</small>
                    </div>
                    
                    <input type="hidden" name="news_file_path" id="news_file_path" value="<?php echo htmlspecialchars($current_news['file_path'] ?? ''); ?>">
                    
                    <?php if (!empty($current_news['file_path'])): ?>
                    <div class="form-group">
                        <label>Tài liệu đính kèm hiện tại:</label>
                        <div style="padding: 10px 12px; background: #f8f9fa; border-radius: 6px; margin: 8px 0; display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 20px;">📎</span>
                            <span style="flex: 1;"><?php echo basename($current_news['file_path']); ?></span>
                            <label style="display: flex; align-items: center; gap: 4px; cursor: pointer; color: #e74c3c; font-size: 13px;">
                                <input type="checkbox" name="delete_news_file" value="1" onchange="if(this.checked) document.getElementById('news_file_path').value=''"> Xóa
                            </label>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="summary">Tóm tắt <span class="required">*</span></label>
                        <textarea id="summary" name="summary" rows="3" required><?php echo htmlspecialchars($current_news['summary']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="content">Nội dung đầy đủ <span class="required">*</span></label>
                        <div style="margin-bottom: 10px;">
                            <input type="file" id="import-word-file" accept=".doc,.docx" style="display: none;" onchange="importWordFile(event)">
                            <button type="button" onclick="document.getElementById('import-word-file').click()" class="editor-btn btn-import-word">
                                📄 Import từ Word (tự động lấy hình ảnh)
                            </button>
                            <small style="display: block; margin-top: 5px; color: #7f8c8d;">
                                Chọn file Word (.doc hoặc .docx) - Hệ thống sẽ tự động đọc nội dung, định dạng và hình ảnh
                            </small>
                        </div>
                        <div class="editor-toolbar">
                            <!-- Hàng 1: Định dạng văn bản cơ bản -->
                            <div class="toolbar-row">
                                <button type="button" class="editor-btn" onclick="document.execCommand('undo')" title="Hoàn tác">↶</button>
                                <button type="button" class="editor-btn" onclick="document.execCommand('redo')" title="Làm lại">↷</button>
                                <span class="toolbar-separator">|</span>
                                
                                <select class="editor-select" onchange="document.execCommand('formatBlock', false, this.value); this.value='';">
                                    <option value="">Định dạng</option>
                                    <option value="p">Đoạn văn</option>
                                    <option value="h1">Tiêu đề 1</option>
                                    <option value="h2">Tiêu đề 2</option>
                                    <option value="h3">Tiêu đề 3</option>
                                    <option value="h4">Tiêu đề 4</option>
                                </select>
                                
                                <select class="editor-select" onchange="document.execCommand('fontName', false, this.value);">
                                    <option value="">Font chữ</option>
                                    <option value="Arial">Arial</option>
                                    <option value="Times New Roman">Times New Roman</option>
                                    <option value="Courier New">Courier New</option>
                                    <option value="Georgia">Georgia</option>
                                    <option value="Verdana">Verdana</option>
                                </select>
                                
                                <select class="editor-select" onchange="document.execCommand('fontSize', false, this.value);">
                                    <option value="">Cỡ chữ</option>
                                    <option value="1">Rất nhỏ</option>
                                    <option value="2">Nhỏ</option>
                                    <option value="3">Trung bình</option>
                                    <option value="4">Lớn</option>
                                    <option value="5">Rất lớn</option>
                                    <option value="6">Cực lớn</option>
                                </select>
                            </div>
                            
                            <!-- Hàng 2: Định dạng text -->
                            <div class="toolbar-row">
                                <button type="button" class="editor-btn" onclick="document.execCommand('bold')" title="Đậm (Ctrl+B)"><b>B</b></button>
                                <button type="button" class="editor-btn" onclick="document.execCommand('italic')" title="Nghiêng (Ctrl+I)"><i>I</i></button>
                                <button type="button" class="editor-btn" onclick="document.execCommand('underline')" title="Gạch chân (Ctrl+U)"><u>U</u></button>
                                <button type="button" class="editor-btn" onclick="document.execCommand('strikeThrough')" title="Gạch ngang"><s>S</s></button>
                                <span class="toolbar-separator">|</span>
                                
                                <input type="color" id="textColor" class="editor-color" onchange="document.execCommand('foreColor', false, this.value)" title="Màu chữ">
                                <input type="color" id="bgColor" class="editor-color" onchange="document.execCommand('backColor', false, this.value)" title="Màu nền">
                                <span class="toolbar-separator">|</span>
                                
                                <button type="button" class="editor-btn" onclick="document.execCommand('justifyLeft')" title="Căn trái">≡</button>
                                <button type="button" class="editor-btn" onclick="document.execCommand('justifyCenter')" title="Căn giữa">≣</button>
                                <button type="button" class="editor-btn" onclick="document.execCommand('justifyRight')" title="Căn phải">≡</button>
                                <button type="button" class="editor-btn" onclick="document.execCommand('justifyFull')" title="Căn đều">≣</button>
                                <span class="toolbar-separator">|</span>
                                
                                <button type="button" class="editor-btn" onclick="document.execCommand('insertUnorderedList')" title="Danh sách">•</button>
                                <button type="button" class="editor-btn" onclick="document.execCommand('insertOrderedList')" title="Danh sách số">1.</button>
                                <button type="button" class="editor-btn" onclick="document.execCommand('indent')" title="Thụt vào">→</button>
                                <button type="button" class="editor-btn" onclick="document.execCommand('outdent')" title="Thụt ra">←</button>
                                <span class="toolbar-separator">|</span>
                                
                                <button type="button" class="editor-btn" onclick="insertLinkEdit()" title="Chèn link">🔗</button>
                                <button type="button" class="editor-btn" onclick="document.execCommand('unlink')" title="Xóa link">⛓️‍💥</button>
                                <input type="file" id="insert-image" accept="image/*" style="display: none;" onchange="insertImage(event)">
                                <button type="button" class="editor-btn" onclick="document.getElementById('insert-image').click()" title="Chèn ảnh">🖼️</button>
                                <button type="button" class="editor-btn" onclick="insertTableEdit()" title="Chèn bảng">📊</button>
                                <span class="toolbar-separator">|</span>
                                
                                <button type="button" class="editor-btn" onclick="document.execCommand('removeFormat')" title="Xóa định dạng">🧹</button>
                                <button type="button" class="editor-btn" onclick="toggleFullscreenEdit()" title="Toàn màn hình" style="background: #9b59b6; color: white;">⛶</button>
                            </div>
                        </div>
                        <div id="content-editor" class="content-editor" contenteditable="true"><?php echo $current_news['content']; ?></div>
                        <textarea id="content" name="content" style="display: none;"></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit" onclick="console.log('Button clicked!')">✓ Cập nhật tin tức</button>
                        <a href="tin-tuc.php?cat=<?php echo $current_news['category']; ?>" class="btn-cancel">✗ Hủy bỏ</a>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>
    <?php include 'quick-actions-toolbar.php'; ?>

    <script>
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

        const isDoc = file.name.toLowerCase().endsWith('.doc');
        const isDocx = file.name.toLowerCase().endsWith('.docx');
        
        if (!isDoc && !isDocx) {
            alert('Hệ thống chỉ hỗ trợ file Word (.doc hoặc .docx).');
            event.target.value = '';
            return;
        }
        
        const editor = document.getElementById('content-editor');
        editor.innerHTML = '<p style="text-align: center; color: #3498db; padding: 40px;">⏳ Đang đọc file Word...<br>Vui lòng đợi...</p>';
        
        if (isDocx) {
            importDocxFile(file, editor, event);
        } else {
            importDocFile(file, editor, event);
        }
    }
    
    function importDocFile(file, editor, event) {
        editor.innerHTML = '<p style="text-align: center; color: #e67e22; padding: 40px;">⏳ Đang thử convert file .doc...<br>Vui lòng đợi</p>';
        
        var formData = new FormData();
        formData.append('word_file', file);
        
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'convert-word.php', true);
        xhr.timeout = 120000;
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    
                    if (data.success && data.html) {
                        editor.innerHTML = data.html;
                        if (window.WordDocumentTools) {
                            window.WordDocumentTools.hydrateEditor(editor);
                        }
                        showImportConfirmation(data.title || '', 0, data.summary || '');
                        event.target.value = '';
                        return;
                    }
                    
                    if (data.use_server === false) {
                        showDocConvertHelp(editor, file, event);
                        return;
                    }
                    
                    if (data.use_client) {
                        importDocxFile(file, editor, event);
                        return;
                    }
                    
                    showDocConvertHelp(editor, file, event);
                } catch(e) {
                    console.error('Parse error:', e);
                    showDocConvertHelp(editor, file, event);
                }
            } else {
                showDocConvertHelp(editor, file, event);
            }
        };
        
        xhr.onerror = function() {
            showDocConvertHelp(editor, file, event);
        };
        
        xhr.ontimeout = function() {
            showDocConvertHelp(editor, file, event);
        };
        
        xhr.send(formData);
    }
    
    function showDocConvertHelp(editor, file, event) {
        event.target.value = '';
        editor.innerHTML = '<div style="padding: 20px; background: #fff3cd; border: 2px solid #ffc107; border-radius: 8px; margin: 10px;">' +
            '<h3 style="color: #856404; margin-top: 0;">⚠️ File .doc chưa thể import trực tiếp</h3>' +
            '<p style="color: #856404;"><strong>Bước 1:</strong> Mở file <strong>"' + file.name + '"</strong> bằng Microsoft Word hoặc WPS Office</p>' +
            '<p style="color: #856404;"><strong>Bước 2:</strong> Chọn <strong>File → Save As (Lưu dưới dạng)</strong></p>' +
            '<p style="color: #856404;"><strong>Bước 3:</strong> Chọn định dạng <strong>Word Document (.docx)</strong></p>' +
            '<p style="color: #856404;"><strong>Bước 4:</strong> Lưu file mới, rồi quay lại bấm nút Import và chọn file .docx vừa lưu</p>' +
            '<hr style="border-color: #ffc107;">' +
            '<p style="color: #856404;"><strong>Hoặc:</strong> Bạn có thể copy nội dung từ Word rồi paste trực tiếp vào ô soạn thảo bên dưới.</p>' +
            '</div>';
    }
                    
                    if (data.use_client) {
                        importDocxFile(file, editor, event);
                        return;
                    }
                    
                    if (data.success && data.html) {
                        editor.innerHTML = data.html;
                        
                        if (window.WordDocumentTools) {
                            window.WordDocumentTools.hydrateEditor(editor);
                        }
                        
                        showImportConfirmation(data.title || '', 0, data.summary || '');
                        event.target.value = '';
                    } else {
                        editor.innerHTML = '<p style="color: #e74c3c;">❌ Phản hồi không hợp lệ</p>';
                        event.target.value = '';
                    }
                } catch(e) {
                    editor.innerHTML = '<p style="color: #e74c3c;">❌ Lỗi xử lý: ' + e.message + '</p>';
                    console.error('Parse error:', e, xhr.responseText.substring(0, 500));
                    event.target.value = '';
                }
            } else {
                editor.innerHTML = '<p style="color: #e74c3c;">❌ Lỗi server (HTTP ' + xhr.status + ')</p>';
                event.target.value = '';
            }
        };
        
        xhr.onerror = function() {
            editor.innerHTML = '<p style="color: #e74c3c;">❌ Không thể kết nối server.</p>';
            event.target.value = '';
        };
        
        xhr.ontimeout = function() {
            editor.innerHTML = '<p style="color: #e74c3c;">❌ Quá thời gian xử lý.</p>';
            event.target.value = '';
        };
        
        xhr.send(formData);
    }
    }
    
    function importDocxFile(file, editor, event) {
        if (typeof mammoth === 'undefined') {
            alert('Lỗi: Thư viện đọc Word chưa được tải. Vui lòng refresh lại trang.');
            event.target.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            mammoth.convertToHtml(
                {arrayBuffer: e.target.result},
                {
                    includeEmbeddedStyleMap: true,
                    ignoreEmptyParagraphs: false,
                    styleMap: [
                        "p[style-name='Title'] => h1:fresh",
                        "p[style-name='Subtitle'] => h2:fresh",
                        "p[style-name='Heading 1'] => h2:fresh",
                        "p[style-name='Heading 2'] => h3:fresh",
                        "p[style-name='Heading 3'] => h4:fresh"
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
                const prepared = window.WordDocumentTools
                    ? window.WordDocumentTools.prepareImportedHtml(result.value)
                    : { html: result.value, title: '', summary: '', imageCount: 0 };

                editor.innerHTML = prepared.html;

                if (window.WordDocumentTools) {
                    window.WordDocumentTools.hydrateEditor(editor);
                }

                showImportConfirmation(prepared.title, prepared.imageCount, prepared.summary);

                // Upload file gốc để lưu làm tài liệu tải về
                const formData = new FormData();
                formData.append('file', file);
                fetch('save-uploaded-file.php', {
                    method: 'POST',
                    body: formData
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data.success) {
                        document.getElementById('news_file_path').value = data.path;
                        console.log('File saved:', data.path);
                    }
                })
                .catch(function(err) {
                    console.warn('Không thể lưu file gốc:', err);
                });
            })
            .catch(function(error) {
                console.error('Lỗi khi đọc file Word:', error);
                editor.innerHTML = '<p style="color: red;">❌ Lỗi khi đọc file Word. Vui lòng thử lại.</p>';
                alert('Lỗi: ' + error.message);
            });
        };
        reader.onerror = function() {
            alert('Lỗi khi đọc file. Vui lòng thử lại!');
            editor.innerHTML = '';
        };
        reader.readAsArrayBuffer(file);
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

    // Đồng bộ nội dung từ editor sang textarea trước khi submit
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const editor = document.getElementById('content-editor');
                const textarea = document.getElementById('content');
                
                if (!editor || !textarea) {
                    console.error('Không tìm thấy editor hoặc textarea');
                    return true;
                }
                
                // Lấy nội dung HTML từ editor
                const content = editor.innerHTML.trim();
                
                console.log('=== SUBMIT DEBUG ===');
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
                
                return true;
            });
        }
    });

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


    <script>
    // Hàm chèn link cho trang sửa tin
    function insertLinkEdit() {
        const url = prompt('Nhập URL:', 'https://');
        if (url === null || url === '' || url === 'https://') return;
        
        const text = prompt('Nhập text hiển thị:', 'Click vào đây');
        if (text === null || text === '') return;
        
        document.execCommand('insertHTML', false, `<a href="${url}" target="_blank">${text}</a>`);
    }
    
    // Hàm chèn bảng cho trang sửa tin
    function insertTableEdit() {
        const rows = prompt('Nhập số hàng (mặc định 3):', '3');
        if (rows === null) return;
        
        const cols = prompt('Nhập số cột (mặc định 3):', '3');
        if (cols === null) return;
        
        const numRows = parseInt(rows) || 3;
        const numCols = parseInt(cols) || 3;
        
        let tableHtml = '<table style="width: 100%; border-collapse: collapse; margin: 20px 0; border: 1px solid #000;">';
        
        tableHtml += '<tr style="background: #fff;">';
        for (let i = 0; i < numCols; i++) {
            tableHtml += '<th style="border: 1px solid #000; padding: 10px 8px; text-align: center; font-weight: bold; color: #000;">Cột ' + (i + 1) + '</th>';
        }
        tableHtml += '</tr>';
        
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
    
    // Hàm bật/tắt chế độ toàn màn hình
    function toggleFullscreenEdit() {
        const editor = document.getElementById('content-editor');
        
        if (!document.querySelector('.editor-fullscreen-overlay')) {
            // Bật fullscreen
            const overlay = document.createElement('div');
            overlay.className = 'editor-fullscreen-overlay';
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: white;
                z-index: 9999;
                overflow-y: auto;
                padding: 80px 40px 40px;
            `;
            
            const editorClone = editor.cloneNode(true);
            editorClone.style.cssText = `
                min-height: calc(100vh - 120px);
                max-width: 1200px;
                margin: 0 auto;
                padding: 40px;
                border: 1px solid #ddd;
                border-radius: 8px;
                font-size: 18px;
            `;
            
            overlay.appendChild(editorClone);
            document.body.appendChild(overlay);
            
            // Tạo toolbar
            const controls = document.createElement('div');
            controls.className = 'fullscreen-controls';
            controls.style.cssText = `
                position: fixed;
                top: 10px;
                left: 50%;
                transform: translateX(-50%);
                z-index: 10000;
                display: flex;
                gap: 8px;
                background: rgba(255, 255, 255, 0.95);
                padding: 10px 15px;
                border-radius: 12px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                flex-wrap: wrap;
                max-width: 95%;
            `;
            controls.innerHTML = `
                <button class="fullscreen-btn" onclick="toggleFullscreenEdit()" style="background: #dc3545;">❌ Thoát toàn màn hình</button>
                <button class="fullscreen-btn" onclick="document.execCommand('bold')"><b>B</b> Đậm</button>
                <button class="fullscreen-btn" onclick="document.execCommand('italic')"><i>I</i> Nghiêng</button>
                <button class="fullscreen-btn" onclick="document.execCommand('underline')"><u>U</u> Gạch chân</button>
                <button class="fullscreen-btn" onclick="document.execCommand('formatBlock', false, 'h2')">H2 Tiêu đề</button>
                <button class="fullscreen-btn" onclick="document.execCommand('formatBlock', false, 'h3')">H3 Tiêu đề nhỏ</button>
                <button class="fullscreen-btn" onclick="document.execCommand('insertUnorderedList')">• Danh sách</button>
                <button class="fullscreen-btn" onclick="insertTableEdit()" style="background: #e74c3c;">📊 Bảng</button>
            `;
            
            // Style cho các nút
            const style = document.createElement('style');
            style.textContent = `
                .fullscreen-btn {
                    padding: 8px 16px;
                    background: var(--primary);
                    color: white;
                    border: none;
                    border-radius: 6px;
                    cursor: pointer;
                    font-size: 14px;
                    font-weight: 600;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
                    transition: all 0.3s;
                    white-space: nowrap;
                }
                .fullscreen-btn:hover {
                    background: #5568d3;
                    transform: translateY(-2px);
                }
                .fullscreen-btn b, .fullscreen-btn i, .fullscreen-btn u {
                    margin-right: 4px;
                }
            `;
            document.head.appendChild(style);
            
            document.body.appendChild(controls);
            document.body.style.overflow = 'hidden';
            
            // Đồng bộ nội dung khi thoát
            editorClone.addEventListener('input', function() {
                editor.innerHTML = editorClone.innerHTML;
            });
            
            editorClone.focus();
        } else {
            // Tắt fullscreen
            const overlay = document.querySelector('.editor-fullscreen-overlay');
            const controls = document.querySelector('.fullscreen-controls');
            
            if (overlay) overlay.remove();
            if (controls) controls.remove();
            
            document.body.style.overflow = '';
        }
    }
    </script>
