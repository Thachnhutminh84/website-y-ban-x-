<?php
session_start();
require_once 'auth.php';

// Clear remember token before destroying session
authClearRememberToken();

// Regenerate session ID trước khi destroy
session_regenerate_id(true);

// Xóa tất cả session
session_unset();
session_destroy();

// Chuyển về trang đăng nhập
header("Location: dang-nhap.php?logout=1");
exit();
?>