<?php
header("Content-Type: text/html; charset=utf-8");
session_start();
require_once 'config.php';
require_once 'auth.php';

$isLoggedIn = authIsLoggedIn();
$currentRole = authCurrentRole();
$displayName = $isLoggedIn ? authDisplayName() : '';

// Lấy tham số
$album_id = isset($_GET['album']) ? (int)$_GET['album'] : 0;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Lấy danh sách album
$albums = [];
$videos = [];
$totalVideos = 0;
$currentAlbum = null;
$hasVideoTables = false;

try {
    $conn = getDBConnection();
    
    // Kiểm tra xem bảng video có tồn tại không
    $result = $conn->query("SHOW TABLES LIKE 'videos'");
    $hasVideoTables = ($result && $result->num_rows > 0);
    
    if ($hasVideoTables) {
        // Kiểm tra bảng video_albums
        $result = $conn->query("SHOW TABLES LIKE 'video_albums'");
        if ($result && $result->num_rows > 0) {
            // Lấy danh sách album
            $stmt = $conn->prepare("SELECT * FROM video_albums WHERE is_active = 1 ORDER BY display_order ASC, name ASC");
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $albums[] = $row;
            }
            
            // Lấy thông tin album hiện tại
            if ($album_id > 0) {
                $stmt = $conn->prepare("SELECT * FROM video_albums WHERE id = ? AND is_active = 1");
                $stmt->bind_param("i", $album_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $currentAlbum = $result->fetch_assoc();
            }
        }
        
        // Lấy danh sách video
        if ($album_id > 0) {
            // Video theo album
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM videos WHERE album_id = ? AND is_active = 1");
            $stmt->bind_param("i", $album_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $totalVideos = $result->fetch_assoc()['total'];
            
            $stmt = $conn->prepare("SELECT * FROM videos WHERE album_id = ? AND is_active = 1 ORDER BY display_order ASC, created_at DESC LIMIT ? OFFSET ?");
            $stmt->bind_param("iii", $album_id, $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            // Tất cả video
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM videos WHERE is_active = 1");
            $stmt->execute();
            $result = $stmt->get_result();
            $totalVideos = $result->fetch_assoc()['total'];
            
            $stmt = $conn->prepare("SELECT v.*, va.name as album_name FROM videos v LEFT JOIN video_albums va ON v.album_id = va.id WHERE v.is_active = 1 ORDER BY v.is_featured DESC, v.display_order ASC, v.created_at DESC LIMIT ? OFFSET ?");
            $stmt->bind_param("ii", $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
        }
        
        while ($row = $result->fetch_assoc()) {
            $videos[] = $row;
        }
        
        $stmt->close();
    }
    
    $conn->close();
} catch (Exception $e) {
    // Fallback data nếu có lỗi
    $hasVideoTables = false;
    $albums = [
        ['id' => 1, 'name' => 'Hoạt động UBND', 'description' => 'Video về các hoạt động của UBND xã'],
        ['id' => 2, 'name' => 'Sự kiện văn hóa', 'description' => 'Video về các sự kiện văn hóa, lễ hội']
    ];
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
    <title><?php echo $currentAlbum ? $currentAlbum['name'] : 'Chuyên mục Video'; ?> - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="video-style.css">
    <script src="dropdown.js"></script>
</head>
<body>
    <!-- Header thống nhất -->
    <?php include 'menu-don-gian.php'; ?>

    <main>
        <section class="video-section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">
                        <?php echo $currentAlbum ? htmlspecialchars($currentAlbum['name'], ENT_QUOTES, 'UTF-8') : 'Chuyên mục Video'; ?>
                    </h2>
                    <?php if ($currentAlbum && !empty($currentAlbum['description'])): ?>
                        <p class="section-description"><?php echo htmlspecialchars($currentAlbum['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>
                </div>

                <!-- Album Filter -->
                <div class="album-filter">
                    <div class="filter-tabs">
                        <a href="video.php" class="filter-tab <?php echo $album_id == 0 ? 'active' : ''; ?>">
                            📺 Tất cả video
                        </a>
                        <?php foreach ($albums as $album): ?>
                            <a href="video.php?album=<?php echo $album['id']; ?>" 
                               class="filter-tab <?php echo $album_id == $album['id'] ? 'active' : ''; ?>">
                                📁 <?php echo htmlspecialchars($album['name'], ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Video Grid -->
                <div class="video-grid">
                    <?php if (!$hasVideoTables): ?>
                        <div class="empty-panel">
                            <h3>� Hệ thống video chưa được thiết lập</h3>
                            <p>Bảng dữ liệu video chưa được tạo. Vui lòng chạy script thiết lập để sử dụng chức năng video.</p>
                            <?php if ($isLoggedIn): ?>
                                <a href="setup-video-system.php" class="btn-primary">🚀 Thiết lập hệ thống video</a>
                            <?php endif; ?>
                            <p><small>Hoặc liên hệ quản trị viên để được hỗ trợ.</small></p>
                        </div>
                    <?php elseif (empty($videos)): ?>
                        <div class="empty-panel">
                            <p>📹 Chưa có video nào được đăng tải.</p>
                            <?php if ($isLoggedIn): ?>
                                <a href="quan-ly-video.php" class="btn-primary">➕ Thêm video đầu tiên</a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <?php foreach ($videos as $video): ?>
                            <div class="video-card">
                                <div class="video-thumbnail">
                                    <?php if ($video['video_type'] == 'youtube'): ?>
                                        <?php $videoId = getYouTubeVideoId($video['video_url']); ?>
                                        <img src="https://img.youtube.com/vi/<?php echo $videoId; ?>/maxresdefault.jpg" 
                                             alt="<?php echo htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8'); ?>"
                                             onerror="this.src='https://img.youtube.com/vi/<?php echo $videoId; ?>/hqdefault.jpg'">
                                        <div class="play-button" onclick="playVideo(<?php echo $video['id']; ?>, '<?php echo $videoId; ?>')">
                                            <span>▶</span>
                                        </div>
                                    <?php else: ?>
                                        <?php 
                                        // Tạo thumbnail cho video local
                                        $thumbnailUrl = $video['thumbnail_url'] ?? '';
                                        if (empty($thumbnailUrl)) {
                                            // Tạo thumbnail mặc định dựa trên loại file
                                            $extension = strtolower(pathinfo($video['video_url'], PATHINFO_EXTENSION));
                                            if (in_array($extension, ['wav', 'mp3'])) {
                                                $thumbnailUrl = 'data:image/svg+xml;base64,' . base64_encode('
                                                    <svg width="300" height="200" xmlns="http://www.w3.org/2000/svg">
                                                        <defs>
                                                            <linearGradient id="audioGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                                                <stop offset="0%" style="stop-color:#f093fb;stop-opacity:1" />
                                                                <stop offset="100%" style="stop-color:#f5576c;stop-opacity:1" />
                                                            </linearGradient>
                                                        </defs>
                                                        <rect width="100%" height="100%" fill="url(#audioGrad)"/>
                                                        <text x="50%" y="40%" text-anchor="middle" fill="white" font-size="48" font-family="Arial">🎵</text>
                                                        <text x="50%" y="70%" text-anchor="middle" fill="white" font-size="16" font-family="Arial">AUDIO</text>
                                                    </svg>
                                                ');
                                            } else {
                                                $thumbnailUrl = 'data:image/svg+xml;base64,' . base64_encode('
                                                    <svg width="300" height="200" xmlns="http://www.w3.org/2000/svg">
                                                        <defs>
                                                            <linearGradient id="videoGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                                                <stop offset="0%" style="stop-color:#667eea;stop-opacity:1" />
                                                                <stop offset="100%" style="stop-color:#764ba2;stop-opacity:1" />
                                                            </linearGradient>
                                                        </defs>
                                                        <rect width="100%" height="100%" fill="url(#videoGrad)"/>
                                                        <text x="50%" y="40%" text-anchor="middle" fill="white" font-size="48" font-family="Arial">🎬</text>
                                                        <text x="50%" y="70%" text-anchor="middle" fill="white" font-size="16" font-family="Arial">VIDEO</text>
                                                    </svg>
                                                ');
                                            }
                                        }
                                        ?>
                                        <img src="<?php echo htmlspecialchars($thumbnailUrl, ENT_QUOTES, 'UTF-8'); ?>" 
                                             alt="<?php echo htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8'); ?>"
                                             onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjY2NjIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IndoaXRlIiBmb250LXNpemU9IjE2Ij5WaWRlbzwvdGV4dD48L3N2Zz4='">
                                        <div class="play-button" onclick="playLocalVideo(<?php echo $video['id']; ?>, '<?php echo htmlspecialchars($video['video_url'], ENT_QUOTES, 'UTF-8'); ?>')">
                                            <span>▶</span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($video['is_featured']): ?>
                                        <span class="featured-badge">⭐ Nổi bật</span>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($video['duration'])): ?>
                                        <span class="duration-badge"><?php echo htmlspecialchars($video['duration'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="video-info">
                                    <h3 class="video-title">
                                        <a href="javascript:void(0)" onclick="<?php 
                                            if ($video['video_type'] == 'youtube') {
                                                $videoId = getYouTubeVideoId($video['video_url']);
                                                echo "playVideo({$video['id']}, '{$videoId}')";
                                            } else {
                                                echo "playLocalVideo({$video['id']}, '" . htmlspecialchars($video['video_url'], ENT_QUOTES, 'UTF-8') . "')";
                                            }
                                        ?>" style="color: #2c3e50; text-decoration: none;">
                                            <?php echo htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8'); ?>
                                        </a>
                                    </h3>
                                    <?php if (!empty($video['description'])): ?>
                                        <p class="video-description"><?php echo htmlspecialchars(mb_substr($video['description'], 0, 100), ENT_QUOTES, 'UTF-8'); ?>...</p>
                                    <?php endif; ?>
                                    
                                    <div class="video-meta">
                                        <?php if (isset($video['album_name']) && !empty($video['album_name'])): ?>
                                            <span class="album-name">📁 <?php echo htmlspecialchars($video['album_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <?php endif; ?>
                                        <span class="view-count">👁 <?php echo number_format($video['views']); ?> lượt xem</span>
                                        <span class="upload-date">📅 <?php echo date('d/m/Y', strtotime($video['created_at'])); ?></span>
                                    </div>
                                    
                                    <!-- Video Actions -->
                                    <div class="video-actions">
                                        <?php if ($video['video_type'] == 'youtube'): ?>
                                            <?php $videoId = getYouTubeVideoId($video['video_url']); ?>
                                            <a href="javascript:void(0)" onclick="playVideo(<?php echo $video['id']; ?>, '<?php echo $videoId; ?>')" class="btn-play">
                                                ▶️ Xem Video
                                            </a>
                                            <a href="<?php echo htmlspecialchars($video['video_url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="btn-external">
                                                🔗 Mở YouTube
                                            </a>
                                        <?php else: ?>
                                            <a href="javascript:void(0)" onclick="playLocalVideo(<?php echo $video['id']; ?>, '<?php echo htmlspecialchars($video['video_url'], ENT_QUOTES, 'UTF-8'); ?>')" class="btn-play">
                                                ▶️ Phát Video
                                            </a>
                                            <a href="<?php echo htmlspecialchars($video['video_url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" class="btn-external">
                                                📁 Tải về
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($isLoggedIn): ?>
                                            <!-- Admin Actions -->
                                            <a href="sua-video.php?id=<?php echo $video['id']; ?>" class="btn-edit">
                                                ✏️ Sửa
                                            </a>
                                            <a href="javascript:void(0)" onclick="saveVideo(<?php echo $video['id']; ?>)" class="btn-save">
                                                💾 Lưu
                                            </a>
                                            <a href="javascript:void(0)" onclick="deleteVideo(<?php echo $video['id']; ?>, '<?php echo htmlspecialchars($video['title'], ENT_QUOTES, 'UTF-8'); ?>')" class="btn-delete">
                                                🗑️ Xóa
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

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

                <!-- Admin Actions -->
                <?php if ($isLoggedIn): ?>
                    <div class="admin-actions">
                        <a href="quan-ly-video.php" class="btn-primary">📹 Quản lý video</a>
                        <a href="them-video.php" class="btn-secondary">➕ Thêm video mới</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <!-- Video Modal -->
    <div id="videoModal" class="video-modal" onclick="closeVideoModal()">
        <div class="video-modal-content" onclick="event.stopPropagation()">
            <span class="video-modal-close" onclick="closeVideoModal()">&times;</span>
            <div id="videoPlayer"></div>
            <div class="video-modal-info">
                <h3 id="modalVideoTitle"></h3>
                <p id="modalVideoDescription"></p>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="video-player.js"></script>
    
    <script>
    // Function để lưu video (bookmark)
    function saveVideo(videoId) {
        if (confirm('Lưu video này vào danh sách yêu thích?')) {
            fetch('api-save-video.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin', // Đảm bảo gửi cookie session
                body: JSON.stringify({ 
                    video_id: videoId,
                    action: 'save'
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('✅ Đã lưu video thành công!');
                } else {
                    alert('❌ Lỗi: ' + (data.message || 'Không thể lưu video'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Lỗi kết nối: ' + error.message);
            });
        }
    }
    
    // Function để xóa video
    function deleteVideo(videoId, videoTitle) {
        if (confirm('Bạn có chắc muốn xóa video "' + videoTitle + '"?\n\nVideo sẽ bị ẩn khỏi danh sách hiển thị.')) {
            fetch('api-delete-video.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin', // Đảm bảo gửi cookie session
                body: JSON.stringify({ 
                    video_id: videoId,
                    action: 'delete'
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert('✅ Đã xóa video thành công!');
                    // Reload trang để cập nhật danh sách
                    location.reload();
                } else {
                    alert('❌ Lỗi: ' + (data.message || 'Không thể xóa video'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Lỗi kết nối: ' + error.message);
            });
        }
    }
    </script>
    
    <style>
    .video-actions {
        display: flex;
        gap: 8px;
        margin-top: 15px;
        flex-wrap: wrap;
    }
    
    .btn-play, .btn-external, .btn-edit, .btn-save, .btn-delete {
        padding: 6px 12px;
        border-radius: 4px;
        text-decoration: none;
        font-size: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
        display: inline-block;
        text-align: center;
    }
    
    .btn-play {
        background: #28a745;
        color: white;
    }
    
    .btn-play:hover {
        background: #218838;
        transform: translateY(-1px);
    }
    
    .btn-external {
        background: #007bff;
        color: white;
    }
    
    .btn-external:hover {
        background: #0056b3;
        transform: translateY(-1px);
    }
    
    .btn-edit {
        background: #17a2b8;
        color: white;
    }
    
    .btn-edit:hover {
        background: #138496;
        transform: translateY(-1px);
    }
    
    .btn-save {
        background: #6f42c1;
        color: white;
    }
    
    .btn-save:hover {
        background: #5a32a3;
        transform: translateY(-1px);
    }
    
    .btn-delete {
        background: #dc3545;
        color: white;
    }
    
    .btn-delete:hover {
        background: #c82333;
        transform: translateY(-1px);
    }
    
    .video-title a:hover {
        color: #007bff !important;
        text-decoration: underline !important;
    }
    
    .video-card {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .video-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .video-card:hover .video-thumbnail img {
        transform: scale(1.05);
    }
    
    .video-thumbnail {
        overflow: hidden;
    }
    
    .video-thumbnail img {
        transition: transform 0.3s ease;
    }
    
    @media (max-width: 768px) {
        .video-actions {
            flex-direction: column;
        }
        
        .btn-play, .btn-external {
            text-align: center;
        }
    }
    </style>
</body>
</html>