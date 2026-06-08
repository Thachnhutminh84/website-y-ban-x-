<?php
header("Content-Type: text/html; charset=utf-8");
session_start();
require_once 'config.php';
require_once 'auth.php';

echo "<h1>🔍 Debug Xóa Video</h1>";

// 1. Kiểm tra session
echo "<h2>1. 📊 Kiểm tra Session</h2>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session data:</strong></p>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

// 2. Kiểm tra đăng nhập
echo "<h2>2. 👤 Kiểm tra Đăng nhập</h2>";
$isLoggedIn = authIsLoggedIn();
echo "<p><strong>Đã đăng nhập:</strong> " . ($isLoggedIn ? '✅ Có' : '❌ Không') . "</p>";

if ($isLoggedIn) {
    echo "<p><strong>User ID:</strong> " . authCurrentUserId() . "</p>";
    echo "<p><strong>Role:</strong> " . authCurrentRole() . "</p>";
    echo "<p><strong>Display Name:</strong> " . authDisplayName() . "</p>";
    
    // 3. Kiểm tra quyền
    echo "<h2>3. 🔐 Kiểm tra Quyền</h2>";
    $hasPermission = authHasPermission('manage_content');
    echo "<p><strong>Quyền manage_content:</strong> " . ($hasPermission ? '✅ Có' : '❌ Không') . "</p>";
    
    if ($hasPermission) {
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; color: #155724;'>";
        echo "<p><strong>✅ Có đủ quyền để xóa video!</strong></p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;'>";
        echo "<p><strong>❌ Không có quyền xóa video!</strong></p>";
        echo "</div>";
    }
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;'>";
    echo "<p><strong>❌ Chưa đăng nhập!</strong></p>";
    echo "<p><a href='dang-nhap.php'>Đăng nhập tại đây</a></p>";
    echo "</div>";
}

// 4. Test API trực tiếp
if ($isLoggedIn && authHasPermission('manage_content')) {
    echo "<h2>4. 🧪 Test API Trực tiếp</h2>";
    
    // Lấy video để test
    try {
        $conn = getDBConnection();
        $result = $conn->query("SELECT * FROM videos WHERE is_active = 1 LIMIT 1");
        if ($video = $result->fetch_assoc()) {
            echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
            echo "<h4>Video để test:</h4>";
            echo "<p><strong>ID:</strong> " . $video['id'] . "</p>";
            echo "<p><strong>Tiêu đề:</strong> " . htmlspecialchars($video['title']) . "</p>";
            echo "<p><strong>Loại:</strong> " . $video['video_type'] . "</p>";
            echo "</div>";
            
            echo "<button onclick='testDeleteAPI(" . $video['id'] . ")' style='background: #dc3545; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>🧪 Test Xóa API</button>";
            
            echo "<div id='testResult' style='margin-top: 15px;'></div>";
        } else {
            echo "<p>Không có video nào để test.</p>";
        }
        $conn->close();
    } catch (Exception $e) {
        echo "<p style='color: red;'>Lỗi database: " . $e->getMessage() . "</p>";
    }
}

// 5. Kiểm tra file API
echo "<h2>5. 📄 Kiểm tra File API</h2>";
$apiFiles = ['api-delete-video.php', 'api-save-video.php'];
foreach ($apiFiles as $file) {
    if (file_exists($file)) {
        echo "<p>✅ $file - Tồn tại</p>";
    } else {
        echo "<p>❌ $file - Không tồn tại</p>";
    }
}

echo "<h2>🔗 Links</h2>";
echo "<div style='display: flex; gap: 10px; flex-wrap: wrap;'>";
echo "<a href='video.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>👀 Trang Video</a>";
echo "<a href='test-delete-video.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🧪 Test Xóa</a>";
echo "<a href='quan-ly-video.php' style='background: #ffc107; color: black; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>⚙️ Quản lý</a>";
echo "</div>";
?>

<script>
function testDeleteAPI(videoId) {
    const resultDiv = document.getElementById('testResult');
    resultDiv.innerHTML = '<p>⏳ Đang test...</p>';
    
    fetch('api-delete-video.php', {
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
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        return response.text();
    })
    .then(text => {
        console.log('Response text:', text);
        try {
            const data = JSON.parse(text);
            if (data.success) {
                resultDiv.innerHTML = '<div style="background: #d4edda; padding: 15px; border-radius: 5px; color: #155724;"><strong>✅ Thành công:</strong> ' + data.message + '</div>';
            } else {
                resultDiv.innerHTML = '<div style="background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;"><strong>❌ Lỗi:</strong> ' + (data.message || 'Không xác định') + '</div>';
            }
        } catch (e) {
            resultDiv.innerHTML = '<div style="background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;"><strong>❌ Lỗi JSON:</strong> ' + text + '</div>';
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        resultDiv.innerHTML = '<div style="background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;"><strong>❌ Lỗi kết nối:</strong> ' + error.message + '</div>';
    });
}
</script>