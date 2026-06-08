# Hướng dẫn phân quyền Read-Only cho Người dân

## Đã hoàn thành ✅
1. **quan-ly-danh-gia.php** - Ẩn nút Sửa/Xóa/Đánh giá, chỉ còn Lịch sử
2. **quan-ly-video.php** - Ẩn nút Thêm/Sửa/Xóa/Nổi bật  
3. **quan-ly-nhan-su.php** - Ẩn nút Thêm/Sửa/Xóa, chỉ còn Xem
4. **auth.php** - Thêm function `authIsReadOnly()` và `authRenderIfNotReadOnly()`
5. **read-only-notice.php** - File hiển thị thông báo read-only

## Cần sửa tiếp

### quan-ly-phong-ban.php
Thêm sau dòng `authRequireCanBo();`:
```php
$isReadOnly = authIsReadOnly();
```

Sau `<div class="page-header">` thêm:
```php
<?php include 'read-only-notice.php'; ?>
```

Wrap nút "Thêm phòng ban" (dòng ~284):
```php
<?php if (!$isReadOnly): ?>
<a href="them-phong-ban.php" class="btn btn-primary">
    <i class="fas fa-plus"></i> Thêm phòng ban
</a>
<?php endif; ?>
```

Wrap nút Sửa/Xóa trong bảng (tìm `btn-edit` và `btn-delete`):
```php
<?php if (!$isReadOnly): ?>
    <!-- Nút Sửa/Xóa -->
<?php endif; ?>
```

### quan-ly-luong-thuong.php
Thêm đầu file (sau `authRequireCanBo();`):
```php
$isReadOnly = authIsReadOnly();
```

Sau header thêm:
```php
<?php include 'read-only-notice.php'; ?>
```

Wrap nút "Thêm thưởng mới" (dòng ~371):
```php
<?php if (!$isReadOnly): ?>
<a href="#" class="btn-primary" onclick="themThuongMoi(); return false;">
    <i class="fas fa-plus-circle"></i>
    Thêm thưởng mới
</a>
<?php endif; ?>
```

Wrap các nút Thêm/Sửa/Xóa thưởng trong bảng (dòng ~418-428):
```php
<?php if (!$isReadOnly): ?>
<button class="btn-sm btn-warning" onclick="themThuong(...)">
    <i class="fas fa-plus-circle"></i> Thêm thưởng
</button>
<?php if ($latest_bonus): ?>
<button class="btn-sm" onclick="suaThuongNhanh(...)">
    <i class="fas fa-edit"></i> Sửa
</button>
<button class="btn-sm" onclick="xoaThuongNhanh(...)">
    <i class="fas fa-trash"></i> Xóa
</button>
<?php endif; ?>
<?php endif; ?>
```

### thong-ke-luong-simple.php  
Thêm đầu file:
```php
$isReadOnly = authIsReadOnly();
```

Sau header thêm:
```php
<?php include 'read-only-notice.php'; ?>
```

Wrap nút "Sửa lương" (dòng ~703):
```php
<?php if (!$isReadOnly): ?>
<button class="btn btn-primary btn-sm" onclick="suaLuong(...)">
    <i class="fas fa-edit"></i> Sửa
</button>
<?php endif; ?>
```

## Cách test
1. Đăng nhập với tài khoản `user_type = 'nguoi_dan'`
2. Vào các trang quản lý
3. Kiểm tra:
   - Thấy thông báo read-only màu tím
   - Không thấy nút Thêm/Sửa/Xóa
   - Chỉ thấy nút Xem/Lịch sử

## SQL để tạo user test
```sql
UPDATE users SET user_type = 'nguoi_dan' WHERE username = 'test_nguoi_dan';
```
