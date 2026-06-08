<?php
header("Content-Type: text/html; charset=utf-8");
session_start();
require_once 'config.php';
require_once 'auth.php';

$isLoggedIn = authIsLoggedIn();
$currentRole = authCurrentRole();
$displayName = $isLoggedIn ? authDisplayName() : '';

// Lấy tham số
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Quét thư mục videos để lấy tất cả file
$videoFiles = [];
$videoDir = 'videos/';

// Tạo thư mục nếu chưa có
if (!is_dir($videoDir)) {
    mkdir($videoDir, 0755, true);
}

// Các định dạng video được hỗ trợ
$supportedFormats = ['mp4', 'webm', 'ogg', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'wav', 'mp3'];

// Quét thư mục
if (is_dir($videoDir)) {
    $files = scandir($videoDir);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..' && is_file($videoDir . $file)) {
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($extension, $supportedFormats)) {
                $filePath = $videoDir . $file;
                $fileSize = filesize($filePath);
                $fileTime = filemtime($filePath);
                
                $videoFiles[] = [
                    'name' => $file,
                    'path' => $filePath,
                    'size' => $fileSize,
                    'extension' => $extension,
                    'modified' => $fileTime,
                    'title' => pathinfo($file, PATHINFO_FILENAME),
                    'is_video' => in_array($extension, ['mp4', 'webm', 'ogg', 'avi', 'mov', 'wmv', 'flv', 'mkv']),
                    'is_audio' => in_array($extension, ['wav', 'mp3'])
                ];
            }
        }
    }
}

// Sắp xếp theo thời gian sửa đổi (mới nhất trước)
usort($videoFiles, function($a, $b) {
    return $b['modified'] - $a['modified'];
});

$totalFiles = count($videoFiles);
$totalPages = ceil($totalFiles / $limit);

// Phân trang
$paginatedFiles = array_slice($videoFiles, $offset, $limit);

// Hàm format kích thước file
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
    <title>Tất cả File Video - UBND Xã Long Hiệp</title>
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
                    <h2 class="section-title">📁 Tất cả File Video trong Thư mục</h2>
                    <p class="section-description">Hiển thị tất cả file video và audio có trong thư mục videos/</p>
                </div>

                <!-- Navigation Tabs -->
                <div class="album-filter">
                    <div class="filter-tabs">
                        <a href="video.php" class="filter-tab">
                            📺 Video từ Database
                        </a>
                        <a href="video-files.php" class="filter-tab active">
                            📁 Tất cả File Video (<?php echo $totalFiles; ?>)
                        </a>
                    </div>
                </div>

                <!-- File Statistics -->
                <div class="stats-panel">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $totalFiles; ?></span>
                        <span class="stat-label">Tổng file</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo count(array_filter($videoFiles, function($f) { return $f['is_video']; })); ?></span>
                        <span class="stat-label">Video</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo count(array_filter($videoFiles, function($f) { return $f['is_audio']; })); ?></span>
                        <span class="stat-label">Audio</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo formatFileSize(array_sum(array_column($videoFiles, 'size'))); ?></span>
                        <span class="stat-label">Tổng dung lượng</span>
                    </div>
                </div>

                <!-- Video Grid -->
                <div class="video-grid">
                    <?php if (empty($paginatedFiles)): ?>
                        <div class="empty-panel">
                            <h3>📁 Thư mục videos/ trống</h3>
                            <p>Chưa có file video nào trong thư mục. Hãy upload video để xem ở đây.</p>
                            <?php if ($isLoggedIn): ?>
                                <a href="them-video.php" class="btn-primary">➕ Upload video đầu tiên</a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <?php foreach ($paginatedFiles as $file): ?>
                            <div class="video-card file-card">
                                <div class="video-thumbnail">
                                    <?php if ($file['is_video']): ?>
                                        <div class="video-preview" style="background: linear-gradient(45deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; height: 200px; color: white;">
                                            <div style="text-align: center;">
                                                <div style="font-size: 48px; margin-bottom: 10px;">🎬</div>
                                                <div style="font-size: 14px; font-weight: bold;"><?php echo strtoupper($file['extension']); ?></div>
                                            </div>
                                        </div>
                                        <div class="play-button" onclick="playLocalFile('<?php echo htmlspecialchars($file['path'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($file['title'], ENT_QUOTES, 'UTF-8'); ?>')">
                                            <span>▶</span>
                                        </div>
                                    <?php else: ?>
                                        <div class="audio-preview" style="background: linear-gradient(45deg, #f093fb 0%, #f5576c 100%); display: flex; align-items: center; justify-content: center; height: 200px; color: white;">
                                            <div style="text-align: center;">
                                                <div style="font-size: 48px; margin-bottom: 10px;">🎵</div>
                                                <div style="font-size: 14px; font-weight: bold;"><?php echo strtoupper($file['extension']); ?></div>
                                            </div>
                                        </div>
                                        <div class="play-button" onclick="playAudioFile('<?php echo htmlspecialchars($file['path'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($file['title'], ENT_QUOTES, 'UTF-8'); ?>')">
                                            <span>▶</span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <span class="file-type-badge"><?php echo strtoupper($file['extension']); ?></span>
                                </div>
                                
                                <div class="video-info">
                                    <h3 class="video-title"><?php echo htmlspecialchars($file['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                    
                                    <div class="video-meta">
                                        <span class="file-name">📄 <?php echo htmlspecialchars($file['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <span class="file-size">💾 <?php echo formatFileSize($file['size']); ?></span>
                                        <span class="file-date">📅 <?php echo date('d/m/Y H:i', $file['modified']); ?></span>
                                        <span class="file-type">
                                            <?php if ($file['is_video']): ?>
                                                🎬 Video
                                            <?php else: ?>
                                                🎵 Audio
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    
                                    <?php if ($isLoggedIn): ?>
                                        <div class="file-actions">
                                            <?php if ($file['is_video']): ?>
                                                <a href="javascript:void(0)" onclick="playLocalFile('<?php echo htmlspecialchars($file['path'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($file['title'], ENT_QUOTES, 'UTF-8'); ?>')" class="btn-play">▶️ Phát Video</a>
                                            <?php else: ?>
                                                <a href="javascript:void(0)" onclick="playAudioFile('<?php echo htmlspecialchars($file['path'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($file['title'], ENT_QUOTES, 'UTF-8'); ?>')" class="btn-play">🎵 Phát Audio</a>
                                            <?php endif; ?>
                                            <a href="<?php echo $file['path']; ?>" target="_blank" class="btn-view">👀 Xem</a>
                                            <a href="<?php echo $file['path']; ?>" download class="btn-download">⬇️ Tải</a>
                                            <a href="javascript:void(0)" onclick="saveFile('<?php echo htmlspecialchars($file['path'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($file['title'], ENT_QUOTES, 'UTF-8'); ?>')" class="btn-save">💾 Lưu</a>
                                            <a href="javascript:void(0)" onclick="deleteFile('<?php echo htmlspecialchars($file['path'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($file['name'], ENT_QUOTES, 'UTF-8'); ?>')" class="btn-delete">🗑️ Xóa</a>
                                        </div>
                                    <?php else: ?>
                                        <div class="file-actions">
                                            <?php if ($file['is_video']): ?>
                                                <a href="javascript:void(0)" onclick="playLocalFile('<?php echo htmlspecialchars($file['path'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($file['title'], ENT_QUOTES, 'UTF-8'); ?>')" class="btn-play">▶️ Phát Video</a>
                                            <?php else: ?>
                                                <a href="javascript:void(0)" onclick="playAudioFile('<?php echo htmlspecialchars($file['path'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($file['title'], ENT_QUOTES, 'UTF-8'); ?>')" class="btn-play">🎵 Phát Audio</a>
                                            <?php endif; ?>
                                            <a href="<?php echo $file['path']; ?>" target="_blank" class="btn-view">👀 Xem File</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>" class="page-btn">« Trước</a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <a href="?page=<?php echo $i; ?>" 
                               class="page-btn <?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?>" class="page-btn">Sau »</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Admin Actions -->
                <?php if ($isLoggedIn): ?>
                    <div class="admin-actions">
                        <a href="them-video.php" class="btn-primary">➕ Upload video mới</a>
                        <a href="quan-ly-video.php" class="btn-secondary">📹 Quản lý database</a>
                        <button onclick="refreshFiles()" class="btn-outline">🔄 Làm mới danh sách</button>
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

    <script>
    // Phát file video local
    function playLocalFile(filePath, title) {
        const modal = document.getElementById('videoModal');
        const player = document.getElementById('videoPlayer');
        
        player.innerHTML = `
            <video controls autoplay style="width: 100%; height: 100%;">
                <source src="${filePath}" type="video/mp4">
                <source src="${filePath}" type="video/webm">
                <source src="${filePath}" type="video/ogg">
                Trình duyệt của bạn không hỗ trợ video HTML5.
            </video>
        `;
        
        document.getElementById('modalVideoTitle').textContent = title;
        document.getElementById('modalVideoDescription').textContent = 'File: ' + filePath;
        
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
    
    // Phát file audio
    function playAudioFile(filePath, title) {
        const modal = document.getElementById('videoModal');
        const player = document.getElementById('videoPlayer');
        
        player.innerHTML = `
            <div style="background: linear-gradient(45deg, #f093fb 0%, #f5576c 100%); height: 300px; display: flex; flex-direction: column; align-items: center; justify-content: center; color: white;">
                <div style="font-size: 72px; margin-bottom: 20px;">🎵</div>
                <h3 style="margin-bottom: 20px;">${title}</h3>
                <audio controls autoplay style="width: 80%;">
                    <source src="${filePath}" type="audio/mpeg">
                    <source src="${filePath}" type="audio/wav">
                    Trình duyệt của bạn không hỗ trợ audio HTML5.
                </audio>
            </div>
        `;
        
        document.getElementById('modalVideoTitle').textContent = title;
        document.getElementById('modalVideoDescription').textContent = 'File: ' + filePath;
        
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
    
    // Đóng modal
    function closeVideoModal() {
        const modal = document.getElementById('videoModal');
        const player = document.getElementById('videoPlayer');
        
        player.innerHTML = '';
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    // Làm mới danh sách
    function refreshFiles() {
        location.reload();
    }
    
    // Lưu file
    function saveFile(filePath, fileName) {
        if (confirm('Lưu file "' + fileName + '" vào danh sách yêu thích?')) {
            // Tạo một entry trong database cho file này
            fetch('api-save-file.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    file_path: filePath,
                    file_name: fileName,
                    action: 'save'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Đã lưu file thành công!');
                } else {
                    alert('❌ Lỗi: ' + (data.message || 'Không thể lưu file'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Lỗi kết nối');
            });
        }
    }
    
    // Xóa file
    function deleteFile(filePath, fileName) {
        if (confirm('Bạn có chắc muốn xóa file "' + fileName + '"?\n\nFile sẽ bị xóa vĩnh viễn khỏi server!')) {
            fetch('api-delete-file.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    file_path: filePath,
                    file_name: fileName,
                    action: 'delete'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Đã xóa file thành công!');
                    // Reload trang để cập nhật danh sách
                    location.reload();
                } else {
                    alert('❌ Lỗi: ' + (data.message || 'Không thể xóa file'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Lỗi kết nối');
            });
        }
    }
    
    // Xử lý phím ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeVideoModal();
        }
    });
    </script>

    <style>
    .stats-panel {
        display: flex;
        justify-content: center;
        gap: 30px;
        margin: 30px 0;
        padding: 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .stat-item {
        text-align: center;
    }
    
    .stat-number {
        display: block;
        font-size: 24px;
        font-weight: bold;
        color: #007bff;
    }
    
    .stat-label {
        font-size: 14px;
        color: #666;
    }
    
    .file-card .video-meta {
        font-size: 12px;
        line-height: 1.4;
    }
    
    .file-card .video-meta span {
        display: block;
        margin: 3px 0;
    }
    
    .file-type-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(0,0,0,0.8);
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
    }
    
    .file-actions {
        display: flex;
        gap: 10px;
        margin-top: 10px;
    }
    
    .btn-view, .btn-download, .btn-save, .btn-delete {
        padding: 5px 10px;
        border-radius: 4px;
        text-decoration: none;
        font-size: 12px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn-play {
        background: #28a745;
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        text-decoration: none;
        font-size: 12px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn-play:hover {
        background: #218838;
        transform: translateY(-1px);
    }
    
    .btn-view {
        background: #17a2b8;
        color: white;
    }
    
    .btn-view:hover {
        background: #138496;
    }
    
    .btn-download {
        background: #28a745;
        color: white;
    }
    
    .btn-download:hover {
        background: #218838;
    }
    
    .btn-save {
        background: #6f42c1;
        color: white;
    }
    
    .btn-save:hover {
        background: #5a32a3;
    }
    
    .btn-delete {
        background: #dc3545;
        color: white;
    }
    
    .btn-delete:hover {
        background: #c82333;
    }
    
    @media (max-width: 768px) {
        .stats-panel {
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .file-actions {
            flex-direction: column;
            gap: 5px;
        }
    }
    </style>
</body>
</html>