<?php
require_once 'config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header("Location: index.php");
    exit();
}

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT title, content, published_at FROM news WHERE id = ? AND status = 'published'");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    header("Location: index.php");
    exit();
}

$row = $result->fetch_assoc();
$stmt->close();
$conn->close();

$title = $row['title'];
$date = date('d/m/Y', strtotime($row['published_at']));
$content = $row['content'];

// Strip inline border styles from tables/cells
$content = preg_replace(
    '/\b(border|border-top|border-bottom|border-left|border-right|border-color|border-style|border-width)\s*:\s*[^;"\']+/i',
    '',
    $content
);
$content = preg_replace('/\bborder\s*=\s*["\'][^"\']*["\']/i', '', $content);

$html = '<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office" 
      xmlns:w="urn:schemas-microsoft-com:office:word" 
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="UTF-8">
<title>' . htmlspecialchars($title) . '</title>
<!--[if gte mso 9]>
<xml>
<w:WordDocument>
<w:View>Print</w:View>
<w:Zoom>100</w:Zoom>
</w:WordDocument>
</xml>
<![endif]-->
<style>
@page {
    size: 21cm 29.7cm;
    margin-top: 2.5cm;
    margin-bottom: 2.5cm;
    margin-left: 3cm;
    margin-right: 2cm;
}
body {
    font-family: "Times New Roman", Times, serif;
    font-size: 14pt;
    line-height: 1.5;
    color: #000;
    margin: 0;
    padding: 0;
    text-align: justify;
}
table {
    border-collapse: collapse;
    width: 100%;
    margin: 6pt 0;
    font-size: 14pt;
}
td, th {
    border: none !important;
    padding: 4pt 8pt;
    vertical-align: top;
    font-size: 14pt;
    font-family: "Times New Roman", Times, serif;
    text-align: justify;
}
p {
    margin: 6pt 0;
    font-size: 14pt;
    font-family: "Times New Roman", Times, serif;
    line-height: 1.5;
    text-align: justify;
}
img {
    max-width: 100%;
    height: auto;
}
</style>
</head>
<body>
' . $content . '
</body>
</html>';

$filename = $title . '.doc';

header('Content-Type: application/msword; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, must-revalidate');

echo $html;
exit();
