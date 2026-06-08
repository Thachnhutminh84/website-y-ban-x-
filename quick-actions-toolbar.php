<?php
// Quick Actions Toolbar for Admin Users
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (file_exists('auth.php')) {
    require_once 'auth.php';
}

$isLoggedIn = function_exists('authIsLoggedIn') ? authIsLoggedIn() : false;
$isApproved = function_exists('authIsApproved') ? authIsApproved() : false;
$currentRole = function_exists('authCurrentRole') ? authCurrentRole() : '';
$canManageContent = function_exists('authCanManageContent') ? authCanManageContent() : false;

// Show for approved cán bộ and admin users only
if (!$isLoggedIn || !$isApproved || !$canManageContent) {
    return;
}

$quickActions = [
    [
        'title' => 'Thêm tin tức',
        'icon' => '📝',
        'url' => 'them-tin.php',
        'color' => '#e74c3c',
        'roles' => ['admin', 'editor'],
        'require_canbo' => true
    ],
    [
        'title' => 'Thêm video',
        'icon' => '🎬',
        'url' => 'them-video.php',
        'color' => '#8e44ad',
        'roles' => ['admin', 'editor'],
        'require_canbo' => true
    ],
    [
        'title' => 'Phê duyệt tài khoản',
        'icon' => '✅',
        'url' => 'quan-ly-phe-duyet.php',
        'color' => '#f39c12',
        'roles' => ['admin'],
        'require_canbo' => false
    ],
    [
        'title' => 'Danh bạ điện thoại',
        'icon' => '📞',
        'url' => 'danh-ba-dien-thoai.php',
        'color' => '#f39c12',
        'roles' => ['admin', 'editor'],
        'require_canbo' => true
    ],
    [
        'title' => 'Quản lý nội dung',
        'icon' => '📋',
        'url' => 'advanced-content-manager-simple.php',
        'color' => '#16a085',
        'roles' => ['admin'],
        'require_canbo' => false
    ],
    [
        'title' => 'Quản lý video',
        'icon' => '🎥',
        'url' => 'quan-ly-video.php',
        'color' => '#9b59b6',
        'roles' => ['admin', 'editor'],
        'require_canbo' => true
    ],
    [
        'title' => 'Tin nhắn liên hệ',
        'icon' => '📧',
        'url' => 'tin-nhan-lien-he.php',
        'color' => '#27ae60',
        'roles' => ['admin'],
        'require_canbo' => false
    ],
    [
        'title' => 'Dashboard',
        'icon' => '📊',
        'url' => 'enhanced-dashboard.php',
        'color' => '#3498db',
        'roles' => ['admin', 'editor'],
        'require_canbo' => true
    ]
];

// Filter actions based on user role and user type
$quickActions = array_filter($quickActions, function($action) use ($currentRole, $canManageContent) {
    // Check role permission
    if (!in_array($currentRole, $action['roles'])) {
        return false;
    }
    
    // Check if action requires cán bộ
    if (isset($action['require_canbo']) && $action['require_canbo'] && !$canManageContent) {
        return false;
    }
    
    return true;
});
?>

<div class="quick-actions-toolbar" id="quickActionsToolbar">
    <div class="toolbar-toggle" onclick="toggleToolbar()">
        <span class="toggle-icon">⚡</span>
        <span class="toggle-text">Thao tác nhanh</span>
    </div>
    
    <div class="toolbar-content">
        <?php foreach ($quickActions as $action): ?>
        <a href="<?php echo htmlspecialchars($action['url']); ?>" 
           class="quick-action-btn" 
           style="--action-color: <?php echo $action['color']; ?>"
           title="<?php echo htmlspecialchars($action['title']); ?>">
            <span class="action-icon"><?php echo $action['icon']; ?></span>
            <span class="action-text"><?php echo htmlspecialchars($action['title']); ?></span>
        </a>
        <?php endforeach; ?>
        
        <div class="toolbar-separator"></div>
        
        <button class="quick-action-btn settings-btn" onclick="toggleToolbarSettings()" title="Cài đặt toolbar">
            <span class="action-icon">⚙️</span>
            <span class="action-text">Cài đặt</span>
        </button>
    </div>
</div>

<style>
.quick-actions-toolbar {
    position: fixed;
    top: 50%;
    right: 0;
    transform: translateY(-50%);
    z-index: 1000;
    background: white;
    border-radius: 12px 0 0 12px;
    box-shadow: -4px 0 20px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
    max-width: 250px;
}

/* Position variants */
.quick-actions-toolbar.position-right {
    top: 50%;
    right: 0;
    left: auto;
    bottom: auto;
    transform: translateY(-50%);
    border-radius: 12px 0 0 12px;
}

.quick-actions-toolbar.position-left {
    top: 50%;
    left: 0;
    right: auto;
    bottom: auto;
    transform: translateY(-50%);
    border-radius: 0 12px 12px 0;
    box-shadow: 4px 0 20px rgba(0,0,0,0.15);
}

.quick-actions-toolbar.position-left .toolbar-toggle {
    border-radius: 0 12px 12px 0;
}

.quick-actions-toolbar.position-top {
    top: 80px;
    left: 50%;
    right: auto;
    bottom: auto;
    transform: translateX(-50%);
    border-radius: 0 0 12px 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    max-width: 90%;
}

.quick-actions-toolbar.position-top .toolbar-toggle {
    border-radius: 0 0 12px 12px;
}

.quick-actions-toolbar.position-top .toolbar-content {
    flex-direction: row;
    flex-wrap: wrap;
    max-width: 100%;
}

.quick-actions-toolbar.position-bottom {
    bottom: 20px;
    left: 50%;
    top: auto;
    right: auto;
    transform: translateX(-50%);
    border-radius: 12px 12px 0 0;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.15);
    max-width: 90%;
}

.quick-actions-toolbar.position-bottom .toolbar-toggle {
    border-radius: 12px 12px 0 0;
}

.quick-actions-toolbar.position-bottom .toolbar-content {
    flex-direction: row;
    flex-wrap: wrap;
    max-width: 100%;
}

.quick-actions-toolbar.collapsed {
    transform: translateY(-50%) translateX(calc(100% - 50px));
}

.quick-actions-toolbar.position-left.collapsed {
    transform: translateY(-50%) translateX(calc(-100% + 50px));
}

.quick-actions-toolbar.position-top.collapsed {
    transform: translateX(-50%) translateY(-100%);
}

.quick-actions-toolbar.position-bottom.collapsed {
    transform: translateX(-50%) translateY(100%);
}

.toolbar-toggle {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 15px;
    background: var(--gradient-primary);
    color: white;
    cursor: pointer;
    border-radius: 12px 0 0 12px;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.toolbar-toggle:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
}

.toggle-icon {
    font-size: 16px;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.toolbar-content {
    padding: 15px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-width: 200px;
}

.quick-action-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    background: white;
    border: 2px solid var(--action-color, #3498db);
    border-radius: 8px;
    text-decoration: none;
    color: var(--action-color, #3498db);
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    cursor: pointer;
}

.quick-action-btn:hover {
    background: var(--action-color, #3498db);
    color: white;
    transform: translateX(-3px);
}

.action-icon {
    font-size: 16px;
    width: 20px;
    text-align: center;
}

.action-text {
    flex: 1;
}

.toolbar-separator {
    height: 1px;
    background: #e9ecef;
    margin: 5px 0;
}

.settings-btn {
    --action-color: #6c757d;
    font-size: 12px;
    padding: 8px 10px;
}

/* Responsive */
@media (max-width: 768px) {
    .quick-actions-toolbar {
        position: fixed;
        bottom: 20px;
        right: 20px;
        top: auto;
        transform: none;
        border-radius: 12px;
        max-width: 280px;
    }
    
    .quick-actions-toolbar.collapsed {
        transform: translateX(calc(100% - 45px));
    }
    
    .toolbar-toggle {
        border-radius: 12px 12px 0 0;
    }
    
    .toolbar-content {
        min-width: 200px;
        padding: 12px;
    }
    
    .quick-action-btn {
        padding: 8px 10px;
        font-size: 13px;
    }
    
    .action-text {
        font-size: 12px;
    }
}

@media (max-width: 480px) {
    .quick-actions-toolbar {
        bottom: 10px;
        right: 10px;
    }
    
    .toolbar-content {
        padding: 10px;
        gap: 6px;
    }
    
    .quick-action-btn {
        padding: 6px 8px;
    }
}

/* Hide on very small screens */
@media (max-width: 360px) {
    .quick-actions-toolbar {
        display: none;
    }
}
</style>

<script>
let toolbarCollapsed = localStorage.getItem('toolbarCollapsed') === 'true';

function toggleToolbar() {
    const toolbar = document.getElementById('quickActionsToolbar');
    toolbarCollapsed = !toolbarCollapsed;
    
    if (toolbarCollapsed) {
        toolbar.classList.add('collapsed');
    } else {
        toolbar.classList.remove('collapsed');
    }
    
    localStorage.setItem('toolbarCollapsed', toolbarCollapsed);
}

function toggleToolbarSettings() {
    const settings = [
        'Ẩn/hiện toolbar',
        'Thay đổi vị trí',
        'Tùy chỉnh thao tác'
    ];
    
    const choice = prompt('Cài đặt toolbar:\n1. ' + settings[0] + '\n2. ' + settings[1] + '\n3. ' + settings[2] + '\n\nNhập số (1-3):');
    
    switch(choice) {
        case '1':
            toggleToolbar();
            break;
        case '2':
            changeToolbarPosition();
            break;
        case '3':
            customizeActions();
            break;
        default:
            if (choice !== null) {
                alert('Vui lòng chọn số từ 1-3');
            }
            break;
    }
}

// Thay đổi vị trí toolbar
function changeToolbarPosition() {
    const positions = [
        'Bên phải (mặc định)',
        'Bên trái',
        'Phía trên',
        'Phía dưới'
    ];
    
    const choice = prompt('Chọn vị trí toolbar:\n1. ' + positions[0] + '\n2. ' + positions[1] + '\n3. ' + positions[2] + '\n4. ' + positions[3] + '\n\nNhập số (1-4):');
    
    const toolbar = document.getElementById('quickActionsToolbar');
    
    // Remove all position classes
    toolbar.classList.remove('position-right', 'position-left', 'position-top', 'position-bottom');
    
    switch(choice) {
        case '1':
            toolbar.classList.add('position-right');
            localStorage.setItem('toolbarPosition', 'right');
            alert('✅ Đã chuyển toolbar sang bên phải');
            break;
        case '2':
            toolbar.classList.add('position-left');
            localStorage.setItem('toolbarPosition', 'left');
            alert('✅ Đã chuyển toolbar sang bên trái');
            break;
        case '3':
            toolbar.classList.add('position-top');
            localStorage.setItem('toolbarPosition', 'top');
            alert('✅ Đã chuyển toolbar lên phía trên');
            break;
        case '4':
            toolbar.classList.add('position-bottom');
            localStorage.setItem('toolbarPosition', 'bottom');
            alert('✅ Đã chuyển toolbar xuống phía dưới');
            break;
        default:
            if (choice !== null) {
                alert('Vui lòng chọn số từ 1-4');
            }
            break;
    }
}

// Tùy chỉnh thao tác
function customizeActions() {
    const options = [
        'Ẩn/hiện các nút',
        'Sắp xếp lại thứ tự',
        'Đặt lại mặc định'
    ];
    
    const choice = prompt('Tùy chỉnh thao tác:\n1. ' + options[0] + '\n2. ' + options[1] + '\n3. ' + options[2] + '\n\nNhập số (1-3):');
    
    switch(choice) {
        case '1':
            toggleActionButtons();
            break;
        case '2':
            alert('💡 Kéo thả các nút để sắp xếp lại thứ tự (tính năng đang phát triển)');
            break;
        case '3':
            if (confirm('Bạn có chắc muốn đặt lại tất cả về mặc định?')) {
                localStorage.removeItem('hiddenActions');
                localStorage.removeItem('toolbarPosition');
                location.reload();
            }
            break;
        default:
            if (choice !== null) {
                alert('Vui lòng chọn số từ 1-3');
            }
            break;
    }
}

// Ẩn/hiện các nút thao tác
function toggleActionButtons() {
    const buttons = document.querySelectorAll('.quick-action-btn:not(.settings-btn)');
    let actionsList = '';
    
    buttons.forEach((btn, index) => {
        const title = btn.getAttribute('title') || btn.querySelector('.action-text').textContent;
        const isHidden = btn.style.display === 'none';
        actionsList += `${index + 1}. ${isHidden ? '☐' : '☑'} ${title}\n`;
    });
    
    const choice = prompt('Chọn nút để ẩn/hiện (nhập số, cách nhau bởi dấu phẩy):\n\n' + actionsList + '\nVí dụ: 1,3,5');
    
    if (choice === null) return;
    
    const indices = choice.split(',').map(s => parseInt(s.trim()) - 1);
    const hiddenActions = [];
    
    buttons.forEach((btn, index) => {
        if (indices.includes(index)) {
            if (btn.style.display === 'none') {
                btn.style.display = '';
            } else {
                btn.style.display = 'none';
                hiddenActions.push(index);
            }
        }
    });
    
    localStorage.setItem('hiddenActions', JSON.stringify(hiddenActions));
    alert('✅ Đã cập nhật hiển thị các nút');
}

// Initialize toolbar state
document.addEventListener('DOMContentLoaded', function() {
    const toolbar = document.getElementById('quickActionsToolbar');
    
    // Load toolbar collapsed state
    if (toolbarCollapsed) {
        toolbar.classList.add('collapsed');
    }
    
    // Load toolbar position from localStorage
    const savedPosition = localStorage.getItem('toolbarPosition');
    if (savedPosition) {
        toolbar.classList.add('position-' + savedPosition);
    } else {
        toolbar.classList.add('position-right'); // Default position
    }
    
    // Load hidden actions from localStorage
    const hiddenActions = JSON.parse(localStorage.getItem('hiddenActions') || '[]');
    const buttons = document.querySelectorAll('.quick-action-btn:not(.settings-btn)');
    hiddenActions.forEach(index => {
        if (buttons[index]) {
            buttons[index].style.display = 'none';
        }
    });
    

});
</script>