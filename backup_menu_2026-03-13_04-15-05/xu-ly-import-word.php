<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once 'auth.php';

if (!authIsLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit();
}

if (!authCanManageNews()) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện thao tác này']);
    exit();
}

require_once 'config.php';
require_once 'news-content-helper.php';

// Lấy dữ liệu từ POST
$category = isset($_POST['category']) ? $_POST['category'] : 'xay-dung-dang';
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$summary = isset($_POST['summary']) ? trim($_POST['summary']) : '';
$content = isset($_POST['content']) ? $_POST['content'] : '';
$content = normalizeNewsContentHtml($content);
$date = isset($_POST['date']) ? $_POST['date'] : date('Y-m-d');

// Kiểm tra dữ liệu
if (empty($title) || empty($summary) || !hasRenderableContent($content)) {
    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
    exit();
}

try {
    $conn = getDBConnection();
    
    // Map category slug sang ID
    $category_map = [
        'xay-dung-dang' => 1,
        'mat-tran' => 2,
        'an-ninh' => 3,
        'su-kien' => 4,
        'tuyen-truyen' => 5,
        'giao-duc' => 6
    ];
    
    $category_id = isset($category_map[$category]) ? $category_map[$category] : 1;
    $slug = generateUniqueNewsSlug($conn, $title);
    
    // Ảnh mặc định
    $image_path = 'images/news-default.jpg';
    
    // Insert vào database
    $stmt = $conn->prepare("INSERT INTO news (category_id, title, slug, summary, content, image, published_at, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'published', NOW())");
    $stmt->bind_param("issssss", $category_id, $title, $slug, $summary, $content, $image_path, $date);
    
    if ($stmt->execute()) {
        $news_id = $conn->insert_id;
        echo json_encode([
            'success' => true, 
            'message' => 'Đã xuất bản tin tức thành công!',
            'news_id' => $news_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $stmt->error]);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()]);
}
?>
