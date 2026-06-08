<?php

require_once 'config.php';

function authNormalizeRole($role)
{
    $role = trim((string) $role);

    return in_array($role, ['admin', 'editor', 'viewer'], true) ? $role : 'viewer';
}

function authRoleLabel($role)
{
    $role = authNormalizeRole($role);

    if ($role === 'admin') {
        return 'Quản trị viên';
    }

    if ($role === 'editor') {
        return 'Biên tập viên';
    }

    return 'Người xem';
}

function authEnsureUsersTableExists(mysqli $conn)
{
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) DEFAULT NULL,
        email VARCHAR(100) DEFAULT NULL,
        role ENUM('admin', 'editor', 'viewer') DEFAULT 'viewer',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        UNIQUE KEY uniq_users_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    return $conn->query($sql) === true;
}

function authSetUserSession(array $user)
{
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['username'] = (string) $user['username'];
    $_SESSION['full_name'] = (string) ($user['full_name'] ?? $user['username']);
    $_SESSION['email'] = (string) ($user['email'] ?? '');
    $_SESSION['role'] = authNormalizeRole($user['role'] ?? 'viewer');
    $_SESSION['user_type'] = (string) ($user['user_type'] ?? 'nguoi_dan');
    $_SESSION['approval_status'] = (string) ($user['approval_status'] ?? 'approved');
    $_SESSION['admin'] = $_SESSION['role'] === 'admin';
    $_SESSION['login_time'] = time();
}

function authLogoutUser()
{
    session_unset();
    session_destroy();
}

function authIsLoggedIn()
{
    return isset($_SESSION['user_id']) && (int) $_SESSION['user_id'] > 0;
}

function authIsApproved()
{
    if (!authIsLoggedIn()) {
        return false;
    }
    
    $approvalStatus = (string) ($_SESSION['approval_status'] ?? 'approved');
    return $approvalStatus === 'approved';
}

function authRequireApproval($redirect = 'dang-nhap.php')
{
    if (!authIsLoggedIn()) {
        $_SESSION['login_error'] = 'Vui lòng đăng nhập để tiếp tục.';
        header('Location: ' . $redirect . '?error=1');
        exit();
    }
    
    if (!authIsApproved()) {
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['login_error'] = 'Tài khoản của bạn chưa được phê duyệt. Vui lòng liên hệ admin.';
        header('Location: ' . $redirect . '?error=1');
        exit();
    }
}

function authCurrentRole()
{
    return authNormalizeRole($_SESSION['role'] ?? 'viewer');
}

function authIsAdmin()
{
    return authCurrentRole() === 'admin';
}

function authCanManageNews()
{
    // Chỉ cán bộ và admin mới có quyền quản lý tin tức
    return authCanManageContent();
}

function authDisplayName()
{
    $fullName = trim((string) ($_SESSION['full_name'] ?? ''));
    if ($fullName !== '') {
        return $fullName;
    }

    return (string) ($_SESSION['username'] ?? 'Tài khoản');
}

function authRequireRole(array $allowedRoles, $redirect = 'dang-nhap.php')
{
    // Kiểm tra đăng nhập
    if (!authIsLoggedIn()) {
        $_SESSION['login_error'] = 'Vui lòng đăng nhập để tiếp tục.';
        header('Location: ' . $redirect . '?error=1');
        exit();
    }

    // Kiểm tra phê duyệt
    if (!authIsApproved()) {
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['login_error'] = 'Tài khoản của bạn chưa được phê duyệt. Vui lòng liên hệ admin.';
        header('Location: ' . $redirect . '?error=1');
        exit();
    }

    // Kiểm tra quyền
    if (!in_array(authCurrentRole(), $allowedRoles, true)) {
        $_SESSION['login_error'] = 'Tài khoản của bạn không có quyền truy cập chức năng này.';
        header('Location: ' . $redirect . '?error=1');
        exit();
    }
}

function authFindUserByUsername(mysqli $conn, $username)
{
    $stmt = $conn->prepare('SELECT id, username, password, full_name, email, role, user_type, status, approval_status, rejection_reason FROM users WHERE username = ? LIMIT 1');
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('s', $username);
    if (!$stmt->execute()) {
        $stmt->close();
        return null;
    }

    $result = $stmt->get_result();
    $user = $result->fetch_assoc() ?: null;
    $stmt->close();

    return $user;
}

function authUpdateLastLogin(mysqli $conn, $userId)
{
    $stmt = $conn->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
    if (!$stmt) {
        return;
    }

    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->close();
}

function authCurrentUserId()
{
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
}

function authHasPermission($permission)
{
    $role = authCurrentRole();
    
    switch ($permission) {
        case 'manage_content':
        case 'manage_videos':
        case 'manage_news':
            return $role === 'admin' || $role === 'editor';
        case 'admin_only':
            return $role === 'admin';
        default:
            return false;
    }
}

// Kiểm tra loại người dùng
function authCurrentUserType()
{
    return (string) ($_SESSION['user_type'] ?? 'nguoi_dan');
}

function authIsCanBo()
{
    return authCurrentUserType() === 'can_bo';
}

function authIsNguoiDan()
{
    return authCurrentUserType() === 'nguoi_dan';
}

// Kiểm tra quyền thêm/sửa/xóa tin tức và video
function authCanManageContent()
{
    // Phải đăng nhập và được phê duyệt
    if (!authIsLoggedIn() || !authIsApproved()) {
        return false;
    }
    
    // Admin luôn có quyền
    if (authIsAdmin()) {
        return true;
    }
    
    // Cán bộ có quyền quản lý nội dung
    if (authIsCanBo()) {
        return true;
    }
    
    // Người dân không có quyền
    return false;
}

// Yêu cầu phải là cán bộ hoặc admin
function authRequireCanBo($redirect = 'index.php')
{
    // Kiểm tra đăng nhập
    if (!authIsLoggedIn()) {
        $_SESSION['login_error'] = 'Vui lòng đăng nhập để tiếp tục.';
        header('Location: dang-nhap.php?error=1');
        exit();
    }
    
    // Kiểm tra phê duyệt
    if (!authIsApproved()) {
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['login_error'] = 'Tài khoản của bạn chưa được phê duyệt. Vui lòng liên hệ admin.';
        header('Location: dang-nhap.php?error=1');
        exit();
    }
    
    // Kiểm tra quyền cán bộ
    if (!authCanManageContent()) {
        $_SESSION['error_message'] = 'Chỉ cán bộ mới có quyền truy cập chức năng này.';
        header('Location: ' . $redirect);
        exit();
    }
}

function authUserTypeLabel($userType = null)
{
    $type = $userType ?? authCurrentUserType();
    return $type === 'can_bo' ? 'Cán bộ' : 'Người dân';
}

// Helper function: Kiểm tra xem user có phải read-only (người dân)
function authIsReadOnly()
{
    return authIsNguoiDan();
}

// Helper function: Render nút chỉ khi không phải read-only
function authRenderIfNotReadOnly($html)
{
    if (!authIsReadOnly()) {
        echo $html;
    }
}
