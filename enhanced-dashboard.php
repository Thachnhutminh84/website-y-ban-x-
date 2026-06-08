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

function getEnhancedDashboardStats()
{
    $conn = getDBConnection();
    if (!$conn) return null;
    
    $stats = [];
    
    // Thống kê tin tức chi tiết
    $result = $conn->query("SELECT 
        COUNT(*) as total_news,
        SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published_news,
        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_news,
        COALESCE(SUM(views), 0) as total_views,
        AVG(COALESCE(views, 0)) as avg_views,
        COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_news,
        COUNT(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 END) as week_news
        FROM news");
    $stats['news'] = $result ? $result->fetch_assoc() : [];
    
    // Thống kê theo danh mục với chi tiết
    $result = $conn->query("SELECT 
        c.name, 
        c.slug,
        COUNT(n.id) as count,
        COALESCE(SUM(n.views), 0) as total_views,
        COALESCE(AVG(n.views), 0) as avg_views
        FROM categories c 
        LEFT JOIN news n ON c.id = n.category_id AND n.status = 'published'
        GROUP BY c.id, c.name, c.slug
        ORDER BY count DESC");
    $stats['categories'] = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    
    // Tin tức phổ biến nhất
    $result = $conn->query("SELECT 
        n.id, n.title, n.published_at, c.name as category, 
        COALESCE(n.views, 0) as views
        FROM news n 
        LEFT JOIN categories c ON n.category_id = c.id
        WHERE n.status = 'published'
        ORDER BY n.views DESC 
        LIMIT 5");
    $stats['popular_news'] = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    
    // Tin tức mới nhất
    $result = $conn->query("SELECT 
        n.id, n.title, n.published_at, c.name as category, 
        COALESCE(n.views, 0) as views
        FROM news n 
        LEFT JOIN categories c ON n.category_id = c.id
        WHERE n.status = 'published'
        ORDER BY n.published_at DESC 
        LIMIT 5");
    $stats['recent_news'] = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    
    // Thống kê theo tháng (6 tháng gần nhất)
    $result = $conn->query("SELECT 
        DATE_FORMAT(published_at, '%Y-%m') as month,
        DATE_FORMAT(published_at, '%m/%Y') as month_display,
        COUNT(*) as count,
        COALESCE(SUM(views), 0) as total_views
        FROM news 
        WHERE status = 'published' AND published_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(published_at, '%Y-%m')
        ORDER BY month DESC");
    $stats['monthly'] = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    
    // Thống kê hoạt động hàng ngày (7 ngày gần nhất)
    $result = $conn->query("SELECT 
        DATE(created_at) as date,
        DATE_FORMAT(created_at, '%d/%m') as date_display,
        COUNT(*) as count
        FROM news 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date DESC");
    $stats['daily'] = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    
    // Thống kê video (nếu có bảng videos)
    $videoTableCheck = $conn->query("SHOW TABLES LIKE 'videos'");
    if ($videoTableCheck && $videoTableCheck->num_rows > 0) {
        $result = $conn->query("SELECT 
            COUNT(*) as total_videos,
            SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_videos,
            SUM(CASE WHEN is_featured = 1 THEN 1 ELSE 0 END) as featured_videos,
            COALESCE(SUM(views), 0) as total_video_views
            FROM videos");
        $stats['videos'] = $result ? $result->fetch_assoc() : [];
    }
    
    // Thống kê lãnh đạo (nếu có bảng leaders)
    $leaderTableCheck = $conn->query("SHOW TABLES LIKE 'leaders'");
    if ($leaderTableCheck && $leaderTableCheck->num_rows > 0) {
        $result = $conn->query("SELECT 
            COUNT(*) as total_leaders,
            SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_leaders
            FROM leaders");
        $stats['leaders'] = $result ? $result->fetch_assoc() : [];
    }
    
    $conn->close();
    return $stats;
}

$dashboardStats = getEnhancedDashboardStats();
$contactStats = getContactStats();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Nâng cao - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css?v=2.1">
    <link rel="stylesheet" href="dashboard-style.css?v=1.0">
    <link rel="stylesheet" href="footer-style.css?v=1.0">
    <link rel="stylesheet" href="responsive-enhancements.css?v=1.0">
    <script src="dropdown.js"></script>
    <style>
        .enhanced-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card-enhanced {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-left: 4px solid #3498db;
            transition: transform 0.3s ease;
        }
        
        .stat-card-enhanced:hover {
            transform: translateY(-2px);
        }
        
        .stat-card-enhanced.news { border-left-color: #e74c3c; }
        .stat-card-enhanced.views { border-left-color: #f39c12; }
        .stat-card-enhanced.contacts { border-left-color: #27ae60; }
        .stat-card-enhanced.videos { border-left-color: #9b59b6; }
        
        .stat-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .stat-icon-enhanced {
            font-size: 24px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: rgba(52, 152, 219, 0.1);
        }
        
        .stat-title {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .stat-main-number {
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .stat-subtitle {
            font-size: 14px;
            color: #7f8c8d;
            margin-bottom: 10px;
        }
        
        .stat-details {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #95a5a6;
        }
        
        .chart-container {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .chart-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .chart-bar {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .chart-label {
            width: 120px;
            font-size: 14px;
            color: #2c3e50;
        }
        
        .chart-progress {
            flex: 1;
            height: 20px;
            background: #ecf0f1;
            border-radius: 10px;
            margin: 0 10px;
            overflow: hidden;
        }
        
        .chart-fill {
            height: 100%;
            background: linear-gradient(90deg, #3498db, #2980b9);
            border-radius: 10px;
            transition: width 0.8s ease;
        }
        
        .chart-value {
            font-size: 14px;
            font-weight: 600;
            color: #2c3e50;
            min-width: 40px;
        }
        
        .recent-items {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .recent-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #ecf0f1;
        }
        
        .recent-item:last-child {
            border-bottom: none;
        }
        
        .recent-content {
            flex: 1;
        }
        
        .recent-title {
            font-size: 14px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 4px;
        }
        
        .recent-meta {
            font-size: 12px;
            color: #7f8c8d;
        }
        
        .recent-views {
            font-size: 12px;
            color: #3498db;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .enhanced-stats {
                grid-template-columns: 1fr;
            }
            
            .chart-label {
                width: 80px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <?php include 'header-menu.php'; ?>

    <main>
        <section class="page-header">
            <div class="container">
                <h1>Dashboard Nâng cao</h1>
                <p>Thống kê chi tiết và phân tích hoạt động website</p>
            </div>
        </section>

        <section class="dashboard">
            <div class="container">
                <!-- Thống kê tổng quan nâng cao -->
                <div class="enhanced-stats">
                    <div class="stat-card-enhanced news">
                        <div class="stat-header">
                            <div class="stat-icon-enhanced">📰</div>
                            <div class="stat-title">Tin tức</div>
                        </div>
                        <div class="stat-main-number"><?php echo $dashboardStats['news']['total_news'] ?? 0; ?></div>
                        <div class="stat-subtitle">Tổng số bài viết</div>
                        <div class="stat-details">
                            <span><?php echo $dashboardStats['news']['published_news'] ?? 0; ?> đã xuất bản</span>
                            <span><?php echo $dashboardStats['news']['today_news'] ?? 0; ?> hôm nay</span>
                        </div>
                    </div>
                    
                    <div class="stat-card-enhanced views">
                        <div class="stat-header">
                            <div class="stat-icon-enhanced">👁️</div>
                            <div class="stat-title">Lượt xem</div>
                        </div>
                        <div class="stat-main-number"><?php echo number_format($dashboardStats['news']['total_views'] ?? 0); ?></div>
                        <div class="stat-subtitle">Tổng lượt xem tin tức</div>
                        <div class="stat-details">
                            <span>TB: <?php echo number_format($dashboardStats['news']['avg_views'] ?? 0, 1); ?>/bài</span>
                            <span><?php echo $dashboardStats['news']['week_news'] ?? 0; ?> tuần này</span>
                        </div>
                    </div>
                    
                    <div class="stat-card-enhanced contacts">
                        <div class="stat-header">
                            <div class="stat-icon-enhanced">📧</div>
                            <div class="stat-title">Liên hệ</div>
                        </div>
                        <div class="stat-main-number"><?php echo $contactStats['total']; ?></div>
                        <div class="stat-subtitle">Tin nhắn liên hệ</div>
                        <div class="stat-details">
                            <span><?php echo $contactStats['new']; ?> chưa xử lý</span>
                            <span><?php echo $contactStats['replied']; ?> đã trả lời</span>
                        </div>
                    </div>
                    
                    <?php if (isset($dashboardStats['videos'])): ?>
                    <div class="stat-card-enhanced videos">
                        <div class="stat-header">
                            <div class="stat-icon-enhanced">🎥</div>
                            <div class="stat-title">Video</div>
                        </div>
                        <div class="stat-main-number"><?php echo $dashboardStats['videos']['total_videos'] ?? 0; ?></div>
                        <div class="stat-subtitle">Tổng số video</div>
                        <div class="stat-details">
                            <span><?php echo $dashboardStats['videos']['active_videos'] ?? 0; ?> hoạt động</span>
                            <span><?php echo number_format($dashboardStats['videos']['total_video_views'] ?? 0); ?> lượt xem</span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                    <!-- Biểu đồ theo danh mục -->
                    <div class="chart-container">
                        <h3 class="chart-title">Tin tức theo danh mục</h3>
                        <?php 
                        $maxCount = max(array_column($dashboardStats['categories'], 'count'));
                        foreach ($dashboardStats['categories'] as $category): 
                            $percentage = $maxCount > 0 ? ($category['count'] / $maxCount) * 100 : 0;
                        ?>
                        <div class="chart-bar">
                            <div class="chart-label"><?php echo htmlspecialchars($category['name']); ?></div>
                            <div class="chart-progress">
                                <div class="chart-fill" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                            <div class="chart-value"><?php echo $category['count']; ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Thống kê theo tháng -->
                    <div class="chart-container">
                        <h3 class="chart-title">Hoạt động 6 tháng gần nhất</h3>
                        <?php 
                        $maxMonthly = max(array_column($dashboardStats['monthly'], 'count'));
                        foreach ($dashboardStats['monthly'] as $month): 
                            $percentage = $maxMonthly > 0 ? ($month['count'] / $maxMonthly) * 100 : 0;
                        ?>
                        <div class="chart-bar">
                            <div class="chart-label"><?php echo $month['month_display']; ?></div>
                            <div class="chart-progress">
                                <div class="chart-fill" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                            <div class="chart-value"><?php echo $month['count']; ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <!-- Tin tức phổ biến -->
                    <div class="recent-items">
                        <h3 class="chart-title">Tin tức phổ biến nhất</h3>
                        <?php foreach ($dashboardStats['popular_news'] as $news): ?>
                        <div class="recent-item">
                            <div class="recent-content">
                                <div class="recent-title"><?php echo htmlspecialchars($news['title']); ?></div>
                                <div class="recent-meta"><?php echo htmlspecialchars($news['category']); ?> • <?php echo date('d/m/Y', strtotime($news['published_at'])); ?></div>
                            </div>
                            <div class="recent-views"><?php echo number_format($news['views']); ?> lượt xem</div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Tin tức mới nhất -->
                    <div class="recent-items">
                        <h3 class="chart-title">Tin tức mới nhất</h3>
                        <?php foreach ($dashboardStats['recent_news'] as $news): ?>
                        <div class="recent-item">
                            <div class="recent-content">
                                <div class="recent-title"><?php echo htmlspecialchars($news['title']); ?></div>
                                <div class="recent-meta"><?php echo htmlspecialchars($news['category']); ?> • <?php echo date('d/m/Y', strtotime($news['published_at'])); ?></div>
                            </div>
                            <div class="recent-views"><?php echo number_format($news['views']); ?> lượt xem</div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Liên kết nhanh -->
                <div style="margin-top: 30px; text-align: center;">
                    <a href="them-tin.php" class="btn btn-primary" style="margin-right: 10px;">Thêm tin tức mới</a>
                    <a href="quan-ly-video.php" class="btn btn-secondary" style="margin-right: 10px;">Quản lý video</a>
                    <a href="tin-nhan-lien-he.php" class="btn btn-info">Xem tin nhắn liên hệ</a>
                </div>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>
    <?php include 'quick-actions-toolbar.php'; ?>

    <script>
        // Animate progress bars on load
        document.addEventListener('DOMContentLoaded', function() {
            const fills = document.querySelectorAll('.chart-fill');
            fills.forEach(fill => {
                const width = fill.style.width;
                fill.style.width = '0%';
                setTimeout(() => {
                    fill.style.width = width;
                }, 100);
            });
        });
    </script>
</body>
</html>