<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'auth.php';

$registerError = $_SESSION['register_error'] ?? null;
$registerSuccess = $_SESSION['register_success'] ?? null;
$registerOld = $_SESSION['register_old'] ?? [
    'full_name' => '',
    'username' => '',
    'email' => '',
    'role' => 'editor',
    'user_type' => ''
];

unset($_SESSION['register_error'], $_SESSION['register_success'], $_SESSION['register_old']);

$isLoggedIn = authIsLoggedIn();
$isAdmin = authIsAdmin();
$currentRole = authCurrentRole();
$displayName = $isLoggedIn ? authDisplayName() : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css?v=2.1">
    <script src="dropdown.js"></script>
    <style>
        /* Container chính */
        .login-section {
            background: var(--gradient-header);
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .login-box {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        /* Header của form */
        .form-header {
            background: var(--gradient-primary);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .form-header h2 {
            margin: 0 0 10px 0;
            font-size: 32px;
            font-weight: 700;
        }
        
        .form-header p {
            margin: 0;
            opacity: 0.95;
            font-size: 16px;
        }
        
        /* Bảng đăng ký */
        .registration-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .registration-table tr {
            transition: all 0.3s;
        }
        
        .registration-table tr:hover:not(.section-header):not(.btn-submit-row) {
            background: #f8f9ff;
        }
        
        .registration-table td {
            padding: 18px 25px;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
        }
        
        .registration-table tr:last-child td {
            border-bottom: none;
        }
        
        .registration-table td:first-child {
            width: 220px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            font-weight: 600;
            color: #2d3748;
            border-right: 3px solid var(--primary);
            position: relative;
        }
        
        .registration-table td:first-child::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(180deg, var(--primary) 0%, var(--primary-dark) 100%);
        }
        
        .registration-table td:first-child .required {
            color: #e53e3e;
            margin-left: 3px;
            font-size: 18px;
        }
        
        /* Input fields */
        .registration-table input[type="text"],
        .registration-table input[type="email"],
        .registration-table input[type="password"],
        .registration-table input[type="tel"],
        .registration-table input[type="date"],
        .registration-table select {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
            background: white;
        }
        
        .registration-table input:focus,
        .registration-table select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            transform: translateY(-1px);
        }
        
        .registration-table input:hover,
        .registration-table select:hover {
            border-color: #a0aec0;
        }
        
        .registration-table select {
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23667eea' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            padding-right: 40px;
        }
        
        .registration-table input[readonly] {
            background: #f7fafc;
            cursor: not-allowed;
            color: #718096;
        }
        
        /* Section headers */
        .section-header {
            background: var(--gradient-primary);
            border: none !important;
        }
        
        .section-header td {
            background: transparent !important;
            border: none !important;
            color: white;
            font-weight: 700;
            font-size: 17px;
            padding: 15px 25px !important;
            text-align: left;
            letter-spacing: 0.5px;
        }
        
        /* Form notes */
        .form-note {
            font-size: 13px;
            color: #718096;
            font-style: italic;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .form-note::before {
            content: 'ℹ️';
            font-style: normal;
        }
        
        /* Submit button row */
        .btn-submit-row td {
            padding: 30px 25px !important;
            text-align: center;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: none !important;
        }
        
        .btn-login {
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 16px 50px;
            font-size: 18px;
            font-weight: 700;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            max-width: 100%;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(102, 126, 234, 0.6);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        /* Error & Success messages */
        .error-message {
            background: linear-gradient(135deg, #fed7d7 0%, #feb2b2 100%);
            color: #742a2a;
            padding: 15px 20px;
            border-radius: 10px;
            margin: 20px 25px;
            border-left: 4px solid #e53e3e;
            font-weight: 600;
        }
        
        .success-message {
            background: linear-gradient(135deg, #c6f6d5 0%, #9ae6b4 100%);
            color: #22543d;
            padding: 15px 20px;
            border-radius: 10px;
            margin: 20px 25px;
            border-left: 4px solid #38a169;
            font-weight: 600;
        }
        
        /* Forgot password link */
        .forgot-password {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .forgot-password:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .login-box {
                border-radius: 15px;
                margin: 0 10px;
            }
            
            .registration-table td {
                display: block;
                width: 100% !important;
                border: none !important;
            }
            
            .registration-table td:first-child {
                background: var(--gradient-primary);
                color: white;
                padding: 12px 20px;
                border-right: none;
            }
            
            .registration-table td:first-child::before {
                display: none;
            }
            
            .registration-table tr:not(.section-header):not(.btn-submit-row) {
                display: block;
                margin-bottom: 2px;
                border-radius: 0;
            }
            
            .section-header td {
                text-align: center;
            }
            
            .form-header h2 {
                font-size: 24px;
            }
        }
        
        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-box {
            animation: fadeInUp 0.6s ease-out;
        }
    </style>
</head>
<body>
    <?php include 'header-menu.php'; ?>

    <main>
        <section class="login-section">
            <div class="container">
                <div class="login-box">
                    <div class="form-header">
                        <h2>📝 Đăng ký tài khoản</h2>
                        <p>
                            <?php if ($isAdmin): ?>
                                Tạo tài khoản mới cho biên tập viên hoặc quản trị viên
                            <?php else: ?>
                                Tạo tài khoản để truy cập hệ thống UBND Xã Long Hiệp
                            <?php endif; ?>
                        </p>
                    </div>

                    <?php if ($registerError): ?>
                        <div class="error-message">❌ <?php echo htmlspecialchars($registerError, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>

                    <?php if ($registerSuccess): ?>
                        <div class="success-message">✅ <?php echo htmlspecialchars($registerSuccess, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>

                    <form action="process-register.php" method="POST" class="login-form">
                        <?php echo SecurityHelper::csrfField(); ?>
                        <table class="registration-table">
                            <!-- Thông tin đăng nhập -->
                            <tr class="section-header">
                                <td colspan="2">🔐 THÔNG TIN ĐĂNG NHẬP</td>
                            </tr>
                            <tr>
                                <td>👤 Tên đăng nhập <span class="required">*</span></td>
                                <td>
                                    <input type="text" id="username" name="username" required 
                                           value="<?php echo htmlspecialchars($registerOld['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="Nhập tên đăng nhập">
                                    <div class="form-note">Tên đăng nhập để truy cập hệ thống</div>
                                </td>
                            </tr>
                            <tr>
                                <td>🔒 Mật khẩu <span class="required">*</span></td>
                                <td>
                                    <input type="password" id="password" name="password" required 
                                           placeholder="Tối thiểu 6 ký tự">
                                </td>
                            </tr>
                            <tr>
                                <td>🔐 Nhập lại mật khẩu <span class="required">*</span></td>
                                <td>
                                    <input type="password" id="confirm_password" name="confirm_password" required 
                                           placeholder="Nhập lại mật khẩu để xác nhận">
                                </td>
                            </tr>

                            <!-- Thông tin cá nhân -->
                            <tr class="section-header">
                                <td colspan="2">👤 THÔNG TIN CÁ NHÂN</td>
                            </tr>
                            <tr>
                                <td>📝 Họ và tên <span class="required">*</span></td>
                                <td>
                                    <input type="text" id="full_name" name="full_name" required 
                                           value="<?php echo htmlspecialchars($registerOld['full_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="Nhập họ tên đầy đủ">
                                </td>
                            </tr>
                            <tr>
                                <td>📧 Email <span class="required">*</span></td>
                                <td>
                                    <input type="email" id="email" name="email" required 
                                           value="<?php echo htmlspecialchars($registerOld['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="Nhập địa chỉ email">
                                </td>
                            </tr>
                            <tr>
                                <td>📱 Điện thoại</td>
                                <td>
                                    <input type="tel" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($registerOld['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="Số điện thoại liên hệ">
                                </td>
                            </tr>
                            <tr>
                                <td>🎂 Ngày sinh</td>
                                <td>
                                    <input type="date" id="date_of_birth" name="date_of_birth" 
                                           value="<?php echo htmlspecialchars($registerOld['date_of_birth'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                </td>
                            </tr>
                            <tr>
                                <td>🌏 Dân tộc</td>
                                <td>
                                    <input type="text" id="ethnicity" name="ethnicity" 
                                           value="<?php echo htmlspecialchars($registerOld['ethnicity'] ?? 'Kinh', ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="Ví dụ: Kinh, Khmer...">
                                </td>
                            </tr>
                            <tr>
                                <td>🙏 Tôn giáo</td>
                                <td>
                                    <select id="religion" name="religion">
                                        <option value="">-- Chọn tôn giáo --</option>
                                        <option value="Không" <?php echo ($registerOld['religion'] ?? '') === 'Không' ? 'selected' : ''; ?>>Không</option>
                                        <option value="Phật giáo" <?php echo ($registerOld['religion'] ?? '') === 'Phật giáo' ? 'selected' : ''; ?>>Phật giáo</option>
                                        <option value="Công giáo" <?php echo ($registerOld['religion'] ?? '') === 'Công giáo' ? 'selected' : ''; ?>>Công giáo</option>
                                        <option value="Tin lành" <?php echo ($registerOld['religion'] ?? '') === 'Tin lành' ? 'selected' : ''; ?>>Tin lành</option>
                                        <option value="Cao Đài" <?php echo ($registerOld['religion'] ?? '') === 'Cao Đài' ? 'selected' : ''; ?>>Cao Đài</option>
                                        <option value="Hòa Hảo" <?php echo ($registerOld['religion'] ?? '') === 'Hòa Hảo' ? 'selected' : ''; ?>>Hòa Hảo</option>
                                        <option value="Khác" <?php echo ($registerOld['religion'] ?? '') === 'Khác' ? 'selected' : ''; ?>>Khác</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>🏠 Địa chỉ</td>
                                <td>
                                    <input type="text" id="address" name="address" 
                                           value="<?php echo htmlspecialchars($registerOld['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="Địa chỉ nơi ở">
                                </td>
                            </tr>
                            <tr>
                                <td>🎓 Trình độ học vấn</td>
                                <td>
                                    <select id="education_level" name="education_level">
                                        <option value="">-- Chọn trình độ --</option>
                                        <option value="Trung học cơ sở" <?php echo ($registerOld['education_level'] ?? '') === 'Trung học cơ sở' ? 'selected' : ''; ?>>Trung học cơ sở</option>
                                        <option value="Trung học phổ thông" <?php echo ($registerOld['education_level'] ?? '') === 'Trung học phổ thông' ? 'selected' : ''; ?>>Trung học phổ thông</option>
                                        <option value="Trung cấp" <?php echo ($registerOld['education_level'] ?? '') === 'Trung cấp' ? 'selected' : ''; ?>>Trung cấp</option>
                                        <option value="Cao đẳng" <?php echo ($registerOld['education_level'] ?? '') === 'Cao đẳng' ? 'selected' : ''; ?>>Cao đẳng</option>
                                        <option value="Đại học" <?php echo ($registerOld['education_level'] ?? '') === 'Đại học' ? 'selected' : ''; ?>>Đại học</option>
                                        <option value="Thạc sĩ" <?php echo ($registerOld['education_level'] ?? '') === 'Thạc sĩ' ? 'selected' : ''; ?>>Thạc sĩ</option>
                                        <option value="Tiến sĩ" <?php echo ($registerOld['education_level'] ?? '') === 'Tiến sĩ' ? 'selected' : ''; ?>>Tiến sĩ</option>
                                    </select>
                                </td>
                            </tr>

                            <!-- Thông tin công tác -->
                            <tr class="section-header">
                                <td colspan="2">💼 THÔNG TIN CÔNG TÁC</td>
                            </tr>
                            <tr>
                                <td>👥 Loại người dùng <span class="required">*</span></td>
                                <td>
                                    <select id="user_type" name="user_type" required>
                                        <option value="">-- Chọn loại người dùng --</option>
                                        <option value="nguoi_dan" <?php echo ($registerOld['user_type'] ?? '') === 'nguoi_dan' ? 'selected' : ''; ?>>Người dân</option>
                                        <option value="can_bo" <?php echo ($registerOld['user_type'] ?? '') === 'can_bo' ? 'selected' : ''; ?>>Cán bộ</option>
                                    </select>
                                    <div class="form-note">Chọn "Cán bộ" nếu bạn là nhân viên UBND</div>
                                </td>
                            </tr>
                            <tr>
                                <td>🏢 Đơn vị</td>
                                <td>
                                    <input type="text" id="organization" name="organization" 
                                           value="<?php echo htmlspecialchars($registerOld['organization'] ?? 'UBND XÃ LONG HIỆP - TỈNH VĨNH LONG', ENT_QUOTES, 'UTF-8'); ?>" 
                                           readonly style="background: #f8f9fa;">
                                </td>
                            </tr>
                            
                            <!-- Thông tin cán bộ (ẩn mặc định) -->
                            <tr class="canbo-field" style="display: none;">
                                <td>🏛️ Phòng ban</td>
                                <td>
                                    <select id="department" name="department">
                                        <option value="">-- Chọn phòng ban --</option>
                                        <option value="Ban Lãnh đạo UBND" <?php echo ($registerOld['department'] ?? '') === 'Ban Lãnh đạo UBND' ? 'selected' : ''; ?>>Ban Lãnh đạo UBND</option>
                                        <option value="Ban Lãnh đạo Đảng ủy" <?php echo ($registerOld['department'] ?? '') === 'Ban Lãnh đạo Đảng ủy' ? 'selected' : ''; ?>>Ban Lãnh đạo Đảng ủy</option>
                                        <option value="Ủy viên Ban Thường vụ Đảng ủy" <?php echo ($registerOld['department'] ?? '') === 'Ủy viên Ban Thường vụ Đảng ủy' ? 'selected' : ''; ?>>Ủy viên Ban Thường vụ Đảng ủy</option>
                                        <option value="Phòng Kinh tế" <?php echo ($registerOld['department'] ?? '') === 'Phòng Kinh tế' ? 'selected' : ''; ?>>Phòng Kinh tế</option>
                                        <option value="Phòng Văn hóa - Xã hội" <?php echo ($registerOld['department'] ?? '') === 'Phòng Văn hóa - Xã hội' ? 'selected' : ''; ?>>Phòng Văn hóa - Xã hội</option>
                                        <option value="Văn phòng Đảng ủy - HĐND - UBND" <?php echo ($registerOld['department'] ?? '') === 'Văn phòng Đảng ủy - HĐND - UBND' ? 'selected' : ''; ?>>Văn phòng Đảng ủy - HĐND - UBND</option>
                                        <option value="Trạm Y tế xã Long Hiệp" <?php echo ($registerOld['department'] ?? '') === 'Trạm Y tế xã Long Hiệp' ? 'selected' : ''; ?>>Trạm Y tế xã Long Hiệp</option>
                                        <option value="Công an xã" <?php echo ($registerOld['department'] ?? '') === 'Công an xã' ? 'selected' : ''; ?>>Công an xã</option>
                                        <option value="Quân sự xã" <?php echo ($registerOld['department'] ?? '') === 'Quân sự xã' ? 'selected' : ''; ?>>Quân sự xã</option>
                                    </select>
                                </td>
                            </tr>
                            <tr class="canbo-field" style="display: none;">
                                <td>💼 Chức danh</td>
                                <td>
                                    <input type="text" id="position" name="position" 
                                           value="<?php echo htmlspecialchars($registerOld['position'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="Ví dụ: Chuyên viên, Trưởng phòng...">
                                </td>
                            </tr>
                            <tr class="canbo-field" style="display: none;">
                                <td>🆔 Mã nhân viên</td>
                                <td>
                                    <input type="text" id="employee_id" name="employee_id" 
                                           value="<?php echo htmlspecialchars($registerOld['employee_id'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="Mã số cán bộ">
                                </td>
                            </tr>
                            <?php if ($isAdmin): ?>
                            <tr class="canbo-field" style="display: none;">
                                <td>⚙️ Vai trò hệ thống</td>
                                <td>
                                    <select id="role" name="role">
                                        <option value="editor" <?php echo ($registerOld['role'] ?? 'editor') === 'editor' ? 'selected' : ''; ?>>Biên tập viên</option>
                                        <option value="admin" <?php echo ($registerOld['role'] ?? 'editor') === 'admin' ? 'selected' : ''; ?>>Quản trị viên</option>
                                        <option value="viewer" <?php echo ($registerOld['role'] ?? 'editor') === 'viewer' ? 'selected' : ''; ?>>Người xem</option>
                                    </select>
                                    <div class="form-note">Chỉ Admin mới có quyền thay đổi vai trò</div>
                                </td>
                            </tr>
                            <?php endif; ?>

                            <?php if (!$isAdmin): ?>
                                <input type="hidden" name="role" value="editor">
                            <?php endif; ?>

                            <!-- Nút submit -->
                            <tr class="btn-submit-row">
                                <td colspan="2">
                                    <button type="submit" class="btn-login" style="background: var(--gradient-primary) !important; color: white !important; padding: 16px 50px !important; font-size: 18px !important; font-weight: 700 !important; border: none !important; border-radius: 12px !important; cursor: pointer !important; box-shadow: 0 4px 15px rgba(185, 28, 28, 0.4) !important;">
                                        ✨ Đăng ký tài khoản
                                    </button>
                                    <div style="margin-top: 20px;">
                                        <a href="dang-nhap.php" class="forgot-password" style="color: var(--primary) !important; font-weight: 600 !important; font-size: 16px !important;">
                                            🔑 Đã có tài khoản? Đăng nhập ngay
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </form>

                    <script>
                        // Hiển thị/ẩn các trường dành cho cán bộ
                        document.getElementById('user_type').addEventListener('change', function() {
                            const canBoFields = document.querySelectorAll('.canbo-field');
                            if (this.value === 'can_bo') {
                                canBoFields.forEach(field => {
                                    field.style.display = 'table-row';
                                });
                            } else {
                                canBoFields.forEach(field => {
                                    field.style.display = 'none';
                                });
                            }
                        });

                        // Trigger on page load
                        document.addEventListener('DOMContentLoaded', function() {
                            const userType = document.getElementById('user_type').value;
                            if (userType === 'can_bo') {
                                const canBoFields = document.querySelectorAll('.canbo-field');
                                canBoFields.forEach(field => {
                                    field.style.display = 'table-row';
                                });
                            }
                        });
                    </script>
                </div>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>
    <?php include 'quick-actions-toolbar.php'; ?>
</body>
</html>
