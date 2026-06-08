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
$fixResults = [];

// Xử lý sửa lỗi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        $conn = getDBConnection();
        
        switch ($action) {
            case 'activate_all_videos':
                // Kích hoạt tất cả video
                $stmt = $conn->prepare("UPDATE videos SET is_active = 1 WHERE is_active != 1 OR is_active IS NULL");
                $stmt->execute();
                $affected = $stmt->affected_rows;
                $fixResults[] = "✅ Đã kích hoạt $affected video";
                break;
                
            case 'fix_video_urls':
                // Sửa URL video local
                $stmt = $conn->prepare("SELECT id, video_url FROM videos WHERE video_type = 'local'");
                $stmt->execute();
                $result = $stmt->get_result();
                $fixed = 0;
                
                while ($row = $result->fetch_assoc()) {
                    $currentUrl = $row['video_url'];
                    $fileName = basename($currentUrl);
                    $correctUrl = 'videos/' . $fileName;
                    
                    // Nếu file tồn tại ở đường dẫn đúng nhưng URL sai
                    if (!file_exists($currentUrl) && file_exists($correctUrl)) {
                        $updateStmt = $conn->prepare("UPDATE videos SET video_url = ? WHERE id = ?");
                        $updateStmt->bind_param("si", $correctUrl, $row['id']);
                        $updateStmt->execute();
                        $fixed++;
                    }
                }
                $fixResults[] = "✅ Đã sửa $fixed đường dẫn video";
                break;
                
            case 'reset_views_to_zero':
                // Reset lượt xem về 0
                $stmt = $conn->prepare("UPDATE videos SET views = 0");
                $stmt->execute();
                $fixResults[] = "✅ Đã reset lượt xem tất cả video về 0";
                break;
                
            case 'add_missing_columns':
                // Thêm cột thiếu nếu cần
                $columns_to_add = [
                    'thumbnail_url' => 'VARCHAR(500) NULL',
                    'duration' => 'VARCHAR(20) NULL',
                    'is_featured' => 'TINYINT(1) DEFAULT 0',
                    'display_order' => 'INT DEFAULT 0',
                    'album_id' => 'INT NULL'
                ];
                
                $added = 0;
                foreach ($columns_to_add as $column => $definition) {
                    $checkColumn = $conn->query("SHOW COLUMNS FROM videos LIKE '$column'");
                    if ($checkColumn->num_rows == 0) {
                        $conn->query("ALTER TABLE videos ADD COLUMN $column $definition");
                        $added++;
                    }
                }
                $fixResults[] = "✅ Đã thêm $added cột thiếu vào bảng videos";
                break;
                
            case 'create_default_thumbnails':
                // Tạo thumbnail mặc định cho video local
                $stmt = $conn->prepare("SELECT id, video_url, video_type FROM videos WHERE video_type = 'local' AND (thumbnail_url IS NULL OR thumbnail_url = '')");
                $stmt->execute();
                $result = $stmt->get_result();
                $updated = 0;
                
                while ($row = $result->fetch_assoc()) {
                    $extension = strtolower(pathinfo($row['video_url'], PATHINFO_EXTENSION));
                    $defaultThumbnail = '';
                    
                    if (in_array($extension, ['wav', 'mp3'])) {
                        $defaultThumbnail = 'images/audio-default.jpg';
                    } else {
                        $defaultThumbnail = 'images/video-default.jpg';
                    }
                    
                    $updateStmt = $conn->prepare("UPDATE videos SET thumbnail_url = ? WHERE id = ?");
                    $updateStmt->bind_param("si", $defaultThumbnail, $row['id']);
                    $updateStmt->execute();
                    $updated++;
                }
                $fixResults[] = "✅ Đã tạo thumbnail mặc định cho $updated video";
                break;
        }
        
        $conn->close();
        
        if (!empty($fixResults)) {
            $message = implode('<br>', $fixResults);
            $messageType = 'success';
        }
        
    } catch (Exception $e) {
        $message = "❌ Lỗi: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Kiểm tra trạng thái hiện tại
$status = [];
try {
    $conn = getDBConnection();
    
    // Kiểm tra bảng videos
    $result = $conn->query("SHOW TABLES LIKE 'videos'");
    $status['table_exists'] = ($result && $result->num_rows > 0);
    
    if ($status['table_exists']) {
        // Đếm video
        $result = $conn->query("SELECT COUNT(*) as total FROM videos");
        $status['total_videos'] = $result->fetch_assoc()['total'];
        
        $result = $conn->query("SELECT COUNT(*) as active FROM videos WHERE is_active = 1");
        $status['active_videos'] = $result->fetch_assoc()['active'];
        
        $result = $conn->query("SELECT COUNT(*) as inactive FROM videos WHERE is_active != 1 OR is_active IS NULL");
        $status['inactive_videos'] = $result->fetch_assoc()['inactive'];
        
        $result = $conn->query("SELECT COUNT(*) as local FROM videos WHERE video_type = 'local'");
        $status['local_videos'] = $result->fetch_assoc()['local'];
        
        // Kiểm tra file thiếu
        $result = $conn->query("SELECT video_url FROM videos WHERE video_type = 'local'");
        $status['missing_files'] = 0;
        while ($row = $result->fetch_assoc()) {
            if (!file_exists($row['video_url'])) {
                $status['missing_files']++;
            }
        }
        
        // Kiểm tra cột
        $result = $conn->query("SHOW COLUMNS FROM videos");
        $status['columns'] = [];
        while ($row = $result->fetch_assoc()) {
            $status['columns'][] = $row['Field'];
        }
        
        // Lấy video mẫu
        $result = $conn->query("SELECT * FROM videos ORDER BY created_at DESC LIMIT 3");
        $status['sample_videos'] = [];
        while ($row = $result->fetch_assoc()) {
            $status['sample_videos'][] = $row;
        }
    }
    
    $conn->close();
} catch (Exception $e) {
    $status['error'] = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Lỗi Hiển thị Video - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard-style.css">
    <style>
        .fix-container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
        }
        .fix-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .fix-section h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .status-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .status-item {
            text-align: center;
            padding: 20px;
            border-radius: 8px;
            border: 2px solid;
        }
        .status-good {
            background: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        .status-warning {
            background: #fff3cd;
            color: #856404;
            border-color: #ffeaa7;
        }
        .status-bad {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        .status-number {
            font-size: 24px;
            font-weight: bold;
            display: block;
        }
        .fix-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .fix-btn {
            padding: 15px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
        }
        .fix-btn-primary {
            background: #007bff;
            color: white;
        }
        .fix-btn-success {
            background: #28a745;
            color: white;
        }
        .fix-btn-warning {
            background: #ffc107;
            color: #212529;
        }
        .fix-btn-danger {
            background: #dc3545;
            color: white;
        }
        .fix-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .video-sample {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            border-left: 4px solid #007bff;
        }
        .video-sample h4 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }
        .video-sample p {
            margin: 5px 0;
            font-size: 14px;
            color: #666;
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
    </style>
</head>
<body>
    <?php include 'menu-don-gian.php'; ?>

    <main>
        <div class="fix-container">
            <h1 style="text-align: center; color: #2c3e50;">🔧 Sửa Lỗi Hiển thị Video</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Trạng thái hiện tại -->
            <div class="fix-section">
                <h2>📊 Trạng thái Hiện tại</h2>
                
                <?php if ($status['table_exists']): ?>
                    <div class="status-overview">
                        <div class="status-item <?php echo $status['total_videos'] > 0 ? 'status-good' : 'status-warning'; ?>">
                            <span class="status-number"><?php echo $status['total_videos']; ?></span>
                            <div>Tổng Video</div>
                        </div>
                        
                        <div class="status-item <?php echo $status['active_videos'] > 0 ? 'status-good' : 'status-bad'; ?>">
                            <span class="status-number"><?php echo $status['active_videos']; ?></span>
                            <div>Video Hiển thị</div>
                        </div>
                        
                        <div class="status-item <?php echo $status['inactive_videos'] == 0 ? 'status-good' : 'status-warning'; ?>">
                            <span class="status-number"><?php echo $status['inactive_videos']; ?></span>
                            <div>Video Ẩn</div>
                        </div>
                        
                        <div class="status-item status-good">
                            <span class="status-number"><?php echo $status['local_videos']; ?></span>
                            <div>Video Local</div>
                        </div>
                        
                        <div class="status-item <?php echo $status['missing_files'] == 0 ? 'status-good' : 'status-bad'; ?>">
                            <span class="status-number"><?php echo $status['missing_files']; ?></span>
                            <div>File Thiếu</div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="status-item status-bad">
                        <p>❌ Bảng videos chưa được tạo!</p>
                        <a href="setup-video-don-gian.php" class="fix-btn fix-btn-primary">Tạo bảng videos</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Hành động sửa lỗi -->
            <?php if ($status['table_exists']): ?>
                <div class="fix-section">
                    <h2>🔧 Hành động Sửa Lỗi</h2>
                    
                    <div class="fix-actions">
                        <?php if ($status['inactive_videos'] > 0): ?>
                            <form method="POST" style="display: contents;">
                                <input type="hidden" name="action" value="activate_all_videos">
                                <button type="submit" class="fix-btn fix-btn-success" 
                                        onclick="return confirm('Kích hoạt tất cả video ẩn?')">
                                    👁️ Kích hoạt Video Ẩn<br>
                                    <small>(<?php echo $status['inactive_videos']; ?> video)</small>
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <?php if ($status['missing_files'] > 0): ?>
                            <form method="POST" style="display: contents;">
                                <input type="hidden" name="action" value="fix_video_urls">
                                <button type="submit" class="fix-btn fix-btn-warning">
                                    📁 Sửa Đường dẫn Video<br>
                                    <small>(<?php echo $status['missing_files']; ?> file thiếu)</small>
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <form method="POST" style="display: contents;">
                            <input type="hidden" name="action" value="add_missing_columns">
                            <button type="submit" class="fix-btn fix-btn-primary">
                                🗂️ Thêm Cột Thiếu<br>
                                <small>Cập nhật cấu trúc bảng</small>
                            </button>
                        </form>
                        
                        <form method="POST" style="display: contents;">
                            <input type="hidden" name="action" value="create_default_thumbnails">
                            <button type="submit" class="fix-btn fix-btn-success">
                                🖼️ Tạo Thumbnail<br>
                                <small>Thumbnail mặc định</small>
                            </button>
                        </form>
                        
                        <form method="POST" style="display: contents;">
                            <input type="hidden" name="action" value="reset_views_to_zero">
                            <button type="submit" class="fix-btn fix-btn-danger"
                                    onclick="return confirm('Reset lượt xem tất cả video về 0?')">
                                🔄 Reset Lượt Xem<br>
                                <small>Đặt về 0</small>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Video mẫu -->
                <?php if (!empty($status['sample_videos'])): ?>
                    <div class="fix-section">
                        <h2>🎬 Video Mẫu (3 video gần đây)</h2>
                        
                        <?php foreach ($status['sample_videos'] as $video): ?>
                            <div class="video-sample">
                                <h4><?php echo htmlspecialchars($video['title']); ?></h4>
                                <p><strong>ID:</strong> <?php echo $video['id']; ?></p>
                                <p><strong>Loại:</strong> <?php echo $video['video_type']; ?></p>
                                <p><strong>URL:</strong> <?php echo htmlspecialchars($video['video_url']); ?></p>
                                <p><strong>Trạng thái:</strong> 
                                    <?php if ($video['is_active']): ?>
                                        <span style="color: #28a745;">✅ Active (Hiển thị)</span>
                                    <?php else: ?>
                                        <span style="color: #dc3545;">❌ Inactive (Ẩn)</span>
                                    <?php endif; ?>
                                </p>
                                <p><strong>Lượt xem:</strong> <?php echo number_format($video['views']); ?></p>
                                <p><strong>Ngày tạo:</strong> <?php echo $video['created_at']; ?></p>
                                
                                <?php if ($video['video_type'] == 'local'): ?>
                                    <p><strong>File tồn tại:</strong> 
                                        <?php if (file_exists($video['video_url'])): ?>
                                            <span style="color: #28a745;">✅ Có</span>
                                        <?php else: ?>
                                            <span style="color: #dc3545;">❌ Không tìm thấy</span>
                                        <?php endif; ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Hướng dẫn -->
            <div class="fix-section">
                <h2>💡 Hướng dẫn Khắc phục</h2>
                
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                    <h4>🔍 Nếu video vẫn không hiển thị sau khi sửa:</h4>
                    <ol>
                        <li><strong>Xóa cache trình duyệt:</strong> Ctrl+F5 hoặc Ctrl+Shift+R</li>
                        <li><strong>Kiểm tra quyền thư mục:</strong> Đảm bảo thư mục videos/ có quyền đọc</li>
                        <li><strong>Kiểm tra đường dẫn:</strong> URL video phải chính xác</li>
                        <li><strong>Kiểm tra định dạng:</strong> File video phải có định dạng hỗ trợ</li>
                    </ol>
                    
                    <h4>📱 Test hiển thị:</h4>
                    <ol>
                        <li>Mở trang <a href="video.php" target="_blank">video.php</a> để xem video</li>
                        <li>Mở trang <a href="video-files.php" target="_blank">video-files.php</a> để xem file</li>
                        <li>Kiểm tra console trình duyệt (F12) để xem lỗi JavaScript</li>
                    </ol>
                </div>
            </div>

            <!-- Links nhanh -->
            <div class="fix-section">
                <h2>🔗 Links Nhanh</h2>
                
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="video.php" class="fix-btn fix-btn-primary" target="_blank">👀 Xem Trang Video</a>
                    <a href="video-files.php" class="fix-btn fix-btn-success" target="_blank">📁 File Video</a>
                    <a href="quan-ly-video.php" class="fix-btn fix-btn-warning">⚙️ Quản lý</a>
                    <a href="them-video.php" class="fix-btn fix-btn-success">➕ Thêm Video</a>
                    <a href="kiem-tra-tong-hop-video.php" class="fix-btn fix-btn-primary">🔍 Kiểm tra Tổng hợp</a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>