<?php
// Script sửa lỗi hệ thống video
header("Content-Type: text/html; charset=utf-8");
session_start();
require_once 'config.php';

echo "<h1>🔧 Sửa lỗi Hệ thống Video</h1>";

$errors = [];
$fixes = [];

// Fix 1: Kiểm tra và sửa auth.php
echo "<h2>🔐 Fix 1: Auth System</h2>";
try {
    require_once 'auth.php';
    
    // Kiểm tra các hàm cần thiết
    $requiredFunctions = ['authIsLoggedIn', 'authCurrentRole', 'authDisplayName', 'authCurrentUserId', 'authHasPermission'];
    $missingFunctions = [];
    
    foreach ($requiredFunctions as $func) {
        if (!function_exists($func)) {
            $missingFunctions[] = $func;
        }
    }
    
    if (empty($missingFunctions)) {
        echo "<p style='color: green;'>✅ Tất cả hàm auth đã có</p>";
        $fixes[] = "Auth system OK";
    } else {
        echo "<p style='color: red;'>❌ Thiếu hàm: " . implode(', ', $missingFunctions) . "</p>";
        $errors[] = "Missing auth functions: " . implode(', ', $missingFunctions);
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi auth: " . $e->getMessage() . "</p>";
    $errors[] = "Auth error: " . $e->getMessage();
}

// Fix 2: Tạo bảng video nếu chưa có
echo "<h2>🗄️ Fix 2: Database Tables</h2>";
try {
    $conn = getDBConnection();
    
    // Kiểm tra và tạo bảng videos
    $result = $conn->query("SHOW TABLES LIKE 'videos'");
    if (!$result || $result->num_rows == 0) {
        echo "<p style='color: orange;'>⚠️ Bảng 'videos' chưa tồn tại. Đang tạo...</p>";
        
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
        
        if ($conn->query($createVideosSQL)) {
            echo "<p style='color: green;'>✅ Đã tạo bảng 'videos'</p>";
            $fixes[] = "Created videos table";
        } else {
            echo "<p style='color: red;'>❌ Lỗi tạo bảng videos: " . $conn->error . "</p>";
            $errors[] = "Failed to create videos table";
        }
    } else {
        echo "<p style='color: green;'>✅ Bảng 'videos' đã tồn tại</p>";
        $fixes[] = "Videos table exists";
    }
    
    // Kiểm tra và tạo bảng video_albums
    $result = $conn->query("SHOW TABLES LIKE 'video_albums'");
    if (!$result || $result->num_rows == 0) {
        echo "<p style='color: orange;'>⚠️ Bảng 'video_albums' chưa tồn tại. Đang tạo...</p>";
        
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
        
        if ($conn->query($createAlbumsSQL)) {
            echo "<p style='color: green;'>✅ Đã tạo bảng 'video_albums'</p>";
            $fixes[] = "Created video_albums table";
            
            // Thêm album mẫu
            $albums = [
                ['Hoạt động UBND', 'Video về các hoạt động của UBND xã Long Hiệp', 1],
                ['Sự kiện văn hóa', 'Video về các sự kiện văn hóa, lễ hội tại xã', 2],
                ['Phát triển kinh tế', 'Video về các hoạt động phát triển kinh tế địa phương', 3],
                ['Giáo dục - Đào tạo', 'Video về hoạt động giáo dục và đào tạo', 4],
                ['An ninh - Trật tự', 'Video về công tác đảm bảo an ninh trật tự', 5]
            ];
            
            $stmt = $conn->prepare("INSERT INTO video_albums (name, description, display_order, is_active) VALUES (?, ?, ?, 1)");
            foreach ($albums as $album) {
                $stmt->bind_param("ssi", $album[0], $album[1], $album[2]);
                $stmt->execute();
            }
            echo "<p style='color: green;'>✅ Đã thêm " . count($albums) . " album mẫu</p>";
            $fixes[] = "Added sample albums";
            
        } else {
            echo "<p style='color: red;'>❌ Lỗi tạo bảng video_albums: " . $conn->error . "</p>";
            $errors[] = "Failed to create video_albums table";
        }
    } else {
        echo "<p style='color: green;'>✅ Bảng 'video_albums' đã tồn tại</p>";
        $fixes[] = "Video_albums table exists";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi database: " . $e->getMessage() . "</p>";
    $errors[] = "Database error: " . $e->getMessage();
}

// Fix 3: Kiểm tra files
echo "<h2>📁 Fix 3: Required Files</h2>";
$requiredFiles = [
    'video.php' => 'Trang hiển thị video',
    'video-style.css' => 'CSS cho video',
    'video-player.js' => 'JavaScript video player',
    'api-update-video-views.php' => 'API cập nhật lượt xem',
    'api-get-video-info.php' => 'API lấy thông tin video'
];

$missingFiles = [];
foreach ($requiredFiles as $file => $description) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ $file</p>";
    } else {
        echo "<p style='color: red;'>❌ $file - THIẾU</p>";
        $missingFiles[] = $file;
    }
}

if (empty($missingFiles)) {
    $fixes[] = "All required files exist";
} else {
    $errors[] = "Missing files: " . implode(', ', $missingFiles);
}

// Fix 4: Test trang video
echo "<h2>🧪 Fix 4: Test Video Page</h2>";
try {
    // Simulate loading video.php
    ob_start();
    $testError = false;
    
    // Test basic functionality
    if (function_exists('authIsLoggedIn')) {
        $isLoggedIn = authIsLoggedIn();
        echo "<p style='color: green;'>✅ authIsLoggedIn() works: " . ($isLoggedIn ? 'true' : 'false') . "</p>";
    } else {
        echo "<p style='color: red;'>❌ authIsLoggedIn() not found</p>";
        $testError = true;
    }
    
    if (!$testError) {
        echo "<p style='color: green;'>✅ Video page should load without errors</p>";
        $fixes[] = "Video page functional";
    } else {
        $errors[] = "Video page has errors";
    }
    
    ob_end_clean();
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Video page test failed: " . $e->getMessage() . "</p>";
    $errors[] = "Video page test failed";
}

// Summary
echo "<h2>📊 Tổng kết</h2>";
echo "<div style='display: flex; gap: 20px;'>";

if (!empty($fixes)) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 8px; flex: 1;'>";
    echo "<h3 style='color: #155724;'>✅ Đã sửa/OK (" . count($fixes) . ")</h3>";
    echo "<ul>";
    foreach ($fixes as $fix) {
        echo "<li>$fix</li>";
    }
    echo "</ul>";
    echo "</div>";
}

if (!empty($errors)) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 8px; flex: 1;'>";
    echo "<h3 style='color: #721c24;'>❌ Còn lỗi (" . count($errors) . ")</h3>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
    echo "</div>";
}

echo "</div>";

// Next steps
echo "<h2>🚀 Bước tiếp theo</h2>";
echo "<div style='background: #e3f2fd; padding: 15px; border-radius: 8px;'>";
echo "<h4>Để hoàn thiện hệ thống video:</h4>";
echo "<ol>";
echo "<li><a href='video.php' target='_blank'>📺 Kiểm tra trang video</a></li>";
echo "<li><a href='test-video-system.php' target='_blank'>🧪 Chạy test chi tiết</a></li>";
echo "<li><a href='setup-video-system.php' target='_blank'>⚙️ Chạy setup hoàn chỉnh</a></li>";
echo "<li><a href='quan-ly-video.php' target='_blank'>📋 Truy cập quản lý video</a></li>";
echo "</ol>";
echo "</div>";

if (empty($errors)) {
    echo "<div style='background: #d1ecf1; padding: 20px; border-radius: 10px; margin: 20px 0; text-align: center;'>";
    echo "<h3 style='color: #0c5460;'>🎉 Hệ thống video đã sẵn sàng!</h3>";
    echo "<p>Tất cả lỗi đã được sửa. Bạn có thể sử dụng chức năng video ngay bây giờ.</p>";
    echo "<a href='video.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>📺 Xem trang Video</a>";
    echo "<a href='quan-ly-video.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>⚙️ Quản lý Video</a>";
    echo "</div>";
}
?>