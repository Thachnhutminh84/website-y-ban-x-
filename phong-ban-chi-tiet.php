<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

require_once 'auth.php';
require_once 'department-data-db.php';

$isLoggedIn = authIsLoggedIn();
$currentRole = authCurrentRole();
$displayName = $isLoggedIn ? authDisplayName() : '';
$canManageContent = authCanManageContent();
$userType = authCurrentUserType();
$isApproved = authIsApproved();

// Debug - xóa sau khi test
echo "<!-- DEBUG INFO -->";
echo "<!-- isLoggedIn: " . ($isLoggedIn ? 'YES' : 'NO') . " -->";
echo "<!-- currentRole: $currentRole -->";
echo "<!-- userType: $userType -->";
echo "<!-- isApproved: " . ($isApproved ? 'YES' : 'NO') . " -->";
echo "<!-- canManageContent: " . ($canManageContent ? 'YES' : 'NO') . " -->";
echo "<!-- Session data: " . json_encode($_SESSION) . " -->";
echo "<!-- END DEBUG -->";

$departmentKey = isset($_GET['dept']) ? (string) $_GET['dept'] : 'vh-xh';

// Lấy thông tin phòng ban từ database
$department = getDepartmentByCode($departmentKey);
if (!$department) {
    header('Location: phong-ban.php');
    exit();
}

$contactItems = $department['contact_items'];
$keyFunctions = $department['key_functions'];
$tasks = $department['tasks'];
$activities = $department['activities'];
$staffMembers = getDepartmentStaffByCode($departmentKey); // Load từ database
$highlightStats = $department['highlight_stats'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($department['name'], ENT_QUOTES, 'UTF-8'); ?> - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css?v=2.1">
    <link rel="stylesheet" href="phong-ban-style.css?v=2.1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="dropdown.js"></script>
</head>
<body>
    <?php include 'header-menu.php'; ?>

    <main class="dept-page">
        <section class="dept-hero">
            <div class="container">
                <div class="breadcrumb-nav">
                    <a href="index.php">Trang chủ</a> /
                    <a href="phong-ban.php">Phòng ban</a> /
                    <span><?php echo htmlspecialchars($department['short_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                </div>

                <div class="dept-hero__grid">
                    <div class="dept-hero__copy">
                        <span class="dept-kicker"><?php echo htmlspecialchars($department['badge'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <h2><?php echo htmlspecialchars($department['name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                        <p><?php echo htmlspecialchars($department['subtitle'], ENT_QUOTES, 'UTF-8'); ?></p>

                        <div class="dept-hero__meta">
                            <div class="dept-meta-card">
                                <span class="dept-meta-card__label">Người phụ trách</span>
                                <strong><?php echo htmlspecialchars($department['manager'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            </div>
                            <div class="dept-meta-card">
                                <span class="dept-meta-card__label">Trọng tâm công việc</span>
                                <strong><?php echo htmlspecialchars($department['focus'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            </div>
                        </div>
                    </div>

                    <div class="dept-hero__panel">
                        <div class="dept-glance">
                            <span class="dept-glance__label">Tổng quan nhanh</span>
                            <ul class="dept-contact-list">
                                <?php foreach ($contactItems as $item): ?>
                                    <li>
                                        <span><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <strong><?php echo htmlspecialchars($item['value'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="dept-stat-grid">
                    <?php foreach ($highlightStats as $stat): ?>
                        <article class="dept-stat-card">
                            <strong><?php echo htmlspecialchars((string) $stat['value'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            <span><?php echo htmlspecialchars($stat['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="dept-section">
            <div class="container">
                <div class="dept-layout">
                    <article class="dept-panel dept-panel--wide">
                        <div class="dept-section-heading">
                            <span class="dept-section-heading__eyebrow">Tổng quan</span>
                            <h3>Vai trò và phạm vi phụ trách</h3>
                        </div>
                        <p class="dept-lead">
                            <?php echo htmlspecialchars($department['name'], ENT_QUOTES, 'UTF-8'); ?> là đầu mối tham mưu cho UBND xã trong việc
                            quản lý các hoạt động liên quan đến đời sống văn hóa, giáo dục, y tế, lao động và an sinh.
                            Mục tiêu của phòng là giữ vững nền tảng xã hội, nâng cao chất lượng phục vụ người dân và phối hợp liên thông
                            giữa nhà trường, trạm y tế, các đoàn thể và cộng đồng dân cư.
                        </p>
                    </article>

                    <aside class="dept-panel dept-panel--accent">
                        <div class="dept-section-heading">
                            <span class="dept-section-heading__eyebrow">Liên hệ</span>
                            <h3>Thông tin tiếp nhận</h3>
                        </div>
                        <ul class="dept-contact-stack">
                            <?php foreach ($contactItems as $item): ?>
                                <li>
                                    <span><?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <strong><?php echo htmlspecialchars($item['value'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </aside>
                </div>
            </div>
        </section>

        <section class="dept-section dept-section--soft">
            <div class="container">
                <div class="dept-section-heading dept-section-heading--center">
                    <span class="dept-section-heading__eyebrow">Chức năng</span>
                    <h3>6 nhóm chức năng đang triển khai</h3>
                </div>

                <div class="dept-chip-grid">
                    <?php foreach ($keyFunctions as $function): ?>
                        <article class="dept-chip-card">
                            <span class="dept-chip-card__index"></span>
                            <p><?php echo htmlspecialchars($function, ENT_QUOTES, 'UTF-8'); ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="dept-section">
            <div class="container">
                <div class="dept-section-heading">
                    <span class="dept-section-heading__eyebrow">Nhiệm vụ trọng tâm</span>
                    <h3>Những đầu việc cần theo dõi thường xuyên</h3>
                </div>

                <div class="dept-task-list">
                    <?php foreach ($tasks as $index => $task): ?>
                        <article class="dept-task-card">;
                            <div class="dept-task-card__number"><?php echo $index + 1; ?></div>
                            <div class="dept-task-card__content">
                                <h4><?php echo htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                <p><?php echo htmlspecialchars($task['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="dept-section dept-section--soft">
            <div class="container">
                <div class="dept-section-heading">
                    <span class="dept-section-heading__eyebrow">Hoạt động nổi bật</span>
                    <h3>Một số mảng phối hợp nổi bật tại địa phương</h3>
                </div>

                <div class="dept-activity-grid">
                    <?php foreach ($activities as $activity): ?>
                        <article class="dept-activity-card">
                            <h4><?php echo htmlspecialchars($activity['title'], ENT_QUOTES, 'UTF-8'); ?></h4>
                            <p><?php echo htmlspecialchars($activity['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="dept-section">
            <div class="container">
                <div class="dept-section-heading">
                    <span class="dept-section-heading__eyebrow">Nhân sự</span>
                    <h3>Danh sách cán bộ, chuyên viên</h3>
                    <?php if ($canManageContent): ?>
                        <button class="btn btn-primary" onclick="openAddStaffModal()">
                            <i class="fas fa-plus"></i> Thêm thành viên
                        </button>
                    <?php endif; ?>
                </div>

                <div class="dept-staff-grid" id="staffGrid">
                    <?php foreach ($staffMembers as $index => $member): ?>
                        <article class="dept-staff-card" data-id="<?php echo $member['id'] ?? $index; ?>">
                            <div class="dept-staff-card__avatar">
                                <?php echo htmlspecialchars(mb_substr($member['name'], 0, 1, 'UTF-8'), ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                            <div class="dept-staff-card__body">
                                <h4><?php echo htmlspecialchars($member['name'], ENT_QUOTES, 'UTF-8'); ?></h4>
                                <p><?php echo htmlspecialchars($member['position'] ?? $member['role'], ENT_QUOTES, 'UTF-8'); ?></p>
                                <span><?php echo htmlspecialchars($member['phone'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <?php if ($canManageContent): ?>
                                <div class="dept-staff-card__actions">
                                    <button class="btn btn-sm btn-secondary" onclick="editStaff(<?php echo $member['id'] ?? $index; ?>)">
                                        <i class="fas fa-edit"></i> Sửa
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteStaff(<?php echo $member['id'] ?? $index; ?>)">
                                        <i class="fas fa-trash"></i> Xóa
                                    </button>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>

    <!-- Modal thêm/sửa thành viên -->
    <div id="staffModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Thêm thành viên</h3>
                <button class="close" onclick="closeStaffModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="modalAlert"></div>
                <form id="staffForm">
                    <input type="hidden" id="staffIndex" value="">
                    <div class="form-group">
                        <label class="form-label" for="staffName">Tên công chức *</label>
                        <input type="text" id="staffName" class="form-control" required placeholder="Nhập tên đầy đủ">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="staffRole">Chức vụ *</label>
                        <input type="text" id="staffRole" class="form-control" required placeholder="Ví dụ: Chuyên viên, Trưởng phòng...">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="staffPhone">Số điện thoại *</label>
                        <input type="tel" id="staffPhone" class="form-control" required placeholder="Ví dụ: 0123.456.789">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeStaffModal()">Hủy</button>
                <button type="submit" form="staffForm" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save"></i> Lưu
                </button>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    <?php include 'quick-actions-toolbar.php'; ?>

    <script>
        const departmentKey = '<?php echo $departmentKey; ?>';
        let currentStaffData = <?php echo json_encode(array_values($staffMembers)); ?>;

        // Mở modal thêm thành viên
        function openAddStaffModal() {
            document.getElementById('modalTitle').textContent = 'Thêm thành viên mới';
            document.getElementById('staffIndex').value = '';
            document.getElementById('staffForm').reset();
            document.getElementById('submitBtn').innerHTML = '<i class="fas fa-plus"></i> Thêm';
            document.getElementById('modalAlert').innerHTML = '';
            document.getElementById('staffModal').style.display = 'block';
        }

        // Mở modal sửa thành viên
        function editStaff(id) {
            if (id < 0) {
                showAlert('danger', 'Thành viên này chưa được lưu vào hệ thống. Vui lòng xóa và thêm lại.');
                return;
            }
            const member = currentStaffData.find(m => m.id == id);
            if (!member) {
                showAlert('danger', 'Không tìm thấy thành viên');
                return;
            }

            document.getElementById('modalTitle').textContent = 'Sửa thông tin thành viên';
            document.getElementById('staffIndex').value = id;
            document.getElementById('staffName').value = member.name;
            document.getElementById('staffRole').value = member.position || member.role;
            document.getElementById('staffPhone').value = member.phone;
            document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Cập nhật';
            document.getElementById('modalAlert').innerHTML = '';
            document.getElementById('staffModal').style.display = 'block';
        }

        // Đóng modal
        function closeStaffModal() {
            document.getElementById('staffModal').style.display = 'none';
        }

        // Xóa thành viên
        function deleteStaff(id) {
            if (id < 0) {
                showAlert('danger', 'Thành viên này chưa được lưu vào hệ thống. Không thể xóa.');
                return;
            }
            const member = currentStaffData.find(m => m.id == id);
            if (!member) {
                showAlert('danger', 'Không tìm thấy thành viên');
                return;
            }

            if (!confirm(`Bạn có chắc chắn muốn xóa thành viên "${member.name}"?`)) {
                return;
            }

            fetch('api-department-staff-db.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'delete',
                    department: departmentKey,
                    index: id
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showAlert('danger', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('danger', 'Có lỗi xảy ra khi xóa thành viên');
            });
        }

        // Xử lý form submit
        document.getElementById('staffForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const index = document.getElementById('staffIndex').value;
            const name = document.getElementById('staffName').value.trim();
            const position = document.getElementById('staffRole').value.trim();
            const phone = document.getElementById('staffPhone').value.trim();

            if (!name || !position || !phone) {
                showModalAlert('danger', 'Vui lòng điền đầy đủ thông tin');
                return;
            }

            const action = index === '' ? 'add' : 'edit';
            const formData = new URLSearchParams({
                action: action,
                department: departmentKey,
                name: name,
                position: position,
                phone: phone,
                email: ''
            });

            if (action === 'edit') {
                formData.append('index', index);
            }

            fetch('api-department-staff-db.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showModalAlert('success', data.message);
                    setTimeout(() => {
                        closeStaffModal();
                        location.reload();
                    }, 1000);
                } else {
                    showModalAlert('danger', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showModalAlert('danger', 'Có lỗi xảy ra khi lưu thông tin');
            });
        });

        // Hiển thị thông báo trong modal
        function showModalAlert(type, message) {
            const alertDiv = document.getElementById('modalAlert');
            alertDiv.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
        }

        // Hiển thị thông báo chung
        function showAlert(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.textContent = message;
            alertDiv.style.position = 'fixed';
            alertDiv.style.top = '20px';
            alertDiv.style.right = '20px';
            alertDiv.style.zIndex = '9999';
            alertDiv.style.minWidth = '300px';
            
            document.body.appendChild(alertDiv);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 3000);
        }

        // Đóng modal khi click bên ngoài
        window.onclick = function(event) {
            const modal = document.getElementById('staffModal');
            if (event.target === modal) {
                closeStaffModal();
            }
        }
    </script>
</body>
</html>
