<?php
/**
 * Configuration File - Enhanced Security Version
 * Load environment variables và thiết lập kết nối database an toàn
 */

// Load environment configuration
require_once __DIR__ . '/env-loader.php';

// Load security helpers
require_once __DIR__ . '/security.php';

// Database Configuration từ .env
define('DB_HOST', EnvLoader::get('DB_HOST', 'localhost'));
define('DB_USER', EnvLoader::get('DB_USER', 'root'));
define('DB_PASS', EnvLoader::get('DB_PASS', ''));
define('DB_NAME', EnvLoader::get('DB_NAME', 'ubnd_longhiep'));
define('DB_CHARSET', EnvLoader::get('DB_CHARSET', 'utf8mb4'));

// Application Configuration
define('APP_NAME', EnvLoader::get('APP_NAME', 'UBND Xã Long Hiệp'));
define('APP_ENV', EnvLoader::get('APP_ENV', 'development'));
define('APP_DEBUG', EnvLoader::getBool('APP_DEBUG', true));
define('APP_URL', EnvLoader::get('APP_URL', 'http://localhost'));

// Security Configuration
define('SESSION_LIFETIME', EnvLoader::getInt('SESSION_LIFETIME', 3600));
define('CSRF_ENABLED', EnvLoader::getBool('CSRF_ENABLED', true));
define('RATE_LIMIT_ENABLED', EnvLoader::getBool('RATE_LIMIT_ENABLED', true));
define('MAX_LOGIN_ATTEMPTS', EnvLoader::getInt('MAX_LOGIN_ATTEMPTS', 5));
define('LOGIN_TIMEOUT', EnvLoader::getInt('LOGIN_TIMEOUT', 300));

// File Upload Configuration
define('MAX_UPLOAD_SIZE', EnvLoader::getInt('MAX_UPLOAD_SIZE', 5242880));

// Error Reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/logs/php-errors.log');
}

// Tạo kết nối database an toàn
$conn = null;
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Kiểm tra kết nối
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    // Set charset UTF-8
    $conn->set_charset(DB_CHARSET);
    
    // Set SQL mode an toàn
    $conn->query("SET SESSION sql_mode = 'STRICT_ALL_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE'");
    
} catch (Exception $e) {
    // Log error
    SecurityHelper::logSecurityEvent('database_connection_failed', [
        'error' => $e->getMessage()
    ]);
    
    if (APP_DEBUG) {
        die("Kết nối database thất bại.");
    } else {
        die("Hệ thống đang bảo trì. Vui lòng thử lại sau.");
    }
}

// Hàm tạo kết nối (để tương thích với code cũ)
function getDBConnection() {
    global $conn;
    return $conn;
}

// Hàm tạo mật khẩu hash - Enhanced với bcrypt
function hashPassword($password) {
    return SecurityHelper::hashPassword($password);
}

// Hàm kiểm tra mật khẩu
function verifyPassword($password, $hash) {
    return SecurityHelper::verifyPassword($password, $hash);
}

// Hàm bảo vệ khỏi SQL Injection
function escapeString($conn, $string) {
    return $conn->real_escape_string($string);
}

// Hàm sanitize input
function sanitizeInput($data) {
    return SecurityHelper::sanitizeInput($data);
}

// Hàm validate email
function validateEmail($email) {
    return SecurityHelper::validateEmail($email);
}

// Hàm validate phone
function validatePhone($phone) {
    return SecurityHelper::validatePhone($phone);
}

// Hàm ghi log hoạt động - Enhanced
function logActivity($conn, $user_id, $action, $table_name = null, $record_id = null, $description = null) {
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    try {
        // Check if table exists
        $tableCheck = $conn->query("SHOW TABLES LIKE 'activity_logs'");
        if ($tableCheck && $tableCheck->num_rows > 0) {
            $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, table_name, record_id, description, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("issssss", $user_id, $action, $table_name, $record_id, $description, $ip_address, $user_agent);
            $stmt->execute();
            $stmt->close();
        }
    } catch (Exception $e) {
        // Silent fail - không làm gián đoạn ứng dụng
        if (APP_DEBUG) {
            error_log("Log activity failed: " . $e->getMessage());
        }
    }
    
    // Also log to security log
    SecurityHelper::logSecurityEvent($action, [
        'user_id' => $user_id,
        'table' => $table_name,
        'record_id' => $record_id,
        'description' => $description
    ]);
}

// Hàm kiểm tra rate limit
function checkRateLimit($identifier, $maxAttempts = null, $timeWindow = null) {
    if (!RATE_LIMIT_ENABLED) {
        return true;
    }
    
    $maxAttempts = $maxAttempts ?? MAX_LOGIN_ATTEMPTS;
    $timeWindow = $timeWindow ?? LOGIN_TIMEOUT;
    
    return SecurityHelper::checkRateLimit($identifier, $maxAttempts, $timeWindow);
}

// Hàm tạo backup tự động
function createAutoBackup($type = 'daily') {
    if (!EnvLoader::getBool('BACKUP_ENABLED', true)) {
        return;
    }
    
    $backupPath = EnvLoader::get('BACKUP_PATH', 'backups/');
    
    if (!file_exists($backupPath)) {
        mkdir($backupPath, 0755, true);
    }
    
    $timestamp = date('Y-m-d_H-i-s');
    $backupFile = $backupPath . "backup_{$type}_{$timestamp}.sql";
    
    // Backup database bằng prepared-style approach
    $escapedUser = escapeshellarg(DB_USER);
    $escapedPass = DB_PASS !== '' ? escapeshellarg(DB_PASS) : '';
    $escapedHost = escapeshellarg(DB_HOST);
    $escapedDb = escapeshellarg(DB_NAME);
    $escapedFile = escapeshellarg($backupFile);
    
    $command = sprintf(
        'mysqldump --user=%s %s --host=%s %s > %s 2>/dev/null',
        $escapedUser,
        $escapedPass,
        $escapedHost,
        $escapedDb,
        $escapedFile
    );
    
    exec($command, $output, $returnVar);
    
    if ($returnVar === 0) {
        // Compress backup
        exec("gzip $backupFile");
        
        // Clean old backups
        cleanOldBackups($backupPath);
        
        return true;
    }
    
    return false;
}

// Hàm xóa backup cũ
function cleanOldBackups($backupPath) {
    $retentionDays = EnvLoader::getInt('BACKUP_RETENTION_DAYS', 30);
    $files = glob($backupPath . 'backup_*.sql.gz');
    
    foreach ($files as $file) {
        if (filemtime($file) < time() - ($retentionDays * 86400)) {
            unlink($file);
        }
    }
}

// Initialize session securely
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 1 : 0);
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_strict_mode', 1);
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    
    session_start();
    
    // Regenerate session ID periodically
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}
?>
