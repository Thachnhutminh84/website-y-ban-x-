<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

// Kiểm tra file config
if (file_exists('config.php')) {
    require_once 'config.php';
}
if (file_exists('auth.php')) {
    require_once 'auth.php';
}

$isLoggedIn = function_exists('authIsLoggedIn') ? authIsLoggedIn() : false;
$currentRole = function_exists('authCurrentRole') ? authCurrentRole() : '';
$displayName = $isLoggedIn && function_exists('authDisplayName') ? authDisplayName() : '';

// Danh bạ điện thoại tĩnh (không cần database)
$phoneDirectory = [
    'leadership' => [
        'name' => 'Ban Lãnh đạo UBND',
        'icon' => '👥',
        'members' => [
            ['name' => 'Nguyễn Khánh Hòa', 'position' => 'Chủ tịch UBND', 'phone' => '0934.032.959'],
            ['name' => 'Kiên Thanh Huy Sale', 'position' => 'Phó Chủ tịch UBND', 'phone' => '0384.975.899'],
            ['name' => 'Trần Thanh Tùng', 'position' => 'Phó Chủ tịch UBND', 'phone' => '0973.672.092']
        ]
    ],
    'party_leadership' => [
        'name' => 'Ban Lãnh đạo Đảng ủy',
        'icon' => '🏛️',
        'members' => [
            ['name' => 'Trần Văn Mười', 'position' => 'Bí thư Đảng ủy', 'phone' => '0913.741.847'],
            ['name' => 'Lâm Thái Hòa', 'position' => 'Phó Bí thư Thường trực Đảng ủy', 'phone' => '0977.700.576'],
            ['name' => 'Nguyễn Khánh Hòa', 'position' => 'Phó Bí thư Đảng ủy', 'phone' => '0934.032.959']
        ]
    ],
    'party_committee' => [
        'name' => 'Ủy viên Ban Thường vụ Đảng ủy',
        'icon' => '🏢',
        'members' => [
            ['name' => 'Hà Minh Tân', 'position' => 'UVBTV, Chủ nhiệm UBKT Đảng ủy', 'phone' => '0949.478.444'],
            ['name' => 'Dương Hoài An', 'position' => 'UVBTV, Trưởng ban Xây dựng Đảng', 'phone' => '0985.070.838'],
            ['name' => 'Tư Thị Mỹ Linh', 'position' => 'UVBTV, Chủ tịch UBMTTQ Việt Nam', 'phone' => '0964.190.901'],
            ['name' => 'Nguyễn Thị Thúy Trang', 'position' => 'UVBTV, Phó Chủ tịch HĐND', 'phone' => '093.9187.484'],
            ['name' => 'Phan Đình Huy', 'position' => 'UVBTV, Trưởng Công an', 'phone' => '0388.373.835'],
            ['name' => 'Nguyễn Văn Ký', 'position' => 'UVBTV, Chỉ huy Trưởng BCH Quân sự', 'phone' => '039.306.4277']
        ]
    ],
    'economic_dept' => [
        'name' => 'Phòng Kinh tế',
        'icon' => '💼',
        'members' => [
            ['name' => 'Kim Bảy Ly', 'position' => 'Trưởng Phòng Kinh tế', 'phone' => '0944.942.121'],
            ['name' => 'Son Ngọc Danh Thái', 'position' => 'Phó Trưởng phòng Kinh tế', 'phone' => '097.863.0124'],
            ['name' => 'Kim Thanh Phong', 'position' => 'Chuyên viên Phòng Kinh tế', 'phone' => '0384.231.224'],
            ['name' => 'Thạch Quanh Tha', 'position' => 'Chuyên viên Phòng Kinh tế', 'phone' => '0375.722.611'],
            ['name' => 'Lê Văn Chắc', 'position' => 'Chuyên viên Phòng Kinh tế', 'phone' => '0379.326.461']
        ]
    ],
    'culture_social_dept' => [
        'name' => 'Phòng Văn hóa - Xã hội',
        'icon' => '🎭',
        'members' => [
            ['name' => 'Thạch Thanh Mỹ', 'position' => 'Trưởng Phòng Văn hóa - Xã hội', 'phone' => '0343.791.397'],
            ['name' => 'Trần Quốc Hùng', 'position' => 'Phó Trưởng phòng Văn hóa - Xã hội', 'phone' => '085.366.7015'],
            ['name' => 'Thạch Ngọc Suyền', 'position' => 'Chuyên viên Phòng Văn hóa - Xã hội', 'phone' => '0388.006.800'],
            ['name' => 'Thạch Sơm', 'position' => 'Chuyên viên Phòng Văn hóa - Xã hội', 'phone' => '0976.991.969']
        ]
    ],
    'health_dept' => [
        'name' => 'Trạm Y tế',
        'icon' => '🏥',
        'members' => [
            ['name' => 'Huỳnh Thanh Tâm', 'position' => 'Trưởng trạm Y tế', 'phone' => '0985.997.414']
        ]
    ],
    'party_office' => [
        'name' => 'Văn phòng Đảng ủy - HĐND - UBND',
        'icon' => '�',
        'members' => [
            ['name' => 'Nguyễn Trọng Thủy', 'position' => 'Chánh Văn phòng Đảng ủy', 'phone' => '0931.060.339'],
            ['name' => 'Thạch Som Nang', 'position' => 'Chánh Văn phòng HĐND và UBND', 'phone' => '0383.422.033'],
            ['name' => 'Trần Thị Hồng Thủy', 'position' => 'Phó Chánh Văn phòng Đảng ủy', 'phone' => '0977.645.951'],
            ['name' => 'Thạch Huỳnh Thủy', 'position' => 'Phó Chánh Văn phòng Đảng ủy', 'phone' => '0975.232.191'],
            ['name' => 'Hà Phước Hiệp', 'position' => 'Chuyên viên Văn phòng Đảng ủy', 'phone' => '0387.765.900'],
            ['name' => 'Kim Ngọc Bình', 'position' => 'Chuyên viên Văn phòng Đảng ủy', 'phone' => '097.320.4499'],
            ['name' => 'Lê Tấn Phương', 'position' => 'Chuyên viên Văn phòng Đảng ủy', 'phone' => '0988.224.059']
        ]
    ],
    'security_military' => [
        'name' => 'Công an - Quân sự',
        'icon' => '🛡️',
        'members' => [
            ['name' => 'Phan Đình Huy', 'position' => 'Trưởng Công an xã', 'phone' => '0388.373.835'],
            ['name' => 'Kim Bình Luận', 'position' => 'Phó Trưởng Công an xã', 'phone' => '0985.215.272'],
            ['name' => 'Nguyễn Văn Kỷ', 'position' => 'Chỉ huy Trưởng BCH Quân sự', 'phone' => '039.306.4277']
        ]
    ],
    'village_leaders' => [
        'name' => 'Lãnh đạo các ấp',
        'icon' => '🏘️',
        'members' => [
            ['name' => 'Thạch Văn Sang', 'position' => 'Bí thư Chi bộ ấp Con Lọp', 'phone' => '097.779.6469'],
            ['name' => 'Võ Chí Tâm', 'position' => 'Phó Bí thư Chi bộ ấp Con Lọp', 'phone' => '039.567.1647'],
            ['name' => 'Dương văn Suối', 'position' => 'Trưởng BCT Mặt trận ấp Con Lọp', 'phone' => '0969.888.549'],
            ['name' => 'Đỗ Thuận Lâm', 'position' => 'Bí thư Chi bộ ấp Ba Trạch B', 'phone' => '0347.637.758'],
            ['name' => 'Trâm Văn Hiệp', 'position' => 'Phó Bí thư Chi bộ ấp Ba Trạch B', 'phone' => '0373.302.573'],
            ['name' => 'Trần Sen', 'position' => 'Trưởng BCT Mặt trận ấp Ba Trạch B', 'phone' => '0384.712.945'],
            ['name' => 'Lê Văn Thắng', 'position' => 'Bí thư Chi bộ ấp Ba Trạch A', 'phone' => '0354.166.375'],
            ['name' => 'Lư Văn Sao', 'position' => 'Phó Bí thư Chi bộ ấp Ba Trạch A', 'phone' => '0974.135.079'],
            ['name' => 'Quách Văn Hiếu', 'position' => 'Trưởng BCT Mặt trận ấp Ba Trạch A', 'phone' => '0348.848.986'],
            ['name' => 'Thạch Minh Thái', 'position' => 'Bí thư Chi bộ ấp Long Trường', 'phone' => '0362.295.613'],
            ['name' => 'Thạch Hải Hùng', 'position' => 'Phó Bí thư Chi bộ ấp Long Trường', 'phone' => '0336.096.824'],
            ['name' => 'Kim Thanh Tú', 'position' => 'Trưởng BCT Mặt trận ấp Long Trường', 'phone' => '0354.760.499'],
            ['name' => 'Thạch Văn Trung', 'position' => 'Bí thư Chi bộ ấp Nô Men', 'phone' => '0368.081.479'],
            ['name' => 'Thạch Tha', 'position' => 'Phó Bí thư Chi bộ ấp Nô Men', 'phone' => '098.792.4731'],
            ['name' => 'Thạch Bửt Thi', 'position' => 'Trưởng BCT Mặt trận ấp Nô Men', 'phone' => '0971.647.594'],
            ['name' => 'Nguyễn Thanh To', 'position' => 'Bí thư Chi bộ ấp Sá Văn A', 'phone' => '0356.976.518']
        ]
    ]
];

// Xử lý tìm kiếm
$searchQuery = $_GET['search'] ?? '';
$selectedDept = $_GET['dept'] ?? 'all';

// Lọc dữ liệu theo tìm kiếm
$filteredDirectory = [];
foreach ($phoneDirectory as $deptKey => $department) {
    if ($selectedDept !== 'all' && $selectedDept !== $deptKey) {
        continue;
    }
    
    $filteredMembers = $department['members'];
    if ($searchQuery) {
        $filteredMembers = array_filter($department['members'], function($member) use ($searchQuery) {
            return stripos($member['name'], $searchQuery) !== false || 
                   stripos($member['position'], $searchQuery) !== false;
        });
    }
    
    if (!empty($filteredMembers)) {
        $filteredDirectory[$deptKey] = [
            'name' => $department['name'],
            'icon' => $department['icon'],
            'members' => array_values($filteredMembers)
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh bạ điện thoại - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css?v=2.1">
    <link rel="stylesheet" href="footer-style.css?v=1.0">
    <link rel="stylesheet" href="responsive-enhancements.css?v=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="dropdown.js"></script>
    <style>
        .phone-directory {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .search-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .search-form {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 15px;
            align-items: end;
        }
        
        .search-group {
            display: flex;
            flex-direction: column;
        }
        
        .search-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .search-input {
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #3498db;
        }
        
        .search-select {
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            background: white;
            cursor: pointer;
        }
        
        .search-btn {
            padding: 12px 25px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .search-btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
        
        .department-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .dept-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .dept-icon {
            font-size: 32px;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #3498db, #2980b9);
            border-radius: 50%;
            color: white;
        }
        
        .dept-name {
            font-size: 24px;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }
        
        .members-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
        }
        
        .member-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            border-left: 4px solid #3498db;
            transition: all 0.3s ease;
        }
        
        .member-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
        
        .member-name {
            font-size: 18px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .member-position {
            font-size: 14px;
            color: #7f8c8d;
            margin-bottom: 12px;
            line-height: 1.4;
        }
        
        .member-phone {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 16px;
            font-weight: 600;
            color: #27ae60;
        }
        
        .phone-icon {
            font-size: 18px;
        }
        
        .phone-link {
            color: #27ae60;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .phone-link:hover {
            color: #219a52;
        }
        
        .stats-section {
            background: var(--gradient-primary);
            color: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #7f8c8d;
        }
        
        .no-results i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        @media (max-width: 768px) {
            .search-form {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .members-grid {
                grid-template-columns: 1fr;
            }
            
            .dept-header {
                flex-direction: column;
                text-align: center;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 480px) {
            .phone-directory {
                padding: 15px;
            }
            
            .department-section,
            .search-section {
                padding: 20px;
            }
            
            .member-card {
                padding: 15px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'header-menu.php'; ?>
    <?php 
    if (file_exists('breadcrumb-system.php')) {
        require_once 'breadcrumb-system.php';
        echo renderBreadcrumb('', [
            ['title' => 'Danh bạ điện thoại', 'url' => '', 'active' => true]
        ]);
    }
    ?>

    <main>
        <section class="page-header">
            <div class="container">
                <h1>📞 Danh bạ điện thoại</h1>
                <p>Thông tin liên hệ các phòng ban và lãnh đạo UBND Xã Long Hiệp</p>
            </div>
        </section>

        <section class="phone-directory">
            <!-- Statistics -->
            <div class="stats-section">
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo count($phoneDirectory); ?></div>
                        <div class="stat-label">Phòng ban</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">
                            <?php 
                            $totalMembers = 0;
                            foreach ($phoneDirectory as $dept) {
                                $totalMembers += count($dept['members']);
                            }
                            echo $totalMembers;
                            ?>
                        </div>
                        <div class="stat-label">Cán bộ</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Hỗ trợ</div>
                    </div>
                </div>
            </div>

            <!-- Search Section -->
            <div class="search-section">
                <form class="search-form" method="GET">
                    <div class="search-group">
                        <label class="search-label">🔍 Tìm kiếm theo tên hoặc chức vụ</label>
                        <input type="text" name="search" class="search-input" 
                               placeholder="Nhập tên hoặc chức vụ..." 
                               value="<?php echo htmlspecialchars($searchQuery); ?>">
                    </div>
                    <div class="search-group">
                        <label class="search-label">🏢 Phòng ban</label>
                        <select name="dept" class="search-select">
                            <option value="all">Tất cả phòng ban</option>
                            <?php foreach ($phoneDirectory as $key => $dept): ?>
                            <option value="<?php echo $key; ?>" <?php echo $selectedDept === $key ? 'selected' : ''; ?>>
                                <?php echo $dept['name']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                </form>
            </div>

            <!-- Directory Sections -->
            <?php if (!empty($filteredDirectory)): ?>
                <?php foreach ($filteredDirectory as $deptKey => $department): ?>
                <div class="department-section">
                    <div class="dept-header">
                        <div class="dept-icon"><?php echo $department['icon']; ?></div>
                        <h2 class="dept-name"><?php echo htmlspecialchars($department['name']); ?></h2>
                    </div>
                    
                    <div class="members-grid">
                        <?php foreach ($department['members'] as $member): ?>
                        <div class="member-card">
                            <div class="member-name"><?php echo htmlspecialchars($member['name']); ?></div>
                            <div class="member-position"><?php echo htmlspecialchars($member['position']); ?></div>
                            <div class="member-phone">
                                <i class="fas fa-phone phone-icon"></i>
                                <a href="tel:<?php echo str_replace(['.', ' '], '', $member['phone']); ?>" class="phone-link">
                                    <?php echo htmlspecialchars($member['phone']); ?>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
            <div class="no-results">
                <i class="fas fa-search"></i>
                <h3>Không tìm thấy kết quả</h3>
                <p>Vui lòng thử lại với từ khóa khác hoặc chọn phòng ban khác.</p>
            </div>
            <?php endif; ?>
        </section>
    </main>

    <?php include 'footer.php'; ?>
    <?php include 'quick-actions-toolbar.php'; ?>

    <script>
        // Auto-focus search input
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('.search-input');
            if (searchInput && !searchInput.value) {
                searchInput.focus();
            }
        });

        // Phone number formatting
        document.querySelectorAll('.phone-link').forEach(link => {
            link.addEventListener('click', function(e) {
                console.log('Phone call initiated:', this.textContent);
            });
        });
    </script>
</body>
</html>