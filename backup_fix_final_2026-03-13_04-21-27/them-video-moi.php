<?php
header("Content-Type: text/html; charset=utf-8");
session_start();
require_once 'config.php';
require_once 'auth.php';

// Kiểm tra đăng nhập
if (!authIsLoggedIn() || !authHasPermission('manage_content')) {
    header("Location: dang-nhap.php");
    exit;
}

$message = '';
$messageType = '';

// Xử lý upload
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    // Debug
    error_log("POST received - Title: $title");
    error_log("FILES: " . print_r($_FILES, true));
    
    if (empty($title)) {
        $message = "Vui lòng nhập tiêu đề video";
        $messageType = "error";
    } elseif (!isset($_FILES['video_file']) || $_FILES['video_file']['error'] != UPLOAD_ERR_OK) {
        $message = "Vui lòng chọn file video. Lỗi: " . ($_FILES['video_file']['error'] ?? 'Không có file');
        $messageType = "error";
    } else {
        $file = $_FILES['video_file'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $allowed = ['mp4', 'webm', 'ogg', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'wav', 'mp3'];
        
        if (!in_array($fileExt, $allowed)) {
            $message = "Định dạng file không hỗ trợ. Chỉ chấp nhận: " . implode(', ', $allowed);
            $messageType = "error";
        } elseif ($fileSize > 500 * 1024 * 1024) {
            $message = "File quá lớn. Tối đa 500MB";
            $messageType = "error";
        } else {
            // Tạo thư mục
            if (!is_dir('videos/')) {
                mkdir('videos/', 0777, true);
                chmod('videos/', 0777);
            }
            
            // Tạo tên file mới
            $newFileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($fileName, PATHINFO_FILENAME));
            $newFileName = $newFileName . '_' . time() . '.' . $fileExt;
            $uploadPath = 'videos/' . $newFileName;
            
            // Upload file
            if (move_uploaded_file($fileTmpName, $uploadPath)) {
                // Lưu vào database
                try {
                    $conn = getDBConnection();
                    $userId = authCurrentUserId();
                    $videoType = 'local';
                    
                    $stmt = $conn->prepare("INSERT INTO videos (title, description, video_url, video_type, created_by, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
                    $stmt->bind_param("ssssi", $title, $description, $uploadPath, $videoType, $userId);
                    
                    if ($stmt->execute()) {
                        $videoId = $stmt->insert_id;
                        $message = "✅ Upload thành công! Video ID: $videoId, File: $newFileName";
                        $messageType = "success";
                        
                        // Reset form
                        $title = '';
                        $description = '';
                    } else {
                        $message = "File đã upload nhưng lỗi lưu database: " . $stmt->error;
                        $messageType = "warning";
                    }
                    
                    $stmt->close();
                    $conn->close();
                } catch (Exception $e) {
                    $message = "File đã upload nhưng lỗi database: " . $e->getMessage();
                    $messageType = "warning";
                }
            } else {
                $message = "Lỗi upload file. Kiểm tra quyền thư mục videos/";
                $messageType = "error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Video Mới - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard-style.css">
    <style>
        .upload-form {
            max-width: 600px;
            margin: 30px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #007bff;
        }
        .btn-submit {
            background: #28a745;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }
        .btn-submit:hover {
            background: #218838;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .file-info {
            margin-top: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            font-size: 13px;
        }
    </style>
</head>
<body>
        <?php include 'menu-don-gian.php'; ?>

    <main>
        <div class="container">
            <h1 style="text-align: center; margin: 30px 0;">📤 Thêm Video Mới</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <div class="upload-form">
                <form method="POST" enctype="multipart/form-data" id="uploadForm">
                    <div class="form-group">
                        <label for="title">Tiêu đề video *</label>
                        <input type="text" id="title" name="title" required 
                               value="<?php echo htmlspecialchars($title ?? ''); ?>"
                               placeholder="Nhập tiêu đề video...">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Mô tả video</label>
                        <textarea id="description" name="description" rows="4" 
                                  placeholder="Nhập mô tả video..."><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="video_file">Chọn file video *</label>
                        <input type="file" id="video_file" name="video_file" required
                               accept="video/*,audio/*,.mp4,.webm,.ogg,.avi,.mov,.wmv,.flv,.mkv,.wav,.mp3"
                               onchange="showFileInfo(this)">
                        <small>Hỗ trợ: MP4, WebM, OGG, AVI, MOV, WMV, FLV, MKV, WAV, MP3 (tối đa 500MB)</small>
                        <div id="fileInfo" class="file-info" style="display: none;"></div>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        💾 Upload Video
                    </button>
                </form>
                
                <div style="margin-top: 20px; text-align: center;">
                    <a href="video-files.php" style="color: #007bff;">📁 Xem tất cả video</a> |
                    <a href="quan-ly-video.php" style="color: #007bff;">⚙️ Quản lý video</a> |
                    <a href="kiem-tra-video-da-them.php" style="color: #007bff;">🔍 Kiểm tra hệ thống</a>
                </div>
            </div>
        </div>
    </main>

    <script>
    function showFileInfo(input) {
        const fileInfo = document.getElementById('fileInfo');
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const size = (file.size / (1024 * 1024)).toFixed(2);
            fileInfo.innerHTML = `
                <strong>File đã chọn:</strong><br>
                📁 ${file.name}<br>
                📊 ${size} MB<br>
                🎬 ${file.type || 'Unknown type'}
            `;
            fileInfo.style.display = 'block';
            fileInfo.style.color = size > 500 ? 'red' : 'green';
        }
    }
    
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        const fileInput = document.getElementById('video_file');
        if (!fileInput.files || !fileInput.files[0]) {
            e.preventDefault();
            alert('Vui lòng chọn file video!');
            return false;
        }
        
        const file = fileInput.files[0];
        const size = file.size / (1024 * 1024);
        if (size > 500) {
            e.preventDefault();
            alert('File quá lớn! Tối đa 500MB. File của bạn: ' + size.toFixed(2) + ' MB');
            return false;
        }
        
        // Hiển thị loading
        const btn = this.querySelector('.btn-submit');
        btn.disabled = true;
        btn.textContent = '⏳ Đang upload...';
    });
    </script>
</body>
</html>