<?php
header("Content-Type: application/json; charset=utf-8");
require_once 'config.php';

// Chỉ chấp nhận POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Lấy dữ liệu JSON
$input = json_decode(file_get_contents('php://input'), true);
$videoId = isset($input['video_id']) ? (int)$input['video_id'] : 0;

if ($videoId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid video ID']);
    exit;
}

try {
    $conn = getDBConnection();
    
    // Kiểm tra bảng videos có tồn tại không
    $result = $conn->query("SHOW TABLES LIKE 'videos'");
    if (!$result || $result->num_rows == 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Video system not initialized'
        ]);
        exit;
    }
    
    // Cập nhật lượt xem
    $stmt = $conn->prepare("UPDATE videos SET views = views + 1 WHERE id = ? AND is_active = 1");
    $stmt->bind_param("i", $videoId);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        // Lấy số lượt xem mới
        $stmt = $conn->prepare("SELECT views FROM videos WHERE id = ?");
        $stmt->bind_param("i", $videoId);
        $stmt->execute();
        $result = $stmt->get_result();
        $video = $result->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'views' => (int)$video['views'],
            'message' => 'Views updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Video not found or inactive'
        ]);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>