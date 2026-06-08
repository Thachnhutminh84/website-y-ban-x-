<?php
header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// Bắt đầu session
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once 'config.php';
require_once 'auth.php';

// Debug mode
$debug = isset($_GET['debug']) || isset($_POST['debug']);

try {
    // Kiểm tra quyền admin
    if (!authIsLoggedIn()) {
        echo json_encode([
            'success' => false, 
            'message' => 'Chưa đăng nhập. Vui lòng đăng nhập lại.',
            'debug' => $debug ? ['session_id' => session_id(), 'logged_in' => false] : null
        ]);
        exit;
    }

    if (!authHasPermission('manage_content')) {
        echo json_encode([
            'success' => false, 
            'message' => 'Không có quyền thực hiện thao tác này',
            'debug' => $debug ? [
                'user_id' => authCurrentUserId(),
                'role' => authCurrentRole()
            ] : null
        ]);
        exit;
    }

    // Chỉ chấp nhận POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'success' => false, 
            'message' => 'Method not allowed. Chỉ chấp nhận POST request.',
            'debug' => $debug ? ['method' => $_SERVER['REQUEST_METHOD']] : null
        ]);
        exit;
    }

    // Lấy dữ liệu JSON hoặc POST data
    $input = null;
    $rawInput = file_get_contents('php://input');
    
    if (!empty($rawInput)) {
        $input = json_decode($rawInput, true);
    }
    
    // Fallback to POST data nếu không có JSON
    if (!$input && !empty($_POST)) {
        $input = $_POST;
    }
    
    if (!$input) {
        echo json_encode([
            'success' => false, 
            'message' => 'Không có dữ liệu được gửi',
            'debug' => $debug ? [
                'raw_input' => $rawInput,
                'post_data' => $_POST
            ] : null
        ]);
        exit;
    }

    $filePath = isset($input['file_path']) ? trim($input['file_path']) : '';
    $fileName = isset($input['file_name']) ? trim($input['file_name']) : '';
    $action = isset($input['action']) ? $input['action'] : '';

    if (empty($filePath) || empty($fileName)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Đường dẫn file hoặc tên file không hợp lệ',
            'debug' => $debug ? [
                'file_path' => $filePath,
                'file_name' => $fileName,
                'input' => $input
            ] : null
        ]);
        exit;
    }

    if ($action !== 'save') {
        echo json_encode([
            'success' => false, 
            'message' => 'Hành động không hợp lệ. Chỉ chấp nhận action=save',
            'debug' => $debug ? ['action' => $action] : null
        ]);
        exit;
    }

    // Kiểm tra đường dẫn file có an toàn không (chỉ cho phép lưu file trong thư mục videos/)
    if (!str_starts_with($filePath, 'videos/')) {
        echo json_encode([
            'success' => false, 
            'message' => 'Chỉ được phép lưu file trong thư mục videos/',
            'debug' => $debug ? ['file_path' => $filePath] : null
        ]);
        exit;
    }

    // Kiểm tra file có tồn tại không
    if (!file_exists($filePath)) {
        echo json_encode([
            'success' => false, 
            'message' => 'File không tồn tại: ' . $fileName,
            'debug' => $debug ? [
                'file_path' => $filePath,
                'file_exists' => false,
                'current_dir' => getcwd()
            ] : null
        ]);
        exit;
    }

    try {
        $conn = getDBConnection();
        
        // Tạo bảng saved_files nếu chưa có
        $createTableSql = "CREATE TABLE IF NOT EXISTS saved_files (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            file_name VARCHAR(255) NOT NULL,
            file_size BIGINT DEFAULT NULL,
            file_type VARCHAR(50) DEFAULT NULL,
            saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_user_file (user_id, file_path)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $conn->query($createTableSql);
        
        // Lấy thông tin file
        $fileSize = filesize($filePath);
        $fileType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        // Lưu file vào danh sách yêu thích
        $userId = authCurrentUserId();
        $saveStmt = $conn->prepare("INSERT INTO saved_files (user_id, file_path, file_name, file_size, file_type) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE saved_at = NOW()");
        $saveStmt->bind_param("issis", $userId, $filePath, $fileName, $fileSize, $fileType);
        
        if ($saveStmt->execute()) {
            // Log hoạt động lưu
            $logMessage = "User ID $userId đã lưu file: " . $fileName . " (" . $filePath . ")";
            error_log($logMessage);
            
            echo json_encode([
                'success' => true,
                'message' => 'Đã lưu file thành công: ' . $fileName,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'file_size' => $fileSize,
                'file_type' => $fileType,
                'debug' => $debug ? [
                    'user_id' => $userId,
                    'timestamp' => date('Y-m-d H:i:s')
                ] : null
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Lỗi khi lưu file: ' . $saveStmt->error,
                'debug' => $debug ? ['sql_error' => $saveStmt->error] : null
            ]);
        }
        
        $saveStmt->close();
        $conn->close();
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
            'debug' => $debug ? [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ] : null
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi nghiêm trọng: ' . $e->getMessage(),
        'debug' => $debug ? [
            'exception' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ] : null
    ]);
}
?>