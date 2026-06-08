<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liên Hệ - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css?v=2.0">
    <script src="dropdown.js"></script>
</head>
<body>
    <?php include 'header-menu.php'; ?>

    <main>
        <section class="page-header">
            <div class="container">
                <h2>Liên Hệ</h2>
                <p>Thông tin liên hệ UBND Xã Long Hiệp</p>
            </div>
        </section>

        <section class="contact">
            <div class="container">
                <div class="contact-grid">
                    <div class="contact-info-box">
                        <h3>Thông tin liên hệ</h3>
                        <div class="info-item">
                            <strong>Địa chỉ:</strong>
                            <p>Xã Long Hiệp, [Huyện], [Tỉnh]</p>
                        </div>
                        <div class="info-item">
                            <strong>Điện thoại:</strong>
                            <p>[số điện thoại]</p>
                        </div>
                        <div class="info-item">
                            <strong>Email:</strong>
                            <p>ubnd@longhiep.gov.vn</p>
                        </div>
                        <div class="info-item">
                            <strong>Fax:</strong>
                            <p>[số fax]</p>
                        </div>
                        <div class="info-item">
                            <strong>Giờ làm việc:</strong>
                            <p>Thứ 2 - Thứ 6: 7h30 - 11h30, 13h30 - 17h00</p>
                            <p>Thứ 7: 7h30 - 11h30</p>
                        </div>
                    </div>

                    <div class="contact-form-box">
                        <h3>Gửi tin nhắn</h3>
                        <form action="process-contact.php" method="POST" class="contact-form">
                            <div class="form-group">
                                <label for="name">Họ và tên *</label>
                                <input type="text" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Số điện thoại</label>
                                <input type="tel" id="phone" name="phone">
                            </div>
                            <div class="form-group">
                                <label for="subject">Tiêu đề *</label>
                                <input type="text" id="subject" name="subject" required>
                            </div>
                            <div class="form-group">
                                <label for="message">Nội dung *</label>
                                <textarea id="message" name="message" rows="5" required></textarea>
                            </div>
                            <button type="submit" class="btn-submit">Gửi tin nhắn</button>
                        </form>
                    </div>
                </div>

                <div class="map-section">
                    <h3>Bản đồ</h3>
                    <div class="map-placeholder">
                        <p>Nhúng Google Maps tại đây</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Liên hệ</h3>
                    <p>Địa chỉ: Xã Long Hiệp</p>
                    <p>Điện thoại: [số điện thoại]</p>
                    <p>Email: ubnd@longhiep.gov.vn</p>
                </div>
                <div class="footer-section">
                    <h3>Liên kết</h3>
                    <ul>
                        <li><a href="#">Cổng thông tin Chính phủ</a></li>
                        <li><a href="#">UBND Huyện</a></li>
                        <li><a href="#">UBND Tỉnh</a></li>
                    </ul>
                </div>
            </div>
            <p class="copyright">&copy; 2026 UBND Xã Long Hiệp. All rights reserved.</p>
        </div>
    <?php include 'footer.php'; ?>
    <?php include 'quick-actions-toolbar.php'; ?>
</body>
</html>
