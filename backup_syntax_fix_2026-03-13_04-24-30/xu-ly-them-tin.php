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
    header("Location: them-tin.php");
    exit();
}

authRequireRole(['admin', 'editor']);

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

// Kiểm tra dữ liệu
if ($category == '' || $title == '' || $date == '' || $summary == '') {
    $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin!";
    header("Location: them-tin.php");
    exit();
}

// Kiểm tra nội dung
if (!hasRenderableContent($content)) {
    $_SESSION['error'] = "Vui lòng nhập nội dung!";
    header("Location: them-tin.php");
    exit();
}

// Upload ảnh
$image_path = "images/news-default.jpg";

if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {

    $allowed = ['jpg','jpeg','png','gif'];
    $filename = $_FILES['image']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (in_array($ext, $allowed)) {

        if (!file_exists("images")) {
            mkdir("images",0777,true);
        }

        $newname = "news_" . time() . "." . $ext;
        $upload = "images/" . $newname;

        if(move_uploaded_file($_FILES['image']['tmp_name'],$upload)){
            $image_path = $upload;
        }
    }
}

// Map category
$category_map = [
    'xay-dung-dang'=>1,
    'mat-tran'=>2,
    'an-ninh'=>3,
    'su-kien'=>4,
    'tuyen-truyen'=>5,
    'giao-duc'=>6
];

$category_id = isset($category_map[$category]) ? $category_map[$category] : 1;

try {

    $conn = getDBConnection();
    $slug = generateUniqueNewsSlug($conn, $title);
    $authorId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;

    $sql = "INSERT INTO news 
    (category_id,title,slug,summary,content,image,author_id,published_at,status,created_at,updated_at)
    VALUES (?,?,?,?,?,?,?, ?, 'published',NOW(),NOW())";

    $stmt = $conn->prepare($sql);

    if(!$stmt){
        die("Prepare failed: ".$conn->error);
    }

    $stmt->bind_param(
        "isssssis",
        $category_id,
        $title,
        $slug,
        $summary,
        $content,
        $image_path,
        $authorId,
        $date
    );

    if($stmt->execute()){

        $id = $conn->insert_id;

        $_SESSION['success'] = "Thêm tin tức thành công!";

        header("Location: chi-tiet-tin.php?id=".$id);
        exit();

    }else{
        die("Execute failed: ".$stmt->error);
    }

}catch(Exception $e){

    $_SESSION['error'] = $e->getMessage();
    header("Location: them-tin.php");
    exit();
}
?>
