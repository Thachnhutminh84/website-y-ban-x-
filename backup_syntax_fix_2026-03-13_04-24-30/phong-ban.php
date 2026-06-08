<?php
header('Content-Type: text/html; charset=utf-8');
session_start();

require_once 'auth.php';
require_once 'department-data.php';

$isLoggedIn = authIsLoggedIn();
$currentRole = authCurrentRole();
$displayName = $isLoggedIn ? authDisplayName() : '';
$departmentList = array_values($departments);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phòng ban - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css?v=2.1">
    <link rel="stylesheet" href="phong-ban-style.css?v=2.1">
    <script src="dropdown.js"></script>
</head>
<body>
    <!-- Header thống nhất -->
    <?php include 'menu-don-gian.php'; ?>

    <main class="dept-page dept-page--directory">
        <section class="department-directory">
            <div class="container">
                <div class="breadcrumb-nav">
                    <a href="index.php">Trang chủ</a> / <span>Phòng ban</span>
                </div>

                <div class="department-directory__intro">
                    <span class="department-directory__eyebrow">Danh mục phòng ban</span>
                    <h2>Tra cứu các đầu mối chuyên môn của UBND xã Long Hiệp</h2>
                    <p>
                        Slideshow bên dưới hiển thị 4 đầu mối chính gồm phòng VH - XH, KT, Văn phòng và Hành chính công.
                        Chọn mục cần xem để mở trang chi tiết tương ứng. Hiện tại hồ sơ chi tiết của phòng VH - XH đã sẵn sàng.
                    </p>
                </div>

                <div class="department-search">
                    <input 
                        type="text" 
                        id="departmentSearchInput" 
                        class="department-search__input" 
                        placeholder="🔍 Tìm kiếm phòng ban theo tên..."
                        aria-label="Tìm kiếm phòng ban"
                    >
                </div>

                <div class="department-slider" data-department-slider>
                    <button class="department-slider__control department-slider__control--prev" type="button" data-direction="-1" aria-label="Xem phòng ban trước">
                        &#10094;
                    </button>

                    <div class="department-slider__viewport">
                        <?php foreach ($departmentList as $index => $department): ?>
                            <?php
                            $theme = $department['theme'];
                            $style = sprintf(
                                '--dept-accent-1:%s;--dept-accent-2:%s;--dept-accent-3:%s;',
                                $theme[0],
                                $theme[1],
                                $theme[2]
                            );
                            ?>
                            <article
                                class="department-slide<?php echo $index === 0 ? ' is-active' : ''; ?>"
                                data-slide
                                style="<?php echo htmlspecialchars($style, ENT_QUOTES, 'UTF-8'); ?>"
                            >
                                <div class="department-slide__top">
                                    <span class="department-slide__eyebrow"><?php echo htmlspecialchars($department['badge'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <span class="department-slide__status"><?php echo htmlspecialchars($department['status_label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>

                                <div class="department-slide__layout">
                                    <div class="department-slide__copy">
                                        <span class="department-slide__code"><?php echo htmlspecialchars($department['short_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                        <h3>
                                            <?php if (!empty($department['detail_available'])): ?>
                                                <a class="department-slide__title-link" href="<?php echo htmlspecialchars($department['detail_href'], ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?php echo htmlspecialchars($department['name'], ENT_QUOTES, 'UTF-8'); ?>
                                                </a>
                                            <?php else: ?>
                                                <?php echo htmlspecialchars($department['name'], ENT_QUOTES, 'UTF-8'); ?>
                                            <?php endif; ?>
                                        </h3>
                                        <p><?php echo htmlspecialchars($department['subtitle'], ENT_QUOTES, 'UTF-8'); ?></p>

                                        <div class="department-slide__meta">
                                            <div class="department-slide__meta-card">
                                                <span>Người phụ trách</span>
                                                <strong><?php echo htmlspecialchars($department['manager'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                            </div>
                                            <div class="department-slide__meta-card">
                                                <span>Trọng tâm</span>
                                                <strong><?php echo htmlspecialchars($department['focus'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="department-slide__panel">
                                        <ul class="department-slide__facts">
                                            <?php foreach ($department['summary_points'] as $point): ?>
                                                <li><?php echo htmlspecialchars($point, ENT_QUOTES, 'UTF-8'); ?></li>
                                            <?php endforeach; ?>
                                        </ul>

                                        <?php if (!empty($department['detail_available'])): ?>
                                            <a class="department-slide__button" href="<?php echo htmlspecialchars($department['detail_href'], ENT_QUOTES, 'UTF-8'); ?>">
                                                Xem trang <?php echo htmlspecialchars($department['short_name'], ENT_QUOTES, 'UTF-8'); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="department-slide__button department-slide__button--disabled">Hồ sơ chi tiết đang cập nhật</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <button class="department-slider__control department-slider__control--next" type="button" data-direction="1" aria-label="Xem phòng ban tiếp theo">
                        &#10095;
                    </button>

                    <div class="department-slider__dots">
                        <?php foreach ($departmentList as $index => $department): ?>
                            <button
                                class="department-slider__dot<?php echo $index === 0 ? ' is-active' : ''; ?>"
                                type="button"
                                data-dot="<?php echo $index; ?>"
                                aria-label="<?php echo htmlspecialchars($department['name'], ENT_QUOTES, 'UTF-8'); ?>"
                            ></button>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="department-card-grid" id="departmentCardGrid">
                    <?php foreach ($departmentList as $department): ?>
                        <article class="department-card<?php echo empty($department['detail_available']) ? ' department-card--disabled' : ''; ?>" data-department-name="<?php echo htmlspecialchars(strtolower($department['name']), ENT_QUOTES, 'UTF-8'); ?>">
                            <span class="department-card__code"><?php echo htmlspecialchars($department['short_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <h3>
                                <?php if (!empty($department['detail_available'])): ?>
                                    <a class="department-card__title-link" href="<?php echo htmlspecialchars($department['detail_href'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars($department['name'], ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($department['name'], ENT_QUOTES, 'UTF-8'); ?>
                                <?php endif; ?>
                            </h3>
                            <p><?php echo htmlspecialchars($department['summary'], ENT_QUOTES, 'UTF-8'); ?></p>
                            
                            <div class="department-card__contact">
                                <div class="department-card__contact-item">
                                    <span>📞</span>
                                    <strong><?php echo htmlspecialchars($department['phone'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                </div>
                                <div class="department-card__contact-item">
                                    <span>✉️</span>
                                    <strong><?php echo htmlspecialchars($department['email'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                </div>
                                <div class="department-card__contact-item">
                                    <span>🕐</span>
                                    <strong><?php echo htmlspecialchars($department['hours'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                </div>
                            </div>

                            <div class="department-card__actions">
                                <a href="tel:<?php echo htmlspecialchars($department['phone'], ENT_QUOTES, 'UTF-8'); ?>" class="department-card__action-btn department-card__action-btn--call">
                                    📞 Gọi ngay
                                </a>
                                <a href="mailto:<?php echo htmlspecialchars($department['email'], ENT_QUOTES, 'UTF-8'); ?>" class="department-card__action-btn department-card__action-btn--email">
                                    ✉️ Gửi email
                                </a>
                            </div>

                            <div class="department-card__footer">
                                <strong><?php echo htmlspecialchars($department['manager'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                <?php if (!empty($department['detail_available'])): ?>
                                    <a href="<?php echo htmlspecialchars($department['detail_href'], ENT_QUOTES, 'UTF-8'); ?>">Mở trang</a>
                                <?php else: ?>
                                    <span><?php echo htmlspecialchars($department['status_label'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const slider = document.querySelector('[data-department-slider]');

        if (!slider) {
            return;
        }

        const slides = Array.from(slider.querySelectorAll('[data-slide]'));
        const dots = Array.from(slider.querySelectorAll('[data-dot]'));
        const controls = Array.from(slider.querySelectorAll('[data-direction]'));

        if (slides.length === 0) {
            return;
        }

        let activeIndex = slides.findIndex(function (slide) {
            return slide.classList.contains('is-active');
        });

        if (activeIndex < 0) {
            activeIndex = 0;
        }

        let timerId = null;

        function showSlide(index) {
            activeIndex = (index + slides.length) % slides.length;

            slides.forEach(function (slide, slideIndex) {
                slide.classList.toggle('is-active', slideIndex === activeIndex);
            });

            dots.forEach(function (dot, dotIndex) {
                dot.classList.toggle('is-active', dotIndex === activeIndex);
            });
        }

        function stopAutoPlay() {
            if (timerId !== null) {
                window.clearInterval(timerId);
                timerId = null;
            }
        }

        function startAutoPlay() {
            stopAutoPlay();
            timerId = window.setInterval(function () {
                showSlide(activeIndex + 1);
            }, 5500);
        }

        controls.forEach(function (control) {
            control.addEventListener('click', function () {
                showSlide(activeIndex + Number(control.dataset.direction || 0));
                startAutoPlay();
            });
        });

        dots.forEach(function (dot, index) {
            dot.addEventListener('click', function () {
                showSlide(index);
                startAutoPlay();
            });
        });

        slider.addEventListener('mouseenter', stopAutoPlay);
        slider.addEventListener('mouseleave', startAutoPlay);

        showSlide(activeIndex);
        startAutoPlay();
    });

    // Department search functionality
    const searchInput = document.getElementById('departmentSearchInput');
    const cardGrid = document.getElementById('departmentCardGrid');
    const cards = Array.from(cardGrid.querySelectorAll('.department-card'));

    if (searchInput && cards.length > 0) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();

            cards.forEach(function(card) {
                const departmentName = card.getAttribute('data-department-name') || '';
                const isMatch = departmentName.includes(searchTerm);
                
                if (isMatch || searchTerm === '') {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
    </script>
</body>
</html>
