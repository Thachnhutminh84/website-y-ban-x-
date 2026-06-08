<?php
// Menu đơn giản và thống nhất cho toàn bộ website
// Sử dụng: include 'menu-don-gian.php';

// Kiểm tra session và auth
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (file_exists('auth.php')) {
    require_once 'auth.php';
}

// Xác định trang hiện tại
$currentPage = basename($_SERVER['PHP_SELF']);
$isLoggedIn = function_exists('authIsLoggedIn') ? authIsLoggedIn() : false;
$currentRole = $isLoggedIn && function_exists('authCurrentRole') ? authCurrentRole() : '';
$displayName = $isLoggedIn && function_exists('authDisplayName') ? authDisplayName() : '';

// Hàm kiểm tra trang active
function isActive($page) {
    global $currentPage;
    if (is_array($page)) {
        return in_array($currentPage, $page);
    }
    return $currentPage === $page;
}
?>

<header class="main-header">
    <div class="container">
        <div class="header-top">
            <div class="logo">
                <img src="images/logo.png" alt="Logo UBND Xã Long Hiệp" onerror="this.style.display='none'">
                <div class="logo-text">
                    <h1>ỦY BAN NHÂN DÂN XÃ LONG HIỆP</h1>
                    <p>Phục vụ nhân dân - Xây dựng quê hương</p>
                </div>
            </div>
            
            <?php if ($isLoggedIn): ?>
                <div class="user-info">
                    <span class="user-role"><?php echo htmlspecialchars(function_exists('authRoleLabel') ? authRoleLabel($currentRole) : $currentRole); ?></span>
                    <span class="user-name"><?php echo htmlspecialchars($displayName); ?></span>
                    <a href="logout.php" class="logout-btn">Đăng xuất</a>
                </div>
            <?php else: ?>
                <div class="login-area">
                    <a href="dang-nhap.php" class="login-btn">Đăng nhập</a>
                </div>
            <?php endif; ?>
        </div>
        
        <nav class="main-nav">
            <ul class="nav-menu">
                <li class="nav-item <?php echo isActive('index.php') ? 'active' : ''; ?>">
                    <a href="index.php">Trang chủ</a>
                </li>
                
                <li class="nav-item dropdown <?php echo isActive(['tin-tuc.php', 'chi-tiet-tin.php']) ? 'active' : ''; ?>">
                    <a href="tin-tuc.php">Tin Tức</a>
                    <ul class="dropdown-menu">
                        <li><a href="tin-tuc.php">Tất cả tin tức</a></li>
                        <li><a href="cong-tac-xay-dung-dang.php">Công tác Đảng</a></li>
                        <li><a href="mat-tran-doan-the.php">Mặt trận đoàn thể</a></li>
                        <li><a href="an-ninh-trat-tu.php">An ninh trật tự</a></li>
                    </ul>
                </li>
                
                <li class="nav-item <?php echo isActive(['phong-ban.php', 'phong-ban-chi-tiet.php']) ? 'active' : ''; ?>">
                    <a href="phong-ban.php">Phòng Ban</a>
                </li>
                
                <li class="nav-item <?php echo isActive(['lanh-dao.php', 'chi-tiet-lanh-dao.php']) ? 'active' : ''; ?>">
                    <a href="lanh-dao.php">Lãnh đạo</a>
                </li>
                
                <li class="nav-item dropdown <?php echo isActive(['video.php', 'video-files.php']) ? 'active' : ''; ?>">
                    <a href="video.php">Video</a>
                    <ul class="dropdown-menu">
                        <li><a href="video.php">Video chính thức</a></li>
                        <li><a href="video-files.php">Tất cả file video</a></li>
                        <li><a href="video-files.php?type=audio">File audio</a></li>
                        <li><a href="video.php?featured=1">Video nổi bật</a></li>
                    </ul>
                </li>
                
                <li class="nav-item <?php echo isActive('thu-tuc-hanh-chinh.php') ? 'active' : ''; ?>">
                    <a href="thu-tuc-hanh-chinh.php">Thủ tục</a>
                </li>
                
                <li class="nav-item <?php echo isActive('lien-he.php') ? 'active' : ''; ?>">
                    <a href="lien-he.php">Liên Hệ</a>
                </li>
                
                <?php if ($isLoggedIn): ?>
                    <li class="nav-item dropdown admin-menu">
                        <a href="dashboard.php">Quản lý</a>
                        <ul class="dropdown-menu">
                            <li><a href="dashboard.php">Dashboard</a></li>
                            <li><a href="quan-ly-video.php">Quản lý video</a></li>
                            <li><a href="them-video-moi.php">Thêm video</a></li>
                            <li><a href="them-tin-don-gian.php">Thêm tin tức</a></li>
                            <li><a href="tin-nhan-lien-he.php">Tin nhắn</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
            
            <!-- Mobile menu button -->
            <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </nav>
    </div>
</header>

<style>
/* Reset và base styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.main-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Header top */
.header-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid rgba(255,255,255,0.2);
}

.logo {
    display: flex;
    align-items: center;
}

.logo img {
    width: 60px;
    height: 60px;
    margin-right: 15px;
    border-radius: 50%;
    border: 2px solid rgba(255,255,255,0.3);
}

.logo-text h1 {
    font-size: 20px;
    margin-bottom: 5px;
    font-weight: 700;
}

.logo-text p {
    font-size: 12px;
    opacity: 0.9;
    font-style: italic;
}

.user-info, .login-area {
    display: flex;
    align-items: center;
    gap: 15px;
}

.user-role {
    background: rgba(255,255,255,0.2);
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 12px;
}

.user-name {
    font-weight: 600;
}

.login-btn, .logout-btn {
    background: rgba(255,255,255,0.2);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: 1px solid rgba(255,255,255,0.3);
}

.login-btn:hover, .logout-btn:hover {
    background: rgba(255,255,255,0.3);
    transform: translateY(-1px);
}

/* Navigation */
.main-nav {
    padding: 0;
}

.nav-menu {
    display: flex;
    list-style: none;
    align-items: center;
}

.nav-item {
    position: relative;
}

.nav-item > a {
    display: block;
    padding: 15px 20px;
    color: white;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    border-bottom: 3px solid transparent;
}

.nav-item:hover > a,
.nav-item.active > a {
    background: rgba(255,255,255,0.1);
    border-bottom-color: rgba(255,255,255,0.8);
}

/* Dropdown */
.dropdown {
    position: relative;
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    background: white;
    min-width: 200px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    border-radius: 8px;
    padding: 8px 0;
    list-style: none;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    z-index: 1001;
}

.dropdown:hover .dropdown-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-menu li {
    list-style: none;
}

.dropdown-menu a {
    display: block;
    padding: 10px 20px;
    color: #2c3e50;
    text-decoration: none;
    transition: background 0.3s ease;
    font-size: 14px;
}

.dropdown-menu a:hover {
    background: #f8f9fa;
    color: #007bff;
}

/* Admin menu */
.admin-menu > a {
    background: rgba(255,255,255,0.15);
    border-radius: 5px;
}

/* Mobile menu */
.mobile-menu-btn {
    display: none;
    flex-direction: column;
    background: none;
    border: none;
    cursor: pointer;
    padding: 10px;
}

.mobile-menu-btn span {
    width: 25px;
    height: 3px;
    background: white;
    margin: 3px 0;
    transition: 0.3s;
}

/* Responsive */
@media (max-width: 768px) {
    .header-top {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .logo-text h1 {
        font-size: 18px;
    }
    
    .logo-text p {
        font-size: 11px;
    }
    
    .mobile-menu-btn {
        display: flex;
        position: absolute;
        top: 20px;
        right: 20px;
    }
    
    .nav-menu {
        flex-direction: column;
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        background: rgba(102, 126, 234, 0.95);
        backdrop-filter: blur(10px);
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
    }
    
    .nav-menu.active {
        max-height: 500px;
    }
    
    .nav-item {
        width: 100%;
    }
    
    .nav-item > a {
        padding: 15px 20px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    
    .dropdown-menu {
        position: static;
        opacity: 1;
        visibility: visible;
        transform: none;
        box-shadow: none;
        background: rgba(255,255,255,0.1);
        margin-left: 20px;
    }
    
    .dropdown-menu a {
        color: white;
        padding: 8px 20px;
    }
}

@media (max-width: 480px) {
    .container {
        padding: 0 10px;
    }
    
    .logo img {
        width: 50px;
        height: 50px;
    }
    
    .logo-text h1 {
        font-size: 16px;
    }
    
    .user-info, .login-area {
        flex-direction: column;
        gap: 8px;
    }
}
</style>

<script>
function toggleMobileMenu() {
    const menu = document.querySelector('.nav-menu');
    const btn = document.querySelector('.mobile-menu-btn');
    
    menu.classList.toggle('active');
    btn.classList.toggle('active');
}

// Đóng menu khi click bên ngoài
document.addEventListener('click', function(event) {
    const menu = document.querySelector('.nav-menu');
    const btn = document.querySelector('.mobile-menu-btn');
    
    if (!event.target.closest('.main-nav')) {
        menu.classList.remove('active');
        btn.classList.remove('active');
    }
});

// Đóng menu khi nhấn ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const menu = document.querySelector('.nav-menu');
        const btn = document.querySelector('.mobile-menu-btn');
        menu.classList.remove('active');
        btn.classList.remove('active');
    }
});
</script>