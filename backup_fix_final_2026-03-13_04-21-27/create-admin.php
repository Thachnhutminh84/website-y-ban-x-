<?php
require_once 'config.php';
require_once 'auth.php';

// Thông tin admin
$username = 'admin';
$password = 'Mi@131204';
$full_name = 'Quản trị viên';
$email = 'admin@longhiep.gov.vn';

// Kết nối database
$conn = getDBConnection();
authEnsureUsersTableExists($conn);

// Hash mật khẩu
$hashed_password = hashPassword($password);

// Kiểm tra xem admin đã tồn tại chưa
$check_sql = "SELECT id FROM users WHERE username = ?";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Cập nhật mật khẩu nếu admin đã tồn tại
    $update_sql = "UPDATE users SET password = ?, full_name = ?, email = ?, role = 'admin', status = 'active' WHERE username = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssss", $hashed_password, $full_name, $email, $username);
    
    if ($stmt->execute()) {
        echo "✅ Cập nhật tài khoản admin thành công!<br>";
    } else {
        echo "❌ Lỗi cập nhật: " . $stmt->error . "<br>";
    }
} else {
    // Thêm admin mới
    $insert_sql = "INSERT INTO users (username, password, full_name, email, role, status) VALUES (?, ?, ?, ?, 'admin', 'active')";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("ssss", $username, $hashed_password, $full_name, $email);
    
    if ($stmt->execute()) {
        echo "✅ Tạo tài khoản admin thành công!<br>";
    } else {
        echo "❌ Lỗi tạo tài khoản: " . $stmt->error . "<br>";
    }
}

echo "<br><strong>Thông tin đăng nhập:</strong><br>";
echo "Tài khoản: <strong>$username</strong><br>";
echo "Mật khẩu: <strong>$password</strong><br>";
echo "Quyền: <strong>admin</strong><br>";
echo "<br><a href='dang-nhap.php'>Đi đến trang đăng nhập</a>";

$stmt->close();
$conn->close();
?>
