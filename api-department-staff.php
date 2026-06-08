<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once 'auth.php';
require_once 'department-data.php';

// Kiểm tra đăng nhập và quyền admin
if (!authIsLoggedIn() || authCurrentRole() !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$departmentKey = $_POST['department'] ?? $_GET['department'] ?? '';

// Kiểm tra phòng ban có tồn tại
if (!isset($departments[$departmentKey])) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Phòng ban không tồn tại']);
    exit();
}

// Đường dẫn file department-data.php
$departmentDataFile = __DIR__ . '/department-data.php';

function updateDepartmentData($departments) {
    global $departmentDataFile;
    
    $content = "<?php\n\n\$departments = " . var_export($departments, true) . ";\n";
    
    if (file_put_contents($departmentDataFile, $content) === false) {
        return false;
    }
    
    return true;
}

function validateStaffData($name, $role, $phone) {
    $errors = [];
    
    if (empty(trim($name))) {
        $errors[] = 'Tên công chức không được để trống';
    }
    
    if (empty(trim($role))) {
        $errors[] = 'Chức vụ không được để trống';
    }
    
    if (empty(trim($phone))) {
        $errors[] = 'Số điện thoại không được để trống';
    } elseif (!preg_match('/^[0-9\.\-\s\+\(\)]{10,15}$/', trim($phone))) {
        $errors[] = 'Số điện thoại không hợp lệ';
    }
    
    return $errors;
}

try {
    switch ($action) {
        case 'add':
            $name = trim($_POST['name'] ?? '');
            $role = trim($_POST['role'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            
            // Validate dữ liệu
            $errors = validateStaffData($name, $role, $phone);
            if (!empty($errors)) {
                echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
                exit();
            }
            
            // Thêm thành viên mới
            $newMember = [
                'name' => $name,
                'role' => $role,
                'phone' => $phone
            ];
            
            $departments[$departmentKey]['staff_members'][] = $newMember;
            
            // Cập nhật số lượng nhân sự
            $departments[$departmentKey]['staff_count'] = count($departments[$departmentKey]['staff_members']);
            
            // Cập nhật highlight_stats
            foreach ($departments[$departmentKey]['highlight_stats'] as &$stat) {
                if ($stat['label'] === 'Nhân sự') {
                    $stat['value'] = (string)$departments[$departmentKey]['staff_count'];
                    break;
                }
            }
            
            // Lưu vào file
            if (updateDepartmentData($departments)) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Thêm thành viên thành công',
                    'member' => $newMember
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi khi lưu dữ liệu']);
            }
            break;
            
        case 'edit':
            $index = (int)($_POST['index'] ?? -1);
            $name = trim($_POST['name'] ?? '');
            $role = trim($_POST['role'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            
            // Kiểm tra index hợp lệ
            if ($index < 0 || $index >= count($departments[$departmentKey]['staff_members'])) {
                echo json_encode(['success' => false, 'message' => 'Thành viên không tồn tại']);
                exit();
            }
            
            // Validate dữ liệu
            $errors = validateStaffData($name, $role, $phone);
            if (!empty($errors)) {
                echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
                exit();
            }
            
            // Cập nhật thông tin thành viên
            $departments[$departmentKey]['staff_members'][$index] = [
                'name' => $name,
                'role' => $role,
                'phone' => $phone
            ];
            
            // Lưu vào file
            if (updateDepartmentData($departments)) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Cập nhật thành viên thành công',
                    'member' => $departments[$departmentKey]['staff_members'][$index]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi khi lưu dữ liệu']);
            }
            break;
            
        case 'delete':
            $index = (int)($_POST['index'] ?? -1);
            
            // Kiểm tra index hợp lệ
            if ($index < 0 || $index >= count($departments[$departmentKey]['staff_members'])) {
                echo json_encode(['success' => false, 'message' => 'Thành viên không tồn tại']);
                exit();
            }
            
            // Xóa thành viên
            $deletedMember = $departments[$departmentKey]['staff_members'][$index];
            array_splice($departments[$departmentKey]['staff_members'], $index, 1);
            
            // Cập nhật số lượng nhân sự
            $departments[$departmentKey]['staff_count'] = count($departments[$departmentKey]['staff_members']);
            
            // Cập nhật highlight_stats
            foreach ($departments[$departmentKey]['highlight_stats'] as &$stat) {
                if ($stat['label'] === 'Nhân sự') {
                    $stat['value'] = (string)$departments[$departmentKey]['staff_count'];
                    break;
                }
            }
            
            // Lưu vào file
            if (updateDepartmentData($departments)) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Xóa thành viên thành công',
                    'deleted_member' => $deletedMember
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Lỗi khi lưu dữ liệu']);
            }
            break;
            
        case 'get':
            // Lấy danh sách thành viên
            echo json_encode([
                'success' => true,
                'members' => $departments[$departmentKey]['staff_members']
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ']);
            break;
    }
    
} catch (Exception $e) {
    error_log("Department Staff API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống']);
}
?>