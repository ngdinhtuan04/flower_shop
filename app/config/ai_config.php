<?php
/**
 * Cấu hình AI Chatbot
 * Hướng dẫn lấy API Key miễn phí
 */

// ===========================================
// GOOGLE GEMINI AI (MIỄN PHÍ - KHUYÊN DÙNG)
// ===========================================

// Bước 1: Truy cập https://makersuite.google.com/app/apikey
// Bước 2: Đăng nhập bằng Google Account
// Bước 3: Click "Create API Key" 
// Bước 4: Copy API Key và paste vào đây

define('GEMINI_API_KEY', 'AIzaSyA7yVfS8eIz2Y6xmPd_In4Kw-eSKq-lbO8');
define('GEMINI_MODEL', 'gemini-pro'); // Model miễn phí

// ===========================================
// OPENAI GPT (TRẢ PHÍ - THÔNG MINH NHẤT)
// ===========================================

define('OPENAI_API_KEY', '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');
define('OPENAI_MODEL', 'gpt-4o-mini'); // Model rẻ: $0.15/1M tokens
define('USE_OPENAI', true); // Dùng OpenAI

// ===========================================
// CẤU HÌNH CHATBOT
// ===========================================

define('AI_ENABLED', true); // Bật/Tắt AI (false = dùng auto-response)
define('AI_TIMEOUT', 10); // Timeout 10 giây
define('AI_FALLBACK', true); // Nếu AI lỗi, dùng auto-response

// ===========================================
// GIỚI HẠN SỬ DỤNG (Rate Limiting)
// ===========================================

define('AI_MAX_REQUESTS_PER_MINUTE', 20); // Tối đa 20 requests/phút
define('AI_MAX_MESSAGE_LENGTH', 500); // Tối đa 500 ký tự/tin nhắn

// ===========================================
// CONTEXT CHO AI
// ===========================================

define('AI_CONTEXT', [
    'shop_name' => 'Flower Shop',
    'business' => 'Cửa hàng hoa tươi & quà tặng',
    'location' => '123 Đường ABC, Quận 1, TP.HCM',
    'phone' => '1900 1234',
    'email' => 'contact@flowershop.vn',
    'hours' => '8:00 - 22:00 hàng ngày',
    
    'products' => [
        'Hoa bó' => '150.000đ - 500.000đ',
        'Hoa giỏ' => '300.000đ - 800.000đ',
        'Hoa hộp' => '400.000đ - 1.200.000đ',
        'Premium' => '1.000.000đ - 2.000.000đ'
    ],
    
    'shipping' => [
        'Nội thành' => '2-4 giờ, phí 30.000đ',
        'Tỉnh thành' => '1-3 ngày',
        'Miễn phí' => 'Đơn từ 500.000đ'
    ],
    
    'payment' => ['COD', 'VNPay', 'Chuyển khoản'],
    
    'promotions' => [
        'Giảm 20% đơn đầu',
        'Miễn ship đơn 500k+',
        'Tặng thiệp & ribbon'
    ]
]);

// ===========================================
// HƯỚNG DẪN SỬ DỤNG
// ===========================================

/*
BƯỚC 1: LẤY GOOGLE GEMINI API KEY (MIỄN PHÍ)
---------------------------------------------
1. Truy cập: https://makersuite.google.com/app/apikey
2. Đăng nhập Google Account
3. Click "Create API Key in new project"
4. Copy API Key (dạng: AIzaSyBFZ8v3h7LX...)
5. Paste vào GEMINI_API_KEY ở trên

BƯỚC 2: BẬT AI TRONG CHATBOT
---------------------------------------------
1. Mở file: app/controllers/ChatbotController.php
2. Tìm dòng: private $useAI = false;
3. Đổi thành: private $useAI = true;
4. Thay $geminiApiKey bằng API key của bạn

BƯỚC 3: TEST CHATBOT
---------------------------------------------
1. Refresh trang web
2. Mở chatbot
3. Nhập câu hỏi bất kỳ
4. AI sẽ trả lời thông minh dựa trên context shop

TÍNH NĂNG AI:
---------------------------------------------
✅ Hiểu ngữ cảnh và trả lời linh hoạt
✅ Nhớ thông tin shop (giá, địa chỉ, giờ mở cửa...)
✅ Tư vấn sản phẩm phù hợp với nhu cầu
✅ Trả lời bằng tiếng Việt tự nhiên
✅ Tự động fallback về auto-response nếu AI lỗi

GIỚI HẠN API MIỄN PHÍ:
---------------------------------------------
- Gemini Pro: 60 requests/phút
- Hoàn toàn FREE, không cần thẻ tín dụng
- Phù hợp cho shop nhỏ & vừa

NÂN CẤP PRO (NẾU CẦN):
---------------------------------------------
- OpenAI GPT-4: $0.03/1K tokens (thông minh nhất)
- Claude AI: $0.015/1K tokens (cân bằng)
- Gemini Advanced: Tính năng nâng cao

*/
