<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config.php';
require_once 'auth.php';

// Kiểm tra đăng nhập
try {
    authRequireCanBo();
} catch (Exception $e) {
    // Nếu có lỗi auth, chuyển hướng về trang chủ
    header('Location: index.php');
    exit();
}

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

// Format criteria scores để hiển thị trong form
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
        'key' => $key,
        'name' => $criteria_names[$key] ?? $key,
        'score' => $score,
        'weight' => $criteria_weights[$key] ?? 0
    ];
}

// Kiểm tra database connection
if (!isset($conn) || $conn->connect_error) {
    die('<div style="padding: 20px; background: #f8d7da; color: #721c24; border-radius: 5px; margin: 20px;">
        <h3>Lỗi kết nối database</h3>
        <p>Không thể kết nối đến database. Vui lòng kiểm tra cấu hình.</p>
        </div>');
}

// Xử lý form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Lấy dữ liệu từ form
        $criteria_scores = [];
        foreach ($evaluation['criteria_scores'] as $key => $oldScore) {
            $criteria_scores[$key] = floatval($_POST['criteria_score_' . $key] ?? $oldScore);
        }
        
        $final_score = floatval($_POST['final_score']);
        $rating = $_POST['rating'];
        $manager_comments = trim($_POST['manager_comments']);
        $strengths = trim($_POST['strengths']);
        $weaknesses = trim($_POST['weaknesses']);
        $recommendations = trim($_POST['recommendations'] ?? '');
        
        // Đọc lại tất cả đánh giá
        $all_evaluations = [];
        if (file_exists($evaluation_file)) {
            $all_evaluations = json_decode(file_get_contents($evaluation_file), true) ?? [];
        }
        
        // Tìm và cập nhật đánh giá
        foreach ($all_evaluations as $key => $eval) {
            if ($eval['id'] == $evaluation_id) {
                $all_evaluations[$key]['criteria_scores'] = $criteria_scores;
                $all_evaluations[$key]['final_score'] = $final_score;
                $all_evaluations[$key]['rating'] = $rating;
                $all_evaluations[$key]['manager_comments'] = $manager_comments;
                $all_evaluations[$key]['strengths'] = $strengths;
                $all_evaluations[$key]['weaknesses'] = $weaknesses;
                $all_evaluations[$key]['recommendations'] = $recommendations;
                $all_evaluations[$key]['updated_at'] = date('Y-m-d H:i:s');
                break;
            }
        }
        
        // Lưu lại file
        if (file_put_contents($evaluation_file, json_encode($all_evaluations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            $_SESSION['success_message'] = 'Cập nhật đánh giá thành công cho nhân viên: ' . $evaluation['employee_name'];
            header('Location: chi-tiet-danh-gia.php?id=' . $evaluation_id . '&employee_id=' . $employee_id);
            exit();
        } else {
            $_SESSION['error_message'] = 'Lỗi khi lưu đánh giá!';
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Có lỗi xảy ra.';
    }
}

// Kiểm tra bảng cần thiết có tồn tại không (bỏ vì đang dùng JSON)
$page_title = "Sửa đánh giá hiệu suất";

// Hiển thị thông báo lỗi nếu có
if (isset($_SESSION['error_message'])) {
    echo '<div style="padding: 15px; background: #f8d7da; color: #721c24; border-radius: 5px; margin: 20px;">
        <strong>Lỗi:</strong> ' . $_SESSION['error_message'] . '
        </div>';
    unset($_SESSION['error_message']);
}

// Hiển thị thông báo thành công nếu có
if (isset($_SESSION['success_message'])) {
    echo '<div style="padding: 15px; background: #d4edda; color: #155724; border-radius: 5px; margin: 20px;">
        <strong>Thành công:</strong> ' . $_SESSION['success_message'] . '
        </div>';
    unset($_SESSION['success_message']);
}

include 'header-menu.php';
?>

<link rel="stylesheet" href="hr-style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
.edit-evaluation {
    max-width: 1000px;
    margin: 30px auto;
    padding: 0 20px;
}

.page-header {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
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

.form-container {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-section {
    margin-bottom: 30px;
    padding-bottom: 30px;
    border-bottom: 2px solid #f0f0f0;
}

.form-section:last-child {
    border-bottom: none;
}

.form-section h3 {
    margin: 0 0 20px 0;
    color: #333;
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.form-group input[type="number"],
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    font-family: inherit;
}

.form-group textarea {
    min-height: 120px;
    resize: vertical;
}

.criteria-edit-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

.criteria-edit-table th,
.criteria-edit-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #f0f0f0;
}

.criteria-edit-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
}

.criteria-edit-table input[type="number"] {
    width: 80px;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-align: center;
}

.info-box {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid #f59e0b;
}

.info-box .label {
    color: #666;
    font-size: 14px;
    margin-bottom: 5px;
}

.info-box .value {
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.button-group {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 30px;
}

.btn {
    padding: 12px 30px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: #f59e0b;
    color: white;
}

.btn-primary:hover {
    background: #d97706;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

.readonly-field {
    background: #f8f9fa;
    color: #666;
}
</style>

<div class="edit-evaluation">
    <div class="page-header">
        <h1><i class="fas fa-edit"></i> Sửa đánh giá hiệu suất</h1>
        <p><?php echo date('d/m/Y', strtotime($evaluation['completed_at'])); ?> - <?php echo htmlspecialchars($evaluation['employee_name']); ?></p>
    </div>

    <div class="form-container">
        <form method="POST" action="">
            <!-- Thông tin nhân viên -->
            <div class="form-section">
                <h3><i class="fas fa-user"></i> Thông tin nhân viên</h3>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                    <div class="info-box">
                        <div class="label">Mã nhân viên</div>
                        <div class="value">NV<?php echo str_pad($evaluation['employee_id'], 3, '0', STR_PAD_LEFT); ?></div>
                    </div>
                    <div class="info-box">
                        <div class="label">Nhân viên</div>
                        <div class="value"><?php echo htmlspecialchars($evaluation['employee_name']); ?></div>
                    </div>
                    <div class="info-box">
                        <div class="label">Người đánh giá</div>
                        <div class="value"><?php echo htmlspecialchars($evaluation['evaluator_name']); ?></div>
                    </div>
                    <div class="info-box">
                        <div class="label">Ngày đánh giá</div>
                        <div class="value"><?php echo date('d/m/Y', strtotime($evaluation['completed_at'])); ?></div>
                    </div>
                </div>
            </div>

            <!-- Chi tiết điểm theo tiêu chí -->
            <div class="form-section">
                <h3><i class="fas fa-list-check"></i> Chi tiết điểm theo tiêu chí</h3>
                <table class="criteria-edit-table">
                    <thead>
                        <tr>
                            <th>Tiêu chí</th>
                            <th style="text-align: center;">Trọng số</th>
                            <th style="text-align: center;">Điểm hiện tại</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($evaluation['criteria_scores_display'] as $criteria): ?>
                        <tr>
                            <td><strong><?php echo $criteria['name']; ?></strong></td>
                            <td style="text-align: center;"><?php echo $criteria['weight']; ?>%</td>
                            <td style="text-align: center;">
                                <input type="number" name="criteria_score_<?php echo $criteria['key']; ?>" value="<?php echo $criteria['score']; ?>" min="0" max="5" step="0.1" required>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Điểm tổng hợp và xếp loại -->
            <div class="form-section">
                <h3><i class="fas fa-star"></i> Điểm tổng hợp và xếp loại</h3>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                    <div class="form-group">
                        <label for="final_score">Điểm tổng hợp (0-5)</label>
                        <input type="number" id="final_score" name="final_score" value="<?php echo $evaluation['final_score']; ?>" min="0" max="5" step="0.1" required>
                    </div>
                    <div class="form-group">
                        <label for="rating">Xếp loại</label>
                        <select id="rating" name="rating" required>
                            <option value="excellent" <?php echo $evaluation['rating'] === 'excellent' ? 'selected' : ''; ?>>Xuất sắc (4.5-5.0)</option>
                            <option value="good" <?php echo $evaluation['rating'] === 'good' ? 'selected' : ''; ?>>Tốt (3.5-4.4)</option>
                            <option value="satisfactory" <?php echo $evaluation['rating'] === 'satisfactory' ? 'selected' : ''; ?>>Đạt (2.5-3.4)</option>
                            <option value="needs_improvement" <?php echo $evaluation['rating'] === 'needs_improvement' ? 'selected' : ''; ?>>Cần cải thiện (0-2.4)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Nhận xét -->
            <div class="form-section">
                <h3><i class="fas fa-comments"></i> Nhận xét và đánh giá</h3>

                <div class="form-group">
                    <label for="manager_comments">Nhận xét của quản lý</label>
                    <textarea id="manager_comments" name="manager_comments" required><?php echo htmlspecialchars($evaluation['manager_comments'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="strengths">Điểm mạnh</label>
                    <textarea id="strengths" name="strengths" required><?php echo htmlspecialchars($evaluation['strengths'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="weaknesses">Điểm cần cải thiện</label>
                    <textarea id="weaknesses" name="weaknesses" required><?php echo htmlspecialchars($evaluation['weaknesses'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="recommendations">Đề xuất phát triển</label>
                    <textarea id="recommendations" name="recommendations"><?php echo htmlspecialchars($evaluation['recommendations'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Nút hành động -->
            <div class="button-group">
                <a href="chi-tiet-danh-gia.php?id=<?php echo $evaluation_id; ?>&employee_id=<?php echo $employee_id; ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Hủy
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Tự động cập nhật xếp loại khi thay đổi điểm
document.getElementById('final_score').addEventListener('input', function() {
    const score = parseFloat(this.value);
    const ratingSelect = document.getElementById('rating');
    
    if (score >= 4.5) {
        ratingSelect.value = 'excellent';
    } else if (score >= 3.5) {
        ratingSelect.value = 'good';
    } else if (score >= 2.5) {
        ratingSelect.value = 'satisfactory';
    } else {
        ratingSelect.value = 'needs_improvement';
    }
});

// Tự động tính điểm tổng từ các tiêu chí
const criteriaInputs = document.querySelectorAll('input[name^="criteria_score_"]');
const weights = {
    'quality': 0.30,
    'productivity': 0.25,
    'skills': 0.20,
    'attitude': 0.15,
    'teamwork': 0.10
};

function updateFinalScore() {
    let totalScore = 0;
    criteriaInputs.forEach(input => {
        const key = input.name.replace('criteria_score_', '');
        const score = parseFloat(input.value) || 0;
        totalScore += score * (weights[key] || 0);
    });
    
    document.getElementById('final_score').value = totalScore.toFixed(2);
    
    // Trigger rating update
    const event = new Event('input');
    document.getElementById('final_score').dispatchEvent(event);
}

criteriaInputs.forEach(input => {
    input.addEventListener('input', updateFinalScore);
});
</script>

<?php
include 'footer.php';
?>
