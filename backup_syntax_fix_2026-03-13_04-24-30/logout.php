<?php
session_start();

// Xóa tất cả session
session_unset();
session_destroy();

// Chuyển về trang đăng nhập
header("Location: dang-nhap.php?logout=1");
exit();
?>
