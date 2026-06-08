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
$diagnostics = [];

// Xử lý các action sửa lỗi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        $conn = getDBConnection();
        
        switch ($action) {
            case 'fix_video_display':
                // Sửa lỗi hiển thị video
                $stmt = $conn->prepare("UPDATE videos SET is_active = 1 WHERE is_active IS NULL OR is_active = 0");
                $stmt->execute();
                $affected = $stmt->affected_rows;
                $message = "✅ Đã kích hoạt $affected video bị ẩn";
                $messageType = "success";
                break;
                
            case 'reset_views':
                // Reset lượt xem về 0
                $stmt = $conn->prepare("UPDATE videos SET views = 0");
                $stmt->execute();
                $message = "✅ Đã reset lượt xem tất cả video về 0";
                $messageType = "success";
                break;
                
            case 'fix_video_paths':
                // Sửa đường dẫn video
                $stmt = $conn->prepare("SELECT id, video_url FROM videos WHERE video_type = 'local'");
                $stmt->execute();
                $result = $stmt->get_result();
                $fixed = 0;
                
                while ($row = $result->fetch_assoc()) {
                    $oldPath = $row['video_url'];
                    $newPath = $oldPath;
                    
                    // Sửa đường dẫn nếu cần
                    if (!file_exists($oldPath) && file_exists('videos/' . basename($oldPath))) {
                        $newPath = 'videos/' . basename($oldPath);
                        $updateStmt = $conn->prepare("UPDATE videos SET video_url = ? WHERE id = ?");
                        $updateStmt->bind_param("si", $newPath, $row['id']);
                        $updateStmt->execute();
                        $fixed++;
                    }
                }
                
                $message = "✅ Đã sửa $fixed đường dẫn video";
                $messageType = "success";
                break;
                
            case 'create_sample_videos':
                // Tạo video mẫu
                $sampleVideos = [
                    [
                        'title' => 'Video Giới thiệu UBND Xã Long Hiệp',
                        'description' => 'Video giới thiệu về hoạt động và chức năng của UBND xã',
                        'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                        'video_type' => 'youtube'
                    ],
                    [
                        'title' => 'Hướng dẫn thủ tục hành chính',
                        'description' => 'Video hướng dẫn các thủ tục hành chính cơ bản',
                        'video_url' => 'https://www.youtube.com/watch?v=oHg5SJYRHA0',
                        'video_type' => 'youtube'
                    ]
                ];
                
                $userId = authCurrentUserId();
                $inserted = 0;
                
                foreach ($sampleVideos as $video) {
                    $stmt = $conn->prepare("INSERT INTO videos (title, description, video_url, video_type, created_by, is_active, views) VALUES (?, ?, ?, ?, ?, 1, 0)");
                    $stmt->bind_param("ssssi", $video['title'], $video['description'], $video['video_url'], $video['video_type'], $userId);
                    if ($stmt->execute()) {
                        $inserted++;
                    }
                }
                
                $message = "✅ Đã tạo $inserted video mẫu";
                $messageType = "success";
                break;
        }
        
        $conn->close();
    } catch (Exception $e) {
        $message = "❌ Lỗi: " . $e->getMessage();
        $messageType = "error";
    }
}

// Chạy chẩn đoán hệ thống
try {
    $conn = getDBConnection();
    
    // 1. Kiểm tra bảng videos
    $result = $conn->query("SHOW TABLES LIKE 'videos'");
    $diagnostics['table_exists'] = ($result && $result->num_rows > 0);
    
    if ($diagnostics['table_exists']) {
        // 2. Đếm video
        $result = $conn->query("SELECT COUNT(*) as total FROM videos");
        $diagnostics['total_videos'] = $result->fetch_assoc()['total'];
        
        $result = $conn->query("SELECT COUNT(*) as active FROM videos WHERE is_active = 1");
        $diagnostics['active_videos'] = $result->fetch_assoc()['active'];
        
        $result = $conn->query("SELECT COUNT(*) as local FROM videos WHERE video_type = 'local'");
        $diagnostics['local_videos'] = $result->fetch_assoc()['local'];
        
        $result = $conn->query("SELECT COUNT(*) as url FROM videos WHERE video_type IN ('youtube', 'vimeo')");
        $diagnostics['url_videos'] = $result->fetch_assoc()['url'];
        
        // 3. Kiểm tra file video local
        $result = $conn->query("SELECT video_url FROM videos WHERE video_type = 'local'");
        $diagnostics['missing_files'] = 0;
        $diagnostics['existing_files'] = 0;
        
        while ($row = $result->fetch_assoc()) {
            if (file_exists($row['video_url'])) {
                $diagnostics['existing_files']++;
            } else {
                $diagnostics['missing_files']++;
            }
        }
        
        // 4. Lấy video mới nhất
        $result = $conn->query("SELECT * FROM videos ORDER BY created_at DESC LIMIT 3");
        $diagnostics['recent_videos'] = [];
        while ($row = $result->fetch_assoc()) {
            $diagnostics['recent_videos'][] = $row;
        }
    }
    
    $conn->close();
} catch (Exception $e) {
    $diagnostics['error'] = $e->getMessage();
}

// Kiểm tra thư mục videos
$diagnostics['videos_folder_exists'] = is_dir('videos/');
if ($diagnostics['videos_folder_exists']) {
    $files = glob('videos/*');
    $diagnostics['files_in_folder'] = count($files);
} else {
    $diagnostics['files_in_folder'] = 0;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa Lỗi Video Hoàn Chỉnh - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard-style.css">
    <style>
        .diagnostic-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }
        .diagnostic-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .diagnostic-section h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .status-card {
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .status-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .status-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status-number {
            font-size: 32px;
            font-weight: bold;
            display: block;
            margin-bottom: 10px;
        }
        .fix-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
        .video-list {
            margin-top: 20px;
        }
        .video-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #007bff;
        }
        .video-item h4 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }
        .video-item p {
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
        <div class="diagnostic-container">
            <h1 style="text-align: center; color: #2c3e50;">🔧 Sửa Lỗi Video Hoàn Chỉnh</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Chẩn đoán hệ thống -->
            <div class="diagnostic-section">
                <h2>📊 Chẩn đoán Hệ thống Video</h2>
                
                <div class="status-grid">
                    <div class="status-card <?php echo $diagnostics['table_exists'] ? 'status-success' : 'status-error'; ?>">
                        <span class="status-number"><?php echo $diagnostics['table_exists'] ? '✅' : '❌'; ?></span>
                        <div>Bảng Database</div>
                        <small><?php echo $diagnostics['table_exists'] ? 'Đã tạo' : 'Chưa tạo'; ?></small>
                    </div>
                    
                    <?php if ($diagnostics['table_exists']): ?>
                        <div class="status-card <?php echo $diagnostics['total_videos'] > 0 ? 'status-success' : 'status-warning'; ?>">
                            <span class="status-number"><?php echo $diagnostics['total_videos']; ?></span>
                            <div>Tổng Video</div>
                            <small>Trong database</small>
                        </div>
                        
                        <div class="status-card <?php echo $diagnostics['active_videos'] > 0 ? 'status-success' : 'status-warning'; ?>">
                            <span class="status-number"><?php echo $diagnostics['active_videos']; ?></span>
                            <div>Video Hiển thị</div>
                            <small>Đang active</small>
                        </div>
                        
                        <div class="status-card status-success">
                            <span class="status-number"><?php echo $diagnostics['local_videos']; ?></span>
                            <div>Video Local</div>
                            <small>File upload</small>
                        </div>
                        
                        <div class="status-card status-success">
                            <span class="status-number"><?php echo $diagnostics['url_videos']; ?></span>
                            <div>Video URL</div>
                            <small>YouTube/Vimeo</small>
                        </div>
                        
                        <div class="status-card <?php echo $diagnostics['missing_files'] == 0 ? 'status-success' : 'status-error'; ?>">
                            <span class="status-number"><?php echo $diagnostics['missing_files']; ?></span>
                            <div>File Thiếu</div>
                            <small>Không tìm thấy</small>
                        </div>
                    <?php endif; ?>
                    
                    <div class="status-card <?php echo $diagnostics['videos_folder_exists'] ? 'status-success' : 'status-error'; ?>">
                        <span class="status-number"><?php echo $diagnostics['files_in_folder']; ?></span>
                        <div>File trong Thư mục</div>
                        <small>videos/ folder</small>
                    </div>
                </div>
            </div>

            <!-- Các hành động sửa lỗi -->
            <div class="diagnostic-section">
                <h2>🔧 Hành động Sửa Lỗi</h2>
                
                <div class="fix-actions">
                    <form method="POST" style="display: contents;">
                        <input type="hidden" name="action" value="fix_video_display">
                        <button type="submit" class="fix-btn fix-btn-primary" 
                                onclick="return confirm('Kích hoạt tất cả video bị ẩn?')">
                            👁️ Kích hoạt Video Ẩn
                        </button>
                    </form>
                    
                    <form method="POST" style="display: contents;">
                        <input type="hidden" name="action" value="fix_video_paths">
                        <button type="submit" class="fix-btn fix-btn-success">
                            📁 Sửa Đường dẫn Video
                        </button>
                    </form>
                    
                    <form method="POST" style="display: contents;">
                        <input type="hidden" name="action" value="create_sample_videos">
                        <button type="submit" class="fix-btn fix-btn-warning"
                                onclick="return confirm('Tạo video mẫu để test?')">
                            🎬 Tạo Video Mẫu
                        </button>
                    </form>
                    
                    <form method="POST" style="display: contents;">
                        <input type="hidden" name="action" value="reset_views">
                        <button type="submit" class="fix-btn fix-btn-danger"
                                onclick="return confirm('Reset lượt xem tất cả video về 0?')">
                            🔄 Reset Lượt Xem
                        </button>
                    </form>
                </div>
            </div>

            <!-- Video gần đây -->
            <?php if (!empty($diagnostics['recent_videos'])): ?>
                <div class="diagnostic-section">
                    <h2>📹 Video Gần Đây (3 video mới nhất)</h2>
                    
                    <div class="video-list">
                        <?php foreach ($diagnostics['recent_videos'] as $video): ?>
                            <div class="video-item">
                                <h4><?php echo htmlspecialchars($video['title']); ?></h4>
                                <p><strong>ID:</strong> <?php echo $video['id']; ?></p>
                                <p><strong>Loại:</strong> <?php echo $video['video_type']; ?></p>
                                <p><strong>URL:</strong> <?php echo htmlspecialchars($video['video_url']); ?></p>
                                <p><strong>Trạng thái:</strong> <?php echo $video['is_active'] ? '✅ Active' : '❌ Inactive'; ?></p>
                                <p><strong>Lượt xem:</strong> <?php echo number_format($video['views']); ?></p>
                                <p><strong>Ngày tạo:</strong> <?php echo $video['created_at']; ?></p>
                                
                                <?php if ($video['video_type'] == 'local'): ?>
                                    <p><strong>File tồn tại:</strong> 
                                        <?php echo file_exists($video['video_url']) ? '✅ Có' : '❌ Không'; ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Hướng dẫn -->
            <div class="diagnostic-section">
                <h2>💡 Hướng dẫn Khắc phục</h2>
                
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                    <h4>🔍 Nếu video không hiển thị trên trang công khai:</h4>
                    <ol>
                        <li>Kiểm tra video có trạng thái "Active" không</li>
                        <li>Đảm bảo file video tồn tại trong thư mục</li>
                        <li>Xóa cache trình duyệt</li>
                        <li>Kiểm tra quyền truy cập thư mục videos/</li>
                    </ol>
                    
                    <h4>📁 Nếu file video bị thiếu:</h4>
                    <ol>
                        <li>Upload lại file vào thư mục videos/</li>
                        <li>Hoặc cập nhật đường dẫn trong database</li>
                        <li>Sử dụng chức năng "Sửa Đường dẫn Video"</li>
                    </ol>
                </div>
            </div>

            <!-- Links hữu ích -->
            <div class="diagnostic-section">
                <h2>🔗 Links Hữu ích</h2>
                
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="video.php" class="fix-btn fix-btn-primary" target="_blank">👀 Xem Trang Video</a>
                    <a href="video-files.php" class="fix-btn fix-btn-success" target="_blank">📁 Tất cả File Video</a>
                    <a href="quan-ly-video.php" class="fix-btn fix-btn-warning">⚙️ Quản lý Video</a>
                    <a href="them-video.php" class="fix-btn fix-btn-success">➕ Thêm Video</a>
                    <a href="test-upload-url.php" class="fix-btn fix-btn-primary">🧪 Test Upload URL</a>
                    <a href="kiem-tra-video-da-them.php" class="fix-btn fix-btn-warning">🔍 Kiểm tra Hệ thống</a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>