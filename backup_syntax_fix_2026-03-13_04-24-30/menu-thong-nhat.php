<?php
// File menu thống nhất cho toàn bộ website
// Sử dụng: include 'menu-thong-nhat.php';

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
function isActivePage($page) {
    global $currentPage;
    if (is_array($page)) {
        return in_array($currentPage, $page);
    }
    return $currentPage === $page;
}

// Hàm tạo class active
function activeClass($page) {
    return isActivePage($page) ? 'active' : '';
}

// Menu chính cho người dùng thường
$publicMenu = [
    [
        'title' => 'Trang chủ',
        'url' => 'index.php',
        'pages' => ['index.php'],
        'icon' => '🏠'
    ],
    [
        'title' => 'Tin Tức - Thông Báo',
        'url' => 'tin-tuc.php',
        'pages' => ['tin-tuc.php', 'chi-tiet-tin.php'],
        'icon' => '📰',
        'dropdown' => [
            ['title' => 'Tất cả tin tức', 'url' => 'tin-tuc.php'],
            ['title' => 'Công tác xây dựng Đảng', 'url' => 'cong-tac-xay-dung-dang.php'],
            ['title' => 'Mặt trận đoàn thể', 'url' => 'mat-tran-doan-the.php'],
            ['title' => 'An ninh trật tự', 'url' => 'an-ninh-trat-tu.php'],
            ['title' => 'Giáo dục và đào tạo', 'url' => 'giao-duc-dao-tao.php'],
            ['title' => 'Phát triển kinh tế', 'url' => 'phat-trien-kinh-te.php']
        ]
    ],
    [
        'title' => 'Phòng Ban',
        'url' => 'phong-ban.php',
        'pages' => ['phong-ban.php', 'phong-ban-chi-tiet.php', 'phong-ubnd.php', 'phong-hanh-chinh-cong.php', 'phong-hdnn.php', 'phong-kinh-te.php'],
        'icon' => '🏢',
        'dropdown' => [
            ['title' => 'Tất cả phòng ban', 'url' => 'phong-ban.php'],
            ['title' => 'Phòng UBND', 'url' => 'phong-ubnd.php'],
            ['title' => 'Phòng Hành chính công', 'url' => 'phong-hanh-chinh-cong.php'],
            ['title' => 'Phòng HDNN', 'url' => 'phong-hdnn.php'],
            ['title' => 'Phòng Kinh tế', 'url' => 'phong-kinh-te.php']
        ]
    ],
    [
        'title' => 'Lãnh đạo',
        'url' => 'lanh-dao.php',
        'pages' => ['lanh-dao.php', 'chi-tiet-lanh-dao.php'],
        'icon' => '👥'
    ],
    [
        'title' => 'Video',
        'url' => 'video.php',
        'pages' => ['video.php', 'video-files.php'],
        'icon' => '🎬',
        'dropdown' => [
            ['title' => '📺 Video chính thức', 'url' => 'video.php'],
            ['title' => '📁 Tất cả file video', 'url' => 'video-files.php'],
            ['title' => '🎵 File audio', 'url' => 'video-files.php?type=audio'],
            ['title' => '⭐ Video nổi bật', 'url' => 'video.php?featured=1']
        ]
    ],
    [
        'title' => 'Thủ tục',
        'url' => 'thu-tuc-hanh-chinh.php',
        'pages' => ['thu-tuc-hanh-chinh.php'],
        'icon' => '📋'
    ],
    [
        'title' => 'Liên Hệ',
        'url' => 'lien-he.php',
        'pages' => ['lien-he.php'],
        'icon' => '📞'
    ]
];

// Menu admin (chỉ hiển thị khi đăng nhập)
$adminMenu = [
    [
        'title' => 'Dashboard',
        'url' => 'dashboard.php',
        'pages' => ['dashboard.php'],
        'icon' => '📊'
    ],
    [
        'title' => 'Quản lý tin',
        'url' => 'tin-tuc.php',
        'pages' => ['tin-tuc.php', 'them-tin.php', 'sua-tin.php', 'them-tin-don-gian.php', 'them-tin-fullscreen.php'],
        'icon' => '📝',
        'dropdown' => [
            ['title' => 'Danh sách tin', 'url' => 'tin-tuc.php'],
            ['title' => 'Thêm tin mới', 'url' => 'them-tin-don-gian.php'],
            ['title' => 'Thêm tin fullscreen', 'url' => 'them-tin-fullscreen.php']
        ]
    ],
    [
        'title' => 'Quản lý video',
        'url' => 'quan-ly-video.php',
        'pages' => ['quan-ly-video.php', 'them-video.php', 'them-video-moi.php', 'sua-video.php', 'quan-ly-album-video.php'],
        'icon' => '🎬',
        'dropdown' => [
            ['title' => '📋 Danh sách video', 'url' => 'quan-ly-video.php'],
            ['title' => '➕ Thêm video mới', 'url' => 'them-video-moi.php'],
            ['title' => '🔧 Thêm video (nâng cao)', 'url' => 'them-video.php'],
            ['title' => '📁 Quản lý album', 'url' => 'quan-ly-album-video.php'],
            ['title' => '🔍 Kiểm tra hệ thống', 'url' => 'kiem-tra-tong-hop-video.php'],
            ['title' => '🛠️ Sửa lỗi video', 'url' => 'sua-loi-video-hoan-chinh.php']
        ]
    ],
    [
        'title' => 'Video công khai',
        'url' => 'video.php',
        'pages' => ['video.php', 'video-files.php'],
        'icon' => '📺',
        'dropdown' => [
            ['title' => '📺 Xem video công khai', 'url' => 'video.php'],
            ['title' => '📁 Tất cả file video', 'url' => 'video-files.php'],
            ['title' => '🎵 File audio', 'url' => 'video-files.php?type=audio'],
            ['title' => '⭐ Video nổi bật', 'url' => 'video.php?featured=1']
        ]
    ],
    [
        'title' => 'Quản lý media',
        'url' => 'quan-ly-media.php',
        'pages' => ['quan-ly-media.php'],
        'icon' => '🖼️'
    ],
    [
        'title' => 'Tin nhắn',
        'url' => 'tin-nhan-lien-he.php',
        'pages' => ['tin-nhan-lien-he.php'],
        'icon' => '💬'
    ]
];
?>



<style>
/* CSS cho menu thống nhất */
.main-navigation {
    background: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 1000;
}

.menu {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    align-items: center;
    flex-wrap: wrap;
}

.menu-item {
    position: relative;
}

.menu-item > a {
    display: block;
    padding: 15px 20px;
    text-decoration: none;
    color: #2c3e50;
    font-weight: 500;
    transition: all 0.3s ease;
    border-bottom: 3px solid transparent;
}

.menu-item > a:hover,
.menu-item > a.active {
    background: #f8f9fa;
    color: #007bff;
    border-bottom-color: #007bff;
}

/* Dropdown */
.dropdown {
    position: relative;
}

.dropdown-toggle {
    background: none;
    border: none;
    padding: 5px;
    cursor: pointer;
    position: absolute;
    right: 5px;
    top: 50%;
    transform: translateY(-50%);
}

.dropdown-toggle::after {
    content: '▼';
    font-size: 12px;
    color: #666;
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    left: 0;
    background: white;
    min-width: 200px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-radius: 8px;
    padding: 8px 0;
    display: none;
    z-index: 1001;
}

.dropdown.active .dropdown-menu {
    display: block;
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
}

.dropdown-menu a:hover {
    background: #f8f9fa;
    color: #007bff;
}

/* Admin info */
.admin-info {
    margin-left: auto;
    padding: 15px 20px;
    color: #2c3e50;
    font-size: 14px;
}

.admin-info span {
    margin: 0 10px;
    font-weight: 600;
}

.logout-btn {
    color: #dc3545;
    text-decoration: none;
    font-weight: 500;
}

.logout-btn:hover {
    text-decoration: underline;
}

/* Login button */
.login-item {
    margin-left: auto;
}

.login-btn {
    display: block;
    padding: 10px 20px;
    background: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    margin: 10px 20px;
    transition: background 0.3s ease;
}

.login-btn:hover {
    background: #0056b3;
}

/* Responsive */
@media (max-width: 768px) {
    .menu {
        flex-direction: column;
        align-items: stretch;
    }
    
    .menu-item {
        width: 100%;
    }
    
    .admin-info,
    .login-item {
        margin-left: 0;
        width: 100%;
    }
    
    .dropdown-menu {
        position: static;
        box-shadow: none;
        background: #f8f9fa;
        margin-left: 20px;
    }
}

/* Animation cho dropdown */
.dropdown-menu {
    opacity: 0;
    transform: translateY(-10px);
    transition: all 0.3s ease;
}

.dropdown.active .dropdown-menu {
    opacity: 1;
    transform: translateY(0);
}
</style>

<script>
// JavaScript cho dropdown menu
function toggleDropdown(event) {
    event.preventDefault();
    event.stopPropagation();
    
    const dropdown = event.target.closest('.dropdown');
    const isActive = dropdown.classList.contains('active');
    
    // Đóng tất cả dropdown khác
    document.querySelectorAll('.dropdown.active').forEach(item => {
        item.classList.remove('active');
    });
    
    // Toggle dropdown hiện tại
    if (!isActive) {
        dropdown.classList.add('active');
    }
}

// Đóng dropdown khi click bên ngoài
document.addEventListener('click', function(event) {
    if (!event.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown.active').forEach(item => {
            item.classList.remove('active');
        });
    }
});

// Đóng dropdown khi nhấn ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        document.querySelectorAll('.dropdown.active').forEach(item => {
            item.classList.remove('active');
        });
    }
});
</script>