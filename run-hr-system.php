<?php
require_once 'config.php';

$conn = getDBConnection();

// Đọc file SQL
$sql_file = 'create-hr-system.sql';
if (!file_exists($sql_file)) {
    die("File $sql_file không tồn tại!");
}

$sql = file_get_contents($sql_file);

// Tách các câu lệnh SQL
$statements = array_filter(
    array_map('trim', explode(';', $sql)),
    function($stmt) {
        return !empty($stmt) && !preg_match('/^--/', $stmt);
    }
);

echo "<h2>Đang chạy script tạo hệ thống quản lý nhân sự...</h2>";
echo "<pre>";

$success_count = 0;
$error_count = 0;

foreach ($statements as $statement) {
    if (empty(trim($statement))) continue;
    
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "Đang thực thi:\n" . substr($statement, 0, 100) . "...\n";
    
    if ($conn->query($statement)) {
        echo "✅ Thành công!\n";
        $success_count++;
    } else {
        echo "❌ Lỗi: " . $conn->error . "\n";
        $error_count++;
    }
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "\n<strong>KẾT QUẢ:</strong>\n";
echo "✅ Thành công: $success_count câu lệnh\n";
echo "❌ Lỗi: $error_count câu lệnh\n";

if ($error_count == 0) {
    echo "\n<span style='color: green; font-size: 18px;'>🎉 Tạo hệ thống quản lý nhân sự thành công!</span>\n";
    echo "\n<a href='quan-ly-nhan-su.php' style='display: inline-block; margin-top: 20px; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px;'>Đi đến trang quản lý nhân sự</a>\n";
} else {
    echo "\n<span style='color: red;'>⚠️ Có lỗi xảy ra, vui lòng kiểm tra lại!</span>\n";
}

echo "</pre>";

$conn->close();
?>
