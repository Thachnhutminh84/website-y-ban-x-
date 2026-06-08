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
    header("Location: tai-lieu-forms.php?error=db");
    exit();
}

$stmt = $conn->prepare("SELECT file_path, title, file_type FROM forms WHERE id = ? AND is_active = 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    header("Location: tai-lieu-forms.php?error=notfound");
    exit();
}

$row = $result->fetch_assoc();
$stmt->close();

// Tang download count
$UpdateStmt = $conn->prepare("UPDATE forms SET download_count = download_count + 1 WHERE id = ?");
$UpdateStmt->bind_param("i", $id);
$UpdateStmt->execute();
$UpdateStmt->close();
$conn->close();

// Neu file ton tai, download
if (!empty($row['file_path']) && file_exists($row['file_path'])) {
    $fileName = $row['title'] . '.' . $row['file_type'];
    
    $mimeTypes = [
        'pdf'  => 'application/pdf',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls'  => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];
    
    $mime = $mimeTypes[$row['file_type']] ?? 'application/octet-stream';
    
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Length: ' . filesize($row['file_path']));
    header('Cache-Control: no-cache, must-revalidate');
    
    readfile($row['file_path']);
    exit();
}

// File chua upload - hien trang huong dan
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($row['title']); ?> - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .download-page { max-width: 700px; margin: 60px auto; padding: 0 20px; }
        .download-card { background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); overflow: hidden; }
        .download-header { background: var(--gradient-primary); color: white; padding: 40px 30px; text-align: center; }
        .download-header i { font-size: 64px; margin-bottom: 15px; opacity: 0.9; }
        .download-header h1 { font-size: 24px; margin: 0 0 8px; }
        .download-header p { opacity: 0.9; margin: 0; }
        .download-body { padding: 30px; text-align: center; }
        .download-body p { color: #666; line-height: 1.8; margin-bottom: 20px; }
        .file-badge { display: inline-block; padding: 8px 20px; background: #f0f0f0; border-radius: 8px; font-weight: 600; color: #555; margin-bottom: 20px; }
        .download-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
        .btn-download { display: inline-flex; align-items: center; gap: 8px; padding: 14px 28px; background: var(--primary); color: white; text-decoration: none; border-radius: 10px; font-weight: 600; transition: all 0.3s; }
        .btn-download:hover { background: var(--primary-dark); transform: translateY(-2px); }
        .btn-back { display: inline-flex; align-items: center; gap: 8px; padding: 14px 28px; background: #f0f0f0; color: #555; text-decoration: none; border-radius: 10px; font-weight: 600; transition: all 0.3s; }
        .btn-back:hover { background: #e0e0e0; }
    </style>
</head>
<body>
    <?php include 'header-menu.php'; ?>
    <div class="download-page">
        <div class="download-card">
            <div class="download-header">
                <i class="fas fa-file-alt"></i>
                <h1><?php echo htmlspecialchars($row['title']); ?></h1>
                <p>Biểu mẫu hành chính - UBND Xã Long Hiệp</p>
            </div>
            <div class="download-body">
                <div class="file-badge"><i class="fas fa-file-<?php echo $row['file_type'] === 'pdf' ? 'pdf' : 'word'; ?>"></i> <?php echo strtoupper($row['file_type']); ?></div>
                <p>File biểu mẫu này đang được cập nhật lên hệ thống.<br>Vui lòng liên hệ Bộ phận Tiếp nhận và Trả kết quả để nhận bản cứng.</p>
                <div class="download-actions">
                    <a href="tai-lieu-forms.php" class="btn-back"><i class="fas fa-arrow-left"></i> Quay lại</a>
                    <a href="lien-he.php" class="btn-download"><i class="fas fa-phone"></i> Liên hệ nhận file</a>
                </div>
            </div>
        </div>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>