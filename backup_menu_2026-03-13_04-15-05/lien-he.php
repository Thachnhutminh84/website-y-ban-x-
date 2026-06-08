<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'contact-message-helper.php';
require_once 'auth.php';

$isLoggedIn = authIsLoggedIn();
$isAdmin = authIsAdmin();
$currentRole = authCurrentRole();
$displayName = $isLoggedIn ? authDisplayName() : '';
$contactSuccess = $_SESSION['contact_success'] ?? null;
$contactError = $_SESSION['contact_error'] ?? null;
$contactTicketCode = $_SESSION['contact_ticket_code'] ?? null;
$oldContact = $_SESSION['contact_old'] ?? [
    'name' => '',
    'email' => '',
    'phone' => '',
    'subject' => '',
    'message' => '',
    'priority' => 'normal'
];

unset($_SESSION['contact_success'], $_SESSION['contact_error'], $_SESSION['contact_old'], $_SESSION['contact_ticket_code']);

$lookupTicketCode = strtoupper(trim((string) ($_GET['ticket_code'] ?? '')));
$lookupEmail = trim((string) ($_GET['ticket_email'] ?? ''));
$ticketLookup = null;
$ticketLookupError = null;

if ($lookupTicketCode !== '' || $lookupEmail !== '') {
    if ($lookupTicketCode === '' || $lookupEmail === '') {
        $ticketLookupError = 'Vui lòng nhập đầy đủ mã phiếu và email để tra cứu.';
    } else {
        $conn = getContactStorageConnection();
        if (!$conn) {
            $ticketLookupError = 'Không thể kết nối cơ sở dữ liệu để tra cứu phiếu.';
        } elseif (!ensureContactsTableExists($conn)) {
            $ticketLookupError = 'Không thể chuẩn bị khu vực tra cứu phiếu liên hệ.';
        } else {
            $stmtLookup = $conn->prepare(
                'SELECT ticket_code, name, email, subject, status, priority, created_at, updated_at
                 FROM contacts
                 WHERE ticket_code = ? AND email = ?
                 LIMIT 1'
            );

            if ($stmtLookup) {
                $stmtLookup->bind_param('ss', $lookupTicketCode, $lookupEmail);
                if ($stmtLookup->execute()) {
                    $resultLookup = $stmtLookup->get_result();
                    $ticketLookup = $resultLookup->fetch_assoc() ?: null;
                    if (!$ticketLookup) {
                        $ticketLookupError = 'Không tìm thấy phiếu phù hợp với thông tin bạn cung cấp.';
                    }
                } else {
                    $ticketLookupError = 'Không thể thực hiện tra cứu trong lúc này.';
                }
                $stmtLookup->close();
            } else {
                $ticketLookupError = 'Không thể chuẩn bị truy vấn tra cứu.';
            }

            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liên hệ - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css?v=2.1">
    <link rel="stylesheet" href="citizen-services.css?v=1.1">
    <script src="dropdown.js"></script>
</head>
<body>
    <!-- Header thống nhất -->
    <?php include 'header-thong-nhat.php'; ?>

    <main>
        <section class="page-header">
            <div class="container">
                <h2>Liên hệ</h2>
                <p>Thông tin liên hệ và khu vực tiếp nhận phản hồi từ người dân.</p>
            </div>
        </section>

        <section class="contact">
            <div class="container">
                <?php if ($contactSuccess): ?>
                    <div class="flash-panel is-success">
                        <?php echo htmlspecialchars($contactSuccess, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <?php if ($contactError): ?>
                    <div class="flash-panel is-error">
                        <?php echo htmlspecialchars($contactError, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <?php if ($contactTicketCode): ?>
                    <div class="ticket-result-card">
                        <h3>Mã phiếu đã được cấp</h3>
                        <p>Giữ lại mã phiếu này để tra cứu trạng thái xử lý về sau.</p>
                        <div class="ticket-meta-grid">
                            <div class="meta-chip">
                                <span>Mã phiếu</span>
                                <strong><?php echo htmlspecialchars($contactTicketCode, ENT_QUOTES, 'UTF-8'); ?></strong>
                            </div>
                            <div class="meta-chip">
                                <span>Trạng thái khởi tạo</span>
                                <strong>Mới tiếp nhận</strong>
                            </div>
                            <div class="meta-chip">
                                <span>Gợi ý</span>
                                <strong>Dùng cùng email để tra cứu</strong>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="lookup-card">
                    <h3>Tra cứu phiếu liên hệ</h3>
                    <p>Nhập mã phiếu và email đã dùng khi gửi để xem trạng thái xử lý hiện tại.</p>
                    <form method="GET" class="lookup-form">
                        <div class="lookup-form__row">
                            <div class="field-span-4">
                                <label for="ticket_code">Mã phiếu</label>
                                <input type="text" id="ticket_code" name="ticket_code" value="<?php echo htmlspecialchars($lookupTicketCode, ENT_QUOTES, 'UTF-8'); ?>" placeholder="LH-000001">
                            </div>
                            <div class="field-span-5">
                                <label for="ticket_email">Email tra cứu</label>
                                <input type="email" id="ticket_email" name="ticket_email" value="<?php echo htmlspecialchars($lookupEmail, ENT_QUOTES, 'UTF-8'); ?>" placeholder="ban@example.com">
                            </div>
                            <div class="field-span-3">
                                <label>&nbsp;</label>
                                <button type="submit" class="lookup-button">Tra cứu</button>
                            </div>
                        </div>
                    </form>
                </div>

                <?php if ($ticketLookupError): ?>
                    <div class="flash-panel is-error">
                        <?php echo htmlspecialchars($ticketLookupError, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <?php if ($ticketLookup): ?>
                    <div class="ticket-result-card">
                        <h3>Kết quả tra cứu phiếu</h3>
                        <p>Phiếu đang được theo dõi trong hệ thống tiếp nhận và xử lý phản ánh.</p>
                        <div class="ticket-meta-grid">
                            <div class="meta-chip">
                                <span>Mã phiếu</span>
                                <strong><?php echo htmlspecialchars($ticketLookup['ticket_code'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            </div>
                            <div class="meta-chip">
                                <span>Tiêu đề</span>
                                <strong><?php echo htmlspecialchars($ticketLookup['subject'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            </div>
                            <div class="meta-chip">
                                <span>Người gửi</span>
                                <strong><?php echo htmlspecialchars($ticketLookup['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            </div>
                            <div class="meta-chip">
                                <span>Trạng thái</span>
                                <strong class="status-pill <?php echo htmlspecialchars(contactStatusClass($ticketLookup['status']), ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars(contactStatusLabel($ticketLookup['status']), ENT_QUOTES, 'UTF-8'); ?>
                                </strong>
                            </div>
                            <div class="meta-chip">
                                <span>Mức độ</span>
                                <strong class="priority-pill <?php echo htmlspecialchars(contactPriorityClass($ticketLookup['priority']), ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars(contactPriorityLabel($ticketLookup['priority']), ENT_QUOTES, 'UTF-8'); ?>
                                </strong>
                            </div>
                            <div class="meta-chip">
                                <span>Cập nhật gần nhất</span>
                                <strong><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime((string) $ticketLookup['updated_at'])), ENT_QUOTES, 'UTF-8'); ?></strong>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="contact-grid">
                    <div class="contact-info-box">
                        <h3>Thông tin liên hệ</h3>
                        <div class="info-item">
                            <strong>Địa chỉ:</strong>
                            <p>Xã Long Hiệp, Tỉnh Vĩnh Long</p>
                        </div>
                        <div class="info-item">
                            <strong>Điện thoại:</strong>
                            <p>Đang cập nhật</p>
                        </div>
                        <div class="info-item">
                            <strong>Email:</strong>
                            <p>ubnd@longhiep.gov.vn</p>
                        </div>
                        <div class="info-item">
                            <strong>Giờ làm việc:</strong>
                            <p>Thứ 2 - Thứ 6: 7h30 - 11h30, 13h30 - 17h00</p>
                            <p>Thứ 7: 7h30 - 11h30</p>
                        </div>
                    </div>

                    <div class="contact-form-box">
                        <h3>Gửi ticket liên hệ</h3>
                        <form action="process-contact.php" method="POST" class="contact-form">
                            <div class="form-group">
                                <label for="name">Họ và tên *</label>
                                <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($oldContact['name'], ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($oldContact['email'], ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="form-group">
                                <label for="phone">Số điện thoại</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($oldContact['phone'], ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="form-group">
                                <label for="priority">Mức độ ưu tiên</label>
                                <select id="priority" name="priority">
                                    <?php foreach (getContactPriorityOptions() as $priorityValue => $priorityOption): ?>
                                        <option value="<?php echo htmlspecialchars($priorityValue, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($oldContact['priority'] ?? 'normal') === $priorityValue ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($priorityOption['label'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="subject">Tiêu đề *</label>
                                <input type="text" id="subject" name="subject" required value="<?php echo htmlspecialchars($oldContact['subject'], ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="form-group">
                                <label for="message">Nội dung *</label>
                                <textarea id="message" name="message" rows="5" required><?php echo htmlspecialchars($oldContact['message'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                            </div>
                            <button type="submit" class="btn-submit">Gửi tin nhắn</button>
                        </form>
                    </div>
                </div>

                <div class="map-section">
                    <h3>Bản đồ vị trí</h3>
                    <div class="map-container">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3929.5!2d105.95!3d10.05!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTDCsDAzJzAwLjAiTiAxMDXCsDU3JzAwLjAiRQ!5e0!3m2!1svi!2s!4v1234567890"
                            width="100%"
                            height="450"
                            style="border:0; border-radius: 10px;"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                        <div class="map-info">
                            <p><strong>📍 Xã Long Hiệp, Tỉnh Vĩnh Long</strong></p>
                            <a href="https://www.google.com/maps/dir/?api=1&destination=10.05,105.95" target="_blank" class="btn-directions">
                                🧭 Chỉ đường đến đây
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
