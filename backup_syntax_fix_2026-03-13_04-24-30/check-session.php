<?php
session_start();
require_once 'auth.php';
echo "<h2>Kiểm tra Session</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (authIsLoggedIn()) {
    echo "<p style='color: green; font-size: 20px;'>✓ Đã đăng nhập với quyền: <strong>" . htmlspecialchars(authRoleLabel(authCurrentRole()), ENT_QUOTES, 'UTF-8') . "</strong></p>";
    echo "<p>Tên hiển thị: <strong>" . htmlspecialchars(authDisplayName(), ENT_QUOTES, 'UTF-8') . "</strong></p>";
} else {
    echo "<p style='color: red; font-size: 20px;'>✗ Bạn chưa đăng nhập</p>";
    echo "<p><a href='dang-nhap.php'>Đăng nhập ngay</a></p>";
}
?>
