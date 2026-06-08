<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';

authRequireRole(['admin']);

$isLoggedIn = authIsLoggedIn();
$currentRole = authCurrentRole();
$displayName = authDisplayName();

// Get statistics
$conn = getDBConnection();
$stats = [
    'total_news' => 0,
    'published_news' => 0,
    'draft_news' => 0
];

if ($conn) {
    $result = $conn->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft
        FROM news");
    
    if ($result) {
        $row = $result->fetch_assoc();
        $stats['total_news'] = $row['total'] ?? 0;
        $stats['published_news'] = $row['published'] ?? 0;
        $stats['draft_news'] = $row['draft'] ?? 0;
    }
    
    // Get recent news
    $recentNews = [];
    $result = $conn->query("SELECT n.id, n.title, n.status, n.created_at, c.name as category 
                           FROM news n 
                           LEFT JOIN categories c ON n.category_id = c.id 
                           ORDER BY n.created_at DESC 
                           LIMIT 10");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $recentNews[] = $row;
        }
    }
    
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Nội dung - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard-style.css">
</head>
<body>
    <?php include 'header-menu.php'; ?>

    <main>
        <div class="admin-container">
            <div class="admin-header">
                <h1>📋 Quản lý Nội dung</h1>
                <div class="admin-actions">
                    <a href="dashboard.php" class="btn-secondary">← Quay lại Dashboard</a>
                    <a href="them-tin.php" class="btn-primary">➕ Thêm tin mới</a>
                </div>
            </div>

            <!-- Statistics -->
            <div class="stats-grid" style="margin-bottom: 30px;">
                <div class="stat-card">
                    <div class="stat-icon">📰</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['total_news']; ?></div>
                        <div class="stat-label">Tổng tin tức</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['published_news']; ?></div>
                        <div class="stat-label">Đã xuất bản</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📝</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $stats['draft_news']; ?></div>
                        <div class="stat-label">Bản nháp</div>
                    </div>
                </div>
            </div>

            <!-- Recent News -->
            <div class="content-section">
                <h2>Tin tức gần đây</h2>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tiêu đề</th>
                                <th>Danh mục</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentNews)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;">Chưa có tin tức nào</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentNews as $news): ?>
                                    <tr>
                                        <td><?php echo $news['id']; ?></td>
                                        <td><?php echo htmlspecialchars($news['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($news['category'] ?? 'Chưa phân loại', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $news['status']; ?>">
                                                <?php echo $news['status'] == 'published' ? 'Đã xuất bản' : 'Bản nháp'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($news['created_at'])); ?></td>
                                        <td>
                                            <a href="sua-tin.php?id=<?php echo $news['id']; ?>" class="btn-sm btn-primary">Sửa</a>
                                            <a href="chi-tiet-tin.php?id=<?php echo $news['id']; ?>" class="btn-sm btn-secondary">Xem</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions-section" style="margin-top: 30px;">
                <h2>Thao tác nhanh</h2>
                <div class="action-grid">
                    <a href="them-tin.php" class="action-card">
                        <div class="action-icon">➕</div>
                        <div class="action-title">Thêm tin tức</div>
                        <div class="action-desc">Tạo tin tức mới</div>
                    </a>
                    
                    <a href="tin-tuc.php" class="action-card">
                        <div class="action-icon">📰</div>
                        <div class="action-title">Quản lý tin tức</div>
                        <div class="action-desc">Xem tất cả tin tức</div>
                    </a>
                    
                    <a href="quan-ly-media.php" class="action-card">
                        <div class="action-icon">🖼️</div>
                        <div class="action-title">Quản lý Media</div>
                        <div class="action-desc">Quản lý hình ảnh, file</div>
                    </a>
                    
                    <a href="quan-ly-video.php" class="action-card">
                        <div class="action-icon">🎥</div>
                        <div class="action-title">Quản lý Video</div>
                        <div class="action-desc">Quản lý video</div>
                    </a>
                </div>
            </div>
        </div>
    </main>

    <style>
    .content-section {
        background: white;
        border-radius: 10px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .content-section h2 {
        margin-bottom: 20px;
        color: #2c3e50;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th,
    .data-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #e9ecef;
    }

    .data-table th {
        background: #f8f9fa;
        font-weight: 600;
        color: #2c3e50;
    }

    .data-table tr:hover {
        background: #f8f9fa;
    }

    .badge {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-published {
        background: #28a745;
        color: white;
    }

    .badge-draft {
        background: #6c757d;
        color: white;
    }

    .btn-sm {
        padding: 4px 12px;
        font-size: 13px;
        text-decoration: none;
        border-radius: 4px;
        display: inline-block;
        margin-right: 5px;
    }

    .btn-sm.btn-primary {
        background: #007bff;
        color: white;
    }

    .btn-sm.btn-secondary {
        background: #6c757d;
        color: white;
    }

    .action-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .action-card {
        background: white;
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .action-card:hover {
        border-color: #007bff;
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .action-icon {
        font-size: 48px;
        margin-bottom: 10px;
    }

    .action-title {
        font-size: 18px;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 5px;
    }

    .action-desc {
        font-size: 14px;
        color: #6c757d;
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .action-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>

    <?php include 'footer.php'; ?>
    <?php include 'quick-actions-toolbar.php'; ?>
</body>
</html>
