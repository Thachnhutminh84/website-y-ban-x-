<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config.php';
require_once 'auth.php';

// Kiểm tra đăng nhập
authRequireCanBo();

// Lấy ID đánh giá từ URL
$evaluation_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$employee_id = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;

if (!$evaluation_id) {
    $_SESSION['error_message'] = 'ID đánh giá không hợp lệ!';
    header('Location: quan-ly-danh-gia.php');
    exit();
}

// Đọc dữ liệu đánh giá từ file JSON
$evaluation_file = 'data/evaluations.json';
$evaluation = null;

if (file_exists($evaluation_file)) {
    $all_evaluations = json_decode(file_get_contents($evaluation_file), true) ?? [];
    
    // Tìm đánh giá theo ID
    foreach ($all_evaluations as $eval) {
        if ($eval['id'] == $evaluation_id) {
            $evaluation = $eval;
            break;
        }
    }
}

// Nếu không tìm thấy, quay lại danh sách
if (!$evaluation) {
    $_SESSION['error_message'] = 'Không tìm thấy đánh giá!';
    header('Location: quan-ly-danh-gia.php');
    exit();
}

// Mapping rating sang text
$rating_labels = [
    'excellent' => 'Xuất sắc',
    'good' => 'Tốt',
    'satisfactory' => 'Đạt',
    'needs_improvement' => 'Cần cải thiện'
];

$evaluation['rating_text'] = $rating_labels[$evaluation['rating']] ?? 'Chưa xếp loại';

// Format criteria scores để hiển thị
$criteria_names = [
    'quality' => 'Chất lượng công việc',
    'productivity' => 'Năng suất làm việc',
    'skills' => 'Kỹ năng chuyên môn',
    'attitude' => 'Tinh thần làm việc & Thái độ',
    'teamwork' => 'Khả năng làm việc nhóm'
];

$criteria_weights = [
    'quality' => 30,
    'productivity' => 25,
    'skills' => 20,
    'attitude' => 15,
    'teamwork' => 10
];

$evaluation['criteria_scores_display'] = [];
foreach ($evaluation['criteria_scores'] as $key => $score) {
    $evaluation['criteria_scores_display'][] = [
        'name' => $criteria_names[$key] ?? $key,
        'score' => $score,
        'weight' => $criteria_weights[$key] ?? 0
    ];
}

$page_title = "Chi tiết đánh giá hiệu suất";
include 'header-menu.php';
?>

<link rel="stylesheet" href="hr-style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
.evaluation-detail {
    max-width: 1200px;
    margin: 30px auto;
    padding: 0 20px;
}

.page-header {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    padding: 30px;
    border-radius: 10px;
    margin-bottom: 30px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.page-header h1 {
    margin: 0 0 10px 0;
    font-size: 28px;
}

.page-header p {
    margin: 0;
    opacity: 0.9;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.info-card {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.info-card .label {
    color: #666;
    font-size: 14px;
    margin-bottom: 5px;
}

.info-card .value {
    font-size: 18px;
    font-weight: bold;
    color: #333;
}

.score-summary {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 30px;
    text-align: center;
}

.score-circle {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    color: white;
}

.score-circle .score {
    font-size: 48px;
    font-weight: bold;
}

.score-circle .max {
    font-size: 18px;
    opacity: 0.9;
}

.rating-badge {
    display: inline-block;
    padding: 8px 24px;
    border-radius: 20px;
    font-size: 18px;
    font-weight: 600;
}

.rating-badge.excellent {
    background: #d1fae5;
    color: #065f46;
}

.criteria-table {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.criteria-table h3 {
    margin: 0 0 20px 0;
    color: #333;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #f0f0f0;
}

th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
}

.score-bar {
    display: flex;
    align-items: center;
    gap: 10px;
}

.bar {
    flex: 1;
    height: 8px;
    background: #e5e7eb;
    border-radius: 4px;
    overflow: hidden;
}

.bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #6366f1 0%, #8b5cf6 100%);
    transition: width 0.3s;
}

.comments-section {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.comments-section h3 {
    margin: 0 0 15px 0;
    color: #333;
}

.comment-box {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid #8b5cf6;
}

.comment-box .author {
    font-weight: 600;
    color: #8b5cf6;
    margin-bottom: 10px;
}

.comment-box .text {
    color: #333;
    line-height: 1.6;
    white-space: pre-line;
}

.btn-back {
    padding: 10px 20px;
    background: #6c757d;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-back:hover {
    background: #5a6268;
}
</style>

<div class="evaluation-detail">
    <div class="page-header">
        <h1><i class="fas fa-file-alt"></i> Chi tiết đánh giá hiệu suất</h1>
        <p><?php echo $evaluation['period']; ?> - <?php echo $evaluation['employee_name']; ?></p>
    </div>

    <div class="info-grid">
        <div class="info-card">
            <div class="label">Mã nhân viên</div>
            <div class="value">NV<?php echo str_pad($evaluation['employee_id'], 3, '0', STR_PAD_LEFT); ?></div>
        </div>
        <div class="info-card">
            <div class="label">Nhân viên</div>
            <div class="value"><?php echo htmlspecialchars($evaluation['employee_name']); ?></div>
        </div>
        <div class="info-card">
            <div class="label">Người đánh giá</div>
            <div class="value"><?php echo htmlspecialchars($evaluation['evaluator_name']); ?></div>
        </div>
        <div class="info-card">
            <div class="label">Ngày đánh giá</div>
            <div class="value"><?php echo date('d/m/Y', strtotime($evaluation['completed_at'])); ?></div>
        </div>
        <?php if (isset($evaluation['eval_month']) && isset($evaluation['eval_year'])): ?>
        <div class="info-card">
            <div class="label">Tháng đánh giá</div>
            <div class="value">Tháng <?php echo $evaluation['eval_month']; ?>/<?php echo $evaluation['eval_year']; ?></div>
        </div>
        <?php endif; ?>
    </div>

    <div class="score-summary">
        <div class="score-circle">
            <div class="score"><?php echo $evaluation['final_score']; ?></div>
            <div class="max">/5.0</div>
        </div>
        <h2 style="margin: 0 0 10px 0;">Điểm tổng hợp</h2>
        <span class="rating-badge <?php echo $evaluation['rating']; ?>">
            <?php echo $evaluation['rating_text']; ?>
        </span>
        <p style="color: #666; margin-top: 15px;">
            Người đánh giá: <strong><?php echo $evaluation['evaluator_name']; ?></strong>
        </p>
        <?php if (isset($evaluation['days_off'])): ?>
        <p style="color: #333; margin-top: 8px; font-size: 16px;">
            <i class="fas fa-calendar-minus" style="color: #ef4444;"></i>
            Số ngày nghỉ: <strong style="color: #ef4444;"><?php echo $evaluation['days_off']; ?> ngày</strong>
        </p>
        <?php endif; ?>
    </div>

    <div class="criteria-table">
        <h3><i class="fas fa-list-check"></i> Chi tiết điểm theo tiêu chí</h3>
        <table>
            <thead>
                <tr>
                    <th>Tiêu chí</th>
                    <th style="text-align: center;">Trọng số</th>
                    <th style="text-align: center;">Điểm</th>
                    <th>Biểu đồ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($evaluation['criteria_scores_display'] as $criteria): ?>
                <tr>
                    <td><strong><?php echo $criteria['name']; ?></strong></td>
                    <td style="text-align: center;"><?php echo $criteria['weight']; ?>%</td>
                    <td style="text-align: center;">
                        <span style="color: #8b5cf6; font-weight: 600; font-size: 18px;"><?php echo number_format($criteria['score'], 1); ?>/5</span>
                    </td>
                    <td>
                        <div class="score-bar">
                            <div class="bar">
                                <div class="bar-fill" style="width: <?php echo ($criteria['score'] / 5 * 100); ?>%;"></div>
                            </div>
                            <span style="font-size: 12px; color: #666;"><?php echo round($criteria['score'] / 5 * 100); ?>%</span>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="comments-section">
        <h3><i class="fas fa-comments"></i> Nhận xét và đánh giá</h3>
        
        <?php if (!empty($evaluation['manager_comments'])): ?>
        <div class="comment-box">
            <div class="author"><i class="fas fa-user-tie"></i> Nhận xét của quản lý</div>
            <div class="text"><?php echo nl2br(htmlspecialchars($evaluation['manager_comments'])); ?></div>
        </div>
        <?php endif; ?>

        <?php if (!empty($evaluation['strengths'])): ?>
        <div class="comment-box" style="border-left-color: #10b981;">
            <div class="author" style="color: #10b981;"><i class="fas fa-thumbs-up"></i> Điểm mạnh</div>
            <div class="text"><?php echo nl2br(htmlspecialchars($evaluation['strengths'])); ?></div>
        </div>
        <?php endif; ?>

        <?php if (!empty($evaluation['weaknesses'])): ?>
        <div class="comment-box" style="border-left-color: #f59e0b;">
            <div class="author" style="color: #f59e0b;"><i class="fas fa-lightbulb"></i> Điểm cần cải thiện</div>
            <div class="text"><?php echo nl2br(htmlspecialchars($evaluation['weaknesses'])); ?></div>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($evaluation['recommendations'])): ?>
        <div class="comment-box" style="border-left-color: #3b82f6;">
            <div class="author" style="color: #3b82f6;"><i class="fas fa-lightbulb"></i> Đề xuất phát triển</div>
            <div class="text"><?php echo nl2br(htmlspecialchars($evaluation['recommendations'])); ?></div>
        </div>
        <?php endif; ?>
        
        <?php if (empty($evaluation['manager_comments']) && empty($evaluation['strengths']) && empty($evaluation['weaknesses']) && empty($evaluation['recommendations'])): ?>
        <div style="padding: 40px; text-align: center; color: #999;">
            <i class="fas fa-info-circle" style="font-size: 32px; margin-bottom: 10px;"></i>
            <p>Chưa có nhận xét</p>
        </div>
        <?php endif; ?>
    </div>

    <div style="text-align: center; margin-top: 30px; display: flex; gap: 15px; justify-content: center;">
        <a href="quan-ly-danh-gia.php" class="btn-back">
            <i class="fas fa-arrow-left"></i>
            Quay lại danh sách
        </a>
        <button onclick="suaDanhGia(<?php echo $evaluation_id; ?>, <?php echo $employee_id; ?>)" class="btn-primary" style="background: #f59e0b;">
            <i class="fas fa-edit"></i>
            Sửa đánh giá
        </button>
    </div>
</div>

<script>
function suaDanhGia(evaluationId, employeeId) {
    if (confirm('Bạn có muốn chỉnh sửa đánh giá này?')) {
        // Chuyển đến trang sửa đánh giá
        window.location.href = 'sua-danh-gia.php?id=' + evaluationId + '&employee_id=' + employeeId;
    }
}
</script>

<?php
include 'footer.php';
?>
