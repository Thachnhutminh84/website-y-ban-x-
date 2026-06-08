<?php
header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'auth.php';

// Kiểm tra đăng nhập
if (!authIsLoggedIn()) {
    header('Location: dang-nhap.php');
    exit;
}

$page_title = "Dashboard Thống kê - UBND Xã Long Hiệp";

// Lấy thống kê từ database
$conn = getDBConnection();
$stats = [
    'news' => ['total' => 0, 'published' => 0, 'draft' => 0, 'this_month' => 0],
    'videos' => ['total' => 0, 'active' => 0, 'views' => 0],
    'staff' => ['total' => 0, 'active' => 0, 'departments' => 0],
    'users' => ['total' => 0, 'active' => 0, 'pending' => 0],
    'messages' => ['total' => 0, 'unread' => 0, 'this_week' => 0]
];

$recent_news = [];
$recent_activities = [];
$top_videos = [];

if ($conn) {
    // Thống kê tin tức
    $news_stats = $conn->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
        SUM(CASE WHEN MONTH(published_at) = MONTH(CURRENT_DATE()) AND YEAR(published_at) = YEAR(CURRENT_DATE()) THEN 1 ELSE 0 END) as this_month
        FROM news");
    
    if ($news_stats) {
        $stats['news'] = $news_stats->fetch_assoc();
    }
    
    // Thống kê video
    $video_stats = $conn->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
        SUM(views) as views
        FROM videos");
    
    if ($video_stats) {
        $stats['videos'] = $video_stats->fetch_assoc();
    }
    
    // Thống kê nhân sự
    $staff_stats = $conn->query("SELECT 
        COUNT(DISTINCT ds.id) as total,
        SUM(CASE WHEN ds.is_active = 1 THEN 1 ELSE 0 END) as active,
        COUNT(DISTINCT ds.department_id) as departments
        FROM department_staff ds");
    
    if ($staff_stats) {
        $stats['staff'] = $staff_stats->fetch_assoc();
    }
    
    // Thống kê người dùng
    $user_stats = $conn->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN is_approved = 1 THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN is_approved = 0 THEN 1 ELSE 0 END) as pending
        FROM users");
    
    if ($user_stats) {
        $stats['users'] = $user_stats->fetch_assoc();
    }
    
    // Thống kê tin nhắn liên hệ
    $message_stats = $conn->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as unread,
        SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as this_week
        FROM contact_messages");
    
    if ($message_stats) {
        $stats['messages'] = $message_stats->fetch_assoc();
    }
    
    // Tin tức mới nhất
    $recent_news_query = $conn->query("SELECT id, title, published_at, status FROM news ORDER BY published_at DESC LIMIT 5");
    if ($recent_news_query) {
        while ($row = $recent_news_query->fetch_assoc()) {
            $recent_news[] = $row;
        }
    }
    
    // Video xem nhiều nhất
    $top_videos_query = $conn->query("SELECT id, title, views FROM videos WHERE is_active = 1 ORDER BY views DESC LIMIT 5");
    if ($top_videos_query) {
        while ($row = $top_videos_query->fetch_assoc()) {
            $top_videos[] = $row;
        }
    }
    
    // Hoạt động gần đây
    $activities_query = $conn->query("SELECT action, table_name, description, created_at FROM activity_logs ORDER BY created_at DESC LIMIT 10");
    if ($activities_query) {
        while ($row = $activities_query->fetch_assoc()) {
            $recent_activities[] = $row;
        }
    }
}

include 'header-menu.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.css">

<style>
.dashboard-page {
    max-width: 1400px;
    margin: 40px auto;
    padding: 0 20px;
}

.dashboard-header {
    margin-bottom: 40px;
}

.dashboard-header h1 {
    font-size: 32px;
    color: #333;
    margin-bottom: 10px;
}

.dashboard-header p {
    color: #666;
    font-size: 16px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.stat-card {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    border-left: 5px solid var(--primary);
    transition: all 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.stat-card.news { border-left-color: var(--primary); }
.stat-card.videos { border-left-color: #e74c3c; }
.stat-card.staff { border-left-color: #27ae60; }
.stat-card.users { border-left-color: #f39c12; }
.stat-card.messages { border-left-color: #9b59b6; }

.stat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.stat-icon {
    font-size: 36px;
    opacity: 0.8;
}

.stat-card.news .stat-icon { color: var(--primary); }
.stat-card.videos .stat-icon { color: #e74c3c; }
.stat-card.staff .stat-icon { color: #27ae60; }
.stat-card.users .stat-icon { color: #f39c12; }
.stat-card.messages .stat-icon { color: #9b59b6; }

.stat-title {
    font-size: 14px;
    color: #999;
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.stat-number {
    font-size: 42px;
    font-weight: 700;
    color: #333;
    margin-bottom: 15px;
}

.stat-details {
    display: flex;
    gap: 20px;
    font-size: 13px;
    color: #666;
}

.stat-detail {
    display: flex;
    flex-direction: column;
}

.stat-detail strong {
    font-size: 18px;
    color: #333;
}

.content-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
    margin-bottom: 40px;
}

.content-card {
    background: white;
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}

.content-card h2 {
    font-size: 20px;
    color: #333;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.list-item {
    padding: 15px 0;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.list-item:last-child {
    border-bottom: none;
}

.list-item-title {
    font-size: 15px;
    color: #333;
    font-weight: 500;
    flex: 1;
}

.list-item-meta {
    font-size: 13px;
    color: #999;
}

.badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.badge.published { background: #d4edda; color: #155724; }
.badge.draft { background: #fff3cd; color: #856404; }
.badge.pending { background: #f8d7da; color: #721c24; }

.activity-item {
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f0;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-action {
    font-size: 14px;
    color: #333;
    font-weight: 500;
    margin-bottom: 5px;
}

.activity-time {
    font-size: 12px;
    color: #999;
}

.chart-container {
    position: relative;
    height: 300px;
    margin-top: 20px;
}

@media (max-width: 1024px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="dashboard-page">
    <div class="dashboard-header">
        <h1><i class="fas fa-chart-line"></i> Dashboard Thống kê</h1>
        <p>Tổng quan hoạt động hệ thống UBND Xã Long Hiệp</p>
    </div>
    
    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card news">
            <div class="stat-header">
                <div class="stat-title">Tin tức</div>
                <div class="stat-icon"><i class="fas fa-newspaper"></i></div>
            </div>
            <div class="stat-number"><?php echo number_format($stats['news']['total']); ?></div>
            <div class="stat-details">
                <div class="stat-detail">
                    <strong><?php echo number_format($stats['news']['published']); ?></strong>
                    <span>Đã đăng</span>
                </div>
                <div class="stat-detail">
                    <strong><?php echo number_format($stats['news']['draft']); ?></strong>
                    <span>Nháp</span>
                </div>
                <div class="stat-detail">
                    <strong><?php echo number_format($stats['news']['this_month']); ?></strong>
                    <span>Tháng này</span>
                </div>
            </div>
        </div>
        
        <div class="stat-card videos">
            <div class="stat-header">
                <div class="stat-title">Video</div>
                <div class="stat-icon"><i class="fas fa-video"></i></div>
            </div>
            <div class="stat-number"><?php echo number_format($stats['videos']['total']); ?></div>
            <div class="stat-details">
                <div class="stat-detail">
                    <strong><?php echo number_format($stats['videos']['active']); ?></strong>
                    <span>Đang hoạt động</span>
                </div>
                <div class="stat-detail">
                    <strong><?php echo number_format($stats['videos']['views']); ?></strong>
                    <span>Lượt xem</span>
                </div>
            </div>
        </div>
        
        <div class="stat-card staff">
            <div class="stat-header">
                <div class="stat-title">Nhân sự</div>
                <div class="stat-icon"><i class="fas fa-users"></i></div>
            </div>
            <div class="stat-number"><?php echo number_format($stats['staff']['total']); ?></div>
            <div class="stat-details">
                <div class="stat-detail">
                    <strong><?php echo number_format($stats['staff']['active']); ?></strong>
                    <span>Đang làm việc</span>
                </div>
                <div class="stat-detail">
                    <strong><?php echo number_format($stats['staff']['departments']); ?></strong>
                    <span>Phòng ban</span>
                </div>
            </div>
        </div>
        
        <div class="stat-card users">
            <div class="stat-header">
                <div class="stat-title">Người dùng</div>
                <div class="stat-icon"><i class="fas fa-user-shield"></i></div>
            </div>
            <div class="stat-number"><?php echo number_format($stats['users']['total']); ?></div>
            <div class="stat-details">
                <div class="stat-detail">
                    <strong><?php echo number_format($stats['users']['active']); ?></strong>
                    <span>Đã duyệt</span>
                </div>
                <div class="stat-detail">
                    <strong><?php echo number_format($stats['users']['pending']); ?></strong>
                    <span>Chờ duyệt</span>
                </div>
            </div>
        </div>
        
        <div class="stat-card messages">
            <div class="stat-header">
                <div class="stat-title">Tin nhắn</div>
                <div class="stat-icon"><i class="fas fa-envelope"></i></div>
            </div>
            <div class="stat-number"><?php echo number_format($stats['messages']['total']); ?></div>
            <div class="stat-details">
                <div class="stat-detail">
                    <strong><?php echo number_format($stats['messages']['unread']); ?></strong>
                    <span>Chưa đọc</span>
                </div>
                <div class="stat-detail">
                    <strong><?php echo number_format($stats['messages']['this_week']); ?></strong>
                    <span>Tuần này</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Content Grid -->
    <div class="content-grid">
        <div class="content-card">
            <h2><i class="fas fa-newspaper"></i> Tin tức mới nhất</h2>
            <?php if (!empty($recent_news)): ?>
                <?php foreach ($recent_news as $news): ?>
                <div class="list-item">
                    <div class="list-item-title">
                        <a href="chi-tiet-tin.php?id=<?php echo $news['id']; ?>" style="color: #333; text-decoration: none;">
                            <?php echo htmlspecialchars($news['title']); ?>
                        </a>
                    </div>
                    <div class="list-item-meta">
                        <span class="badge <?php echo $news['status']; ?>">
                            <?php echo $news['status'] === 'published' ? 'Đã đăng' : 'Nháp'; ?>
                        </span>
                        <?php echo date('d/m/Y', strtotime($news['published_at'])); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #999; text-align: center; padding: 20px;">Chưa có tin tức</p>
            <?php endif; ?>
        </div>
        
        <div class="content-card">
            <h2><i class="fas fa-fire"></i> Video xem nhiều</h2>
            <?php if (!empty($top_videos)): ?>
                <?php foreach ($top_videos as $video): ?>
                <div class="list-item">
                    <div class="list-item-title">
                        <?php echo htmlspecialchars($video['title']); ?>
                    </div>
                    <div class="list-item-meta">
                        <i class="fas fa-eye"></i> <?php echo number_format($video['views']); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #999; text-align: center; padding: 20px;">Chưa có video</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Activity Log -->
    <div class="content-card">
        <h2><i class="fas fa-history"></i> Hoạt động gần đây</h2>
        <?php if (!empty($recent_activities)): ?>
            <?php foreach ($recent_activities as $activity): ?>
            <div class="activity-item">
                <div class="activity-action">
                    <i class="fas fa-circle" style="font-size: 6px; color: var(--primary);"></i>
                    <?php echo htmlspecialchars($activity['description'] ?? $activity['action']); ?>
                </div>
                <div class="activity-time">
                    <?php echo date('d/m/Y H:i', strtotime($activity['created_at'])); ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: #999; text-align: center; padding: 20px;">Chưa có hoạt động</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
