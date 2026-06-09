<?php
header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'auth.php';
require_once 'check-approval.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$page_title = "UBND Xã Long Hiệp";
include 'header-menu.php';
?>

<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="footer-style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
/* ============================================
   HOMEPAGE — ENHANCED DESIGN
   ============================================ */

/* --- Hero Section --- */
.hero {
    position: relative;
    background: linear-gradient(135deg, #7f1d1d 0%, #b91c1c 40%, #991b1b 70%, #450a0a 100%);
    color: white;
    overflow: hidden;
}

.hero::before {
    content: '';
    position: absolute;
    top: -200px;
    right: -100px;
    width: 700px;
    height: 700px;
    background: radial-gradient(circle, rgba(212,168,67,0.12) 0%, transparent 65%);
    border-radius: 50%;
    animation: heroGlow 8s ease-in-out infinite;
}

.hero::after {
    content: '';
    position: absolute;
    bottom: -150px;
    left: -80px;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(255,255,255,0.04) 0%, transparent 65%);
    border-radius: 50%;
    animation: heroGlow 10s ease-in-out infinite reverse;
}

@keyframes heroGlow {
    0%, 100% { transform: scale(1) translate(0, 0); opacity: 0.7; }
    50% { transform: scale(1.08) translate(8px, -8px); opacity: 1; }
}

.hero-inner {
    max-width: 1200px;
    margin: 0 auto;
    padding: 80px 24px 70px;
    position: relative;
    z-index: 1;
    text-align: center;
}

.hero-badge {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 10px 26px;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.18);
    border-radius: 9999px;
    color: var(--gold-light);
    font-size: 16px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    margin-bottom: 32px;
    backdrop-filter: blur(12px);
    animation: fadeSlideDown 0.6s ease both;
}

@keyframes fadeSlideDown {
    from { opacity: 0; transform: translateY(-20px) scale(0.96); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.hero h1 {
    font-size: clamp(36px, 5.5vw, 62px);
    font-weight: 900;
    line-height: 1.08;
    margin-bottom: 22px;
    letter-spacing: -0.5px;
    animation: fadeSlideUp 0.7s ease both;
}

.hero h1 .gold {
    color: var(--gold);
    text-shadow: 0 0 40px rgba(212,168,67,0.3);
}

.hero-sub {
    font-size: clamp(18px, 2.2vw, 22px);
    color: rgba(255,255,255,0.85);
    max-width: 720px;
    margin: 0 auto 40px;
    line-height: 1.9;
    animation: fadeSlideUp 0.8s ease both;
}

.hero-stats {
    display: flex;
    justify-content: center;
    gap: 48px;
    flex-wrap: wrap;
    animation: fadeSlideUp 0.9s ease both;
}

.hero-stat {
    text-align: center;
    padding: 16px 20px;
    background: rgba(255,255,255,0.06);
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,0.1);
    min-width: 140px;
    backdrop-filter: blur(8px);
    transition: transform 0.3s ease, background 0.3s ease;
}

.hero-stat:hover {
    transform: translateY(-4px);
    background: rgba(255,255,255,0.1);
}

.hero-stat strong {
    display: block;
    font-size: 38px;
    font-weight: 900;
    color: var(--gold);
    line-height: 1.1;
}

.hero-stat span {
    display: block;
    font-size: 16px;
    color: rgba(255,255,255,0.7);
    margin-top: 6px;
    font-weight: 500;
}

@keyframes fadeSlideUp {
    from { opacity: 0; transform: translateY(24px); }
    to { opacity: 1; transform: translateY(0); }
}

/* --- Section Wrapper --- */
.section {
    max-width: 1200px;
    margin: 0 auto;
    padding: 64px 20px;
}

.section-header {
    text-align: center;
    margin-bottom: 48px;
}

.section-header .eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 20px;
    background: var(--primary-bg);
    color: var(--primary);
    border-radius: 9999px;
    font-size: 15px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    margin-bottom: 18px;
}

.section-header h2 {
    font-size: clamp(28px, 3.5vw, 42px);
    color: var(--dark);
    font-weight: 800;
    line-height: 1.2;
}

.section-header p {
    color: var(--text-light);
    font-size: 19px;
    margin-top: 14px;
    max-width: 650px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.8;
}

/* --- Feature Cards --- */
.features-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
}

.feature-card {
    background: white;
    border-radius: 24px;
    padding: 40px 30px;
    text-align: center;
    text-decoration: none;
    color: inherit;
    border: 1px solid #f0f0f0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04), 0 8px 32px rgba(0,0,0,0.06);
    transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.35s ease, border-color 0.35s ease;
    position: relative;
    overflow: hidden;
}

.feature-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--gradient-primary);
    opacity: 0;
    transition: opacity 0.35s ease;
}

.feature-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.08), 0 24px 60px rgba(0,0,0,0.1);
    border-color: transparent;
}

.feature-card:hover::before { opacity: 1; }

.feature-icon {
    width: 78px;
    height: 78px;
    border-radius: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 22px;
    font-size: 34px;
    transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

.feature-card:hover .feature-icon { transform: scale(1.1) rotate(-3deg); }

.feature-card h3 {
    font-size: 22px;
    color: var(--dark);
    margin-bottom: 12px;
    font-weight: 700;
}

.feature-card p {
    font-size: 18px;
    color: var(--text-light);
    line-height: 1.8;
}

.feature-card .arrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 18px;
    color: var(--primary);
    font-weight: 700;
    font-size: 17px;
    opacity: 0;
    transform: translateX(-12px);
    transition: all 0.3s ease;
}

.feature-card:hover .arrow {
    opacity: 1;
    transform: translateX(0);
}

/* --- Quick Services Banner --- */
.quick-services {
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 50%, #fef2f2 100%);
    border-radius: 24px;
    padding: 48px 40px;
    border: 1px solid #fecaca;
    position: relative;
    overflow: hidden;
}

.quick-services::before {
    content: '';
    position: absolute;
    top: -60px;
    right: -60px;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(220,38,38,0.08) 0%, transparent 70%);
    border-radius: 50%;
}

.qs-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-top: 32px;
}

.qs-item {
    background: white;
    border-radius: 18px;
    padding: 28px 20px;
    text-align: center;
    border: 1px solid #f5f5f5;
    text-decoration: none;
    color: inherit;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

.qs-item:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 32px rgba(220,38,38,0.1);
    border-color: #fecaca;
}

.qs-item .qs-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 14px;
    font-size: 26px;
    transition: transform 0.3s ease;
}

.qs-item:hover .qs-icon { transform: scale(1.1); }

.qs-item h4 {
    font-size: 18px;
    color: var(--dark);
    font-weight: 700;
    margin-bottom: 6px;
}

.qs-item p {
    font-size: 16px;
    color: var(--text-light);
    line-height: 1.6;
}

/* --- About Section --- */
.about-section {
    background: white;
    border-radius: 24px;
    border: 1px solid #f0f0f0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04), 0 8px 32px rgba(0,0,0,0.06);
    overflow: hidden;
    margin-bottom: 0;
}

.about-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
    align-items: stretch;
}

.about-content {
    padding: 52px 48px;
}

.about-content h2 {
    font-size: 34px;
    color: var(--dark);
    margin-bottom: 18px;
    font-weight: 800;
    line-height: 1.2;
}

.about-content h2 span { color: var(--primary); }

.about-content > p {
    color: var(--text-light);
    font-size: 19px;
    line-height: 1.9;
    margin-bottom: 28px;
}

.about-list {
    list-style: none;
    padding: 0;
}

.about-list li {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 18px 0;
    border-bottom: 1px solid #f5f5f5;
}

.about-list li:last-child { border-bottom: none; }

.about-list li i {
    color: var(--primary);
    font-size: 20px;
    margin-top: 4px;
    flex-shrink: 0;
}

.about-list li strong {
    color: var(--dark);
    display: block;
    margin-bottom: 4px;
    font-size: 19px;
    font-weight: 700;
}

.about-list li span {
    color: var(--text-light);
    font-size: 18px;
    line-height: 1.7;
}

.about-visual {
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    min-height: 420px;
}

.about-visual img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.about-visual .float-card {
    position: absolute;
    bottom: 28px;
    left: 28px;
    background: white;
    padding: 20px 28px;
    border-radius: 18px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.12);
    display: flex;
    align-items: center;
    gap: 16px;
    animation: floatBounce 3s ease-in-out infinite;
}

@keyframes floatBounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}

.about-visual .float-card .icon {
    width: 54px;
    height: 54px;
    border-radius: 16px;
    background: var(--primary-bg);
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.about-visual .float-card .text strong {
    display: block;
    font-size: 24px;
    color: var(--dark);
    font-weight: 800;
}

.about-visual .float-card .text span {
    font-size: 16px;
    color: var(--text-light);
}

/* --- Department Showcase --- */
.dept-showcase {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
}

.dept-show-card {
    background: white;
    border-radius: 24px;
    padding: 40px 28px;
    text-align: center;
    border: 1px solid #f0f0f0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04), 0 8px 32px rgba(0,0,0,0.06);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.dept-show-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    opacity: 0;
    transition: opacity 0.4s ease;
}

.dept-show-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 16px 48px rgba(0,0,0,0.1);
    border-color: transparent;
}

.dept-show-card:hover::before { opacity: 1; }

.dept-show-card .ds-icon {
    width: 80px;
    height: 80px;
    border-radius: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 22px;
    font-size: 36px;
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.dept-show-card:hover .ds-icon { transform: scale(1.1) rotate(-3deg); }

.dept-show-card h4 {
    font-size: 22px;
    color: var(--dark);
    margin-bottom: 10px;
    font-weight: 700;
}

.dept-show-card > p {
    font-size: 18px;
    color: var(--text-light);
    line-height: 1.7;
    margin-bottom: 18px;
}

.dept-show-card ul {
    list-style: none;
    padding: 0;
    text-align: left;
}

.dept-show-card ul li {
    padding: 10px 0;
    border-bottom: 1px solid #f5f5f5;
    font-size: 17px;
    color: #475569;
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.dept-show-card ul li:last-child { border-bottom: none; }

.dept-show-card ul li i {
    color: #10b981;
    font-size: 16px;
    margin-top: 4px;
    flex-shrink: 0;
}

/* --- CTA Section --- */
.cta-section {
    background: linear-gradient(135deg, #b91c1c 0%, #7f1d1d 50%, #450a0a 100%);
    border-radius: 24px;
    padding: 64px 52px;
    text-align: center;
    color: white;
    position: relative;
    overflow: hidden;
}

.cta-section::before {
    content: '';
    position: absolute;
    top: -150px;
    right: -150px;
    width: 450px;
    height: 450px;
    background: radial-gradient(circle, rgba(212,168,67,0.15) 0%, transparent 65%);
    border-radius: 50%;
}

.cta-section::after {
    content: '';
    position: absolute;
    bottom: -100px;
    left: -100px;
    width: 350px;
    height: 350px;
    background: radial-gradient(circle, rgba(255,255,255,0.04) 0%, transparent 65%);
    border-radius: 50%;
}

.cta-section h2 {
    font-size: clamp(28px, 3.5vw, 40px);
    margin-bottom: 18px;
    position: relative;
    z-index: 1;
    font-weight: 800;
}

.cta-section > p {
    font-size: 20px;
    opacity: 0.9;
    max-width: 680px;
    margin: 0 auto 36px;
    line-height: 1.9;
    position: relative;
    z-index: 1;
}

.cta-buttons {
    display: flex;
    justify-content: center;
    gap: 18px;
    flex-wrap: wrap;
    position: relative;
    z-index: 1;
}

.btn-hero {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 16px 36px;
    border-radius: 9999px;
    font-weight: 700;
    font-size: 18px;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-hero--white {
    background: white;
    color: var(--primary);
    box-shadow: 0 8px 28px rgba(0,0,0,0.15);
}

.btn-hero--white:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.2);
}

.btn-hero--outline {
    background: transparent;
    color: white;
    border: 2px solid rgba(255,255,255,0.35);
}

.btn-hero--outline:hover {
    background: rgba(255,255,255,0.1);
    border-color: white;
    transform: translateY(-4px);
}

/* --- Statistics Section --- */
.stats-banner {
    background: white;
    border-radius: 24px;
    border: 1px solid #f0f0f0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04), 0 8px 32px rgba(0,0,0,0.06);
    padding: 52px 48px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    text-align: center;
}

.stat-item {
    padding: 30px 16px;
    border-radius: 18px;
    transition: all 0.3s ease;
    border: 1px solid #f5f5f5;
}

.stat-item:hover {
    background: #fef2f2;
    border-color: #fecaca;
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(220,38,38,0.08);
}

.stat-item .stat-icon {
    width: 72px;
    height: 72px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 32px;
    transition: transform 0.3s ease;
}

.stat-item:hover .stat-icon { transform: scale(1.1); }

.stat-item strong {
    display: block;
    font-size: 42px;
    font-weight: 900;
    line-height: 1.1;
    margin-bottom: 10px;
}

.stat-item span {
    display: block;
    font-size: 18px;
    color: #64748b;
    font-weight: 600;
    line-height: 1.4;
}

/* --- Contact Grid --- */
.contact-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

.contact-card {
    background: white;
    padding: 36px 24px;
    border-radius: 20px;
    text-align: center;
    border: 1px solid #f0f0f0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.contact-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.08);
}

.contact-card .contact-icon {
    width: 64px;
    height: 64px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 18px;
    font-size: 26px;
    transition: transform 0.3s ease;
}

.contact-card:hover .contact-icon { transform: scale(1.08); }

.contact-card h4 {
    color: var(--dark);
    font-size: 19px;
    margin-bottom: 8px;
    font-weight: 700;
}

.contact-card p {
    color: var(--text-light);
    font-size: 17px;
    line-height: 1.7;
}

/* --- News Preview --- */
.news-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
}

.news-card {
    background: white;
    border-radius: 20px;
    border: 1px solid #f0f0f0;
    overflow: hidden;
    text-decoration: none;
    color: inherit;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

.news-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.1);
    border-color: transparent;
}

.news-card .news-img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.news-card:hover .news-img { transform: scale(1.05); }

.news-card .news-img-wrap {
    overflow: hidden;
    position: relative;
}

.news-card .news-tag {
    position: absolute;
    top: 14px;
    left: 14px;
    padding: 6px 14px;
    border-radius: 9999px;
    font-size: 14px;
    font-weight: 700;
    color: white;
}

.news-card .news-body {
    padding: 24px;
}

.news-card .news-date {
    font-size: 15px;
    color: var(--text-muted);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.news-card h4 {
    font-size: 19px;
    color: var(--dark);
    font-weight: 700;
    margin-bottom: 10px;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.news-card p {
    font-size: 17px;
    color: var(--text-light);
    line-height: 1.7;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* --- Responsive --- */
@media (max-width: 1024px) {
    .features-grid { grid-template-columns: repeat(2, 1fr); }
    .about-grid { grid-template-columns: 1fr; }
    .about-visual { display: none; }
    .dept-showcase { grid-template-columns: repeat(2, 1fr); }
    .qs-grid { grid-template-columns: repeat(2, 1fr); }
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .news-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 768px) {
    .hero-inner { padding: 56px 20px 48px; }
    .hero-stats { gap: 16px; }
    .hero-stat { min-width: 120px; padding: 14px 16px; }
    .hero-stat strong { font-size: 30px; }
    .features-grid { grid-template-columns: 1fr; }
    .about-content { padding: 36px 24px; }
    .cta-section { padding: 48px 24px; }
    .contact-grid { grid-template-columns: repeat(2, 1fr); }
    .dept-showcase { grid-template-columns: 1fr; }
    .qs-grid { grid-template-columns: 1fr; }
    .stats-grid { grid-template-columns: 1fr 1fr; }
    .news-grid { grid-template-columns: 1fr; }
    .section { padding: 48px 16px; }
    .stats-banner { padding: 36px 24px; }
}

@media (max-width: 480px) {
    .contact-grid { grid-template-columns: 1fr; }
    .cta-buttons { flex-direction: column; align-items: center; }
    .btn-hero { width: 100%; justify-content: center; }
    .stats-grid { grid-template-columns: 1fr; }
}
</style>

<!-- ========== HERO ========== -->
<section class="hero">
    <div class="hero-inner">
        <div class="hero-badge">
            <i class="fas fa-landmark"></i>
            Cổng thông tin điện tử chính thức
        </div>
        <h1>
            UBND Xã <span class="gold">Long Hiệp</span>
        </h1>
        <p class="hero-sub">
            Ủy ban Nhân dân xã Long Hiệp, tỉnh Vĩnh Long — nơi tiếp nhận và giải quyết 
            mọi thủ tục hành chính, lắng nghe phản ánh của Nhân dân, phục vụ cộng đồng 
            ngày một tốt hơn.
        </p>
        <div class="hero-stats">
            <div class="hero-stat">
                <strong>6.516</strong>
                <span>Hecta diện tích</span>
            </div>
            <div class="hero-stat">
                <strong>30.272</strong>
                <span>Dân số (người)</span>
            </div>
            <div class="hero-stat">
                <strong>22</strong>
                <span>Ấp</span>
            </div>
            <div class="hero-stat">
                <strong>410</strong>
                <span>Thủ tục hành chính</span>
            </div>
        </div>
    </div>
</section>

<!-- ========== QUICK SERVICES ========== -->
<section class="section">
    <div class="quick-services">
        <div class="section-header" style="margin-bottom: 0;">
            <div class="eyebrow" style="background: #fee2e2; color: #dc2626;"><i class="fas fa-bolt"></i> Dịch vụ nhanh</div>
            <h2>Cần gì — Gọi ngay</h2>
            <p>Các dịch vụ được người dân sử dụng nhiều nhất tại UBND xã Long Hiệp</p>
        </div>
        <div class="qs-grid">
            <a href="phong-hdnn.php" class="qs-item">
                <div class="qs-icon" style="background: #fee2e2; color: #dc2626;">
                    <i class="fas fa-file-alt"></i>
                </div>
                <h4>Nộp hồ sơ</h4>
                <p>Tiếp nhận, thụ lý hồ sơ trực tuyến tại Văn phòng HĐND & UBND</p>
            </a>
            <a href="lien-he.php" class="qs-item">
                <div class="qs-icon" style="background: #dbeafe; color: #2563eb;">
                    <i class="fas fa-comments"></i>
                </div>
                <h4>Phản ánh kiến nghị</h4>
                <p>Gửi ý kiến, phản ánh trực tiếp đến UBND xã để được xử lý</p>
            </a>
            <a href="danh-ba-dien-thoai.php" class="qs-item">
                <div class="qs-icon" style="background: #d1fae5; color: #059669;">
                    <i class="fas fa-phone-alt"></i>
                </div>
                <h4>Liên hệ cán bộ</h4>
                <p>Tra cứu danh bạ, số điện thoại cán bộ chuyên môn phụ trách</p>
            </a>
            <a href="tin-tuc.php" class="qs-item">
                <div class="qs-icon" style="background: #fef3c7; color: #d97706;">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <h4>Tin tức — Thông báo</h4>
                <p>Theo dõi thông báo mới nhất từ UBND xã và các cơ quan cấp huyện</p>
            </a>
        </div>
    </div>
</section>

<!-- ========== FEATURE LINKS ========== -->
<section class="section" style="padding-top: 0;">
    <div class="section-header">
        <div class="eyebrow"><i class="fas fa-th-large"></i> Chức năng chính</div>
        <h2>Khám phá thông tin</h2>
        <p>Truy cập nhanh các chức năng và thông tin quan trọng của UBND xã Long Hiệp</p>
    </div>

    <div class="features-grid">
        <a href="gioi-thieu.php" class="feature-card">
            <div class="feature-icon" style="background: #ecfdf5; color: #059669;">
                <i class="fas fa-info-circle"></i>
            </div>
            <h3>Giới thiệu</h3>
            <p>Tổng quan về xã Long Hiệp — lịch sử, cảnh quan thiên nhiên, Nghị định 150/NĐ-CP và 3 phòng ban mới.</p>
            <span class="arrow">Xem thêm <i class="fas fa-arrow-right"></i></span>
        </a>

        <a href="tin-tuc.php" class="feature-card">
            <div class="feature-icon" style="background: #fef3c7; color: #d97706;">
                <i class="fas fa-newspaper"></i>
            </div>
            <h3>Tin tức — Văn bản</h3>
            <p>Theo dõi hoạt động chỉ đạo, tuyên truyền, thông báo chính thức và văn bản quy phạm pháp luật.</p>
            <span class="arrow">Xem thêm <i class="fas fa-arrow-right"></i></span>
        </a>

        <a href="lanh-dao.php" class="feature-card">
            <div class="feature-icon" style="background: #ede9fe; color: #7c3aed;">
                <i class="fas fa-users"></i>
            </div>
            <h3>Tổ chức nhân sự</h3>
            <p>Thông tin lãnh đạo UBND xã, danh bạ liên hệ và cơ cấu tổ chức bộ máy hành chính.</p>
            <span class="arrow">Xem thêm <i class="fas fa-arrow-right"></i></span>
        </a>

        <a href="phong-ban.php" class="feature-card">
            <div class="feature-icon" style="background: #fce7f3; color: #db2777;">
                <i class="fas fa-building"></i>
            </div>
            <h3>Phòng ban</h3>
            <p>3 phòng chuyên môn: Văn phòng HĐND & UBND, Phòng Kinh tế, Phòng Văn hóa — Xã hội.</p>
            <span class="arrow">Xem thêm <i class="fas fa-arrow-right"></i></span>
        </a>

        <a href="lien-he.php" class="feature-card">
            <div class="feature-icon" style="background: #fee2e2; color: #dc2626;">
                <i class="fas fa-envelope"></i>
            </div>
            <h3>Liên hệ — Phản ánh</h3>
            <p>Gửi kiến nghị, câu hỏi hoặc phản ánh trực tuyến để UBND xã tiếp nhận và xử lý kịp thời.</p>
            <span class="arrow">Xem thêm <i class="fas fa-arrow-right"></i></span>
        </a>

        <a href="danh-ba-dien-thoai.php" class="feature-card">
            <div class="feature-icon" style="background: #dbeafe; color: #2563eb;">
                <i class="fas fa-phone-alt"></i>
            </div>
            <h3>Danh bạ liên hệ</h3>
            <p>Tra cứu thông tin liên hệ cán bộ, công chức và đầu mối chuyên môn theo từng phòng ban.</p>
            <span class="arrow">Xem thêm <i class="fas fa-arrow-right"></i></span>
        </a>
    </div>
</section>

<!-- ========== ABOUT ========== -->
<section class="section" style="padding-top: 0;">
    <div class="about-section">
        <div class="about-grid">
            <div class="about-content">
                <h2>Về <span>Long Hiệp</span></h2>
                <p>
                    Long Hiệp là xã nông nghiệp trù phú nằm trong lưu vực sông Mê Kông, 
                    tỉnh Vĩnh Long. Với diện tích 6.516,77 ha và 22 ấp, xã là nơi sinh sống 
                    của hơn 30.000 Nhân dân, trong đó đồng bào Khmer chiếm đa số, 
                    tạo nên bản sắc văn hóa đặc trưng với 16 ngôi chùa Khmer.
                </p>
                <ul class="about-list">
                    <li>
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <strong>Nghị định 150/2025/NĐ-CP</strong>
                            <span>Sắp xếp lại 3 phòng ban chuyên môn, công khai 410 thủ tục hành chính, 
                            giúp Nhân dân dễ hiểu, dễ tiếp cận dịch vụ công.</span>
                        </div>
                    </li>
                    <li>
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <strong>Ch chuyển đổi số toàn diện</strong>
                            <span>Ứng dụng công nghệ thông tin trong quản lý điều hành, nâng chỉ số hài lòng 
                            Nhân dân lên trên 93 điểm —top đầu tỉnh Vĩnh Long.</span>
                        </div>
                    </li>
                    <li>
                        <i class="fas fa-check-circle"></i>
                        <div>
                            <strong>Vùng nông nghiệp trọng điểm</strong>
                            <span>Hệ thống thủy lợi phục vụ sản xuất lúa gạo, cây ăn trái, nuôi trồng thủy sản — 
                            thế mạnh kinh tế nông nghiệp địa phương.</span>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="about-visual">
                <img src="images/ubnd-longhiep-banner.png" alt="Long Hiệp">
                <div class="float-card">
                    <div class="icon"><i class="fas fa-om"></i></div>
                    <div class="text">
                        <strong>16</strong>
                        <span>Chùa Khmer</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========== STATISTICS ========== -->
<section class="section" style="padding-top: 0;">
    <div class="stats-banner">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-icon" style="background: #fee2e2; color: #dc2626;">
                    <i class="fas fa-users"></i>
                </div>
                <strong style="color: #dc2626;">30.272</strong>
                <span>Dân số xã Long Hiệp</span>
            </div>
            <div class="stat-item">
                <div class="stat-icon" style="background: #dbeafe; color: #2563eb;">
                    <i class="fas fa-map-marked-alt"></i>
                </div>
                <strong style="color: #2563eb;">6.516 ha</strong>
                <span>Diện tích tự nhiên</span>
            </div>
            <div class="stat-item">
                <div class="stat-icon" style="background: #d1fae5; color: #059669;">
                    <i class="fas fa-file-alt"></i>
                </div>
                <strong style="color: #059669;">410</strong>
                <span>Thủ tục hành chính</span>
            </div>
            <div class="stat-item">
                <div class="stat-icon" style="background: #fef3c7; color: #d97706;">
                    <i class="fas fa-smile"></i>
                </div>
                <strong style="color: #d97706;">93+</strong>
                <span>Điểm hài lòng Nhân dân</span>
            </div>
        </div>
    </div>
</section>

<!-- ========== CTA ========== -->
<section class="section" style="padding-top: 0;">
    <div class="cta-section">
        <h2><i class="fas fa-hand-holding-heart"></i> Sẵn sàng phục vụ Nhân dân</h2>
        <p>
            UBND xã Long Hiệp luôn lắng nghe và tiếp nhận mọi kiến nghị, phản ánh 
            của Nhân dân. Đội ngũ cán bộ, công chức sẵn sàng hỗ trợ Nhân dân 
            giải quyết mọi thủ tục nhanh chóng, minh bạch.
        </p>
        <div class="cta-buttons">
            <a href="lien-he.php" class="btn-hero btn-hero--white">
                <i class="fas fa-paper-plane"></i> Gửi phản ánh
            </a>
            <a href="gioi-thieu.php" class="btn-hero btn-hero--outline">
                <i class="fas fa-info-circle"></i> Tìm hiểu thêm
            </a>
        </div>
    </div>
</section>

<!-- ========== CONTACT ========== -->
<section class="section" style="padding-top: 0;">
    <div class="section-header">
        <div class="eyebrow"><i class="fas fa-headset"></i> Hỗ trợ</div>
        <h2>Thông tin liên hệ</h2>
        <p>Liên hệ trực tiếp với UBND xã Long Hiệp để được hỗ trợ nhanh nhất</p>
    </div>

    <div class="contact-grid">
        <div class="contact-card">
            <div class="contact-icon" style="background: #fee2e2; color: #dc2626;">
                <i class="fas fa-map-marker-alt"></i>
            </div>
            <h4>Địa chỉ</h4>
            <p>UBND xã Long Hiệp,<br>Huyện Long Hồ, tỉnh Vĩnh Long</p>
        </div>
        <div class="contact-card">
            <div class="contact-icon" style="background: #dbeafe; color: #2563eb;">
                <i class="fas fa-phone"></i>
            </div>
            <h4>Điện thoại</h4>
            <p>0270.3.876.123</p>
        </div>
        <div class="contact-card">
            <div class="contact-icon" style="background: #e0e7ff; color: #4f46e5;">
                <i class="fas fa-envelope"></i>
            </div>
            <h4>Email</h4>
            <p>ubndlonghiep@vinhlong.gov.vn</p>
        </div>
        <div class="contact-card">
            <div class="contact-icon" style="background: #d1fae5; color: #059669;">
                <i class="fas fa-clock"></i>
            </div>
            <h4>Giờ làm việc</h4>
            <p>Thứ 2 — Thứ 6<br>7:30 — 16:30</p>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
<?php include 'quick-actions-toolbar.php'; ?>
</body>
</html>
