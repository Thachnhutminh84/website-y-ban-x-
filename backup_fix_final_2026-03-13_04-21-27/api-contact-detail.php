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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$contactId = (int) ($_GET['id'] ?? 0);
if ($contactId <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
    exit;
}

$conn = getContactStorageConnection();
if (!$conn || !ensureContactsTableExists($conn)) {
    echo json_encode(['success' => false, 'message' => 'Không thể kết nối database']);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM contacts WHERE id = ? LIMIT 1");
if (!$stmt) {
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Lỗi chuẩn bị truy vấn']);
    exit;
}

$stmt->bind_param('i', $contactId);
if (!$stmt->execute()) {
    $stmt->close();
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Lỗi thực thi truy vấn']);
    exit;
}

$result = $stmt->get_result();
$contact = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (!$contact) {
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy liên hệ']);
    exit;
}

echo json_encode([
    'success' => true,
    'contact' => [
        'id' => (int) $contact['id'],
        'ticket_code' => $contact['ticket_code'],
        'name' => $contact['name'],
        'email' => $contact['email'],
        'phone' => $contact['phone'],
        'subject' => $contact['subject'],
        'message' => $contact['message'],
        'status' => $contact['status'],
        'priority' => $contact['priority'],
        'admin_note' => $contact['admin_note'],
        'created_at' => $contact['created_at'],
        'updated_at' => $contact['updated_at']
    ]
], JSON_UNESCAPED_UNICODE);
?>