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

$currentRole = authCurrentRole();
$displayName = authDisplayName();

// Xử lý các action
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        $conn = getDBConnection();
        
        if ($action == 'delete' && isset($_POST['video_id'])) {
            $videoId = (int)$_POST['video_id'];
            $stmt = $conn->prepare("UPDATE videos SET is_active = 0 WHERE id = ?");
            $stmt->bind_param("i", $videoId);
            $stmt->execute();
            $message = "Đã xóa video thành công!";
            $messageType = "success";
        }
        
        if ($action == 'toggle_featured' && isset($_POST['video_id'])) {
            $videoId = (int)$_POST['video_id'];
            $stmt = $conn->prepare("UPDATE videos SET is_featured = NOT is_featured WHERE id = ?");
            $stmt->bind_param("i", $videoId);
            $stmt->execute();
            $message = "Đã cập nhật trạng thái nổi bật!";
            $messageType = "success";
        }
        
        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        $message = "Lỗi: " . $e->getMessage();
        $messageType = "error";
    }
}

// Lấy danh sách video
$videos = [];
$albums = [];
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';
$album_filter = $_GET['album'] ?? '';

try {
    $conn = getDBConnection();
    
    // Lấy danh sách album
    $stmt = $conn->prepare("SELECT * FROM video_albums WHERE is_active = 1 ORDER BY display_order ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $albums[] = $row;
    }
    
    // Xây dựng query tìm kiếm
    $whereClause = "WHERE v.is_active = 1";
    $params = [];
    $types = "";
    
    if (!empty($search)) {
        $whereClause .= " AND (v.title LIKE ? OR v.description LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= "ss";
    }
    
    if (!empty($album_filter)) {
        $whereClause .= " AND v.album_id = ?";
        $params[] = (int)$album_filter;
        $types .= "i";
    }
    
    // Đếm tổng số video
    $countQuery = "SELECT COUNT(*) as total FROM videos v $whereClause";
    $stmt = $conn->prepare($countQuery);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $totalVideos = $stmt->get_result()->fetch_assoc()['total'];
    
    // Lấy danh sách video
    $query = "SELECT v.*, va.name as album_name 
              FROM videos v 
              LEFT JOIN video_albums va ON v.album_id = va.id 
              $whereClause 
              ORDER BY v.is_featured DESC, v.created_at DESC 
              LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $videos[] = $row;
    }
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $message = "Lỗi kết nối database: " . $e->getMessage();
    $messageType = "error";
}

$totalPages = ceil($totalVideos / $limit);

// Hàm lấy video ID từ URL YouTube
function getYouTubeVideoId($url) {
    preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches);
    return isset($matches[1]) ? $matches[1] : '';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Video - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard-style.css">
    <link rel="stylesheet" href="video-style.css">
    <script src="dropdown.js"></script>
</head>
<body>
        <?php include 'menu-don-gian.php'; ?>

    <main>
        <div class="admin-container">
            <div class="admin-header">
                <h1>📹 Quản lý Video</h1>
                <div class="admin-actions">
                    <a href="them-video.php" class="btn-primary">➕ Thêm video mới</a>
                    <a href="quan-ly-album-video.php" class="btn-secondary">📁 Quản lý album</a>
                    <a href="video.php" class="btn-outline" target="_blank">👀 Xem trang video</a>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <!-- Search and Filter -->
            <div class="search-filter-panel">
                <form method="GET" class="search-form">
                    <div class="search-group">
                        <input type="text" name="search" placeholder="🔍 Tìm kiếm video..." 
                               value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
                        
                        <select name="album">
                            <option value="">📁 Tất cả album</option>
                            <?php foreach ($albums as $album): ?>
                                <option value="<?php echo $album['id']; ?>" 
                                        <?php echo $album_filter == $album['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($album['name'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <button type="submit" class="btn-search">Tìm kiếm</button>
                        <a href="quan-ly-video.php" class="btn-reset">Đặt lại</a>
                    </div>
                </form>
            </div>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📹</div>
                    <div class="stat-info">
                        <h3><?php echo number_format($totalVideos); ?></h3>
                        <p>Tổng video</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📁</div>
                    <div class="stat-info">
                        <h3><?php echo count($albums); ?></h3>
                        <p>Album video</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⭐</div>
                    <div class="stat-info">
                        <h3><?php echo count(array_filter($videos, function($v) { return $v['is_featured']; })); ?></h3>
                        <p>Video nổi bật</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👁</div>
                    <div class="stat-info">
                        <h3><?php echo number_format(array_sum(array_column($videos, 'views'))); ?></h3>
                        <p>Tổng lượt xem</p>
                    </div>
                </div>
            </div>

            <!-- Video List -->
            <div class="content-panel">
                <div class="panel-header">
                    <h2>Danh sách Video</h2>
                    <div class="panel-actions">
                        <span class="result-count">Hiển thị <?php echo count($videos); ?> / <?php echo $totalVideos; ?> video</span>
                    </div>
                </div>

                <?php if (empty($videos)): ?>
                    <div class="empty-panel">
                        <p>📹 Không tìm thấy video nào.</p>
                        <a href="them-video.php" class="btn-primary">➕ Thêm video đầu tiên</a>
                    </div>
                <?php else: ?>
                    <div class="video-management-grid">
                        <?php foreach ($videos as $video): ?>
                            <div class="video-management-card">
                                <div class="video-thumbnail">
                                    <?php if ($video['video_type'] == 'youtube'): ?>
                                        <?php $videoId = getYouTubeVideoId($video['video_url']); ?>
                                        <img src="https://img.youtube.com/vi/<?php echo $videoId; ?>/hqdefault.jpg" 
                                             alt="<?php echo htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php else: ?>
                                        <img src="<?php echo htmlspecialchars($video['thumbnail_url'] ?? 'images/video-default.jpg', ENT_QUOTES, 'UTF-8'); ?>" 
                                             alt="<?php echo htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php endif; ?>
                                    
                                    <?php if ($video['is_featured']): ?>
                                        <span class="featured-badge">⭐ Nổi bật</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="video-info">
                                    <h3><?php echo htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                    <p class="video-meta">
                                        📁 <?php echo htmlspecialchars($video['album_name'] ?? 'Chưa phân loại', ENT_QUOTES, 'UTF-8'); ?><br>
                                        👁 <?php echo number_format($video['views']); ?> lượt xem<br>
                                        📅 <?php echo date('d/m/Y H:i', strtotime($video['created_at'])); ?>
                                    </p>
                                    
                                    <div class="video-actions">
                                        <a href="sua-video.php?id=<?php echo $video['id']; ?>" class="btn-edit">✏️ Sửa</a>
                                        
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Bạn có chắc muốn <?php echo $video['is_featured'] ? 'bỏ nổi bật' : 'đặt nổi bật'; ?> video này?')">
                                            <input type="hidden" name="action" value="toggle_featured">
                                            <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                                            <button type="submit" class="btn-feature">
                                                <?php echo $video['is_featured'] ? '⭐ Bỏ nổi bật' : '⭐ Nổi bật'; ?>
                                            </button>
                                        </form>
                                        
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Bạn có chắc muốn xóa video này?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="video_id" value="<?php echo $video['id']; ?>">
                                            <button type="submit" class="btn-delete">🗑️ Xóa</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="page-btn">« Trước</a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                               class="page-btn <?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="page-btn">Sau »</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <style>
    .video-management-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .video-management-card {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }

    .video-management-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }

    .video-management-card .video-thumbnail {
        position: relative;
        height: 180px;
        overflow: hidden;
    }

    .video-management-card .video-thumbnail img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .video-management-card .video-info {
        padding: 15px;
    }

    .video-management-card .video-info h3 {
        font-size: 16px;
        margin-bottom: 10px;
        color: #2c3e50;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .video-meta {
        font-size: 13px;
        color: #666;
        margin-bottom: 15px;
        line-height: 1.4;
    }

    .video-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .video-actions .btn-edit,
    .video-actions .btn-feature,
    .video-actions .btn-delete {
        padding: 6px 12px;
        border: none;
        border-radius: 5px;
        font-size: 12px;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-edit {
        background: #17a2b8;
        color: white;
    }

    .btn-feature {
        background: #ffc107;
        color: #212529;
    }

    .btn-delete {
        background: #dc3545;
        color: white;
    }

    .btn-edit:hover { background: #138496; }
    .btn-feature:hover { background: #e0a800; }
    .btn-delete:hover { background: #c82333; }
    </style>
</body>
</html>