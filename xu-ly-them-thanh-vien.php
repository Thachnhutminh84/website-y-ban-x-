<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';

authRequireRole(['admin']);

// Kiểm tra CSRF token
SecurityHelper::validateRequest();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: them-thanh-vien.php');
    exit();
}

$conn = getDBConnection();

// Lấy dữ liệu từ form
$department_id = (int)$_POST['department_id'];
$name = trim($_POST['name']);
$position = trim($_POST['position']);
$phone = trim($_POST['phone']);
$email = trim($_POST['email'] ?? '');
$basic_salary = (float)($_POST['basic_salary'] ?? 5000000);
$salary_coefficient = (float)($_POST['salary_coefficient'] ?? 1.0);
$display_order = (int)($_POST['display_order'] ?? 0);
$status = $_POST['status'] ?? 'active';

// Validate
$errors = [];

if (empty($name)) {
    $errors[] = 'Họ và tên không được để trống';
}

if (empty($position)) {
    $errors[] = 'Chức vụ không được để trống';
}

if (empty($phone)) {
    $errors[] = 'Số điện thoại không được để trống';
}

if ($department_id <= 0) {
    $errors[] = 'Vui lòng chọn phòng ban';
}

// Kiểm tra phòng ban có tồn tại
$stmt = $conn->prepare("SELECT id, code FROM departments WHERE id = ?");
$stmt->bind_param("i", $department_id);
$stmt->execute();
$deptRow = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$deptRow) {
    $errors[] = 'Phòng ban không tồn tại';
}

$department_code = $deptRow['code'] ?? '';

if (!empty($errors)) {
    $_SESSION['error'] = implode(', ', $errors);
    header('Location: them-thanh-vien.php');
    exit();
}

// Detect column type
$colCheck = $conn->query("SHOW COLUMNS FROM department_staff LIKE 'department_code'");
$hasDeptCodeCol = ($colCheck && $colCheck->num_rows > 0);

// Nếu display_order = 0, tự động lấy số thứ tự tiếp theo
if ($display_order == 0) {
    if ($hasDeptCodeCol) {
        $result = $conn->query("SELECT COALESCE(MAX(display_order), 0) + 1 as next_order FROM department_staff WHERE department_code = '" . $conn->real_escape_string($department_code) . "'");
    } else {
        $result = $conn->query("SELECT COALESCE(MAX(display_order), 0) + 1 as next_order FROM department_staff WHERE department_id = $department_id");
    }
    $row = $result->fetch_assoc();
    $display_order = $row['next_order'];
}

// Insert vào database
$columns_check = $conn->query("SHOW COLUMNS FROM department_staff LIKE 'basic_salary'");
$has_salary_columns = ($columns_check && $columns_check->num_rows > 0);

if ($hasDeptCodeCol) {
    if ($has_salary_columns) {
        $stmt = $conn->prepare("INSERT INTO department_staff (department_code, name, position, phone, email, basic_salary, salary_coefficient, display_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssddis", $department_code, $name, $position, $phone, $email, $basic_salary, $salary_coefficient, $display_order, $status);
    } else {
        $stmt = $conn->prepare("INSERT INTO department_staff (department_code, name, position, phone, email, display_order, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssis", $department_code, $name, $position, $phone, $email, $display_order, $status);
    }
} else {
    if ($has_salary_columns) {
        $stmt = $conn->prepare("INSERT INTO department_staff (department_id, name, position, phone, email, basic_salary, salary_coefficient, display_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssddis", $department_id, $name, $position, $phone, $email, $basic_salary, $salary_coefficient, $display_order, $status);
    } else {
        $stmt = $conn->prepare("INSERT INTO department_staff (department_id, name, position, phone, email, display_order, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssis", $department_id, $name, $position, $phone, $email, $display_order, $status);
    }
}

if ($stmt->execute()) {
    $_SESSION['success'] = "Đã thêm thành viên '$name' thành công!";
    header('Location: quan-ly-danh-ba.php');
} else {
    $_SESSION['error'] = 'Lỗi khi thêm thành viên: ' . ($stmt->error ?: $conn->error);
    header('Location: them-thanh-vien.php');
}

$stmt->close();
$conn->close();
exit();
?>