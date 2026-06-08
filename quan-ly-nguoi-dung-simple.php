<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config.php';
require_once 'auth.php';

// Kiểm tra đăng nhập
if (!authIsLoggedIn()) {
    die("Bạn chưa đăng nhập. <a href='dang-nhap.php'>Đăng nhập</a>");
}

// Chỉ admin và editor mới được truy cập
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'editor') {
    die("Bạn không có quyền truy cập trang này.");
}

$is_admin = ($_SESSION['role'] === 'admin');

// Lấy danh sách người dùng
$sql = "SELECT id, username, full_name, email, role, status FROM users ORDER BY created_at DESC LIMIT 20";
$result = $conn->query($sql);

if (!$result) {
    die("Lỗi hệ thống.");
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý người dùng</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        h1 { color: #2c5f2d; }
        table { width: 100%; background: white; border-collapse: collapse; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #2c5f2d; color: white; }
        tr:hover { background: #f8f9fa; }
        .badge { padding: 4px 12px; border-radius: 12px; font-size: 12px; }
        .badge-admin { background: #dc3545; color: white; }
        .badge-editor { background: #ffc107; color: #333; }
        .badge-viewer { background: #6c757d; color: white; }
        .badge-active { background: #d4edda; color: #155724; }
        .badge-inactive { background: #f8d7da; color: #721c24; }
        .info { background: #d1ecf1; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>👥 Quản lý người dùng</h1>
    
    <div class="info">
        <strong>Thông tin đăng nhập:</strong><br>
        User ID: <?php echo $_SESSION['user_id']; ?><br>
        Username: <?php echo $_SESSION['username']; ?><br>
        Role: <?php echo $_SESSION['role']; ?><br>
        Quyền: <?php echo $is_admin ? 'Admin (Toàn quyền)' : 'Editor (Chỉ xem)'; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên đăng nhập</th>
                <th>Họ và tên</th>
                <th>Email</th>
                <th>Vai trò</th>
                <th>Trạng thái</th>
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
                            <span class="badge badge-<?php echo $user['status']; ?>">
                                <?php echo $user['status'] === 'active' ? 'Hoạt động' : 'Khóa'; ?>
                            </span>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px;">Không có người dùng nào.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <p style="margin-top: 20px;">
        <a href="dashboard.php" style="padding: 10px 20px; background: #2c5f2d; color: white; text-decoration: none; border-radius: 4px;">← Quay lại Dashboard</a>
    </p>
</body>
</html>
<?php
$conn->close();
?>
