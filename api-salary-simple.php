<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

session_start();
require_once 'config.php';
require_once 'auth.php';

// Kiểm tra đăng nhập
try {
    authRequireCanBo();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để thực hiện thao tác này']);
    exit();
}

$action = $_POST['action'] ?? '';

// File lưu dữ liệu lương
$salary_file = 'data/salary_data.json';

// Đảm bảo thư mục data tồn tại
if (!file_exists('data')) {
    mkdir('data', 0755, true);
}

// Dữ liệu lương mặc định
$default_salary_data = [
    ['name' => 'Nguyễn Khánh Hòa', 'position' => 'Chủ tịch Ủy ban Nhân dân xã', 'department' => 'Ủy ban Nhân dân Xã Long Hiệp', 'salary' => 10100000],
    ['name' => 'Kiên Thanh Huy Sale', 'position' => 'Phó Chủ tịch Ủy ban Nhân dân xã', 'department' => 'Ủy ban Nhân dân Xã Long Hiệp', 'salary' => 8700000],
    ['name' => 'Trần Thanh Tùng', 'position' => 'Phó Chủ tịch Ủy ban Nhân dân xã', 'department' => 'Ủy ban Nhân dân Xã Long Hiệp', 'salary' => 8600000],
    ['name' => 'Lê Văn Dũng', 'position' => 'Chuyên viên Văn phòng UBMTTQVN xã', 'department' => 'Ủy ban Nhân dân Xã Long Hiệp', 'salary' => 5400000],
    ['name' => 'Lê Thị Hồng Cẩm', 'position' => 'Chuyên viên Văn phòng UBMTTQVN xã', 'department' => 'Ủy ban Nhân dân Xã Long Hiệp', 'salary' => 5300000],
    ['name' => 'Thạch Ra', 'position' => 'Chuyên viên Văn phòng UBMTTQVN xã', 'department' => 'Ủy ban Nhân dân Xã Long Hiệp', 'salary' => 5200000],
    ['name' => 'Kim Ngọc Huỳnh', 'position' => 'Chuyên viên Văn phòng UBMTTQVN xã', 'department' => 'Ủy ban Nhân dân Xã Long Hiệp', 'salary' => 5100000],
    ['name' => 'Kiên Thắng', 'position' => 'Chuyên viên Văn phòng UBMTTQVN xã', 'department' => 'Ủy ban Nhân dân Xã Long Hiệp', 'salary' => 5000000],
    ['name' => 'Kim Ngọc Khang', 'position' => 'Chuyên viên Văn phòng UBMTTQVN xã', 'department' => 'Ủy ban Nhân dân Xã Long Hiệp', 'salary' => 4900000],
    ['name' => 'Kim Thị Di Na', 'position' => 'Chuyên viên Văn phòng UBMTTQVN xã', 'department' => 'Ủy ban Nhân dân Xã Long Hiệp', 'salary' => 4800000],
    ['name' => 'Nguyễn Văn Hùng', 'position' => 'Chuyên viên Văn phòng UBMTTQVN xã', 'department' => 'Ủy ban Nhân dân Xã Long Hiệp', 'salary' => 4700000],
    ['name' => 'Trần Văn Mười', 'position' => 'Bí thư Đảng ủy xã', 'department' => 'Ban Lãnh đạo Đảng ủy', 'salary' => 10500000],
    ['name' => 'Lâm Thái Hòa', 'position' => 'Phó Bí thư Thường trực Đảng ủy', 'department' => 'Ban Lãnh đạo Đảng ủy', 'salary' => 9200000],
    ['name' => 'Nguyễn Khánh Hòa', 'position' => 'Phó Bí thư Đảng ủy', 'department' => 'Ban Lãnh đạo Đảng ủy', 'salary' => 9000000],
    ['name' => 'Hà Minh Tân', 'position' => 'UVBTV, Chủ nhiệm UBKT Đảng ủy', 'department' => 'Ủy viên Ban Thường vụ Đảng ủy', 'salary' => 8500000],
    ['name' => 'Dương Hoài An', 'position' => 'UVBTV, Trưởng ban Xây dựng Đảng', 'department' => 'Ủy viên Ban Thường vụ Đảng ủy', 'salary' => 8400000],
    ['name' => 'Tư Thị Mỹ Linh', 'position' => 'UVBTV, Chủ tịch UBMTTQ Việt Nam', 'department' => 'Ủy viên Ban Thường vụ Đảng ủy', 'salary' => 8300000],
    ['name' => 'Nguyễn Thị Thúy Trang', 'position' => 'UVBTV, Phó Chủ tịch HĐND', 'department' => 'Ủy viên Ban Thường vụ Đảng ủy', 'salary' => 8200000],
    ['name' => 'Phan Đình Huy', 'position' => 'UVBTV, Trưởng Công an xã', 'department' => 'Ủy viên Ban Thường vụ Đảng ủy', 'salary' => 8100000],
    ['name' => 'Nguyễn Văn Ký', 'position' => 'UVBTV, Chỉ huy Trưởng BCH Quân sự', 'department' => 'Ủy viên Ban Thường vụ Đảng ủy', 'salary' => 8000000],
    ['name' => 'Thạch Thanh Mỹ', 'position' => 'UVBCH, Trưởng Phòng Văn hóa - Xã hội', 'department' => 'Ủy viên Ban Thường vụ Đảng ủy', 'salary' => 7900000],
    ['name' => 'Lâm Quí', 'position' => 'UVBCH, Phó chủ nhiệm UBKT ĐU', 'department' => 'Ủy viên Ban Thường vụ Đảng ủy', 'salary' => 7800000],
    ['name' => 'Kim Tha', 'position' => 'UVBCH, phó trưởng ban VH - XH HĐND', 'department' => 'Ủy viên Ban Thường vụ Đảng ủy', 'salary' => 7700000],
    ['name' => 'Trầm Thị Sa Nên', 'position' => 'UVBCH, Phó trưởng ban XĐĐ', 'department' => 'Ủy viên Ban Thường vụ Đảng ủy', 'salary' => 7600000],
    ['name' => 'Đoàn Công Uẩn', 'position' => 'UVBCH, Phó trưởng ban XĐĐ', 'department' => 'Ủy viên Ban Thường vụ Đảng ủy', 'salary' => 7500000],
    ['name' => 'Kim Thái Bình', 'position' => 'UVBCH, Phó chủ tịch UBMTTQ Việt Nam', 'department' => 'Ủy viên Ban Thường vụ Đảng ủy', 'salary' => 7400000],
    ['name' => 'Tăng Thị Hồng Thắm', 'position' => 'UVBCH, PCT.UBMTTQ-CT.HLHPN', 'department' => 'Ủy viên Ban Thường vụ Đảng ủy', 'salary' => 7300000],
    ['name' => 'Trần Trung Thương', 'position' => 'UVBCH, PCT.UBMTTQ-BT. Đoàn TN', 'department' => 'Ủy viên Ban Thường vụ Đảng ủy', 'salary' => 7200000],
    ['name' => 'Kim Ngọc Mạnh', 'position' => 'UVBCH, PCT.UBMTTQ-CT.HCCB', 'department' => 'Ủy viên Ban Thường vụ Đảng ủy', 'salary' => 7100000],
    ['name' => 'Thạch Kim Sĩ', 'position' => 'UVBCH, PCT.UBMTTQ-CT.HND', 'department' => 'Ủy viên Ban Thường vụ Đảng ủy', 'salary' => 7000000],
    ['name' => 'Kim Thanh Nhứt', 'position' => 'UVBCH, Phó chủ nhiệm UBKT Đảng', 'department' => 'Ủy viên Ban Thường vụ Đảng ủy', 'salary' => 6900000],
    ['name' => 'Nguyễn Hoàng Quân', 'position' => 'Chuyên viên ban kiểm tra Đảng Ủy', 'department' => 'Ủy viên Ban Thường vụ Đảng ủy', 'salary' => 5500000],
    ['name' => 'Thạch Ngọc Bình', 'position' => 'Chuyên viên ban kiểm tra Đảng Ủy', 'department' => 'Ủy viên Ban Thường vụ Đảng ủy', 'salary' => 5400000],
    ['name' => 'Thạch Công Bình', 'position' => 'Chuyên viên ban kiểm tra Đảng Ủy', 'department' => 'Ủy viên Ban Thường vụ Đảng ủy', 'salary' => 5300000],
    ['name' => 'Nguyễn Công Hường', 'position' => 'Chuyên viên ban kiểm tra Đảng Ủy', 'department' => 'Ủy viên Ban Thường vụ Đảng ủy', 'salary' => 5200000],
    ['name' => 'Thạch Thị Kiều Oanh', 'position' => 'Ủy viên Ủy ban kiểm tra Đảng ủy xã', 'department' => 'Ủy viên Ban Thường vụ Đảng ủy', 'salary' => 5100000],
    ['name' => 'Trần Thị Xuân Thủy', 'position' => 'Ủy viên Ủy ban kiểm tra Đảng ủy xã', 'department' => 'Ủy viên Ban Thường vụ Đảng ủy', 'salary' => 5000000],
    ['name' => 'Thạch Rạch Ta Na', 'position' => 'Chuyên viên Ủy ban kiểm tra Đảng ủy xã', 'department' => 'Ủy viên Ban Thường vụ Đảng ủy', 'salary' => 4900000],
    ['name' => 'Kim Bảy Ly', 'position' => 'Trưởng Phòng Kinh tế', 'department' => 'Phòng Kinh tế', 'salary' => 8100000],
    ['name' => 'Son Ngọc Danh Thái', 'position' => 'Phó Trưởng phòng Kinh tế', 'department' => 'Phòng Kinh tế', 'salary' => 6900000],
    ['name' => 'Kim Thanh Phong', 'position' => 'Chuyên viên Phòng Kinh tế', 'department' => 'Phòng Kinh tế', 'salary' => 5600000],
    ['name' => 'Lê Văn Chắc', 'position' => 'Chuyên viên Phòng Kinh tế', 'department' => 'Phòng Kinh tế', 'salary' => 5500000],
    ['name' => 'Kim Ngọc Chhộts', 'position' => 'Chuyên viên Phòng Kinh tế', 'department' => 'Phòng Kinh tế', 'salary' => 5400000],
    ['name' => 'Kim Ngọc Cường', 'position' => 'Chuyên viên Phòng Kinh tế', 'department' => 'Phòng Kinh tế', 'salary' => 5300000],
    ['name' => 'Lâm Phước Hoàng', 'position' => 'Chuyên viên Phòng Kinh tế', 'department' => 'Phòng Kinh tế', 'salary' => 5700000],
    ['name' => 'Thạch Văn Thi', 'position' => 'Chuyên viên Phòng Kinh tế', 'department' => 'Phòng Kinh tế', 'salary' => 5200000],
    ['name' => 'Lê Tấn Phương', 'position' => 'Chuyên viên Phòng Kinh tế', 'department' => 'Phòng Kinh tế', 'salary' => 5600000],
    ['name' => 'TRần Văn Phúc', 'position' => 'UVBCH, Phó trưởng ban KT - NS HĐND', 'department' => 'Phòng Kinh tế', 'salary' => 6800000],
    ['name' => 'Trần Hồng Thủy', 'position' => 'Chuyên viên phòng kinh tế xã Long Hiệp', 'department' => 'Phòng Kinh tế', 'salary' => 5100000],
    ['name' => 'Nguyễn Trọng Thủy', 'position' => 'Chánh Văn phòng Đảng ủy', 'department' => 'Văn phòng HĐND và UBND', 'salary' => 8000000],
    ['name' => 'Thạch Som Nang', 'position' => 'Chánh Văn phòng HĐND và UBND', 'department' => 'Văn phòng HĐND và UBND', 'salary' => 7700000],
    ['name' => 'Trần Thị Hồng Thủy', 'position' => 'Phó Chánh Văn phòng Đảng ủy', 'department' => 'Văn phòng HĐND và UBND', 'salary' => 6800000],
    ['name' => 'Thạch Huỳnh Thủy', 'position' => 'Phó Chánh Văn phòng Đảng ủy', 'department' => 'Văn phòng HĐND và UBND', 'salary' => 6700000],
    ['name' => 'Hà Phước Hiệp', 'position' => 'Chuyên viên Văn phòng Đảng ủy', 'department' => 'Văn phòng HĐND và UBND', 'salary' => 5500000],
    ['name' => 'Kim Ngọc Bình', 'position' => 'Chuyên viên Văn phòng Đảng ủy', 'department' => 'Văn phòng HĐND và UBND', 'salary' => 5400000],
    ['name' => 'Thạch Thanh Mỹ', 'position' => 'Trưởng Phòng Văn hóa - Xã hội', 'department' => 'Phòng Văn hóa - Xã hội', 'salary' => 8300000],
    ['name' => 'Trần Quốc Hùng', 'position' => 'Phó Trưởng phòng Văn hóa - Xã hội', 'department' => 'Phòng Văn hóa - Xã hội', 'salary' => 7000000],
    ['name' => 'Thạch Ngọc Suyến', 'position' => 'Chuyên viên Phòng Văn hóa - Xã hội', 'department' => 'Phòng Văn hóa - Xã hội', 'salary' => 5700000],
    ['name' => 'Đoàn Văn Để', 'position' => 'Chuyên viên Phòng Văn hóa - Xã hội', 'department' => 'Phòng Văn hóa - Xã hội', 'salary' => 5600000],
    ['name' => 'Thạch Quanh Tha', 'position' => 'Chuyên viên Phòng Văn hóa - Xã hội', 'department' => 'Phòng Văn hóa - Xã hội', 'salary' => 5500000],
    ['name' => 'Thạch Ri Tâm', 'position' => 'Chuyên viên Phòng Văn hóa - Xã hội', 'department' => 'Phòng Văn hóa - Xã hội', 'salary' => 5800000],
    ['name' => 'Kim Thu Na', 'position' => 'Chuyên viên Phòng Văn hóa - Xã hội', 'department' => 'Phòng Văn hóa - Xã hội', 'salary' => 5400000],
    ['name' => 'Nguyễn Thị Trúc Giang', 'position' => 'Chuyên viên Phòng Văn hóa - Xã hội', 'department' => 'Phòng Văn hóa - Xã hội', 'salary' => 5300000],
    ['name' => 'Thạch Ngọc Cường', 'position' => 'Chuyên viên phòng Văn Hóa - Xã Hội xã', 'department' => 'Phòng Văn hóa - Xã hội', 'salary' => 5200000],
    ['name' => 'Huỳnh Thanh Tâm', 'position' => 'Trạm trưởng Trạm Y tế', 'department' => 'Trạm Y tế xã', 'salary' => 7500000],
    ['name' => 'Kim Bình Luận', 'position' => 'UVBCH, Phó Trưởng công an', 'department' => 'Công an xã', 'salary' => 7200000],
    ['name' => 'Phan Đình Huy', 'position' => 'UVBTV, Trưởng công an xã', 'department' => 'Công an xã', 'salary' => 8100000]
];

// Đọc dữ liệu từ file hoặc sử dụng mặc định
if (file_exists($salary_file)) {
    $salary_data = json_decode(file_get_contents($salary_file), true);
} else {
    $salary_data = $default_salary_data;
    file_put_contents($salary_file, json_encode($salary_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Xử lý các action
switch ($action) {
    case 'update_salary':
        $employee_index = intval($_POST['employee_index'] ?? -1);
        $new_base_salary = floatval($_POST['new_base_salary'] ?? 0);
        $new_attendance_score = intval($_POST['new_attendance_score'] ?? 0);
        
        if ($employee_index < 0 || $employee_index >= count($salary_data)) {
            echo json_encode(['success' => false, 'message' => 'Chỉ số nhân viên không hợp lệ']);
            exit();
        }
        
        if ($new_base_salary <= 0) {
            echo json_encode(['success' => false, 'message' => 'Mức lương phải lớn hơn 0']);
            exit();
        }
        
        if ($new_attendance_score < 0 || $new_attendance_score > 100) {
            echo json_encode(['success' => false, 'message' => 'Số chấm phải từ 0 đến 100']);
            exit();
        }
        
        // Cập nhật lương và số chấm
        $salary_data[$employee_index]['base_salary'] = $new_base_salary;
        $salary_data[$employee_index]['attendance_score'] = $new_attendance_score;
        // Xóa key 'salary' cũ nếu có
        unset($salary_data[$employee_index]['salary']);
        
        // Lưu vào file
        if (file_put_contents($salary_file, json_encode($salary_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            echo json_encode([
                'success' => true, 
                'message' => 'Cập nhật lương và số chấm thành công cho ' . $salary_data[$employee_index]['name']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lỗi khi lưu dữ liệu']);
        }
        break;
        
    case 'get_salary_data':
        echo json_encode(['success' => true, 'data' => $salary_data]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Action không hợp lệ']);
        break;
}
?>
