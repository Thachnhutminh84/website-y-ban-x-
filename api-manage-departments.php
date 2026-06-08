<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once 'auth.php';
require_once 'config.php';

// Chỉ cán bộ và admin mới có quyền
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

switch ($action) {
    case 'add':
        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $shortName = trim($_POST['short_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $leaderName = trim($_POST['leader_name'] ?? '');
        $leaderPosition = trim($_POST['leader_position'] ?? '');
        $displayOrder = (int)($_POST['display_order'] ?? 0);
        
        if (empty($code) || empty($name) || empty($shortName)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc']);
            exit();
        }
        
        // Kiểm tra mã phòng ban đã tồn tại chưa
        $stmt = $conn->prepare("SELECT id FROM departments WHERE code = ?");
        $stmt->bind_param('s', $code);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $stmt->close();
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Mã phòng ban đã tồn tại']);
            exit();
        }
        $stmt->close();
        
        // Thêm phòng ban mới
        $stmt = $conn->prepare("INSERT INTO departments (code, name, short_name, description, leader_name, leader_position, display_order) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssssi', $code, $name, $shortName, $description, $leaderName, $leaderPosition, $displayOrder);
        
        if ($stmt->execute()) {
            $stmt->close();
            echo json_encode(['success' => true, 'message' => 'Thêm phòng ban thành công']);
        } else {
            $stmt->close();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Không thể thêm phòng ban']);
        }
        break;
        
    case 'edit':
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $shortName = trim($_POST['short_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $leaderName = trim($_POST['leader_name'] ?? '');
        $leaderPosition = trim($_POST['leader_position'] ?? '');
        $displayOrder = (int)($_POST['display_order'] ?? 0);
        
        if ($id <= 0 || empty($name) || empty($shortName)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Thông tin không hợp lệ']);
            exit();
        }
        
        // Cập nhật thông tin
        $stmt = $conn->prepare("UPDATE departments 
                                SET name = ?, short_name = ?, description = ?, 
                                    leader_name = ?, leader_position = ?, display_order = ?
                                WHERE id = ?");
        $stmt->bind_param('sssssii', $name, $shortName, $description, $leaderName, $leaderPosition, $displayOrder, $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            echo json_encode(['success' => true, 'message' => 'Cập nhật thông tin thành công']);
        } else {
            $stmt->close();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Không thể cập nhật thông tin']);
        }
        break;
        
    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
            exit();
        }
        
        // Soft delete
        $stmt = $conn->prepare("UPDATE departments SET status = 'inactive' WHERE id = ?");
        $stmt->bind_param('i', $id);
        
        if ($stmt->execute()) {
            $stmt->close();
            echo json_encode(['success' => true, 'message' => 'Xóa phòng ban thành công']);
        } else {
            $stmt->close();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Không thể xóa phòng ban']);
        }
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
        break;
}

$conn->close();
