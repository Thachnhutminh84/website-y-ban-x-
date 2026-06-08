<?php
// Test nhanh hệ thống video
header("Content-Type: text/html; charset=utf-8");
session_start();
require_once 'config.php';

echo "<h1>🧪 Test Nhanh Hệ thống Video</h1>";
echo "<p>Kiểm tra nhanh các thành phần chính của hệ thống video...</p>";

// Test 1: Database connection
echo "<h2>1. 🔌 Kết nối Database</h2>";
try {
    $conn = getDBConnection();
    echo "<p style='color: green;'>✅ Kết nối database thành công</p>";
    
    // Test bảng videos
    $result = $conn->query("SHOW TABLES LIKE 'videos'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✅ Bảng 'videos' tồn tại</p>";
        
        // Đếm video
        $result = $conn->query("SELECT COUNT(*) as total FROM videos WHERE is_active = 1");
        $count = $result->fetch_assoc()['total'];
        echo "<p style='color: blue;'>📊 Có $count video trong database</p>";
    } else {
        echo "<p style='color: red;'>❌ Bảng 'videos' không tồn tại</p>";
    }
    
    // Test bảng video_albums
    $result = $conn->query("SHOW TABLES LIKE 'video_albums'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✅ Bảng 'video_albums' tồn tại</p>";
        
        // Đếm album
        $result = $conn->query("SELECT COUNT(*) as total FROM video_albums WHERE is_active = 1");
        $count = $result->fetch_assoc()['total'];
        echo "<p style='color: blue;'>📁 Có $count album trong database</p>";
    } else {
        echo "<p style='color: red;'>❌ Bảng 'video_albums' không tồn tại</p>";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi kết nối database: " . $e->getMessage() . "</p>";
}

// Test 2: Files
echo "<h2>2. 📁 Kiểm tra Files</h2>";
$files = [
    'video.php' => 'Trang hiển thị video',
    'them-video.php' => 'Form thêm video', 
    'quan-ly-video.php' => 'Quản lý video',
    'upload-video.php' => 'API upload',
    'video-style.css' => 'CSS',
    'video-player.js' => 'JavaScript'
];

foreach ($files as $file => $desc) {
    if (file_exists($file)) {
        $size = filesize($file);
        echo "<p style='color: green;'>✅ $file ($desc) - " . number_format($size) . " bytes</p>";
    } else {
        echo "<p style='color: red;'>❌ $file ($desc) - THIẾU</p>";
    }
}

// Test 3: Permissions
echo "<h2>3. 🔐 Kiểm tra Quyền</h2>";
if (is_writable('.')) {
    echo "<p style='color: green;'>✅ Thư mục gốc có quyền ghi</p>";
} else {
    echo "<p style='color: red;'>❌ Thư mục gốc không có quyền ghi</p>";
}

if (!is_dir('videos/')) {
    mkdir('videos/', 0755, true);
    echo "<p style='color: orange;'>⚠️ Đã tạo thư mục videos/</p>";
}

if (is_writable('videos/')) {
    echo "<p style='color: green;'>✅ Thư mục videos/ có quyền ghi</p>";
} else {
    echo "<p style='color: red;'>❌ Thư mục videos/ không có quyền ghi</p>";
}

// Test 4: Session & Auth
echo "<h2>4. 👤 Kiểm tra Session</h2>";
if (function_exists('authIsLoggedIn')) {
    if (authIsLoggedIn()) {
        echo "<p style='color: green;'>✅ Đã đăng nhập</p>";
        echo "<p style='color: blue;'>👤 User: " . authDisplayName() . "</p>";
        echo "<p style='color: blue;'>🔑 Role: " . authCurrentRole() . "</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Chưa đăng nhập</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Hàm auth không tồn tại</p>";
}

// Test 5: Quick Links
echo "<h2>5. 🔗 Links Test</h2>";
echo "<div style='display: flex; gap: 10px; flex-wrap: wrap;'>";
echo "<a href='video.php' target='_blank' style='background: #007bff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px;'>📺 Trang Video</a>";
echo "<a href='them-video.php' target='_blank' style='background: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px;'>➕ Thêm Video</a>";
echo "<a href='quan-ly-video.php' target='_blank' style='background: #17a2b8; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px;'>⚙️ Quản lý</a>";
echo "<a href='setup-video-system.php' target='_blank' style='background: #ffc107; color: black; padding: 8px 15px; text-decoration: none; border-radius: 5px;'>🚀 Setup</a>";
echo "<a href='fix-video-system.php' target='_blank' style='background: #dc3545; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px;'>🔧 Fix</a>";
echo "</div>";

echo "<h2>6. 💡 Hướng dẫn</h2>";
echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 8px; border-left: 4px solid #007bff;'>";
echo "<p><strong>Nếu gặp lỗi:</strong></p>";
echo "<ol>";
echo "<li>Chạy <a href='setup-video-system.php'>setup-video-system.php</a> để thiết lập database</li>";
echo "<li>Chạy <a href='fix-video-system.php'>fix-video-system.php</a> để khắc phục lỗi</li>";
echo "<li>Kiểm tra file config.php có đúng thông tin database không</li>";
echo "<li>Đảm bảo đã đăng nhập với quyền admin</li>";
echo "</ol>";
echo "</div>";

echo "<p style='margin-top: 20px; text-align: center;'>";
echo "<small>Test completed at " . date('Y-m-d H:i:s') . "</small>";
echo "</p>";
?>