<?php
header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';

echo "<h2>Thêm thành viên Văn phòng UBMTTQVN xã</h2>";

try {
    $conn = getDBConnection();
    
    // Đọc file SQL
    $sql = file_get_contents('insert-danh-ba-van-phong-ubmttqvn.sql');
    
    // Xóa comments
    $sql = preg_replace('/--.*$/m', '', $sql);
    
    // Tách các câu lệnh
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $success = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement)) continue;
        
        if ($conn->query($statement)) {
            $success++;
            echo "<p style='color: green;'>✓ Thực thi thành công câu lệnh</p>";
        } else {
            $errors++;
            echo "<p style='color: red;'>✗ Lỗi: " . $conn->error . "</p>";
            echo "<pre>" . htmlspecialchars($statement) . "</pre>";
        }
    }
    
    echo "<hr>";
    echo "<h3>Kết quả:</h3>";
    echo "<p>Thành công: <strong style='color: green;'>$success</strong> câu lệnh</p>";
    echo "<p>Lỗi: <strong style='color: red;'>$errors</strong> câu lệnh</p>";
    
    if ($errors == 0) {
        echo "<p style='color: green; font-size: 18px;'><strong>✓ Đã thêm tất cả thành viên thành công!</strong></p>";
        echo "<p><a href='quan-ly-danh-ba.php'>← Quay lại Quản lý danh bạ</a></p>";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Lỗi: " . $e->getMessage() . "</p>";
}
?>
