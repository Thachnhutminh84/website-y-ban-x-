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

    if ($action !== 'delete') {
        echo json_encode([
            'success' => false, 
            'message' => 'Hành động không hợp lệ. Chỉ chấp nhận action=delete',
            'debug' => $debug ? ['action' => $action] : null
        ]);
        exit;
    }

    // Kiểm tra đường dẫn file có an toàn không (chỉ cho phép xóa trong thư mục videos/)
    if (!str_starts_with($filePath, 'videos/')) {
        echo json_encode([
            'success' => false, 
            'message' => 'Chỉ được phép xóa file trong thư mục videos/',
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

    // Kiểm tra quyền ghi file
    if (!is_writable($filePath)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Không có quyền xóa file này',
            'debug' => $debug ? [
                'file_path' => $filePath,
                'is_writable' => false,
                'file_perms' => substr(sprintf('%o', fileperms($filePath)), -4)
            ] : null
        ]);
        exit;
    }

    try {
        // Xóa file khỏi hệ thống
        if (unlink($filePath)) {
            // Xóa khỏi database nếu có
            $conn = getDBConnection();
            $dbUpdated = false;
            
            // Xóa khỏi bảng videos nếu có
            $result = $conn->query("SHOW TABLES LIKE 'videos'");
            if ($result && $result->num_rows > 0) {
                $stmt = $conn->prepare("UPDATE videos SET is_active = 0, updated_at = NOW() WHERE video_url = ?");
                $stmt->bind_param("s", $filePath);
                if ($stmt->execute() && $stmt->affected_rows > 0) {
                    $dbUpdated = true;
                }
                $stmt->close();
            }
            
            // Xóa khỏi bảng saved_files nếu có
            $result = $conn->query("SHOW TABLES LIKE 'saved_files'");
            if ($result && $result->num_rows > 0) {
                $stmt = $conn->prepare("DELETE FROM saved_files WHERE file_path = ?");
                $stmt->bind_param("s", $filePath);
                $stmt->execute();
                $stmt->close();
            }
            
            $conn->close();
            
            // Log hoạt động xóa
            $userId = authCurrentUserId();
            $logMessage = "User ID $userId đã xóa file: " . $fileName . " (" . $filePath . ")";
            error_log($logMessage);
            
            echo json_encode([
                'success' => true,
                'message' => 'Đã xóa file thành công: ' . $fileName,
                'file_path' => $filePath,
                'file_name' => $fileName,
                'db_updated' => $dbUpdated,
                'debug' => $debug ? [
                    'user_id' => $userId,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'db_updated' => $dbUpdated
                ] : null
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Không thể xóa file. Kiểm tra quyền truy cập hoặc file đang được sử dụng.',
                'debug' => $debug ? [
                    'file_path' => $filePath,
                    'last_error' => error_get_last()
                ] : null
            ]);
        }
        
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