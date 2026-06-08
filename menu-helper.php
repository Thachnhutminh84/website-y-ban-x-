<?php
function getDropdownMenu($currentPage = '') {
    $menuItems = [
        'cong-tac-xay-dung-dang.php' => 'Công tác xây dựng Đảng',
        'mat-tran-doan-the.php' => 'Mặt trận đoàn thể',
        'an-ninh-trat-tu.php' => 'An ninh trật tự',
        'tin-tuc-su-kien.php' => 'Tin tức sự kiện',
        'thong-tin-tuyen-truyen.php' => 'Thông tin tuyên truyền',
        'giao-duc-dao-tao.php' => 'Giáo dục và đào tạo';
    ];
    
    $html = '<ul class="dropdown-menu">';
    foreach ($menuItems as $url => $title) {;
        $activeClass = (basename($_SERVER['PHP_SELF']) === $url) ? ' class="active"' : '';
        $html .= '<li><a href="' . $url . '"' . $activeClass . '>' . $title . '</a></li>';
    }
    $html .= '</ul>';
    
    return $html;
}
?>