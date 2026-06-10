<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dịch vụ công - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        :root {
            --primary: #c0392b;
            --primary-dark: #96281b;
            --primary-light: #e74c3c;
            --secondary: #2c3e50;
            --accent: #f39c12;
            --accent-light: #f1c40f;
            --success: #27ae60;
            --info: #2980b9;
            --warning: #e67e22;
            --danger: #c0392b;
            --gradient-primary: linear-gradient(135deg, #c0392b 0%, #8e44ad 100%);
            --gradient-secondary: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            --gradient-accent: linear-gradient(135deg, #f39c12 0%, #e74c3c 100%);
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.08);
            --shadow-md: 0 4px 16px rgba(0,0,0,0.12);
            --shadow-lg: 0 8px 32px rgba(0,0,0,0.15);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.7;
            color: #333;
            background: #f5f6fa;
        }

        .dv-page-wrapper {
            min-height: 100vh;
        }

        /* Page Header */
        .dv-page-header {
            background: var(--gradient-primary);
            padding: 60px 0 50px;
            position: relative;
            overflow: hidden;
        }

        .dv-page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="rgba(255,255,255,0.05)" d="M0,96L48,112C96,128,192,160,288,186.7C384,213,480,235,576,213.3C672,192,768,128,864,128C960,128,1056,192,1152,208C1248,224,1344,192,1392,176L1440,160L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') no-repeat bottom;
            background-size: cover;
        }

        .dv-header-content {
            position: relative;
            z-index: 1;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            text-align: center;
        }

        .dv-header-icon {
            width: 90px;
            height: 90px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255,255,255,0.2);
        }

        .dv-header-icon i {
            font-size: 40px;
            color: #fff;
        }

        .dv-page-header h1 {
            font-size: 2.2rem;
            color: #fff;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .dv-page-header p {
            color: rgba(255,255,255,0.9);
            font-size: 1.1rem;
            max-width: 700px;
            margin: 0 auto;
        }

        .dv-breadcrumb {
            background: rgba(255,255,255,0.1);
            padding: 10px 20px;
            border-radius: 30px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
            backdrop-filter: blur(5px);
        }

        .dv-breadcrumb a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .dv-breadcrumb a:hover {
            color: #fff;
        }

        .dv-breadcrumb span {
            color: var(--accent-light);
            font-weight: 500;
        }

        .dv-breadcrumb i {
            color: rgba(255,255,255,0.5);
            font-size: 0.7rem;
        }

        /* Main Content */
        .dv-main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /* Section Styles */
        .dv-section {
            margin-bottom: 40px;
        }

        .dv-section-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--primary);
        }

        .dv-section-icon {
            width: 50px;
            height: 50px;
            background: var(--gradient-primary);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .dv-section-icon i {
            color: #fff;
            font-size: 22px;
        }

        .dv-section-header h2 {
            font-size: 1.5rem;
            color: var(--secondary);
            font-weight: 700;
        }

        .dv-section-header .count-badge {
            background: var(--accent);
            color: #fff;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-left: auto;
        }

        /* Statistics Cards */
        .dv-stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }

        .dv-stat-card {
            background: #fff;
            border-radius: var(--radius-md);
            padding: 25px 20px;
            text-align: center;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            border-top: 4px solid var(--primary);
        }

        .dv-stat-card:nth-child(2) { border-top-color: var(--info); }
        .dv-stat-card:nth-child(3) { border-top-color: var(--success); }
        .dv-stat-card:nth-child(4) { border-top-color: var(--accent); }

        .dv-stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .dv-stat-card .stat-icon {
            width: 60px;
            height: 60px;
            background: rgba(192,57,43,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }

        .dv-stat-card:nth-child(2) .stat-icon { background: rgba(41,128,185,0.1); }
        .dv-stat-card:nth-child(3) .stat-icon { background: rgba(39,174,96,0.1); }
        .dv-stat-card:nth-child(4) .stat-icon { background: rgba(243,156,18,0.1); }

        .dv-stat-card .stat-icon i {
            font-size: 26px;
            color: var(--primary);
        }

        .dv-stat-card:nth-child(2) .stat-icon i { color: var(--info); }
        .dv-stat-card:nth-child(3) .stat-icon i { color: var(--success); }
        .dv-stat-card:nth-child(4) .stat-icon i { color: var(--accent); }

        .dv-stat-card .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--secondary);
            margin-bottom: 5px;
        }

        .dv-stat-card .stat-label {
            font-size: 0.9rem;
            color: #777;
        }

        /* Procedures Section */
        .dv-procedures-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .dv-tab-btn {
            padding: 10px 20px;
            border: none;
            background: #fff;
            color: var(--secondary);
            border-radius: 30px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .dv-tab-btn:hover {
            background: var(--primary);
            color: #fff;
        }

        .dv-tab-btn.active {
            background: var(--gradient-primary);
            color: #fff;
        }

        .dv-tab-btn i {
            font-size: 1rem;
        }

        .dv-tab-content {
            display: none;
        }

        .dv-tab-content.active {
            display: block;
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .dv-category-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--secondary);
            margin: 20px 0 15px;
            padding: 10px 15px;
            background: #f8f9fa;
            border-radius: var(--radius-sm);
            border-left: 4px solid var(--primary);
        }

        .dv-category-title i {
            color: var(--primary);
        }

        .dv-procedure-list {
            list-style: none;
        }

        .dv-procedure-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            background: #fff;
            border-radius: var(--radius-sm);
            margin-bottom: 8px;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            border-left: 3px solid transparent;
        }

        .dv-procedure-item:hover {
            border-left-color: var(--primary);
            transform: translateX(5px);
            box-shadow: var(--shadow-md);
        }

        .dv-procedure-item .proc-number {
            width: 30px;
            height: 30px;
            background: var(--gradient-primary);
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 600;
            flex-shrink: 0;
        }

        .dv-procedure-item .proc-name {
            flex: 1;
            font-size: 0.95rem;
            color: #555;
        }

        .dv-procedure-item .proc-time {
            font-size: 0.8rem;
            color: var(--info);
            background: rgba(41,128,185,0.1);
            padding: 3px 10px;
            border-radius: 15px;
            white-space: nowrap;
        }

        /* Guide Section */
        .dv-guide-steps {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            margin-top: 20px;
        }

        .dv-guide-step {
            text-align: center;
            padding: 30px 20px;
            background: #fff;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            position: relative;
            transition: var(--transition);
        }

        .dv-guide-step:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .dv-guide-step::after {
            content: '\f105';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: -18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            font-size: 1.5rem;
            z-index: 1;
        }

        .dv-guide-step:last-child::after {
            display: none;
        }

        .dv-step-number {
            width: 50px;
            height: 50px;
            background: var(--gradient-primary);
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            font-weight: 700;
            margin: 0 auto 15px;
        }

        .dv-step-icon {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 12px;
        }

        .dv-guide-step h4 {
            font-size: 1.05rem;
            color: var(--secondary);
            margin-bottom: 8px;
            font-weight: 600;
        }

        .dv-guide-step p {
            font-size: 0.85rem;
            color: #777;
            line-height: 1.5;
        }

        /* Time Table */
        .dv-time-table-wrapper {
            background: #fff;
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .dv-time-table {
            width: 100%;
            border-collapse: collapse;
        }

        .dv-time-table thead {
            background: var(--gradient-primary);
        }

        .dv-time-table th {
            color: #fff;
            padding: 15px 20px;
            text-align: left;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .dv-time-table td {
            padding: 14px 20px;
            border-bottom: 1px solid #eee;
            font-size: 0.9rem;
        }

        .dv-time-table tbody tr {
            transition: var(--transition);
        }

        .dv-time-table tbody tr:hover {
            background: rgba(192,57,43,0.03);
        }

        .dv-time-table tbody tr:last-child td {
            border-bottom: none;
        }

        .dv-time-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .dv-time-badge.fast {
            background: rgba(39,174,96,0.1);
            color: var(--success);
        }

        .dv-time-badge.medium {
            background: rgba(243,156,18,0.1);
            color: var(--warning);
        }

        .dv-time-badge.slow {
            background: rgba(192,57,43,0.1);
            color: var(--danger);
        }

        /* Contact Section */
        .dv-contact-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .dv-contact-card {
            background: #fff;
            border-radius: var(--radius-md);
            padding: 30px 25px;
            text-align: center;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .dv-contact-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }

        .dv-contact-icon {
            width: 70px;
            height: 70px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
        }

        .dv-contact-icon i {
            color: #fff;
            font-size: 28px;
        }

        .dv-contact-card h4 {
            font-size: 1.1rem;
            color: var(--secondary);
            margin-bottom: 10px;
            font-weight: 600;
        }

        .dv-contact-card p {
            color: #777;
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .dv-contact-card .contact-highlight {
            display: inline-block;
            margin-top: 12px;
            padding: 8px 20px;
            background: rgba(192,57,43,0.08);
            color: var(--primary);
            border-radius: 25px;
            font-weight: 600;
            font-size: 1rem;
        }

        .dv-contact-card a {
            color: var(--primary);
            text-decoration: none;
            transition: var(--transition);
        }

        .dv-contact-card a:hover {
            color: var(--primary-dark);
        }

        /* Important Notice */
        .dv-notice-box {
            background: linear-gradient(135deg, rgba(41,128,185,0.05) 0%, rgba(39,174,96,0.05) 100%);
            border: 1px solid rgba(41,128,185,0.2);
            border-radius: var(--radius-md);
            padding: 25px 30px;
            margin-top: 30px;
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }

        .dv-notice-box .notice-icon {
            width: 45px;
            height: 45px;
            background: var(--info);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .dv-notice-box .notice-icon i {
            color: #fff;
            font-size: 20px;
        }

        .dv-notice-box h4 {
            color: var(--secondary);
            margin-bottom: 5px;
            font-size: 1rem;
        }

        .dv-notice-box p {
            color: #666;
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .dv-stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .dv-guide-steps {
                grid-template-columns: repeat(2, 1fr);
            }
            .dv-guide-step:nth-child(2)::after {
                display: none;
            }
            .dv-contact-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .dv-page-header h1 {
                font-size: 1.6rem;
            }
            .dv-page-header p {
                font-size: 0.95rem;
            }
            .dv-stats-grid {
                grid-template-columns: 1fr 1fr;
                gap: 12px;
            }
            .dv-guide-steps {
                grid-template-columns: 1fr;
            }
            .dv-guide-step::after {
                display: none;
            }
            .dv-procedures-tabs {
                justify-content: center;
            }
            .dv-time-table-wrapper {
                overflow-x: auto;
            }
            .dv-time-table {
                min-width: 600px;
            }
            .dv-section-header h2 {
                font-size: 1.2rem;
            }
            .dv-breadcrumb {
                font-size: 0.8rem;
            }
        }

        @media (max-width: 480px) {
            .dv-stats-grid {
                grid-template-columns: 1fr;
            }
            .dv-stat-card {
                padding: 18px 15px;
            }
            .dv-tab-btn {
                padding: 8px 14px;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'header-menu.php'; ?>

    <div class="dv-page-wrapper">
        <!-- Page Header -->
        <section class="dv-page-header">
            <div class="dv-header-content">
                <div class="dv-header-icon">
                    <i class="fas fa-landmark"></i>
                </div>
                <h1>Dịch vụ công trực tuyến</h1>
                <p>Cổng thông tin thủ tục hành chính - UBND Xã Long Hiệp, Tỉnh Vĩnh Long</p>
                <div class="dv-breadcrumb">
                    <a href="index.php"><i class="fas fa-home"></i> Trang chủ</a>
                    <i class="fas fa-chevron-right"></i>
                    <span>Dịch vụ công</span>
                </div>
            </div>
        </section>

        <!-- Main Content -->
        <div class="dv-main-content">

            <!-- Statistics -->
            <div class="dv-stats-grid">
                <div class="dv-stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-number">410</div>
                    <div class="stat-label">Thủ tục hành chính</div>
                </div>
                <div class="dv-stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <div class="stat-number">12</div>
                    <div class="stat-label">Lĩnh vực chuyên môn</div>
                </div>
                <div class="dv-stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-number">98%</div>
                    <div class="stat-label">Tỷ lệ đúng hạn</div>
                </div>
                <div class="dv-stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-smile"></i>
                    </div>
                    <div class="stat-number">95%</div>
                    <div class="stat-label">Hài lòng người dân</div>
                </div>
            </div>

            <!-- Thủ tục hành chính -->
            <section class="dv-section">
                <div class="dv-section-header">
                    <div class="dv-section-icon">
                        <i class="fas fa-list-check"></i>
                    </div>
                    <h2>Thủ tục hành chính</h2>
                    <span class="count-badge">410 thủ tục</span>
                </div>

                <div class="dv-procedures-tabs">
                    <button class="dv-tab-btn active" onclick="switchTab('tab-all')">
                        <i class="fas fa-th-large"></i> Tất cả
                    </button>
                    <button class="dv-tab-btn" onclick="switchTab('tab-tu-phap')">
                        <i class="fas fa-gavel"></i> Tư pháp - Hộ tịch
                    </button>
                    <button class="dv-tab-btn" onclick="switchTab('tab-dia-chinh')">
                        <i class="fas fa-home"></i> Địa chính - Nhà đất
                    </button>
                    <button class="dv-tab-btn" onclick="switchTab('tab-thuong-mai')">
                        <i class="fas fa-store"></i> Thương mại - Đầu tư
                    </button>
                    <button class="dv-tab-btn" onclick="switchTab('tab-xa-hoi')">
                        <i class="fas fa-users"></i> Xã hội
                    </button>
                </div>

                <!-- Tất cả -->
                <div class="dv-tab-content active" id="tab-all">
                    <div class="dv-category-title">
                        <i class="fas fa-gavel"></i> Tư pháp - Hộ tịch (120 thủ tục)
                    </div>
                    <ul class="dv-procedure-list">
                        <li class="dv-procedure-item">
                            <span class="proc-number">1</span>
                            <span class="proc-name">Cấp mới Chứng minh nhân dân (CMND)</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 7 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">2</span>
                            <span class="proc-name">Cấp mới Căn cước công dân (CCCD)</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 15 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">3</span>
                            <span class="proc-name">Đăng ký kết hôn</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 3 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">4</span>
                            <span class="proc-name">Đăng ký khai sinh</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 1 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">5</span>
                            <span class="proc-name">Đăng ký khai tử</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 1 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">6</span>
                            <span class="proc-name">Cấp bản sao hộ tịch</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 1 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">7</span>
                            <span class="proc-name">Cải chính hộ tịch</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 5 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">8</span>
                            <span class="proc-name">Đăng ký giám hộ</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 3 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">9</span>
                            <span class="proc-name">Cấp giấy xác nhận tình trạng hôn nhân</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 3 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">10</span>
                            <span class="proc-name">Thay đổi, cải chính hộ tịch</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 5 ngày</span>
                        </li>
                    </ul>

                    <div class="dv-category-title">
                        <i class="fas fa-home"></i> Địa chính - Nhà đất (130 thủ tục)
                    </div>
                    <ul class="dv-procedure-list">
                        <li class="dv-procedure-item">
                            <span class="proc-number">11</span>
                            <span class="proc-name">Cấp giấy chứng nhận quyền sử dụng đất</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 30 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">12</span>
                            <span class="proc-name">Chuyển nhượng quyền sử dụng đất</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 15 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">13</span>
                            <span class="proc-name">Cấp phép xây dựng nhà ở</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 20 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">14</span>
                            <span class="proc-name">Đăng ký biến động đất đai</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 10 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">15</span>
                            <span class="proc-name">Tách thửa đất ở</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 15 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">16</span>
                            <span class="proc-name">Cấp giấy phép xây dựng có thời hạn</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 20 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">17</span>
                            <span class="proc-name">Phê duyệt quy hoạch chi tiết</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 30 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">18</span>
                            <span class="proc-name">Cấp phép sử dụng đất lâm nghiệp</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 20 ngày</span>
                        </li>
                    </ul>

                    <div class="dv-category-title">
                        <i class="fas fa-store"></i> Thương mại - Đầu tư (90 thủ tục)
                    </div>
                    <ul class="dv-procedure-list">
                        <li class="dv-procedure-item">
                            <span class="proc-number">19</span>
                            <span class="proc-name">Đăng ký hộ kinh doanh cá thể</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 5 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">20</span>
                            <span class="proc-name">Cấp giấy phép kinh doanh</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 7 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">21</span>
                            <span class="proc-name">Đăng ký thành lập doanh nghiệp</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 5 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">22</span>
                            <span class="proc-name">Cấp phép kinh doanh rượu, bia</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 10 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">23</span>
                            <span class="proc-name">Cấp giấy phép an toàn thực phẩm</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 10 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">24</span>
                            <span class="proc-name">Đăng ký nhãn hiệu sản phẩm</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 12 ngày</span>
                        </li>
                    </ul>

                    <div class="dv-category-title">
                        <i class="fas fa-users"></i> Xã hội (70 thủ tục)
                    </div>
                    <ul class="dv-procedure-list">
                        <li class="dv-procedure-item">
                            <span class="proc-number">25</span>
                            <span class="proc-name">Đăng ký nhận trợ cấp ưu đãi người có công</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 10 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">26</span>
                            <span class="proc-name">Đăng ký hộ nghèo</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 15 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">27</span>
                            <span class="proc-name">Cấp thẻ bảo hiểm y tế</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 5 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">28</span>
                            <span class="proc-name">Đề nghị trợ cấp xã hội</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 10 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">29</span>
                            <span class="proc-name">Phúc lợi người cao tuổi</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 7 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">30</span>
                            <span class="proc-name">Hỗ trợ giáo dục cho hộ cận nghèo</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 10 ngày</span>
                        </li>
                    </ul>
                </div>

                <!-- Tư pháp - Hộ tịch -->
                <div class="dv-tab-content" id="tab-tu-phap">
                    <div class="dv-category-title">
                        <i class="fas fa-gavel"></i> Tư pháp - Hộ tịch - CMND, CCCD, giấy tờ cá nhân
                    </div>
                    <ul class="dv-procedure-list">
                        <li class="dv-procedure-item">
                            <span class="proc-number">1</span>
                            <span class="proc-name">Cấp mới Chứng minh nhân dân (CMND)</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 7 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">2</span>
                            <span class="proc-name">Cấp mới Căn cước công dân (CCCD) gắn chip</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 15 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">3</span>
                            <span class="proc-name">Cấp lại CMND/CCCD khi mất hoặc hư hỏng</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 15 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">4</span>
                            <span class="proc-name">Đăng ký kết hôn</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 3 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">5</span>
                            <span class="proc-name">Giấy xác nhận tình trạng hôn nhân</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 3 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">6</span>
                            <span class="proc-name">Đăng ký khai sinh cho trẻ em</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 1 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">7</span>
                            <span class="proc-name">Đăng ký khai tử</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 1 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">8</span>
                            <span class="proc-name">Cấp bản sao trích lục hộ tịch</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 1 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">9</span>
                            <span class="proc-name">Cải chính hộ tịch (sai tên, ngày sinh...)</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 5 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">10</span>
                            <span class="proc-name">Đăng ký giám hộ</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 3 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">11</span>
                            <span class="proc-name">Cấp giấy chứng nhận nuôi con nuôi</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 15 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">12</span>
                            <span class="proc-name">Xác nhận độc thân</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 3 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">13</span>
                            <span class="proc-name">Đăng ký thường trú</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 7 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">14</span>
                            <span class="proc-name">Đăng ký tạm trú</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 3 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">15</span>
                            <span class="proc-name">Xác nhận Citizen ID cho công dân</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 15 ngày</span>
                        </li>
                    </ul>
                </div>

                <!-- Địa chính - Nhà đất -->
                <div class="dv-tab-content" id="tab-dia-chinh">
                    <div class="dv-category-title">
                        <i class="fas fa-home"></i> Địa chính - Nhà đất - Quản lý đất đai, xây dựng
                    </div>
                    <ul class="dv-procedure-list">
                        <li class="dv-procedure-item">
                            <span class="proc-number">1</span>
                            <span class="proc-name">Cấp giấy chứng nhận quyền sử dụng đất lần đầu</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 30 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">2</span>
                            <span class="proc-name">Chuyển nhượng quyền sử dụng đất</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 15 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">3</span>
                            <span class="proc-name">Cho thuê, tặng cho quyền sử dụng đất</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 15 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">4</span>
                            <span class="proc-name">Thế chấp quyền sử dụng đất</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 5 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">5</span>
                            <span class="proc-name">Đăng ký biến động đất đai</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 10 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">6</span>
                            <span class="proc-name">Tách thửa đất ở</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 15 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">7</span>
                            <span class="proc-name">Gộp thửa đất</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 10 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">8</span>
                            <span class="proc-name">Cấp phép xây dựng nhà ở riêng lẻ</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 20 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">9</span>
                            <span class="proc-name">Cấp phép xây dựng công trình</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 25 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">10</span>
                            <span class="proc-name">Phê duyệt quy hoạch chi tiết 1/500</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 30 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">11</span>
                            <span class="proc-name">Cấp giấy phép sử dụng đất lâm nghiệp</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 20 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">12</span>
                            <span class="proc-name">Đo đạc, lập bản đồ địa chính</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 30 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">13</span>
                            <span class="proc-name">Đăng ký quyền sử dụng đất nông nghiệp</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 20 ngày</span>
                        </li>
                    </ul>
                </div>

                <!-- Thương mại - Đầu tư -->
                <div class="dv-tab-content" id="tab-thuong-mai">
                    <div class="dv-category-title">
                        <i class="fas fa-store"></i> Thương mại - Đầu tư - Đăng ký kinh doanh, giấy phép
                    </div>
                    <ul class="dv-procedure-list">
                        <li class="dv-procedure-item">
                            <span class="proc-number">1</span>
                            <span class="proc-name">Đăng ký hộ kinh doanh cá thể</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 5 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">2</span>
                            <span class="proc-name">Đăng ký hộ kinh doanh hộ gia đình</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 5 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">3</span>
                            <span class="proc-name">Đăng ký thành lập công ty TNHH</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 5 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">4</span>
                            <span class="proc-name">Cấp giấy phép kinh doanh bán lẻ</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 7 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">5</span>
                            <span class="proc-name">Cấp phép kinh doanh rượu, bia, nước giải khát</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 10 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">6</span>
                            <span class="proc-name">Giấy chứng nhận cơ sở đủ điều kiện ATTP</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 10 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">7</span>
                            <span class="proc-name">Đăng ký nhãn hiệu sản phẩm</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 12 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">8</span>
                            <span class="proc-name">Cấp phép kinh doanh dịch vụ ăn uống</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 10 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">9</span>
                            <span class="proc-name">Đăng ký hộ nông nghiệp</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 5 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">10</span>
                            <span class="proc-name">Đăng ký hộ sản xuất nông nghiệp</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 5 ngày</span>
                        </li>
                    </ul>
                </div>

                <!-- Xã hội -->
                <div class="dv-tab-content" id="tab-xa-hoi">
                    <div class="dv-category-title">
                        <i class="fas fa-users"></i> Xã hội - Phúc lợi, trợ cấp, bảo hiểm
                    </div>
                    <ul class="dv-procedure-list">
                        <li class="dv-procedure-item">
                            <span class="proc-number">1</span>
                            <span class="proc-name">Đăng ký trợ cấp người có công với cách mạng</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 10 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">2</span>
                            <span class="proc-name">Đăng ký hộ nghèo hàng năm</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 15 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">3</span>
                            <span class="proc-name">Đăng ký hộ cận nghèo</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 15 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">4</span>
                            <span class="proc-name">Cấp thẻ bảo hiểm y tế miễn phí</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 5 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">5</span>
                            <span class="proc-name">Đề nghị trợ cấp hàng tháng</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 10 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">6</span>
                            <span class="proc-name">Trợ cấp người cao tuổi (trên 80 tuổi)</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 7 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">7</span>
                            <span class="proc-name">Hỗ trợ kinh phí cho học sinh khó khăn</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 10 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">8</span>
                            <span class="proc-name">Trợ cấp thất nghiệp</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 10 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">9</span>
                            <span class="proc-name">Đề nghị hưởng chế độ tai nạn lao động</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 15 ngày</span>
                        </li>
                        <li class="dv-procedure-item">
                            <span class="proc-number">10</span>
                            <span class="proc-name">Hỗ trợ nhà ở cho hộ khó khăn</span>
                            <span class="proc-time"><i class="far fa-clock"></i> 30 ngày</span>
                        </li>
                    </ul>
                </div>
            </section>

            <!-- Hướng dẫn nộp hồ sơ -->
            <section class="dv-section">
                <div class="dv-section-header">
                    <div class="dv-section-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <h2>Hướng dẫn nộp hồ sơ</h2>
                </div>

                <div class="dv-guide-steps">
                    <div class="dv-guide-step">
                        <div class="dv-step-number">1</div>
                        <div class="dv-step-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h4>Tra cứu thủ tục</h4>
                        <p>Tìm kiếm thủ tục hành chính cần thực hiện trên danh mục 410 thủ tục của xã</p>
                    </div>
                    <div class="dv-guide-step">
                        <div class="dv-step-number">2</div>
                        <div class="dv-step-icon">
                            <i class="fas fa-file-signature"></i>
                        </div>
                        <h4>Chuẩn bị hồ sơ</h4>
                        <p>Thu thập đầy đủ giấy tờ theo yêu cầu. Liên hệ bộ phận tư vấn để được hỗ trợ</p>
                    </div>
                    <div class="dv-guide-step">
                        <div class="dv-step-number">3</div>
                        <div class="dv-step-icon">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        <h4>Nộp hồ sơ</h4>
                        <p>Nộp trực tiếp tại Bộ phận Tiếp nhận và Trả kết quả hoặc qua cổng dịch vụ công trực tuyến</p>
                    </div>
                    <div class="dv-guide-step">
                        <div class="dv-step-number">4</div>
                        <div class="dv-step-icon">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <h4>Nhận kết quả</h4>
                        <p>Nhận kết quả đúng hạn theo thông báo. Có thể nhận qua bưu điện hoặc trực tiếp</p>
                    </div>
                </div>

                <div class="dv-notice-box">
                    <div class="notice-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div>
                        <h4>Lưu ý quan trọng khi nộp hồ sơ</h4>
                        <p>
                            Tất cả hồ sơ cần photo công chứng hoặc bản gốc. Hồ sơ nộp trước 10h sáng sẽ được tiếp nhận trong ngày.
                            Hồ sơ nộp sau 10h sáng sẽ được xử lý vào ngày làm việc kế tiếp. Quý vị vui lòng mang theo CMND/CCCD khi đến nộp hồ sơ.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Thời gian giải quyết -->
            <section class="dv-section">
                <div class="dv-section-header">
                    <div class="dv-section-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h2>Thời gian giải quyết hồ sơ</h2>
                </div>

                <div class="dv-time-table-wrapper">
                    <table class="dv-time-table">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Lĩnh vực</th>
                                <th>Số lượng thủ tục</th>
                                <th>Thời gian giải quyết</th>
                                <th>Mức độ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>Tư pháp - Hộ tịch</td>
                                <td>120 thủ tục</td>
                                <td>1 - 7 ngày làm việc</td>
                                <td><span class="dv-time-badge fast"><i class="fas fa-bolt"></i> Nhanh</span></td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>Địa chính - Nhà đất</td>
                                <td>130 thủ tục</td>
                                <td>5 - 30 ngày làm việc</td>
                                <td><span class="dv-time-badge medium"><i class="fas fa-hourglass-half"></i> Trung bình</span></td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>Thương mại - Đầu tư</td>
                                <td>90 thủ tục</td>
                                <td>3 - 12 ngày làm việc</td>
                                <td><span class="dv-time-badge fast"><i class="fas fa-bolt"></i> Nhanh</span></td>
                            </tr>
                            <tr>
                                <td>4</td>
                                <td>Xã hội - Phúc lợi</td>
                                <td>70 thủ tục</td>
                                <td>5 - 30 ngày làm việc</td>
                                <td><span class="dv-time-badge medium"><i class="fas fa-hourglass-half"></i> Trung bình</span></td>
                            </tr>
                            <tr>
                                <td>5</td>
                                <td>Tài chính - Ngân sách</td>
                                <td>35 thủ tục</td>
                                <td>3 - 10 ngày làm việc</td>
                                <td><span class="dv-time-badge fast"><i class="fas fa-bolt"></i> Nhanh</span></td>
                            </tr>
                            <tr>
                                <td>6</td>
                                <td>Văn hóa - Thể thao</td>
                                <td>25 thủ tục</td>
                                <td>3 - 7 ngày làm việc</td>
                                <td><span class="dv-time-badge fast"><i class="fas fa-bolt"></i> Nhanh</span></td>
                            </tr>
                            <tr>
                                <td>7</td>
                                <td>Kế hoạch - Đầu tư</td>
                                <td>20 thủ tục</td>
                                <td>10 - 30 ngày làm việc</td>
                                <td><span class="dv-time-badge slow"><i class="fas fa-hourglass-end"></i> Chậm</span></td>
                            </tr>
                            <tr>
                                <td>8</td>
                                <td>Nông nghiệp - PTNT</td>
                                <td>15 thủ tục</td>
                                <td>5 - 15 ngày làm việc</td>
                                <td><span class="dv-time-badge medium"><i class="fas fa-hourglass-half"></i> Trung bình</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Liên hệ hỗ trợ -->
            <section class="dv-section">
                <div class="dv-section-header">
                    <div class="dv-section-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h2>Liên hệ hỗ trợ</h2>
                </div>

                <div class="dv-contact-grid">
                    <div class="dv-contact-card">
                        <div class="dv-contact-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <h4>Đường dây nóng</h4>
                        <p>Gọi cho chúng tôi để được tư vấn và hỗ trợ nhanh nhất</p>
                        <div class="contact-highlight">
                            <a href="tel:02703865030">0270 3865 030</a>
                        </div>
                    </div>
                    <div class="dv-contact-card">
                        <div class="dv-contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h4>Email hỗ trợ</h4>
                        <p>Gửi thắc mắc qua email, chúng tôi phản hồi trong 24 giờ</p>
                        <div class="contact-highlight">
                            <a href="mailto:ubnd.longhiep@vinhlong.gov.vn">ubnd.longhiep@vinhlong.gov.vn</a>
                        </div>
                    </div>
                    <div class="dv-contact-card">
                        <div class="dv-contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h4>Địa chỉ tiếp dân</h4>
                        <p>Bộ phận Tiếp nhận và Trả kết quả - Tầng 1, trụ sở UBND xã</p>
                        <div class="contact-highlight">Xã Long Hiệp, Vĩnh Long</div>
                    </div>
                </div>

                <div class="dv-notice-box">
                    <div class="notice-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <h4>Giờ làm việc</h4>
                        <p>
                            <strong>Thứ Hai đến Thứ Sáu:</strong> 7:30 - 11:30 &amp; 13:00 - 17:00<br>
                            <strong>Thứ Bảy:</strong> 7:30 - 11:30 (Chỉ giải quyết hồ sơ khẩn cấp)<br>
                            <strong>Chủ nhật &amp; Ngày lễ:</strong> Nghỉ
                        </p>
                    </div>
                </div>
            </section>

        </div>
    </div>

    <script>
        function switchTab(tabId) {
            document.querySelectorAll('.dv-tab-content').forEach(function(el) {
                el.classList.remove('active');
            });
            document.querySelectorAll('.dv-tab-btn').forEach(function(el) {
                el.classList.remove('active');
            });
            document.getElementById(tabId).classList.add('active');
            event.currentTarget.classList.add('active');
        }
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>