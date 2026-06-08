<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';

$isLoggedIn = authIsLoggedIn();
$currentRole = authCurrentRole();
$displayName = $isLoggedIn ? authDisplayName() : '';

// Lấy tin tức danh mục xây dựng đảng
$news = [];
try {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT n.*, c.name as category_name FROM news n 
                            LEFT JOIN categories c ON n.category_id = c.id 
                            WHERE c.slug = 'xay-dung-dang' AND n.status = 'published' 
                            ORDER BY n.published_at DESC LIMIT 10");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $news[] = $row;
    }
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    // Handle error
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Công tác xây dựng Đảng - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css?v=2.1">
    <script src="dropdown.js"></script>
</head>
<body>
    <?php include 'menu-don-gian.php'; ?>

    <main>
        <section class="page-header">
            <div class="container">
                <h2>Công tác xây dựng Đảng</h2>
                <p>Thông tin về công tác xây dựng và phát triển Đảng bộ xã Long Hiệp</p>
            </div>
        </section>

        <section class="news-page">
            <div class="container">
                <?php if (count($news) > 0): ?>
                    <div class="news-grid">
                        <?php foreach ($news as $item): ?>
                            <article class="news-item">
                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" onerror="this.src='images/news-default.jpg'">
                                <div class="news-content">
                                    <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                    <p class="date">📅 <?php echo date('d/m/Y', strtotime($item['published_at'])); ?></p>
                                    <p><?php echo htmlspecialchars(substr(strip_tags($item['summary']), 0, 150)); ?>...</p>
                                    <a href="chi-tiet-tin.php?id=<?php echo $item['id']; ?>" class="read-more">Xem thêm →</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-panel">
                        <p>📰 Chưa có tin tức nào trong danh mục này.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>