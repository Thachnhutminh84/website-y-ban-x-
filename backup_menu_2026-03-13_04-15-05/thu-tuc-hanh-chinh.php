<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

require_once 'config.php';
require_once 'thu-tuc-bieu-mau-helper.php';
require_once 'auth.php';

function procedureExcerpt($text, $length = 180)
{
    $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $text)));
    if ($text === '') {
        return '';
    }

    $textLength = function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
    if ($textLength <= $length) {
        return $text;
    }

    $shortText = function_exists('mb_substr') ? mb_substr($text, 0, $length, 'UTF-8') : substr($text, 0, $length);
    return rtrim($shortText) . '...';
}

$isLoggedIn = authIsLoggedIn();
$isAdmin = authIsAdmin();
$currentRole = authCurrentRole();
$displayName = $isLoggedIn ? authDisplayName() : '';
$keyword = trim((string) ($_GET['keyword'] ?? ''));
$category = trim((string) ($_GET['category'] ?? 'all'));
$categories = getProcedureCategoryOptions();

if (!array_key_exists($category, $categories)) {
    $category = 'all';
}

$procedures = [];
$pageError = null;

$conn = getProcedureStorageConnection();
if (!$conn) {
    $pageError = 'Không thể kết nối cơ sở dữ liệu để tải kho thủ tục.';
} elseif (!ensureProceduresTableExists($conn)) {
    $pageError = 'Không thể chuẩn bị dữ liệu thủ tục hành chính.';
} else {
    $conditions = ["status = 'active'"];
    $params = [];
    $types = '';

    if ($category !== 'all') {
        $conditions[] = 'category = ?';
        $params[] = $category;
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

    $sql = "SELECT id, code, title, category, summary, required_documents, process_steps,
                   processing_time, fee, form_url, form_label, secondary_form_url, secondary_form_label,
                   form_note, official_source_url, contact_point, is_featured
            FROM administrative_procedures
            WHERE " . implode(' AND ', $conditions) . "
            ORDER BY is_featured DESC, display_order ASC, updated_at DESC, id DESC";

    $stmt = $conn->prepare($sql);
    if ($stmt && procedureBindParams($stmt, $types, $params) && $stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $procedures[] = $row;
        }
        $stmt->close();
    } else {
        $pageError = 'Không thể tải danh sách thủ tục trong lúc này.';
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thủ tục hành chính - Biểu mẫu - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css?v=2.1">
    <link rel="stylesheet" href="citizen-services.css?v=1.1">
    <script src="dropdown.js"></script>
</head>
<body>
    <header>
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
                    <li class="dropdown">
                        <a href="tin-tuc.php">Tin tức - Thông báo</a>
                        <button class="dropdown-toggle" onclick="toggleDropdown(event)">&#9662;</button>
                        <ul class="dropdown-menu">
                            <li><a href="tin-tuc.php?cat=xay-dung-dang">Công tác xây dựng Đảng</a></li>
                            <li><a href="tin-tuc.php?cat=mat-tran">Mặt trận đoàn thể</a></li>
                            <li><a href="tin-tuc.php?cat=an-ninh">An ninh trật tự</a></li>
                            <li><a href="tin-tuc.php?cat=su-kien">Tin tức sự kiện</a></li>
                            <li><a href="tin-tuc.php?cat=tuyen-truyen">Thông tin tuyên truyền</a></li>
                            <li><a href="tin-tuc.php?cat=giao-duc">Giáo dục và đào tạo</a></li>
                        </ul>
                    </li>
                    <li><a href="phong-ban.php">Phòng ban</a></li>
                    <li><a href="lanh-dao.php">Lãnh đạo</a></li>
                    <li><a href="thu-tuc-hanh-chinh.php" class="active">Thủ tục - Biểu mẫu</a></li>
                    <li><a href="lien-he.php">Liên hệ</a></li>
                    <?php if ($isLoggedIn): ?>
                        <li class="admin-info">
                            👤 <?php echo htmlspecialchars(authRoleLabel($currentRole), ENT_QUOTES, 'UTF-8'); ?>
                            <a href="tin-tuc.php"><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></a>
                            <?php if ($isAdmin): ?>
                                <a href="quan-ly-thu-tuc.php">Quản lý thủ tục</a>
                            <?php endif; ?>
                            <a href="logout.php">Đăng xuất</a>
                        </li>
                    <?php else: ?>
                        <li><a href="dang-nhap.php" class="login-btn">Đăng nhập</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="page-header">
            <div class="container">
                <h2>Kho thủ tục hành chính và biểu mẫu</h2>
                <p>Tra cứu hồ sơ cần chuẩn bị, thời hạn xử lý và biểu mẫu liên quan tại UBND xã Long Hiệp.</p>
            </div>
        </section>

        <section class="form-section">
            <div class="container">
                <div class="filters-card">
                    <h3>Tìm thủ tục nhanh</h3>
                    <p>Nhập tên thủ tục, mã hồ sơ hoặc chọn lĩnh vực để lọc danh sách phù hợp.</p>
                    <form method="GET" class="filters-form">
                        <div class="filters-form__row">
                            <div class="field-span-8">
                                <label for="keyword">Từ khóa</label>
                                <input type="text" id="keyword" name="keyword" value="<?php echo htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ví dụ: khai sinh, chứng thực, xác nhận...">
                            </div>
                            <div class="field-span-4">
                                <label for="category">Lĩnh vực</label>
                                <select id="category" name="category">
                                    <?php foreach ($categories as $slug => $label): ?>
                                        <option value="<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $category === $slug ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="filters-actions">
                            <button type="submit" class="filters-button">Lọc danh sách</button>
                            <a href="thu-tuc-hanh-chinh.php" class="ghost-button">Đặt lại</a>
                            <?php if ($isAdmin): ?>
                                <a href="quan-ly-thu-tuc.php" class="secondary-button">Mở quản trị</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <?php if ($pageError): ?>
                    <div class="flash-panel is-error">
                        <?php echo htmlspecialchars($pageError, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php else: ?>
                    <div class="results-bar">
                        <div class="results-count">
                            <?php echo count($procedures); ?> thủ tục phù hợp
                        </div>
                        <div class="results-meta">
                            <?php if ($keyword !== ''): ?>
                                Từ khóa: <strong><?php echo htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?></strong>
                            <?php else: ?>
                                Hiển thị toàn bộ danh sách đang áp dụng.
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!$pageError && empty($procedures)): ?>
                    <div class="empty-panel">
                        Chưa tìm thấy thủ tục phù hợp với bộ lọc hiện tại.
                    </div>
                <?php endif; ?>

                <?php if (!$pageError && !empty($procedures)): ?>
                    <div class="procedure-grid">
                        <?php foreach ($procedures as $procedure): ?>
                            <article class="procedure-card" id="thu-tuc-<?php echo (int) $procedure['id']; ?>">
                                <div class="procedure-card__header">
                                    <div>
                                        <span class="procedure-badge"><?php echo htmlspecialchars(procedureCategoryLabel($procedure['category']), ENT_QUOTES, 'UTF-8'); ?></span>
                                        <h3><?php echo htmlspecialchars($procedure['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                    </div>
                                    <div class="procedure-card__actions">
                                        <span class="status-pill is-resolved"><?php echo htmlspecialchars($procedure['code'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                </div>

                                <p class="procedure-card__summary">
                                    <?php echo htmlspecialchars(procedureExcerpt($procedure['summary']), ENT_QUOTES, 'UTF-8'); ?>
                                </p>

                                <div class="procedure-card__meta">
                                    <div class="meta-chip">
                                        <span>Thời hạn</span>
                                        <strong><?php echo htmlspecialchars(trim((string) $procedure['processing_time']) !== '' ? $procedure['processing_time'] : 'Đang cập nhật', ENT_QUOTES, 'UTF-8'); ?></strong>
                                    </div>
                                    <div class="meta-chip">
                                        <span>Lệ phí</span>
                                        <strong><?php echo htmlspecialchars(trim((string) $procedure['fee']) !== '' ? $procedure['fee'] : 'Xem hướng dẫn', ENT_QUOTES, 'UTF-8'); ?></strong>
                                    </div>
                                    <div class="meta-chip">
                                        <span>Tiếp nhận</span>
                                        <strong><?php echo htmlspecialchars(trim((string) $procedure['contact_point']) !== '' ? $procedure['contact_point'] : 'Bộ phận một cửa', ENT_QUOTES, 'UTF-8'); ?></strong>
                                    </div>
                                </div>

                                <details>
                                    <summary>Xem chi tiết hồ sơ và quy trình</summary>
                                    <div class="procedure-card__detail">
                                        <h4>Mô tả</h4>
                                        <p><?php echo nl2br(htmlspecialchars((string) $procedure['summary'], ENT_QUOTES, 'UTF-8')); ?></p>

                                        <h4>Thành phần hồ sơ</h4>
                                        <pre><?php echo htmlspecialchars(trim((string) $procedure['required_documents']) !== '' ? $procedure['required_documents'] : 'Đang cập nhật', ENT_QUOTES, 'UTF-8'); ?></pre>

                                        <h4>Trình tự thực hiện</h4>
                                        <pre><?php echo htmlspecialchars(trim((string) $procedure['process_steps']) !== '' ? $procedure['process_steps'] : 'Đang cập nhật', ENT_QUOTES, 'UTF-8'); ?></pre>

                                        <h4>Biểu mẫu và nguồn đối chiếu</h4>
                                        <div class="procedure-form-links">
                                            <?php if (trim((string) $procedure['form_url']) !== ''): ?>
                                                <a href="<?php echo htmlspecialchars($procedure['form_url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="admin-button">
                                                    <?php echo htmlspecialchars(trim((string) $procedure['form_label']) !== '' ? $procedure['form_label'] : 'Biểu mẫu chính thức', ENT_QUOTES, 'UTF-8'); ?>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (trim((string) $procedure['secondary_form_url']) !== ''): ?>
                                                <a href="<?php echo htmlspecialchars($procedure['secondary_form_url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="secondary-button">
                                                    <?php echo htmlspecialchars(trim((string) $procedure['secondary_form_label']) !== '' ? $procedure['secondary_form_label'] : 'Biểu mẫu bổ sung', ENT_QUOTES, 'UTF-8'); ?>
                                                </a>
                                            <?php endif; ?>
                                            <?php if (trim((string) $procedure['official_source_url']) !== ''): ?>
                                                <a href="<?php echo htmlspecialchars($procedure['official_source_url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="ghost-button">
                                                    Xem nguồn chính thức
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (trim((string) $procedure['form_note']) !== ''): ?>
                                            <p class="procedure-form-note"><?php echo nl2br(htmlspecialchars((string) $procedure['form_note'], ENT_QUOTES, 'UTF-8')); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </details>

                                <div class="procedure-card__actions">
                                    <?php if (trim((string) $procedure['form_url']) !== ''): ?>
                                        <a href="<?php echo htmlspecialchars($procedure['form_url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="admin-button">
                                            <?php echo htmlspecialchars(trim((string) $procedure['form_label']) !== '' ? $procedure['form_label'] : 'Mở biểu mẫu chính thức', ENT_QUOTES, 'UTF-8'); ?>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (trim((string) $procedure['secondary_form_url']) !== ''): ?>
                                        <a href="<?php echo htmlspecialchars($procedure['secondary_form_url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="secondary-button">
                                            <?php echo htmlspecialchars(trim((string) $procedure['secondary_form_label']) !== '' ? $procedure['secondary_form_label'] : 'Mở biểu mẫu bổ sung', ENT_QUOTES, 'UTF-8'); ?>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (trim((string) $procedure['official_source_url']) !== ''): ?>
                                        <a href="<?php echo htmlspecialchars($procedure['official_source_url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="ghost-button">
                                            Xem nguồn chính thức
                                        </a>
                                    <?php endif; ?>
                                    <?php if (trim((string) $procedure['form_url']) === '' && trim((string) $procedure['secondary_form_url']) === '' && trim((string) $procedure['form_note']) !== ''): ?>
                                        <span class="procedure-form-note procedure-form-note--inline"><?php echo htmlspecialchars($procedure['form_note'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php endif; ?>
                                    <a href="lien-he.php" class="ghost-button">Hỏi thêm về thủ tục này</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
