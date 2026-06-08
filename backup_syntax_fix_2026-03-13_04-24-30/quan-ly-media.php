<?php
session_start();
require_once 'config.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: dang-nhap.php');
    exit();
}

// Kết nối database
$conn = getDBConnection();

// Lấy danh sách media
$filter_type = isset($_GET['type']) ? $_GET['type'] : 'all';
$filter_category = isset($_GET['category']) ? $_GET['category'] : 'all';

$sql = "SELECT m.*, u.full_name as uploader_name 
        FROM media m 
        LEFT JOIN users u ON m.uploaded_by = u.id 
        WHERE m.status = 'active'";

if ($filter_type != 'all') {
    $sql .= " AND m.file_type = '" . $conn->real_escape_string($filter_type) . "'";
}

if ($filter_category != 'all') {
    $sql .= " AND m.category = '" . $conn->real_escape_string($filter_category) . "'";
}

$sql .= " ORDER BY m.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Media - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px 0;
        }
        
        .media-item {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .media-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .media-preview {
            width: 100%;
            height: 200px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .media-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .media-preview video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .media-preview .icon {
            font-size: 60px;
            color: #999;
        }
        
        .media-info {
            padding: 15px;
        }
        
        .media-title {
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 16px;
            color: #333;
        }
        
        .media-meta {
            font-size: 13px;
            color: #666;
            margin-bottom: 3px;
        }
        
        .media-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .filter-bar {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-bar select {
            padding: 10px 15px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .btn-upload {
            background: #28a745;
            color: white;
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
        }
        
        .btn-upload:hover {
            background: #218838;
        }
        
        .btn-view {
            background: #007bff;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <?php include 'menu-don-gian.php'; ?>

    <main>
        <section class="form-section">
            <div class="container">
                <div class="filter-bar">
                    <a href="upload-media.php" class="btn-upload">+ Upload Media</a>
                    
                    <select onchange="window.location.href='?type='+this.value+'&category=<?php echo $filter_category; ?>'">
                        <option value="all" <?php echo $filter_type == 'all' ? 'selected' : ''; ?>>Tất cả loại</option>
                        <option value="image" <?php echo $filter_type == 'image' ? 'selected' : ''; ?>>Hình ảnh</option>
                        <option value="video" <?php echo $filter_type == 'video' ? 'selected' : ''; ?>>Video</option>
                    </select>
                    
                    <select onchange="window.location.href='?type=<?php echo $filter_type; ?>&category='+this.value">
                        <option value="all" <?php echo $filter_category == 'all' ? 'selected' : ''; ?>>Tất cả danh mục</option>
                        <option value="tin-tuc" <?php echo $filter_category == 'tin-tuc' ? 'selected' : ''; ?>>Tin tức</option>
                        <option value="lanh-dao" <?php echo $filter_category == 'lanh-dao' ? 'selected' : ''; ?>>Lãnh đạo</option>
                        <option value="su-kien" <?php echo $filter_category == 'su-kien' ? 'selected' : ''; ?>>Sự kiện</option>
                        <option value="dang" <?php echo $filter_category == 'dang' ? 'selected' : ''; ?>>Đảng</option>
                        <option value="khac" <?php echo $filter_category == 'khac' ? 'selected' : ''; ?>>Khác</option>
                    </select>
                </div>

                <div class="media-grid">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($media = $result->fetch_assoc()): ?>
                            <div class="media-item">
                                <div class="media-preview">
                                    <?php if ($media['file_type'] == 'image'): ?>
                                        <img src="<?php echo htmlspecialchars($media['file_path']); ?>" 
                                             alt="<?php echo htmlspecialchars($media['alt_text'] ?? $media['title']); ?>">
                                    <?php elseif ($media['file_type'] == 'video'): ?>
                                        <video controls>
                                            <source src="<?php echo htmlspecialchars($media['file_path']); ?>" 
                                                    type="<?php echo htmlspecialchars($media['mime_type']); ?>">
                                        </video>
                                    <?php else: ?>
                                        <div class="icon">📄</div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="media-info">
                                    <div class="media-title"><?php echo htmlspecialchars($media['title'] ?? $media['file_name']); ?></div>
                                    <div class="media-meta">Loại: <?php echo $media['file_type']; ?></div>
                                    <div class="media-meta">Danh mục: <?php echo $media['category'] ?? 'Chưa phân loại'; ?></div>
                                    <div class="media-meta">Ngày tải: <?php echo date('d/m/Y', strtotime($media['created_at'])); ?></div>
                                    
                                    <div class="media-actions">
                                        <a href="<?php echo htmlspecialchars($media['file_path']); ?>" 
                                           target="_blank" class="btn-view">Xem</a>
                                        <a href="xoa-media.php?id=<?php echo $media['id']; ?>" 
                                           onclick="return confirm('Bạn có chắc muốn xóa media này?')" 
                                           class="btn-delete">Xóa</a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p style="grid-column: 1/-1; text-align: center; padding: 40px; color: #999;">
                            Chưa có media nào. <a href="upload-media.php">Upload ngay</a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <p class="copyright">&copy; 2026 UBND Xã Long Hiệp. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
