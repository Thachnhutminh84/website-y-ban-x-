<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';

$isLoggedIn = authIsLoggedIn();
$currentRole = authCurrentRole();
$displayName = $isLoggedIn ? authDisplayName() : '';

// Lấy tin tức danh mục an ninh trật tự
$news = [];
try {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT n.*, c.name as category_name FROM news n 
                            LEFT JOIN categories c ON n.category_id = c.id 
                            WHERE c.slug = 'an-ninh' AND n.status = 'published' 
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
    <title>An ninh trật tự - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css?v=2.1">
    <script src="dropdown.js"></script>
</head>
<body>
    <header<?php echo $isLoggedIn ? ' class="header--compact"' : ''; ?>>
        <div class="container">
            <div class="logo">
                <img src="images/logo.png" alt="Logo UBND Xã Long Hiệp">
                <div class="header-text">
                    <h1>ỦY BAN NHÂN DÂN XÃ LONG HIỆP</h1>
                    <p>Phục vụ nhân dân - Xây dựng quê hương</p>
                </div>
            </div>
            <nav>
                <ul class="menu">
                    <li><a href="index.php">Trang chủ</a></li>
                    <li class="dropdown">
                        <a href="tin-tuc.php" class="active">Tin tức - Thông báo</a>
                        <button class="dropdown-toggle" onclick="toggleDropdown(event)">&#9662;</button>
                        <ul class="dropdown-menu">
                            <li><a href="cong-tac-xay-dung-dang.php">Công tác xây dựng Đảng</a></li>
                            <li><a href="mat-tran-doan-the.php">Mặt trận đoàn thể</a></li>
                            <li><a href="an-ninh-trat-tu.php">An ninh trật tự</a></li>
                            <li><a href="tin-tuc-su-kien.php">Tin tức sự kiện</a></li>
                            <li><a href="thong-tin-tuyen-truyen.php">Thông tin tuyên truyền</a></li>
                            <li><a href="giao-duc-dao-tao.php">Giáo dục và đào tạo</a></li>
                        </ul>
                    </li>
                    <li><a href="phong-ban.php">Phòng ban</a></li>
                    <li><a href="lanh-dao.php">Lãnh đạo</a></li>
                    <li><a href="thu-tuc-hanh-chinh.php">Thủ tục</a></li>
                    <li><a href="lien-he.php">Liên hệ</a></li>
                    <?php if ($isLoggedIn): ?>
                        <li class="admin-info">
                            👤 <?php echo htmlspecialchars(authRoleLabel($currentRole), ENT_QUOTES, 'UTF-8'); ?>
                            <a href="tin-tuc.php"><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></a>
                            <a href="logout.php">Đăng xuất</a>
                        </li>
                    <?php else: ?>
                        <li><a href="dang-nhap.php" class="login-btn">Đăng nhập</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="page-header">
            <div class="container">
                <h2>An ninh trật tự</h2>
                <p>Thông tin về công tác đảm bảo an ninh trật tự tại xã Long Hiệp</p>
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