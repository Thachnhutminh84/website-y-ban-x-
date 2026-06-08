<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'auth.php';

// Nếu chưa đăng nhập, chuyển về trang đăng nhập
if (!authIsLoggedIn()) {
    header('Location: dang-nhap.php');
    exit();
}

// Nếu đã được phê duyệt, chuyển về trang chủ
if (authIsApproved()) {
    header('Location: index.php');
    exit();
}

$displayName = authDisplayName();
$username = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chờ phê duyệt - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css?v=2.1">
    <style>
        .pending-container {
            max-width: 600px;
            margin: 100px auto;
            padding: 40px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .pending-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        
        .pending-container h1 {
            color: #f39c12;
            margin-bottom: 20px;
        }
        
        .pending-container p {
            color: #666;
            line-height: 1.8;
            margin-bottom: 15px;
        }
        
        .user-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .user-info strong {
            color: #333;
        }
        
        .btn-logout {
            display: inline-block;
            padding: 12px 30px;
            background: #e74c3c;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
            transition: all 0.3s;
        }
        
        .btn-logout:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }
        
        .contact-info {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #e9ecef;
        }
        
        .contact-info p {
            font-size: 14px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="pending-container">
        <div class="pending-icon">⏳</div>
        <h1>Tài khoản đang chờ phê duyệt</h1>
        
        <div class="user-info">
            <p><strong>Tên đăng nhập:</strong> <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>Họ tên:</strong> <?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
        
        <p>Tài khoản của bạn đã được đăng ký thành công và đang chờ quản trị viên phê duyệt.</p>
        <p>Bạn sẽ nhận được thông báo qua email khi tài khoản được kích hoạt.</p>
        <p>Trong thời gian chờ phê duyệt, bạn chưa thể sử dụng các chức năng của website.</p>
        
        <a href="dang-xuat.php" class="btn-logout">Đăng xuất</a>
        
        <div class="contact-info">
            <p><strong>Cần hỗ trợ?</strong></p>
            <p>Vui lòng liên hệ quản trị viên qua email: admin@longhiep.vinhlong.gov.vn</p>
            <p>Hoặc gọi điện thoại: (0272) 3xxx xxx</p>
        </div>
    </div>
</body>
</html>
