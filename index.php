<?php
header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'auth.php';
require_once 'check-approval.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function homeGetDbConnection()
{
    if (!class_exists('mysqli') || !defined('DB_HOST') || !defined('DB_USER') || !defined('DB_PASS') || !defined('DB_NAME')) {
        return null;
    }

    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_errno) {
        return null;
    }

    $conn->set_charset('utf8mb4');

    return $conn;
}

function homeHasText($value)
{
    return trim((string) $value) !== '';
}

function homeLimitText($text, $length = 160)
{
    $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $text)));

    if ($text === '') {
        return '';
    }

    $textLength = function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
    if ($textLength <= $length) {
        return $text;
    }

    $shortText = function_exists('mb_substr') ? mb_substr($text, 0, $length, 'UTF-8') : substr($text, 0, $length);
    return rtrim($shortText) . '...';
}

$highlightStats = [
    ['value' => '6.516,77 ha', 'label' => 'Diện tích tự nhiên'],
    ['value' => '22 ấp', 'label' => 'Đơn vị dân cư'],
    ['value' => '7.337 hộ', 'label' => 'Quy mô hộ dân'],
    ['value' => '30.272 người', 'label' => 'Dân số toàn xã'],
    ['value' => '80,45%', 'label' => 'Tỷ lệ hộ đồng bào Khmer']
];

$focusAreas = [
    [
        'title' => 'Dân vận gắn với đối thoại trực tiếp',
        'description' => 'Tăng cường tiếp dân, lắng nghe phản ánh, phối hợp MTTQ và đoàn thể để xử lý kịp thời các kiến nghị chính đáng của Nhân dân.'
    ],
    [
        'title' => 'Cải cách hành chính và chuyển đổi số',
        'description' => 'Rà soát 410 thủ tục thuộc thẩm quyền, đẩy mạnh niêm yết công khai, dịch vụ công trực tuyến và ứng dụng công nghệ thông tin trong điều hành.'
    ],
    [
        'title' => 'An sinh, giáo dục và văn hóa Khmer',
        'description' => 'Gắn phát triển nông nghiệp với giảm nghèo bền vững, chăm sóc sức khỏe ban đầu, nâng cao chất lượng giáo dục và giữ gìn bản sắc văn hóa địa phương.'
    ]
    ];

$quickLinks = [
    ['title' => 'Tin tức - điều hành', 'description' => 'Theo dõi hoạt động chỉ đạo, tuyên truyền, thi đua và thông báo mới trên địa bàn xã.', 'href' => 'tin-tuc.php'],
    ['title' => 'Lãnh đạo - tiếp dân', 'description' => 'Xem đầu mối chỉ đạo, hồ sơ lãnh đạo và phạm vi phụ trách tại xã.', 'href' => 'lanh-dao.php'],
    ['title' => 'Phòng ban - đoàn thể', 'description' => 'Tra cứu đơn vị chuyên môn và các đầu mối phối hợp phục vụ người dân.', 'href' => 'phong-ban.php'],
    ['title' => 'Liên hệ - phản ánh', 'description' => 'Gửi kiến nghị, câu hỏi hoặc phản ánh để UBND xã tiếp nhận và xử lý.', 'href' => 'lien-he.php']
    ];

$serviceCards = [
    ['title' => 'Cải cách thủ tục hành chính', 'description' => 'Niêm yết, công khai và hướng dẫn đầy đủ các thủ tục thuộc thẩm quyền giải quyết của xã.', 'tag' => '410 TTHC'],
    ['title' => 'Chuyển đổi số trong điều hành', 'description' => 'Tăng cường văn bản điện tử, hệ thống thông tin và dịch vụ công trực tuyến để phục vụ người dân, doanh nghiệp.', 'tag' => 'Điều hành số'],
    ['title' => 'Giáo dục, y tế và an sinh', 'description' => 'Duy trì chăm sóc sức khỏe ban đầu, chất lượng trường học và chính sách cho hộ nghèo, cận nghèo.', 'tag' => '13 trường | 01 trạm'],
    ['title' => 'Văn hóa, tín ngưỡng cộng đồng', 'description' => 'Tạo điều kiện cho hoạt động lễ hội, tôn giáo đúng quy định và bảo tồn bản sắc văn hóa Khmer.', 'tag' => '16 chùa']
    ];

$communityFacts = [
    'Long Hiệp là xã nông thôn mới thuộc tỉnh Vĩnh Long, cách trung tâm tỉnh khoảng 15 km với tổng diện tích tự nhiên 6.516,77 ha.',
    'Toàn xã có 22 ấp, 7.337 hộ với 30.272 nhân khẩu; đồng bào Khmer chiếm 80,45% tổng số hộ trên địa bàn.',
    'Hộ nghèo còn 154 hộ, chiếm 2,1%; hộ cận nghèo 123 hộ, chiếm 1,69%, phản ánh kết quả giảm nghèo đang tiếp tục được duy trì.'
];

$infrastructureFacts = [
    '13 trường học, trong đó có 01 trường THPT và 12 trường trực thuộc địa phương.',
    '01 trạm y tế bảo đảm chăm sóc sức khỏe ban đầu cho người dân.',
    '03 chùa Bắc tông và 13 chùa Phật giáo Nam tông Khmer, tạo nên không gian văn hóa đặc trưng của xã.'
];

$governanceMetrics = [
    [
        'value' => '70,62 triệu đồng',
        'label' => 'Thu nhập bình quân/người/năm',
        'description' => 'Mức thu nhập bình quân năm 2025 cho thấy kinh tế địa phương tiếp tục chuyển biến tích cực.'
    ],
    [
        'value' => '154 hộ',
        'label' => 'Hộ nghèo',
        'description' => 'Tương ứng 2,1%; tiếp tục là nhóm ưu tiên trong công tác dân vận, an sinh và hỗ trợ sinh kế.'
    ],
    [
        'value' => '123 hộ',
        'label' => 'Hộ cận nghèo',
        'description' => 'Tương ứng 1,69%; được theo dõi để hạn chế tái nghèo và nâng cao khả năng tự chủ của hộ dân.'
    ],
    [
        'value' => '410',
        'label' => 'Thủ tục hành chính',
        'description' => 'Thuộc thẩm quyền giải quyết của xã, được rà soát và công khai tại Bộ phận tiếp nhận và trả kết quả.'
    ],
    [
        'value' => '93+',
        'label' => 'Điểm hài lòng và CCHC',
        'description' => 'Chỉ số cải cách hành chính và mức độ hài lòng của người dân đạt từ 93 điểm trở lên.'
    ],
    [
        'value' => '100% / 99%',
        'label' => 'Nước hợp vệ sinh / điện',
        'description' => 'Hạ tầng thiết yếu cơ bản được phủ rộng, phục vụ ổn định cho sinh hoạt và sản xuất.'
    ]
];

$homeNews = [
    [
        'id' => 0,
        'title' => 'Long Hiệp tăng cường dân vận chính quyền gắn với phục vụ Nhân dân trong năm 2025',
        'summary' => 'Báo cáo năm 2025 cho thấy xã tiếp tục quán triệt công tác dân vận, đề cao trách nhiệm người đứng đầu, tăng đối thoại trực tiếp và phối hợp chặt chẽ với MTTQ, đoàn thể.',
        'image' => 'images/news-1772612257.jpg',
        'published_at' => '2025-11-01',
        'category_name' => 'Báo cáo 2025',
        'category_slug' => 'tuyen-truyen'
    ],
    [
        'id' => 0,
        'title' => '410 thủ tục hành chính được rà soát, công khai và hỗ trợ tra cứu tại xã',
        'summary' => 'Cải cách hành chính tiếp tục là nhiệm vụ trọng tâm, gắn với ứng dụng công nghệ thông tin, vận hành hệ thống thông tin và phát triển dịch vụ công trực tuyến.',
        'image' => 'images/news-1772612257.jpg',
        'published_at' => '2025-11-01',
        'category_name' => 'Cải cách hành chính',
        'category_slug' => 'tuyen-truyen'
    ],
    [
        'id' => 0,
        'title' => 'Dân sinh ổn định với trường học, y tế và hạ tầng thiết yếu được bảo đảm',
        'summary' => 'Toàn xã có 13 trường học, 01 trạm y tế, tỷ lệ hộ dùng nước hợp vệ sinh đạt 100% và tỷ lệ hộ sử dụng điện đạt 99%, phục vụ tốt nhu cầu sinh hoạt cơ bản.',
        'image' => 'images/news-1772612257.jpg',
        'published_at' => '2025-11-01',
        'category_name' => 'Dân sinh',
        'category_slug' => 'giao-duc'
    ],
    [
        'id' => 0,
        'title' => 'Giữ gìn bản sắc văn hóa Khmer gắn với xây dựng đời sống cộng đồng',
        'summary' => 'Địa phương tạo điều kiện cho hoạt động tín ngưỡng, lễ hội truyền thống và bảo tồn không gian văn hóa với 03 chùa Bắc tông, 13 chùa Nam tông Khmer.',
        'image' => 'images/news-1772612257.jpg',
        'published_at' => '2025-11-01',
        'category_name' => 'Văn hóa - xã hội',
        'category_slug' => 'su-kien'
    ]
];

$leaders = [
    [
        'id' => 1,
        'name' => 'Nguyễn Khánh Hòa',
        'position' => 'Chủ tịch UBND xã',
        'responsibilities' => 'Phụ trách chung công tác điều hành UBND xã.',
        'image_path' => 'images/z7588425037361_7335bf55fb98ce56ddadcf5ea03dc31d.jpg'
    ],
    [
        'id' => 2,
        'name' => 'Trần Văn Mười',
        'position' => 'Bí thư Đảng ủy xã Long Hiệp',
        'responsibilities' => 'Phụ trách công tác lãnh đạo Đảng bộ.',
        'image_path' => 'images/z7589735189490_f0d5aad79c3d71ef112527e1f3a14893.jpg'
    ]
];

// Video mặc định cho trang chủ - MP4 local
$homepageVideo = [
    'video_url' => 'videos/7619542452296.mp4',
    'title' => 'Hiệu quả xoa nghèo ở xã Long Hiệp',
    'description' => 'Xóa đói, giảm nghèo ở xã Long Hiệp',
    'type' => 'mp4' // Đánh dấu đây là video MP4
];

try {
    $conn = homeGetDbConnection();

    if ($conn) {
        $newsTableCheck = $conn->query("SHOW TABLES LIKE 'news'");
        $categoryTableCheck = $conn->query("SHOW TABLES LIKE 'categories'");

        if ($newsTableCheck && $newsTableCheck->num_rows > 0 && $categoryTableCheck && $categoryTableCheck->num_rows > 0) {
            $sqlNews = "SELECT n.id, n.title, n.summary, n.image, n.published_at, c.name AS category_name, c.slug AS category_slug
                        FROM news n
                        INNER JOIN categories c ON c.id = n.category_id
                        WHERE n.status = 'published'
                        ORDER BY n.published_at DESC, n.id DESC
                        LIMIT 4";

            $resultNews = $conn->query($sqlNews);
            if ($resultNews && $resultNews->num_rows > 0) {
                $homeNews = [];
                while ($row = $resultNews->fetch_assoc()) {
                    $homeNews[] = $row;
                }
            }
        }

        $leadersTableCheck = $conn->query("SHOW TABLES LIKE 'leaders'");
        if ($leadersTableCheck && $leadersTableCheck->num_rows > 0) {
            $sqlLeaders = "SELECT id, name, position, responsibilities, image_path
                           FROM leaders
                           WHERE is_active = 1
                           ORDER BY display_order ASC, id ASC
                           LIMIT 3";

            $resultLeaders = $conn->query($sqlLeaders);
            if ($resultLeaders && $resultLeaders->num_rows > 0) {
                $leaders = [];
                while ($row = $resultLeaders->fetch_assoc()) {
                    $leaders[] = $row;
                }
            }
        }

        // Lấy video nổi bật cho trang chủ
        $videosTableCheck = $conn->query("SHOW TABLES LIKE 'videos'");
        if ($videosTableCheck && $videosTableCheck->num_rows > 0) {
            $sqlVideo = "SELECT video_url, title, description
                         FROM videos
                         WHERE is_featured = 1 AND is_active = 1
                         ORDER BY display_order ASC, id DESC
                         LIMIT 1";

            $resultVideo = $conn->query($sqlVideo);
            if ($resultVideo && $resultVideo->num_rows > 0) {
                $videoFromDb = $resultVideo->fetch_assoc();
                // Chỉ ghi đè nếu có video trong database
                if (!empty($videoFromDb['video_url'])) {
                    $homepageVideo = $videoFromDb;
                    // Thêm type mặc định là mp4 nếu không có
                    if (!isset($homepageVideo['type'])) {
                        $homepageVideo['type'] = 'mp4';
                    }
                }
            }
        }
        
        // Lấy số liệu thống kê cho phần thống kê hệ thống
        $newsCount = 0;
        $videoCount = 0;
        
        if ($newsTableCheck && $newsTableCheck->num_rows > 0) {
            $newsCountResult = $conn->query("SELECT COUNT(*) as count FROM news WHERE status = 'published'");
            if ($newsCountResult) {
                $newsCount = $newsCountResult->fetch_assoc()['count'];
            }
        }
        
        if ($videosTableCheck && $videosTableCheck->num_rows > 0) {
            $videoCountResult = $conn->query("SELECT COUNT(*) as count FROM videos WHERE is_active = 1");
            if ($videoCountResult) {
                $videoCount = $videoCountResult->fetch_assoc()['count'];
            }
        }

        $conn->close();
    }
} catch (Throwable $e) {
    // Giữ fallback tĩnh nếu database chưa sẵn sàng.
}

$heroNews = $homeNews[0];
$secondaryNews = array_slice($homeNews, 1, 3);
$isLoggedIn = authIsLoggedIn();
$currentRole = authCurrentRole();
$displayName = $isLoggedIn ? authDisplayName() : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="design-system.css?v=1.0">
    <link rel="stylesheet" href="style.css?v=2.2">
    <link rel="stylesheet" href="home-style.css?v=2.0">
    <link rel="stylesheet" href="footer-style.css?v=2.0">
    <link rel="stylesheet" href="responsive-enhancements.css?v=1.0">
    <script src="dropdown.js"></script>
</head>
<body>
    <!-- Header và Menu thống nhất -->
    <?php include 'header-menu.php'; ?>

    <!-- Bản tin khẩn cấp -->
    <?php
    $urgentNews = [
        ['id' => 0, 'title' => 'Thông báo lịch tiếp dân UBND xã Long Hiệp tháng 6/2026', 'summary' => 'Lịch tiếp dân định kỳ tại trụ sở UBND xã', 'published_at' => '2026-06-01'],
        ['id' => 0, 'title' => 'Triển khai chương trình khuyến nông vụ hè thu 2026', 'summary' => 'Hỗ trợ giống, phân bón cho nông dân trên địa bàn xã', 'published_at' => '2026-05-28']
    ];
    ?>
    <?php if (!empty($urgentNews)): ?>
    <div class="urgent-banner">
        <div class="container">
            <div class="urgent-banner__inner">
                <div class="urgent-banner__label">
                    <i class="fas fa-bullhorn"></i>
                    <span>Tin khẩn cấp</span>
                </div>
                <div class="urgent-banner__scroll">
                    <?php foreach ($urgentNews as $u): ?>
                    <a href="<?php echo $u['id'] > 0 ? 'chi-tiet-tin.php?id=' . (int)$u['id'] : '#'; ?>" class="urgent-banner__item">
                        <span class="urgent-banner__dot"></span>
                        <?php echo htmlspecialchars($u['title']); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <main class="home-page">
        <section class="home-hero">
            <div class="container home-hero__grid">
                <div class="home-hero__content">
                    <span class="home-kicker">Cổng thông tin điện tử xã Long Hiệp</span>
                    <h1>Xã nông thôn mới Long Hiệp: dữ liệu dân sinh, điều hành và dịch vụ công trên cùng một trang</h1>
                    <p>
                        Trang chủ tập trung các thông tin cốt lõi từ báo cáo dân vận chính quyền năm 2025: quy mô dân cư,
                        cải cách hành chính, an sinh xã hội, bảo tồn văn hóa Khmer và các đầu mối phục vụ người dân.
                    </p>

                    <div class="home-hero__actions">
                        <a href="tin-tuc.php" class="home-btn home-btn--solid">Xem tin điều hành</a>
                        <a href="lien-he.php" class="home-btn home-btn--ghost">Liên hệ - phản ánh</a>
                    </div>

                    <div class="home-stat-grid">
                        <?php foreach ($highlightStats as $stat): ?>
                            <article class="home-stat-card">
                                <strong><?php echo htmlspecialchars($stat['value'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                <span><?php echo htmlspecialchars($stat['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>

                <aside class="home-hero__aside">
                    <article class="home-highlight-card">
                        <span class="home-card__eyebrow">Tin nổi bật</span>
                        <h2><?php echo htmlspecialchars($heroNews['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
                        <p><?php echo htmlspecialchars(homeLimitText($heroNews['summary'], 160), ENT_QUOTES, 'UTF-8'); ?></p>
                        <div class="home-highlight-card__meta">
                            <span><?php echo htmlspecialchars($heroNews['category_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <strong><?php echo htmlspecialchars($heroNews['id'] > 0 ? date('d/m/Y', strtotime((string) $heroNews['published_at'])) : 'Báo cáo 2025', ENT_QUOTES, 'UTF-8'); ?></strong>
                        </div>
                        <a href="<?php echo $heroNews['id'] > 0 ? 'chi-tiet-tin.php?id=' . (int) $heroNews['id'] : 'tin-tuc.php'; ?>" class="home-inline-link">
                            Mở bài viết
                        </a>
                    </article>

                    <div class="home-focus-list">
                        <?php foreach ($focusAreas as $area): ?>
                            <article class="home-focus-item">
                                <h3><?php echo htmlspecialchars($area['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                <p><?php echo htmlspecialchars($area['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </aside>
            </div>
        </section>

        <section class="home-section">
            <div class="container">
                <div class="home-section__heading">
                    <span class="home-kicker home-kicker--soft">Lối tắt truy cập</span>
                    <h2>Các nhóm thông tin người dân thường sử dụng</h2>
                </div>

                <div class="home-quick-grid">
                    <?php foreach ($quickLinks as $link): ?>
                        <a href="<?php echo htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8'); ?>" class="home-quick-card">
                            <h3><?php echo htmlspecialchars($link['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <p><?php echo htmlspecialchars($link['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <span>Truy cập ngay</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="home-section home-section--soft">
            <div class="container home-story-grid">
                <div class="home-story-card">
                    <div class="home-section__heading home-section__heading--left">
                        <span class="home-kicker home-kicker--soft">Tổng quan địa phương</span>
                        <h2>Bức tranh tổng quan từ báo cáo dân vận năm 2025</h2>
                    </div>
                    <p>
                        Báo cáo cho thấy Long Hiệp tiếp tục giữ vai trò là địa bàn nông nghiệp, có mạng lưới hạ tầng dân sinh
                        từng bước được nâng cấp, đời sống văn hóa cộng đồng ổn định và công tác dân vận được gắn trực tiếp với
                        điều hành, cải cách hành chính và chăm lo đời sống Nhân dân.
                    </p>

                    <ul class="home-outline-list">
                        <?php foreach ($communityFacts as $fact): ?>
                            <li><?php echo htmlspecialchars($fact, ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <?php if (true): ?>
                <div class="home-media-card">
                    <div class="home-video-shell">
                        <?php if (isset($homepageVideo['type']) && $homepageVideo['type'] === 'youtube'): ?>
                            <!-- YouTube Embed -->
                            <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%;">
                                <iframe 
                                    src="<?php echo htmlspecialchars($homepageVideo['video_url'], ENT_QUOTES, 'UTF-8'); ?>" 
                                    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none;"
                                    allowfullscreen
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture">
                                </iframe>
                            </div>
                        <?php elseif (isset($homepageVideo['type']) && $homepageVideo['type'] === 'tiktok'): ?>
                            <!-- TikTok Embed với iframe -->
                            <div style="position: relative; padding-bottom: 177.78%; height: 0; overflow: hidden; max-width: 100%;">
                                <iframe 
                                    src="https://www.tiktok.com/embed/v2/<?php echo htmlspecialchars($homepageVideo['video_id'], ENT_QUOTES, 'UTF-8'); ?>" 
                                    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none;"
                                    allowfullscreen
                                    scrolling="no"
                                    allow="encrypted-media;">
                                </iframe>
                            </div>
                        <?php else: ?>
                            <!-- Video HTML5 thông thường (MP4) -->
                            <video controls preload="metadata" style="width: 100%; height: auto; background: #000;">
                                <source src="<?php echo htmlspecialchars($homepageVideo['video_url'], ENT_QUOTES, 'UTF-8'); ?>" type="video/mp4">
                                Trình duyệt của bạn không hỗ trợ video HTML5.
                            </video>
                        <?php endif; ?>
                    </div>
                    <div class="home-media-card__caption">
                        <strong><?php echo htmlspecialchars($homepageVideo['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                        <span><?php echo htmlspecialchars($homepageVideo['description'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <ul class="home-media-facts">
                        <?php foreach ($infrastructureFacts as $fact): ?>
                            <li><?php echo htmlspecialchars($fact, ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="home-section">
            <div class="container">
                <div class="home-section__heading">
                    <span class="home-kicker home-kicker--soft">Chỉ số nổi bật 2025</span>
                    <h2>Dân sinh, cải cách hành chính và chất lượng phục vụ</h2>
                </div>

                <div class="home-metric-grid">
                    <?php foreach ($governanceMetrics as $metric): ?>
                        <article class="home-metric-card">
                            <strong><?php echo htmlspecialchars($metric['value'], ENT_QUOTES, 'UTF-8'); ?></strong>
                            <h3><?php echo htmlspecialchars($metric['label'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <p><?php echo htmlspecialchars($metric['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="home-section">
            <div class="container">
                <div class="home-section__heading">
                    <span class="home-kicker home-kicker--soft">Tin tức</span>
                    <h2>Cập nhật mới từ hoạt động của xã</h2>
                </div>

                <div class="home-news-grid">
                    <article class="home-news-feature">
                        <div class="home-news-feature__image">
                            <img src="<?php echo htmlspecialchars(homeHasText($heroNews['image']) ? $heroNews['image'] : 'images/news-1772612257.jpg', ENT_QUOTES, 'UTF-8'); ?>"
                                 alt="<?php echo htmlspecialchars($heroNews['title'], ENT_QUOTES, 'UTF-8'); ?>"
                                 loading="lazy"
                                 onerror="this.src='images/news-1772612257.jpg'">
                        </div>
                        <div class="home-news-feature__body">
                            <span class="home-news-tag"><?php echo htmlspecialchars($heroNews['category_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <h3><?php echo htmlspecialchars($heroNews['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <p><?php echo htmlspecialchars(homeLimitText($heroNews['summary'], 220), ENT_QUOTES, 'UTF-8'); ?></p>
                            <div class="home-news-feature__footer">
                                <?php if ($heroNews['id'] > 0): ?>
                                    <time datetime="<?php echo htmlspecialchars((string) $heroNews['published_at'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars(date('d/m/Y', strtotime((string) $heroNews['published_at'])), ENT_QUOTES, 'UTF-8'); ?>
                                    </time>
                                <?php else: ?>
                                    <span class="home-news-meta">Báo cáo 2025</span>
                                <?php endif; ?>
                                <a href="<?php echo $heroNews['id'] > 0 ? 'chi-tiet-tin.php?id=' . (int) $heroNews['id'] : 'tin-tuc.php'; ?>">Đọc chi tiết</a>
                            </div>
                        </div>
                    </article>

                    <div class="home-news-list">
                        <?php foreach ($secondaryNews as $item): ?>
                            <article class="home-news-mini">
                                <span class="home-news-tag"><?php echo htmlspecialchars($item['category_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <h3><?php echo htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                <p><?php echo htmlspecialchars(homeLimitText($item['summary'], 110), ENT_QUOTES, 'UTF-8'); ?></p>
                                <a href="<?php echo $item['id'] > 0 ? 'chi-tiet-tin.php?id=' . (int) $item['id'] : 'tin-tuc.php'; ?>">Xem bài viết</a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <section class="home-section home-section--soft">
            <div class="container home-dual-grid">
                <div class="home-panel">
                    <div class="home-section__heading home-section__heading--left">
                        <span class="home-kicker home-kicker--soft">Lãnh đạo</span>
                        <h2>Đầu mối điều hành chính</h2>
                    </div>

                    <div class="home-leader-grid">
                        <?php foreach ($leaders as $leader): ?>
                            <article class="home-leader-card">
                                <div class="home-leader-card__photo">
                                    <img src="<?php echo htmlspecialchars(homeHasText($leader['image_path']) ? $leader['image_path'] : 'images/news-1772612257.jpg', ENT_QUOTES, 'UTF-8'); ?>"
                                         alt="<?php echo htmlspecialchars($leader['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                         loading="lazy"
                                         onerror="this.src='images/news-1772612257.jpg'">
                                </div>
                                <div class="home-leader-card__body">
                                    <h3><?php echo htmlspecialchars($leader['name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                    <span><?php echo htmlspecialchars($leader['position'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <p><?php echo htmlspecialchars(homeLimitText($leader['responsibilities'], 120), ENT_QUOTES, 'UTF-8'); ?></p>
                                    <a href="chi-tiet-lanh-dao.php?id=<?php echo (int) $leader['id']; ?>">Xem hồ sơ</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="home-panel">
                    <div class="home-section__heading home-section__heading--left">
                        <span class="home-kicker home-kicker--soft">Dịch vụ và hỗ trợ</span>
                        <h2>Nhóm nội dung phục vụ người dân</h2>
                    </div>

                    <div class="home-service-grid">
                        <?php foreach ($serviceCards as $service): ?>
                            <article class="home-service-card">
                                <span class="home-service-card__tag"><?php echo htmlspecialchars($service['tag'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <h3><?php echo htmlspecialchars($service['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                <p><?php echo htmlspecialchars($service['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>

        <section class="home-section">
            <div class="container home-map-grid">
                <div class="home-panel">
                    <div class="home-section__heading home-section__heading--left">
                        <span class="home-kicker home-kicker--soft">Thống kê hệ thống</span>
                        <h2>Số liệu hoạt động</h2>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-top: 20px;">
                        <?php
                        $system_stats = [
                            ['icon' => 'newspaper', 'label' => 'Tin tức đã đăng', 'value' => $newsCount ?? 0, 'color' => '#b91c1c'],
                            ['icon' => 'video', 'label' => 'Video hoạt động', 'value' => $videoCount ?? 0, 'color' => '#e74c3c'],
                            ['icon' => 'users', 'label' => 'Cán bộ nhân viên', 'value' => '67', 'color' => '#27ae60'],
                            ['icon' => 'building', 'label' => 'Phòng ban', 'value' => '8', 'color' => '#f39c12']
                        ];
                        
                        foreach ($system_stats as $stat):
                        ?>
                        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); text-align: center; border-left: 4px solid <?php echo $stat['color']; ?>;">
                            <i class="fas fa-<?php echo $stat['icon']; ?>" style="font-size: 36px; color: <?php echo $stat['color']; ?>; margin-bottom: 15px;"></i>
                            <div style="font-size: 32px; font-weight: 700; color: #333; margin-bottom: 8px;"><?php echo $stat['value']; ?></div>
                            <div style="font-size: 14px; color: #666;"><?php echo $stat['label']; ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <p class="home-map-note" style="margin-top: 25px;">
                        Hệ thống quản lý thông tin điện tử UBND Xã Long Hiệp - Cập nhật liên tục các hoạt động và thông tin của xã.
                    </p>
                </div>

                <aside class="home-cta-card">
                    <span class="home-kicker">Thông điệp điều hành</span>
                    <h2>Trọng dân, gần dân, nghe dân nói và làm dân tin</h2>
                    <p>
                        Định hướng của xã là tiếp tục đối thoại trực tiếp, nâng cao kỷ luật công vụ, xử lý phản ánh kịp thời
                        và gắn cải cách hành chính với chuyển đổi số để phục vụ người dân tốt hơn trong năm 2026.
                    </p>
                    <a href="lien-he.php" class="home-btn home-btn--solid">Gửi phản ánh, kiến nghị</a>
                </aside>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>
    <?php include 'quick-actions-toolbar.php'; ?>
</body>
</html>

