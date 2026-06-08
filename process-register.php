<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dang-ky.php');
    exit();
}

// Kiểm tra CSRF token
SecurityHelper::validateRequest();

$fullName = trim((string) ($_POST['full_name'] ?? ''));
$username = trim((string) ($_POST['username'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');
$confirmPassword = (string) ($_POST['confirm_password'] ?? '');
$requestedRole = authNormalizeRole($_POST['role'] ?? 'editor');
$userType = trim((string) ($_POST['user_type'] ?? ''));

// New fields
$phone = trim((string) ($_POST['phone'] ?? ''));
$ethnicity = trim((string) ($_POST['ethnicity'] ?? ''));
$address = trim((string) ($_POST['address'] ?? ''));
$religion = trim((string) ($_POST['religion'] ?? ''));
$organization = trim((string) ($_POST['organization'] ?? 'UBND XÃ LONG HIỆP - TỈNH VĨNH LONG'));
$department = trim((string) ($_POST['department'] ?? ''));
$position = trim((string) ($_POST['position'] ?? ''));
$educationLevel = trim((string) ($_POST['education_level'] ?? ''));
$dateOfBirth = trim((string) ($_POST['date_of_birth'] ?? ''));
$employeeId = trim((string) ($_POST['employee_id'] ?? ''));

$_SESSION['register_old'] = [
    'full_name' => $fullName,
    'username' => $username,
    'email' => $email,
    'role' => $requestedRole,
    'user_type' => $userType,
    'phone' => $phone,
    'ethnicity' => $ethnicity,
    'address' => $address,
    'religion' => $religion,
    'organization' => $organization,
    'department' => $department,
    'position' => $position,
    'education_level' => $educationLevel,
    'date_of_birth' => $dateOfBirth,
    'employee_id' => $employeeId
];

if ($fullName === '' || $username === '' || $email === '' || $password === '' || $confirmPassword === '' || $userType === '') {
    $_SESSION['register_error'] = 'Vui lòng nhập đầy đủ thông tin đăng ký.';
    header('Location: dang-ky.php');
    exit();
}

if (!in_array($userType, ['nguoi_dan', 'can_bo'])) {
    $_SESSION['register_error'] = 'Loại người dùng không hợp lệ.';
    header('Location: dang-ky.php');
    exit();
}

if (strlen($username) < 2 || strlen($username) > 50) {
    $_SESSION['register_error'] = 'Tên đăng nhập phải có từ 2-50 ký tự.';
    header('Location: dang-ky.php');
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['register_error'] = 'Địa chỉ email không hợp lệ.';
    header('Location: dang-ky.php');
    exit();
}

if (strlen($password) < 8) {
    $_SESSION['register_error'] = 'Mật khẩu phải có ít nhất 8 ký tự.';
    header('Location: dang-ky.php');
    exit();
}

// Kiểm tra mật khẩu mạnh
if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
    $_SESSION['register_error'] = 'Mật khẩu phải chứa ít nhất 1 chữ hoa, 1 chữ thường và 1 số.';
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

// Xác định role dựa trên user_type
// - Người dân: viewer (chỉ xem)
// - Cán bộ: editor (có quyền quản lý nội dung)
// - Admin tạo hoặc tài khoản đầu tiên: admin
if ($isFirstAccount) {
    $roleToCreate = 'admin';
} elseif (authIsAdmin() && $requestedRole === 'admin') {
    $roleToCreate = 'admin';
} elseif ($userType === 'can_bo') {
    $roleToCreate = 'editor';
} else {
    // Người dân chỉ có quyền xem
    $roleToCreate = 'viewer';
}

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

// Tài khoản đầu tiên hoặc admin tạo sẽ được duyệt tự động
// Các tài khoản khác phải chờ admin phê duyệt
$approvalStatus = ($isFirstAccount || authIsAdmin()) ? 'approved' : 'pending';
$approvedBy = authIsAdmin() ? authCurrentUserId() : null;
$approvedAt = ($isFirstAccount || authIsAdmin()) ? date('Y-m-d H:i:s') : null;

// Chuẩn bị câu lệnh INSERT với các trường mới
$stmtInsert = $conn->prepare('INSERT INTO users (username, password, full_name, email, phone, ethnicity, address, religion, organization, department, position, education_level, date_of_birth, employee_id, role, user_type, status, approval_status, approved_by, approved_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

if (!$stmtInsert) {
    $conn->close();
    $_SESSION['register_error'] = 'Không thể chuẩn bị thao tác tạo tài khoản.';
    header('Location: dang-ky.php');
    exit();
}

// Xử lý date_of_birth - chuyển thành NULL nếu rỗng
$dobValue = !empty($dateOfBirth) ? $dateOfBirth : null;

// Xử lý các giá trị có thể NULL
$phoneValue = !empty($phone) ? $phone : null;
$ethnicityValue = !empty($ethnicity) ? $ethnicity : null;
$addressValue = !empty($address) ? $address : null;
$religionValue = !empty($religion) ? $religion : null;
$departmentValue = !empty($department) ? $department : null;
$positionValue = !empty($position) ? $position : null;
$educationLevelValue = !empty($educationLevel) ? $educationLevel : null;
$employeeIdValue = !empty($employeeId) ? $employeeId : null;

$stmtInsert->bind_param('ssssssssssssssssssss', 
    $username, 
    $hashedPassword, 
    $fullName, 
    $email, 
    $phoneValue, 
    $ethnicityValue, 
    $addressValue, 
    $religionValue, 
    $organization, 
    $departmentValue, 
    $positionValue, 
    $educationLevelValue, 
    $dobValue, 
    $employeeIdValue, 
    $roleToCreate, 
    $userType, 
    $status, 
    $approvalStatus, 
    $approvedBy, 
    $approvedAt
);
$created = $stmtInsert->execute();
$stmtInsert->close();
$conn->close();

if (!$created) {
    $_SESSION['register_error'] = 'Không thể tạo tài khoản mới.';
    header('Location: dang-ky.php');
    exit();
}

unset($_SESSION['register_old']);

// Thông báo khác nhau tùy trạng thái phê duyệt
if ($approvalStatus === 'approved') {
    $_SESSION['register_success'] = $roleToCreate === 'admin'
        ? 'Đã tạo tài khoản quản trị viên mới.'
        : 'Đăng ký tài khoản biên tập thành công. Bạn có thể đăng nhập ngay.';
} else {
    $_SESSION['register_success'] = 'Đăng ký tài khoản thành công! Tài khoản của bạn đang chờ quản trị viên phê duyệt. Bạn sẽ nhận được thông báo qua email khi tài khoản được kích hoạt.';
}

header('Location: dang-ky.php');
exit();
