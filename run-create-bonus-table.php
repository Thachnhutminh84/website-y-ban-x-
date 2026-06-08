<?php
require_once 'config.php';

echo "<h2>Tạo bảng hr_bonuses</h2>";

$sql = file_get_contents('create-bonus-table.sql');

if ($conn->multi_query($sql)) {
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());
    
    echo "<p style='color: green;'>✓ Tạo bảng hr_bonuses thành công!</p>";
} else {
    echo "<p style='color: red;'>✗ Lỗi: " . $conn->error . "</p>";
}

$conn->close();
?>
<p><a href="quan-ly-luong-thuong.php">← Quay lại quản lý lương thưởng</a></p>
