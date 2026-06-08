<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

echo "<h2>Sửa lỗi database cho sua-danh-gia.php</h2>";

// Đọc và thực thi SQL từ file create-performance-tables.sql
$sql_file = 'create-performance-tables.sql';

if (!file_exists($sql_file)) {
    echo "<p style='color: red;'>Không tìm thấy file $sql_file</p>";
    exit();
}

$sql_content = file_get_contents($sql_file);
$sql_statements = explode(';', $sql_content);

$success_count = 0;
$error_count = 0;

foreach ($sql_statements as $sql) {
    $sql = trim($sql);
    if (empty($sql) || strpos($sql, '--') === 0) {
        continue;
    }
    
    if ($conn->query($sql)) {
        $success_count++;
        echo "<p style='color: green;'>✓ Thực thi thành công: " . substr($sql, 0, 50) . "...</p>";
    } else {
        $error_count++;
        echo "<p style='color: red;'>✗ Lỗi: " . $conn->error . "</p>";
        echo "<p style='color: gray;'>SQL: " . substr($sql, 0, 100) . "...</p>";
    }
}

echo "<hr>";
echo "<p><strong>Kết quả:</strong></p>";
echo "<p>✓ Thành công: $success_count câu lệnh</p>";
echo "<p>✗ Lỗi: $error_count câu lệnh</p>";

// Tạo chu kỳ đánh giá mẫu
echo "<h3>Tạo chu kỳ đánh giá mẫu</h3>";

$period_sql = "INSERT IGNORE INTO hr_evaluation_periods (name, period_type, start_date, end_date, deadline, status) VALUES 
('Quý 1/2024', 'quarterly', '2024-01-01', '2024-03-31', '2024-04-15', 'active'),
('Quý 2/2024', 'quarterly', '2024-04-01', '2024-06-30', '2024-07-15', 'active'),
('Quý 3/2024', 'quarterly', '2024-07-01', '2024-09-30', '2024-10-15', 'active'),
('Quý 4/2024', 'quarterly', '2024-10-01', '2024-12-31', '2025-01-15', 'active')";

if ($conn->query($period_sql)) {
    echo "<p style='color: green;'>✓ Đã tạo chu kỳ đánh giá mẫu</p>";
} else {
    echo "<p style='color: red;'>✗ Lỗi tạo chu kỳ: " . $conn->error . "</p>";
}

// Kiểm tra bảng hr_employees
$check_emp = "SELECT COUNT(*) as count FROM hr_employees";
$result = $conn->query($check_emp);
if ($result) {
    $row = $result->fetch_assoc();
    echo "<p>Số nhân viên trong hệ thống: " . $row['count'] . "</p>";
    
    if ($row['count'] == 0) {
        echo "<p style='color: orange;'>⚠️ Chưa có nhân viên nào. Hãy thêm nhân viên trước khi tạo đánh giá.</p>";
        echo "<p><a href='them-nhan-su.php'>→ Thêm nhân viên</a></p>";
    }
} else {
    echo "<p style='color: red;'>✗ Không thể kiểm tra bảng nhân viên: " . $conn->error . "</p>";
}

echo "<hr>";
echo "<p><strong>Bây giờ bạn có thể truy cập:</strong></p>";
echo "<p><a href='sua-danh-gia.php?id=1&employee_id=1'>→ Test sua-danh-gia.php</a></p>";
echo "<p><a href='quan-ly-danh-gia.php'>→ Quản lý đánh giá</a></p>";
echo "<p><a href='create-sample-evaluations.php'>→ Tạo dữ liệu đánh giá mẫu</a></p>";

$conn->close();
?>