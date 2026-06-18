<?php
// ============================================================
// CHATBOT API - AI Backend for UBND Xã Long Hiệp
// ============================================================
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once 'config.php';
require_once 'chatbot-knowledge.php';

// ============================================================
// CONFIG - Hướng dẫn lấy API key MIỄN PHÍ:
// 1. Truy cập: https://aistudio.google.com/apikey
// 2. Đăng nhập tài khoản Google
// 3. Bấm "Create API Key" để lấy key miễn phí
// 4. Dán key vào bên dưới (giữa hai dấu ')
// ============================================================
define('AI_PROVIDER', 'gemini'); // 'openai' or 'gemini'
define('OPENAI_API_KEY', ''); // Add your OpenAI API key
define('GEMINI_API_KEY', ''); // <-- DÁN API KEY VÀO ĐÂY (MIỄN PHÍ)
define('MAX_CONTEXT_MESSAGES', 10);

// ============================================================
// Handle request
// ============================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? '');

if (empty($message)) {
    echo json_encode(['error' => 'Message is required']);
    exit();
}

// ============================================================
// 1. Try local knowledge base first
// ============================================================
$localAnswer = chatbotFindAnswer($message);
if ($localAnswer) {
    echo json_encode([
        'success' => true,
        'response' => $localAnswer,
        'source' => 'local',
        'suggestions' => getSuggestions($message)
    ]);
    exit();
}

// ============================================================
// 2. Try AI API if available
// ============================================================
$aiAnswer = getAIResponse($message);
if ($aiAnswer) {
    echo json_encode([
        'success' => true,
        'response' => $aiAnswer,
        'source' => 'ai',
        'suggestions' => getSuggestions($message)
    ]);
    exit();
}

// ============================================================
// 3. Default response
// ============================================================
echo json_encode([
    'success' => true,
    'response' => 'Xin lỗi, tôi chưa có thông tin về vấn đề này. Bạn có thể thử hỏi lại với từ khóa khác hoặc liên hệ trực tiếp UBND xã Long Hiệp:\n\n📍 Địa chỉ: xã Long Hiệp, tỉnh Vĩnh Long\n📞 ĐT: (0270) 3.856.417\n📧 Email: ubnd.longhiep@vinhlong.gov.vn\n\nHoặc thử hỏi về: Thủ tục hành chính, Lịch tiếp dân, Giờ làm việc, Liên hệ, Phòng ban',
    'source' => 'default',
    'suggestions' => ['Thủ tục hành chính', 'Lịch tiếp dân', 'Liên hệ', 'Giờ làm việc', 'Phòng ban', 'Địa chỉ']
]);
exit();

// ============================================================
// AI API Functions
// ============================================================
function getAIResponse($message) {
    $provider = AI_PROVIDER;
    
    if ($provider === 'openai' && !empty(OPENAI_API_KEY)) {
        return callOpenAI($message);
    }
    
    if ($provider === 'gemini' && !empty(GEMINI_API_KEY)) {
        return callGemini($message);
    }
    
    return null;
}

function callOpenAI($message) {
    $apiKey = OPENAI_API_KEY;
    
    $systemPrompt = getSystemPrompt();
    
    $data = [
        'model' => 'gpt-3.5-turbo',
        'messages' => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $message]
        ],
        'max_tokens' => 500,
        'temperature' => 0.7
    ];
    
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ],
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_TIMEOUT => 15
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $result = json_decode($response, true);
        if (isset($result['choices'][0]['message']['content'])) {
            return $result['choices'][0]['message']['content'];
        }
    }
    
    return null;
}

function callGemini($message) {
    $apiKey = GEMINI_API_KEY;
    
    $systemPrompt = getSystemPrompt();
    
    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $systemPrompt . "\n\nCâu hỏi của người dùng: " . $message]
                ]
            ]
        ],
        'generationConfig' => [
            'maxOutputTokens' => 500,
            'temperature' => 0.7
        ]
    ];
    
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey;
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json'
        ],
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_TIMEOUT => 15
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $result = json_decode($response, true);
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return $result['candidates'][0]['content']['parts'][0]['text'];
        }
    }
    
    return null;
}

function getSystemPrompt() {
    return 'Bạn là trợ lý ảo của Ủy ban Nhân dân xã Long Hiệp, tỉnh Vĩnh Long, Việt Nam.

NHIỆM VỤ: Hỗ trợ người dân tìm thông tin về thủ tục hành chính, lịch tiếp dân, giờ làm việc, liên hệ, và các dịch vụ của UBND xã.

THÔNG TIN QUAN TRỌNG:
- Địa chỉ: xã Long Hiệp, tỉnh Vĩnh Long
- ĐT: (0270) 3.856.417
- Email: ubnd.longhiep@vinhlong.gov.vn
- Website: https://longhiep.vinhlong.gov.vn

GIỜ LÀM VIỆC: Thứ 2-6: 7:30-11:30, 13:30-17:00. Thứ 7: 7:30-11:30. Chủ nhật: Nghỉ.

LỊCH TIẾP DÂN: Thứ 2 và Thứ 5 hàng tuần, 8:00-11:00 và 13:30-17:00.

PHÒNG BAN:
1. Ủy ban Nhân dân Xã - Chủ tịch: Nguyễn Khánh Hòa (0934.032.959)
2. Văn phòng HĐND và UBND - Chánh VP: Nguyễn Trọng Thủy (0931.060.339)
3. Phòng Kinh tế - Trưởng phòng: Kim Bảy Ly (0944.942.121)
4. Phòng Văn hóa - Xã hội - Trưởng phòng: Thạch Thanh Mỹ (0343.791.397)

QUY TẮC TRẢ LỜI:
- Trả lời bằng tiếng Việt, ngắn gọn, dễ hiểu
- Nếu không chắc chắn, khuyên người dân liên hệ trực tiếp UBND xã
- Luôn lịch sự và thân thiện
- Nếu câu hỏi không liên quan đến UBND xã, trả lời: "Câu hỏi này không nằm trong phạm vi hỗ trợ của tôi. Vui lòng liên hệ UBND xã để được hỗ trợ thêm."';
}

function getSuggestions($message) {
    $suggestions = [
        'Thủ tục hành chính',
        'Lịch tiếp dân',
        'Liên hệ',
        'Giờ làm việc',
        'Phòng ban',
        'Địa chỉ'
    ];
    
    $lower = mb_strtolower($message, 'UTF-8');
    
    if (mb_strpos($lower, 'thủ tục', 0, 'UTF-8') !== false || mb_strpos($lower, 'giấy tờ', 0, 'UTF-8') !== false) {
        return ['Lịch tiếp dân', 'Liên hệ', 'Giờ làm việc'];
    }
    
    if (mb_strpos($lower, 'liên hệ', 0, 'UTF-8') !== false || mb_strpos($lower, 'điện thoại', 0, 'UTF-8') !== false) {
        return ['Giờ làm việc', 'Địa chỉ', 'Phòng ban'];
    }
    
    return array_slice($suggestions, 0, 4);
}
