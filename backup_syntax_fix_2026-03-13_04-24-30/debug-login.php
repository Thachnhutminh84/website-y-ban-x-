<?php
// File debug để kiểm tra đăng nhập
require_once 'config.php';

echo "<h2>Debug Đăng Nhập</h2>";

// Test kết nối database
echo "<h3>1. Kiểm tra kết nối database:</h3>";
try {
    $conn = getDBConnection();
    echo "✅ Kết nối database thành công!<br>";
} catch (Exception $e) {
    echo "❌ Lỗi kết nối: " . $e->getMessage() . "<br>";
    exit;
}

// Test lấy thông tin user
echo "<h3>2. Kiểm tra thông tin user trong database:</h3>";
$username = 'admin';
$sql = "SELECT id, username, password, full_name, role, status FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "✅ Tìm thấy user: <strong>" . $user['username'] . "</strong><br>";
    echo "- ID: " . $user['id'] . "<br>";
    echo "- Họ tên: " . $user['full_name'] . "<br>";
    echo "- Role: " . $user['role'] . "<br>";
    echo "- Status: " . $user['status'] . "<br>";
    echo "- Password hash: " . substr($user['password'], 0, 30) . "...<br>";
} else {
    echo "❌ Không tìm thấy user admin<br>";
    exit;
}

// Test mật khẩu
echo "<h3>3. Kiểm tra mật khẩu:</h3>";
$test_password = 'Mi@131204';
echo "Mật khẩu test: <strong>$test_password</strong><br>";

// Tạo hash mới
$new_hash = password_hash($test_password, PASSWORD_DEFAULT);
echo "Hash mới tạo: " . substr($new_hash, 0, 30) . "...<br>";

// Kiểm tra hash hiện tại
$current_hash = $user['password'];
echo "Hash trong DB: " . substr($current_hash, 0, 30) . "...<br>";

// Test verify
if (password_verify($test_password, $current_hash)) {
    echo "✅ <strong style='color:green;'>Mật khẩu ĐÚNG! Có thể đăng nhập được.</strong><br>";
} else {
    echo "❌ <strong style='color:red;'>Mật khẩu SAI! Cần cập nhật hash.</strong><br>";
    
    // Cập nhật hash mới
    echo "<h3>4. Cập nhật mật khẩu mới:</h3>";
    $update_sql = "UPDATE users SET password = ?, role = 'admin', status = 'active' WHERE username = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ss", $new_hash, $username);
    
    if ($update_stmt->execute()) {
        echo "✅ <strong style='color:green;'>Đã cập nhật mật khẩu thành công!</strong><br>";
        echo "Bây giờ bạn có thể đăng nhập với:<br>";
        echo "- Tài khoản: <strong>admin</strong><br>";
        echo "- Mật khẩu: <strong>Mi@131204</strong><br>";
    } else {
        echo "❌ Lỗi cập nhật: " . $update_stmt->error . "<br>";
    }
    $update_stmt->close();
}

$stmt->close();
$conn->close();

echo "<hr>";
echo "<a href='dang-nhap.php' style='background:#c41e3a; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Đi đến trang đăng nhập</a>";
?>
