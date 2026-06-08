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
    header('Location: index.php');
    exit();
}

$page_title = "Bảng lương cán bộ";

// Lấy dữ liệu từ database
$salary_data = [];
$conn = getDBConnection();

if ($conn) {
    // Lấy dữ liệu từ bảng department_staff join với departments
    $query = "SELECT 
                ds.id,
                ds.name,
                ds.position,
                d.name as department,
                COALESCE(ds.basic_salary, 5000000) as salary,
                COALESCE(ds.salary_coefficient, 0) as attendance_score,
                ds.status
              FROM department_staff ds
              LEFT JOIN departments d ON ds.department_id = d.id
              WHERE ds.status = 'active'
              ORDER BY ds.basic_salary DESC";
    
    $result = $conn->query($query);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $salary_data[] = [
                'id' => $row['id'],
                'name' => $row['na