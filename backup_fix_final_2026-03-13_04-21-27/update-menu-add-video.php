<?php
// Script để cập nhật menu thêm link Video vào tất cả các file PHP

$files = [
    'tin-tuc.php', 'lanh-dao.php', 'phong-ban.php', 'lien-he.php', 'dang-nhap.php', 'dang-ky.php',
    'chi-tiet-tin.php', 'chi-tiet-lanh-dao.php', 'thu-tuc-hanh-chinh.php',
    'cong-tac-xay-dung-dang.php', 'mat-tran-doan-the.php', 'an-ninh-trat-tu.php',
    'tin-tuc-su-kien.php', 'thong-tin-tuyen-truyen.php', 'giao-duc-dao-tao.php',
    'phong-hanh-chinh-cong.php', 'phong-hdnn.php', 'phong-kinh-te.php', 'phong-ubnd.php',
    'phong-ban-chi-tiet.php'
];

$oldMenuPattern = '<li><a href="lanh-dao.php"';
$newMenuPattern = '<li><a href="lanh-dao.php"';

$oldMenuLine = '<li><a href="thu-tuc-hanh-chinh.php">Thủ tục</a></li>';
$newMenuLine = '<li><a href="video.php">Video</a></li>
                    <li><a href="thu-tuc-hanh-chinh.php">Thủ tục</a></li>';

$updatedFiles = [];
$errorFiles = [];

foreach ($files as $file) {
    if (!file_exists($file)) {
        $errorFiles[] = "$file - File không tồn tại";
        continue;
    }
    
    $content = file_get_contents($file);
    if ($content === false) {
        $errorFiles[] = "$file - Không thể đọc file";
        continue;
    }
    
    // Kiểm tra xem đã có link video chưa
    if (strpos($content, 'href="video.php"') !== false) {
        $errorFiles[] = "$file - Đã có link video";
        continue;
    }
    
    // Thay thế menu
    $newContent = str_replace($oldMenuLine, $newMenuLine, $content);
    
    if ($newContent === $content) {
        $errorFiles[] = "$file - Không tìm thấy pattern để thay thế";
        continue;
    }
    
    if (file_put_contents($file, $newContent) !== false) {
        $updatedFiles[] = $file;
    } else {
        $errorFiles[] = "$file - Không thể ghi file";
    }
}

echo "<h2>🔧 Kết quả cập nhật menu thêm Video</h2>";

if (!empty($updatedFiles)) {
    echo "<h3 style='color: green;'>✅ Đã cập nhật thành công (" . count($updatedFiles) . " files):</h3>";
    echo "<ul>";
    foreach ($updatedFiles as $file) {
        echo "<li>$file</li>";
    }
    echo "</ul>";
}

if (!empty($errorFiles)) {
    echo "<h3 style='color: orange;'>⚠️ Lỗi hoặc bỏ qua (" . count($errorFiles) . " files):</h3>";
    echo "<ul>";
    foreach ($errorFiles as $error) {
        echo "<li>$error</li>";
    }
    echo "</ul>";
}

echo "<h3>📋 Tổng kết:</h3>";
echo "<p>✅ Thành công: " . count($updatedFiles) . " files</p>";
echo "<p>⚠️ Lỗi/Bỏ qua: " . count($errorFiles) . " files</p>";
echo "<p>📁 Tổng cộng: " . count($files) . " files được kiểm tra</p>";

echo "<h3>🎯 Bước tiếp theo:</h3>";
echo "<ul>";
echo "<li><a href='video.php' target='_blank'>👀 Kiểm tra trang Video</a></li>";
echo "<li><a href='index.php' target='_blank'>🏠 Kiểm tra menu trên trang chủ</a></li>";
echo "<li><a href='create-videos-table.sql' target='_blank'>📊 Chạy SQL tạo bảng video</a></li>";
echo "</ul>";
?>