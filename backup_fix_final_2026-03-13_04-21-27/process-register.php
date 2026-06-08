<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dang-ky.php');
    exit();
}

$fullName = trim((string) ($_POST['full_name'] ?? ''));
$username = trim((string) ($_POST['username'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');
$confirmPassword = (string) ($_POST['confirm_password'] ?? '');
$requestedRole = authNormalizeRole($_POST['role'] ?? 'editor');

$_SESSION['register_old'] = [
    'full_name' => $fullName,
    'username' => $username,
    'email' => $email,
    'role' => $requestedRole
];

if ($fullName === '' || $username === '' || $email === '' || $password === '' || $confirmPassword === '') {
    $_SESSION['register_error'] = 'Vui lòng nhập đầy đủ thông tin đăng ký.';
    header('Location: dang-ky.php');
    exit();
}

if (!preg_match('/^[a-zA-Z0-9_.-]{3,50}$/', $username)) {
    $_SESSION['register_error'] = 'Tên đăng nhập chỉ được chứa chữ, số, dấu gạch dưới, dấu chấm hoặc gạch ngang.';
    header('Location: dang-ky.php');
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['register_error'] = 'Địa chỉ email không hợp lệ.';
    header('Location: dang-ky.php');
    exit();
}

if (strlen($password) < 6) {
    $_SESSION['register_error'] = 'Mật khẩu phải có ít nhất 6 ký tự.';
    header('Location: dang-ky.php');
    exit();
}

if ($password !== $confirmPassword) {
    $_SESSION['register_error'] = 'Mật khẩu nhập lại không khớp.';
    header('Location: dang-ky.php');
    exit();
}

$conn = getDBConnection();
if (!authEnsureUsersTableExists($conn)) {
    $_SESSION['register_error'] = 'Không thể chuẩn bị bảng tài khoản.';
    header('Location: dang-ky.php');
    exit();
}

$resultCount = $conn->query('SELECT COUNT(*) AS total FROM users');
$countRow = $resultCount ? $resultCount->fetch_assoc() : ['total' => 0];
$isFirstAccount = (int) ($countRow['total'] ?? 0) === 0;

$roleToCreate = $isFirstAccount ? 'admin' : (authIsAdmin() && $requestedRole === 'admin' ? 'admin' : 'editor');

$stmtCheck = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
if (!$stmtCheck) {
    $conn->close();
    $_SESSION['register_error'] = 'Không thể chuẩn bị kiểm tra trùng tài khoản.';
    header('Location: dang-ky.php');
    exit();
}

$stmtCheck->bind_param('ss', $username, $email);
$stmtCheck->execute();
$exists = $stmtCheck->get_result()->fetch_assoc();
$stmtCheck->close();

if ($exists) {
    $conn->close();
    $_SESSION['register_error'] = 'Tên đăng nhập hoặc email đã tồn tại.';
    header('Location: dang-ky.php');
    exit();
}

$hashedPassword = hashPassword($password);
$status = 'active';
$stmtInsert = $conn->prepare('INSERT INTO users (username, password, full_name, email, role, status) VALUES (?, ?, ?, ?, ?, ?)');

if (!$stmtInsert) {
    $conn->close();
    $_SESSION['register_error'] = 'Không thể chuẩn bị thao tác tạo tài khoản.';
    header('Location: dang-ky.php');
    exit();
}

$stmtInsert->bind_param('ssssss', $username, $hashedPassword, $fullName, $email, $roleToCreate, $status);
$created = $stmtInsert->execute();
$stmtInsert->close();
$conn->close();

if (!$created) {
    $_SESSION['register_error'] = 'Không thể tạo tài khoản mới.';
    header('Location: dang-ky.php');
    exit();
}

unset($_SESSION['register_old']);
$_SESSION['register_success'] = $roleToCreate === 'admin'
    ? 'Đã tạo tài khoản quản trị viên mới.'
    : 'Đăng ký tài khoản biên tập thành công. Bạn có thể đăng nhập ngay.';

header('Location: dang-ky.php');
exit();
