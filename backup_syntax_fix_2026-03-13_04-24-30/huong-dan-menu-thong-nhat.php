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

$allPages = [];
$menuPages = [];

// Quét tất cả file PHP trong thư mục
$files = glob('*.php');
foreach ($files as $file) {
    if ($file !== 'menu-thong-nhat.php' && $file !== 'header-thong-nhat.php') {
        $allPages[] = $file;
        
        // Kiểm tra xem file có sử dụng menu thống nhất chưa
        $content = file_get_contents($file);
        if (strpos($content, 'header-thong-nhat.php') !== false) {
            $menuPages[] = $file;
        }
    }
}

$totalPages = count($allPages);
$updatedPages = count($menuPages);
$remainingPages = $totalPages - $updatedPages;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hướng dẫn Menu Thống nhất - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard-style.css">
</head>
<body>
    <!-- Header thống nhất -->
    <?php include 'menu-don-gian.php'; ?>

    <main>
        <div class="container">
            <div class="guide-header">
                <h1>📋 Hướng dẫn Menu Thống nhất</h1>
                <p>Cách áp dụng menu thống nhất cho toàn bộ website</p>
            </div>

            <!-- Thống kê -->
            <div class="stats-section">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalPages; ?></div>
                    <div class="stat-label">Tổng trang PHP</div>
                </div>
                <div class="stat-card success">
                    <div class="stat-number"><?php echo $updatedPages; ?></div>
                    <div class="stat-label">Đã cập nhật</div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-number"><?php echo $remainingPages; ?></div>
                    <div class="stat-label">Chưa cập nhật</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo round(($updatedPages / $totalPages) * 100); ?>%</div>
                    <div class="stat-label">Tiến độ</div>
                </div>
            </div>

            <!-- Hướng dẫn từng bước -->
            <div class="guide-section">
                <h2>🚀 Hướng dẫn áp dụng</h2>
                
                <div class="step-card">
                    <h3>Bước 1: Thay thế header cũ</h3>
                    <p>Tìm và thay thế đoạn code header cũ bằng:</p>
                    <div class="code-block">
                        <code>&lt;?php include 'header-thong-nhat.php'; ?&gt;</code>
                    </div>
                    <p><strong>Thay thế:</strong> Toàn bộ thẻ <code>&lt;header&gt;...&lt;/header&gt;</code> và menu bên trong</p>
                </div>

                <div class="step-card">
                    <h3>Bước 2: Kiểm tra dependencies</h3>
                    <p>Đảm bảo trang có các require cần thiết:</p>
                    <div class="code-block">
                        <code>
require_once 'config.php';<br>
require_once 'auth.php';
                        </code>
                    </div>
                </div>

                <div class="step-card">
                    <h3>Bước 3: Xóa CSS menu cũ</h3>
                    <p>Xóa các CSS liên quan đến menu cũ để tránh conflict:</p>
                    <ul>
                        <li>CSS cho <code>.menu</code></li>
                        <li>CSS cho <code>.dropdown</code></li>
                        <li>CSS cho <code>header nav</code></li>
                    </ul>
                </div>

                <div class="step-card">
                    <h3>Bước 4: Test và điều chỉnh</h3>
                    <p>Kiểm tra:</p>
                    <ul>
                        <li>Menu hiển thị đúng</li>
                        <li>Active state hoạt động</li>
                        <li>Dropdown menu mở/đóng</li>
                        <li>Responsive trên mobile</li>
                    </ul>
                </div>
            </div>

            <!-- Danh sách trang -->
            <div class="pages-section">
                <h2>📄 Trạng thái các trang</h2>
                
                <div class="pages-grid">
                    <div class="pages-column">
                        <h3>✅ Đã cập nhật (<?php echo $updatedPages; ?>)</h3>
                        <div class="pages-list">
                            <?php foreach ($menuPages as $page): ?>
                                <div class="page-item success">
                                    <span class="page-name"><?php echo $page; ?></span>
                                    <a href="<?php echo $page; ?>" target="_blank" class="page-link">Xem</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="pages-column">
                        <h3>⏳ Chưa cập nhật (<?php echo $remainingPages; ?>)</h3>
                        <div class="pages-list">
                            <?php foreach ($allPages as $page): ?>
                                <?php if (!in_array($page, $menuPages)): ?>
                                    <div class="page-item warning">
                                        <span class="page-name"><?php echo $page; ?></span>
                                        <a href="<?php echo $page; ?>" target="_blank" class="page-link">Xem</a>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ví dụ code -->
            <div class="example-section">
                <h2>💡 Ví dụ thực tế</h2>
                
                <div class="example-card">
                    <h3>Trước khi cập nhật:</h3>
                    <div class="code-block old">
                        <code>
&lt;header&gt;<br>
&nbsp;&nbsp;&lt;div class="container"&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&lt;div class="logo"&gt;...&lt;/div&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&lt;nav&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;ul class="menu"&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;li&gt;&lt;a href="index.php"&gt;Trang chủ&lt;/a&gt;&lt;/li&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;...<br>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;/ul&gt;<br>
&nbsp;&nbsp;&nbsp;&nbsp;&lt;/nav&gt;<br>
&nbsp;&nbsp;&lt;/div&gt;<br>
&lt;/header&gt;
                        </code>
                    </div>
                </div>
                
                <div class="example-card">
                    <h3>Sau khi cập nhật:</h3>
                    <div class="code-block new">
                        <code>
&lt;?php include 'header-thong-nhat.php'; ?&gt;
                        </code>
                    </div>
                </div>
            </div>

            <!-- Tools -->
            <div class="tools-section">
                <h2>🛠️ Tools hỗ trợ</h2>
                
                <div class="tool-buttons">
                    <button onclick="generateUpdateScript()" class="btn-tool">
                        📝 Tạo script cập nhật tự động
                    </button>
                    <button onclick="checkAllPages()" class="btn-tool">
                        🔍 Kiểm tra tất cả trang
                    </button>
                    <button onclick="downloadBackup()" class="btn-tool">
                        💾 Backup trước khi cập nhật
                    </button>
                </div>
            </div>
        </div>
    </main>

    <style>
    .guide-header {
        text-align: center;
        padding: 40px 0;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        margin: 30px 0;
    }

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
        border-left: 4px solid #007bff;
    }

    .stat-card.success {
        border-left-color: #28a745;
    }

    .stat-card.warning {
        border-left-color: #ffc107;
    }

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

    .guide-section {
        margin: 40px 0;
    }

    .step-card {
        background: white;
        padding: 25px;
        border-radius: 10px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-left: 4px solid #007bff;
    }

    .step-card h3 {
        color: #2c3e50;
        margin-bottom: 15px;
    }

    .code-block {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        margin: 15px 0;
        border-left: 3px solid #007bff;
    }

    .code-block.old {
        border-left-color: #dc3545;
        background: #fff5f5;
    }

    .code-block.new {
        border-left-color: #28a745;
        background: #f0fff4;
    }

    .code-block code {
        font-family: 'Courier New', monospace;
        font-size: 14px;
        color: #2c3e50;
    }

    .pages-section {
        margin: 40px 0;
    }

    .pages-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
        margin-top: 20px;
    }

    .pages-column h3 {
        color: #2c3e50;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
    }

    .pages-list {
        max-height: 400px;
        overflow-y: auto;
    }

    .page-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 15px;
        margin-bottom: 5px;
        border-radius: 5px;
        border-left: 3px solid;
    }

    .page-item.success {
        background: #f0fff4;
        border-left-color: #28a745;
    }

    .page-item.warning {
        background: #fffbf0;
        border-left-color: #ffc107;
    }

    .page-name {
        font-family: monospace;
        font-size: 14px;
    }

    .page-link {
        color: #007bff;
        text-decoration: none;
        font-size: 12px;
        padding: 4px 8px;
        border: 1px solid #007bff;
        border-radius: 3px;
    }

    .page-link:hover {
        background: #007bff;
        color: white;
    }

    .example-section {
        margin: 40px 0;
    }

    .example-card {
        background: white;
        padding: 25px;
        border-radius: 10px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .tools-section {
        margin: 40px 0;
        text-align: center;
    }

    .tool-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
        margin-top: 20px;
    }

    .btn-tool {
        padding: 12px 24px;
        background: #007bff;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        transition: background 0.3s ease;
    }

    .btn-tool:hover {
        background: #0056b3;
    }

    @media (max-width: 768px) {
        .pages-grid {
            grid-template-columns: 1fr;
        }
        
        .tool-buttons {
            flex-direction: column;
            align-items: center;
        }
    }
    </style>

    <script>
    function generateUpdateScript() {
        alert('Tính năng này sẽ tạo script PHP để tự động cập nhật tất cả trang. Đang phát triển...');
    }

    function checkAllPages() {
        alert('Đang kiểm tra tất cả trang...');
        location.reload();
    }

    function downloadBackup() {
        alert('Tính năng backup sẽ tạo file zip chứa tất cả trang hiện tại. Đang phát triển...');
    }
    </script>
</body>
</html>