<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config.php';
require_once 'auth.php';

// Kiểm tra đăng nhập và quyền
authRequireCanBo();
$isReadOnly = authIsReadOnly();

$page_title = "Quản lý lương thưởng";
include 'header-menu.php';

// Danh sách cán bộ đầy đủ từ danh bạ điện thoại
$employees_data = [
    // ỦY BAN NHÂN DÂN XÃ LONG HIỆP
    ['id' => 1, 'full_name' => 'Nguyễn Khánh Hòa', 'employee_code' => 'NV001', 'position' => 'Chủ tịch Ủy ban Nhân dân xã', 'department_name' => 'Ủy ban Nhân dân Xã Long Hiệp'],
    ['id' => 2, 'full_name' => 'Kiên Thanh Huy Sale', 'employee_code' => 'NV002', 'position' => 'Phó Chủ tịch Ủy ban Nhân dân xã', 'department_name' => 'Ủy ban Nhân dân Xã Long Hiệp'],
    ['id' => 3, 'full_name' => 'Trần Thanh Tùng', 'employee_code' => 'NV003', 'position' => 'Phó Chủ tịch Ủy ban Nhân dân xã', 'department_name' => 'Ủy ban Nhân dân Xã Long Hiệp'],
    ['id' => 4, 'full_name' => 'Lê Văn Dũng', 'employee_code' => 'NV004', 'position' => 'Chuyên viên Văn phòng UBMTTQVN xã', 'department_name' => 'Ủy ban Nhân dân Xã Long Hiệp'],
    ['id' => 5, 'full_name' => 'Lê Thị Hồng Cẩm', 'employee_code' => 'NV005', 'position' => 'Chuyên viên Văn phòng UBMTTQVN xã', 'department_name' => 'Ủy ban Nhân dân Xã Long Hiệp'],
    ['id' => 6, 'full_name' => 'Thạch Ra', 'employee_code' => 'NV006', 'position' => 'Chuyên viên Văn phòng UBMTTQVN xã', 'department_name' => 'Ủy ban Nhân dân Xã Long Hiệp'],
    ['id' => 7, 'full_name' => 'Kim Ngọc Huỳnh', 'employee_code' => 'NV007', 'position' => 'Chuyên viên Văn phòng UBMTTQVN xã', 'department_name' => 'Ủy ban Nhân dân Xã Long Hiệp'],
    ['id' => 8, 'full_name' => 'Kiên Thắng', 'employee_code' => 'NV008', 'position' => 'Chuyên viên Văn phòng UBMTTQVN xã', 'department_name' => 'Ủy ban Nhân dân Xã Long Hiệp'],
    ['id' => 9, 'full_name' => 'Kim Ngọc Khang', 'employee_code' => 'NV009', 'position' => 'Chuyên viên Văn phòng UBMTTQVN xã', 'department_name' => 'Ủy ban Nhân dân Xã Long Hiệp'],
    ['id' => 10, 'full_name' => 'Kim Thị Di Na', 'employee_code' => 'NV010', 'position' => 'Chuyên viên Văn phòng UBMTTQVN xã', 'department_name' => 'Ủy ban Nhân dân Xã Long Hiệp'],
    ['id' => 11, 'full_name' => 'Nguyễn Văn Hùng', 'employee_code' => 'NV011', 'position' => 'Chuyên viên Văn phòng UBMTTQVN xã', 'department_name' => 'Ủy ban Nhân dân Xã Long Hiệp'],
    
    // BAN LÃNH ĐẠO ĐẢNG ỦY
    ['id' => 12, 'full_name' => 'Trần Văn Mười', 'employee_code' => 'NV012', 'position' => 'Bí thư Đảng ủy xã', 'department_name' => 'Ban Lãnh đạo Đảng ủy'],
    ['id' => 13, 'full_name' => 'Lâm Thái Hòa', 'employee_code' => 'NV013', 'position' => 'Phó Bí thư Thường trực Đảng ủy', 'department_name' => 'Ban Lãnh đạo Đảng ủy'],
    ['id' => 14, 'full_name' => 'Nguyễn Khánh Hòa', 'employee_code' => 'NV014', 'position' => 'Phó Bí thư Đảng ủy', 'department_name' => 'Ban Lãnh đạo Đảng ủy'],
    
    // ỦY VIÊN BAN THƯỜNG VỤ ĐẢNG ỦY
    ['id' => 15, 'full_name' => 'Hà Minh Tân', 'employee_code' => 'NV015', 'position' => 'UVBTV, Chủ nhiệm UBKT Đảng ủy', 'department_name' => 'Ủy viên Ban Thường vụ Đảng ủy'],
    ['id' => 16, 'full_name' => 'Dương Hoài An', 'employee_code' => 'NV016', 'position' => 'UVBTV, Trưởng ban Xây dựng Đảng', 'department_name' => 'Ủy viên Ban Thường vụ Đảng ủy'],
    ['id' => 17, 'full_name' => 'Tư Thị Mỹ Linh', 'employee_code' => 'NV017', 'position' => 'UVBTV, Chủ tịch UBMTTQ Việt Nam', 'department_name' => 'Ủy viên Ban Thường vụ Đảng ủy'],
    ['id' => 18, 'full_name' => 'Nguyễn Thị Thúy Trang', 'employee_code' => 'NV018', 'position' => 'UVBTV, Phó Chủ tịch HĐND', 'department_name' => 'Ủy viên Ban Thường vụ Đảng ủy'],
    ['id' => 19, 'full_name' => 'Phan Đình Huy', 'employee_code' => 'NV019', 'position' => 'UVBTV, Trưởng Công an xã', 'department_name' => 'Ủy viên Ban Thường vụ Đảng ủy'],
    ['id' => 20, 'full_name' => 'Nguyễn Văn Ký', 'employee_code' => 'NV020', 'position' => 'UVBTV, Chỉ huy Trưởng BCH Quân sự', 'department_name' => 'Ủy viên Ban Thường vụ Đảng ủy'],
    ['id' => 21, 'full_name' => 'Thạch Thanh Mỹ', 'employee_code' => 'NV021', 'position' => 'UVBCH, Trưởng Phòng Văn hóa - Xã hội', 'department_name' => 'Ủy viên Ban Thường vụ Đảng ủy'],
    ['id' => 22, 'full_name' => 'Lâm Quí', 'employee_code' => 'NV022', 'position' => 'UVBCH, Phó chủ nhiệm UBKT ĐU', 'department_name' => 'Ủy viên Ban Thường vụ Đảng ủy'],
    ['id' => 23, 'full_name' => 'Kim Tha', 'employee_code' => 'NV023', 'position' => 'UVBCH, phó trưởng ban VH - XH HĐND', 'department_name' => 'Ủy viên Ban Thường vụ Đảng ủy'],
    ['id' => 24, 'full_name' => 'Trầm Thị Sa Nên', 'employee_code' => 'NV024', 'position' => 'UVBCH, Phó trưởng ban XĐĐ', 'department_name' => 'Ủy viên Ban Thường vụ Đảng ủy'],
    ['id' => 25, 'full_name' => 'Đoàn Công Uẩn', 'employee_code' => 'NV025', 'position' => 'UVBCH, Phó trưởng ban XĐĐ', 'department_name' => 'Ủy viên Ban Thường vụ Đảng ủy'],
    ['id' => 26, 'full_name' => 'Kim Thái Bình', 'employee_code' => 'NV026', 'position' => 'UVBCH, Phó chủ tịch UBMTTQ Việt Nam', 'department_name' => 'Ủy viên Ban Thường vụ Đảng ủy'],
    ['id' => 27, 'full_name' => 'Tăng Thị Hồng Thắm', 'employee_code' => 'NV027', 'position' => 'UVBCH, PCT.UBMTTQ-CT.HLHPN', 'department_name' => 'Ủy viên Ban Thường vụ Đảng ủy'],
    ['id' => 28, 'full_name' => 'Trần Trung Thương', 'employee_code' => 'NV028', 'position' => 'UVBCH, PCT.UBMTTQ-BT. Đoàn TN', 'department_name' => 'Ủy viên Ban Thường vụ Đảng ủy'],
    ['id' => 29, 'full_name' => 'Kim Ngọc Mạnh', 'employee_code' => 'NV029', 'position' => 'UVBCH, PCT.UBMTTQ-CT.HCCB', 'department_name' => 'Ủy viên Ban Thường vụ Đảng ủy'],
    ['id' => 30, 'full_name' => 'Thạch Kim Sĩ', 'employee_code' => 'NV030', 'position' => 'UVBCH, PCT.UBMTTQ-CT.HND', 'department_name' => 'Ủy viên Ban Thường vụ Đảng ủy'],
    ['id' => 31, 'full_name' => 'Kim Thanh Nhứt', 'employee_code' => 'NV031', 'position' => 'UVBCH, Phó chủ nhiệm UBKT Đảng', 'department_name' => 'Ủy viên Ban Thường vụ Đảng ủy'],
    ['id' => 32, 'full_name' => 'Nguyễn Hoàng Quân', 'employee_code' => 'NV032', 'position' => 'Chuyên viên ban kiểm tra Đảng Ủy', 'department_name' => 'Ủy viên Ban Thường vụ Đảng ủy'],
    ['id' => 33, 'full_name' => 'Thạch Ngọc Bình', 'employee_code' => 'NV033', 'position' => 'Chuyên viên ban kiểm tra Đảng Ủy', 'department_name' => 'Ủy viên Ban Thường vụ Đảng ủy'],
    ['id' => 34, 'full_name' => 'Thạch Công Bình', 'employee_code' => 'NV034', 'position' => 'Chuyên viên ban kiểm tra Đảng Ủy', 'department_name' => 'Ủy viên Ban Thường vụ Đảng ủy'],
    ['id' => 35, 'full_name' => 'Nguyễn Công Hường', 'employee_code' => 'NV035', 'position' => 'Chuyên viên ban kiểm tra Đảng Ủy', 'department_name' => 'Ủy viên Ban Thường vụ Đảng ủy'],
    ['id' => 36, 'full_name' => 'Thạch Thị Kiều Oanh', 'employee_code' => 'NV036', 'position' => 'Ủy viên Ủy ban kiểm tra Đảng ủy xã', 'department_name' => 'Ủy viên Ban Thường vụ Đảng ủy'],
    ['id' => 37, 'full_name' => 'Trần Thị Xuân Thủy', 'employee_code' => 'NV037', 'position' => 'Ủy viên Ủy ban kiểm tra Đảng ủy xã', 'department_name' => 'Ủy viên Ban Thường vụ Đảng ủy'],
    ['id' => 38, 'full_name' => 'Thạch Rạch Ta Na', 'employee_code' => 'NV038', 'position' => 'Chuyên viên Ủy ban kiểm tra Đảng ủy xã', 'department_name' => 'Ủy viên Ban Thường vụ Đảng ủy'],
    
    // PHÒNG KINH TẾ
    ['id' => 39, 'full_name' => 'Kim Bảy Ly', 'employee_code' => 'NV039', 'position' => 'Trưởng Phòng Kinh tế', 'department_name' => 'Phòng Kinh tế'],
    ['id' => 40, 'full_name' => 'Son Ngọc Danh Thái', 'employee_code' => 'NV040', 'position' => 'Phó Trưởng phòng Kinh tế', 'department_name' => 'Phòng Kinh tế'],
    ['id' => 41, 'full_name' => 'Kim Thanh Phong', 'employee_code' => 'NV041', 'position' => 'Chuyên viên Phòng Kinh tế', 'department_name' => 'Phòng Kinh tế'],
    ['id' => 42, 'full_name' => 'Lê Văn Chắc', 'employee_code' => 'NV042', 'position' => 'Chuyên viên Phòng Kinh tế', 'department_name' => 'Phòng Kinh tế'],
    ['id' => 43, 'full_name' => 'Kim Ngọc Chhộts', 'employee_code' => 'NV043', 'position' => 'Chuyên viên Phòng Kinh tế', 'department_name' => 'Phòng Kinh tế'],
    ['id' => 44, 'full_name' => 'Kim Ngọc Cường', 'employee_code' => 'NV044', 'position' => 'Chuyên viên Phòng Kinh tế', 'department_name' => 'Phòng Kinh tế'],
    ['id' => 45, 'full_name' => 'Lâm Phước Hoàng', 'employee_code' => 'NV045', 'position' => 'Chuyên viên Phòng Kinh tế', 'department_name' => 'Phòng Kinh tế'],
    ['id' => 46, 'full_name' => 'Thạch Văn Thi', 'employee_code' => 'NV046', 'position' => 'Chuyên viên Phòng Kinh tế', 'department_name' => 'Phòng Kinh tế'],
    ['id' => 47, 'full_name' => 'Lê Tấn Phương', 'employee_code' => 'NV047', 'position' => 'Chuyên viên Phòng Kinh tế', 'department_name' => 'Phòng Kinh tế'],
    ['id' => 48, 'full_name' => 'TRần Văn Phúc', 'employee_code' => 'NV048', 'position' => 'UVBCH, Phó trưởng ban KT - NS HĐND', 'department_name' => 'Phòng Kinh tế'],
    ['id' => 49, 'full_name' => 'Trần Hồng Thủy', 'employee_code' => 'NV049', 'position' => 'Chuyên viên phòng kinh tế xã Long Hiệp', 'department_name' => 'Phòng Kinh tế'],
    
    // VĂN PHÒNG HĐND VÀ UBND
    ['id' => 50, 'full_name' => 'Nguyễn Trọng Thủy', 'employee_code' => 'NV050', 'position' => 'Chánh Văn phòng Đảng ủy', 'department_name' => 'Văn phòng HĐND và UBND'],
    ['id' => 51, 'full_name' => 'Thạch Som Nang', 'employee_code' => 'NV051', 'position' => 'Chánh Văn phòng HĐND và UBND', 'department_name' => 'Văn phòng HĐND và UBND'],
    ['id' => 52, 'full_name' => 'Trần Thị Hồng Thủy', 'employee_code' => 'NV052', 'position' => 'Phó Chánh Văn phòng Đảng ủy', 'department_name' => 'Văn phòng HĐND và UBND'],
    ['id' => 53, 'full_name' => 'Thạch Huỳnh Thủy', 'employee_code' => 'NV053', 'position' => 'Phó Chánh Văn phòng Đảng ủy', 'department_name' => 'Văn phòng HĐND và UBND'],
    ['id' => 54, 'full_name' => 'Hà Phước Hiệp', 'employee_code' => 'NV054', 'position' => 'Chuyên viên Văn phòng Đảng ủy', 'department_name' => 'Văn phòng HĐND và UBND'],
    ['id' => 55, 'full_name' => 'Kim Ngọc Bình', 'employee_code' => 'NV055', 'position' => 'Chuyên viên Văn phòng Đảng ủy', 'department_name' => 'Văn phòng HĐND và UBND'],
    
    // PHÒNG VĂN HÓA - XÃ HỘI
    ['id' => 56, 'full_name' => 'Thạch Thanh Mỹ', 'employee_code' => 'NV056', 'position' => 'Trưởng Phòng Văn hóa - Xã hội', 'department_name' => 'Phòng Văn hóa - Xã hội'],
    ['id' => 57, 'full_name' => 'Trần Quốc Hùng', 'employee_code' => 'NV057', 'position' => 'Phó Trưởng phòng Văn hóa - Xã hội', 'department_name' => 'Phòng Văn hóa - Xã hội'],
    ['id' => 58, 'full_name' => 'Thạch Ngọc Suyến', 'employee_code' => 'NV058', 'position' => 'Chuyên viên Phòng Văn hóa - Xã hội', 'department_name' => 'Phòng Văn hóa - Xã hội'],
    ['id' => 59, 'full_name' => 'Đoàn Văn Để', 'employee_code' => 'NV059', 'position' => 'Chuyên viên Phòng Văn hóa - Xã hội', 'department_name' => 'Phòng Văn hóa - Xã hội'],
    ['id' => 60, 'full_name' => 'Thạch Quanh Tha', 'employee_code' => 'NV060', 'position' => 'Chuyên viên Phòng Văn hóa - Xã hội', 'department_name' => 'Phòng Văn hóa - Xã hội'],
    ['id' => 61, 'full_name' => 'Thạch Ri Tâm', 'employee_code' => 'NV061', 'position' => 'Chuyên viên Phòng Văn hóa - Xã hội', 'department_name' => 'Phòng Văn hóa - Xã hội'],
    ['id' => 62, 'full_name' => 'Kim Thu Na', 'employee_code' => 'NV062', 'position' => 'Chuyên viên Phòng Văn hóa - Xã hội', 'department_name' => 'Phòng Văn hóa - Xã hội'],
    ['id' => 63, 'full_name' => 'Nguyễn Thị Trúc Giang', 'employee_code' => 'NV063', 'position' => 'Chuyên viên Phòng Văn hóa - Xã hội', 'department_name' => 'Phòng Văn hóa - Xã hội'],
    ['id' => 64, 'full_name' => 'Thạch Ngọc Cường', 'employee_code' => 'NV064', 'position' => 'Chuyên viên phòng Văn Hóa - Xã Hội xã', 'department_name' => 'Phòng Văn hóa - Xã hội'],
    
    // TRẠM Y TẾ XÃ
    ['id' => 65, 'full_name' => 'Huỳnh Thanh Tâm', 'employee_code' => 'NV065', 'position' => 'Trạm trưởng Trạm Y tế', 'department_name' => 'Trạm Y tế xã'],
    
    // CÔNG AN XÃ
    ['id' => 66, 'full_name' => 'Kim Bình Luận', 'employee_code' => 'NV066', 'position' => 'UVBCH, Phó Trưởng công an', 'department_name' => 'Công an xã'],
    ['id' => 67, 'full_name' => 'Phan Đình Huy', 'employee_code' => 'NV067', 'position' => 'UVBTV, Trưởng công an xã', 'department_name' => 'Công an xã']
];

$total_employees = count($employees_data);
?>

<link rel="stylesheet" href="hr-style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
.salary-management {
    max-width: 1400px;
    margin: 30px auto;
    padding: 0 20px;
}

.page-header {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    padding: 30px;
    border-radius: 10px;
    margin-bottom: 30px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.page-header h1 {
    margin: 0 0 10px 0;
    font-size: 28px;
}

.page-header p {
    margin: 0;
    opacity: 0.9;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 20px;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.stat-icon.green {
    background: #d4edda;
    color: #28a745;
}

.stat-icon.blue {
    background: #d1ecf1;
    color: #17a2b8;
}

.stat-icon.orange {
    background: #fff3cd;
    color: #ffc107;
}

.stat-icon.purple {
    background: #e2d9f3;
    color: #6f42c1;
}

.stat-content .number {
    font-size: 28px;
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
}

.stat-content .label {
    color: #666;
    font-size: 14px;
}

.salary-table-container {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
}

.table-header {
    padding: 20px;
    border-bottom: 2px solid #f0f0f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table-header h3 {
    margin: 0;
    color: #333;
}

.btn-primary {
    padding: 10px 20px;
    background: #28a745;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary:hover {
    background: #218838;
}

.salary-table {
    width: 100%;
    border-collapse: collapse;
}

.salary-table th,
.salary-table td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #f0f0f0;
}

.salary-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

.salary-table tr:hover {
    background: #f8f9fa;
}

.badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.badge-bien-che {
    background: #d4edda;
    color: #155724;
}

.badge-hop-dong {
    background: #fff3cd;
    color: #856404;
}

.salary-amount {
    font-weight: bold;
    color: #28a745;
}

.action-buttons {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.btn-sm {
    padding: 5px 10px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    text-decoration: none;
    display: inline-block;
}

.btn-info {
    background: #17a2b8;
    color: white;
}

.btn-warning {
    background: #ffc107;
    color: #333;
}

.btn-sm:hover {
    opacity: 0.8;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}

.empty-state i {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.3;
}
</style>

<div class="salary-management">
    <div class="page-header">
        <h1><i class="fas fa-money-bill-wave"></i> Quản lý lương thưởng</h1>
        <p>Quản lý thông tin lương, phụ cấp, thưởng và các khoản khấu trừ</p>
    </div>

    <?php include 'read-only-notice.php'; ?>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <div class="number"><?php echo $total_employees; ?></div>
                <div class="label">Tổng nhân viên</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fas fa-calculator"></i>
            </div>
            <div class="stat-content">
                <div class="number">0</div>
                <div class="label">Phiếu lương tháng này</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="fas fa-gift"></i>
            </div>
            <div class="stat-content">
                <div class="number">0</div>
                <div class="label">Khoản thưởng</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon purple">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <div class="number">0 VNĐ</div>
                <div class="label">Tổng quỹ lương</div>
            </div>
        </div>
    </div>

    <div class="salary-table-container">
        <div class="table-header">
            <h3>Danh sách lương nhân viên</h3>
            <div>
                <a href="#" class="btn-primary" onclick="themThuongMoi(); return false;">
                    <i class="fas fa-plus-circle"></i>
                    Thêm thưởng mới
                </a>
            </div>
        </div>

        <?php if ($total_employees > 0): ?>
        <table class="salary-table">
            <thead>
                <tr>
                    <th>Mã NV</th>
                    <th>Họ và tên</th>
                    <th>Phòng ban</th>
                    <th>Chức vụ</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees_data as $row): ?>
                    <?php
                    // Lấy khoản thưởng gần nhất
                    $bonus_sql = "SELECT id, bonus_type, reason, amount, bonus_date 
                                 FROM hr_bonuses 
                                 WHERE employee_id = ? 
                                 ORDER BY bonus_date DESC, created_at DESC 
                                 LIMIT 1";
                    $bonus_stmt = $conn->prepare($bonus_sql);
                    $bonus_stmt->bind_param("i", $row['id']);
                    $bonus_stmt->execute();
                    $bonus_result = $bonus_stmt->get_result();
                    $latest_bonus = $bonus_result->fetch_assoc();
                    $bonus_stmt->close();
                    ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($row['employee_code']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['department_name'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($row['position'] ?? '-'); ?></td>
                    <td>
                        <span class="badge badge-bien-che">Đang làm việc</span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-sm btn-info" onclick="xemLichSuThuong(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['full_name']); ?>')" title="Xem lịch sử thưởng">
                                <i class="fas fa-gift"></i> Lịch sử thưởng
                            </button>
                            <button class="btn-sm btn-warning" onclick="themThuong(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['full_name']); ?>')" title="Thêm khoản thưởng">
                                <i class="fas fa-plus-circle"></i> Thêm thưởng
                            </button>
                            <?php if ($latest_bonus): ?>
                            <button class="btn-sm" style="background: #ffc107; color: #333;" onclick="suaThuongNhanh(<?php echo $latest_bonus['id']; ?>, '<?php echo $latest_bonus['bonus_type']; ?>', '<?php echo htmlspecialchars(addslashes($latest_bonus['reason'])); ?>', <?php echo $latest_bonus['amount']; ?>, '<?php echo $latest_bonus['bonus_date']; ?>', <?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['full_name']); ?>')" title="Sửa thưởng gần nhất">
                                <i class="fas fa-edit"></i> Sửa
                            </button>
                            <button class="btn-sm" style="background: #dc3545; color: white;" onclick="xoaThuongNhanh(<?php echo $latest_bonus['id']; ?>, '<?php echo htmlspecialchars($row['full_name']); ?>')" title="Xóa thưởng gần nhất">
                                <i class="fas fa-trash"></i> Xóa
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p>Chưa có dữ liệu nhân viên</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal xem lịch sử thưởng -->
<div id="modalLichSuThuong" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; overflow-y: auto;">
    <div style="max-width: 800px; margin: 50px auto; background: white; border-radius: 10px; padding: 30px; position: relative;">
        <button onclick="dongModal('modalLichSuThuong')" style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">&times;</button>
        
        <h2 style="margin: 0 0 20px 0; color: #28a745;">
            <i class="fas fa-gift"></i> Lịch sử thưởng
        </h2>
        <p id="tenNhanVien" style="color: #666; margin-bottom: 20px;"></p>
        
        <div id="noiDungLichSuThuong">
            <div style="text-align: center; padding: 40px; color: #999;">
                <i class="fas fa-spinner fa-spin" style="font-size: 32px;"></i>
                <p>Đang tải dữ liệu...</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal thêm/sửa thưởng -->
<div id="modalThemThuong" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; overflow-y: auto;">
    <div style="max-width: 600px; margin: 50px auto; background: white; border-radius: 10px; padding: 30px; position: relative;">
        <button onclick="dongModal('modalThemThuong')" style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">&times;</button>
        
        <h2 id="tieuDeModalThuong" style="margin: 0 0 20px 0; color: #28a745;">
            <i class="fas fa-plus-circle"></i> Thêm khoản thưởng
        </h2>
        <p id="tenNhanVienThuong" style="color: #666; margin-bottom: 20px;"></p>
        
        <form id="formThemThuong" style="display: flex; flex-direction: column; gap: 15px;">
            <input type="hidden" id="employeeIdThuong" name="employee_id">
            <input type="hidden" id="bonusIdEdit" name="bonus_id">
            
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Họ và tên nhân viên được thưởng:</label>
                <input type="text" id="newEmployeeName" name="new_employee_name" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" placeholder="Nhập họ và tên nhân viên...">
                <small style="color: #666; font-style: italic;">* Hệ thống sẽ tự động tạo nhân viên mới nếu chưa tồn tại</small>
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Chức vụ:</label>
                <input type="text" id="employeePosition" name="employee_position" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" placeholder="Ví dụ: Chuyên viên, Phó phòng, Trưởng phòng...">
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Phòng ban:</label>
                <select id="employeeDepartment" name="employee_department" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="">-- Chọn phòng ban --</option>
                    <?php
                    $dept_sql = "SELECT id, name FROM departments ORDER BY name ASC";
                    $dept_result = $conn->query($dept_sql);
                    if ($dept_result) {
                        while ($dept = $dept_result->fetch_assoc()) {
                            echo '<option value="' . $dept['id'] . '">' . htmlspecialchars($dept['name']) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Loại thưởng:</label>
                <select id="bonusType" name="bonus_type" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="">-- Chọn loại thưởng --</option>
                    <option value="tet">Thưởng Tết Nguyên Đán</option>
                    <option value="30_4">Thưởng 30/4 - 1/5</option>
                    <option value="2_9">Thưởng 2/9</option>
                    <option value="thanh_tich">Thưởng thành tích</option>
                    <option value="du_an">Thưởng hoàn thành dự án</option>
                    <option value="khen_thuong">Khen thưởng đột xuất</option>
                    <option value="khac">Khác</option>
                </select>
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Lý do thưởng:</label>
                <textarea id="bonusReason" name="reason" required rows="3" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" placeholder="Nhập lý do thưởng..."></textarea>
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Số tiền thưởng (VNĐ):</label>
                <input type="number" id="bonusAmount" name="amount" required min="0" step="1000" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" placeholder="Ví dụ: 1000000">
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Ngày thưởng:</label>
                <input type="date" id="bonusDate" name="bonus_date" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;" value="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 10px;">
                <button type="submit" style="flex: 1; padding: 12px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;">
                    <i class="fas fa-save"></i> Lưu thưởng
                </button>
                <button type="button" onclick="dongModal('modalThemThuong')" style="flex: 1; padding: 12px; background: #6c757d; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600;">
                    <i class="fas fa-times"></i> Hủy
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentEmployeeId = 0;

function xemLichSuThuong(employeeId, fullName) {
    currentEmployeeId = employeeId;
    document.getElementById('tenNhanVien').textContent = 'Nhân viên: ' + fullName;
    document.getElementById('modalLichSuThuong').style.display = 'block';
    
    // Hiển thị loading
    document.getElementById('noiDungLichSuThuong').innerHTML = '<div style="text-align: center; padding: 40px; color: #999;"><i class="fas fa-spinner fa-spin" style="font-size: 32px;"></i><p>Đang tải dữ liệu...</p></div>';
    
    // Gọi API lấy dữ liệu thật
    fetch('api-bonus.php?action=get_bonuses&employee_id=' + employeeId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                hienThiLichSuThuong(data.bonuses, data.total);
            } else {
                document.getElementById('noiDungLichSuThuong').innerHTML = '<div style="text-align: center; padding: 40px; color: #dc3545;"><i class="fas fa-exclamation-circle" style="font-size: 32px;"></i><p>' + data.message + '</p></div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('noiDungLichSuThuong').innerHTML = '<div style="text-align: center; padding: 40px; color: #dc3545;"><i class="fas fa-exclamation-circle" style="font-size: 32px;"></i><p>Lỗi khi tải dữ liệu</p></div>';
        });
}

function hienThiLichSuThuong(bonuses, total) {
    const bonusTypeNames = {
        'tet': 'Thưởng Tết Nguyên Đán',
        '30_4': 'Thưởng 30/4 - 1/5',
        '2_9': 'Thưởng 2/9',
        'thanh_tich': 'Thưởng thành tích',
        'du_an': 'Thưởng dự án',
        'khen_thuong': 'Khen thưởng đột xuất',
        'khac': 'Khác'
    };
    
    let html = '<table style="width: 100%; border-collapse: collapse;">';
    html += '<thead><tr style="background: #f8f9fa;">';
    html += '<th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Ngày</th>';
    html += '<th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Loại thưởng</th>';
    html += '<th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Lý do</th>';
    html += '<th style="padding: 12px; text-align: right; border-bottom: 2px solid #ddd;">Số tiền</th>';
    html += '<th style="padding: 12px; text-align: center; border-bottom: 2px solid #ddd; width: 100px;">Thao tác</th>';
    html += '</tr></thead><tbody>';
    
    if (bonuses.length > 0) {
        bonuses.forEach(item => {
            html += '<tr style="border-bottom: 1px solid #f0f0f0;">';
            html += '<td style="padding: 12px;">' + item.bonus_date + '</td>';
            html += '<td style="padding: 12px;"><span style="background: #d4edda; color: #155724; padding: 4px 12px; border-radius: 12px; font-size: 12px;">' + (bonusTypeNames[item.bonus_type] || item.bonus_type) + '</span></td>';
            html += '<td style="padding: 12px;">' + item.reason + '</td>';
            html += '<td style="padding: 12px; text-align: right; font-weight: bold; color: #28a745;">' + item.amount.toLocaleString('vi-VN') + ' VNĐ</td>';
            html += '<td style="padding: 12px; text-align: center;">';
            html += '<div style="display: flex; gap: 5px; justify-content: center;">';
            html += '<button onclick="suaThuong(' + item.id + ', \'' + item.bonus_type + '\', \'' + item.reason.replace(/'/g, "\\'") + '\', ' + item.amount + ', \'' + item.bonus_date + '\')" style="padding: 5px 10px; background: #ffc107; color: #333; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;" title="Sửa khoản thưởng">';
            html += '<i class="fas fa-edit"></i> Sửa';
            html += '</button>';
            html += '<button onclick="xoaThuong(' + item.id + ', \'' + (bonusTypeNames[item.bonus_type] || item.bonus_type) + '\')" style="padding: 5px 10px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;" title="Xóa khoản thưởng">';
            html += '<i class="fas fa-trash"></i> Xóa';
            html += '</button>';
            html += '</div>';
            html += '</td>';
            html += '</tr>';
        });
        
        html += '<tr style="background: #f8f9fa; font-weight: bold;">';
        html += '<td colspan="4" style="padding: 12px; text-align: right;">Tổng cộng:</td>';
        html += '<td style="padding: 12px; text-align: right; color: #28a745; font-size: 16px;">' + total.toLocaleString('vi-VN') + ' VNĐ</td>';
        html += '</tr>';
    } else {
        html += '<tr><td colspan="5" style="padding: 40px; text-align: center; color: #999;">Chưa có lịch sử thưởng</td></tr>';
    }
    
    html += '</tbody></table>';
    document.getElementById('noiDungLichSuThuong').innerHTML = html;
}

function themThuong(employeeId, fullName) {
    currentEmployeeId = employeeId;
    document.getElementById('tieuDeModalThuong').innerHTML = '<i class="fas fa-plus-circle"></i> Thêm khoản thưởng';
    document.getElementById('tenNhanVienThuong').textContent = 'Nhân viên: ' + fullName;
    document.getElementById('employeeIdThuong').value = employeeId;
    document.getElementById('newEmployeeName').value = fullName;
    document.getElementById('bonusIdEdit').value = '';
    document.getElementById('formThemThuong').reset();
    document.getElementById('newEmployeeName').value = fullName;
    document.getElementById('bonusDate').value = '<?php echo date('Y-m-d'); ?>';
    document.getElementById('modalThemThuong').style.display = 'block';
}

function themThuongMoi() {
    currentEmployeeId = 0;
    document.getElementById('tieuDeModalThuong').innerHTML = '<i class="fas fa-plus-circle"></i> Thêm khoản thưởng mới';
    document.getElementById('tenNhanVienThuong').textContent = 'Nhập thông tin nhân viên và khoản thưởng';
    document.getElementById('employeeIdThuong').value = '';
    document.getElementById('newEmployeeName').value = '';
    document.getElementById('bonusIdEdit').value = '';
    document.getElementById('formThemThuong').reset();
    document.getElementById('bonusDate').value = '<?php echo date('Y-m-d'); ?>';
    document.getElementById('modalThemThuong').style.display = 'block';
}

function suaThuong(bonusId, bonusType, reason, amount, bonusDate) {
    document.getElementById('tieuDeModalThuong').innerHTML = '<i class="fas fa-edit"></i> Sửa khoản thưởng';
    // Giữ nguyên tên nhân viên từ modal lịch sử
    // document.getElementById('tenNhanVienThuong') đã có sẵn từ modal lịch sử
    document.getElementById('bonusIdEdit').value = bonusId;
    document.getElementById('bonusType').value = bonusType;
    document.getElementById('bonusReason').value = reason;
    document.getElementById('bonusAmount').value = amount;
    document.getElementById('bonusDate').value = bonusDate;
    document.getElementById('modalThemThuong').style.display = 'block';
}

function suaThuongNhanh(bonusId, bonusType, reason, amount, bonusDate, employeeId, fullName) {
    currentEmployeeId = employeeId;
    document.getElementById('tieuDeModalThuong').innerHTML = '<i class="fas fa-edit"></i> Sửa khoản thưởng';
    document.getElementById('tenNhanVienThuong').textContent = 'Nhân viên: ' + fullName;
    document.getElementById('employeeIdThuong').value = employeeId;
    document.getElementById('newEmployeeName').value = fullName;
    document.getElementById('bonusIdEdit').value = bonusId;
    document.getElementById('bonusType').value = bonusType;
    document.getElementById('bonusReason').value = reason;
    document.getElementById('bonusAmount').value = amount;
    document.getElementById('bonusDate').value = bonusDate;
    document.getElementById('modalThemThuong').style.display = 'block';
}

function xoaThuongNhanh(bonusId, fullName) {
    if (confirm('Bạn có chắc chắn muốn xóa khoản thưởng gần nhất của nhân viên "' + fullName + '" không?\n\nHành động này không thể hoàn tác!')) {
        const formData = new FormData();
        formData.append('action', 'delete_bonus');
        formData.append('bonus_id', bonusId);
        
        fetch('api-bonus.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload(); // Reload trang để cập nhật danh sách
            } else {
                alert('Lỗi: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Lỗi khi xóa khoản thưởng');
        });
    }
}

function xoaThuong(bonusId, loaiThuong) {
    if (confirm('Bạn có chắc chắn muốn xóa khoản thưởng "' + loaiThuong + '" này không?\n\nHành động này không thể hoàn tác!')) {
        // Gửi request xóa đến server
        const formData = new FormData();
        formData.append('action', 'delete_bonus');
        formData.append('bonus_id', bonusId);
        
        fetch('api-bonus.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                // Reload lại danh sách thưởng
                xemLichSuThuong(currentEmployeeId, document.getElementById('tenNhanVien').textContent.replace('Nhân viên: ', ''));
            } else {
                alert('Lỗi: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Lỗi khi xóa khoản thưởng');
        });
    }
}

function dongModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

document.getElementById('formThemThuong').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const bonusId = document.getElementById('bonusIdEdit').value;
    const newEmployeeName = document.getElementById('newEmployeeName').value.trim();
    
    console.log('Form submit - bonusId:', bonusId);
    console.log('Form submit - newEmployeeName:', newEmployeeName);
    
    // Kiểm tra phải nhập tên nhân viên
    if (!newEmployeeName) {
        alert('Vui lòng nhập họ và tên nhân viên!');
        return;
    }
    
    formData.set('new_employee_name', newEmployeeName);
    
    // Nếu có bonusId thì là sửa, không thì là thêm mới
    if (bonusId) {
        formData.append('action', 'update_bonus');
        console.log('Action: update_bonus');
    } else {
        formData.append('action', 'add_bonus');
        console.log('Action: add_bonus');
    }
    
    // Log tất cả form data
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
    
    fetch('api-bonus-simple.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            alert(data.message);
            dongModal('modalThemThuong');
            this.reset();
            // Reload trang để cập nhật
            location.reload();
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Lỗi khi xử lý khoản thưởng: ' + error.message);
    });
});

// Đóng modal khi click bên ngoài
window.onclick = function(event) {
    if (event.target.id === 'modalLichSuThuong') {
        dongModal('modalLichSuThuong');
    }
    if (event.target.id === 'modalThemThuong') {
        dongModal('modalThemThuong');
    }
}
</script>

<?php
$conn->close();
include 'footer.php';
?>
