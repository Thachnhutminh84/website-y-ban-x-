<?php
header("Content-Type: application/json; charset=utf-8");
session_start();
require_once 'config.php';
require_once 'auth.php';

// Kiểm tra quyền admin
if (!authIsLoggedIn() || !authHasPermission('manage_content')) {
    echo json_encode(['success' => false, 'message' => 'Khong co quyen thuc hien'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Chỉ chấp nhận POST và GET request
if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'GET'])) {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Lấy ID từ GET hoặc POST
$mediaId = 0;
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $mediaId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
} else {
    $input = json_decode(file_get_contents('php://input'), true);
    $mediaId = isset($input['id']) ? (int)$input['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
}

if ($mediaId <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID media khong hop le'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $conn = getDBConnection();
    
    // Kiểm tra bảng media có tồn tại không
    $result = $conn->query("SHOW TABLES LIKE 'media'");
    if (!$result || $result->num_rows == 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Bang media chua duoc tao'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Lấy thông tin media trước khi xóa
    $stmt = $conn->prepare("SELECT id, file_name, file_path FROM media WHERE id = ?");
    $stmt->bind_param("i", $mediaId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($media = $result->fetch_assoc()) {
        // Xóa file khỏi server nếu tồn tại
        $fileDeleted = false;
        if (!empty($media['file_path']) && file_exists($media['file_path'])) {
            $fileDeleted = unlink($media['file_path']);
        }
        
        // Xóa record khỏi database
        $deleteStmt = $conn->prepare("DELETE FROM media WHERE id = ?");
        $deleteStmt->bind_param("i", $mediaId);
        
        if ($deleteStmt->execute()) {
            // Log hoạt động xóa
            $userId = authCurrentUserId();
            $logMessage = "User ID $userId da xoa media: " . $media['file_name'] . " (ID: " . $media['id'] . ")";
            error_log($logMessage);
            
            echo json_encode([
                'success' => true,
                'message' => 'Da xoa media thanh cong: ' . $media['file_name'],
                'media_id' => $mediaId,
                'file_deleted' => $fileDeleted
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Loi khi xoa media: ' . $deleteStmt->error
            ], JSON_UNESCAPED_UNICODE);
        }
        
        $deleteStmt->close();
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Media khong ton tai'
        ], JSON_UNESCAPED_UNICODE);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Loi he thong: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>