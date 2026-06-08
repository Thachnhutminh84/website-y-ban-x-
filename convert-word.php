<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

if (!isset($_FILES['word_file']) || $_FILES['word_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['use_server' => false, 'error' => 'File upload error']);
    exit();
}

$file = $_FILES['word_file'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if ($ext !== 'doc') {
    echo json_encode(['use_client' => true]);
    exit();
}

$tmpDir = sys_get_temp_dir() . '\\word_' . uniqid();
if (!@mkdir($tmpDir, 0777, true)) {
    echo json_encode(['use_server' => false, 'error' => 'Temp dir failed']);
    exit();
}

$safePath = $tmpDir . '\\input.doc';
if (!move_uploaded_file($file['tmp_name'], $safePath)) {
    echo json_encode(['use_server' => false, 'error' => 'Cannot save file']);
    @rmdir($tmpDir);
    exit();
}

$phpExe = 'C:\\xampp\\php\\php.exe';
$cliScript = __DIR__ . '\\convert-word-cli.php';
$cmd = '"' . $phpExe . '" "' . $cliScript . '" "' . $safePath . '" "' . $tmpDir . '"';

$output = @shell_exec($cmd . ' 2>&1');
$result = $output ? json_decode(trim($output), true) : null;

cleanup($tmpDir);

if ($result && isset($result['success']) && $result['success'] && isset($result['html'])) {
    $html = $result['html'];
    $wrapped = '<div class="word-document-content" data-doc-import="word">' . $html . '</div>';
    echo json_encode([
        'success' => true,
        'html' => $wrapped,
        'title' => extractTitle($html),
        'summary' => extractSummary($html)
    ]);
} else {
    $msg = $result && isset($result['error']) ? $result['error'] : 'Server COM không khả dụng';
    error_log("convert-word fail: " . $msg . " | output=" . substr($output ?? '', 0, 200));
    echo json_encode(['use_server' => false, 'error' => $msg]);
}

function cleanup($dir) {
    if (!is_dir($dir)) return;
    foreach (@glob("$dir\\*") as $f) { if (is_file($f)) @unlink($f); else cleanup($f); }
    @rmdir($dir);
}

function extractTitle($h) {
    if (preg_match('/<h[12][^>]*>(.*?)<\/h[12]>/i', $h, $m)) return trim(strip_tags($m[1]));
    if (preg_match('/<p[^>]*>\s*<b[^>]*>(.*?)<\/b>\s*<\/p>/i', $h, $m)) { $t = trim(strip_tags($m[1])); return $t ? mb_substr($t, 0, 160) : ''; }
    return '';
}
function extractSummary($h) {
    $t = trim(preg_replace('/\s+/', ' ', strip_tags($h)));
    return mb_strlen($t) > 200 ? mb_substr($t, 0, 200) . '...' : $t;
}
