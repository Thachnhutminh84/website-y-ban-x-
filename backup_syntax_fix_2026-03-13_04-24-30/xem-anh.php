<?php
header("Content-Type: text/html; charset=utf-8");
session_start();

// Lấy đường dẫn ảnh từ URL
$imagePath = $_GET['img'] ?? '';
$returnUrl = $_GET['return'] ?? 'javascript:history.back()';

// Kiểm tra file có tồn tại không
if (empty($imagePath) || !file_exists($imagePath)) {
    header("Location: index.php");
    exit;
}

// Lấy thông tin file
$fileName = basename($imagePath);
$fileSize = filesize($imagePath);
$fileDate = date('d/m/Y H:i', filemtime($imagePath));
$imageInfo = getimagesize($imagePath);
$imageWidth = $imageInfo[0] ?? 0;
$imageHeight = $imageInfo[1] ?? 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($fileName); ?> - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #000;
            overflow: hidden;
        }
        
        .image-viewer {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .viewer-header {
            background: rgba(0,0,0,0.9);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
            z-index: 1000;
        }
        
        .viewer-title {
            font-size: 16px;
            font-weight: 500;
            flex: 1;
            margin: 0 20px;
        }
        
        .viewer-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-action {
            background: rgba(255,255,255,0.1);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .btn-action:hover {
            background: rgba(255,255,255,0.2);
            border-color: rgba(255,255,255,0.5);
        }
        
        .btn-back {
            background: #007bff;
            border-color: #007bff;
        }
        
        .btn-back:hover {
            background: #0056b3;
        }
        
        .viewer-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow: auto;
        }
        
        .viewer-image {
            max-width: 100%;
            max-height: calc(100vh - 140px);
            object-fit: contain;
            box-shadow: 0 10px 50px rgba(0,0,0,0.5);
        }
        
        .viewer-footer {
            background: rgba(0,0,0,0.9);
            padding: 15px 20px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
        }
        
        .image-info {
            display: flex;
            gap: 20px;
        }
        
        .zoom-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .zoom-btn {
            background: rgba(255,255,255,0.1);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            width: 35px;
            height: 35px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .zoom-btn:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .zoom-level {
            min-width: 60px;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .viewer-header {
                padding: 10px;
            }
            
            .viewer-title {
                font-size: 14px;
                margin: 0 10px;
            }
            
            .btn-action {
                padding: 6px 10px;
                font-size: 12px;
            }
            
            .image-info {
                flex-direction: column;
                gap: 5px;
            }
            
            .viewer-footer {
                font-size: 11px;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <?php include 'menu-don-gian.php'; ?>
    <div class="image-viewer">
        <div class="viewer-header">
            <a href="<?php echo htmlspecialchars($returnUrl); ?>" class="btn-action btn-back">
                ← Quay lại
            </a>
            
            <div class="viewer-title">
                <?php echo htmlspecialchars($fileName); ?>
            </div>
            
            <div class="viewer-actions">
                <a href="<?php echo htmlspecialchars($imagePath); ?>" download class="btn-action">
                    ⬇️ Tải về
                </a>
                <button onclick="toggleFullscreen()" class="btn-action">
                    🖵 Toàn màn hình
                </button>
            </div>
        </div>
        
        <div class="viewer-content" id="viewerContent">
            <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                 alt="<?php echo htmlspecialchars($fileName); ?>" 
                 class="viewer-image"
                 id="viewerImage">
        </div>
        
        <div class="viewer-footer">
            <div class="image-info">
                <span>📐 <?php echo $imageWidth; ?> × <?php echo $imageHeight; ?> px</span>
                <span>💾 <?php echo number_format($fileSize / 1024, 2); ?> KB</span>
                <span>📅 <?php echo $fileDate; ?></span>
            </div>
            
            <div class="zoom-controls">
                <button class="zoom-btn" onclick="zoomOut()">−</button>
                <span class="zoom-level" id="zoomLevel">100%</span>
                <button class="zoom-btn" onclick="zoomIn()">+</button>
                <button class="zoom-btn" onclick="resetZoom()">⟲</button>
            </div>
        </div>
    </div>

    <script>
    let currentZoom = 1;
    const zoomStep = 0.1;
    const minZoom = 0.1;
    const maxZoom = 5;
    
    const image = document.getElementById('viewerImage');
    const zoomLevelDisplay = document.getElementById('zoomLevel');
    
    function updateZoom() {
        image.style.transform = `scale(${currentZoom})`;
        zoomLevelDisplay.textContent = Math.round(currentZoom * 100) + '%';
    }
    
    function zoomIn() {
        if (currentZoom < maxZoom) {
            currentZoom += zoomStep;
            updateZoom();
        }
    }
    
    function zoomOut() {
        if (currentZoom > minZoom) {
            currentZoom -= zoomStep;
            updateZoom();
        }
    }
    
    function resetZoom() {
        currentZoom = 1;
        updateZoom();
    }
    
    function toggleFullscreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
        } else {
            document.exitFullscreen();
        }
    }
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        switch(e.key) {
            case 'Escape':
                if (document.fullscreenElement) {
                    document.exitFullscreen();
                } else {
                    window.location.href = '<?php echo htmlspecialchars($returnUrl); ?>';
                }
                break;
            case '+':
            case '=':
                zoomIn();
                break;
            case '-':
                zoomOut();
                break;
            case '0':
                resetZoom();
                break;
        }
    });
    
    // Mouse wheel zoom
    document.getElementById('viewerContent').addEventListener('wheel', function(e) {
        e.preventDefault();
        if (e.deltaY < 0) {
            zoomIn();
        } else {
            zoomOut();
        }
    });
    
    // Drag to pan when zoomed
    let isDragging = false;
    let startX, startY, scrollLeft, scrollTop;
    
    const content = document.getElementById('viewerContent');
    
    content.addEventListener('mousedown', function(e) {
        if (currentZoom > 1) {
            isDragging = true;
            startX = e.pageX - content.offsetLeft;
            startY = e.pageY - content.offsetTop;
            scrollLeft = content.scrollLeft;
            scrollTop = content.scrollTop;
            content.style.cursor = 'grabbing';
        }
    });
    
    content.addEventListener('mouseleave', function() {
        isDragging = false;
        content.style.cursor = 'default';
    });
    
    content.addEventListener('mouseup', function() {
        isDragging = false;
        content.style.cursor = 'default';
    });
    
    content.addEventListener('mousemove', function(e) {
        if (!isDragging) return;
        e.preventDefault();
        const x = e.pageX - content.offsetLeft;
        const y = e.pageY - content.offsetTop;
        const walkX = (x - startX) * 2;
        const walkY = (y - startY) * 2;
        content.scrollLeft = scrollLeft - walkX;
        content.scrollTop = scrollTop - walkY;
    });
    </script>
</body>
</html>