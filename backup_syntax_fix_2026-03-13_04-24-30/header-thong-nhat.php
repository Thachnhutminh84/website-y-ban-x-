<?php
// File header thống nhất cho toàn bộ website
// Sử dụng: include 'header-thong-nhat.php';

// Kiểm tra session và auth
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (file_exists('auth.php')) {
    require_once 'auth.php';
}

$isLoggedIn = function_exists('authIsLoggedIn') ? authIsLoggedIn() : false;
$currentRole = $isLoggedIn && function_exists('authCurrentRole') ? authCurrentRole() : '';
$displayName = $isLoggedIn && function_exists('authDisplayName') ? authDisplayName() : '';

// Xác định class header dựa trên trạng thái đăng nhập
$headerClass = $isLoggedIn ? 'header--compact' : '';
?>



<style>
/* CSS cho header thống nhất */
header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px 0;
    position: relative;
}

header.header--compact {
    padding: 10px 0;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.logo {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
}

header.header--compact .logo {
    margin-bottom: 10px;
}

.logo img {
    width: 80px;
    height: 80px;
    margin-right: 20px;
    border-radius: 50%;
    border: 3px solid rgba(255,255,255,0.3);
}

header.header--compact .logo img {
    width: 60px;
    height: 60px;
}

.header-text h1 {
    font-size: 28px;
    margin: 0;
    font-weight: 700;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

header.header--compact .header-text h1 {
    font-size: 24px;
}

.header-text p {
    margin: 5px 0 0 0;
    font-size: 16px;
    opacity: 0.9;
    font-style: italic;
}

header.header--compact .header-text p {
    font-size: 14px;
}

/* Override menu styles for header integration */
header .main-navigation {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    border-radius: 10px;
    margin-top: 10px;
}

header .menu-item > a {
    color: white;
    border-bottom-color: transparent;
}

header .menu-item > a:hover,
header .menu-item > a.active {
    background: rgba(255,255,255,0.2);
    color: white;
    border-bottom-color: rgba(255,255,255,0.5);
}

header .dropdown-menu {
    background: rgba(255,255,255,0.95);
    backdrop-filter: blur(10px);
}

header .dropdown-menu a {
    color: #2c3e50;
}

header .admin-info {
    color: rgba(255,255,255,0.9);
}

header .logout-btn {
    color: #ffeb3b;
}

header .login-btn {
    background: rgba(255,255,255,0.2);
    border: 2px solid rgba(255,255,255,0.3);
}

header .login-btn:hover {
    background: rgba(255,255,255,0.3);
}

/* Responsive */
@media (max-width: 768px) {
    .logo {
        flex-direction: column;
        text-align: center;
    }
    
    .logo img {
        margin-right: 0;
        margin-bottom: 10px;
    }
    
    .header-text h1 {
        font-size: 20px;
    }
    
    .header-text p {
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    .container {
        padding: 0 10px;
    }
    
    .header-text h1 {
        font-size: 18px;
    }
    
    .header-text p {
        font-size: 12px;
    }
}
</style>