<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';

$page_title = "Giới thiệu - UBND Xã Long Hiệp";
include 'header-menu.php';
?>

<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="gioi-thieu-style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
/* --- Two-col layout --- */
.intro-two-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    align-items: start;
}
.intro-two-col .intro-card { height: 100%; }

/* --- Quick stats banner --- */
.intro-quick-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0;
    background: white;
    border-radius: 24px;
    border: 1px solid #f0f0f0;
    box-shadow: 0 4px 24px rgba(0,0,0,0.06);
    overflow: hidden;
    margin-top: -40px;
    position: relative;
    z-index: 2;
}

.intro-qs-item {
    padding: 32px 24px;
    text-align: center;
    border-right: 1px solid #f0f0f0;
    transition: background 0.3s ease;
}

.intro-qs-item:last-child { border-right: none; }
.intro-qs-item:hover { background: #fef2f2; }

.intro-qs-item .qs-num {
    font-size: 46px;
    font-weight: 900;
    color: #dc2626;
    line-height: 1;
    margin-bottom: 8px;
}

.intro-qs-item .qs-label {
    font-size: 17px;
    color: #94a3b8;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}

/* --- Divider with text --- */
.intro-divider {
    display: flex;
    align-items: center;
    gap: 20px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px 20px 0;
}

.intro-divider::before,
.intro-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, transparent, #e5e7eb, transparent);
}

.intro-divider span {
    color: #94a3b8;
    font-size: 16px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.15em;
    white-space: nowrap;
}

@media (max-width: 1024px) {
    .intro-two-col { grid-template-columns: 1fr; }
    .intro-quick-stats { grid-template-columns: repeat(2, 1fr); }
    .intro-qs-item:nth-child(2) { border-right: none; }
}

@media (max-width: 768px) {
    .intro-quick-stats { grid-template-columns: 1fr; margin-top: -20px; }
    .intro-qs-item { border-right: none; border-bottom: 1px solid #f0f0f0; }
    .intro-qs-item:last-child { border-bottom: none; }
}
</style>

<!-- ========== HERO ========== -->
<section class="intro-hero">
    <div class="intro-hero-inner">
        <div class="intro-hero-badge">
            <i class="fas fa-landmark"></i> Giới thiệu
        </div>
        <h1>UBND Xã <span class="gold">Long Hiệp</span></h1>
        <p class="intro-hero-sub">
            Ủy ban nhân dân xã Long Hiệp, tỉnh Vĩnh Long — 
            nơi gắn liền với bản sắc văn hóa Khmer và tinh thần đoàn kết "rồng hợp sức".
        </p>
    </div>
</section>

<!-- ========== QUICK STATS BANNER ========== -->
<div class="intro-quick-stats">
    <div class="intro-qs-item">
        <div class="qs-num">6.516</div>
        <div class="qs-label">Hecta diện tích</div>
    </div>
    <div class="intro-qs-item">
        <div class="qs-num">30.272</div>
        <div class="qs-label">Dân số (người)</div>
    </div>
    <div class="intro-qs-item">
        <div class="qs-num">22</div>
        <div class="qs-label">Ấp</div>
    </div>
    <div class="intro-qs-item">
        <div class="qs-num">410</div>
        <div class="qs-label">Thủ tục hành chính</div>
    </div>
</div>

<!-- ========== TỔNG QUAN + VIDEO ========== -->
<section class="intro-section" style="padding-top: 48px;">
    <div class="intro-two-col">
        <!-- Trái: Tổng quan -->
        <div class="intro-card">
            <div class="intro-card-body">
                <div class="intro-sh">
                    <div class="intro-sh-icon" style="background: #fee2e2; color: #dc2626;">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <h2>Về xã Long Hiệp<small>Tỉnh Vĩnh Long</small></h2>
                </div>
                <p class="intro-text">
                    Tên gọi "Long Hiệp" mang ý nghĩa "rồng hợp sức", thể hiện tinh thần đoàn kết 
                    của cư dân nơi đây. Long Hiệp là vùng đất nông nghiệp trù phú nằm trong lưu vực sông Mê Kông, 
                    với hệ thống kênh rạch chằng chịt, cánh đồng lúa mênh mông và vườn cây ăn trái sum suê.
                </p>
                <p class="intro-text">
                    Đây là địa phương có tỷ lệ đồng bào dân tộc Khmer chiếm đa số (khoảng 80,45%), 
                    tạo nên bản sắc văn hóa độc đáo với 16 ngôi chùa Nam tông và Bắc tông.
                </p>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 20px;">
                    <div style="background: #fef2f2; border-radius: 12px; padding: 16px; border: 1px solid #fecaca;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                            <i class="fas fa-seedling" style="color: #dc2626; font-size: 16px;"></i>
                            <strong style="color: #0f172a; font-size: 18px;">Nông nghiệp</strong>
                        </div>
                        <p style="color: #64748b; font-size: 18px; line-height: 1.6; margin: 0;">Lúa gạo, cây ăn trái, nuôi trồng thủy sản — thế mạnh kinh tế nông nghiệp.</p>
                    </div>
                    <div style="background: #eff6ff; border-radius: 12px; padding: 16px; border: 1px solid #bfdbfe;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                            <i class="fas fa-place-of-worship" style="color: #2563eb; font-size: 16px;"></i>
                            <strong style="color: #0f172a; font-size: 18px;">Văn hóa</strong>
                        </div>
                        <p style="color: #64748b; font-size: 17px; line-height: 1.6; margin: 0;">16 chùa Khmer, lễ hội Ok Om Bok, Sene Dolta — bản sắc đặc trưng.</p>
                    </div>
                    <div style="background: #ecfdf5; border-radius: 12px; padding: 16px; border: 1px solid #a7f3d0;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                            <i class="fas fa-users" style="color: #059669; font-size: 16px;"></i>
                            <strong style="color: #0f172a; font-size: 18px;">Dân cư</strong>
                        </div>
                        <p style="color: #64748b; font-size: 17px; line-height: 1.6; margin: 0;">Đồng bào Khmer chiếm đa số, sống đoàn kết, hiếu khách.</p>
                    </div>
                    <div style="background: #fffbeb; border-radius: 12px; padding: 16px; border: 1px solid #fde68a;">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                            <i class="fas fa-water" style="color: #d97706; font-size: 16px;"></i>
                            <strong style="color: #0f172a; font-size: 18px;">Địa lý</strong>
                        </div>
                        <p style="color: #64748b; font-size: 17px; line-height: 1.6; margin: 0;">Vùng sông nước miền Tây, kênh rạch chằng chịt, giao thông đường thủy.</p>
                    </div>
                </div>

                <div class="info-box info-box--green" style="margin-top: 20px;">
                    <i class="fas fa-trophy"></i>
                    <div>
                        <strong>Xã đạt chuẩn Nông thôn mới</strong>
                        <span>Hoàn thành các tiêu chí Quốc gia, mô hình điểm của tỉnh Vĩnh Long.</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Phải: Video -->
        <div class="intro-card" style="overflow: hidden;">
            <div class="intro-card-body" style="padding: 0;">
                <div style="position: relative; background: #000; height: 100%; min-height: 400px;">
                    <video 
                        id="introVideo"
                        autoplay muted loop playsinline
                        style="width: 100%; height: 100%; min-height: 400px; object-fit: cover; display: block;"
                        poster="images/ubnd-longhiep-banner.png"
                    >
                        <source src="videos/7619542452296.mp4" type="video/mp4">
                    </video>
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; padding: 32px 28px 24px; background: linear-gradient(transparent, rgba(0,0,0,0.8)); color: white;">
                        <h3 style="font-size: 22px; font-weight: 800; margin-bottom: 4px;">
                            <i class="fas fa-play-circle"></i> Video giới thiệu
                        </h3>
                        <p style="font-size: 18px; opacity: 0.8; margin: 0;">Cảnh quan & hoạt động của UBND xã Long Hiệp</p>
                    </div>
                    <button onclick="toggleIntroVideo()" id="btnPlayPause" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 56px; height: 56px; border-radius: 50%; background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border: 2px solid rgba(255,255,255,0.3); color: white; font-size: 20px; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center;" onmouseover="this.style.background='rgba(220,38,38,0.8)'; this.style.transform='translate(-50%,-50%) scale(1.1)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'; this.style.transform='translate(-50%,-50%) scale(1)'">
                        <i class="fas fa-pause" id="iconPlayPause"></i>
                    </button>
                    <button onclick="toggleSound()" id="btnSound" style="position: absolute; top: 16px; right: 16px; padding: 8px 16px; border-radius: 9999px; background: rgba(220,38,38,0.85); backdrop-filter: blur(10px); border: none; color: white; font-size: 18px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; gap: 6px;" onmouseover="this.style.background='rgba(185,28,28,1)'" onmouseout="this.style.background='rgba(220,38,38,0.85)'">
                        <i class="fas fa-volume-mute" id="iconSound"></i>
                        <span id="textSound">Bật tiếng</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
var introMuted = true;
function toggleIntroVideo() {
    var v = document.getElementById('introVideo');
    var icon = document.getElementById('iconPlayPause');
    if (v.paused) { v.play(); icon.className = 'fas fa-pause'; }
    else { v.pause(); icon.className = 'fas fa-play'; }
}
function toggleSound() {
    var v = document.getElementById('introVideo');
    var icon = document.getElementById('iconSound');
    var text = document.getElementById('textSound');
    introMuted = !introMuted;
    v.muted = introMuted;
    if (introMuted) { icon.className = 'fas fa-volume-mute'; text.textContent = 'Bật tiếng'; }
    else { icon.className = 'fas fa-volume-up'; text.textContent = 'Tắt tiếng'; }
}
</script>

<!-- ===== DIVIDER ===== -->
<div class="intro-divider"><span><i class="fas fa-gavel"></i> &nbsp;Nghị định 150/2025/NĐ-CP</span></div>

<!-- ========== NĐ 150: DỄ HIỂU ========== -->
<section class="intro-section">
    <div class="intro-card">
        <div class="intro-card-body">
            <div class="intro-sh">
                <div class="intro-sh-icon" style="background: #fef3c7; color: #d97706;">
                    <i class="fas fa-gavel"></i>
                </div>
                <h2>Nghị định 150 là gì?<small>Nghị định 150/2025/NĐ-CP ban hành ngày 12/06/2025</small></h2>
            </div>

            <!-- Giải thích đơn giản -->
            <div style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-radius: 16px; padding: 28px 32px; border: 1px solid #bfdbfe; margin-bottom: 28px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 14px;">
                    <i class="fas fa-lightbulb" style="color: #2563eb; font-size: 24px;"></i>
                    <strong style="color: #1e40af; font-size: 20px;">Giải thích đơn giản</strong>
                </div>
                <p style="color: #1e40af; font-size: 18px; line-height: 1.9; margin: 0;">
                    Nghị định 150 là <strong>quy định mới của Chính phủ</strong> về cách tổ chức các phòng ban 
                    trong cơ quan nhà nước từ cấp tỉnh đến cấp xã. Nói nôm na: 
                        <strong>nó sắp xếp lại các phòng ban cho gọn gàng, rõ ràng hơn</strong>, 
                    để cán bộ làm việc hiệu quả và Nhân dân dễ hiểu.
                </p>
            </div>

            <!-- Trước và sau -->
            <div style="display: grid; grid-template-columns: 1fr auto 1fr; gap: 20px; align-items: center; margin-bottom: 28px;">
                <!-- Trước -->
                <div style="background: #fef2f2; border-radius: 16px; padding: 24px; border: 1px solid #fecaca; text-align: center;">
                    <div style="display: inline-flex; width: 48px; height: 48px; border-radius: 14px; background: #dc2626; color: white; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 12px;">
                        <i class="fas fa-times"></i>
                    </div>
                    <h4 style="color: #991b1b; font-size: 18px; margin-bottom: 8px;">Trước đây</h4>
                    <p style="color: #b91c1c; font-size: 18px; line-height: 1.7; margin: 0;">
                        Các phòng ban nhiều khi chồng chéo, không rõ ai làm gì. 
                        Có nơi thừa, có nơi thiếu, Nhân dân khó hiểu.
                    </p>
                </div>

                <!-- Arrow -->
                <div style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                    <i class="fas fa-arrow-right" style="color: #dc2626; font-size: 28px;"></i>
                    <span style="color: #94a3b8; font-size: 17px; font-weight: 600;">SẮP XẾP LẠI</span>
                </div>

                <!-- Sau -->
                <div style="background: #ecfdf5; border-radius: 16px; padding: 24px; border: 1px solid #a7f3d0; text-align: center;">
                    <div style="display: inline-flex; width: 48px; height: 48px; border-radius: 14px; background: #059669; color: white; align-items: center; justify-content: center; font-size: 20px; margin-bottom: 12px;">
                        <i class="fas fa-check"></i>
                    </div>
                    <h4 style="color: #065f46; font-size: 18px; margin-bottom: 8px;">Sau khi có NĐ 150</h4>
                    <p style="color: #047857; font-size: 18px; line-height: 1.7; margin: 0;">
                        Mỗi xã có đúng <strong>4 phòng chuyên môn</strong>, mỗi phòng rõ chức năng. 
                        Nhân dân biết cần gặp ai, gọi ai khi có việc.
                    </p>
                </div>
            </div>

            <!-- 4 phòng ban cụ thể -->
            <div style="background: #fafafa; border-radius: 16px; padding: 28px; border: 1px solid #f0f0f0; margin-bottom: 28px;">
                <h3 style="color: #0f172a; font-size: 20px; margin-bottom: 18px; text-align: center;">
                    <i class="fas fa-building" style="color: #dc2626;"></i> 4 Phòng ban tại xã Long Hiệp
                </h3>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px;">
                    <div style="background: white; border-radius: 14px; padding: 20px; border: 1px solid #f0f0f0; text-align: center;">
                        <div style="display: inline-flex; width: 44px; height: 44px; border-radius: 12px; background: #fee2e2; color: #dc2626; align-items: center; justify-content: center; font-size: 18px; margin-bottom: 10px;">
                            <i class="fas fa-scroll"></i>
                        </div>
                        <h4 style="color: #0f172a; font-size: 18px; margin-bottom: 6px;">Văn phòng HĐND & UBND</h4>
                        <p style="color: #64748b; font-size: 17px; line-height: 1.6; margin: 0;">Tiếp nhận hồ sơ, quản lý văn bản, tổ chức họp</p>
                    </div>
                    <div style="background: white; border-radius: 14px; padding: 20px; border: 1px solid #f0f0f0; text-align: center;">
                        <div style="display: inline-flex; width: 44px; height: 44px; border-radius: 12px; background: #dbeafe; color: #2563eb; align-items: center; justify-content: center; font-size: 18px; margin-bottom: 10px;">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h4 style="color: #0f172a; font-size: 18px; margin-bottom: 6px;">Phòng Kinh tế</h4>
                        <p style="color: #64748b; font-size: 17px; line-height: 1.6; margin: 0;">Tài chính, đất đai, đầu tư, phát triển kinh tế</p>
                    </div>
                    <div style="background: white; border-radius: 14px; padding: 20px; border: 1px solid #f0f0f0; text-align: center;">
                        <div style="display: inline-flex; width: 44px; height: 44px; border-radius: 12px; background: #fce7f3; color: #db2777; align-items: center; justify-content: center; font-size: 18px; margin-bottom: 10px;">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <h4 style="color: #0f172a; font-size: 18px; margin-bottom: 6px;">Phòng Văn hóa - Xã hội</h4>
                        <p style="color: #64748b; font-size: 17px; line-height: 1.6; margin: 0;">Giáo dục, y tế, văn hóa, an sinh xã hội</p>
                    </div>
                    <div style="background: white; border-radius: 14px; padding: 20px; border: 1px solid #f0f0f0; text-align: center;">
                        <div style="display: inline-flex; width: 44px; height: 44px; border-radius: 12px; background: #ecfdf5; color: #059669; align-items: center; justify-content: center; font-size: 18px; margin-bottom: 10px;">
                            <i class="fas fa-concierge-bell"></i>
                        </div>
                        <h4 style="color: #0f172a; font-size: 18px; margin-bottom: 6px;">Trung tâm Phục vụ Hành chính công</h4>
                        <p style="color: #64748b; font-size: 17px; line-height: 1.6; margin: 0;">Tiếp nhận, hướng dẫn, trả kết quả TTHC một cửa</p>
                    </div>
                </div>
            </div>

            <!-- Lợi ích cho Nhân dân -->
            <div style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border-radius: 16px; padding: 28px 32px; border: 1px solid #a7f3d0;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                    <i class="fas fa-hand-holding-heart" style="color: #059669; font-size: 24px;"></i>
                    <strong style="color: #065f46; font-size: 20px;">Lợi ích cho Nhân dân</strong>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div style="display: flex; align-items: flex-start; gap: 10px;">
                        <i class="fas fa-check-circle" style="color: #10b981; font-size: 18px; margin-top: 2px;"></i>
                        <span style="color: #047857; font-size: 18px; line-height: 1.6;">Biết rõ cần gặp <strong>ai</strong> khi có việc</span>
                    </div>
                    <div style="display: flex; align-items: flex-start; gap: 10px;">
                        <i class="fas fa-check-circle" style="color: #10b981; font-size: 18px; margin-top: 2px;"></i>
                        <span style="color: #047857; font-size: 18px; line-height: 1.6;">Giải quyết hồ sơ <strong>nhanh hơn</strong></span>
                    </div>
                    <div style="display: flex; align-items: flex-start; gap: 10px;">
                        <i class="fas fa-check-circle" style="color: #10b981; font-size: 18px; margin-top: 2px;"></i>
                        <span style="color: #047857; font-size: 18px; line-height: 1.6;">Không còn <strong>chồng chéo</strong> trách nhiệm</span>
                    </div>
                    <div style="display: flex; align-items: flex-start; gap: 10px;">
                        <i class="fas fa-check-circle" style="color: #10b981; font-size: 18px; margin-top: 2px;"></i>
                        <span style="color: #047857; font-size: 18px; line-height: 1.6;">Cán bộ <strong>hiệu quả hơn</strong>, phục vụ tốt hơn</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== DIVIDER ===== -->
<div class="intro-divider"><span><i class="fas fa-building"></i> &nbsp;Tổ chức phòng ban & Nhiệm vụ</span></div>

<!-- ========== 3 PHÒNG CHUYÊN MÔN ========== -->
<section class="intro-section">
    <div class="intro-card">
        <div class="intro-card-body">
            <div class="intro-sh">
                <div class="intro-sh-icon" style="background: #d1fae5; color: #059669;">
                    <i class="fas fa-building"></i>
                </div>
                <h2>4 Phòng chuyên môn cấp xã<small>Theo Điều 11 — Điều 16, Nghị định 150/2025/NĐ-CP</small></h2>
            </div>
                <div class="dept-grid">
                <div class="dept-card">
                    <div class="dept-icon" style="background: #fee2e2; color: #dc2626;">
                        <i class="fas fa-scroll"></i>
                    </div>
                    <h4>Văn phòng HĐND & UBND</h4>
                    <p>Tham mưu, giúp việc chung cho HĐND và UBND cấp xã</p>
                    <ul>
                        <li><i class="fas fa-check"></i> Tiếp nhận, phân loại, trình UBND xử lý văn bản</li>
                        <li><i class="fas fa-check"></i> Tổ chức kỳ họp HĐND, họp UBND</li>
                        <li><i class="fas fa-check"></i> Theo dõi, đôn đốc thực hiện kết luận, chỉ đạo</li>
                        <li><i class="fas fa-check"></i> Quản lý con dấu, tài liệu mật</li>
                        <li><i class="fas fa-check"></i> Tiếp công dân, giải quyết khiếu nại, tố cáo</li>
                    </ul>
                </div>
                <div class="dept-card">
                    <div class="dept-icon" style="background: #dbeafe; color: #2563eb;">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4>Phòng Kinh tế</h4>
                    <p>Quản lý nhà nước về kinh tế, tài chính, đầu tư, thương mại</p>
                    <ul>
                        <li><i class="fas fa-check"></i> Quản lý quy hoạch, kế hoạch phát triển kinh tế</li>
                        <li><i class="fas fa-check"></i> Quản lý tài chính, ngân sách, tài sản công</li>
                        <li><i class="fas fa-check"></i> Tham mưu thu hút đầu tư, phát triển DN</li>
                        <li><i class="fas fa-check"></i> Quản lý đất đai, xây dựng, hạ tầng kỹ thuật</li>
                        <li><i class="fas fa-check"></i> Quản lý kinh tế tập thể, kinh tế tư nhân</li>
                    </ul>
                </div>
                <div class="dept-card">
                    <div class="dept-icon" style="background: #fce7f3; color: #db2777;">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <h4>Phòng Văn hóa - Xã hội</h4>
                    <p>Quản lý nhà nước về văn hóa, xã hội, y tế, giáo dục</p>
                    <ul>
                        <li><i class="fas fa-check"></i> Quản lý hoạt động văn hóa, thông tin, truyền thông</li>
                        <li><i class="fas fa-check"></i> Quản lý giáo dục, đào tạo, dạy nghề</li>
                        <li><i class="fas fa-check"></i> Quản lý y tế, dân số, KHHGĐ</li>
                        <li><i class="fas fa-check"></i> Quản lý lao động, việc làm, an sinh xã hội</li>
                        <li><i class="fas fa-check"></i> Quản lý hội, tổ chức phi chính phủ</li>
                    </ul>
                </div>
                <div class="dept-card">
                    <div class="dept-icon" style="background: #ecfdf5; color: #059669;">
                        <i class="fas fa-concierge-bell"></i>
                    </div>
                    <h4>Trung tâm Phục vụ Hành chính công</h4>
                    <p>Đầu mối tiếp nhận, số hóa, hướng dẫn và trả kết quả giải quyết TTHC một cửa</p>
                    <ul>
                        <li><i class="fas fa-check"></i> Công khai thủ tục hành chính, hỗ trợ dịch vụ công trực tuyến</li>
                        <li><i class="fas fa-check"></i> Hướng dẫn, tiếp nhận hồ sơ thủ tục hành chính</li>
                        <li><i class="fas fa-check"></i> Số hóa hồ sơ, phối hợp giải quyết TTHC</li>
                        <li><i class="fas fa-check"></i> Trả kết quả giải quyết TTHC cho cá nhân, tổ chức</li>
                        <li><i class="fas fa-check"></i> Giám sát, đôn đốc việc giải quyết TTHC trên địa bàn</li>
                    </ul>
                </div>
            </div>
            <div class="info-box info-box--amber" style="margin-top: 24px;">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Quản lý điều hành</strong>
                    <span>Chịu sự chỉ đạo, quản lý về tổ chức bộ máy, biên chế của UBND cấp xã; 
                    đồng thời chịu sự hướng dẫn chuyên môn của cơ quan cấp tỉnh.</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== 8 NHIỆM VỤ ========== -->
<section class="intro-section" style="padding-top: 0;">
    <div class="intro-card">
        <div class="intro-card-body">
            <div class="intro-sh">
                <div class="intro-sh-icon" style="background: #fef3c7; color: #d97706;">
                    <i class="fas fa-tasks"></i>
                </div>
                <h2>Nhiệm vụ, quyền hạn của phòng chuyên môn<small>Theo Điều 14, Nghị định 150/2025/NĐ-CP</small></h2>
            </div>
            <div class="intro-two-col" style="gap: 16px;">
                <ul class="intro-list" style="margin: 0;">
                    <li>
                        <i class="fas fa-circle" style="font-size:10px; color:#dc2626; margin-top:8px;"></i>
                        <div>
                            <strong>1. Trình UBND cấp xã</strong>
                            <span>Dự thảo nghị quyết HĐND, quyết định UBND; dự thảo kế hoạch phát triển lĩnh vực; chương trình, biện pháp tổ chức thực hiện nhiệm vụ trên địa bàn.</span>
                        </div>
                    </li>
                    <li>
                        <i class="fas fa-circle" style="font-size:10px; color:#f59e0b; margin-top:8px;"></i>
                        <div>
                            <strong>2. Tổ chức thực hiện pháp luật</strong>
                            <span>Thực hiện văn bản pháp luật, quy hoạch, kế hoạch; thông tin, tuyên truyền, phổ biến pháp luật; theo dõi thi hành pháp luật.</span>
                        </div>
                    </li>
                    <li>
                        <i class="fas fa-circle" style="font-size:10px; color:#2563eb; margin-top:8px;"></i>
                        <div>
                            <strong>3. Tham mưu phát triển kinh tế - xã hội</strong>
                            <span>Phân tích, dự báo; đề xuất giải pháp phát triển ngành, lĩnh vực thuộc phạm vi quản lý.</span>
                        </div>
                    </li>
                    <li>
                        <i class="fas fa-circle" style="font-size:10px; color:#7c3aed; margin-top:8px;"></i>
                        <div>
                            <strong>4. Quản lý tổ chức và nhân sự</strong>
                            <span>Quản lý hoạt động kinh tế tập thể, tư nhân, các hội và tổ chức phi chính phủ trên địa bàn.</span>
                        </div>
                    </li>
                </ul>
                <ul class="intro-list" style="margin: 0;">
                    <li>
                        <i class="fas fa-circle" style="font-size:10px; color:#059669; margin-top:8px;"></i>
                        <div>
                            <strong>5. Quản lý tài chính, tài sản</strong>
                            <span>Quản lý nhà nước về tài chính, ngân sách, tài sản công, giá cả, thống kê thuộc lĩnh vực được giao.</span>
                        </div>
                    </li>
                    <li>
                        <i class="fas fa-circle" style="font-size:10px; color:#db2777; margin-top:8px;"></i>
                        <div>
                            <strong>6. Thanh tra, kiểm tra</strong>
                            <span>Thanh tra, kiểm tra, giải quyết khiếu nại, tố cáo và xử lý vi phạm hành chính trong lĩnh vực quản lý.</span>
                        </div>
                    </li>
                    <li>
                        <i class="fas fa-circle" style="font-size:10px; color:#0891b2; margin-top:8px;"></i>
                        <div>
                            <strong>7. Hợp tác quốc tế</strong>
                            <span>Thực hiện hợp tác quốc tế theo phân công, ủy quyền của UBND cấp xã.</span>
                        </div>
                    </li>
                    <li>
                        <i class="fas fa-circle" style="font-size:10px; color:#ea580c; margin-top:8px;"></i>
                        <div>
                            <strong>8. Báo cáo định kỳ</strong>
                            <span>Báo cáo UBND cấp xã và cơ quan cấp tỉnh về tình hình thực hiện nhiệm vụ thuộc phạm vi quản lý.</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- ===== DIVIDER ===== -->
<div class="intro-divider"><span><i class="fas fa-check-double"></i> &nbsp;Thực tiễn tại Long Hiệp</span></div>

<!-- ========== TRIỂN KHAI + LỊCH SỬ ========== -->
<section class="intro-section">
    <div class="intro-two-col">
        <!-- Trái: Triển khai -->
        <div class="intro-card">
            <div class="intro-card-body">
                <div class="intro-sh">
                    <div class="intro-sh-icon" style="background: #d1fae5; color: #059669;">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <h2>Triển khai tại Long Hiệp<small>Thực tiễn áp dụng NĐ 150</small></h2>
                </div>
                <div class="timeline">
                    <div class="timeline-item">
                        <h4>Rà soát 410 thủ tục hành chính</h4>
                        <p>Công khai và hỗ trợ tra cứu trên hệ thống "một cửa", "một cửa liên thông".</p>
                    </div>
                    <div class="timeline-item">
                        <h4>Cải cách hành chính</h4>
                        <p>Hiện đại hóa "một cửa", đạt chỉ số hài lòng Nhân dân trên 93 điểm. Chuyển đổi số trong điều hành.</p>
                    </div>
                    <div class="timeline-item">
                        <h4>Tổ chức 4 phòng chuyên môn</h4>
                        <p>Văn phòng HĐND & UBND, Phòng Kinh tế, Phòng Văn hóa - Xã hội, Trung tâm Phục vụ Hành chính công.</p>
                    </div>
                    <div class="timeline-item">
                        <h4>Phục vụ Nhân dân</h4>
                        <p>Giải quyết TTHC theo cơ chế "một cửa liên thông", bảo đảm đúng thời hạn, công khai, minh bạch.</p>
                    </div>
                </div>
                <div class="info-box info-box--red" style="margin-top: 24px;">
                    <i class="fas fa-landmark"></i>
                    <div>
                        <strong>Phương châm hoạt động</strong>
                        <span style="font-size: 18px; font-weight: 700; color: #991b1b;">
                            "Trọng dân, gần dân, nghe dân nói và làm dân tin"
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Phải: Lịch sử -->
        <div class="intro-card">
            <div class="intro-card-body">
                <div class="intro-sh">
                    <div class="intro-sh-icon" style="background: #fee2e2; color: #dc2626;">
                        <i class="fas fa-flag"></i>
                    </div>
                    <h2>Lịch sử truyền thống<small>Vùng đất cách mạng "rồng hợp sức"</small></h2>
                </div>
                <p class="intro-text">
                    Long Hiệp là vùng đất giàu truyền thống cách mạng, nơi nhân dân đã cùng nhau đấu tranh giành độc lập, tự do. 
                   Xã anh hùng trong kháng chiến chống Mỹ, phát triển phong trào đồng khởi nổi tiếng.
                </p>
                <ul class="intro-list">
                    <li>
                        <i class="fas fa-medal"></i>
                        <div>
                            <strong>Xã anh hùng lực lượng vũ trang nhân dân</strong>
                            <span>Phong tặng danh hiệu trong kháng chiến chống Mỹ, phong trào đồng khởi tiêu biểu tỉnh Vĩnh Long.</span>
                        </div>
                    </li>
                    <li>
                        <i class="fas fa-place-of-worship"></i>
                        <div>
                            <strong>16 ngôi chùa Khmer</strong>
                            <span>13 chùa Nam tông và 3 chùa Bắc tông — trung tâm tín ngưỡng, bảo tồn bản sắc văn hóa Khmer.</span>
                        </div>
                    </li>
                    <li>
                        <i class="fas fa-handshake"></i>
                        <div>
                            <strong>"Lấy dân làm gốc"</strong>
                            <span>Truyền thống dân vận, thể hiện tinh thần đoàn kết "rồng hợp sức" của cư dân Long Hiệp.</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
<?php include 'quick-actions-toolbar.php'; ?>
</body>
</html>
