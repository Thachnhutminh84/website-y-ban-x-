<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dang-nhap.php');
    exit();
}

// Lấy thông tin từ form
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Kiểm tra dữ liệu đầu vào
if (empty($username) || empty($password)) {
    $_SESSION['login_error'] = "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!";
    header("Location: dang-nhap.php?error=1");
    exit();
}

$conn = getDBConnection();
if (!authEnsureUsersTableExists($conn)) {
    $_SESSION['login_error'] = "Không thể chuẩn bị bảng tài khoản người dùng.";
    header("Location: dang-nhap.php?error=1");
    exit();
}

$user = authFindUserByUsername($conn, $username);

if (!$user) {
    $conn->close();
    $_SESSION['login_error'] = "Tên đăng nhập hoặc mật khẩu không đúng!";
    header("Location: dang-nhap.php?error=1");
    exit();
}

if (($user['status'] ?? 'inactive') !== 'active') {
    $conn->close();
    $_SESSION['login_error'] = "Tài khoản này đang bị khóa.";
    header("Location: dang-nhap.php?error=1");
    exit();
}

if (!verifyPassword($password, $user['password'])) {
    $conn->close();
    $_SESSION['login_error'] = "Tên đăng nhập hoặc mật khẩu không đúng!";
    header("Location: dang-nhap.php?error=1");
    exit();
}

authSetUserSession($user);
authUpdateLastLogin($conn, (int) $user['id']);
$conn->close();

header("Location: tin-tuc.php");
exit();
?>
