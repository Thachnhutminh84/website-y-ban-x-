<?php
// Script nhanh để sửa lỗi và áp dụng menu
header("Content-Type: text/html; charset=utf-8");
session_start();
require_once 'config.php';
require_once 'auth.php';

if (!authIsLoggedIn() || !authHasPermission('manage_content')) {
    die("Cần quyền admin");
}

echo "<h1>🚀 Sửa lỗi nhanh</h1>";

// Danh sách file cần sửa
$files = [
    'lanh-dao.php',
    'phong-ban.php', 
    'lien-he.php',
    'dashboard.php',
    'quan-ly-video.php',
    'them-video.php',
    'sua-tin.php',
    'them-tin.php'
];

$backup = 'backup_quick_' . date('H-i-s');
mkdir($backup, 0755, true);

foreach ($files as $file) {
    if (!file_exists($file)) continue;
    
    // Backup
    copy($file, $backup . '/' . $file);
    
    $content = file_get_contents($file);
    
    // Nếu đã có header thống nhất thì bỏ qua
    if (strpos($content, 'header-thong-nhat.php') !== false) {
        echo "<p>⏭️ $file: Đã có menu thống nhất</p>";
        continue;
    }
    
    // Thay thế header cũ
    $patterns = [
        '/\<header[^>]*\>.*?\<\/header\>/s',
        '/\<nav[^>]*\>.*?\<\/nav\>/s'
    ];
    
    $replaced = false;
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, '    <!-- Header thống nhất -->' . "\n" . '    <?php include \'header-thong-nhat.php\'; ?>', $content, 1);
            $replaced = true;
            break;
        }
    }
    
    if ($replaced) {
        file_put_contents($file, $content);
        echo "<p>✅ $file: Đã cập nhật</p>";
    } else {
        echo "<p>❌ $file: Không tìm thấy header để thay thế</p>";
    }
}

echo "<p><strong>Backup:</strong> $backup</p>";
echo "<p><a href='tin-tuc.php'>Test tin-tuc.php</a></p>";
echo "<p><a href='video.php'>Test video.php</a></p>";
?>