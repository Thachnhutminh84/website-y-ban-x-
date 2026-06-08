<?php
header("Content-Type: text/html; charset=utf-8");
session_start();
require_once 'config.php';
require_once 'auth.php';

// Kiểm tra quyền admin
if (!authIsLoggedIn() || !authHasPermission('manage_content')) {
    header("Location: dang-nhap.php");
    exit;
}

$currentRole = authCurrentRole();
$displayName = authDisplayName();
$userId = authCurrentUserId();

$message = '';
$messageType = '';

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $video_type = $_POST['video_type'] ?? 'youtube';
    $video_url = '';
    
    // Xử lý theo loại video
    if ($video_type == 'local' && isset($_FILES['video_file']) && $_FILES['video_file']['error'] == UPLOAD_ERR_OK) {
        // Upload file
        $uploadedFile = $_FILES['video_file'];
        $fileName = $uploadedFile['name'];
        $fileTmpName = $uploadedFile['tmp_name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Kiểm tra định dạng
        $allowedExtensions = ['mp4', 'webm', 'ogg', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'wav', 'mp3'];
        if (in_array($fileExtension, $allowedExtensions)) {
            // Tạo tên file an toàn
            $safeFileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($fileName, PATHINFO_FILENAME));
            $newFileName = $safeFileName . '_' . time() . '.' . $fileExtension;
            $uploadPath = 'videos/' . $newFileName;
            
            // Tạo thư mục nếu chưa có
            if (!is_dir('videos/')) {
                mkdir('videos/', 0755, true);
            }
            
            // Upload file
            if (move_uploaded_file($fileTmpName, $uploadPath)) {
                $video_url = $uploadPath;
                // Không set message ở đây, để lưu database trước
            } else {
                $message = "Lỗi khi upload file";
                $messageType = "error";
            }
        } else {
            $message = "Định dạng file không được hỗ trợ";
            $messageType = "error";
        }
    } elseif ($video_type == 'local') {
        // Không có file được chọn
        $message = "Vui lòng chọn file video để upload";
        $messageType = "error";
    } else {
        // URL video
        $video_url = trim($_POST['video_url'] ?? '');
    }
    
    // Lưu vào database nếu có đủ thông tin và không có lỗi
    if (!empty($title) && !empty($video_url) && $messageType !== 'error') {
        try {
            $conn = getDBConnection();
            
            // Kiểm tra bảng videos có tồn tại không
            $result = $conn->query("SHOW TABLES LIKE 'videos'");
            if ($result && $result->num_rows > 0) {
                $stmt = $conn->prepare("INSERT INTO videos (title, description, video_url, video_type, created_by, is_active) VALUES (?, ?, ?, ?, ?, 1)");
                $stmt->bind_param("ssssi", $title, $description, $video_url, $video_type, $userId);
                
                if ($stmt->execute()) {
                    if ($video_type == 'local') {
                        $message = "Đã upload và lưu video thành công: " . basename($video_url);
                    } else {
                        $message = "Đã thêm video thành công!";
                    }
                    $messageType = "success";
                    
                    // Reset form
                    $title = $description = $video_url = '';
                } else {
                    $message = "Lỗi khi lưu video: " . $stmt->error;
                    $messageType = "error";
                }
                $stmt->close();
            } else {
                $message = "Bảng videos chưa tồn tại. Vui lòng chạy setup-video-system.php";
                $messageType = "error";
            }
            
            $conn->close();
        } catch (Exception $e) {
            $message = "Lỗi: " . $e->getMessage();
            $messageType = "error";
        }
    } elseif (empty($message)) {
        $message = "Vui lòng điền đầy đủ thông tin";
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Video Đơn Giản - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard-style.css">
</head>
<body>
    <?php include 'menu-don-gian.php'; ?>

    <main>
        <div class="admin-container">
            <div class="admin-header">
                <h1>➕ Thêm Video Đơn Giản</h1>
                <div class="admin-actions">
                    <a href="quan-ly-video.php" class="btn-secondary">← Quay lại danh sách</a>
                    <a href="them-video.php" class="btn-outline">🔧 Form nâng cao</a>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <div class="form-container">
                <form method="POST" enctype="multipart/form-data" class="simple-video-form">
                    <div class="form-section">
                        <h3>📝 Thông tin video</h3>
                        
                        <div class="form-group">
                            <label for="title">Tiêu đề video *</label>
                            <input type="text" id="title" name="title" required 
                                   value="<?php echo htmlspecialchars($title ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                   placeholder="Nhập tiêu đề video...">
                        </div>

                        <div class="form-group">
                            <label for="description">Mô tả video</label>
                            <textarea id="description" name="description" rows="3" 
                                      placeholder="Nhập mô tả video..."><?php echo htmlspecialchars($description ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="video_type">Loại video *</label>
                            <select id="video_type" name="video_type" onchange="toggleInputType()" required>
                                <option value="local">💾 Upload file từ máy tính</option>
                                <option value="youtube">📺 YouTube</option>
                                <option value="vimeo">🎬 Vimeo</option>
                            </select>
                        </div>

                        <!-- File Upload -->
                        <div class="form-group" id="file-group">
                            <label for="video_file">Chọn file video *</label>
                            <input type="file" id="video_file" name="video_file" required
                                   accept="video/*,audio/*,.mp4,.webm,.ogg,.avi,.mov,.wmv,.flv,.mkv,.wav,.mp3">
                            <small>Hỗ trợ: MP4, WebM, OGG, AVI, MOV, WMV, FLV, MKV, WAV, MP3 (tối đa 100MB)</small>
                            <div id="file-info" style="margin-top: 10px; color: #666;"></div>
                        </div>

                        <!-- URL Input -->
                        <div class="form-group" id="url-group" style="display: none;">
                            <label for="video_url">URL Video *</label>
                            <input type="url" id="video_url" name="video_url" 
                                   value="<?php echo htmlspecialchars($video_url ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                   placeholder="https://www.youtube.com/watch?v=...">
                            <small>Ví dụ: https://www.youtube.com/watch?v=ABC123</small>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">💾 Lưu video</button>
                        <button type="reset" class="btn-secondary">🔄 Đặt lại</button>
                        <a href="quan-ly-video.php" class="btn-outline">❌ Hủy</a>
                    </div>
                </form>
            </div>

            <div class="help-section">
                <h3>💡 Hướng dẫn sử dụng</h3>
                <div class="help-grid">
                    <div class="help-card">
                        <h4>💾 Upload File (Mặc định)</h4>
                        <p>1. Giữ nguyên "Upload file từ máy tính"</p>
                        <p>2. Click "Chọn tệp" và chọn video</p>
                        <p>3. Click "Lưu video"</p>
                    </div>
                    <div class="help-card">
                        <h4>📺 YouTube</h4>
                        <p>1. Chọn "YouTube"</p>
                        <p>2. Paste URL video YouTube</p>
                        <p>3. Click "Lưu video"</p>
                    </div>
                    <div class="help-card">
                        <h4>🎬 Vimeo</h4>
                        <p>1. Chọn "Vimeo"</p>
                        <p>2. Paste URL video Vimeo</p>
                        <p>3. Click "Lưu video"</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
    function toggleInputType() {
        const videoType = document.getElementById('video_type').value;
        const urlGroup = document.getElementById('url-group');
        const fileGroup = document.getElementById('file-group');
        const urlInput = document.getElementById('video_url');
        const fileInput = document.getElementById('video_file');
        
        if (videoType === 'local') {
            // Hiển thị file input, ẩn URL input
            urlGroup.style.display = 'none';
            fileGroup.style.display = 'block';
            urlInput.required = false;
            fileInput.required = true;
        } else {
            // Hiển thị URL input, ẩn file input
            urlGroup.style.display = 'block';
            fileGroup.style.display = 'none';
            urlInput.required = true;
            fileInput.required = false;
            
            // Cập nhật placeholder
            if (videoType === 'youtube') {
                urlInput.placeholder = 'https://www.youtube.com/watch?v=...';
            } else if (videoType === 'vimeo') {
                urlInput.placeholder = 'https://vimeo.com/...';
            }
        }
    }
    
    // Hiển thị thông tin file khi chọn
    document.getElementById('video_file').addEventListener('change', function() {
        const fileInfo = document.getElementById('file-info');
        const file = this.files[0];
        
        if (file) {
            const fileSize = (file.size / (1024 * 1024)).toFixed(2);
            fileInfo.innerHTML = `
                <strong>File đã chọn:</strong><br>
                📁 ${file.name}<br>
                📊 ${fileSize} MB<br>
                🎬 ${file.type || 'Unknown type'}
            `;
            fileInfo.style.color = '#28a745';
        } else {
            fileInfo.innerHTML = '';
        }
    });
    
    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        toggleInputType();
    });
    </script>

    <style>
    .simple-video-form {
        max-width: 600px;
        margin: 0 auto;
    }

    .form-section {
        background: #f8f9fa;
        padding: 30px;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    .form-section h3 {
        color: #2c3e50;
        margin-bottom: 20px;
        font-size: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #2c3e50;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 12px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.3s ease;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
    }

    .form-group small {
        display: block;
        margin-top: 5px;
        color: #666;
        font-size: 12px;
    }

    .form-actions {
        display: flex;
        gap: 15px;
        justify-content: center;
        padding: 20px 0;
    }

    .help-section {
        background: white;
        border-radius: 10px;
        padding: 30px;
        margin-top: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .help-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .help-card {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #007bff;
    }

    .help-card h4 {
        color: #007bff;
        margin-bottom: 10px;
    }

    .help-card p {
        margin: 5px 0;
        font-size: 14px;
    }

    #file-info {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 5px;
        border-left: 3px solid #28a745;
        font-size: 13px;
    }

    @media (max-width: 768px) {
        .form-actions {
            flex-direction: column;
        }
        
        .help-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>
</body>
</html>