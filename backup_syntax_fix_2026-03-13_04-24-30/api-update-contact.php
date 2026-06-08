<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';
require_once 'contact-message-helper.php';

if (!authIsAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$contactId = (int) ($_POST['id'] ?? 0);
$newStatus = normalizeContactStatus($_POST['status'] ?? '');
$adminNote = trim((string) ($_POST['note'] ?? ''));

if ($contactId <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
    exit;
}

$conn = getContactStorageConnection();
if (!$conn || !ensureContactsTableExists($conn)) {
    echo json_encode(['success' => false, 'message' => 'Không thể kết nối database']);
    exit;
}

$resolvedAt = ($newStatus === 'resolved') ? 'NOW()' : 'NULL';
$sql = "UPDATE contacts SET status = ?, admin_note = ?, resolved_at = {$resolvedAt} WHERE id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Lỗi chuẩn bị truy vấn']);
    exit;
}

$stmt->bind_param('ssi', $newStatus, $adminNote, $contactId);
$success = $stmt->execute();
$stmt->close();
$conn->close();

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Cập nhật trạng thái thành công']);
} else {
    echo json_encode(['success' => false, 'message' => 'Không thể cập nhật trạng thái']);
}
?>