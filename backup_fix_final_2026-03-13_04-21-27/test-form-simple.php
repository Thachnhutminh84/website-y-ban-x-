<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'></head><body>
    <?php include 'menu-don-gian.php'; ?>";
    echo "<h2 style='color: green;'>✓ FORM ĐÃ SUBMIT THÀNH CÔNG!</h2>";
    echo "<h3>Dữ liệu nhận được:</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    echo "<p><a href='test-form-simple.php'>Thử lại</a></p>";
    echo "</body></html>";
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test Form Submit</title>
</head>
<body>
    <h2>Test Form Submit - Đơn giản</h2>
    
    <form action="test-form-simple.php" method="POST">
        <div style="margin: 10px 0;">
            <label>Category:</label><br>
            <select name="category" required>
                <option value="">-- Chọn --</option>
                <option value="su-kien">Sự kiện</option>
            </select>
        </div>
        
        <div style="margin: 10px 0;">
            <label>Title:</label><br>
            <input type="text" name="title" required value="Test Title">
        </div>
        
        <div style="margin: 10px 0;">
            <label>Date:</label><br>
            <input type="date" name="date" required value="2025-03-06">
        </div>
        
        <div style="margin: 10px 0;">
            <label>Summary:</label><br>
            <textarea name="summary" required>Test summary</textarea>
        </div>
        
        <div style="margin: 10px 0;">
            <label>Content:</label><br>
            <textarea name="content" required>Test content with some text</textarea>
        </div>
        
        <button type="submit" style="padding: 10px 20px; background: green; color: white; border: none; cursor: pointer;">
            Submit Test
        </button>
    </form>
    
    <script>
    document.querySelector('form').addEventListener('submit', function(e) {
        console.log('Form submit event fired');
    });
    
    document.querySelector('button').addEventListener('click', function() {
        console.log('Button clicked');
    });
    </script>
</body>
</html>
