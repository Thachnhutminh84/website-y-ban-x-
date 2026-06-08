<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

authRequireCanBo('index.php');

$isReadOnly = authIsReadOnly(); // Người dân chỉ xem
$conn = getDBConnection();

// Xử lý xóa nhân viên (chỉ khi không phải read-only)
if (!$isReadOnly && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM hr_employees WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $success_message = "Xóa nhân viên thành công!";
    } else {
        $error_message = "Lỗi khi xóa nhân viên!";
    }
    $stmt->close();
}

// Lấy danh sách phòng ban
$departments = [];
$dept_result = $conn->query("SELECT id, name FROM departments ORDER BY name");
if ($dept_result) {
    while ($row = $dept_result->fetch_assoc()) {
        $departments[$row['id']] = $row['name'];
    }
}

// Tìm kiếm và lọc
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$department_filter = isset($_GET['department']) ? (int)$_GET['department'] : 0;
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$where_conditions = ["1=1"];

if ($search) {
    $search_safe = $conn->real_escape_string($search);
    $where_conditions[] = "(full_name LIKE '%$search_safe%' OR employee_code LIKE '%$search_safe%' OR phone LIKE '%$search_safe%')";
}

if ($department_filter > 0) {
    $where_conditions[] = "department_id = $department_filter";
}

if ($status_filter) {
    $status_safe = $conn->real_escape_string($status_filter);
    $where_conditions[] = "status = '$status_safe'";
}

$where_sql = 'WHERE ' . implode(' AND ', $where_conditions);

// Phân trang
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Đếm tổng số
$count_sql = "SELECT COUNT(*) as total FROM hr_employees $where_sql";
$count_result = $conn->query($count_sql);
$total = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total / $per_page);

// Lấy danh sách nhân viên
$sql = "SELECT e.*, d.name as department_name 
        FROM hr_employees e 
        LEFT JOIN departments d ON e.department_id = d.id 
        $where_sql 
        ORDER BY e.created_at DESC 
        LIMIT $per_page OFFSET $offset";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý hồ sơ nhân sự</title>
    <link rel="stylesheet" href="dashboard-style.css">
    <link rel="stylesheet" href="hr-style.css">
</head>
<body>
    <?php include 'header-menu.php'; ?>
    
    <div class="hr-container">
        <?php include 'read-only-notice.php'; ?>
        
        <div class="hr-header">
            <h1>📋 Quản lý hồ sơ nhân sự</h1>
            <?php if (!$isReadOnly): ?>
            <a href="them-nhan-su.php" class="btn btn-primary">➕ Thêm nhân viên mới</a>
            <?php endif; ?>
        </div>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Thống kê -->
        <div class="stats-row">
            <?php
            $stats = [
                'total' => $conn->query("SELECT COUNT(*) as c FROM hr_employees")->fetch_assoc()['c'],
                'active' => $conn->query("SELECT COUNT(*) as c FROM hr_employees WHERE status='active'")->fetch_assoc()['c'],
                'inactive' => $conn->query("SELECT COUNT(*) as c FROM hr_employees WHERE status='inactive'")->fetch_assoc()['c'],
                'resigned' => $conn->query("SELECT COUNT(*) as c FROM hr_employees WHERE status='resigned'")->fetch_assoc()['c']
            ];
            ?>
            <div class="stat-card">
                <h3><?php echo $stats['total']; ?></h3>
                <p>Tổng nhân viên</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['active']; ?></h3>
                <p>Đang làm việc</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['inactive']; ?></h3>
                <p>Tạm nghỉ</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $stats['resigned']; ?></h3>
                <p>Đã nghỉ việc</p>
            </div>
        </div>

        <!-- Bộ lọc -->
        <div class="filter-section">
            <form method="GET" action="">
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Tìm kiếm</label>
                        <input type="text" name="search" placeholder="Tên, mã NV, SĐT..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="filter-group">
                        <label>Phòng ban</label>
                        <select name="department">
                            <option value="">Tất cả</option>
                            <?php foreach ($departments as $id => $name): ?>
                                <option value="<?php echo $id; ?>" <?php echo $department_filter == $id ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Trạng thái</label>
                        <select name="status">
                            <option value="">Tất cả</option>
                            <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Đang làm việc</option>
                            <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Tạm nghỉ</option>
                            <option value="resigned" <?php echo $status_filter == 'resigned' ? 'selected' : ''; ?>>Đã nghỉ việc</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <button type="submit" class="btn btn-primary">🔍 Tìm kiếm</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Bảng danh sách -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Ảnh</th>
                        <th>Mã NV</th>
                        <th>Họ và tên</th>
                        <th>Phòng ban</th>
                        <th>Chức vụ</th>
                        <th>Điện thoại</th>
                        <th>Ngày vào làm</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if ($row['avatar']): ?>
                                        <img src="<?php echo htmlspecialchars($row['avatar']); ?>" class="employee-avatar" alt="Avatar">
                                    <?php else: ?>
                                        <div class="employee-avatar-placeholder">
                                            <?php echo mb_substr($row['full_name'], 0, 1, 'UTF-8'); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo htmlspecialchars($row['employee_code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['department_name'] ?? 'Chưa phân'); ?></td>
                                <td><?php echo htmlspecialchars($row['position'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['phone'] ?? '-'); ?></td>
                                <td><?php echo $row['start_date'] ? date('d/m/Y', strtotime($row['start_date'])) : '-'; ?></td>
                                <td>
                                    <?php
                                    $status_class = 'status-' . $row['status'];
                                    $status_text = [
                                        'active' => 'Đang làm việc',
                                        'inactive' => 'Tạm nghỉ',
                                        'resigned' => 'Đã nghỉ việc'
                                    ];
                                    ?>
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <?php echo $status_text[$row['status']]; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="chi-tiet-nhan-su.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">👁️ Xem</a>
                                        <?php if (!$isReadOnly): ?>
                                        <a href="sua-nhan-su.php?id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm">✏️ Sửa</a>
                                        <a href="?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa nhân viên này?')">🗑️ Xóa</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 40px; color: #7f8c8d;">
                                Không tìm thấy nhân viên nào
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Phân trang -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&department=<?php echo $department_filter; ?>&status=<?php echo $status_filter; ?>" 
                       class="<?php echo $i == $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
<?php
$conn->close();
?>
