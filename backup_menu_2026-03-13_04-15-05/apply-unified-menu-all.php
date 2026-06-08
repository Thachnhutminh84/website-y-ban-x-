<?php
header("Content-Type: text/html; charset=utf-8");
session_start();
require_once 'config.php';
require_once 'auth.php';

// Kiểm tra quyền admin
if (!authIsLoggedIn() || !authHasPermission('manage_content')) {
    die("❌ Cần quyền admin để chạy script này. <a href='dang-nhap.php'>Đăng nhập</a>");
}

echo "<h1>🚀 Áp dụng Menu Thống nhất cho Toàn bộ Website</h1>";

// Lấy danh sách tất cả file PHP
$allFiles = glob('*.php');
$excludeFiles = [
    'menu-thong-nhat.php', 
    'header-thong-nhat.php', 
    'apply-unified-menu-all.php',
    'ap-dung-menu-video-toan-bo.php',
    'quick-fix.php',
    'fix-syntax-errors.php',
    'check-all-fixed.php'
];
$phpFiles = array_diff($allFiles, $excludeFiles);

$results = [];
$backupDir = 'backup_unified_menu_' . date('Y-m-d_H-i-s');

// Tạo backup
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
    echo "<p style='color: green;'>✅ Tạo thư mục backup: <strong>$backupDir</strong></p>";
}

echo "<h2>📋 Đang xử lý " . count($phpFiles) . " file PHP...</h2>";

foreach ($phpFiles as $file) {
    if (!file_exists($file) || pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
        continue;
    }
    
    try {
        // Backup file gốc
        copy($file, $backupDir . '/' . $file);
        
        // Đọc nội dung file
        $content = file_get_contents($file);
        $originalContent = $content;
        
        // Kiểm tra xem đã có header thống nhất chưa
        if (strpos($content, 'header-thong-nhat.php') !== false) {
            $results[$file] = ['status' => 'skip', 'message' => 'Đã có menu thống nhất'];
            continue;
        }
        
        // Các pattern để tìm và thay thế header cũ
        $headerPatterns = [
            // Pattern 1: Header với nav bên trong (có class động)
            '/(<header[^>]*>.*?<\/header>)/s',
            // Pattern 2: Chỉ có nav
            '/(<nav[^>]*>.*?<\/nav>)/s'
        ];
        
        $replaced = false;
        foreach ($headerPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $newContent = preg_replace($pattern, '    <!-- Header thống nhất -->' . "\n" . '    <?php include \'header-thong-nhat.php\'; ?>', $content, 1);
                if ($newContent !== $content && $newContent !== null) {
                    $content = $newContent;
                    $replaced = true;
                    break;
                }
            }
        }
        
        // Nếu không tìm thấy header, thêm vào sau thẻ body
        if (!$replaced) {
            if (preg_match('/(<body[^>]*>)/i', $content)) {
                $newContent = preg_replace('/(<body[^>]*>)/i', '$1' . "\n    <!-- Header thống nhất -->\n    <?php include 'header-thong-nhat.php'; ?>", $content, 1);
                if ($newContent !== $content && $newContent !== null) {
                    $content = $newContent;
                    $replaced = true;
                }
            }
        }
        
        // Lưu file nếu có thay đổi
        if ($replaced && $content !== $originalContent) {
            file_put_contents($file, $content);
            $results[$file] = ['status' => 'success', 'message' => 'Đã cập nhật thành công'];
        } else if (!$replaced) {
            $results[$file] = ['status' => 'error', 'message' => 'Không tìm thấy vị trí phù hợp để thêm menu'];
        } else {
            $results[$file] = ['status' => 'skip', 'message' => 'Không có thay đổi'];
        }
        
    } catch (Exception $e) {
        $results[$file] = ['status' => 'error', 'message' => 'Lỗi: ' . $e->getMessage()];
    }
}

// Hiển thị kết quả
echo "<h2>📊 Kết quả xử lý</h2>";
$successCount = 0;
$skipCount = 0;
$errorCount = 0;

echo "<div style='max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 15px; background: #f9f9f9;'>";

foreach ($results as $file => $result) {
    $icon = '';
    $color = '';
    
    switch ($result['status']) {
        case 'success':
            $icon = '✅';
            $color = 'green';
            $successCount++;
            break;
        case 'skip':
            $icon = '⏭️';
            $color = 'blue';
            $skipCount++;
            break;
        case 'error':
            $icon = '❌';
            $color = 'red';
            $errorCount++;
            break;
    }
    
    echo "<p style='color: $color; margin: 5px 0;'>$icon <strong>$file:</strong> {$result['message']}</p>";
}

echo "</div>";

echo "<h3>📈 Thống kê tổng kết</h3>";
echo "<div style='background: #e7f3ff; padding: 20px; border-radius: 8px; border-left: 4px solid #007bff;'>";
echo "<ul style='font-size: 16px; line-height: 1.6;'>";
echo "<li><strong style='color: green;'>Thành công:</strong> $successCount file</li>";
echo "<li><strong style='color: blue;'>Đã có menu:</strong> $skipCount file</li>";
echo "<li><strong style='color: red;'>Lỗi:</strong> $errorCount file</li>";
echo "<li><strong>Tổng cộng:</strong> " . count($results) . " file đã xử lý</li>";
echo "</ul>";
echo "</div>";

echo "<p style='background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;'>";
echo "<strong>📁 Backup được lưu tại:</strong> <code>$backupDir</code><br>";
echo "Nếu có vấn đề, bạn có thể khôi phục từ thư mục backup này.";
echo "</p>";

echo "<h3>🔗 Kiểm tra kết quả</h3>";
echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0;'>";

$testPages = [
    'index.php' => '🏠 Trang chủ',
    'tin-tuc.php' => '📰 Tin tức',
    'video.php' => '📺 Video',
    'lanh-dao.php' => '👥 Lãnh đạo',
    'phong-ban.php' => '🏢 Phòng ban',
    'lien-he.php' => '📞 Liên hệ',
    'dashboard.php' => '📊 Dashboard',
    'quan-ly-video.php' => '🎬 Quản lý video'
];

foreach ($testPages as $page => $title) {
    if (file_exists($page)) {
        echo "<a href='$page' target='_blank' style='display: block; padding: 15px; background: white; border: 2px solid #e9ecef; border-radius: 8px; text-decoration: none; color: #2c3e50; transition: all 0.3s ease;' onmouseover='this.style.borderColor=\"#007bff\"; this.style.transform=\"translateY(-2px)\"' onmouseout='this.style.borderColor=\"#e9ecef\"; this.style.transform=\"translateY(0)\"'>";
        echo "<strong>$title</strong><br>";
        echo "<small style='color: #666;'>Kiểm tra menu thống nhất</small>";
        echo "</a>";
    }
}

echo "</div>";

echo "<h3>🛠️ Tools hỗ trợ</h3>";
echo "<div style='display: flex; gap: 15px; flex-wrap: wrap;'>";
echo "<a href='check-all-fixed.php' style='padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 5px;'>✅ Kiểm tra tổng thể</a>";
echo "<a href='kiem-tra-menu-video-toan-bo.php' style='padding: 10px 20px; background: #17a2b8; color: white; text-decoration: none; border-radius: 5px;'>🔍 Kiểm tra menu video</a>";
echo "<a href='test-delete-simple.php' style='padding: 10px 20px; background: #6f42c1; color: white; text-decoration: none; border-radius: 5px;'>🧪 Test chức năng</a>";
echo "</div>";

if ($successCount > 0) {
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; border-left: 4px solid #28a745; margin: 30px 0;'>";
    echo "<h3 style='color: #155724; margin-bottom: 15px;'>🎉 Hoàn thành!</h3>";
    echo "<p style='color: #155724; margin: 0;'>Đã áp dụng menu thống nhất thành công cho <strong>$successCount</strong> trang. Tất cả các trang bây giờ sẽ có menu video đầy đủ với các tùy chọn:</p>";
    echo "<ul style='color: #155724; margin: 10px 0;'>";
    echo "<li>📺 Video chính thức</li>";
    echo "<li>📁 Tất cả file video</li>";
    echo "<li>🎵 File audio</li>";
    echo "<li>⭐ Video nổi bật</li>";
    echo "</ul>";
    echo "</div>";
}

?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.6;
    margin: 0;
    padding: 20px;
    background: #f8f9fa;
}

h1 {
    color: #2c3e50;
    text-align: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 10px;
    margin-bottom: 30px;
}

h2 {
    color: #2c3e50;
    border-bottom: 2px solid #007bff;
    padding-bottom: 10px;
}

h3 {
    color: #495057;
    margin-top: 30px;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
</style>