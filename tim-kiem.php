<?php
header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'auth.php';

$page_title = "Tìm kiếm - UBND Xã Long Hiệp";
$keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];
$total_results = 0;

if (!empty($keyword)) {
    $conn = getDBConnection();
    
    if ($conn) {
        $search_term = '%' . $conn->real_escape_string($keyword) . '%';
        
        // Tìm trong tin tức
        $news_sql = "SELECT 'news' as type, id, title, summary as description, published_at as date, 'tin-tuc' as icon 
                     FROM news 
                     WHERE status = 'published' 
                     AND (title LIKE ? OR summary LIKE ? OR content LIKE ?)
                     ORDER BY published_at DESC 
                     LIMIT 20";
        
        $stmt = $conn->prepare($news_sql);
        $stmt->bind_param('sss', $search_term, $search_term, $search_term);
        $stmt->execute();
        $news_result = $stmt->get_result();
        
        while ($row = $news_result->fetch_assoc()) {
            $results[] = $row;
        }
        
        // Tìm trong video
        $video_sql = "SELECT 'video' as type, id, title, description, created_at as date, 'video' as icon 
                      FROM videos 
                      WHERE is_active = 1 
                      AND (title LIKE ? OR description LIKE ?)
                      ORDER BY created_at DESC 
                      LIMIT 20";
        
        $stmt = $conn->prepare($video_sql);
        $stmt->bind_param('ss', $search_term, $search_term);
        $stmt->execute();
        $video_result = $stmt->get_result();
        
        while ($row = $video_result->fetch_assoc()) {
            $results[] = $row;
        }
        
        // Tìm trong cán bộ
        $staff_sql = "SELECT 'staff' as type, id, name as title, position as description, created_at as date, 'user' as icon 
                      FROM department_staff 
                      WHERE is_active = 1 
                      AND (name LIKE ? OR position LIKE ? OR phone LIKE ?)
                      ORDER BY name ASC 
                      LIMIT 20";
        
        $stmt = $conn->prepare($staff_sql);
        $stmt->bind_param('sss', $search_term, $search_term, $search_term);
        $stmt->execute();
        $staff_result = $stmt->get_result();
        
        while ($row = $staff_result->fetch_assoc()) {
            $results[] = $row;
        }

        // Tìm trong phòng ban
        $dept_sql = "SELECT 'department' as type, id, name as title, description, 'phong-ban' as icon
                     FROM departments
                     WHERE status = 'active'
                     AND (name LIKE ? OR short_name LIKE ? OR description LIKE ? OR leader_name LIKE ?)
                     ORDER BY display_order ASC
                     LIMIT 10";

        $stmt = $conn->prepare($dept_sql);
        $stmt->bind_param('ssss', $search_term, $search_term, $search_term, $search_term);
        $stmt->execute();
        $dept_result = $stmt->get_result();

        while ($row = $dept_result->fetch_assoc()) {
            $results[] = $row;
        }

        // Tìm trong lãnh đạo
        $leader_sql = "SELECT 'leader' as type, id, name as title, position as description, image_path, 'user-tie' as icon
                       FROM leaders
                       WHERE is_active = 1
                       AND (name LIKE ? OR position LIKE ? OR responsibilities LIKE ?)
                       ORDER BY display_order ASC
                       LIMIT 10";

        $stmt = $conn->prepare($leader_sql);
        $stmt->bind_param('sss', $search_term, $search_term, $search_term);
        $stmt->execute();
        $leader_result = $stmt->get_result();

        while ($row = $leader_result->fetch_assoc()) {
            $results[] = $row;
        }
        
        $total_results = count($results);
    }
}

include 'header-menu.php';
?>

<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
.search-page {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
}

.search-header {
    background: var(--gradient-primary);
    padding: 60px 20px;
    border-radius: 15px;
    color: white;
    text-align: center;
    margin-bottom: 40px;
}

.search-header h1 {
    font-size: 36px;
    margin-bottom: 20px;
}

.search-box {
    max-width: 600px;
    margin: 0 auto;
    position: relative;
}

.search-box input {
    width: 100%;
    padding: 18px 60px 18px 25px;
    font-size: 16px;
    border: none;
    border-radius: 50px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
}

.search-box button {
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    background: var(--primary);
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 50px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
}

.search-box button:hover {
    background: #5568d3;
}

.search-results {
    margin-top: 40px;
}

.results-info {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
    margin-bottom: 30px;
}

.results-info h2 {
    font-size: 24px;
    color: #333;
    margin-bottom: 10px;
}

.results-info p {
    color: #666;
    font-size: 16px;
}

.result-item {
    background: white;
    padding: 25px;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    transition: all 0.3s;
    border-left: 4px solid var(--primary);
}

.result-item:hover {
    transform: translateX(5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.result-type {
    display: inline-block;
    padding: 5px 15px;
    background: var(--primary);
    color: white;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 15px;
}

.result-type.video {
    background: #e74c3c;
}

.result-type.staff {
    background: #27ae60;
}

.result-title {
    font-size: 22px;
    font-weight: 700;
    color: #333;
    margin-bottom: 10px;
}

.result-title a {
    color: #333;
    text-decoration: none;
}

.result-title a:hover {
    color: var(--primary);
}

.result-description {
    color: #666;
    font-size: 15px;
    line-height: 1.6;
    margin-bottom: 15px;
}

.result-meta {
    display: flex;
    align-items: center;
    gap: 20px;
    color: #999;
    font-size: 14px;
}

.result-meta i {
    margin-right: 5px;
}

.no-results {
    text-align: center;
    padding: 60px 20px;
}

.no-results i {
    font-size: 80px;
    color: #ddd;
    margin-bottom: 20px;
}

.no-results h3 {
    font-size: 24px;
    color: #666;
    margin-bottom: 10px;
}

.no-results p {
    color: #999;
    font-size: 16px;
}

@media (max-width: 768px) {
    .search-header h1 {
        font-size: 28px;
    }
    
    .result-title {
        font-size: 18px;
    }
}
</style>

<div class="search-page">
    <div class="search-header">
        <h1><i class="fas fa-search"></i> Tìm kiếm thông tin</h1>
        <form method="GET" action="tim-kiem.php" class="search-box">
            <input type="text" name="q" placeholder="Nhập từ khóa tìm kiếm..." value="<?php echo htmlspecialchars($keyword); ?>" required>
            <button type="submit"><i class="fas fa-search"></i> Tìm</button>
        </form>
    </div>
    
    <?php if (!empty($keyword)): ?>
        <div class="search-results">
            <?php if ($total_results > 0): ?>
                <div class="results-info">
                    <h2>Kết quả tìm kiếm</h2>
                    <p>Tìm thấy <strong><?php echo $total_results; ?></strong> kết quả cho từ khóa "<strong><?php echo htmlspecialchars($keyword); ?></strong>"</p>
                </div>
                
                <?php foreach ($results as $result): ?>
                    <div class="result-item">
                        <span class="result-type <?php echo $result['type']; ?>">
                            <i class="fas fa-<?php echo $result['icon']; ?>"></i>
                            <?php 
                            $typeLabels = ['news' => 'Tin tức', 'video' => 'Video', 'staff' => 'Cán bộ', 'department' => 'Phòng ban', 'leader' => 'Lãnh đạo'];
                            echo $typeLabels[$result['type']] ?? $result['type'];
                            ?>
                        </span>
                        
                        <h3 class="result-title">
                            <?php if ($result['type'] === 'news'): ?>
                                <a href="chi-tiet-tin.php?id=<?php echo $result['id']; ?>">
                                    <?php echo htmlspecialchars($result['title']); ?>
                                </a>
                            <?php elseif ($result['type'] === 'video'): ?>
                                <a href="video.php?id=<?php echo $result['id']; ?>">
                                    <?php echo htmlspecialchars($result['title']); ?>
                                </a>
                            <?php elseif ($result['type'] === 'department'): ?>
                                <a href="phong-ban.php">
                                    <?php echo htmlspecialchars($result['title']); ?>
                                </a>
                            <?php elseif ($result['type'] === 'leader'): ?>
                                <a href="chi-tiet-lanh-dao.php?id=<?php echo $result['id']; ?>">
                                    <?php echo htmlspecialchars($result['title']); ?>
                                </a>
                            <?php else: ?>
                                <a href="danh-ba-dien-thoai.php">
                                    <?php echo htmlspecialchars($result['title']); ?>
                                </a>
                            <?php endif; ?>
                        </h3>
                        
                        <p class="result-description">
                            <?php echo htmlspecialchars(substr($result['description'] ?? '', 0, 200)); ?>
                            <?php echo strlen($result['description'] ?? '') > 200 ? '...' : ''; ?>
                        </p>
                        
                        <div class="result-meta">
                            <span><i class="far fa-calendar"></i> <?php echo date('d/m/Y', strtotime($result['date'])); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <h3>Không tìm thấy kết quả</h3>
                    <p>Không có kết quả nào phù hợp với từ khóa "<strong><?php echo htmlspecialchars($keyword); ?></strong>"</p>
                    <p>Vui lòng thử lại với từ khóa khác</p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
