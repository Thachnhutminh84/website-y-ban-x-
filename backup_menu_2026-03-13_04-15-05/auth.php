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
    $role = authCurrentRole();
    return $role === 'admin' || $role === 'editor';
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
    if (!authIsLoggedIn()) {
        $_SESSION['login_error'] = 'Vui lòng đăng nhập để tiếp tục.';
        header('Location: ' . $redirect . '?error=1');
        exit();
    }

    if (!in_array(authCurrentRole(), $allowedRoles, true)) {
        $_SESSION['login_error'] = 'Tài khoản của bạn không có quyền truy cập chức năng này.';
        header('Location: ' . $redirect . '?error=1');
        exit();
    }
}

function authFindUserByUsername(mysqli $conn, $username)
{
    $stmt = $conn->prepare('SELECT id, username, password, full_name, email, role, status FROM users WHERE username = ? LIMIT 1');
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
