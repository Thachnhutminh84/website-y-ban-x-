<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'auth.php';

// Kiểm tra phải là cán bộ hoặc admin
authRequireCanBo('index.php');

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
    <script src="mammoth.browser.min.js"></script>
    <script>
    // Kiểm tra mammoth.js có load không
    window.addEventListener('DOMContentLoaded', function() {
        if (typeof mammoth === 'undefined') {
            console.error('mammoth.js chưa được tải!');
            alert('Lỗi: Không thể tải thư viện đọc Word. Vui lòng refresh lại trang.');
        } else {
            console.log('mammoth.js đã load thành công!');
        }
    });
    </script>
    <style>
    .content-editor {
        border: 1px solid #c0c0c0;
        border-top: none;
        min-height: 400px;
        padding: 20px 30px;
        background: white;
        font-family: 'Times New Roman', Times, serif;
        font-size: 14pt;
        line-height: 1.5;
        outline: none;
    }
    .content-editor:focus {
        outline: none;
    }
    .word-toolbar {
        border: 1px solid #c0c0c0;
        border-radius: 4px 4px 0 0;
        background: #f3f3f3;
        user-select: none;
    }
    .word-toolbar-tabs {
        display: flex;
        background: #f3f3f3;
        border-bottom: 1px solid #c0c0c0;
        padding: 0 4px;
    }
    .word-toolbar-tab {
        padding: 6px 14px;
        font-size: 12px;
        cursor: pointer;
        border: none;
        background: none;
        color: #444;
        font-family: 'Segoe UI', Tahoma, sans-serif;
    }
    .word-toolbar-tab.active {
        background: #fff;
        border: 1px solid #c0c0c0;
        border-bottom: 1px solid #fff;
        margin-bottom: -1px;
        font-weight: 600;
        color: #1a73e8;
    }
    .word-ribbon {
        display: flex;
        align-items: stretch;
        padding: 4px 6px;
        gap: 0;
        background: #fff;
        flex-wrap: wrap;
        min-height: 64px;
    }
    .ribbon-group {
        display: flex;
        flex-direction: column;
        align-items: center;
        border-right: 1px solid #e0e0e0;
        padding: 2px 6px;
        position: relative;
    }
    .ribbon-group:last-child {
        border-right: none;
    }
    .ribbon-group-btns {
        display: flex;
        gap: 1px;
        align-items: center;
        flex-wrap: wrap;
        flex: 1;
        padding: 2px 0;
    }
    .ribbon-group-label {
        font-size: 10px;
        color: #666;
        text-align: center;
        padding-top: 2px;
        border-top: 1px solid #e8e8e8;
        margin-top: auto;
        white-space: nowrap;
        font-family: 'Segoe UI', Tahoma, sans-serif;
    }
    .rbtn {
        padding: 4px 6px;
        background: transparent;
        border: 1px solid transparent;
        border-radius: 3px;
        cursor: pointer;
        font-size: 13px;
        min-width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background 0.15s;
        font-family: 'Segoe UI', Tahoma, sans-serif;
        color: #333;
    }
    .rbtn:hover {
        background: #e0e8f6;
        border-color: #b0c4de;
    }
    .rbtn.active {
        background: #cce4f7;
        border-color: #98c8ea;
    }
    .rbtn-bold { font-weight: 700; font-size: 14px; font-family: 'Times New Roman', serif; }
    .rbtn-italic { font-style: italic; font-family: 'Times New Roman', serif; font-size: 14px; }
    .rbtn-underline { text-decoration: underline; font-family: 'Times New Roman', serif; font-size: 14px; }
    .rbtn-strike { text-decoration: line-through; font-family: 'Times New Roman', serif; font-size: 14px; }
    .rbtn-big { font-size: 16px; padding: 2px 5px; }
    .ribbon-sep {
        width: 1px;
        background: #e0e0e0;
        margin: 4px 3px;
        align-self: stretch;
    }
    .ribbon-select {
        padding: 3px 6px;
        border: 1px solid #c0c0c0;
        border-radius: 3px;
        font-size: 12px;
        height: 26px;
        background: white;
        cursor: pointer;
        font-family: 'Segoe UI', Tahoma, sans-serif;
    }
    .ribbon-select:hover {
        border-color: #8ab4f8;
    }
    .ribbon-select-font {
        width: 140px;
    }
    .ribbon-select-size {
        width: 52px;
    }
    .ribbon-color-wrap {
        position: relative;
        display: inline-flex;
        align-items: center;
    }
    .ribbon-color-btn {
        width: 22px;
        height: 22px;
        border: 1px solid #ccc;
        border-radius: 2px;
        cursor: pointer;
        padding: 0;
    }
    .ribbon-color-indicator {
        position: absolute;
        bottom: 1px;
        left: 4px;
        right: 4px;
        height: 3px;
        border-radius: 1px;
    }
    .btn-import-word {
        background: #27ae60 !important;
        color: white !important;
        font-weight: 600;
        font-size: 13px;
        padding: 6px 14px;
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
    }
    
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
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }
    
    .fullscreen-btn:first-child {
        background: #dc3545;
    }
    
    .fullscreen-btn:first-child:hover {
        background: #c82333;
    }
    
    .fullscreen-btn b,
    .fullscreen-btn i,
    .fullscreen-btn u {
        margin-right: 4px;
    }
    </style>
    <link rel="stylesheet" href="word-document.css?v=1.0">
</head>
<body>
    <?php include 'header-menu.php'; ?>

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
                    <?php echo SecurityHelper::csrfField(); ?>
                    
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
                        <label for="image">Ảnh đại diện *</label>
                        <input type="file" id="image" name="image" accept="image/*">
                        <small>Định dạng: JPG, PNG, GIF. Kích thước tối đa: 2MB</small>
                    </div>
                    
                    <input type="hidden" name="news_file_path" id="news_file_path" value="">

                    <div class="form-group">
                        <label for="summary">Tóm tắt <span class="required">*</span></label>
                        <textarea id="summary" name="summary" rows="3" required placeholder="Nhập tóm tắt ngắn gọn về tin tức"></textarea>
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
                        <div class="word-toolbar">
                            <div class="word-toolbar-tabs">
                                <button type="button" class="word-toolbar-tab active">Home</button>
                            </div>
                            <div class="word-ribbon">
                                <!-- Clipboard -->
                                <div class="ribbon-group">
                                    <div class="ribbon-group-btns">
                                        <button type="button" class="rbtn" onclick="document.execCommand('paste')" title="Dán">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="4" y="1" width="8" height="3" rx="0.5" stroke="#444" stroke-width="1.2"/><rect x="2" y="4" width="12" height="11" rx="1" stroke="#444" stroke-width="1.2" fill="#fff8dc"/><rect x="5" y="7" width="6" height="1" fill="#c9a227"/><rect x="5" y="9.5" width="4" height="1" fill="#c9a227"/></svg>
                                        </button>
                                        <button type="button" class="rbtn" onclick="document.execCommand('cut')" title="Cắt">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="5" cy="12" r="2.5" stroke="#444" stroke-width="1.2" fill="none"/><circle cx="11" cy="12" r="2.5" stroke="#444" stroke-width="1.2" fill="none"/><line x1="5" y1="9.5" x2="11" y2="2" stroke="#444" stroke-width="1.2"/><line x1="11" y1="9.5" x2="5" y2="2" stroke="#444" stroke-width="1.2"/></svg>
                                        </button>
                                        <button type="button" class="rbtn" onclick="document.execCommand('copy')" title="Sao chép">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="4" y="4" width="9" height="11" rx="1" stroke="#444" stroke-width="1.2" fill="#fff"/><rect x="3" y="1" width="9" height="11" rx="1" stroke="#444" stroke-width="1.2" fill="#f0f4ff"/></svg>
                                        </button>
                                    </div>
                                    <div class="ribbon-group-label">Clipboard</div>
                                </div>

                                <!-- Font -->
                                <div class="ribbon-group">
                                    <div class="ribbon-group-btns" style="flex-wrap: wrap; gap: 2px;">
                                        <select class="ribbon-select ribbon-select-font" onchange="document.execCommand('fontName', false, this.value);" title="Phông chữ">
                                            <option value="Times New Roman">Times New Roman</option>
                                            <option value="Arial">Arial</option>
                                            <option value="Courier New">Courier New</option>
                                            <option value="Georgia">Georgia</option>
                                            <option value="Verdana">Verdana</option>
                                            <option value="Tahoma">Tahoma</option>
                                        </select>
                                        <select class="ribbon-select ribbon-select-size" onchange="setFontSize(this.value);" title="Kích thước">
                                            <option value="1">8</option>
                                            <option value="2">10</option>
                                            <option value="3">12</option>
                                            <option value="4" selected>14</option>
                                            <option value="5">16</option>
                                            <option value="6">18</option>
                                            <option value="7">24</option>
                                        </select>
                                        <div class="ribbon-sep"></div>
                                        <button type="button" class="rbtn rbtn-big" onclick="changeFontSize(1)" title="Tăng cỡ chữ">A<svg width="8" height="8" viewBox="0 0 8 8" style="vertical-align:top;margin-left:1px"><path d="M4 1 L7 5 H5 L4 3 L3 5 H1 Z" fill="#444"/></svg></button>
                                        <button type="button" class="rbtn rbtn-big" onclick="changeFontSize(-1)" title="Giảm cỡ chữ">A<svg width="8" height="8" viewBox="0 0 8 8" style="vertical-align:top;margin-left:1px"><path d="M4 7 L7 3 H5 L4 5 L3 3 H1 Z" fill="#444"/></svg></button>
                                        <div class="ribbon-sep"></div>
                                        <button type="button" class="rbtn rbtn-bold" onclick="document.execCommand('bold')" title="Đậm (Ctrl+B)">B</button>
                                        <button type="button" class="rbtn rbtn-italic" onclick="document.execCommand('italic')" title="Nghiêng (Ctrl+I)">I</button>
                                        <button type="button" class="rbtn rbtn-underline" onclick="document.execCommand('underline')" title="Gạch chân (Ctrl+U)">U</button>
                                        <button type="button" class="rbtn rbtn-strike" onclick="document.execCommand('strikeThrough')" title="Gạch ngang">S</button>
                                        <div class="ribbon-sep"></div>
                                        <div class="ribbon-color-wrap">
                                            <button type="button" class="rbtn" onclick="document.execCommand('subscript')" title="Chữ dưới">X<sub style="font-size:9px">2</sub></button>
                                            <button type="button" class="rbtn" onclick="document.execCommand('superscript')" title="Chữ trên">X<sup style="font-size:9px">2</sup></button>
                                        </div>
                                        <div class="ribbon-sep"></div>
                                        <div class="ribbon-color-wrap" title="Màu chữ">
                                            <button type="button" class="rbtn" onclick="document.getElementById('textColorInput').click()">
                                                <span style="font-weight:700;font-family:serif">A</span>
                                            </button>
                                            <input type="color" id="textColorInput" value="#ff0000" style="position:absolute;bottom:2px;left:2px;width:18px;height:3px;opacity:0;pointer-events:none;" onchange="document.execCommand('foreColor', false, this.value)">
                                            <div class="ribbon-color-indicator" style="background:#ff0000" id="textColorBar"></div>
                                        </div>
                                        <div class="ribbon-color-wrap" title="Màu đánh dấu">
                                            <button type="button" class="rbtn" onclick="document.getElementById('bgColorInput').click()">
                                                <svg width="14" height="14" viewBox="0 0 14 14"><rect x="1" y="10" width="12" height="3" rx="0.5" fill="#ffff00"/><path d="M3 2 L7 8 L11 2" stroke="#444" stroke-width="1.5" fill="none"/></svg>
                                            </button>
                                            <input type="color" id="bgColorInput" value="#ffff00" style="position:absolute;bottom:2px;left:2px;width:18px;height:3px;opacity:0;pointer-events:none;" onchange="document.execCommand('backColor', false, this.value)">
                                            <div class="ribbon-color-indicator" style="background:#ffff00" id="bgColorBar"></div>
                                        </div>
                                    </div>
                                    <div class="ribbon-group-label">Font</div>
                                </div>

                                <!-- Paragraph -->
                                <div class="ribbon-group">
                                    <div class="ribbon-group-btns">
                                        <button type="button" class="rbtn" onclick="document.execCommand('insertUnorderedList')" title="Danh sách bullet">
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><circle cx="2" cy="3" r="1.3" fill="#444"/><rect x="6" y="2" width="7" height="2" rx="0.5" fill="#444"/><circle cx="2" cy="7" r="1.3" fill="#444"/><rect x="6" y="6" width="7" height="2" rx="0.5" fill="#444"/><circle cx="2" cy="11" r="1.3" fill="#444"/><rect x="6" y="10" width="7" height="2" rx="0.5" fill="#444"/></svg>
                                        </button>
                                        <button type="button" class="rbtn" onclick="document.execCommand('insertOrderedList')" title="Danh sách số">
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><text x="0" y="4.5" font-size="4" fill="#444" font-family="sans-serif">1.</text><rect x="6" y="2" width="7" height="2" rx="0.5" fill="#444"/><text x="0" y="8.5" font-size="4" fill="#444" font-family="sans-serif">2.</text><rect x="6" y="6" width="7" height="2" rx="0.5" fill="#444"/><text x="0" y="12.5" font-size="4" fill="#444" font-family="sans-serif">3.</text><rect x="6" y="10" width="7" height="2" rx="0.5" fill="#444"/></svg>
                                        </button>
                                        <div class="ribbon-sep"></div>
                                        <button type="button" class="rbtn" onclick="document.execCommand('outdent')" title="Giảm thụt lùi">
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><line x1="8" y1="2" x2="14" y2="2" stroke="#444" stroke-width="1.5"/><line x1="4" y1="6" x2="14" y2="6" stroke="#444" stroke-width="1.5"/><line x1="8" y1="10" x2="14" y2="10" stroke="#444" stroke-width="1.5"/><path d="M5 5 L1 7.5 L5 10" stroke="#444" stroke-width="1.5" fill="none"/></svg>
                                        </button>
                                        <button type="button" class="rbtn" onclick="document.execCommand('indent')" title="Tăng thụt lùi">
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><line x1="8" y1="2" x2="14" y2="2" stroke="#444" stroke-width="1.5"/><line x1="4" y1="6" x2="14" y2="6" stroke="#444" stroke-width="1.5"/><line x1="8" y1="10" x2="14" y2="10" stroke="#444" stroke-width="1.5"/><path d="M1 5 L5 7.5 L1 10" stroke="#444" stroke-width="1.5" fill="none"/></svg>
                                        </button>
                                        <div class="ribbon-sep"></div>
                                        <button type="button" class="rbtn" onclick="document.execCommand('justifyLeft')" title="Căn trái">
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><rect x="1" y="2" width="12" height="2" rx="0.3" fill="#444"/><rect x="1" y="6" width="8" height="2" rx="0.3" fill="#444"/><rect x="1" y="10" width="10" height="2" rx="0.3" fill="#444"/></svg>
                                        </button>
                                        <button type="button" class="rbtn" onclick="document.execCommand('justifyCenter')" title="Căn giữa">
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><rect x="1" y="2" width="12" height="2" rx="0.3" fill="#444"/><rect x="3" y="6" width="8" height="2" rx="0.3" fill="#444"/><rect x="2" y="10" width="10" height="2" rx="0.3" fill="#444"/></svg>
                                        </button>
                                        <button type="button" class="rbtn" onclick="document.execCommand('justifyRight')" title="Căn phải">
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><rect x="1" y="2" width="12" height="2" rx="0.3" fill="#444"/><rect x="5" y="6" width="8" height="2" rx="0.3" fill="#444"/><rect x="3" y="10" width="10" height="2" rx="0.3" fill="#444"/></svg>
                                        </button>
                                        <button type="button" class="rbtn" onclick="document.execCommand('justifyFull')" title="Căn đều">
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><rect x="1" y="2" width="12" height="2" rx="0.3" fill="#444"/><rect x="1" y="6" width="12" height="2" rx="0.3" fill="#444"/><rect x="1" y="10" width="12" height="2" rx="0.3" fill="#444"/></svg>
                                        </button>
                                        <div class="ribbon-sep"></div>
                                        <button type="button" class="rbtn" onclick="insertTable()" title="Chèn bảng">
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><rect x="1" y="1" width="12" height="12" rx="1" stroke="#444" stroke-width="1.2" fill="none"/><line x1="5" y1="1" x2="5" y2="13" stroke="#444" stroke-width="1"/><line x1="9" y1="1" x2="9" y2="13" stroke="#444" stroke-width="1"/><line x1="1" y1="5" x2="13" y2="5" stroke="#444" stroke-width="1"/><line x1="1" y1="9" x2="13" y2="9" stroke="#444" stroke-width="1"/></svg>
                                        </button>
                                    </div>
                                    <div class="ribbon-group-label">Paragraph</div>
                                </div>

                                <!-- Insert -->
                                <div class="ribbon-group">
                                    <div class="ribbon-group-btns">
                                        <button type="button" class="rbtn" onclick="insertLink()" title="Chèn hyperlink">
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M6 8 L8 6 M4 10 C2 10 1 8.5 1 7 C1 5 2.5 3.5 4.5 3.5 L5.5 3.5 M8.5 10.5 C10.5 10.5 12 9 12 7 C12 5 10.5 3.5 8.5 3.5 L7.5 3.5" stroke="#444" stroke-width="1.3" fill="none"/></svg>
                                        </button>
                                        <input type="file" id="insert-image" accept="image/*" style="display: none;" onchange="insertImage(event)">
                                        <button type="button" class="rbtn" onclick="document.getElementById('insert-image').click()" title="Chèn ảnh">
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><rect x="1" y="2" width="12" height="10" rx="1" stroke="#444" stroke-width="1.2" fill="#e8f4e8"/><circle cx="4.5" cy="5.5" r="1.5" fill="#88b44a"/><path d="M1 10 L4 7 L7 9.5 L10 6 L13 10" stroke="#5a8f29" stroke-width="1.2" fill="none"/></svg>
                                        </button>
                                    </div>
                                    <div class="ribbon-group-label">Insert</div>
                                </div>

                                <!-- Undo / Clear -->
                                <div class="ribbon-group">
                                    <div class="ribbon-group-btns">
                                        <button type="button" class="rbtn" onclick="document.execCommand('undo')" title="Hoàn tác (Ctrl+Z)">
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 5 C2 5 5 2 8 2 C11 2 13 4 13 7 C13 10 11 12 8 12 C6 12 4.5 11 4 9.5" stroke="#444" stroke-width="1.5" fill="none"/><path d="M1 6 L4 3 L4 8 Z" fill="#444"/></svg>
                                        </button>
                                        <button type="button" class="rbtn" onclick="document.execCommand('redo')" title="Làm lại (Ctrl+Y)">
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M12 5 C12 5 9 2 6 2 C3 2 1 4 1 7 C1 10 3 12 6 12 C8 12 9.5 11 10 9.5" stroke="#444" stroke-width="1.5" fill="none"/><path d="M13 6 L10 3 L10 8 Z" fill="#444"/></svg>
                                        </button>
                                        <div class="ribbon-sep"></div>
                                        <button type="button" class="rbtn" onclick="document.execCommand('removeFormat')" title="Xóa định dạng">
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M3 12 L7 2 L11 12" stroke="#e74c3c" stroke-width="1.5" fill="none"/><line x1="4.5" y1="9" x2="9.5" y2="9" stroke="#e74c3c" stroke-width="1.2"/></svg>
                                        </button>
                                        <button type="button" class="rbtn" onclick="toggleFullscreen()" title="Toàn màn hình" style="color: #9b59b6;">
                                            <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M1 5 V1 H5 M9 1 H13 V5 M13 9 V13 H9 M5 13 H1 V9" stroke="#9b59b6" stroke-width="1.5"/></svg>
                                        </button>
                                    </div>
                                    <div class="ribbon-group-label">Editing</div>
                                </div>
                            </div>
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
    <?php include 'quick-actions-toolbar.php'; ?>

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
                <button class="fullscreen-btn" onclick="document.execCommand('bold')" title="Đậm (Ctrl+B)"><b>B</b> Đậm</button>
                <button class="fullscreen-btn" onclick="document.execCommand('italic')" title="Nghiêng (Ctrl+I)"><i>I</i> Nghiêng</button>
                <button class="fullscreen-btn" onclick="document.execCommand('underline')" title="Gạch chân (Ctrl+U)"><u>U</u> Gạch chân</button>
                <button class="fullscreen-btn" onclick="document.execCommand('formatBlock', false, 'h2')" title="Tiêu đề lớn">H2 Tiêu đề</button>
                <button class="fullscreen-btn" onclick="document.execCommand('formatBlock', false, 'h3')" title="Tiêu đề nhỏ">H3 Tiêu đề nhỏ</button>
                <button class="fullscreen-btn" onclick="document.execCommand('insertUnorderedList')" title="Danh sách">• Danh sách</button>
                <button class="fullscreen-btn" onclick="insertImage()" title="Chèn ảnh">🖼️ Chèn ảnh</button>
                <button class="fullscreen-btn" onclick="insertTable()" style="background: #e74c3c;" title="Chèn bảng">📊 Bảng</button>
            `;
            document.body.appendChild(controls);
            
            // Ẩn scroll body
            document.body.style.overflow = 'hidden';
            
            // Focus vào editor
            editor.focus();
        }
    }
    
    // Hàm chèn link
    function insertLink() {
        const url = prompt('Nhập URL:', 'https://');
        if (url === null || url === '' || url === 'https://') return;
        
        const text = prompt('Nhập text hiển thị:', 'Click vào đây');
        if (text === null || text === '') return;
        
        document.execCommand('insertHTML', false, `<a href="${url}" target="_blank">${text}</a>`);
    }
    
    // Hàm chèn ảnh
    function insertImage() {
        const url = prompt('Nhập URL ảnh:', 'https://');
        if (url === null || url === '' || url === 'https://') return;
        
        const alt = prompt('Nhập mô tả ảnh (alt text):', 'Hình ảnh');
        
        const imgHtml = `<img src="${url}" alt="${alt || 'Hình ảnh'}" style="max-width: 100%; height: auto; margin: 10px 0; display: block;"><br>`;
        
        const editor = document.getElementById('content-editor');
        const div = document.createElement('div');
        div.innerHTML = imgHtml;
        editor.appendChild(div.firstChild);
        editor.appendChild(document.createElement('br'));
    }
    
    // Hàm chèn bảng
    function insertTable() {
        const rows = prompt('Nhập số hàng (mặc định 3):', '3');
        if (rows === null) return;
        
        const cols = prompt('Nhập số cột (mặc định 3):', '3');
        if (cols === null) return;
        
        const numRows = parseInt(rows) || 3;
        const numCols = parseInt(cols) || 3;
        
        let tableHtml = '<table style="width: 100%; border-collapse: collapse; margin: 20px 0; border: 1px solid #000;">';
        
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
        var file = event.target.files[0];
        if (!file) return;

        var isDoc = file.name.toLowerCase().endsWith('.doc');
        var isDocx = file.name.toLowerCase().endsWith('.docx');
        
        if (!isDoc && !isDocx) {
            alert('Hệ thống chỉ hỗ trợ file Word (.doc hoặc .docx).');
            event.target.value = '';
            return;
        }
        
        var editor = document.getElementById('content-editor');
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
    
    // Import .docx qua mammoth.js
    function importDocxFile(file, editor, event) {
        
        // Kiểm tra mammoth.js có load không
        if (typeof mammoth === 'undefined') {
            alert('Lỗi: Thư viện đọc Word chưa được tải. Vui lòng refresh lại trang.');
            event.target.value = '';
            return;
        }
        
        console.log('Bắt đầu đọc file:', file.name, 'Size:', file.size);
        
        // Hiển thị loading
        editor = document.getElementById('content-editor') || editor;
        editor.innerHTML = '<p style="text-align: center; color: #3498db; padding: 40px;">⏳ Đang đọc file Word và trích xuất hình ảnh...<br>Vui lòng đợi...</p>';
        
        const reader = new FileReader();
        
        reader.onload = function(e) {
            console.log('File đã được đọc, bắt đầu chuyển đổi...');
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
                console.log('Chuyển đổi thành công!');
                console.log('HTML length:', result.value.length);
                console.log('Warnings:', result.messages);
                
                let htmlContent = result.value;
                
                // Nếu nội dung rỗng
                if (!htmlContent || htmlContent.trim() === '') {
                    editor.innerHTML = '<p style="color: #e74c3c;">⚠️ File Word không có nội dung hoặc không đọc được. Vui lòng kiểm tra lại file.</p>';
                    alert('File Word trống hoặc không đọc được!');
                    return;
                }
                
                console.log('Preview HTML:', htmlContent.substring(0, 500));
                
                // Xử lý HTML để giữ định dạng Word
                const parser = new DOMParser();
                const doc = parser.parseFromString(htmlContent, 'text/html');
                
                // Xử lý tất cả các đoạn văn
                const paragraphs = doc.querySelectorAll('p');
                paragraphs.forEach(function(p) {
                    const text = p.textContent.trim();
                    
                    // Nếu đoạn văn chỉ chứa <strong> hoặc <b> -> căn giữa
                    const hasOnlyBold = p.querySelector('strong, b') && 
                                       p.textContent === p.querySelector('strong, b').textContent;
                    
                    // Kiểm tra văn bản in hoa toàn bộ
                    const isAllCaps = text === text.toUpperCase() && /[A-ZÀÁẢÃẠĂẰẮẲẴẶÂẦẤẨẪẬÈÉẺẼẸÊỀẾỂỄỆÌÍỈĨỊÒÓỎÕỌÔỒỐỔỖỘƠỜỚỞỠỢÙÚỦŨỤƯỪỨỬỮỰỲÝỶỸỴĐ]/.test(text);
                    
                    // Căn giữa các dòng tiêu đề
                    if (hasOnlyBold || isAllCaps || 
                        text.includes('ỦY BAN') || 
                        text.includes('CỘNG HÒA') ||
                        text.includes('QUYẾT ĐỊNH') ||
                        /^Số:\s*[\w\-\/]+/i.test(text) ||
                        /Long.{0,5}hiệp.{0,10}ngày/i.test(text)) {
                        p.style.textAlign = 'center';
                        p.style.fontWeight = 'bold';
                    }
                    
                    // Giữ màu chữ đen
                    p.style.color = '#000';
                });
                
                // Xử lý tất cả các bảng - giữ border từ Word
                const tables = doc.querySelectorAll('table');
                tables.forEach(function(table) {
                    table.style.width = '100%';
                    table.style.borderCollapse = 'collapse';
                    table.style.margin = '20px 0';
                    
                    const cells = table.querySelectorAll('th, td');
                    cells.forEach(function(cell) {
                        cell.style.padding = '10px 8px';
                        cell.style.color = '#000';
                        cell.style.wordWrap = 'break-word';
                        cell.style.wordBreak = 'break-word';
                        cell.style.verticalAlign = 'top';
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

    // Preview ảnh đại diện
    function previewImage(event) {
        const file = event.target.files[0];
        
        console.log('File selected:', file); // Debug
        
        if (!file) {
            return;
        }
        
        // Kiểm tra file type - chấp nhận nhiều định dạng hơn
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!validTypes.includes(file.type.toLowerCase())) {
            alert('Vui lòng chọn file ảnh (JPG, PNG, GIF)');
            event.target.value = '';
            return;
        }
        
        // Kiểm tra kích thước (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Kích thước ảnh không được vượt quá 2MB');
            event.target.value = '';
            return;
        }
        
        // Hiển thị preview
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewImg = document.getElementById('preview-img');
            const previewDiv = document.getElementById('image-preview');
            
            if (previewImg && previewDiv) {
                previewImg.src = e.target.result;
                previewDiv.style.display = 'block';
                console.log('Preview displayed'); // Debug
            }
        };
        
        reader.onerror = function(e) {
            console.error('FileReader error:', e);
            alert('Lỗi đọc file. Vui lòng thử lại.');
        };
        
        reader.readAsDataURL(file);
    }
    
    // Xóa preview ảnh
    function clearImagePreview() {
        const imageInput = document.getElementById('image');
        const previewImg = document.getElementById('preview-img');
        const previewDiv = document.getElementById('image-preview');
        
        if (imageInput) imageInput.value = '';
        if (previewImg) previewImg.src = '';
        if (previewDiv) previewDiv.style.display = 'none';
    }
    </script>
    <script src="word-document.js?v=1.0"></script>
</body>
</html>
