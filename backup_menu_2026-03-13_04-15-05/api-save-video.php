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

    $videoId = isset($input['video_id']) ? (int)$input['video_id'] : 0;
    $action = isset($input['action']) ? $input['action'] : '';

    if ($videoId <= 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'ID video không hợp lệ',
            'debug' => $debug ? ['video_id' => $videoId, 'input' => $input] : null
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

    try {
        $conn = getDBConnection();
        
        // Kiểm tra bảng videos có tồn tại không
        $result = $conn->query("SHOW TABLES LIKE 'videos'");
        if (!$result || $result->num_rows == 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Hệ thống video chưa được khởi tạo. Vui lòng chạy setup trước.'
            ]);
            exit;
        }
        
        // Kiểm tra video có tồn tại không
        $stmt = $conn->prepare("SELECT id, title, video_url, video_type FROM videos WHERE id = ? AND is_active = 1");
        $stmt->bind_param("i", $videoId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($video = $result->fetch_assoc()) {
            // Tạo bảng saved_videos nếu chưa có
            $createTableSql = "CREATE TABLE IF NOT EXISTS saved_videos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                video_id INT NOT NULL,
                saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_user_video (user_id, video_id),
                FOREIGN KEY (video_id) REFERENCES videos(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            $conn->query($createTableSql);
            
            // Lưu video vào danh sách yêu thích
            $userId = authCurrentUserId();
            $saveStmt = $conn->prepare("INSERT INTO saved_videos (user_id, video_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE saved_at = NOW()");
            $saveStmt->bind_param("ii", $userId, $videoId);
            
            if ($saveStmt->execute()) {
                // Log hoạt động lưu
                $logMessage = "User ID $userId đã lưu video: " . $video['title'] . " (ID: " . $video['id'] . ")";
                error_log($logMessage);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Đã lưu video thành công: ' . $video['title'],
                    'video_id' => $videoId,
                    'video_title' => $video['title'],
                    'debug' => $debug ? [
                        'user_id' => $userId,
                        'video_data' => $video,
                        'timestamp' => date('Y-m-d H:i:s')
                    ] : null
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Lỗi khi lưu video: ' . $saveStmt->error,
                    'debug' => $debug ? ['sql_error' => $saveStmt->error] : null
                ]);
            }
            
            $saveStmt->close();
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Video không tồn tại hoặc đã bị xóa',
                'debug' => $debug ? ['video_id' => $videoId] : null
            ]);
        }
        
        $stmt->close();
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