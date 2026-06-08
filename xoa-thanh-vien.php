<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';

authRequireRole(['admin']);

$conn = getDBConnection();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['error'] = 'ID không hợp lệ';
    header('Location: quan-ly-danh-ba.php');
    exit();
}

// Lấy thông tin thành viên trước khi xóa
$stmt = $conn->prepare("SELECT name FROM department_staff WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = 'Không tìm thấy thành viên';
    header('Location: quan-ly-danh-ba.php');
    exit();
}

$member = $result->fetch_assoc();
$name = $member['name'];
$stmt->close();

// Xóa thành viên
$stmt = $conn->prepare("DELETE FROM department_staff WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Đã xóa thành viên '$name' thành công!";
} else {
    $_SESSION['error'] = 'Lỗi khi xóa thành viên.';
}

$stmt->close();
$conn->close();

header('Location: quan-ly-danh-ba.php');
exit();
?>