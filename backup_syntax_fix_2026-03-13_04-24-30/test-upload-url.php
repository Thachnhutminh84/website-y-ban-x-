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
$testResults = [];

// Test 1: Kiểm tra bảng videos
$testResults['database'] = false;
try {
    $conn = getDBConnection();
    $result = $conn->query("SHOW TABLES LIKE 'videos'");
    if ($result && $result->num_rows > 0) {
        $testResults['database'] = true;
        $testResults['database_msg'] = "✅ Bảng videos tồn tại";
    } else {
        $testResults['database_msg'] = "❌ Bảng videos không tồn tại";
    }
} catch (Exception $e) {
    $testResults['database_msg'] = "❌ Lỗi: " . $e->getMessage();
}

// Test 2: Xử lý form test
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['test_url'])) {
    $title = "Test Video URL - " . date('Y-m-d H:i:s');
    $description = "Video test upload bằng URL";
    $video_url = trim($_POST['video_url']);
    $video_type = $_POST['video_type'];
    
    if (empty($video_url)) {
        $message = "❌ Vui lòng nhập URL video";
        $messageType = "error";
    } else {
        try {
            $conn = getDBConnection();
            $userId = authCurrentUserId();
            
            $stmt = $conn->prepare("INSERT INTO videos (title, description, video_url, video_type, created_by, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, NOW())");
            $stmt->bind_param("ssssi", $title, $description, $video_url, $video_type, $userId);
            
            if ($stmt->execute()) {
                $videoId = $stmt->insert_id;
                $message = "✅ Thêm video thành công! Video ID: $videoId";
                $messageType = "success";
                
                // Lấy thông tin video vừa thêm
                $stmt2 = $conn->prepare("SELECT * FROM videos WHERE id = ?");
                $stmt2->bind_param("i", $videoId);
                $stmt2->execute();
                $result = $stmt2->get_result();
                $testResults['inserted_video'] = $result->fetch_assoc();
                $stmt2->close();
            } else {
                $message = "❌ Lỗi khi thêm video: " . $stmt->error;
                $messageType = "error";
            }
            
            $stmt->close();
            $conn->close();
        } catch (Exception $e) {
            $message = "❌ Lỗi: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// Lấy danh sách video URL gần đây
$recentUrlVideos = [];
try {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM videos WHERE video_type IN ('youtube', 'vimeo') ORDER BY created_at DESC LIMIT 5");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $recentUrlVideos[] = $row;
    }
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    // Ignore
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Upload Video URL - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard-style.css">
    <style>
        .test-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }
        .test-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .test-section h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .status-box {
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
        .status-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
        }
        .btn-test {
            background: #28a745;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }
        .btn-test:hover {
            background: #218838;
        }
        .video-list {
            margin-top: 20px;
        }
        .video-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #007bff;
        }
        .video-item h4 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }
        .video-item p {
            margin: 5px 0;
            font-size: 14px;
            color: #666;
        }
        .sample-urls {
            background: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .sample-urls h4 {
            margin-top: 0;
            color: #856404;
        }
        .sample-urls code {
            display: block;
            background: white;
            padding: 8px;
            border-radius: 4px;
            margin: 5px 0;
            font-size: 13px;
            cursor: pointer;
        }
        .sample-urls code:hover {
            background: #f8f9fa;
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
    </style>
</head>
<body>
    <?php include 'menu-don-gian.php'; ?>

    <main>
        <div class="test-container">
            <h1 style="text-align: center; color: #2c3e50;">🧪 Test Upload Video bằng URL</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Test 1: Kiểm tra Database -->
            <div class="test-section">
                <h2>1. 📊 Kiểm tra Database</h2>
                <div class="status-box <?php echo $testResults['database'] ? 'status-success' : 'status-error'; ?>">
                    <?php echo $testResults['database_msg']; ?>
                </div>
            </div>

            <!-- Test 2: Form Test Upload URL -->
            <div class="test-section">
                <h2>2. 🧪 Test Upload Video URL</h2>
                
                <div class="sample-urls">
                    <h4>📺 URL mẫu để test (click để copy):</h4>
                    <code onclick="copyToInput(this, 'youtube')">https://www.youtube.com/watch?v=dQw4w9WgXcQ</code>
                    <code onclick="copyToInput(this, 'youtube')">https://youtu.be/dQw4w9WgXcQ</code>
                    <code onclick="copyToInput(this, 'vimeo')">https://vimeo.com/148751763</code>
                </div>
                
                <form method="POST">
                    <input type="hidden" name="test_url" value="1">
                    
                    <div class="form-group">
                        <label for="video_type">Loại video:</label>
                        <select id="video_type" name="video_type" required>
                            <option value="youtube">📺 YouTube</option>
                            <option value="vimeo">🎬 Vimeo</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="video_url">URL Video:</label>
                        <input type="url" id="video_url" name="video_url" required
                               placeholder="https://www.youtube.com/watch?v=...">
                    </div>
                    
                    <button type="submit" class="btn-test">🚀 Test Upload URL</button>
                </form>
                
                <?php if (isset($testResults['inserted_video'])): ?>
                    <div style="margin-top: 20px;">
                        <h3>✅ Video vừa thêm:</h3>
                        <div class="video-item">
                            <h4><?php echo htmlspecialchars($testResults['inserted_video']['title']); ?></h4>
                            <p><strong>ID:</strong> <?php echo $testResults['inserted_video']['id']; ?></p>
                            <p><strong>URL:</strong> <?php echo htmlspecialchars($testResults['inserted_video']['video_url']); ?></p>
                            <p><strong>Loại:</strong> <?php echo $testResults['inserted_video']['video_type']; ?></p>
                            <p><strong>Trạng thái:</strong> <?php echo $testResults['inserted_video']['is_active'] ? '✅ Active' : '❌ Inactive'; ?></p>
                            <p><strong>Ngày tạo:</strong> <?php echo $testResults['inserted_video']['created_at']; ?></p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Test 3: Danh sách video URL gần đây -->
            <div class="test-section">
                <h2>3. 📋 Video URL gần đây (5 video mới nhất)</h2>
                
                <?php if (empty($recentUrlVideos)): ?>
                    <p style="color: #666;">Chưa có video URL nào trong database.</p>
                <?php else: ?>
                    <div class="video-list">
                        <?php foreach ($recentUrlVideos as $video): ?>
                            <div class="video-item">
                                <h4><?php echo htmlspecialchars($video['title']); ?></h4>
                                <p><strong>ID:</strong> <?php echo $video['id']; ?></p>
                                <p><strong>URL:</strong> <a href="<?php echo htmlspecialchars($video['video_url']); ?>" target="_blank"><?php echo htmlspecialchars($video['video_url']); ?></a></p>
                                <p><strong>Loại:</strong> <?php echo $video['video_type']; ?></p>
                                <p><strong>Lượt xem:</strong> <?php echo number_format($video['views']); ?></p>
                                <p><strong>Ngày tạo:</strong> <?php echo $video['created_at']; ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Links -->
            <div class="test-section">
                <h2>🔗 Links hữu ích</h2>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="them-video.php" class="btn-test" style="text-decoration: none; display: inline-block;">📝 Form thêm video</a>
                    <a href="quan-ly-video.php" class="btn-test" style="text-decoration: none; display: inline-block; background: #007bff;">📹 Quản lý video</a>
                    <a href="video.php" class="btn-test" style="text-decoration: none; display: inline-block; background: #17a2b8;" target="_blank">👀 Xem trang video</a>
                    <a href="kiem-tra-video-da-them.php" class="btn-test" style="text-decoration: none; display: inline-block; background: #ffc107; color: #212529;">🔍 Kiểm tra hệ thống</a>
                </div>
            </div>
        </div>
    </main>

    <script>
    function copyToInput(element, type) {
        const url = element.textContent;
        document.getElementById('video_url').value = url;
        document.getElementById('video_type').value = type;
        
        // Hiệu ứng
        element.style.background = '#d4edda';
        setTimeout(() => {
            element.style.background = 'white';
        }, 500);
    }
    </script>
</body>
</html>
