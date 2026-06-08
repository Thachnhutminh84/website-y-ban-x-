<?php
// Script thiết lập hệ thống video hoàn chỉnh
require_once 'config.php';

echo "<h1>🎬 Thiết lập Hệ thống Video</h1>";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>📋 Bước 1: Kiểm tra bảng</h2>";
    
    // Kiểm tra bảng videos
    $stmt = $pdo->query("SHOW TABLES LIKE 'videos'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: orange;'>⚠️ Bảng 'videos' chưa tồn tại. Đang tạo...</p>";
        
        // Tạo bảng videos
        $createSQL = file_get_contents('create-videos-table.sql');
        $pdo->exec($createSQL);
        echo "<p style='color: green;'>✅ Đã tạo bảng 'videos' và 'video_albums'</p>";
    } else {
        echo "<p style='color: green;'>✅ Bảng 'videos' đã tồn tại</p>";
    }
    
    // Kiểm tra bảng video_albums
    $stmt = $pdo->query("SHOW TABLES LIKE 'video_albums'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: orange;'>⚠️ Bảng 'video_albums' chưa tồn tại. Đang tạo...</p>";
        $pdo->exec("
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "<p style='color: green;'>✅ Đã tạo bảng 'video_albums'</p>";
    } else {
        echo "<p style='color: green;'>✅ Bảng 'video_albums' đã tồn tại</p>";
    }
    
    echo "<h2>📁 Bước 2: Kiểm tra album</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM video_albums");
    $albumCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($albumCount == 0) {
        echo "<p style='color: orange;'>⚠️ Chưa có album nào. Đang thêm album mẫu...</p>";
        
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
    } else {
        echo "<p style='color: green;'>✅ Đã có $albumCount album</p>";
    }
    
    echo "<h2>🎬 Bước 3: Kiểm tra video</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM videos WHERE is_active = 1");
    $videoCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($videoCount == 0) {
        echo "<p style='color: orange;'>⚠️ Chưa có video nào. Đang thêm video mẫu...</p>";
        
        // Thêm video mẫu
        $sampleSQL = file_get_contents('insert-sample-videos.sql');
        $pdo->exec($sampleSQL);
        
        // Đếm lại
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM videos WHERE is_active = 1");
        $newVideoCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        echo "<p style='color: green;'>✅ Đã thêm $newVideoCount video mẫu</p>";
    } else {
        echo "<p style='color: green;'>✅ Đã có $videoCount video</p>";
    }
    
    echo "<h2>🔗 Bước 4: Kiểm tra menu</h2>";
    $indexContent = file_get_contents('index.php');
    if (strpos($indexContent, 'href="video.php"') !== false) {
        echo "<p style='color: green;'>✅ Menu đã có link Video</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Menu chưa có link Video</p>";
    }
    
    echo "<h2>📊 Bước 5: Thống kê</h2>";
    
    // Thống kê album
    $stmt = $pdo->query("SELECT name, (SELECT COUNT(*) FROM videos WHERE album_id = va.id AND is_active = 1) as video_count FROM video_albums va WHERE is_active = 1 ORDER BY display_order");
    $albums = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>📁 Album và số lượng video:</h4>";
    echo "<ul>";
    foreach ($albums as $album) {
        echo "<li><strong>{$album['name']}</strong>: {$album['video_count']} video</li>";
    }
    echo "</ul>";
    
    // Thống kê video nổi bật
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM videos WHERE is_featured = 1 AND is_active = 1");
    $featuredCount = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "<p><strong>⭐ Video nổi bật:</strong> $featuredCount video</p>";
    
    // Thống kê lượt xem
    $stmt = $pdo->query("SELECT SUM(views) as total_views FROM videos WHERE is_active = 1");
    $totalViews = $stmt->fetch(PDO::FETCH_ASSOC)['total_views'] ?? 0;
    echo "<p><strong>👁 Tổng lượt xem:</strong> " . number_format($totalViews) . " lượt</p>";
    
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>🎉 Thiết lập hoàn tất!</h3>";
    echo "<p><strong>Hệ thống video đã sẵn sàng sử dụng:</strong></p>";
    echo "<ul>";
    echo "<li>✅ Database: Bảng videos và video_albums</li>";
    echo "<li>✅ Album: " . count($albums) . " album phân loại</li>";
    echo "<li>✅ Video: $videoCount video mẫu</li>";
    echo "<li>✅ Giao diện: Trang video và quản lý</li>";
    echo "<li>✅ API: Cập nhật lượt xem và thông tin</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<h3>🚀 Bước tiếp theo:</h3>";
    echo "<div style='display: flex; gap: 15px; flex-wrap: wrap;'>";
    echo "<a href='video.php' target='_blank' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📺 Xem trang Video</a>";
    echo "<a href='quan-ly-video.php' target='_blank' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>⚙️ Quản lý Video</a>";
    echo "<a href='them-video.php' target='_blank' style='background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>➕ Thêm Video</a>";
    echo "<a href='index.php' target='_blank' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 Trang chủ</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; color: #721c24;'>";
    echo "<h3>❌ Lỗi thiết lập</h3>";
    echo "<p><strong>Chi tiết lỗi:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Hướng dẫn khắc phục:</strong></p>";
    echo "<ul>";
    echo "<li>Kiểm tra kết nối database trong file config.php</li>";
    echo "<li>Đảm bảo user database có quyền CREATE TABLE</li>";
    echo "<li>Kiểm tra các file SQL có tồn tại không</li>";
    echo "</ul>";
    echo "</div>";
}
?>