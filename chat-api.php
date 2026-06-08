<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';

// Chỉ cán bộ và admin đã được phê duyệt mới được sử dụng chat
if (!authIsLoggedIn() || !authIsApproved() || !authCanManageContent()) {
    echo json_encode(['success' => false, 'message' => 'Chỉ cán bộ đã được phê duyệt mới có quyền sử dụng chat']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$userId = authCurrentUserId();
$userName = authDisplayName();

$conn = getDBConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Không thể kết nối database']);
    exit;
}

switch ($action) {
    case 'send':
        // Gửi tin nhắn
        $message = trim($_POST['message'] ?? '');
        
        if (empty($message)) {
            echo json_encode(['success' => false, 'message' => 'Tin nhắn không được để trống']);
            exit;
        }
        
        $stmt = $conn->prepare("INSERT INTO chat_messages (sender_id, sender_name, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $userId, $userName, $message);
        
        if ($stmt->execute()) {
            $messageId = $stmt->insert_id;
            echo json_encode([
                'success' => true,
                'message_id' => $messageId,
                'sender_name' => $userName,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không thể gửi tin nhắn']);
        }
        $stmt->close();
        break;
        
    case 'get_messages':
        // Lấy tin nhắn (50 tin gần nhất)
        $lastId = (int)($_GET['last_id'] ?? 0);
        
        if ($lastId > 0) {
            // Lấy tin nhắn mới hơn lastId
            $stmt = $conn->prepare("SELECT id, sender_id, sender_name, message, created_at 
                                   FROM chat_messages 
                                   WHERE id > ? 
                                   ORDER BY created_at ASC 
                                   LIMIT 50");
            $stmt->bind_param("i", $lastId);
        } else {
            // Lấy 50 tin nhắn gần nhất
            $stmt = $conn->prepare("SELECT id, sender_id, sender_name, message, created_at 
                                   FROM chat_messages 
                                   ORDER BY created_at DESC 
                                   LIMIT 50");
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $messages = [];
        
        while ($row = $result->fetch_assoc()) {
            $messages[] = [
                'id' => (int)$row['id'],
                'sender_id' => (int)$row['sender_id'],
                'sender_name' => htmlspecialchars($row['sender_name'], ENT_QUOTES, 'UTF-8'),
                'message' => htmlspecialchars($row['message'], ENT_QUOTES, 'UTF-8'),
                'created_at' => $row['created_at'],
                'is_mine' => (int)$row['sender_id'] === $userId
            ];
        }
        
        // Đảo ngược để tin cũ ở trên, mới ở dưới
        if ($lastId === 0) {
            $messages = array_reverse($messages);
        }
        
        echo json_encode(['success' => true, 'messages' => $messages]);
        $stmt->close();
        break;
        
    case 'update_status':
        // Cập nhật trạng thái online
        $stmt = $conn->prepare("INSERT INTO chat_online_status (user_id, user_name, is_online) 
                               VALUES (?, ?, 1) 
                               ON DUPLICATE KEY UPDATE last_seen = NOW(), is_online = 1");
        $stmt->bind_param("is", $userId, $userName);
        $stmt->execute();
        $stmt->close();
        
        echo json_encode(['success' => true]);
        break;
        
    case 'get_online_users':
        // Lấy danh sách cán bộ đang online (trong vòng 5 phút)
        $stmt = $conn->prepare("SELECT user_id, user_name, last_seen 
                               FROM chat_online_status 
                               WHERE last_seen >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) 
                               AND is_online = 1
                               ORDER BY user_name ASC");
        $stmt->execute();
        $result = $stmt->get_result();
        $users = [];
        
        while ($row = $result->fetch_assoc()) {
            $users[] = [
                'user_id' => (int)$row['user_id'],
                'user_name' => $row['user_name'],
                'is_me' => (int)$row['user_id'] === $userId
            ];
        }
        
        echo json_encode(['success' => true, 'users' => $users, 'total' => count($users)]);
        $stmt->close();
        break;
        
    case 'set_offline':
        // Đặt trạng thái offline
        $stmt = $conn->prepare("UPDATE chat_online_status SET is_online = 0 WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();
        
        echo json_encode(['success' => true]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
        break;
}

$conn->close();
