<?php
// Script khắc phục toàn bộ lỗi hệ thống video
header("Content-Type: text/html; charset=utf-8");
session_start();
require_once 'config.php';

echo "<!DOCTYPE html>";
echo "<html lang='vi'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Khắc phục Lỗi Video - UBND Xã Long Hiệp</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }";
echo ".container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }";
echo ".error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }";
echo ".warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }";
echo ".info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }";
echo ".btn { display: inline-block; padding: 10px 20px; margin: 5px; text-decoration: none; border-radius: 5px; color: white; }";
echo ".btn-primary { background: #007bff; } .btn-success { background: #28a745; } .btn-warning { background: #ffc107; color: black; }";
echo ".step { margin: 20px 0; padding: 15px; border-left: 4px solid #007bff; background: #f8f9fa; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>🔧 Khắc phục Lỗi Hệ thống Video</h1>";
echo "<p>Script này sẽ tự động khắc phục tất cả các lỗi có thể xảy ra với hệ thống video.</p>";

$totalSteps = 6;
$currentStep = 0;
$errors = [];
$fixes = [];

// Bước 1: Kiểm tra và tạo database
$currentStep++;
echo "<div class='step'>";
echo "<h2>Bước $currentStep/$totalSteps: 🗄️ Kiểm tra Database</h2>";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<div class='success'>✅ Kết nối database thành công</div>";
    
    // Kiểm tra và tạo bảng videos
    $stmt = $pdo->query("SHOW TABLES LIKE 'videos'");
    if ($stmt->rowCount() == 0) {
        echo "<div class='warning'>⚠️ Bảng 'videos' chưa tồn tại. Đang tạo...</div>";
        
        $createVideosSQL = "
        CREATE TABLE IF NOT EXISTS videos (
            id INT PRIMARY KEY AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            video_url VARCHAR(500) NOT NULL,
            video_type ENUM('youtube', 'local', 'vimeo') DEFAULT 'youtube',
            thumbnail_url VARCHAR(500),
            duration VARCHAR(20),
            category_id INT,
            album_id INT,
            views INT DEFAULT 0,
            is_featured TINYINT(1) DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            display_order INT DEFAULT 0,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_album (album_id),
            INDEX idx_active (is_active),
            INDEX idx_featured (is_featured)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($createVideosSQL);
        echo "<div class='success'>✅ Đã tạo bảng 'videos'</div>";
        $fixes[] = "Tạo bảng videos";
    } else {
        echo "<div class='success'>✅ Bảng 'videos' đã tồn tại</div>";
    }
    
    // Kiểm tra và tạo bảng video_albums
    $stmt = $pdo->query("SHOW TABLES LIKE 'video_albums'");
    if ($stmt->rowCount() == 0) {
        echo "<div class='warning'>⚠️ Bảng 'video_albums' chưa tồn tại. Đang tạo...</div>";
        
        $createAlbumsSQL = "
        CREATE TABLE IF NOT EXISTS video_albums (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            thumbnail_url VARCHAR(500),
            video_count INT DEFAULT 0,
            is_active TINYINT(1) DEFAULT 1,
            display_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($createAlbumsSQL);
        echo "<div class='success'>✅ Đã tạo bảng 'video_albums'</div>";
        $fixes[] = "Tạo bảng video_albums";
        
        // Thêm album mẫu
        $albums = [
            ['Hoạt động UBND', 'Video về các hoạt động của UBND xã Long Hiệp', 1],
            ['Sự kiện văn hóa', 'Video về các sự kiện văn hóa, lễ hội tại xã', 2],
            ['Phát triển kinh tế', 'Video về các hoạt động phát triển kinh tế địa phương', 3],
            ['Giáo dục - Đào tạo', 'Video về hoạt động giáo dục và đào tạo', 4],
            ['An ninh - Trật tự', 'Video về công tác đảm bảo an ninh trật tự', 5]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO video_albums (name, description, display_order, is_active) VALUES (?, ?, ?, 1)");
        foreach ($albums as $album) {
            $stmt->execute($album);
        }
        echo "<div class='success'>✅ Đã thêm " . count($albums) . " album mẫu</div>";
        $fixes[] = "Thêm album mẫu";
    } else {
        echo "<div class='success'>✅ Bảng 'video_albums' đã tồn tại</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Lỗi database: " . $e->getMessage() . "</div>";
    $errors[] = "Database: " . $e->getMessage();
}
echo "</div>";

// Bước 2: Kiểm tra files
$currentStep++;
echo "<div class='step'>";
echo "<h2>Bước $currentStep/$totalSteps: 📁 Kiểm tra Files</h2>";

$requiredFiles = [
    'video.php' => 'Trang hiển thị video',
    'them-video.php' => 'Form thêm video',
    'quan-ly-video.php' => 'Quản lý video',
    'upload-video.php' => 'API upload video',
    'api-update-video-views.php' => 'API cập nhật lượt xem',
    'api-get-video-info.php' => 'API lấy thông tin video',
    'video-style.css' => 'CSS cho video',
    'video-player.js' => 'JavaScript video player'
];

$missingFiles = [];
foreach ($requiredFiles as $file => $description) {
    if (file_exists($file)) {
        $size = number_format(filesize($file));
        echo "<div class='success'>✅ $file - $description ($size bytes)</div>";
    } else {
        echo "<div class='error'>❌ $file - $description (THIẾU)</div>";
        $missingFiles[] = $file;
        $errors[] = "File thiếu: $file";
    }
}

if (empty($missingFiles)) {
    echo "<div class='success'>🎉 Tất cả files cần thiết đều có sẵn!</div>";
} else {
    echo "<div class='warning'>⚠️ Thiếu " . count($missingFiles) . " files quan trọng</div>";
}
echo "</div>";

// Bước 3: Kiểm tra thư mục và quyền
$currentStep++;
echo "<div class='step'>";
echo "<h2>Bước $currentStep/$totalSteps: 🔐 Kiểm tra Quyền</h2>";

// Kiểm tra thư mục videos
if (!is_dir('videos/')) {
    mkdir('videos/', 0755, true);
    echo "<div class='success'>✅ Đã tạo thư mục videos/</div>";
    $fixes[] = "Tạo thư mục videos";
} else {
    echo "<div class='success'>✅ Thư mục videos/ đã tồn tại</div>";
}

// Kiểm tra quyền ghi
if (is_writable('videos/')) {
    echo "<div class='success'>✅ Thư mục videos/ có quyền ghi</div>";
} else {
    echo "<div class='error'>❌ Thư mục videos/ không có quyền ghi</div>";
    $errors[] = "Không có quyền ghi thư mục videos";
}

if (is_writable('.')) {
    echo "<div class='success'>✅ Thư mục gốc có quyền ghi</div>";
} else {
    echo "<div class='warning'>⚠️ Thư mục gốc không có quyền ghi</div>";
}
echo "</div>";

// Bước 4: Kiểm tra session và auth
$currentStep++;
echo "<div class='step'>";
echo "<h2>Bước $currentStep/$totalSteps: 👤 Kiểm tra Authentication</h2>";

if (function_exists('authIsLoggedIn')) {
    echo "<div class='success'>✅ Hệ thống auth có sẵn</div>";
    
    if (authIsLoggedIn()) {
        echo "<div class='success'>✅ Đã đăng nhập</div>";
        echo "<div class='info'>👤 User: " . authDisplayName() . "</div>";
        echo "<div class='info'>🔑 Role: " . authCurrentRole() . "</div>";
        
        if (authHasPermission('manage_content')) {
            echo "<div class='success'>✅ Có quyền quản lý nội dung</div>";
        } else {
            echo "<div class='warning'>⚠️ Không có quyền quản lý nội dung</div>";
        }
    } else {
        echo "<div class='warning'>⚠️ Chưa đăng nhập</div>";
        echo "<div class='info'>💡 Cần đăng nhập để sử dụng đầy đủ chức năng</div>";
    }
} else {
    echo "<div class='error'>❌ Hệ thống auth không có sẵn</div>";
    $errors[] = "Hệ thống authentication không hoạt động";
}
echo "</div>";

// Bước 5: Test kết nối và truy vấn
$currentStep++;
echo "<div class='step'>";
echo "<h2>Bước $currentStep/$totalSteps: 🧪 Test Chức năng</h2>";

try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM videos WHERE is_active = 1");
    $videoCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "<div class='success'>✅ Có $videoCount video trong hệ thống</div>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM video_albums WHERE is_active = 1");
    $albumCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "<div class='success'>✅ Có $albumCount album video</div>";
    
    // Test một truy vấn phức tạp
    $stmt = $pdo->query("SELECT v.title, va.name as album_name FROM videos v LEFT JOIN video_albums va ON v.album_id = va.id WHERE v.is_active = 1 LIMIT 3");
    $testVideos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($testVideos)) {
        echo "<div class='success'>✅ Truy vấn JOIN hoạt động bình thường</div>";
        echo "<div class='info'>📋 Video mẫu:</div>";
        foreach ($testVideos as $video) {
            echo "<div class='info'>• " . htmlspecialchars($video['title']) . " (" . ($video['album_name'] ?? 'Chưa phân loại') . ")</div>";
        }
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Lỗi test chức năng: " . $e->getMessage() . "</div>";
    $errors[] = "Test chức năng thất bại: " . $e->getMessage();
}
echo "</div>";

// Bước 6: Tổng kết và hướng dẫn
$currentStep++;
echo "<div class='step'>";
echo "<h2>Bước $currentStep/$totalSteps: 📊 Tổng kết</h2>";

if (empty($errors)) {
    echo "<div class='success'>";
    echo "<h3>🎉 Hệ thống Video hoạt động hoàn hảo!</h3>";
    echo "<p>Không phát hiện lỗi nào. Hệ thống video đã sẵn sàng sử dụng.</p>";
    
    if (!empty($fixes)) {
        echo "<p><strong>Đã tự động khắc phục:</strong></p>";
        echo "<ul>";
        foreach ($fixes as $fix) {
            echo "<li>✅ $fix</li>";
        }
        echo "</ul>";
    }
    echo "</div>";
    
    echo "<h3>🚀 Sử dụng hệ thống:</h3>";
    echo "<div style='display: flex; gap: 10px; flex-wrap: wrap; margin: 20px 0;'>";
    echo "<a href='video.php' target='_blank' class='btn btn-primary'>📺 Xem trang Video</a>";
    echo "<a href='them-video.php' target='_blank' class='btn btn-success'>➕ Thêm Video mới</a>";
    echo "<a href='quan-ly-video.php' target='_blank' class='btn btn-primary'>⚙️ Quản lý Video</a>";
    echo "<a href='index.php' target='_blank' class='btn btn-primary'>🏠 Trang chủ</a>";
    echo "</div>";
    
} else {
    echo "<div class='error'>";
    echo "<h3>⚠️ Phát hiện " . count($errors) . " vấn đề</h3>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>❌ $error</li>";
    }
    echo "</ul>";
    echo "</div>";
    
    if (!empty($fixes)) {
        echo "<div class='warning'>";
        echo "<p><strong>Đã tự động khắc phục:</strong></p>";
        echo "<ul>";
        foreach ($fixes as $fix) {
            echo "<li>✅ $fix</li>";
        }
        echo "</ul>";
        echo "</div>";
    }
    
    echo "<h3>🔧 Hướng dẫn khắc phục:</h3>";
    echo "<ol>";
    echo "<li>Kiểm tra file config.php có đúng thông tin database không</li>";
    echo "<li>Đảm bảo user database có quyền CREATE TABLE và INSERT</li>";
    echo "<li>Kiểm tra quyền ghi file trên server (chmod 755 hoặc 777)</li>";
    echo "<li>Đăng nhập với tài khoản admin để sử dụng đầy đủ chức năng</li>";
    echo "<li>Liên hệ quản trị viên nếu vẫn gặp lỗi</li>";
    echo "</ol>";
}

echo "<div style='margin-top: 30px; padding: 15px; background: #e7f3ff; border-radius: 8px;'>";
echo "<h4>💡 Lưu ý quan trọng:</h4>";
echo "<ul>";
echo "<li>Hệ thống video hỗ trợ YouTube, Vimeo và upload file local</li>";
echo "<li>File upload tối đa 100MB, hỗ trợ MP4, WebM, OGG, AVI, MOV, WMV, FLV, MKV</li>";
echo "<li>Cần đăng nhập với quyền admin để thêm/sửa/xóa video</li>";
echo "<li>Menu 'Video' đã được tự động thêm vào trang chủ</li>";
echo "</ul>";
echo "</div>";

echo "</div>";

echo "<p style='text-align: center; margin-top: 30px; color: #666;'>";
echo "<small>Khắc phục hoàn tất lúc " . date('d/m/Y H:i:s') . "</small>";
echo "</p>";

echo "</div>";
echo "</body>";
echo "</html>";
?>