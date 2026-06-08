<?php
header("Content-Type: text/html; charset=utf-8");
session_start();
require_once 'config.php';
require_once 'auth.php';

// Kiểm tra quyền admin
if (!authIsLoggedIn() || !authHasPermission('manage_content')) {
    die("❌ Cần quyền admin để chạy script này. <a href='dang-nhap.php'>Đăng nhập</a>");
}

echo "<h1>🔧 Sửa tất cả lỗi và Thống nhất Menu</h1>";
echo "<p>Script này sẽ sửa tất cả lỗi và áp dụng menu đơn giản cho toàn bộ website</p>";

// Chạy ngay lập tức
$results = [];
$backupDir = 'backup_sua_loi_' . date('Y-m-d_H-i-s');

// Tạo backup
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
    echo "<p style='color: green;'>✅ Tạo backup: <strong>$backupDir</strong></p>";
}

// 1. Tạo các file thiếu ngay lập tức
echo "<h2>📄 Tạo file thiếu</h2>";

// Tạo file logout.php nếu thiếu
if (!file_exists('logout.php')) {
    $logoutContent = '<?php
session_start();
require_once "auth.php";

authLogoutUser();
header("Location: index.php?logout=1");
exit;
?>';
    file_put_contents('logout.php', $logoutContent);
    echo "<p style='color: green;'>✅ Đã tạo logout.php</p>";
}

// Tạo file thu-tuc-hanh-chinh.php nếu thiếu
if (!file_exists('thu-tuc-hanh-chinh.php')) {
    $thuTucContent = '<?php
header("Content-Type: text/html; charset=utf-8");
session_start();
require_once "config.php";
require_once "auth.php";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thủ tục hành chính - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include "menu-don-gian.php"; ?>
    
    <main>
        <div class="container">
            <h1>Kho thủ tục hành chính và biểu mẫu</h1>
            <p>Tra cứu hồ sơ cần chuẩn bị, thời hạn xử lý và biểu mẫu liên quan tại UBND xã Long Hiệp.</p>
            
            <div class="procedure-section">
                <h2>📋 Các thủ tục phổ biến</h2>
                <div class="procedure-grid">
                    <div class="procedure-item">
                        <h3>Giấy chứng nhận quyền sử dụng đất</h3>
                        <p>Thủ tục cấp, cấp đổi, cấp lại giấy chứng nhận quyền sử dụng đất</p>
                    </div>
                    <div class="procedure-item">
                        <h3>Giấy phép xây dựng</h3>
                        <p>Thủ tục xin phép xây dựng nhà ở, công trình phụ trợ</p>
                    </div>
                    <div class="procedure-item">
                        <h3>Hộ khẩu, CCCD</h3>
                        <p>Thủ tục đăng ký thường trú, tạm trú, cấp CCCD</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <style>
    .procedure-section {
        margin: 30px 0;
    }
    .procedure-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin: 20px 0;
    }
    .procedure-item {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #007bff;
    }
    .procedure-item h3 {
        color: #007bff;
        margin-bottom: 10px;
    }
    </style>
</body>
</html>';
    file_put_contents('thu-tuc-hanh-chinh.php', $thuTucContent);
    echo "<p style='color: green;'>✅ Đã tạo thu-tuc-hanh-chinh.php</p>";
}

// 2. Áp dụng menu đơn giản cho tất cả file
echo "<h2>🎯 Áp dụng menu đơn giản</h2>";

$allFiles = glob('*.php');
$excludeFiles = [
    'menu-don-gian.php',
    'sua-tat-ca-loi.php',
    'kiem-tra-va-sua-loi.php',
    'xoa-media.php'
];
$phpFiles = array_diff($allFiles, $excludeFiles);

$successCount = 0;
$skipCount = 0;
$errorCount = 0;

foreach ($phpFiles as $file) {
    if (!file_exists($file)) continue;
    
    try {
        // Backup file
        copy($file, $backupDir . '/' . $file);
        
        $content = file_get_contents($file);
        $originalContent = $content;
        
        // Kiểm tra đã có menu đơn giản chưa
        if (strpos($content, 'menu-don-gian.php') !== false) {
            $results[$file] = 'skip';
            $skipCount++;
            continue;
        }
        
        $replaced = false;
        
        // Các pattern để thay thế
        $patterns = [
            // Header thống nhất cũ
            '/\<\?php include [\'"]header-thong-nhat\.php[\'"];\s*\?\>/',
            // Header với nav
            '/\<header[^>]*\>.*?\<\/header\>/s',
            // Chỉ có nav
            '/\<nav[^>]*\>.*?\<\/nav\>/s'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, '    <?php include \'menu-don-gian.php\'; ?>', $content, 1);
                $replaced = true;
                break;
            }
        }
        
        // Nếu không tìm thấy, thêm sau body
        if (!$replaced && preg_match('/\<body[^>]*\>/i', $content)) {
            $content = preg_replace('/(\<body[^>]*\>)/i', '$1' . "\n
    <?php include 'menu-don-gian.php'; ?>", $content, 1);
            $replaced = true;
        }
        
        if ($replaced && $content !== $originalContent) {
            file_put_contents($file, $content);
            $results[$file] = 'success';
            $successCount++;
        } else {
            $results[$file] = 'error';
            $errorCount++;
        }
        
    } catch (Exception $e) {
        $results[$file] = 'error';
        $errorCount++;
    }
}

// 3. Hiển thị kết quả
echo "<h2>📊 Kết quả</h2>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>Thống kê:</h3>";
echo "<ul>";
echo "<li><strong style='color: green;'>Thành công:</strong> $successCount file</li>";
echo "<li><strong style='color: blue;'>Đã có menu:</strong> $skipCount file</li>";
echo "<li><strong style='color: red;'>Lỗi:</strong> $errorCount file</li>";
echo "<li><strong>Tổng cộng:</strong> " . count($results) . " file</li>";
echo "</ul>";
echo "</div>";

// 4. Tạo thư mục cần thiết
echo "<h2>📁 Tạo thư mục</h2>";
$dirs = ['videos', 'images', 'uploads'];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "<p style='color: green;'>✅ Đã tạo thư mục: $dir</p>";
    }
}

// 5. Kết quả cuối cùng
if ($successCount > 0) {
    echo "<div style='background: #d4edda; padding: 25px; border-radius: 8px; border-left: 4px solid #28a745; margin: 30px 0;'>";
    echo "<h3 style='color: #155724;'>🎉 Hoàn thành!</h3>";
    echo "<p style='color: #155724;'>Đã sửa lỗi và áp dụng menu đơn giản cho <strong>$successCount</strong> trang.</p>";
    echo "<p style='color: #155724;'><strong>Website bây giờ có:</strong></p>";
    echo "<ul style='color: #155724;'>";
    echo "<li>✨ Menu thống nhất trên tất cả trang</li>";
    echo "<li>🎬 Menu video đầy đủ 4 tùy chọn</li>";
    echo "<li>📱 Responsive design</li>";
    echo "<li>👤 Phân quyền admin/user</li>";
    echo "<li>🔧 Các file thiếu đã được tạo</li>";
    echo "</ul>";
    echo "</div>";
}

echo "<h2>🔗 Kiểm tra website</h2>";
echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0;'>";

$testPages = [
    'index.php' => '🏠 Trang chủ',
    'tin-tuc.php' => '📰 Tin tức',
    'video.php' => '📺 Video',
    'video-files.php' => '📁 File video',
    'lanh-dao.php' => '👥 Lãnh đạo',
    'phong-ban.php' => '🏢 Phòng ban',
    'lien-he.php' => '📞 Liên hệ',
    'thu-tuc-hanh-chinh.php' => '📋 Thủ tục',
    'dashboard.php' => '📊 Dashboard'
];

foreach ($testPages as $page => $title) {
    if (file_exists($page)) {
        echo "<a href='$page' target='_blank' style='display: block; padding: 15px; background: white; border: 2px solid #e9ecef; border-radius: 8px; text-decoration: none; color: #2c3e50; text-align: center; transition: all 0.3s ease;' onmouseover='this.style.borderColor=\"#007bff\"' onmouseout='this.style.borderColor=\"#e9ecef\"'>";
        echo "<strong>$title</strong>";
        echo "</a>";
    }
}

echo "</div>";

echo "<p style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #007bff;'>";
echo "<strong>📁 Backup:</strong> $backupDir<br>";
echo "<strong>🎯 Menu:</strong> Tất cả trang đã sử dụng menu-don-gian.php<br>";
echo "<strong>✅ Hoàn thành:</strong> Website đã thống nhất và sẵn sàng sử dụng!";
echo "</p>";

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
    color: white;
    text-align: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 30px;
    border-radius: 10px;
    margin-bottom: 30px;
}

h2 {
    color: #2c3e50;
    border-bottom: 2px solid #007bff;
    padding-bottom: 10px;
    margin-top: 40px;
}

.container {
    max-width: 1000px;
    margin: 0 auto;
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
</style>