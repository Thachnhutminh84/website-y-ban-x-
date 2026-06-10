<?php
// Chatbot Widget - UBND Xã Long Hiệp, Vĩnh Long
// Included widget only - not a standalone page
?>
<!-- Chatbot Widget -->
<style>
    :root {
        --primary: #1a73e8;
        --primary-dark: #1558b0;
        --gradient-primary: linear-gradient(135deg, #1a73e8, #6c5ce7);
        --gradient-header: linear-gradient(135deg, #1a73e8, #4a6cf7);
        --white: #ffffff;
        --gray-50: #f8f9fa;
        --gray-100: #f1f3f5;
        --gray-200: #e9ecef;
        --gray-300: #dee2e6;
        --gray-600: #6c757d;
        --gray-700: #495057;
        --gray-800: #343a40;
        --shadow-sm: 0 2px 8px rgba(0,0,0,0.08);
        --shadow-md: 0 4px 20px rgba(0,0,0,0.12);
        --shadow-lg: 0 8px 40px rgba(0,0,0,0.18);
        --radius-sm: 8px;
        --radius-md: 12px;
        --radius-lg: 16px;
        --radius-full: 50%;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Toggle Button */
    .chatbot-toggle {
        position: fixed;
        bottom: 24px;
        right: 24px;
        width: 60px;
        height: 60px;
        border-radius: var(--radius-full);
        background: var(--gradient-primary);
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: var(--shadow-lg);
        transition: var(--transition);
        z-index: 9999;
        animation: pulse-chat 2s ease-in-out infinite;
    }
    .chatbot-toggle:hover {
        transform: scale(1.1);
        box-shadow: 0 8px 30px rgba(26,115,232,0.4);
    }
    .chatbot-toggle.active {
        animation: none;
        background: linear-gradient(135deg, #e74c3c, #c0392b);
    }
    .chatbot-toggle svg {
        width: 28px;
        height: 28px;
        fill: var(--white);
        transition: var(--transition);
    }
    .chatbot-toggle.active svg {
        transform: rotate(90deg);
    }
    .chatbot-toggle .notification-dot {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 14px;
        height: 14px;
        background: #e74c3c;
        border-radius: var(--radius-full);
        border: 2px solid var(--white);
        animation: bounce-dot 1.5s ease-in-out infinite;
    }
    @keyframes pulse-chat {
        0%, 100% { box-shadow: var(--shadow-lg), 0 0 0 0 rgba(26,115,232,0.4); }
        50% { box-shadow: var(--shadow-lg), 0 0 0 12px rgba(26,115,232,0); }
    }
    @keyframes bounce-dot {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.2); }
    }

    /* Chat Window */
    .chatbot-window {
        position: fixed;
        bottom: 96px;
        right: 24px;
        width: 380px;
        height: 520px;
        background: var(--white);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-lg);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        z-index: 9998;
        opacity: 0;
        visibility: hidden;
        transform: translateY(20px) scale(0.95);
        transition: var(--transition);
    }
    .chatbot-window.open {
        opacity: 1;
        visibility: visible;
        transform: translateY(0) scale(1);
    }

    /* Header */
    .chatbot-header {
        background: var(--gradient-header);
        color: var(--white);
        padding: 16px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
    }
    .chatbot-header-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .chatbot-header-avatar {
        width: 38px;
        height: 38px;
        border-radius: var(--radius-full);
        background: rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .chatbot-header-avatar svg {
        width: 22px;
        height: 22px;
        fill: var(--white);
    }
    .chatbot-header-text h3 {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
    }
    .chatbot-header-text span {
        font-size: 12px;
        opacity: 0.85;
    }
    .chatbot-header-close {
        background: rgba(255,255,255,0.15);
        border: none;
        color: var(--white);
        width: 32px;
        height: 32px;
        border-radius: var(--radius-full);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition);
        font-size: 18px;
        line-height: 1;
    }
    .chatbot-header-close:hover {
        background: rgba(255,255,255,0.3);
    }

    /* Messages Area */
    .chatbot-messages {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        background: var(--gray-50);
        scroll-behavior: smooth;
    }
    .chatbot-messages::-webkit-scrollbar {
        width: 5px;
    }
    .chatbot-messages::-webkit-scrollbar-track {
        background: transparent;
    }
    .chatbot-messages::-webkit-scrollbar-thumb {
        background: var(--gray-300);
        border-radius: 10px;
    }

    /* Message Bubbles */
    .chatbot-msg {
        display: flex;
        gap: 8px;
        max-width: 88%;
        animation: fadeInMsg 0.3s ease;
    }
    .chatbot-msg.bot {
        align-self: flex-start;
    }
    .chatbot-msg.user {
        align-self: flex-end;
        flex-direction: row-reverse;
    }
    .chatbot-msg-avatar {
        width: 30px;
        height: 30px;
        border-radius: var(--radius-full);
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 2px;
    }
    .chatbot-msg.bot .chatbot-msg-avatar {
        background: var(--gradient-primary);
    }
    .chatbot-msg.bot .chatbot-msg-avatar svg {
        width: 16px;
        height: 16px;
        fill: var(--white);
    }
    .chatbot-msg.user .chatbot-msg-avatar {
        background: var(--gray-200);
    }
    .chatbot-msg.user .chatbot-msg-avatar svg {
        width: 16px;
        height: 16px;
        fill: var(--gray-600);
    }
    .chatbot-msg-content {
        padding: 10px 14px;
        border-radius: var(--radius-md);
        font-size: 14px;
        line-height: 1.5;
        word-wrap: break-word;
    }
    .chatbot-msg.bot .chatbot-msg-content {
        background: var(--white);
        color: var(--gray-800);
        border-bottom-left-radius: 4px;
        box-shadow: var(--shadow-sm);
    }
    .chatbot-msg.user .chatbot-msg-content {
        background: var(--primary);
        color: var(--white);
        border-bottom-right-radius: 4px;
    }
    @keyframes fadeInMsg {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Typing Indicator */
    .chatbot-typing {
        display: none;
        align-self: flex-start;
        padding: 10px 14px;
        background: var(--white);
        border-radius: var(--radius-md);
        border-bottom-left-radius: 4px;
        box-shadow: var(--shadow-sm);
        margin-left: 38px;
    }
    .chatbot-typing.active {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .chatbot-typing span {
        width: 7px;
        height: 7px;
        background: var(--gray-300);
        border-radius: var(--radius-full);
        animation: typing-bounce 1.4s ease-in-out infinite;
    }
    .chatbot-typing span:nth-child(2) { animation-delay: 0.2s; }
    .chatbot-typing span:nth-child(3) { animation-delay: 0.4s; }
    @keyframes typing-bounce {
        0%, 60%, 100% { transform: translateY(0); background: var(--gray-300); }
        30% { transform: translateY(-6px); background: var(--primary); }
    }

    /* Quick Reply Buttons */
    .chatbot-quick-replies {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 4px 0;
    }
    .chatbot-quick-btn {
        background: var(--white);
        color: var(--primary);
        border: 1.5px solid var(--primary);
        border-radius: 20px;
        padding: 7px 14px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: var(--transition);
        white-space: nowrap;
    }
    .chatbot-quick-btn:hover {
        background: var(--primary);
        color: var(--white);
        transform: translateY(-1px);
        box-shadow: 0 3px 12px rgba(26,115,232,0.3);
    }

    /* Input Area */
    .chatbot-input-area {
        padding: 12px 16px;
        background: var(--white);
        border-top: 1px solid var(--gray-200);
        display: flex;
        gap: 10px;
        align-items: center;
        flex-shrink: 0;
    }
    .chatbot-input {
        flex: 1;
        border: 1.5px solid var(--gray-200);
        border-radius: 24px;
        padding: 10px 16px;
        font-size: 14px;
        font-family: inherit;
        outline: none;
        transition: var(--transition);
        color: var(--gray-800);
        background: var(--gray-50);
    }
    .chatbot-input:focus {
        border-color: var(--primary);
        background: var(--white);
        box-shadow: 0 0 0 3px rgba(26,115,232,0.1);
    }
    .chatbot-input::placeholder {
        color: var(--gray-600);
    }
    .chatbot-send-btn {
        width: 40px;
        height: 40px;
        border-radius: var(--radius-full);
        background: var(--gradient-primary);
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: var(--transition);
        flex-shrink: 0;
    }
    .chatbot-send-btn:hover {
        transform: scale(1.08);
        box-shadow: 0 4px 15px rgba(26,115,232,0.35);
    }
    .chatbot-send-btn svg {
        width: 18px;
        height: 18px;
        fill: var(--white);
    }

    /* Responsive */
    @media (max-width: 480px) {
        .chatbot-toggle {
            bottom: 16px;
            right: 16px;
            width: 54px;
            height: 54px;
        }
        .chatbot-window {
            bottom: 0;
            right: 0;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            border-radius: 0;
        }
        .chatbot-header {
            padding: 14px 16px;
            border-radius: 0;
        }
    }
</style>

<!-- Toggle Button -->
<button class="chatbot-toggle" id="chatbotToggle" aria-label="Mở trợ lý trực tuyến">
    <svg viewBox="0 0 24 24"><path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H5.17L4 17.17V4h16v12zM7 9h2v2H7zm4 0h2v2h-2zm4 0h2v2h-2z"/></svg>
    <span class="notification-dot" id="chatbotDot"></span>
</button>

<!-- Chat Window -->
<div class="chatbot-window" id="chatbotWindow">
    <div class="chatbot-header">
        <div class="chatbot-header-info">
            <div class="chatbot-header-avatar">
                <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
            </div>
            <div class="chatbot-header-text">
                <h3>Hỗ trợ trực tuyến</h3>
                <span>UBND Xã Long Hiệp</span>
            </div>
        </div>
        <button class="chatbot-header-close" id="chatbotClose" aria-label="Đóng">×</button>
    </div>

    <div class="chatbot-messages" id="chatbotMessages">
        <div class="chatbot-msg bot">
            <div class="chatbot-msg-avatar">
                <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>
            </div>
            <div class="chatbot-msg-content">
                Xin chào! Tôi là trợ lý ảo của UBND Xã Long Hiệp. Tôi có thể giúp bạn tìm thông tin về:
                <div class="chatbot-quick-replies">
                    <button class="chatbot-quick-btn" onclick="sendQuickReply('Thủ tục hành chính')">Thủ tục hành chính</button>
                    <button class="chatbot-quick-btn" onclick="sendQuickReply('Lịch tiếp dân')">Lịch tiếp dân</button>
                    <button class="chatbot-quick-btn" onclick="sendQuickReply('Liên hệ')">Liên hệ</button>
                    <button class="chatbot-quick-btn" onclick="sendQuickReply('Tìm kiếm')">Tìm kiếm</button>
                </div>
            </div>
        </div>
    </div>

    <div class="chatbot-typing" id="chatbotTyping">
        <span></span><span></span><span></span>
    </div>

    <div class="chatbot-input-area">
        <input type="text" class="chatbot-input" id="chatbotInput" placeholder="Nhập câu hỏi của bạn..." autocomplete="off">
        <button class="chatbot-send-btn" id="chatbotSendBtn" aria-label="Gửi">
            <svg viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
        </button>
    </div>
</div>

<script>
(function() {
    const toggle = document.getElementById('chatbotToggle');
    const win = document.getElementById('chatbotWindow');
    const closeBtn = document.getElementById('chatbotClose');
    const messages = document.getElementById('chatbotMessages');
    const input = document.getElementById('chatbotInput');
    const sendBtn = document.getElementById('chatbotSendBtn');
    const typing = document.getElementById('chatbotTyping');
    const dot = document.getElementById('chatbotDot');

    // FAQ Knowledge Base
    const faq = [
        {
            keywords: ['thủ tục', 'hành chính', 'tthc', 'giấy tờ', 'hồ sơ', 'lệ phí'],
            response: 'UBND xã đang quản lý 410 thủ tục hành chính. Bạn có thể xem chi tiết tại trang Dịch vụ công.'
        },
        {
            keywords: ['lịch tiếp dân', 'tiếp dân', 'gặp gỡ', 'phản ánh', 'khiếu nại', 'tố cáo'],
            response: 'Lịch tiếp dân định kỳ: Thứ 2 và Thứ 5 hàng tuần, từ 8:00 - 11:00, 13:30 - 17:00 tại trụ sở UBND xã.'
        },
        {
            keywords: ['liên hệ', 'điện thoại', 'số điện thoại', 'email', 'hotline', 'gọi', 'fax'],
            response: 'Điện thoại: (0272) 3xxx xxx | Email: ubnd@longhiep.vinhlong.gov.vn'
        },
        {
            keywords: ['đăng ký', 'tài khoản', 'đăng nhập', 'tạo tài khoản', 'người dùng', 'đăng kí'],
            response: 'Bạn có thể đăng ký tài khoản tại trang Đăng ký để sử dụng dịch vụ trực tuyến.'
        },
        {
            keywords: ['giờ làm việc', 'làm việc', 'thời gian', 'giờ mở cửa', 'mở cửa', 'nghỉ', 'cuối tuần'],
            response: 'Thứ 2 - Thứ 6: 7:30 - 11:30 | 13:30 - 17:00. Thứ 7: 7:30 - 11:30. Chủ nhật: Nghỉ.'
        },
        {
            keywords: ['địa chỉ', 'ở đâu', 'nơi nào', 'trụ sở', 'vị trí', 'map'],
            response: 'Trụ sở UBND xã Long Hiệp, tỉnh Vĩnh Long. Bạn có thể xem bản đồ tại trang Liên hệ.'
        },
        {
            keywords: ['dịch vụ', 'công', 'trực tuyến', 'online', 'mạng'],
            response: 'Hiện nay UBND xã Long Hiệp cung cấp nhiều dịch vụ công trực tuyến. Bạn có thể truy cập trang Dịch vụ công để biết thêm chi tiết.'
        },
        {
            keywords: ['cảm ơn', 'thanks', 'thank', 'cám ơn', 'ok', 'tốt'],
            response: 'Rất vui được hỗ trợ bạn! Nếu có câu hỏi khác, đừng ngần ngại hỏi nhé.'
        },
        {
            keywords: ['xin chào', 'chào', 'hello', 'hi', 'hey', 'alo'],
            response: 'Xin chào! Tôi có thể giúp bạn tìm thông tin về thủ tục hành chính, lịch tiếp dân, liên hệ và nhiều thông tin khác.'
        },
        {
            keywords: ['tìm kiếm', 'tra cứu', 'tìm', 'search'],
            response: 'Bạn có thể sử dụng ô tìm kiếm trên trang chủ hoặc truy cập các trang Dịch vụ công, Hỏi - Đáp để tìm thông tin cần thiết.'
        }
    ];

    const defaultResponse = 'Xin lỗi, tôi chưa hiểu câu hỏi. Bạn có thể thử hỏi lại hoặc liên hệ trực tiếp UBND xã.';

    function toggleChat() {
        win.classList.toggle('open');
        toggle.classList.toggle('active');
        if (win.classList.contains('open')) {
            dot.style.display = 'none';
            input.focus();
        }
    }

    function scrollToBottom() {
        messages.scrollTop = messages.scrollHeight;
    }

    function addMessage(text, isUser) {
        const msg = document.createElement('div');
        msg.className = 'chatbot-msg ' + (isUser ? 'user' : 'bot');

        const avatar = document.createElement('div');
        avatar.className = 'chatbot-msg-avatar';
        avatar.innerHTML = isUser
            ? '<svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>'
            : '<svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>';

        const content = document.createElement('div');
        content.className = 'chatbot-msg-content';
        content.textContent = text;

        msg.appendChild(avatar);
        msg.appendChild(content);
        messages.appendChild(msg);
        scrollToBottom();
    }

    function addBotMessageWithQuickReplies(text, replies) {
        const msg = document.createElement('div');
        msg.className = 'chatbot-msg bot';

        const avatar = document.createElement('div');
        avatar.className = 'chatbot-msg-avatar';
        avatar.innerHTML = '<svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>';

        const content = document.createElement('div');
        content.className = 'chatbot-msg-content';
        content.textContent = text;

        if (replies && replies.length) {
            const qr = document.createElement('div');
            qr.className = 'chatbot-quick-replies';
            replies.forEach(function(r) {
                const btn = document.createElement('button');
                btn.className = 'chatbot-quick-btn';
                btn.textContent = r;
                btn.onclick = function() { sendQuickReply(r); };
                qr.appendChild(btn);
            });
            content.appendChild(qr);
        }

        msg.appendChild(avatar);
        msg.appendChild(content);
        messages.appendChild(msg);
        scrollToBottom();
    }

    function getResponse(input) {
        const lower = input.toLowerCase().trim();
        for (let i = 0; i < faq.length; i++) {
            for (let j = 0; j < faq[i].keywords.length; j++) {
                if (lower.includes(faq[i].keywords[j])) {
                    return faq[i].response;
                }
            }
        }
        return defaultResponse;
    }

    function showTyping() {
        typing.classList.add('active');
        scrollToBottom();
    }

    function hideTyping() {
        typing.classList.remove('active');
    }

    function handleSend() {
        const text = input.value.trim();
        if (!text) return;

        addMessage(text, true);
        input.value = '';

        showTyping();
        const delay = 600 + Math.random() * 800;
        setTimeout(function() {
            hideTyping();
            const response = getResponse(text);
            addBotMessageWithQuickReplies(response, ['Thủ tục hành chính', 'Lịch tiếp dân', 'Liên hệ', 'Giờ làm việc']);
        }, delay);
    }

    window.sendQuickReply = function(text) {
        addMessage(text, true);
        showTyping();
        const delay = 500 + Math.random() * 600;
        setTimeout(function() {
            hideTyping();
            const response = getResponse(text);
            addBotMessageWithQuickReplies(response, ['Thủ tục hành chính', 'Lịch tiếp dân', 'Liên hệ', 'Giờ làm việc']);
        }, delay);
    };

    toggle.addEventListener('click', toggleChat);
    closeBtn.addEventListener('click', toggleChat);
    sendBtn.addEventListener('click', handleSend);
    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') handleSend();
    });
})();
</script>