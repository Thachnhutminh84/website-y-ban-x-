<?php
header("Content-Type: text/html; charset=utf-8");
session_start();
require_once 'config.php';
require_once 'auth.php';

// Kiểm tra đăng nhập
if (!authIsLoggedIn()) {
    header("Location: dang-nhap.php");
    exit;
}

$message = '';
$videos = [];

// Lấy danh sách video để test
try {
    $conn = getDBConnection();
    $result = $conn->query("SELECT * FROM videos WHERE is_active = 1 ORDER BY created_at DESC LIMIT 5");
    while ($row = $result->fetch_assoc()) {
        $videos[] = $row;
    }
    $conn->close();
} catch (Exception $e) {
    $message = "Lỗi database: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Xóa Video - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Header thống nhất -->
        <?php include 'menu-don-gian.php'; ?>

    <main>
        <div class="container">
            <h1>🧪 Test Chức năng Xóa Video</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-error"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <div class="test-info">
                <h2>📊 Thông tin Session</h2>
                <p><strong>User ID:</strong> <?php echo authCurrentUserId(); ?></p>
                <p><strong>Role:</strong> <?php echo authCurrentRole(); ?></p>
                <p><strong>Display Name:</strong> <?php echo authDisplayName(); ?></p>
                <p><strong>Has Permission:</strong> <?php echo authHasPermission('manage_content') ? '✅ Có' : '❌ Không'; ?></p>
            </div>
            
            <div class="test-videos">
                <h2>🎬 Video để Test</h2>
                
                <?php if (empty($videos)): ?>
                    <p>Không có video nào để test. <a href="them-video-moi.php">Thêm video mới</a></p>
                <?php else: ?>
                    <div class="video-list">
                        <?php foreach ($videos as $video): ?>
                            <div class="video-item">
                                <h3><?php echo htmlspecialchars($video['title']); ?></h3>
                                <p><strong>ID:</strong> <?php echo $video['id']; ?></p>
                                <p><strong>Loại:</strong> <?php echo $video['video_type']; ?></p>
                                <p><strong>URL:</strong> <?php echo htmlspecialchars($video['video_url']); ?></p>
                                
                                <div class="test-actions">
                                    <button onclick="testDeleteVideo(<?php echo $video['id']; ?>, '<?php echo htmlspecialchars($video['title'], ENT_QUOTES); ?>')" 
                                            class="btn-delete">🧪 Test Xóa</button>
                                    <button onclick="testSaveVideo(<?php echo $video['id']; ?>, '<?php echo htmlspecialchars($video['title'], ENT_QUOTES); ?>')" 
                                            class="btn-save">💾 Test Lưu</button>
                                </div>
                                
                                <div id="result_<?php echo $video['id']; ?>" class="test-result"></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="test-links">
                <h2>🔗 Links hữu ích</h2>
                <a href="video.php" class="btn-primary">📺 Trang Video</a>
                <a href="quan-ly-video.php" class="btn-secondary">⚙️ Quản lý Video</a>
                <a href="debug-delete-video.php" class="btn-warning">🔍 Debug Delete</a>
            </div>
        </div>
    </main>

    <script>
    function testDeleteVideo(videoId, videoTitle) {
        const resultDiv = document.getElementById('result_' + videoId);
        resultDiv.innerHTML = '<p style="color: blue;">⏳ Đang test xóa...</p>';
        
        fetch('api-delete-video.php?debug=1', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ 
                video_id: videoId,
                action: 'delete'
            })
        })
        .then(response => {
            console.log('Delete Response status:', response.status);
            return response.text();
        })
        .then(text => {
            console.log('Delete Response text:', text);
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    resultDiv.innerHTML = '<div style="background: #d4edda; padding: 10px; border-radius: 5px; color: #155724; margin-top: 10px;"><strong>✅ Xóa thành công:</strong> ' + data.message + '</div>';
                    if (data.debug) {
                        resultDiv.innerHTML += '<details><summary>Debug Info</summary><pre>' + JSON.stringify(data.debug, null, 2) + '</pre></details>';
                    }
                } else {
                    resultDiv.innerHTML = '<div style="background: #f8d7da; padding: 10px; border-radius: 5px; color: #721c24; margin-top: 10px;"><strong>❌ Lỗi:</strong> ' + (data.message || 'Không xác định') + '</div>';
                    if (data.debug) {
                        resultDiv.innerHTML += '<details><summary>Debug Info</summary><pre>' + JSON.stringify(data.debug, null, 2) + '</pre></details>';
                    }
                }
            } catch (e) {
                resultDiv.innerHTML = '<div style="background: #f8d7da; padding: 10px; border-radius: 5px; color: #721c24; margin-top: 10px;"><strong>❌ Lỗi JSON:</strong><br>' + text + '</div>';
            }
        })
        .catch(error => {
            console.error('Delete Fetch error:', error);
            resultDiv.innerHTML = '<div style="background: #f8d7da; padding: 10px; border-radius: 5px; color: #721c24; margin-top: 10px;"><strong>❌ Lỗi kết nối:</strong> ' + error.message + '</div>';
        });
    }
    
    function testSaveVideo(videoId, videoTitle) {
        const resultDiv = document.getElementById('result_' + videoId);
        resultDiv.innerHTML = '<p style="color: blue;">⏳ Đang test lưu...</p>';
        
        fetch('api-save-video.php?debug=1', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ 
                video_id: videoId,
                action: 'save'
            })
        })
        .then(response => {
            console.log('Save Response status:', response.status);
            return response.text();
        })
        .then(text => {
            console.log('Save Response text:', text);
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    resultDiv.innerHTML = '<div style="background: #d4edda; padding: 10px; border-radius: 5px; color: #155724; margin-top: 10px;"><strong>✅ Lưu thành công:</strong> ' + data.message + '</div>';
                } else {
                    resultDiv.innerHTML = '<div style="background: #f8d7da; padding: 10px; border-radius: 5px; color: #721c24; margin-top: 10px;"><strong>❌ Lỗi:</strong> ' + (data.message || 'Không xác định') + '</div>';
                }
            } catch (e) {
                resultDiv.innerHTML = '<div style="background: #f8d7da; padding: 10px; border-radius: 5px; color: #721c24; margin-top: 10px;"><strong>❌ Lỗi JSON:</strong><br>' + text + '</div>';
            }
        })
        .catch(error => {
            console.error('Save Fetch error:', error);
            resultDiv.innerHTML = '<div style="background: #f8d7da; padding: 10px; border-radius: 5px; color: #721c24; margin-top: 10px;"><strong>❌ Lỗi kết nối:</strong> ' + error.message + '</div>';
        });
    }
    </script>
    
    <style>
    .test-info, .test-videos, .test-links {
        background: white;
        padding: 20px;
        margin: 20px 0;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .video-list {
        display: grid;
        gap: 20px;
    }
    
    .video-item {
        border: 1px solid #ddd;
        padding: 15px;
        border-radius: 8px;
        background: #f9f9f9;
    }
    
    .test-actions {
        margin: 15px 0;
        display: flex;
        gap: 10px;
    }
    
    .btn-delete, .btn-save, .btn-primary, .btn-secondary, .btn-warning {
        padding: 8px 16px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        font-size: 14px;
        font-weight: 600;
    }
    
    .btn-delete { background: #dc3545; color: white; }
    .btn-save { background: #6f42c1; color: white; }
    .btn-primary { background: #007bff; color: white; }
    .btn-secondary { background: #6c757d; color: white; }
    .btn-warning { background: #ffc107; color: #212529; }
    
    .btn-delete:hover { background: #c82333; }
    .btn-save:hover { background: #5a32a3; }
    .btn-primary:hover { background: #0056b3; }
    .btn-secondary:hover { background: #545b62; }
    .btn-warning:hover { background: #e0a800; }
    
    .test-result {
        margin-top: 10px;
    }
    
    .alert {
        padding: 15px;
        margin: 20px 0;
        border-radius: 5px;
    }
    
    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    details {
        margin-top: 10px;
    }
    
    pre {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 4px;
        overflow-x: auto;
        font-size: 12px;
    }
    </style>
</body>
</html>