<?php
header("Content-Type: text/html; charset=utf-8");
session_start();
require_once 'config.php';
require_once 'auth.php';

// Kiểm tra quyền admin
if (!authIsLoggedIn() || !authHasPermission('manage_content')) {
    die("❌ Cần quyền admin để chạy script này. <a href='dang-nhap.php'>Đăng nhập</a>");
}

echo "<h1>🔍 Kiểm tra và Sửa lỗi Website</h1>";

// 1. Kiểm tra các file quan trọng có tồn tại không
$importantFiles = [
    'index.php' => 'Trang chủ',
    'tin-tuc.php' => 'Tin tức',
    'video.php' => 'Video',
    'video-files.php' => 'File video',
    'lanh-dao.php' => 'Lãnh đạo',
    'phong-ban.php' => 'Phòng ban',
    'lien-he.php' => 'Liên hệ',
    'dashboard.php' => 'Dashboard',
    'quan-ly-video.php' => 'Quản lý video',
    'them-video-moi.php' => 'Thêm video',
    'dang-nhap.php' => 'Đăng nhập'
];

echo "<h2>📋 Kiểm tra file quan trọng</h2>";
$missingFiles = [];
$existingFiles = [];

foreach ($importantFiles as $file => $name) {
    if (file_exists($file)) {
        $existingFiles[] = $file;
        echo "<p style='color: green;'>✅ <strong>$file</strong> - $name</p>";
    } else {
        $missingFiles[] = $file;
        echo "<p style='color: red;'>❌ <strong>$file</strong> - $name (THIẾU)</p>";
    }
}

// 2. Kiểm tra menu hiện tại
echo "<h2>🎯 Kiểm tra menu hiện tại</h2>";
$allFiles = glob('*.php');
$menuStats = [
    'menu_don_gian' => 0,
    'header_thong_nhat' => 0,
    'menu_cu' => 0,
    'khong_menu' => 0
];

foreach ($allFiles as $file) {
    if (in_array($file, ['menu-don-gian.php', 'kiem-tra-va-sua-loi.php'])) continue;
    
    $content = file_get_contents($file);
    
    if (strpos($content, 'menu-don-gian.php') !== false) {
        $menuStats['menu_don_gian']++;
    } elseif (strpos($content, 'header-thong-nhat.php') !== false) {
        $menuStats['header_thong_nhat']++;
    } elseif (preg_match('/<header[^>]*>.*?<\/header>/s', $content) || preg_match('/<nav[^>]*>.*?<\/nav>/s', $content)) {
        $menuStats['menu_cu']++;
    } else {
        $menuStats['khong_menu']++;
    }
}

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<h3>📊 Thống kê menu:</h3>";
echo "<ul>";
echo "<li><strong style='color: green;'>Menu đơn giản:</strong> {$menuStats['menu_don_gian']} file</li>";
echo "<li><strong style='color: blue;'>Header thống nhất cũ:</strong> {$menuStats['header_thong_nhat']} file</li>";
echo "<li><strong style='color: orange;'>Menu cũ:</strong> {$menuStats['menu_cu']} file</li>";
echo "<li><strong style='color: red;'>Không có menu:</strong> {$menuStats['khong_menu']} file</li>";
echo "</ul>";
echo "</div>";

// 3. Tạo file thiếu nếu cần
if (!empty($missingFiles)) {
    echo "<h2>🛠️ Tạo file thiếu</h2>";
    
    foreach ($missingFiles as $file) {
        if ($file === 'dang-nhap.php') {
            // Tạo trang đăng nhập đơn giản
            $loginContent = '<?php
header("Content-Type: text/html; charset=utf-8");
session_start();
require_once "config.php";
require_once "auth.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";
    
    if (!empty($username) && !empty($password)) {
        $conn = getDBConnection();
        $user = authFindUserByUsername($conn, $username);
        
        if ($user && password_verify($password, $user["password"]) && $user["status"] === "active") {
            authSetUserSession($user);
            authUpdateLastLogin($conn, $user["id"]);
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Tên đăng nhập hoặc mật khẩu không đúng";
        }
        $conn->close();
    } else {
        $error = "Vui lòng nhập đầy đủ thông tin";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include "menu-don-gian.php"; ?>
    
    <main>
        <div class="container">
            <div class="login-form">
                <h2>Đăng nhập</h2>
                <?php if (isset($error)): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Tên đăng nhập:</label>
                        <input type="text" name="username" required>
                    </div>
                    <div class="form-group">
                        <label>Mật khẩu:</label>
                        <input type="password" name="password" required>
                    </div>
                    <button type="submit">Đăng nhập</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>';
            
            file_put_contents($file, $loginContent);
            echo "<p style='color: green;'>✅ Đã tạo $file</p>";
        }
    }
}

// 4. Sửa lỗi menu
echo "<h2>🔧 Sửa lỗi menu</h2>";

if ($menuStats['menu_cu'] > 0 || $menuStats['header_thong_nhat'] > 0) {
    echo "<p>Phát hiện " . ($menuStats['menu_cu'] + $menuStats['header_thong_nhat']) . " file cần cập nhật menu.</p>";
    echo "<a href='ap-dung-menu-don-gian.php' class='btn-primary' style='display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0;'>🚀 Áp dụng menu đơn giản</a>";
} else {
    echo "<p style='color: green;'>✅ Tất cả file đã có menu phù hợp</p>";
}

// 5. Kiểm tra database
echo "<h2>🗄️ Kiểm tra Database</h2>";
try {
    $conn = getDBConnection();
    
    // Kiểm tra bảng videos
    $result = $conn->query("SHOW TABLES LIKE 'videos'");
    if ($result && $result->num_rows > 0) {
        $videoCount = $conn->query("SELECT COUNT(*) as count FROM videos WHERE is_active = 1")->fetch_assoc()['count'];
        echo "<p style='color: green;'>✅ Bảng videos: $videoCount video</p>";
    } else {
        echo "<p style='color: red;'>❌ Bảng videos chưa tồn tại</p>";
        echo "<a href='setup-video-don-gian.php' class='btn-warning' style='display: inline-block; padding: 8px 16px; background: #ffc107; color: #212529; text-decoration: none; border-radius: 5px; margin: 5px 0;'>🛠️ Tạo bảng videos</a>";
    }
    
    // Kiểm tra bảng users
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if ($result && $result->num_rows > 0) {
        $userCount = $conn->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'")->fetch_assoc()['count'];
        echo "<p style='color: green;'>✅ Bảng users: $userCount user</p>";
    } else {
        echo "<p style='color: red;'>❌ Bảng users chưa tồn tại</p>";
    }
    
    $conn->close();
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Lỗi kết nối database: " . $e->getMessage() . "</p>";
}

// 6. Kiểm tra thư mục quan trọng
echo "<h2>📁 Kiểm tra thư mục</h2>";
$importantDirs = ['videos', 'images'];

foreach ($importantDirs as $dir) {
    if (is_dir($dir)) {
        $fileCount = count(glob($dir . '/*'));
        echo "<p style='color: green;'>✅ Thư mục $dir: $fileCount file</p>";
    } else {
        mkdir($dir, 0755, true);
        echo "<p style='color: blue;'>📁 Đã tạo thư mục $dir</p>";
    }
}

// 7. Tóm tắt và hành động
echo "<h2>📋 Tóm tắt và Hành động</h2>";

if (count($missingFiles) > 0 || $menuStats['menu_cu'] > 0 || $menuStats['header_thong_nhat'] > 0) {
    echo "<div style='background: #fff3cd; padding: 20px; border-radius: 8px; border-left: 4px solid #ffc107; margin: 20px 0;'>";
    echo "<h3 style='color: #856404;'>⚠️ Cần khắc phục:</h3>";
    echo "<ul style='color: #856404;'>";
    
    if (count($missingFiles) > 0) {
        echo "<li>Có " . count($missingFiles) . " file quan trọng bị thiếu</li>";
    }
    
    if ($menuStats['menu_cu'] > 0 || $menuStats['header_thong_nhat'] > 0) {
        echo "<li>Có " . ($menuStats['menu_cu'] + $menuStats['header_thong_nhat']) . " file cần cập nhật menu</li>";
    }
    
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='margin: 20px 0;'>";
    echo "<a href='ap-dung-menu-don-gian.php' class='btn-primary' style='display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>🚀 Áp dụng menu đơn giản</a>";
    echo "<a href='demo-menu-don-gian.php' class='btn-secondary' style='display: inline-block; padding: 12px 24px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px; margin: 5px;'>👀 Xem demo menu</a>";
    echo "</div>";
    
} else {
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; border-left: 4px solid #28a745; margin: 20px 0;'>";
    echo "<h3 style='color: #155724;'>🎉 Website đã ổn!</h3>";
    echo "<p style='color: #155724;'>Tất cả file quan trọng đều tồn tại và có menu phù hợp.</p>";
    echo "</div>";
}

// 8. Links kiểm tra
echo "<h2>🔗 Kiểm tra các trang</h2>";
echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0;'>";

foreach ($existingFiles as $file) {
    $name = $importantFiles[$file];
    echo "<a href='$file' target='_blank' style='display: block; padding: 15px; background: white; border: 2px solid #e9ecef; border-radius: 8px; text-decoration: none; color: #2c3e50; text-align: center; transition: all 0.3s ease;' onmouseover='this.style.borderColor=\"#007bff\"' onmouseout='this.style.borderColor=\"#e9ecef\"'>";
    echo "<strong>$name</strong><br>";
    echo "<small style='color: #666;'>$file</small>";
    echo "</a>";
}

echo "</div>";

?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.6;
    margin: 0;
    padding: 20px;
    background: #f8f9fa;
}

h1 {
    color: white;
    text-align: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 30px;
    border-radius: 10px;
    margin-bottom: 30px;
}

h2 {
    color: #2c3e50;
    border-bottom: 2px solid #007bff;
    padding-bottom: 10px;
    margin-top: 40px;
}

.container {
    max-width: 1000px;
    margin: 0 auto;
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.btn-primary, .btn-secondary, .btn-warning {
    display: inline-block;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 5px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary { background: #007bff; color: white; }
.btn-secondary { background: #6c757d; color: white; }
.btn-warning { background: #ffc107; color: #212529; }

.btn-primary:hover { background: #0056b3; }
.btn-secondary:hover { background: #545b62; }
.btn-warning:hover { background: #e0a800; }
</style>