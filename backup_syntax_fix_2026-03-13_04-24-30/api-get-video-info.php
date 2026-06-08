<?php
header("Content-Type: application/json; charset=utf-8");
require_once 'config.php';

// Chỉ chấp nhận GET request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$videoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

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
    
    $stmt = $conn->prepare("
        SELECT v.*, va.name as album_name 
        FROM videos v 
        LEFT JOIN video_albums va ON v.album_id = va.id 
        WHERE v.id = ? AND v.is_active = 1
    ");
    $stmt->bind_param("i", $videoId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($video = $result->fetch_assoc()) {
        echo json_encode([
            'success' => true,
            'video' => [
                'id' => (int)$video['id'],
                'title' => $video['title'],
                'description' => $video['description'],
                'video_url' => $video['video_url'],
                'video_type' => $video['video_type'],
                'thumbnail_url' => $video['thumbnail_url'],
                'duration' => $video['duration'],
                'album_name' => $video['album_name'],
                'views' => (int)$video['views'],
                'is_featured' => (bool)$video['is_featured'],
                'created_at' => $video['created_at']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Video not found'
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