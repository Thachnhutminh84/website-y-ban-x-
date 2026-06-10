<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

session_start();
require_once 'config.php';
require_once 'auth.php';

try {
    authRequireCanBo();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập']);
    exit();
}

$action = $_POST['action'] ?? 'get_salary_data';

$conn = getDBConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Không kết nối được database']);
    exit();
}

switch ($action) {
    case 'get_salary_data':
        $query = "SELECT 
                    ds.id,
                    ds.name,
                    ds.position,
                    ds.department_id,
                    d.name as department,
                    COALESCE(ds.basic_salary, 2530000) as basic_salary,
                    COALESCE(ds.salary_coefficient, 1.0) as salary_coefficient
                  FROM department_staff ds
                  LEFT JOIN departments d ON ds.department_id = d.id
                  WHERE ds.status = 'active'
                  ORDER BY ds.basic_salary DESC";
        
        $result = $conn->query($query);
        $data = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data[] = [
                    'id' => intval($row['id']),
                    'name' => $row['name'],
                    'position' => $row['position'],
                    'department' => $row['department'] ?? 'Chưa phân công',
                    'basic_salary' => floatval($row['basic_salary']),
                    'salary_coefficient' => floatval($row['salary_coefficient'])
                ];
            }
        }
        
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    case 'update_salary':
        if (!authIsAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Chỉ admin mới có quyền sửa lương']);
            exit();
        }
        
        $employee_id = intval($_POST['employee_id'] ?? 0);
        $new_base_salary = floatval($_POST['new_base_salary'] ?? 0);
        $new_coefficient = floatval($_POST['new_coefficient'] ?? 1.0);
        
        if ($employee_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID nhân viên không hợp lệ']);
            exit();
        }
        
        if ($new_base_salary <= 0) {
            echo json_encode(['success' => false, 'message' => 'Mức lương phải lớn hơn 0']);
            exit();
        }
        
        if ($new_coefficient < 0 || $new_coefficient > 10) {
            echo json_encode(['success' => false, 'message' => 'Hệ số phải từ 0 đến 10']);
            exit();
        }
        
        $stmt = $conn->prepare("UPDATE department_staff SET basic_salary = ?, salary_coefficient = ? WHERE id = ? AND status = 'active'");
        $stmt->bind_param("ddi", $new_base_salary, $new_coefficient, $employee_id);
        
        if ($stmt->execute() && $stmt->affected_rows >= 0) {
            echo json_encode(['success' => true, 'message' => 'Cập nhật lương thành công']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật']);
        }
        $stmt->close();
        break;

    case 'delete_salary':
        if (!authIsAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Chỉ admin mới có quyền xóa']);
            exit();
        }

        $employee_id = intval($_POST['employee_id'] ?? 0);

        if ($employee_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID nhân viên không hợp lệ']);
            exit();
        }

        $stmt = $conn->prepare("UPDATE department_staff SET status = 'inactive' WHERE id = ? AND status = 'active'");
        $stmt->bind_param("i", $employee_id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Xóa nhân viên thành công']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi xóa']);
        }
        $stmt->close();
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
        break;
}

$conn->close();
?>