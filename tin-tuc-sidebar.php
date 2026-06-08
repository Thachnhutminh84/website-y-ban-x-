<?php
// Sidebar menu cho trang tin tức

$newsCategories = [
    ['name' => 'TIN TỨC', 'url' => 'tin-tuc.php', 'icon' => '📰'],
    ['name' => 'CHUYỂN ĐỔI SỐ', 'url' => 'chuyen-doi-so.php', 'icon' => '💻'],
    ['name' => 'VĂN HÓA - XÃ HỘI', 'url' => 'van-hoa-xa-hoi.php', 'icon' => '🎭'],
    ['name' => 'CÔNG TÁC XÂY DỰNG ĐẢNG', 'url' => 'cong-tac-xay-dung-dang.php', 'icon' => '🏛️'],
    ['name' => 'MẶT TRẬN ĐOÀN THỂ', 'url' => 'mat-tran-doan-the.php', 'icon' => '👥'],
    ['name' => 'AN NINH TRẬT TỰ', 'url' => 'an-ninh-trat-tu.php', 'icon' => '🛡️'],
    ['name' => 'TIN TỨC SỰ KIỆN', 'url' => 'tin-tuc-su-kien.php', 'icon' => '📅'],
    ['name' => 'THÔNG TIN TUYÊN TRUYỀN', 'url' => 'thong-tin-tuyen-truyen.php', 'icon' => '📢'],
    ['name' => 'GIÁO DỤC VÀ ĐÀO TẠO', 'url' => 'giao-duc-dao-tao.php', 'icon' => '🎓']
];

$currentPage = basename($_SERVER['PHP_SELF']);
?>

<style>
.news-sidebar {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
}

.news-sidebar-title {
    background: linear-gradient(135deg, #0066cc 0%, #0099ff 100%);
    color: white;
    padding: 15px 20px;
    font-size: 18px;
    font-weight: 700;
    text-align: center;
    margin: 0;
}

.news-categories {
    list-style: none;
    margin: 0;
    padding: 0;
}

.news-categories li {
    border-bottom: 1px solid #e0e0e0;
}

.news-categories li:last-child {
    border-bottom: none;
}

.news-categories a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px 20px;
    color: #555;
    text-decoration: none;
    font-size: 15px;
    font-weight: 600;
    transition: all 0.3s;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.news-categories a:hover {
    background: #f0f8ff;
    color: #0066cc;
    padding-left: 25px;
}

.news-categories a.active {
    background: #0066cc;
    color: white;
}

.news-icon {
    font-size: 20px;
    flex-shrink: 0;
}

@media (max-width: 768px) {
    .news-sidebar {
        margin-bottom: 20px;
    }
    
    .news-categories a {
        font-size: 14px;
        padding: 12px 15px;
    }
}
</style>

<aside class="news-sidebar">
    <h3 class="news-sidebar-title">DANH MỤC TIN TỨC</h3>
    <ul class="news-categories">
        <?php foreach ($newsCategories as $cat): ?>
        <li>
            <a href="<?php echo $cat['url']; ?>" class="<?php echo $currentPage === basename($cat['url']) ? 'active' : ''; ?>">
                <span class="news-icon"><?php echo $cat['icon']; ?></span>
                <span><?php echo $cat['name']; ?></span>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
</aside>
