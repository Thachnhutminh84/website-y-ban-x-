<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';
require_once 'contact-message-helper.php';

// Test data insertion for contacts
function insertTestContacts() {
    $conn = getContactStorageConnection();
    if (!$conn || !ensureContactsTableExists($conn)) {
        return false;
    }
    
    $testContacts = [
        [
            'name' => 'Nguyễn Văn A',
            'email' => 'nguyenvana@example.com',
            'phone' => '0901234567',
            'subject' => 'Hỏi về thủ tục đăng ký kinh doanh',
            'message' => 'Tôi muốn hỏi về thủ tục đăng ký kinh doanh tại địa phương. Xin cho biết cần chuẩn bị những giấy tờ gì?',
            'priority' => 'normal',
            'status' => 'new'
        ],
        [
            'name' => 'Trần Thị B',
            'email' => 'tranthib@example.com',
            'phone' => '0912345678',
            'subject' => 'Phản ánh về tình trạng đường xá',
            'message' => 'Đường trong xóm tôi bị hư hỏng nhiều chỗ, mong UBND xã quan tâm sửa chữa.',
            'priority' => 'high',
            'status' => 'processing'
        ],
        [
            'name' => 'Lê Văn C',
            'email' => 'levanc@example.com',
            'phone' => '0923456789',
            'subject' => 'Cảm ơn về việc xử lý nước sinh hoạt',
            'message' => 'Cảm ơn UBND xã đã khắc phục xong vấn đề nước sinh hoạt trong khu vực.',
            'priority' => 'low',
            'status' => 'resolved'
        ]
    ];
    
    $stmt = $conn->prepare('INSERT INTO contacts (name, email, phone, subject, message, priority, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
    
    foreach ($testContacts as $contact) {
        $stmt->bind_param('sssssss', 
            $contact['name'], 
            $contact['email'], 
            $contact['phone'], 
            $contact['subject'], 
            $contact['message'], 
            $contact['priority'], 
            $contact['status']
        );
        $stmt->execute();
    }
    
    $stmt->close();
    $conn->close();
    return true;
}

$message = '';
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'test_contacts':
            if (insertTestContacts()) {
                $message = '✅ Đã thêm dữ liệu test cho liên hệ thành công!';
            } else {
                $message = '❌ Lỗi khi thêm dữ liệu test cho liên hệ.';
            }
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test New Features - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css?v=2.1">
</head>
<body>
    <div class="container" style="margin-top: 50px;">
        <h1>🧪 Test Các Chức Năng Mới</h1>
        
        <?php if ($message): ?>
            <div style="padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; margin: 20px 0;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 30px 0;">
            
            <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <h3>📧 Quản lý Liên hệ/Phản ánh</h3>
                <p>Hệ thống quản lý tin nhắn từ người dân với đầy đủ tính năng.</p>
                <div style="margin: 15px 0;">
                    <a href="?action=test_contacts" style="display: inline-block; padding: 8px 16px; background: #3498db; color: white; text-decoration: none; border-radius: 6px; margin-right: 10px;">Thêm dữ liệu test</a>
                    <a href="tin-nhan-lien-he.php" style="display: inline-block; padding: 8px 16px; background: #27ae60; color: white; text-decoration: none; border-radius: 6px;">Xem quản lý</a>
                </div>
                <small style="color: #666;">
                    ✅ Xem danh sách tin nhắn<br>
                    ✅ Lọc theo trạng thái & mức độ<br>
                    ✅ Tìm kiếm tin nhắn<br>
                    ✅ Xem chi tiết & cập nhật trạng thái
                </small>
            </div>
            
            <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <h3>📊 Dashboard Thống kê</h3>
                <p>Trang tổng quan với các thống kê quan trọng của website.</p>
                <div style="margin: 15px 0;">
                    <a href="dashboard.php" style="display: inline-block; padding: 8px 16px; background: #9b59b6; color: white; text-decoration: none; border-radius: 6px;">Xem Dashboard</a>
                </div>
                <small style="color: #666;">
                    ✅ Thống kê tin tức & lượt xem<br>
                    ✅ Thống kê liên hệ theo trạng thái<br>
                    ✅ Biểu đồ theo tháng<br>
                    ✅ Tin tức mới nhất & thao tác nhanh
                </small>
            </div>
            
            <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <h3>🔍 Tìm kiếm Nâng cao</h3>
                <p>Tìm kiếm tin tức với AJAX và gợi ý thời gian thực.</p>
                <div style="margin: 15px 0;">
                    <a href="tin-tuc.php" style="display: inline-block; padding: 8px 16px; background: #e74c3c; color: white; text-decoration: none; border-radius: 6px;">Test tìm kiếm</a>
                </div>
                <small style="color: #666;">
                    ✅ Tìm kiếm AJAX realtime<br>
                    ✅ Highlight từ khóa trong kết quả<br>
                    ✅ Dropdown gợi ý<br>
                    ✅ API tìm kiếm riêng biệt
                </small>
            </div>
            
        </div>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 12px; margin: 30px 0;">
            <h3>📋 Hướng dẫn sử dụng</h3>
            <ol style="line-height: 1.8;">
                <li><strong>Quản lý liên hệ:</strong> Đăng nhập admin → Menu "Tin nhắn" → Xem, lọc, cập nhật trạng thái</li>
                <li><strong>Dashboard:</strong> Đăng nhập admin → Menu "Dashboard" → Xem thống kê tổng quan</li>
                <li><strong>Tìm kiếm nâng cao:</strong> Vào trang Tin tức → Gõ từ khóa vào ô tìm kiếm → Xem gợi ý realtime</li>
                <li><strong>Test liên hệ:</strong> Vào trang Liên hệ → Gửi tin nhắn test → Vào quản lý để xem</li>
            </ol>
        </div>
        
        <div style="text-align: center; margin: 40px 0;">
            <a href="index.php" style="display: inline-block; padding: 12px 24px; background: #2c3e50; color: white; text-decoration: none; border-radius: 8px;">🏠 Về trang chủ</a>
        </div>
    </div>
</body>
</html>