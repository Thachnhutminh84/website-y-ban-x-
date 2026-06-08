<?php
/**
 * Simple File-based Cache System
 * Cải thiện performance bằng caching
 */

class CacheHelper {
    private static $cacheDir = 'cache/';
    private static $enabled = true;
    private static $defaultLifetime = 3600; // 1 hour
    
    /**
     * Initialize cache system
     */
    public static function init() {
        self::$enabled = EnvLoader::getBool('CACHE_ENABLED', false);
        self::$defaultLifetime = EnvLoader::getInt('CACHE_LIFETIME', 3600);
        
        if (!file_exists(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
    }
    
    /**
     * Get cache file path
     */
    private static function getCacheFile($key) {
        $hash = md5($key);
        return self::$cacheDir . $hash . '.cache';
    }
    
    /**
     * Set cache
     */
    public static function set($key, $value, $lifetime = null) {
        if (!self::$enabled) {
            return false;
        }
        
        $lifetime = $lifetime ?? self::$defaultLifetime;
        $cacheFile = self::getCacheFile($key);
        
        $data = [
            'key' => $key,
            'value' => $value,
            'expires_at' => time() + $lifetime,
            'created_at' => time()
        ];
        
        return file_put_contents($cacheFile, serialize($data), LOCK_EX) !== false;
    }
    
    /**
     * Get cache
     */
    public static function get($key, $default = null) {
        if (!self::$enabled) {
            return $default;
        }
        
        $cacheFile = self::getCacheFile($key);
        
        if (!file_exists($cacheFile)) {
            return $default;
        }
        
        $data = unserialize(file_get_contents($cacheFile));
        
        // Check if expired
        if ($data['expires_at'] < time()) {
            self::delete($key);
            return $default;
        }
        
        return $data['value'];
    }
    
    /**
     * Check if cache exists and valid
     */
    public static function has($key) {
        if (!self::$enabled) {
            return false;
        }
        
        $cacheFile = self::getCacheFile($key);
        
        if (!file_exists($cacheFile)) {
            return false;
        }
        
        $data = unserialize(file_get_contents($cacheFile));
        
        return $data['expires_at'] >= time();
    }
    
    /**
     * Delete cache
     */
    public static function delete($key) {
        $cacheFile = self::getCacheFile($key);
        
        if (file_exists($cacheFile)) {
            return unlink($cacheFile);
        }
        
        return false;
    }
    
    /**
     * Clear all cache
     */
    public static function clear() {
        $files = glob(self::$cacheDir . '*.cache');
        
        foreach ($files as $file) {
            unlink($file);
        }
        
        return true;
    }
    
    /**
     * Clean expired cache
     */
    public static function cleanExpired() {
        $files = glob(self::$cacheDir . '*.cache');
        $cleaned = 0;
        
        foreach ($files as $file) {
            $data = unserialize(file_get_contents($file));
            
            if ($data['expires_at'] < time()) {
                unlink($file);
                $cleaned++;
            }
        }
        
        return $cleaned;
    }
    
    /**
     * Remember - Get from cache or execute callback
     */
    public static function remember($key, $callback, $lifetime = null) {
        if (self::has($key)) {
            return self::get($key);
        }
        
        $value = $callback();
        self::set($key, $value, $lifetime);
        
        return $value;
    }
    
    /**
     * Get cache statistics
     */
    public static function getStats() {
        $files = glob(self::$cacheDir . '*.cache');
        $totalSize = 0;
        $expired = 0;
        $valid = 0;
        
        foreach ($files as $file) {
            $totalSize += filesize($file);
            $data = unserialize(file_get_contents($file));
            
            if ($data['expires_at'] < time()) {
                $expired++;
            } else {
                $valid++;
            }
        }
        
        return [
            'total_items' => count($files),
            'valid_items' => $valid,
            'expired_items' => $expired,
            'total_size' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2)
        ];
    }
}

// Initialize cache
CacheHelper::init();

// Clean expired cache periodically (1% chance)
if (rand(1, 100) === 1) {
    CacheHelper::cleanExpired();
}
?>
