<?php
/**
 * Check approval middleware
 * Kiểm tra trạng thái duyệt tài khoản
 */

// Chỉ chạy nếu đã đăng nhập
if (isset($_SESSION['user_id'])) {
    // Kiểm tra user có bị khóa không
    if (isset($conn) && $conn instanceof mysqli) {
        $stmt = $conn->prepare("SELECT status FROM users WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();
            
            if ($user && ($user['status'] === 'banned' || $user['status'] === 'inactive')) {
                session_destroy();
                header("Location: dang-nhap.php?error=tai_khoan_bi_khoa");
                exit();
            }
        }
    }
}
