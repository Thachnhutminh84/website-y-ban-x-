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

$videoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($videoId <= 0) {
    header("Location: quan-ly-video.php");
    exit;
}

$message = '';
$messageType = '';
$video = null;

// Lấy thông tin video
try {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM videos WHERE id = ?");
    $stmt->bind_param("i", $videoId);
    $stmt->execute();
    $result = $stmt->get_result();
    $video = $result->fetch_assoc();
    $stmt->close();
    
    if (!$video) {
        header("Location: quan-ly-video.php");
        exit;
    }
} catch (Exception $e) {
    $message = "Lỗi: " . $e->getMessage();
    $messageType = "error";
}

// Xử lý cập nhật
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $video) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $album_id = isset($_POST['album_id']) ? (int)$_POST['album_id'] : null;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    if (empty($title)) {
        $message = "Vui lòng nhập tiêu đề";
        $messageType = "error";
    } else {
        try {
            $stmt = $conn->prepare("UPDATE videos SET title = ?, description = ?, album_id = ?, is_featured = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("ssiii", $title, $description, $album_id, $is_featured, $videoId);
            
            if ($stmt->execute()) {
                $message = "Cập nhật video thành công!";
                $messageType = "success";
                
                // Cập nhật lại thông tin video
                $video['title'] = $title;
                $video['description'] = $description;
                $video['album_id'] = $album_id;
                $video['is_featured'] = $is_featured;
            } else {
                $message = "Lỗi cập nhật: " . $stmt->error;
                $messageType = "error";
            }
            $stmt->close();
        } catch (Exception $e) {
            $message = "Lỗi: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// Lấy danh sách album
$albums = [];
try {
    $stmt = $conn->prepare("SELECT * FROM video_albums WHERE is_active = 1 ORDER BY display_order ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $albums[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    // Ignore
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Video - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard-style.css">
    <style>
        .edit-form {
            max-width: 800px;
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
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #007bff;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .checkbox-group input[type="checkbox"] {
            width: auto;
        }
        .video-preview {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .video-preview h3 {
            margin-top: 0;
        }
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <header class="header--compact">
        <div class="container">
            <div class="logo">
                <img src="images/logo.png" alt="Logo">
                <div class="header-text">
                    <h1>ỦY BAN NHÂN DÂN XÃ LONG HIỆP</h1>
                </div>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <h1 style="text-align: center; margin: 30px 0;">✏️ Sửa Video</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($video): ?>
                <div class="edit-form">
                    <div class="video-preview">
                        <h3>📹 Thông tin video hiện tại</h3>
                        <p><strong>File:</strong> <?php echo htmlspecialchars($video['video_url']); ?></p>
                        <p><strong>Loại:</strong> <?php echo htmlspecialchars($video['video_type']); ?></p>
                        <p><strong>Lượt xem:</strong> <?php echo number_format($video['views']); ?></p>
                        <p><strong>Ngày tạo:</strong> <?php echo date('d/m/Y H:i', strtotime($video['created_at'])); ?></p>
                        
                        <?php if ($video['video_type'] == 'local' && file_exists($video['video_url'])): ?>
                            <p>
                                <a href="<?php echo htmlspecialchars($video['video_url']); ?>" target="_blank" class="btn btn-secondary">
                                    👀 Xem video
                                </a>
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label for="title">Tiêu đề video *</label>
                            <input type="text" id="title" name="title" required 
                                   value="<?php echo htmlspecialchars($video['title']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Mô tả video</label>
                            <textarea id="description" name="description" rows="5"><?php echo htmlspecialchars($video['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="album_id">Album</label>
                            <select id="album_id" name="album_id">
                                <option value="">-- Chưa phân loại --</option>
                                <?php foreach ($albums as $album): ?>
                                    <option value="<?php echo $album['id']; ?>" 
                                            <?php echo ($video['album_id'] == $album['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($album['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="is_featured" name="is_featured" value="1"
                                       <?php echo $video['is_featured'] ? 'checked' : ''; ?>>
                                <label for="is_featured" style="margin: 0;">⭐ Đặt làm video nổi bật</label>
                            </div>
                        </div>
                        
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">💾 Lưu thay đổi</button>
                            <a href="quan-ly-video.php" class="btn btn-secondary">← Quay lại</a>
                            <a href="video.php" class="btn btn-secondary" target="_blank">👀 Xem trang video</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>