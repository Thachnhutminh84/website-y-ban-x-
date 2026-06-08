<?php
// File dashboard.php đã sửa lỗi SQL
// Sau khi test OK, đổi tên file này thành dashboard.php

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
    
    // Thống kê tin tức - ĐÃ SỬA: xóa dấu ; thừa
    $result = $conn->query("SELECT 
        COUNT(*) as total_news,
        SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published_news,
        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_news,
        COALESCE(SUM(views), 0) as total_views
        FROM news");
    $stats['news'] = $result ? $result->fetch_assoc() : [];
    
    // Thống kê theo danh mục - ĐÃ SỬA
    $result = $conn->query("SELECT c.name, COUNT(n.id) as count 
        FROM categories c 
        LEFT JOIN news n ON c.id = n.category_id AND n.status = 'published'
        GROUP BY c.id, c.name 
        ORDER BY count DESC");
    $stats['categories'] = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    
    // Tin tức mới nhất - ĐÃ SỬA
    $result = $conn->query("SELECT n.title, n.published_at, c.name as category, COALESCE(n.views, 0) as views
        FROM news n 
        LEFT JOIN categories c ON n.category_id = c.id
        WHERE n.status = 'published'
        ORDER BY n.published_at DESC 
        LIMIT 5");
    $stats['recent_news'] = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    
    // Thống kê theo tháng - ĐÃ SỬA
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
</head>
<body>
    <?php include 'header-menu.php'; ?>

    <main>
        <section class="page-header">
            <div class="container">
                <h2>Dashboard Quản trị</h2>
                <p>Tổng quan hoạt động website</p>
            </div>
        </section>

        <section class="dashboard">
            <div class="container">
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>📰 Tin tức</h3>
                        <p class="stat-number"><?php echo $dashboardStats['news']['total_news'] ?? 0; ?></p>
                        <p><?php echo $dashboardStats['news']['published_news'] ?? 0; ?> đã xuất bản</p>
                    </div>
                    
                    <div class="stat-card">
                        <h3>👁️ Lượt xem</h3>
                        <p class="stat-number"><?php echo number_format($dashboardStats['news']['total_views'] ?? 0); ?></p>
                    </div>
                    
                    <div class="stat-card">
                        <h3>📧 Liên hệ</h3>
                        <p class="stat-number"><?php echo $contactStats['total']; ?></p>
                        <p><?php echo $contactStats['new']; ?> chưa xử lý</p>
                    </div>
                </div>

                <div class="quick-actions" style="margin-top: 30px;">
                    <h3>Thao tác nhanh</h3>
                    <a href="them-tin.php" class="btn">➕ Thêm tin tức</a>
                    <a href="tin-tuc.php" class="btn">📰 Quản lý tin tức</a>
                    <a href="tin-nhan-lien-he.php" class="btn">📧 Tin nhắn</a>
                </div>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
