<?php
/**
 * AUDIT LOGGER - Ghi log tất cả hoạt động quan trọng
 */

require_once 'config.php';

class AuditLogger {
    private $conn;
    
    public function __construct() {
        $this->conn = getDBConnection();
    }
    
    /**
     * Ghi log hoạt động chung
     */
    public function log($actionType, $tableName = null, $recordId = null, $description = null, $oldValues = null, $newValues = null, $status = 'success') {
        if (!$this->conn) return false;
        
        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        $username = isset($_SESSION['username']) ? $_SESSION['username'] : null;
        $ipAddress = $this->getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? null;
        $requestUrl = $_SERVER['REQUEST_URI'] ?? null;
        
        $oldValuesJson = $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null;
        $newValuesJson = $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null;
        
        $stmt = $this->conn->prepare("
            INSERT INTO audit_logs 
            (user_id, username, action_type, table_name, record_id, old_values, new_values, 
             description, ip_address, user_agent, request_method, request_url, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        if (!$stmt) return false;
        
        $stmt->bind_param('isssissssssss', 
            $userId, $username, $actionType, $tableName, $recordId, 
            $oldValuesJson, $newValuesJson, $description, 
            $ipAddress, $userAgent, $requestMethod, $requestUrl, $status
        );
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Ghi log đăng nhập
     */
    public function logLogin($username, $userId = null, $success = true, $failureReason = null) {
        if (!$this->conn) return false;
        
        $loginType = $success ? 'success' : 'failed';
        $ipAddress = $this->getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $sessionId = session_id();
        
        // Parse user agent
        $browser = $this->getBrowser($userAgent);
        $os = $this->getOS($userAgent);
        $deviceType = $this->getDeviceType($userAgent);
        
        $stmt = $this->conn->prepare("
            INSERT INTO login_history 
            (user_id, username, login_type, ip_address, user_agent, browser, os, 
             device_type, failure_reason, session_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        if (!$stmt) return false;
        
        $stmt->bind_param('isssssssss', 
            $userId, $username, $loginType, $ipAddress, $userAgent, 
            $browser, $os, $deviceType, $failureReason, $sessionId
        );
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Ghi log đăng xuất
     */
    public function logLogout($username, $userId) {
        if (!$this->conn) return false;
        
        $ipAddress = $this->getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        $sessionId = session_id();
        
        $stmt = $this->conn->prepare("
            INSERT INTO login_history 
            (user_id, username, login_type, ip_address, user_agent, session_id)
            VALUES (?, ?, 'logout', ?, ?, ?)
        ");
        
        if (!$stmt) return false;
        
        $stmt->bind_param('issss', $userId, $username, $ipAddress, $userAgent, $sessionId);
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Ghi log thay đổi dữ liệu
     */
    public function logDataChange($tableName, $recordId, $action, $fieldName = null, $oldValue = null, $newValue = null, $reason = null) {
        if (!$this->conn) return false;
        
        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        $ipAddress = $this->getClientIP();
        
        $stmt = $this->conn->prepare("
            INSERT INTO data_change_logs 
            (user_id, table_name, record_id, action, field_name, old_value, new_value, change_reason, ip_address)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        if (!$stmt) return false;
        
        $stmt->bind_param('isissssss', 
            $userId, $tableName, $recordId, $action, $fieldName, 
            $oldValue, $newValue, $reason, $ipAddress
        );
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Ghi log truy cập file
     */
    public function logFileAccess($filePath, $fileName, $action, $fileType = null, $fileSize = 0) {
        if (!$this->conn) return false;
        
        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        $ipAddress = $this->getClientIP();
        
        $stmt = $this->conn->prepare("
            INSERT INTO file_access_logs 
            (user_id, file_path, file_name, file_type, action, file_size, ip_address)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        if (!$stmt) return false;
        
        $stmt->bind_param('isssis', $userId, $filePath, $fileName, $fileType, $action, $fileSize, $ipAddress);
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Ghi log lỗi hệ thống
     */
    public function logError($errorType, $errorMessage, $severity = 'medium', $stackTrace = null, $filePath = null, $lineNumber = null) {
        if (!$this->conn) return false;
        
        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        $ipAddress = $this->getClientIP();
        $requestUrl = $_SERVER['REQUEST_URI'] ?? null;
        $requestData = json_encode($_REQUEST, JSON_UNESCAPED_UNICODE);
        
        $stmt = $this->conn->prepare("
            INSERT INTO system_error_logs 
            (error_type, severity, error_message, stack_trace, file_path, line_number, 
             user_id, ip_address, request_url, request_data)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        if (!$stmt) return false;
        
        $stmt->bind_param('sssssiisss', 
            $errorType, $severity, $errorMessage, $stackTrace, $filePath, $lineNumber,
            $userId, $ipAddress, $requestUrl, $requestData
        );
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Lấy IP của client
     */
    private function getClientIP() {
        $ipAddress = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
        }
        return $ipAddress;
    }
    
    /**
     * Parse browser từ user agent
     */
    private function getBrowser($userAgent) {
        if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
        if (strpos($userAgent, 'Chrome') !== false) return 'Chrome';
        if (strpos($userAgent, 'Safari') !== false) return 'Safari';
        if (strpos($userAgent, 'Edge') !== false) return 'Edge';
        if (strpos($userAgent, 'Opera') !== false) return 'Opera';
        if (strpos($userAgent, 'MSIE') !== false || strpos($userAgent, 'Trident') !== false) return 'IE';
        return 'Unknown';
    }
    
    /**
     * Parse OS từ user agent
     */
    private function getOS($userAgent) {
        if (strpos($userAgent, 'Windows') !== false) return 'Windows';
        if (strpos($userAgent, 'Mac') !== false) return 'MacOS';
        if (strpos($userAgent, 'Linux') !== false) return 'Linux';
        if (strpos($userAgent, 'Android') !== false) return 'Android';
        if (strpos($userAgent, 'iOS') !== false || strpos($userAgent, 'iPhone') !== false) return 'iOS';
        return 'Unknown';
    }
    
    /**
     * Parse device type từ user agent
     */
    private function getDeviceType($userAgent) {
        if (strpos($userAgent, 'Mobile') !== false || strpos($userAgent, 'Android') !== false) return 'mobile';
        if (strpos($userAgent, 'Tablet') !== false || strpos($userAgent, 'iPad') !== false) return 'tablet';
        return 'desktop';
    }
}

// Khởi tạo global instance
$auditLogger = new AuditLogger();

/**
 * Helper functions
 */
function auditLog($actionType, $tableName = null, $recordId = null, $description = null, $oldValues = null, $newValues = null, $status = 'success') {
    global $auditLogger;
    return $auditLogger->log($actionType, $tableName, $recordId, $description, $oldValues, $newValues, $status);
}

function auditLogLogin($username, $userId = null, $success = true, $failureReason = null) {
    global $auditLogger;
    return $auditLogger->logLogin($username, $userId, $success, $failureReason);
}

function auditLogLogout($username, $userId) {
    global $auditLogger;
    return $auditLogger->logLogout($username, $userId);
}

function auditLogDataChange($tableName, $recordId, $action, $fieldName = null, $oldValue = null, $newValue = null, $reason = null) {
    global $auditLogger;
    return $auditLogger->logDataChange($tableName, $recordId, $action, $fieldName, $oldValue, $newValue, $reason);
}

function auditLogFileAccess($filePath, $fileName, $action, $fileType = null, $fileSize = 0) {
    global $auditLogger;
    return $auditLogger->logFileAccess($filePath, $fileName, $action, $fileType, $fileSize);
}

function auditLogError($errorType, $errorMessage, $severity = 'medium', $stackTrace = null, $filePath = null, $lineNumber = null) {
    global $auditLogger;
    return $auditLogger->logError($errorType, $errorMessage, $severity, $stackTrace, $filePath, $lineNumber);
}
