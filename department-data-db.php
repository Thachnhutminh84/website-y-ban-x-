<?php
require_once 'config.php';
require_once 'department-data.php';

// Định nghĩa hàm getDepartmentStaffByCode TRƯỚC
function getDepartmentStaffByCode($departmentCode) {
    global $departments;
    
    // Fallback về file PHP trước
    if (!isset($departments[$departmentCode])) {
        return [];
    }
    
    $fallback = $departments[$departmentCode]['staff_members'] ?? [];
    
    // Thử lấy từ database
    try {
        $conn = getDBConnection();
        if (!$conn) {
            return $fallback;
        }
        
        $tableCheck = @$conn->query("SHOW TABLES LIKE 'department_staff'");
        if (!$tableCheck || $tableCheck->num_rows === 0) {
            return $fallback;
        }
        
        // Detect columns
        $colCode = @$conn->query("SHOW COLUMNS FROM department_staff LIKE 'department_code'");
        $hasDeptCodeCol = ($colCode && $colCode->num_rows > 0);
        $colId = @$conn->query("SHOW COLUMNS FROM department_staff LIKE 'department_id'");
        $hasDeptIdCol = ($colId && $colId->num_rows > 0);
        
        $staffMembers = [];
        
        // Query by department_code if column exists
        if ($hasDeptCodeCol) {
            $stmt = @$conn->prepare("SELECT id, name, position, phone, email, display_order 
                                    FROM department_staff 
                                    WHERE department_code = ? AND status = 'active'
                                    ORDER BY display_order ASC, id ASC");
            if ($stmt) {
                $stmt->bind_param('s', $departmentCode);
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()) {
                        $staffMembers[(int)$row['id']] = [
                            'id' => (int)$row['id'],
                            'name' => $row['name'],
                            'position' => $row['position'],
                            'role' => $row['position'],
                            'phone' => $row['phone'],
                            'email' => $row['email'] ?? ''
                        ];
                    }
                }
                $stmt->close();
            }
        }
        
        // Also query by department_id if column exists
        if ($hasDeptIdCol) {
            $dStmt = @$conn->prepare("SELECT id FROM departments WHERE code = ?");
            if ($dStmt) {
                $dStmt->bind_param('s', $departmentCode);
                $dStmt->execute();
                $dResult = $dStmt->get_result();
                $dRow = $dResult->fetch_assoc();
                $dStmt->close();
                
                if ($dRow) {
                    $deptId = (int)$dRow['id'];
                    $stmt = @$conn->prepare("SELECT id, name, position, phone, email, display_order 
                                            FROM department_staff 
                                            WHERE department_id = ? AND status = 'active'
                                            ORDER BY display_order ASC, id ASC");
                    if ($stmt) {
                        $stmt->bind_param('i', $deptId);
                        if ($stmt->execute()) {
                            $result = $stmt->get_result();
                            while ($row = $result->fetch_assoc()) {
                                $id = (int)$row['id'];
                                if (!isset($staffMembers[$id])) {
                                    $staffMembers[$id] = [
                                        'id' => $id,
                                        'name' => $row['name'],
                                        'position' => $row['position'],
                                        'role' => $row['position'],
                                        'phone' => $row['phone'],
                                        'email' => $row['email'] ?? ''
                                    ];
                                }
                            }
                        }
                        $stmt->close();
                    }
                }
            }
        }
        
        // Also check for salary columns and display_order
        $colSalary = @$conn->query("SHOW COLUMNS FROM department_staff LIKE 'basic_salary'");
        $hasSalaryCol = ($colSalary && $colSalary->num_rows > 0);
        
        // Merge: DB results take priority, fallback fills missing names
        if (!empty($staffMembers)) {
            $dbNames = array_map('strtolower', array_column($staffMembers, 'name'));
            $merged = array_values($staffMembers);
            foreach ($fallback as $fb) {
                $fbLower = strtolower($fb['name']);
                if (!in_array($fbLower, $dbNames)) {
                    $fb['id'] = -(count($merged) + 1) * 1000;
                    $merged[] = $fb;
                }
            }
            return $merged;
        }
        
        // All from fallback - add id
        $result = [];
        foreach ($fallback as $idx => $fb) {
            $fb['id'] = -(($idx + 1) * 1000);
            $result[] = $fb;
        }
        return $result;
        
    } catch (Exception $e) {
        return $fallback;
    }
}

// Sau đó mới định nghĩa getDepartmentByCode
function getDepartmentByCode($code) {
    global $departments;
    
    if (!isset($departments[$code])) {
        return null;
    }
    
    $department = $departments[$code];
    
    // Load staff từ database
    $department['staff_members'] = getDepartmentStaffByCode($code);
    
    return $department;
}

function getAllDepartments() {
    global $departments;
    return $departments;
}
?>
