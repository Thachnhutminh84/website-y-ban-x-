<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once 'auth.php';
require_once 'config.php';

// Kiểm tra đăng nhập và quyền
if (!authIsLoggedIn() || !authCanManageContent()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit();
}

$conn = getDBConnection();
if (!$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Không thể kết nối database']);
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$departmentCode = $_POST['department'] ?? $_GET['department'] ?? '';

// Detect which column the table uses: department_code (VARCHAR) or department_id (INT)
$hasDeptCodeCol = false;
$hasDeptIdCol = false;
$colCheck = $conn->query("SHOW COLUMNS FROM department_staff LIKE 'department_code'");
if ($colCheck && $colCheck->num_rows > 0) {
    $hasDeptCodeCol = true;
}
$colCheck2 = $conn->query("SHOW COLUMNS FROM department_staff LIKE 'department_id'");
if ($colCheck2 && $colCheck2->num_rows > 0) {
    $hasDeptIdCol = true;
}

// Helper: convert department_code to department_id if needed
function getDeptId($conn, $code) {
    $stmt = $conn->prepare("SELECT id FROM departments WHERE code = ?");
    if (!$stmt) return 0;
    $stmt->bind_param('s', $code);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row ? (int)$row['id'] : 0;
}

// Validate dữ liệu thành viên
function validateStaffData($name, $position, $phone) {
    $errors = [];
    
    if (empty(trim($name))) {
        $errors[] = 'Tên công chức không được để trống';
    } elseif (strlen(trim($name)) > 100) {
        $errors[] = 'Tên công chức không được quá 100 ký tự';
    }
    
    if (empty(trim($position))) {
        $errors[] = 'Chức vụ không được để trống';
    } elseif (strlen(trim($position)) > 100) {
        $errors[] = 'Chức vụ không được quá 100 ký tự';
    }
    
    if (empty(trim($phone))) {
        $errors[] = 'Số điện thoại không được để trống';
    } elseif (!preg_match('/^[0-9\.\-\s\+\(\)]{10,20}$/', trim($phone))) {
        $errors[] = 'Số điện thoại không hợp lệ';
    }

    return $errors;
}

// Kiểm tra phòng ban
if (empty($departmentCode)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu mã phòng ban']);
    exit();
}

// Xử lý các action
switch ($action) {
    case 'get':
        // Lấy danh sách thành viên từ database
        $whereCol = $hasDeptCodeCol ? 'department_code' : 'department_id';
        $stmt = $conn->prepare("SELECT id, name, position, phone, email, display_order, status 
                                FROM department_staff 
                                WHERE $whereCol = ? AND status = 'active'
                                ORDER BY display_order ASC, id ASC");
        if ($hasDeptCodeCol) {
            $stmt->bind_param('s', $departmentCode);
        } else {
            $deptId = getDeptId($conn, $departmentCode);
            $stmt->bind_param('i', $deptId);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $staffMembers = [];
        while ($row = $result->fetch_assoc()) {
            $staffMembers[] = $row;
        }
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'data' => $staffMembers,
            'department' => ['code' => $departmentCode]
        ]);
        break;
        
    case 'add':
        // Thêm thành viên mới
        $name = trim($_POST['name'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        $errors = validateStaffData($name, $position, $phone);
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
            exit();
        }
        
        // Lấy display_order lớn nhất
        $orderCol = $hasDeptCodeCol ? 'department_code' : 'department_id';
        $stmt = $conn->prepare("SELECT MAX(display_order) as max_order FROM department_staff WHERE $orderCol = ?");
        if ($hasDeptCodeCol) {
            $stmt->bind_param('s', $departmentCode);
        } else {
            $deptId = getDeptId($conn, $departmentCode);
            if ($deptId <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Không tìm thấy phòng ban']);
                exit();
            }
            $stmt->bind_param('i', $deptId);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $nextOrder = ($row['max_order'] ?? 0) + 1;
        $stmt->close();
        
        // Thêm vào database
        if ($hasDeptCodeCol) {
            $stmt = $conn->prepare("INSERT INTO department_staff (department_code, name, position, phone, email, display_order) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssssi', $departmentCode, $name, $position, $phone, $email, $nextOrder);
        } else {
            $stmt = $conn->prepare("INSERT INTO department_staff (department_id, name, position, phone, email, display_order) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('issssi', $deptId, $name, $position, $phone, $email, $nextOrder);
        }
        
        if ($stmt->execute()) {
            $newId = $stmt->insert_id;
            $stmt->close();
            echo json_encode([
                'success' => true,
                'message' => 'Thêm thành viên thành công',
                'id' => $newId
            ]);
        } else {
            $errMsg = $stmt->error ?: $conn->error;
            $stmt->close();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Không thể thêm thành viên: ' . $errMsg]);
        }
        break;
        
    case 'edit':
        // Sửa thông tin thành viên
        $id = (int)($_POST['index'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
            exit();
        }
        
        $errors = validateStaffData($name, $position, $phone);
        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
            exit();
        }
        
        // Cập nhật database
        if ($hasDeptCodeCol) {
            $stmt = $conn->prepare("UPDATE department_staff SET name = ?, position = ?, phone = ?, email = ? WHERE id = ? AND department_code = ?");
            $stmt->bind_param('ssssis', $name, $position, $phone, $email, $id, $departmentCode);
        } else {
            $deptId = getDeptId($conn, $departmentCode);
            $stmt = $conn->prepare("UPDATE department_staff SET name = ?, position = ?, phone = ?, email = ? WHERE id = ? AND department_id = ?");
            $stmt->bind_param('ssssii', $name, $position, $phone, $email, $id, $deptId);
        }
        
        if ($stmt->execute()) {
            $affectedRows = $stmt->affected_rows;
            $stmt->close();
            
            if ($affectedRows > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Cập nhật thông tin thành công'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Không tìm thấy thành viên hoặc không có thay đổi'
                ]);
            }
        } else {
            $stmt->close();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Không thể cập nhật thông tin.']);
        }
        break;
        
    case 'delete':
        // Xóa thành viên (soft delete)
        $id = (int)($_POST['index'] ?? 0);
        
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
            exit();
        }
        
        if ($hasDeptCodeCol) {
            $stmt = $conn->prepare("UPDATE department_staff SET status = 'inactive' WHERE id = ? AND department_code = ?");
            $stmt->bind_param('is', $id, $departmentCode);
        } else {
            $deptId = getDeptId($conn, $departmentCode);
            $stmt = $conn->prepare("UPDATE department_staff SET status = 'inactive' WHERE id = ? AND department_id = ?");
            $stmt->bind_param('ii', $id, $deptId);
        }
        
        if ($stmt->execute()) {
            $affectedRows = $stmt->affected_rows;
            $stmt->close();
            
            if ($affectedRows > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Xóa thành viên thành công'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Không tìm thấy thành viên'
                ]);
            }
        } else {
            $stmt->close();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Không thể xóa thành viên.']);
        }
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Action không hợp lệ: ' . $action]);
        break;
}

$conn->close();
?>
