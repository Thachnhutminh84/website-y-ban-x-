<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'auth.php';
require_once 'config.php';

// Chỉ admin mới được thêm phòng ban
authRequireRole(['admin']);

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $departmentCode = trim($_POST['department_code'] ?? '');
    $departmentName = trim($_POST['department_name'] ?? '');
    $shortName = trim($_POST['short_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $manager = trim($_POST['manager'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    if (empty($departmentCode) || empty($departmentName)) {
        $message = 'Mã phòng ban và tên phòng ban là bắt buộc!';
        $messageType = 'error';
    } else {
        $conn = getDBConnection();
        
        if ($conn) {
            // Kiểm tra mã phòng ban đã tồn tại chưa
            $checkStmt = $conn->prepare("SELECT id FROM departments WHERE code = ?");
            $checkStmt->bind_param('s', $departmentCode);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                $message = 'Mã phòng ban đã tồn tại!';
                $messageType = 'error';
            } else {
                // Thêm phòng ban mới
                $stmt = $conn->prepare("
                    INSERT INTO departments (code, name, short_name, description, manager, phone, email, address, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                
                $stmt->bind_param('sssssssss', 
                    $departmentCode, 
                    $departmentName, 
                    $shortName, 
                    $description, 
                    $manager, 
                    $phone, 
                    $email, 
                    $address, 
                    $status
                );
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = 'Thêm phòng ban thành công!';
                    header('Location: quan-ly-phong-ban.php');
                    exit();
                } else {
                    $message = 'Lỗi khi thêm phòng ban.';
                    $messageType = 'error';
                }
                
                $stmt->close();
            }
            
            $checkStmt->close();
            $conn->close();
        } else {
            $message = 'Không thể kết nối database!';
            $messageType = 'error';
        }
    }
}

$currentRole = authCurrentRole();
$displayName = authDisplayName();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm phòng ban - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard-style.css">
</head>
<body>
    <?php include 'header-menu.php'; ?>

    <main>
        <div class="admin-container">
            <div class="admin-header">
                <h1>➕ Thêm phòng ban mới</h1>
                <div class="admin-actions">
                    <a href="quan-ly-phong-ban.php" class="btn-secondary">← Quay lại</a>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <div class="form-container">
                <form method="POST" class="admin-form">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="department_code">Mã phòng ban *</label>
                            <input type="text" id="department_code" name="department_code" required 
                                   placeholder="VD: PB-HC" value="<?php echo htmlspecialchars($_POST['department_code'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <small>Mã duy nhất để phân biệt phòng ban</small>
                        </div>

                        <div class="form-group">
                            <label for="short_name">Tên viết tắt</label>
                            <input type="text" id="short_name" name="short_name" 
                                   placeholder="VD: P.HC" value="<?php echo htmlspecialchars($_POST['short_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="form-group full-width">
                            <label for="department_name">Tên phòng ban *</label>
                            <input type="text" id="department_name" name="department_name" required 
                                   placeholder="VD: Phòng Hành chính - Công vụ" value="<?php echo htmlspecialchars($_POST['department_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="form-group full-width">
                            <label for="description">Mô tả</label>
                            <textarea id="description" name="description" rows="3" 
                                      placeholder="Mô tả ngắn về chức năng, nhiệm vụ của phòng ban"><?php echo htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="manager">Người phụ trách</label>
                            <input type="text" id="manager" name="manager" 
                                   placeholder="Họ tên trưởng phòng" value="<?php echo htmlspecialchars($_POST['manager'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="form-group">
                            <label for="phone">Số điện thoại</label>
                            <input type="tel" id="phone" name="phone" 
                                   placeholder="0123.456.789" value="<?php echo htmlspecialchars($_POST['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" 
                                   placeholder="phongban@longhiep.gov.vn" value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="form-group">
                            <label for="status">Trạng thái</label>
                            <select id="status" name="status">
                                <option value="active" <?php echo ($_POST['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Hoạt động</option>
                                <option value="inactive" <?php echo ($_POST['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Tạm ngưng</option>
                            </select>
                        </div>

                        <div class="form-group full-width">
                            <label for="address">Địa chỉ</label>
                            <input type="text" id="address" name="address" 
                                   placeholder="Vị trí văn phòng" value="<?php echo htmlspecialchars($_POST['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i> Thêm phòng ban
                        </button>
                        <a href="quan-ly-phong-ban.php" class="btn-secondary">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <style>
    .form-container {
        background: white;
        border-radius: 10px;
        padding: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-group label {
        font-weight: 600;
        margin-bottom: 8px;
        color: #2c3e50;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        padding: 10px;
        border: 2px solid #e9ecef;
        border-radius: 5px;
        font-size: 14px;
        font-family: inherit;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: var(--primary);
    }

    .form-group small {
        margin-top: 5px;
        color: #6c757d;
        font-size: 12px;
    }

    .form-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-start;
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
        
        .form-group.full-width {
            grid-column: 1;
        }
    }
    </style>

    <?php include 'footer.php'; ?>
    <?php include 'quick-actions-toolbar.php'; ?>
</body>
</html>
