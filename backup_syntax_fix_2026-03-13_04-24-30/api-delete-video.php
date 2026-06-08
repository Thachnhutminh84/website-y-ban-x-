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

// Debug mode - có thể bật để debug
$debug = isset($_GET['debug']) || isset($_POST['debug']);

try {
    // Kiểm tra quyền admin
    if (!authIsLoggedIn()) {
        echo json_encode([
            'success' => false, 
            'message' => 'Chưa đăng nhập. Vui lòng đăng nhập lại.',
            'debug' => $debug ? [
                'session_id' => session_id(),
                'session_data' => $_SESSION,
                'logged_in' => false
            ] : null
        ]);
        exit;
    }

    if (!authHasPermission('manage_content')) {
        echo json_encode([
            'success' => false, 
            'message' => 'Không có quyền thực hiện thao tác này',
            'debug' => $debug ? [
                'user_id' => authCurrentUserId(),
                'role' => authCurrentRole(),
                'has_permission' => false
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
                'post_data' => $_POST,
                'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'unknown'
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

    if ($action !== 'delete') {
        echo json_encode([
            'success' => false, 
            'message' => 'Hành động không hợp lệ. Chỉ chấp nhận action=delete',
            'debug' => $debug ? ['action' => $action, 'input' => $input] : null
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
        
        // Lấy thông tin video trước khi xóa
        $stmt = $conn->prepare("SELECT id, title, video_url, video_type FROM videos WHERE id = ? AND is_active = 1");
        $stmt->bind_param("i", $videoId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($video = $result->fetch_assoc()) {
            // Xóa video (soft delete - chỉ đặt is_active = 0)
            $updateStmt = $conn->prepare("UPDATE videos SET is_active = 0, updated_at = NOW() WHERE id = ?");
            $updateStmt->bind_param("i", $videoId);
            
            if ($updateStmt->execute()) {
                // Kiểm tra số dòng bị ảnh hưởng
                $affectedRows = $updateStmt->affected_rows;
                
                if ($affectedRows > 0) {
                    // Log hoạt động xóa
                    $userId = authCurrentUserId();
                    $logMessage = "User ID $userId đã xóa video: " . $video['title'] . " (ID: " . $video['id'] . ")";
                    error_log($logMessage);
                    
                    // Nếu là video local, có thể xóa file (tùy chọn)
                    $fileDeleted = false;
                    if ($video['video_type'] == 'local' && !empty($video['video_url']) && file_exists($video['video_url'])) {
                        // Uncomment dòng dưới nếu muốn xóa luôn file
                        // $fileDeleted = unlink($video['video_url']);
                    }
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Đã xóa video thành công: ' . $video['title'],
                        'video_id' => $videoId,
                        'video_title' => $video['title'],
                        'affected_rows' => $affectedRows,
                        'file_deleted' => $fileDeleted,
                        'debug' => $debug ? [
                            'user_id' => $userId,
                            'video_data' => $video,
                            'timestamp' => date('Y-m-d H:i:s')
                        ] : null
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Không có thay đổi nào được thực hiện. Video có thể đã bị xóa trước đó.',
                        'debug' => $debug ? ['affected_rows' => $affectedRows] : null
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Lỗi khi cập nhật database: ' . $updateStmt->error,
                    'debug' => $debug ? ['sql_error' => $updateStmt->error] : null
                ]);
            }
            
            $updateStmt->close();
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Video không tồn tại hoặc đã bị xóa trước đó',
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