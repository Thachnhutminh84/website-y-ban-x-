<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';

$isLoggedIn = authIsLoggedIn();
$currentRole = authCurrentRole();
$displayName = $isLoggedIn ? authDisplayName() : '';

// Lấy ID tin tức từ URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Đọc tin tức từ database
$news = null;

try {
    $conn = getDBConnection();
    
    // Lấy tin tức theo ID
    $stmt = $conn->prepare("SELECT n.*, c.slug as category_slug FROM news n 
                            LEFT JOIN categories c ON n.category_id = c.id 
                            WHERE n.id = ? AND n.status = 'published'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $news = [
            'id' => $row['id'],
            'title' => $row['title'],
            'summary' => $row['summary'],
            'content' => $row['content'],
            'image' => $row['image'],
            'date' => $row['published_at'],
            'category' => $row['category_slug']
        ];
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    // Nếu lỗi database
    echo "<!-- Lỗi database: " . $e->getMessage() . " -->";
}

// Nếu không tìm thấy, chuyển về trang tin tức
if (!$news) {
    header("Location: tin-tuc.php");
    exit();
}

$hasDetailContent = trim(strip_tags((string) $news['content'])) !== '' || stripos((string) $news['content'], '<img') !== false;
$looksLikeWordDocument = stripos((string) $news['content'], 'word-document-content') !== false
    || stripos((string) $news['content'], 'data-doc-import="word"') !== false
    || stripos((string) $news['content'], '<table') !== false;
$showLeadImage = !($looksLikeWordDocument && (!isset($news['image']) || $news['image'] === '' || $news['image'] === 'images/news-default.jpg'));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $news['title']; ?> - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css?v=2.0">
    <link rel="stylesheet" href="word-document.css?v=1.0">
    <script src="dropdown.js"></script>
</head>
<body>
    <header<?php echo $isLoggedIn ? ' class="header--compact"' : ''; ?>>
        <div class="container">
            <div class="logo">
                <img src="images/logo.png" alt="Logo UBND Xã Long Hiệp">
                <div class="header-text">
                    <h1>ỦY BAN NHÂN DÂN XÃ LONG HIỆP</h1>
                    <p>Phục vụ nhân dân - Xây dựng quê hương</p>
                </div>
            </div>
            <nav>
                <ul class="menu">
                    <li><a href="index.php">Trang chủ</a></li>
                    <li class="dropdown">
                        <a href="tin-tuc.php">Tin Tức - Thông Báo</a>
                        <button class="dropdown-toggle" onclick="toggleDropdown(event)">▼</button>
                        <ul class="dropdown-menu">
                            <li><a href="cong-tac-xay-dung-dang.php">Công tác xây dựng Đảng</a></li>
                            <li><a href="mat-tran-doan-the.php">Mặt trận đoàn thể</a></li>
                            <li><a href="an-ninh-trat-tu.php">An ninh trật tự</a></li>
                            <li><a href="tin-tuc-su-kien.php">Tin tức sự kiện</a></li>
                            <li><a href="thong-tin-tuyen-truyen.php">Thông tin tuyên truyền</a></li>
                            <li><a href="giao-duc-dao-tao.php">Giáo dục và đào tạo</a></li>
                        </ul>
                    </li>
                    <li><a href="phong-ban.php">Phòng Ban</a></li>
                    <li><a href="lanh-dao.php">Lãnh đạo</a></li>
                    <li><a href="thu-tuc-hanh-chinh.php">Thủ tục</a></li>
                    <li><a href="lien-he.php">Liên Hệ</a></li>
                    <?php if ($isLoggedIn): ?>
                        <li class="admin-info">
                            👤 <?php echo htmlspecialchars(authRoleLabel($currentRole), ENT_QUOTES, 'UTF-8'); ?>
                            <a href="tin-tuc.php"><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></a>
                            <a href="logout.php">Đăng xuất</a>
                        </li>
                    <?php else: ?>
                        <li><a href="dang-nhap.php" class="login-btn">Đăng nhập</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="container" style="margin-top: 20px;">
                <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; border: 1px solid #c3e6cb;">
                    <?php 
                    echo $_SESSION['success']; 
                    unset($_SESSION['success']);
                    ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="container" style="margin-top: 20px;">
                <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; border: 1px solid #f5c6cb;">
                    <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']);
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <section class="news-detail">
            <div class="container">
                <div class="breadcrumb">
                    <a href="index.php">Trang chủ</a> / <span>Tin tức</span>
                </div>
                
                <article class="detail-content<?php echo $looksLikeWordDocument ? ' detail-content--word' : ''; ?>">
                    <h1><?php echo htmlspecialchars($news['title']); ?></h1>
                    <p class="detail-date">Ngày đăng: <?php echo date('d/m/Y', strtotime($news['date'])); ?></p>
                    
                    <?php if ($showLeadImage): ?>
                    <div class="detail-image">
                        <img src="<?php echo htmlspecialchars($news['image']); ?>" 
                             alt="<?php echo htmlspecialchars($news['title']); ?>" 
                             loading="eager"
                             width="1000"
                             height="600"
                             onerror="this.src='images/news-default.jpg'">
                    </div>
                    <?php endif; ?>
                    
                    <div class="detail-body detail-body--large" data-word-surface="detail">
                        <?php if ($hasDetailContent): ?>
                            <?php echo $news['content']; ?>
                        <?php else: ?>
                            <p class="detail-empty">Noi dung bai viet dang duoc cap nhat.</p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="back-link">
                        <a href="tin-tuc.php?cat=<?php echo $news['category']; ?>" class="btn-back">← Quay lại danh sách tin tức</a>
                    </div>
                </article>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Liên hệ</h3>
                    <p>Địa chỉ: Xã Long Hiệp, Tỉnh Vĩnh Long</p>
                    <p>Điện thoại: [số điện thoại]</p>
                    <p>Email: ubnd@longhiep.gov.vn</p>
                </div>
                <div class="footer-section">
                    <h3>Thông tin</h3>
                    <p>Diện tích: 6.516,77 ha</p>
                    <p>Dân số: 30.272 người</p>
                    <p>Số hộ: 7.337 hộ</p>
                </div>
                <div class="footer-section">
                    <h3>Liên kết</h3>
                    <ul>
                        <li><a href="https://www.chinhphu.vn" target="_blank">Cổng thông tin Chính phủ</a></li>
                        <li><a href="https://vinhlong.gov.vn" target="_blank">UBND Tỉnh Vĩnh Long</a></li>
                        <li><a href="https://longhiep.vinhlong.gov.vn/gioi-thieu/gioi-thieu-tong-quan" target="_blank">UBND Xã Long Hiệp</a></li>
                    </ul>
                </div>
            </div>
            <p class="copyright">&copy; 2026 UBND Xã Long Hiệp. All rights reserved.</p>
        </div>
    </footer>
    <script src="word-document.js?v=1.0"></script>
</body>
</html>
