<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: dang-nhap.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm tin - Đơn giản</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 8px; }
        button { padding: 10px 20px; background: green; color: white; border: none; cursor: pointer; font-size: 16px; }
    </style>
</head>
<body>
    <?php include 'menu-don-gian.php'; ?>
    <h2>Thêm tin tức - Đơn giản</h2>
    
    <form action="xu-ly-them-tin.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Danh mục:</label>
            <select name="category">
                <option value="su-kien">Tin tức sự kiện</option>
                <option value="xay-dung-dang">Xây dựng Đảng</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Tiêu đề:</label>
            <input type="text" name="title" value="Test tin tức">
        </div>
        
        <div class="form-group">
            <label>Ngày đăng:</label>
            <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>">
        </div>
        
        <div class="form-group">
            <label>Tóm tắt:</label>
            <textarea name="summary" rows="3">Đây là tóm tắt test</textarea>
        </div>
        
        <div class="form-group">
            <label>Nội dung:</label>
            <textarea name="content" rows="10">Đây là nội dung test với nhiều chữ hơn để kiểm tra xem form có submit được không.</textarea>
        </div>
        
        <button type="submit">THÊM TIN NGAY</button>
    </form>
    
    <script>
    document.querySelector('form').addEventListener('submit', function(e) {
        alert('Form đang submit!');
    });
    </script>
</body>
</html>
