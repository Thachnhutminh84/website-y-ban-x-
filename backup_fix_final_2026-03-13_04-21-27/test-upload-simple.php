<?php
// Test đơn giản cho upload video
header("Content-Type: text/html; charset=utf-8");
session_start();
require_once 'config.php';

echo "<h1>🧪 Test Upload Video Đơn Giản</h1>";

// Kiểm tra thư mục videos
echo "<h2>📁 Kiểm tra Thư mục</h2>";
if (!is_dir('videos/')) {
    mkdir('videos/', 0755, true);
    echo "<p style='color: orange;'>⚠️ Đã tạo thư mục videos/</p>";
} else {
    echo "<p style='color: green;'>✅ Thư mục videos/ đã tồn tại</p>";
}

if (is_writable('videos/')) {
    echo "<p style='color: green;'>✅ Thư mục videos/ có quyền ghi</p>";
} else {
    echo "<p style='color: red;'>❌ Thư mục videos/ không có quyền ghi</p>";
}

// Liệt kê file hiện có
echo "<h2>📋 File hiện có trong videos/</h2>";
$files = glob('videos/*');
if (empty($files)) {
    echo "<p>📁 Thư mục trống</p>";
} else {
    echo "<ul>";
    foreach ($files as $file) {
        $size = filesize($file);
        $date = date('d/m/Y H:i', filemtime($file));
        echo "<li><strong>" . basename($file) . "</strong> - " . number_format($size) . " bytes - $date</li>";
    }
    echo "</ul>";
}

// Form upload đơn giản
echo "<h2>📤 Test Upload</h2>";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['test_file'])) {
    $uploadedFile = $_FILES['test_file'];
    
    if ($uploadedFile['error'] == UPLOAD_ERR_OK) {
        $fileName = $uploadedFile['name'];
        $fileTmpName = $uploadedFile['tmp_name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $allowedExtensions = ['mp4', 'webm', 'ogg', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'wav', 'mp3'];
        
        if (in_array($fileExtension, $allowedExtensions)) {
            $safeFileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($fileName, PATHINFO_FILENAME));
            $newFileName = $safeFileName . '_' . time() . '.' . $fileExtension;
            $uploadPath = 'videos/' . $newFileName;
            
            if (move_uploaded_file($fileTmpName, $uploadPath)) {
                echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; color: #155724;'>";
                echo "<h3>✅ Upload thành công!</h3>";
                echo "<p><strong>File gốc:</strong> $fileName</p>";
                echo "<p><strong>File lưu:</strong> $newFileName</p>";
                echo "<p><strong>Đường dẫn:</strong> $uploadPath</p>";
                echo "<p><strong>Kích thước:</strong> " . number_format(filesize($uploadPath)) . " bytes</p>";
                echo "<p><a href='$uploadPath' target='_blank'>👀 Xem file</a></p>";
                echo "</div>";
            } else {
                echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;'>";
                echo "<h3>❌ Lỗi upload!</h3>";
                echo "<p>Không thể di chuyển file từ thư mục tạm</p>";
                echo "</div>";
            }
        } else {
            echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; color: #856404;'>";
            echo "<h3>⚠️ Định dạng không hỗ trợ!</h3>";
            echo "<p>Chỉ chấp nhận: " . implode(', ', $allowedExtensions) . "</p>";
            echo "</div>";
        }
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;'>";
        echo "<h3>❌ Lỗi upload!</h3>";
        echo "<p>Mã lỗi: " . $uploadedFile['error'] . "</p>";
        echo "</div>";
    }
    
    echo "<hr>";
}
?>

<form method="POST" enctype="multipart/form-data" style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;">
    <h3>📤 Chọn file để test upload</h3>
    <p>
        <label for="test_file">Chọn file video/audio:</label><br>
        <input type="file" id="test_file" name="test_file" accept="video/*,audio/*,.mp4,.webm,.ogg,.avi,.mov,.wmv,.flv,.mkv,.wav,.mp3" required>
    </p>
    <p>
        <button type="submit" style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
            🚀 Upload Test
        </button>
    </p>
    <small>Hỗ trợ: MP4, WebM, OGG, AVI, MOV, WMV, FLV, MKV, WAV, MP3</small>
</form>

<div style="margin-top: 30px; padding: 15px; background: #e7f3ff; border-radius: 8px;">
    <h3>💡 Hướng dẫn</h3>
    <ol>
        <li>Chọn file video từ máy tính</li>
        <li>Click "Upload Test"</li>
        <li>Kiểm tra kết quả upload</li>
        <li>File sẽ được lưu trong thư mục videos/</li>
        <li>Sau đó có thể xem trong <a href="video-files.php">video-files.php</a></li>
    </ol>
</div>

<div style="margin-top: 20px;">
    <h3>🔗 Links hữu ích</h3>
    <a href="them-video.php" style="background: #28a745; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; margin-right: 10px;">➕ Form thêm video chính</a>
    <a href="video-files.php" style="background: #17a2b8; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; margin-right: 10px;">📁 Xem tất cả file</a>
    <a href="video.php" style="background: #007bff; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px;">📺 Trang video</a>
</div>

<p style="margin-top: 30px; text-align: center; color: #666;">
    <small>Test completed at <?php echo date('Y-m-d H:i:s'); ?></small>
</p>