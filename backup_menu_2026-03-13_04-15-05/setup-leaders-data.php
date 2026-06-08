<?php
header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';

echo "<h1>🏛️ Thiết lập dữ liệu lãnh đạo</h1>";

try {
    $conn = getDBConnection();
    
    // Đọc và thực thi file SQL
    $sql = file_get_contents('insert-leader-data-complete.sql');
    
    // Tách các câu lệnh SQL
    $statements = explode(';', $sql);
    
    $success = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            if ($conn->query($statement)) {
                $success++;
                echo "✅ Thực thi thành công: " . substr($statement, 0, 50) . "...<br>";
            } else {
                $errors++;
                echo "❌ Lỗi: " . $conn->error . "<br>";
                echo "SQL: " . substr($statement, 0, 100) . "...<br><br>";
            }
        }
    }
    
    echo "<hr>";
    echo "<h2>📊 Kết quả</h2>";
    echo "✅ Thành công: $success câu lệnh<br>";
    echo "❌ Lỗi: $errors câu lệnh<br>";
    
    // Kiểm tra dữ liệu đã thêm
    $result = $conn->query("SELECT COUNT(*) as total FROM leaders WHERE is_active = 1");
    $row = $result->fetch_assoc();
    echo "<br>👥 Tổng số lãnh đạo: " . $row['total'] . "<br>";
    
    // Hiển thị danh sách lãnh đạo
    echo "<h2>👥 Danh sách lãnh đạo</h2>";
    $result = $conn->query("SELECT name, position, display_order FROM leaders WHERE is_active = 1 ORDER BY display_order ASC");
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>STT</th><th>Họ tên</th><th>Chức vụ</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['display_order'] . "</td>";
        echo "<td><strong>" . htmlspecialchars($row['name']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($row['position']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Kiểm tra quá trình công tác
    $result = $conn->query("SELECT COUNT(*) as total FROM leader_work_history");
    $row = $result->fetch_assoc();
    echo "<br>📋 Tổng số bản ghi quá trình công tác: " . $row['total'] . "<br>";
    
    $conn->close();
    
    echo "<hr>";
    echo "<h2>🔗 Liên kết</h2>";
    echo "<a href='lanh-dao.php' style='display: inline-block; padding: 10px 20px; background: #c41e3a; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>👥 Xem trang lãnh đạo</a>";
    echo "<a href='chi-tiet-lanh-dao.php?id=1' style='display: inline-block; padding: 10px 20px; background: #27ae60; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>👤 Xem chi tiết lãnh đạo</a>";
    echo "<a href='index.php' style='display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>🏠 Về trang chủ</a>";
    
} catch (Exception $e) {
    echo "❌ Lỗi kết nối database: " . $e->getMessage();
}
?>