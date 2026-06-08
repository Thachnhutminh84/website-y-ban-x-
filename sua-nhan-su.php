<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

authRequireCanBo('index.php');

$conn = getDBConnection();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: quan-ly-nhan-su.php');
    exit;
}

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

// Lấy thông tin nhân viên
$stmt = $conn->prepare("SELECT * FROM hr_employees WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: quan-ly-nhan-su.php');
    exit;
}

$employee = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    
    $updated_by = $_SESSION['user_id'];
    
    if (empty($full_name)) {
        $error_message = "Vui lòng nhập họ tên!";
    } else {
        $sql = "UPDATE hr_employees SET 
            full_name=?, date_of_birth=?, gender=?, id_card=?, phone=?, email=?, address=?, current_address=?,
            department_id=?, position=?, job_title=?, employment_type=?, start_date=?, contract_end_date=?, salary=?,
            education_level=?, major=?, graduation_year=?, school=?,
            ethnic=?, religion=?, marital_status=?, emergency_contact=?, emergency_phone=?,
            status=?, notes=?, updated_by=?
            WHERE id=?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "ssssssssisssssdssisssssssii",
            $full_name, $date_of_birth, $gender, $id_card, $phone, $email, $address, $current_address,
            $department_id, $position, $job_title, $employment_type, $start_date, $contract_end_date, $salary,
            $education_level, $major, $graduation_year, $school,
            $ethnic, $religion, $marital_status, $emergency_contact, $emergency_phone,
            $status, $notes, $updated_by, $id
        );
        
        if ($stmt->execute()) {
            $success_message = "Cập nhật thông tin thành công!";
            // Reload dữ liệu
            $stmt2 = $conn->prepare("SELECT * FROM hr_employees WHERE id = ?");
            $stmt2->bind_param("i", $id);
            $stmt2->execute();
            $employee = $stmt2->get_result()->fetch_assoc();
            $stmt2->close();
        } else {
            $error_message = "Lỗi cập nhật thông tin.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa thông tin nhân viên</title>
    <link rel="stylesheet" href="dashboard-style.css">
    <link rel="stylesheet" href="hr-style.css">
</head>
<body>
    <?php include 'header-menu.php'; ?>
    
    <div class="form-container">
        <div class="form-header">
            <h1>✏️ Sửa thông tin nhân viên</h1>
            <p>Mã nhân viên: <strong><?php echo htmlspecialchars($employee['employee_code']); ?></strong></p>
        </div>

        <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <!-- Thông tin cơ bản -->
            <div class="form-section">
                <h2>📋 Thông tin cơ bản</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="required">Họ và tên</label>
                        <input type="text" name="full_name" required value="<?php echo htmlspecialchars($employee['full_name']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Ngày sinh</label>
                        <input type="date" name="date_of_birth" value="<?php echo $employee['date_of_birth']; ?>">
                    </div>
                    <div class="form-group">
                        <label>Giới tính</label>
                        <select name="gender">
                            <option value="Nam" <?php echo $employee['gender'] == 'Nam' ? 'selected' : ''; ?>>Nam</option>
                            <option value="Nữ" <?php echo $employee['gender'] == 'Nữ' ? 'selected' : ''; ?>>Nữ</option>
                            <option value="Khác" <?php echo $employee['gender'] == 'Khác' ? 'selected' : ''; ?>>Khác</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>CMND/CCCD</label>
                        <input type="text" name="id_card" value="<?php echo htmlspecialchars($employee['id_card']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Số điện thoại</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($employee['phone']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($employee['email']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Dân tộc</label>
                        <input type="text" name="ethnic" value="<?php echo htmlspecialchars($employee['ethnic']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Tôn giáo</label>
                        <input type="text" name="religion" value="<?php echo htmlspecialchars($employee['religion']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Tình trạng hôn nhân</label>
                        <select name="marital_status">
                            <option value="Độc thân" <?php echo $employee['marital_status'] == 'Độc thân' ? 'selected' : ''; ?>>Độc thân</option>
                            <option value="Đã kết hôn" <?php echo $employee['marital_status'] == 'Đã kết hôn' ? 'selected' : ''; ?>>Đã kết hôn</option>
                            <option value="Ly hôn" <?php echo $employee['marital_status'] == 'Ly hôn' ? 'selected' : ''; ?>>Ly hôn</option>
                            <option value="Góa" <?php echo $employee['marital_status'] == 'Góa' ? 'selected' : ''; ?>>Góa</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Địa chỉ thường trú</label>
                    <textarea name="address"><?php echo htmlspecialchars($employee['address']); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Địa chỉ hiện tại</label>
                    <textarea name="current_address"><?php echo htmlspecialchars($employee['current_address']); ?></textarea>
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
                                <option value="<?php echo $dept['id']; ?>" <?php echo $employee['department_id'] == $dept['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Chức vụ</label>
                        <input type="text" name="position" value="<?php echo htmlspecialchars($employee['position']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Vị trí công việc</label>
                        <input type="text" name="job_title" value="<?php echo htmlspecialchars($employee['job_title']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Loại hợp đồng</label>
                        <select name="employment_type">
                            <option value="Biên chế" <?php echo $employee['employment_type'] == 'Biên chế' ? 'selected' : ''; ?>>Biên chế</option>
                            <option value="Hợp đồng" <?php echo $employee['employment_type'] == 'Hợp đồng' ? 'selected' : ''; ?>>Hợp đồng</option>
                            <option value="Thời vụ" <?php echo $employee['employment_type'] == 'Thời vụ' ? 'selected' : ''; ?>>Thời vụ</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Ngày vào làm</label>
                        <input type="date" name="start_date" value="<?php echo $employee['start_date']; ?>">
                    </div>
                    <div class="form-group">
                        <label>Ngày hết hạn HĐ</label>
                        <input type="date" name="contract_end_date" value="<?php echo $employee['contract_end_date']; ?>">
                    </div>
                    <div class="form-group">
                        <label>Mức lương (VNĐ)</label>
                        <input type="number" name="salary" value="<?php echo $employee['salary']; ?>">
                    </div>
                    <div class="form-group">
                        <label>Trạng thái</label>
                        <select name="status">
                            <option value="active" <?php echo $employee['status'] == 'active' ? 'selected' : ''; ?>>Đang làm việc</option>
                            <option value="inactive" <?php echo $employee['status'] == 'inactive' ? 'selected' : ''; ?>>Tạm nghỉ</option>
                            <option value="resigned" <?php echo $employee['status'] == 'resigned' ? 'selected' : ''; ?>>Đã nghỉ việc</option>
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
                        <input type="text" name="education_level" value="<?php echo htmlspecialchars($employee['education_level']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Chuyên ngành</label>
                        <input type="text" name="major" value="<?php echo htmlspecialchars($employee['major']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Năm tốt nghiệp</label>
                        <input type="number" name="graduation_year" value="<?php echo $employee['graduation_year']; ?>">
                    </div>
                    <div class="form-group">
                        <label>Trường học</label>
                        <input type="text" name="school" value="<?php echo htmlspecialchars($employee['school']); ?>">
                    </div>
                </div>
            </div>

            <!-- Liên hệ khẩn cấp -->
            <div class="form-section">
                <h2>🚨 Liên hệ khẩn cấp</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Người liên hệ</label>
                        <input type="text" name="emergency_contact" value="<?php echo htmlspecialchars($employee['emergency_contact']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Số điện thoại</label>
                        <input type="tel" name="emergency_phone" value="<?php echo htmlspecialchars($employee['emergency_phone']); ?>">
                    </div>
                </div>
            </div>

            <!-- Ghi chú -->
            <div class="form-section">
                <div class="form-group">
                    <label>Ghi chú</label>
                    <textarea name="notes"><?php echo htmlspecialchars($employee['notes']); ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <a href="chi-tiet-nhan-su.php?id=<?php echo $id; ?>" class="btn btn-secondary">❌ Hủy</a>
                <button type="submit" class="btn btn-primary">✅ Lưu thay đổi</button>
            </div>
        </form>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
<?php $conn->close(); ?>
