<?php
header("Content-Type: text/html; charset=utf-8");
session_start();
require_once 'config.php';
require_once 'auth.php';

// Kiểm tra quyền admin
if (!authIsLoggedIn() || !authIsAdmin()) {
    header("Location: dang-nhap.php");
    exit;
}

$currentRole = authCurrentRole();
$displayName = authDisplayName();
$userId = authCurrentUserId();

$message = '';
$messageType = '';

// Xử lý phê duyệt/từ chối
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $targetUserId = (int)($_POST['user_id'] ?? 0);
    $action = $_POST['action'];
    $reason = trim($_POST['reason'] ?? '');
    
    if ($targetUserId > 0 && in_array($action, ['approve', 'reject'])) {
        try {
            $conn = getDBConnection();
            
            if ($action == 'approve') {
                $stmt = $conn->prepare("UPDATE users SET approval_status = 'approved', approved_by = ?, approved_at = NOW(), rejection_reason = NULL WHERE id = ?");
                $stmt->bind_param("ii", $userId, $targetUserId);
                
                if ($stmt->execute()) {
                    $message = "Đã phê duyệt tài khoản thành công!";
                    $messageType = "success";
                } else {
                    $message = "Lỗi khi phê duyệt.";
                    $messageType = "error";
                }
            } else {
                if (empty($reason)) {
                    $message = "Vui lòng nhập lý do từ chối!";
                    $messageType = "error";
                } else {
                    $stmt = $conn->prepare("UPDATE users SET approval_status = 'rejected', approved_by = ?, approved_at = NOW(), rejection_reason = ? WHERE id = ?");
                    $stmt->bind_param("isi", $userId, $reason, $targetUserId);
                    
                    if ($stmt->execute()) {
                        $message = "Đã từ chối tài khoản!";
                        $messageType = "success";
                    } else {
                        $message = "Lỗi khi từ chối.";
                        $messageType = "error";
                    }
                }
            }
            
            $stmt->close();
            $conn->close();
        } catch (Exception $e) {
            $message = "Lỗi hệ thống";
            $messageType = "error";
        }
    }
}

// Lấy danh sách tài khoản chờ duyệt
$pendingUsers = [];
$approvedUsers = [];
$rejectedUsers = [];

try {
    $conn = getDBConnection();
    
    // Tài khoản chờ duyệt
    $stmt = $conn->prepare("SELECT id, username, full_name, email, role, user_type, created_at FROM users WHERE approval_status = 'pending' ORDER BY created_at ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $pendingUsers[] = $row;
    }
    $stmt->close();
    
    // Tài khoản đã duyệt (10 gần nhất)
    $stmt = $conn->prepare("SELECT u.id, u.username, u.full_name, u.email, u.role, u.user_type, u.approved_at, a.full_name as approved_by_name FROM users u LEFT JOIN users a ON u.approved_by = a.id WHERE u.approval_status = 'approved' ORDER BY u.approved_at DESC LIMIT 10");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $approvedUsers[] = $row;
    }
    $stmt->close();
    
    // Tài khoản bị từ chối (10 gần nhất)
    $stmt = $conn->prepare("SELECT u.id, u.username, u.full_name, u.email, u.role, u.user_type, u.approved_at, u.rejection_reason, a.full_name as approved_by_name FROM users u LEFT JOIN users a ON u.approved_by = a.id WHERE u.approval_status = 'rejected' ORDER BY u.approved_at DESC LIMIT 10");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $rejectedUsers[] = $row;
    }
    $stmt->close();
    
    $conn->close();
} catch (Exception $e) {
    $message = "Lỗi khi tải dữ liệu.";
    $messageType = "error";
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý phê duyệt tài khoản - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard-style.css">
</head>
<body>
    <?php include 'header-menu.php'; ?>

    <main>
        <div class="admin-container">
            <div class="admin-header">
                <h1>✅ Quản lý phê duyệt tài khoản</h1>
                <div class="admin-actions">
                    <a href="dashboard.php" class="btn-secondary">← Quay lại Dashboard</a>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <!-- Tài khoản chờ duyệt -->
            <div class="approval-section">
                <h2>⏳ Tài khoản chờ phê duyệt (<?php echo count($pendingUsers); ?>)</h2>
                
                <?php if (empty($pendingUsers)): ?>
                    <div class="empty-state">
                        <p>✅ Không có tài khoản nào chờ phê duyệt</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tên đăng nhập</th>
                                    <th>Họ tên</th>
                                    <th>Email</th>
                                    <th>Loại người dùng</th>
                                    <th>Vai trò</th>
                                    <th>Ngày đăng ký</th>
                                    <th>Thời gian chờ</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingUsers as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                        <td><?php echo htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $user['user_type'] ?? 'nguoi_dan'; ?>">
                                                <?php echo ($user['user_type'] ?? 'nguoi_dan') === 'can_bo' ? '👔 Cán bộ' : '👤 Người dân'; ?>
                                            </span>
                                        </td>
                                        <td><span class="badge badge-<?php echo $user['role']; ?>"><?php echo authRoleLabel($user['role']); ?></span></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <?php 
                                            $hours = floor((time() - strtotime($user['created_at'])) / 3600);
                                            echo $hours . ' giờ';
                                            ?>
                                        </td>
                                        <td class="action-buttons">
                                            <button onclick="approveUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?>')" class="btn-approve">
                                                ✅ Phê duyệt
                                            </button>
                                            <button onclick="showRejectModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?>')" class="btn-reject">
                                                ❌ Từ chối
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tài khoản đã duyệt -->
            <div class="approval-section">
                <h2>✅ Tài khoản đã phê duyệt (10 gần nhất)</h2>
                
                <?php if (empty($approvedUsers)): ?>
                    <div class="empty-state">
                        <p>Chưa có tài khoản nào được phê duyệt</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tên đăng nhập</th>
                                    <th>Họ tên</th>
                                    <th>Email</th>
                                    <th>Loại người dùng</th>
                                    <th>Vai trò</th>
                                    <th>Người duyệt</th>
                                    <th>Ngày duyệt</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($approvedUsers as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $user['user_type'] ?? 'nguoi_dan'; ?>">
                                                <?php echo ($user['user_type'] ?? 'nguoi_dan') === 'can_bo' ? '👔 Cán bộ' : '👤 Người dân'; ?>
                                            </span>
                                        </td>
                                        <td><span class="badge badge-<?php echo $user['role']; ?>"><?php echo authRoleLabel($user['role']); ?></span></td>
                                        <td><?php echo htmlspecialchars($user['approved_by_name'] ?? 'Hệ thống', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo $user['approved_at'] ? date('d/m/Y H:i', strtotime($user['approved_at'])) : '-'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tài khoản bị từ chối -->
            <div class="approval-section">
                <h2>❌ Tài khoản bị từ chối (10 gần nhất)</h2>
                
                <?php if (empty($rejectedUsers)): ?>
                    <div class="empty-state">
                        <p>Chưa có tài khoản nào bị từ chối</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tên đăng nhập</th>
                                    <th>Họ tên</th>
                                    <th>Email</th>
                                    <th>Loại người dùng</th>
                                    <th>Lý do từ chối</th>
                                    <th>Người từ chối</th>
                                    <th>Ngày từ chối</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rejectedUsers as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($user['full_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $user['user_type'] ?? 'nguoi_dan'; ?>">
                                                <?php echo ($user['user_type'] ?? 'nguoi_dan') === 'can_bo' ? '👔 Cán bộ' : '👤 Người dân'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['rejection_reason'] ?? 'Không rõ', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo htmlspecialchars($user['approved_by_name'] ?? 'Hệ thống', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?php echo $user['approved_at'] ? date('d/m/Y H:i', strtotime($user['approved_at'])) : '-'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Modal từ chối -->
    <div id="rejectModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeRejectModal()">&times;</span>
            <h3>Từ chối tài khoản</h3>
            <form method="POST" id="rejectForm">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="user_id" id="reject_user_id">
                <p>Bạn có chắc muốn từ chối tài khoản <strong id="reject_username"></strong>?</p>
                <div class="form-group">
                    <label for="reason">Lý do từ chối *</label>
                    <textarea name="reason" id="reason" rows="4" required placeholder="Nhập lý do từ chối..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn-reject">❌ Từ chối</button>
                    <button type="button" onclick="closeRejectModal()" class="btn-secondary">Hủy</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function approveUser(userId, username) {
        if (confirm('Bạn có chắc muốn phê duyệt tài khoản "' + username + '"?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="user_id" value="${userId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function showRejectModal(userId, username) {
        document.getElementById('reject_user_id').value = userId;
        document.getElementById('reject_username').textContent = username;
        document.getElementById('rejectModal').style.display = 'block';
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').style.display = 'none';
        document.getElementById('reason').value = '';
    }

    // Đóng modal khi click bên ngoài
    window.onclick = function(event) {
        const modal = document.getElementById('rejectModal');
        if (event.target == modal) {
            closeRejectModal();
        }
    }
    </script>

    <style>
    .approval-section {
        background: white;
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .approval-section h2 {
        color: #2c3e50;
        margin-bottom: 20px;
        font-size: 20px;
    }

    .empty-state {
        text-align: center;
        padding: 40px;
        color: #666;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th,
    .data-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #e9ecef;
    }

    .data-table th {
        background: #f8f9fa;
        font-weight: 600;
        color: #2c3e50;
    }

    .data-table tr:hover {
        background: #f8f9fa;
    }

    .badge {
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-admin {
        background: #dc3545;
        color: white;
    }

    .badge-editor {
        background: #007bff;
        color: white;
    }

    .badge-viewer {
        background: #6c757d;
        color: white;
    }

    .badge-can_bo {
        background: #17a2b8;
        color: white;
    }

    .badge-nguoi_dan {
        background: #6c757d;
        color: white;
    }

    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .btn-approve {
        background: #28a745;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 13px;
    }

    .btn-approve:hover {
        background: #218838;
    }

    .btn-reject {
        background: #dc3545;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 13px;
    }

    .btn-reject:hover {
        background: #c82333;
    }

    .modal {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
    }

    .modal-content {
        background-color: white;
        margin: 10% auto;
        padding: 30px;
        border-radius: 10px;
        width: 90%;
        max-width: 500px;
        position: relative;
    }

    .close {
        position: absolute;
        right: 15px;
        top: 15px;
        font-size: 28px;
        font-weight: bold;
        color: #aaa;
        cursor: pointer;
    }

    .close:hover {
        color: #000;
    }

    .modal-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #2c3e50;
    }

    .form-group textarea {
        width: 100%;
        padding: 10px;
        border: 2px solid #e9ecef;
        border-radius: 4px;
        font-family: inherit;
    }

    .form-group textarea:focus {
        outline: none;
        border-color: #007bff;
    }

    @media (max-width: 768px) {
        .action-buttons {
            flex-direction: column;
        }
        
        .data-table {
            font-size: 14px;
        }
        
        .data-table th,
        .data-table td {
            padding: 8px;
        }
    }
    </style>

    <?php include 'footer.php'; ?>
    <?php include 'quick-actions-toolbar.php'; ?>
</body>
</html>
