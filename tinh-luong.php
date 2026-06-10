<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'auth.php';
require_once 'config.php';

// Chỉ admin mới được tính lương
authRequireRole(['admin']);

$message = '';
$messageType = '';
$salaryResult = null;

// Lấy danh sách nhân viên từ department_staff
$employees = [];
$conn = getDBConnection();
if ($conn) {
    $result = $conn->query("SELECT ds.id, ds.name, ds.position, ds.department_id, 
                                   COALESCE(ds.basic_salary, 2530000) as basic_salary,
                                   COALESCE(ds.salary_coefficient, 1.0) as salary_coefficient,
                                   d.name as department_name
                            FROM department_staff ds
                            LEFT JOIN departments d ON ds.department_id = d.id
                            WHERE ds.status = 'active'
                            ORDER BY ds.name ASC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $employees[] = $row;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['calculate'])) {
    $employeeId = (int)($_POST['employee_id'] ?? 0);
    $month = (int)($_POST['month'] ?? date('n'));
    $year = (int)($_POST['year'] ?? date('Y'));
    
    // Lương cơ bản
    $basicSalary = (float)($_POST['basic_salary'] ?? 2530000);
    
    // Hệ số lương
    $salaryCoefficient = (float)($_POST['salary_coefficient'] ?? 1.0);
    
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
    
    // Số ngày nghỉ trong tháng
    $daysOff = intval($_POST['days_off'] ?? 0);
    $workingDaysPerMonth = 26;
    
    // Công thức chuẩn: Lương thực nhận = (Lương cơ bản × Hệ số) - [(Lương cơ bản × Hệ số) / 26 × Ngày nghỉ]
    $baseSalaryReal = round($basicSalary * $salaryCoefficient);
    $daysOffDeduction = round(($baseSalaryReal / $workingDaysPerMonth) * $daysOff);
    $actualSalary = $baseSalaryReal - $daysOffDeduction;
    
    // Tính toán tổng
    $totalAllowance = $positionAllowance + $responsibilityAllowance + $regionalAllowance + $otherAllowance;
    $totalBonus = $performanceBonus + $holidayBonus + $otherBonus;
    $totalDeduction = $insurance + $tax + $advance + $otherDeduction;
    
    $grossSalary = $actualSalary + $totalAllowance + $totalBonus;
    $netSalary = $grossSalary - $totalDeduction;
    
    $salaryResult = [
        'employee_id' => $employeeId,
        'month' => $month,
        'year' => $year,
        'basic_salary' => $basicSalary,
        'salary_coefficient' => $salaryCoefficient,
        'base_salary_real' => $baseSalaryReal,
        'days_off' => $daysOff,
        'days_off_deduction' => $daysOffDeduction,
        'actual_salary' => $actualSalary,
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
    
    // Lưu vào database nếu nhấn "Lưu"
    if (isset($_POST['save']) && $conn) {
        $stmt = $conn->prepare("
            UPDATE department_staff 
            SET basic_salary = ?, salary_coefficient = ?
            WHERE id = ? AND status = 'active'
        ");
        $stmt->bind_param('ddi', $basicSalary, $salaryCoefficient, $employeeId);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Lưu bảng lương thành công!';
            header('Location: thong-ke-luong-simple.php?month=' . $month . '&year=' . $year);
            exit();
        } else {
            $message = 'Lỗi khi lưu dữ liệu.';
            $messageType = 'error';
        }
        $stmt->close();
    }
}

if ($conn) $conn->close();

$currentRole = authCurrentRole();
$displayName = authDisplayName();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tính lương cán bộ - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'header-menu.php'; ?>

    <main>
        <div class="admin-container">
            <div class="admin-header">
                <h1>💰 Tính lương cán bộ</h1>
                <div class="admin-actions">
                    <a href="thong-ke-luong-simple.php" class="btn-secondary">← Quay lại</a>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <div class="salary-calculator">
                <form method="POST" class="salary-form" id="salaryForm">
                    <div class="form-section">
                        <h3>📋 Thông tin chung</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="employee_id">Nhân viên *</label>
                                <select id="employee_id" name="employee_id" required onchange="fillEmployeeData(this)">
                                    <option value="">-- Chọn nhân viên --</option>
                                    <?php foreach ($employees as $emp): ?>
                                        <option value="<?php echo $emp['id']; ?>"
                                                data-salary="<?php echo $emp['basic_salary']; ?>"
                                                data-coefficient="<?php echo $emp['salary_coefficient']; ?>"
                                                <?php echo ($salaryResult && $salaryResult['employee_id'] == $emp['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($emp['name'] . ' - ' . $emp['position'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="month">Tháng *</label>
                                <select id="month" name="month" required>
                                    <?php for ($m = 1; $m <= 12; $m++): ?>
                                        <option value="<?php echo $m; ?>" <?php echo ($salaryResult && $salaryResult['month'] == $m) || (!$salaryResult && $m == date('n')) ? 'selected' : ''; ?>>
                                            Tháng <?php echo $m; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="year">Năm *</label>
                                <select id="year" name="year" required>
                                    <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                                        <option value="<?php echo $y; ?>" <?php echo ($salaryResult && $salaryResult['year'] == $y) || (!$salaryResult && $y == date('Y')) ? 'selected' : ''; ?>>
                                            <?php echo $y; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="basic_salary">Lương cơ bản (VNĐ) *</label>
                                <?php if (authIsAdmin()): ?>
                                <input type="number" id="basic_salary" name="basic_salary" step="10000" min="0" required
                                       value="<?php echo $salaryResult ? $salaryResult['basic_salary'] : '2530000'; ?>"
                                       placeholder="2,530,000">
                                <?php else: ?>
                                <input type="number" id="basic_salary" name="basic_salary" step="10000" min="0" required
                                       value="<?php echo $salaryResult ? $salaryResult['basic_salary'] : '2530000'; ?>"
                                       readonly style="background-color: #f5f5f5;">
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="salary_coefficient">Hệ số lương *</label>
                                <?php if (authIsAdmin()): ?>
                                <input type="number" id="salary_coefficient" name="salary_coefficient" step="0.01" min="0" max="10" required
                                       value="<?php echo $salaryResult ? $salaryResult['salary_coefficient'] : '1.0'; ?>"
                                       placeholder="1.0">
                                <?php else: ?>
                                <input type="number" id="salary_coefficient" name="salary_coefficient" step="0.01" min="0" max="10" required
                                       value="<?php echo $salaryResult ? $salaryResult['salary_coefficient'] : '1.0'; ?>"
                                       readonly style="background-color: #f5f5f5;">
                                <?php endif; ?>
                                <small style="color: #666;">Lương thực nhận = (Cơ bản × Hệ số) - [(Cơ bản × Hệ số) / 26 × Ngày nghỉ]</small>
                            </div>

                            <div class="form-group">
                                <label for="days_off">Số ngày nghỉ trong tháng</label>
                                <input type="number" id="days_off" name="days_off" min="0" max="31" step="1"
                                       value="<?php echo $salaryResult ? $salaryResult['days_off'] : '0'; ?>"
                                       placeholder="0">
                                <small style="color: #666;">Công tiêu chuẩn: 26 ngày/tháng</small>
                            </div>

                            <div class="form-group">
                                <label for="month">Tháng *</label>
                                <select id="month" name="month" required>
                                    <?php for ($m = 1; $m <= 12; $m++): ?>
                                        <option value="<?php echo $m; ?>" <?php echo ($salaryResult && $salaryResult['month'] == $m) || (!$salaryResult && $m == date('n')) ? 'selected' : ''; ?>>
                                            Tháng <?php echo $m; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="year">Năm *</label>
                                <select id="year" name="year" required>
                                    <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                                        <option value="<?php echo $y; ?>" <?php echo ($salaryResult && $salaryResult['year'] == $y) || (!$salaryResult && $y == date('Y')) ? 'selected' : ''; ?>>
                                            <?php echo $y; ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="basic_salary">Lương cơ bản *</label>
                                <input type="number" id="salaryInput" name="basic_salary" step="1000" min="0" required
                                       value="<?php echo $salaryResult ? $salaryResult['basic_salary'] : '2530000'; ?>"
                                       placeholder="2,530,000" readonly
                                       style="background-color: #f5f5f5;">
                                <small style="color: #666;">
                                    <?php if (authIsAdmin()): ?>
                                    <button type="button" onclick="toggleSalaryEdit()" style="margin-top:5px; padding:3px 10px; font-size:12px; cursor:pointer; background:#007bff; color:white; border:none; border-radius:3px;">Cho phép sửa</button>
                                    <?php else: ?>
                                    Chỉ admin mới có quyền sửa lương
                                    <?php endif; ?>
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="days_off">Số ngày nghỉ trong tháng</label>
                                <input type="number" id="days_off" name="days_off" min="0" max="31" step="1"
                                       value="<?php echo $salaryResult ? $salaryResult['days_off'] : '0'; ?>"
                                       placeholder="0">
                                <small style="color: #666;">Lương = (Cơ bản / 26) × (26 - Ngày nghỉ)</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>💵 Phụ cấp</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="position_allowance">Phụ cấp chức vụ</label>
                                <input type="number" id="position_allowance" name="position_allowance" step="1000" min="0"
                                       value="<?php echo $salaryResult ? $salaryResult['details']['position_allowance'] : '0'; ?>"
                                       placeholder="500,000">
                            </div>

                            <div class="form-group">
                                <label for="responsibility_allowance">Phụ cấp trách nhiệm</label>
                                <input type="number" id="responsibility_allowance" name="responsibility_allowance" step="1000" min="0"
                                       value="<?php echo $salaryResult ? $salaryResult['details']['responsibility_allowance'] : '0'; ?>"
                                       placeholder="300,000">
                            </div>

                            <div class="form-group">
                                <label for="regional_allowance">Phụ cấp khu vực</label>
                                <input type="number" id="regional_allowance" name="regional_allowance" step="1000" min="0"
                                       value="<?php echo $salaryResult ? $salaryResult['details']['regional_allowance'] : '0'; ?>"
                                       placeholder="200,000">
                            </div>

                            <div class="form-group">
                                <label for="other_allowance">Phụ cấp khác</label>
                                <input type="number" id="other_allowance" name="other_allowance" step="1000" min="0"
                                       value="<?php echo $salaryResult ? $salaryResult['details']['other_allowance'] : '0'; ?>"
                                       placeholder="100,000">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>🎁 Thưởng</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="performance_bonus">Thưởng hiệu suất</label>
                                <input type="number" id="performance_bonus" name="performance_bonus" step="1000" min="0"
                                       value="<?php echo $salaryResult ? $salaryResult['details']['performance_bonus'] : '0'; ?>"
                                       placeholder="1,000,000">
                            </div>

                            <div class="form-group">
                                <label for="holiday_bonus">Thưởng lễ/tết</label>
                                <input type="number" id="holiday_bonus" name="holiday_bonus" step="1000" min="0"
                                       value="<?php echo $salaryResult ? $salaryResult['details']['holiday_bonus'] : '0'; ?>"
                                       placeholder="500,000">
                            </div>

                            <div class="form-group">
                                <label for="other_bonus">Thưởng khác</label>
                                <input type="number" id="other_bonus" name="other_bonus" step="1000" min="0"
                                       value="<?php echo $salaryResult ? $salaryResult['details']['other_bonus'] : '0'; ?>"
                                       placeholder="200,000">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>📉 Khấu trừ</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="insurance">Bảo hiểm (BHXH, BHYT, BHTN)</label>
                                <input type="number" id="insurance" name="insurance" step="1000" min="0"
                                       value="<?php echo $salaryResult ? $salaryResult['details']['insurance'] : '0'; ?>"
                                       placeholder="500,000">
                            </div>

                            <div class="form-group">
                                <label for="tax">Thuế TNCN</label>
                                <input type="number" id="tax" name="tax" step="1000" min="0"
                                       value="<?php echo $salaryResult ? $salaryResult['details']['tax'] : '0'; ?>"
                                       placeholder="300,000">
                            </div>

                            <div class="form-group">
                                <label for="advance">Tạm ứng</label>
                                <input type="number" id="advance" name="advance" step="1000" min="0"
                                       value="<?php echo $salaryResult ? $salaryResult['details']['advance'] : '0'; ?>"
                                       placeholder="1,000,000">
                            </div>

                            <div class="form-group">
                                <label for="other_deduction">Khấu trừ khác</label>
                                <input type="number" id="other_deduction" name="other_deduction" step="1000" min="0"
                                       value="<?php echo $salaryResult ? $salaryResult['details']['other_deduction'] : '0'; ?>"
                                       placeholder="100,000">
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="calculate" class="btn-primary">
                            <i class="fas fa-calculator"></i> Tính lương
                        </button>
                        <?php if ($salaryResult): ?>
                            <button type="submit" name="save" class="btn-success">
                                <i class="fas fa-save"></i> Lưu bảng lương
                            </button>
                        <?php endif; ?>
                        <a href="thong-ke-luong-simple.php" class="btn-secondary">Hủy</a>
                    </div>
                </form>

                <?php if ($salaryResult): ?>
                    <div class="salary-result">
                        <h3>📊 Kết quả tính lương</h3>
                        <div class="result-grid">
                            <div class="result-item">
                                <span class="label">Lương cơ bản:</span>
                                <span class="value"><?php echo number_format($salaryResult['basic_salary'], 0, ',', '.'); ?> đ</span>
                            </div>
                            <div class="result-item">
                                <span class="label">Hệ số lương:</span>
                                <span class="value"><?php echo $salaryResult['salary_coefficient']; ?></span>
                            </div>
                            <div class="result-item">
                                <span class="label">Lương cơ bản × Hệ số:</span>
                                <span class="value"><?php echo number_format($salaryResult['base_salary_real'], 0, ',', '.'); ?> đ</span>
                            </div>
                            <div class="result-item">
                                <span class="label">Số ngày nghỉ:</span>
                                <span class="value" style="<?php echo $salaryResult['days_off'] > 0 ? 'color: #ef4444;' : ''; ?>"><?php echo $salaryResult['days_off']; ?> ngày</span>
                            </div>
                            <div class="result-item">
                                <span class="label">Trừ ngày nghỉ:</span>
                                <span class="value negative">-<?php echo number_format($salaryResult['days_off_deduction'], 0, ',', '.'); ?> đ</span>
                            </div>
                            <div class="result-item">
                                <span class="label">Lương thực nhận:</span>
                                <span class="value"><?php echo number_format($salaryResult['actual_salary'], 0, ',', '.'); ?> đ</span>
                            </div>
                            <div class="result-item">
                                <span class="label">Tổng phụ cấp:</span>
                                <span class="value positive">+<?php echo number_format($salaryResult['total_allowance'], 0, ',', '.'); ?> đ</span>
                            </div>
                            <div class="result-item">
                                <span class="label">Tổng thưởng:</span>
                                <span class="value positive">+<?php echo number_format($salaryResult['total_bonus'], 0, ',', '.'); ?> đ</span>
                            </div>
                            <div class="result-item highlight">
                                <span class="label">Tổng thu nhập:</span>
                                <span class="value"><?php echo number_format($salaryResult['gross_salary'], 0, ',', '.'); ?> đ</span>
                            </div>
                            <div class="result-item">
                                <span class="label">Tổng khấu trừ:</span>
                                <span class="value negative">-<?php echo number_format($salaryResult['total_deduction'], 0, ',', '.'); ?> đ</span>
                            </div>
                            <div class="result-item highlight success">
                                <span class="label"><strong>Lương thực nhận:</strong></span>
                                <span class="value"><strong><?php echo number_format($salaryResult['net_salary'], 0, ',', '.'); ?> đ</strong></span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <style>
    .salary-calculator {
        background: white;
        border-radius: 10px;
        padding: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .form-section {
        margin-bottom: 30px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .form-section h3 {
        margin: 0 0 20px 0;
        color: #2c3e50;
        font-size: 18px;
        border-bottom: 2px solid var(--primary);
        padding-bottom: 10px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-weight: 600;
        margin-bottom: 8px;
        color: #2c3e50;
        font-size: 14px;
    }

    .form-group input,
    .form-group select {
        padding: 10px;
        border: 2px solid #e9ecef;
        border-radius: 5px;
        font-size: 14px;
        font-family: inherit;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--primary);
    }

    .form-actions {
        display: flex;
        gap: 10px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 2px solid #e9ecef;
    }

    .btn-success {
        background: #28a745;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-success:hover {
        background: #218838;
    }

    .salary-result {
        margin-top: 30px;
        padding: 25px;
        background: var(--gradient-primary);
        border-radius: 10px;
        color: white;
    }

    .salary-result h3 {
        margin: 0 0 20px 0;
        font-size: 20px;
    }

    .result-grid {
        display: grid;
        gap: 15px;
    }

    .result-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 15px;
        background: rgba(255,255,255,0.1);
        border-radius: 5px;
    }

    .result-item.highlight {
        background: rgba(255,255,255,0.2);
        font-size: 16px;
    }

    .result-item.success {
        background: rgba(40,167,69,0.3);
        border: 2px solid rgba(255,255,255,0.5);
    }

    .result-item .label {
        font-weight: 500;
    }

    .result-item .value {
        font-weight: 600;
        font-size: 16px;
    }

    .result-item .value.positive {
        color: #90EE90;
    }

    .result-item .value.negative {
        color: #FFB6C1;
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
        
        .form-actions {
            flex-direction: column;
        }
    }
    </style>

    <script>
    // Auto fill salary data when selecting employee
    function fillEmployeeData(select) {
        var option = select.options[select.selectedIndex];
        if (option.value) {
            var salary = option.getAttribute('data-salary');
            var coefficient = option.getAttribute('data-coefficient');
            if (salary) document.getElementById('basic_salary').value = salary;
            if (coefficient) document.getElementById('salary_coefficient').value = coefficient;
        }
    }

    // Auto format số tiền khi nhập
    document.querySelectorAll('input[type="number"]').forEach(input => {
        input.addEventListener('blur', function() {
            if (this.value) {
                this.value = Math.round(parseFloat(this.value) * 100) / 100;
            }
        });
    });
    </script>

    <?php include 'footer.php'; ?>
    <?php include 'quick-actions-toolbar.php'; ?>
</body>
</html>
