<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config.php';
require_once 'auth.php';

// Kiểm tra đăng nhập
try {
    authRequireCanBo();
} catch (Exception $e) {
    header('Location: index.php');
    exit();
}

$isReadOnly = authIsReadOnly();
$page_title = "Bảng lương cán bộ";

// Lấy dữ liệu từ database
$salary_data = [];
$conn_salary = getDBConnection();

if ($conn_salary) {
    // Lấy dữ liệu từ bảng department_staff join với departments
    $query = "SELECT 
                ds.id,
                ds.name,
                ds.position,
                d.name as department,
                COALESCE(ds.basic_salary, 5000000) as salary,
                COALESCE(ds.salary_coefficient, 0) as attendance_score,
                ds.status
              FROM department_staff ds
              LEFT JOIN departments d ON ds.department_id = d.id
              WHERE ds.status = 'active'
              ORDER BY ds.basic_salary DESC";
    
    $result = $conn_salary->query($query);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $salary_data[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'position' => $row['position'],
                'department' => $row['department'] ?? 'Chưa phân công',
                'salary' => $row['salary'],
                'base_salary' => $row['salary'],
                'attendance_score' => $row['attendance_score']
            ];
        }
    }
    
    $conn_salary->close();
}

// Sắp xếp danh sách theo lương cơ bản
usort($salary_data, function($a, $b) {
    $salary_a = $a['base_salary'] ?? $a['salary'] ?? 0;
    $salary_b = $b['base_salary'] ?? $b['salary'] ?? 0;
    return $salary_b - $salary_a;
});

// Xử lý tính lương mới
$salaryResult = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['calculate_salary'])) {
    $employeeName = $_POST['employee_name'] ?? '';
    $month = (int)($_POST['month'] ?? date('n'));
    $year = (int)($_POST['year'] ?? date('Y'));
    
    // Lương cơ bản
    $basicSalary = (float)($_POST['basic_salary'] ?? 0);
    
    // Phụ cấp
    $positionAllowance = (float)($_POST['position_allowance'] ?? 0);
    $responsibilityAllowance = (float)($_POST['responsibility_allowance'] ?? 0);
    $regionalAllowance = (float)($_POST['regional_allowance'] ?? 0);
    $otherAllowance = (float)($_POST['other_allowance'] ?? 0);
    
    // Thưởng
    $performanceBonus = (float)($_POST['performance_bonus'] ?? 0);
    $holidayBonus = (float)($_POST['holiday_bonus'] ?? 0);
    $otherBonus = (float)($_POST['other_bonus'] ?? 0);
    
    // Khấu trừ
    $insurance = (float)($_POST['insurance'] ?? 0);
    $tax = (float)($_POST['tax'] ?? 0);
    $advance = (float)($_POST['advance'] ?? 0);
    $otherDeduction = (float)($_POST['other_deduction'] ?? 0);
    
    // Tính toán
    $totalAllowance = $positionAllowance + $responsibilityAllowance + $regionalAllowance + $otherAllowance;
    $totalBonus = $performanceBonus + $holidayBonus + $otherBonus;
    $totalDeduction = $insurance + $tax + $advance + $otherDeduction;
    
    $grossSalary = $basicSalary + $totalAllowance + $totalBonus;
    $netSalary = $grossSalary - $totalDeduction;
    
    $salaryResult = [
        'employee_name' => $employeeName,
        'month' => $month,
        'year' => $year,
        'basic_salary' => $basicSalary,
        'total_allowance' => $totalAllowance,
        'total_bonus' => $totalBonus,
        'gross_salary' => $grossSalary,
        'total_deduction' => $totalDeduction,
        'net_salary' => $netSalary,
        'details' => [
            'position_allowance' => $positionAllowance,
            'responsibility_allowance' => $responsibilityAllowance,
            'regional_allowance' => $regionalAllowance,
            'other_allowance' => $otherAllowance,
            'performance_bonus' => $performanceBonus,
            'holiday_bonus' => $holidayBonus,
            'other_bonus' => $otherBonus,
            'insurance' => $insurance,
            'tax' => $tax,
            'advance' => $advance,
            'other_deduction' => $otherDeduction
        ]
    ];
}

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

.dept-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #f9fafb;
    border-radius: 8px;
    border-left: 4px solid #2563eb;
    margin-bottom: 15px;
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

.salary-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.salary-table th {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    color: white;
    padding: 15px 12px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
}

.salary-table th:nth-child(1) {
    width: 60px;
    text-align: center;
}

.salary-table th:nth-child(5) {
    width: 150px;
    text-align: right;
}

.salary-table th:nth-child(6) {
    width: 80px;
    text-align: center;
}

.salary-table th:nth-child(7) {
    width: 150px;
    text-align: right;
}

.salary-table th:nth-child(8) {
    width: 150px;
    text-align: center;
}

.salary-table td {
    padding: 12px;
    border-bottom: 1px solid #e5e7eb;
    vertical-align: middle;
}

.salary-table td:nth-child(1) {
    text-align: center;
    font-weight: 600;
}

.salary-table td:nth-child(5) {
    text-align: right;
}

.salary-table td:nth-child(6) {
    text-align: center;
}

.salary-table td:nth-child(7) {
    text-align: right;
}

.salary-table tbody tr:hover {
    background: #f9fafb;
}

.salary-amount {
    font-weight: 600;
    color: #059669;
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

.btn {
    display: inline-block;
    padding: 10px 20px;
    background: #2563eb;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    margin: 5px;
}

.btn:hover {
    background: #1d4ed8;
}

.btn-secondary {
    background: #6b7280;
}

.btn-secondary:hover {
    background: #4b5563;
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

.btn-warning {
    background: #ffc107;
    color: #333;
}

.btn-warning:hover {
    background: #e0a800;
}
</style>

<!-- Modal sửa lương -->
<div id="modalSuaLuong" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; overflow-y: auto;">
    <div style="max-width: 500px; margin: 100px auto; background: white; border-radius: 10px; padding: 30px; position: relative;">
        <button onclick="dongModal()" style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">&times;</button>
        
        <h2 style="margin: 0 0 20px 0; color: #2563eb;">
            <i class="fas fa-edit"></i> Sửa lương cán bộ
        </h2>
        
        <form id="formSuaLuong" style="display: flex; flex-direction: column; gap: 15px;">
            <input type="hidden" id="employeeIndex" name="employee_index">
            
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Họ và tên:</label>
                <input type="text" id="employeeName" readonly style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; background: #f5f5f5;">
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Lương cơ bản mới (VNĐ):</label>
                <input type="number" id="newBaseSalary" name="new_base_salary" required min="0" step="10000" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" placeholder="Nhập mức lương cơ bản mới...">
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Số chấm:</label>
                <input type="number" id="newAttendanceScore" name="new_attendance_score" required min="0" max="100" step="1" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" placeholder="Nhập số chấm (0-100)...">
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 10px;">
                <button type="submit" style="flex: 1; padding: 12px; background: #2563eb; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;">
                    <i class="fas fa-save"></i> Lưu thay đổi
                </button>
                <button type="button" onclick="dongModal()" style="flex: 1; padding: 12px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;">
                    <i class="fas fa-times"></i> Hủy
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function suaLuong(index, name, currentBaseSalary, currentScore) {
    document.getElementById('employeeIndex').value = index;
    document.getElementById('employeeName').value = name;
    document.getElementById('newBaseSalary').value = currentBaseSalary;
    document.getElementById('newAttendanceScore').value = currentScore;
    document.getElementById('modalSuaLuong').style.display = 'block';
}

function dongModal() {
    document.getElementById('modalSuaLuong').style.display = 'none';
}

document.getElementById('formSuaLuong').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const index = document.getElementById('employeeIndex').value;
    const newBaseSalary = document.getElementById('newBaseSalary').value;
    const newAttendanceScore = document.getElementById('newAttendanceScore').value;
    const name = document.getElementById('employeeName').value;
    
    // Gửi request cập nhật lương
    const formData = new FormData();
    formData.append('action', 'update_salary');
    formData.append('employee_index', index);
    formData.append('new_base_salary', newBaseSalary);
    formData.append('new_attendance_score', newAttendanceScore);
    
    fetch('api-salary-simple.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Cập nhật lương thành công cho ' + name + '!');
            dongModal();
            location.reload();
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Lỗi khi cập nhật lương');
    });
});

// Đóng modal khi click bên ngoài
window.onclick = function(event) {
    if (event.target.id === 'modalSuaLuong') {
        dongModal();
    }
}
</script>

<div class="salary-stats">
    <div class="page-header">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h1><i class="fas fa-money-bill-wave"></i> Bảng lương cán bộ</h1>
                <p>Danh sách lương của toàn bộ cán bộ UBND xã Long Hiệp</p>
            </div>
            <div>
                <button onclick="moFormTinhLuong()" class="btn" style="background: #10b981; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-calculator"></i> Tính lương mới
                </button>
            </div>
        </div>
    </div>

    <!-- Form tính lương mới -->
    <div id="formTinhLuongSection" style="display: <?php echo $salaryResult ? 'block' : 'none'; ?>; margin-bottom: 30px;">
        <div class="chart-section">
            <h3 style="color: #10b981;"><i class="fas fa-calculator"></i> Tính lương cán bộ</h3>
            
            <form method="POST" style="margin-top: 20px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
                    <!-- Thông tin chung -->
                    <div style="grid-column: 1 / -1; background: #f0f9ff; padding: 15px; border-radius: 8px;">
                        <h4 style="margin: 0 0 15px 0; color: #2563eb;">📋 Thông tin chung</h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 5px;">Nhân viên *</label>
                                <select name="employee_name" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                                    <option value="">-- Chọn nhân viên --</option>
                                    <?php foreach ($salary_data as $emp): ?>
                                        <option value="<?php echo htmlspecialchars($emp['name']); ?>" 
                                                <?php echo ($salaryResult && $salaryResult['employee_name'] == $emp['name']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($emp['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 5px;">Tháng *</label>
                                <select name="month" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                                    <?php for ($m = 1; $m <= 12; $m++): ?>
                                        <option value="<?php echo $m; ?>" <?php echo ($salaryResult && $salaryResult['month'] == $m) || (!$salaryResult && $m == date('n')) ? 'selected' : ''; ?>>
                                            Tháng <?php echo $m; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 5px;">Năm *</label>
                                <select name="year" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                                    <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                                        <option value="<?php echo $y; ?>" <?php echo ($salaryResult && $salaryResult['year'] == $y) || (!$salaryResult && $y == date('Y')) ? 'selected' : ''; ?>>
                                            <?php echo $y; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 5px;">Lương cơ bản *</label>
                                <input type="number" name="basic_salary" step="10000" min="0" required
                                       value="<?php echo $salaryResult ? $salaryResult['basic_salary'] : '5000000'; ?>"
                                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;"
                                       placeholder="5,000,000">
                            </div>
                        </div>
                    </div>

                    <!-- Phụ cấp -->
                    <div style="grid-column: 1 / -1; background: #f0fdf4; padding: 15px; border-radius: 8px;">
                        <h4 style="margin: 0 0 15px 0; color: #10b981;">💵 Phụ cấp</h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 5px;">Phụ cấp chức vụ</label>
                                <input type="number" name="position_allowance" step="10000" min="0"
                                       value="<?php echo $salaryResult ? $salaryResult['details']['position_allowance'] : '0'; ?>"
                                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;"
                                       placeholder="500,000">
                            </div>
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 5px;">Phụ cấp trách nhiệm</label>
                                <input type="number" name="responsibility_allowance" step="10000" min="0"
                                       value="<?php echo $salaryResult ? $salaryResult['details']['responsibility_allowance'] : '0'; ?>"
                                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;"
                                       placeholder="300,000">
                            </div>
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 5px;">Phụ cấp khu vực</label>
                                <input type="number" name="regional_allowance" step="10000" min="0"
                                       value="<?php echo $salaryResult ? $salaryResult['details']['regional_allowance'] : '0'; ?>"
                                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;"
                                       placeholder="200,000">
                            </div>
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 5px;">Phụ cấp khác</label>
                                <input type="number" name="other_allowance" step="10000" min="0"
                                       value="<?php echo $salaryResult ? $salaryResult['details']['other_allowance'] : '0'; ?>"
                                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;"
                                       placeholder="100,000">
                            </div>
                        </div>
                    </div>

                    <!-- Thưởng -->
                    <div style="grid-column: 1 / -1; background: #fef3c7; padding: 15px; border-radius: 8px;">
                        <h4 style="margin: 0 0 15px 0; color: #f59e0b;">🎁 Thưởng</h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 5px;">Thưởng hiệu suất</label>
                                <input type="number" name="performance_bonus" step="10000" min="0"
                                       value="<?php echo $salaryResult ? $salaryResult['details']['performance_bonus'] : '0'; ?>"
                                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;"
                                       placeholder="1,000,000">
                            </div>
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 5px;">Thưởng lễ/tết</label>
                                <input type="number" name="holiday_bonus" step="10000" min="0"
                                       value="<?php echo $salaryResult ? $salaryResult['details']['holiday_bonus'] : '0'; ?>"
                                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;"
                                       placeholder="500,000">
                            </div>
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 5px;">Thưởng khác</label>
                                <input type="number" name="other_bonus" step="10000" min="0"
                                       value="<?php echo $salaryResult ? $salaryResult['details']['other_bonus'] : '0'; ?>"
                                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;"
                                       placeholder="200,000">
                            </div>
                        </div>
                    </div>

                    <!-- Khấu trừ -->
                    <div style="grid-column: 1 / -1; background: #fee2e2; padding: 15px; border-radius: 8px;">
                        <h4 style="margin: 0 0 15px 0; color: #ef4444;">📉 Khấu trừ</h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 5px;">Bảo hiểm</label>
                                <input type="number" name="insurance" step="10000" min="0"
                                       value="<?php echo $salaryResult ? $salaryResult['details']['insurance'] : '0'; ?>"
                                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;"
                                       placeholder="500,000">
                            </div>
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 5px;">Thuế TNCN</label>
                                <input type="number" name="tax" step="10000" min="0"
                                       value="<?php echo $salaryResult ? $salaryResult['details']['tax'] : '0'; ?>"
                                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;"
                                       placeholder="300,000">
                            </div>
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 5px;">Tạm ứng</label>
                                <input type="number" name="advance" step="10000" min="0"
                                       value="<?php echo $salaryResult ? $salaryResult['details']['advance'] : '0'; ?>"
                                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;"
                                       placeholder="1,000,000">
                            </div>
                            <div>
                                <label style="display: block; font-weight: 600; margin-bottom: 5px;">Khấu trừ khác</label>
                                <input type="number" name="other_deduction" step="10000" min="0"
                                       value="<?php echo $salaryResult ? $salaryResult['details']['other_deduction'] : '0'; ?>"
                                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;"
                                       placeholder="100,000">
                            </div>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; justify-content: center;">
                    <button type="submit" name="calculate_salary" class="btn" style="background: #2563eb;">
                        <i class="fas fa-calculator"></i> Tính lương
                    </button>
                    <button type="button" onclick="dongFormTinhLuong()" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Đóng
                    </button>
                </div>
            </form>

            <?php if ($salaryResult): ?>
                <div style="margin-top: 30px; padding: 25px; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); border-radius: 10px; color: white;">
                    <h3 style="margin: 0 0 20px 0; font-size: 20px;"><i class="fas fa-chart-line"></i> Kết quả tính lương - <?php echo htmlspecialchars($salaryResult['employee_name']); ?></h3>
                    <div style="display: grid; gap: 12px;">
                        <div style="display: flex; justify-content: space-between; padding: 10px 15px; background: rgba(255,255,255,0.1); border-radius: 5px;">
                            <span>Lương cơ bản:</span>
                            <span style="font-weight: 600;"><?php echo number_format($salaryResult['basic_salary'], 0, ',', '.'); ?> đ</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 10px 15px; background: rgba(255,255,255,0.1); border-radius: 5px;">
                            <span>Tổng phụ cấp:</span>
                            <span style="font-weight: 600; color: #90EE90;">+<?php echo number_format($salaryResult['total_allowance'], 0, ',', '.'); ?> đ</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 10px 15px; background: rgba(255,255,255,0.1); border-radius: 5px;">
                            <span>Tổng thưởng:</span>
                            <span style="font-weight: 600; color: #90EE90;">+<?php echo number_format($salaryResult['total_bonus'], 0, ',', '.'); ?> đ</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 12px 15px; background: rgba(255,255,255,0.2); border-radius: 5px; font-size: 16px;">
                            <span style="font-weight: 600;">Tổng thu nhập:</span>
                            <span style="font-weight: 700;"><?php echo number_format($salaryResult['gross_salary'], 0, ',', '.'); ?> đ</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 10px 15px; background: rgba(255,255,255,0.1); border-radius: 5px;">
                            <span>Tổng khấu trừ:</span>
                            <span style="font-weight: 600; color: #FFB6C1;">-<?php echo number_format($salaryResult['total_deduction'], 0, ',', '.'); ?> đ</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 15px; background: rgba(40,167,69,0.3); border: 2px solid rgba(255,255,255,0.5); border-radius: 5px; font-size: 18px;">
                            <span style="font-weight: 700;">Lương thực nhận:</span>
                            <span style="font-weight: 700;"><?php echo number_format($salaryResult['net_salary'], 0, ',', '.'); ?> đ</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bảng lương chi tiết -->
    <div class="chart-section">
        <table class="salary-table">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Họ tên</th>
                    <th>Chức vụ</th>
                    <th>Phòng ban</th>
                    <th>Lương cơ bản (VNĐ)</th>
                    <th>Số chấm</th>
                    <th>Tổng lương (VNĐ)</th>
                    <th style="width: 150px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($salary_data as $index => $emp): 
                    $basicSalary = $emp['salary'] ?? $emp['base_salary'] ?? 0;
                    $attendanceScore = $emp['attendance_score'] ?? 0;
                    // Công thức: Tổng lương = Lương cơ bản × Số chấm
                    $totalSalary = $basicSalary * $attendanceScore;
                ?>
                <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td><strong><?php echo $emp['name']; ?></strong></td>
                    <td>
                        <span class="position-badge"><?php echo $emp['position']; ?></span>
                    </td>
                    <td><?php echo $emp['department']; ?></td>
                    <td class="salary-amount"><?php echo number_format($basicSalary, 0, ',', '.'); ?> VNĐ</td>
                    <td style="text-align: center; font-weight: 600; color: #2563eb; font-size: 16px;">
                        <?php echo $attendanceScore; ?>
                    </td>
                    <td style="text-align: right; font-weight: 700; color: #059669; font-size: 16px;">
                        <?php echo number_format($totalSalary, 0, ',', '.'); ?> VNĐ
                    </td>
                    <td>
                        <button class="btn-sm btn-warning" onclick="suaLuong(<?php echo $index; ?>, '<?php echo htmlspecialchars($emp['name']); ?>', <?php echo $basicSalary; ?>, <?php echo $attendanceScore; ?>)" title="Sửa lương">
                            <i class="fas fa-edit"></i> Sửa
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Nút hành động -->
    <div style="text-align: center; margin-top: 30px;">
        <a href="dashboard.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại Dashboard
        </a>
    </div>
</div>

<?php include 'footer.php'; ?>