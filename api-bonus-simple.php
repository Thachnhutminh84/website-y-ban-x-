<?php
// Tắt tất cả output không mong muốn
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once 'config.php';
require_once 'auth.php';

// Xóa bất kỳ output nào trước đó
ob_clean();

// Set header JSON
header('Content-Type: application/json; charset=utf-8');

try {
    // Kiểm tra đăng nhập
    if (!authIsLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập'], JSON_UNESCAPED_UNICODE);
        exit();
    }

    // Kiểm tra quyền
    if (!authCanManageContent()) {
        echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập'], JSON_UNESCAPED_UNICODE);
        exit();
    }

    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    // Thêm thưởng mới
    if ($action === 'add_bonus') {
        $new_employee_name = trim($_POST['new_employee_name'] ?? '');
        $employee_position = trim($_POST['employee_position'] ?? '');
        $employee_department = (int)($_POST['employee_department'] ?? 0);
        $bonus_type = $_POST['bonus_type'] ?? '';
        $reason = $_POST['reason'] ?? '';
        $amount = (float)($_POST['amount'] ?? 0);
        $bonus_date = $_POST['bonus_date'] ?? '';
        
        // Validate
        if (empty($new_employee_name)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng nhập tên nhân viên'], JSON_UNESCAPED_UNICODE);
            exit();
        }
        
        if (empty($bonus_type) || empty($reason) || $amount <= 0 || empty($bonus_date)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin'], JSON_UNESCAPED_UNICODE);
            exit();
        }
        
        // Kiểm tra nhân viên đã tồn tại chưa
        $check_sql = "SELECT id FROM hr_employees WHERE full_name = ? AND status = 'active' LIMIT 1";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $new_employee_name);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Nhân viên đã tồn tại
            $existing_emp = $check_result->fetch_assoc();
            $employee_id = $existing_emp['id'];
            $check_stmt->close();
        } else {
            // Tạo nhân viên mới
            $check_stmt->close();
            
            // Tạo mã nhân viên
            $code_sql = "SELECT MAX(CAST(SUBSTRING(employee_code, 3) AS UNSIGNED)) as max_code FROM hr_employees WHERE employee_code LIKE 'NV%'";
            $code_result = $conn->query($code_sql);
            $code_row = $code_result->fetch_assoc();
            $next_number = ($code_row['max_code'] ?? 0) + 1;
            $employee_code = 'NV' . str_pad($next_number, 3, '0', STR_PAD_LEFT);
            
            // Thêm nhân viên
            $insert_emp_sql = "INSERT INTO hr_employees (employee_code, full_name, position, department_id, status) VALUES (?, ?, ?, ?, 'active')";
            $insert_emp_stmt = $conn->prepare($insert_emp_sql);
            $dept_id_or_null = $employee_department > 0 ? $employee_department : null;
            $insert_emp_stmt->bind_param("sssi", $employee_code, $new_employee_name, $employee_position, $dept_id_or_null);
            
            if (!$insert_emp_stmt->execute()) {
                echo json_encode(['success' => false, 'message' => 'Lỗi tạo nhân viên.'], JSON_UNESCAPED_UNICODE);
                exit();
            }
            
            $employee_id = $insert_emp_stmt->insert_id;
            $insert_emp_stmt->close();
        }
        
        // Thêm thưởng
        $created_by = $_SESSION['user_id'];
        $sql = "INSERT INTO hr_bonuses (employee_id, bonus_type, reason, amount, bonus_date, created_by) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issdsi", $employee_id, $bonus_type, $reason, $amount, $bonus_date, $created_by);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Đã thêm khoản thưởng thành công cho: ' . $new_employee_name,
                'bonus_id' => $stmt->insert_id
            ], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống.'], JSON_UNESCAPED_UNICODE);
        }
        
        $stmt->close();
    }
    // Các action khác...
    else {
        // Include file API cũ cho các action khác
        include 'api-bonus.php';
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống.'], JSON_UNESCAPED_UNICODE);
}

$conn->close();
ob_end_flush();
?>
