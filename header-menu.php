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
    ['text' => 'Giới thiệu', 'url' => 'gioi-thieu.php', 'pages' => ['gioi-thieu.php']],
    ['text' => 'Văn bản', 'url' => 'tin-tuc.php', 'pages' => ['tin-tuc.php', 'chi-tiet-tin.php', 'cong-tac-xay-dung-dang.php', 'mat-tran-doan-the.php', 'an-ninh-trat-tu.php', 'tin-tuc-su-kien.php', 'thong-tin-tuyen-truyen.php', 'giao-duc-dao-tao.php', 'video.php'],
        'submenu' => [
            ['text' => 'Công tác xây dựng Đảng', 'url' => 'cong-tac-xay-dung-dang.php'],
            ['text' => 'Mặt trận đoàn thể', 'url' => 'mat-tran-doan-the.php'],
            ['text' => 'An ninh trật tự', 'url' => 'an-ninh-trat-tu.php'],
            ['text' => 'Tin tức sự kiện', 'url' => 'tin-tuc-su-kien.php'],
            ['text' => 'Thông tin tuyên truyền', 'url' => 'thong-tin-tuyen-truyen.php'],
            ['text' => 'Giáo dục và đào tạo', 'url' => 'giao-duc-dao-tao.php'],
            ['text' => 'Video', 'url' => 'video.php']
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
    ['text' => 'Tổ chức nhân sự', 'url' => 'lanh-dao.php', 'pages' => ['lanh-dao.php', 'chi-tiet-lanh-dao.php', 'danh-ba-dien-thoai.php'],
        'submenu' => [
            ['text' => 'Lãnh đạo', 'url' => 'lanh-dao.php'],
            ['text' => 'Danh bạ', 'url' => 'danh-ba-dien-thoai.php']
        ]
    ],
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

        ['text' => '<i class="fas fa-chart-bar"></i> Thống kê lương', 'url' => 'thong-ke-luong-simple.php'],
        ['text' => '<i class="fas fa-clipboard-check"></i> Đánh giá hiệu suất', 'url' => 'quan-ly-danh-gia.php']
    ];
    if (authIsAdmin()) {
        $submenuItems[] = ['text' => '<i class="fas fa-user-check"></i> Phê duyệt tài khoản', 'url' => 'quan-ly-phe-duyet.php'];
    }
    $menuItems[] = [
        'text' => '<i class="fas fa-cog"></i> Quản lý',
        'url' => 'dashboard.php',
        'pages' => ['dashboard.php', 'quan-ly-video.php', 'them-tin.php', 'sua-tin.php', 'quan-ly-danh-gia.php', 'quan-ly-phe-duyet.php', 'advanced-content-manager.php', 'thong-ke-luong-simple.php', 'tin-nhan-lien-he.php'],
        'submenu' => $submenuItems
    ];
}
?>

<link rel="stylesheet" href="header-menu-style.css">

<header class="site-header">
    <div class="header-wrap">
        <div class="header-top">
            <div class="header-logo-wrap">
                <img class="header-logo" src="images/logo-longhiep.png" alt="Logo UBND Xã Long Hiệp">
            </div>
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
    </div>
</header>

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