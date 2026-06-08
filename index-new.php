<?php
header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'auth.php';

$page_title = "Trang chủ - UBND Xã Long Hiệp";

// Lấy tin tức nổi bật
$conn = getDBConnection();
$featured_news = [];
$latest_news = [];
$stats = [
    'news_count' => 0,
    'video_count' => 0,
    'staff_count' => 67,
    'departments' => 8
];

if ($conn) {
    // Kiểm tra bảng news có tồn tại không
    $table_check = $conn->query("SHOW TABLES LIKE 'news'");
    
    if ($table_check && $table_check->num_rows > 0) {
        // Lấy 3 tin nổi bật - sử dụng cột đúng: image và published_at
        $featured_sql = "SELECT n.id, n.title, n.summary, n.image, n.published_at, c.name as category_name 
                        FROM news n 
                        LEFT JOIN categories c ON c.id = n.category_id 
                        WHERE n.status = 'published' 
                        ORDER BY n.published_at DESC 
                        LIMIT 3";
        $featured_result = $conn->query($featured_sql);
        if ($featured_result) {
            while ($row = $featured_result->fetch_assoc()) {
                $featured_news[] = $row;
            }
        }
        
        // Lấy 6 tin mới nhất
        $latest_sql = "SELECT n.id, n.title, n.summary, n.image, n.published_at, c.name as category_name 
                      FROM news n 
                      LEFT JOIN categories c ON c.id = n.category_id 
                      WHERE n.status = 'published' 
                      ORDER BY n.published_at DESC 
                      LIMIT 6";
        $latest_result = $conn->query($latest_sql);
        if ($latest_result) {
            while ($row = $latest_result->fetch_assoc()) {
                $latest_news[] = $row;
            }
        }
        
        // Thống kê tin tức
        $news_count = $conn->query("SELECT COUNT(*) as count FROM news WHERE status = 'published'");
        if ($news_count) {
            $stats['news_count'] = $news_count->fetch_assoc()['count'];
        }
    }
    
    // Kiểm tra bảng videos
    $video_table_check = $conn->query("SHOW TABLES LIKE 'videos'");
    if ($video_table_check && $video_table_check->num_rows > 0) {
        $video_count = $conn->query("SELECT COUNT(*) as count FROM videos WHERE is_active = 1");
        if ($video_count) {
            $stats['video_count'] = $video_count->fetch_assoc()['count'];
        }
    }
}

include 'header-menu.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
/* Hero Slider */
.hero-slider {
    position: relative;
    height: 500px;
    overflow: hidden;
    background: var(--gradient-primary);
}

.slider-container {
    position: relative;
    height: 100%;
}

.slide {
    display: none;
    position: absolute;
    width: 100%;
    height: 100%;
    animation: fadeIn 1s;
}

.slide.active {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.slide-content {
    position: relative;
    max-width: 1200px;
    margin: 0 auto;
    padding: 80px 20px;
    color: white;
    z-index: 2;
}

.slide-content h1 {
    font-size: 48px;
    font-weight: 700;
    margin-bottom: 20px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.slide-content p {
    font-size: 20px;
    margin-bottom: 30px;
    opacity: 0.95;
}

.slide-btn {
    display: inline-block;
    padding: 15px 40px;
    background: white;
    color: var(--primary);
    text-decoration: none;
    border-radius: 50px;
    font-weight: 600;
    transition: all 0.3s;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.slide-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.3);
}

.slider-nav {
    position: absolute;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 10px;
    z-index: 3;
}

.slider-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(255,255,255,0.5);
    cursor: pointer;
    transition: all 0.3s;
}

.slider-dot.active {
    background: white;
    width: 30px;
    border-radius: 6px;
}

/* Stats Section */
.stats-section {
    background: white;
    padding: 60px 20px;
    margin-top: -50px;
    position: relative;
    z-index: 10;
}

.stats-container {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
}

.stat-card {
    background: var(--gradient-primary);
    padding: 40px 30px;
    border-radius: 15px;
    text-align: center;
    color: white;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    transition: transform 0.3s;
}

.stat-card:hover {
    transform: translateY(-10px);
}

.stat-icon {
    font-size: 48px;
    margin-bottom: 20px;
    opacity: 0.9;
}

.stat-number {
    font-size: 42px;
    font-weight: 700;
    margin-bottom: 10px;
}

.stat-label {
    font-size: 16px;
    opacity: 0.9;
}

/* News Section */
.news-section {
    max-width: 1200px;
    margin: 80px auto;
    padding: 0 20px;
}

.section-header {
    text-align: center;
    margin-bottom: 50px;
}

.section-header h2 {
    font-size: 36px;
    color: #333;
    margin-bottom: 15px;
}

.section-header p {
    font-size: 18px;
    color: #666;
}

.news-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 30px;
}

.news-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: all 0.3s;
}

.news-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.2);
}

.news-image {
    width: 100%;
    height: 220px;
    object-fit: cover;
    background: var(--gradient-primary);
}

.news-content {
    padding: 25px;
}

.news-category {
    display: inline-block;
    padding: 5px 15px;
    background: var(--primary);
    color: white;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 15px;
}

.news-title {
    font-size: 20px;
    font-weight: 700;
    color: #333;
    margin-bottom: 15px;
    line-height: 1.4;
}

.news-summary {
    color: #666;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 15px;
}

.news-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 15px;
    border-top: 1px solid #eee;
}

.news-date {
    color: #999;
    font-size: 13px;
}

.news-link {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
}

.news-link:hover {
    text-decoration: underline;
}

/* Quick Links */
.quick-links-section {
    background: #f8f9fa;
    padding: 80px 20px;
}

.quick-links-container {
    max-width: 1200px;
    margin: 0 auto;
}

.quick-links-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
    margin-top: 40px;
}

.quick-link-card {
    background: white;
    padding: 30px;
    border-radius: 12px;
    text-align: center;
    text-decoration: none;
    color: #333;
    transition: all 0.3s;
    border: 2px solid transparent;
}

.quick-link-card:hover {
    border-color: var(--primary);
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.quick-link-icon {
    font-size: 48px;
    color: var(--primary);
    margin-bottom: 20px;
}

.quick-link-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 10px;
}

.quick-link-desc {
    font-size: 14px;
    color: #666;
}

@media (max-width: 768px) {
    .hero-slider {
        height: 400px;
    }
    
    .slide-content h1 {
        font-size: 32px;
    }
    
    .slide-content p {
        font-size: 16px;
    }
    
    .stats-container {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .news-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- Hero Slider -->
<div class="hero-slider">
    <div class="slider-container">
        <?php if (!empty($featured_news)): ?>
            <?php foreach ($featured_news as $index => $news): ?>
            <div class="slide <?php echo $index === 0 ? 'active' : ''; ?>">
                <div class="slide-content">
                    <h1><?php echo htmlspecialchars($news['title']); ?></h1>
                    <p><?php echo htmlspecialchars(substr($news['summary'] ?? '', 0, 150)); ?><?php echo strlen($news['summary'] ?? '') > 150 ? '...' : ''; ?></p>
                    <?php if ($news['id'] > 0): ?>
                        <a href="chi-tiet-tin.php?id=<?php echo $news['id']; ?>" class="slide-btn">
                            Xem chi tiết <i class="fas fa-arrow-right"></i>
                        </a>
                    <?php else: ?>
                        <a href="tin-tuc.php" class="slide-btn">
                            Xem tin tức <i class="fas fa-arrow-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="slide active">
                <div class="slide-content">
                    <h1>Chào mừng đến với UBND Xã Long Hiệp</h1>
                    <p>Hệ thống quản lý thông tin và nhân sự tích hợp - Xã nông thôn mới với 30.272 người dân</p>
                    <a href="tin-tuc.php" class="slide-btn">
                        Khám phá ngay <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="slider-nav">
            <?php for ($i = 0; $i < max(count($featured_news), 1); $i++): ?>
            <div class="slider-dot <?php echo $i === 0 ? 'active' : ''; ?>" data-slide="<?php echo $i; ?>"></div>
            <?php endfor; ?>
        </div>
    </div>
</div>

<!-- Stats Section -->
<div class="stats-section">
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-newspaper"></i></div>
            <div class="stat-number"><?php echo $stats['news_count']; ?></div>
            <div class="stat-label">Tin tức đã đăng</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-video"></i></div>
            <div class="stat-number"><?php echo $stats['video_count']; ?></div>
            <div class="stat-label">Video hoạt động</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-number"><?php echo $stats['staff_count']; ?></div>
            <div class="stat-label">Cán bộ nhân viên</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-building"></i></div>
            <div class="stat-number"><?php echo $stats['departments']; ?></div>
            <div class="stat-label">Phòng ban</div>
        </div>
    </div>
</div>

<!-- Latest News -->
<div class="news-section">
    <div class="section-header">
        <h2>Tin tức mới nhất</h2>
        <p>Cập nhật thông tin hoạt động của UBND xã</p>
    </div>
    
    <div class="news-grid">
        <?php if (!empty($latest_news)): ?>
            <?php foreach ($latest_news as $news): ?>
            <div class="news-card">
                <?php if (!empty($news['image'])): ?>
                    <img src="<?php echo htmlspecialchars($news['image']); ?>" alt="<?php echo htmlspecialchars($news['title']); ?>" class="news-image">
                <?php else: ?>
                    <div class="news-image"></div>
                <?php endif; ?>
                
                <div class="news-content">
                    <span class="news-category"><?php echo htmlspecialchars($news['category_name'] ?? 'Tin tức'); ?></span>
                    <h3 class="news-title"><?php echo htmlspecialchars($news['title']); ?></h3>
                    <p class="news-summary"><?php echo htmlspecialchars(substr($news['summary'] ?? '', 0, 120)); ?>...</p>
                    
                    <div class="news-meta">
                        <span class="news-date">
                            <i class="far fa-calendar"></i>
                            <?php echo date('d/m/Y', strtotime($news['published_at'])); ?>
                        </span>
                        <a href="chi-tiet-tin.php?id=<?php echo $news['id']; ?>" class="news-link">
                            Đọc thêm <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; color: #999; grid-column: 1/-1;">Chưa có tin tức nào</p>
        <?php endif; ?>
    </div>
    
    <div style="text-align: center; margin-top: 40px;">
        <a href="tin-tuc.php" class="slide-btn" style="color: var(--primary); background: white; border: 2px solid var(--primary);">
            Xem tất cả tin tức <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</div>

<!-- Quick Links -->
<div class="quick-links-section">
    <div class="quick-links-container">
        <div class="section-header">
            <h2>Truy cập nhanh</h2>
            <p>Các chức năng và dịch vụ chính</p>
        </div>
        
        <div class="quick-links-grid">
            <a href="phong-ban.php" class="quick-link-card">
                <div class="quick-link-icon"><i class="fas fa-sitemap"></i></div>
                <div class="quick-link-title">Phòng ban</div>
                <div class="quick-link-desc">Cơ cấu tổ chức</div>
            </a>
            
            <a href="lanh-dao.php" class="quick-link-card">
                <div class="quick-link-icon"><i class="fas fa-user-tie"></i></div>
                <div class="quick-link-title">Lãnh đạo</div>
                <div class="quick-link-desc">Ban lãnh đạo UBND</div>
            </a>
            
            <a href="danh-ba-dien-thoai.php" class="quick-link-card">
                <div class="quick-link-icon"><i class="fas fa-address-book"></i></div>
                <div class="quick-link-title">Danh bạ</div>
                <div class="quick-link-desc">Thông tin liên hệ</div>
            </a>
            
            <a href="video.php" class="quick-link-card">
                <div class="quick-link-icon"><i class="fas fa-play-circle"></i></div>
                <div class="quick-link-title">Video</div>
                <div class="quick-link-desc">Hoạt động hình ảnh</div>
            </a>
            
            <a href="lien-he.php" class="quick-link-card">
                <div class="quick-link-icon"><i class="fas fa-envelope"></i></div>
                <div class="quick-link-title">Liên hệ</div>
                <div class="quick-link-desc">Gửi tin nhắn</div>
            </a>
            
            <a href="dang-nhap.php" class="quick-link-card">
                <div class="quick-link-icon"><i class="fas fa-sign-in-alt"></i></div>
                <div class="quick-link-title">Đăng nhập</div>
                <div class="quick-link-desc">Dành cho cán bộ</div>
            </a>
        </div>
    </div>
</div>

<script>
// Slider functionality
let currentSlide = 0;
const slides = document.querySelectorAll('.slide');
const dots = document.querySelectorAll('.slider-dot');

function showSlide(index) {
    slides.forEach(slide => slide.classList.remove('active'));
    dots.forEach(dot => dot.classList.remove('active'));
    
    if (index >= slides.length) currentSlide = 0;
    if (index < 0) currentSlide = slides.length - 1;
    
    slides[currentSlide].classList.add('active');
    dots[currentSlide].classList.add('active');
}

function nextSlide() {
    currentSlide++;
    showSlide(currentSlide);
}

// Auto slide every 5 seconds
setInterval(nextSlide, 5000);

// Dot navigation
dots.forEach((dot, index) => {
    dot.addEventListener('click', () => {
        currentSlide = index;
        showSlide(currentSlide);
    });
});
</script>

<?php include 'footer.php'; ?>
