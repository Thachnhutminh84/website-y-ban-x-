<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

echo "<h2>TEST SỬA TIN - DEBUG</h2>";
echo "<pre>";

echo "=== POST DATA ===\n";
print_r($_POST);

echo "\n=== FILES DATA ===\n";
print_r($_FILES);

echo "\n=== SESSION ===\n";
print_r($_SESSION);

echo "\n=== KIỂM TRA DỮ LIỆU ===\n";
$news_id = isset($_POST['news_id']) ? intval($_POST['news_id']) : 0;
$category = isset($_POST['category']) ? $_POST['category'] : '';
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$date = isset($_POST['date']) ? $_POST['date'] : '';
$summary = isset($_POST['summary']) ? trim($_POST['summary']) : '';
$content = isset($_POST['content']) ? $_POST['content'] : '';

echo "News ID: " . $news_id . "\n";
echo "Category: " . $category . "\n";
echo "Title: " . $title . "\n";
echo "Date: " . $date . "\n";
echo "Summary length: " . strlen($summary) . "\n";
echo "Content length: " . strlen($content) . "\n";
echo "Content preview: " . substr($content, 0, 300) . "\n";

echo "\n=== VALIDATION ===\n";
if ($news_id <= 0) echo "❌ News ID không hợp lệ\n";
if (empty($category)) echo "❌ Category trống\n";
if (empty($title)) echo "❌ Title trống\n";
if (empty($date)) echo "❌ Date trống\n";
if (empty($summary)) echo "❌ Summary trống\n";
if (empty(trim(strip_tags($content)))) echo "❌ Content trống\n";

if ($news_id > 0 && !empty($category) && !empty($title) && !empty($date) && !empty($summary) && !empty(trim(strip_tags($content)))) {
    echo "✅ Tất cả dữ liệu hợp lệ!\n";
}

echo "</pre>";
?>
