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

// Lấy thông tin nhân viên
$sql = "SELECT e.*, d.name as department_name 
        FROM hr_employees e 
        LEFT JOIN departments d ON e.department_id = d.id 
        WHERE e.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: quan-ly-nhan-su.php');
    exit;
}

$employee = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết nhân viên - <?php echo htmlspecialchars($employee['full_name']); ?></title>
    <link rel="stylesheet" href="dashboard-style.css">
    <link rel="stylesheet" href="hr-style.css">
</head>
<body>
    <?php include 'header-menu.php'; ?>
    
    <div class="detail-container">
        <!-- Header với ảnh và thông tin cơ bản -->
        <div class="detail-header">
            <div>
                <?php if ($employee['avatar']): ?>
                    <img src="<?php echo htmlspecialchars($employee['avatar']); ?>" class="employee-avatar-large" alt="Avatar">
                <?php else: ?>
                    <div class="employee-avatar-large employee-avatar-placeholder" style="width: 160px; height: 160px; font-size: 48px;">
                        <?php echo mb_substr($employee['full_name'], 0, 1, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="employee-info">
                <h1><?php echo htmlspecialchars($employee['full_name']); ?></h1>
                <span class="employee-code"><?php echo htmlspecialchars($employee['employee_code']); ?></span>
                
                <?php
                $status_class = 'status-' . $employee['status'];
                $status_text = [
                    'active' => 'Đang làm việc',
                    'inactive' => 'Tạm nghỉ',
                    'resigned' => 'Đã nghỉ việc'
                ];
                ?>
                <span class="status-badge <?php echo $status_class; ?>">
                    <?php echo $status_text[$employee['status']]; ?>
                </span>
                
                <div style="margin-top: 15px;">
                    <p style="margin: 5px 0;"><strong>Chức vụ:</strong> <?php echo htmlspecialchars($employee['position'] ?? 'Chưa có'); ?></p>
                    <p style="margin: 5px 0;"><strong>Phòng ban:</strong> <?php echo htmlspecialchars($employee['department_name'] ?? 'Chưa phân'); ?></p>
                </div>
                
                <div class="action-buttons">
                    <a href="sua-nhan-su.php?id=<?php echo $employee['id']; ?>" class="btn btn-success">✏️ Chỉnh sửa</a>
                    <a href="quan-ly-nhan-su.php" class="btn btn-secondary">← Quay lại</a>
                </div>
            </div>
        </div>

        <!-- Thông tin cá nhân -->
        <div class="detail-section">
            <h2>📋 Thông tin cá nhân</h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Ngày sinh</div>
                    <div class="info-value"><?php echo $employee['date_of_birth'] ? date('d/m/Y', strtotime($employee['date_of_birth'])) : 'Chưa có'; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Giới tính</div>
                    <div class="info-value"><?php echo htmlspecialchars($employee['gender']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">CMND/CCCD</div>
                    <div class="info-value"><?php echo htmlspecialchars($employee['id_card'] ?? 'Chưa có'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Số điện thoại</div>
                    <div class="info-value"><?php echo htmlspecialchars($employee['phone'] ?? 'Chưa có'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo htmlspecialchars($employee['email'] ?? 'Chưa có'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Dân tộc</div>
                    <div class="info-value"><?php echo htmlspecialchars($employee['ethnic'] ?? 'Chưa có'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tôn giáo</div>
                    <div class="info-value"><?php echo htmlspecialchars($employee['religion'] ?? 'Chưa có'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tình trạng hôn nhân</div>
                    <div class="info-value"><?php echo htmlspecialchars($employee['marital_status'] ?? 'Chưa có'); ?></div>
                </div>
            </div>
            
            <div class="info-grid" style="margin-top: 20px;">
                <div class="info-item">
                    <div class="info-label">Địa chỉ thường trú</div>
                    <div class="info-value"><?php echo htmlspecialchars($employee['address'] ?? 'Chưa có'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Địa chỉ hiện tại</div>
                    <div class="info-value"><?php echo htmlspecialchars($employee['current_address'] ?? 'Chưa có'); ?></div>
                </div>
            </div>
        </div>

        <!-- Thông tin công việc -->
        <div class="detail-section">
            <h2>💼 Thông tin công việc</h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Phòng ban</div>
                    <div class="info-value"><?php echo htmlspecialchars($employee['department_name'] ?? 'Chưa phân'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Chức vụ</div>
                    <div class="info-value"><?php echo htmlspecialchars($employee['position'] ?? 'Chưa có'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Vị trí công việc</div>
                    <div class="info-value"><?php echo htmlspecialchars($employee['job_title'] ?? 'Chưa có'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Loại hợp đồng</div>
                    <div class="info-value"><?php echo htmlspecialchars($employee['employment_type']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Ngày vào làm</div>
                    <div class="info-value"><?php echo $employee['start_date'] ? date('d/m/Y', strtotime($employee['start_date'])) : 'Chưa có'; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Ngày hết hạn HĐ</div>
                    <div class="info-value"><?php echo $employee['contract_end_date'] ? date('d/m/Y', strtotime($employee['contract_end_date'])) : 'Không có'; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Mức lương</div>
                    <div class="info-value"><?php echo $employee['salary'] ? number_format($employee['salary']) . ' VNĐ' : 'Chưa có'; ?></div>
                </div>
            </div>
        </div>

        <!-- Trình độ học vấn -->
        <div class="detail-section">
            <h2>🎓 Trình độ học vấn</h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Trình độ</div>
                    <div class="info-value"><?php echo htmlspecialchars($employee['education_level'] ?? 'Chưa có'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Chuyên ngành</div>
                    <div class="info-value"><?php echo htmlspecialchars($employee['major'] ?? 'Chưa có'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Năm tốt nghiệp</div>
                    <div class="info-value"><?php echo $employee['graduation_year'] ?? 'Chưa có'; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Trường học</div>
                    <div class="info-value"><?php echo htmlspecialchars($employee['school'] ?? 'Chưa có'); ?></div>
                </div>
            </div>
        </div>

        <!-- Liên hệ khẩn cấp -->
        <div class="detail-section">
            <h2>🚨 Liên hệ khẩn cấp</h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Người liên hệ</div>
                    <div class="info-value"><?php echo htmlspecialchars($employee['emergency_contact'] ?? 'Chưa có'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Số điện thoại</div>
                    <div class="info-value"><?php echo htmlspecialchars($employee['emergency_phone'] ?? 'Chưa có'); ?></div>
                </div>
            </div>
        </div>

        <!-- Ghi chú -->
        <?php if ($employee['notes']): ?>
        <div class="detail-section">
            <h2>📝 Ghi chú</h2>
            <p><?php echo nl2br(htmlspecialchars($employee['notes'])); ?></p>
        </div>
        <?php endif; ?>

        <!-- Thông tin hệ thống -->
        <div class="detail-section">
            <h2>ℹ️ Thông tin hệ thống</h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Ngày tạo</div>
                    <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($employee['created_at'])); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Cập nhật lần cuối</div>
                    <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($employee['updated_at'])); ?></div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
<?php
$conn->close();
?>
