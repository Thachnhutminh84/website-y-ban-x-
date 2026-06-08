<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'auth.php';
require_once 'config.php';

authRequireCanBo('index.php');

$isReadOnly = authIsReadOnly();
$isLoggedIn = authIsLoggedIn();
$currentRole = authCurrentRole();
$displayName = authDisplayName();

// Lấy danh sách phòng ban từ database
$conn = getDBConnection();
$departments = [];

if ($conn) {
    $result = $conn->query("SELECT id, code, name, short_name, description, functions, leader_name, leader_position, phone, email, display_order FROM departments WHERE status = 'active' ORDER BY display_order ASC, id ASC");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $departments[] = $row;
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
    <title>Quản lý Phòng ban - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dept-container {
            max-width: 1400px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .dept-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 25px;
            background: var(--gradient-primary);
            border-radius: 12px;
            color: white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .dept-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        
        .dept-header p {
            margin: 5px 0 0 0;
            opacity: 0.9;
        }
        
        .dept-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            transition: all 0.3s ease;
            border-left: 4px solid #3498db;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 36px;
            color: #3498db;
            font-weight: 700;
        }
        
        .stat-card p {
            margin: 0;
            color: #7f8c8d;
            font-weight: 500;
        }
        
        .dept-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }
        
        .dept-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s ease;
            border-top: 4px solid #3498db;
        }
        
        .dept-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        
        .dept-card-header {
            background: var(--gradient-primary);
            color: white;
            padding: 20px;
        }
        
        .dept-code {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .dept-name {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 5px 0;
        }
        
        .dept-short-name {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .dept-card-body {
            padding: 20px;
        }
        
        .dept-info-item {
            display: flex;
            align-items: start;
            gap: 12px;
            margin-bottom: 15px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .dept-info-item i {
            width: 20px;
            color: #3498db;
            margin-top: 2px;
        }
        
        .dept-info-content {
            flex: 1;
        }
        
        .dept-info-label {
            font-size: 12px;
            color: #7f8c8d;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        
        .dept-info-value {
            font-size: 14px;
            color: #2c3e50;
            font-weight: 500;
        }
        
        .dept-card-footer {
            padding: 15px 20px;
            background: #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #e0e0e0;
        }
        
        .dept-order {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .dept-actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-icon.btn-edit {
            background: #3498db;
            color: white;
        }
        
        .btn-icon.btn-edit:hover {
            background: #2980b9;
            transform: scale(1.1);
        }
        
        .btn-icon.btn-delete {
            background: #e74c3c;
            color: white;
        }
        
        .btn-icon.btn-delete:hover {
            background: #c0392b;
            transform: scale(1.1);
        }

        .btn-icon.btn-view {
            background: #27ae60;
            color: white;
        }

        .btn-icon.btn-view:hover {
            background: #219a52;
            transform: scale(1.1);
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.6);
            z-index: 99999;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-box {
            background: white;
            border-radius: 16px;
            width: 100%;
            max-width: 700px;
            max-height: 85vh;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            display: flex;
            flex-direction: column;
        }

        .modal-header {
            background: var(--gradient-primary);
            color: white;
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.2rem;
        }

        .modal-close {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .modal-close:hover {
            background: rgba(255,255,255,0.35);
        }

        .modal-body {
            padding: 24px;
            overflow-y: auto;
            flex: 1;
        }

        .modal-dept-info {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 20px;
            padding: 16px;
            background: #f0f8f3;
            border-radius: 10px;
            border-left: 4px solid var(--primary);
        }

        .modal-dept-icon {
            width: 50px;
            height: 50px;
            background: var(--gradient-primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .modal-dept-name {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 2px;
        }

        .modal-dept-short {
            font-size: 0.85rem;
            color: var(--text-light);
        }

        .modal-section-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modal-section-title i {
            color: var(--accent);
        }

        .func-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .func-list li {
            padding: 12px 16px;
            border-bottom: 1px solid #eee;
            font-size: 0.9rem;
            line-height: 1.6;
            color: var(--text-dark);
            display: flex;
            gap: 12px;
        }

        .func-list li:last-child {
            border-bottom: none;
        }

        .func-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 28px;
            height: 28px;
            background: var(--gradient-primary);
            color: white;
            border-radius: 50%;
            font-size: 0.75rem;
            font-weight: 700;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .func-text {
            flex: 1;
        }

        @media (max-width: 768px) {
            .modal-box {
                max-height: 90vh;
            }

            .modal-body {
                padding: 16px;
            }

            .func-list li {
                font-size: 0.85rem;
            }
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .empty-state i {
            font-size: 64px;
            color: #bdc3c7;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            color: #7f8c8d;
            margin: 0 0 10px 0;
        }
        
        .empty-state p {
            color: #95a5a6;
        }
        
        @media (max-width: 768px) {
            .dept-grid {
                grid-template-columns: 1fr;
            }
            
            .dept-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'header-menu.php'; ?>
    
    <div class="dept-container">
        <div class="dept-header">
            <div>
                <h1><i class="fas fa-building"></i> Quản lý Phòng ban</h1>
                <p>Quản lý thông tin các phòng ban, đơn vị trực thuộc UBND xã</p>
            </div>
            <?php if (!$isReadOnly): ?>
            <a href="them-phong-ban.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm phòng ban
            </a>
            <?php endif; ?>
        </div>

        <!-- Thống kê -->
        <div class="dept-stats">
            <div class="stat-card">
                <h3><?php echo count($departments); ?></h3>
                <p>Tổng phòng ban</p>
            </div>
            <div class="stat-card" style="border-left-color: #27ae60;">
                <h3 style="color: #27ae60;"><?php echo count(array_filter($departments, fn($d) => $d['status'] === 'active')); ?></h3>
                <p>Đang hoạt động</p>
            </div>
            <div class="stat-card" style="border-left-color: #f39c12;">
                <h3 style="color: #f39c12;"><?php echo count(array_filter($departments, fn($d) => isset($d['leader_name']) && $d['leader_name'])); ?></h3>
                <p>Có lãnh đạo</p>
            </div>
        </div>

        <!-- Danh sách phòng ban -->
        <?php if (count($departments) > 0): ?>
            <div class="dept-grid">
                <?php foreach ($departments as $dept): ?>
                    <div class="dept-card">
                        <div class="dept-card-header">
                            <span class="dept-code"><?php echo htmlspecialchars($dept['code']); ?></span>
                            <h3 class="dept-name"><?php echo htmlspecialchars($dept['name']); ?></h3>
                            <p class="dept-short-name"><?php echo htmlspecialchars($dept['short_name']); ?></p>
                        </div>
                        
                        <div class="dept-card-body">
                            <?php if (isset($dept['description']) && $dept['description']): ?>
                                <div class="dept-info-item">
                                    <i class="fas fa-info-circle"></i>
                                    <div class="dept-info-content">
                                        <div class="dept-info-label">Mô tả</div>
                                        <div class="dept-info-value"><?php echo htmlspecialchars($dept['description']); ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($dept['leader_name']) && $dept['leader_name']): ?>
                                <div class="dept-info-item">
                                    <i class="fas fa-user-tie"></i>
                                    <div class="dept-info-content">
                                        <div class="dept-info-label">Lãnh đạo</div>
                                        <div class="dept-info-value">
                                            <?php echo htmlspecialchars($dept['leader_name']); ?>
                                            <?php if (isset($dept['leader_position']) && $dept['leader_position']): ?>
                                                <br><small style="color: #7f8c8d;"><?php echo htmlspecialchars($dept['leader_position']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($dept['phone']) && $dept['phone']): ?>
                                <div class="dept-info-item">
                                    <i class="fas fa-phone"></i>
                                    <div class="dept-info-content">
                                        <div class="dept-info-label">Điện thoại</div>
                                        <div class="dept-info-value"><?php echo htmlspecialchars($dept['phone']); ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($dept['email']) && $dept['email']): ?>
                                <div class="dept-info-item">
                                    <i class="fas fa-envelope"></i>
                                    <div class="dept-info-content">
                                        <div class="dept-info-label">Email</div>
                                        <div class="dept-info-value"><?php echo htmlspecialchars($dept['email']); ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="dept-card-footer">
                            <div class="dept-order">
                                <i class="fas fa-sort-numeric-down"></i>
                                <span>Thứ tự: <?php echo $dept['display_order']; ?></span>
                            </div>
                            <div class="dept-actions">
                                <?php if (!empty($dept['functions'])): ?>
                                <button class="btn-icon btn-view" onclick="viewFunctions(<?php echo $dept['id']; ?>)" title="Xem chức năng, nhiệm vụ">
                                    <i class="fas fa-list-check"></i>
                                </button>
                                <?php endif; ?>
                                <?php if (!$isReadOnly): ?>
                                <button class="btn-icon btn-edit" onclick="window.location.href='sua-phong-ban.php?id=<?php echo $dept['id']; ?>'" title="Sửa">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-icon btn-delete" onclick="deleteDepartment(<?php echo $dept['id']; ?>, '<?php echo htmlspecialchars($dept['name'], ENT_QUOTES); ?>')" title="Xóa">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-building"></i>
                <h3>Chưa có phòng ban nào</h3>
                <p>Hãy thêm phòng ban đầu tiên</p>
                <a href="them-phong-ban.php" class="btn btn-primary" style="margin-top: 20px;">
                    <i class="fas fa-plus"></i> Thêm phòng ban
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>

    <!-- Modal xem chức năng phòng ban -->
    <div class="modal-overlay" id="funcModal">
        <div class="modal-box">
            <div class="modal-header">
                <h2 id="modalTitle">Chức năng, nhiệm vụ</h2>
                <button class="modal-close" onclick="closeFuncModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="modal-dept-info">
                    <div class="modal-dept-icon"><i class="fas fa-building"></i></div>
                    <div>
                        <div class="modal-dept-name" id="modalDeptName"></div>
                        <div class="modal-dept-short" id="modalDeptShort"></div>
                    </div>
                </div>
                <div class="modal-section-title"><i class="fas fa-list-check"></i> Chức năng, nhiệm vụ theo Nghị định 150</div>
                <ul class="func-list" id="modalFuncList"></ul>
            </div>
        </div>
    </div>

    <script>
        var deptData = <?php
            $jsData = [];
            foreach ($departments as $d) {
                $jsData[] = [
                    'id' => $d['id'],
                    'name' => $d['name'],
                    'short_name' => $d['short_name'] ?? '',
                    'functions' => $d['functions'] ?? ''
                ];
            }
            echo json_encode($jsData, JSON_UNESCAPED_UNICODE);
        ?>;

        function viewFunctions(id) {
            var dept = null;
            for (var i = 0; i < deptData.length; i++) {
                if (deptData[i].id == id) { dept = deptData[i]; break; }
            }
            if (!dept) return;

            document.getElementById('modalTitle').textContent = 'Chức năng, nhiệm vụ - ' + dept.name;
            document.getElementById('modalDeptName').textContent = dept.name;
            document.getElementById('modalDeptShort').textContent = dept.short_name;

            var list = document.getElementById('modalFuncList');
            list.innerHTML = '';

            if (dept.functions) {
                var lines = dept.functions.split('\n');
                lines.forEach(function(line) {
                    line = line.trim();
                    if (!line) return;
                    var numMatch = line.match(/^(\d+)\.\s*/);
                    var num = numMatch ? numMatch[1] : '';
                    var text = numMatch ? line.replace(/^\d+\.\s*/, '') : line;
                    var li = document.createElement('li');
                    li.innerHTML = '<span class="func-num">' + num + '</span><span class="func-text">' + text + '</span>';
                    list.appendChild(li);
                });
            } else {
                list.innerHTML = '<li style="color:#999;text-align:center;">Chưa có thông tin chức năng, nhiệm vụ</li>';
            }

            document.getElementById('funcModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeFuncModal() {
            document.getElementById('funcModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        document.getElementById('funcModal').addEventListener('click', function(e) {
            if (e.target === this) closeFuncModal();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeFuncModal();
        });

        function deleteDepartment(id, name) {
            if (confirm('Bạn có chắc muốn xóa phòng ban "' + name + '"?')) {
                window.location.href = 'api-manage-departments.php?action=delete&id=' + id;
            }
        }
    </script>
</body>
</html>
