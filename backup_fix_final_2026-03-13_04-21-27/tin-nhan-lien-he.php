<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';
require_once 'contact-message-helper.php';

authRequireRole(['admin']);

$isLoggedIn = authIsLoggedIn();
$currentRole = authCurrentRole();
$displayName = authDisplayName();

$statusFilter = trim((string) ($_GET['status'] ?? 'all'));
$priorityFilter = trim((string) ($_GET['priority'] ?? 'all'));
$searchKeyword = trim((string) ($_GET['search'] ?? ''));
$currentPage = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;

$conn = getContactStorageConnection();
if (!$conn || !ensureContactsTableExists($conn)) {
    die('Không thể kết nối cơ sở dữ liệu.');
}

$conditions = ['1=1'];
$params = [];
$types = '';

if ($statusFilter !== 'all') {
    $conditions[] = 'status = ?';
    $params[] = $statusFilter;
    $types .= 's';
}

if ($priorityFilter !== 'all') {
    $conditions[] = 'priority = ?';
    $params[] = $priorityFilter;
    $types .= 's';
}

if ($searchKeyword !== '') {
    $searchValue = '%' . $searchKeyword . '%';
    $conditions[] = '(name LIKE ? OR email LIKE ? OR subject LIKE ? OR ticket_code LIKE ?)';
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    $types .= 'ssss';
}

$whereSql = implode(' AND ', $conditions);

$countSql = "SELECT COUNT(*) AS total FROM contacts WHERE {$whereSql}";
$countStmt = $conn->prepare($countSql);
if ($types !== '' && !empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalContacts = (int) $countStmt->get_result()->fetch_assoc()['total'];
$countStmt->close();

$totalPages = max(1, (int) ceil($totalContacts / $perPage));
$offset = ($currentPage - 1) * $perPage;

$listSql = "SELECT * FROM contacts WHERE {$whereSql} ORDER BY created_at DESC LIMIT ?, ?";
$listParams = $params;
$listParams[] = $offset;
$listParams[] = $perPage;
$listTypes = $types . 'ii';

$stmt = $conn->prepare($listSql);
if ($listTypes !== '' && !empty($listParams)) {
    $stmt->bind_param($listTypes, ...$listParams);
}
$stmt->execute();
$contacts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

$stats = getContactStats();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý tin nhắn liên hệ - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css?v=2.1">
    <link rel="stylesheet" href="contact-messages-style.css?v=1.0">
    <script src="dropdown.js"></script>
</head>
<body>
        <?php include 'menu-don-gian.php'; ?>

    <main>
        <section class="page-header">
            <div class="container">
                <h2>Quản lý tin nhắn liên hệ</h2>
                <p>Theo dõi và xử lý phản ánh từ người dân</p>
            </div>
        </section>

        <section class="contact-management">
            <div class="container">
                <div class="stats-grid">
                    <div class="stat-card stat-card--total">
                        <div class="stat-number"><?php echo $stats['total']; ?></div>
                        <div class="stat-label">Tổng tin nhắn</div>
                    </div>
                    <div class="stat-card stat-card--new">
                        <div class="stat-number"><?php echo $stats['new']; ?></div>
                        <div class="stat-label">Chưa xử lý</div>
                    </div>
                    <div class="stat-card stat-card--processing">
                        <div class="stat-number"><?php echo $stats['processing']; ?></div>
                        <div class="stat-label">Đang xử lý</div>
                    </div>
                    <div class="stat-card stat-card--resolved">
                        <div class="stat-number"><?php echo $stats['resolved']; ?></div>
                        <div class="stat-label">Đã giải quyết</div>
                    </div>
                </div>
                <div class="filters-card">
                    <h3>Bộ lọc và tìm kiếm</h3>
                    <form method="GET" class="filters-form">
                        <div class="filters-form__row">
                            <div class="field-span-3">
                                <label for="status">Trạng thái</label>
                                <select id="status" name="status">
                                    <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>Tất cả</option>
                                    <option value="new" <?php echo $statusFilter === 'new' ? 'selected' : ''; ?>>Mới</option>
                                    <option value="processing" <?php echo $statusFilter === 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                                    <option value="resolved" <?php echo $statusFilter === 'resolved' ? 'selected' : ''; ?>>Đã giải quyết</option>
                                </select>
                            </div>
                            <div class="field-span-3">
                                <label for="priority">Mức độ</label>
                                <select id="priority" name="priority">
                                    <option value="all" <?php echo $priorityFilter === 'all' ? 'selected' : ''; ?>>Tất cả</option>
                                    <option value="low" <?php echo $priorityFilter === 'low' ? 'selected' : ''; ?>>Thấp</option>
                                    <option value="normal" <?php echo $priorityFilter === 'normal' ? 'selected' : ''; ?>>Bình thường</option>
                                    <option value="high" <?php echo $priorityFilter === 'high' ? 'selected' : ''; ?>>Cao</option>
                                    <option value="urgent" <?php echo $priorityFilter === 'urgent' ? 'selected' : ''; ?>>Khẩn cấp</option>
                                </select>
                            </div>
                            <div class="field-span-6">
                                <label for="search">Tìm kiếm</label>
                                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($searchKeyword, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Tên, email, tiêu đề hoặc mã phiếu">
                            </div>
                        </div>
                        <div class="filters-actions">
                            <button type="submit" class="filters-button">Áp dụng bộ lọc</button>
                            <a href="tin-nhan-lien-he.php" class="ghost-button">Xóa bộ lọc</a>
                        </div>
                    </form>
                </div>

                <div class="results-bar">
                    <div class="results-count"><?php echo $totalContacts; ?> tin nhắn</div>
                    <div class="results-meta">Trang <?php echo $currentPage; ?> / <?php echo $totalPages; ?></div>
                </div>
                <?php if (count($contacts) > 0): ?>
                    <div class="contacts-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Mã phiếu</th>
                                    <th>Người gửi</th>
                                    <th>Tiêu đề</th>
                                    <th>Mức độ</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày gửi</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contacts as $contact): ?>
                                    <tr>
                                        <td><code><?php echo htmlspecialchars($contact['ticket_code'], ENT_QUOTES, 'UTF-8'); ?></code></td>
                                        <td>
                                            <div class="contact-info">
                                                <strong><?php echo htmlspecialchars($contact['name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                <small><?php echo htmlspecialchars($contact['email'], ENT_QUOTES, 'UTF-8'); ?></small>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($contact['subject'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <span class="priority-pill <?php echo htmlspecialchars(contactPriorityClass($contact['priority']), ENT_QUOTES, 'UTF-8'); ?>">
                                                <?php echo htmlspecialchars(contactPriorityLabel($contact['priority']), ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-pill <?php echo htmlspecialchars(contactStatusClass($contact['status']), ENT_QUOTES, 'UTF-8'); ?>">
                                                <?php echo htmlspecialchars(contactStatusLabel($contact['status']), ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($contact['created_at'])); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button onclick="viewContact(<?php echo $contact['id']; ?>)" class="btn-view">👁️ Xem</button>
                                                <button onclick="updateStatus(<?php echo $contact['id']; ?>, '<?php echo $contact['status']; ?>')" class="btn-status">🔄 Cập nhật</button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php
                            $baseUrl = 'tin-nhan-lien-he.php?';
                            $queryParams = [];
                            if ($statusFilter !== 'all') $queryParams['status'] = $statusFilter;
                            if ($priorityFilter !== 'all') $queryParams['priority'] = $priorityFilter;
                            if ($searchKeyword !== '') $queryParams['search'] = $searchKeyword;
                            $baseQuery = http_build_query($queryParams);
                            if ($baseQuery) $baseUrl .= $baseQuery . '&';
                            ?>
                            
                            <?php if ($currentPage > 1): ?>
                                <a href="<?php echo $baseUrl; ?>page=<?php echo $currentPage - 1; ?>" class="pagination__link">←</a>
                            <?php endif; ?>

                            <?php for ($page = 1; $page <= $totalPages; $page++): ?>
                                <?php if ($page === $currentPage): ?>
                                    <span class="pagination__current"><?php echo $page; ?></span>
                                <?php else: ?>
                                    <a href="<?php echo $baseUrl; ?>page=<?php echo $page; ?>" class="pagination__link"><?php echo $page; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($currentPage < $totalPages): ?>
                                <a href="<?php echo $baseUrl; ?>page=<?php echo $currentPage + 1; ?>" class="pagination__link">→</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="empty-panel">
                        <p>📭 Không có tin nhắn nào phù hợp với bộ lọc hiện tại.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>

    <script src="contact-management.js?v=1.0"></script>
</body>
</html>