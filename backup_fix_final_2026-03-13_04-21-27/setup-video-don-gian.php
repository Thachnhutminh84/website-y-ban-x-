<?php
// Setup đơn giản cho hệ thống video
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';

echo "<h1>🚀 Setup Hệ thống Video Đơn Giản</h1>";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✅ Kết nối database thành công</p>";
    
    // Xóa bảng cũ nếu có
    echo "<h2>Bước 1: Xóa bảng cũ (nếu có)</h2>";
    $pdo->exec("DROP TABLE IF EXISTS videos");
    echo "<p style='color: orange;'>⚠️ Đã xóa bảng videos cũ</p>";
    
    $pdo->exec("DROP TABLE IF EXISTS video_albums");
    echo "<p style='color: orange;'>⚠️ Đã xóa bảng video_albums cũ</p>";
    
    // Tạo bảng video_albums
    echo "<h2>Bước 2: Tạo bảng video_albums</h2>";
    $createAlbums = "
    CREATE TABLE video_albums (
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
    
    $pdo->exec($createAlbums);
    echo "<p style='color: green;'>✅ Đã tạo bảng video_albums</p>";
    
    // Tạo bảng videos
    echo "<h2>Bước 3: Tạo bảng videos</h2>";
    $createVideos = "
    CREATE TABLE videos (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        video_url VARCHAR(500) NOT NULL,
        video_type ENUM('youtube', 'local', 'vimeo') DEFAULT 'local',
        thumbnail_url VARCHAR(500),
        duration VARCHAR(20),
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
    
    $pdo->exec($createVideos);
    echo "<p style='color: green;'>✅ Đã tạo bảng videos</p>";
    
    // Thêm album mẫu
    echo "<h2>Bước 4: Thêm album mẫu</h2>";
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
    
    // Tạo thư mục videos
    echo "<h2>Bước 5: Tạo thư mục videos</h2>";
    if (!is_dir('videos/')) {
        mkdir('videos/', 0777, true);
        chmod('videos/', 0777);
        echo "<p style='color: green;'>✅ Đã tạo thư mục videos/ với quyền 777</p>";
    } else {
        chmod('videos/', 0777);
        echo "<p style='color: green;'>✅ Thư mục videos/ đã tồn tại, đã set quyền 777</p>";
    }
    
    // Kiểm tra quyền ghi
    if (is_writable('videos/')) {
        echo "<p style='color: green;'>✅ Thư mục videos/ có quyền ghi</p>";
    } else {
        echo "<p style='color: red;'>❌ Thư mục videos/ không có quyền ghi</p>";
    }
    
    // Test tạo file
    $testFile = 'videos/test.txt';
    if (file_put_contents($testFile, 'test')) {
        echo "<p style='color: green;'>✅ Test ghi file thành công</p>";
        unlink($testFile);
    } else {
        echo "<p style='color: red;'>❌ Không thể ghi file vào thư mục videos/</p>";
    }
    
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h2>🎉 Setup hoàn tất!</h2>";
    echo "<p><strong>Hệ thống video đã sẵn sàng sử dụng!</strong></p>";
    echo "<ul>";
    echo "<li>✅ Bảng videos: Đã tạo</li>";
    echo "<li>✅ Bảng video_albums: Đã tạo</li>";
    echo "<li>✅ Album mẫu: " . count($albums) . " album</li>";
    echo "<li>✅ Thư mục videos/: Đã tạo với quyền ghi</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h2>🚀 Bước tiếp theo</h2>";
    echo "<div style='display: flex; gap: 10px; flex-wrap: wrap;'>";
    echo "<a href='them-video.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>➕ Thêm video đầu tiên</a>";
    echo "<a href='video.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📺 Xem trang video</a>";
    echo "<a href='video-files.php' style='background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📁 Xem tất cả file</a>";
    echo "<a href='kiem-tra-he-thong-video.php' style='background: #ffc107; color: black; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔍 Kiểm tra hệ thống</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; color: #721c24;'>";
    echo "<h2>❌ Lỗi Setup</h2>";
    echo "<p><strong>Chi tiết lỗi:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Hướng dẫn:</strong></p>";
    echo "<ul>";
    echo "<li>Kiểm tra file config.php có đúng thông tin database không</li>";
    echo "<li>Đảm bảo user database có quyền CREATE TABLE</li>";
    echo "<li>Kiểm tra MySQL service đang chạy</li>";
    echo "</ul>";
    echo "</div>";
}
?>