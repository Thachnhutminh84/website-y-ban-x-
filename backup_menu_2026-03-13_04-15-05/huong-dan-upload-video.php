<?php
header("Content-Type: text/html; charset=utf-8");
session_start();
require_once 'config.php';
require_once 'auth.php';

$isLoggedIn = authIsLoggedIn();
$currentRole = authCurrentRole();
$displayName = $isLoggedIn ? authDisplayName() : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hướng dẫn Upload Video - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="dashboard-style.css">
</head>
<body>
    <header class="header--compact">
        <div class="container">
            <div class="logo">
                <img src="images/logo.png" alt="Logo UBND Xã Long Hiệp">
                <div class="header-text">
                    <h1>ỦY BAN NHÂN DÂN XÃ LONG HIỆP</h1>
                    <p>Phục vụ nhân dân - Xây dựng quê hương</p>
                </div>
            </div>
            <nav>
                <ul class="menu">
                    <li><a href="index.php">Trang chủ</a></li>
                    <?php if ($isLoggedIn): ?>
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="quan-ly-video.php">Quản lý video</a></li>
                        <li><a href="them-video.php">Thêm video</a></li>
                    <?php endif; ?>
                    <li class="admin-info">
                        <?php if ($isLoggedIn): ?>
                            👤 <?php echo htmlspecialchars(authRoleLabel($currentRole), ENT_QUOTES, 'UTF-8'); ?>
                            <span><?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?></span>
                            <a href="logout.php">Đăng xuất</a>
                        <?php else: ?>
                            <a href="dang-nhap.php" class="login-btn">Đăng nhập</a>
                        <?php endif; ?>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="admin-container">
            <div class="admin-header">
                <h1>📹 Hướng dẫn Upload Video</h1>
                <div class="admin-actions">
                    <a href="them-video.php" class="btn-primary">← Quay lại thêm video</a>
                </div>
            </div>

            <div class="guide-container">
                <div class="guide-section">
                    <h2>🎯 3 Cách thêm video</h2>
                    
                    <div class="method-grid">
                        <div class="method-card">
                            <h3>📺 YouTube (Khuyến nghị)</h3>
                            <div class="method-content">
                                <p><strong>Ưu điểm:</strong></p>
                                <ul>
                                    <li>✅ Không tốn dung lượng server</li>
                                    <li>✅ Tự động tạo thumbnail</li>
                                    <li>✅ Tốc độ tải nhanh</li>
                                    <li>✅ Hỗ trợ nhiều độ phân giải</li>
                                </ul>
                                
                                <p><strong>Cách sử dụng:</strong></p>
                                <ol>
                                    <li>Upload video lên YouTube</li>
                                    <li>Copy URL video (vd: https://www.youtube.com/watch?v=ABC123)</li>
                                    <li>Paste vào form thêm video</li>
                                </ol>
                                
                                <div class="example">
                                    <strong>Ví dụ URL hợp lệ:</strong><br>
                                    • https://www.youtube.com/watch?v=dQw4w9WgXcQ<br>
                                    • https://youtu.be/dQw4w9WgXcQ
                                </div>
                            </div>
                        </div>

                        <div class="method-card">
                            <h3>💾 Video Local</h3>
                            <div class="method-content">
                                <p><strong>Ưu điểm:</strong></p>
                                <ul>
                                    <li>✅ Kiểm soát hoàn toàn</li>
                                    <li>✅ Không phụ thuộc bên thứ 3</li>
                                    <li>✅ Hỗ trợ nhiều định dạng</li>
                                </ul>
                                
                                <p><strong>Nhược điểm:</strong></p>
                                <ul>
                                    <li>❌ Tốn dung lượng server</li>
                                    <li>❌ Cần upload thủ công</li>
                                </ul>
                                
                                <p><strong>Cách sử dụng:</strong></p>
                                <ol>
                                    <li>Upload file video vào thư mục <code>videos/</code></li>
                                    <li>Nhập đường dẫn: <code>videos/ten-file.mp4</code></li>
                                </ol>
                                
                                <div class="example">
                                    <strong>Định dạng hỗ trợ:</strong><br>
                                    .mp4, .webm, .ogg, .avi, .mov, .wmv, .flv, .mkv, .wav, .mp3
                                </div>
                            </div>
                        </div>

                        <div class="method-card">
                            <h3>🎬 Vimeo</h3>
                            <div class="method-content">
                                <p><strong>Ưu điểm:</strong></p>
                                <ul>
                                    <li>✅ Chất lượng cao</li>
                                    <li>✅ Ít quảng cáo</li>
                                    <li>✅ Giao diện chuyên nghiệp</li>
                                </ul>
                                
                                <p><strong>Cách sử dụng:</strong></p>
                                <ol>
                                    <li>Upload video lên Vimeo</li>
                                    <li>Copy URL video</li>
                                    <li>Paste vào form thêm video</li>
                                </ol>
                                
                                <div class="example">
                                    <strong>Ví dụ URL hợp lệ:</strong><br>
                                    • https://vimeo.com/123456789
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="guide-section">
                    <h2>📁 Hướng dẫn Upload Video Local</h2>
                    
                    <div class="step-by-step">
                        <div class="step">
                            <div class="step-number">1</div>
                            <div class="step-content">
                                <h3>Tạo thư mục videos</h3>
                                <p>Tạo thư mục <code>videos/</code> trong thư mục gốc website nếu chưa có.</p>
                                <div class="code-block">
                                    mkdir videos<br>
                                    chmod 755 videos
                                </div>
                            </div>
                        </div>

                        <div class="step">
                            <div class="step-number">2</div>
                            <div class="step-content">
                                <h3>Upload file video</h3>
                                <p>Copy file video vào thư mục <code>videos/</code>. Đặt tên file không dấu, không khoảng trắng.</p>
                                <div class="code-block">
                                    ✅ Tốt: hoat-dong-ubnd-2025.mp4<br>
                                    ❌ Tránh: Hoạt động UBND 2025.mp4
                                </div>
                            </div>
                        </div>

                        <div class="step">
                            <div class="step-number">3</div>
                            <div class="step-content">
                                <h3>Nhập đường dẫn</h3>
                                <p>Trong form thêm video, chọn "Video local" và nhập:</p>
                                <div class="code-block">
                                    videos/ten-file.mp4
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="guide-section">
                    <h2>⚠️ Lưu ý quan trọng</h2>
                    
                    <div class="warning-box">
                        <h3>🚫 Lỗi thường gặp</h3>
                        <ul>
                            <li><strong>URL YouTube không hợp lệ:</strong> Đảm bảo URL có dạng youtube.com/watch?v= hoặc youtu.be/</li>
                            <li><strong>File local không tìm thấy:</strong> Kiểm tra file đã upload đúng thư mục videos/</li>
                            <li><strong>Định dạng không hỗ trợ:</strong> Chỉ hỗ trợ các định dạng video/audio phổ biến</li>
                        </ul>
                    </div>

                    <div class="tip-box">
                        <h3>💡 Mẹo hay</h3>
                        <ul>
                            <li>Nén video trước khi upload để tiết kiệm dung lượng</li>
                            <li>Sử dụng YouTube cho video dài, local cho video ngắn</li>
                            <li>Đặt tên file có ý nghĩa để dễ quản lý</li>
                            <li>Kiểm tra preview trước khi lưu</li>
                        </ul>
                    </div>
                </div>

                <div class="guide-section">
                    <h2>🛠️ Công cụ hỗ trợ</h2>
                    
                    <div class="tools-grid">
                        <div class="tool-card">
                            <h3>📊 Kiểm tra file</h3>
                            <p>Kiểm tra file video có tồn tại không:</p>
                            <div class="tool-form">
                                <input type="text" id="check-file" placeholder="videos/ten-file.mp4">
                                <button onclick="checkFile()">Kiểm tra</button>
                                <div id="check-result"></div>
                            </div>
                        </div>

                        <div class="tool-card">
                            <h3>🔗 Test YouTube URL</h3>
                            <p>Kiểm tra URL YouTube có hợp lệ không:</p>
                            <div class="tool-form">
                                <input type="text" id="check-youtube" placeholder="https://www.youtube.com/watch?v=...">
                                <button onclick="checkYouTube()">Kiểm tra</button>
                                <div id="youtube-result"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
    function checkFile() {
        const filePath = document.getElementById('check-file').value;
        const result = document.getElementById('check-result');
        
        if (!filePath) {
            result.innerHTML = '<p style="color: orange;">Vui lòng nhập đường dẫn file</p>';
            return;
        }
        
        // Simple check - in real implementation, you'd make an AJAX call
        if (filePath.startsWith('videos/') && /\.(mp4|webm|ogg|avi|mov|wmv|flv|mkv|wav|mp3)$/i.test(filePath)) {
            result.innerHTML = '<p style="color: green;">✅ Đường dẫn có vẻ hợp lệ</p>';
        } else {
            result.innerHTML = '<p style="color: red;">❌ Đường dẫn không hợp lệ</p>';
        }
    }
    
    function checkYouTube() {
        const url = document.getElementById('check-youtube').value;
        const result = document.getElementById('youtube-result');
        
        if (!url) {
            result.innerHTML = '<p style="color: orange;">Vui lòng nhập URL YouTube</p>';
            return;
        }
        
        const regex = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/;
        const matches = url.match(regex);
        
        if (matches) {
            const videoId = matches[1];
            result.innerHTML = `
                <p style="color: green;">✅ URL hợp lệ</p>
                <p>Video ID: ${videoId}</p>
                <img src="https://img.youtube.com/vi/${videoId}/hqdefault.jpg" style="width: 120px; height: 90px; border-radius: 5px;">
            `;
        } else {
            result.innerHTML = '<p style="color: red;">❌ URL YouTube không hợp lệ</p>';
        }
    }
    </script>

    <style>
    .guide-container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .guide-section {
        background: white;
        border-radius: 15px;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }

    .method-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .method-card {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 20px;
        transition: all 0.3s ease;
    }

    .method-card:hover {
        border-color: #007bff;
        transform: translateY(-2px);
    }

    .method-card h3 {
        color: #007bff;
        margin-bottom: 15px;
    }

    .example {
        background: #f8f9fa;
        padding: 10px;
        border-radius: 5px;
        margin-top: 10px;
        font-size: 13px;
    }

    .step-by-step {
        margin-top: 20px;
    }

    .step {
        display: flex;
        margin-bottom: 30px;
        align-items: flex-start;
    }

    .step-number {
        background: #007bff;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-right: 20px;
        flex-shrink: 0;
    }

    .step-content h3 {
        margin-bottom: 10px;
        color: #2c3e50;
    }

    .code-block {
        background: #2d3748;
        color: #e2e8f0;
        padding: 15px;
        border-radius: 8px;
        font-family: 'Courier New', monospace;
        margin: 10px 0;
        overflow-x: auto;
    }

    .warning-box {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
    }

    .warning-box h3 {
        color: #856404;
        margin-bottom: 10px;
    }

    .tip-box {
        background: #d1ecf1;
        border: 1px solid #bee5eb;
        border-radius: 8px;
        padding: 20px;
        margin: 20px 0;
    }

    .tip-box h3 {
        color: #0c5460;
        margin-bottom: 10px;
    }

    .tools-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .tool-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
    }

    .tool-form {
        margin-top: 15px;
    }

    .tool-form input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        margin-bottom: 10px;
    }

    .tool-form button {
        background: #007bff;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
    }

    .tool-form button:hover {
        background: #0056b3;
    }

    #check-result, #youtube-result {
        margin-top: 10px;
        padding: 10px;
        border-radius: 5px;
    }

    @media (max-width: 768px) {
        .method-grid, .tools-grid {
            grid-template-columns: 1fr;
        }
        
        .step {
            flex-direction: column;
        }
        
        .step-number {
            margin-bottom: 10px;
        }
    }
    </style>
</body>
</html>