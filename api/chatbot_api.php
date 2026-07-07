<?php
// CLK SHOP - GEMINI CHATBOT API BACKEND

header('Content-Type: application/json');
$gemini_api_key = ''; 

// Đọc dữ liệu từ JS gửi lên
$input = json_decode(file_get_contents('php://input'), true);
$user_message = isset($input['message']) ? trim($input['message']) : '';

if (empty($user_message)) {
    echo json_encode(['status' => 'error', 'message' => 'Tin nhắn rỗng']);
    exit;
}

// Nếu chưa có API Key, trả về phản hồi giả lập (Simulated AI)
if (empty($gemini_api_key)) {
    // Phân tích từ khóa cơ bản để trả lời tạm thời khi chưa có Key
    $msg_lower = mb_strtolower($user_message, 'UTF-8');
    
    $reply = "👋 Dạ em chào Anh/Chị ạ! Em là nhân viên tư vấn của CLK Store. Hiện tại hệ thống AI bên em đang bảo trì nên tạm thời chưa thể tự động giải đáp các câu hỏi phức tạp.\n\n";
    $reply .= "Anh/Chị đang quan tâm đến:\n";
    $reply .= "📱 1. Điện thoại (iPhone, Samsung, Oppo, Xiaomi)\n";
    $reply .= "🎧 2. Phụ kiện (Tai nghe, Ốp lưng, Sạc)\n";
    $reply .= "🛠️ 3. Hỗ trợ đơn hàng/Bảo hành\n\n";
    $reply .= "Anh/Chị có thể để lại số điện thoại hoặc gọi hotline bên em 0358 *** *** để được nhân viên hỗ trợ trực tiếp và nhanh nhất nhé!";

    echo json_encode(['status' => 'success', 'reply' => $reply, 'mode' => 'fallback']);
    exit;
}

// 2. GỌI GEMINI API THẬT
$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $gemini_api_key;

// Prompt định hướng tính cách cho AI
$system_prompt = "Bạn là nhân viên tư vấn chăm sóc khách hàng chuyên nghiệp, nhiệt tình của 'CLK Store' - hệ thống chuyên bán điện thoại (iPhone, Samsung, Oppo, Xiaomi) và phụ kiện (Tai nghe, Ốp lưng, Sạc).
Quy tắc trả lời:
1. Luôn xưng hô là 'Dạ', 'Em' và gọi khách hàng là 'Anh/Chị' hoặc 'Quý khách'. Trả lời thân thiện, lễ phép và chuyên nghiệp như một nhân viên Sale thật.
2. Trả lời ngắn gọn, súc tích bằng tiếng Việt, có dùng emoji hợp lý.
3. Nếu khách hỏi giá: Báo giá tham khảo (iPhone X 82k, 12 Pro 122k, Samsung A23 122k, S6 Edge 220k, Tai nghe từ 50k, Ốp lưng từ 20k). Mời khách tham khảo chi tiết trên web.
4. Nếu khách hỏi chính sách: Dạ bên em bảo hành chính hãng 12 tháng, lỗi 1 đổi 1 trong 7 ngày nếu có lỗi từ nhà sản xuất ạ.
5. Nếu khách hỏi ngoài lề: Lịch sự từ chối khéo léo và xin phép chỉ hỗ trợ các thông tin liên quan đến sản phẩm của shop.
Khách hàng hỏi: " . $user_message;

$data = [
    'contents' => [
        [
            'parts' => [
                ['text' => $system_prompt]
            ]
        ]
    ],
    'generationConfig' => [
        'temperature' => 0.7,
        'maxOutputTokens' => 400
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $result = json_decode($response, true);
    $ai_reply = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
    
    // Format Markdown bold (**text**) to HTML (<strong>text</strong>)
    $ai_reply = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $ai_reply);
    
    echo json_encode(['status' => 'success', 'reply' => $ai_reply, 'mode' => 'ai']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Lỗi kết nối AI: ' . $http_code]);
}
