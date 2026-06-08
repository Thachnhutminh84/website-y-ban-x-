<?php
header("Content-Type: text/html; charset=utf-8");
session_start();
require_once 'config.php';
require_once 'auth.php';

$isLoggedIn = authIsLoggedIn();
$currentRole = authCurrentRole();
$displayName = $isLoggedIn ? authDisplayName() : '';

// Lấy danh sách lãnh đạo từ database
$leaders = [];
try {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM leaders WHERE is_active = 1 ORDER BY display_order ASC, id ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $leaders[] = $row;
    }
    
    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    // Fallback data nếu không kết nối được database
    $leaders = [
        [
            'id' => 1,
            'name' => 'Nguyễn Khánh Hòa',
            'position' => 'Chủ tịch UBND xã Long Hiệp',
            'image_path' => 'images/leaders/nguyen-khanh-hoa.jpg',
            'responsibilities' => 'Phụ trách chung công tác điều hành UBND xã'
        ],
        [
            'id' => 2,
            'name' => 'Trần Văn Mười',
            'position' => 'Bí thư Đảng ủy xã Long Hiệp',
            'image_path' => 'images/leaders/tran-van-muoi.jpg',
            'responsibilities' => 'Phụ trách công tác lãnh đạo Đảng bộ'
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lãnh đạo - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
    <script src="dropdown.js"></script>
</head>
<body>
    <!-- Header thống nhất -->
    <?php include 'menu-don-gian.php'; ?>

    <main>
        <section class="leaders">
            <div class="container">
                <h2 class="section-title">Ban Lãnh Đạo UBND Xã Long Hiệp</h2>
                <div class="leaders-grid">
                    <?php foreach ($leaders as $leader): ?>
                        <div class="leader-card">
                            <div class="leader-photo">
                                <img src="<?php echo htmlspecialchars($leader['image_path'] ?? 'images/leader-default.jpg', ENT_QUOTES, 'UTF-8'); ?>" 
                                     alt="<?php echo htmlspecialchars($leader['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                     onerror="this.src='images/leader-default.jpg'">
                            </div>
                            <div class="leader-info">
                                <h3><?php echo htmlspecialchars($leader['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                <p class="position"><?php echo htmlspecialchars($leader['position'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <?php if (!empty($leader['responsibilities'])): ?>
                                    <p class="description"><?php echo htmlspecialchars($leader['responsibilities'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <?php endif; ?>
                                <div class="contact-info">
                                    <?php if (!empty($leader['phone'])): ?>
                                        <p><strong>Điện thoại:</strong> <?php echo htmlspecialchars($leader['phone'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($leader['email'])): ?>
                                        <p><strong>Email:</strong> <?php echo htmlspecialchars($leader['email'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <?php endif; ?>
                                </div>
                                <a href="chi-tiet-lanh-dao.php?id=<?php echo $leader['id']; ?>" class="btn-view-detail">Xem chi tiết</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if (empty($leaders)): ?>
                    <div class="empty-panel">
                        <p>📋 Chưa có thông tin lãnh đạo nào được cập nhật.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>
