<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config.php';
require_once 'auth.php';

authRequireCanBo('index.php');

$conn = getDBConnection();
$error_message = '';
$success_message = '';

// Lấy danh sách phòng ban
$departments = [];
$dept_result = $conn->query("SELECT id, name FROM departments ORDER BY name");
if ($dept_result) {
    while ($row = $dept_result->fetch_assoc()) {
        $departments[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_code = trim($_POST['employee_code']);
    $full_name = trim($_POST['full_name']);
    $date_of_birth = $_POST['date_of_birth'] ?: null;
    $gender = $_POST['gender'];
    $id_card = trim($_POST['id_card']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $current_address = trim($_POST['current_address']);
    
    $department_id = $_POST['department_id'] ?: null;
    $position = trim($_POST['position']);
    $job_title = trim($_POST['job_title']);
    $employment_type = $_POST['employment_type'];
    $start_date = $_POST['start_date'] ?: null;
    $contract_end_date = $_POST['contract_end_date'] ?: null;
    $salary = $_POST['salary'] ?: null;
    
    $education_level = trim($_POST['education_level']);
    $major = trim($_POST['major']);
    $graduation_year = $_POST['graduation_year'] ?: null;
    $school = trim($_POST['school']);
    
    $ethnic = trim($_POST['ethnic']);
    $religion = trim($_POST['religion']);
    $marital_status = $_POST['marital_status'];
    $emergency_contact = trim($_POST['emergency_contact']);
    $emergency_phone = trim($_POST['emergency_phone']);
    
    $status = $_POST['status'];
    $notes = trim($_POST['notes']);
    
    $created_by = $_SESSION['user_id'];
    
    // Validate
    if (empty($employee_code) || empty($full_name)) {
        $error_message = "Vui lòng nhập mã nhân viên và họ tên!";
    } else {
        // Kiểm tra mã nhân viên trùng
        $check_stmt = $conn->prepare("SELECT id FROM hr_employees WHERE employee_code = ?");
        $check_stmt->bind_param("s", $employee_code);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $error_message = "Mã nhân viên đã tồn tại!";
        } else {
            $sql = "INSERT INTO hr_employees (
                employee_code, full_name, date_of_birth, gender, id_card, phone, email, address, current_address,
                department_id, position, job_title, employment_type, start_date, contract_end_date, salary,
                education_level, major, graduation_year, school,
                ethnic, religion, marital_status, emergency_contact, emergency_phone,
                status, notes, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssssssssssssssssssssssssssss",
                $employee_code, $full_name, $date_of_birth, $gender, $id_card, $phone, $email, $address, $current_address,
                $department_id, $position, $job_title, $employment_type, $start_date, $contract_end_date, $salary,
                $education_level, $major, $graduation_year, $school,
                $ethnic, $religion, $marital_status, $emergency_contact, $emergency_phone,
                $status, $notes, $created_by
            );
            
            if ($stmt->execute()) {
                $success_message = "Thêm nhân viên thành công!";
                header("Location: quan-ly-nhan-su.php");
                exit;
            } else {
                $error_message = "Lỗi thêm nhân viên: " . ($stmt->error ?: $conn->error);
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm nhân viên mới</title>
    <link rel="stylesheet" href="dashboard-style.css">
    <link rel="stylesheet" href="hr-style.css">
</head>
<body>
    <?php include 'header-menu.php'; ?>
    
    <div class="form-container">
        <div class="form-header">
            <h1>➕ Thêm nhân viên mới</h1>
            <p>Điền đầy đủ thông tin nhân viên vào form bên dưới</p>
        </div>

                <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <div class="alert alert-info" style="background:#cce5ff;color:#004085;padding:15px;border-radius:8px;margin-bottom:15px;">
                <strong>Debug POST:</strong> Form đã được submit<br>
                employee_code: <?php echo isset($_POST['employee_code']) ? htmlspecialchars($_POST['employee_code']) : 'KHÔNG CÓ'; ?><br>
                full_name: <?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : 'KHÔNG CÓ'; ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST">
            <!-- Thông tin cơ bản -->
            <div class="form-section">
                <h2>📋 Thông tin cơ bản</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="required">Mã nhân viên</label>
                        <input type="text" name="employee_code" required placeholder="VD: NV001">
                    </div>
                    <div class="form-group">
                        <label class="required">Họ và tên</label>
                        <input type="text" name="full_name" required placeholder="Nguyễn Văn A">
                    </div>
                    <div class="form-group">
                        <label>Ngày sinh</label>
                        <input type="date" name="date_of_birth">
                    </div>
                    <div class="form-group">
                        <label>Giới tính</label>
                        <select name="gender">
                            <option value="Nam">Nam</option>
                            <option value="Nữ">Nữ</option>
                            <option value="Khác">Khác</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>CMND/CCCD</label>
                        <input type="text" name="id_card" placeholder="079123456789">
                    </div>
                    <div class="form-group">
                        <label>Số điện thoại</label>
                        <input type="tel" name="phone" placeholder="0901234567">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" placeholder="email@example.com">
                    </div>
                    <div class="form-group">
                        <label>Dân tộc</label>
                        <input type="text" name="ethnic" placeholder="Kinh">
                    </div>
                    <div class="form-group">
                        <label>Tôn giáo</label>
                        <input type="text" name="religion" placeholder="Không">
                    </div>
                    <div class="form-group">
                        <label>Tình trạng hôn nhân</label>
                        <select name="marital_status">
                            <option value="Độc thân">Độc thân</option>
                            <option value="Đã kết hôn">Đã kết hôn</option>
                            <option value="Ly hôn">Ly hôn</option>
                            <option value="Góa">Góa</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Địa chỉ thường trú</label>
                    <textarea name="address" placeholder="Nhập địa chỉ thường trú"></textarea>
                </div>
                <div class="form-group">
                    <label>Địa chỉ hiện tại</label>
                    <textarea name="current_address" placeholder="Nhập địa chỉ hiện tại"></textarea>
                </div>
            </div>

            <!-- Thông tin công việc -->
            <div class="form-section">
                <h2>💼 Thông tin công việc</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Phòng ban</label>
                        <select name="department_id">
                            <option value="">-- Chọn phòng ban --</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Chức vụ</label>
                        <input type="text" name="position" placeholder="Trưởng phòng, Phó phòng...">
                    </div>
                    <div class="form-group">
                        <label>Vị trí công việc</label>
                        <input type="text" name="job_title" placeholder="Chuyên viên, Kỹ thuật viên...">
                    </div>
                    <div class="form-group">
                        <label>Loại hợp đồng</label>
                        <select name="employment_type">
                            <option value="Biên chế">Biên chế</option>
                            <option value="Hợp đồng">Hợp đồng</option>
                            <option value="Thời vụ">Thời vụ</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Ngày vào làm</label>
                        <input type="date" name="start_date">
                    </div>
                    <div class="form-group">
                        <label>Ngày hết hạn HĐ</label>
                        <input type="date" name="contract_end_date">
                    </div>
                    <div class="form-group">
                        <label>Mức lương (VNĐ)</label>
                        <input type="number" name="salary" placeholder="10000000">
                    </div>
                    <div class="form-group">
                        <label>Trạng thái</label>
                        <select name="status">
                            <option value="active">Đang làm việc</option>
                            <option value="inactive">Tạm nghỉ</option>
                            <option value="resigned">Đã nghỉ việc</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Trình độ học vấn -->
            <div class="form-section">
                <h2>🎓 Trình độ học vấn</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Trình độ</label>
                        <input type="text" name="education_level" placeholder="Đại học, Cao đẳng...">
                    </div>
                    <div class="form-group">
                        <label>Chuyên ngành</label>
                        <input type="text" name="major" placeholder="Công nghệ thông tin...">
                    </div>
                    <div class="form-group">
                        <label>Năm tốt nghiệp</label>
                        <input type="number" name="graduation_year" placeholder="2020">
                    </div>
                    <div class="form-group">
                        <label>Trường học</label>
                        <input type="text" name="school" placeholder="Đại học ABC...">
                    </div>
                </div>
            </div>

            <!-- Liên hệ khẩn cấp -->
            <div class="form-section">
                <h2>🚨 Liên hệ khẩn cấp</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Người liên hệ</label>
                        <input type="text" name="emergency_contact" placeholder="Họ tên người thân">
                    </div>
                    <div class="form-group">
                        <label>Số điện thoại</label>
                        <input type="tel" name="emergency_phone" placeholder="0901234567">
                    </div>
                </div>
            </div>

            <!-- Ghi chú -->
            <div class="form-section">
                <div class="form-group">
                    <label>Ghi chú</label>
                    <textarea name="notes" placeholder="Ghi chú thêm về nhân viên..."></textarea>
                </div>
            </div>

            <div class="form-actions">
                <a href="quan-ly-nhan-su.php" class="btn btn-secondary">❌ Hủy</a>
                <button type="submit" class="btn btn-primary">✅ Lưu thông tin</button>
            </div>
        </form>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
<?php $conn->close(); ?>
