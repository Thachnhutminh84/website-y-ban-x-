<?php
header('Content-Type: text/html; charset=utf-8');
session_start();
require_once 'config.php';
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tài liệu, biểu mẫu - UBND Xã Long Hiệp</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #1a4d2e;
            --primary-light: #2d7a4a;
            --primary-dark: #0e2e1c;
            --gradient-primary: linear-gradient(135deg, #1a4d2e 0%, #2d7a4a 50%, #3a9d5e 100%);
            --gradient-header: linear-gradient(135deg, #1a4d2e 0%, #143a24 50%, #0e2e1c 100%);
            --accent: #c9a227;
            --accent-light: #e6c44a;
            --bg-light: #f4f7f5;
            --bg-white: #ffffff;
            --text-dark: #1a1a1a;
            --text-medium: #555555;
            --text-light: #888888;
            --border: #d9e2dc;
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.06);
            --shadow-md: 0 4px 16px rgba(0,0,0,0.1);
            --shadow-lg: 0 8px 32px rgba(0,0,0,0.12);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --pdf-color: #e74c3c;
            --doc-color: #2980b9;
            --xls-color: #27ae60;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg-light);
            color: var(--text-dark);
            line-height: 1.6;
        }

        .page-hero {
            background: var(--gradient-header);
            padding: 60px 20px 50px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .page-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 1;
        }

        .page-hero::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--accent);
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            margin: 0 auto;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            padding: 8px 20px;
            border-radius: 50px;
            color: var(--accent-light);
            font-size: 0.85rem;
            margin-bottom: 20px;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .hero-content h1 {
            font-size: 2.2rem;
            color: #ffffff;
            margin-bottom: 12px;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .hero-content h1 i {
            color: var(--accent-light);
            margin-right: 12px;
        }

        .hero-content p {
            color: rgba(255,255,255,0.85);
            font-size: 1.05rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .search-section {
            margin-top: -35px;
            position: relative;
            z-index: 10;
            padding: 0 20px;
        }

        .search-box {
            max-width: 800px;
            margin: 0 auto;
            background: var(--bg-white);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            padding: 20px 24px;
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .search-box .search-icon {
            color: var(--primary);
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .search-box input {
            flex: 1;
            border: none;
            outline: none;
            font-size: 1rem;
            color: var(--text-dark);
            background: transparent;
        }

        .search-box input::placeholder {
            color: var(--text-light);
        }

        .search-box button {
            background: var(--gradient-primary);
            color: #fff;
            border: none;
            padding: 12px 28px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 600;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .search-box button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(26,77,46,0.3);
        }

        .tabs-section {
            margin-top: 40px;
            padding: 0 20px;
        }

        .tabs-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 8px;
            scrollbar-width: thin;
        }

        .tab-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 22px;
            border: 2px solid var(--border);
            background: var(--bg-white);
            border-radius: 50px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-medium);
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .tab-btn:hover {
            border-color: var(--primary-light);
            color: var(--primary);
            background: #f0f8f3;
        }

        .tab-btn.active {
            background: var(--gradient-primary);
            color: #fff;
            border-color: transparent;
            box-shadow: 0 4px 12px rgba(26,77,46,0.25);
        }

        .tab-btn .count {
            background: rgba(0,0,0,0.1);
            padding: 2px 8px;
            border-radius: 50px;
            font-size: 0.75rem;
        }

        .tab-btn.active .count {
            background: rgba(255,255,255,0.25);
        }

        .forms-section {
            padding: 40px 20px 60px;
        }

        .forms-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
        }

        .form-card {
            background: var(--bg-white);
            border-radius: var(--radius-md);
            padding: 28px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        .form-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gradient-primary);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .form-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary-light);
        }

        .form-card:hover::before {
            opacity: 1;
        }

        .card-header {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 16px;
        }

        .card-icon {
            width: 52px;
            height: 52px;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .card-icon.ho-tich {
            background: #fef3e2;
            color: #e67e22;
        }

        .card-icon.dat-dai {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .card-icon.kinh-doanh {
            background: #e3f2fd;
            color: #1565c0;
        }

        .card-icon.xa-hoi {
            background: #fce4ec;
            color: #c62828;
        }

        .card-icon.giao-duc {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .card-icon.phap-luat {
            background: #fff3e0;
            color: #e65100;
        }

        .card-info {
            flex: 1;
            min-width: 0;
        }

        .card-info h3 {
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 6px;
            line-height: 1.4;
        }

        .card-category {
            font-size: 0.78rem;
            color: var(--text-light);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-desc {
            color: var(--text-medium);
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 20px;
            flex: 1;
        }

        .card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 16px;
            border-top: 1px solid var(--border);
        }

        .file-badges {
            display: flex;
            gap: 6px;
        }

        .badge {
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .badge.pdf {
            background: #fde8e8;
            color: var(--pdf-color);
        }

        .badge.doc {
            background: #e3f2fd;
            color: var(--doc-color);
        }

        .badge.xls {
            background: #e8f5e9;
            color: var(--xls-color);
        }

        .download-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--gradient-primary);
            color: #fff;
            border: none;
            padding: 8px 18px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .download-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(26,77,46,0.3);
        }

        .download-btn.view-btn {
            padding: 8px 12px;
            background: var(--accent);
        }

        .download-btn.view-btn:hover {
            box-shadow: 0 4px 12px rgba(201,162,39,0.3);
        }

        .guide-section {
            padding: 60px 20px;
            background: var(--bg-white);
            border-top: 1px solid var(--border);
        }

        .guide-wrapper {
            max-width: 900px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            margin-bottom: 40px;
        }

        .section-title h2 {
            font-size: 1.6rem;
            color: var(--primary);
            margin-bottom: 8px;
        }

        .section-title h2 i {
            margin-right: 10px;
            color: var(--accent);
        }

        .section-title p {
            color: var(--text-medium);
            font-size: 0.95rem;
        }

        .guide-steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 24px;
        }

        .guide-step {
            text-align: center;
            padding: 30px 20px;
            border-radius: var(--radius-md);
            background: var(--bg-light);
            border: 1px solid var(--border);
            transition: all 0.3s ease;
        }

        .guide-step:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-sm);
            border-color: var(--primary-light);
        }

        .step-number {
            width: 48px;
            height: 48px;
            background: var(--gradient-primary);
            color: #fff;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 16px;
        }

        .guide-step h4 {
            font-size: 1rem;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .guide-step p {
            color: var(--text-medium);
            font-size: 0.85rem;
            line-height: 1.5;
        }

        .guide-note {
            margin-top: 30px;
            padding: 20px 24px;
            background: #fef9e7;
            border-left: 4px solid var(--accent);
            border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .guide-note i {
            color: var(--accent);
            font-size: 1.2rem;
            margin-top: 2px;
        }

        .guide-note p {
            color: var(--text-medium);
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .guide-note strong {
            color: var(--text-dark);
        }

        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 1.6rem;
            }

            .hero-content p {
                font-size: 0.95rem;
            }

            .search-box {
                flex-direction: column;
            }

            .search-box button {
                width: 100%;
            }

            .forms-grid {
                grid-template-columns: 1fr;
            }

            .tabs-wrapper {
                gap: 6px;
            }

            .tab-btn {
                padding: 8px 16px;
                font-size: 0.82rem;
            }
        }
    </style>
</head>
<body>

<?php include 'header-menu.php'; ?>

<section class="page-hero">
    <div class="hero-content">
        <div class="hero-badge">
            <i class="fas fa-landmark"></i>
            UBND Xã Long Hiệp, Vĩnh Long
        </div>
        <h1><i class="fas fa-file-alt"></i>Tài liệu, biểu mẫu</h1>
        <p>Tổng hợp các biểu mẫu hành chính, tài liệu hướng dẫn phục vụ người dân và doanh nghiệp trên địa bàn xã Long Hiệp</p>
    </div>
</section>

<section class="search-section">
    <div class="search-box">
        <i class="fas fa-search search-icon"></i>
        <input type="text" id="searchInput" placeholder="Tìm kiếm tài liệu, biểu mẫu theo tên hoặc từ khóa..." onkeyup="filterForms()">
        <button onclick="filterForms()"><i class="fas fa-search"></i> Tìm kiếm</button>
    </div>
</section>

<section class="tabs-section">
    <div class="tabs-wrapper">
        <button class="tab-btn active" data-category="all" onclick="filterByCategory('all', this)">
            <i class="fas fa-th-large"></i> Tất cả <span class="count">10</span>
        </button>
        <button class="tab-btn" data-category="ho-tich" onclick="filterByCategory('ho-tich', this)">
            <i class="fas fa-id-card"></i> Hộ tịch <span class="count">3</span>
        </button>
        <button class="tab-btn" data-category="dat-dai" onclick="filterByCategory('dat-dai', this)">
            <i class="fas fa-map-marked-alt"></i> Đất đai <span class="count">2</span>
        </button>
        <button class="tab-btn" data-category="kinh-doanh" onclick="filterByCategory('kinh-doanh', this)">
            <i class="fas fa-briefcase"></i> Kinh doanh <span class="count">1</span>
        </button>
        <button class="tab-btn" data-category="xa-hoi" onclick="filterByCategory('xa-hoi', this)">
            <i class="fas fa-hands-helping"></i> Xã hội <span class="count">2</span>
        </button>
        <button class="tab-btn" data-category="giao-duc" onclick="filterByCategory('giao-duc', this)">
            <i class="fas fa-graduation-cap"></i> Giáo dục <span class="count">1</span>
        </button>
        <button class="tab-btn" data-category="phap-luat" onclick="filterByCategory('phap-luat', this)">
            <i class="fas fa-gavel"></i> Pháp luật <span class="count">1</span>
        </button>
    </div>
</section>

<section class="forms-section">
    <div class="forms-grid" id="formsGrid">
        <!-- Cards rendered by JS -->
    </div>
</section>

<section class="guide-section">
    <div class="guide-wrapper">
        <div class="section-title">
            <h2><i class="fas fa-book-open"></i>Hướng dẫn tải tài liệu</h2>
            <p>Làm theo các bước đơn giản dưới đây để tải và sử dụng biểu mẫu</p>
        </div>
        <div class="guide-steps">
            <div class="guide-step">
                <div class="step-number">1</div>
                <h4>Tìm kiếm biểu mẫu</h4>
                <p>Sử dụng thanh tìm kiếm hoặc chọn danh mục để tìm biểu mẫu cần thiết</p>
            </div>
            <div class="guide-step">
                <div class="step-number">2</div>
                <h4>Chọn định dạng</h4>
                <p>Xem định dạng phù hợp (PDF, DOC) và nhấn nút "Tải về"</p>
            </div>
            <div class="guide-step">
                <div class="step-number">3</div>
                <h4>Điền thông tin</h4>
                <p>Mở file và điền đầy đủ thông tin theo yêu cầu trên biểu mẫu</p>
            </div>
            <div class="guide-step">
                <div class="step-number">4</div>
                <h4>Nộp hồ sơ</h4>
                <p>In và nộp hồ sơ tại Bộ phận Tiếp nhận hồ sơ UBND xã Long Hiệp</p>
            </div>
        </div>
        <div class="guide-note">
            <i class="fas fa-info-circle"></i>
            <p><strong>Lưu ý:</strong> Các biểu mẫu được cung cấp miễn phí. Nếu gặp khó khăn trong việc tải hoặc điền biểu mẫu, vui lòng liên hệ Bộ phận Tiếp nhận hồ sơ qua số điện thoại <strong>(0270) 3.826.xxx</strong> hoặc đến trực tiếp trụ sở UBND xã Long Hiệp để được hỗ trợ.</p>
        </div>
    </div>
</section>

<?php
$conn = getDBConnection();
$formsData = [];
$categoryCounts = ['all' => 0, 'ho-tich' => 0, 'dat-dai' => 0, 'kinh-doanh' => 0, 'xa-hoi' => 0, 'giao-duc' => 0, 'phap-luat' => 0];
$categoryLabels = ['ho-tich' => 'Hộ tịch', 'dat-dai' => 'Đất đai', 'kinh-doanh' => 'Kinh doanh', 'xa-hoi' => 'Xã hội', 'giao-duc' => 'Giáo dục', 'phap-luat' => 'Pháp luật'];
$categoryIcons = ['ho-tich' => 'fa-id-card', 'dat-dai' => 'fa-map-marked-alt', 'kinh-doanh' => 'fa-briefcase', 'xa-hoi' => 'fa-hands-helping', 'giao-duc' => 'fa-graduation-cap', 'phap-luat' => 'fa-gavel'];

if ($conn) {
    $sql = "SELECT id, title, description, category, file_type, file_path, file_size, download_count FROM forms WHERE is_active = 1 ORDER BY display_order ASC, id ASC";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $formsData[] = $row;
            $cat = $row['category'];
            $categoryCounts['all']++;
            if (isset($categoryCounts[$cat])) {
                $categoryCounts[$cat]++;
            }
        }
    }
    $conn->close();
}
?>

<script>
const formsData = <?php echo json_encode($formsData, JSON_UNESCAPED_UNICODE); ?>;
const categoryLabels = <?php echo json_encode($categoryLabels, JSON_UNESCAPED_UNICODE); ?>;
const categoryCounts = <?php echo json_encode($categoryCounts); ?>;
const categoryIcons = <?php echo json_encode($categoryIcons); ?>;

let currentCategory = 'all';

function formatFileSize(bytes) {
    if (!bytes || bytes === 0) return '';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

function renderForms(forms) {
    const grid = document.getElementById('formsGrid');
    if (forms.length === 0) {
        grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:60px 20px;color:var(--text-light);"><i class="fas fa-search" style="font-size:3rem;margin-bottom:16px;display:block;opacity:0.3;"></i><p style="font-size:1.1rem;">Không tìm thấy biểu mẫu phù hợp</p></div>';
        return;
    }
    grid.innerHTML = forms.map(f => {
        const cat = f.category || 'khac';
        const catLabel = categoryLabels[cat] || 'Khác';
        const icon = categoryIcons[cat] || 'fa-file';
        const ext = (f.file_type || 'pdf').toLowerCase();
        const hasFile = f.file_path && f.file_path.length > 0;
        const fileSize = formatFileSize(f.file_size);
        const downloads = f.download_count || 0;

        const badgeClass = ext === 'pdf' ? 'pdf' : (ext === 'doc' || ext === 'docx' ? 'doc' : 'xls');
        const extLabel = ext.toUpperCase();

        const viewBtn = hasFile
            ? `<a href="tai-lieu-preview.php?id=${f.id}" class="download-btn view-btn" title="Xem trước"><i class="fas fa-eye"></i></a>`
            : '';
        const downloadBtn = hasFile
            ? `<a href="tai-lieu-download.php?id=${f.id}" class="download-btn" title="Tải về"><i class="fas fa-download"></i> Tải về</a>`
            : `<a href="#" class="download-btn" onclick="showContactModal('${f.title.replace(/'/g, "\\'")}'); return false;" title="Liên hệ nhận file"><i class="fas fa-phone"></i> Liên hệ</a>`;

        const sizeInfo = fileSize ? `<span style="font-size:0.75rem;color:var(--text-light);margin-left:8px;">${fileSize}</span>` : '';
        const downloadInfo = downloads > 0 ? `<span style="font-size:0.72rem;color:var(--text-light);margin-left:6px;"><i class="fas fa-download" style="font-size:0.65rem;"></i> ${downloads}</span>` : '';

        return `
            <div class="form-card" data-category="${cat}" data-name="${(f.title || '').toLowerCase()}" data-desc="${(f.description || '').toLowerCase()}">
                <div class="card-header">
                    <div class="card-icon ${cat}">
                        <i class="fas ${icon}"></i>
                    </div>
                    <div class="card-info">
                        <h3>${f.title}</h3>
                        <div class="card-category">${catLabel}</div>
                    </div>
                </div>
                <div class="card-desc">${f.description || 'Chưa có mô tả chi tiết cho biểu mẫu này.'}</div>
                <div class="card-footer">
                    <div class="file-badges">
                        <span class="badge ${badgeClass}">${extLabel}</span>
                        ${sizeInfo}
                        ${downloadInfo}
                    </div>
                    <div style="display:flex;gap:6px;">
                        ${viewBtn}
                        ${downloadBtn}
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

function filterByCategory(category, btn) {
    currentCategory = category;
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.tab-btn .count').forEach(c => {
        const btnCat = c.closest('.tab-btn').dataset.category;
        c.textContent = categoryCounts[btnCat] || 0;
    });
    filterForms();
}

function filterForms() {
    const query = document.getElementById('searchInput').value.toLowerCase().trim();
    let filtered = formsData;
    if (currentCategory !== 'all') {
        filtered = filtered.filter(f => f.category === currentCategory);
    }
    if (query) {
        filtered = filtered.filter(f =>
            (f.title || '').toLowerCase().includes(query) ||
            (f.description || '').toLowerCase().includes(query) ||
            (f.category || '').toLowerCase().includes(query)
        );
    }
    renderForms(filtered);
}

function showContactModal(formName) {
    const msg = `Biểu mẫu "${formName}" chưa được tải lên hệ thống.\n\nVui lòng liên hệ:\n• Điện thoại: (0270) 3.826.xxx\n• Email: ubnd.longhiep@vinhlong.gov.vn\n• Đến trực tiếp UBND xã Long Hiệp`;
    alert(msg);
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.tab-btn .count').forEach(c => {
        const btnCat = c.closest('.tab-btn').dataset.category;
        c.textContent = categoryCounts[btnCat] || 0;
    });
    renderForms(formsData);
});
</script>

<?php include 'footer.php'; ?>

</body>
</html>