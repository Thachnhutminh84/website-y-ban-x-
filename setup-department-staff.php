<?php
header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';

echo "<h2>Setup Department Staff Table</h2>";

$conn = getDBConnection();
if (!$conn) {
    die("✗ Không thể kết nối database");
}

// Đọc file SQL
$sqlFile = 'create-department-staff-table.sql';
if (!file_exists($sqlFile)) {
    die("✗ Không tìm thấy file $sqlFile");
}

$sql = file_get_contents($sqlFile);

// Tách các câu lệnh SQL
$statements = array_filter(
    array_map('trim', explode(';', $sql)),
    function($stmt) {
        return !empty($stmt) && strpos($stmt, '--') !== 0;
    }
);

echo "<h3>Đang chạy SQL...</h3>";
$success = 0;
$errors = 0;

foreach ($statements as $statement) {
    if (empty(trim($statement))) continue;
    
    if ($conn->query($statement)) {
        $success++;
        // Hiển thị câu lệnh thành công (chỉ 100 ký tự đầu)
        $preview = substr($statement, 0, 100);
        echo "✓ " . htmlspecialchars($preview) . "...<br>";
    } else {
        $errors++;
        echo "✗ Lỗi: " . $conn->error . "<br>";
        echo "SQL: " . htmlspecialchars(substr($statement, 0, 200)) . "...<br>";
    }
}

echo "<hr>";
echo "<h3>Kết quả</h3>";
echo "✓ Thành công: $success câu lệnh<br>";
echo "✗ Lỗi: $errors câu lệnh<br>";

// Kiểm tra kết quả
$result = $conn->query("SELECT COUNT(*) as total FROM department_staff WHERE status = 'active'");
if ($result) {
    $row = $result->fetch_assoc();
    echo "<br>✓ Hiện có {$row['total']} cán bộ trong database<br>";
}

$conn->close();

echo "<hr>";
echo "<p><a href='test-department-staff.php'>→ Kiểm tra lại hệ thống</a></p>";
echo "<p><a href='phong-ban-chi-tiet.php?dept=vh-xh'>→ Xem trang Phòng Văn hóa - Xã hội</a></p>";
?>
