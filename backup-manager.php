<?php
/**
 * BACKUP MANAGER - Quản lý backup/restore database
 */

require_once 'config.php';
require_once 'audit-logger.php';

class BackupManager {
    private $conn;
    private $backupPath;
    
    public function __construct($backupPath = null) {
        $this->conn = getDBConnection();
        $this->backupPath = $backupPath ?? __DIR__ . '/backups';
        
        // Tạo thư mục backup nếu chưa có
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }
    
    /**
     * Tạo backup database
     */
    public function createBackup($type = 'manual') {
        try {
            $timestamp = date('Y-m-d_H-i-s');
            $fileName = "backup_{$timestamp}.sql";
            $filePath = $this->backupPath . '/' . $fileName;
            
            // Lưu vào bảng backup_history
            $backupId = $this->logBackupStart($fileName, $type, $filePath);
            
            // Thực hiện backup
            $result = $this->executeBackup($filePath);
            
            if ($result['success']) {
                $fileSize = filesize($filePath);
                $this->logBackupComplete($backupId, $fileSize);
                
                // Log audit
                auditLog('create', 'backup_history', $backupId, "Backup database thành công: $fileName");
                
                return [
                    'success' => true,
                    'message' => 'Backup thành công',
                    'file' => $fileName,
                    'size' => $fileSize,
                    'path' => $filePath
                ];
            } else {
                $this->logBackupFailed($backupId, $result['error']);
                
                // Log error
                auditLogError('mysql', "Backup failed: " . $result['error'], 'high');
                
                return [
                    'success' => false,
                    'message' => 'Backup thất bại: ' . $result['error']
                ];
            }
        } catch (Exception $e) {
            auditLogError('php', $e->getMessage(), 'high', $e->getTraceAsString());
            return [
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Thực hiện backup bằng mysqldump
     */
    private function executeBackup($filePath) {
        $dbHost = DB_HOST;
        $dbUser = DB_USER;
        $dbPass = DB_PASS;
        $dbName = DB_NAME;
        
        // Sử dụng mysqldump
        $command = sprintf(
            'mysqldump --host=%s --user=%s --password=%s %s > %s 2>&1',
            escapeshellarg($dbHost),
            escapeshellarg($dbUser),
            escapeshellarg($dbPass),
            escapeshellarg($dbName),
            escapeshellarg($filePath)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0 && file_exists($filePath)) {
            return ['success' => true];
        } else {
            return [
                'success' => false,
                'error' => implode("\n", $output)
            ];
        }
    }
    
    /**
     * Restore database từ file backup
     */
    public function restoreBackup($fileName) {
        try {
            $filePath = $this->backupPath . '/' . $fileName;
            
            if (!file_exists($filePath)) {
                return [
                    'success' => false,
                    'message' => 'File backup không tồn tại'
                ];
            }
            
            $result = $this->executeRestore($filePath);
            
            if ($result['success']) {
                auditLog('restore', 'backup_history', null, "Restore database từ: $fileName");
                
                return [
                    'success' => true,
                    'message' => 'Restore thành công'
                ];
            } else {
                auditLogError('mysql', "Restore failed: " . $result['error'], 'high');
                
                return [
                    'success' => false,
                    'message' => 'Restore thất bại: ' . $result['error']
                ];
            }
        } catch (Exception $e) {
            auditLogError('php', $e->getMessage(), 'high', $e->getTraceAsString());
            return [
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Thực hiện restore bằng mysql
     */
    private function executeRestore($filePath) {
        $dbHost = DB_HOST;
        $dbUser = DB_USER;
        $dbPass = DB_PASS;
        $dbName = DB_NAME;
        
        $command = sprintf(
            'mysql --host=%s --user=%s --password=%s %s < %s 2>&1',
            escapeshellarg($dbHost),
            escapeshellarg($dbUser),
            escapeshellarg($dbPass),
            escapeshellarg($dbName),
            escapeshellarg($filePath)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            return ['success' => true];
        } else {
            return [
                'success' => false,
                'error' => implode("\n", $output)
            ];
        }
    }
    
    /**
     * Lấy danh sách backup
     */
    public function listBackups() {
        if (!$this->conn) return [];
        
        $stmt = $this->conn->prepare("
            SELECT id, backup_file, backup_type, file_size, status, 
                   created_at, completed_at, error_message
            FROM backup_history
            ORDER BY created_at DESC
            LIMIT 50
        ");
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $backups = [];
        while ($row = $result->fetch_assoc()) {
            $backups[] = $row;
        }
        
        $stmt->close();
        return $backups;
    }
    
    /**
     * Xóa backup cũ (retention policy)
     */
    public function cleanOldBackups($retentionDays = 30) {
        if (!$this->conn) return false;
        
        // Lấy danh sách backup cũ
        $stmt = $this->conn->prepare("
            SELECT id, backup_file, backup_path
            FROM backup_history
            WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            AND status = 'completed'
        ");
        
        $stmt->bind_param('i', $retentionDays);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $deletedCount = 0;
        while ($row = $result->fetch_assoc()) {
            // Xóa file
            $filePath = $row['backup_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // Xóa record
            $deleteStmt = $this->conn->prepare("DELETE FROM backup_history WHERE id = ?");
            $deleteStmt->bind_param('i', $row['id']);
            $deleteStmt->execute();
            $deleteStmt->close();
            
            $deletedCount++;
        }
        
        $stmt->close();
        
        if ($deletedCount > 0) {
            auditLog('delete', 'backup_history', null, "Đã xóa $deletedCount backup cũ (> $retentionDays ngày)");
        }
        
        return $deletedCount;
    }
    
    /**
     * Log bắt đầu backup
     */
    private function logBackupStart($fileName, $type, $filePath) {
        if (!$this->conn) return null;
        
        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        
        $stmt = $this->conn->prepare("
            INSERT INTO backup_history (backup_file, backup_type, backup_path, status, created_by)
            VALUES (?, ?, ?, 'pending', ?)
        ");
        
        $stmt->bind_param('sssi', $fileName, $type, $filePath, $userId);
        $stmt->execute();
        $backupId = $stmt->insert_id;
        $stmt->close();
        
        return $backupId;
    }
    
    /**
     * Log backup hoàn thành
     */
    private function logBackupComplete($backupId, $fileSize) {
        if (!$this->conn || !$backupId) return false;
        
        $stmt = $this->conn->prepare("
            UPDATE backup_history 
            SET status = 'completed', file_size = ?, completed_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->bind_param('ii', $fileSize, $backupId);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
    
    /**
     * Log backup thất bại
     */
    private function logBackupFailed($backupId, $errorMessage) {
        if (!$this->conn || !$backupId) return false;
        
        $stmt = $this->conn->prepare("
            UPDATE backup_history 
            SET status = 'failed', error_message = ?, completed_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->bind_param('si', $errorMessage, $backupId);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }
}

// Helper functions
function createBackup($type = 'manual') {
    $manager = new BackupManager();
    return $manager->createBackup($type);
}

function restoreBackup($fileName) {
    $manager = new BackupManager();
    return $manager->restoreBackup($fileName);
}

function listBackups() {
    $manager = new BackupManager();
    return $manager->listBackups();
}

function cleanOldBackups($retentionDays = 30) {
    $manager = new BackupManager();
    return $manager->cleanOldBackups($retentionDays);
}
