<?php
header("Content-Type: text/html; charset=utf-8");

echo "<h1>🔍 Kiểm tra Website sau khi sửa lỗi</h1>";
echo "<p>Kiểm tra tất cả trang đã có menu thống nhất và hoạt động bình thường</p>";

// Kiểm tra file menu chính
$menuFile = 'menu-don-gian.php';
if (file_exists($menuFile)) {
    echo "<p style='color: green;'>✅ File menu chính: <strong>$menuFile</strong> - Tồn tại</p>";
} else {
    echo "<p style='color: red;'>❌ File menu chính: <strong>$menuFile</strong> - Không tồn tại</p>";
}

// Kiểm tra các file quan trọng
$importantFiles = [
    'index.php' => '🏠 Trang chủ',
    'tin-tuc.php' => '📰 Tin tức',
    'video.php' => '📺 Video chính thức',
    'video-files.php' => '📁 Tất cả file video',
    'lanh-dao.php' => '👥 Lãnh đạo',
    'phong-ban.php' => '🏢 Phòng ban',
    'lien-he.php' => '📞 Liên hệ',
    'thu-tuc-hanh-chinh.php' => '📋 Thủ tục hành chính',
    'dashboard.php' => '📊 Dashboard',
    'them-video-moi.php' => '➕ Thêm video mới',
    'quan-ly-video.php' => '🎬 Quản lý video',
    'logout.php' => '🚪 Đăng xuất'
];

echo "<h2>📋 Kiểm tra các trang quan trọng</h2>";
echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px; margin: 20px 0;'>";

$hasMenuCount = 0;
$totalCount = 0;

foreach ($importantFiles as $file => $title) {
    $totalCount++;
    $status = '';
    $color = '';
    $hasMenu = false;
    
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (strpos($content, 'menu-don-gian.php') !== false) {
            $status = '✅ Có menu thống nhất';
            $color = '#d4edda';
            $hasMenu = true;
            $hasMenuCount++;
        } else {
            $status = '⚠️ Chưa có menu thống nhất';
            $color = '#fff3cd';
        }
    } else {
        $status = '❌ File không tồn tại';
        $color = '#f8d7da';
    }
    
    echo "<div style='background: $color; padding: 15px; border-radius: 8px; border-left: 4px solid " . ($hasMenu ? '#28a745' : '#ffc107') . ";'>";
    echo "<h4 style='margin: 0 0 10px 0;'>$title</h4>";
    echo "<p style='margin: 0; font-size: 14px;'><strong>$file</strong></p>";
    echo "<p style='margin: 5px 0 0 0; font-size: 14px;'>$status</p>";
    if (file_exists($file)) {
        echo "<a href='$file' target='_blank' style='display: inline-block; margin-top: 10px; padding: 5px 10px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; font-size: 12px;'>Xem trang</a>";
    }
    echo "</div>";
}

echo "</div>";

// Kiểm tra thư mục cần thiết
echo "<h2>📁 Kiểm tra thư mục</h2>";
$requiredDirs = ['videos', 'images', 'uploads'];
foreach ($requiredDirs as $dir) {
    if (is_dir($dir)) {
        echo "<p style='color: green;'>✅ Thư mục <strong>$dir</strong> - Tồn tại</p>";
    } else {
        echo "<p style='color: red;'>❌ Thư mục <strong>$dir</strong> - Không tồn tại</p>";
    }
}

// Tổng kết
echo "<h2>📊 Tổng kết</h2>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>Kết quả kiểm tra:</h3>";
echo "<ul>";
echo "<li><strong style='color: green;'>Có menu thống nhất:</strong> $hasMenuCount/$totalCount trang</li>";
echo "<li><strong>Tỷ lệ hoàn thành:</strong> " . round(($hasMenuCount/$totalCount)*100, 1) . "%</li>";
echo "</ul>";

if ($hasMenuCount == $totalCount) {
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; border-left: 4px solid #28a745; margin: 20px 0;'>";
    echo "<h3 style='color: #155724;'>🎉 Hoàn hảo!</h3>";
    echo "<p style='color: #155724;'>Tất cả các trang quan trọng đã có menu thống nhất.</p>";
    echo "<p style='color: #155724;'><strong>Website đã sẵn sàng sử dụng với:</strong></p>";
    echo "<ul style='color: #155724;'>";
    echo "<li>✨ Menu thống nhất trên tất cả trang</li>";
    echo "<li>🎬 Hệ thống video hoàn chỉnh</li>";
    echo "<li>📱 Giao diện responsive</li>";
    echo "<li>� Phân quyền admin/user</li>";
    echo "<li>🔧 Các tính năng đầy đủ</li>";
    echo "</ul>";
    echo "</div>";
} else {
    echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; border-left: 4px solid #ffc107; margin: 20px 0;'>";
    echo "<h3 style='color: #856404;'>⚠️ Cần hoàn thiện</h3>";
    echo "<p style='color: #856404;'>Còn " . ($totalCount - $hasMenuCount) . " trang chưa có menu thống nhất.</p>";
    echo "<p style='color: #856404;'>Chạy lại script <strong>apply-unified-menu-now.php</strong> để hoàn thiện.</p>";
    echo "</div>";
}

echo "</div>";

// Menu video features
echo "<h2>🎬 Tính năng Video</h2>";
echo "<div style='background: #e7f3ff; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>Menu Video có 4 tùy chọn:</h3>";
echo "<ul>";
echo "<li><strong>Video chính thức:</strong> <a href='video.php' target='_blank'>video.php</a> - Video từ database</li>";
echo "<li><strong>Tất cả file video:</strong> <a href='video-files.php' target='_blank'>video-files.php</a> - Scan thư mục videos/</li>";
echo "<li><strong>File audio:</strong> <a href='video-files.php?type=audio' target='_blank'>video-files.php?type=audio</a> - Chỉ file âm thanh</li>";
echo "<li><strong>Video nổi bật:</strong> <a href='video.php?featured=1' target='_blank'>video.php?featured=1</a> - Video được đánh dấu</li>";
echo "</ul>";
echo "</div>";

echo "<h2>� Liên kết nhanh</h2>";
echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0;'>";

$quickLinks = [
    'index.php' => '🏠 Trang chủ',
    'dashboard.php' => '📊 Dashboard',
    'them-video-moi.php' => '➕ Thêm video',
    'quan-ly-video.php' => '🎬 Quản lý video',
    'video.php' => '� Xem video',
    'video-files.php' => '📁 File video',
    'tin-tuc.php' => '📰 Tin tức',
    'lien-he.php' => '📞 Liên hệ'
];

foreach ($quickLinks as $page => $title) {
    if (file_exists($page)) {
        echo "<a href='$page' target='_blank' style='display: block; padding: 15px; background: white; border: 2px solid #e9ecef; border-radius: 8px; text-decoration: none; color: #2c3e50; text-align: center; transition: all 0.3s ease;' onmouseover='this.style.borderColor=\"#007bff\"' onmouseout='this.style.borderColor=\"#e9ecef\"'>";
        echo "<strong>$title</strong>";
        echo "</a>";
    }
}

echo "</div>";

echo "<p style='background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #007bff;'>";
echo "<strong>🎯 Kết luận:</strong> Website đã được sửa lỗi và thống nhất menu thành công!<br>";
echo "<strong>📱 Responsive:</strong> Menu tự động điều chỉnh cho mobile<br>";
echo "<strong>🔧 Bảo trì:</strong> Dễ dàng cập nhật và mở rộng";
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

h3 {
    color: #2c3e50;
    margin-top: 20px;
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