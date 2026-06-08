<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'auth.php';

require_once 'config.php';
require_once 'thu-tuc-bieu-mau-helper.php';

authRequireRole(['admin']);

$currentRole = authCurrentRole();
$displayName = authDisplayName();

$flashMessage = $_SESSION['procedure_admin_message'] ?? null;
$flashType = $_SESSION['procedure_admin_type'] ?? 'success';
unset($_SESSION['procedure_admin_message'], $_SESSION['procedure_admin_type']);

$categories = getProcedureCategoryOptions();
$statusOptions = [
    'all' => 'Tất cả trạng thái',
    'active' => 'Đang áp dụng',
    'inactive' => 'Tạm ẩn'
];

$keyword = trim((string) ($_GET['keyword'] ?? ''));
$filterCategory = trim((string) ($_GET['category'] ?? 'all'));
$filterStatus = trim((string) ($_GET['status'] ?? 'all'));
$editId = max(0, (int) ($_GET['edit'] ?? 0));

if (!array_key_exists($filterCategory, $categories)) {
    $filterCategory = 'all';
}

if (!array_key_exists($filterStatus, $statusOptions)) {
    $filterStatus = 'all';
}

$formData = [
    'id' => 0,
    'code' => '',
    'title' => '',
    'category' => 'khac',
    'summary' => '',
    'required_documents' => '',
    'process_steps' => '',
    'processing_time' => '',
    'fee' => '',
    'form_url' => '',
    'form_label' => '',
    'secondary_form_url' => '',
    'secondary_form_label' => '',
    'form_note' => '',
    'official_source_url' => '',
    'contact_point' => '',
    'is_featured' => 0,
    'display_order' => 0,
    'status' => 'active'
];

$pageError = null;
$procedures = [];
$conn = getProcedureStorageConnection();

if (!$conn) {
    $pageError = 'Không thể kết nối cơ sở dữ liệu.';
} elseif (!ensureProceduresTableExists($conn)) {
    $pageError = 'Không thể chuẩn bị bảng dữ liệu thủ tục.';
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = trim((string) ($_POST['action'] ?? ''));

        if ($action === 'save') {
            $procedureId = max(0, (int) ($_POST['procedure_id'] ?? 0));
            $code = strtoupper(trim((string) ($_POST['code'] ?? '')));
            $title = trim((string) ($_POST['title'] ?? ''));
            $category = trim((string) ($_POST['category'] ?? 'khac'));
            $summary = trim((string) ($_POST['summary'] ?? ''));
            $requiredDocuments = trim((string) ($_POST['required_documents'] ?? ''));
            $processSteps = trim((string) ($_POST['process_steps'] ?? ''));
            $processingTime = trim((string) ($_POST['processing_time'] ?? ''));
            $fee = trim((string) ($_POST['fee'] ?? ''));
            $formUrl = trim((string) ($_POST['form_url'] ?? ''));
            $formLabel = trim((string) ($_POST['form_label'] ?? ''));
            $secondaryFormUrl = trim((string) ($_POST['secondary_form_url'] ?? ''));
            $secondaryFormLabel = trim((string) ($_POST['secondary_form_label'] ?? ''));
            $formNote = trim((string) ($_POST['form_note'] ?? ''));
            $officialSourceUrl = trim((string) ($_POST['official_source_url'] ?? ''));
            $contactPoint = trim((string) ($_POST['contact_point'] ?? ''));
            $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
            $displayOrder = max(0, (int) ($_POST['display_order'] ?? 0));
            $status = normalizeProcedureStatus($_POST['status'] ?? 'active');

            if ($code === '' || $title === '' || $summary === '') {
                $_SESSION['procedure_admin_message'] = 'Mã thủ tục, tên thủ tục và mô tả ngắn là bắt buộc.';
                $_SESSION['procedure_admin_type'] = 'error';
            } else {
                $category = $category === 'all' ? 'khac' : normalizeProcedureCategory($category);

                if ($procedureId > 0) {
                    $stmtSave = $conn->prepare(
                        "UPDATE administrative_procedures
                         SET code = ?, title = ?, category = ?, summary = ?, required_documents = ?,
                             process_steps = ?, processing_time = ?, fee = ?, form_url = ?, form_label = ?,
                             secondary_form_url = ?, secondary_form_label = ?, form_note = ?, official_source_url = ?,
                             contact_point = ?, is_featured = ?, display_order = ?, status = ?
                         WHERE id = ?"
                    );

                    if ($stmtSave) {
                        $stmtSave->bind_param(
                            'sssssssssssssssiisi',
                            $code,
                            $title,
                            $category,
                            $summary,
                            $requiredDocuments,
                            $processSteps,
                            $processingTime,
                            $fee,
                            $formUrl,
                            $formLabel,
                            $secondaryFormUrl,
                            $secondaryFormLabel,
                            $formNote,
                            $officialSourceUrl,
                            $contactPoint,
                            $isFeatured,
                            $displayOrder,
                            $status,
                            $procedureId
                        );
                        $saved = $stmtSave->execute();
                        $stmtSave->close();
                    } else {
                        $saved = false;
                    }
                } else {
                    $stmtSave = $conn->prepare(
                        "INSERT INTO administrative_procedures
                            (code, title, category, summary, required_documents, process_steps,
                             processing_time, fee, form_url, form_label, secondary_form_url, secondary_form_label,
                             form_note, official_source_url, contact_point, is_featured, display_order, status)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                    );

                    if ($stmtSave) {
                        $stmtSave->bind_param(
                            'sssssssssssssssiis',
                            $code,
                            $title,
                            $category,
                            $summary,
                            $requiredDocuments,
                            $processSteps,
                            $processingTime,
                            $fee,
                            $formUrl,
                            $formLabel,
                            $secondaryFormUrl,
                            $secondaryFormLabel,
                            $formNote,
                            $officialSourceUrl,
                            $contactPoint,
                            $isFeatured,
                            $displayOrder,
                            $status
                        );
                        $saved = $stmtSave->execute();
                        $stmtSave->close();
                    } else {
                        $saved = false;
                    }
                }

                if ($saved) {
                    $_SESSION['procedure_admin_message'] = $procedureId > 0 ? 'Đã cập nhật thủ tục.' : 'Đã thêm thủ tục mới.';
                    $_SESSION['procedure_admin_type'] = 'success';
                } else {
                    $_SESSION['procedure_admin_message'] = 'Không thể lưu thủ tục. Kiểm tra lại mã thủ tục có bị trùng không.';
                    $_SESSION['procedure_admin_type'] = 'error';
                }
            }

            header('Location: quan-ly-thu-tuc.php');
            exit();
        }

        if ($action === 'delete') {
            $procedureId = max(0, (int) ($_POST['procedure_id'] ?? 0));
            $stmtDelete = $conn->prepare('DELETE FROM administrative_procedures WHERE id = ?');

            if ($procedureId > 0 && $stmtDelete) {
                $stmtDelete->bind_param('i', $procedureId);
                $deleted = $stmtDelete->execute();
                $stmtDelete->close();
            } else {
                $deleted = false;
            }

            $_SESSION['procedure_admin_message'] = $deleted ? 'Đã xóa thủ tục.' : 'Không thể xóa thủ tục đã chọn.';
            $_SESSION['procedure_admin_type'] = $deleted ? 'success' : 'error';
            header('Location: quan-ly-thu-tuc.php');
            exit();
        }
    }

    if ($editId > 0) {
        $stmtEdit = $conn->prepare('SELECT * FROM administrative_procedures WHERE id = ? LIMIT 1');
        if ($stmtEdit) {
            $stmtEdit->bind_param('i', $editId);
            if ($stmtEdit->execute()) {
                $resultEdit = $stmtEdit->get_result();
                $editRow = $resultEdit->fetch_assoc();
                if ($editRow) {
                    $formData = [
                        'id' => (int) $editRow['id'],
                        'code' => $editRow['code'],
                        'title' => $editRow['title'],
                        'category' => $editRow['category'],
                        'summary' => $editRow['summary'],
                        'required_documents' => $editRow['required_documents'],
                        'process_steps' => $editRow['process_steps'],
                        'processing_time' => $editRow['processing_time'],
                        'fee' => $editRow['fee'],
                        'form_url' => $editRow['form_url'],
                        'form_label' => $editRow['form_label'] ?? '',
                        'secondary_form_url' => $editRow['secondary_form_url'] ?? '',
                        'secondary_form_label' => $editRow['secondary_form_label'] ?? '',
                        'form_note' => $editRow['form_note'] ?? '',
                        'official_source_url' => $editRow['official_source_url'] ?? '',
                        'contact_point' => $editRow['contact_point'],
                        'is_featured' => (int) $editRow['is_featured'],
                        'display_order' => (int) $editRow['display_order'],
                        'status' => normalizeProcedureStatus($editRow['status'])
                    ];
                }
            }
            $stmtEdit->close();
        }
    }

    $conditions = ['1 = 1'];
    $params = [];
    $types = '';

    if ($filterCategory !== 'all') {
        $conditions[] = 'category = ?';
        $params[] = $filterCategory;
        $types .= 's';
    }

    if ($filterStatus !== 'all') {
        $conditions[] = 'status = ?';
        $params[] = $filterStatus;
        $types .= 's';
    }

    if ($keyword !== '') {
        $searchValue = '%' . $keyword . '%';
        $conditions[] = '(title LIKE ? OR code LIKE ? OR summary LIKE ?)';
        $params[] = $searchValue;
        $params[] = $searchValue;
        $params[] = $searchValue;
        $types .= 'sss';
    }

    $sqlList = "SELECT id, code, title, category, summary, processing_time, fee, contact_point, is_featured, status, updated_at
                FROM administrative_procedures
                WHERE " . implode(' AND ', $conditions) . "
                ORDER BY is_featured DESC, display_order ASC, updated_at DESC, id DESC";
    $stmtList = $conn->prepare($sqlList);

    if ($stmtList && procedureBindParams($stmtList, $types, $params) && $stmtList->execute()) {
        $resultList = $stmtList->get_result();
        while ($row = $resultList->fetch_assoc()) {
            $procedures[] = $row;
        }
        $stmtList->close();
    } else {
        $pageError = 'Không thể tải danh sách thủ tục.';
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý thủ tục hành chính - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css?v=2.1">
    <link rel="stylesheet" href="citizen-services.css?v=1.1">
    <script src="dropdown.js"></script>
</head>
<body>
        <?php include 'menu-don-gian.php'; ?>

    <main>
        <section class="page-header">
            <div class="container">
                <h2>Quản lý kho thủ tục</h2>
                <p>Thêm mới, cập nhật, ẩn hoặc xóa thủ tục và biểu mẫu công khai.</p>
            </div>
        </section>

        <section class="form-section">
            <div class="container">
                <?php if ($flashMessage): ?>
                    <div class="flash-panel <?php echo $flashType === 'error' ? 'is-error' : 'is-success'; ?>">
                        <?php echo htmlspecialchars($flashMessage, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <?php if ($pageError): ?>
                    <div class="flash-panel is-error">
                        <?php echo htmlspecialchars($pageError, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <div class="admin-surface">
                    <form method="GET" class="admin-filter-form">
                        <div class="admin-filter-form__row">
                            <div class="field-span-6">
                                <label for="keyword">Tìm kiếm</label>
                                <input type="text" id="keyword" name="keyword" value="<?php echo htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Tên thủ tục, mã hồ sơ...">
                            </div>
                            <div class="field-span-3">
                                <label for="category">Lĩnh vực</label>
                                <select id="category" name="category">
                                    <?php foreach ($categories as $slug => $label): ?>
                                        <option value="<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $filterCategory === $slug ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="field-span-3">
                                <label for="status">Trạng thái</label>
                                <select id="status" name="status">
                                    <?php foreach ($statusOptions as $slug => $label): ?>
                                        <option value="<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $filterStatus === $slug ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="admin-filter-actions">
                            <button type="submit" class="admin-button">Lọc danh sách</button>
                            <a href="quan-ly-thu-tuc.php" class="ghost-button">Đặt lại</a>
                            <a href="thu-tuc-hanh-chinh.php" class="secondary-button">Xem trang công khai</a>
                        </div>
                    </form>
                </div>

                <div class="admin-surface-grid">
                    <div class="admin-surface">
                        <h3><?php echo $formData['id'] > 0 ? 'Cập nhật thủ tục' : 'Thêm thủ tục mới'; ?></h3>
                        <p>Nhập thông tin cốt lõi để người dân tra cứu hồ sơ, thời hạn và biểu mẫu.</p>

                        <form method="POST" class="stack-form">
                            <input type="hidden" name="action" value="save">
                            <input type="hidden" name="procedure_id" value="<?php echo (int) $formData['id']; ?>">

                            <div class="admin-form-grid">
                                <div class="field-span-4">
                                    <label for="code">Mã thủ tục</label>
                                    <input type="text" id="code" name="code" required value="<?php echo htmlspecialchars($formData['code'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="HC-004">
                                </div>
                                <div class="field-span-8">
                                    <label for="title">Tên thủ tục</label>
                                    <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($formData['title'], ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                                <div class="field-span-4">
                                    <label for="form-category">Lĩnh vực</label>
                                    <select id="form-category" name="category">
                                        <?php foreach ($categories as $slug => $label): ?>
                                            <?php if ($slug !== 'all'): ?>
                                                <option value="<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $formData['category'] === $slug ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="field-span-4">
                                    <label for="display_order">Thứ tự hiển thị</label>
                                    <input type="number" id="display_order" name="display_order" min="0" value="<?php echo (int) $formData['display_order']; ?>">
                                </div>
                                <div class="field-span-4">
                                    <label for="form-status">Trạng thái</label>
                                    <select id="form-status" name="status">
                                        <option value="active" <?php echo $formData['status'] === 'active' ? 'selected' : ''; ?>>Đang áp dụng</option>
                                        <option value="inactive" <?php echo $formData['status'] === 'inactive' ? 'selected' : ''; ?>>Tạm ẩn</option>
                                    </select>
                                </div>
                                <div class="field-span-12">
                                    <label for="summary">Mô tả ngắn</label>
                                    <textarea id="summary" name="summary" required><?php echo htmlspecialchars($formData['summary'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                                <div class="field-span-12">
                                    <label for="required_documents">Thành phần hồ sơ</label>
                                    <textarea id="required_documents" name="required_documents"><?php echo htmlspecialchars($formData['required_documents'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                                <div class="field-span-12">
                                    <label for="process_steps">Trình tự thực hiện</label>
                                    <textarea id="process_steps" name="process_steps"><?php echo htmlspecialchars($formData['process_steps'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                                <div class="field-span-4">
                                    <label for="processing_time">Thời hạn giải quyết</label>
                                    <input type="text" id="processing_time" name="processing_time" value="<?php echo htmlspecialchars($formData['processing_time'], ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                                <div class="field-span-4">
                                    <label for="fee">Lệ phí</label>
                                    <input type="text" id="fee" name="fee" value="<?php echo htmlspecialchars($formData['fee'], ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                                <div class="field-span-4">
                                    <label for="contact_point">Bộ phận tiếp nhận</label>
                                    <input type="text" id="contact_point" name="contact_point" value="<?php echo htmlspecialchars($formData['contact_point'], ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                                <div class="field-span-12">
                                    <label for="form_url">Link biểu mẫu chính</label>
                                    <input type="text" id="form_url" name="form_url" value="<?php echo htmlspecialchars($formData['form_url'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://... hoặc duong-dan/file.pdf">
                                </div>
                                <div class="field-span-12">
                                    <label for="form_label">Nhãn biểu mẫu chính</label>
                                    <input type="text" id="form_label" name="form_label" value="<?php echo htmlspecialchars($formData['form_label'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ví dụ: Tờ khai trực tiếp (Mẫu 01)">
                                </div>
                                <div class="field-span-12">
                                    <label for="secondary_form_url">Link biểu mẫu bổ sung</label>
                                    <input type="text" id="secondary_form_url" name="secondary_form_url" value="<?php echo htmlspecialchars($formData['secondary_form_url'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Link mẫu điện tử tương tác hoặc biểu mẫu thứ hai">
                                </div>
                                <div class="field-span-12">
                                    <label for="secondary_form_label">Nhãn biểu mẫu bổ sung</label>
                                    <input type="text" id="secondary_form_label" name="secondary_form_label" value="<?php echo htmlspecialchars($formData['secondary_form_label'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ví dụ: Mẫu điện tử tương tác">
                                </div>
                                <div class="field-span-12">
                                    <label for="official_source_url">Link nguồn chính thức</label>
                                    <input type="text" id="official_source_url" name="official_source_url" value="<?php echo htmlspecialchars($formData['official_source_url'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Link trang thủ tục trên cổng dịch vụ công">
                                </div>
                                <div class="field-span-12">
                                    <label for="form_note">Ghi chú biểu mẫu</label>
                                    <textarea id="form_note" name="form_note"><?php echo htmlspecialchars($formData['form_note'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                                </div>
                                <div class="field-span-12">
                                    <label>
                                        <input type="checkbox" name="is_featured" value="1" <?php echo (int) $formData['is_featured'] === 1 ? 'checked' : ''; ?>>
                                        Đánh dấu là thủ tục nổi bật
                                    </label>
                                </div>
                            </div>

                            <div class="stack-form__actions">
                                <button type="submit" class="admin-button"><?php echo $formData['id'] > 0 ? 'Lưu cập nhật' : 'Thêm thủ tục'; ?></button>
                                <a href="quan-ly-thu-tuc.php" class="ghost-button">Tạo biểu mẫu trống</a>
                            </div>
                        </form>
                    </div>

                    <div class="admin-surface">
                        <h3>Danh sách thủ tục</h3>
                        <p><?php echo count($procedures); ?> thủ tục trong bộ lọc hiện tại.</p>

                        <?php if (empty($procedures) && !$pageError): ?>
                            <div class="empty-panel">Chưa có thủ tục nào phù hợp với điều kiện lọc.</div>
                        <?php endif; ?>

                        <div class="admin-record-list">
                            <?php foreach ($procedures as $procedure): ?>
                                <article class="admin-record-card">
                                    <div class="admin-record-card__header">
                                        <div>
                                            <span class="procedure-badge"><?php echo htmlspecialchars(procedureCategoryLabel($procedure['category']), ENT_QUOTES, 'UTF-8'); ?></span>
                                            <h4><?php echo htmlspecialchars($procedure['title'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                            <p><?php echo htmlspecialchars($procedure['code'], ENT_QUOTES, 'UTF-8'); ?></p>
                                        </div>
                                        <div class="procedure-admin-links">
                                            <span class="status-pill <?php echo $procedure['status'] === 'active' ? 'is-resolved' : 'is-processing'; ?>">
                                                <?php echo htmlspecialchars(procedureStatusLabel($procedure['status']), ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                            <?php if ((int) $procedure['is_featured'] === 1): ?>
                                                <span class="priority-pill is-high">Nổi bật</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <p class="admin-record-card__summary">
                                        <?php echo htmlspecialchars($procedure['summary'], ENT_QUOTES, 'UTF-8'); ?>
                                    </p>

                                    <div class="admin-card__meta">
                                        <div class="meta-chip">
                                            <span>Thời hạn</span>
                                            <strong><?php echo htmlspecialchars(trim((string) $procedure['processing_time']) !== '' ? $procedure['processing_time'] : 'Đang cập nhật', ENT_QUOTES, 'UTF-8'); ?></strong>
                                        </div>
                                        <div class="meta-chip">
                                            <span>Lệ phí</span>
                                            <strong><?php echo htmlspecialchars(trim((string) $procedure['fee']) !== '' ? $procedure['fee'] : 'Đang cập nhật', ENT_QUOTES, 'UTF-8'); ?></strong>
                                        </div>
                                        <div class="meta-chip">
                                            <span>Cập nhật</span>
                                            <strong><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime((string) $procedure['updated_at'])), ENT_QUOTES, 'UTF-8'); ?></strong>
                                        </div>
                                    </div>

                                    <div class="procedure-card__actions" style="margin-top: 16px;">
                                        <a href="quan-ly-thu-tuc.php?edit=<?php echo (int) $procedure['id']; ?>" class="admin-button">Sửa</a>
                                        <a href="thu-tuc-hanh-chinh.php#thu-tuc-<?php echo (int) $procedure['id']; ?>" class="secondary-button">Xem công khai</a>
                                        <form method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa thủ tục này?');" style="display: inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="procedure_id" value="<?php echo (int) $procedure['id']; ?>">
                                            <button type="submit" class="ghost-button">Xóa</button>
                                        </form>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
