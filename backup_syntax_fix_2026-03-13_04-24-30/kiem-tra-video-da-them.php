<?php
header("Content-Type: text/html; charset=utf-8");
require_once 'config.php';

echo "<h1>🔍 Kiểm tra Video Đã Thêm</h1>";

// 1. Kiểm tra database
echo "<h2>1. 📊 Kiểm tra Database</h2>";
try {
    $conn = getDBConnection();
    
    // Đếm video trong database
    $result = $conn->query("SELECT COUNT(*) as total FROM videos");
    $totalDB = $result->fetch_assoc()['total'];
    echo "<p><strong>Tổng video trong database:</strong> $totalDB</p>";
    
    if ($totalDB > 0) {
        echo "<h3>Danh sách video trong database:</h3>";
        $result = $conn->query("SELECT id, title, video_url, video_type, is_active, created_at FROM videos ORDER BY created_at DESC LIMIT 10");
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>ID</th><th>Tiêu đề</th><th>URL</th><th>Loại</th><th>Trạng thái</th><th>Ngày tạo</th></tr>";
        while ($row = $result->fetch_assoc()) {
            $status = $row['is_active'] ? '✅ Active' : '❌ Inactive';
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>" . htmlspecialchars($row['title']) . "</td>";
            echo "<td>" . htmlspecialchars($row['video_url']) . "</td>";
            echo "<td>{$row['video_type']}</td>";
            echo "<td>$status</td>";
            echo "<td>{$row['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ Không có video nào trong database!</p>";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi database: " . $e->getMessage() . "</p>";
}

// 2. Kiểm tra thư mục videos
echo "<h2>2. 📁 Kiểm tra Thư mục videos/</h2>";
if (is_dir('videos/')) {
    $files = glob('videos/*');
    echo "<p><strong>Tổng file trong thư mục videos/:</strong> " . count($files) . "</p>";
    
    if (!empty($files)) {
        echo "<h3>Danh sách file trong thư mục:</h3>";
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>STT</th><th>Tên file</th><th>Kích thước</th><th>Ngày sửa đổi</th><th>Link</th></tr>";
        foreach ($files as $index => $file) {
            if (is_file($file)) {
                $fileName = basename($file);
                $fileSize = number_format(filesize($file) / 1024, 2) . ' KB';
                $fileDate = date('d/m/Y H:i', filemtime($file));
                echo "<tr>";
                echo "<td>" . ($index + 1) . "</td>";
                echo "<td>$fileName</td>";
                echo "<td>$fileSize</td>";
                echo "<td>$fileDate</td>";
                echo "<td><a href='$file' target='_blank'>Xem</a></td>";
                echo "</tr>";
            }
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ Thư mục videos/ trống!</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Thư mục videos/ không tồn tại!</p>";
}

// 3. Kiểm tra form thêm video
echo "<h2>3. 📝 Kiểm tra Form Thêm Video</h2>";
if (file_exists('them-video.php')) {
    echo "<p style='color: green;'>✅ File them-video.php tồn tại</p>";
    echo "<p><a href='them-video.php' target='_blank'>Mở form thêm video</a></p>";
} else {
    echo "<p style='color: red;'>❌ File them-video.php không tồn tại</p>";
}

// 4. Kết luận
echo "<h2>4. 💡 Kết luận</h2>";
if ($totalDB > 0 && !empty($files)) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; color: #155724;'>";
    echo "<p><strong>✅ Video đã được thêm thành công!</strong></p>";
    echo "<p>Có $totalDB video trong database và " . count($files) . " file trong thư mục.</p>";
    echo "<p><strong>Vấn đề:</strong> Trang video.php không hiển thị video. Có thể do:</p>";
    echo "<ul>";
    echo "<li>Truy vấn SQL trong video.php có vấn đề</li>";
    echo "<li>Video bị đặt trạng thái inactive</li>";
    echo "<li>Cache trình duyệt</li>";
    echo "</ul>";
    echo "</div>";
} elseif ($totalDB == 0 && !empty($files)) {
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; color: #856404;'>";
    echo "<p><strong>⚠️ File đã upload nhưng chưa lưu vào database!</strong></p>";
    echo "<p>Có " . count($files) . " file trong thư mục nhưng không có trong database.</p>";
    echo "<p><strong>Nguyên nhân:</strong> Form thêm video chỉ upload file mà không lưu thông tin vào database.</p>";
    echo "</div>";
} elseif ($totalDB > 0 && empty($files)) {
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; color: #856404;'>";
    echo "<p><strong>⚠️ Có thông tin trong database nhưng không có file!</strong></p>";
    echo "<p>Có $totalDB video trong database nhưng thư mục videos/ trống.</p>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: #721c24;'>";
    echo "<p><strong>❌ Chưa có video nào!</strong></p>";
    echo "<p>Không có video trong database và thư mục videos/ trống.</p>";
    echo "<p><strong>Hướng dẫn:</strong> Vào <a href='them-video.php'>them-video.php</a> để thêm video.</p>";
    echo "</div>";
}

echo "<h2>5. 🔗 Links hữu ích</h2>";
echo "<div style='display: flex; gap: 10px; flex-wrap: wrap;'>";
echo "<a href='them-video.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>➕ Thêm video</a>";
echo "<a href='video.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📺 Trang video</a>";
echo "<a href='video-files.php' style='background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📁 Tất cả file</a>";
echo "<a href='quan-ly-video.php' style='background: #ffc107; color: black; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>⚙️ Quản lý</a>";
echo "</div>";
?>