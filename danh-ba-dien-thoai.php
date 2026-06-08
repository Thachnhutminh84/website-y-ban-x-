<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';

$isLoggedIn = authIsLoggedIn();
$currentRole = authCurrentRole();
$displayName = $isLoggedIn ? authDisplayName() : '';

// Dữ liệu danh bạ mặc định (fallback)
$defaultPhoneDirectory = [
    'leadership' => [
        'name' => 'Ban Lãnh đạo UBND',
        'icon' => '👥',
        'members' => [
            ['name' => 'Nguyễn Khánh Hòa', 'position' => 'Chủ tịch UBND', 'phone' => '0934.032.959', 'email' => ''],
            ['name' => 'Kiên Thanh Huy Sale', 'position' => 'Phó Chủ tịch UBND', 'phone' => '0384.975.899', 'email' => ''],
            ['name' => 'Trần Thanh Tùng', 'position' => 'Phó Chủ tịch UBND', 'phone' => '0973.672.092', 'email' => '']
        ]
    ],
    'party_leadership' => [
        'name' => 'Ban Lãnh đạo Đảng ủy',
        'icon' => '🏛️',
        'members' => [
            ['name' => 'Trần Văn Mười', 'position' => 'Bí thư Đảng ủy', 'phone' => '0913.741.847', 'email' => ''],
            ['name' => 'Lâm Thái Hòa', 'position' => 'Phó Bí thư Thường trực Đảng ủy', 'phone' => '0977.700.576', 'email' => ''],
            ['name' => 'Nguyễn Khánh Hòa', 'position' => 'Phó Bí thư Đảng ủy', 'phone' => '0934.032.959', 'email' => '']
        ]
    ],
    'party_committee' => [
        'name' => 'Ủy viên Ban Thường vụ Đảng ủy',
        'icon' => '🏢',
        'members' => [
            ['name' => 'Hà Minh Tân', 'position' => 'UVBTV, Chủ nhiệm UBKT Đảng ủy', 'phone' => '0949.478.444', 'email' => ''],
            ['name' => 'Dương Hoài An', 'position' => 'UVBTV, Trưởng ban Xây dựng Đảng', 'phone' => '0985.070.838', 'email' => ''],
            ['name' => 'Tư Thị Mỹ Linh', 'position' => 'UVBTV, Chủ tịch UBMTTQ Việt Nam', 'phone' => '0964.190.901', 'email' => ''],
            ['name' => 'Nguyễn Thị Thúy Trang', 'position' => 'UVBTV, Phó Chủ tịch HĐND', 'phone' => '093.9187.484', 'email' => ''],
            ['name' => 'Phan Đình Huy', 'position' => 'UVBTV, Trưởng Công an', 'phone' => '0388.373.835', 'email' => ''],
            ['name' => 'Nguyễn Văn Ký', 'position' => 'UVBTV, Chỉ huy Trưởng BCH Quân sự', 'phone' => '039.306.4277', 'email' => '']
        ]
    ],
    'economic_dept' => [
        'name' => 'Phòng Kinh tế',
        'icon' => '💼',
        'members' => [
            ['name' => 'Kim Bảy Ly', 'position' => 'Trưởng Phòng Kinh tế', 'phone' => '0944.942.121', 'email' => ''],
            ['name' => 'Son Ngọc Danh Thái', 'position' => 'Phó Trưởng phòng Kinh tế', 'phone' => '097.863.0124', 'email' => ''],
            ['name' => 'Kim Thanh Phong', 'position' => 'Chuyên viên Phòng Kinh tế', 'phone' => '0384.231.224', 'email' => ''],
            ['name' => 'Lê Văn Chắc', 'position' => 'Chuyên viên Phòng Kinh tế', 'phone' => '0379.326.461', 'email' => '']
        ]
    ],
    'culture_social' => [
        'name' => 'Phòng Văn hóa - Xã hội',
        'icon' => '🎭',
        'members' => [
            ['name' => 'Thạch Thanh Mỹ', 'position' => 'UVBCH, Trưởng Phòng Văn hóa - Xã hội', 'phone' => '0343.791.397', 'email' => ''],
            ['name' => 'Trần Quốc Hùng', 'position' => 'Phó Trưởng phòng Văn hóa - Xã hội xã', 'phone' => '085.366.7015', 'email' => ''],
            ['name' => 'Thạch Ngọc Suyến', 'position' => 'Chuyên viên Phòng Văn hóa - Xã hội xã', 'phone' => '0388.006.800', 'email' => ''],
            ['name' => 'Đoàn Văn Để', 'position' => 'Chuyên viên Phòng Văn hóa - Xã hội xã', 'phone' => '0385.393.562', 'email' => '']
        ]
    ],
    'office' => [
        'name' => 'Văn phòng Đảng ủy - HĐND - UBND',
        'icon' => '🏛️',
        'members' => [
            ['name' => 'Nguyễn Trọng Thủy', 'position' => 'Chánh Văn phòng Đảng ủy', 'phone' => '0931.060.339', 'email' => ''],
            ['name' => 'Thạch Som Nang', 'position' => 'Chánh Văn phòng HĐND và UBND', 'phone' => '0383.422.033', 'email' => ''],
            ['name' => 'Trần Thị Hồng Thủy', 'position' => 'Phó Chánh Văn phòng Đảng ủy', 'phone' => '0977.645.951', 'email' => '']
        ]
    ],
    'health' => [
        'name' => 'Trạm Y tế xã Long Hiệp',
        'icon' => '🏥',
        'members' => [
            ['name' => 'Huỳnh Thanh Tâm', 'position' => 'Trạm trưởng Trạm Y tế', 'phone' => '0985.997.414', 'email' => ''],
            ['name' => 'Nguyễn Thị Kim Ngân', 'position' => 'Phó Trạm trưởng', 'phone' => '0912.345.678', 'email' => ''],
            ['name' => 'Trần Văn Bình', 'position' => 'Bác sĩ', 'phone' => '0923.456.789', 'email' => '']
        ]
    ]
];

// Kết nối database và lấy dữ liệu
$phoneDirectory = [];
$usingDatabase = false;

$conn = getDBConnection();
if ($conn) {
    // Kiểm tra bảng có tồn tại không
    $tableCheck = $conn->query("SHOW TABLES LIKE 'departments'");
    if ($tableCheck && $tableCheck->num_rows > 0) {
        $deptResult = $conn->query("
            SELECT d.*, COUNT(ds.id) as member_count
            FROM departments d
            LEFT JOIN department_staff ds ON d.id = ds.department_id AND ds.status = 'active'
            WHERE d.status = 'active'
            GROUP BY d.id
            ORDER BY d.display_order ASC
        ");

        if ($deptResult) {
            while ($dept = $deptResult->fetch_assoc()) {
                // Lấy thành viên của phòng ban
                $stmt = $conn->prepare("
                    SELECT name, position, phone, email
                    FROM department_staff
                    WHERE department_id = ? AND status = 'active'
                    ORDER BY display_order ASC, name ASC
                ");
                $stmt->bind_param("i", $dept['id']);
                $stmt->execute();
                $memberResult = $stmt->get_result();
                
                $members = [];
                while ($member = $memberResult->fetch_assoc()) {
                    $members[] = $member;
                }
                $stmt->close();
                
                // Chỉ thêm phòng ban có thành viên
                if (!empty($members)) {
                    $phoneDirectory[$dept['code']] = [
                        'name' => $dept['name'],
                        'short_name' => $dept['short_name'],
                        'icon' => '📞',
                        'members' => $members
                    ];
                    $usingDatabase = true;
                }
            }
        }
    }
    $conn->close();
}

// Nếu không có dữ liệu từ database, sử dụng dữ liệu mặc định
if (empty($phoneDirectory)) {
    $phoneDirectory = $defaultPhoneDirectory;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh bạ điện thoại - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css?v=2.1">
    <style>
        .phone-directory-page {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-header {
            background: var(--gradient-primary);
            color: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .page-header h1 {
            margin: 0 0 10px 0;
            font-size: 36px;
        }
        
        .page-header p {
            margin: 0;
            font-size: 18px;
            opacity: 0.9;
        }
        
        .data-source-info {
            background: <?php echo $usingDatabase ? '#d4edda' : '#fff3cd'; ?>;
            border: 2px solid <?php echo $usingDatabase ? '#28a745' : '#ffc107'; ?>;
            color: <?php echo $usingDatabase ? '#155724' : '#856404'; ?>;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-toolbar {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .admin-toolbar .info {
            color: #856404;
            font-weight: 600;
        }
        
        .btn-manage {
            background: #ffc107;
            color: #000;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-manage:hover {
            background: #e0a800;
            transform: translateY(-2px);
        }
        
        .search-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .search-box input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
        }
        
        .department-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .department-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .department-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--primary);
        }
        
        .department-icon {
            font-size: 40px;
        }
        
        .department-info h2 {
            margin: 0 0 5px 0;
            color: #333;
            font-size: 24px;
        }
        
        .department-info .badge {
            background: var(--primary);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .members-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .member-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid var(--primary);
            transition: all 0.3s;
        }
        
        .member-card:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        
        .member-name {
            font-size: 18px;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
        }
        
        .member-position {
            color: #666;
            font-size: 14px;
            margin-bottom: 12px;
            font-style: italic;
        }
        
        .member-contact {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 15px;
        }
        
        .contact-item .icon {
            font-size: 18px;
            width: 25px;
        }
        
        .contact-item a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
        
        .contact-item a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            .members-grid {
                grid-template-columns: 1fr;
            }
            
            .page-header h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <?php include 'header-menu.php'; ?>

    <div class="phone-directory-page">
        <div class="page-header">
            <h1>📞 Danh bạ Điện thoại</h1>
            <p>Thông tin liên hệ cán bộ, nhân viên UBND Xã Long Hiệp</p>
        </div>

        <div class="data-source-info">
            <div>
                <?php if ($usingDatabase): ?>
                    ✅ <strong>Dữ liệu từ Database</strong> - Dữ liệu được quản lý qua hệ thống
                <?php else: ?>
                    ℹ️ <strong>Dữ liệu mặc định</strong> - Chưa có dữ liệu trong database
                <?php endif; ?>
            </div>
            <?php if (!$usingDatabase && $isLoggedIn && $currentRole === 'admin'): ?>
                <a href="run-insert-staff.php" class="btn-manage">
                    📊 Import dữ liệu vào Database
                </a>
            <?php endif; ?>
        </div>

        <?php if ($isLoggedIn && $currentRole === 'admin'): ?>
            <div class="admin-toolbar">
                <div class="info">
                    🔐 Bạn đang đăng nhập với quyền Admin
                </div>
                <a href="quan-ly-danh-ba.php" class="btn-manage">
                    ⚙️ Quản lý danh bạ
                </a>
            </div>
        <?php endif; ?>

        <div class="search-box">
            <input type="text" id="searchInput" placeholder="🔍 Tìm kiếm theo tên, chức vụ, số điện thoại...">
        </div>

        <div id="directoryContent">
            <?php foreach ($phoneDirectory as $code => $department): ?>
                <div class="department-section" data-department="<?php echo htmlspecialchars($department['name']); ?>">
                    <div class="department-header">
                        <div class="department-icon"><?php echo $department['icon']; ?></div>
                        <div class="department-info">
                            <h2><?php echo htmlspecialchars($department['name']); ?></h2>
                            <span class="badge"><?php echo count($department['members']); ?> thành viên</span>
                        </div>
                    </div>

                    <div class="members-grid">
                        <?php foreach ($department['members'] as $member): ?>
                            <div class="member-card" 
                                 data-name="<?php echo htmlspecialchars($member['name']); ?>"
                                 data-position="<?php echo htmlspecialchars($member['position']); ?>"
                                 data-phone="<?php echo htmlspecialchars($member['phone']); ?>">
                                <div class="member-name"><?php echo htmlspecialchars($member['name']); ?></div>
                                <div class="member-position"><?php echo htmlspecialchars($member['position']); ?></div>
                                <div class="member-contact">
                                    <div class="contact-item">
                                        <span class="icon">📱</span>
                                        <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $member['phone']); ?>">
                                            <?php echo htmlspecialchars($member['phone']); ?>
                                        </a>
                                    </div>
                                    <?php if (!empty($member['email'])): ?>
                                        <div class="contact-item">
                                            <span class="icon">✉️</span>
                                            <a href="mailto:<?php echo htmlspecialchars($member['email']); ?>">
                                                <?php echo htmlspecialchars($member['email']); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        // Tìm kiếm
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const sections = document.querySelectorAll('.department-section');
            
            sections.forEach(section => {
                const cards = section.querySelectorAll('.member-card');
                let hasVisibleCard = false;
                
                cards.forEach(card => {
                    const name = card.dataset.name.toLowerCase();
                    const position = card.dataset.position.toLowerCase();
                    const phone = card.dataset.phone.toLowerCase();
                    
                    if (name.includes(searchTerm) || position.includes(searchTerm) || phone.includes(searchTerm)) {
                        card.style.display = 'block';
                        hasVisibleCard = true;
                    } else {
                        card.style.display = 'none';
                    }
                });
                
                section.style.display = hasVisibleCard ? 'block' : 'none';
            });
        });
    </script>

    <?php include 'footer.php'; ?>
    <?php include 'quick-actions-toolbar.php'; ?>
</body>
</html>