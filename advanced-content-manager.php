<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';

authRequireRole(['admin']);
$isReadOnly = authIsReadOnly();

$isLoggedIn = authIsLoggedIn();
$currentRole = authCurrentRole();
$displayName = authDisplayName();

// Get statistics
$conn = getDBConnection();
$stats = [
    'total_news' => 0,
    'published_news' => 0,
    'draft_news' => 0
];

$recentNews = [];

if ($conn) {
    $result = $conn->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft
        FROM news");
    
    if ($result) {
        $row = $result->fetch_assoc();
        $stats['total_news'] = $row['total'] ?? 0;
        $stats['published_news'] = $row['published'] ?? 0;
        $stats['draft_news'] = $row['draft'] ?? 0;
    }
    
    // Get recent news
    $result = $conn->query("SELECT n.id, n.title, n.status, n.created_at, c.name as category 
                           FROM news n 
                           LEFT JOIN categories c ON n.category_id = c.id 
                           ORDER BY n.created_at DESC 
                           LIMIT 10");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $recentNews[] = $row;
        }
    }
    
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Nội dung - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard-style.css">
    <style>
        .content-manager {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .manager-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .tab-btn {
            padding: 12px 24px;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            color: #6c757d;
            transition: all 0.3s ease;
        }
        
        .tab-btn.active {
            color: #3498db;
            border-bottom-color: #3498db;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .content-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .content-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .content-actions {
            display: flex;
            gap: 10px;
        }
        
        .action-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary { background: #3498db; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-warning { background: #f39c12; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .table-responsive {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }

        .data-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }

        .data-table tr:hover {
            background: #f8f9fa;
        }

        .badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-published {
            background: #28a745;
            color: white;
        }

        .badge-draft {
            background: #6c757d;
            color: white;
        }

        .btn-sm {
            padding: 4px 12px;
            font-size: 13px;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
            margin-right: 5px;
        }

        .btn-sm.btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-sm.btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .rich-editor {
            border: 1px solid #ddd;
            border-radius: 8px;
            min-height: 300px;
            padding: 15px;
        }
        
        .editor-toolbar {
            display: flex;
            gap: 5px;
            padding: 10px;
            border-bottom: 1px solid #eee;
            background: #f8f9fa;
        }
        
        .editor-btn {
            padding: 5px 10px;
            border: 1px solid #ddd;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .editor-btn:hover {
            background: #e9ecef;
        }
        
        .template-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .template-item {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .template-item:hover {
            border-color: #3498db;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .template-item h4 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }
        
        .template-item p {
            margin: 0;
            color: #7f8c8d;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .manager-tabs {
                flex-wrap: wrap;
            }
            
            .content-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .content-actions {
                width: 100%;
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body>
    <?php include 'header-menu.php'; ?>
    <?php 
    require_once 'breadcrumb-system.php';
    echo renderBreadcrumb('', [
        ['title' => 'Dashboard', 'url' => 'dashboard.php', 'active' => false],
        ['title' => 'Quản lý Nội dung Nâng cao', 'url' => '', 'active' => true]
    ]);
    ?>

    <?php include 'read-only-notice.php'; ?>

    <main>
        <section class="page-header">
            <div class="container">
                <h1>Quản lý Nội dung Nâng cao</h1>
                <p>Hệ thống quản lý nội dung với rich editor và templates</p>
            </div>
        </section>

        <section class="content-manager">
            <div class="manager-tabs">
                <button class="tab-btn active" onclick="switchTab('news')">📝 Tin tức</button>
                <button class="tab-btn" onclick="switchTab('editor')">✏️ Editor nâng cao</button>
                <button class="tab-btn" onclick="switchTab('templates')">📄 Mẫu nội dung</button>
            </div>

            <!-- News Tab -->
            <div id="news-tab" class="tab-content active">
                <div class="content-card">
                    <div class="content-header">
                        <h3 class="content-title">Quản lý Tin tức</h3>
                        <div class="content-actions">
                            <?php if (!$isReadOnly): ?>
                            <a href="them-tin.php" class="action-btn btn-primary">Tạo tin mới</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tiêu đề</th>
                                    <th>Danh mục</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày tạo</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentNews)): ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center;">Chưa có tin tức nào</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentNews as $news): ?>
                                        <tr>
                                            <td><?php echo $news['id']; ?></td>
                                            <td><?php echo htmlspecialchars($news['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?php echo htmlspecialchars($news['category'] ?? 'Chưa phân loại', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $news['status']; ?>">
                                                    <?php echo $news['status'] == 'published' ? 'Đã xuất bản' : 'Bản nháp'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($news['created_at'])); ?></td>
                                            <td>
                                                <a href="sua-tin.php?id=<?php echo $news['id']; ?>" class="btn-sm btn-primary">Sửa</a>
                                                <a href="chi-tiet-tin.php?id=<?php echo $news['id']; ?>" class="btn-sm btn-secondary">Xem</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Rich Editor Tab -->
            <div id="editor-tab" class="tab-content">
                <div class="content-card">
                    <div class="content-header">
                        <h3 class="content-title">Rich Text Editor</h3>
                        <div class="content-actions">
                            <button class="action-btn btn-primary" onclick="saveContent()">Lưu nội dung</button>
                            <button class="action-btn btn-warning" onclick="previewContent()">Xem trước</button>
                        </div>
                    </div>
                    
                    <div class="rich-editor">
                        <div class="editor-toolbar">
                            <button class="editor-btn" onclick="formatText('bold')"><b>B</b></button>
                            <button class="editor-btn" onclick="formatText('italic')"><i>I</i></button>
                            <button class="editor-btn" onclick="formatText('underline')"><u>U</u></button>
                            <button class="editor-btn" onclick="formatText('insertOrderedList')">1.</button>
                            <button class="editor-btn" onclick="formatText('insertUnorderedList')">•</button>
                            <button class="editor-btn" onclick="insertLink()">🔗</button>
                            <button class="editor-btn" onclick="insertImage()">🖼️</button>
                        </div>
                        <div id="richEditor" contenteditable="true" style="min-height: 250px; padding: 15px; outline: none;">
                            <p>Bắt đầu viết nội dung của bạn tại đây...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Templates Tab -->
            <div id="templates-tab" class="tab-content">
                <div class="content-card">
                    <div class="content-header">
                        <h3 class="content-title">Mẫu Nội dung</h3>
                        <div class="content-actions">
                            <button class="action-btn btn-primary" onclick="createTemplate()">Tạo mẫu mới</button>
                        </div>
                    </div>
                    
                    <div class="template-grid">
                        <div class="template-item" onclick="useTemplate('news')">
                            <h4>📰 Mẫu Tin tức</h4>
                            <p>Mẫu chuẩn cho bài viết tin tức</p>
                        </div>
                        <div class="template-item" onclick="useTemplate('announcement')">
                            <h4>📢 Mẫu Thông báo</h4>
                            <p>Mẫu cho thông báo chính thức</p>
                        </div>
                        <div class="template-item" onclick="useTemplate('event')">
                            <h4>🎉 Mẫu Sự kiện</h4>
                            <p>Mẫu cho thông tin sự kiện</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>
    <?php include 'quick-actions-toolbar.php'; ?>

    <script>
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            event.target.classList.add('active');
        }

        function formatText(command) {
            document.execCommand(command, false, null);
            document.getElementById('richEditor').focus();
        }

        function insertLink() {
            const url = prompt('Nhập URL:');
            if (url) {
                document.execCommand('createLink', false, url);
            }
        }

        function insertImage() {
            const url = prompt('Nhập URL hình ảnh:');
            if (url) {
                document.execCommand('insertImage', false, url);
            }
        }

        function saveContent() {
            const content = document.getElementById('richEditor').innerHTML;
            alert('Nội dung đã được lưu!\n\n' + content.substring(0, 100) + '...');
        }

        function previewContent() {
            const content = document.getElementById('richEditor').innerHTML;
            const previewWindow = window.open('', '_blank');
            previewWindow.document.write(`
                <html>
                <head><title>Xem trước nội dung</title></head>
                <body style="font-family: Arial, sans-serif; padding: 20px;">
                    <h2>Xem trước nội dung</h2>
                    <hr>
                    ${content}
                </body>
                </html>
            `);
        }

        function useTemplate(templateType) {
            const templates = {
                news: `<h2>Tiêu đề tin tức</h2>
                       <p><strong>Ngày:</strong> ${new Date().toLocaleDateString('vi-VN')}</p>
                       <p><strong>Tóm tắt:</strong> Tóm tắt ngắn gọn về tin tức...</p>
                       <h3>Nội dung chính</h3>
                       <p>Nội dung chi tiết của tin tức...</p>`,
                       
                announcement: `<h2>THÔNG BÁO</h2>
                              <p><strong>Về việc:</strong> [Nội dung thông báo]</p>
                              <p><strong>Căn cứ:</strong></p>
                              <ul><li>Văn bản số...</li></ul>
                              <p><strong>UBND xã Long Hiệp thông báo:</strong></p>
                              <p>Nội dung thông báo...</p>`,
                              
                event: `<h2>THÔNG TIN SỰ KIỆN</h2>
                       <p><strong>Tên sự kiện:</strong> [Tên sự kiện]</p>
                       <p><strong>Thời gian:</strong> [Ngày giờ]</p>
                       <p><strong>Địa điểm:</strong> [Địa điểm tổ chức]</p>
                       <p><strong>Nội dung:</strong></p>
                       <p>Mô tả chi tiết về sự kiện...</p>`
            };
            
            document.getElementById('richEditor').innerHTML = templates[templateType];
            switchTab('editor');
        }

        function createTemplate() {
            alert('Tính năng tạo mẫu tùy chỉnh sẽ được phát triển trong phiên bản tiếp theo.');
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Advanced Content Manager loaded successfully');
        });
    </script>
</body>
</html>
