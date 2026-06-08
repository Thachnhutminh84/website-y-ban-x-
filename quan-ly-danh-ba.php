<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';

// Kiểm tra quyền admin
authRequireRole(['admin']);

$isLoggedIn = authIsLoggedIn();
$currentRole = authCurrentRole();
$displayName = authDisplayName();

// Kết nối database
$conn = getDBConnection();

// Xử lý thông báo
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);

// Lấy danh sách phòng ban
$departments = [];
$deptResult = $conn->query("SELECT * FROM departments ORDER BY display_order ASC");
while ($row = $deptResult->fetch_assoc()) {
    $departments[] = $row;
}

// Lấy danh sách thành viên
$filter_dept = $_GET['dept'] ?? '';
$search = $_GET['search'] ?? '';

$sql = "SELECT ds.*, d.name as department_name, d.short_name 
        FROM department_staff ds 
        JOIN departments d ON ds.department_id = d.id 
        WHERE 1=1";

if ($filter_dept) {
    $sql .= " AND d.code = '" . $conn->real_escape_string($filter_dept) . "'";
}

if ($search) {
    $search_term = $conn->real_escape_string($search);
    $sql .= " AND (ds.name LIKE '%$search_term%' OR ds.position LIKE '%$search_term%' OR ds.phone LIKE '%$search_term%')";
}

$sql .= " ORDER BY d.display_order ASC, ds.display_order ASC";

$staffResult = $conn->query($sql);
$staffMembers = [];
while ($row = $staffResult->fetch_assoc()) {
    $staffMembers[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Danh bạ - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css?v=2.1">
    <link rel="stylesheet" href="dashboard-style.css?v=1.0">
    <style>
        .management-container {
            max-width: 1400px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .page-header {
            background: var(--gradient-primary);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .page-header h1 {
            margin: 0 0 10px 0;
            font-size: 28px;
        }
        
        .toolbar {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #4CAF50;
            color: white;
        }
        
        .btn-primary:hover {
            background: #45a049;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-edit {
            background: #ffc107;
            color: #000;
            padding: 6px 12px;
            font-size: 13px;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
            padding: 6px 12px;
            font-size: 13px;
        }
        
        .search-box {
            flex: 1;
            min-width: 250px;
        }
        
        .search-box input {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .filter-box select {
            padding: 10px 15px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            min-width: 200px;
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card .number {
            font-size: 36px;
            font-weight: bold;
            color: var(--primary);
            margin: 10px 0;
        }
        
        .stat-card .label {
            color: #666;
            font-size: 14px;
        }
        
        .data-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background: #f8f9fa;
        }
        
        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #dee2e6;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-active {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .actions {
            display: flex;
            gap: 8px;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'header-menu.php'; ?>

    <div class="management-container">
        <div class="page-header">
            <h1>📞 Quản lý Danh bạ Điện thoại</h1>
            <p>Quản lý thông tin liên hệ của cán bộ, nhân viên các phòng ban</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">✅ <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error">❌ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="label">Tổng số thành viên</div>
                <div class="number"><?php echo count($staffMembers); ?></div>
            </div>
            <div class="stat-card">
                <div class="label">Số phòng ban</div>
                <div class="number"><?php echo count($departments); ?></div>
            </div>
            <div class="stat-card">
                <div class="label">Đang hoạt động</div>
                <div class="number"><?php echo count(array_filter($staffMembers, fn($s) => $s['status'] == 'active')); ?></div>
            </div>
        </div>

        <div class="toolbar">
            <a href="them-thanh-vien.php" class="btn btn-primary">
                ➕ Thêm thành viên mới
            </a>
            
            <div class="filter-box">
                <select onchange="window.location.href='?dept=' + this.value + '<?php echo $search ? '&search=' . urlencode($search) : ''; ?>'">
                    <option value="">Tất cả phòng ban</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo htmlspecialchars($dept['code']); ?>" 
                                <?php echo $filter_dept == $dept['code'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept['short_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <form method="GET" class="search-box" style="margin: 0;">
                <?php if ($filter_dept): ?>
                    <input type="hidden" name="dept" value="<?php echo htmlspecialchars($filter_dept); ?>">
                <?php endif; ?>
                <input type="text" name="search" placeholder="🔍 Tìm kiếm theo tên, chức vụ, số điện thoại..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </form>
            
            <a href="danh-ba-dien-thoai.php" class="btn btn-secondary">
                👁️ Xem danh bạ công khai
            </a>
        </div>

        <div class="data-table">
            <?php if (empty($staffMembers)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <h3>Chưa có dữ liệu</h3>
                    <p>Không tìm thấy thành viên nào. Hãy thêm thành viên mới!</p>
                    <a href="them-thanh-vien.php" class="btn btn-primary" style="margin-top: 20px;">
                        ➕ Thêm thành viên đầu tiên
                    </a>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Họ và tên</th>
                            <th>Chức vụ</th>
                            <th>Phòng ban</th>
                            <th>Số điện thoại</th>
                            <th>Email</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($staffMembers as $index => $member): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><strong><?php echo htmlspecialchars($member['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($member['position']); ?></td>
                                <td><?php echo htmlspecialchars($member['short_name']); ?></td>
                                <td><?php echo htmlspecialchars($member['phone']); ?></td>
                                <td><?php echo htmlspecialchars($member['email'] ?? ''); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $member['status']; ?>">
                                        <?php echo $member['status'] == 'active' ? 'Hoạt động' : 'Tạm ẩn'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="sua-thanh-vien.php?id=<?php echo $member['id']; ?>" 
                                           class="btn btn-edit">✏️ Sửa</a>
                                        <a href="xoa-thanh-vien.php?id=<?php echo $member['id']; ?>" 
                                           class="btn btn-delete"
                                           onclick="return confirm('Bạn có chắc muốn xóa <?php echo htmlspecialchars($member['name']); ?>?')">
                                           🗑️ Xóa
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>