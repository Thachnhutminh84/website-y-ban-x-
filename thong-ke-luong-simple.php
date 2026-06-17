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

// Export Excel/CSV
if (isset($_GET['export']) && $_GET['export'] === '1') {
    $export_month = intval($_GET['month'] ?? date('n'));
    $export_year = intval($_GET['year'] ?? date('Y'));

    $export_data = [];
    $conn_exp = getDBConnection();
    $emp_id_filter = intval($_GET['emp_id'] ?? 0);
    if ($conn_exp) {
        $q = "SELECT ds.id, ds.name, ds.position, ds.phone, ds.phone_number, ds.email, d.name as department,
                     COALESCE(ds.basic_salary, 2530000) as salary,
                     COALESCE(ds.salary_coefficient, 1.0) as coeff,
                     ds.status
              FROM department_staff ds
              LEFT JOIN departments d ON ds.department_id = d.id
              WHERE ds.status = 'active'";
        if ($emp_id_filter > 0) {
            $q .= " AND ds.id = " . $emp_id_filter;
        }
        $q .= " ORDER BY ds.basic_salary DESC";
        $r = $conn_exp->query($q);
        if ($r) {
            while ($row = $r->fetch_assoc()) {
                $export_data[] = $row;
            }
        }
        $conn_exp->close();
    }

    $evals = [];
    if (file_exists('data/evaluations.json')) {
        $evals = json_decode(file_get_contents('data/evaluations.json'), true) ?? [];
    }

    $wd = 26;
    foreach ($export_data as &$ex) {
        $ex['days_off'] = 0;
        foreach ($evals as $ev) {
            $en = $ev['employee_name'] ?? '';
            $em = intval($ev['eval_month'] ?? 0);
            $ey = intval($ev['eval_year'] ?? 0);
            if ($en === $ex['name'] || similar_text($en, $ex['name']) > 0.7 * max(strlen($en), strlen($ex['name']))) {
                if ($em == $export_month && $ey == $export_year && isset($ev['days_off'])) {
                    $ex['days_off'] = intval($ev['days_off']);
                }
            }
        }
        $c = floatval($ex['coeff']);
        $base = $ex['salary'] * $c;
        $ded = round(($base / $wd) * $ex['days_off']);
        $ex['base_real'] = round($base);
        $ex['deduction'] = $ded;
        $ex['actual'] = round($base - $ded);
    }
    unset($ex);

    $emp_name_str = '';
    if ($emp_id_filter > 0 && count($export_data) > 0) {
        $emp_name_str = '-' . preg_replace('/[^a-zA-Z0-9]/u', '', $export_data[0]['name']);
    }

    // Them phone/email vao export data
    foreach ($export_data as &$ex) {
        $ex['phone'] = $ex['phone'] ?? $ex['phone_number'] ?? '';
        $ex['email'] = $ex['email'] ?? '';
    }
    unset($ex);

    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="bang-luong-thang-' . $export_month . '-' . $export_year . $emp_name_str . '.xls"');

    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office"
              xmlns:x="urn:schemas-microsoft-com:office:excel"
              xmlns="http://www.w3.org/TR/REC-html40">
              <head>
              <meta charset="utf-8">
              <!--[if gte mso 9]><xml>
              <x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>
              <x:Name>Bảng lương</x:Name>
              <x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>
              </x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml>
              <![endif]-->
              <style>
                  .title { font-size: 18px; font-weight: bold; color: #1d4ed8; }
                  .subtitle { font-size: 13px; color: #555; }
                  .header-row th {
                      background: #2563eb; color: white; font-weight: bold;
                      border: 1px solid #1d4ed8; padding: 8px 10px; text-align: center;
                      font-size: 12px;
                  }
                  .data-row td {
                      border: 1px solid #d1d5db; padding: 7px 10px; font-size: 12px;
                  }
                  .data-row:nth-child(even) { background: #f0f7ff; }
                  .data-row:hover { background: #dbeafe; }
                  .money { text-align: right; mso-number-format:"\\#\\,\\#\\#0"; }
                  .center { text-align: center; }
                  .total-row td {
                      background: #fef3c7; font-weight: bold; border: 1px solid #d97706;
                      padding: 8px 10px; font-size: 13px; color: #92400e;
                  }
              </style></head><body>';
    
    echo '<table cellpadding="0" cellspacing="0" style="border-collapse:collapse; min-width: 900px;">';
    echo '<tr><td colspan="10" class="title" style="padding: 15px 10px 5px;">BẢNG LƯƠNG CÁN BỘ THÁNG ' . $export_month . '/' . $export_year . '</td></tr>';
    echo '<tr><td colspan="10" class="subtitle" style="padding: 0 10px 15px;">UBND xã Long Hiệp, huyện Long Hồ, tỉnh Vĩnh Long</td></tr>';
    
    echo '<tr class="header-row">';
    echo '<th>STT</th><th>Họ tên</th><th>Chức vụ</th><th>Phòng ban</th>';
    echo '<th>Số điện thoại</th><th>Email</th><th>Lương cơ bản</th><th>Hệ số</th><th>Lương CS × Hệ số</th>';
    echo '<th>Ngày nghỉ</th><th>Trừ ngày nghỉ</th><th>Lương thực nhận</th>';
    echo '</tr>';

    $stt = 0;
    $totalActual = 0;
    foreach ($export_data as $ex) {
        $stt++;
        $totalActual += $ex['actual'];
        $rowClass = ($stt % 2 == 0) ? 'even' : 'odd';
        echo '<tr class="data-row">';
        echo '<td class="center">' . $stt . '</td>';
        echo '<td><b>' . htmlspecialchars($ex['name']) . '</b></td>';
        echo '<td>' . htmlspecialchars($ex['position']) . '</td>';
        echo '<td>' . htmlspecialchars($ex['department']) . '</td>';
        echo '<td>' . htmlspecialchars($ex['phone'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($ex['email'] ?? '') . '</td>';
        echo '<td class="money">' . number_format($ex['salary'], 0, ',', '.') . '</td>';
        echo '<td class="center">' . $ex['coeff'] . '</td>';
        echo '<td class="money">' . number_format($ex['base_real'], 0, ',', '.') . '</td>';
        echo '<td class="center">' . $ex['days_off'] . ' ngày</td>';
        echo '<td class="money" style="color:#ef4444;">' . ($ex['deduction'] > 0 ? '-' . number_format($ex['deduction'], 0, ',', '.') : '0') . '</td>';
        echo '<td class="money" style="color:#059669; font-weight:bold;">' . number_format($ex['actual'], 0, ',', '.') . '</td>';
        echo '</tr>';
    }

    echo '<tr class="total-row">';
    echo '<td colspan="11" style="text-align:right;">TỔNG CỘNG</td>';
    echo '<td class="money" style="color:#059669; font-size:14px;">' . number_format($totalActual, 0, ',', '.') . '</td>';
    echo '</tr>';

    echo '</table></body></html>';
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
                ds.phone,
                ds.phone_number,
                ds.email,
                d.name as department,
                d.short_name as dept_short_name,
                COALESCE(ds.basic_salary, 2530000) as salary,
                COALESCE(ds.salary_coefficient, 1.0) as attendance_score,
                ds.status
              FROM department_staff ds
              LEFT JOIN departments d ON ds.department_id = d.id
              WHERE ds.status = 'active'
              ORDER BY ds.basic_salary DESC";
    
    $result = $conn_salary->query($query);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $phone = $row['phone'] ?? $row['phone_number'] ?? '';
            $salary_data[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'position' => $row['position'],
                'department' => $row['department'] ?? 'Chưa phân công',
                'dept_short' => $row['dept_short_name'] ?? '',
                'phone' => $phone,
                'email' => $row['email'] ?? '',
                'salary' => $row['salary'],
                'base_salary' => $row['salary'],
                'salary_coefficient' => $row['attendance_score'],
                'status' => $row['status']
            ];
        }
    }
}

// Lay thong ke so luong can bo theo phong ban (giong danh ba)
$dept_stats = [];
$conn_dept = getDBConnection();
if ($conn_dept) {
    $deptQuery = "SELECT d.name as dept_name, d.short_name, COUNT(ds.id) as member_count,
                  SUM(COALESCE(ds.basic_salary, 2530000)) as total_salary
                  FROM departments d
                  LEFT JOIN department_staff ds ON d.id = ds.department_id AND ds.status = 'active'
                  WHERE d.status = 'active'
                  GROUP BY d.id, d.name, d.short_name
                  HAVING COUNT(ds.id) > 0
                  ORDER BY member_count DESC";
    $deptResult = $conn_dept->query($deptQuery);
    if ($deptResult) {
        while ($row = $deptResult->fetch_assoc()) {
            $dept_stats[] = $row;
        }
    }
}

// Sắp xếp danh sách theo lương cơ bản
usort($salary_data, function($a, $b) {
    $salary_a = $a['base_salary'] ?? $a['salary'] ?? 0;
    $salary_b = $b['base_salary'] ?? $b['salary'] ?? 0;
    return $salary_b - $salary_a;
});

// Đọc dữ liệu đánh giá
$evaluation_file = 'data/evaluations.json';
$all_evaluations = [];
if (file_exists($evaluation_file)) {
    $all_evaluations = json_decode(file_get_contents($evaluation_file), true) ?? [];
}

// Tháng/năm hiện tại (hoặc từ URL)
$current_month = intval($_GET['month'] ?? date('n'));
$current_year = intval($_GET['year'] ?? date('Y'));

// === TỰ ĐỘNG RESET THEO QUÝ ===
// Quý 1: tháng 1-3, Quý 2: tháng 4-6, Quý 3: tháng 7-9, Quý 4: tháng 10-12
$current_quarter = ceil($current_month / 3);
$quarter_months = [
    $current_quarter * 3 - 2,
    $current_quarter * 3 - 1,
    $current_quarter * 3
];

// Kiểm tra quý hiện tại đã có dữ liệu đánh giá chưa
$has_quarter_data = false;
foreach ($all_evaluations as $ev) {
    $evMonth = intval($ev['eval_month'] ?? 0);
    $evYear = intval($ev['eval_year'] ?? 0);
    if ($evYear == $current_year && in_array($evMonth, $quarter_months)) {
        $has_quarter_data = true;
        break;
    }
}

// Nếu chưa có đánh giá quý này → tự động tạo mặc định (0 ngày nghỉ)
if (!$has_quarter_data) {
    $conn_reset = getDBConnection();
    if ($conn_reset) {
        $r = $conn_reset->query("SELECT id, name FROM department_staff WHERE status = 'active'");
        if ($r) {
            $max_id = 0;
            foreach ($all_evaluations as $ev) {
                if (intval($ev['id'] ?? 0) > $max_id) $max_id = intval($ev['id']);
            }

            foreach ($quarter_months as $qm) {
                $new_evals = [];
                $r->data_seek(0);
                while ($row = $r->fetch_assoc()) {
                    $max_id++;
                    $new_evals[] = [
                        'id' => $max_id,
                        'employee_id' => intval($row['id']),
                        'employee_name' => $row['name'],
                        'criteria_scores' => ['quality'=>0,'productivity'=>0,'skills'=>0,'attitude'=>0,'teamwork'=>0],
                        'final_score' => 0,
                        'rating' => 'unrated',
                        'days_off' => 0,
                        'eval_month' => $qm,
                        'eval_year' => $current_year,
                        'strengths' => '',
                        'weaknesses' => '',
                        'recommendations' => '',
                        'manager_comments' => '',
                        'evaluator_name' => '',
                        'evaluator_id' => 0,
                        'status' => 'pending',
                        'created_at' => date('Y-m-d H:i:s'),
                        'completed_at' => ''
                    ];
                }
                $all_evaluations = array_merge($all_evaluations, $new_evals);
            }

            file_put_contents($evaluation_file, json_encode($all_evaluations, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }
        $conn_reset->close();
    }
}

// Gán số ngày nghỉ và tính lương thực nhận cho mỗi nhân viên
$working_days_per_month = 26;
foreach ($salary_data as &$emp) {
    $emp['days_off'] = 0;
    $emp['eval_month'] = $current_month;
    $emp['eval_year'] = $current_year;

    $latest_eval = null;
    foreach ($all_evaluations as $eval) {
        $evalName = $eval['employee_name'] ?? '';
        $evalMonth = intval($eval['eval_month'] ?? 0);
        $evalYear = intval($eval['eval_year'] ?? 0);

        if ($evalName === $emp['name'] || similar_text($evalName, $emp['name']) > 0.7 * max(strlen($evalName), strlen($emp['name']))) {
            if ($evalMonth > 0 && $evalYear > 0) {
                if ($evalMonth == $current_month && $evalYear == $current_year) {
                    if ($latest_eval === null || strtotime($eval['created_at']) > strtotime($latest_eval['created_at'])) {
                        $latest_eval = $eval;
                    }
                }
            } else {
                if ($latest_eval === null) {
                    $latest_eval = $eval;
                }
            }
        }
    }

    if ($latest_eval && isset($latest_eval['days_off'])) {
        $emp['days_off'] = intval($latest_eval['days_off']);
        $emp['eval_month'] = intval($latest_eval['eval_month'] ?? $current_month);
        $emp['eval_year'] = intval($latest_eval['eval_year'] ?? $current_year);
    }

    $coefficient = floatval($emp['salary_coefficient'] ?? 1.0);
    $baseSalaryReal = $emp['salary'] * $coefficient;
    $daysOffDeduction = round(($baseSalaryReal / $working_days_per_month) * $emp['days_off']);
    $emp['base_salary_real'] = round($baseSalaryReal);
    $emp['days_off_deduction'] = $daysOffDeduction;
    $emp['actual_salary'] = round($baseSalaryReal - $daysOffDeduction);
}
unset($emp);

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
    width: 50px;
    text-align: center;
}

.salary-table th:nth-child(7),
.salary-table th:nth-child(9),
.salary-table th:nth-child(11),
.salary-table th:nth-child(12) {
    text-align: right;
}

.salary-table th:nth-child(8),
.salary-table th:nth-child(10) {
    text-align: center;
}

.salary-table td:nth-child(1) {
    text-align: center;
    font-weight: 600;
}

.salary-table td:nth-child(7),
.salary-table td:nth-child(9),
.salary-table td:nth-child(11),
.salary-table td:nth-child(12) {
    text-align: right;
}

.salary-table td:nth-child(8),
.salary-table td:nth-child(10) {
    text-align: center;
}

.salary-table td {
    padding: 12px;
    border-bottom: 1px solid #e5e7eb;
    vertical-align: middle;
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
            <input type="hidden" id="employeeId" name="employee_id">
            
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Họ và tên:</label>
                <input type="text" id="employeeName" readonly style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; background: #f5f5f5;">
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Lương cơ bản mới (VNĐ):</label>
                <input type="number" id="newBaseSalary" name="new_base_salary" required min="0" step="10000" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" placeholder="Nhập mức lương cơ bản mới...">
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Hệ số lương:</label>
                <input type="number" id="newCoefficient" name="new_coefficient" required min="0" max="10" step="0.01" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" placeholder="1.0">
                <small style="color: #666;">Lương thực nhận = (Cơ bản × Hệ số) - [(Cơ bản × Hệ số) / 26 × Ngày nghỉ]</small>
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

<!-- Modal thêm cán bộ -->
<div id="modalThemCanBo" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; overflow-y: auto;">
    <div style="max-width: 550px; margin: 80px auto; background: white; border-radius: 10px; padding: 30px; position: relative;">
        <button onclick="dongModalThemCanBo()" style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">&times;</button>

        <h2 style="margin: 0 0 20px 0; color: #8b5cf6;">
            <i class="fas fa-user-plus"></i> Thêm cán bộ mới
        </h2>

        <form id="formThemCanBo" style="display: flex; flex-direction: column; gap: 15px;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Họ và tên <span style="color: red;">*</span></label>
                <input type="text" name="name" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" placeholder="Nguyễn Văn A">
            </div>

            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Chức vụ <span style="color: red;">*</span></label>
                <input type="text" name="position" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" placeholder="Chuyên viên">
            </div>

            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Phòng ban <span style="color: red;">*</span></label>
                <select name="department_id" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="">-- Chọn phòng ban --</option>
                    <?php
                    $conn_dept_list = getDBConnection();
                    if ($conn_dept_list) {
                        $dept_list = $conn_dept_list->query("SELECT id, name FROM departments WHERE status = 'active' ORDER BY display_order ASC, name ASC");
                        if ($dept_list) {
                            while ($dl = $dept_list->fetch_assoc()) {
                                echo '<option value="' . $dl['id'] . '">' . htmlspecialchars($dl['name']) . '</option>';
                            }
                        }
                    }
                    ?>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 600;">Số điện thoại</label>
                    <input type="text" name="phone" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" placeholder="0912.345.678">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 600;">Email</label>
                    <input type="email" name="email" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" placeholder="email@example.com">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 600;">Lương cơ bản (VNĐ)</label>
                    <input type="number" name="basic_salary" min="0" step="10000" value="2530000" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 5px; font-weight: 600;">Hệ số lương</label>
                    <input type="number" name="salary_coefficient" min="0" max="10" step="0.01" value="1.0" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
            </div>

            <div style="display: flex; gap: 10px; margin-top: 10px;">
                <button type="submit" style="flex: 1; padding: 12px; background: #8b5cf6; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;">
                    <i class="fas fa-plus"></i> Thêm cán bộ
                </button>
                <button type="button" onclick="dongModalThemCanBo()" style="flex: 1; padding: 12px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;">
                    <i class="fas fa-times"></i> Hủy
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function suaLuong(id, name, currentBaseSalary, currentCoefficient) {
    document.getElementById('employeeId').value = id;
    document.getElementById('employeeName').value = name;
    document.getElementById('newBaseSalary').value = currentBaseSalary;
    document.getElementById('newCoefficient').value = currentCoefficient;
    document.getElementById('modalSuaLuong').style.display = 'block';
}

function dongModal() {
    document.getElementById('modalSuaLuong').style.display = 'none';
}

document.getElementById('formSuaLuong').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const employeeId = document.getElementById('employeeId').value;
    const newBaseSalary = document.getElementById('newBaseSalary').value;
    const newCoefficient = document.getElementById('newCoefficient').value;
    const name = document.getElementById('employeeName').value;
    
    const formData = new FormData();
    formData.append('action', 'update_salary');
    formData.append('employee_id', employeeId);
    formData.append('new_base_salary', newBaseSalary);
    formData.append('new_coefficient', newCoefficient);
    
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

function xoaNhanVien(id, name) {
    if (!confirm('Bạn có chắc muốn xóa "' + name + '" không?\nNhân viên sẽ bị ẩn khỏi danh sách lương.')) {
        return;
    }
    const formData = new FormData();
    formData.append('action', 'delete_salary');
    formData.append('employee_id', id);

    fetch('api-salary-simple.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Đã xóa ' + name + ' thành công!');
            location.reload();
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert('Lỗi khi xóa nhân viên');
    });
}

// === Thêm cán bộ ===
function openModalThemCanBo() {
    document.getElementById('modalThemCanBo').style.display = 'block';
}

function dongModalThemCanBo() {
    document.getElementById('modalThemCanBo').style.display = 'none';
}

document.getElementById('formThemCanBo').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    formData.append('action', 'add_staff');

    fetch('api-salary-simple.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Thêm cán bộ thành công!');
            dongModalThemCanBo();
            location.reload();
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert('Lỗi khi thêm cán bộ');
    });
});

window.addEventListener('click', function(e) {
    if (e.target.id === 'modalThemCanBo') dongModalThemCanBo();
});
</script>

<div class="salary-stats">
    <?php if (isset($_GET['reset']) && $_GET['reset'] == 1): ?>
    <div style="background: #d1fae5; border: 1px solid #a7f3d0; color: #065f46; padding: 16px 20px; border-radius: 8px; margin-bottom: 20px; font-size: 16px;">
        <i class="fas fa-check-circle"></i> <strong>Đã reset thành công!</strong> Dữ liệu đánh giá tháng <?php echo $current_month; ?>/<?php echo $current_year; ?> đã được khôi phục về mặc định (0 ngày nghỉ).
    </div>
    <?php endif; ?>

    <div class="page-header">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h1><i class="fas fa-money-bill-wave"></i> Bảng lương cán bộ</h1>
                <p>Danh sách lương <?php echo count($salary_data); ?> cán bộ UBND xã Long Hiệp</p>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <form method="GET" style="display: flex; gap: 8px; align-items: center;">
                    <select name="month" style="padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo $m; ?>" <?php echo $m == $current_month ? 'selected' : ''; ?>>Tháng <?php echo $m; ?></option>
                        <?php endfor; ?>
                    </select>
                    <select name="year" style="padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                        <?php for ($y = date('Y'); $y >= date('Y') - 2; $y--): ?>
                        <option value="<?php echo $y; ?>" <?php echo $y == $current_year ? 'selected' : ''; ?>><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" class="btn" style="background: #2563eb; padding: 8px 16px;"><i class="fas fa-filter"></i> Lọc</button>
                </form>
                <button onclick="moFormTinhLuong()" class="btn" style="background: #10b981; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-calculator"></i> Tính lương mới
                </button>
                <?php if (authIsAdmin()): ?>
                <button onclick="openModalThemCanBo()" class="btn" style="background: #8b5cf6; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fas fa-user-plus"></i> Thêm cán bộ
                </button>
                <?php endif; ?>
                <a href="?month=<?php echo $current_month; ?>&year=<?php echo $current_year; ?>&export=1" class="btn" style="background: #f59e0b; display: inline-flex; align-items: center; gap: 8px; color: white; text-decoration: none;">
                    <i class="fas fa-file-excel"></i> Xuất Excel
                </a>
            </div>
        </div>
    </div>

    <!-- Thong ke so luong can bo theo phong ban -->
    <div class="chart-section" style="margin-bottom: 30px;">
        <h3 style="margin: 0 0 20px 0; color: #374151; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-building"></i> Thống kê cán bộ theo phòng ban
            <small style="color: #666; font-weight: normal; font-size: 14px;">(Tổng: <?php echo count($salary_data); ?> cán bộ)</small>
        </h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 15px;">
            <?php foreach ($dept_stats as $ds): ?>
            <div class="dept-item">
                <div class="dept-info">
                    <div class="dept-name"><?php echo htmlspecialchars($ds['dept_name']); ?></div>
                    <div class="dept-stats">
                        <i class="fas fa-users" style="margin-right: 4px;"></i>
                        <?php echo $ds['member_count']; ?> cán bộ
                    </div>
                </div>
                <div class="dept-salary">
                    <div class="dept-total"><?php echo number_format($ds['total_salary'], 0, ',', '.'); ?>đ</div>
                    <div class="dept-avg">TB: <?php echo number_format($ds['total_salary'] / $ds['member_count'], 0, ',', '.'); ?>đ/người</div>
                </div>
            </div>
            <?php endforeach; ?>
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
                                <?php if (authIsAdmin()): ?>
                                <input type="number" name="basic_salary" step="10000" min="0" required
                                       value="<?php echo $salaryResult ? $salaryResult['basic_salary'] : '2530000'; ?>"
                                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;"
                                       placeholder="2,530,000">
                                <?php else: ?>
                                <input type="number" name="basic_salary" step="10000" min="0" required
                                       value="<?php echo $salaryResult ? $salaryResult['basic_salary'] : '2530000'; ?>"
                                       style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; background-color: #f5f5f5;"
                                       placeholder="2,530,000" readonly>
                                <small style="color: #999;">Chỉ admin mới có quyền sửa lương</small>
                                <?php endif; ?>
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
        <h3 style="margin-bottom: 15px; color: #2563eb;">
            <i class="fas fa-calendar-alt"></i> Bảng lương tháng <?php echo $current_month; ?>/<?php echo $current_year; ?>
            <small style="color: #666; font-weight: normal;">(<?php echo count($salary_data); ?> cán bộ - Dựa trên dữ liệu đánh giá hiệu suất)</small>
        </h3>

        <!-- Ô tìm kiếm -->
        <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 20px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 250px; position: relative;">
                <i class="fas fa-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 15px;"></i>
                <input type="text" id="searchSalary" placeholder="Tìm theo tên, chức vụ, phòng ban, SĐT, email..."
                       onkeyup="locBangLuong()"
                       style="width: 100%; padding: 12px 16px 12px 42px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 15px; outline: none; transition: border-color 0.2s; box-sizing: border-box;"
                       onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#e5e7eb'">
            </div>
            <div id="searchResultCount" style="color: #6b7280; font-size: 14px; white-space: nowrap;"></div>
        </div>

        <table class="salary-table" id="salaryTable">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Họ tên</th>
                    <th>Chức vụ</th>
                    <th>Phòng ban</th>
                    <th>Số điện thoại</th>
                    <th>Email</th>
                    <th>Lương cơ bản</th>
                    <th>Hệ số</th>
                    <th>Lương CS × Hệ số</th>
                    <th>Ngày nghỉ</th>
                    <th>Trừ ngày nghỉ</th>
                    <th>Lương thực nhận</th>
                    <th style="width: 100px;">Thao tác</th>
                </tr>
            </thead>
            <tbody id="salaryBody">
                <?php foreach ($salary_data as $index => $emp): 
                    $basicSalary = $emp['salary'] ?? $emp['base_salary'] ?? 0;
                    $coefficient = $emp['salary_coefficient'] ?? 1.0;
                    $baseSalaryReal = $emp['base_salary_real'] ?? $basicSalary;
                    $daysOff = $emp['days_off'] ?? 0;
                    $daysOffDeduction = $emp['days_off_deduction'] ?? 0;
                    $actualSalary = $emp['actual_salary'] ?? $basicSalary;
                ?>
                <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td><strong><?php echo $emp['name']; ?></strong></td>
                    <td>
                        <span class="position-badge"><?php echo $emp['position']; ?></span>
                    </td>
                    <td><?php echo $emp['department']; ?></td>
                    <td style="white-space: nowrap;">
                        <?php if (!empty($emp['phone'])): ?>
                            <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $emp['phone']); ?>" style="color: #2563eb; text-decoration: none;">
                                <i class="fas fa-phone" style="margin-right: 4px; font-size: 11px;"></i><?php echo htmlspecialchars($emp['phone']); ?>
                            </a>
                        <?php else: ?>
                            <span style="color: #999;">-</span>
                        <?php endif; ?>
                    </td>
                    <td style="white-space: nowrap;">
                        <?php if (!empty($emp['email'])): ?>
                            <a href="mailto:<?php echo htmlspecialchars($emp['email']); ?>" style="color: #2563eb; text-decoration: none; font-size: 13px;">
                                <?php echo htmlspecialchars($emp['email']); ?>
                            </a>
                        <?php else: ?>
                            <span style="color: #999;">-</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align: right; font-weight: 600;"><?php echo number_format($basicSalary, 0, ',', '.'); ?>đ</td>
                    <td style="text-align: center; font-weight: 600; color: #7c3aed;"><?php echo $coefficient; ?></td>
                    <td style="text-align: right; font-weight: 600; color: #2563eb;"><?php echo number_format($baseSalaryReal, 0, ',', '.'); ?>đ</td>
                    <td style="text-align: center; font-weight: 600; <?php echo $daysOff > 0 ? 'color: #ef4444;' : 'color: #999;'; ?>">
                        <?php echo $daysOff; ?> ngày
                    </td>
                    <td style="text-align: right; font-weight: 600; color: #ef4444;">
                        <?php echo $daysOff > 0 ? '-' . number_format($daysOffDeduction, 0, ',', '.') . 'đ' : '-'; ?>
                    </td>
                    <td style="text-align: right; font-weight: 700; color: #059669; font-size: 15px;">
                        <?php echo number_format($actualSalary, 0, ',', '.'); ?>đ
                    </td>
                    <td>
                        <?php if (authIsAdmin()): ?>
                        <button class="btn-sm btn-warning" onclick="suaLuong(<?php echo $emp['id']; ?>, '<?php echo htmlspecialchars($emp['name']); ?>', <?php echo $basicSalary; ?>, <?php echo $coefficient; ?>)" title="Sửa lương">
                            <i class="fas fa-edit"></i> Sửa
                        </button>
                        <button class="btn-sm" style="background: #ef4444; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; padding: 5px 10px; margin-top: 4px;" onclick="xoaNhanVien(<?php echo $emp['id']; ?>, '<?php echo htmlspecialchars($emp['name']); ?>')" title="Xóa nhân viên">
                            <i class="fas fa-trash"></i> Xóa
                        </button>
                        <?php endif; ?>
                        <a href="?month=<?php echo $current_month; ?>&year=<?php echo $current_year; ?>&export=1&emp_id=<?php echo $emp['id']; ?>" class="btn-sm" style="background: #f59e0b; color: white; text-decoration: none; margin-top: 4px; display: inline-block;" title="Xuất lương cá nhân">
                            <i class="fas fa-file-excel"></i> Xuất
                        </a>
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
<script>
function toggleSalaryEdit() {
    var input = document.getElementById('salaryInput');
    if (input.readOnly) {
        input.readOnly = false;
        input.style.backgroundColor = '#fff';
    } else {
        input.readOnly = true;
        input.style.backgroundColor = '#f5f5f5';
    }
}

function locBangLuong() {
    var keyword = document.getElementById('searchSalary').value.toLowerCase().trim();
    var table = document.getElementById('salaryTable');
    var rows = table.querySelectorAll('tbody tr');
    var visibleCount = 0;

    rows.forEach(function(row) {
        var cells = row.querySelectorAll('td');
        var match = false;
        for (var i = 0; i < cells.length; i++) {
            var text = cells[i].textContent.toLowerCase();
            if (text.indexOf(keyword) !== -1) {
                match = true;
                break;
            }
        }
        if (keyword === '' || match) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    var countEl = document.getElementById('searchResultCount');
    if (keyword === '') {
        countEl.textContent = '';
    } else {
        countEl.textContent = 'Tìm thấy ' + visibleCount + ' kết quả';
    }
}
</script>