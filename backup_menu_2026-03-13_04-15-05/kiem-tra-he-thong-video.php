<?php
header("Content-Type: text/html; charset=utf-8");
session_start();
require_once 'config.php';
require_once 'auth.php';

echo "<!DOCTYPE html>";
echo "<html lang='vi'><head><meta charset='UTF-8'><title>Kiểm tra Hệ thống Video</title>";
echo "<style>body{font-family:Arial;margin:20px;} .ok{color:green;} .error{color:red;} .warning{color:orange;} .section{background:#f5f5f5;padding:15px;margin:10px 0;border-radius:5px;}</style>";
echo "</head><body>";

echo "<h1>🔍 Kiểm tra Hệ thống Video</h1>";

$errors = [];
$warnings = [];
$success = [];

// 1. Kiểm tra đăng nhập
echo "<div class='section'><h2>1. 👤 Kiểm tra Đăng nhập</h2>";
if (authIsLoggedIn()) {
    echo "<p class='ok'>✅ Đã đăng nhập: " . authDisplayName() . "</p>";
    echo "<p class='ok'>✅ Role: " . authCurrentRole() . "</p>";
    if (authHasPermission('manage_content')) {
        echo "<p class='ok'>✅ Có quyền quản lý nội dung</p>";
        $success[] = "Đăng nhập OK";
    } else {
        echo "<p class='error'>❌ Không có quyền quản lý nội dung</p>";
        $errors[] = "Không có quyền";
    }
} else {
    echo "<p class='warning'>⚠️ Chưa đăng nhập</p>";
    $warnings[] = "Chưa đăng nhập";
}
echo "</div>";

// 2. Kiểm tra database
echo "<div class='section'><h2>2. 🗄️ Kiểm tra Database</h2>";
try {
    $conn = getDBConnection();
    echo "<p class='ok'>✅ Kết nối database thành công</p>";
    
    // Kiểm tra bảng videos
    $result = $conn->query("SHOW TABLES LIKE 'videos'");
    if ($result && $result->num_rows > 0) {
        echo "<p class='ok'>✅ Bảng 'videos' tồn tại</p>";
        
        // Đếm video
        $result = $conn->query("SELECT COUNT(*) as total FROM videos");
        $count = $result->fetch_assoc()['total'];
        echo "<p class='ok'>✅ Có $count video trong database</p>";
        $success[] = "Database OK";
    } else {
        echo "<p class='error'>❌ Bảng 'videos' không tồn tại</p>";
        echo "<p class='warning'>👉 Chạy: <a href='setup-video-system.php'>setup-video-system.php</a></p>";
        $errors[] = "Bảng videos không tồn tại";
    }
    
    // Kiểm tra bảng video_albums
    $result = $conn->query("SHOW TABLES LIKE 'video_albums'");
    if ($result && $result->num_rows > 0) {
        echo "<p class='ok'>✅ Bảng 'video_albums' tồn tại</p>";
        
        $result = $conn->query("SELECT COUNT(*) as total FROM video_albums");
        $count = $result->fetch_assoc()['total'];
        echo "<p class='ok'>✅ Có $count album</p>";
    } else {
        echo "<p class='error'>❌ Bảng 'video_albums' không tồn tại</p>";
        $errors[] = "Bảng video_albums không tồn tại";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "<p class='error'>❌ Lỗi database: " . $e->getMessage() . "</p>";
    $errors[] = "Lỗi database: " . $e->getMessage();
}
echo "</div>";

// 3. Kiểm tra thư mục
echo "<div class='section'><h2>3. 📁 Kiểm tra Thư mục</h2>";
if (!is_dir('videos/')) {
    mkdir('videos/', 0755, true);
    echo "<p class='warning'>⚠️ Đã tạo thư mục videos/</p>";
    $warnings[] = "Đã tạo thư mục videos";
} else {
    echo "<p class='ok'>✅ Thư mục videos/ tồn tại</p>";
}

if (is_writable('videos/')) {
    echo "<p class='ok'>✅ Thư mục videos/ có quyền ghi</p>";
    $success[] = "Thư mục OK";
} else {
    echo "<p class='error'>❌ Thư mục videos/ không có quyền ghi</p>";
    $errors[] = "Không có quyền ghi thư mục videos";
}

// Liệt kê file
$files = glob('videos/*');
echo "<p>📊 Có " . count($files) . " file trong thư mục videos/</p>";
if (!empty($files)) {
    echo "<ul>";
    foreach (array_slice($files, 0, 5) as $file) {
        echo "<li>" . basename($file) . " - " . number_format(filesize($file)) . " bytes</li>";
    }
    if (count($files) > 5) {
        echo "<li>... và " . (count($files) - 5) . " file khác</li>";
    }
    echo "</ul>";
}
echo "</div>";

// 4. Kiểm tra file PHP
echo "<div class='section'><h2>4. 📄 Kiểm tra File PHP</h2>";
$requiredFiles = [
    'them-video.php' => 'Form thêm video',
    'video.php' => 'Trang hiển thị video',
    'quan-ly-video.php' => 'Quản lý video',
    'video-files.php' => 'Tất cả file video',
    'upload-video.php' => 'API upload',
    'video-style.css' => 'CSS',
    'video-player.js' => 'JavaScript'
];

foreach ($requiredFiles as $file => $desc) {
    if (file_exists($file)) {
        echo "<p class='ok'>✅ $file - $desc</p>";
    } else {
        echo "<p class='error'>❌ $file - $desc (THIẾU)</p>";
        $errors[] = "File $file không tồn tại";
    }
}
echo "</div>";

// 5. Test upload
echo "<div class='section'><h2>5. 📤 Test Upload</h2>";
$uploadMaxSize = ini_get('upload_max_filesize');
$postMaxSize = ini_get('post_max_size');
echo "<p>📊 upload_max_filesize: <strong>$uploadMaxSize</strong></p>";
echo "<p>📊 post_max_size: <strong>$postMaxSize</strong></p>";

if (function_exists('move_uploaded_file')) {
    echo "<p class='ok'>✅ Hàm move_uploaded_file() có sẵn</p>";
    $success[] = "Upload function OK";
} else {
    echo "<p class='error'>❌ Hàm move_uploaded_file() không có</p>";
    $errors[] = "Không có hàm upload";
}
echo "</div>";

// Tổng kết
echo "<div class='section'><h2>📊 Tổng kết</h2>";
echo "<p><strong>✅ Thành công:</strong> " . count($success) . "</p>";
echo "<p><strong>⚠️ Cảnh báo:</strong> " . count($warnings) . "</p>";
echo "<p><strong>❌ Lỗi:</strong> " . count($errors) . "</p>";

if (empty($errors)) {
    echo "<div style='background:#d4edda;padding:20px;border-radius:10px;color:#155724;margin:20px 0;'>";
    echo "<h3>🎉 Hệ thống sẵn sàng!</h3>";
    echo "<p>Không có lỗi nghiêm trọng. Bạn có thể bắt đầu sử dụng.</p>";
    echo "</div>";
} else {
    echo "<div style='background:#f8d7da;padding:20px;border-radius:10px;color:#721c24;margin:20px 0;'>";
    echo "<h3>⚠️ Có " . count($errors) . " lỗi cần khắc phục</h3>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
    echo "</div>";
}

if (!empty($warnings)) {
    echo "<div style='background:#fff3cd;padding:20px;border-radius:10px;color:#856404;margin:20px 0;'>";
    echo "<h3>💡 Lưu ý</h3>";
    echo "<ul>";
    foreach ($warnings as $warning) {
        echo "<li>$warning</li>";
    }
    echo "</ul>";
    echo "</div>";
}
echo "</div>";

// Hướng dẫn
echo "<div class='section'><h2>🚀 Bước tiếp theo</h2>";
if (!empty($errors)) {
    echo "<ol>";
    echo "<li><a href='setup-video-system.php'>Chạy Setup Database</a></li>";
    echo "<li><a href='khac-phuc-loi-video.php'>Chạy Khắc phục Lỗi</a></li>";
    echo "<li>Quay lại trang này để kiểm tra lại</li>";
    echo "</ol>";
} else {
    echo "<ol>";
    echo "<li><a href='them-video.php'>Thêm video mới</a></li>";
    echo "<li><a href='video-files.php'>Xem tất cả file video</a></li>";
    echo "<li><a href='video.php'>Xem trang video</a></li>";
    echo "<li><a href='quan-ly-video.php'>Quản lý video</a></li>";
    echo "</ol>";
}
echo "</div>";

echo "<p style='text-align:center;color:#666;margin-top:30px;'>";
echo "<small>Kiểm tra lúc " . date('d/m/Y H:i:s') . "</small>";
echo "</p>";

echo "</body></html>";
?>