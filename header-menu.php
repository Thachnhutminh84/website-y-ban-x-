<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (file_exists('auth.php')) {
    require_once 'auth.php';
}

$isLoggedIn = function_exists('authIsLoggedIn') ? authIsLoggedIn() : false;
$isApproved = function_exists('authIsApproved') ? authIsApproved() : false;
$displayName = $isLoggedIn && function_exists('authDisplayName') ? authDisplayName() : '';
$currentRole = function_exists('authCurrentRole') ? authCurrentRole() : '';
$currentPage = basename($_SERVER['PHP_SELF']);

if (!function_exists('isMenuActive')) {
    function isMenuActive($pages) {
        global $currentPage;
        return (is_array($pages) && in_array($currentPage, $pages)) || $currentPage === $pages ? 'active' : '';
    }
}

$menuItems = [
    ['text' => 'Trang chủ', 'url' => 'index.php', 'pages' => ['index.php']],
    ['text' => 'Tin Tức', 'url' => 'tin-tuc.php', 'pages' => ['tin-tuc.php', 'chi-tiet-tin.php', 'cong-tac-xay-dung-dang.php', 'mat-tran-doan-the.php', 'an-ninh-trat-tu.php', 'tin-tuc-su-kien.php', 'thong-tin-tuyen-truyen.php', 'giao-duc-dao-tao.php'],
        'submenu' => [
            ['text' => 'Công tác xây dựng Đảng', 'url' => 'cong-tac-xay-dung-dang.php'],
            ['text' => 'Mặt trận đoàn thể', 'url' => 'mat-tran-doan-the.php'],
            ['text' => 'An ninh trật tự', 'url' => 'an-ninh-trat-tu.php'],
            ['text' => 'Tin tức sự kiện', 'url' => 'tin-tuc-su-kien.php'],
            ['text' => 'Thông tin tuyên truyền', 'url' => 'thong-tin-tuyen-truyen.php'],
            ['text' => 'Giáo dục và đào tạo', 'url' => 'giao-duc-dao-tao.php']
        ]
    ],
    ['text' => 'Phòng Ban', 'url' => 'phong-ban.php', 'pages' => ['phong-ban.php', 'phong-ubnd.php', 'phong-hdnn.php', 'phong-kinh-te.php', 'phong-ban-chi-tiet.php'],
        'submenu' => [
            ['text' => 'Tất cả phòng ban', 'url' => 'phong-ban.php'],
            ['text' => 'Ủy ban Nhân dân Xã', 'url' => 'phong-ubnd.php'],
            ['text' => 'Văn phòng HĐND và UBND', 'url' => 'phong-hdnn.php'],
            ['text' => 'Phòng Kinh tế', 'url' => 'phong-kinh-te.php'],
            ['text' => 'Phòng Văn hóa - Xã hội', 'url' => 'phong-ban-chi-tiet.php?dept=vh-xh']
        ]
    ],
    ['text' => 'Lãnh đạo', 'url' => 'lanh-dao.php', 'pages' => ['lanh-dao.php', 'chi-tiet-lanh-dao.php']],
    ['text' => 'Video', 'url' => 'video.php', 'pages' => ['video.php']],
    ['text' => 'Danh bạ', 'url' => 'danh-ba-dien-thoai.php', 'pages' => ['danh-ba-dien-thoai.php']],
    ['text' => 'Liên Hệ', 'url' => 'lien-he.php', 'pages' => ['lien-he.php']]
];

if ($isLoggedIn) {
    $menuItems[] = [
        'text' => '<i class="fas fa-user-circle"></i> ' . htmlspecialchars($displayName),
        'url' => '#',
        'pages' => [],
        'submenu' => [
            ['text' => '<i class="fas fa-tachometer-alt"></i> Dashboard', 'url' => 'dashboard.php'],
            ['text' => '<i class="fas fa-sign-out-alt"></i> Đăng xuất', 'url' => 'dang-xuat.php']
        ]
    ];
} else {
    // Không thêm "Đăng nhập" vào menu vì đã có nút ở header-top
}

if ($isLoggedIn && $isApproved && authCanManageContent()) {
    $submenuItems = [
        ['text' => '<i class="fas fa-newspaper"></i> Quản lý tin tức', 'url' => 'advanced-content-manager.php'],
        ['text' => '<i class="fas fa-video"></i> Quản lý video', 'url' => 'quan-ly-video.php'],
        ['text' => '<i class="fas fa-users"></i> Quản lý nhân sự', 'url' => 'quan-ly-nhan-su.php'],
        ['text' => '<i class="fas fa-money-bill-wave"></i> Lương thưởng', 'url' => 'quan-ly-luong-thuong.php'],
        ['text' => '<i class="fas fa-chart-bar"></i> Thống kê lương', 'url' => 'thong-ke-luong-simple.php'],
        ['text' => '<i class="fas fa-clipboard-check"></i> Đánh giá hiệu suất', 'url' => 'quan-ly-danh-gia.php']
    ];
    if (authIsAdmin()) {
        $submenuItems[] = ['text' => '<i class="fas fa-user-check"></i> Phê duyệt tài khoản', 'url' => 'quan-ly-phe-duyet.php'];
    }
    $menuItems[] = [
        'text' => '<i class="fas fa-cog"></i> Quản lý',
        'url' => 'dashboard.php',
        'pages' => ['dashboard.php', 'quan-ly-video.php', 'them-tin.php', 'sua-tin.php', 'quan-ly-nhan-su.php', 'them-nhan-su.php', 'sua-nhan-su.php', 'quan-ly-luong-thuong.php', 'quan-ly-danh-gia.php', 'quan-ly-phe-duyet.php', 'advanced-content-manager.php', 'thong-ke-luong-simple.php', 'tin-nhan-lien-he.php'],
        'submenu' => $submenuItems
    ];
}
?>

<style>
/* ============================================
   HEADER - UBND LONG HIỆP
   ============================================ */
.site-header {
    background: transparent;
    position: relative;
    box-shadow: var(--shadow-lg);
    min-height: 50vh;
}

.site-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('images/ubnd-longhiep-banner.png') center/cover no-repeat;
    opacity: 1;
    z-index: 0;
}

.site-header::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(180deg, rgba(0,0,0,0.05) 0%, rgba(0,0,0,0) 60%, rgba(0,0,0,0.05) 100%);
    z-index: 0;
}

.header-wrap {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0;
    position: relative;
    z-index: 20;
}

/* === HEADER TOP === */
.header-top {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 40px 60px;
    gap: 24px;
    min-height: calc(50vh - 50px);
}

.header-logo {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    flex-shrink: 0;
    box-shadow: 0 4px 16px rgba(0,0,0,0.25);
    border: 3px solid rgba(255,255,255,0.95);
    background: white;
    object-fit: contain;
    padding: 5px;
}

.header-info {
    flex: 1;
    text-align: center;
    overflow: hidden;
    min-width: 0;
}

.header-info h1 {
    margin: 0;
    font-size: 14px;
    font-weight: 500;
    letter-spacing: 4px;
    text-transform: uppercase;
    line-height: 1.4;
    color: white;
    font-family: var(--font-heading);
    text-shadow: 0 1px 4px rgba(0,0,0,0.7), 0 0 10px rgba(0,0,0,0.5);
}

.header-info .site-title {
    font-size: clamp(18px, 3vw, 32px);
    font-weight: 900;
    letter-spacing: 2px;
    color: white;
    text-shadow: 0 2px 12px rgba(0,0,0,0.8), 0 1px 3px rgba(0,0,0,0.6), 0 0 20px rgba(0,0,0,0.4);
    margin-top: 4px;
    font-family: var(--font-heading);
    text-transform: uppercase;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.header-info .site-subtitle {
    display: inline-block;
    margin-top: 6px;
    font-size: 13px;
    font-weight: 600;
    color: var(--gold);
    background: rgba(0,0,0,0.2);
    padding: 3px 16px;
    border-radius: var(--radius-full);
    letter-spacing: 1px;
    border: 1px solid rgba(212, 168, 67, 0.3);
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.header-actions .btn-login {
    padding: 8px 18px;
    background: rgba(255,255,255,0.15);
    color: white;
    text-decoration: none;
    border-radius: var(--radius);
    font-size: 13px;
    font-weight: 600;
    border: 1px solid rgba(255,255,255,0.25);
    transition: var(--transition);
}

.header-actions .btn-login:hover {
    background: rgba(255,255,255,0.25);
    color: white;
}

/* === NAVIGATION === */
.header-center-logo {
    position: absolute;
    top: 49%;
    left: 17%;
    transform: translateX(-50%);
    z-index: 5;
}

.header-center-logo img {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid rgba(255,255,255,0.8);
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
}

.header-nav {
    background: transparent;
    position: relative;
    z-index: 20;
    padding: 0;
}

.header-menu {
    display: flex;
    flex-wrap: wrap;
    list-style: none;
    margin: 0;
    padding: 2px 0;
    justify-content: center;
    align-items: stretch;
}

.header-menu > li {
    position: relative;
}

.header-menu > li > a {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 8px 14px;
    color: white;
    text-decoration: none;
    font-size: 25px;
    font-weight: 700;
    white-space: nowrap;
    transition: var(--transition);
    border-bottom: none;
    text-shadow: 0 1px 4px rgba(0,0,0,0.8);
}

.header-menu > li > a:hover,
.header-menu > li > a.active {
    background: rgba(255,255,255,0.15);
    color: white;
}

.header-menu > li > a i {
    font-size: 12px;
    opacity: 0.8;
}

/* === SUBMENU === */
.submenu {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    background: white;
    min-width: 260px;
    box-shadow: var(--shadow-xl);
    border-radius: 0 0 var(--radius) var(--radius);
    padding: 8px 0;
    list-style: none;
    margin: 0;
    z-index: 30;
    border-top: 3px solid var(--primary);
}

.header-menu > li:hover > .submenu {
    display: block;
}

.submenu li {
    margin: 0;
}

.submenu a {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 20px;
    color: var(--text);
    text-decoration: none;
    font-size: 14px;
    transition: var(--transition);
}

.submenu a:hover {
    background: var(--primary-bg);
    color: var(--primary);
    padding-left: 24px;
}

.submenu a i {
    width: 16px;
    text-align: center;
    color: var(--text-muted);
    font-size: 12px;
}

.submenu a:hover i {
    color: var(--primary);
}

/* === MOBILE MENU === */
@media (max-width: 1024px) {
    .header-top {
        flex-direction: column;
        gap: 12px;
        padding: 20px;
        text-align: center;
    }
    
    .header-logo {
        width: 110px;
        height: 110px;
    }
    
    .header-info h1 {
        font-size: 11px;
        letter-spacing: 2px;
    }
    
    .header-info .site-title {
        font-size: 22px;
    }
    
    .header-actions {
        justify-content: center;
    }
    
    .header-menu {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .header-menu > li > a {
        padding: 10px 12px;
        font-size: 12px;
    }
    
    .submenu {
        position: static;
        display: block;
        background: rgba(0,0,0,0.1);
        box-shadow: none;
        border-radius: var(--radius);
        margin: 4px 8px;
        border-top: none;
    }
    
    .header-menu > li:hover > .submenu {
        display: block;
    }
    
    .submenu a {
        color: white;
        padding: 8px 16px;
        font-size: 13px;
    }
    
    .submenu a:hover {
        background: rgba(255,255,255,0.1);
        padding-left: 20px;
    }
}
</style>

<header class="site-header">
    <div class="header-wrap">
        <div class="header-top">
            <div class="header-info">
                <h1>Trang thông tin điện tử</h1>
                <div class="site-title">UBND Xã Long Hiệp</div>
                <span class="site-subtitle">Tỉnh Vĩnh Long</span>
            </div>
            <div class="header-actions">
                <?php if (!$isLoggedIn): ?>
                <a href="dang-nhap.php" class="btn-login"><i class="fas fa-sign-in-alt"></i> Đăng nhập</a>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="header-center-logo">
            <img src="images/logo-longhiep.png" alt="Logo UBND Xã Long Hiệp">
        </div>

        <nav class="header-nav">
            <ul class="header-menu">
                <?php foreach ($menuItems as $item): ?>
                <li>
                    <a href="<?php echo $item['url']; ?>" class="<?php echo isMenuActive($item['pages']); ?>">
                        <?php echo $item['text']; ?>
                    </a>
                    <?php if (isset($item['submenu'])): ?>
                    <ul class="submenu">
                        <?php foreach ($item['submenu'] as $sub): ?>
                        <li><a href="<?php echo $sub['url']; ?>"><?php echo $sub['text']; ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</header>