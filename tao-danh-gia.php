<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config.php';
require_once 'auth.php';

// Kiểm tra đăng nhập và quyền
authRequireCanBo();

$employee_id = $_GET['employee_id'] ?? 0;
$employee_name = $_GET['employee_name'] ?? '';

if (!$employee_id) {
    header('Location: quan-ly-danh-gia.php');
    exit();
}

$page_title = "Tạo đánh giá mới";
include 'header-menu.php';
?>

<link rel="stylesheet" href="hr-style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
.evaluation-form-container {
    max-width: 900px;
    margin: 30px auto;
    padding: 0 20px;
}

.page-header {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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

.form-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 30px;
    margin-bottom: 20px;
}

.form-section {
    margin-bottom: 30px;
}

.form-section h3 {
    color: #333;
    margin: 0 0 20px 0;
    padding-bottom: 10px;
    border-bottom: 2px solid #10b981;
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
    color: #374151;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group input[type="date"],
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 15px;
    transition: border-color 0.3s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #10b981;
}

.form-group textarea {
    min-height: 100px;
    resize: vertical;
}

.criteria-grid {
    display: grid;
    gap: 20px;
}

.criteria-item {
    background: #f9fafb;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #10b981;
}

.criteria-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.criteria-name {
    font-weight: 600;
    color: #333;
    font-size: 16px;
}

.criteria-weight {
    background: #10b981;
    color: white;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
}

.score-input-group {
    display: flex;
    gap: 15px;
    align-items: center;
}

.score-input-group input {
    flex: 1;
}

.score-display {
    font-size: 24px;
    font-weight: 700;
    color: #10b981;
    min-width: 60px;
    text-align: center;
}

.rating-buttons {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

.rating-btn {
    flex: 1;
    padding: 10px;
    border: 2px solid #e5e7eb;
    background: white;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
}

.rating-btn:hover {
    border-color: #10b981;
    background: #f0fdf4;
}

.rating-btn.active {
    border-color: #10b981;
    background: #10b981;
    color: white;
}

.rating-btn.excellent { border-color: #10b981; }
.rating-btn.good { border-color: #3b82f6; }
.rating-btn.satisfactory { border-color: #f59e0b; }
.rating-btn.needs-improvement { border-color: #ef4444; }

.rating-btn.excellent.active { background: #10b981; }
.rating-btn.good.active { background: #3b82f6; }
.rating-btn.satisfactory.active { background: #f59e0b; }
.rating-btn.needs-improvement.active { background: #ef4444; }

.final-score-display {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    padding: 30px;
    border-radius: 10px;
    text-align: center;
    margin-top: 30px;
}

.final-score-display .score {
    font-size: 48px;
    font-weight: 700;
    margin: 10px 0;
}

.final-score-display .label {
    font-size: 18px;
    opacity: 0.9;
}

.action-buttons {
    display: flex;
    gap: 15px;
    margin-top: 30px;
}

.btn {
    flex: 1;
    padding: 15px 30px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.btn-primary {
    background: #10b981;
    color: white;
}

.btn-primary:hover {
    background: #059669;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

.info-box {
    background: #dbeafe;
    border-left: 4px solid #3b82f6;
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.info-box p {
    margin: 5px 0;
    color: #1e40af;
}

.info-box strong {
    color: #1e3a8a;
}
</style>

<div class="evaluation-form-container">
    <div class="page-header">
        <h1><i class="fas fa-clipboard-check"></i> Tạo đánh giá mới</h1>
        <p>Đánh giá hiệu suất làm việc của nhân viên</p>
    </div>

    <div class="info-box">
        <p><strong>Nhân viên:</strong> <?php echo htmlspecialchars($employee_name); ?></p>
        <p><strong>Người đánh giá:</strong> <?php echo htmlspecialchars(authDisplayName()); ?></p>
        <p><strong>Ngày đánh giá:</strong> <?php echo date('d/m/Y'); ?></p>
    </div>

    <form id="evaluationForm" method="POST" action="xu-ly-danh-gia.php">
        <input type="hidden" name="employee_id" value="<?php echo $employee_id; ?>">
        <input type="hidden" name="employee_name" value="<?php echo htmlspecialchars($employee_name); ?>">
        <input type="hidden" name="action" value="create">

        <!-- Tiêu chí đánh giá -->
        <div class="form-card">
            <div class="form-section">
                <h3><i class="fas fa-tasks"></i> Tiêu chí đánh giá</h3>
                
                <div class="criteria-grid">
                    <!-- Tiêu chí 1: Chất lượng công việc -->
                    <div class="criteria-item">
                        <div class="criteria-header">
                            <span class="criteria-name">1. Chất lượng công việc</span>
                            <span class="criteria-weight">Trọng số: 30%</span>
                        </div>
                        <div class="score-input-group">
                            <input type="number" name="criteria_1_score" id="criteria_1_score" 
                                   min="0" max="5" step="0.1" value="0" 
                                   onchange="updateFinalScore()" required>
                            <span class="score-display" id="criteria_1_display">0.0</span>
                        </div>
                        <div class="rating-buttons">
                            <button type="button" class="rating-btn excellent" onclick="setScore(1, 5)">Xuất sắc (5)</button>
                            <button type="button" class="rating-btn good" onclick="setScore(1, 4)">Tốt (4)</button>
                            <button type="button" class="rating-btn satisfactory" onclick="setScore(1, 3)">Đạt (3)</button>
                            <button type="button" class="rating-btn needs-improvement" onclick="setScore(1, 2)">Cần cải thiện (2)</button>
                        </div>
                    </div>

                    <!-- Tiêu chí 2: Năng suất làm việc -->
                    <div class="criteria-item">
                        <div class="criteria-header">
                            <span class="criteria-name">2. Năng suất làm việc</span>
                            <span class="criteria-weight">Trọng số: 25%</span>
                        </div>
                        <div class="score-input-group">
                            <input type="number" name="criteria_2_score" id="criteria_2_score" 
                                   min="0" max="5" step="0.1" value="0" 
                                   onchange="updateFinalScore()" required>
                            <span class="score-display" id="criteria_2_display">0.0</span>
                        </div>
                        <div class="rating-buttons">
                            <button type="button" class="rating-btn excellent" onclick="setScore(2, 5)">Xuất sắc (5)</button>
                            <button type="button" class="rating-btn good" onclick="setScore(2, 4)">Tốt (4)</button>
                            <button type="button" class="rating-btn satisfactory" onclick="setScore(2, 3)">Đạt (3)</button>
                            <button type="button" class="rating-btn needs-improvement" onclick="setScore(2, 2)">Cần cải thiện (2)</button>
                        </div>
                    </div>

                    <!-- Tiêu chí 3: Kỹ năng chuyên môn -->
                    <div class="criteria-item">
                        <div class="criteria-header">
                            <span class="criteria-name">3. Kỹ năng chuyên môn</span>
                            <span class="criteria-weight">Trọng số: 20%</span>
                        </div>
                        <div class="score-input-group">
                            <input type="number" name="criteria_3_score" id="criteria_3_score" 
                                   min="0" max="5" step="0.1" value="0" 
                                   onchange="updateFinalScore()" required>
                            <span class="score-display" id="criteria_3_display">0.0</span>
                        </div>
                        <div class="rating-buttons">
                            <button type="button" class="rating-btn excellent" onclick="setScore(3, 5)">Xuất sắc (5)</button>
                            <button type="button" class="rating-btn good" onclick="setScore(3, 4)">Tốt (4)</button>
                            <button type="button" class="rating-btn satisfactory" onclick="setScore(3, 3)">Đạt (3)</button>
                            <button type="button" class="rating-btn needs-improvement" onclick="setScore(3, 2)">Cần cải thiện (2)</button>
                        </div>
                    </div>

                    <!-- Tiêu chí 4: Tinh thần làm việc -->
                    <div class="criteria-item">
                        <div class="criteria-header">
                            <span class="criteria-name">4. Tinh thần làm việc & Thái độ</span>
                            <span class="criteria-weight">Trọng số: 15%</span>
                        </div>
                        <div class="score-input-group">
                            <input type="number" name="criteria_4_score" id="criteria_4_score" 
                                   min="0" max="5" step="0.1" value="0" 
                                   onchange="updateFinalScore()" required>
                            <span class="score-display" id="criteria_4_display">0.0</span>
                        </div>
                        <div class="rating-buttons">
                            <button type="button" class="rating-btn excellent" onclick="setScore(4, 5)">Xuất sắc (5)</button>
                            <button type="button" class="rating-btn good" onclick="setScore(4, 4)">Tốt (4)</button>
                            <button type="button" class="rating-btn satisfactory" onclick="setScore(4, 3)">Đạt (3)</button>
                            <button type="button" class="rating-btn needs-improvement" onclick="setScore(4, 2)">Cần cải thiện (2)</button>
                        </div>
                    </div>

                    <!-- Tiêu chí 5: Khả năng làm việc nhóm -->
                    <div class="criteria-item">
                        <div class="criteria-header">
                            <span class="criteria-name">5. Khả năng làm việc nhóm</span>
                            <span class="criteria-weight">Trọng số: 10%</span>
                        </div>
                        <div class="score-input-group">
                            <input type="number" name="criteria_5_score" id="criteria_5_score" 
                                   min="0" max="5" step="0.1" value="0" 
                                   onchange="updateFinalScore()" required>
                            <span class="score-display" id="criteria_5_display">0.0</span>
                        </div>
                        <div class="rating-buttons">
                            <button type="button" class="rating-btn excellent" onclick="setScore(5, 5)">Xuất sắc (5)</button>
                            <button type="button" class="rating-btn good" onclick="setScore(5, 4)">Tốt (4)</button>
                            <button type="button" class="rating-btn satisfactory" onclick="setScore(5, 3)">Đạt (3)</button>
                            <button type="button" class="rating-btn needs-improvement" onclick="setScore(5, 2)">Cần cải thiện (2)</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Điểm tổng kết -->
        <div class="final-score-display">
            <div class="label">Điểm tổng kết</div>
            <div class="score" id="final_score_display">0.0</div>
            <div class="label" id="rating_text">Chưa đánh giá</div>
            <input type="hidden" name="final_score" id="final_score" value="0">
            <input type="hidden" name="rating" id="rating" value="">
        </div>

        <!-- Nhận xét -->
        <div class="form-card">
            <div class="form-section">
                <h3><i class="fas fa-comment-alt"></i> Nhận xét và đề xuất</h3>
                
                <div class="form-group">
                    <label for="strengths">Điểm mạnh</label>
                    <textarea id="strengths" name="strengths" placeholder="Nhập các điểm mạnh của nhân viên..."></textarea>
                </div>

                <div class="form-group">
                    <label for="weaknesses">Điểm cần cải thiện</label>
                    <textarea id="weaknesses" name="weaknesses" placeholder="Nhập các điểm cần cải thiện..."></textarea>
                </div>

                <div class="form-group">
                    <label for="recommendations">Đề xuất phát triển</label>
                    <textarea id="recommendations" name="recommendations" placeholder="Nhập các đề xuất để phát triển..."></textarea>
                </div>

                <div class="form-group">
                    <label for="manager_comments">Nhận xét của quản lý</label>
                    <textarea id="manager_comments" name="manager_comments" placeholder="Nhập nhận xét tổng quan..."></textarea>
                </div>
            </div>
        </div>

        <!-- Nút hành động -->
        <div class="action-buttons">
            <a href="quan-ly-danh-gia.php" class="btn btn-secondary">
                <i class="fas fa-times"></i> Hủy bỏ
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Lưu đánh giá
            </button>
        </div>
    </form>
</div>

<script>
// Trọng số các tiêu chí
const weights = {
    1: 0.30, // Chất lượng công việc
    2: 0.25, // Năng suất
    3: 0.20, // Kỹ năng
    4: 0.15, // Tinh thần
    5: 0.10  // Làm việc nhóm
};

function setScore(criteriaNum, score) {
    const input = document.getElementById(`criteria_${criteriaNum}_score`);
    input.value = score;
    updateFinalScore();
}

function updateFinalScore() {
    let totalScore = 0;
    
    // Tính điểm tổng theo trọng số
    for (let i = 1; i <= 5; i++) {
        const score = parseFloat(document.getElementById(`criteria_${i}_score`).value) || 0;
        document.getElementById(`criteria_${i}_display`).textContent = score.toFixed(1);
        totalScore += score * weights[i];
    }
    
    // Hiển thị điểm tổng
    document.getElementById('final_score_display').textContent = totalScore.toFixed(1);
    document.getElementById('final_score').value = totalScore.toFixed(2);
    
    // Xác định xếp loại
    let rating = '';
    let ratingText = '';
    
    if (totalScore >= 4.5) {
        rating = 'excellent';
        ratingText = 'Xuất sắc';
    } else if (totalScore >= 3.5) {
        rating = 'good';
        ratingText = 'Tốt';
    } else if (totalScore >= 2.5) {
        rating = 'satisfactory';
        ratingText = 'Đạt';
    } else if (totalScore > 0) {
        rating = 'needs_improvement';
        ratingText = 'Cần cải thiện';
    } else {
        ratingText = 'Chưa đánh giá';
    }
    
    document.getElementById('rating_text').textContent = ratingText;
    document.getElementById('rating').value = rating;
}

// Validate form trước khi submit
document.getElementById('evaluationForm').addEventListener('submit', function(e) {
    const finalScore = parseFloat(document.getElementById('final_score').value);
    
    if (finalScore === 0) {
        e.preventDefault();
        alert('Vui lòng nhập điểm cho ít nhất một tiêu chí đánh giá!');
        return false;
    }
    
    if (!confirm('Bạn có chắc chắn muốn lưu đánh giá này không?')) {
        e.preventDefault();
        return false;
    }
});

// Khởi tạo
updateFinalScore();
</script>

<?php
include 'footer.php';
?>
