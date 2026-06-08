<?php
/**
 * Security Helper Functions
 * Cung cấp các chức năng bảo mật cho hệ thống
 */

// CSRF Token Management
class SecurityHelper {
    
    /**
     * Tạo CSRF token mới
     */
    public static function generateCSRFToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Lấy CSRF token hiện tại
     */
    public static function getCSRFToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return $_SESSION['csrf_token'] ?? self::generateCSRFToken();
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCSRFToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Tạo HTML input hidden cho CSRF token
     */
    public static function csrfField() {
        $token = self::getCSRFToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Validate CSRF token từ POST request
     */
    public static function validateRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            
            if (!self::validateCSRFToken($token)) {
                http_response_code(403);
                die('CSRF token validation failed. Request blocked for security.');
            }
        }
    }
    
    /**
     * Set security headers
     */
    public static function setSecurityHeaders() {
        // Prevent clickjacking
        header('X-Frame-Options: SAMEORIGIN');
        
        // XSS Protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy (basic)
        header("Content-Security-Policy: default-src 'self' https://cdnjs.cloudflare.com https://fonts.googleapis.com https://fonts.gstatic.com; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; img-src 'self' data: https:; media-src 'self' https:; font-src 'self' https://fonts.gstatic.com;");
        
        // Strict Transport Security (nếu dùng HTTPS)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
    
    /**
     * Sanitize input data
     */
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        
        // Remove null bytes
        $data = str_replace(chr(0), '', $data);
        
        // Trim whitespace
        $data = trim($data);
        
        // Convert special characters to HTML entities
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        
        return $data;
    }
    
    /**
     * Validate email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate phone number (Vietnam format)
     */
    public static function validatePhone($phone) {
        // Remove spaces and dots
        $phone = preg_replace('/[\s\.]/', '', $phone);
        
        // Check Vietnam phone format
        return preg_match('/^(0|\+84)[0-9]{9,10}$/', $phone);
    }
    
    /**
     * Hash password securely
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Rate limiting - Simple implementation
     */
    public static function checkRateLimit($identifier, $maxAttempts = 5, $timeWindow = 300) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $key = 'rate_limit_' . $identifier;
        $now = time();
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['attempts' => 0, 'first_attempt' => $now];
        }
        
        $data = $_SESSION[$key];
        
        // Reset if time window expired
        if ($now - $data['first_attempt'] > $timeWindow) {
            $_SESSION[$key] = ['attempts' => 1, 'first_attempt' => $now];
            return true;
        }
        
        // Check if exceeded
        if ($data['attempts'] >= $maxAttempts) {
            $waitTime = $timeWindow - ($now - $data['first_attempt']);
            return [
                'allowed' => false,
                'wait_time' => $waitTime,
                'message' => "Quá nhiều lần thử. Vui lòng đợi " . ceil($waitTime / 60) . " phút."
            ];
        }
        
        // Increment attempts
        $_SESSION[$key]['attempts']++;
        return true;
    }
    
    /**
     * Generate secure random string
     */
    public static function generateRandomString($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Validate file upload
     */
    public static function validateFileUpload($file, $allowedTypes = [], $maxSize = 5242880) {
        $errors = [];
        
        // Check if file was uploaded
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $errors[] = 'File không hợp lệ';
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            $errors[] = 'File quá lớn. Kích thước tối đa: ' . ($maxSize / 1024 / 1024) . 'MB';
        }
        
        // Check file type
        if (!empty($allowedTypes)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, $allowedTypes)) {
                $errors[] = 'Loại file không được phép';
            }
        }
        
        // Check for malicious content
        $content = file_get_contents($file['tmp_name'], false, null, 0, 1024);
        if (preg_match('/<\?php|<script|javascript:/i', $content)) {
            $errors[] = 'File chứa nội dung nguy hiểm';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'mime_type' => $mimeType ?? null
        ];
    }
    
    /**
     * Log security events
     */
    public static function logSecurityEvent($event, $details = []) {
        $logFile = 'logs/security.log';
        
        // Create logs directory if not exists
        if (!file_exists('logs')) {
            mkdir('logs', 0755, true);
        }
        
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'user_id' => $_SESSION['user_id'] ?? null,
            'details' => $details
        ];
        
        $logLine = json_encode($logEntry, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        file_put_contents($logFile, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Clean old sessions
     */
    public static function cleanOldSessions($maxLifetime = 3600) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['last_activity'])) {
            if (time() - $_SESSION['last_activity'] > $maxLifetime) {
                session_unset();
                session_destroy();
                return false;
            }
        }
        
        $_SESSION['last_activity'] = time();
        return true;
    }
}

// Auto-set security headers on every request
SecurityHelper::setSecurityHeaders();

// Clean old sessions
SecurityHelper::cleanOldSessions();
?>
