<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';

if (!authIsLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit();
}

if (!authIsAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa tin tức']);
    exit();
}

// Lấy ID tin tức cần xóa
$news_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($news_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
    exit();
}

try {
    $conn = getDBConnection();
    
    // Lấy thông tin tin tức trước khi xóa (để xóa ảnh)
    $stmt = $conn->prepare("SELECT image, category_id FROM news WHERE id = ?");
    $stmt->bind_param("i", $news_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $news = $result->fetch_assoc();
        $image_path = $news['image'];
        $category_id = $news['category_id'];
        
        // Xóa tin tức
        $stmt_delete = $conn->prepare("DELETE FROM news WHERE id = ?");
        $stmt_delete->bind_param("i", $news_id);
        
        if ($stmt_delete->execute()) {
            // Xóa ảnh (nếu không phải ảnh mặc định)
            if (!empty($image_path) && $image_path != 'images/news-default.jpg' && file_exists($image_path)) {
                unlink($image_path);
            }
            
            echo json_encode(['success' => true, 'message' => 'Xóa tin tức thành công']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa tin tức']);
        }
        
        $stmt_delete->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy tin tức']);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}

exit();
?>
