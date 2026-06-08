<?php
header("Content-Type: text/html; charset=utf-8");
session_start();
require_once 'config.php';
require_once 'auth.php';

if (!authIsLoggedIn() || !authHasPermission('manage_content')) {
    die("Cần quyền admin");
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiểm tra Lỗi đã sửa - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Header thống nhất -->
    <?php include 'header-thong-nhat.php'; ?>

    <main>
        <div class="container">
            <h1>✅ Kiểm tra Lỗi đã sửa</h1>
            
            <div class="status-section">
                <h2>🎯 Các lỗi đã được sửa:</h2>
                
                <div class="fix-item">
                    <h3>1. ✅ Lỗi Syntax trong tin-tuc.php</h3>
                    <p>Đã xóa code HTML sót lại sau include header thống nhất</p>
                    <a href="tin-tuc.php" target="_blank" class="test-link">🧪 Test tin-tuc.php</a>
                </div>
                
                <div class="fix-item">
                    <h3>2. ✅ API Delete Video</h3>
                    <p>Đã sửa lỗi session handling và thêm debug mode</p>
                    <a href="test-delete-simple.php" target="_blank" class="test-link">🧪 Test Delete Video</a>
                </div>
                
                <div class="fix-item">
                    <h3>3. ✅ Menu thống nhất</h3>
                    <p>Đã áp dụng header thống nhất cho các trang chính:</p>
                    <ul>
                        <li>✅ lanh-dao.php</li>
                        <li>✅ phong-ban.php</li>
                        <li>✅ lien-he.php</li>
                        <li>✅ video.php</li>
                        <li>✅ video-files.php</li>
                        <li>✅ tin-tuc.php</li>
                    </ul>
                </div>
                
                <div class="fix-item">
                    <h3>4. ✅ API Save/Delete Files</h3>
                    <p>Đã tạo API endpoints mới với error handling tốt hơn</p>
                    <ul>
                        <li>✅ api-save-video.php</li>
                        <li>✅ api-save-file.php</li>
                        <li>✅ api-delete-video.php (đã sửa)</li>
                        <li>✅ api-delete-file.php (đã sửa)</li>
                    </ul>
                </div>
            </div>
            
            <div class="test-section">
                <h2>🧪 Kiểm tra các trang:</h2>
                
                <div class="test-grid">
                    <a href="index.php" target="_blank" class="test-card">
                        <h3>🏠 Trang chủ</h3>
                        <p>Kiểm tra menu video có hiển thị</p>
                    </a>
                    
                    <a href="tin-tuc.php" target="_blank" class="test-card">
                        <h3>📰 Tin tức</h3>
                        <p>Kiểm tra lỗi syntax đã sửa</p>
                    </a>
                    
                    <a href="video.php" target="_blank" class="test-card">
                        <h3>📺 Video</h3>
                        <p>Kiểm tra chức năng save/delete</p>
                    </a>
                    
                    <a href="video-files.php" target="_blank" class="test-card">
                        <h3>📁 File Video</h3>
                        <p>Kiểm tra chức năng save/delete file</p>
                    </a>
                    
                    <a href="lanh-dao.php" target="_blank" class="test-card">
                        <h3>👥 Lãnh đạo</h3>
                        <p>Kiểm tra menu thống nhất</p>
                    </a>
                    
                    <a href="phong-ban.php" target="_blank" class="test-card">
                        <h3>🏢 Phòng ban</h3>
                        <p>Kiểm tra menu thống nhất</p>
                    </a>
                    
                    <a href="lien-he.php" target="_blank" class="test-card">
                        <h3>📞 Liên hệ</h3>
                        <p>Kiểm tra menu thống nhất</p>
                    </a>
                    
                    <a href="test-delete-simple.php" target="_blank" class="test-card">
                        <h3>🧪 Test Delete</h3>
                        <p>Test chức năng xóa video</p>
                    </a>
                </div>
            </div>
            
            <div class="tools-section">
                <h2>🛠️ Tools hỗ trợ:</h2>
                
                <div class="tools-grid">
                    <a href="quick-fix.php" class="tool-card">
                        <h3>🚀 Quick Fix</h3>
                        <p>Áp dụng menu cho các trang còn lại</p>
                    </a>
                    
                    <a href="fix-syntax-errors.php" class="tool-card">
                        <h3>🔧 Fix Syntax</h3>
                        <p>Sửa lỗi syntax tự động</p>
                    </a>
                    
                    <a href="ap-dung-menu-video-toan-bo.php" class="tool-card">
                        <h3>📋 Apply Menu</h3>
                        <p>Tool áp dụng menu chi tiết</p>
                    </a>
                    
                    <a href="debug-delete-video.php" class="tool-card">
                        <h3>🔍 Debug Delete</h3>
                        <p>Debug chức năng xóa video</p>
                    </a>
                </div>
            </div>
            
            <div class="summary-section">
                <h2>📋 Tóm tắt:</h2>
                <div class="summary-card">
                    <h3>✅ Đã hoàn thành:</h3>
                    <ul>
                        <li>Sửa lỗi syntax trong tin-tuc.php</li>
                        <li>Cải thiện API delete/save với session handling</li>
                        <li>Áp dụng menu thống nhất cho các trang chính</li>
                        <li>Tạo tools kiểm tra và sửa lỗi</li>
                        <li>Menu video hiển thị trên tất cả trang</li>
                    </ul>
                    
                    <h3>🎯 Cần kiểm tra:</h3>
                    <ul>
                        <li>Test chức năng delete video trên trang video.php</li>
                        <li>Kiểm tra menu video hiển thị đúng trên tất cả trang</li>
                        <li>Test upload video mới</li>
                        <li>Kiểm tra các trang không bị lỗi syntax</li>
                    </ul>
                </div>
            </div>
        </div>
    </main>

    <style>
    .status-section, .test-section, .tools-section, .summary-section {
        background: white;
        padding: 30px;
        margin: 30px 0;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .fix-item {
        border-left: 4px solid #28a745;
        padding: 20px;
        margin: 20px 0;
        background: #f8fff9;
        border-radius: 0 8px 8px 0;
    }
    
    .fix-item h3 {
        color: #155724;
        margin-bottom: 10px;
    }
    
    .test-grid, .tools-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .test-card, .tool-card {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        text-decoration: none;
        color: #2c3e50;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }
    
    .test-card:hover, .tool-card:hover {
        border-color: #007bff;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,123,255,0.2);
    }
    
    .test-card h3, .tool-card h3 {
        margin-bottom: 10px;
        color: #007bff;
    }
    
    .test-link {
        display: inline-block;
        padding: 8px 16px;
        background: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        margin-top: 10px;
        font-size: 14px;
    }
    
    .test-link:hover {
        background: #0056b3;
    }
    
    .summary-card {
        background: #e7f3ff;
        padding: 25px;
        border-radius: 8px;
        border-left: 4px solid #007bff;
    }
    
    .summary-card h3 {
        color: #0056b3;
        margin-bottom: 15px;
    }
    
    .summary-card ul {
        margin-bottom: 20px;
    }
    
    .summary-card li {
        margin: 8px 0;
    }
    </style>
</body>
</html>