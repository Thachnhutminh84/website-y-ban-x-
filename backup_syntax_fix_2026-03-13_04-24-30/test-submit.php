<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: dang-nhap.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>POST Data Received:</h2>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    echo "<h3>Content field:</h3>";
    echo "<p>Length: " . strlen($_POST['content'] ?? '') . "</p>";
    echo "<p>Preview: " . htmlspecialchars(substr($_POST['content'] ?? '', 0, 200)) . "</p>";
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test Submit</title>
</head>
<body>
    <?php include 'menu-don-gian.php'; ?>
    <h1>Test Form Submit</h1>
    
    <form action="test-submit.php" method="POST">
        <div>
            <label>Title:</label>
            <input type="text" name="title" value="Test Title" required>
        </div>
        
        <div>
            <label>Summary:</label>
            <textarea name="summary" required>Test Summary</textarea>
        </div>
        
        <div>
            <label>Content Editor:</label>
            <div id="content-editor" contenteditable="true" style="border: 1px solid #ddd; padding: 10px; min-height: 100px;">
                <p>This is test content with <b>bold</b> text.</p>
            </div>
            <textarea id="content" name="content" style="display: none;"></textarea>
        </div>
        
        <div style="margin-top: 20px;">
            <button type="submit" onclick="document.getElementById('content').value = document.getElementById('content-editor').innerHTML; alert('Content synced: ' + document.getElementById('content').value.length + ' chars'); return true;">
                Submit Test
            </button>
        </div>
    </form>
    
    <script>
    console.log('Test page loaded');
    </script>
</body>
</html>
