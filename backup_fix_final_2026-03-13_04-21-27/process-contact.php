<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

require_once 'config.php';
require_once 'contact-message-helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: lien-he.php');
    exit();
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$phone = trim((string) ($_POST['phone'] ?? ''));
$subject = trim((string) ($_POST['subject'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));
$priority = normalizeContactPriority($_POST['priority'] ?? 'normal');

$_SESSION['contact_old'] = [
    'name' => $name,
    'email' => $email,
    'phone' => $phone,
    'subject' => $subject,
    'message' => $message,
    'priority' => $priority
];

if ($name === '' || $email === '' || $subject === '' || $message === '') {
    $_SESSION['contact_error'] = 'Vui lòng nhập đầy đủ họ tên, email, tiêu đề và nội dung.';
    header('Location: lien-he.php');
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['contact_error'] = 'Địa chỉ email không hợp lệ.';
    header('Location: lien-he.php');
    exit();
}

$conn = getContactStorageConnection();

if (!$conn) {
    $_SESSION['contact_error'] = 'Không thể kết nối cơ sở dữ liệu để lưu tin nhắn.';
    header('Location: lien-he.php');
    exit();
}

if (!ensureContactsTableExists($conn)) {
    $_SESSION['contact_error'] = 'Không thể chuẩn bị khu vực lưu trữ tin nhắn.';
    $conn->close();
    header('Location: lien-he.php');
    exit();
}

$stmt = $conn->prepare('INSERT INTO contacts (name, email, phone, subject, message, status, priority) VALUES (?, ?, ?, ?, ?, ?, ?)');

if (!$stmt) {
    $_SESSION['contact_error'] = 'Không thể chuẩn bị thao tác lưu tin nhắn.';
    $conn->close();
    header('Location: lien-he.php');
    exit();
}

$status = 'new';
$stmt->bind_param('sssssss', $name, $email, $phone, $subject, $message, $status, $priority);
$saved = $stmt->execute();
$contactId = $saved ? (int) $conn->insert_id : 0;
$stmt->close();

if ($saved && $contactId > 0) {
    $ticketCode = buildContactTicketCode($contactId);
    $stmtTicket = $conn->prepare('UPDATE contacts SET ticket_code = ? WHERE id = ?');
    if ($stmtTicket) {
        $stmtTicket->bind_param('si', $ticketCode, $contactId);
        $saved = $stmtTicket->execute();
        $stmtTicket->close();
    } else {
        $saved = false;
    }
}

$conn->close();

if (!$saved) {
    $_SESSION['contact_error'] = 'Có lỗi xảy ra khi lưu tin nhắn. Vui lòng thử lại.';
    header('Location: lien-he.php');
    exit();
}

unset($_SESSION['contact_old']);
$_SESSION['contact_ticket_code'] = $ticketCode ?? '';
$_SESSION['contact_success'] = 'Tin nhắn của bạn đã được ghi nhận thành công. UBND xã sẽ phản hồi theo mã phiếu đã cấp.';

header('Location: lien-he.php?sent=1');
exit();
