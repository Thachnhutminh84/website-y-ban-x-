<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'auth.php';

$registerError = $_SESSION['register_error'] ?? null;
$registerSuccess = $_SESSION['register_success'] ?? null;
$registerOld = $_SESSION['register_old'] ?? [
    'full_name' => '',
    'username' => '',
    'email' => '',
    'role' => 'editor'
];

unset($_SESSION['register_error'], $_SESSION['register_success'], $_SESSION['register_old']);

$isLoggedIn = authIsLoggedIn();
$isAdmin = authIsAdmin();
$currentRole = authCurrentRole();
$displayName = $isLoggedIn ? authDisplayName() : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css?v=2.1">
    <script src="dropdown.js"></script>
</head>
<body>
        <?php include 'menu-don-gian.php'; ?>

    <main>
        <section class="login-section">
            <div class="container">
                <div class="login-box">
                    <h2>Đăng ký tài khoản</h2>
                    <p class="login-subtitle">
                        <?php if ($isAdmin): ?>
                            Tạo tài khoản mới cho biên tập viên hoặc quản trị viên.
                        <?php else: ?>
                            Tài khoản đăng ký mới mặc định có quyền biên tập: thêm tin và sửa tin.
                        <?php endif; ?>
                    </p>
                    <p class="login-subtitle" style="margin-top: -10px; font-size: 15px;">
                        Nếu hệ thống chưa có tài khoản nào, tài khoản đầu tiên đăng ký sẽ trở thành quản trị viên.
                    </p>

                    <?php if ($registerError): ?>
                        <div class="error-message"><?php echo htmlspecialchars($registerError, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>

                    <?php if ($registerSuccess): ?>
                        <div class="success-message"><?php echo htmlspecialchars($registerSuccess, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>

                    <form action="process-register.php" method="POST" class="login-form">
                        <div class="form-group">
                            <label for="full_name">Họ và tên</label>
                            <input type="text" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($registerOld['full_name'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Nhập họ tên người dùng">
                        </div>
                        <div class="form-group">
                            <label for="username">Tên đăng nhập</label>
                            <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($registerOld['username'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Nhập tên đăng nhập">
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($registerOld['email'], ENT_QUOTES, 'UTF-8'); ?>" placeholder="Nhập email">
                        </div>
                        <?php if ($isAdmin): ?>
                            <div class="form-group">
                                <label for="role">Phân quyền</label>
                                <select id="role" name="role">
                                    <option value="editor" <?php echo ($registerOld['role'] ?? 'editor') === 'editor' ? 'selected' : ''; ?>>Biên tập viên</option>
                                    <option value="admin" <?php echo ($registerOld['role'] ?? 'editor') === 'admin' ? 'selected' : ''; ?>>Quản trị viên</option>
                                </select>
                            </div>
                        <?php else: ?>
                            <input type="hidden" name="role" value="editor">
                        <?php endif; ?>
                        <div class="form-group">
                            <label for="password">Mật khẩu</label>
                            <input type="password" id="password" name="password" required placeholder="Nhập mật khẩu tối thiểu 6 ký tự">
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Nhập lại mật khẩu</label>
                            <input type="password" id="confirm_password" name="confirm_password" required placeholder="Nhập lại mật khẩu">
                        </div>
                        <button type="submit" class="btn-login">Đăng ký</button>
                        <a href="dang-nhap.php" class="forgot-password">Đã có tài khoản? Đăng nhập</a>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
