<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config.php';
require_once 'auth.php';

// Kiểm tra đăng nhập và quyền truy cập
if (!authIsLoggedIn()) {
    header('Location: dang-nhap.php');
    exit();
}

// Chỉ admin và editor mới được truy cập
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'editor') {
    die("Bạn không có quyền truy cập trang này.");
}

$is_admin = ($_SESSION['role'] === 'admin');

// Xử lý các action
$message = '';
$error = '';

// Xử lý cập nhật trạng thái
if (isset($_POST['action']) && $_POST['action'] === 'toggle_status' && $is_admin) {
    $user_id = (int)$_POST['user_id'];
    $current_status = $_POST['current_status'];
    $new_status = ($current_status === 'active') ? 'inactive' : 'active';
    
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $user_id);
    
    if ($stmt->execute()) {
        $message = "Đã cập nhật trạng thái người dùng thành công!";
    } else {
        $error = "Lỗi khi cập nhật trạng thái.";
    }
    $stmt->close();
}

// Xử lý xóa người dùng
if (isset($_POST['action']) && $_POST['action'] === 'delete' && $is_admin) {
    $user_id = (int)$_POST['user_id'];
    
    // Không cho phép xóa chính mình
    if ($user_id === $_SESSION['user_id']) {
        $error = "Bạn không thể xóa tài khoản của chính mình!";
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $message = "Đã xóa người dùng thành công!";
        } else {
            $error = "Lỗi khi xóa người dùng.";
        }
        $stmt->close();
    }
}

// Lấy danh sách người dùng với phân trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Đếm tổng số người dùng
$count_sql = "SELECT COUNT(*) as total FROM users";
$count_result = $conn->query($count_sql);
$total_users = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_users / $per_page);

// Lấy danh sách người dùng
$sql = "SELECT id, username, full_name, email, role, status, user_type, approval_status, created_at, last_login 
        FROM users 
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();

$page_title = "Quản lý người dùng";
include 'header-menu.php';
?>

<link rel="stylesheet" href="hr-style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
.user-management {
    max-width: 1400px;
    margin: 30px auto;
    padding: 0 20px;
}

.page-header {
    background: var(--gradient-primary);
    color: white;
    padding: 30px;
    border-radius: 10px;
    margin-bottom: 30px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.page-header h1 {
    margin: 0 0 10px 0;
    font-size: 28px;
}

.page-header p {
    margin: 0;
    opacity: 0.9;
}

.page-header .version-link {
    margin-top: 10px;
    font-size: 14px;
}

.page-header .version-link a {
    color: white;
    text-decoration: underline;
    opacity: 0.9;
}

.page-header .version-link a:hover {
    opacity: 1;
}

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    text-align: center;
}

.stat-card .number {
    font-size: 32px;
    font-weight: bold;
    color: var(--primary);
    margin: 10px 0;
}

.stat-card .label {
    color: #666;
    font-size: 14px;
}

.users-table-container {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.table-header {
    padding: 20px;
    border-bottom: 2px solid #f0f0f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.users-table {
    width: 100%;
    border-collapse: collapse;
}

.users-table th,
.users-table td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #f0f0f0;
}

.users-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
}

.users-table tr:hover {
    background: #f8f9fa;
}

.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.badge-admin {
    background: #dc3545;
    color: white;
}

.badge-editor {
    background: #ffc107;
    color: #333;
}

.badge-viewer {
    background: #6c757d;
    color: white;
}

.badge-active {
    background: #d4edda;
    color: #155724;
}

.badge-inactive {
    background: #f8d7da;
    color: #721c24;
}

.badge-can-bo {
    background: #e3f2fd;
    color: #1976d2;
}

.badge-nguoi-dan {
    background: #f3e5f5;
    color: #7b1fa2;
}

.action-buttons {
    display: flex;
    gap: 5px;
}

.btn {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 13px;
    text-decoration: none;
    display: inline-block;
}

.btn-toggle {
    background: #17a2b8;
    color: white;
}

.btn-delete {
    background: #dc3545;
    color: white;
}

.btn:hover {
    opacity: 0.8;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 10px;
    padding: 20px;
}

.pagination a,
.pagination span {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-decoration: none;
    color: #333;
}

.pagination a:hover {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.pagination .current {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.no-permission {
    text-align: center;
    padding: 40px;
    color: #999;
}
</style>

<div class="user-management">
    <div class="page-header">
        <h1>👥 Quản lý người dùng (Đầy đủ chức năng)</h1>
        <p>Quản lý tài khoản và phân quyền người dùng hệ thống</p>
        <p class="version-link">
            <a href="quan-ly-nguoi-dung-simple.php">
                ← Chuyển sang phiên bản đơn giản
            </a>
        </p>
    </div>

    <?php if ($message): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="label">Tổng người dùng</div>
            <div class="number"><?php echo $total_users; ?></div>
        </div>
        <div class="stat-card">
            <div class="label">Trang hiện tại</div>
            <div class="number"><?php echo $page; ?>/<?php echo $total_pages; ?></div>
        </div>
        <div class="stat-card">
            <div class="label">Quyền của bạn</div>
            <div class="number"><?php echo $is_admin ? 'Admin' : 'Editor'; ?></div>
        </div>
    </div>

    <div class="users-table-container">
        <div class="table-header">
            <h3>Danh sách người dùng</h3>
        </div>

        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên đăng nhập</th>
                    <th>Họ và tên</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Loại</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <?php if ($is_admin): ?>
                    <th>Thao tác</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($user = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                            <td><?php echo htmlspecialchars($user['full_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($user['email'] ?? '-'); ?></td>
                            <td>
                                <?php
                                $badge_class = 'badge-' . $user['role'];
                                $role_names = ['admin' => 'Admin', 'editor' => 'Editor', 'viewer' => 'Viewer'];
                                ?>
                                <span class="badge <?php echo $badge_class; ?>">
                                    <?php echo $role_names[$user['role']]; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $user['user_type'] ?? 'nguoi_dan'; ?>">
                                    <?php echo ($user['user_type'] ?? 'nguoi_dan') === 'can_bo' ? 'Cán bộ' : 'Người dân'; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $user['status']; ?>">
                                    <?php echo $user['status'] === 'active' ? 'Hoạt động' : 'Khóa'; ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                            <?php if ($is_admin): ?>
                            <td>
                                <div class="action-buttons">
                                    <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="current_status" value="<?php echo $user['status']; ?>">
                                        <button type="submit" class="btn btn-toggle" onclick="return confirm('Xác nhận thay đổi trạng thái?')">
                                            <?php echo $user['status'] === 'active' ? 'Khóa' : 'Mở khóa'; ?>
                                        </button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-delete" onclick="return confirm('Bạn có chắc muốn xóa người dùng này?')">
                                            Xóa
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <span class="no-permission">-</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?php echo $is_admin ? '9' : '8'; ?>" style="text-align: center; padding: 40px;">
                            Không có người dùng nào.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=1">« Đầu</a>
                <a href="?page=<?php echo $page - 1; ?>">‹ Trước</a>
            <?php endif; ?>

            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?php echo $page + 1; ?>">Sau ›</a>
                <a href="?page=<?php echo $total_pages; ?>">Cuối »</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
$stmt->close();
$conn->close();
include 'footer.php';
?>
