<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dang-nhap.php');
    exit();
}

// Kiểm tra CSRF token
SecurityHelper::validateRequest();

// Lấy thông tin từ form
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Kiểm tra dữ liệu đầu vào
if (empty($username) || empty($password)) {
    $_SESSION['login_error'] = "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!";
    header("Location: dang-nhap.php?error=1");
    exit();
}

// Rate limiting - chống brute force
$rateLimit = checkRateLimit('login_' . $username);
if (is_array($rateLimit) && isset($rateLimit['allowed']) && $rateLimit['allowed'] === false) {
    $_SESSION['login_error'] = $rateLimit['message'];
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

// Kiểm tra trạng thái phê duyệt
$approvalStatus = $user['approval_status'] ?? 'approved';
if ($approvalStatus === 'pending') {
    $conn->close();
    $_SESSION['login_error'] = "Tài khoản của bạn đang chờ quản trị viên phê duyệt. Vui lòng liên hệ admin để được hỗ trợ.";
    header("Location: dang-nhap.php?error=1");
    exit();
}

if ($approvalStatus === 'rejected') {
    $rejectionReason = $user['rejection_reason'] ?? 'Không rõ lý do';
    $conn->close();
    $_SESSION['login_error'] = "Tài khoản của bạn đã bị từ chối. Lý do: " . htmlspecialchars($rejectionReason, ENT_QUOTES, 'UTF-8');
    header("Location: dang-nhap.php?error=1");
    exit();
}

if (!verifyPassword($password, $user['password'])) {
    $conn->close();
    $_SESSION['login_error'] = "Tên đăng nhập hoặc mật khẩu không đúng!";
    header("Location: dang-nhap.php?error=1");
    exit();
}

// Đăng nhập thành công - regenerate session ID để chống session fixation
session_regenerate_id(true);

authSetUserSession($user);
authUpdateLastLogin($conn, (int) $user['id']);
$conn->close();

header("Location: tin-tuc.php");
exit();
?>