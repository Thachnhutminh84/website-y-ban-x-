<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once 'auth.php';

// Kiểm tra quyền
if (!authIsLoggedIn() || !authCanManageContent()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit();
}

$action = $_POST['action'] ?? '';
$departmentCode = $_POST['department'] ?? '';

if (empty($departmentCode)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Thiếu mã phòng ban']);
    exit();
}

// Đọc file department-data.php
$filePath = 'department-data.php';
$fileContent = file_get_contents($filePath);

if ($fileContent === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Không thể đọc file dữ liệu']);
    exit();
}

// Parse file để lấy mảng $departments
require_once $filePath;

if (!isset($departments[$departmentCode])) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Không tìm thấy phòng ban']);
    exit();
}

switch ($action) {
    case 'add':
        $name = trim($_POST['name'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        
        if (empty($name) || empty($position) || empty($phone)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
            exit();
        }
        
        // Thêm thành viên mới
        $newMember = [
            'name' => $name,
            'role' => $position,
            'phone' => $phone
        ];
        
        $departments[$departmentCode]['staff_members'][] = $newMember;
        
        // Ghi lại file
        if (saveDepartmentData($departments)) {
            echo json_encode(['success' => true, 'message' => 'Thêm thành viên thành công']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Không thể lưu dữ liệu']);
        }
        break;
        
    case 'edit':
        $index = (int)($_POST['index'] ?? -1);
        $name = trim($_POST['name'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        
        if ($index < 0 || !isset($departments[$departmentCode]['staff_members'][$index])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy thành viên']);
            exit();
        }
        
        if (empty($name) || empty($position) || empty($phone)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
            exit();
        }
        
        // Cập nhật thông tin
        $departments[$departmentCode]['staff_members'][$index] = [
            'name' => $name,
            'role' => $position,
            'phone' => $phone
        ];
        
        // Ghi lại file
        if (saveDepartmentData($departments)) {
            echo json_encode(['success' => true, 'message' => 'Cập nhật thành công']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Không thể lưu dữ liệu']);
        }
        break;
        
    case 'delete':
        $index = (int)($_POST['index'] ?? -1);
        
        if ($index < 0 || !isset($departments[$departmentCode]['staff_members'][$index])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy thành viên']);
            exit();
        }
        
        // Xóa thành viên
        array_splice($departments[$departmentCode]['staff_members'], $index, 1);
        
        // Ghi lại file
        if (saveDepartmentData($departments)) {
            echo json_encode(['success' => true, 'message' => 'Xóa thành viên thành công']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Không thể lưu dữ liệu']);
        }
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
        break;
}

function saveDepartmentData($departments) {
    $filePath = 'department-data.php';
    
    // Tạo nội dung file mới
    $content = "<?php\n\n";
    $content .= "\$departments = " . var_export($departments, true) . ";\n";
    
    // Backup file cũ
    if (file_exists($filePath)) {
        $backupPath = 'department-data-backup-' . date('Y-m-d-H-i-s') . '.php';
        copy($filePath, $backupPath);
    }
    
    // Ghi file mới
    return file_put_contents($filePath, $content) !== false;
}
?>
