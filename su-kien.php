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
    <title>Lịch sự kiện & Pháp luật - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #1a5276;
            --primary-light: #2980b9;
            --secondary: #c0392b;
            --accent: #e74c3c;
            --gradient-primary: linear-gradient(135deg, #1a5276 0%, #2980b9 100%);
            --gradient-secondary: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%);
            --gradient-accent: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
            --bg-light: #f8f9fa;
            --bg-white: #ffffff;
            --border-color: #dee2e6;
            --shadow: 0 2px 10px rgba(0,0,0,0.1);
            --shadow-hover: 0 4px 20px rgba(0,0,0,0.15);
            --radius: 8px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-dark);
            line-height: 1.6;
            background-color: var(--bg-light);
        }

        .page-header {
            background: var(--gradient-primary);
            color: white;
            padding: 40px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="2"/></svg>') repeat;
            opacity: 0.3;
        }

        .page-header h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .page-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 60% 40%;
            gap: 30px;
        }

        @media (max-width: 992px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        .section-card {
            background: var(--bg-white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .section-header {
            background: var(--gradient-primary);
            color: white;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-header i {
            font-size: 1.3rem;
        }

        .section-header h2 {
            font-size: 1.2rem;
            font-weight: 600;
        }

        .section-content {
            padding: 20px;
        }

        /* Calendar Styles */
        .calendar {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px;
            background: var(--bg-light);
            border-radius: var(--radius);
        }

        .calendar-header h3 {
            font-size: 1.1rem;
            color: var(--primary);
        }

        .calendar-nav {
            display: flex;
            gap: 5px;
        }

        .calendar-nav button {
            background: var(--primary);
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            transition: var(--transition);
        }

        .calendar-nav button:hover {
            background: var(--primary-light);
        }

        .calendar table {
            width: 100%;
            border-collapse: collapse;
        }

        .calendar th,
        .calendar td {
            padding: 10px;
            text-align: center;
            border: 1px solid var(--border-color);
        }

        .calendar th {
            background: var(--primary);
            color: white;
            font-weight: 500;
        }

        .calendar td {
            background: var(--bg-white);
            min-height: 60px;
            vertical-align: top;
        }

        .calendar td.today {
            background: #e8f4fc;
            font-weight: bold;
        }

        .calendar td.has-event {
            position: relative;
        }

        .calendar td.has-event::after {
            content: '';
            position: absolute;
            bottom: 5px;
            left: 50%;
            transform: translateX(-50%);
            width: 6px;
            height: 6px;
            background: var(--accent);
            border-radius: 50%;
        }

        .calendar td.other-month {
            color: #ccc;
        }

        /* Events List */
        .events-list {
            list-style: none;
        }

        .event-item {
            display: flex;
            gap: 15px;
            padding: 15px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            margin-bottom: 15px;
            transition: var(--transition);
        }

        .event-item:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-2px);
        }

        .event-date {
            background: var(--gradient-primary);
            color: white;
            padding: 10px 15px;
            border-radius: var(--radius);
            text-align: center;
            min-width: 70px;
        }

        .event-date .day {
            font-size: 1.5rem;
            font-weight: bold;
            display: block;
        }

        .event-date .month {
            font-size: 0.8rem;
            opacity: 0.9;
        }

        .event-details h4 {
            font-size: 1rem;
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .event-details p {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-bottom: 8px;
        }

        .event-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .badge-hop {
            background: #3498db;
            color: white;
        }

        .badge-le-hoi {
            background: #9b59b6;
            color: white;
        }

        .badge-huong-dan {
            background: #27ae60;
            color: white;
        }

        .badge-phap-luat {
            background: #e74c3c;
            color: white;
        }

        /* Laws Section */
        .laws-list {
            list-style: none;
        }

        .law-item {
            padding: 15px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            margin-bottom: 15px;
            transition: var(--transition);
        }

        .law-item:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-2px);
        }

        .law-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .law-date {
            font-size: 0.85rem;
            color: var(--text-light);
        }

        .law-category {
            background: var(--gradient-secondary);
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
        }

        .law-item h4 {
            font-size: 1rem;
            color: var(--primary);
            margin-bottom: 8px;
        }

        .law-item p {
            font-size: 0.9rem;
            color: var(--text-light);
            line-height: 1.5;
        }

        .view-all-link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            margin-top: 10px;
            transition: var(--transition);
        }

        .view-all-link:hover {
            color: var(--primary-light);
        }

        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 1.6rem;
            }

            .calendar th,
            .calendar td {
                padding: 5px;
                font-size: 0.85rem;
            }

            .event-item {
                flex-direction: column;
            }

            .event-date {
                min-width: auto;
            }

            .event-date .day {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'header-menu.php'; ?>

    <div class="page-header">
        <h1><i class="fas fa-calendar-alt"></i> Lịch sự kiện & Pháp luật</h1>
        <p>UBND Xã Long Hiệp, Tỉnh Vĩnh Long</p>
    </div>

    <div class="container">
        <div class="content-grid">
            <!-- LEFT COLUMN: Events Calendar -->
            <div class="events-column">
                <div class="section-card">
                    <div class="section-header">
                        <i class="fas fa-calendar"></i>
                        <h2>Lịch sự kiện tháng <?php echo date('m'); ?>-<?php echo date('Y'); ?></h2>
                    </div>
                    <div class="section-content">
                        <div class="calendar-header">
                            <div class="calendar-nav">
                                <button title="Tháng trước"><i class="fas fa-chevron-left"></i></button>
                            </div>
                            <h3>Tháng <?php echo date('m'); ?>-<?php echo date('Y'); ?></h3>
                            <div class="calendar-nav">
                                <button title="Tháng sau"><i class="fas fa-chevron-right"></i></button>
                            </div>
                        </div>
                        <table class="calendar">
                            <thead>
                                <tr>
                                    <th>CN</th>
                                    <th>T2</th>
                                    <th>T3</th>
                                    <th>T4</th>
                                    <th>T5</th>
                                    <th>T6</th>
                                    <th>T7</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $currentMonth = date('m');
                                $currentYear = date('Y');
                                $daysInMonth = date('t');
                                $firstDayOfMonth = date('w', mktime(0, 0, 0, $currentMonth, 1, $currentYear));
                                $today = date('d');

                                // Sample event days for demo
                                $eventDays = [5, 10, 15, 20, 25];
                                
                                echo '<tr>';
                                
                                // Empty cells for days before the first day of the month
                                for ($i = 0; $i < $firstDayOfMonth; $i++) {
                                    echo '<td class="other-month"></td>';
                                }
                                
                                // Days of the month
                                for ($day = 1; $day <= $daysInMonth; $day++) {
                                    $dayOfWeek = ($firstDayOfMonth + $day - 1) % 7;
                                    
                                    $classes = '';
                                    if ($day == $today) $classes .= ' today';
                                    if (in_array($day, $eventDays)) $classes .= ' has-event';
                                    
                                    echo '<td class="' . trim($classes) . '">' . $day . '</td>';
                                    
                                    if ($dayOfWeek == 6 && $day < $daysInMonth) {
                                        echo '</tr><tr>';
                                    }
                                }
                                
                                // Empty cells for remaining days
                                $remainingCells = 7 - (($firstDayOfMonth + $daysInMonth) % 7);
                                if ($remainingCells < 7) {
                                    for ($i = 0; $i < $remainingCells; $i++) {
                                        echo '<td class="other-month"></td>';
                                    }
                                }
                                
                                echo '</tr>';
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="section-card">
                    <div class="section-header">
                        <i class="fas fa-list"></i>
                        <h2>Sự kiện sắp tới</h2>
                    </div>
                    <div class="section-content">
                        <ul class="events-list">
                            <?php
                            $events = [
                                [
                                    'day' => '05',
                                    'month' => 'Th 6',
                                    'title' => 'Lịch tiếp dân định kỳ',
                                    'time' => 'Thứ 2 hàng tuần, 8:00 - 11:00',
                                    'location' => 'UBND xã Long Hiệp',
                                    'type' => 'hop',
                                    'type_label' => 'Họp'
                                ],
                                [
                                    'day' => '10',
                                    'month' => 'Th 6',
                                    'title' => 'Họp HĐND xã quý II/2026',
                                    'time' => '8:00 - 11:30',
                                    'location' => 'Hội trường UBND xã',
                                    'type' => 'hop',
                                    'type_label' => 'Họp'
                                ],
                                [
                                    'day' => '15',
                                    'month' => 'Th 6',
                                    'title' => 'Tập huấn cán bộ cơ sở',
                                    'time' => '14:00 - 17:00',
                                    'location' => 'Trung tâm Văn hóa xã',
                                    'type' => 'huong-dan',
                                    'type_label' => 'Hướng dẫn'
                                ],
                                [
                                    'day' => '20',
                                    'month' => 'Th 6',
                                    'title' => 'Lễ hội Ok Om Bok',
                                    'time' => '7:00 - 12:00',
                                    'location' => 'Đình làng Long Hiệp',
                                    'type' => 'le-hoi',
                                    'type_label' => 'Lễ hội'
                                ],
                                [
                                    'day' => '25',
                                    'month' => 'Th 6',
                                    'title' => 'Khởi động chương trình khuyến nông',
                                    'time' => '8:30 - 11:00',
                                    'location' => 'Nhà văn hóa ấp Long Thạnh',
                                    'type' => 'huong-dan',
                                    'type_label' => 'Hướng dẫn'
                                ],
                                [
                                    'day' => '28',
                                    'month' => 'Th 6',
                                    'title' => 'Tuần lễ văn hóa Khmer',
                                    'time' => 'Cả ngày',
                                    'location' => 'Khu vực trung tâm xã',
                                    'type' => 'le-hoi',
                                    'type_label' => 'Lễ hội'
                                ]
                            ];

                            foreach ($events as $event) {
                                $badgeClass = '';
                                switch ($event['type']) {
                                    case 'hop': $badgeClass = 'badge-hop'; break;
                                    case 'le-hoi': $badgeClass = 'badge-le-hoi'; break;
                                    case 'huong-dan': $badgeClass = 'badge-huong-dan'; break;
                                    case 'phap-luat': $badgeClass = 'badge-phap-luat'; break;
                                }
                                echo '<li class="event-item">';
                                echo '<div class="event-date">';
                                echo '<span class="day">' . $event['day'] . '</span>';
                                echo '<span class="month">' . $event['month'] . '</span>';
                                echo '</div>';
                                echo '<div class="event-details">';
                                echo '<h4>' . $event['title'] . '</h4>';
                                echo '<p><i class="fas fa-clock"></i> ' . $event['time'] . '</p>';
                                echo '<p><i class="fas fa-map-marker-alt"></i> ' . $event['location'] . '</p>';
                                echo '<span class="event-badge ' . $badgeClass . '">' . $event['type_label'] . '</span>';
                                echo '</div>';
                                echo '</li>';
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Laws & Regulations -->
            <div class="laws-column">
                <div class="section-card">
                    <div class="section-header" style="background: var(--gradient-secondary);">
                        <i class="fas fa-gavel"></i>
                        <h2>Pháp luật mới</h2>
                    </div>
                    <div class="section-content">
                        <ul class="laws-list">
                            <?php
                            $laws = [
                                [
                                    'date' => '28/05/2026',
                                    'title' => 'Luật Giao dịch điện tử (sửa đổi)',
                                    'category' => 'Luật',
                                    'summary' => 'Bổ sung các quy định về giao dịch điện tử trong lĩnh vực hành chính công, đảm bảo tính pháp lý của hồ sơ số.'
                                ],
                                [
                                    'date' => '25/05/2026',
                                    'title' => 'Nghị định về hộ tịch điện tử',
                                    'category' => 'Nghị định',
                                    'summary' => 'Quy định thủ tục đăng ký khai sinh, kết hôn, ly hôn trực tuyến trên hệ thống hộ tịch điện tử quốc gia.'
                                ],
                                [
                                    'date' => '20/05/2026',
                                    'title' => 'Luật Đất đai (sửa đổi)',
                                    'category' => 'Luật',
                                    'summary' => 'Điều chỉnh giá đất, bồi thường khi thu hồi đất và quyền sử dụng đất của hộ gia đình, cá nhân.'
                                ],
                                [
                                    'date' => '18/05/2026',
                                    'title' => 'Quy định về bảo vệ môi trường',
                                    'category' => 'Thông tư',
                                    'summary' => 'Hướng dẫn xử lý rác thải sinh hoạt tại khu vực nông thôn, khuyến khích phân loại rác tại nguồn.'
                                ],
                                [
                                    'date' => '15/05/2026',
                                    'title' => 'Nghị định về trợ giúp xã hội',
                                    'category' => 'Nghị định',
                                    'summary' => 'Mở rộng đối tượng hưởng trợ cấp xã hội hàng tháng cho người cao tuổi và người khuyết tật.'
                                ],
                                [
                                    'date' => '10/05/2026',
                                    'title' => 'Thông tư hướng dẫn đầu tư công',
                                    'category' => 'Thông tư',
                                    'summary' => 'Hướng dẫn chi tiết quy trình lập, thẩm định và phê duyệt dự án đầu tư công cấp xã.'
                                ]
                            ];

                            foreach ($laws as $law) {
                                echo '<li class="law-item">';
                                echo '<div class="law-meta">';
                                echo '<span class="law-date"><i class="fas fa-calendar-alt"></i> ' . $law['date'] . '</span>';
                                echo '<span class="law-category">' . $law['category'] . '</span>';
                                echo '</div>';
                                echo '<h4>' . $law['title'] . '</h4>';
                                echo '<p>' . $law['summary'] . '</p>';
                                echo '</li>';
                            }
                            ?>
                        </ul>
                        <a href="#" class="view-all-link">
                            <i class="fas fa-arrow-right"></i> Xem tất cả văn bản mới
                        </a>
                    </div>
                </div>

                <div class="section-card">
                    <div class="section-header" style="background: var(--gradient-accent);">
                        <i class="fas fa-info-circle"></i>
                        <h2>Thông tin hữu ích</h2>
                    </div>
                    <div class="section-content">
                        <div style="padding: 15px; background: #fff8e1; border-radius: var(--radius); border-left: 4px solid #ffc107; margin-bottom: 15px;">
                            <h4 style="color: #f57c00; margin-bottom: 5px;"><i class="fas fa-bell"></i> Nhắc nhở</h4>
                            <p style="font-size: 0.9rem; color: #5d4037;">Hạn nộp thuế sử dụng đất nông nghiệp: 30/06/2026</p>
                        </div>
                        <div style="padding: 15px; background: #e8f5e9; border-radius: var(--radius); border-left: 4px solid #4caf50; margin-bottom: 15px;">
                            <h4 style="color: #2e7d32; margin-bottom: 5px;"><i class="fas fa-check-circle"></i> Thông báo</h4>
                            <p style="font-size: 0.9rem; color: #1b5e20;">Đăng ký hộ khẩu trực tuyến đã chính thức triển khai</p>
                        </div>
                        <div style="padding: 15px; background: #e3f2fd; border-radius: var(--radius); border-left: 4px solid #2196f3;">
                            <h4 style="color: #1565c0; margin-bottom: 5px;"><i class="fas fa-question-circle"></i> Hỗ trợ</h4>
                            <p style="font-size: 0.9rem; color: #0d47a1;">Hotline hỗ trợ: 0123.456.789</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
