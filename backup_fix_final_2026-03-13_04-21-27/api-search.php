<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once 'config.php';

function searchNews($keyword, $category = 'all', $limit = 10)
{
    $conn = getDBConnection();
    if (!$conn) {
        return ['success' => false, 'message' => 'Không thể kết nối database'];
    }

    $conditions = ["n.status = 'published'"];
    $params = [];
    $types = '';

    if ($keyword !== '') {
        $searchValue = '%' . $keyword . '%';
        $conditions[] = '(n.title LIKE ? OR n.summary LIKE ? OR n.content LIKE ?)';
        $params[] = $searchValue;
        $params[] = $searchValue;
        $params[] = $searchValue;
        $types .= 'sss';
    }

    if ($category !== 'all') {
        $conditions[] = 'c.slug = ?';
        $params[] = $category;
        $types .= 's';
    }

    $whereSql = implode(' AND ', $conditions);
    $sql = "SELECT n.id, n.title, n.summary, n.image, n.published_at, c.name as category_name, c.slug as category_slug
            FROM news n
            LEFT JOIN categories c ON n.category_id = c.id
            WHERE {$whereSql}
            ORDER BY n.published_at DESC
            LIMIT ?";
    
    $params[] = $limit;
    $types .= 'i';

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $conn->close();
        return ['success' => false, 'message' => 'Lỗi chuẩn bị truy vấn'];
    }

    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Lỗi thực thi truy vấn'];
    }

    $result = $stmt->get_result();
    $news = [];
    while ($row = $result->fetch_assoc()) {
        $news[] = [
            'id' => (int) $row['id'],
            'title' => $row['title'],
            'summary' => substr(strip_tags($row['summary']), 0, 150) . '...',
            'image' => $row['image'],
            'published_at' => $row['published_at'],
            'category_name' => $row['category_name'],
            'category_slug' => $row['category_slug'],
            'url' => 'chi-tiet-tin.php?id=' . $row['id']
        ];
    }

    $stmt->close();
    $conn->close();

    return ['success' => true, 'data' => $news, 'count' => count($news)];
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $keyword = trim((string) ($_GET['q'] ?? ''));
    $category = trim((string) ($_GET['cat'] ?? 'all'));
    $limit = min(20, max(1, (int) ($_GET['limit'] ?? 10)));

    if ($keyword === '' && $category === 'all') {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập từ khóa tìm kiếm']);
        exit;
    }

    $result = searchNews($keyword, $category, $limit);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>