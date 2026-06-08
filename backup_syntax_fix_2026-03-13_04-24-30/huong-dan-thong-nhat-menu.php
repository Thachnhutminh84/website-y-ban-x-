<?php
header("Content-Type: text/html; charset=utf-8");
session_start();
require_once 'config.php';
require_once 'auth.php';

$isLoggedIn = authIsLoggedIn();
$hasPermission = $isLoggedIn && authHasPermission('manage_content');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hướng dẫn Thống nhất Menu - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Header thống nhất -->
    <?php include 'menu-don-gian.php'; ?>

    <main>
        <div class="container">
            <div class="guide-header">
                <h1>📋 Hướng dẫn Thống nhất Menu cho Website</h1>
                <p>Hướng dẫn chi tiết để áp dụng menu thống nhất với chức năng video đầy đủ cho toàn bộ website</p>
            </div>

            <div class="step-section">
                <h2>🎯 Mục tiêu</h2>
                <div class="goal-card">
                    <p>Tạo menu thống nhất cho tất cả các trang trong website với:</p>
                    <ul>
                        <li>📺 Menu video đầy đủ (Video chính thức, File video, Audio, Nổi bật)</li>
                        <li>🎨 Giao diện nhất quán trên mọi trang</li>
                        <li>👤 Menu khác nhau cho admin và user thường</li>
                        <li>📱 Responsive design cho mobile</li>
                        <li>🔧 Dễ bảo trì và cập nhật</li>
                    </ul>
                </div>
            </div>

            <div class="step-section">
                <h2>🚀 Bước 1: Kiểm tra hiện trạng</h2>
                <div class="step-card">
                    <p>Trước tiên, hãy kiểm tra tình trạng menu hiện tại:</p>
                    
                    <?php if ($hasPermission): ?>
                        <div class="action-buttons">
                            <a href="verify-unified-menu.php" class="btn-primary">
                                🔍 Kiểm tra menu hiện tại
                            </a>
                        </div>
                    <?php else: ?>
                        <p class="warning">⚠️ Cần đăng nhập với quyền admin để sử dụng tools kiểm tra.</p>
                    <?php endif; ?>
                    
                    <div class="info-box">
                        <h4>📊 Tool sẽ hiển thị:</h4>
                        <ul>
                            <li>Số trang đã có menu thống nhất</li>
                            <li>Số trang còn dùng menu cũ</li>
                            <li>Số trang chưa có menu</li>
                            <li>Danh sách chi tiết từng file</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="step-section">
                <h2>🛠️ Bước 2: Áp dụng menu thống nhất</h2>
                <div class="step-card">
                    <p>Sử dụng tool tự động để áp dụng menu cho tất cả trang:</p>
                    
                    <?php if ($hasPermission): ?>
                        <div class="action-buttons">
                            <a href="apply-unified-menu-all.php" class="btn-success">
                                🚀 Áp dụng menu cho tất cả
                            </a>
                        </div>
                    <?php else: ?>
                        <p class="warning">⚠️ Cần đăng nhập với quyền admin để chạy script áp dụng menu.</p>
                    <?php endif; ?>
                    
                    <div class="info-box">
                        <h4>⚙️ Tool sẽ thực hiện:</h4>
                        <ul>
                            <li>Tự động backup tất cả file trước khi thay đổi</li>
                            <li>Tìm và thay thế menu cũ bằng menu thống nhất</li>
                            <li>Thêm menu mới cho trang chưa có</li>
                            <li>Báo cáo chi tiết kết quả xử lý</li>
                        </ul>
                    </div>
                    
                    <div class="warning-box">
                        <h4>⚠️ Lưu ý quan trọng:</h4>
                        <ul>
                            <li>Tool sẽ tự động tạo backup trước khi thay đổi</li>
                            <li>Kiểm tra kỹ kết quả sau khi chạy</li>
                            <li>Có thể khôi phục từ backup nếu cần</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="step-section">
                <h2>✅ Bước 3: Kiểm tra kết quả</h2>
                <div class="step-card">
                    <p>Sau khi áp dụng menu, hãy kiểm tra các trang:</p>
                    
                    <div class="test-grid">
                        <a href="index.php" target="_blank" class="test-item">
                            <h4>🏠 Trang chủ</h4>
                            <p>Kiểm tra menu video hiển thị</p>
                        </a>
                        
                        <a href="tin-tuc.php" target="_blank" class="test-item">
                            <h4>📰 Tin tức</h4>
                            <p>Kiểm tra dropdown menu</p>
                        </a>
                        
                        <a href="video.php" target="_blank" class="test-item">
                            <h4>📺 Video</h4>
                            <p>Kiểm tra trang video chính</p>
                        </a>
                        
                        <a href="video-files.php" target="_blank" class="test-item">
                            <h4>📁 File Video</h4>
                            <p>Kiểm tra trang duyệt file</p>
                        </a>
                        
                        <a href="lanh-dao.php" target="_blank" class="test-item">
                            <h4>👥 Lãnh đạo</h4>
                            <p>Kiểm tra menu trên trang lãnh đạo</p>
                        </a>
                        
                        <a href="phong-ban.php" target="_blank" class="test-item">
                            <h4>🏢 Phòng ban</h4>
                            <p>Kiểm tra menu trên trang phòng ban</p>
                        </a>
                    </div>
                </div>
            </div>

            <div class="step-section">
                <h2>🎬 Bước 4: Test chức năng video</h2>
                <div class="step-card">
                    <p>Kiểm tra các chức năng video hoạt động đúng:</p>
                    
                    <?php if ($hasPermission): ?>
                        <div class="action-buttons">
                            <a href="test-delete-simple.php" class="btn-warning">
                                🧪 Test chức năng Save/Delete
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <div class="checklist">
                        <h4>📋 Checklist kiểm tra:</h4>
                        <label><input type="checkbox"> Menu video hiển thị trên tất cả trang</label>
                        <label><input type="checkbox"> Dropdown video có 4 tùy chọn</label>
                        <label><input type="checkbox"> Link video hoạt động đúng</label>
                        <label><input type="checkbox"> Chức năng save video hoạt động</label>
                        <label><input type="checkbox"> Chức năng delete video hoạt động</label>
                        <label><input type="checkbox"> Menu responsive trên mobile</label>
                        <label><input type="checkbox"> Menu admin/user hiển thị đúng</label>
                    </div>
                </div>
            </div>

            <div class="step-section">
                <h2>🔧 Bước 5: Khắc phục sự cố (nếu có)</h2>
                <div class="step-card">
                    <div class="troubleshoot-grid">
                        <div class="issue-card">
                            <h4>❌ Lỗi syntax PHP</h4>
                            <p>Nếu có trang bị lỗi syntax sau khi áp dụng menu</p>
                            <?php if ($hasPermission): ?>
                                <a href="fix-syntax-errors.php" class="btn-danger">🔧 Sửa lỗi syntax</a>
                            <?php endif; ?>
                        </div>
                        
                        <div class="issue-card">
                            <h4>🔍 Debug delete function</h4>
                            <p>Nếu chức năng xóa video không hoạt động</p>
                            <?php if ($hasPermission): ?>
                                <a href="debug-delete-video.php" class="btn-info">🔍 Debug delete</a>
                            <?php endif; ?>
                        </div>
                        
                        <div class="issue-card">
                            <h4>📋 Kiểm tra tổng thể</h4>
                            <p>Xem tổng quan tình trạng hệ thống</p>
                            <?php if ($hasPermission): ?>
                                <a href="check-all-fixed.php" class="btn-success">✅ Kiểm tra tổng thể</a>
                            <?php endif; ?>
                        </div>
                        
                        <div class="issue-card">
                            <h4>🔄 Khôi phục backup</h4>
                            <p>Nếu cần khôi phục từ backup</p>
                            <div class="backup-info">
                                <p>Backup được lưu trong thư mục: <code>backup_unified_menu_[timestamp]</code></p>
                                <p>Copy file từ backup về thư mục gốc để khôi phục</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="step-section">
                <h2>📚 Thông tin kỹ thuật</h2>
                <div class="step-card">
                    <div class="tech-info">
                        <h4>🗂️ Cấu trúc menu thống nhất:</h4>
                        <ul>
                            <li><code>header-thong-nhat.php</code> - Header chính chứa logo và menu</li>
                            <li><code>menu-thong-nhat.php</code> - Logic menu với phân quyền</li>
                            <li>CSS tích hợp trong header cho styling</li>
                            <li>JavaScript cho dropdown và responsive</li>
                        </ul>
                        
                        <h4>🎯 Menu video bao gồm:</h4>
                        <ul>
                            <li>📺 <strong>Video chính thức</strong> → <code>video.php</code></li>
                            <li>📁 <strong>Tất cả file video</strong> → <code>video-files.php</code></li>
                            <li>🎵 <strong>File audio</strong> → <code>video-files.php?type=audio</code></li>
                            <li>⭐ <strong>Video nổi bật</strong> → <code>video.php?featured=1</code></li>
                        </ul>
                        
                        <h4>👤 Phân quyền menu:</h4>
                        <ul>
                            <li><strong>User thường:</strong> Menu công khai + đăng nhập</li>
                            <li><strong>Admin:</strong> Menu quản lý + thông tin admin</li>
                            <li>Tự động ẩn/hiện menu theo quyền</li>
                        </ul>
                    </div>
                </div>
            </div>

            <?php if (!$hasPermission): ?>
                <div class="login-section">
                    <h2>🔐 Cần đăng nhập</h2>
                    <div class="login-card">
                        <p>Để sử dụng các tools thống nhất menu, bạn cần đăng nhập với quyền admin.</p>
                        <a href="dang-nhap.php" class="btn-primary">🔐 Đăng nhập</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <style>
    .guide-header {
        text-align: center;
        padding: 40px 0;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        margin: 30px 0;
    }

    .step-section {
        background: white;
        padding: 30px;
        margin: 30px 0;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .step-card, .goal-card {
        background: #f8f9fa;
        padding: 25px;
        border-radius: 8px;
        border-left: 4px solid #007bff;
    }

    .info-box {
        background: #e7f3ff;
        padding: 20px;
        border-radius: 8px;
        margin: 20px 0;
        border-left: 4px solid #17a2b8;
    }

    .warning-box {
        background: #fff3cd;
        padding: 20px;
        border-radius: 8px;
        margin: 20px 0;
        border-left: 4px solid #ffc107;
    }

    .action-buttons {
        display: flex;
        gap: 15px;
        margin: 20px 0;
        flex-wrap: wrap;
    }

    .btn-primary, .btn-success, .btn-warning, .btn-danger, .btn-info {
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

    .btn-primary { background: #007bff; color: white; }
    .btn-success { background: #28a745; color: white; }
    .btn-warning { background: #ffc107; color: #212529; }
    .btn-danger { background: #dc3545; color: white; }
    .btn-info { background: #17a2b8; color: white; }

    .btn-primary:hover { background: #0056b3; }
    .btn-success:hover { background: #218838; }
    .btn-warning:hover { background: #e0a800; }
    .btn-danger:hover { background: #c82333; }
    .btn-info:hover { background: #138496; }

    .test-grid, .troubleshoot-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin: 20px 0;
    }

    .test-item, .issue-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        text-decoration: none;
        color: #2c3e50;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .test-item:hover {
        border-color: #007bff;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,123,255,0.2);
    }

    .issue-card {
        border-left: 4px solid #ffc107;
    }

    .checklist {
        margin: 20px 0;
    }

    .checklist label {
        display: block;
        margin: 10px 0;
        cursor: pointer;
    }

    .checklist input[type="checkbox"] {
        margin-right: 10px;
    }

    .tech-info {
        background: #f8f9fa;
        padding: 25px;
        border-radius: 8px;
    }

    .tech-info h4 {
        color: #495057;
        margin: 20px 0 10px 0;
    }

    .tech-info code {
        background: #e9ecef;
        padding: 2px 6px;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
    }

    .backup-info {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        margin-top: 10px;
    }

    .login-section {
        background: #fff3cd;
        padding: 30px;
        margin: 30px 0;
        border-radius: 10px;
        border-left: 4px solid #ffc107;
    }

    .login-card {
        text-align: center;
        padding: 20px;
    }

    .warning {
        background: #f8d7da;
        color: #721c24;
        padding: 15px;
        border-radius: 5px;
        border-left: 4px solid #dc3545;
    }
    </style>
</body>
</html>