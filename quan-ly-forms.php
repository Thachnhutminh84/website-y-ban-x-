<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';
requireLogin();

if (!authCanManageContent()) {
    if (!authIsLoggedIn()) {
        header("Location: dang-nhap.php");
    } else {
        header("Location: index.php?error=no_permission");
    }
    exit();
}

$conn = getDBConnection();
$message = '';
$messageType = '';

// Xuly upload form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = $_POST['category'] ?? 'khac';
        $display_order = intval($_POST['display_order'] ?? 0);
        
        if (empty($title)) {
            $message = 'Vui lòng nhập tên tài liệu!';
            $messageType = 'error';
        } else {
            $filePath = '';
            $fileType = 'pdf';
            $fileSize = 0;
            
            if (isset($_FILES['form_file']) && $_FILES['form_file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/uploads/forms/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $originalName = basename($_FILES['form_file']['name']);
                $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                $allowedExts = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];
                
                if (!in_array($ext, $allowedExts)) {
                    $message = 'Định dạng file không được hỗ trợ. Chỉ chấp nhận: PDF, DOC, DOCX, XLS, XLSX';
                    $messageType = 'error';
                } elseif ($_FILES['form_file']['size'] > 10 * 1024 * 1024) {
                    $message = 'Kích thước file không được vượt quá 10MB!';
                    $messageType = 'error';
                } else {
                    $newName = 'form_' . time() . '_' . preg_replace('/[^a-zA-Z0-9]/', '', pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $ext;
                    
                    if (move_uploaded_file($_FILES['form_file']['tmp_name'], $uploadDir . $newName)) {
                        $filePath = 'uploads/forms/' . $newName;
                        $fileType = $ext;
                        $fileSize = $_FILES['form_file']['size'];
                    } else {
                        $message = 'Lỗi khi tải file lên. Vui lòng thử lại!';
                        $messageType = 'error';
                    }
                }
            }
            
            if ($messageType !== 'error') {
                $validCats = ['ho-tich', 'dat-dai', 'kinh-doanh', 'xa-hoi', 'giao-duc', 'phap-luat'];
                if (!in_array($category, $validCats)) $category = 'khac';
                
                $stmt = $conn->prepare("INSERT INTO forms (title, description, category, file_path, file_type, file_size, display_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssii", $title, $description, $category, $filePath, $fileType, $fileSize, $display_order);
                
                if ($stmt->execute()) {
                    $message = 'Thêm tài liệu thành công!';
                    $messageType = 'success';
                } else {
                    $message = 'Lỗi khi lưu dữ liệu: ' . $conn->error;
                    $messageType = 'error';
                }
                $stmt->close();
            }
        }
    }
    
    if ($action === 'delete' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("SELECT file_path FROM forms WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if (!empty($row['file_path']) && file_exists($row['file_path'])) {
                unlink($row['file_path']);
            }
        }
        $stmt->close();
        
        $stmt = $conn->prepare("DELETE FROM forms WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = 'Xóa tài liệu thành công!';
            $messageType = 'success';
        } else {
            $message = 'Lỗi khi xóa tài liệu!';
            $messageType = 'error';
        }
        $stmt->close();
    }
    
    if ($action === 'toggle' && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $stmt = $conn->prepare("UPDATE forms SET is_active = NOT is_active WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = 'Cập nhật trạng thái thành công!';
            $messageType = 'success';
        } else {
            $message = 'Lỗi cập nhật!';
            $messageType = 'error';
        }
        $stmt->close();
    }
}

// Lay danh sach forms
$formsList = [];
$result = $conn->query("SELECT * FROM forms ORDER BY display_order ASC, id DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $formsList[] = $row;
    }
}
$conn->close();

$categoryLabels = ['ho-tich' => 'Hộ tịch', 'dat-dai' => 'Đất đai', 'kinh-doanh' => 'Kinh doanh', 'xa-hoi' => 'Xã hội', 'giao-duc' => 'Giáo dục', 'phap-luat' => 'Pháp luật'];
$formatBytes = function($bytes) {
    if (!$bytes || $bytes === 0) return '0 B';
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1024 * 1024) return round($bytes / 1024, 1) . ' KB';
    return round($bytes / (1024 * 1024), 1) . ' MB';
};
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý tài liệu - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background: var(--bg-light, #f4f7f5); }

        .admin-page {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px 60px;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .admin-header h1 {
            font-size: 1.5rem;
            color: var(--primary, #1a4d2e);
        }

        .admin-header h1 i {
            margin-right: 10px;
            color: var(--accent, #c9a227);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-primary {
            background: var(--primary, #1a4d2e);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark, #0e2e1c);
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-warning {
            background: #ffc107;
            color: #333;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
        }

        .message-box {
            padding: 14px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .message-box.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message-box.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .stats-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border: 1px solid var(--border, #d9e2dc);
        }

        .stat-card .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary, #1a4d2e);
        }

        .stat-card .stat-label {
            font-size: 0.85rem;
            color: var(--text-medium, #555);
            margin-top: 4px;
        }

        .table-wrapper {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            overflow: hidden;
            border: 1px solid var(--border, #d9e2dc);
        }

        .table-wrapper table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-wrapper th {
            background: var(--primary, #1a4d2e);
            color: white;
            padding: 14px 16px;
            text-align: left;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .table-wrapper td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border, #eee);
            font-size: 0.88rem;
            vertical-align: middle;
        }

        .table-wrapper tr:last-child td {
            border-bottom: none;
        }

        .table-wrapper tr:hover td {
            background: #f8fdf9;
        }

        .table-title {
            font-weight: 600;
            color: var(--text-dark);
        }

        .badge-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-active {
            background: #d4edda;
            color: #155724;
        }

        .badge-inactive {
            background: #f8d7da;
            color: #721c24;
        }

        .actions-cell {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal {
            background: white;
            border-radius: 16px;
            width: 100%;
            max-width: 560px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .modal-header {
            background: var(--gradient-primary, linear-gradient(135deg, #1a4d2e, #2d7a4a));
            color: white;
            padding: 20px 24px;
            border-radius: 16px 16px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            font-size: 1.15rem;
            margin: 0;
        }

        .modal-close {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-body {
            padding: 24px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 6px;
        }

        .form-group label .required {
            color: #dc3545;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--border, #d9e2dc);
            border-radius: 8px;
            font-size: 0.9rem;
            transition: border-color 0.2s;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary, #1a4d2e);
            box-shadow: 0 0 0 3px rgba(26,77,46,0.1);
        }

        .form-group textarea {
            min-height: 80px;
            resize: vertical;
        }

        .form-group .hint {
            font-size: 0.78rem;
            color: var(--text-light);
            margin-top: 4px;
        }

        .file-upload-area {
            border: 2px dashed var(--border, #d9e2dc);
            border-radius: 10px;
            padding: 24px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }

        .file-upload-area:hover {
            border-color: var(--primary, #1a4d2e);
            background: #f0f8f3;
        }

        .file-upload-area input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
        }

        .file-upload-area i {
            font-size: 2rem;
            color: var(--primary, #1a4d2e);
            opacity: 0.5;
            margin-bottom: 8px;
            display: block;
        }

        .file-upload-area p {
            font-size: 0.85rem;
            color: var(--text-medium);
            margin: 0;
        }

        .file-upload-area .file-name {
            font-size: 0.85rem;
            color: var(--primary);
            font-weight: 600;
            margin-top: 8px;
        }

        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--border, #eee);
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        @media (max-width: 768px) {
            .admin-header { flex-direction: column; align-items: flex-start; }
            .table-wrapper { overflow-x: auto; }
            table { min-width: 700px; }
            .stats-bar { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
    <?php include 'header-menu.php'; ?>

    <div class="admin-page">
        <div class="admin-header">
            <h1><i class="fas fa-file-alt"></i> Quản lý tài liệu, biểu mẫu</h1>
            <div style="display:flex;gap:10px;">
                <a href="tai-lieu-forms.php" class="btn btn-primary" style="background:var(--accent);color:#1a1a1a;"><i class="fas fa-eye"></i> Xem trang công khai</a>
                <button class="btn btn-primary" onclick="openModal()"><i class="fas fa-plus"></i> Thêm mới</button>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="message-box <?php echo $messageType; ?>">
                <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php
        $totalForms = count($formsList);
        $totalActive = 0;
        $totalDownloads = 0;
        foreach ($formsList as $f) {
            if ($f['is_active']) $totalActive++;
            $totalDownloads += $f['download_count'];
        }
        ?>
        <div class="stats-bar">
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalForms; ?></div>
                <div class="stat-label">Tổng tài liệu</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color:#28a745;"><?php echo $totalActive; ?></div>
                <div class="stat-label">Đang hiển thị</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color:#dc3545;"><?php echo $totalForms - $totalActive; ?></div>
                <div class="stat-label">Đang ẩn</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color:var(--accent);"><?php echo $totalDownloads; ?></div>
                <div class="stat-label">Tổng lượt tải</div>
            </div>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th style="width:40px;">#</th>
                        <th>Tên tài liệu</th>
                        <th>Danh mục</th>
                        <th>Định dạng</th>
                        <th>Kích thước</th>
                        <th>Lượt tải</th>
                        <th>Trạng thái</th>
                        <th style="width:180px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($formsList)): ?>
                        <tr>
                            <td colspan="8" style="text-align:center;padding:40px;color:var(--text-light);">
                                <i class="fas fa-folder-open" style="font-size:2rem;display:block;margin-bottom:10px;opacity:0.3;"></i>
                                Chưa có tài liệu nào
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($formsList as $i => $f): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td>
                                    <div class="table-title"><?php echo htmlspecialchars($f['title']); ?></div>
                                    <?php if ($f['description']): ?>
                                        <div style="font-size:0.78rem;color:var(--text-light);margin-top:2px;max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars($f['description']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $categoryLabels[$f['category']] ?? 'Khác'; ?></td>
                                <td><span class="badge-status badge-active"><?php echo strtoupper($f['file_type']); ?></span></td>
                                <td><?php echo $formatBytes($f['file_size']); ?></td>
                                <td><?php echo $f['download_count']; ?></td>
                                <td>
                                    <span class="badge-status <?php echo $f['is_active'] ? 'badge-active' : 'badge-inactive'; ?>">
                                        <?php echo $f['is_active'] ? 'Hiển thị' : 'Ẩn'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="actions-cell">
                                        <a href="tai-lieu-preview.php?id=<?php echo $f['id']; ?>" class="btn btn-sm btn-warning" target="_blank"><i class="fas fa-eye"></i></a>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="toggle">
                                            <input type="hidden" name="id" value="<?php echo $f['id']; ?>">
                                            <button type="submit" class="btn btn-sm <?php echo $f['is_active'] ? 'btn-warning' : 'btn-success'; ?>">
                                                <i class="fas <?php echo $f['is_active'] ? 'fa-eye-slash' : 'fa-eye'; ?>"></i>
                                            </button>
                                        </form>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc muốn xóa tài liệu này?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $f['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal them moi -->
    <div class="modal-overlay" id="addModal">
        <div class="modal">
            <div class="modal-header">
                <h2><i class="fas fa-plus-circle"></i> Thêm tài liệu mới</h2>
                <button class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="addForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="form-group">
                        <label>Tên tài liệu <span class="required">*</span></label>
                        <input type="text" name="title" required placeholder="VD: Đơn đăng ký kết hôn">
                    </div>

                    <div class="form-group">
                        <label>Mô tả</label>
                        <textarea name="description" placeholder="Mô tả ngắn gọn về nội dung tài liệu..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>Danh mục <span class="required">*</span></label>
                        <select name="category" required>
                            <option value="ho-tich">Hộ tịch</option>
                            <option value="dat-dai">Đất đai</option>
                            <option value="kinh-doanh">Kinh doanh</option>
                            <option value="xa-hoi">Xã hội</option>
                            <option value="giao-duc">Giáo dục</option>
                            <option value="phap-luat">Pháp luật</option>
                            <option value="khac">Khác</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>File tài liệu (PDF, DOC, DOCX, XLS, XLSX - tối đa 10MB)</label>
                        <div class="file-upload-area" id="fileUploadArea">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Nhấn hoặc kéo thả file vào đây</p>
                            <div class="file-name" id="fileName"></div>
                            <input type="file" name="form_file" accept=".pdf,.doc,.docx,.xls,.xlsx" onchange="showFileName(this)">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Thứ tự hiển thị</label>
                        <input type="number" name="display_order" value="0" min="0">
                        <div class="hint">Số càng nhỏ hiển thị càng trước</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" style="background:#f0f0f0;color:#666;" onclick="closeModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu tài liệu</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openModal() {
        document.getElementById('addModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        document.getElementById('addModal').classList.remove('active');
        document.body.style.overflow = '';
    }

    function showFileName(input) {
        var nameEl = document.getElementById('fileName');
        if (input.files && input.files[0]) {
            nameEl.textContent = '\uD83D\uDCCE ' + input.files[0].name;
        } else {
            nameEl.textContent = '';
        }
    }

    document.getElementById('addModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>