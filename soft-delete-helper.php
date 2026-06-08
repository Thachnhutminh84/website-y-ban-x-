<?php
/**
 * SOFT DELETE HELPER - Hỗ trợ soft delete cho tất cả bảng
 */

require_once 'config.php';
require_once 'audit-logger.php';

class SoftDeleteHelper {
    private $conn;
    
    // Whitelist các bảng được phép soft delete
    private $allowedTables = [
        'news', 'media', 'users', 'departments', 'department_staff',
        'hr_employees', 'contact_messages', 'chat_messages',
        'performance_evaluations', 'salary_records'
    ];
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    /**
     * Validate table name
     */
    private function validateTableName($tableName) {
        if (!in_array($tableName, $this->allowedTables, true)) {
            return false;
        }
        return preg_match('/^[a-zA-Z0-9_]+$/', $tableName) === 1;
    }
    
    /**
     * Soft delete một record
     */
    public function softDelete($tableName, $recordId, $reason = null) {
        if (!$this->conn) {
            return ['success' => false, 'message' => 'Không thể kết nối database'];
        }
        
        if (!$this->validateTableName($tableName)) {
            return ['success' => false, 'message' => 'Bảng không hợp lệ'];
        }
        
        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        
        // Lấy dữ liệu cũ trước khi xóa
        $oldData = $this->getRecord($tableName, $recordId);
        
        $stmt = $this->conn->prepare("
            UPDATE $tableName 
            SET deleted_at = NOW(), deleted_by = ?
            WHERE id = ? AND deleted_at IS NULL
        ");
        
        if (!$stmt) {
            return ['success' => false, 'message' => 'Lỗi prepare statement'];
        }
        
        $stmt->bind_param('ii', $userId, $recordId);
        $result = $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        
        if ($result && $affectedRows > 0) {
            // Log audit
            auditLog('delete', $tableName, $recordId, $reason, $oldData, null);
            auditLogDataChange($tableName, $recordId, 'delete', null, json_encode($oldData), null, $reason);
            
            return [
                'success' => true,
                'message' => 'Xóa thành công',
                'affected_rows' => $affectedRows
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Không tìm thấy record hoặc đã bị xóa'
            ];
        }
    }
    
    /**
     * Restore một record đã bị soft delete
     */
    public function restore($tableName, $recordId, $reason = null) {
        if (!$this->conn) {
            return ['success' => false, 'message' => 'Không thể kết nối database'];
        }
        
        if (!$this->validateTableName($tableName)) {
            return ['success' => false, 'message' => 'Bảng không hợp lệ'];
        }
        
        // Lấy dữ liệu trước khi restore
        $oldData = $this->getRecord($tableName, $recordId, true);
        
        $stmt = $this->conn->prepare("
            UPDATE $tableName 
            SET deleted_at = NULL, deleted_by = NULL
            WHERE id = ? AND deleted_at IS NOT NULL
        ");
        
        if (!$stmt) {
            return ['success' => false, 'message' => 'Lỗi prepare statement'];
        }
        
        $stmt->bind_param('i', $recordId);
        $result = $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        
        if ($result && $affectedRows > 0) {
            // Log audit
            auditLog('restore', $tableName, $recordId, $reason, $oldData, null);
            auditLogDataChange($tableName, $recordId, 'restore', null, json_encode($oldData), null, $reason);
            
            return [
                'success' => true,
                'message' => 'Khôi phục thành công',
                'affected_rows' => $affectedRows
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Không tìm thấy record đã bị xóa'
            ];
        }
    }
    
    /**
     * Hard delete (xóa vĩnh viễn)
     */
    public function hardDelete($tableName, $recordId, $reason = null) {
        if (!$this->conn) {
            return ['success' => false, 'message' => 'Không thể kết nối database'];
        }
        
        if (!$this->validateTableName($tableName)) {
            return ['success' => false, 'message' => 'Bảng không hợp lệ'];
        }
        
        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        
        // Lấy dữ liệu trước khi xóa
        $oldData = $this->getRecord($tableName, $recordId, true);
        
        $stmt = $this->conn->prepare("DELETE FROM $tableName WHERE id = ?");
        
        if (!$stmt) {
            return ['success' => false, 'message' => 'Lỗi prepare statement'];
        }
        
        $stmt->bind_param('i', $recordId);
        $result = $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        
        if ($result && $affectedRows > 0) {
            // Log audit
            auditLog('delete', $tableName, $recordId, "HARD DELETE: $reason", $oldData, null);
            
            return [
                'success' => true,
                'message' => 'Xóa vĩnh viễn thành công',
                'affected_rows' => $affectedRows
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Không tìm thấy record'
            ];
        }
    }
    
    /**
     * Lấy danh sách records đã bị xóa
     */
    public function getDeleted($tableName, $limit = 50, $offset = 0) {
        if (!$this->conn) return [];
        
        if (!$this->validateTableName($tableName)) return [];
        
        $stmt = $this->conn->prepare("
            SELECT * FROM $tableName 
            WHERE deleted_at IS NOT NULL
            ORDER BY deleted_at DESC
            LIMIT ? OFFSET ?
        ");
        
        if (!$stmt) return [];
        
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $records = [];
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
        
        $stmt->close();
        return $records;
    }
    
    /**
     * Đếm số records đã bị xóa
     */
    public function countDeleted($tableName) {
        if (!$this->conn) return 0;
        
        if (!$this->validateTableName($tableName)) return 0;
        
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as total FROM $tableName WHERE deleted_at IS NOT NULL
        ");
        
        if (!$stmt) return 0;
        
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return (int)($row['total'] ?? 0);
    }
    
    /**
     * Xóa vĩnh viễn các records đã bị soft delete quá lâu
     */
    public function cleanOldDeleted($tableName, $days = 30) {
        if (!$this->conn) return 0;
        
        if (!$this->validateTableName($tableName)) return 0;
        
        $stmt = $this->conn->prepare("
            DELETE FROM $tableName 
            WHERE deleted_at IS NOT NULL 
            AND deleted_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        
        if (!$stmt) return 0;
        
        $stmt->bind_param('i', $days);
        $stmt->execute();
        $affectedRows = $stmt->affected_rows;
        $stmt->close();
        
        if ($affectedRows > 0) {
            auditLog('delete', $tableName, null, "Đã xóa vĩnh viễn $affectedRows records cũ (> $days ngày)");
        }
        
        return $affectedRows;
    }
    
    /**
     * Lấy thông tin một record
     */
    private function getRecord($tableName, $recordId, $includeDeleted = false) {
        if (!$this->conn) return null;
        
        if (!$this->validateTableName($tableName)) return null;
        
        $whereClause = $includeDeleted ? "id = ?" : "id = ? AND deleted_at IS NULL";
        
        $stmt = $this->conn->prepare("SELECT * FROM $tableName WHERE $whereClause LIMIT 1");
        
        if (!$stmt) return null;
        
        $stmt->bind_param('i', $recordId);
        $stmt->execute();
        $result = $stmt->get_result();
        $record = $result->fetch_assoc();
        $stmt->close();
        
        return $record;
    }
}

// Global instance
$softDeleteHelper = new SoftDeleteHelper();

/**
 * Helper functions
 */
function soft_delete($tableName, $recordId, $reason = null) {
    global $softDeleteHelper;
    return $softDeleteHelper->softDelete($tableName, $recordId, $reason);
}

function restore_deleted($tableName, $recordId, $reason = null) {
    global $softDeleteHelper;
    return $softDeleteHelper->restore($tableName, $recordId, $reason);
}

function hard_delete($tableName, $recordId, $reason = null) {
    global $softDeleteHelper;
    return $softDeleteHelper->hardDelete($tableName, $recordId, $reason);
}

function get_deleted($tableName, $limit = 50, $offset = 0) {
    global $softDeleteHelper;
    return $softDeleteHelper->getDeleted($tableName, $limit, $offset);
}

function count_deleted($tableName) {
    global $softDeleteHelper;
    return $softDeleteHelper->countDeleted($tableName);
}

function clean_old_deleted($tableName, $days = 30) {
    global $softDeleteHelper;
    return $softDeleteHelper->cleanOldDeleted($tableName, $days);
}
