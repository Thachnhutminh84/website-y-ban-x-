<?php
// Chatbox widget - chỉ hiển thị cho cán bộ
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (file_exists('auth.php')) {
    require_once 'auth.php';
}

$isLoggedIn = function_exists('authIsLoggedIn') ? authIsLoggedIn() : false;
$isApproved = function_exists('authIsApproved') ? authIsApproved() : false;
$canManageContent = function_exists('authCanManageContent') ? authCanManageContent() : false;

// Chỉ hiển thị cho cán bộ và admin đã được phê duyệt
if (!$isLoggedIn || !$isApproved || !$canManageContent) {
    return;
}

$currentUserName = authDisplayName();
$currentUserId = authCurrentUserId();
?>

<!-- Chatbox Widget -->
<div id="chatbox-widget" class="chatbox-widget">
    <!-- Chat Button -->
    <button id="chat-toggle-btn" class="chat-toggle-btn" onclick="toggleChatbox()">
        <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
            <path d="M16 2C8.268 2 2 7.925 2 15.25C2 18.638 3.414 21.712 5.75 24L4 30L10.5 27.75C12.25 28.5 14.083 29 16 29C23.732 29 30 23.075 30 15.75C30 8.425 23.732 2 16 2Z" fill="white"/>
        </svg>
        <span class="chat-badge" id="chat-badge" style="display: none;">0</span>
    </button>

    <!-- Chat Window -->
    <div id="chat-window" class="chat-window" style="display: none;">
        <!-- Header -->
        <div class="chat-header">
            <div class="chat-header-info">
                <h3>💬 Chat Cán bộ</h3>
                <p class="chat-online-count">
                    <span class="online-dot"></span>
                    <span id="online-count">0</span> đang online
                </p>
            </div>
            <button class="chat-close-btn" onclick="toggleChatbox()">✕</button>
        </div>

        <!-- Messages Area -->
        <div id="chat-messages" class="chat-messages">
            <div class="chat-loading">Đang tải tin nhắn...</div>
        </div>

        <!-- Input Area -->
        <div class="chat-input-area">
            <textarea 
                id="chat-input" 
                class="chat-input" 
                placeholder="Nhập tin nhắn..."
                rows="1"
                onkeypress="handleChatKeyPress(event)"></textarea>
            <button class="chat-send-btn" onclick="sendMessage()">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M2 21L23 12L2 3V10L17 12L2 14V21Z" fill="currentColor"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<style>
.chatbox-widget {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
}

.chat-toggle-btn {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0068FF 0%, #0095FF 100%);
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0, 104, 255, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    position: relative;
}

.chat-toggle-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 16px rgba(0, 104, 255, 0.6);
}

.chat-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #ff4444;
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: bold;
}

.chat-window {
    position: fixed;
    bottom: 90px;
    right: 20px;
    width: 380px;
    height: 550px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.chat-header {
    background: linear-gradient(135deg, #0068FF 0%, #0095FF 100%);
    color: white;
    padding: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-header-info h3 {
    margin: 0;
    font-size: 18px;
}

.chat-online-count {
    margin: 4px 0 0 0;
    font-size: 13px;
    opacity: 0.9;
    display: flex;
    align-items: center;
    gap: 6px;
}

.online-dot {
    width: 8px;
    height: 8px;
    background: #4ade80;
    border-radius: 50%;
    display: inline-block;
    animation: pulse-dot 2s infinite;
}

@keyframes pulse-dot {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.chat-close-btn {
    background: none;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background 0.2s;
}

.chat-close-btn:hover {
    background: rgba(255, 255, 255, 0.2);
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    background: #f8f9fa;
}

.chat-loading {
    text-align: center;
    color: #6c757d;
    padding: 20px;
}

.chat-message {
    margin-bottom: 12px;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.chat-message.mine {
    text-align: right;
}

.message-sender {
    font-size: 12px;
    color: #6c757d;
    margin-bottom: 4px;
    font-weight: 600;
}

.message-bubble {
    display: inline-block;
    max-width: 75%;
    padding: 10px 14px;
    border-radius: 16px;
    word-wrap: break-word;
    white-space: pre-wrap;
}

.chat-message:not(.mine) .message-bubble {
    background: white;
    color: #2c3e50;
    border-bottom-left-radius: 4px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.chat-message.mine .message-bubble {
    background: linear-gradient(135deg, #0068FF 0%, #0095FF 100%);
    color: white;
    border-bottom-right-radius: 4px;
}

.message-time {
    font-size: 11px;
    color: #6c757d;
    margin-top: 4px;
}

.chat-input-area {
    padding: 12px;
    background: white;
    border-top: 1px solid #e9ecef;
    display: flex;
    gap: 8px;
    align-items: flex-end;
}

.chat-input {
    flex: 1;
    border: 1px solid #dee2e6;
    border-radius: 20px;
    padding: 10px 16px;
    font-size: 14px;
    resize: none;
    max-height: 100px;
    font-family: inherit;
}

.chat-input:focus {
    outline: none;
    border-color: #0068FF;
}

.chat-send-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0068FF 0%, #0095FF 100%);
    border: none;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.chat-send-btn:hover {
    transform: scale(1.1);
}

.chat-send-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Responsive */
@media (max-width: 480px) {
    .chat-window {
        width: calc(100vw - 40px);
        height: calc(100vh - 120px);
        right: 20px;
        bottom: 90px;
    }
}

/* Scrollbar */
.chat-messages::-webkit-scrollbar {
    width: 6px;
}

.chat-messages::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.chat-messages::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.chat-messages::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

<script>
let chatLastMessageId = 0;
let chatPollInterval = null;
let chatStatusInterval = null;
let isChatOpen = false;

function toggleChatbox() {
    const chatWindow = document.getElementById('chat-window');
    const chatBtn = document.getElementById('chat-toggle-btn');
    
    isChatOpen = !isChatOpen;
    
    if (isChatOpen) {
        chatWindow.style.display = 'flex';
        chatBtn.style.display = 'none';
        loadChatMessages();
        startChatPolling();
        updateOnlineStatus();
        document.getElementById('chat-input').focus();
    } else {
        chatWindow.style.display = 'none';
        chatBtn.style.display = 'flex';
        stopChatPolling();
    }
}

function loadChatMessages(lastId = 0) {
    fetch(`chat-api.php?action=get_messages&last_id=${lastId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const messagesDiv = document.getElementById('chat-messages');
                
                if (lastId === 0) {
                    messagesDiv.innerHTML = '';
                }
                
                if (data.messages.length === 0 && lastId === 0) {
                    messagesDiv.innerHTML = '<div class="chat-loading">Chưa có tin nhắn nào. Hãy bắt đầu trò chuyện!</div>';
                    return;
                }
                
                data.messages.forEach(msg => {
                    if (msg.id > chatLastMessageId) {
                        appendMessage(msg);
                        chatLastMessageId = msg.id;
                    }
                });
                
                // Scroll to bottom
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            }
        })
        .catch(error => console.error('Error loading messages:', error));
}

function appendMessage(msg) {
    const messagesDiv = document.getElementById('chat-messages');
    const messageDiv = document.createElement('div');
    messageDiv.className = 'chat-message' + (msg.is_mine ? ' mine' : '');
    
    const time = new Date(msg.created_at).toLocaleTimeString('vi-VN', {
        hour: '2-digit',
        minute: '2-digit'
    });
    
    messageDiv.innerHTML = `
        ${!msg.is_mine ? `<div class="message-sender">${escapeHtml(msg.sender_name)}</div>` : ''}
        <div class="message-bubble">${escapeHtml(msg.message)}</div>
        <div class="message-time">${time}</div>
    `;
    
    messagesDiv.appendChild(messageDiv);
}

function sendMessage() {
    const input = document.getElementById('chat-input');
    const message = input.value.trim();
    
    if (!message) return;
    
    const sendBtn = document.querySelector('.chat-send-btn');
    sendBtn.disabled = true;
    
    const formData = new FormData();
    formData.append('action', 'send');
    formData.append('message', message);
    
    fetch('chat-api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            input.style.height = 'auto';
            loadChatMessages(chatLastMessageId);
        } else {
            alert('Không thể gửi tin nhắn: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
        alert('Lỗi khi gửi tin nhắn');
    })
    .finally(() => {
        sendBtn.disabled = false;
        input.focus();
    });
}

function handleChatKeyPress(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage();
    }
}

function startChatPolling() {
    // Poll for new messages every 3 seconds
    chatPollInterval = setInterval(() => {
        loadChatMessages(chatLastMessageId);
    }, 3000);
    
    // Update online status every 30 seconds
    chatStatusInterval = setInterval(() => {
        updateOnlineStatus();
        loadOnlineUsers();
    }, 30000);
    
    // Load online users immediately
    loadOnlineUsers();
}

function stopChatPolling() {
    if (chatPollInterval) {
        clearInterval(chatPollInterval);
        chatPollInterval = null;
    }
    if (chatStatusInterval) {
        clearInterval(chatStatusInterval);
        chatStatusInterval = null;
    }
}

function updateOnlineStatus() {
    fetch('chat-api.php?action=update_status')
        .catch(error => console.error('Error updating status:', error));
}

function loadOnlineUsers() {
    fetch('chat-api.php?action=get_online_users')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('online-count').textContent = data.total;
            }
        })
        .catch(error => console.error('Error loading online users:', error));
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Auto-resize textarea
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('chat-input');
    if (input) {
        input.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        });
    }
    
    // Set offline when page unloads
    window.addEventListener('beforeunload', function() {
        if (isChatOpen) {
            navigator.sendBeacon('chat-api.php?action=set_offline');
        }
    });
});
</script>
