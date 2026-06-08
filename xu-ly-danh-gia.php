<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config.php';
require_once 'auth.php';

// Kiểm tra đăng nhập
authRequireCanBo();

$action = $_POST['action'] ?? '';

if ($action === 'create') {
    // Lấy dữ liệu từ form
    $employee_id = intval($_POST['employee_id'] ?? 0);
    $employee_name = $_POST['employee_name'] ?? '';
    
    // Điểm các tiêu chí
    $criteria_1_score = floatval($_POST['criteria_1_score'] ?? 0);
    $criteria_2_score = floatval($_POST['criteria_2_score'] ?? 0);
    $criteria_3_score = floatval($_POST['criteria_3_score'] ?? 0);
    $criteria_4_score = floatval($_POST['criteria_4_score'] ?? 0);
    $criteria_5_score = floatval($_POST['criteria_5_score'] ?? 0);
    
    $final_score = floatval($_POST['final_score'] ?? 0);
    $rating = $_POST['rating'] ?? '';
    
    // Nhận xét
    $strengths = $_POST['strengths'] ?? '';
    $weaknesses = $_POST['weaknesses'] ?? '';
    $recommendations = $_POST['recommendations'] ?? '';
    $manager_comments = $_POST['manager_comments'] ?? '';
    
    // Validate
    if (!$employee_id) {
        $_SESSION['error_message'] = 'Thông tin nhân viên không hợp lệ!';
        header('Location: tao-danh-gia.php?employee_id=' . $employee_id . '&employee_name=' . urlencode($employee_name));
        exit();
    }
    
    if ($final_score <= 0) {
        $_SESSION['error_message'] = 'Vui lòng nhập điểm cho các tiêu chí đánh giá!';
        header('Location: tao-danh-gia.php?employee_id=' . $employee_id . '&employee_name=' . urlencode($employee_name));
        exit();
    }
    
    // Lưu vào file JSON (không cần database)
    $evaluation_file = 'data/evaluations.json';
    
    // Đảm bảo thư mục data tồn tại
    if (!file_exists('data')) {
        mkdir('data', 0755, true);
    }
    
    // Đọc dữ liệu hiện có
    $evaluations = [];
    if (file_exists($evaluation_file)) {
        $evaluations = json_decode(file_get_contents($evaluation_file), true) ?? [];
    }
    
    // Tạo ID mới
    $new_id = count($evaluations) + 1;
    
    // Tạo bản ghi mới
    $new_evaluation = [
        'id' => $new_id,
        'employee_id' => $employee_id,
        'employee_name' => $employee_name,
        'criteria_scores' => [
            'quality' => $criteria_1_score,
            'productivity' => $criteria_2_score,
            'skills' => $criteria_3_score,
            'attitude' => $criteria_4_score,
            'teamwork' => $criteria_5_score
        ],
        'final_score' => $final_score,
        'rating' => $rating,
        'strengths' => $strengths,
        'weaknesses' => $weaknesses,
        'recommendations' => $recommendations,
        'manager_comments' => $manager_comments,
        'evaluator_name' => authDisplayName(),
        'evaluator_id' => $_SESSION['user_id'] ?? 0,
        'status' => 'finalized',
        'created_at' => date('Y-m-d H:i:s'),
        'completed_at' => date('Y-m-d H:i:s')
    ];
    
    // Thêm vào mảng
    $evaluations[] = $new_evaluation;
    
    // Lưu vào file
    if (file_put_contents($evaluation_file, json_encode($evaluations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        $_SESSION['success_message'] = 'Tạo đánh giá thành công cho nhân viên: ' . $employee_name;
        header('Location: quan-ly-danh-gia.php');
        exit();
    } else {
        $_SESSION['error_message'] = 'Lỗi khi lưu đánh giá!';
        header('Location: tao-danh-gia.php?employee_id=' . $employee_id . '&employee_name=' . urlencode($employee_name));
        exit();
    }
} else {
    header('Location: quan-ly-danh-gia.php');
    exit();
}
?>
