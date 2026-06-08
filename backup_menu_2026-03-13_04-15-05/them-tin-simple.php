<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: text/html; charset=utf-8');
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    echo "Chưa đăng nhập. <a href='dang-nhap.php'>Đăng nhập</a>";
    exit();
}

$category = isset($_GET['cat']) ? $_GET['cat'] : 'all';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Test Thêm tin</title>
</head>
<body>
    <h1>Trang thêm tin - TEST</h1>
    <p>Category: <?php echo htmlspecialchars($category); ?></p>
    <p>Session admin: <?php echo $_SESSION['admin'] ? 'Yes' : 'No'; ?></p>
</body>
</html>
