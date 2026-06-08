/**
 * WEBSOCKET CHAT SERVER - Real-time chat với WebSocket
 * Chạy: node websocket-chat-server.js
 */

const WebSocket = require('ws');
const mysql = require('mysql2/promise');

// Cấu hình
const WS_PORT = 8080;
const DB_CONFIG = {
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'longhiep_db',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
};

// Tạo connection pool
const pool = mysql.createPool(DB_CONFIG);

// Tạo WebSocket server
const wss = new WebSocket.Server({ port: WS_PORT });

// Lưu danh sách clients đang kết nối
const clients = new Map(); // userId => WebSocket

console.log(`✅ WebSocket Chat Server đang chạy trên port ${WS_PORT}`);

// Xử lý kết nối mới
wss.on('connection', async (ws, req) => {
    console.log('🔌 Client mới kết nối');
    
    let userId = null;
    let username = null;
    
    // Xử lý tin nhắn từ client
    ws.on('message', async (message) => {
        try {
            const data = JSON.parse(message);
            
            switch (data.type) {
                case 'auth':
                    // Xác thực user
                    userId = data.userId;
                    username = data.username;
                    clients.set(userId, ws);
                    
                    // Cập nhật online status
                    await updateOnlineStatus(userId, true);
                    
                    // Gửi danh sách users online
                    await broadcastOnlineUsers();
                    
                    console.log(`✅ User ${username} (ID: ${userId}) đã xác thực`);
                    break;
                    
                case 'message':
                    // Lưu tin nhắn vào database
                    const messageId = await saveMessage(userId, data.message);
                    
                    // Broadcast tin nhắn đến tất cả clients
                    const messageData = {
                        type: 'new_message',
                        id: messageId,
                        sender_id: userId,
                        sender_name: username,
                        message: data.message,
                        created_at: new Date().toISOString()
                    };
                    
                    broadcast(messageData);
                    console.log(`📨 Tin nhắn từ ${username}: ${data.message}`);
                    break;
                    
                case 'typing':
                    // Broadcast typing indicator
                    broadcast({
                        type: 'typing',
                        user_id: userId,
                        username: username,
                        is_typing: data.is_typing
                    }, userId);
                    break;
                    
                case 'read':
                    // Đánh dấu tin nhắn đã đọc
                    await markMessagesAsRead(userId);
                    break;
                    
                default:
                    console.log('❓ Unknown message type:', data.type);
            }
        } catch (error) {
            console.error('❌ Error processing message:', error);
            ws.send(JSON.stringify({
                type: 'error',
                message: 'Lỗi xử lý tin nhắn'
            }));
        }
    });
    
    // Xử lý ngắt kết nối
    ws.on('close', async () => {
        if (userId) {
            clients.delete(userId);
            await updateOnlineStatus(userId, false);
            await broadcastOnlineUsers();
            console.log(`👋 User ${username} (ID: ${userId}) đã ngắt kết nối`);
        }
    });
    
    // Xử lý lỗi
    ws.on('error', (error) => {
        console.error('❌ WebSocket error:', error);
    });
});

/**
 * Broadcast tin nhắn đến tất cả clients (trừ sender)
 */
function broadcast(data, excludeUserId = null) {
    const message = JSON.stringify(data);
    
    clients.forEach((client, userId) => {
        if (userId !== excludeUserId && client.readyState === WebSocket.OPEN) {
            client.send(message);
        }
    });
}

/**
 * Gửi tin nhắn đến một user cụ thể
 */
function sendToUser(userId, data) {
    const client = clients.get(userId);
    if (client && client.readyState === WebSocket.OPEN) {
        client.send(JSON.stringify(data));
    }
}

/**
 * Lưu tin nhắn vào database
 */
async function saveMessage(senderId, message) {
    try {
        const [result] = await pool.execute(
            'INSERT INTO chat_messages (sender_id, message, created_at) VALUES (?, ?, NOW())',
            [senderId, message]
        );
        return result.insertId;
    } catch (error) {
        console.error('❌ Error saving message:', error);
        throw error;
    }
}

/**
 * Cập nhật online status
 */
async function updateOnlineStatus(userId, isOnline) {
    try {
        await pool.execute(
            `INSERT INTO chat_online_status (user_id, is_online, last_seen) 
             VALUES (?, ?, NOW())
             ON DUPLICATE KEY UPDATE is_online = ?, last_seen = NOW()`,
            [userId, isOnline ? 1 : 0, isOnline ? 1 : 0]
        );
    } catch (error) {
        console.error('❌ Error updating online status:', error);
    }
}

/**
 * Đánh dấu tin nhắn đã đọc
 */
async function markMessagesAsRead(userId) {
    try {
        await pool.execute(
            'UPDATE chat_messages SET is_read = 1 WHERE sender_id != ? AND is_read = 0',
            [userId]
        );
    } catch (error) {
        console.error('❌ Error marking messages as read:', error);
    }
}

/**
 * Broadcast danh sách users online
 */
async function broadcastOnlineUsers() {
    try {
        const [rows] = await pool.execute(`
            SELECT u.id, u.username, u.full_name, cos.is_online, cos.last_seen
            FROM users u
            LEFT JOIN chat_online_status cos ON u.id = cos.user_id
            WHERE u.role IN ('admin', 'editor')
            ORDER BY cos.is_online DESC, u.full_name ASC
        `);
        
        broadcast({
            type: 'online_users',
            users: rows
        });
    } catch (error) {
        console.error('❌ Error broadcasting online users:', error);
    }
}

/**
 * Ping clients để giữ kết nối
 */
setInterval(() => {
    clients.forEach((client, userId) => {
        if (client.readyState === WebSocket.OPEN) {
            client.ping();
        } else {
            clients.delete(userId);
        }
    });
}, 30000); // 30 seconds

/**
 * Cleanup old messages (chạy mỗi ngày)
 */
setInterval(async () => {
    try {
        const [result] = await pool.execute(
            'DELETE FROM chat_messages WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)'
        );
        if (result.affectedRows > 0) {
            console.log(`🧹 Đã xóa ${result.affectedRows} tin nhắn cũ`);
        }
    } catch (error) {
        console.error('❌ Error cleaning old messages:', error);
    }
}, 24 * 60 * 60 * 1000); // 24 hours

// Xử lý tắt server
process.on('SIGINT', async () => {
    console.log('\n👋 Đang tắt WebSocket server...');
    
    // Cập nhật tất cả users thành offline
    try {
        await pool.execute('UPDATE chat_online_status SET is_online = 0, last_seen = NOW()');
    } catch (error) {
        console.error('❌ Error updating offline status:', error);
    }
    
    // Đóng tất cả connections
    wss.close(() => {
        console.log('✅ WebSocket server đã tắt');
        process.exit(0);
    });
});
