<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'auth.php';

$isLoggedIn = authIsLoggedIn();
$currentRole = authCurrentRole();
$displayName = $isLoggedIn ? authDisplayName() : '';
$loginError = isset($_GET['error']) ? ($_SESSION['login_error'] ?? null) : null;
$logoutMessage = isset($_GET['logout']) ? 'Đăng xuất thành công!' : null;
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css?v=2.1">
    <script src="dropdown.js"></script>
</head>
<body>
    <?php include 'header-menu.php'; ?>

    <main>
        <section class="login-section">
            <div class="container">
                <div class="login-box">
                    <h2>Đăng nhập hệ thống</h2>
                    <p class="login-subtitle">Dành cho cán bộ UBND Xã Long Hiệp</p>
                    
                    <?php if ($loginError): ?>
                        <div class="error-message"><?php echo htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($logoutMessage): ?>
                        <div class="success-message"><?php echo htmlspecialchars($logoutMessage, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>
                    
                    <form action="process-login.php" method="POST" class="login-form">
                        <?php echo SecurityHelper::csrfField(); ?>
                        <div class="form-group">
                            <label for="username">Tên đăng nhập</label>
                            <input type="text" id="username" name="username" required placeholder="Nhập tên đăng nhập">
                        </div>
                        <div class="form-group">
                            <label for="password">Mật khẩu</label>
                            <input type="password" id="password" name="password" required placeholder="Nhập mật khẩu">
                        </div>
                        <button type="submit" class="btn-login" style="display: block !important; width: 100% !important; padding: 14px !important; background: var(--primary) !important; color: white !important; border: none !important; border-radius: 8px !important; font-size: 16px !important; font-weight: 600 !important; cursor: pointer !important; margin-top: 10px !important;">Đăng nhập</button>
                        <a href="dang-ky.php" class="forgot-password" style="color: var(--primary) !important; font-weight: 600 !important; display: block !important; text-align: center !important; margin-top: 15px !important; text-decoration: none !important;">Chưa có tài khoản? Đăng ký</a>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>
    <?php include 'quick-actions-toolbar.php'; ?>
</body>
</html>
