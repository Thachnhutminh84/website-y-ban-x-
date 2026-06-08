<?php
/**
 * Environment Configuration Loader
 * Load configuration từ file .env
 */

class EnvLoader {
    private static $loaded = false;
    private static $config = [];
    
    /**
     * Load .env file
     */
    public static function load($path = '.env') {
        if (self::$loaded) {
            return;
        }
        
        if (!file_exists($path)) {
            // Nếu không có .env, dùng giá trị mặc định
            self::loadDefaults();
            self::$loaded = true;
            return;
        }
        
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parse KEY=VALUE
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes
                $value = trim($value, '"\'');
                
                // Set environment variable
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
                self::$config[$key] = $value;
            }
        }
        
        self::$loaded = true;
    }
    
    /**
     * Load default values
     */
    private static function loadDefaults() {
        $defaults = [
            'DB_HOST' => 'localhost',
            'DB_NAME' => 'longhiep_db',
            'DB_USER' => 'root',
            'DB_PASS' => '',
            'DB_CHARSET' => 'utf8mb4',
            'APP_NAME' => 'UBND Xã Long Hiệp',
            'APP_ENV' => 'development',
            'APP_DEBUG' => 'true',
            'APP_URL' => 'http://localhost',
            'SESSION_LIFETIME' => '3600',
            'CSRF_ENABLED' => 'true',
            'RATE_LIMIT_ENABLED' => 'true',
            'MAX_LOGIN_ATTEMPTS' => '5',
            'LOGIN_TIMEOUT' => '300',
            'MAX_UPLOAD_SIZE' => '5242880',
            'LOG_ENABLED' => 'true',
            'LOG_LEVEL' => 'info',
            'BACKUP_ENABLED' => 'true',
            'CACHE_ENABLED' => 'false'
        ];
        
        foreach ($defaults as $key => $value) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            self::$config[$key] = $value;
        }
    }
    
    /**
     * Get environment variable
     */
    public static function get($key, $default = null) {
        if (!self::$loaded) {
            self::load();
        }
        
        return self::$config[$key] ?? getenv($key) ?: $default;
    }
    
    /**
     * Get as boolean
     */
    public static function getBool($key, $default = false) {
        $value = self::get($key);
        
        if ($value === null) {
            return $default;
        }
        
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
    
    /**
     * Get as integer
     */
    public static function getInt($key, $default = 0) {
        $value = self::get($key);
        
        if ($value === null) {
            return $default;
        }
        
        return (int) $value;
    }
    
    /**
     * Check if running in production
     */
    public static function isProduction() {
        return self::get('APP_ENV') === 'production';
    }
    
    /**
     * Check if debug mode is enabled
     */
    public static function isDebug() {
        return self::getBool('APP_DEBUG', false);
    }
}

// Auto-load on include
EnvLoader::load();
?>
