<?php
// Test script để kiểm tra SQL lãnh đạo
require_once 'config.php';

echo "<h2>Test SQL Lãnh đạo</h2>";

try {
    // Kiểm tra bảng leaders có tồn tại không
    $stmt = $pdo->query("SHOW TABLES LIKE 'leaders'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: red;'>❌ Bảng 'leaders' chưa tồn tại. Hãy chạy create-leaders-table.sql trước.</p>";
        exit;
    }
    
    // Kiểm tra cấu trúc bảng
    echo "<h3>Cấu trúc bảng leaders:</h3>";
    $stmt = $pdo->query("DESCRIBE leaders");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Tên cột</th><th>Kiểu dữ liệu</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>{$col['Field']}</td>";
        echo "<td>{$col['Type']}</td>";
        echo "<td>{$col['Null']}</td>";
        echo "<td>{$col['Key']}</td>";
        echo "<td>{$col['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p><strong>Tổng số cột:</strong> " . count($columns) . "</p>";
    
    // Kiểm tra dữ liệu hiện tại
    echo "<h3>Dữ liệu hiện tại:</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM leaders");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Số lượng lãnh đạo trong database: <strong>{$count['total']}</strong></p>";
    
    if ($count['total'] > 0) {
        $stmt = $pdo->query("SELECT id, name, position FROM leaders ORDER BY display_order");
        $leaders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<ul>";
        foreach ($leaders as $leader) {
            echo "<li>ID: {$leader['id']} - {$leader['name']} - {$leader['position']}</li>";
        }
        echo "</ul>";
    }
    
    echo "<h3>Hướng dẫn:</h3>";
    echo "<ol>";
    echo "<li>Nếu bảng chưa có dữ liệu, hãy chạy file <code>insert-leader-data-complete.sql</code> trong phpMyAdmin</li>";
    echo "<li>Kiểm tra xem có lỗi 'Column count doesn't match value count' không</li>";
    echo "<li>Nếu có lỗi, kiểm tra lại số cột và số giá trị trong câu INSERT</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi: " . $e->getMessage() . "</p>";
}
?>