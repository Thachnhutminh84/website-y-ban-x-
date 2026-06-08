<?php
// Cấu hình kết nối database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ubnd_longhiep');

// Tạo kết nối
function getDBConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        // Kiểm tra kết nối
        if ($conn->connect_error) {
            die("Kết nối thất bại: " . $conn->connect_error);
        }
        
        // Set charset UTF-8
        $conn->set_charset("utf8mb4");
        
        return $conn;
    } catch (Exception $e) {
        die("Lỗi kết nối database: " . $e->getMessage());
    }
}

// Hàm tạo mật khẩu hash
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Hàm kiểm tra mật khẩu
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Hàm bảo vệ khỏi SQL Injection
function escapeString($conn, $string) {
    return $conn->real_escape_string($string);
}

// Hàm ghi log hoạt động
function logActivity($conn, $user_id, $action, $table_name = null, $record_id = null, $description = null) {
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, table_name, record_id, description, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ississ", $user_id, $action, $table_name, $record_id, $description, $ip_address);
    $stmt->execute();
    $stmt->close();
}
?>
