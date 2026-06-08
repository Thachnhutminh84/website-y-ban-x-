<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';
require_once 'news-content-helper.php';

$contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
$maxPostSize = convertIniSizeToBytes(ini_get('post_max_size'));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $contentLength > 0 && empty($_POST) && $maxPostSize > 0 && $contentLength > $maxPostSize) {
    $_SESSION['error'] = "Noi dung bai viet qua lon so voi gioi han post_max_size hien tai. Hay giam so luong anh trong file Word roi thu lai.";
    header("Location: tin-tuc.php");
    exit();
}

authRequireCanBo('index.php');

// Kiểm tra CSRF token
SecurityHelper::validateRequest();

// Kiểm tra method POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: tin-tuc.php");
    exit();
}

// Lấy dữ liệu từ form
$news_id = isset($_POST['news_id']) ? intval($_POST['news_id']) : 0;
$category = isset($_POST['category']) ? trim($_POST['category']) : '';
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$date = isset($_POST['date']) ? trim($_POST['date']) : '';
$summary = isset($_POST['summary']) ? trim($_POST['summary']) : '';
$content = isset($_POST['content']) ? $_POST['content'] : '';
$content = normalizeNewsContentHtml($content);
$current_image = isset($_POST['current_image']) ? trim($_POST['current_image']) : '';
$news_file_path = isset($_POST['news_file_path']) ? trim($_POST['news_file_path']) : null;
if ($news_file_path === '') $news_file_path = null;

// Xử lý xóa file đính kèm
$deleteFileChecked = isset($_POST['delete_news_file']) && $_POST['delete_news_file'] == '1';
if ($deleteFileChecked) {
    // Đọc file_path cũ từ DB để xóa file
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT file_path FROM news WHERE id = ?");
        $stmt->bind_param("i", $news_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $old_file = $row['file_path'];
            if (!empty($old_file) && file_exists($old_file)) {
                @unlink($old_file);
            }
        }
        $stmt->close();
        $conn->close();
    } catch (Exception $e) {}
    $news_file_path = null;
}

// Validate dữ liệu cơ bản
if ($news_id <= 0) {
    $_SESSION['error'] = "ID tin tức không hợp lệ!";
    header("Location: tin-tuc.php");
    exit();
}

if (empty($category)) {
    $_SESSION['error'] = "Vui lòng chọn danh mục!";
    header("Location: sua-tin.php?id=$news_id");
    exit();
}

if (empty($title)) {
    $_SESSION['error'] = "Vui lòng nhập tiêu đề!";
    header("Location: sua-tin.php?id=$news_id");
    exit();
}

if (empty($date)) {
    $_SESSION['error'] = "Vui lòng chọn ngày đăng!";
    header("Location: sua-tin.php?id=$news_id");
    exit();
}

if (empty($summary)) {
    $_SESSION['error'] = "Vui lòng nhập tóm tắt!";
    header("Location: sua-tin.php?id=$news_id");
    exit();
}

// Kiểm tra content (cho phép HTML nhưng phải có text)
if (!hasRenderableContent($content)) {
    $_SESSION['error'] = "Vui lòng nhập nội dung đầy đủ!";
    header("Location: sua-tin.php?id=$news_id");
    exit();
}

// Xử lý upload ảnh mới (nếu có)
$image_path = $current_image;
if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $_FILES['image']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (in_array($ext, $allowed)) {
        $new_filename = 'news-' . time() . '.' . $ext;
        $upload_path = 'images/' . $new_filename;
        
        if (!file_exists('images')) {
            mkdir('images', 0777, true);
        }
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            $image_path = $upload_path;
            
            // Xóa ảnh cũ nếu không phải ảnh mặc định
            if (!empty($current_image) && $current_image != 'images/news-default.jpg' && file_exists($current_image)) {
                @unlink($current_image);
            }
        }
    }
}

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

// Cập nhật database
try {
    $conn = getDBConnection();
    $slug = generateUniqueNewsSlug($conn, $title, $news_id);
    
    // Xóa file cũ nếu có file mới được import
    $old_file_path = null;
    if (!empty($news_file_path)) {
        $stmt = $conn->prepare("SELECT file_path FROM news WHERE id = ?");
        $stmt->bind_param("i", $news_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $old_file_path = $row['file_path'];
        }
        $stmt->close();
        if (!empty($old_file_path) && $old_file_path !== $news_file_path && file_exists($old_file_path)) {
            @unlink($old_file_path);
        }
    }
    
    // Chuẩn bị câu SQL
    $sql = "UPDATE news SET 
            category_id = ?, 
            title = ?, 
            slug = ?, 
            summary = ?, 
            content = ?, 
            image = ?, 
            file_path = ?,
            published_at = ?, 
            updated_at = NOW()
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Lỗi prepare statement");
    }
    
    // Bind parameters
    $stmt->bind_param("isssssssi", 
        $category_id, 
        $title, 
        $slug,
        $summary, 
        $content, 
        $image_path, 
        $news_file_path,
        $date, 
        $news_id
    );
    
    // Thực thi
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['success'] = "✓ Cập nhật tin tức thành công!";
        } else {
            $_SESSION['warning'] = "Không có thay đổi nào được thực hiện.";
        }
    } else {
        throw new Exception("Lỗi execute");
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    $_SESSION['error'] = "Lỗi cập nhật tin tức.";
    header("Location: sua-tin.php?id=$news_id");
    exit();
}

// Redirect về trang chi tiết tin vừa sửa
header("Location: chi-tiet-tin.php?id=$news_id");
exit();
?>
