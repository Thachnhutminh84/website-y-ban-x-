<?php
// Breadcrumb System for UBND Xã Long Hiệp Website

function generateBreadcrumb($currentPage = '', $customItems = []) {
    $breadcrumbs = [];
    
    // Always start with home
    $breadcrumbs[] = [
        'title' => 'Trang chủ',
        'url' => 'index.php',
        'active' => false
    ];
    
    // Add custom items if provided
    if (!empty($customItems)) {
        foreach ($customItems as $item) {
            $breadcrumbs[] = $item;
        }
        return $breadcrumbs;
    }
    
    // Auto-generate based on current page
    $currentFile = basename($_SERVER['PHP_SELF']);
    
    switch ($currentFile) {
        case 'tin-tuc.php':
            $breadcrumbs[] = [
                'title' => 'Tin tức',
                'url' => '',
                'active' => true
            ];
            break;
            
        case 'chi-tiet-tin.php':
            $breadcrumbs[] = [
                'title' => 'Tin tức',
                'url' => 'tin-tuc.php',
                'active' => false
            ];
            $breadcrumbs[] = [
                'title' => 'Chi tiết tin tức',
                'url' => '',
                'active' => true
            ];
            break;
            
        case 'phong-ban.php':
            $breadcrumbs[] = [
                'title' => 'Phòng ban',
                'url' => '',
                'active' => true
            ];
            break;
            
        case 'lanh-dao.php':
            $breadcrumbs[] = [
                'title' => 'Lãnh đạo',
                'url' => '',
                'active' => true
            ];
            break;
            
        case 'chi-tiet-lanh-dao.php':
            $breadcrumbs[] = [
                'title' => 'Lãnh đạo',
                'url' => 'lanh-dao.php',
                'active' => false
            ];
            $breadcrumbs[] = [
                'title' => 'Chi tiết lãnh đạo',
                'url' => '',
                'active' => true
            ];
            break;
            
        case 'video.php':
            $breadcrumbs[] = [
                'title' => 'Video',
                'url' => '',
                'active' => true
            ];
            break;
            
        case 'thu-tuc-hanh-chinh.php':
            $breadcrumbs[] = [
                'title' => 'Thủ tục hành chính',
                'url' => '',
                'active' => true
            ];
            break;
            
        case 'lien-he.php':
            $breadcrumbs[] = [
                'title' => 'Liên hệ',
                'url' => '',
                'active' => true
            ];
            break;
            
        case 'dashboard.php':
        case 'enhanced-dashboard.php':
            $breadcrumbs[] = [
                'title' => 'Dashboard',
                'url' => '',
                'active' => true
            ];
            break;
            
        case 'them-tin.php':
            $breadcrumbs[] = [
                'title' => 'Dashboard',
                'url' => 'dashboard.php',
                'active' => false
            ];
            $breadcrumbs[] = [
                'title' => 'Thêm tin tức',
                'url' => '',
                'active' => true
            ];
            break;
            
        case 'sua-tin.php':
            $breadcrumbs[] = [
                'title' => 'Dashboard',
                'url' => 'dashboard.php',
                'active' => false
            ];
            $breadcrumbs[] = [
                'title' => 'Sửa tin tức',
                'url' => '',
                'active' => true
            ];
            break;
            
        // Category pages
        case 'cong-tac-xay-dung-dang.php':
            $breadcrumbs[] = [
                'title' => 'Tin tức',
                'url' => 'tin-tuc.php',
                'active' => false
            ];
            $breadcrumbs[] = [
                'title' => 'Công tác xây dựng Đảng',
                'url' => '',
                'active' => true
            ];
            break;
            
        case 'mat-tran-doan-the.php':
            $breadcrumbs[] = [
                'title' => 'Tin tức',
                'url' => 'tin-tuc.php',
                'active' => false
            ];
            $breadcrumbs[] = [
                'title' => 'Mặt trận đoàn thể',
                'url' => '',
                'active' => true
            ];
            break;
            
        case 'an-ninh-trat-tu.php':
            $breadcrumbs[] = [
                'title' => 'Tin tức',
                'url' => 'tin-tuc.php',
                'active' => false
            ];
            $breadcrumbs[] = [
                'title' => 'An ninh trật tự',
                'url' => '',
                'active' => true
            ];
            break;
            
        case 'tin-tuc-su-kien.php':
            $breadcrumbs[] = [
                'title' => 'Tin tức',
                'url' => 'tin-tuc.php',
                'active' => false
            ];
            $breadcrumbs[] = [
                'title' => 'Tin tức sự kiện',
                'url' => '',
                'active' => true
            ];
            break;
            
        case 'thong-tin-tuyen-truyen.php':
            $breadcrumbs[] = [
                'title' => 'Tin tức',
                'url' => 'tin-tuc.php',
                'active' => false
            ];
            $breadcrumbs[] = [
                'title' => 'Thông tin tuyên truyền',
                'url' => '',
                'active' => true
            ];
            break;
            
        case 'giao-duc-dao-tao.php':
            $breadcrumbs[] = [
                'title' => 'Tin tức',
                'url' => 'tin-tuc.php',
                'active' => false
            ];
            $breadcrumbs[] = [
                'title' => 'Giáo dục và đào tạo',
                'url' => '',
                'active' => true
            ];
            break;
            
        // Department pages
        case 'phong-ubnd.php':
        case 'phong-hdnn.php':
        case 'phong-kinh-te.php':
            $breadcrumbs[] = [
                'title' => 'Phòng ban',
                'url' => 'phong-ban.php',
                'active' => false
            ];
            $breadcrumbs[] = [
                'title' => 'Chi tiết phòng ban',
                'url' => '',
                'active' => true
            ];
            break;
    }
    
    return $breadcrumbs;
}

function renderBreadcrumb($currentPage = '', $customItems = []) {
    $breadcrumbs = generateBreadcrumb($currentPage, $customItems);
    
    if (empty($breadcrumbs)) {
        return '';
    }
    
    $html = '<nav class="breadcrumb-nav" aria-label="Breadcrumb">';
    $html .= '<div class="container">';
    $html .= '<ol class="breadcrumb">';
    
    foreach ($breadcrumbs as $index => $item) {
        $isLast = ($index === count($breadcrumbs) - 1);
        
        $html .= '<li class="breadcrumb-item' . ($item['active'] ? ' active' : '') . '">';
        
        if (!$item['active'] && !empty($item['url'])) {
            $html .= '<a href="' . htmlspecialchars($item['url']) . '">';
            $html .= htmlspecialchars($item['title']);
            $html .= '</a>';
        } else {
            $html .= '<span>' . htmlspecialchars($item['title']) . '</span>';
        }
        
        if (!$isLast) {
            $html .= '<span class="breadcrumb-separator">›</span>';
        }
        
        $html .= '</li>';
    }
    
    $html .= '</ol>';
    $html .= '</div>';
    $html .= '</nav>';
    
    return $html;
}
?>

<style>
.breadcrumb-nav {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    padding: 10px 0;
    font-size: 14px;
}

.breadcrumb {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 5px;
}

.breadcrumb-item {
    display: flex;
    align-items: center;
    gap: 5px;
}

.breadcrumb-item a {
    color: #3498db;
    text-decoration: none;
    transition: color 0.3s ease;
}

.breadcrumb-item a:hover {
    color: #2980b9;
    text-decoration: underline;
}

.breadcrumb-item.active span {
    color: #6c757d;
    font-weight: 500;
}

.breadcrumb-separator {
    color: #6c757d;
    margin: 0 5px;
    font-weight: 300;
}

@media (max-width: 768px) {
    .breadcrumb-nav {
        font-size: 12px;
        padding: 8px 0;
    }
    
    .breadcrumb {
        gap: 3px;
    }
    
    .breadcrumb-separator {
        margin: 0 3px;
    }
}
</style>