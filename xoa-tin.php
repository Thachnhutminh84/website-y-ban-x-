<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';

// Kiểm tra quyền cán bộ hoặc admin
if (!authIsLoggedIn() || !authIsApproved()) {
    echo json_encode(['success' => false, 'message' => 'Bạn chưa đăng nhập hoặc tài khoản chưa được phê duyệt'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!authCanManageContent()) {
    echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa tin tức'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Lấy ID tin tức
$news_id = 0;
$isAjax = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($contentType, 'application/json') !== false) {
        $input = json_decode(file_get_contents('php://input'), true);
        $news_id = isset($input['id']) ? (int)$input['id'] : 0;
        $isAjax = true;
    } else {
        $news_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        SecurityHelper::validateRequest();
    }
} else {
    $news_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
}

if ($news_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID tin tức không hợp lệ'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $conn = getDBConnection();
    
    // Lấy thông tin tin tức trước khi xóa (để xóa ảnh)
    $stmt = $conn->prepare("SELECT image, file_path FROM news WHERE id = ?");
    $stmt->bind_param("i", $news_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $news = $result->fetch_assoc();
    $stmt->close();
    
    if (!$news) {
        if (!$isAjax) {
            $_SESSION['error'] = 'Không tìm thấy tin tức';
            header("Location: tin-tuc.php");
            exit;
        }
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy tin tức'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Xóa tin tức
    $stmt = $conn->prepare("DELETE FROM news WHERE id = ?");
    $stmt->bind_param("i", $news_id);
    
    if ($stmt->execute()) {
        // Xóa file ảnh nếu có
        $imageDeleted = false;
        if (!empty($news['image']) && file_exists($news['image'])) {
            $imageDeleted = @unlink($news['image']);
        }
        
        // Xóa file tài liệu nếu có
        $fileDeleted = false;
        if (!empty($news['file_path']) && file_exists($news['file_path'])) {
            $fileDeleted = @unlink($news['file_path']);
        }
        
        if (!$isAjax) {
            $_SESSION['success'] = 'Xóa tin tức thành công!';
            $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'tin-tuc.php';
            header("Location: $referrer");
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Xóa tin tức thành công',
            'news_id' => $news_id,
            'image_deleted' => $imageDeleted,
            'file_deleted' => $fileDeleted
        ], JSON_UNESCAPED_UNICODE);
    } else {
        if (!$isAjax) {
            $_SESSION['error'] = 'Lỗi khi xóa tin tức';
            $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'tin-tuc.php';
            header("Location: $referrer");
            exit;
        }
        echo json_encode([
            'success' => false,
            'message' => 'Lỗi khi xóa tin tức'
        ], JSON_UNESCAPED_UNICODE);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    if (!$isAjax) {
        $_SESSION['error'] = 'Lỗi hệ thống';
        header("Location: tin-tuc.php");
        exit;
    }
    echo json_encode([
        'success' => false,
        'message' => 'Lỗi hệ thống'
    ], JSON_UNESCAPED_UNICODE);
}
?>