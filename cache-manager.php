<?php
/**
 * CACHE MANAGER - Quản lý cache với file-based caching
 * Có thể dễ dàng chuyển sang Redis/Memcached sau
 */

class CacheManager {
    private $cacheDir;
    private $defaultTTL = 3600; // 1 hour
    
    public function __construct($cacheDir = null) {
        $this->cacheDir = $cacheDir ?? __DIR__ . '/cache';
        
        // Tạo thư mục cache nếu chưa có
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
    
    /**
     * Lấy dữ liệu từ cache
     */
    public function get($key) {
        $filePath = $this->getCacheFilePath($key);
        
        if (!file_exists($filePath)) {
            return null;
        }
        
        $data = file_get_contents($filePath);
        $cache = json_decode($data, true);
        
        if (!$cache) {
            return null;
        }
        
        // Kiểm tra expiration
        if (isset($cache['expires_at']) && time() > $cache['expires_at']) {
            $this->delete($key);
            return null;
        }
        
        return $cache['value'];
    }
    
    /**
     * Lưu dữ liệu vào cache
     */
    public function set($key, $value, $ttl = null) {
        $ttl = $ttl ?? $this->defaultTTL;
        $filePath = $this->getCacheFilePath($key);
        
        $cache = [
            'key' => $key,
            'value' => $value,
            'created_at' => time(),
            'expires_at' => time() + $ttl
        ];
        
        $data = json_encode($cache, JSON_UNESCAPED_UNICODE);
        return file_put_contents($filePath, $data) !== false;
    }
    
    /**
     * Xóa cache
     */
    public function delete($key) {
        $filePath = $this->getCacheFilePath($key);
        
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        
        return true;
    }
    
    /**
     * Xóa tất cả cache
     */
    public function flush() {
        $files = glob($this->cacheDir . '/*.cache');
        $count = 0;
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Kiểm tra cache có tồn tại không
     */
    public function has($key) {
        return $this->get($key) !== null;
    }
    
    /**
     * Lấy hoặc tạo cache (cache-aside pattern)
     */
    public function remember($key, $callback, $ttl = null) {
        $value = $this->get($key);
        
        if ($value !== null) {
            return $value;
        }
        
        $value = $callback();
        $this->set($key, $value, $ttl);
        
        return $value;
    }
    
    /**
     * Xóa cache theo pattern
     */
    public function deletePattern($pattern) {
        $files = glob($this->cacheDir . '/' . $pattern . '*.cache');
        $count = 0;
        
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Xóa cache đã hết hạn
     */
    public function cleanExpired() {
        $files = glob($this->cacheDir . '/*.cache');
        $count = 0;
        
        foreach ($files as $file) {
            if (!is_file($file)) continue;
            
            $data = file_get_contents($file);
            $cache = json_decode($data, true);
            
            if ($cache && isset($cache['expires_at']) && time() > $cache['expires_at']) {
                unlink($file);
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Lấy thông tin cache
     */
    public function getStats() {
        $files = glob($this->cacheDir . '/*.cache');
        $totalSize = 0;
        $expiredCount = 0;
        $activeCount = 0;
        
        foreach ($files as $file) {
            if (!is_file($file)) continue;
            
            $totalSize += filesize($file);
            
            $data = file_get_contents($file);
            $cache = json_decode($data, true);
            
            if ($cache && isset($cache['expires_at'])) {
                if (time() > $cache['expires_at']) {
                    $expiredCount++;
                } else {
                    $activeCount++;
                }
            }
        }
        
        return [
            'total_files' => count($files),
            'active_count' => $activeCount,
            'expired_count' => $expiredCount,
            'total_size' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2)
        ];
    }
    
    /**
     * Lấy đường dẫn file cache
     */
    private function getCacheFilePath($key) {
        $hash = md5($key);
        return $this->cacheDir . '/' . $hash . '.cache';
    }
}

// Global instance
$cacheManager = new CacheManager();

/**
 * Helper functions
 */
function cache_get($key) {
    global $cacheManager;
    return $cacheManager->get($key);
}

function cache_set($key, $value, $ttl = null) {
    global $cacheManager;
    return $cacheManager->set($key, $value, $ttl);
}

function cache_delete($key) {
    global $cacheManager;
    return $cacheManager->delete($key);
}

function cache_flush() {
    global $cacheManager;
    return $cacheManager->flush();
}

function cache_has($key) {
    global $cacheManager;
    return $cacheManager->has($key);
}

function cache_remember($key, $callback, $ttl = null) {
    global $cacheManager;
    return $cacheManager->remember($key, $callback, $ttl);
}

function cache_delete_pattern($pattern) {
    global $cacheManager;
    return $cacheManager->deletePattern($pattern);
}

function cache_clean_expired() {
    global $cacheManager;
    return $cacheManager->cleanExpired();
}

function cache_stats() {
    global $cacheManager;
    return $cacheManager->getStats();
}

/**
 * Cache keys constants
 */
define('CACHE_NEWS_LIST', 'news_list_');
define('CACHE_NEWS_DETAIL', 'news_detail_');
define('CACHE_VIDEO_LIST', 'video_list');
define('CACHE_VIDEO_DETAIL', 'video_detail_');
define('CACHE_DEPARTMENT_LIST', 'department_list');
define('CACHE_DEPARTMENT_STAFF', 'department_staff_');
define('CACHE_USER_LIST', 'user_list');
define('CACHE_STATS', 'stats_');
