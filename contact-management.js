// Contact Management JavaScript
function viewContact(contactId) {
    // Tạo modal để xem chi tiết liên hệ
    fetch(`api-contact-detail.php?id=${contactId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showContactModal(data.contact);
            } else {
                alert('Không thể tải thông tin liên hệ: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi tải thông tin liên hệ');
        });
}

function updateStatus(contactId, currentStatus) {
    const statuses = {
        'new': 'Mới',
        'processing': 'Đang xử lý', 
        'resolved': 'Đã giải quyết'
    };
    
    const statusOptions = Object.keys(statuses).map(key => 
        `<option value="${key}" ${key === currentStatus ? 'selected' : ''}>${statuses[key]}</option>`
    ).join('');
    
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal-content">
            <h3>Cập nhật trạng thái</h3>
            <form id="statusForm">
                <div class="form-group">
                    <label for="newStatus">Trạng thái mới:</label>
                    <select id="newStatus" name="status" required>
                        ${statusOptions}
                    </select>
                </div>
                <div class="form-group">
                    <label for="adminNote">Ghi chú (tùy chọn):</label>
                    <textarea id="adminNote" name="note" rows="3" placeholder="Ghi chú về việc xử lý..."></textarea>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">Cập nhật</button>
                    <button type="button" onclick="closeModal()" class="btn-secondary">Hủy</button>
                </div>
            </form>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    document.getElementById('statusForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append('id', contactId);
        formData.append('status', document.getElementById('newStatus').value);
        formData.append('note', document.getElementById('adminNote').value);
        
        fetch('api-update-contact.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Lỗi: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi cập nhật trạng thái');
        });
    });
}

function showContactModal(contact) {
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h3>Chi tiết liên hệ</h3>
                <button onclick="closeModal()" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="contact-detail-grid">
                    <div class="detail-item">
                        <label>Mã phiếu:</label>
                        <span class="ticket-code">${contact.ticket_code}</span>
                    </div>
                    <div class="detail-item">
                        <label>Người gửi:</label>
                        <span>${contact.name}</span>
                    </div>
                    <div class="detail-item">
                        <label>Email:</label>
                        <span>${contact.email}</span>
                    </div>
                    <div class="detail-item">
                        <label>Điện thoại:</label>
                        <span>${contact.phone || 'Không có'}</span>
                    </div>
                    <div class="detail-item">
                        <label>Tiêu đề:</label>
                        <span>${contact.subject}</span>
                    </div>
                    <div class="detail-item">
                        <label>Mức độ:</label>
                        <span class="priority-pill ${getPriorityClass(contact.priority)}">${getPriorityLabel(contact.priority)}</span>
                    </div>
                    <div class="detail-item">
                        <label>Trạng thái:</label>
                        <span class="status-pill ${getStatusClass(contact.status)}">${getStatusLabel(contact.status)}</span>
                    </div>
                    <div class="detail-item">
                        <label>Ngày gửi:</label>
                        <span>${formatDate(contact.created_at)}</span>
                    </div>
                </div>
                <div class="detail-section">
                    <label>Nội dung:</label>
                    <div class="message-content">${contact.message}</div>
                </div>
                ${contact.admin_note ? `
                <div class="detail-section">
                    <label>Ghi chú xử lý:</label>
                    <div class="admin-note">${contact.admin_note}</div>
                </div>
                ` : ''}
            </div>
            <div class="modal-footer">
                <button onclick="updateStatus(${contact.id}, '${contact.status}')" class="btn-primary">Cập nhật trạng thái</button>
                <button onclick="closeModal()" class="btn-secondary">Đóng</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
}

function closeModal() {
    const modal = document.querySelector('.modal-overlay');
    if (modal) {
        modal.remove();
    }
}

function getPriorityClass(priority) {
    const classes = {
        'low': 'is-low',
        'normal': 'is-normal', 
        'high': 'is-high',
        'urgent': 'is-urgent'
    };
    return classes[priority] || 'is-normal';
}

function getPriorityLabel(priority) {
    const labels = {
        'low': 'Thấp',
        'normal': 'Bình thường',
        'high': 'Cao', 
        'urgent': 'Khẩn'
    };
    return labels[priority] || 'Bình thường';
}

function getStatusClass(status) {
    const classes = {
        'new': 'is-new',
        'processing': 'is-processing',
        'resolved': 'is-resolved'
    };
    return classes[status] || 'is-new';
}

function getStatusLabel(status) {
    const labels = {
        'new': 'Mới',
        'processing': 'Đang xử lý',
        'resolved': 'Đã giải quyết'
    };
    return labels[status] || 'Mới';
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('vi-VN');
}

// CSS cho modal
const modalStyles = `
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
}

.modal-content {
    background: white;
    border-radius: 12px;
    padding: 25px;
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-large {
    max-width: 700px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #ecf0f1;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #7f8c8d;
}

.contact-detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.detail-item label {
    font-weight: 600;
    color: #2c3e50;
    font-size: 0.9rem;
}

.detail-section {
    margin-bottom: 20px;
}

.detail-section label {
    display: block;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
}

.message-content,
.admin-note {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    line-height: 1.6;
    white-space: pre-wrap;
}

.admin-note {
    background: #e8f5e8;
    border-left: 4px solid #27ae60;
}

.ticket-code {
    font-family: monospace;
    background: #f8f9fa;
    padding: 4px 8px;
    border-radius: 4px;
    color: #e74c3c;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #2c3e50;
}

.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
}

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
}

.btn-primary,
.btn-secondary {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
}

.btn-primary {
    background: #3498db;
    color: white;
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.modal-footer {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #ecf0f1;
}
`;

// Thêm CSS vào head
const styleSheet = document.createElement('style');
styleSheet.textContent = modalStyles;
document.head.appendChild(styleSheet);