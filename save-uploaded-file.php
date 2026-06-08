<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['file'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit();
}

$file = $_FILES['file'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'Upload error: ' . $file['error']]);
    exit();
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['pdf', 'doc', 'docx'];

if (!in_array($ext, $allowed)) {
    echo json_encode(['success' => false, 'error' => 'Invalid file type']);
    exit();
}

$upload_dir = 'uploads/news/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$new_name = 'news_file_' . time() . '_' . random_int(1000, 9999) . '.' . $ext;
$upload_path = $upload_dir . $new_name;

if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
    echo json_encode(['success' => false, 'error' => 'Failed to save file']);
    exit();
}

echo json_encode([
    'success' => true,
    'path' => $upload_path,
    'name' => $file['name']
]);
