<?php
header("Content-Type: text/html; charset=utf-8");
session_start();
require_once 'config.php';
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Menu Đơn giản - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Menu đơn giản mới -->
    <?php include 'menu-don-gian.php'; ?>

    <main>
        <div class="container">
            <div class="demo-header">
                <h1>🎨 Demo Menu Đơn giản</h1>
                <p>Xem trước menu mới - đơn giản, gọn gàng và thống nhất</p>
            </div>

            <div class="demo-section">
                <h2>✨ Tính năng Menu mới</h2>
                
                <div class="feature-grid">
                    <div class="feature-card">
                        <h3>🎯 Đơn giản</h3>
                        <p>Menu gọn gàng, không lộn xộn như trước</p>
                    </div>
                    
                    <div class="feature-card">
                        <h3>📱 Responsive</h3>
                        <p>Hoạt động tốt trên cả desktop và mobile</p>
                    </div>
                    
                    <div class="feature-card">
                        <h3>🎬 Video đầy đủ</h3>
                        <p>Menu video với 4 tùy chọn rõ ràng</p>
                    </div>
                    
                    <div class="feature-card">
                        <h3>👤 Phân quyền</h3>
                        <p>Menu khác nhau cho admin và user</p>
                    </div>
                    
                    <div class="feature-card">
                        <h3>🎨 Đẹp mắt</h3>
                        <p>Thiết kế hiện đại với gradient</p>
                    </div>
                    
                    <div class="feature-card">
                        <h3>⚡ Nhanh</h3>
                        <p>Code tối ưu, tải nhanh</p>
                    </div>
                </div>
            </div>

            <div class="demo-section">
                <h2>🎬 Menu Video</h2>
                <p>Hover vào menu "Video" ở trên để xem dropdown với 4 tùy chọn:</p>
                
                <div class="video-options">
                    <div class="option-item">
                        <h4>📺 Video chính thức</h4>
                        <p>Video từ database đã được duyệt</p>
                    </div>
                    
                    <div class="option-item">
                        <h4>📁 Tất cả file video</h4>
                        <p>Tất cả file video trong thư mục</p>
                    </div>
                    
                    <div class="option-item">
                        <h4>🎵 File audio</h4>
                        <p>Các file âm thanh (WAV, MP3)</p>
                    </div>
                    
                    <div class="option-item">
                        <h4>⭐ Video nổi bật</h4>
                        <p>Video được đánh dấu nổi bật</p>
                    </div>
                </div>
            </div>

            <?php if (authIsLoggedIn() && authHasPermission('manage_content')): ?>
                <div class="demo-section">
                    <h2>🚀 Áp dụng cho Website</h2>
                    <p>Bạn thích menu này? Hãy áp dụng cho toàn bộ website:</p>
                    
                    <div class="action-buttons">
                        <a href="ap-dung-menu-don-gian.php" class="btn-primary">
                            🚀 Áp dụng menu đơn giản
                        </a>
                        <a href="verify-unified-menu.php" class="btn-secondary">
                            🔍 Kiểm tra menu hiện tại
                        </a>
                    </div>
                    
                    <div class="warning-box">
                        <h4>⚠️ Lưu ý:</h4>
                        <ul>
                            <li>Script sẽ tự động backup tất cả file trước khi thay đổi</li>
                            <li>Thay thế tất cả menu cũ bằng menu đơn giản này</li>
                            <li>Có thể khôi phục từ backup nếu cần</li>
                        </ul>
                    </div>
                </div>
            <?php else: ?>
                <div class="demo-section">
                    <h2>🔐 Cần đăng nhập</h2>
                    <p>Để áp dụng menu này cho website, bạn cần đăng nhập với quyền admin.</p>
                    <a href="dang-nhap.php" class="btn-primary">🔐 Đăng nhập</a>
                </div>
            <?php endif; ?>

            <div class="demo-section">
                <h2>📋 So sánh</h2>
                
                <div class="comparison">
                    <div class="compare-item old">
                        <h3>❌ Menu cũ</h3>
                        <ul>
                            <li>Nhiều phiên bản khác nhau</li>
                            <li>Lộn xộn, không thống nhất</li>
                            <li>Khó bảo trì</li>
                            <li>Không responsive tốt</li>
                            <li>Menu video rời rạc</li>
                        </ul>
                    </div>
                    
                    <div class="compare-item new">
                        <h3>✅ Menu mới</h3>
                        <ul>
                            <li>Một phiên bản duy nhất</li>
                            <li>Gọn gàng, thống nhất</li>
                            <li>Dễ bảo trì và cập nhật</li>
                            <li>Responsive hoàn hảo</li>
                            <li>Menu video tập trung</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <style>
    .demo-header {
        text-align: center;
        padding: 40px 0;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        margin: 30px 0;
    }

    .demo-section {
        background: white;
        padding: 30px;
        margin: 30px 0;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .feature-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin: 20px 0;
    }

    .feature-card {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #007bff;
        text-align: center;
    }

    .feature-card h3 {
        color: #007bff;
        margin-bottom: 10px;
    }

    .video-options {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin: 20px 0;
    }

    .option-item {
        background: #e7f3ff;
        padding: 15px;
        border-radius: 8px;
        border-left: 4px solid #17a2b8;
    }

    .option-item h4 {
        color: #17a2b8;
        margin-bottom: 8px;
    }

    .action-buttons {
        display: flex;
        gap: 15px;
        margin: 20px 0;
        flex-wrap: wrap;
    }

    .btn-primary, .btn-secondary {
        padding: 12px 24px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-block;
    }

    .btn-primary {
        background: #007bff;
        color: white;
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .btn-primary:hover {
        background: #0056b3;
        transform: translateY(-1px);
    }

    .btn-secondary:hover {
        background: #545b62;
        transform: translateY(-1px);
    }

    .warning-box {
        background: #fff3cd;
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid #ffc107;
        margin: 20px 0;
    }

    .warning-box h4 {
        color: #856404;
        margin-bottom: 10px;
    }

    .warning-box ul {
        color: #856404;
        margin: 0;
        padding-left: 20px;
    }

    .comparison {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin: 20px 0;
    }

    .compare-item {
        padding: 20px;
        border-radius: 8px;
    }

    .compare-item.old {
        background: #f8d7da;
        border-left: 4px solid #dc3545;
    }

    .compare-item.new {
        background: #d4edda;
        border-left: 4px solid #28a745;
    }

    .compare-item h3 {
        margin-bottom: 15px;
    }

    .compare-item.old h3 {
        color: #721c24;
    }

    .compare-item.new h3 {
        color: #155724;
    }

    .compare-item ul {
        margin: 0;
        padding-left: 20px;
    }

    .compare-item.old ul {
        color: #721c24;
    }

    .compare-item.new ul {
        color: #155724;
    }

    @media (max-width: 768px) {
        .comparison {
            grid-template-columns: 1fr;
        }
        
        .action-buttons {
            flex-direction: column;
        }
    }
    </style>
</body>
</html>