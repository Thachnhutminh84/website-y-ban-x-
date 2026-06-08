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

$results = [];
$message = '';
$messageType = '';

// Test 1: Kiểm tra database
$results['database'] = [];
try {
    $conn = getDBConnection();
    
    // Kiểm tra bảng videos
    $result = $conn->query("SHOW TABLES LIKE 'videos'");
    $results['database']['table_exists'] = ($result && $result->num_rows > 0);
    
    if ($results['database']['table_exists']) {
        // Đếm video
        $result = $conn->query("SELECT COUNT(*) as total FROM videos");
        $results['database']['total_videos'] = $result->fetch_assoc()['total'];
        
        $result = $conn->query("SELECT COUNT(*) as active FROM videos WHERE is_active = 1");
        $results['database']['active_videos'] = $result->fetch_assoc()['active'];
        
        // Lấy video mới nhất
        $result = $conn->query("SELECT * FROM videos ORDER BY created_at DESC LIMIT 1");
        $results['database']['latest_video'] = $result->fetch_assoc();
        
        // Kiểm tra video có hiển thị được không
        $result = $conn->query("SELECT * FROM videos WHERE is_active = 1 LIMIT 3");
        $results['database']['sample_videos'] = [];
        while ($row = $result->fetch_assoc()) {
            $results['database']['sample_videos'][] = $row;
        }
    }
    
    $conn->close();
} catch (Exception $e) {
    $results['database']['error'] = $e->getMessage();
}

// Test 2: Kiểm tra thư mục videos
$results['files'] = [];
$results['files']['folder_exists'] = is_dir('videos/');
if ($results['files']['folder_exists']) {
    $files = glob('videos/*');
    $results['files']['total_files'] = count($files);
    $results['files']['file_list'] = [];
    
    foreach ($files as $file) {
        if (is_file($file)) {
            $results['files']['file_list'][] = [
                'name' => basename($file),
                'size' => filesize($file),
                'path' => $file,
                'extension' => strtolower(pathinfo($file, PATHINFO_EXTENSION)),
                'modified' => filemtime($file)
            ];
        }
    }
    
    // Sắp xếp theo thời gian
    usort($results['files']['file_list'], function($a, $b) {
        return $b['modified'] - $a['modified'];
    });
}

// Test 3: Kiểm tra các trang
$results['pages'] = [];
$pages_to_check = [
    'video.php' => 'Trang video chính',
    'video-files.php' => 'Trang tất cả file',
    'quan-ly-video.php' => 'Quản lý video',
    'them-video.php' => 'Thêm video',
    'them-video-moi.php' => 'Thêm video mới',
    'api-update-video-views.php' => 'API cập nhật lượt xem',
    'api-get-video-info.php' => 'API lấy thông tin video'
];

foreach ($pages_to_check as $page => $description) {
    $results['pages'][$page] = [
        'exists' => file_exists($page),
        'description' => $description
    ];
}

// Test 4: Xử lý action test
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'test_video_display') {
        // Test hiển thị video
        try {
            $conn = getDBConnection();
            $result = $conn->query("SELECT * FROM videos WHERE is_active = 1 LIMIT 1");
            if ($video = $result->fetch_assoc()) {
                $message = "✅ Tìm thấy video để hiển thị: " . $video['title'];
                $messageType = "success";
                $results['test_video'] = $video;
            } else {
                $message = "❌ Không tìm thấy video nào để hiển thị";
                $messageType = "error";
            }
            $conn->close();
        } catch (Exception $e) {
            $message = "❌ Lỗi: " . $e->getMessage();
            $messageType = "error";
        }
    }
    
    if ($action == 'create_test_video') {
        // Tạo video test
        try {
            $conn = getDBConnection();
            $userId = authCurrentUserId();
            $title = "Video Test - " . date('Y-m-d H:i:s');
            $description = "Video test để kiểm tra hệ thống";
            $video_url = "https://www.youtube.com/watch?v=dQw4w9WgXcQ";
            $video_type = "youtube";
            
            $stmt = $conn->prepare("INSERT INTO videos (title, description, video_url, video_type, created_by, is_active, views) VALUES (?, ?, ?, ?, ?, 1, 0)");
            $stmt->bind_param("ssssi", $title, $description, $video_url, $video_type, $userId);
            
            if ($stmt->execute()) {
                $videoId = $stmt->insert_id;
                $message = "✅ Đã tạo video test thành công! ID: $videoId";
                $messageType = "success";
            } else {
                $message = "❌ Lỗi tạo video test: " . $stmt->error;
                $messageType = "error";
            }
            
            $stmt->close();
            $conn->close();
        } catch (Exception $e) {
            $message = "❌ Lỗi: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiểm tra Tổng hợp Video - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard-style.css">
    <style>
        .test-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }
        .test-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .test-section h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .status-card {
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border: 2px solid;
        }
        .status-success {
            background: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        .status-warning {
            background: #fff3cd;
            color: #856404;
            border-color: #ffeaa7;
        }
        .status-error {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        .status-number {
            font-size: 28px;
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
        }
        .item-list {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .item {
            padding: 10px;
            margin: 5px 0;
            background: white;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }
        .item h4 {
            margin: 0 0 5px 0;
            color: #2c3e50;
        }
        .item p {
            margin: 3px 0;
            font-size: 14px;
            color: #666;
        }
        .btn-test {
            background: #007bff;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            margin: 5px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-test:hover {
            background: #0056b3;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #218838;
        }
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        .btn-warning:hover {
            background: #e0a800;
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
        .video-preview {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <header class="header--compact">
        <div class="container">
            <div class="logo">
                <img src="images/logo.png" alt="Logo">
                <div class="header-text">
                    <h1>ỦY BAN NHÂN DÂN XÃ LONG HIỆP</h1>
                </div>
            </div>
        </div>
    </header>

    <main>
        <div class="test-container">
            <h1 style="text-align: center; color: #2c3e50;">🔍 Kiểm tra Tổng hợp Hệ thống Video</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Test 1: Database -->
            <div class="test-section">
                <h2>1. 📊 Kiểm tra Database</h2>
                
                <div class="status-grid">
                    <div class="status-card <?php echo $results['database']['table_exists'] ? 'status-success' : 'status-error'; ?>">
                        <span class="status-number"><?php echo $results['database']['table_exists'] ? '✅' : '❌'; ?></span>
                        <div>Bảng Videos</div>
                        <small><?php echo $results['database']['table_exists'] ? 'Đã tạo' : 'Chưa tạo'; ?></small>
                    </div>
                    
                    <?php if ($results['database']['table_exists']): ?>
                        <div class="status-card status-success">
                            <span class="status-number"><?php echo $results['database']['total_videos']; ?></span>
                            <div>Tổng Video</div>
                            <small>Trong database</small>
                        </div>
                        
                        <div class="status-card <?php echo $results['database']['active_videos'] > 0 ? 'status-success' : 'status-warning'; ?>">
                            <span class="status-number"><?php echo $results['database']['active_videos']; ?></span>
                            <div>Video Active</div>
                            <small>Đang hiển thị</small>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($results['database']['latest_video'])): ?>
                    <div class="video-preview">
                        <h4>📹 Video mới nhất:</h4>
                        <p><strong>Tiêu đề:</strong> <?php echo htmlspecialchars($results['database']['latest_video']['title']); ?></p>
                        <p><strong>Loại:</strong> <?php echo $results['database']['latest_video']['video_type']; ?></p>
                        <p><strong>URL:</strong> <?php echo htmlspecialchars($results['database']['latest_video']['video_url']); ?></p>
                        <p><strong>Trạng thái:</strong> <?php echo $results['database']['latest_video']['is_active'] ? '✅ Active' : '❌ Inactive'; ?></p>
                        <p><strong>Ngày tạo:</strong> <?php echo $results['database']['latest_video']['created_at']; ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Test 2: Files -->
            <div class="test-section">
                <h2>2. 📁 Kiểm tra File Video</h2>
                
                <div class="status-grid">
                    <div class="status-card <?php echo $results['files']['folder_exists'] ? 'status-success' : 'status-error'; ?>">
                        <span class="status-number"><?php echo $results['files']['folder_exists'] ? '✅' : '❌'; ?></span>
                        <div>Thư mục videos/</div>
                        <small><?php echo $results['files']['folder_exists'] ? 'Tồn tại' : 'Không tồn tại'; ?></small>
                    </div>
                    
                    <?php if ($results['files']['folder_exists']): ?>
                        <div class="status-card <?php echo $results['files']['total_files'] > 0 ? 'status-success' : 'status-warning'; ?>">
                            <span class="status-number"><?php echo $results['files']['total_files']; ?></span>
                            <div>File trong thư mục</div>
                            <small>Tổng file</small>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($results['files']['file_list'])): ?>
                    <div class="item-list">
                        <h4>📄 File video gần đây (5 file mới nhất):</h4>
                        <?php foreach (array_slice($results['files']['file_list'], 0, 5) as $file): ?>
                            <div class="item">
                                <h4><?php echo htmlspecialchars($file['name']); ?></h4>
                                <p><strong>Kích thước:</strong> <?php echo formatFileSize($file['size']); ?></p>
                                <p><strong>Định dạng:</strong> <?php echo strtoupper($file['extension']); ?></p>
                                <p><strong>Ngày sửa đổi:</strong> <?php echo date('d/m/Y H:i', $file['modified']); ?></p>
                                <p><strong>Đường dẫn:</strong> <?php echo htmlspecialchars($file['path']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Test 3: Pages -->
            <div class="test-section">
                <h2>3. 📄 Kiểm tra Trang Web</h2>
                
                <div class="item-list">
                    <?php foreach ($results['pages'] as $page => $info): ?>
                        <div class="item">
                            <h4><?php echo $info['exists'] ? '✅' : '❌'; ?> <?php echo $page; ?></h4>
                            <p><?php echo $info['description']; ?></p>
                            <?php if ($info['exists']): ?>
                                <a href="<?php echo $page; ?>" target="_blank" class="btn-test" style="font-size: 12px; padding: 5px 10px;">Mở trang</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Test Actions -->
            <div class="test-section">
                <h2>4. 🧪 Hành động Test</h2>
                
                <div style="display: flex; gap: 10px; flex-wrap: wrap; margin: 20px 0;">
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="test_video_display">
                        <button type="submit" class="btn-test">🔍 Test Hiển thị Video</button>
                    </form>
                    
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="create_test_video">
                        <button type="submit" class="btn-test btn-success" onclick="return confirm('Tạo video test YouTube?')">➕ Tạo Video Test</button>
                    </form>
                </div>
                
                <?php if (isset($results['test_video'])): ?>
                    <div class="video-preview">
                        <h4>🎬 Video test hiển thị:</h4>
                        <p><strong>Tiêu đề:</strong> <?php echo htmlspecialchars($results['test_video']['title']); ?></p>
                        <p><strong>Loại:</strong> <?php echo $results['test_video']['video_type']; ?></p>
                        <p><strong>URL:</strong> <?php echo htmlspecialchars($results['test_video']['video_url']); ?></p>
                        <p><strong>Lượt xem:</strong> <?php echo number_format($results['test_video']['views']); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sample Videos -->
            <?php if (!empty($results['database']['sample_videos'])): ?>
                <div class="test-section">
                    <h2>5. 🎬 Video Mẫu (3 video đầu tiên)</h2>
                    
                    <div class="item-list">
                        <?php foreach ($results['database']['sample_videos'] as $video): ?>
                            <div class="item">
                                <h4><?php echo htmlspecialchars($video['title']); ?></h4>
                                <p><strong>ID:</strong> <?php echo $video['id']; ?></p>
                                <p><strong>Loại:</strong> <?php echo $video['video_type']; ?></p>
                                <p><strong>URL:</strong> <?php echo htmlspecialchars($video['video_url']); ?></p>
                                <p><strong>Lượt xem:</strong> <?php echo number_format($video['views']); ?></p>
                                <p><strong>Trạng thái:</strong> <?php echo $video['is_active'] ? '✅ Active' : '❌ Inactive'; ?></p>
                                
                                <?php if ($video['video_type'] == 'local'): ?>
                                    <p><strong>File tồn tại:</strong> <?php echo file_exists($video['video_url']) ? '✅ Có' : '❌ Không'; ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Quick Links -->
            <div class="test-section">
                <h2>🔗 Links Nhanh</h2>
                
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="video.php" class="btn-test" target="_blank">👀 Xem Trang Video</a>
                    <a href="video-files.php" class="btn-test btn-success" target="_blank">📁 Tất cả File Video</a>
                    <a href="quan-ly-video.php" class="btn-test btn-warning">⚙️ Quản lý Video</a>
                    <a href="them-video.php" class="btn-test btn-success">➕ Thêm Video</a>
                    <a href="sua-loi-video-hoan-chinh.php" class="btn-test btn-warning">🔧 Sửa Lỗi Video</a>
                    <a href="test-upload-url.php" class="btn-test">🧪 Test Upload URL</a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>