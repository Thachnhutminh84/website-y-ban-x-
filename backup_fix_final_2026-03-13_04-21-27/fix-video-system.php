<?php
// Script khắc phục lỗi hệ thống video
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';

echo "<h1>🔧 Khắc phục Lỗi Hệ thống Video</h1>";
echo "<p>Đang kiểm tra và sửa lỗi hệ thống video...</p>";

$errors = [];
$fixes = [];

try {
    // Kiểm tra kết nối database
    echo "<h2>📋 Bước 1: Kiểm tra Database</h2>";
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Kết nối database thành công</p>";
    
    // Kiểm tra bảng videos
    $stmt = $pdo->query("SHOW TABLES LIKE 'videos'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: red;'>❌ Bảng 'videos' không tồn tại</p>";
        $errors[] = "Bảng videos chưa được tạo";
        
        // Tạo bảng videos
        echo "<p style='color: orange;'>🔧 Đang tạo bảng videos...</p>";
        $createSQL = "
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
        
        $pdo->exec($createSQL);
        echo "<p style='color: green;'>✅ Đã tạo bảng videos</p>";
        $fixes[] = "Tạo bảng videos";
    } else {
        echo "<p style='color: green;'>✅ Bảng 'videos' đã tồn tại</p>";
    }
    
    // Kiểm tra bảng video_albums
    $stmt = $pdo->query("SHOW TABLES LIKE 'video_albums'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: red;'>❌ Bảng 'video_albums' không tồn tại</p>";
        $errors[] = "Bảng video_albums chưa được tạo";
        
        // Tạo bảng video_albums
        echo "<p style='color: orange;'>🔧 Đang tạo bảng video_albums...</p>";
        $createAlbumSQL = "
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
        
        $pdo->exec($createAlbumSQL);
        echo "<p style='color: green;'>✅ Đã tạo bảng video_albums</p>";
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
        echo "<p style='color: green;'>✅ Đã thêm " . count($albums) . " album mẫu</p>";
        $fixes[] = "Thêm album mẫu";
    } else {
        echo "<p style='color: green;'>✅ Bảng 'video_albums' đã tồn tại</p>";
    }
    
    echo "<h2>📁 Bước 2: Kiểm tra Files</h2>";
    
    // Kiểm tra các file quan trọng
    $requiredFiles = [
        'video.php' => 'Trang hiển thị video',
        'them-video.php' => 'Form thêm video',
        'quan-ly-video.php' => 'Quản lý video',
        'upload-video.php' => 'API upload video',
        'video-style.css' => 'CSS cho video',
        'video-player.js' => 'JavaScript video player'
    ];
    
    foreach ($requiredFiles as $file => $description) {
        if (file_exists($file)) {
            echo "<p style='color: green;'>✅ $file - $description</p>";
        } else {
            echo "<p style='color: red;'>❌ $file - $description (THIẾU)</p>";
            $errors[] = "File $file không tồn tại";
        }
    }
    
    // Kiểm tra thư mục videos
    if (!is_dir('videos/')) {
        echo "<p style='color: orange;'>⚠️ Thư mục 'videos/' chưa tồn tại. Đang tạo...</p>";
        mkdir('videos/', 0755, true);
        echo "<p style='color: green;'>✅ Đã tạo thư mục videos/</p>";
        $fixes[] = "Tạo thư mục videos";
    } else {
        echo "<p style='color: green;'>✅ Thư mục videos/ đã tồn tại</p>";
    }
    
    echo "<h2>🔗 Bước 3: Kiểm tra Menu</h2>";
    
    // Kiểm tra menu trong index.php
    if (file_exists('index.php')) {
        $indexContent = file_get_contents('index.php');
        if (strpos($indexContent, 'href="video.php"') !== false) {
            echo "<p style='color: green;'>✅ Menu trang chủ đã có link Video</p>";
        } else {
            echo "<p style='color: red;'>❌ Menu trang chủ chưa có link Video</p>";
            $errors[] = "Menu chưa có link video";
        }
    }
    
    echo "<h2>🧪 Bước 4: Test Chức năng</h2>";
    
    // Test kết nối và truy vấn
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM videos WHERE is_active = 1");
        $videoCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "<p style='color: green;'>✅ Truy vấn database thành công - Có $videoCount video</p>";
        
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM video_albums WHERE is_active = 1");
        $albumCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "<p style='color: green;'>✅ Có $albumCount album video</p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Lỗi truy vấn: " . $e->getMessage() . "</p>";
        $errors[] = "Lỗi truy vấn database: " . $e->getMessage();
    }
    
    // Tổng kết
    echo "<h2>📊 Tổng kết</h2>";
    
    if (empty($errors)) {
        echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; color: #155724;'>";
        echo "<h3>🎉 Hệ thống Video hoạt động bình thường!</h3>";
        echo "<p>Không phát hiện lỗi nào. Hệ thống video đã sẵn sàng sử dụng.</p>";
        if (!empty($fixes)) {
            echo "<p><strong>Đã khắc phục:</strong></p>";
            echo "<ul>";
            foreach ($fixes as $fix) {
                echo "<li>✅ $fix</li>";
            }
            echo "</ul>";
        }
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; color: #721c24;'>";
        echo "<h3>⚠️ Phát hiện " . count($errors) . " lỗi</h3>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li>❌ $error</li>";
        }
        echo "</ul>";
        
        if (!empty($fixes)) {
            echo "<p><strong>Đã khắc phục:</strong></p>";
            echo "<ul>";
            foreach ($fixes as $fix) {
                echo "<li>✅ $fix</li>";
            }
            echo "</ul>";
        }
        echo "</div>";
    }
    
    echo "<h3>🚀 Bước tiếp theo:</h3>";
    echo "<div style='display: flex; gap: 15px; flex-wrap: wrap; margin: 20px 0;'>";
    echo "<a href='video.php' target='_blank' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📺 Kiểm tra trang Video</a>";
    echo "<a href='them-video.php' target='_blank' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>➕ Thêm Video</a>";
    echo "<a href='quan-ly-video.php' target='_blank' style='background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>⚙️ Quản lý Video</a>";
    echo "<a href='index.php' target='_blank' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Trang chủ</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; color: #721c24;'>";
    echo "<h3>❌ Lỗi nghiêm trọng</h3>";
    echo "<p><strong>Chi tiết lỗi:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Hướng dẫn khắc phục:</strong></p>";
    echo "<ul>";
    echo "<li>Kiểm tra kết nối database trong file config.php</li>";
    echo "<li>Đảm bảo user database có quyền CREATE TABLE</li>";
    echo "<li>Kiểm tra các file PHP có lỗi syntax không</li>";
    echo "<li>Kiểm tra quyền ghi file trên server</li>";
    echo "</ul>";
    echo "</div>";
}
?>