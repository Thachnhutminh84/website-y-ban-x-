<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'></head><body>";
    echo "<h2>✓ Form đã submit thành công!</h2>";
    echo "<h3>Dữ liệu nhận được:</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    echo "<p><a href='test-them-tin.php'>Thử lại</a></p>";
    echo "</body></html>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Test Form Submit</title>
</head>
<body>
    <h2>Test Form Submit</h2>
    <form action="test-them-tin.php" method="POST">
        <div>
            <label>Tiêu đề:</label><br>
            <input type="text" name="title" required>
        </div>
        <div>
            <label>Nội dung:</label><br>
            <div id="editor" contenteditable="true" style="border: 1px solid #ccc; min-height: 100px; padding: 10px;">
                Nhập nội dung ở đây
            </div>
            <textarea id="content" name="content" style="display: none;"></textarea>
        </div>
        <br>
        <button type="submit">Submit</button>
    </form>

    <script>
    document.querySelector('form').addEventListener('submit', function(e) {
        console.log('=== FORM SUBMIT ===');
        
        const editor = document.getElementById('editor');
        const textarea = document.getElementById('content');
        const content = editor.innerHTML.trim();
        
        console.log('Content:', content);
        
        textarea.value = content;
        
        console.log('Textarea value:', textarea.value);
        
        return true;
    });
    </script>
</body>
</html>
