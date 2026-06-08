<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config.php';
require_once 'auth.php';

// Kiểm tra đăng nhập và quyền
try {
    authRequireCanBo();
} catch (Exception $e) {
    header('Location: index.php');
    exit();
}

$page_title = "Thống kê lương cán bộ";

// Kiểm tra database connection
if (!isset($conn) || $conn->connect_error) {
    die('<div style="padding: 20px; background: #f8d7da; color: #721c24; border-radius: 5px; margin: 20px;">
        <h3>Lỗi kết nối database</h3>
        <p>Không thể kết nối đến database. Vui lòng kiểm tra cấu hình.</p>
        </div>');
}

// Kiểm tra bảng có tồn tại không
$table_check = $conn->query("SHOW TABLES LIKE 'hr_employee_salaries'");
if (!$table_check || $table_check->num_rows == 0) {
    echo '<div style="padding: 20px; background: #fff3cd; color: #856404; border-radius: 5px; margin: 20px;">
        <h3>⚠️ Chưa có bảng lương</h3>
        <p>Hệ thống chưa được khởi tạo. Vui lòng tạo bảng lương trước.</p>
        <p><a href="run-create-salary-system.php" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">→ Tạo hệ thống lương</a></p>
        <p><a href="thong-ke-luong-simple.php" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">→ Xem thống kê đơn giản</a></p>
        <p><a href="test-thong-ke-luong.php" style="background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">→ Test hệ thống</a></p>
        </div>';
    exit();
}

// Lấy dữ liệu thống kê với xử lý lỗi
$stats_query = "
    SELECT 
        COUNT(*) as total_employees,
        COALESCE(SUM(total_salary), 0) as total_salary_budget,
        COALESCE(AVG(total_salary), 0) as average_salary,
        COALESCE(MIN(total_salary), 0) as min_salary,
        COALESCE(MAX(total_salary), 0) as max_salary
    FROM hr_employee_salaries 
    WHERE status = 'active'
";

$stats_result = $conn->query($stats_query);
if (!$stats_result) {
    echo '<div style="padding: 20px; background: #f8d7da; color: #721c24; border-radius: 5px; margin: 20px;">
        <h3>Lỗi truy vấn thống kê</h3>
        <p>Lỗi hệ thống</p>
        <p><a href="thong-ke-luong-simple.php">→ Xem thống kê đơn giản</a></p>
        </div>';
    exit();
}
$stats = $stats_result->fetch_assoc();

// Kiểm tra có dữ liệu không
if ($stats['total_employees'] == 0) {
    echo '<div style="padding: 20px; background: #d1ecf1; color: #0c5460; border-radius: 5px; margin: 20px;">
        <h3>📊 Chưa có dữ liệu lương</h3>
        <p>Hệ thống chưa có dữ liệu lương cán bộ.</p>
        <p><a href="run-create-salary-system.php" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">→ Tạo dữ liệu mẫu</a></p>
        <p><a href="thong-ke-luong-simple.php" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">→ Xem thống kê đơn giản</a></p>
        </div>';
    exit();
}

// Thống kê theo phòng ban
$dept_query = "
    SELECT 
        department,
        COUNT(*) as employee_count,
        SUM(total_salary) as dept_total_salary,
        AVG(total_salary) as dept_avg_salary
    FROM hr_employee_salaries 
    WHERE status = 'active'
    GROUP BY department
    ORDER BY dept_total_salary DESC
";

$dept_result = $conn->query($dept_query);

// Thống kê theo chức vụ
$position_query = "
    SELECT 
        position,
        COUNT(*) as position_count,
        AVG(total_salary) as position_avg_salary,
        MIN(total_salary) as position_min_salary,
        MAX(total_salary) as position_max_salary
    FROM hr_employee_salaries 
    WHERE status = 'active'
    GROUP BY position
    ORDER BY position_avg_salary DESC
";

$position_result = $conn->query($position_query);

// Lấy danh sách lương chi tiết
$detail_query = "
    SELECT 
        employee_name,
        employee_code,
        department,
        position,
        base_salary,
        position_allowance,
        responsibility_allowance,
        seniority_allowance,
        regional_allowance,
        other_allowances,
        total_salary
    FROM hr_employee_salaries 
    WHERE status = 'active'
    ORDER BY total_salary DESC
";

$detail_result = $conn->query($detail_query);

include 'header-menu.php';
?>

<link rel="stylesheet" href="hr-style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
.salary-stats {
    max-width: 1400px;
    margin: 30px auto;
    padding: 0 20px;
}

.page-header {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: white;
    padding: 30px;
    border-radius: 10px;
    margin-bottom: 30px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    text-align: center;
    border-left: 4px solid #2563eb;
}

.stat-value {
    font-size: 28px;
    font-weight: 700;
    color: #2563eb;
    margin-bottom: 8px;
}

.stat-label {
    color: #6b7280;
    font-size: 14px;
    font-weight: 500;
}

.chart-section {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.chart-section h3 {
    margin: 0 0 20px 0;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 10px;
}

.dept-chart {
    display: grid;
    gap: 15px;
}

.dept-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #f9fafb;
    border-radius: 8px;
    border-left: 4px solid #2563eb;
}

.dept-info {
    flex: 1;
}

.dept-name {
    font-weight: 600;
    color: #374151;
    margin-bottom: 5px;
}

.dept-stats {
    font-size: 14px;
    color: #6b7280;
}

.dept-salary {
    text-align: right;
}

.dept-total {
    font-size: 18px;
    font-weight: 700;
    color: #2563eb;
}

.dept-avg {
    font-size: 14px;
    color: #6b7280;
}

.detail-table {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.detail-table table {
    width: 100%;
    border-collapse: collapse;
}

.detail-table th {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: white;
    padding: 15px 12px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
}

.detail-table td {
    padding: 12px;
    border-bottom: 1px solid #e5e7eb;
    font-size: 14px;
}

.detail-table tbody tr:hover {
    background: #f9fafb;
}

.salary-amount {
    font-weight: 600;
    color: #059669;
}

.employee-info {
    display: flex;
    flex-direction: column;
}

.employee-name {
    font-weight: 600;
    color: #374151;
}

.employee-code {
    font-size: 12px;
    color: #6b7280;
}

.position-badge {
    display: inline-block;
    padding: 4px 8px;
    background: #dbeafe;
    color: #1d4ed8;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .detail-table {
        overflow-x: auto;
    }
    
    .dept-item {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<div class="salary-stats">
    <div class="page-header">
        <h1><i class="fas fa-chart-bar"></i> Thống kê lương cán bộ</h1>
        <p>Tổng hợp thông tin lương và phụ cấp của toàn bộ cán bộ UBND xã Long Hiệp</p>
    </div>

    <!-- Thống kê tổng quan -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?php echo number_format($stats['total_employees']); ?></div>
            <div class="stat-label">Tổng số cán bộ</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo number_format($stats['total_salary_budget']); ?></div>
            <div class="stat-label">Tổng quỹ lương (VNĐ)</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo number_format($stats['average_salary']); ?></div>
            <div class="stat-label">Lương trung bình (VNĐ)</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo number_format($stats['min_salary']); ?></div>
            <div class="stat-label">Lương thấp nhất (VNĐ)</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo number_format($stats['max_salary']); ?></div>
            <div class="stat-label">Lương cao nhất (VNĐ)</div>
        </div>
    </div>

    <!-- Thống kê theo phòng ban -->
    <div class="chart-section">
        <h3><i class="fas fa-building"></i> Thống kê theo phòng ban</h3>
        <div class="dept-chart">
            <?php while ($dept = $dept_result->fetch_assoc()): ?>
            <div class="dept-item">
                <div class="dept-info">
                    <div class="dept-name"><?php echo $dept['department']; ?></div>
                    <div class="dept-stats">
                        <?php echo $dept['employee_count']; ?> cán bộ • 
                        Trung bình: <?php echo number_format($dept['dept_avg_salary']); ?> VNĐ
                    </div>
                </div>
                <div class="dept-salary">
                    <div class="dept-total"><?php echo number_format($dept['dept_total_salary']); ?> VNĐ</div>
                    <div class="dept-avg">Tổng quỹ lương</div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Thống kê theo chức vụ -->
    <div class="chart-section">
        <h3><i class="fas fa-users"></i> Thống kê theo chức vụ</h3>
        <div class="detail-table">
            <table>
                <thead>
                    <tr>
                        <th>Chức vụ</th>
                        <th>Số lượng</th>
                        <th>Lương trung bình</th>
                        <th>Lương thấp nhất</th>
                        <th>Lương cao nhất</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($pos = $position_result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <span class="position-badge"><?php echo $pos['position']; ?></span>
                        </td>
                        <td><?php echo $pos['position_count']; ?> người</td>
                        <td class="salary-amount"><?php echo number_format($pos['position_avg_salary']); ?> VNĐ</td>
                        <td><?php echo number_format($pos['position_min_salary']); ?> VNĐ</td>
                        <td><?php echo number_format($pos['position_max_salary']); ?> VNĐ</td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bảng lương chi tiết -->
    <div class="chart-section">
        <h3><i class="fas fa-table"></i> Bảng lương chi tiết</h3>
        <div class="detail-table">
            <table>
                <thead>
                    <tr>
                        <th>Cán bộ</th>
                        <th>Phòng ban</th>
                        <th>Chức vụ</th>
                        <th>Lương cơ bản</th>
                        <th>Phụ cấp chức vụ</th>
                        <th>Phụ cấp trách nhiệm</th>
                        <th>Phụ cấp thâm niên</th>
                        <th>Phụ cấp khu vực</th>
                        <th>Phụ cấp khác</th>
                        <th>Tổng lương</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($emp = $detail_result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div class="employee-info">
                                <span class="employee-name"><?php echo $emp['employee_name']; ?></span>
                                <span class="employee-code"><?php echo $emp['employee_code']; ?></span>
                            </div>
                        </td>
                        <td><?php echo $emp['department']; ?></td>
                        <td>
                            <span class="position-badge"><?php echo $emp['position']; ?></span>
                        </td>
                        <td><?php echo number_format($emp['base_salary']); ?></td>
                        <td><?php echo number_format($emp['position_allowance']); ?></td>
                        <td><?php echo number_format($emp['responsibility_allowance']); ?></td>
                        <td><?php echo number_format($emp['seniority_allowance']); ?></td>
                        <td><?php echo number_format($emp['regional_allowance']); ?></td>
                        <td><?php echo number_format($emp['other_allowances']); ?></td>
                        <td class="salary-amount"><?php echo number_format($emp['total_salary']); ?> VNĐ</td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Nút hành động -->
    <div style="text-align: center; margin-top: 30px;">
        <a href="quan-ly-luong-thuong.php" class="btn btn-primary">
            <i class="fas fa-cog"></i> Quản lý lương thưởng
        </a>
        <a href="dashboard.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại Dashboard
        </a>
    </div>
</div>

<script>
// Thêm hiệu ứng số đếm cho các thống kê
document.addEventListener('DOMContentLoaded', function() {
    const statValues = document.querySelectorAll('.stat-value');
    
    statValues.forEach(stat => {
        const finalValue = parseInt(stat.textContent.replace(/,/g, ''));
        let currentValue = 0;
        const increment = finalValue / 50;
        
        const counter = setInterval(() => {
            currentValue += increment;
            if (currentValue >= finalValue) {
                stat.textContent = finalValue.toLocaleString();
                clearInterval(counter);
            } else {
                stat.textContent = Math.floor(currentValue).toLocaleString();
            }
        }, 30);
    });
});
</script>

<?php
include 'footer.php';
?>