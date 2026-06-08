<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';
require_once 'contact-message-helper.php';

authRequireRole(['admin']);

$isLoggedIn = authIsLoggedIn();
$currentRole = authCurrentRole();
$displayName = authDisplayName();

function getDashboardStats()
{
    $conn = getDBConnection();
    if (!$conn) return null;
    
    $stats = [];
    
    // Thống kê tin tức
    $result = $conn->query("SELECT 
        COUNT(*) as total_news,
        SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published_news,
        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_news,
        SUM(views) as total_views
        FROM news");
    $stats['news'] = $result ? $result->fetch_assoc() : [];
    
    // Thống kê theo danh mục
    $result = $conn->query("SELECT c.name, COUNT(n.id) as count 
        FROM categories c 
        LEFT JOIN news n ON c.id = n.category_id AND n.status = 'published'
        GROUP BY c.id, c.name 
        ORDER BY count DESC");
    $stats['categories'] = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    
    // Tin tức mới nhất
    $result = $conn->query("SELECT n.title, n.published_at, c.name as category, n.views
        FROM news n 
        LEFT JOIN categories c ON n.category_id = c.id
        WHERE n.status = 'published'
        ORDER BY n.published_at DESC 
        LIMIT 5");
    $stats['recent_news'] = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    
    // Thống kê theo tháng (6 tháng gần nhất)
    $result = $conn->query("SELECT 
        DATE_FORMAT(published_at, '%Y-%m') as month,
        COUNT(*) as count
        FROM news 
        WHERE status = 'published' AND published_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(published_at, '%Y-%m')
        ORDER BY month DESC");
    $stats['monthly'] = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    
    $conn->close();
    return $stats;
}

$dashboardStats = getDashboardStats();
$contactStats = getContactStats();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css?v=2.1">
    <link rel="stylesheet" href="dashboard-style.css?v=1.0">
    <script src="dropdown.js"></script>
</head>
<body>
        <?php include 'menu-don-gian.php'; ?>

    <main>
        <section class="page-header">
            <div class="container">
                <h2>Dashboard Quản trị</h2>
                <p>Tổng quan hoạt động website và thống kê</p>
            </div>
        </section>

        <section class="dashboard">
            <div class="container">
                <!-- Thống kê tổng quan -->
                <div class="stats-overview">
                    <h3>Tổng quan hệ thống</h3>
                    <div class="stats-grid">
                        <div class="stat-card stat-card--news">
                            <div class="stat-icon">📰</div>
                            <div class="stat-content">
                                <div class="stat-number"><?php echo $dashboardStats['news']['total_news'] ?? 0; ?></div>
                                <div class="stat-label">Tổng tin tức</div>
                                <div class="stat-detail"><?php echo $dashboardStats['news']['published_news'] ?? 0; ?> đã xuất bản</div>
                            </div>
                        </div>
                        
                        <div class="stat-card stat-card--views">
                            <div class="stat-icon">👁️</div>
                            <div class="stat-content">
                                <div class="stat-number"><?php echo number_format($dashboardStats['news']['total_views'] ?? 0); ?></div>
                                <div class="stat-label">Lượt xem</div>
                                <div class="stat-detail">Tổng lượt xem tin tức</div>
                            </div>
                        </div>
                        
                        <div class="stat-card stat-card--contacts">
                            <div class="stat-icon">📧</div>
                            <div class="stat-content">
                                <div class="stat-number"><?php echo $contactStats['total']; ?></div>
                                <div class="stat-label">Tin nhắn liên hệ</div>
                                <div class="stat-detail"><?php echo $contactStats['new']; ?> chưa xử lý</div>
                            </div>
                        </div>
                        
                        <div class="stat-card stat-card--drafts">
                            <div class="stat-icon">📝</div>
                            <div class="stat-content">
                                <div class="stat-number"><?php echo $dashboardStats['news']['draft_news'] ?? 0; ?></div>
                                <div class="stat-label">Bản nháp</div>
                                <div class="stat-detail">Tin tức chưa xuất bản</div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Thống kê chi tiết -->
                <div class="dashboard-grid">
                    <div class="dashboard-panel">
                        <h3>Tin tức theo danh mục</h3>
                        <div class="category-stats">
                            <?php foreach ($dashboardStats['categories'] as $category): ?>
                                <div class="category-item">
                                    <div class="category-name"><?php echo htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="category-count"><?php echo $category['count']; ?> bài</div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="dashboard-panel">
                        <h3>Tin tức mới nhất</h3>
                        <div class="recent-news">
                            <?php foreach ($dashboardStats['recent_news'] as $news): ?>
                                <div class="news-item">
                                    <div class="news-title"><?php echo htmlspecialchars($news['title'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="news-meta">
                                        <span class="news-category"><?php echo htmlspecialchars($news['category'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <span class="news-date"><?php echo date('d/m/Y', strtotime($news['published_at'])); ?></span>
                                        <span class="news-views"><?php echo $news['views']; ?> lượt xem</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="dashboard-panel">
                        <h3>Thống kê theo tháng</h3>
                        <div class="monthly-chart">
                            <?php foreach ($dashboardStats['monthly'] as $month): ?>
                                <div class="month-item">
                                    <div class="month-label"><?php echo date('m/Y', strtotime($month['month'] . '-01')); ?></div>
                                    <div class="month-bar">
                                        <div class="month-fill" style="width: <?php echo min(100, ($month['count'] / 10) * 100); ?>%"></div>
                                    </div>
                                    <div class="month-count"><?php echo $month['count']; ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="dashboard-panel">
                        <h3>Trạng thái liên hệ</h3>
                        <div class="contact-status-chart">
                            <div class="status-item">
                                <div class="status-color status-new"></div>
                                <div class="status-label">Mới</div>
                                <div class="status-count"><?php echo $contactStats['new']; ?></div>
                            </div>
                            <div class="status-item">
                                <div class="status-color status-processing"></div>
                                <div class="status-label">Đang xử lý</div>
                                <div class="status-count"><?php echo $contactStats['processing']; ?></div>
                            </div>
                            <div class="status-item">
                                <div class="status-color status-resolved"></div>
                                <div class="status-label">Đã giải quyết</div>
                                <div class="status-count"><?php echo $contactStats['resolved']; ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions">
                    <h3>Thao tác nhanh</h3>
                    <div class="actions-grid">
                        <a href="them-tin.php" class="action-card">
                            <div class="action-icon">➕</div>
                            <div class="action-label">Thêm tin tức mới</div>
                        </a>
                        <a href="tin-nhan-lien-he.php" class="action-card">
                            <div class="action-icon">📧</div>
                            <div class="action-label">Xem tin nhắn liên hệ</div>
                        </a>
                        <a href="tin-tuc.php" class="action-card">
                            <div class="action-icon">📰</div>
                            <div class="action-label">Quản lý tin tức</div>
                        </a>
                        <a href="quan-ly-media.php" class="action-card">
                            <div class="action-icon">🖼️</div>
                            <div class="action-label">Quản lý media</div>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>