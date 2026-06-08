<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';

function newsBindParams(mysqli_stmt $stmt, $types, array $params)
{
    if ($types === '') {
        return true;
    }

    $bindValues = [$types];
    foreach ($params as $key => $value) {
        $bindValues[] = &$params[$key];
    }

    return call_user_func_array([$stmt, 'bind_param'], $bindValues);
}

function newsBuildPageUrl($category, $keyword, $page)
{
    $query = ['page' => max(1, (int) $page)];
    if ($category !== 'all') {
        $query['cat'] = $category;
    }
    if ($keyword !== '') {
        $query['keyword'] = $keyword;
    }

    return 'tin-tuc.php?' . http_build_query($query);
}

function newsExcerpt($text, $length = 150)
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

$isLoggedIn = authIsLoggedIn();
$isAdmin = authIsAdmin();
$canManageNews = authCanManageNews();
$currentRole = authCurrentRole();
$displayName = $isLoggedIn ? authDisplayName() : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tin Tức - Thông Báo - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css?v=2.1">
    <link rel="stylesheet" href="citizen-services.css?v=1.1">
    <script src="dropdown.js"></script>
</head>
<body>
    <!-- Header thống nhất -->
        <?php include 'menu-don-gian.php'; ?>

    <script>
        function toggleDropdown(event) {
            event.preventDefault();
            event.stopPropagation();
            const button = event.target;
            const dropdown = button.parentElement;
            const isOpen = dropdown.classList.contains('open');
            
            // Đóng tất cả dropdown khác
            document.querySelectorAll('.dropdown.open').forEach(item => {
                item.classList.remove('open');
            });
            
            // Toggle dropdown hiện tại
            if (!isOpen) {
                dropdown.classList.add('open');
            }
        }

        // Đóng dropdown khi click bên ngoài
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown.open').forEach(item => {
                    item.classList.remove('open');
                });
            }
        });
    </script>

    <main>
        <?php
        $category = isset($_GET['cat']) ? $_GET['cat'] : 'all';
        $keyword = trim((string) ($_GET['keyword'] ?? ''));
        $currentPage = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 6;
        
        // Định nghĩa tiêu đề và mô tả cho từng danh mục
        $categories = [
            'all' => ['title' => 'Tin Tức - Thông Báo', 'desc' => 'Cập nhật thông tin mới nhất từ UBND Xã Long Hiệp', 'id' => 0],
            'xay-dung-dang' => ['title' => 'Công tác xây dựng Đảng', 'desc' => 'Thông tin về công tác xây dựng và phát triển Đảng bộ xã', 'id' => 1],
            'mat-tran' => ['title' => 'Mặt trận đoàn thể', 'desc' => 'Hoạt động của Mặt trận Tổ quốc và các đoàn thể', 'id' => 2],
            'an-ninh' => ['title' => 'An ninh trật tự', 'desc' => 'Thông tin về công tác đảm bảo an ninh trật tự', 'id' => 3],
            'su-kien' => ['title' => 'Tin tức sự kiện', 'desc' => 'Các sự kiện và hoạt động nổi bật', 'id' => 4],
            'tuyen-truyen' => ['title' => 'Thông tin tuyên truyền', 'desc' => 'Công tác tuyên truyền và phổ biến chính sách', 'id' => 5],
            'giao-duc' => ['title' => 'Giáo dục và đào tạo', 'desc' => 'Thông tin về giáo dục và đào tạo', 'id' => 6]
        ];

        if (!array_key_exists($category, $categories)) {
            $category = 'all';
        }

        $currentCat = $categories[$category];
        $addNewsCategory = $category === 'all' ? 'su-kien' : $category;
        $filtered_news = [];
        $totalNews = 0;
        $pageError = null;

        try {
            $conn = getDBConnection();
            $conditions = ["n.status = 'published'"];
            $params = [];
            $types = '';

            if ($category !== 'all') {
                $conditions[] = 'n.category_id = ?';
                $params[] = (int) $currentCat['id'];
                $types .= 'i';
            }

            if ($keyword !== '') {
                $searchValue = '%' . $keyword . '%';
                $conditions[] = '(n.title LIKE ? OR n.summary LIKE ? OR c.name LIKE ?)';
                $params[] = $searchValue;
                $params[] = $searchValue;
                $params[] = $searchValue;
                $types .= 'sss';
            }

            $whereSql = implode(' AND ', $conditions);
            $countSql = "SELECT COUNT(*) AS total
                         FROM news n
                         LEFT JOIN categories c ON n.category_id = c.id
                         WHERE {$whereSql}";

            $countStmt = $conn->prepare($countSql);
            if ($countStmt && newsBindParams($countStmt, $types, $params) && $countStmt->execute()) {
                $countResult = $countStmt->get_result();
                $countRow = $countResult->fetch_assoc();
                $totalNews = (int) ($countRow['total'] ?? 0);
                $countStmt->close();
            } else {
                $pageError = 'Không thể đếm số lượng tin tức.';
            }

            $totalPages = max(1, (int) ceil($totalNews / $perPage));
            if ($currentPage > $totalPages) {
                $currentPage = $totalPages;
            }
            $offset = ($currentPage - 1) * $perPage;

            if ($pageError === null) {
                $listSql = "SELECT n.id, n.title, n.summary, n.image, n.published_at, c.slug AS category_slug
                            FROM news n
                            LEFT JOIN categories c ON n.category_id = c.id
                            WHERE {$whereSql}
                            ORDER BY n.published_at DESC, n.id DESC
                            LIMIT ?, ?";
                $listParams = $params;
                $listParams[] = $offset;
                $listParams[] = $perPage;
                $listTypes = $types . 'ii';

                $stmt = $conn->prepare($listSql);
                if ($stmt && newsBindParams($stmt, $listTypes, $listParams) && $stmt->execute()) {
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()) {
                        $filtered_news[] = [
                            'id' => $row['id'],
                            'title' => $row['title'],
                            'summary' => $row['summary'],
                            'image' => $row['image'],
                            'date' => $row['published_at'],
                            'category' => $row['category_slug']
                        ];
                    }
                    $stmt->close();
                } else {
                    $pageError = 'Không thể tải danh sách tin tức.';
                }
            }

            $conn->close();
        } catch (Throwable $e) {
            $pageError = 'Không thể kết nối cơ sở dữ liệu để tải tin tức.';
            $totalPages = 1;
        }

        if (!isset($totalPages)) {
            $totalPages = 1;
        }
        ?>
        
        <section class="page-header">
            <div class="container">
                <h2><?php echo $currentCat['title']; ?></h2>
                <p><?php echo $currentCat['desc']; ?></p>
                <?php if ($canManageNews): ?>
                    <div class="admin-actions">
                        <a href="them-tin.php?cat=<?php echo htmlspecialchars($addNewsCategory, ENT_QUOTES, 'UTF-8'); ?>" class="btn-admin btn-add">+ Thêm tin mới</a>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="news-page">
            <div class="container">
                <div class="filters-card">
                    <h3>Tìm kiếm và lọc tin tức</h3>
                    <p>Tìm nhanh bài viết theo từ khóa hoặc chuyển ngay sang danh mục bạn cần theo dõi.</p>
                    <form method="GET" class="filters-form">
                        <div class="filters-form__row">
                            <div class="field-span-8">
                                <label for="keyword">Từ khóa</label>
                                <div class="search-input-container">
                                    <input type="text" id="keyword" name="keyword" value="<?php echo htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Nhập tiêu đề, tóm tắt hoặc tên danh mục">
                                </div>
                            </div>
                            <div class="field-span-4">
                                <label for="cat">Danh mục</label>
                                <select id="cat" name="cat">
                                    <?php foreach ($categories as $slug => $catItem): ?>
                                        <option value="<?php echo htmlspecialchars($slug, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $category === $slug ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($catItem['title'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="filters-actions">
                            <button type="submit" class="filters-button">Áp dụng bộ lọc</button>
                            <a href="tin-tuc.php<?php echo $category !== 'all' ? '?cat=' . urlencode($category) : ''; ?>" class="ghost-button">Xóa từ khóa</a>
                            <a href="tin-tuc.php" class="text-button">Về danh sách đầy đủ</a>
                        </div>
                    </form>
                </div>

                <?php if ($pageError): ?>
                    <div class="flash-panel is-error">
                        <?php echo htmlspecialchars($pageError, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php else: ?>
                    <div class="results-bar">
                        <div class="results-count">
                            <?php echo $totalNews; ?> bài viết
                        </div>
                        <div class="results-meta">
                            <?php if ($keyword !== ''): ?>
                                Kết quả cho từ khóa <strong><?php echo htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8'); ?></strong>
                            <?php else: ?>
                                Trang <?php echo $currentPage; ?> / <?php echo $totalPages; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!$pageError && count($filtered_news) > 0): ?>
                    <div class="news-grid">
                        <?php foreach ($filtered_news as $news): ?>
                            <article class="news-item">
                                <img src="<?php echo htmlspecialchars($news['image']); ?>" alt="<?php echo htmlspecialchars($news['title']); ?>" onerror="this.src='images/news-default.jpg'">
                                <div class="news-content">
                                    <h3><?php echo htmlspecialchars($news['title']); ?></h3>
                                    <p class="date">📅 <?php echo date('d/m/Y', strtotime($news['date'])); ?></p>
                                    <p><?php echo htmlspecialchars(newsExcerpt($news['summary'], 150), ENT_QUOTES, 'UTF-8'); ?></p>
                                    <div class="news-actions">
                                        <a href="chi-tiet-tin.php?id=<?php echo $news['id']; ?>" class="read-more">Xem thêm →</a>
                                        <?php if ($canManageNews): ?>
                                            <div class="admin-buttons">
                                                <a href="sua-tin.php?id=<?php echo $news['id']; ?>" class="btn-edit">✏️ Sửa</a>
                                                <?php if ($isAdmin): ?>
                                                    <a href="#" class="btn-delete" onclick="deleteNews(event, '<?php echo $news['id']; ?>', this)">🗑️ Xóa</a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($currentPage > 1): ?>
                                <a href="<?php echo htmlspecialchars(newsBuildPageUrl($category, $keyword, $currentPage - 1), ENT_QUOTES, 'UTF-8'); ?>" class="pagination__link">←</a>
                            <?php endif; ?>

                            <?php for ($page = 1; $page <= $totalPages; $page++): ?>
                                <?php if ($page === $currentPage): ?>
                                    <span class="pagination__current"><?php echo $page; ?></span>
                                <?php else: ?>
                                    <a href="<?php echo htmlspecialchars(newsBuildPageUrl($category, $keyword, $page), ENT_QUOTES, 'UTF-8'); ?>" class="pagination__link"><?php echo $page; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($currentPage < $totalPages): ?>
                                <a href="<?php echo htmlspecialchars(newsBuildPageUrl($category, $keyword, $currentPage + 1), ENT_QUOTES, 'UTF-8'); ?>" class="pagination__link">→</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php elseif (!$pageError): ?>
                    <div class="empty-panel">
                        <p style="font-size: 20px; color: #7f8c8d;">📰 Chưa có tin tức phù hợp với bộ lọc hiện tại.</p>
                        <?php if ($canManageNews): ?>
                            <a href="them-tin.php?cat=<?php echo htmlspecialchars($addNewsCategory, ENT_QUOTES, 'UTF-8'); ?>" style="display: inline-block; margin-top: 20px; padding: 12px 24px; background: #c41e3a; color: white; text-decoration: none; border-radius: 8px;">+ Thêm tin tức đầu tiên</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>

    <script src="search-widget.js?v=1.0"></script>
    <script>
function deleteNews(event, newsId, element) {
    event.preventDefault();
    
    if (!confirm('Bạn có chắc chắn muốn xóa tin tức này?')) {
        return;
    }
    
    // Gửi request xóa
    fetch('xoa-tin.php?id=' + newsId, {
        method: 'GET'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Xóa phần tử khỏi DOM
            const newsItem = element.closest('.news-item');
            newsItem.style.animation = 'fadeOut 0.3s ease';
            setTimeout(() => {
                newsItem.remove();
                
                // Kiểm tra nếu không còn tin nào
                const newsGrid = document.querySelector('.news-grid');
                if (newsGrid && newsGrid.children.length === 0) {
                    location.reload();
                }
            }, 300);
            
            showNotification('Đã xóa tin tức thành công!', 'success');
        } else {
            showNotification('Lỗi: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Có lỗi xảy ra khi xóa tin tức', 'error');
        console.error('Error:', error);
    });
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = 'notification ' + type;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px;
        background: ${type === 'success' ? '#27ae60' : '#e74c3c'};
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        z-index: 10000;
        font-size: 16px;
        font-weight: 600;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    // Tự động ẩn sau 3 giây
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// CSS Animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
    
    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: scale(1);
        }
        to {
            opacity: 0;
            transform: scale(0.9);
        }
    }
`;
document.head.appendChild(style);
</script>

</body>
</html>
