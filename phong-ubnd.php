<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

require_once 'auth.php';
require_once 'department-data.php';

$isLoggedIn = authIsLoggedIn();
$currentRole = authCurrentRole();
$displayName = $isLoggedIn ? authDisplayName() : '';

$department = $departments['ubnd'];
$contactItems = $department['contact_items'];
$keyFunctions = $department['key_functions'];
$tasks = $department['tasks'];
$activities = $department['activities'];
$staffMembers = $department['staff_members'];
$highlightStats = $department['highlight_stats'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($department['name'], ENT_QUOTES, 'UTF-8'); ?> - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css?v=2.1">
    <link rel="stylesheet" href="phong-ban-style.css?v=2.1">
    <link rel="stylesheet" href="footer-style.css?v=1.0">
    <script src="dropdown.js"></script>
</head>
<body>
    <?php include 'header-menu.php'; ?>

    <main class="dept-page">
        <section class="dept-hero">
            <div class="container">
                <div class="breadcrumb-nav">
                    <a href="index.php">Trang chủ</a> /
                    <a href="phong-ban.php">Phòng ban</a> /
                    <span><?php echo htmlspecialchars($department['short_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>

                <div class="dept-hero__grid">
                    <div class="dept-hero__copy">
                        <span class="dept-kicker"><?php echo htmlspecialchars($department['badge'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <h2><?php echo htmlspecialchars($department['name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                        <p><?php echo htmlspecialchars($department['subtitle'], ENT_QUOTES, 'UTF-8'); ?></p>

                        <div class="dept-hero__meta">
                            <div class="dept-meta-card">
                                <span class="dept-meta-card__label">Người phụ trách</span>
                                <strong><?php echo htmlspecialchars($department['manager'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            </div>
                            <div class="dept-meta-card">
                                <span class="dept-meta-card__label">Trọng tâm công việc</span>
                                <strong><?php echo htmlspecialchars($department['focus'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            </div>
                        </div>
                    </div>

                    <div class="dept-hero__panel">
                        <div class="dept-glance">
                            <span class="dept-glance__label">Tổng quan nhanh</span>
                            <ul class="dept-contact-list">
                                <?php foreach ($contactItems as $item): ?>
                                    <li>
                                        <span><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <strong><?php echo htmlspecialchars($item['value'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="dept-stat-grid">
                    <?php foreach ($highlightStats as $stat): ?>
                        <article class="dept-stat-card">
                            <strong><?php echo htmlspecialchars((string) $stat['value'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            <span><?php echo htmlspecialchars($stat['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="dept-section">
            <div class="container">
                <div class="dept-layout">
                    <article class="dept-panel dept-panel--wide">
                        <div class="dept-section-heading">
                            <span class="dept-section-heading__eyebrow">Tổng quan</span>
                            <h3>Vai trò và phạm vi phụ trách</h3>
                        </div>
                        <p class="dept-lead">
                            <?php echo htmlspecialchars($department['name'], ENT_QUOTES, 'UTF-8'); ?> là cơ quan hành chính nhân dân cấp xã,
                            thực hiện chức năng quản lý hành chính, tổ chức thực hiện các quyết định của HĐNN và các chính sách, pháp luật của Nhà nước.
                            UBND xã là đầu mối điều hành công tác hành chính, phát triển kinh tế - xã hội và bảo vệ quyền lợi hợp pháp của nhân dân.
                            Mục tiêu của UBND là xây dựng xã Long Hiệp phát triển bền vững, giàu có, văn minh và hạnh phúc.
                        </p>
                    </article>

                    <aside class="dept-panel dept-panel--accent">
                        <div class="dept-section-heading">
                            <span class="dept-section-heading__eyebrow">Liên hệ</span>
                            <h3>Thông tin tiếp nhận</h3>
                        </div>
                        <ul class="dept-contact-stack">
                            <?php foreach ($contactItems as $item): ?>
                                <li>
                                    <span><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <strong><?php echo htmlspecialchars($item['value'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </aside>
                </div>
            </div>
        </section>

        <section class="dept-section dept-section--soft">
            <div class="container">
                <div class="dept-section-heading dept-section-heading--center">
                    <span class="dept-section-heading__eyebrow">Chức năng</span>
                    <h3>6 nhóm chức năng đang triển khai</h3>
                </div>

                <div class="dept-chip-grid">
                    <?php foreach ($keyFunctions as $function): ?>
                        <article class="dept-chip-card">
                            <span class="dept-chip-card__index"></span>
                            <p><?php echo htmlspecialchars($function, ENT_QUOTES, 'UTF-8'); ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="dept-section">
            <div class="container">
                <div class="dept-section-heading">
                    <span class="dept-section-heading__eyebrow">Nhiệm vụ trọng tâm</span>
                    <h3>Những đầu việc cần theo dõi thường xuyên</h3>
                </div>

                <div class="dept-task-list">
                    <?php foreach ($tasks as $index => $task): ?>
                        <article class="dept-task-card">
;
                            <div class="dept-task-card__number"><?php echo $index + 1; ?></div>
                            <div class="dept-task-card__content">
                                <h4><?php echo htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                <p><?php echo htmlspecialchars($task['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="dept-section dept-section--soft">
            <div class="container">
                <div class="dept-section-heading">
                    <span class="dept-section-heading__eyebrow">Hoạt động nổi bật</span>
                    <h3>Một số mảng phối hợp nổi bật tại địa phương</h3>
                </div>

                <div class="dept-activity-grid">
                    <?php foreach ($activities as $activity): ?>
                        <article class="dept-activity-card">
                            <h4><?php echo htmlspecialchars($activity['title'], ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p><?php echo htmlspecialchars($activity['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="dept-section">
            <div class="container">
                <div class="dept-section-heading">
                    <span class="dept-section-heading__eyebrow">Nhân sự</span>
                    <h3>Danh sách lãnh đạo, quản lý</h3>
                </div>

                <div class="dept-staff-grid">
                    <?php foreach ($staffMembers as $member): ?>
                        <article class="dept-staff-card">
                            <div class="dept-staff-card__avatar">
                                <?php echo htmlspecialchars(mb_substr($member['name'], 0, 1, 'UTF-8'), ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <div class="dept-staff-card__body">
                                <h4><?php echo htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                <p><?php echo htmlspecialchars($member['role'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <span><?php echo htmlspecialchars($member['phone'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>
    <?php include 'quick-actions-toolbar.php'; ?>
</body>
</html>

