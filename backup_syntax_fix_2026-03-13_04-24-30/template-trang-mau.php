<?php
// Template mẫu sử dụng header và menu thống nhất
header("Content-Type: text/html; charset=utf-8");
session_start();
require_once 'config.php';
require_once 'auth.php';

// Xử lý logic trang ở đây...
$pageTitle = "Trang Mẫu";
$pageDescription = "Đây là trang mẫu sử dụng header và menu thống nhất";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard-style.css">
    
    <!-- Meta tags SEO -->
    <meta name="description" content="<?php echo $pageDescription; ?>">
    <meta name="keywords" content="UBND, Long Hiệp, Trà Vinh">
    <meta name="author" content="UBND Xã Long Hiệp">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo $pageTitle; ?>">
    <meta property="og:description" content="<?php echo $pageDescription; ?>">
    <meta property="og:type" content="website">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="images/logo.png">
</head>
<body>
    <!-- Header thống nhất -->
    <?php include 'menu-don-gian.php'; ?>

    <!-- Nội dung chính -->
    <main>
        <div class="container">
            <div class="page-header">
                <h1><?php echo $pageTitle; ?></h1>
                <p><?php echo $pageDescription; ?></p>
            </div>
            
            <div class="content-section">
                <h2>Hướng dẫn sử dụng Template</h2>
                
                <div class="instruction-card">
                    <h3>1. 📄 Sử dụng Header thống nhất</h3>
                    <p>Thêm dòng này vào trang của bạn:</p>
                    <code>&lt;?php include 'header-thong-nhat.php'; ?&gt;</code>
                </div>
                
                <div class="instruction-card">
                    <h3>2. 🧭 Menu tự động</h3>
                    <p>Menu sẽ tự động hiển thị khác nhau cho:</p>
                    <ul>
                        <li><strong>Người dùng thường:</strong> Menu công khai với các trang chính</li>
                        <li><strong>Admin đã đăng nhập:</strong> Menu quản trị với các chức năng admin</li>
                    </ul>
                </div>
                
                <div class="instruction-card">
                    <h3>3. 🎨 Tùy chỉnh</h3>
                    <p>Bạn có thể:</p>
                    <ul>
                        <li>Thêm/sửa menu items trong <code>menu-thong-nhat.php</code></li>
                        <li>Tùy chỉnh CSS trong <code>header-thong-nhat.php</code></li>
                        <li>Thêm logic phân quyền cho từng menu item</li>
                    </ul>
                </div>
                
                <div class="instruction-card">
                    <h3>4. 📱 Responsive</h3>
                    <p>Menu đã được tối ưu cho:</p>
                    <ul>
                        <li>Desktop: Menu ngang với dropdown</li>
                        <li>Mobile: Menu dọc thu gọn</li>
                        <li>Tablet: Tự động điều chỉnh</li>
                    </ul>
                </div>
            </div>
            
            <div class="demo-section">
                <h2>Demo các tính năng</h2>
                
                <div class="feature-grid">
                    <div class="feature-card">
                        <h4>🔐 Phân quyền tự động</h4>
                        <p>Menu thay đổi dựa trên trạng thái đăng nhập</p>
                    </div>
                    
                    <div class="feature-card">
                        <h4>📍 Active state</h4>
                        <p>Tự động highlight trang hiện tại</p>
                    </div>
                    
                    <div class="feature-card">
                        <h4>🎯 Dropdown menu</h4>
                        <p>Menu con cho các chức năng phức tạp</p>
                    </div>
                    
                    <div class="feature-card">
                        <h4>⚡ Performance</h4>
                        <p>Tối ưu tốc độ tải và SEO</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer (tùy chọn) -->
    <footer style="background: #2c3e50; color: white; text-align: center; padding: 20px 0; margin-top: 50px;">
        <div class="container">
            <p>&copy; 2024 UBND Xã Long Hiệp. Tất cả quyền được bảo lưu.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="dropdown.js"></script>
    
    <style>
    /* CSS cho trang mẫu */
    .page-header {
        text-align: center;
        padding: 40px 0;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border-radius: 10px;
        margin: 30px 0;
    }
    
    .page-header h1 {
        color: #2c3e50;
        margin-bottom: 10px;
    }
    
    .page-header p {
        color: #666;
        font-size: 18px;
    }
    
    .content-section {
        margin: 40px 0;
    }
    
    .instruction-card {
        background: white;
        padding: 25px;
        border-radius: 10px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-left: 4px solid #007bff;
    }
    
    .instruction-card h3 {
        color: #2c3e50;
        margin-bottom: 15px;
    }
    
    .instruction-card code {
        background: #f8f9fa;
        padding: 8px 12px;
        border-radius: 4px;
        font-family: 'Courier New', monospace;
        color: #e83e8c;
        display: block;
        margin: 10px 0;
    }
    
    .instruction-card ul {
        margin: 10px 0;
        padding-left: 20px;
    }
    
    .instruction-card li {
        margin: 5px 0;
    }
    
    .demo-section {
        margin: 40px 0;
    }
    
    .feature-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .feature-card {
        background: white;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    
    .feature-card:hover {
        transform: translateY(-5px);
    }
    
    .feature-card h4 {
        color: #2c3e50;
        margin-bottom: 10px;
    }
    
    .feature-card p {
        color: #666;
        font-size: 14px;
    }
    </style>
</body>
</html>