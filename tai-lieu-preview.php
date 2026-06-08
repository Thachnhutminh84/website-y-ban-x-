<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: tai-lieu-forms.php");
    exit();
}

$id = intval($_GET['id']);
$conn = getDBConnection();

if (!$conn) {
    header("Location: tai-lieu-forms.php");
    exit();
}

$stmt = $conn->prepare("SELECT id, title, description, category, file_type, file_path, file_size, download_count FROM forms WHERE id = ? AND is_active = 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    header("Location: tai-lieu-forms.php?error=notfound");
    exit();
}

$form = $result->fetch_assoc();
$stmt->close();
$conn->close();

$categoryLabels = ['ho-tich' => 'Hộ tịch', 'dat-dai' => 'Đất đai', 'kinh-doanh' => 'Kinh doanh', 'xa-hoi' => 'Xã hội', 'giao-duc' => 'Giáo dục', 'phap-luat' => 'Pháp luật'];
$catLabel = $categoryLabels[$form['category']] ?? 'Khác';

$hasFile = !empty($form['file_path']) && file_exists($form['file_path']);
$isImage = in_array(strtolower($form['file_type']), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
$isPdf = strtolower($form['file_type']) === 'pdf';
$isOffice = in_array(strtolower($form['file_type']), ['doc', 'docx', 'xls', 'xlsx']);

$formatBytes = function($bytes) {
    if (!$bytes || $bytes === 0) return 'Không xác định';
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1024 * 1024) return round($bytes / 1024, 1) . ' KB';
    return round($bytes / (1024 * 1024), 1) . ' MB';
};
$fileSize = $formatBytes($form['file_size']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($form['title']); ?> - Xem trước - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background: #f0f2f5; margin: 0; }

        .preview-page {
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px 60px;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            color: var(--text-light);
            margin-bottom: 20px;
        }

        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .preview-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .preview-header {
            background: var(--gradient-header, linear-gradient(135deg, #1a4d2e 0%, #0e2e1c 100%));
            color: white;
            padding: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .preview-header-info h1 {
            font-size: 1.4rem;
            margin: 0 0 6px;
        }

        .preview-header-info .meta {
            display: flex;
            gap: 16px;
            font-size: 0.85rem;
            opacity: 0.85;
            flex-wrap: wrap;
        }

        .preview-header-info .meta span {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .preview-actions {
            display: flex;
            gap: 10px;
            flex-shrink: 0;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-download {
            background: var(--accent, #c9a227);
            color: #1a1a1a;
        }

        .btn-download:hover {
            background: #b8911f;
            transform: translateY(-1px);
        }

        .btn-print {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
        }

        .btn-print:hover {
            background: rgba(255,255,255,0.3);
        }

        .btn-back {
            background: rgba(255,255,255,0.15);
            color: white;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .btn-back:hover {
            background: rgba(255,255,255,0.25);
        }

        .preview-body {
            padding: 30px;
        }

        .preview-viewer {
            min-height: 500px;
            background: #e5e7eb;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .preview-viewer img {
            max-width: 100%;
            max-height: 80vh;
            object-fit: contain;
        }

        .preview-viewer iframe {
            width: 100%;
            height: 70vh;
            border: none;
            border-radius: 12px;
        }

        .preview-placeholder {
            text-align: center;
            padding: 60px 30px;
            color: #999;
        }

        .preview-placeholder i {
            font-size: 72px;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .preview-placeholder h3 {
            font-size: 1.2rem;
            margin-bottom: 8px;
            color: #666;
        }

        .preview-placeholder p {
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .preview-info {
            margin-top: 24px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
            border: 1px solid #e9ecef;
        }

        .preview-info h3 {
            font-size: 1rem;
            color: var(--text-dark);
            margin-bottom: 12px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            background: white;
            border-radius: 8px;
        }

        .info-item i {
            color: var(--primary);
            width: 20px;
            text-align: center;
        }

        .info-item .label {
            font-size: 0.78rem;
            color: var(--text-light);
        }

        .info-item .value {
            font-size: 0.9rem;
            color: var(--text-dark);
            font-weight: 600;
        }

        @media (max-width: 640px) {
            .preview-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .preview-actions {
                width: 100%;
                flex-wrap: wrap;
            }

            .btn {
                flex: 1;
                justify-content: center;
                min-width: 120px;
            }

            .preview-body {
                padding: 16px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'header-menu.php'; ?>

    <div class="preview-page">
        <div class="breadcrumb">
            <a href="index.php"><i class="fas fa-home"></i> Trang chủ</a>
            <i class="fas fa-chevron-right"></i>
            <a href="tai-lieu-forms.php">Tài liệu, biểu mẫu</a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo htmlspecialchars($form['title']); ?></span>
        </div>

        <div class="preview-card">
            <div class="preview-header">
                <div class="preview-header-info">
                    <h1><?php echo htmlspecialchars($form['title']); ?></h1>
                    <div class="meta">
                        <span><i class="fas fa-folder"></i> <?php echo $catLabel; ?></span>
                        <span><i class="fas fa-file"></i> <?php echo strtoupper($form['file_type']); ?></span>
                        <span><i class="fas fa-weight-hanging"></i> <?php echo $fileSize; ?></span>
                        <span><i class="fas fa-download"></i> <?php echo $form['download_count']; ?> lượt tải</span>
                    </div>
                </div>
                <div class="preview-actions">
                    <a href="tai-lieu-forms.php" class="btn btn-back"><i class="fas fa-arrow-left"></i> Quay lại</a>
                    <?php if ($hasFile): ?>
                        <a href="tai-lieu-download.php?id=<?php echo $form['id']; ?>" class="btn btn-download"><i class="fas fa-download"></i> Tải về</a>
                    <?php else: ?>
                        <a href="#" class="btn btn-download" onclick="alert('File chưa được tải lên hệ thống. Vui lòng liên hệ UBND xã Long Hiệp.'); return false;"><i class="fas fa-phone"></i> Liên hệ nhận file</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="preview-body">
                <?php if ($hasFile): ?>
                    <?php if ($isPdf): ?>
                        <div class="preview-viewer">
                            <iframe src="uploads/forms/<?php echo basename($form['file_path']); ?>" title="Xem trước tài liệu"></iframe>
                        </div>
                    <?php elseif ($isImage): ?>
                        <div class="preview-viewer">
                            <img src="uploads/forms/<?php echo basename($form['file_path']); ?>" alt="<?php echo htmlspecialchars($form['title']); ?>">
                        </div>
                    <?php else: ?>
                        <div class="preview-viewer">
                            <div class="preview-placeholder">
                                <i class="fas fa-file-word"></i>
                                <h3>Không thể xem trước file <?php echo strtoupper($form['file_type']); ?></h3>
                                <p>Vui lòng tải file về máy để xem nội dung.<br>Hỗ trợ xem trước: PDF, JPG, PNG</p>
                                <a href="tai-lieu-download.php?id=<?php echo $form['id']; ?>" class="btn btn-download" style="margin-top: 20px; color: #1a1a1a;">
                                    <i class="fas fa-download"></i> Tải file về
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="preview-viewer">
                        <div class="preview-placeholder">
                            <i class="fas fa-file-alt"></i>
                            <h3>File chưa được tải lên hệ thống</h3>
                            <p>Biểu mẫu này đang được cập nhật. Vui lòng liên hệ bộ phận tiếp nhận<br>để nhận bản cứng tại trụ sở UBND xã Long Hiệp.</p>
                            <a href="lien-he.php" class="btn btn-download" style="margin-top: 20px; color: #1a1a1a;">
                                <i class="fas fa-phone"></i> Liên hệ nhận file
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="preview-info">
                    <h3><i class="fas fa-info-circle"></i> Thông tin tài liệu</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <i class="fas fa-file-alt"></i>
                            <div>
                                <div class="label">Tên tài liệu</div>
                                <div class="value"><?php echo htmlspecialchars($form['title']); ?></div>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-folder"></i>
                            <div>
                                <div class="label">Danh mục</div>
                                <div class="value"><?php echo $catLabel; ?></div>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-file"></i>
                            <div>
                                <div class="label">Định dạng</div>
                                <div class="value"><?php echo strtoupper($form['file_type']); ?></div>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-weight-hanging"></i>
                            <div>
                                <div class="label">Kích thước</div>
                                <div class="value"><?php echo $fileSize; ?></div>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-download"></i>
                            <div>
                                <div class="label">Lượt tải</div>
                                <div class="value"><?php echo $form['download_count']; ?></div>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-info-circle"></i>
                            <div>
                                <div class="label">Trạng thái</div>
                                <div class="value"><?php echo $hasFile ? '✅ Có thể tải' : '⏳ Đang cập nhật'; ?></div>
                            </div>
                        </div>
                    </div>
                    <?php if ($form['description']): ?>
                        <div style="margin-top: 16px; padding: 14px; background: white; border-radius: 8px; border-left: 3px solid var(--primary);">
                            <div style="font-size: 0.78rem; color: var(--text-light); margin-bottom: 4px;">Mô tả</div>
                            <div style="font-size: 0.9rem; color: var(--text-dark); line-height: 1.6;"><?php echo nl2br(htmlspecialchars($form['description'])); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>