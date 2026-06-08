<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config.php';
require_once 'auth.php';

// Kiểm tra đăng nhập
if (!authIsLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit();
}

// Kiểm tra quyền
if (!authCanManageContent()) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Lấy danh sách thưởng của nhân viên
if ($action === 'get_bonuses') {
    $employee_id = (int)($_GET['employee_id'] ?? 0);
    
    if ($employee_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID nhân viên không hợp lệ']);
        exit();
    }
    
    $sql = "SELECT 
        b.id,
        b.bonus_type,
        b.reason,
        b.amount,
        b.bonus_date,
        b.created_at,
        u.full_name as created_by_name
    FROM hr_bonuses b
    LEFT JOIN users u ON b.created_by = u.id
    WHERE b.employee_id = ?
    ORDER BY b.bonus_date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $bonuses = [];
    $total = 0;
    
    while ($row = $result->fetch_assoc()) {
        $bonuses[] = [
            'id' => $row['id'],
            'bonus_type' => $row['bonus_type'],
            'reason' => $row['reason'],
            'amount' => (float)$row['amount'],
            'bonus_date' => $row['bonus_date'],
            'created_at' => $row['created_at'],
            'created_by_name' => $row['created_by_name']
        ];
        $total += (float)$row['amount'];
    }
    
    echo json_encode([
        'success' => true,
        'bonuses' => $bonuses,
        'total' => $total
    ]);
    
    $stmt->close();
}

// Thêm thưởng mới
elseif ($action === 'add_bonus') {
    $new_employee_name = trim($_POST['new_employee_name'] ?? '');
    $bonus_type = $_POST['bonus_type'] ?? '';
    $reason = $_POST['reason'] ?? '';
    $amount = (float)($_POST['amount'] ?? 0);
    $bonus_date = $_POST['bonus_date'] ?? '';
    
    // Kiểm tra phải có tên nhân viên
    if (empty($new_employee_name)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập tên nhân viên']);
        exit();
    }
    
    if (empty($bonus_type) || empty($reason) || $amount <= 0 || empty($bonus_date)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
        exit();
    }
    
    // Kiểm tra xem nhân viên đã tồn tại chưa (theo tên)
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
        
        // Tạo mã nhân viên tự động
        $code_sql = "SELECT MAX(CAST(SUBSTRING(employee_code, 3) AS UNSIGNED)) as max_code FROM hr_employees WHERE employee_code LIKE 'NV%'";
        $code_result = $conn->query($code_sql);
        $code_row = $code_result->fetch_assoc();
        $next_number = ($code_row['max_code'] ?? 0) + 1;
        $employee_code = 'NV' . str_pad($next_number, 3, '0', STR_PAD_LEFT);
        
        // Thêm nhân viên mới
        $insert_emp_sql = "INSERT INTO hr_employees (employee_code, full_name, status) VALUES (?, ?, 'active')";
        $insert_emp_stmt = $conn->prepare($insert_emp_sql);
        $insert_emp_stmt->bind_param("ss", $employee_code, $new_employee_name);
        
        if (!$insert_emp_stmt->execute()) {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi tạo nhân viên mới.']);
            exit();
        }
        
        $employee_id = $insert_emp_stmt->insert_id;
        $insert_emp_stmt->close();
    }
    
    $created_by = $_SESSION['user_id'];
    
    $sql = "INSERT INTO hr_bonuses (employee_id, bonus_type, reason, amount, bonus_date, created_by) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issdsi", $employee_id, $bonus_type, $reason, $amount, $bonus_date, $created_by);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Đã thêm khoản thưởng thành công cho nhân viên: ' . $new_employee_name,
            'bonus_id' => $stmt->insert_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống.']);
    }
    
    $stmt->close();
}

// Xóa thưởng
elseif ($action === 'delete_bonus') {
    $bonus_id = (int)($_POST['bonus_id'] ?? 0);
    
    if ($bonus_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID thưởng không hợp lệ']);
        exit();
    }
    
    $sql = "DELETE FROM hr_bonuses WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $bonus_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Đã xóa khoản thưởng thành công']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy khoản thưởng']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống.']);
    }
    
    $stmt->close();
}

// Sửa thưởng
elseif ($action === 'update_bonus') {
    $bonus_id = (int)($_POST['bonus_id'] ?? 0);
    $new_employee_name = trim($_POST['new_employee_name'] ?? '');
    $bonus_type = $_POST['bonus_type'] ?? '';
    $reason = $_POST['reason'] ?? '';
    $amount = (float)($_POST['amount'] ?? 0);
    $bonus_date = $_POST['bonus_date'] ?? '';
    
    if ($bonus_id <= 0 || empty($new_employee_name) || empty($bonus_type) || empty($reason) || $amount <= 0 || empty($bonus_date)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
        exit();
    }
    
    // Kiểm tra xem nhân viên đã tồn tại chưa (theo tên)
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
        
        // Tạo mã nhân viên tự động
        $code_sql = "SELECT MAX(CAST(SUBSTRING(employee_code, 3) AS UNSIGNED)) as max_code FROM hr_employees WHERE employee_code LIKE 'NV%'";
        $code_result = $conn->query($code_sql);
        $code_row = $code_result->fetch_assoc();
        $next_number = ($code_row['max_code'] ?? 0) + 1;
        $employee_code = 'NV' . str_pad($next_number, 3, '0', STR_PAD_LEFT);
        
        // Thêm nhân viên mới
        $insert_emp_sql = "INSERT INTO hr_employees (employee_code, full_name, status) VALUES (?, ?, 'active')";
        $insert_emp_stmt = $conn->prepare($insert_emp_sql);
        $insert_emp_stmt->bind_param("ss", $employee_code, $new_employee_name);
        
        if (!$insert_emp_stmt->execute()) {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi tạo nhân viên mới.']);
            exit();
        }
        
        $employee_id = $insert_emp_stmt->insert_id;
        $insert_emp_stmt->close();
    }
    
    $sql = "UPDATE hr_bonuses 
            SET employee_id = ?, bonus_type = ?, reason = ?, amount = ?, bonus_date = ?
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issdsi", $employee_id, $bonus_type, $reason, $amount, $bonus_date, $bonus_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Đã cập nhật khoản thưởng thành công']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống.']);
    }
    
    $stmt->close();
}

else {
    echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
}

$conn->close();
?>
