<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';
require_once 'news-content-helper.php';

$contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
$maxPostSize = convertIniSizeToBytes(ini_get('post_max_size'));

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $contentLength > 0 && empty($_POST) && $maxPostSize > 0 && $contentLength > $maxPostSize) {
    $_SESSION['error'] = "Nội dung bài viết quá lớn so với giới hạn post_max_size hiện tại. Hãy giảm số lượng ảnh trong file Word rồi thử lại.";
    header("Location: them-tin-don-gian.php");
    exit();
}

authRequireCanBo('index.php');

// Kiểm tra CSRF token
SecurityHelper::validateRequest();

// Chỉ cho phép POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    die("Không phải POST request");
}

// Lấy dữ liệu từ form
$category = isset($_POST['category']) ? trim($_POST['category']) : '';
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$date = isset($_POST['date']) ? trim($_POST['date']) : '';
$summary = isset($_POST['summary']) ? trim($_POST['summary']) : '';
$content = isset($_POST['content']) ? $_POST['content'] : '';
$content = normalizeNewsContentHtml($content);
$news_file_path = isset($_POST['news_file_path']) ? trim($_POST['news_file_path']) : null;
if ($news_file_path === '') $news_file_path = null;

// Kiểm tra dữ liệu
if (empty($category) || empty($title) || empty($date) || empty($summary)) {
    $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin (danh mục, tiêu đề, ngày đăng, tóm tắt)!";
    header("Location: them-tin.php?cat=" . ($category ?: 'su-kien'));
    exit();
}

// Kiểm tra nội dung - cho phép cả text thuần và HTML
$contentStripped = strip_tags($content);
if (empty(trim($contentStripped)) && empty($content)) {
    $_SESSION['error'] = "Vui lòng nhập nội dung tin tức!";
    header("Location: them-tin.php?cat=" . $category);
    exit();
}

// Upload ảnh
$image_path = "images/news-default.jpg";

if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $file_type = $_FILES['image']['type'];
    
    if (in_array($file_type, $allowed_types)) {
        $upload_dir = "images/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $new_filename = "news_" . time() . "_" . random_int(1000, 9999) . "." . $file_extension;
        $upload_path = $upload_dir . $new_filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
            $image_path = $upload_path;
        }
    }
}

// Map category
$category_map = [
    'xay-dung-dang' => 1,
    'mat-tran' => 2,
    'an-ninh' => 3,
    'su-kien' => 4,
    'tuyen-truyen' => 5,
    'giao-duc' => 6
];

$category_id = isset($category_map[$category]) ? $category_map[$category] : 1;

try {
    $conn = getDBConnection();
    
    // Tạo slug từ title
    $slug = createSlug($title);
    
    // Kiểm tra slug trùng
    $check_stmt = $conn->prepare("SELECT id FROM news WHERE slug = ?");
    $check_stmt->bind_param("s", $slug);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        $slug = $slug . "-" . time();
    }
    
    // Insert tin tức
    $stmt = $conn->prepare("INSERT INTO news (title, slug, summary, content, image, file_path, category_id, published_at, created_at, updated_at, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'published')");
    
    $published_at = $date . " " . date("H:i:s");
    $created_at = date("Y-m-d H:i:s");
    $updated_at = date("Y-m-d H:i:s");
    
    $stmt->bind_param("ssssssisss", $title, $slug, $summary, $content, $image_path, $news_file_path, $category_id, $published_at, $created_at, $updated_at);
    
    if ($stmt->execute()) {
        $news_id = $stmt->insert_id;
        $_SESSION['success'] = "Thêm tin tức thành công!";
        
        // Redirect về trang tin tức với category tương ứng
        $category_slugs = [
            1 => 'xay-dung-dang',
            2 => 'mat-tran',
            3 => 'an-ninh',
            4 => 'su-kien',
            5 => 'tuyen-truyen',
            6 => 'giao-duc'
        ];
        
        $cat_slug = isset($category_slugs[$category_id]) ? $category_slugs[$category_id] : 'all';
        header("Location: tin-tuc.php?cat=" . $cat_slug);
    } else {
        $_SESSION['error'] = "Lỗi khi thêm tin tức.";
        header("Location: them-tin.php?cat=" . $category);
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    $_SESSION['error'] = "Lỗi hệ thống.";
    header("Location: them-tin-don-gian.php");
}

exit();

function createSlug($text) {
    // Chuyển về chữ thường
    $text = strtolower($text);
    
    // Bỏ dấu tiếng Việt
    $text = removeVietnameseAccents($text);
    
    // Chỉ giữ lại chữ cái, số và dấu cách
    $text = preg_replace('/[^a-z0-9\s]/', '', $text);
    
    // Thay dấu cách bằng dấu gạch ngang
    $text = preg_replace('/\s+/', '-', $text);
    
    // Bỏ dấu gạch ngang ở đầu và cuối
    $text = trim($text, '-');
    
    return $text;
}

function removeVietnameseAccents($text) {
    $map = [
        'a' => ['à', 'á', 'ạ', 'ả', 'ã', 'â', 'ầ', 'ấ', 'ậ', 'ẩ', 'ẫ', 'ă', 'ằ', 'ắ', 'ặ', 'ẳ', 'ẵ'],
        'A' => ['À', 'Á', 'Ạ', 'Ả', 'Ã', 'Â', 'Ầ', 'Ấ', 'Ậ', 'Ẩ', 'Ẫ', 'Ă', 'Ằ', 'Ắ', 'Ặ', 'Ẳ', 'Ẵ'],
        'e' => ['è', 'é', 'ẹ', 'ẻ', 'ẽ', 'ê', 'ề', 'ế', 'ệ', 'ể', 'ễ'],
        'E' => ['È', 'É', 'Ẹ', 'Ẻ', 'Ẽ', 'Ê', 'Ề', 'Ế', 'Ệ', 'Ể', 'Ễ'],
        'i' => ['ì', 'í', 'ị', 'ỉ', 'ĩ'],
        'I' => ['Ì', 'Í', 'Ị', 'Ỉ', 'Ĩ'],
        'o' => ['ò', 'ó', 'ọ', 'ỏ', 'õ', 'ô', 'ồ', 'ố', 'ộ', 'ổ', 'ỗ', 'ơ', 'ờ', 'ớ', 'ợ', 'ở', 'ỡ'],
        'O' => ['Ò', 'Ó', 'Ọ', 'Ỏ', 'Õ', 'Ô', 'Ồ', 'Ố', 'Ộ', 'Ổ', 'Ỗ', 'Ơ', 'Ờ', 'Ớ', 'Ợ', 'Ở', 'Ỡ'],
        'u' => ['ù', 'ú', 'ụ', 'ủ', 'ũ', 'ư', 'ừ', 'ứ', 'ự', 'ử', 'ữ'],
        'U' => ['Ù', 'Ú', 'Ụ', 'Ủ', 'Ũ', 'Ư', 'Ừ', 'Ứ', 'Ự', 'Ử', 'Ữ'],
        'y' => ['ỳ', 'ý', 'ỵ', 'ỷ', 'ỹ'],
        'Y' => ['Ỳ', 'Ý', 'Ỵ', 'Ỷ', 'Ỹ'],
        'd' => ['đ'],
        'D' => ['Đ']
    ];

    foreach ($map as $ascii => $unicodeChars) {
        $text = str_replace($unicodeChars, $ascii, $text);
    }

    return $text;
}
?>