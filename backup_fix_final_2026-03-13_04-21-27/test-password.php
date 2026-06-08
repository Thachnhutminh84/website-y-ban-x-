<?php
// Script tạo mật khẩu hash cho Mi@131204

$password = 'Mi@131204';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "<h2>Thông tin mật khẩu</h2>";
echo "<p><strong>Mật khẩu gốc:</strong> $password</p>";
echo "<p><strong>Mật khẩu hash:</strong> $hash</p>";

echo "<hr>";
echo "<h3>Chạy SQL này trong phpMyAdmin:</h3>";
echo "<textarea style='width:100%; height:150px; font-family:monospace;'>";
echo "USE ubnd_longhiep;\n\n";
echo "UPDATE users SET password = '$hash' WHERE username = 'admin';\n\n";
echo "SELECT id, username, full_name, email, role FROM users WHERE username = 'admin';";
echo "</textarea>";

echo "<hr>";
echo "<h3>Hoặc dùng cách đơn giản hơn:</h3>";
echo "<p>Chạy script này để cập nhật trực tiếp vào database:</p>";

// Kết nối và cập nhật
require_once 'config.php';
$conn = getDBConnection();

$username = 'admin';
$update_sql = "UPDATE users SET password = ? WHERE username = ?";
$stmt = $conn->prepare($update_sql);
$stmt->bind_param("ss", $hash, $username);

if ($stmt->execute()) {
    echo "<div style='background:#d4edda; padding:15px; border-radius:5px; color:#155724;'>";
    echo "✅ <strong>Cập nhật mật khẩu thành công!</strong><br>";
    echo "Bây giờ bạn có thể đăng nhập với:<br>";
    echo "- Tài khoản: <strong>admin</strong><br>";
    echo "- Mật khẩu: <strong>Mi@131204</strong>";
    echo "</div>";
    echo "<br><a href='dang-nhap.php' style='background:#c41e3a; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Đi đến trang đăng nhập</a>";
} else {
    echo "<div style='background:#f8d7da; padding:15px; border-radius:5px; color:#721c24;'>";
    echo "❌ Lỗi: " . $stmt->error;
    echo "</div>";
}

$stmt->close();
$conn->close();
?>
