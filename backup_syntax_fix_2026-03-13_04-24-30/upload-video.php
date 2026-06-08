<?php
header("Content-Type: application/json; charset=utf-8");
session_start();
require_once 'config.php';
require_once 'auth.php';

// Kiểm tra quyền admin
if (!authIsLoggedIn() || !authHasPermission('manage_content')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

// Chỉ chấp nhận POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Kiểm tra có file upload không
if (!isset($_FILES['video_file']) || $_FILES['video_file']['error'] !== UPLOAD_ERR_OK) {
    $error_message = 'Không có file được upload';
    if (isset($_FILES['video_file']['error'])) {
        switch ($_FILES['video_file']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $error_message = 'File quá lớn';
                break;
            case UPLOAD_ERR_PARTIAL:
                $error_message = 'File upload không hoàn tất';
                break;
            case UPLOAD_ERR_NO_FILE:
                $error_message = 'Không có file nào được chọn';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $error_message = 'Thiếu thư mục tạm';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $error_message = 'Không thể ghi file';
                break;
        }
    }
    echo json_encode(['success' => false, 'message' => $error_message]);
    exit;
}

$uploadedFile = $_FILES['video_file'];
$fileName = $uploadedFile['name'];
$fileSize = $uploadedFile['size'];
$fileTmpName = $uploadedFile['tmp_name'];
$fileType = $uploadedFile['type'];

// Kiểm tra định dạng file
$allowedExtensions = ['mp4', 'webm', 'ogg', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'wav', 'mp3'];
$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

if (!in_array($fileExtension, $allowedExtensions)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Định dạng file không được hỗ trợ. Chỉ chấp nhận: ' . implode(', ', $allowedExtensions)
    ]);
    exit;
}

// Kiểm tra kích thước file (tối đa 100MB)
$maxFileSize = 100 * 1024 * 1024; // 100MB
if ($fileSize > $maxFileSize) {
    echo json_encode([
        'success' => false, 
        'message' => 'File quá lớn. Kích thước tối đa: 100MB'
    ]);
    exit;
}

// Tạo thư mục videos nếu chưa có
$uploadDir = 'videos/';
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Không thể tạo thư mục upload'
        ]);
        exit;
    }
}

// Tạo tên file an toàn
$safeFileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', pathinfo($fileName, PATHINFO_FILENAME));
$safeFileName = substr($safeFileName, 0, 50); // Giới hạn độ dài
$newFileName = $safeFileName . '_' . time() . '.' . $fileExtension;
$uploadPath = $uploadDir . $newFileName;

// Kiểm tra file đã tồn tại
$counter = 1;
while (file_exists($uploadPath)) {
    $newFileName = $safeFileName . '_' . time() . '_' . $counter . '.' . $fileExtension;
    $uploadPath = $uploadDir . $newFileName;
    $counter++;
}

// Upload file
if (move_uploaded_file($fileTmpName, $uploadPath)) {
    // Tạo thumbnail nếu là video
    $thumbnailPath = null;
    if (in_array($fileExtension, ['mp4', 'webm', 'ogg', 'avi', 'mov', 'wmv', 'flv', 'mkv'])) {
        $thumbnailPath = generateVideoThumbnail($uploadPath, $newFileName);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Upload thành công',
        'file_path' => $uploadPath,
        'file_name' => $newFileName,
        'file_size' => formatFileSize($fileSize),
        'thumbnail' => $thumbnailPath,
        'duration' => getVideoDuration($uploadPath)
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Không thể lưu file'
    ]);
}

// Hàm tạo thumbnail cho video (cần ffmpeg)
function generateVideoThumbnail($videoPath, $fileName) {
    $thumbnailDir = 'videos/thumbnails/';
    if (!is_dir($thumbnailDir)) {
        mkdir($thumbnailDir, 0755, true);
    }
    
    $thumbnailName = pathinfo($fileName, PATHINFO_FILENAME) . '.jpg';
    $thumbnailPath = $thumbnailDir . $thumbnailName;
    
    // Sử dụng ffmpeg để tạo thumbnail (nếu có)
    $ffmpegCmd = "ffmpeg -i \"$videoPath\" -ss 00:00:01 -vframes 1 -y \"$thumbnailPath\" 2>/dev/null";
    exec($ffmpegCmd, $output, $returnCode);
    
    if ($returnCode === 0 && file_exists($thumbnailPath)) {
        return $thumbnailPath;
    }
    
    return null;
}

// Hàm lấy thời lượng video (cần ffprobe)
function getVideoDuration($videoPath) {
    $ffprobeCmd = "ffprobe -v quiet -show_entries format=duration -of csv=\"p=0\" \"$videoPath\" 2>/dev/null";
    $duration = exec($ffprobeCmd);
    
    if ($duration && is_numeric($duration)) {
        $seconds = (int)$duration;
        $minutes = floor($seconds / 60);
        $seconds = $seconds % 60;
        return sprintf('%02d:%02d', $minutes, $seconds);
    }
    
    return null;
}

// Hàm format kích thước file
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
?>