<?php
header("Content-Type: text/html; charset=utf-8");
session_start();
require_once 'config.php';
require_once 'auth.php';

// Kiểm tra quyền admin
if (!authIsLoggedIn() || !authHasPermission('manage_content')) {
    die("Cần quyền admin để chạy script này.");
}

echo "<h1>🔧 Sửa lỗi Syntax</h1>";

// Lấy danh sách tất cả file PHP
$allFiles = glob('*.php');
$excludeFiles = ['menu-thong-nhat.php', 'header-thong-nhat.php', 'fix-syntax-errors.php'];
$phpFiles = array_diff($allFiles, $excludeFiles);

$results = [];
$backupDir = 'backup_syntax_' . date('Y-m-d_H-i-s');

// Tạo backup
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
    echo "<p>✅ Tạo thư mục backup: $backupDir</p>";
}

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
        
        // Kiểm tra xem có include header thống nhất không
        if (strpos($content, 'header-thong-nhat.php') === false) {
            $results[$file] = ['status' => 'skip', 'message' => 'Chưa có header thống nhất'];
            continue;
        }
        
        // Sửa lỗi: Code HTML sót lại sau include header
        $patterns = [
            // Lỗi 1: Code menu cũ sau include header
            '/(\<\?php include [\'"]header-thong-nhat\.php[\'"];\s*\?\>)\s*.*?(\<\/header\>)/s' => '$1',
            
            // Lỗi 2: Code PHP sót lại
            '/(\<\?php include [\'"]header-thong-nhat\.php[\'"];\s*\?\>)\s*.*?(\<\?php endif;\s*\?\>)/s' => '$1',
            
            // Lỗi 3: Thẻ nav sót lại
            '/(\<\?php include [\'"]header-thong-nhat\.php[\'"];\s*\?\>)\s*.*?(\<\/nav\>)/s' => '$1',
            
            // Lỗi 4: Code HTML khác sót lại
            '/(\<\?php include [\'"]header-thong-nhat\.php[\'"];\s*\?\>)\s*.*?(\<\/ul\>)/s' => '$1',
        ];
        
        $fixed = false;
        foreach ($patterns as $pattern => $replacement) {
            if (preg_match($pattern, $content)) {
                $newContent = preg_replace($pattern, $replacement, $content);
                if ($newContent !== $content) {
                    $content = $newContent;
                    $fixed = true;
                }
            }
        }
        
        // Sửa lỗi: Thẻ đóng sót lại
        $cleanupPatterns = [
            '/(\<\?php include [\'"]header-thong-nhat\.php[\'"];\s*\?\>)\s*\<\/li\>/s' => '$1',
            '/(\<\?php include [\'"]header-thong-nhat\.php[\'"];\s*\?\>)\s*\<\/ul\>/s' => '$1',
            '/(\<\?php include [\'"]header-thong-nhat\.php[\'"];\s*\?\>)\s*\<\/nav\>/s' => '$1',
            '/(\<\?php include [\'"]header-thong-nhat\.php[\'"];\s*\?\>)\s*\<\/div\>/s' => '$1',
            '/(\<\?php include [\'"]header-thong-nhat\.php[\'"];\s*\?\>)\s*\<\/header\>/s' => '$1',
        ];
        
        foreach ($cleanupPatterns as $pattern => $replacement) {
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $replacement, $content);
                $fixed = true;
            }
        }
        
        // Sửa lỗi: Code PHP endif sót lại
        $content = preg_replace('/(\<\?php include [\'"]header-thong-nhat\.php[\'"];\s*\?\>)\s*\<\?php endif;\s*\?\>/s', '$1', $content);
        
        // Sửa lỗi: Logout link sót lại
        $content = preg_replace('/(\<\?php include [\'"]header-thong-nhat\.php[\'"];\s*\?\>)\s*.*?logout\.php.*?\<\/a\>/s', '$1', $content);
        
        if ($content !== $originalContent) {
            file_put_contents($file, $content);
            $results[$file] = ['status' => 'success', 'message' => 'Đã sửa lỗi syntax'];
        } else {
            $results[$file] = ['status' => 'ok', 'message' => 'Không có lỗi'];
        }
        
    } catch (Exception $e) {
        $results[$file] = ['status' => 'error', 'message' => 'Lỗi: ' . $e->getMessage()];
    }
}

// Hiển thị kết quả
echo "<h2>📊 Kết quả sửa lỗi</h2>";
$successCount = 0;
$okCount = 0;
$skipCount = 0;
$errorCount = 0;

foreach ($results as $file => $result) {
    $icon = '';
    $color = '';
    
    switch ($result['status']) {
        case 'success':
            $icon = '✅';
            $color = 'green';
            $successCount++;
            break;
        case 'ok':
            $icon = '👍';
            $color = 'blue';
            $okCount++;
            break;
        case 'skip':
            $icon = '⏭️';
            $color = 'gray';
            $skipCount++;
            break;
        case 'error':
            $icon = '❌';
            $color = 'red';
            $errorCount++;
            break;
    }
    
    echo "<p style='color: $color;'>$icon <strong>$file:</strong> {$result['message']}</p>";
}

echo "<h3>📈 Thống kê</h3>";
echo "<ul>";
echo "<li><strong>Đã sửa lỗi:</strong> $successCount file</li>";
echo "<li><strong>Không có lỗi:</strong> $okCount file</li>";
echo "<li><strong>Bỏ qua:</strong> $skipCount file</li>";
echo "<li><strong>Lỗi:</strong> $errorCount file</li>";
echo "<li><strong>Tổng cộng:</strong> " . count($results) . " file</li>";
echo "</ul>";

echo "<p><strong>Backup được lưu tại:</strong> $backupDir</p>";

echo "<h3>🔗 Kiểm tra</h3>";
echo "<p><a href='tin-tuc.php' target='_blank'>📰 Kiểm tra trang Tin tức</a></p>";
echo "<p><a href='video.php' target='_blank'>📺 Kiểm tra trang Video</a></p>";
echo "<p><a href='lanh-dao.php' target='_blank'>👥 Kiểm tra trang Lãnh đạo</a></p>";

// Kiểm tra syntax PHP
echo "<h3>🔍 Kiểm tra Syntax PHP</h3>";
foreach ($phpFiles as $file) {
    if (file_exists($file)) {
        $output = [];
        $return_var = 0;
        exec("php -l \"$file\" 2>&1", $output, $return_var);
        
        if ($return_var === 0) {
            echo "<p style='color: green;'>✅ <strong>$file:</strong> Syntax OK</p>";
        } else {
            echo "<p style='color: red;'>❌ <strong>$file:</strong> " . implode(' ', $output) . "</p>";
        }
    }
}
?>