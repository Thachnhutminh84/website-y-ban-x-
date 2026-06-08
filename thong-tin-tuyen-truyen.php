<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';

$isLoggedIn = authIsLoggedIn();
$currentRole = authCurrentRole();
$displayName = $isLoggedIn ? authDisplayName() : '';

// Lấy tin tức danh mục tuyên truyền
$news = [];
try {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT n.*, c.name as category_name FROM news n 
                            LEFT JOIN categories c ON n.category_id = c.id 
                            WHERE c.slug = 'tuyen-truyen' AND n.status = 'published' 
                            ORDER BY n.published_at DESC LIMIT 10");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
;
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
    <title>Thông tin tuyên truyền - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css?v=2.1">
    <link rel="stylesheet" href="footer-style.css?v=1.0">
    <script src="dropdown.js"></script>
    <?php include 'header-menu.php'; ?>
<body>

    <main>
        <section class="page-header">
            <div class="container">
                <h2>Thông tin tuyên truyền</h2>
                <p>Công tác tuyên truyền và phổ biến chính sách tại xã Long Hiệp</p>
                <?php if ($isLoggedIn): ?>
                    <div style="margin-top: 20px;">
                        <a href="them-tin.php?cat=tuyen-truyen" class="btn-primary" style="display: inline-block; padding: 12px 24px; background: #28a745; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">
                            ➕ Thêm tin tức mới
                        </a>
                    </div>
                <?php endif; ?>
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
                                    <div class="news-actions">
                                        <a href="chi-tiet-tin.php?id=<?php echo $item['id']; ?>" class="read-more">Xem thêm →</a>
                                        <?php if ($isLoggedIn && authCanManageContent()): ?>
                                            <div class="admin-buttons">
                                                <a href="sua-tin.php?id=<?php echo $item['id']; ?>" class="btn-edit">✏️ Sửa</a>
                                                <a href="xoa-tin.php?id=<?php echo $item['id']; ?>&cat=tuyen-truyen" 
                                                   onclick="return confirm('Bạn có chắc chắn muốn xóa tin này?')"
                                                   class="btn-delete">🗑️ Xóa</a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
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
    <?php include 'quick-actions-toolbar.php'; ?>
</body>
</html>