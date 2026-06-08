<?php
echo "<h1>🔍 Kiểm tra Cấu hình PHP</h1>";

$configs = [
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'max_execution_time' => ini_get('max_execution_time'),
    'max_input_time' => ini_get('max_input_time'),
    'memory_limit' => ini_get('memory_limit'),
    'file_uploads' => ini_get('file_uploads') ? 'Enabled' : 'Disabled'
];

echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>Cấu hình</th><th>Giá trị hiện tại</th><th>Yêu cầu</th><th>Trạng thái</th></tr>";

$requirements = [
    'upload_max_filesize' => '500M',
    'post_max_size' => '500M',
    'max_execution_time' => '600',
    'max_input_time' => '600',
    'memory_limit' => '512M',
    'file_uploads' => 'Enabled'
];

foreach ($configs as $key => $value) {
    $required = $requirements[$key];
    $status = '✅';
    $color = 'green';
    
    if ($key == 'upload_max_filesize' || $key == 'post_max_size') {
        $currentBytes = return_bytes($value);
        $requiredBytes = return_bytes($required);
        if ($currentBytes < $requiredBytes) {
            $status = '❌';
            $color = 'red';
        }
    } elseif ($key == 'file_uploads') {
        if ($value != 'Enabled') {
            $status = '❌';
            $color = 'red';
        }
    }
    
    echo "<tr>";
    echo "<td><strong>$key</strong></td>";
    echo "<td>$value</td>";
    echo "<td>$required</td>";
    echo "<td style='color: $color;'>$status</td>";
    echo "</tr>";
}

echo "</table>";

function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;
    switch($last) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    return $val;
}

echo "<h2>💡 Hướng dẫn tăng giới hạn</h2>";
echo "<p>Nếu giá trị chưa đủ, bạn cần:</p>";
echo "<ol>";
echo "<li><strong>Cách 1:</strong> Sửa file <code>php.ini</code> (thường ở <code>C:\\xampp\\php\\php.ini</code>)</li>";
echo "<li><strong>Cách 2:</strong> Tạo file <code>.htaccess</code> trong thư mục website</li>";
echo "<li><strong>Cách 3:</strong> Tạo file <code>php.ini</code> trong thư mục website</li>";
echo "<li>Sau đó <strong>restart Apache</strong></li>";
echo "</ol>";

echo "<h2>📝 Nội dung cần thêm vào php.ini:</h2>";
echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>";
echo "upload_max_filesize = 500M\n";
echo "post_max_size = 500M\n";
echo "max_execution_time = 600\n";
echo "max_input_time = 600\n";
echo "memory_limit = 512M";
echo "</pre>";

echo "<p style='margin-top: 20px;'>";
echo "<a href='them-video-moi.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>➕ Thêm video</a> ";
echo "<a href='phpinfo.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>ℹ️ Xem phpinfo()</a>";
echo "</p>";
?>