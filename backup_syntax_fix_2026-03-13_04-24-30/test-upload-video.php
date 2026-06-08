<?php
header("Content-Type: text/html; charset=utf-8");
session_start();
require_once 'config.php';
require_once 'auth.php';

$isLoggedIn = authIsLoggedIn();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Upload Video - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard-style.css">
</head>
<body>
    <?php include 'menu-don-gian.php'; ?>

    <main>
        <div class="admin-container">
            <div class="admin-header">
                <h1>🧪 Test Upload Video</h1>
                <div class="admin-actions">
                    <a href="them-video.php" class="btn-primary">← Quay lại thêm video</a>
                </div>
            </div>

            <?php if (!$isLoggedIn): ?>
                <div class="alert alert-warning">
                    <p>⚠️ Bạn cần <a href="dang-nhap.php">đăng nhập</a> để test upload video.</p>
                </div>
            <?php else: ?>
                <div class="form-container">
                    <h2>📁 Test Upload File</h2>
                    
                    <div class="upload-test-area">
                        <div class="upload-area" id="test-upload-area">
                            <div class="upload-content">
                                <div class="upload-icon">📁</div>
                                <p>Kéo thả file video vào đây hoặc <span class="upload-link">chọn file</span></p>
                                <p class="upload-info">Hỗ trợ: MP4, WebM, OGG, AVI, MOV, WMV, FLV, MKV, WAV, MP3 (tối đa 100MB)</p>
                            </div>
                            <input type="file" id="test_video_file" accept="video/*,audio/*" style="display: none;">
                        </div>
                        
                        <div id="test-upload-progress" style="display: none;">
                            <div class="progress-bar">
                                <div class="progress-fill" id="test-progress-fill"></div>
                            </div>
                            <p id="test-progress-text">Đang upload...</p>
                        </div>
                        
                        <div id="test-upload-result" style="display: none;"></div>
                    </div>

                    <div class="test-info">
                        <h3>📋 Thông tin test:</h3>
                        <ul>
                            <li><strong>Thư mục upload:</strong> videos/</li>
                            <li><strong>Kích thước tối đa:</strong> 100MB</li>
                            <li><strong>Định dạng hỗ trợ:</strong> Video và Audio</li>
                            <li><strong>Bảo mật:</strong> Chỉ admin mới upload được</li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($isLoggedIn): ?>
        initTestUpload();
        <?php endif; ?>
    });

    function initTestUpload() {
        const uploadArea = document.getElementById('test-upload-area');
        const fileInput = document.getElementById('test_video_file');
        const uploadLink = uploadArea.querySelector('.upload-link');
        
        // Click to select file
        uploadLink.addEventListener('click', (e) => {
            e.stopPropagation();
            fileInput.click();
        });
        
        uploadArea.addEventListener('click', () => {
            fileInput.click();
        });
        
        // File input change
        fileInput.addEventListener('change', handleTestFileSelect);
        
        // Drag and drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('drag-over');
        });
        
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('drag-over');
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('drag-over');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                handleTestFileSelect();
            }
        });
    }
    
    function handleTestFileSelect() {
        const fileInput = document.getElementById('test_video_file');
        const file = fileInput.files[0];
        
        if (!file) return;
        
        console.log('Selected file:', file.name, file.size, file.type);
        
        // Validate file type
        const allowedTypes = ['video/', 'audio/'];
        const allowedExtensions = ['mp4', 'webm', 'ogg', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'wav', 'mp3'];
        const fileExtension = file.name.split('.').pop().toLowerCase();
        
        if (!allowedTypes.some(type => file.type.startsWith(type)) && !allowedExtensions.includes(fileExtension)) {
            alert('Định dạng file không được hỗ trợ!\nHỗ trợ: ' + allowedExtensions.join(', '));
            return;
        }
        
        // Validate file size (100MB)
        if (file.size > 100 * 1024 * 1024) {
            alert('File quá lớn! Kích thước tối đa: 100MB\nFile hiện tại: ' + formatFileSize(file.size));
            return;
        }
        
        testUploadFile(file);
    }
    
    function testUploadFile(file) {
        const formData = new FormData();
        formData.append('video_file', file);
        
        const progressBar = document.getElementById('test-upload-progress');
        const progressFill = document.getElementById('test-progress-fill');
        const progressText = document.getElementById('test-progress-text');
        const uploadResult = document.getElementById('test-upload-result');
        
        // Show progress
        progressBar.style.display = 'block';
        uploadResult.style.display = 'none';
        
        const xhr = new XMLHttpRequest();
        
        // Progress tracking
        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                progressFill.style.width = percentComplete + '%';
                progressText.textContent = `Đang upload... ${Math.round(percentComplete)}%`;
            }
        });
        
        xhr.addEventListener('load', () => {
            progressBar.style.display = 'none';
            
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        uploadResult.innerHTML = `
                            <div class="upload-success">
                                <h3>✅ Upload thành công!</h3>
                                <p><strong>File:</strong> ${response.file_name}</p>
                                <p><strong>Đường dẫn:</strong> ${response.file_path}</p>
                                <p><strong>Kích thước:</strong> ${response.file_size}</p>
                                ${response.duration ? `<p><strong>Thời lượng:</strong> ${response.duration}</p>` : ''}
                                ${response.thumbnail ? `<p><strong>Thumbnail:</strong> ${response.thumbnail}</p>` : ''}
                                <p><a href="${response.file_path}" target="_blank">🎬 Xem file</a></p>
                            </div>
                        `;
                    } else {
                        uploadResult.innerHTML = `
                            <div class="upload-error">
                                <h3>❌ Upload thất bại</h3>
                                <p>${response.message}</p>
                            </div>
                        `;
                    }
                } catch (e) {
                    uploadResult.innerHTML = `
                        <div class="upload-error">
                            <h3>❌ Lỗi xử lý phản hồi</h3>
                            <p>Server response: ${xhr.responseText}</p>
                        </div>
                    `;
                }
            } else {
                uploadResult.innerHTML = `
                    <div class="upload-error">
                        <h3>❌ Lỗi HTTP ${xhr.status}</h3>
                        <p>${xhr.statusText}</p>
                    </div>
                `;
            }
            
            uploadResult.style.display = 'block';
        });
        
        xhr.addEventListener('error', () => {
            progressBar.style.display = 'none';
            uploadResult.innerHTML = `
                <div class="upload-error">
                    <h3>❌ Lỗi kết nối</h3>
                    <p>Không thể kết nối đến server</p>
                </div>
            `;
            uploadResult.style.display = 'block';
        });
        
        xhr.open('POST', 'upload-video.php');
        xhr.send(formData);
    }
    
    function formatFileSize(bytes) {
        if (bytes >= 1073741824) {
            return (bytes / 1073741824).toFixed(2) + ' GB';
        } else if (bytes >= 1048576) {
            return (bytes / 1048576).toFixed(2) + ' MB';
        } else if (bytes >= 1024) {
            return (bytes / 1024).toFixed(2) + ' KB';
        } else {
            return bytes + ' bytes';
        }
    }
    </script>

    <style>
    .upload-test-area {
        margin: 20px 0;
    }

    .upload-area {
        border: 2px dashed #dee2e6;
        border-radius: 10px;
        padding: 40px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }

    .upload-area:hover,
    .upload-area.drag-over {
        border-color: #007bff;
        background: #e3f2fd;
    }

    .upload-content {
        pointer-events: none;
    }

    .upload-icon {
        font-size: 48px;
        margin-bottom: 15px;
    }

    .upload-link {
        color: #007bff;
        text-decoration: underline;
        cursor: pointer;
        pointer-events: all;
    }

    .upload-info {
        font-size: 12px;
        color: #666;
        margin-top: 10px;
    }

    .progress-bar {
        width: 100%;
        height: 20px;
        background: #e9ecef;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 10px;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(45deg, #007bff, #0056b3);
        width: 0%;
        transition: width 0.3s ease;
        border-radius: 10px;
    }

    #test-progress-text {
        text-align: center;
        font-weight: 500;
        color: #007bff;
    }

    .upload-success {
        background: #d4edda;
        border: 1px solid #c3e6cb;
        border-radius: 8px;
        padding: 20px;
        color: #155724;
    }

    .upload-error {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        border-radius: 8px;
        padding: 20px;
        color: #721c24;
    }

    .test-info {
        background: #e3f2fd;
        border-radius: 8px;
        padding: 20px;
        margin-top: 20px;
    }

    .test-info h3 {
        color: #1976d2;
        margin-bottom: 10px;
    }

    .test-info ul {
        margin-left: 20px;
    }

    .test-info li {
        margin-bottom: 5px;
    }
    </style>
</body>
</html>