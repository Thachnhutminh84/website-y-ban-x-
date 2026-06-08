<?php
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

session_start();
require_once 'config.php';
require_once 'auth.php';

$isLoggedIn = authIsLoggedIn();
$currentRole = authCurrentRole();
$displayName = $isLoggedIn ? authDisplayName() : '';

function leaderHasValue($value)
{
    $value = trim((string) $value);
    return $value !== '' && $value !== '0000-00-00' && $value !== '0000-00-00 00:00:00';
}

function leaderText($value, $fallback = 'Đang cập nhật')
{
    return leaderHasValue($value) ? trim((string) $value) : $fallback;
}

function leaderDate($value, $fallback = 'Đang cập nhật')
{
    if (!leaderHasValue($value)) {
        return $fallback;
    }

    $value = trim((string) $value);

    if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
        return $value;
    }

    $timestamp = strtotime($value);

    if ($timestamp === false) {
        return $value;
    }

    return date('d/m/Y', $timestamp);
}

function leaderField($label, $value)
{
    return [
        'label' => $label,
        'value' => $value,
        'empty' => $value === 'Đang cập nhật'
    ];
}

$conn = getDBConnection();
$id = isset($_GET['id']) ? (int) $_GET['id'] : 1;

$tableCheck = $conn->query("SHOW TABLES LIKE 'leaders'");
if ($tableCheck->num_rows === 0) {
    die('Vui lòng import file create-leaders-table.sql và insert-leaders-data.sql vào database trước!');
}

$stmt = $conn->prepare("SELECT * FROM leaders WHERE id = ? AND is_active = 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: lanh-dao.php');
    exit();
}

$leader = $result->fetch_assoc();
$stmt->close();

$stmtHistory = $conn->prepare("SELECT * FROM leader_work_history WHERE leader_id = ? ORDER BY display_order ASC");
$stmtHistory->bind_param('i', $id);
$stmtHistory->execute();
$resultHistory = $stmtHistory->get_result();

$workHistory = [];
while ($row = $resultHistory->fetch_assoc()) {
    $workHistory[] = $row;
}

$stmtHistory->close();
$conn->close();

$responsibilities = leaderText($leader['responsibilities'], 'Nội dung phân công phụ trách đang được cập nhật.');
$phone = leaderText($leader['phone']);
$email = leaderText($leader['email']);
$phoneHref = preg_replace('/[^0-9+]/', '', (string) $leader['phone']);
$emailHref = filter_var((string) $leader['email'], FILTER_VALIDATE_EMAIL) ? trim((string) $leader['email']) : '';

$personalFields = [
    leaderField('Họ và tên khai sinh', leaderText($leader['name'])),
    leaderField('Ngày, tháng, năm sinh', leaderDate($leader['birth_date'])),
    leaderField('Giới tính', leaderText($leader['gender'])),
    leaderField('Quốc tịch', leaderText($leader['nationality'])),
    leaderField('Dân tộc', leaderText($leader['ethnicity'])),
    leaderField('Tôn giáo', leaderText($leader['religion'])),
    leaderField('Quê quán', leaderText($leader['hometown'])),
    leaderField('Nơi ở hiện nay', leaderText($leader['residence'])),
    leaderField('Số CMND/CCCD', leaderText($leader['id_number'])),
    leaderField('Ngày cấp', leaderDate($leader['id_issue_date'])),
    leaderField('Nơi cấp', leaderText($leader['id_issue_place'])),
    leaderField('Ngày vào Đảng', leaderDate($leader['party_date'])),
    leaderField('Ngày chính thức', leaderDate($leader['party_official_date']))
];

if (leaderHasValue($leader['party_member_id'])) {
    $personalFields[] = leaderField('Số thẻ đảng viên', leaderText($leader['party_member_id']));
}

$professionalFields = [
    leaderField('Trình độ học vấn', leaderText($leader['education'])),
    leaderField('Lý luận chính trị', leaderText($leader['political_theory'])),
    leaderField('Quản lý nhà nước', leaderText($leader['state_management'])),
    leaderField('Ngoại ngữ', leaderText($leader['language'])),
    leaderField('Nghề nghiệp', leaderText($leader['profession']))
];

$currentRoleFields = [
    leaderField('Chức vụ Đảng', leaderText($leader['party_position'])),
    leaderField('Chức vụ chính quyền', leaderText($leader['work_position']))
];

if (leaderHasValue($leader['party_organization'])) {
    $currentRoleFields[] = leaderField('Tham gia tổ chức đoàn thể', leaderText($leader['party_organization']));
}

if (leaderHasValue($leader['awards'])) {
    $currentRoleFields[] = leaderField('Khen thưởng', leaderText($leader['awards']));
}

$summaryFields = [
    ['label' => 'Ngày sinh', 'value' => leaderDate($leader['birth_date'])],
    ['label' => 'Quê quán', 'value' => leaderText($leader['hometown'])],
    ['label' => 'Trình độ', 'value' => leaderText($leader['education'])],
    ['label' => 'Ngày vào Đảng', 'value' => leaderDate($leader['party_date'])]
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($leader['name'], ENT_QUOTES, 'UTF-8'); ?> - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css?v=2.1">
    <link rel="stylesheet" href="leader-detail-style.css?v=1.0">
    <script src="dropdown.js"></script>
</head>
<body>
    <header<?php echo $isLoggedIn ? ' class="header--compact"' : ''; ?>>
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
                    <li><a href="lanh-dao.php" class="active">Lãnh đạo</a></li>
                    <li><a href="lien-he.php">Liên hệ</a></li>
                    <li><a href="dang-nhap.php" class="login-btn">Đăng nhập</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="leader-page">
        <div class="container">
            <nav class="leader-page__breadcrumb" aria-label="Breadcrumb">
                <a href="index.php">Trang chủ</a>
                <span>/</span>
                <a href="lanh-dao.php">Lãnh đạo</a>
                <span>/</span>
                <strong><?php echo htmlspecialchars($leader['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
            </nav>

            <section class="leader-hero-card">
                <div class="leader-portrait">
                    <div class="leader-portrait__frame">
                        <img
                            src="<?php echo htmlspecialchars($leader['image_path'], ENT_QUOTES, 'UTF-8'); ?>"
                            alt="<?php echo htmlspecialchars($leader['name'], ENT_QUOTES, 'UTF-8'); ?>"
                            loading="eager"
                            onerror="this.src='images/news-default.jpg'">
                    </div>
                </div>

                <div class="leader-hero-content">
                    <span class="leader-kicker">Hồ sơ lãnh đạo</span>
                    <h1 class="leader-name"><?php echo htmlspecialchars($leader['name'], ENT_QUOTES, 'UTF-8'); ?></h1>
                    <p class="leader-position"><?php echo htmlspecialchars($leader['position'], ENT_QUOTES, 'UTF-8'); ?></p>

                    <div class="leader-responsibility-card">
                        <span class="leader-responsibility-card__label">Phạm vi phụ trách</span>
                        <p><?php echo htmlspecialchars($responsibilities, ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>

                    <div class="leader-hero-meta">
                        <?php foreach ($summaryFields as $field): ?>
                            <article class="leader-meta-card">
                                <span><?php echo htmlspecialchars($field['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <strong><?php echo htmlspecialchars($field['value'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <div class="leader-hero-actions">
                        <?php if ($phone !== 'Đang cập nhật' && $phoneHref !== ''): ?>
                            <a href="tel:<?php echo htmlspecialchars($phoneHref, ENT_QUOTES, 'UTF-8'); ?>" class="leader-action leader-action--light">
                                Gọi <?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        <?php endif; ?>

                        <?php if ($email !== 'Đang cập nhật' && $emailHref !== ''): ?>
                            <a href="mailto:<?php echo htmlspecialchars($emailHref, ENT_QUOTES, 'UTF-8'); ?>" class="leader-action leader-action--accent">
                                Gửi email
                            </a>
                        <?php endif; ?>

                        <a href="lanh-dao.php" class="leader-action leader-action--ghost">Danh sách lãnh đạo</a>
                    </div>
                </div>
            </section>

            <section class="leader-content-grid">
                <div class="leader-main">
                    <section class="leader-section-card">
                        <div class="leader-section-card__header">
                            <span class="leader-section-card__eyebrow">Thông tin cá nhân</span>
                            <h2>Hồ sơ cá nhân</h2>
                        </div>

                        <div class="leader-info-grid">
                            <?php foreach ($personalFields as $field): ?>
                                <article class="leader-info-item<?php echo $field['empty'] ? ' is-empty' : ''; ?>">
                                    <span class="leader-info-item__label"><?php echo htmlspecialchars($field['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <strong class="leader-info-item__value"><?php echo htmlspecialchars($field['value'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <section class="leader-section-card">
                        <div class="leader-section-card__header">
                            <span class="leader-section-card__eyebrow">Trình độ</span>
                            <h2>Năng lực chuyên môn</h2>
                        </div>

                        <div class="leader-info-grid leader-info-grid--compact">
                            <?php foreach ($professionalFields as $field): ?>
                                <article class="leader-info-item<?php echo $field['empty'] ? ' is-empty' : ''; ?>">
                                    <span class="leader-info-item__label"><?php echo htmlspecialchars($field['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <strong class="leader-info-item__value"><?php echo htmlspecialchars($field['value'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <section class="leader-section-card">
                        <div class="leader-section-card__header">
                            <span class="leader-section-card__eyebrow">Chức vụ hiện tại</span>
                            <h2>Vai trò đang đảm nhiệm</h2>
                        </div>

                        <div class="leader-info-grid leader-info-grid--compact">
                            <?php foreach ($currentRoleFields as $field): ?>
                                <article class="leader-info-item<?php echo $field['empty'] ? ' is-empty' : ''; ?>">
                                    <span class="leader-info-item__label"><?php echo htmlspecialchars($field['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <strong class="leader-info-item__value"><?php echo htmlspecialchars($field['value'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <section class="leader-section-card">
                        <div class="leader-section-card__header">
                            <span class="leader-section-card__eyebrow">Quá trình công tác</span>
                            <h2>Các mốc công tác nổi bật</h2>
                        </div>

                        <?php if (!empty($workHistory)): ?>
                            <div class="leader-timeline">
                                <?php foreach ($workHistory as $work): ?>
                                    <article class="leader-timeline-item">
                                        <div class="leader-timeline-item__period">
                                            <?php echo htmlspecialchars(leaderText($work['period']), ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                        <div class="leader-timeline-item__body">
                                            <p><?php echo htmlspecialchars(leaderText($work['position']), ENT_QUOTES, 'UTF-8'); ?></p>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="leader-empty-state">Thông tin quá trình công tác đang được cập nhật.</div>
                        <?php endif; ?>
                    </section>
                </div>

                <aside class="leader-sidebar">
                    <section class="leader-side-card leader-side-card--contact">
                        <div class="leader-section-card__header leader-section-card__header--tight">
                            <span class="leader-section-card__eyebrow">Liên hệ nhanh</span>
                            <h2>Kênh liên hệ</h2>
                        </div>

                        <div class="leader-contact-list">
                            <div class="leader-contact-item">
                                <span>Điện thoại</span>
                                <strong><?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?></strong>
                            </div>
                            <div class="leader-contact-item">
                                <span>Email</span>
                                <strong><?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></strong>
                            </div>
                            <div class="leader-contact-item">
                                <span>Chức vụ</span>
                                <strong><?php echo htmlspecialchars(leaderText($leader['work_position'], $leader['position']), ENT_QUOTES, 'UTF-8'); ?></strong>
                            </div>
                        </div>
                    </section>

                    <section class="leader-side-card">
                        <div class="leader-section-card__header leader-section-card__header--tight">
                            <span class="leader-section-card__eyebrow">Tóm tắt hồ sơ</span>
                            <h2>Thông tin nổi bật</h2>
                        </div>

                        <div class="leader-summary-stack">
                            <?php foreach ($summaryFields as $field): ?>
                                <article class="leader-summary-item">
                                    <span><?php echo htmlspecialchars($field['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <strong><?php echo htmlspecialchars($field['value'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <section class="leader-side-card leader-side-card--note">
                        <div class="leader-section-card__header leader-section-card__header--tight">
                            <span class="leader-section-card__eyebrow">Ghi chú</span>
                            <h2>Trách nhiệm chính</h2>
                        </div>

                        <p class="leader-side-note"><?php echo htmlspecialchars($responsibilities, ENT_QUOTES, 'UTF-8'); ?></p>
                    </section>
                </aside>
            </section>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
