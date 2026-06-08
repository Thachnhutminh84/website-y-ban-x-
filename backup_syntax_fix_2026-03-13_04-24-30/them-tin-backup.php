<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: dang-nhap.php");
    exit();
}

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
    </style>
</head>
<body>
    <?php include 'menu-don-gian.php'; ?>

    <main>
        <section class="page-header">
            <div class="container">
                <h2>Sửa tin tức</h2>
                <p>Cập nhật thông tin tin tức</p>
            </div>
        </section>

        <section class="form-section">
            <div class="container">
                <form action="xu-ly-sua-tin.php" method="POST" enctype="multipart/form-data" class="news-form">
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

                    <div class="form-group">
                        <label for="summary">Tóm tắt <span class="required">*</span></label>
                        <textarea id="summary" name="summary" rows="3" required><?php echo htmlspecialchars($current_news['summary']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="content">Nội dung đầy đủ <span class="required">*</span></label>
                        <div style="margin-bottom: 10px;">
                            <input type="file" id="import-word-file" accept=".docx,.doc" style="display: none;" onchange="importWordFile(event)">
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
                            <input type="file" id="insert-image" accept="image/*" style="display: none;" onchange="insertImage(event)">
                            <button type="button" class="editor-btn" onclick="document.getElementById('insert-image').click()" style="background: #3498db; color: white;">📷 Chèn ảnh</button>
                        </div>
                        <div id="content-editor" class="content-editor" contenteditable="true"><?php echo $current_news['content']; ?></div>
                        <textarea id="content" name="content" style="display: none;" required></textarea>
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
                const htmlContent = result.value;
                
                // Trích xuất tiêu đề từ H1 hoặc đoạn đầu
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = htmlContent;
                
                let extractedTitle = '';
                const h1 = tempDiv.querySelector('h1');
                if (h1) {
                    extractedTitle = h1.textContent.trim();
                    h1.remove();
                } else {
                    const firstP = tempDiv.querySelector('p');
                    if (firstP) {
                        extractedTitle = firstP.textContent.trim().substring(0, 100);
                    }
                }
                
                // Trích xuất tóm tắt
                const textContent = tempDiv.textContent.trim();
                const extractedSummary = textContent.substring(0, 200) + (textContent.length > 200 ? '...' : '');
                
                // Nội dung đầy đủ
                const finalContent = tempDiv.innerHTML;
                
                // Điền HTML vào editor
                editor.innerHTML = finalContent;
                
                // Thêm style cho hình ảnh
                const images = editor.querySelectorAll('img');
                images.forEach(img => {
                    img.style.maxWidth = '100%';
                    img.style.height = 'auto';
                    img.style.display = 'block';
                    img.style.margin = '20px 0';
                    img.style.borderRadius = '8px';
                });
                
                // Hiển thị popup xác nhận
                showImportConfirmation(extractedTitle, images.length, extractedSummary);
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
</body>
</html>
