<?php
/**
 * Enhanced Authentication System
 * Bổ sung rate limiting, CSRF protection, và security logging
 */

require_once 'auth.php';

/**
 * Login với rate limiting
 */
function authLoginWithRateLimit($username, $password) {
    global $conn;
    
    // Check rate limit
    $rateLimitCheck = checkRateLimit('login_' . $username);
    
    if (is_array($rateLimitCheck) && !$rateLimitCheck['allowed']) {
        SecurityHelper::logSecurityEvent('login_rate_limit_exceeded', [
            'username' => $username
        ]);
        
        return [
            'success' => false,
            'message' => $rateLimitCheck['message']
        ];
    }
    
    // Validate input
    if (empty($username) || empty($password)) {
        return [
            'success' => false,
            'message' => 'Vui lòng nhập đầy đủ thông tin'
        ];
    }
    
    // Sanitize username
    $username = sanitizeInput($username);
    
    // Get user from database
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND status = 'active' LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        SecurityHelper::logSecurityEvent('login_failed_user_not_found', [
            'username' => $username
        ]);
        
        return [
            'success' => false,
            'message' => 'Tên đăng nhập hoặc mật khẩu không đúng'
        ];
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Verify password
    if (!verifyPassword($password, $user['password'])) {
        SecurityHelper::logSecurityEvent('login_failed_wrong_password', [
            'username' => $username,
            'user_id' => $user['id']
        ]);
        
        return [
            'success' => false,
            'message' => 'Tên đăng nhập hoặc mật khẩu không đúng'
        ];
    }
    
    // Update last login
    $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $updateStmt->bind_param("i", $user['id']);
    $updateStmt->execute();
    $updateStmt->close();
    
    // Set session
    authSetUserSession($user);
    
    // Log successful login
    SecurityHelper::logSecurityEvent('login_success', [
        'username' => $username,
        'user_id' => $user['id']
    ]);
    
    logActivity($conn, $user['id'], 'login', 'users', $user['id'], 'Đăng nhập thành công');
    
    return [
        'success' => true,
        'message' => 'Đăng nhập thành công',
        'user' => $user
    ];
}

/**
 * Logout với logging
 */
function authLogoutEnhanced() {
    if (authIsLoggedIn()) {
        $userId = $_SESSION['user_id'] ?? null;
        $username = $_SESSION['username'] ?? 'unknown';
        
        SecurityHelper::logSecurityEvent('logout', [
            'username' => $username,
            'user_id' => $userId
        ]);
        
        if ($userId) {
            global $conn;
            logActivity($conn, $userId, 'logout', 'users', $userId, 'Đăng xuất');
        }
    }
    
    authLogout();
}

/**
 * Change password với validation
 */
function authChangePassword($userId, $oldPassword, $newPassword, $confirmPassword) {
    global $conn;
    
    // Validate inputs
    if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
        return [
            'success' => false,
            'message' => 'Vui lòng điền đầy đủ thông tin'
        ];
    }
    
    if ($newPassword !== $confirmPassword) {
        return [
            'success' => false,
            'message' => 'Mật khẩu mới không khớp'
        ];
    }
    
    // Password strength check
    if (strlen($newPassword) < 8) {
        return [
            'success' => false,
            'message' => 'Mật khẩu phải có ít nhất 8 ký tự'
        ];
    }
    
    if (!preg_match('/[A-Z]/', $newPassword)) {
        return [
            'success' => false,
            'message' => 'Mật khẩu phải có ít nhất 1 chữ hoa'
        ];
    }
    
    if (!preg_match('/[a-z]/', $newPassword)) {
        return [
            'success' => false,
            'message' => 'Mật khẩu phải có ít nhất 1 chữ thường'
        ];
    }
    
    if (!preg_match('/[0-9]/', $newPassword)) {
        return [
            'success' => false,
            'message' => 'Mật khẩu phải có ít nhất 1 số'
        ];
    }
    
    // Get current password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return [
            'success' => false,
            'message' => 'Người dùng không tồn tại'
        ];
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Verify old password
    if (!verifyPassword($oldPassword, $user['password'])) {
        SecurityHelper::logSecurityEvent('password_change_failed_wrong_old_password', [
            'user_id' => $userId
        ]);
        
        return [
            'success' => false,
            'message' => 'Mật khẩu cũ không đúng'
        ];
    }
    
    // Hash new password
    $hashedPassword = hashPassword($newPassword);
    
    // Update password
    $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $updateStmt->bind_param("si", $hashedPassword, $userId);
    
    if ($updateStmt->execute()) {
        $updateStmt->close();
        
        SecurityHelper::logSecurityEvent('password_changed', [
            'user_id' => $userId
        ]);
        
        logActivity($conn, $userId, 'password_change', 'users', $userId, 'Đổi mật khẩu thành công');
        
        return [
            'success' => true,
            'message' => 'Đổi mật khẩu thành công'
        ];
    }
    
    $updateStmt->close();
    
    return [
        'success' => false,
        'message' => 'Lỗi khi cập nhật mật khẩu'
    ];
}

/**
 * Check permission với logging
 */
function authCheckPermission($requiredRole) {
    if (!authIsLoggedIn()) {
        SecurityHelper::logSecurityEvent('unauthorized_access_attempt', [
            'required_role' => $requiredRole,
            'url' => $_SERVER['REQUEST_URI'] ?? 'unknown'
        ]);
        
        return false;
    }
    
    $currentRole = authCurrentRole();
    $roleHierarchy = ['viewer' => 1, 'editor' => 2, 'admin' => 3];
    
    $currentLevel = $roleHierarchy[$currentRole] ?? 0;
    $requiredLevel = $roleHierarchy[$requiredRole] ?? 999;
    
    if ($currentLevel < $requiredLevel) {
        SecurityHelper::logSecurityEvent('insufficient_permission', [
            'user_id' => $_SESSION['user_id'] ?? null,
            'current_role' => $currentRole,
            'required_role' => $requiredRole,
            'url' => $_SERVER['REQUEST_URI'] ?? 'unknown'
        ]);
        
        return false;
    }
    
    return true;
}

/**
 * Require permission hoặc redirect
 */
function authRequirePermission($requiredRole, $redirectUrl = 'index.php') {
    if (!authCheckPermission($requiredRole)) {
        header('Location: ' . $redirectUrl);
        exit();
    }
}

/**
 * Create user với validation
 */
function authCreateUserEnhanced($username, $password, $fullName, $email, $role = 'viewer') {
    global $conn;
    
    // Validate inputs
    if (empty($username) || empty($password)) {
        return [
            'success' => false,
            'message' => 'Tên đăng nhập và mật khẩu không được để trống'
        ];
    }
    
    // Validate email
    if (!empty($email) && !validateEmail($email)) {
        return [
            'success' => false,
            'message' => 'Email không hợp lệ'
        ];
    }
    
    // Check password strength
    if (strlen($password) < 8) {
        return [
            'success' => false,
            'message' => 'Mật khẩu phải có ít nhất 8 ký tự'
        ];
    }
    
    // Sanitize inputs
    $username = sanitizeInput($username);
    $fullName = sanitizeInput($fullName);
    $email = sanitizeInput($email);
    $role = authNormalizeRole($role);
    
    // Check if username exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $checkStmt->close();
        return [
            'success' => false,
            'message' => 'Tên đăng nhập đã tồn tại'
        ];
    }
    $checkStmt->close();
    
    // Hash password
    $hashedPassword = hashPassword($password);
    
    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, email, role, status) VALUES (?, ?, ?, ?, ?, 'active')");
    $stmt->bind_param("sssss", $username, $hashedPassword, $fullName, $email, $role);
    
    if ($stmt->execute()) {
        $userId = $stmt->insert_id;
        $stmt->close();
        
        SecurityHelper::logSecurityEvent('user_created', [
            'new_user_id' => $userId,
            'username' => $username,
            'role' => $role,
            'created_by' => $_SESSION['user_id'] ?? null
        ]);
        
        return [
            'success' => true,
            'message' => 'Tạo người dùng thành công',
            'user_id' => $userId
        ];
    }
    
    $stmt->close();
    
    return [
        'success' => false,
        'message' => 'Lỗi khi tạo người dùng'
    ];
}
?>
