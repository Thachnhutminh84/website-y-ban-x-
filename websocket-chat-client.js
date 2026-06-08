/**
 * WEBSOCKET CHAT CLIENT - Client-side cho real-time chat
 * Include file này trong chatbox.php
 */

class WebSocketChatClient {
    constructor(wsUrl, userId, username) {
        this.wsUrl = wsUrl;
        this.userId = userId;
        this.username = username;
        this.ws = null;
        this.reconnectInterval = 5000;
        this.reconnectTimer = null;
        this.isConnected = false;
        this.messageHandlers = [];
        this.onlineUsersHandlers = [];
        this.typingHandlers = [];
    }
    
    /**
     * Kết nối đến WebSocket server
     */
    connect() {
        try {
            this.ws = new WebSocket(this.wsUrl);
            
            this.ws.onopen = () => {
                console.log('✅ WebSocket connected');
                this.isConnected = true;
                
                // Xác thực
                this.send({
                    type: 'auth',
                    userId: this.userId,
                    username: this.username
                });
                
                // Clear reconnect timer
                if (this.reconnectTimer) {
                    clearTimeout(this.reconnectTimer);
                    this.reconnectTimer = null;
                }
            };
            
            this.ws.onmessage = (event) => {
                try {
                    const data = JSON.parse(event.data);
                    this.handleMessage(data);
                } catch (error) {
                    console.error('❌ Error parsing message:', error);
                }
            };
            
            this.ws.onclose = () => {
                console.log('👋 WebSocket disconnected');
                this.isConnected = false;
                this.reconnect();
            };
            
            this.ws.onerror = (error) => {
                console.error('❌ WebSocket error:', error);
            };
        } catch (error) {
            console.error('❌ Error connecting to WebSocket:', error);
            this.reconnect();
        }
    }
    
    /**
     * Ngắt kết nối
     */
    disconnect() {
        if (this.ws) {
            this.ws.close();
            this.ws = null;
        }
        
        if (this.reconnectTimer) {
            clearTimeout(this.reconnectTimer);
            this.reconnectTimer = null;
        }
    }
    
    /**
     * Tự động kết nối lại
     */
    reconnect() {
        if (this.reconnectTimer) return;
        
        console.log(`🔄 Reconnecting in ${this.reconnectInterval / 1000}s...`);
        
        this.reconnectTimer = setTimeout(() => {
            this.reconnectTimer = null;
            this.connect();
        }, this.reconnectInterval);
    }
    
    /**
     * Gửi tin nhắn
     */
    send(data) {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.send(JSON.stringify(data));
        } else {
            console.error('❌ WebSocket not connected');
        }
    }
    
    /**
     * Gửi tin nhắn chat
     */
    sendMessage(message) {
        this.send({
            type: 'message',
            message: message
        });
    }
    
    /**
     * Gửi typing indicator
     */
    sendTyping(isTyping) {
        this.send({
            type: 'typing',
            is_typing: isTyping
        });
    }
    
    /**
     * Đánh dấu đã đọc
     */
    markAsRead() {
        this.send({
            type: 'read'
        });
    }
    
    /**
     * Xử lý tin nhắn từ server
     */
    handleMessage(data) {
        switch (data.type) {
            case 'new_message':
                this.messageHandlers.forEach(handler => handler(data));
                break;
                
            case 'online_users':
                this.onlineUsersHandlers.forEach(handler => handler(data.users));
                break;
                
            case 'typing':
                this.typingHandlers.forEach(handler => handler(data));
                break;
                
            case 'error':
                console.error('❌ Server error:', data.message);
                break;
                
            default:
                console.log('❓ Unknown message type:', data.type);
        }
    }
    
    /**
     * Đăng ký handler cho tin nhắn mới
     */
    onMessage(handler) {
        this.messageHandlers.push(handler);
    }
    
    /**
     * Đăng ký handler cho danh sách online users
     */
    onOnlineUsers(handler) {
        this.onlineUsersHandlers.push(handler);
    }
    
    /**
     * Đăng ký handler cho typing indicator
     */
    onTyping(handler) {
        this.typingHandlers.push(handler);
    }
}

// Export cho sử dụng global
window.WebSocketChatClient = WebSocketChatClient;
