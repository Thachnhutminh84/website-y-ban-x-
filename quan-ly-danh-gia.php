<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config.php';
require_once 'auth.php';

// Kiểm tra đăng nhập và quyền
authRequireCanBo();

$page_title = "Quản lý đánh giá hiệu suất";
include 'header-menu.php';

// Hiển thị thông báo nếu có
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Đọc danh sách nhân viên từ database
$employees_data = [];
$conn = getDBConnection();
if ($conn) {
    $result = $conn->query("SELECT ds.id, ds.name as full_name, 
                                   CONCAT('NV', LPAD(ds.id, 3, '0')) as employee_code,
                                   ds.position, 
                                   d.name as department_name
                            FROM department_staff ds
                            LEFT JOIN departments d ON ds.department_id = d.id
                            WHERE ds.status = 'active'
                            ORDER BY d.display_order ASC, ds.position DESC, ds.name ASC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $employees_data[] = $row;
        }
    }
}

$total_employees = count($employees_data);

// Thống kê
$stats = [
    'total_employees' => $total_employees,
    'active_periods' => 0,
    'completed_evaluations' => 0
];
?>

<link rel="stylesheet" href="hr-style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
.evaluation-management {
    max-width: 1400px;
    margin: 30px auto;
    padding: 0 20px;
}

.page-header {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
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

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 20px;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.stat-icon.purple {
    background: #ede9fe;
    color: #8b5cf6;
}

.stat-icon.blue {
    background: #dbeafe;
    color: #3b82f6;
}

.stat-icon.green {
    background: #d1fae5;
    color: #10b981;
}

.stat-content .number {
    font-size: 28px;
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
}

.stat-content .label {
    color: #666;
    font-size: 14px;
}

.evaluation-table-container {
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

.table-header h3 {
    margin: 0;
    color: #333;
}

.btn-primary {
    padding: 10px 20px;
    background: #8b5cf6;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary:hover {
    background: #7c3aed;
}

.evaluation-table {
    width: 100%;
    border-collapse: collapse;
}

.evaluation-table th,
.evaluation-table td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #f0f0f0;
}

.evaluation-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

.evaluation-table tr:hover {
    background: #f8f9fa;
}

.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.badge-excellent {
    background: #d1fae5;
    color: #065f46;
}

.badge-good {
    background: #dbeafe;
    color: #1e40af;
}

.badge-satisfactory {
    background: #fef3c7;
    color: #92400e;
}

.badge-needs-improvement {
    background: #fee2e2;
    color: #991b1b;
}

.action-buttons {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.btn-sm {
    padding: 5px 10px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    text-decoration: none;
    display: inline-block;
}

.btn-info {
    background: #3b82f6;
    color: white;
}

.btn-success {
    background: #10b981;
    color: white;
}

.btn-sm:hover {
    opacity: 0.8;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}

.empty-state i {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.3;
}
</style>

<div class="evaluation-management">
    <?php if ($success_message): ?>
    <div style="background: #d1fae5; border: 2px solid #10b981; color: #065f46; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px;">
        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
    </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
    <div style="background: #fee2e2; border: 2px solid #ef4444; color: #991b1b; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px;">
        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
    </div>
    <?php endif; ?>
    
    <div class="page-header">
        <h1><i class="fas fa-chart-line"></i> Quản lý đánh giá hiệu suất</h1>
        <p>Đánh giá và theo dõi hiệu suất làm việc của nhân viên</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon purple">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <div class="number"><?php echo $stats['total_employees']; ?></div>
                <div class="label">Tổng nhân viên</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-content">
                <div class="number"><?php echo $stats['active_periods']; ?></div>
                <div class="label">Chu kỳ đang diễn ra</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fas fa-clipboard-check"></i>
            </div>
            <div class="stat-content">
                <div class="number"><?php echo $stats['completed_evaluations']; ?></div>
                <div class="label">Đánh giá hoàn thành</div>
            </div>
        </div>
    </div>

    <div class="evaluation-table-container">
        <div class="table-header">
            <h3>Danh sách nhân viên</h3>
            <div>
                <a href="#" class="btn-primary" onclick="alert('Chức năng đang phát triển'); return false;">
                    <i class="fas fa-plus-circle"></i>
                    Tạo chu kỳ đánh giá mới
                </a>
            </div>
        </div>

        <?php if ($total_employees > 0): ?>
        <table class="evaluation-table">
            <thead>
                <tr>
                    <th>Mã NV</th>
                    <th>Họ và tên</th>
                    <th>Phòng ban</th>
                    <th>Chức vụ</th>
                    <th>Đánh giá gần nhất</th>
                    <th>Xếp loại</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Đọc dữ liệu đánh giá từ file JSON
                $evaluation_file = 'data/evaluations.json';
                $all_evaluations = [];
                if (file_exists($evaluation_file)) {
                    $all_evaluations = json_decode(file_get_contents($evaluation_file), true) ?? [];
                }
                
                $rating_labels = [
                    'excellent' => 'Xuất sắc',
                    'good' => 'Tốt',
                    'satisfactory' => 'Đạt',
                    'needs_improvement' => 'Cần cải thiện'
                ];
                
                foreach ($employees_data as $row): 
                    // Tìm đánh giá gần nhất cho nhân viên này
                    $evaluation = null;
                    foreach (array_reverse($all_evaluations) as $eval) {
                        if ($eval['employee_id'] == $row['id']) {
                            $evaluation = $eval;
                            break;
                        }
                    }
                ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($row['employee_code']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['department_name'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($row['position'] ?? '-'); ?></td>
                    <td>
                        <?php if ($evaluation): ?>
                            <?php if (isset($evaluation['eval_month']) && isset($evaluation['eval_year'])): ?>
                                <strong>Tháng <?php echo $evaluation['eval_month']; ?>/<?php echo $evaluation['eval_year']; ?></strong>
                                <br>
                            <?php endif; ?>
                            <small style="color: #666;">Điểm: <?php echo number_format($evaluation['final_score'], 1); ?>/5</small>
                            <?php if (isset($evaluation['days_off']) && $evaluation['days_off'] > 0): ?>
                            <br><small style="color: #ef4444;"><i class="fas fa-calendar-minus"></i> Nghỉ <?php echo $evaluation['days_off']; ?> ngày</small>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color: #999;">Chưa có đánh giá</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($evaluation): ?>
                            <span class="badge badge-<?php echo $evaluation['rating']; ?>">
                                <?php echo $rating_labels[$evaluation['rating']]; ?>
                            </span>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-sm btn-info" onclick="xemLichSuDanhGia(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['full_name']); ?>')" title="Xem lịch sử đánh giá">
                                <i class="fas fa-history"></i> Lịch sử
                            </button>
                            <?php if ($evaluation): ?>
                            <a href="sua-danh-gia.php?id=<?php echo $evaluation['id']; ?>&employee_id=<?php echo $row['id']; ?>" class="btn-sm" style="background: #f59e0b; color: white; text-decoration: none;" title="Sửa đánh giá gần nhất">
                                <i class="fas fa-edit"></i> Sửa
                            </a>
                            <button class="btn-sm" style="background: #dc3545; color: white;" onclick="xoaDanhGia(<?php echo $evaluation['id']; ?>, '<?php echo htmlspecialchars($row['full_name']); ?>')" title="Xóa đánh giá gần nhất">
                                <i class="fas fa-trash"></i> Xóa
                            </button>
                            <?php endif; ?>
                            <button class="btn-sm btn-success" onclick="taoDanhGia(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['full_name']); ?>')" title="Tạo đánh giá mới">
                                <i class="fas fa-plus"></i> <?php echo $evaluation ? 'Đánh giá lại' : 'Đánh giá'; ?>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p>Chưa có dữ liệu nhân viên</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal xem lịch sử đánh giá -->
<div id="modalLichSuDanhGia" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; overflow-y: auto;">
    <div style="max-width: 900px; margin: 50px auto; background: white; border-radius: 10px; padding: 30px; position: relative;">
        <button onclick="dongModal('modalLichSuDanhGia')" style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">&times;</button>
        
        <h2 style="margin: 0 0 20px 0; color: #8b5cf6;">
            <i class="fas fa-history"></i> Lịch sử đánh giá
        </h2>
        <p id="tenNhanVienDanhGia" style="color: #666; margin-bottom: 20px;"></p>
        
        <div id="noiDungLichSuDanhGia">
            <div style="text-align: center; padding: 40px; color: #999;">
                <i class="fas fa-spinner fa-spin" style="font-size: 32px;"></i>
                <p>Đang tải dữ liệu...</p>
            </div>
        </div>
    </div>
</div>

<script>
function xemLichSuDanhGia(employeeId, fullName) {
    document.getElementById('tenNhanVienDanhGia').textContent = 'Nhân viên: ' + fullName;
    document.getElementById('modalLichSuDanhGia').style.display = 'block';
    
    // Lấy dữ liệu lịch sử từ API
    fetch('api-evaluation.php?action=get_employee_evaluations&employee_id=' + employeeId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const lichSu = data.data;
                const ratingLabels = {
                    'excellent': 'Xuất sắc',
                    'good': 'Tốt',
                    'satisfactory': 'Đạt',
                    'needs_improvement': 'Cần cải thiện'
                };
                
                let html = '<table style="width: 100%; border-collapse: collapse;">';
                html += '<thead><tr style="background: #f8f9fa;">';
                html += '<th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Ngày đánh giá</th>';
                html += '<th style="padding: 12px; text-align: center; border-bottom: 2px solid #ddd;">Điểm số</th>';
                html += '<th style="padding: 12px; text-align: center; border-bottom: 2px solid #ddd;">Xếp loại</th>';
                html += '<th style="padding: 12px; text-align: center; border-bottom: 2px solid #ddd;">Chi tiết</th>';
                html += '</tr></thead><tbody>';
                
                if (lichSu.length > 0) {
                    lichSu.forEach(item => {
                        const date = new Date(item.completed_at);
                        const dateStr = date.toLocaleDateString('vi-VN');
                        
                        html += '<tr style="border-bottom: 1px solid #f0f0f0;">';
                        html += '<td style="padding: 12px;">' + dateStr + '</td>';
                        html += '<td style="padding: 12px; text-align: center;"><span style="font-size: 18px; font-weight: bold; color: #8b5cf6;">' + parseFloat(item.final_score).toFixed(1) + '/5</span></td>';
                        html += '<td style="padding: 12px; text-align: center;"><span class="badge badge-' + item.rating + '">' + ratingLabels[item.rating] + '</span></td>';
                        html += '<td style="padding: 12px; text-align: center;"><a href="chi-tiet-danh-gia.php?id=' + item.id + '&employee_id=' + employeeId + '" class="btn-sm btn-info" style="text-decoration: none;"><i class="fas fa-eye"></i> Xem</a></td>';
                        html += '</tr>';
                    });
                } else {
                    html += '<tr><td colspan="4" style="padding: 40px; text-align: center; color: #999;">Chưa có lịch sử đánh giá</td></tr>';
                }
                
                html += '</tbody></table>';
                document.getElementById('noiDungLichSuDanhGia').innerHTML = html;
            } else {
                document.getElementById('noiDungLichSuDanhGia').innerHTML = '<div style="padding: 40px; text-align: center; color: #999;">Lỗi khi tải dữ liệu</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('noiDungLichSuDanhGia').innerHTML = '<div style="padding: 40px; text-align: center; color: #999;">Lỗi khi tải dữ liệu</div>';
        });
}

function taoDanhGia(employeeId, fullName) {
    // Chuyển thẳng đến trang form đánh giá
    window.location.href = 'tao-danh-gia.php?employee_id=' + employeeId + '&employee_name=' + encodeURIComponent(fullName);
}

function xoaDanhGia(evaluationId, fullName) {
    if (confirm('Bạn có chắc chắn muốn xóa đánh giá gần nhất của nhân viên "' + fullName + '" không?\n\nHành động này không thể hoàn tác!')) {
        // Gửi request xóa
        const formData = new FormData();
        formData.append('action', 'delete_evaluation');
        formData.append('evaluation_id', evaluationId);
        
        fetch('api-evaluation.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Lỗi: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Lỗi khi xóa đánh giá');
        });
    }
}

function dongModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Đóng modal khi click bên ngoài
window.onclick = function(event) {
    if (event.target.id === 'modalLichSuDanhGia') {
        dongModal('modalLichSuDanhGia');
    }
}
</script>

<?php
$conn->close();
include 'footer.php';
?>
