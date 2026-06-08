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

$message = '';
$messageType = '';
$testResults = [];

// Test xóa video
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['test_delete'])) {
    $videoId = (int)$_POST['video_id'];
    
    if ($videoId > 0) {
        // Gọi API xóa video
        $postData = json_encode([
            'video_id' => $videoId,
            'action' => 'delete'
        ]);
        
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Content-Type: application/json',
                    'Cookie: ' . $_SERVER['HTTP_COOKIE']
                ],
                'content' => $postData
            ]
        ]);
        
        $response = file_get_contents('http://localhost/website-thuctap/api-delete-video.php', false, $context);
        $result = json_decode($response, true);
        
        if ($result && $result['success']) {
            $message = "✅ " . $result['message'];
            $messageType = "success";
        } else {
            $message = "❌ " . ($result['message'] ?? 'Lỗi không xác định');
            $messageType = "error";
        }
    }
}

// Lấy danh sách video để test
$videos = [];
try {
    $conn = getDBConnection();
    $result = $conn->query("SELECT * FROM videos WHERE is_active = 1 ORDER BY created_at DESC LIMIT 10");
    while ($row = $result->fetch_assoc()) {
        $videos[] = $row;
    }
    $conn->close();
} catch (Exception $e) {
    $testResults['error'] = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Xóa Video - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard-style.css">
    <style>
        .test-container {
            max-width: 800px;
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
        .video-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
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
        .btn-test {
            background: #dc3545;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-test:hover {
            background: #c82333;
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
        <div class="test-container">
            <h1 style="text-align: center; color: #2c3e50;">🧪 Test Xóa Video</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="test-section">
                <h2>📹 Danh sách Video (Có thể xóa)</h2>
                
                <?php if (empty($videos)): ?>
                    <p>Không có video nào để test xóa.</p>
                    <a href="them-video.php" class="btn-test" style="background: #28a745;">➕ Thêm video test</a>
                <?php else: ?>
                    <?php foreach ($videos as $video): ?>
                        <div class="video-item">
                            <h4><?php echo htmlspecialchars($video['title']); ?></h4>
                            <p><strong>ID:</strong> <?php echo $video['id']; ?></p>
                            <p><strong>Loại:</strong> <?php echo $video['video_type']; ?></p>
                            <p><strong>URL:</strong> <?php echo htmlspecialchars($video['video_url']); ?></p>
                            <p><strong>Lượt xem:</strong> <?php echo number_format($video['views']); ?></p>
                            <p><strong>Ngày tạo:</strong> <?php echo $video['created_at']; ?></p>
                            
                            <form method="POST" style="display: inline;" 
                                  onsubmit="return confirm('Bạn có chắc muốn xóa video này?')">
                                <input type="hidden" name="test_delete" value="1">
                                <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                                <button type="submit" class="btn-test">🗑️ Test Xóa</button>
                            </form>
                            
                            <button onclick="testDeleteAjax(<?php echo $video['id']; ?>, '<?php echo htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8'); ?>')" 
                                    class="btn-test" style="background: #6f42c1; margin-left: 10px;">
                                🧪 Test AJAX
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="test-section">
                <h2>🔗 Links hữu ích</h2>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="video.php" class="btn-test" style="background: #007bff;" target="_blank">👀 Xem Trang Video</a>
                    <a href="quan-ly-video.php" class="btn-test" style="background: #28a745;">⚙️ Quản lý Video</a>
                    <a href="them-video.php" class="btn-test" style="background: #17a2b8;">➕ Thêm Video</a>
                </div>
            </div>
        </div>
    </main>

    <script>
    function testDeleteAjax(videoId, videoTitle) {
        if (confirm('Test xóa video "' + videoTitle + '" bằng AJAX?')) {
            fetch('api-delete-video.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    video_id: videoId,
                    action: 'delete'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ ' + data.message);
                    location.reload();
                } else {
                    alert('❌ Lỗi: ' + (data.message || 'Không thể xóa video'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Lỗi kết nối: ' + error.message);
            });
        }
    }
    </script>
</body>
</html>