<?php
header("Content-Type: text/html; charset=utf-8");
session_start();
require_once 'config.php';
require_once 'auth.php';

// Kiểm tra quyền admin
if (!authIsLoggedIn() || !authHasPermission('manage_content')) {
    header("Location: dang-nhap.php");
    exit;
}

// Lấy danh sách tất cả file PHP
$allFiles = glob('*.php');
$excludeFiles = [
    'menu-thong-nhat.php', 
    'header-thong-nhat.php', 
    'ap-dung-menu-video-toan-bo.php',
    'kiem-tra-menu-video-toan-bo.php',
    'config.php',
    'auth.php'
];
$phpFiles = array_diff($allFiles, $excludeFiles);

// Kiểm tra trạng thái từng file
$fileStatus = [];
$totalFiles = 0;
$hasUnifiedMenu = 0;
$hasVideoMenu = 0;

foreach ($phpFiles as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        $unified = strpos($content, 'header-thong-nhat.php') !== false;
        $hasVideo = strpos($content, 'video.php') !== false || strpos($content, 'Video') !== false;
        $hasOldHeader = preg_match('/<header[^>]*>.*?<\/header>/s', $content);
        
        $fileStatus[$file] = [
            'has_unified' => $unified,
            'has_video_menu' => $hasVideo,
            'has_old_header' => $hasOldHeader,
            'size' => filesize($file),
            'modified' => filemtime($file),
            'is_video_page' => in_array($file, ['video.php', 'video-files.php', 'quan-ly-video.php', 'them-video.php', 'them-video-moi.php'])
        ];
        
        $totalFiles++;
        if ($unified) $hasUnifiedMenu++;
        if ($hasVideo) $hasVideoMenu++;
    }
}

// Tính toán thống kê
$unifiedPercent = $totalFiles > 0 ? round(($hasUnifiedMenu / $totalFiles) * 100) : 0;
$videoPercent = $totalFiles > 0 ? round(($hasVideoMenu / $totalFiles) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiểm tra Menu Video Toàn bộ - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard-style.css">
</head>
<body>
    <!-- Header thống nhất -->
    <?php include 'header-thong-nhat.php'; ?>

    <main>
        <div class="container">
            <div class="page-header">
                <h1>🎬 Kiểm tra Menu Video Toàn bộ Website</h1>
                <p>Theo dõi tiến độ áp dụng menu video cho tất cả các trang</p>
            </div>

            <!-- Thống kê tổng quan -->
            <div class="overview-stats">
                <div class="stat-card">
                    <div class="stat-icon">📄</div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $totalFiles; ?></div>
                        <div class="stat-label">Tổng file PHP</div>
                    </div>
                </div>
                
                <div class="stat-card success">
                    <div class="stat-icon">✅</div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $hasUnifiedMenu; ?></div>
                        <div class="stat-label">Có menu thống nhất</div>
                        <div class="stat-percent"><?php echo $unifiedPercent; ?>%</div>
                    </div>
                </div>
                
                <div class="stat-card video">
                    <div class="stat-icon">🎬</div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $hasVideoMenu; ?></div>
                        <div class="stat-label">Có menu video</div>
                        <div class="stat-percent"><?php echo $videoPercent; ?>%</div>
                    </div>
                </div>
                
                <div class="stat-card warning">
                    <div class="stat-icon">⚠️</div>
                    <div class="stat-info">
                        <div class="stat-number"><?php echo $totalFiles - $hasUnifiedMenu; ?></div>
                        <div class="stat-label">Cần cập nhật</div>
                        <div class="stat-percent"><?php echo 100 - $unifiedPercent; ?>%</div>
                    </div>
                </div>
            </div>

            <!-- Progress bars -->
            <div class="progress-section">
                <div class="progress-item">
                    <div class="progress-label">
                        <span>Menu thống nhất</span>
                        <span><?php echo $unifiedPercent; ?>%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $unifiedPercent; ?>%"></div>
                    </div>
                </div>
                
                <div class="progress-item">
                    <div class="progress-label">
                        <span>Menu video</span>
                        <span><?php echo $videoPercent; ?>%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill video" style="width: <?php echo $videoPercent; ?>%"></div>
                    </div>
                </div>
            </div>

            <!-- Danh sách file chi tiết -->
            <div class="files-section">
                <h2>📋 Chi tiết từng trang</h2>
                
                <div class="filter-tabs">
                    <button class="filter-tab active" onclick="filterFiles('all')">Tất cả (<?php echo $totalFiles; ?>)</button>
                    <button class="filter-tab" onclick="filterFiles('unified')">Có menu thống nhất (<?php echo $hasUnifiedMenu; ?>)</button>
                    <button class="filter-tab" onclick="filterFiles('video')">Có menu video (<?php echo $hasVideoMenu; ?>)</button>
                    <button class="filter-tab" onclick="filterFiles('need-update')">Cần cập nhật (<?php echo $totalFiles - $hasUnifiedMenu; ?>)</button>
                    <button class="filter-tab" onclick="filterFiles('video-pages')">Trang video</button>
                </div>
                
                <div class="files-grid">
                    <?php foreach ($fileStatus as $file => $status): ?>
                        <div class="file-card <?php 
                            echo $status['has_unified'] ? 'unified ' : 'need-update ';
                            echo $status['has_video_menu'] ? 'has-video ' : '';
                            echo $status['is_video_page'] ? 'video-page ' : '';
                        ?>" data-file="<?php echo $file; ?>">
                            
                            <div class="file-header">
                                <h3><?php echo $file; ?></h3>
                                <div class="file-badges">
                                    <?php if ($status['has_unified']): ?>
                                        <span class="badge success">✅ Menu thống nhất</span>
                                    <?php else: ?>
                                        <span class="badge warning">⚠️ Cần cập nhật</span>
                                    <?php endif; ?>
                                    
                                    <?php if ($status['has_video_menu']): ?>
                                        <span class="badge video">🎬 Có video</span>
                                    <?php endif; ?>
                                    
                                    <?php if ($status['is_video_page']): ?>
                                        <span class="badge special">⭐ Trang video</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="file-info">
                                <div class="file-meta">
                                    <span>📊 <?php echo number_format($status['size'] / 1024, 1); ?> KB</span>
                                    <span>📅 <?php echo date('d/m/Y H:i', $status['modified']); ?></span>
                                </div>
                                
                                <div class="file-status">
                                    <?php if ($status['has_unified']): ?>
                                        <div class="status-item success">
                                            <span class="status-icon">✅</span>
                                            <span>Đã có menu thống nhất với video</span>
                                        </div>
                                    <?php elseif ($status['has_old_header']): ?>
                                        <div class="status-item warning">
                                            <span class="status-icon">🔄</span>
                                            <span>Có header cũ - cần thay thế</span>
                                        </div>
                                    <?php else: ?>
                                        <div class="status-item error">
                                            <span class="status-icon">❌</span>
                                            <span>Chưa có header - cần thêm mới</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="file-actions">
                                <a href="<?php echo $file; ?>" target="_blank" class="btn-view">👀 Xem trang</a>
                                <?php if (!$status['has_unified']): ?>
                                    <button onclick="updateSingleFile('<?php echo $file; ?>')" class="btn-update">🔄 Cập nhật</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Actions -->
            <div class="actions-section">
                <h2>🛠️ Hành động</h2>
                
                <div class="action-buttons">
                    <a href="ap-dung-menu-video-toan-bo.php" class="btn-primary">
                        🚀 Áp dụng menu cho tất cả trang
                    </a>
                    <button onclick="checkAllPages()" class="btn-secondary">
                        🔍 Kiểm tra lại tất cả
                    </button>
                    <a href="huong-dan-menu-thong-nhat.php" class="btn-info">
                        📖 Hướng dẫn chi tiết
                    </a>
                    <button onclick="testVideoMenu()" class="btn-success">
                        🧪 Test menu video
                    </button>
                </div>
            </div>

            <!-- Video menu preview -->
            <div class="preview-section">
                <h2>🎬 Preview Menu Video</h2>
                
                <div class="menu-preview">
                    <div class="preview-item">
                        <h4>📺 Video chính thức</h4>
                        <p>Video từ database đã được duyệt và phê duyệt</p>
                        <a href="video.php" target="_blank">→ video.php</a>
                    </div>
                    
                    <div class="preview-item">
                        <h4>📁 Tất cả file video</h4>
                        <p>Tất cả file video và audio trong thư mục videos/</p>
                        <a href="video-files.php" target="_blank">→ video-files.php</a>
                    </div>
                    
                    <div class="preview-item">
                        <h4>🎵 File audio</h4>
                        <p>Các file âm thanh (WAV, MP3) được lọc riêng</p>
                        <a href="video-files.php?type=audio" target="_blank">→ video-files.php?type=audio</a>
                    </div>
                    
                    <div class="preview-item">
                        <h4>⭐ Video nổi bật</h4>
                        <p>Video được đánh dấu nổi bật và quan trọng</p>
                        <a href="video.php?featured=1" target="_blank">→ video.php?featured=1</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <style>
    .page-header {
        text-align: center;
        padding: 40px 0;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        margin: 30px 0;
    }

    .overview-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin: 30px 0;
    }

    .stat-card {
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 15px;
        border-left: 4px solid #007bff;
    }

    .stat-card.success { border-left-color: #28a745; }
    .stat-card.warning { border-left-color: #ffc107; }
    .stat-card.video { border-left-color: #6f42c1; }

    .stat-icon {
        font-size: 32px;
    }

    .stat-number {
        font-size: 28px;
        font-weight: bold;
        color: #2c3e50;
    }

    .stat-label {
        color: #666;
        font-size: 14px;
    }

    .stat-percent {
        color: #007bff;
        font-weight: 600;
        font-size: 12px;
    }

    .progress-section {
        background: white;
        padding: 25px;
        border-radius: 10px;
        margin: 30px 0;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .progress-item {
        margin-bottom: 20px;
    }

    .progress-label {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-weight: 600;
    }

    .progress-bar {
        height: 10px;
        background: #e9ecef;
        border-radius: 5px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: #28a745;
        transition: width 0.3s ease;
    }

    .progress-fill.video {
        background: #6f42c1;
    }

    .files-section {
        margin: 40px 0;
    }

    .filter-tabs {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .filter-tab {
        padding: 10px 20px;
        border: 2px solid #e9ecef;
        background: white;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .filter-tab.active,
    .filter-tab:hover {
        border-color: #007bff;
        background: #007bff;
        color: white;
    }

    .files-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
    }

    .file-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-left: 4px solid;
        transition: all 0.3s ease;
    }

    .file-card.unified {
        border-left-color: #28a745;
    }

    .file-card.need-update {
        border-left-color: #ffc107;
    }

    .file-card.video-page {
        background: linear-gradient(135deg, #f8f9ff 0%, #fff8f9 100%);
    }

    .file-header {
        margin-bottom: 15px;
    }

    .file-header h3 {
        margin: 0 0 10px 0;
        color: #2c3e50;
        font-size: 16px;
    }

    .file-badges {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge.success {
        background: #d4edda;
        color: #155724;
    }

    .badge.warning {
        background: #fff3cd;
        color: #856404;
    }

    .badge.video {
        background: #e7e3ff;
        color: #6f42c1;
    }

    .badge.special {
        background: #fff3cd;
        color: #856404;
    }

    .file-info {
        margin-bottom: 15px;
    }

    .file-meta {
        display: flex;
        gap: 15px;
        margin-bottom: 10px;
        font-size: 12px;
        color: #666;
    }

    .status-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }

    .status-item.success { color: #28a745; }
    .status-item.warning { color: #ffc107; }
    .status-item.error { color: #dc3545; }

    .file-actions {
        display: flex;
        gap: 10px;
    }

    .btn-view, .btn-update {
        padding: 6px 12px;
        border-radius: 4px;
        text-decoration: none;
        font-size: 12px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-view {
        background: #17a2b8;
        color: white;
    }

    .btn-update {
        background: #ffc107;
        color: #212529;
    }

    .actions-section {
        background: white;
        padding: 30px;
        border-radius: 10px;
        margin: 30px 0;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        text-align: center;
    }

    .action-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 20px;
    }

    .btn-primary, .btn-secondary, .btn-info, .btn-success {
        padding: 12px 24px;
        border-radius: 5px;
        text-decoration: none;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-primary { background: #007bff; color: white; }
    .btn-secondary { background: #6c757d; color: white; }
    .btn-info { background: #17a2b8; color: white; }
    .btn-success { background: #28a745; color: white; }

    .preview-section {
        background: white;
        padding: 30px;
        border-radius: 10px;
        margin: 30px 0;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .menu-preview {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .preview-item {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #6f42c1;
    }

    .preview-item h4 {
        margin: 0 0 10px 0;
        color: #2c3e50;
    }

    .preview-item p {
        margin: 0 0 10px 0;
        color: #666;
        font-size: 14px;
    }

    .preview-item a {
        color: #6f42c1;
        text-decoration: none;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .files-grid {
            grid-template-columns: 1fr;
        }
        
        .action-buttons {
            flex-direction: column;
            align-items: center;
        }
        
        .filter-tabs {
            flex-direction: column;
        }
    }
    </style>

    <script>
    function filterFiles(type) {
        const cards = document.querySelectorAll('.file-card');
        const tabs = document.querySelectorAll('.filter-tab');
        
        // Update active tab
        tabs.forEach(tab => tab.classList.remove('active'));
        event.target.classList.add('active');
        
        // Filter cards
        cards.forEach(card => {
            let show = false;
            
            switch(type) {
                case 'all':
                    show = true;
                    break;
                case 'unified':
                    show = card.classList.contains('unified');
                    break;
                case 'video':
                    show = card.classList.contains('has-video');
                    break;
                case 'need-update':
                    show = card.classList.contains('need-update');
                    break;
                case 'video-pages':
                    show = card.classList.contains('video-page');
                    break;
            }
            
            card.style.display = show ? 'block' : 'none';
        });
    }

    function updateSingleFile(filename) {
        if (confirm(`Cập nhật menu thống nhất cho file "${filename}"?`)) {
            // Redirect to update page with specific file
            window.location.href = `ap-dung-menu-video-toan-bo.php?file=${filename}`;
        }
    }

    function checkAllPages() {
        location.reload();
    }

    function testVideoMenu() {
        // Open video pages in new tabs
        window.open('video.php', '_blank');
        window.open('video-files.php', '_blank');
    }
    </script>
</body>
</html>