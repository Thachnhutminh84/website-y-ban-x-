<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';

authRequireRole(['admin']);

$conn = getDBConnection();

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['error'] = 'ID không hợp lệ';
    header('Location: quan-ly-danh-ba.php');
    exit();
}

// Lấy thông tin thành viên
$stmt = $conn->prepare("SELECT * FROM department_staff WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = 'Không tìm thấy thành viên';
    header('Location: quan-ly-danh-ba.php');
    exit();
}

$member = $result->fetch_assoc();
$stmt->close();

// Lấy danh sách phòng ban
$departments = [];
$result = $conn->query("SELECT * FROM departments WHERE status = 'active' ORDER BY display_order ASC");
while ($row = $result->fetch_assoc()) {
    $departments[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa thành viên - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css?v=2.1">
    <style>
        .form-container {
            max-width: 800px;
            margin: 40px auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #ffc107;
        }
        
        .form-header h1 {
            color: #333;
            margin: 0 0 10px 0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group label .required {
            color: #dc3545;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #ffc107;
        }
        
        .form-group small {
            display: block;
            margin-top: 5px;
            color: #666;
            font-size: 13px;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 15px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #000;
        }
        
        .btn-warning:hover {
            background: #e0a800;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <?php include 'header-menu.php'; ?>

    <div class="form-container">
        <div class="form-header">
            <h1>✏️ Sửa thông tin thành viên</h1>
            <p>Cập nhật thông tin cho: <strong><?php echo htmlspecialchars($member['name']); ?></strong></p>
        </div>

        <form action="xu-ly-sua-thanh-vien.php" method="POST">
            <?php echo SecurityHelper::csrfField(); ?>
            <input type="hidden" name="id" value="<?php echo $member['id']; ?>">
            
            <div class="form-group">
                <label>Phòng ban <span class="required">*</span></label>
                <select name="department_id" required>
                    <option value="">-- Chọn phòng ban --</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept['id']; ?>"
                                <?php echo $member['department_id'] == $dept['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dept['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Họ và tên <span class="required">*</span></label>
                <input type="text" name="name" required maxlength="100" 
                       value="<?php echo htmlspecialchars($member['name']); ?>">
            </div>

            <div class="form-group">
                <label>Chức vụ <span class="required">*</span></label>
                <input type="text" name="position" required maxlength="100" 
                       value="<?php echo htmlspecialchars($member['position']); ?>">
            </div>

            <div class="form-group">
                <label>Số điện thoại <span class="required">*</span></label>
                <input type="text" name="phone" required maxlength="20" 
                       value="<?php echo htmlspecialchars($member['phone']); ?>">
                <small>Định dạng: 0xxx.xxx.xxx hoặc 0xxxxxxxxx</small>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" maxlength="100" 
                       value="<?php echo htmlspecialchars($member['email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Lương cơ bản (VNĐ)</label>
                <input type="number" name="basic_salary" step="10000" min="0" 
                       value="<?php echo $member['basic_salary'] ?? 2530000; ?>"
                       placeholder="Ví dụ: 2530000">
                <small>Mức lương cơ bản hàng tháng</small>
            </div>

            <div class="form-group">
                <label>Hệ số lương</label>
                <input type="number" name="salary_coefficient" step="0.01" min="0" max="10" 
                       value="<?php echo $member['salary_coefficient'] ?? 1.0; ?>"
                       placeholder="Ví dụ: 2.34">
                <small>Hệ số lương theo quy định (từ 0 đến 10)</small>
            </div>

            <div class="form-group">
                <label>Thứ tự hiển thị</label>
                <input type="number" name="display_order" value="<?php echo $member['display_order']; ?>" min="0">
                <small>Số thứ tự để sắp xếp</small>
            </div>

            <div class="form-group">
                <label>Trạng thái</label>
                <select name="status">
                    <option value="active" <?php echo $member['status'] == 'active' ? 'selected' : ''; ?>>
                        Hoạt động
                    </option>
                    <option value="inactive" <?php echo $member['status'] == 'inactive' ? 'selected' : ''; ?>>
                        Tạm ẩn
                    </option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-warning">
                    💾 Cập nhật
                </button>
                <a href="quan-ly-danh-ba.php" class="btn btn-secondary">
                    ❌ Hủy bỏ
                </a>
            </div>
        </form>
    </div>

    <?php include 'footer.php'; ?>
    <script>
    function toggleSalaryEdit() {
        var input = document.getElementById('salaryInput');
        if (input.readOnly) {
            input.readOnly = false;
            input.style.backgroundColor = '#fff';
        } else {
            input.readOnly = true;
            input.style.backgroundColor = '#f5f5f5';
        }
    }
    </script>
</body>
</html>