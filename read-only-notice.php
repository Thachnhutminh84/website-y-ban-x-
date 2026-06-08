<?php
// Helper: Hiển thị thông báo read-only cho người dân
if (function_exists('authIsReadOnly') && authIsReadOnly()): 
?>
<div style="background: var(--gradient-primary); color: white; padding: 15px 20px; border-radius: 8px; margin: 20px 0; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <i class="fas fa-eye"></i>
    <strong>Chế độ xem:</strong> Bạn đang đăng nhập với tài khoản <strong>Người dân</strong>. Bạn chỉ có thể xem thông tin, không thể thêm/sửa/xóa.
</div>
<?php endif; ?>
