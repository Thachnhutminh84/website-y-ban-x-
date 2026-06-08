<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

session_start();
require_once 'config.php';
require_once 'auth.php';

// Kiểm tra đăng nhập
try {
    authRequireCanBo();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để thực hiện thao tác này']);
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$evaluation_file = 'data/evaluations.json';

// Đảm bảo thư mục data tồn tại
if (!file_exists('data')) {
    mkdir('data', 0755, true);
}

switch ($action) {
    case 'delete_evaluation':
        $evaluation_id = intval($_POST['evaluation_id'] ?? 0);
        
        if (!$evaluation_id) {
            echo json_encode(['success' => false, 'message' => 'ID đánh giá không hợp lệ']);
            exit();
        }
        
        // Đọc dữ liệu hiện có
        $evaluations = [];
        if (file_exists($evaluation_file)) {
            $evaluations = json_decode(file_get_contents($evaluation_file), true) ?? [];
        }
        
        // Tìm và xóa đánh giá
        $found = false;
        $employee_name = '';
        foreach ($evaluations as $key => $eval) {
            if ($eval['id'] == $evaluation_id) {
                $employee_name = $eval['employee_name'];
                unset($evaluations[$key]);
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy đánh giá']);
            exit();
        }
        
        // Reindex array
        $evaluations = array_values($evaluations);
        
        // Lưu lại file
        if (file_put_contents($evaluation_file, json_encode($evaluations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            echo json_encode([
                'success' => true, 
                'message' => 'Đã xóa đánh giá của nhân viên: ' . $employee_name
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi lưu dữ liệu']);
        }
        break;
        
    case 'get_evaluation':
        $evaluation_id = intval($_GET['evaluation_id'] ?? 0);
        
        if (!$evaluation_id) {
            echo json_encode(['success' => false, 'message' => 'ID đánh giá không hợp lệ']);
            exit();
        }
        
        // Đọc dữ liệu
        $evaluations = [];
        if (file_exists($evaluation_file)) {
            $evaluations = json_decode(file_get_contents($evaluation_file), true) ?? [];
        }
        
        // Tìm đánh giá
        foreach ($evaluations as $eval) {
            if ($eval['id'] == $evaluation_id) {
                echo json_encode(['success' => true, 'data' => $eval]);
                exit();
            }
        }
        
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy đánh giá']);
        break;
        
    case 'get_employee_evaluations':
        $employee_id = intval($_GET['employee_id'] ?? 0);
        
        if (!$employee_id) {
            echo json_encode(['success' => false, 'message' => 'ID nhân viên không hợp lệ']);
            exit();
        }
        
        // Đọc dữ liệu
        $evaluations = [];
        if (file_exists($evaluation_file)) {
            $all_evaluations = json_decode(file_get_contents($evaluation_file), true) ?? [];
            
            // Lọc đánh giá của nhân viên
            foreach ($all_evaluations as $eval) {
                if ($eval['employee_id'] == $employee_id) {
                    $evaluations[] = $eval;
                }
            }
        }
        
        echo json_encode(['success' => true, 'data' => $evaluations]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
        break;
}
?>
