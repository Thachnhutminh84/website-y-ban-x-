<?php
require_once 'config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: index.php");
    exit();
}

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT file_path, title FROM news WHERE id = ? AND status = 'published'");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    header("Location: index.php");
    exit();
}

$row = $result->fetch_assoc();
$stmt->close();
$conn->close();

if (empty($row['file_path']) || !file_exists($row['file_path'])) {
    header("Location: index.php");
    exit();
}

$ext = strtolower(pathinfo($row['file_path'], PATHINFO_EXTENSION));

$mimeTypes = [
    'pdf'  => 'application/pdf',
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
];

$mime = $mimeTypes[$ext] ?? 'application/octet-stream';
$fileName = $row['title'] . '.' . $ext;

header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . filesize($row['file_path']));

readfile($row['file_path']);
exit();
