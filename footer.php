<?php
// Footer cho website UBND Xã Long Hiệp
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (file_exists('auth.php')) {
    require_once 'auth.php';
}
$isLoggedIn = function_exists('authIsLoggedIn') ? authIsLoggedIn() : false;
$isApproved = function_exists('authIsApproved') ? authIsApproved() : false;
$canManageContent = function_exists('authCanManageContent') ? authCanManageContent() : false;
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="footer-style.css">

<footer class="modern-footer">
    <div class="footer-wave">
        <svg viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z"></path>
        </svg>
    </div>
    
    <div class="footer-container">
        <!-- Main Footer Content -->
        <div class="footer-grid">
            <!-- About Section -->
            <div class="footer-col footer-about">
                <div class="footer-logo">
                    <img src="images/logo.png" alt="Logo UBND" onerror="this.style.display='none'">
                    <div>
                        <h3>UBND Xã Long Hiệp</h3>
                        <p class="footer-tagline">Phục vụ nhân dân - Xây dựng quê hương</p>
                    </div>
                </div>
                <p class="footer-description">
                    Cổng thông tin điện tử UBND Xã Long Hiệp, Tỉnh Vĩnh Long. 
                    Cung cấp thông tin, dịch vụ công và kết nối với người dân.
                </p>
                <div class="footer-social">
                    <a href="https://www.facebook.com/ubndlonghiep" target="_blank" rel="noopener" class="social-btn facebook" title="Facebook">
                        <svg viewBox="0 0 320 512" width="18" height="18" fill="white"><path d="M279.14 288l14.22-92.83h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S260.43 0 225.36 0c-73.22 0-121.08 44.38-121.08 124.72v70.45H22.89V288h81.39v224h100.17V288z"/></svg>
                    </a>
                    <a href="https://www.youtube.com/@ubndlonghiep" target="_blank" rel="noopener" class="social-btn youtube" title="YouTube">
                        <svg viewBox="0 0 576 512" width="20" height="20" fill="white"><path d="M549.655 124.083c-6.281-23.65-24.787-42.276-48.284-48.597C458.781 64 288 64 288 64S117.22 64 74.629 75.486c-23.497 6.322-42.003 24.947-48.284 48.597-11.412 42.867-11.412 132.305-11.412 132.305s0 89.438 11.412 132.305c6.281 23.65 24.787 41.5 48.284 47.821C117.22 448 288 448 288 448s170.78 0 213.371-11.486c23.497-6.321 42.003-24.171 48.284-47.821 11.412-42.867 11.412-132.305 11.412-132.305s0-89.438-11.412-132.305zm-317.51 213.508V175.185l142.739 81.205-142.739 81.201z"/></svg>
                    </a>
                    <a href="https://zalo.me/UBNDXaLongHiep" target="_blank" rel="noopener" class="social-btn zalo" title="Zalo">
                        <span style="font-weight:bold;font-size:18px;line-height:1;">Z</span>
                    </a>
                    <a href="https://www.instagram.com/ubndlonghiep" target="_blank" rel="noopener" class="social-btn instagram" title="Instagram">
                        <svg viewBox="0 0 448 512" width="18" height="18" fill="white"><path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"/></svg>
                    </a>
                    <?php if ($isLoggedIn && $isApproved && $canManageContent): ?>
                    <a href="javascript:void(0)" onclick="toggleChatbox()" class="social-btn chat" title="Chat">
                        <i class="fab fa-telegram-plane"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="footer-col">
                <h4><i class="fas fa-link"></i> Liên kết nhanh</h4>
                <ul class="footer-links">
                    <li><a href="tin-tuc.php"><i class="fas fa-chevron-right"></i> Tin tức</a></li>
                    <li><a href="phong-ban.php"><i class="fas fa-chevron-right"></i> Phòng ban</a></li>
                    <li><a href="lanh-dao.php"><i class="fas fa-chevron-right"></i> Lãnh đạo</a></li>
                    <li><a href="lien-he.php"><i class="fas fa-chevron-right"></i> Liên hệ</a></li>
                </ul>
            </div>

            <!-- Categories -->
            <div class="footer-col">
                <h4><i class="fas fa-newspaper"></i> Danh mục tin tức</h4>
                <ul class="footer-links">
                    <li><a href="cong-tac-xay-dung-dang.php"><i class="fas fa-chevron-right"></i> Công tác xây dựng Đảng</a></li>
                    <li><a href="mat-tran-doan-the.php"><i class="fas fa-chevron-right"></i> Mặt trận đoàn thể</a></li>
                    <li><a href="an-ninh-trat-tu.php"><i class="fas fa-chevron-right"></i> An ninh trật tự</a></li>
                    <li><a href="tin-tuc-su-kien.php"><i class="fas fa-chevron-right"></i> Tin tức sự kiện</a></li>
                    <li><a href="giao-duc-dao-tao.php"><i class="fas fa-chevron-right"></i> Giáo dục và đào tạo</a></li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="footer-col">
                <h4><i class="fas fa-address-card"></i> Thông tin liên hệ</h4>
                <ul class="footer-contact">
                    <li>
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Xã Long Hiệp, Tỉnh Vĩnh Long</span>
                    </li>
                    <li>
                        <i class="fas fa-phone"></i>
                        <span>(0272) 3xxx xxx</span>
                    </li>
                    <li>
                        <i class="fas fa-envelope"></i>
                        <span>ubnd@longhiep.vinhlong.gov.vn</span>
                    </li>
                    <li>
                        <i class="fas fa-globe"></i>
                        <span>www.longhiep.vinhlong.gov.vn</span>
                    </li>
                </ul>
                
                <div class="working-hours">
                    <h5><i class="fas fa-clock"></i> Giờ làm việc</h5>
                    <p><strong>Thứ 2 - Thứ 6:</strong> 7:30 - 11:30 | 13:30 - 17:00</p>
                    <p><strong>Thứ 7:</strong> 7:30 - 11:30</p>
                    <p><strong>Chủ nhật:</strong> Nghỉ</p>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <div class="footer-bottom-content">
                <div class="copyright">
                    <p>&copy; <?php echo date('Y'); ?> UBND Xã Long Hiệp. Tất cả quyền được bảo lưu.</p>
                    <p>Thiết kế và phát triển bởi <strong>Ban Công nghệ thông tin</strong></p>
                </div>
                <div class="footer-stats">
                    <div class="stat-item">
                        <i class="fas fa-eye"></i>
                        <span>Lượt truy cập: <strong>1,234</strong></span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-users"></i>
                        <span>Online: <strong>5</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<?php include 'chatbox.php'; ?>
<?php include 'chatbot.php'; ?>
