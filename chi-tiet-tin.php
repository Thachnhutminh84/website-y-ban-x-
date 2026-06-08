<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';

$isLoggedIn = authIsLoggedIn();
$currentRole = authCurrentRole();
$displayName = $isLoggedIn ? authDisplayName() : '';

// Lấy ID tin tức từ URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Đọc tin tức từ database
$news = null;

try {
    $conn = getDBConnection();
    
    // Lấy tin tức theo ID
    $stmt = $conn->prepare("SELECT n.*, c.slug as category_slug FROM news n 
                            LEFT JOIN categories c ON n.category_id = c.id 
                            WHERE n.id = ? AND n.status = 'published'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $news = [
            'id' => $row['id'],
            'title' => $row['title'],
            'summary' => $row['summary'],
            'content' => $row['content'],
            'image' => $row['image'],
            'file_path' => $row['file_path'],
            'date' => $row['published_at'],
            'category' => $row['category_slug']
        ];
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    // Nếu lỗi database
    echo "<!-- Lỗi database -->";
}

// Nếu không tìm thấy, chuyển về trang tin tức
if (!$news) {
    header("Location: tin-tuc.php");
    exit();
}

// Không cần format lại nội dung vì đã có HTML từ Word
// Nội dung từ Word editor đã có đầy đủ định dạng HTML

$hasDetailContent = trim(strip_tags((string) $news['content'])) !== '' || stripos((string) $news['content'], '<img') !== false;
$looksLikeWordDocument = stripos((string) $news['content'], 'word-document-content') !== false
    || stripos((string) $news['content'], 'data-doc-import="word"') !== false
    || stripos((string) $news['content'], '<table') !== false;
$showLeadImage = !($looksLikeWordDocument && (!isset($news['image']) || $news['image'] === '' || $news['image'] === 'images/news-default.jpg'));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $news['title']; ?> - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css?v=2.3">
    <link rel="stylesheet" href="word-document.css?v=1.3">
    <link rel="stylesheet" href="responsive-enhancements.css">
    <script src="dropdown.js"></script>
</head>
<body>
    <?php include 'header-menu.php'; ?>

    <main>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="container" style="margin-top: 20px;">
                <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; border: 1px solid #c3e6cb;">
                    <?php 
                    echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); 
                    unset($_SESSION['success']);
                    ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="container" style="margin-top: 20px;">
                <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; border: 1px solid #f5c6cb;">
                    <?php 
                    echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); 
                    unset($_SESSION['error']);
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <section class="news-detail">
            <div class="container">
                <div class="breadcrumb">
                    <a href="index.php">Trang chủ</a> / <span>Tin tức</span>
                </div>
                
                <article class="detail-content<?php echo $looksLikeWordDocument ? ' detail-content--word' : ''; ?>">
                    <h1><?php echo htmlspecialchars($news['title']); ?></h1>
                    <p class="detail-date">Ngày đăng: <?php echo date('d/m/Y', strtotime($news['date'])); ?></p>
                    
                    <?php if ($showLeadImage): ?>
                    <div class="detail-image">
                        <img src="<?php echo htmlspecialchars($news['image']); ?>" 
                             alt="<?php echo htmlspecialchars($news['title']); ?>" 
                             loading="eager"
                             width="1000"
                             height="600"
                             onerror="this.src='images/news-default.jpg'">
                    </div>
                    <?php endif; ?>
                    
                    <div class="detail-body detail-body--large" data-word-surface="detail">
                        <?php if ($hasDetailContent): ?>
                            <?php echo $news['content']; // Nội dung HTML từ Word editor - đã được filter khi lưu ?>
                        <?php else: ?>
                            <p class="detail-empty">Noi dung bai viet dang duoc cap nhat.</p>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($news['file_path'])): ?>
                    <div class="news-attachment">
                        <a href="download-news.php?id=<?php echo $news['id']; ?>" class="btn-download" target="_blank">
                            <span class="icon">📎</span>
                            <span class="text">Tải tài liệu đính kèm</span>
                            <span class="filename">(<?php echo basename($news['file_path']); ?>)</span>
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <div class="news-actions">
                        <a href="export-word.php?id=<?php echo $news['id']; ?>" class="btn-export">
                            <span>📄</span> Xuất file Word
                        </a>
                    </div>
                    
                    <div class="back-link">
                        <a href="tin-tuc.php?cat=<?php echo $news['category']; ?>" class="btn-back">← Quay lại danh sách tin tức</a>
                    </div>
                </article>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>
    <script src="word-document.js?v=1.0"></script>
    <?php include 'quick-actions-toolbar.php'; ?>
</body>
</html>