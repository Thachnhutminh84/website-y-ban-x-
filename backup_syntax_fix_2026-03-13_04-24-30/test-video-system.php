<?php
// Test hệ thống video
header("Content-Type: text/html; charset=utf-8");
session_start();
require_once 'config.php';

echo "<h1>🧪 Test Hệ thống Video</h1>";

// Test 1: Kiểm tra file auth.php
echo "<h2>📋 Test 1: Kiểm tra Auth System</h2>";
try {
    require_once 'auth.php';
    echo "<p style='color: green;'>✅ File auth.php loaded successfully</p>";
    
    // Test các hàm auth
    $isLoggedIn = authIsLoggedIn();
    echo "<p>🔐 authIsLoggedIn(): " . ($isLoggedIn ? 'true' : 'false') . "</p>";
    
    $currentRole = authCurrentRole();
    echo "<p>👤 authCurrentRole(): $currentRole</p>";
    
    $displayName = authDisplayName();
    echo "<p>📝 authDisplayName(): $displayName</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Auth Error: " . $e->getMessage() . "</p>";
}

// Test 2: Kiểm tra database connection
echo "<h2>🗄️ Test 2: Kiểm tra Database</h2>";
try {
    $conn = getDBConnection();
    echo "<p style='color: green;'>✅ Database connection successful</p>";
    
    // Kiểm tra bảng videos
    $result = $conn->query("SHOW TABLES LIKE 'videos'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✅ Bảng 'videos' tồn tại</p>";
        
        // Đếm số video
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM videos");
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['total'];
        echo "<p>📊 Số lượng video: $count</p>";
        
    } else {
        echo "<p style='color: orange;'>⚠️ Bảng 'videos' chưa tồn tại</p>";
    }
    
    // Kiểm tra bảng video_albums
    $result = $conn->query("SHOW TABLES LIKE 'video_albums'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✅ Bảng 'video_albums' tồn tại</p>";
        
        // Đếm số album
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM video_albums");
        $stmt->execute();
        $result = $stmt->get_result();
        $count = $result->fetch_assoc()['total'];
        echo "<p>📁 Số lượng album: $count</p>";
        
    } else {
        echo "<p style='color: orange;'>⚠️ Bảng 'video_albums' chưa tồn tại</p>";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database Error: " . $e->getMessage() . "</p>";
}

// Test 3: Kiểm tra các file cần thiết
echo "<h2>📁 Test 3: Kiểm tra Files</h2>";
$requiredFiles = [
    'video.php' => 'Trang hiển thị video',
    'video-style.css' => 'CSS cho video',
    'video-player.js' => 'JavaScript video player',
    'quan-ly-video.php' => 'Quản lý video',
    'them-video.php' => 'Thêm video',
    'api-update-video-views.php' => 'API cập nhật lượt xem',
    'api-get-video-info.php' => 'API lấy thông tin video',
    'create-videos-table.sql' => 'SQL tạo bảng',
    'setup-video-system.php' => 'Script thiết lập'
];

foreach ($requiredFiles as $file => $description) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ $file - $description</p>";
    } else {
        echo "<p style='color: red;'>❌ $file - $description (THIẾU)</p>";
    }
}

// Test 4: Kiểm tra menu
echo "<h2>🔗 Test 4: Kiểm tra Menu</h2>";
$indexContent = file_get_contents('index.php');
if (strpos($indexContent, 'href="video.php"') !== false) {
    echo "<p style='color: green;'>✅ Menu trang chủ đã có link Video</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Menu trang chủ chưa có link Video</p>";
}

$lanh_dao_content = file_get_contents('lanh-dao.php');
if (strpos($lanh_dao_content, 'href="video.php"') !== false) {
    echo "<p style='color: green;'>✅ Menu trang lãnh đạo đã có link Video</p>";
} else {
    echo "<p style='color: orange;'>⚠️ Menu trang lãnh đạo chưa có link Video</p>";
}

// Test 5: Recommendations
echo "<h2>🎯 Test 5: Khuyến nghị</h2>";
echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 8px;'>";
echo "<h4>📋 Để hoàn thiện hệ thống video:</h4>";
echo "<ol>";
echo "<li><a href='setup-video-system.php'>🚀 Chạy script thiết lập hệ thống video</a></li>";
echo "<li><a href='video.php'>📺 Kiểm tra trang video</a></li>";
echo "<li><a href='quan-ly-video.php'>⚙️ Truy cập quản lý video (cần đăng nhập)</a></li>";
echo "<li><a href='them-video.php'>➕ Thử thêm video mới (cần đăng nhập)</a></li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin-top: 15px;'>";
echo "<h4>⚠️ Lưu ý:</h4>";
echo "<ul>";
echo "<li>Đảm bảo đã đăng nhập với tài khoản admin để truy cập các chức năng quản lý</li>";
echo "<li>Nếu gặp lỗi, hãy chạy setup-video-system.php trước</li>";
echo "<li>Kiểm tra quyền ghi file và database</li>";
echo "</ul>";
echo "</div>";
?>