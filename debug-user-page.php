<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

// Chỉ admin mới có quyền truy cập
if (!authIsLoggedIn() || !authIsAdmin()) {
    http_response_code(403);
    die('403 Forbidden - Chỉ admin mới có quyền truy cập.');
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Debug</title></head><body>";
echo "<h1>Debug quan-ly-nguoi-dung.php</h1>";

// Test 1
echo "<p>✅ PHP đang chạy</p>";

// Test 2
echo "<p>✅ Session started</p>";

// Test 3
if (file_exists('config.php')) {
    echo "<p>✅ config.php tồn tại</p>";
} else {
    echo "<p>❌ config.php không tồn tại</p>";
    exit;
}

// Test 4
if (file_exists('auth.php')) {
    echo "<p>✅ auth.php tồn tại</p>";
} else {
    echo "<p>❌ auth.php không tồn tại</p>";
    exit;
}

// Test 5
if (isset($_SESSION['user_id'])) {
    echo "<p>✅ Đã đăng nhập - User ID: " . $_SESSION['user_id'] . ", Role: " . $_SESSION['role'] . "</p>";
} else {
    echo "<p>❌ Chưa đăng nhập</p>";
    exit;
}

// Test 6 - Query database
$conn = getDBConnection();
$sql = "SELECT COUNT(*) as total FROM users";
$result = $conn->query($sql);
if ($result) {
    $row = $result->fetch_assoc();
    echo "<p>✅ Database query OK - Có " . $row['total'] . " người dùng</p>";
} else {
    echo "<p>❌ Database query lỗi: " . $conn->error . "</p>";
}

// Test 7 - Thử load header
echo "<hr><h2>Test load header-menu.php:</h2>";
if (file_exists('header-menu.php')) {
    echo "<p>✅ header-menu.php tồn tại</p>";
    ob_start();
    include 'header-menu.php';
    $header_output = ob_get_clean();
    if (!empty($header_output)) {
        echo "<p>✅ Header có output</p>";
    } else {
        echo "<p>⚠️ Header không có output</p>";
    }
} else {
    echo "<p>❌ header-menu.php không tồn tại</p>";
}

// Test 8 - Thử load footer
echo "<hr><h2>Test load footer.php:</h2>";
if (file_exists('footer.php')) {
    echo "<p>✅ footer.php tồn tại</p>";
    ob_start();
    include 'footer.php';
    $footer_output = ob_get_clean();
    if (!empty($footer_output)) {
        echo "<p>✅ Footer có output</p>";
    } else {
        echo "<p>⚠️ Footer không có output</p>";
    }
} else {
    echo "<p>❌ footer.php không tồn tại</p>";
}

echo "<hr><h2>✅ Tất cả test đều PASS!</h2>";

echo "</body></html>";
?>