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

$message = '';
$messageType = '';
$results = [];

// Xử lý áp dụng menu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['apply_menu'])) {
    $selectedFiles = $_POST['files'] ?? [];
    $backupCreated = false;
    
    if (!empty($selectedFiles)) {
        // Tạo backup trước khi thay đổi
        $backupDir = 'backup_' . date('Y-m-d_H-i-s');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
            $backupCreated = true;
        }
        
        foreach ($selectedFiles as $file) {
            if (file_exists($file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                try {
                    // Backup file gốc
                    if ($backupCreated) {
                        copy($file, $backupDir . '/' . $file);
                    }
                    
                    // Đọc nội dung file
                    $content = file_get_contents($file);
                    
                    // Kiểm tra xem đã có header thống nhất chưa
                    if (strpos($content, 'header-thong-nhat.php') !== false) {
                        $results[$file] = ['status' => 'skip', 'message' => 'Đã có menu thống nhất'];
                        continue;
                    }
                    
                    // Tìm và thay thế header cũ
                    $headerPatterns = [
                        // Pattern 1: Header với nav bên trong
                        '/(<header[^>]*>.*?<nav[^>]*>.*?<\/nav>.*?<\/header>)/s',
                        // Pattern 2: Header đơn giản
                        '/(<header[^>]*>.*?<\/header>)/s',
                        // Pattern 3: Chỉ có nav
                        '/(<nav[^>]*>.*?<\/nav>)/s'
                    ];
                    
                    $replaced = false;
                    foreach ($headerPatterns as $pattern) {
                        if (preg_match($pattern, $content)) {
                            $newContent = preg_replace($pattern, '<?php include \'header-thong-nhat.php\'; ?>', $content, 1);
                            if ($newContent !== $content) {
                                file_put_contents($file, $newContent);
                                $results[$file] = ['status' => 'success', 'message' => 'Đã cập nhật thành công'];
                                $replaced = true;
                                break;
                            }
                        }
                    }
                    
                    if (!$replaced) {
                        // Nếu không tìm thấy header, thêm vào sau thẻ body
                        if (preg_match('/(<body[^>]*>)/i', $content)) {
                            $newContent = preg_replace('/(<body[^>]*>)/i', '$1' . "\n    <?php include 'header-thong-nhat.php'; ?>", $content, 1);
                            file_put_contents($file, $newContent);
                            $results[$file] = ['status' => 'success', 'message' => 'Đã thêm menu mới'];
                        } else {
                            $results[$file] = ['status' => 'error', 'message' => 'Không tìm thấy vị trí phù hợp'];
                        }
                    }
                    
                } catch (Exception $e) {
                    $results[$file] = ['status' => 'error', 'message' => 'Lỗi: ' . $e->getMessage()];
                }
            }
        }
        
        $successCount = count(array_filter($results, function($r) { return $r['status'] === 'success'; }));
        $message = "✅ Đã cập nhật $successCount file thành công!";
        if ($backupCreated) {
            $message .= " Backup được lưu tại: $backupDir";
        }
        $messageType = 'success';
    } else {
        $message = "❌ Vui lòng chọn ít nhất một file để cập nhật";
        $messageType = 'error';
    }
}

// Lấy danh sách tất cả file PHP
$allFiles = glob('*.php');
$excludeFiles = ['menu-thong-nhat.php', 'header-thong-nhat.php', 'ap-dung-menu-video-toan-bo.php'];
$phpFiles = array_diff($allFiles, $excludeFiles);

// Kiểm tra trạng thái từng file
$fileStatus = [];
foreach ($phpFiles as $file) {
    $content = file_get_contents($file);
    $hasUnifiedMenu = strpos($content, 'header-thong-nhat.php') !== false;
    $hasOldMenu = preg_match('/<header[^>]*>.*?<\/header>/s', $content) || preg_match('/<nav[^>]*>.*?<\/nav>/s', $content);
    
    $fileStatus[$file] = [
        'has_unified' => $hasUnifiedMenu,
        'has_old_menu' => $hasOldMenu,
        'size' => filesize($file),
        'modified' => filemtime($file)
    ];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Áp dụng Menu Video toàn bộ - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard-style.css">
</head>
<body>
    <!-- Header thống nhất -->
    <?php include 'header-thong-nhat.php'; ?>

    <main>
        <div class="container">
            <div class="page-header">
                <h1>🎬 Áp dụng Menu Video cho Toàn bộ Website</h1>
                <p>Tự động thêm menu video vào tất cả các trang PHP</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Thống kê -->
            <div class="stats-section">
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($phpFiles); ?></div>
                    <div class="stat-label">Tổng file PHP</div>
                </div>
                <div class="stat-card success">
                    <div class="stat-number"><?php echo count(array_filter($fileStatus, function($s) { return $s['has_unified']; })); ?></div>
                    <div class="stat-label">Đã có menu thống nhất</div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-number"><?php echo count(array_filter($fileStatus, function($s) { return !$s['has_unified'] && $s['has_old_menu']; })); ?></div>
                    <div class="stat-label">Có menu cũ</div>
                </div>
                <div class="stat-card error">
                    <div class="stat-number"><?php echo count(array_filter($fileStatus, function($s) { return !$s['has_unified'] && !$s['has_old_menu']; })); ?></div>
                    <div class="stat-label">Chưa có menu</div>
                </div>
            </div>

            <!-- Form áp dụng -->
            <div class="apply-section">
                <h2>🚀 Áp dụng Menu Video</h2>
                
                <form method="POST" id="applyForm">
                    <input type="hidden" name="apply_menu" value="1">
                    
                    <div class="form-actions">
                        <button type="button" onclick="selectAll()" class="btn-secondary">✅ Chọn tất cả</button>
                        <button type="button" onclick="selectNone()" class="btn-secondary">❌ Bỏ chọn tất cả</button>
                        <button type="button" onclick="selectNeedUpdate()" class="btn-warning">⚠️ Chọn cần cập nhật</button>
                        <button type="submit" class="btn-primary" onclick="return confirmApply()">🚀 Áp dụng menu</button>
                    </div>
                    
                    <div class="files-grid">
                        <?php foreach ($phpFiles as $file): ?>
                            <?php $status = $fileStatus[$file]; ?>
                            <div class="file-item <?php 
                                if ($status['has_unified']) echo 'unified';
                                elseif ($status['has_old_menu']) echo 'old-menu';
                                else echo 'no-menu';
                            ?>">
                                <div class="file-checkbox">
                                    <input type="checkbox" name="files[]" value="<?php echo $file; ?>" 
                                           id="file_<?php echo md5($file); ?>"
                                           <?php echo !$status['has_unified'] ? 'checked' : ''; ?>>
                                </div>
                                
                                <div class="file-info">
                                    <h4><?php echo $file; ?></h4>
                                    <div class="file-meta">
                                        <span class="file-size"><?php echo number_format($status['size'] / 1024, 1); ?> KB</span>
                                        <span class="file-date"><?php echo date('d/m/Y H:i', $status['modified']); ?></span>
                                    </div>
                                    
                                    <div class="file-status">
                                        <?php if ($status['has_unified']): ?>
                                            <span class="status-badge success">✅ Đã có menu thống nhất</span>
                                        <?php elseif ($status['has_old_menu']): ?>
                                            <span class="status-badge warning">⚠️ Có menu cũ - cần cập nhật</span>
                                        <?php else: ?>
                                            <span class="status-badge error">❌ Chưa có menu</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="file-actions">
                                    <a href="<?php echo $file; ?>" target="_blank" class="btn-view">👀 Xem</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </form>
            </div>

            <!-- Kết quả -->
            <?php if (!empty($results)): ?>
                <div class="results-section">
                    <h2>📊 Kết quả áp dụng</h2>
                    
                    <div class="results-list">
                        <?php foreach ($results as $file => $result): ?>
                            <div class="result-item <?php echo $result['status']; ?>">
                                <div class="result-file"><?php echo $file; ?></div>
                                <div class="result-message">
                                    <?php if ($result['status'] === 'success'): ?>
                                        ✅ <?php echo $result['message']; ?>
                                    <?php elseif ($result['status'] === 'skip'): ?>
                                        ⏭️ <?php echo $result['message']; ?>
                                    <?php else: ?>
                                        ❌ <?php echo $result['message']; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Hướng dẫn -->
            <div class="guide-section">
                <h2>💡 Hướng dẫn</h2>
                
                <div class="guide-card">
                    <h3>🎯 Menu Video bao gồm:</h3>
                    <ul>
                        <li><strong>📺 Video chính thức:</strong> Video từ database đã được duyệt</li>
                        <li><strong>📁 Tất cả file video:</strong> Tất cả file video trong thư mục</li>
                        <li><strong>🎵 File audio:</strong> Các file âm thanh (WAV, MP3)</li>
                        <li><strong>⭐ Video nổi bật:</strong> Video được đánh dấu nổi bật</li>
                    </ul>
                </div>
                
                <div class="guide-card">
                    <h3>⚠️ Lưu ý quan trọng:</h3>
                    <ul>
                        <li>Hệ thống sẽ tự động tạo backup trước khi thay đổi</li>
                        <li>Chỉ áp dụng cho file chưa có menu thống nhất</li>
                        <li>Kiểm tra kỹ sau khi áp dụng</li>
                        <li>Có thể hoàn tác từ thư mục backup</li>
                    </ul>
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

    .stat-card.success { border-left-color: #28a745; }
    .stat-card.warning { border-left-color: #ffc107; }
    .stat-card.error { border-left-color: #dc3545; }

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

    .apply-section {
        background: white;
        padding: 30px;
        border-radius: 10px;
        margin: 30px 0;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .form-actions {
        display: flex;
        gap: 15px;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }

    .btn-primary, .btn-secondary, .btn-warning {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-primary { background: #007bff; color: white; }
    .btn-secondary { background: #6c757d; color: white; }
    .btn-warning { background: #ffc107; color: #212529; }

    .btn-primary:hover { background: #0056b3; }
    .btn-secondary:hover { background: #545b62; }
    .btn-warning:hover { background: #e0a800; }

    .files-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 20px;
    }

    .file-item {
        display: flex;
        align-items: center;
        padding: 20px;
        border-radius: 10px;
        border: 2px solid;
        transition: all 0.3s ease;
    }

    .file-item.unified {
        border-color: #28a745;
        background: #f8fff9;
    }

    .file-item.old-menu {
        border-color: #ffc107;
        background: #fffdf5;
    }

    .file-item.no-menu {
        border-color: #dc3545;
        background: #fff5f5;
    }

    .file-checkbox {
        margin-right: 15px;
    }

    .file-checkbox input[type="checkbox"] {
        width: 20px;
        height: 20px;
    }

    .file-info {
        flex: 1;
    }

    .file-info h4 {
        margin: 0 0 10px 0;
        color: #2c3e50;
        font-size: 16px;
    }

    .file-meta {
        display: flex;
        gap: 15px;
        margin-bottom: 10px;
        font-size: 12px;
        color: #666;
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

    .status-badge.warning {
        background: #fff3cd;
        color: #856404;
    }

    .status-badge.error {
        background: #f8d7da;
        color: #721c24;
    }

    .file-actions {
        margin-left: 15px;
    }

    .btn-view {
        padding: 6px 12px;
        background: #17a2b8;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        font-size: 12px;
    }

    .btn-view:hover {
        background: #138496;
    }

    .results-section {
        background: white;
        padding: 30px;
        border-radius: 10px;
        margin: 30px 0;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .results-list {
        margin-top: 20px;
    }

    .result-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        margin-bottom: 10px;
        border-radius: 8px;
        border-left: 4px solid;
    }

    .result-item.success {
        background: #f8fff9;
        border-left-color: #28a745;
    }

    .result-item.skip {
        background: #f8f9fa;
        border-left-color: #6c757d;
    }

    .result-item.error {
        background: #fff5f5;
        border-left-color: #dc3545;
    }

    .result-file {
        font-family: monospace;
        font-weight: 600;
    }

    .result-message {
        font-size: 14px;
    }

    .guide-section {
        margin: 40px 0;
    }

    .guide-card {
        background: white;
        padding: 25px;
        border-radius: 10px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-left: 4px solid #007bff;
    }

    .guide-card h3 {
        color: #2c3e50;
        margin-bottom: 15px;
    }

    .guide-card ul {
        margin: 0;
        padding-left: 20px;
    }

    .guide-card li {
        margin: 8px 0;
    }

    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 8px;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    @media (max-width: 768px) {
        .files-grid {
            grid-template-columns: 1fr;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .file-item {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .file-checkbox {
            margin-right: 0;
            margin-bottom: 10px;
        }
    }
    </style>

    <script>
    function selectAll() {
        document.querySelectorAll('input[name="files[]"]').forEach(cb => cb.checked = true);
    }

    function selectNone() {
        document.querySelectorAll('input[name="files[]"]').forEach(cb => cb.checked = false);
    }

    function selectNeedUpdate() {
        document.querySelectorAll('input[name="files[]"]').forEach(cb => {
            const fileItem = cb.closest('.file-item');
            cb.checked = !fileItem.classList.contains('unified');
        });
    }

    function confirmApply() {
        const checkedFiles = document.querySelectorAll('input[name="files[]"]:checked');
        if (checkedFiles.length === 0) {
            alert('Vui lòng chọn ít nhất một file để áp dụng menu!');
            return false;
        }
        
        return confirm(`Bạn có chắc muốn áp dụng menu video cho ${checkedFiles.length} file?\n\nHệ thống sẽ tự động tạo backup trước khi thay đổi.`);
    }
    </script>
</body>
</html>