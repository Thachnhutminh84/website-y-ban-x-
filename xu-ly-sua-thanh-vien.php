<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';

authRequireRole(['admin']);

// Kiểm tra CSRF token
SecurityHelper::validateRequest();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: quan-ly-danh-ba.php');
    exit();
}

$conn = getDBConnection();

// Lấy dữ liệu từ form
$id = (int)$_POST['id'];
$department_id = (int)$_POST['department_id'];
$name = trim($_POST['name']);
$position = trim($_POST['position']);
$phone = trim($_POST['phone']);
$email = trim($_POST['email'] ?? '');
$basic_salary = (float)($_POST['basic_salary'] ?? 2530000);
$salary_coefficient = (float)($_POST['salary_coefficient'] ?? 1.0);
$display_order = (int)($_POST['display_order'] ?? 0);
$status = $_POST['status'] ?? 'active';

// Validate
$errors = [];

if ($id <= 0) {
    $errors[] = 'ID không hợp lệ';
}

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

if (!empty($errors)) {
    $_SESSION['error'] = implode(', ', $errors);
    header("Location: sua-thanh-vien.php?id=$id");
    exit();
}

// Kiểm tra thành viên có tồn tại
$stmt = $conn->prepare("SELECT id FROM department_staff WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
if ($stmt->get_result()->num_rows == 0) {
    $_SESSION['error'] = 'Không tìm thấy thành viên';
    header('Location: quan-ly-danh-ba.php');
    exit();
}
$stmt->close();

// Update database
// Kiểm tra xem các cột basic_salary và salary_coefficient có tồn tại không
$columns_check = $conn->query("SHOW COLUMNS FROM department_staff LIKE 'basic_salary'");
$has_salary_columns = ($columns_check && $columns_check->num_rows > 0);

if ($has_salary_columns) {
    // Có cột lương - update đầy đủ
    $stmt = $conn->prepare("UPDATE department_staff SET department_id = ?, name = ?, position = ?, phone = ?, email = ?, basic_salary = ?, salary_coefficient = ?, display_order = ?, status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("issssddssi", $department_id, $name, $position, $phone, $email, $basic_salary, $salary_coefficient, $display_order, $status, $id);
} else {
    // Chưa có cột lương - update cơ bản
    $stmt = $conn->prepare("UPDATE department_staff SET department_id = ?, name = ?, position = ?, phone = ?, email = ?, display_order = ?, status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("issssisi", $department_id, $name, $position, $phone, $email, $display_order, $status, $id);
}

if ($stmt->execute()) {
    $_SESSION['success'] = "Đã cập nhật thông tin '$name' thành công!";
    header('Location: quan-ly-danh-ba.php');
} else {
    $_SESSION['error'] = 'Lỗi khi cập nhật thông tin.';
    header("Location: sua-thanh-vien.php?id=$id");
}

$stmt->close();
$conn->close();
exit();
?>