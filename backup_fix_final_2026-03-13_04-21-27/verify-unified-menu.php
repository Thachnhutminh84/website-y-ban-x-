<?php
header("Content-Type: text/html; charset=utf-8");
session_start();
require_once 'config.php';
require_once 'auth.php';

// Kiểm tra quyền admin
if (!authIsLoggedIn() || !authHasPermission('manage_content')) {
    die("❌ Cần quyền admin để chạy script này. <a href='dang-nhap.php'>Đăng nhập</a>");
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiểm tra Menu Thống nhất - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Header thống nhất -->
        <?php include 'menu-don-gian.php'; ?>

    <main>
        <div class="container">
            <h1>🔍 Kiểm tra Menu Thống nhất</h1>
            
            <?php
            // Lấy danh sách tất cả file PHP
            $allFiles = glob('*.php');
            $excludeFiles = [
                'menu-thong-nhat.php', 
                'header-thong-nhat.php', 
                'verify-unified-menu.php',
                'apply-unified-menu-all.php'
            ];
            $phpFiles = array_diff($allFiles, $excludeFiles);
            
            $hasUnified = [];
            $needsUpdate = [];
            $hasOldMenu = [];
            $noMenu = [];
            
            foreach ($phpFiles as $file) {
                if (!file_exists($file)) continue;
                
                $content = file_get_contents($file);
                
                $unified = strpos($content, 'header-thong-nhat.php') !== false;
                $oldHeader = preg_match('/<header[^>]*>.*?<\/header>/s', $content);
                $oldNav = preg_match('/<nav[^>]*>.*?<\/nav>/s', $content);
                
                if ($unified) {
                    $hasUnified[] = $file;
                } else if ($oldHeader || $oldNav) {
                    $hasOldMenu[] = $file;
                    $needsUpdate[] = $file;
                } else {
                    $noMenu[] = $file;
                    $needsUpdate[] = $file;
                }
            }
            ?>
            
            <div class="stats-section">
                <div class="stat-card success">
                    <div class="stat-number"><?php echo count($hasUnified); ?></div>
                    <div class="stat-label">Đã có menu thống nhất</div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-number"><?php echo count($hasOldMenu); ?></div>
                    <div class="stat-label">Có menu cũ</div>
                </div>
                <div class="stat-card error">
                    <div class="stat-number"><?php echo count($noMenu); ?></div>
                    <div class="stat-label">Chưa có menu</div>
                </div>
                <div class="stat-card info">
                    <div class="stat-number"><?php echo count($phpFiles); ?></div>
                    <div class="stat-label">Tổng file PHP</div>
                </div>
            </div>
            
            <?php if (!empty($needsUpdate)): ?>
                <div class="action-section">
                    <h2>🚀 Cần cập nhật</h2>
                    <p>Có <strong><?php echo count($needsUpdate); ?></strong> file cần áp dụng menu thống nhất:</p>
                    
                    <div class="action-buttons">
                        <a href="apply-unified-menu-all.php" class="btn-primary">
                            🚀 Áp dụng menu cho tất cả
                        </a>
                        <button onclick="showFileList()" class="btn-secondary">
                            📋 Xem danh sách file
                        </button>
                    </div>
                    
                    <div id="fileList" style="display: none; margin-top: 20px;">
                        <h3>📄 File cần cập nhật:</h3>
                        <div class="file-grid">
                            <?php foreach ($needsUpdate as $file): ?>
                                <div class="file-item need-update">
                                    <h4><?php echo $file; ?></h4>
                                    <p>
                                        <?php if (in_array($file, $hasOldMenu)): ?>
                                            ⚠️ Có menu cũ - cần thay thế
                                        <?php else: ?>
                                            ❌ Chưa có menu - cần thêm mới
                                        <?php endif; ?>
                                    </p>
                                    <a href="<?php echo $file; ?>" target="_blank" class="btn-view">👀 Xem</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="results-section">
                <h2>✅ File đã có menu thống nhất</h2>
                
                <?php if (empty($hasUnified)): ?>
                    <p>Chưa có file nào sử dụng menu thống nhất.</p>
                <?php else: ?>
                    <div class="file-grid">
                        <?php foreach ($hasUnified as $file): ?>
                            <div class="file-item unified">
                                <h4><?php echo $file; ?></h4>
                                <p>✅ Đã có menu thống nhất</p>
                                <a href="<?php echo $file; ?>" target="_blank" class="btn-view">👀 Xem</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="test-section">
                <h2>🧪 Test Menu Video</h2>
                <p>Kiểm tra menu video có hiển thị đúng trên các trang:</p>
                
                <div class="test-grid">
                    <?php
                    $testPages = [
                        'index.php' => ['🏠 Trang chủ', 'Kiểm tra menu video trên trang chủ'],
                        'tin-tuc.php' => ['📰 Tin tức', 'Kiểm tra dropdown video trong menu'],
                        'video.php' => ['📺 Video', 'Kiểm tra trang video chính'],
                        'video-files.php' => ['📁 File Video', 'Kiểm tra trang duyệt file'],
                        'lanh-dao.php' => ['👥 Lãnh đạo', 'Kiểm tra menu trên trang lãnh đạo'],
                        'phong-ban.php' => ['🏢 Phòng ban', 'Kiểm tra menu trên trang phòng ban'],
                        'lien-he.php' => ['📞 Liên hệ', 'Kiểm tra menu trên trang liên hệ'],
                        'dashboard.php' => ['📊 Dashboard', 'Kiểm tra menu admin']
                    ];
                    
                    foreach ($testPages as $page => $info):
                        if (file_exists($page)):
                            $hasMenu = in_array($page, $hasUnified);
                    ?>
                        <div class="test-card <?php echo $hasMenu ? 'has-menu' : 'no-menu'; ?>">
                            <h4><?php echo $info[0]; ?></h4>
                            <p><?php echo $info[1]; ?></p>
                            <div class="test-status">
                                <?php if ($hasMenu): ?>
                                    <span class="status-badge success">✅ Có menu thống nhất</span>
                                <?php else: ?>
                                    <span class="status-badge error">❌ Chưa có menu thống nhất</span>
                                <?php endif; ?>
                            </div>
                            <a href="<?php echo $page; ?>" target="_blank" class="btn-test">🧪 Test</a>
                        </div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
            </div>
            
            <div class="summary-section">
                <h2>📋 Tóm tắt</h2>
                
                <?php if (count($hasUnified) == count($phpFiles)): ?>
                    <div class="summary-success">
                        <h3>🎉 Hoàn hảo!</h3>
                        <p>Tất cả <?php echo count($phpFiles); ?> file PHP đều đã có menu thống nhất. Website của bạn bây giờ có menu video nhất quán trên mọi trang.</p>
                    </div>
                <?php else: ?>
                    <div class="summary-warning">
                        <h3>⚠️ Cần hoàn thiện</h3>
                        <p>Còn <?php echo count($needsUpdate); ?> file chưa có menu thống nhất. Hãy chạy script áp dụng menu để hoàn thiện.</p>
                        <a href="apply-unified-menu-all.php" class="btn-primary">🚀 Áp dụng ngay</a>
                    </div>
                <?php endif; ?>
                
                <div class="menu-features">
                    <h3>🎯 Menu Video bao gồm:</h3>
                    <ul>
                        <li>📺 <strong>Video chính thức</strong> - Video từ database đã được duyệt</li>
                        <li>📁 <strong>Tất cả file video</strong> - Tất cả file video trong thư mục</li>
                        <li>🎵 <strong>File audio</strong> - Các file âm thanh (WAV, MP3)</li>
                        <li>⭐ <strong>Video nổi bật</strong> - Video được đánh dấu nổi bật</li>
                    </ul>
                </div>
            </div>
        </div>
    </main>

    <script>
    function showFileList() {
        const fileList = document.getElementById('fileList');
        if (fileList.style.display === 'none') {
            fileList.style.display = 'block';
        } else {
            fileList.style.display = 'none';
        }
    }
    </script>

    <style>
    .stats-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin: 30px 0;
    }

    .stat-card {
        background: white;
        padding: 30px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-left: 4px solid;
    }

    .stat-card.success { border-left-color: #28a745; }
    .stat-card.warning { border-left-color: #ffc107; }
    .stat-card.error { border-left-color: #dc3545; }
    .stat-card.info { border-left-color: #17a2b8; }

    .stat-number {
        font-size: 36px;
        font-weight: bold;
        color: #2c3e50;
        margin-bottom: 10px;
    }

    .stat-label {
        color: #666;
        font-size: 14px;
    }

    .action-section, .results-section, .test-section, .summary-section {
        background: white;
        padding: 30px;
        margin: 30px 0;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .action-buttons {
        display: flex;
        gap: 15px;
        margin: 20px 0;
    }

    .btn-primary, .btn-secondary, .btn-view, .btn-test {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-block;
    }

    .btn-primary { background: #007bff; color: white; }
    .btn-secondary { background: #6c757d; color: white; }
    .btn-view { background: #17a2b8; color: white; }
    .btn-test { background: #28a745; color: white; }

    .btn-primary:hover { background: #0056b3; }
    .btn-secondary:hover { background: #545b62; }
    .btn-view:hover { background: #138496; }
    .btn-test:hover { background: #218838; }

    .file-grid, .test-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .file-item, .test-card {
        border: 2px solid;
        padding: 20px;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .file-item.unified, .test-card.has-menu {
        border-color: #28a745;
        background: #f8fff9;
    }

    .file-item.need-update, .test-card.no-menu {
        border-color: #ffc107;
        background: #fffdf5;
    }

    .file-item h4, .test-card h4 {
        margin: 0 0 10px 0;
        color: #2c3e50;
    }

    .test-status {
        margin: 10px 0;
    }

    .status-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-badge.success {
        background: #d4edda;
        color: #155724;
    }

    .status-badge.error {
        background: #f8d7da;
        color: #721c24;
    }

    .summary-success {
        background: #d4edda;
        padding: 25px;
        border-radius: 8px;
        border-left: 4px solid #28a745;
        color: #155724;
    }

    .summary-warning {
        background: #fff3cd;
        padding: 25px;
        border-radius: 8px;
        border-left: 4px solid #ffc107;
        color: #856404;
    }

    .menu-features {
        background: #e7f3ff;
        padding: 25px;
        border-radius: 8px;
        border-left: 4px solid #007bff;
        margin-top: 20px;
    }

    .menu-features h3 {
        color: #0056b3;
        margin-bottom: 15px;
    }

    .menu-features ul {
        margin: 0;
        padding-left: 20px;
    }

    .menu-features li {
        margin: 8px 0;
    }
    </style>
</body>
</html>