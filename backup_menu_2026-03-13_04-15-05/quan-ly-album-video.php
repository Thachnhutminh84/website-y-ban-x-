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
        
        if ($action == 'add' && !empty($_POST['name'])) {
            $name = trim($_POST['name']);
            $description = trim($_POST['description'] ?? '');
            $display_order = (int)($_POST['display_order'] ?? 0);
            
            $stmt = $conn->prepare("INSERT INTO video_albums (name, description, display_order, is_active) VALUES (?, ?, ?, 1)");
            $stmt->bind_param("ssi", $name, $description, $display_order);
            $stmt->execute();
            $message = "Đã thêm album '$name' thành công!";
            $messageType = "success";
        }
        
        if ($action == 'edit' && isset($_POST['album_id'])) {
            $albumId = (int)$_POST['album_id'];
            $name = trim($_POST['name']);
            $description = trim($_POST['description'] ?? '');
            $display_order = (int)($_POST['display_order'] ?? 0);
            
            $stmt = $conn->prepare("UPDATE video_albums SET name = ?, description = ?, display_order = ? WHERE id = ?");
            $stmt->bind_param("ssii", $name, $description, $display_order, $albumId);
            $stmt->execute();
            $message = "Đã cập nhật album thành công!";
            $messageType = "success";
        }
        
        if ($action == 'delete' && isset($_POST['album_id'])) {
            $albumId = (int)$_POST['album_id'];
            $stmt = $conn->prepare("UPDATE video_albums SET is_active = 0 WHERE id = ?");
            $stmt->bind_param("i", $albumId);
            $stmt->execute();
            $message = "Đã xóa album thành công!";
            $messageType = "success";
        }
        
        if ($action == 'toggle_active' && isset($_POST['album_id'])) {
            $albumId = (int)$_POST['album_id'];
            $stmt = $conn->prepare("UPDATE video_albums SET is_active = NOT is_active WHERE id = ?");
            $stmt->bind_param("i", $albumId);
            $stmt->execute();
            $message = "Đã cập nhật trạng thái album!";
            $messageType = "success";
        }
        
        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        $message = "Lỗi: " . $e->getMessage();
        $messageType = "error";
    }
}

// Lấy danh sách album
$albums = [];
try {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("
        SELECT va.*, 
               (SELECT COUNT(*) FROM videos v WHERE v.album_id = va.id AND v.is_active = 1) as video_count
        FROM video_albums va 
        ORDER BY va.display_order ASC, va.name ASC
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $albums[] = $row;
    }
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $message = "Lỗi kết nối database: " . $e->getMessage();
    $messageType = "error";
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Album Video - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard-style.css">
    <script src="dropdown.js"></script>
</head>
<body>
    <header class="header--compact">
        <div class="container">
            <div class="logo">
                <img src="images/logo.png" alt="Logo UBND Xã Long Hiệp">
                <div class="header-text">
                    <h1>ỦY BAN NHÂN DÂN XÃ LONG HIỆP</h1>
                    <p>Phục vụ nhân dân - Xây dựng quê hương</p>
                </div>
            </div>
            <nav>
                <ul class="menu">
                    <li><a href="index.php">Trang chủ</a></li>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="quan-ly-video.php">Quản lý video</a></li>
                    <li><a href="quan-ly-album-video.php" class="active">Quản lý album</a></li>
                    <li class="admin-info">
                        👤 <?php echo htmlspecialchars(authRoleLabel($currentRole), ENT_QUOTES, 'UTF-8'); ?>
                        <span><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></span>
                        <a href="logout.php">Đăng xuất</a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="admin-container">
            <div class="admin-header">
                <h1>📁 Quản lý Album Video</h1>
                <div class="admin-actions">
                    <button onclick="showAddForm()" class="btn-primary">➕ Thêm album mới</button>
                    <a href="quan-ly-video.php" class="btn-secondary">📹 Quản lý video</a>
                    <a href="video.php" class="btn-outline" target="_blank">👀 Xem trang video</a>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <!-- Form thêm/sửa album -->
            <div id="albumForm" class="form-panel" style="display: none;">
                <div class="panel-header">
                    <h2 id="formTitle">➕ Thêm Album Mới</h2>
                    <button onclick="hideForm()" class="btn-close">✕</button>
                </div>
                
                <form method="POST" id="albumFormElement">
                    <input type="hidden" name="action" id="formAction" value="add">
                    <input type="hidden" name="album_id" id="albumId" value="">
                    
                    <div class="form-group">
                        <label for="name">Tên album *</label>
                        <input type="text" id="name" name="name" required placeholder="Nhập tên album...">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Mô tả album</label>
                        <textarea id="description" name="description" rows="3" placeholder="Nhập mô tả album..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="display_order">Thứ tự hiển thị</label>
                        <input type="number" id="display_order" name="display_order" value="0" min="0">
                        <small>Số nhỏ hơn sẽ hiển thị trước</small>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn-primary">💾 Lưu album</button>
                        <button type="button" onclick="hideForm()" class="btn-secondary">❌ Hủy</button>
                    </div>
                </form>
            </div>

            <!-- Danh sách album -->
            <div class="content-panel">
                <div class="panel-header">
                    <h2>📋 Danh sách Album</h2>
                    <div class="panel-actions">
                        <span class="result-count">Tổng cộng: <?php echo count($albums); ?> album</span>
                    </div>
                </div>

                <?php if (empty($albums)): ?>
                    <div class="empty-panel">
                        <p>📁 Chưa có album nào được tạo.</p>
                        <button onclick="showAddForm()" class="btn-primary">➕ Tạo album đầu tiên</button>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Tên Album</th>
                                    <th>Mô tả</th>
                                    <th>Số Video</th>
                                    <th>Thứ tự</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày tạo</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($albums as $index => $album): ?>
                                    <tr class="<?php echo $album['is_active'] ? '' : 'inactive'; ?>">
                                        <td><?php echo $index + 1; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($album['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars(mb_substr($album['description'] ?? '', 0, 50), ENT_QUOTES, 'UTF-8'); ?>
                                            <?php if (strlen($album['description'] ?? '') > 50): ?>...<?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-info"><?php echo $album['video_count']; ?> video</span>
                                        </td>
                                        <td><?php echo $album['display_order']; ?></td>
                                        <td>
                                            <?php if ($album['is_active']): ?>
                                                <span class="badge badge-success">✅ Hoạt động</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">❌ Ẩn</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($album['created_at'])); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button onclick="editAlbum(<?php echo htmlspecialchars(json_encode($album), ENT_QUOTES, 'UTF-8'); ?>)" 
                                                        class="btn-edit" title="Sửa">✏️</button>
                                                
                                                <form method="POST" style="display: inline;" 
                                                      onsubmit="return confirm('Bạn có chắc muốn <?php echo $album['is_active'] ? 'ẩn' : 'hiện'; ?> album này?')">
                                                    <input type="hidden" name="action" value="toggle_active">
                                                    <input type="hidden" name="album_id" value="<?php echo $album['id']; ?>">
                                                    <button type="submit" class="btn-toggle" title="<?php echo $album['is_active'] ? 'Ẩn' : 'Hiện'; ?>">
                                                        <?php echo $album['is_active'] ? '👁️' : '🙈'; ?>
                                                    </button>
                                                </form>
                                                
                                                <form method="POST" style="display: inline;" 
                                                      onsubmit="return confirm('Bạn có chắc muốn xóa album này? Các video trong album sẽ chuyển về chưa phân loại.')">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="album_id" value="<?php echo $album['id']; ?>">
                                                    <button type="submit" class="btn-delete" title="Xóa">🗑️</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
    function showAddForm() {
        document.getElementById('albumForm').style.display = 'block';
        document.getElementById('formTitle').textContent = '➕ Thêm Album Mới';
        document.getElementById('formAction').value = 'add';
        document.getElementById('albumId').value = '';
        document.getElementById('albumFormElement').reset();
        document.getElementById('name').focus();
    }
    
    function editAlbum(album) {
        document.getElementById('albumForm').style.display = 'block';
        document.getElementById('formTitle').textContent = '✏️ Sửa Album';
        document.getElementById('formAction').value = 'edit';
        document.getElementById('albumId').value = album.id;
        document.getElementById('name').value = album.name;
        document.getElementById('description').value = album.description || '';
        document.getElementById('display_order').value = album.display_order;
        document.getElementById('name').focus();
    }
    
    function hideForm() {
        document.getElementById('albumForm').style.display = 'none';
        document.getElementById('albumFormElement').reset();
    }
    
    // Ẩn form khi click outside
    document.addEventListener('click', function(event) {
        const form = document.getElementById('albumForm');
        const formContent = form.querySelector('.panel-header, form');
        
        if (form.style.display === 'block' && !formContent.contains(event.target)) {
            hideForm();
        }
    });
    </script>

    <style>
    .form-panel {
        background: white;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        margin-bottom: 30px;
        overflow: hidden;
    }
    
    .panel-header {
        background: #f8f9fa;
        padding: 20px;
        border-bottom: 1px solid #e9ecef;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .btn-close {
        background: #dc3545;
        color: white;
        border: none;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 16px;
    }
    
    .admin-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    
    .admin-table th,
    .admin-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #e9ecef;
    }
    
    .admin-table th {
        background: #f8f9fa;
        font-weight: 600;
        color: #2c3e50;
    }
    
    .admin-table tr:hover {
        background: #f8f9fa;
    }
    
    .admin-table tr.inactive {
        opacity: 0.6;
    }
    
    .badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .badge-success { background: #d4edda; color: #155724; }
    .badge-danger { background: #f8d7da; color: #721c24; }
    .badge-info { background: #d1ecf1; color: #0c5460; }
    
    .action-buttons {
        display: flex;
        gap: 5px;
    }
    
    .btn-edit, .btn-toggle, .btn-delete {
        padding: 5px 8px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
    }
    
    .btn-edit { background: #17a2b8; color: white; }
    .btn-toggle { background: #ffc107; color: #212529; }
    .btn-delete { background: #dc3545; color: white; }
    
    .table-responsive {
        overflow-x: auto;
    }
    
    @media (max-width: 768px) {
        .admin-table {
            font-size: 14px;
        }
        
        .admin-table th,
        .admin-table td {
            padding: 8px;
        }
        
        .action-buttons {
            flex-direction: column;
            gap: 2px;
        }
    }
    </style>
</body>
</html>