<?php
/**
 * ChatbotController V2
 * AI Chatbot thông minh với khả năng query database
 */
class ChatbotController extends Controller {
    
    private $useAI = false;
    private $geminiApiKey = '';
    private $openaiApiKey = '';
    private $useOpenAI = false;
    
    public function __construct() {
        // Load AI config
        $configFile = APP_PATH . '/config/ai_config.php';
        if (file_exists($configFile)) {
            require_once $configFile;
            
            // Check OpenAI first
            if (defined('USE_OPENAI') && USE_OPENAI && defined('OPENAI_API_KEY')) {
                $this->openaiApiKey = OPENAI_API_KEY;
                $this->useOpenAI = true;
                $this->useAI = defined('AI_ENABLED') ? AI_ENABLED : false;
            }
            // Fallback to Gemini
            elseif (defined('GEMINI_API_KEY') && GEMINI_API_KEY !== 'YOUR_GEMINI_API_KEY_HERE') {
                $this->geminiApiKey = GEMINI_API_KEY;
                $this->useAI = defined('AI_ENABLED') ? AI_ENABLED : false;
            }
        }
    }
    
    /**
     * API xử lý tin nhắn từ chatbot
     */
    public function sendMessage() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        
        $message = $_POST['message'] ?? '';
        $message = trim($message);
        
        if (empty($message)) {
            $this->json(['success' => false, 'message' => 'Tin nhắn không được để trống']);
        }
        
        // Thử dùng AI trước, fallback về auto-response
        if ($this->useAI) {
            // Check OpenAI first (dùng method mới với danh sách sản phẩm)
            if (defined('USE_OPENAI') && USE_OPENAI && defined('OPENAI_API_KEY')) {
                $response = $this->getOpenAIResponse($message);  // GỌI METHOD TRONG CLASS
                if ($response) {
                    error_log("Using OpenAI GPT-4 response (with smart context)");
                }
            }
            
            // Fallback to auto-response
            if (empty($response)) {
                $response = $this->getAutoResponse($message);
                error_log("AI failed, using auto-response");
            }
        } else {
            $response = $this->getAutoResponse($message);
            error_log("Using auto-response (AI disabled)");
        }
        
        $this->json([
            'success' => true,
            'response' => $response,
            'timestamp' => date('H:i')
        ]);
    }
    
    /**
     * Lấy câu trả lời từ OpenAI GPT với khả năng query database
     */
    private function getOpenAIResponse($message) {
        try {
            // Phân tích ý định của user (Intent Detection)
            $intent = $this->detectIntent($message);
            
            // Lấy dữ liệu động từ database theo ý định
            $contextData = $this->getContextData($intent, $message);
            
            // Build system prompt với toàn bộ context
            $systemPrompt = $this->buildSystemPrompt($contextData);
            
            // Debug logging
            error_log("ChatBot: Intent detected: {$intent['type']}");
            
            $url = 'https://api.openai.com/v1/chat/completions';
            
            // Few-shot learning
            $data = [
                'model' => defined('OPENAI_MODEL') ? OPENAI_MODEL : 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $message]
                ],
                'max_tokens' => 500,
                'temperature' => 0.3
            ];
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->openaiApiKey
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200 && $response) {
                $result = json_decode($response, true);
                if (isset($result['choices'][0]['message']['content'])) {
                    return trim($result['choices'][0]['message']['content']);
                }
            }
            
            error_log("OpenAI API Error - HTTP $httpCode");
            return false;
            
        } catch (Exception $e) {
            error_log('OpenAI Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Phát hiện ý định của user từ tin nhắn
     */
    private function detectIntent($message) {
        $message = mb_strtolower($this->removeAccents($message), 'UTF-8');
        
        // Tra cứu đơn hàng (hỗ trợ nhiều format: ORD, DH, ORDER)
        if (preg_match('/(tra cuu|kiem tra|xem don|don hang|ma don|order)/i', $message)) {
            // Tìm mã đơn: ORD + số, DH + số, ORDER + số
            if (preg_match('/(ORD|DH|ORDER)\d+/i', $message, $matches)) {
                return ['type' => 'order_tracking', 'order_code' => strtoupper($matches[0])];
            }
            return ['type' => 'order_info', 'data' => null];
        }
        
        // Gợi ý sản phẩm
        if (preg_match('/(goi y|de xuat|phu hop|nen chon|mua gi|tang|muon|tim)/i', $message)) {
            return ['type' => 'product_recommendation', 'query' => $message];
        }
        
        // Tin tức/Promotion (ưu tiên trước coupon)
        if (preg_match('/(tin tuc|bai viet|su kien|chuong trinh|khuyen mai|promotion|giam gia tu dong)/i', $message)) {
            return ['type' => 'news_promotion', 'query' => $message];
        }
        
        // Check coupon (mã nhập tay)
        if (preg_match('/(ma giam gia|coupon|voucher|ma code)/i', $message)) {
            return ['type' => 'coupon_check', 'query' => $message];
        }
        
        // Chính sách
        if (preg_match('/(giao hang|ship|van chuyen|thanh toan|doi tra|hoan tien|chinh sach)/i', $message)) {
            return ['type' => 'policy_info', 'query' => $message];
        }
        
        // Default
        return ['type' => 'general', 'query' => $message];
    }
    
    /**
     * Lấy dữ liệu context từ database theo intent
     */
    private function getContextData($intent, $message) {
        $data = [];
        
        switch ($intent['type']) {
            case 'order_tracking':
                if (!empty($intent['order_code'])) {
                    $orderModel = $this->model('Order');
                    $order = $orderModel->getOrderByCode($intent['order_code']);
                    
                    // Debug logging
                    error_log("ChatBot: Searching order: {$intent['order_code']}");
                    
                    if ($order) {
                        $data['order'] = $order;
                        $data['order_items'] = $orderModel->getOrderItems($order['id']);
                        error_log("ChatBot: Order found - ID: {$order['id']}, Total: {$order['total']}");
                    } else {
                        error_log("ChatBot: Order NOT FOUND - Code: {$intent['order_code']}");
                        $data['order_not_found'] = true;
                        $data['searched_code'] = $intent['order_code'];
                    }
                }
                break;
                
            case 'product_recommendation':
                $productModel = $this->model('Product');
                $products = $productModel->getAll(15);
                require_once APP_PATH . '/helpers/promotion_helper.php';
                $data['products'] = applyPromotionsToProducts($products);
                break;
                
            case 'coupon_check':
                $couponModel = $this->model('Coupon');
                $data['coupons'] = $couponModel->getAll(['is_active' => '1', 'valid_now' => true]);
                break;
                
            case 'news_promotion':
                $newsModel = $this->model('News');
                $data['news'] = $newsModel->getAll(1, 5, ['status' => 'published']);
                
                $promotionModel = $this->model('Promotion');
                $data['promotions'] = $promotionModel->getAll(['is_active' => 1, 'valid_only' => true]);
                break;
                
            default:
                $productModel = $this->model('Product');
                $products = $productModel->getAll(10);
                require_once APP_PATH . '/helpers/promotion_helper.php';
                $data['products'] = applyPromotionsToProducts($products);
                break;
        }
        
        $data['intent'] = $intent;
        return $data;
    }
    
    /**
     * Build system prompt với toàn bộ thông tin
     */
    private function buildSystemPrompt($contextData) {
        $prompt = "Bạn là AI của Flower Shop - shop hoa tươi Việt Nam.\n\n";
        
        $prompt .= "🏪 THÔNG TIN SHOP:\n";
        $prompt .= "• Tên: Flower Shop\n";
        $prompt .= "• Giờ: 8:00-22:00 hàng ngày\n";
        $prompt .= "• Địa chỉ: 123 ABC, Q1, HCM\n";
        $prompt .= "• Hotline: 1900 1234\n\n";
        
        $prompt .= "🚚 GIAO HÀNG:\n";
        $prompt .= "• Nội thành: 2-4h (30k, FREE từ 500k)\n";
        $prompt .= "• Tỉnh: 1-3 ngày\n\n";
        
        $prompt .= "💳 THANH TOÁN: COD, VNPay, Chuyển khoản\n\n";
        
        $prompt .= "🔄 ĐỔI TRẢ:\n";
        $prompt .= "• Đổi trong 24h nếu không đúng mô tả\n";
        $prompt .= "• Bảo hành hoa 3 ngày\n";
        $prompt .= "• Hoàn tiền nếu giao muộn >2h\n\n";
        
        $prompt .= "📦 CÁCH ĐẶT: Chọn hoa → Giỏ → Điền thông tin → Thanh toán → Nhận mã DH\n\n";
        
        $prompt .= "🌸 CHĂM SÓC HOA:\n";
        $prompt .= "• Thay nước 2 ngày/lần\n";
        $prompt .= "• Cắt chéo cuống\n";
        $prompt .= "• Tránh nắng trực tiếp\n";
        $prompt .= "• Nhiệt độ 18-22°C\n\n";
        
        // Thêm thông tin đơn hàng (nếu tra cứu)
        if (isset($contextData['order_not_found']) && $contextData['order_not_found']) {
            // Không tìm thấy đơn
            $prompt .= "⚠️ THÔNG BÁO:\n";
            $prompt .= "Mã đơn {$contextData['searched_code']} KHÔNG TÌM THẤY trong hệ thống.\n";
            $prompt .= "Vui lòng kiểm tra lại mã hoặc liên hệ hotline 1900 1234.\n\n";
        } elseif (isset($contextData['order'])) {
            // Tìm thấy đơn
            $order = $contextData['order'];
            $prompt .= "╔═══════════════════════════════════╗\n";
            $prompt .= "║    📦 THÔNG TIN ĐƠN HÀNG         ║\n";
            $prompt .= "╚═══════════════════════════════════╝\n";
            $prompt .= "🔖 Mã đơn: {$order['order_code']}\n";
            $prompt .= "👤 Khách: {$order['customer_name']}\n";
            $prompt .= "📞 SĐT: {$order['customer_phone']}\n";
            $prompt .= "📍 Địa chỉ: {$order['shipping_address']}\n";
            $prompt .= "💰 Tổng tiền: " . number_format($order['total']) . "đ\n";
            $prompt .= "📊 Trạng thái: " . $this->getOrderStatusText($order['order_status']) . "\n";
            $prompt .= "💳 Thanh toán: " . $this->getPaymentStatusText($order['payment_status']) . "\n";
            $prompt .= "📅 Ngày đặt: " . date('d/m/Y H:i', strtotime($order['created_at'])) . "\n";
            
            // Thêm sản phẩm trong đơn
            if (isset($contextData['order_items']) && !empty($contextData['order_items'])) {
                $prompt .= "\n📝 SẢN PHẨM TRONG ĐƠN:\n";
                foreach ($contextData['order_items'] as $item) {
                    $prompt .= "• {$item['product_name']} x{$item['quantity']} - " . number_format($item['subtotal']) . "đ\n";
                }
            }
            $prompt .= "\n";
        }
        
        // Thêm sản phẩm
        if (isset($contextData['products'])) {
            $prompt .= "📋 SẢN PHẨM:\n";
            foreach ($contextData['products'] as $i => $p) {
                $finalPrice = $p['final_price'] ?? $p['price'];
                $prompt .= ($i+1) . ". {$p['name']} - " . number_format($finalPrice) . "đ";
                
                if (isset($p['has_promotion']) && $p['has_promotion']) {
                    $discount = round((1 - $finalPrice / $p['price']) * 100);
                    $prompt .= " (Giảm {$discount}%)";
                }
                
                if (!empty($p['category_name'])) {
                    $prompt .= " [{$p['category_name']}]";
                }
                $prompt .= "\n";
            }
            $prompt .= "\n";
        }
        
        // Thêm PROMOTION (Khuyến mãi tự động) - TRƯỚC coupon
        if (isset($contextData['promotions']) && !empty($contextData['promotions'])) {
            $prompt .= "╔═══════════════════════════════════╗\n";
            $prompt .= "║  🎉 CHƯƠNG TRÌNH KHUYẾN MÃI      ║\n";
            $prompt .= "║  (Giảm giá TỰ ĐỘNG - Không cần mã) ║\n";
            $prompt .= "╚═══════════════════════════════════╝\n\n";
            
            foreach ($contextData['promotions'] as $promo) {
                $prompt .= "🎁 {$promo['name']}\n";
                
                // Loại khuyến mãi
                if ($promo['apply_to'] == 'all') {
                    $prompt .= "   📌 Áp dụng: TẤT CẢ sản phẩm\n";
                } elseif ($promo['apply_to'] == 'category') {
                    $prompt .= "   📌 Áp dụng: Danh mục {$promo['category_name']}\n";
                } elseif ($promo['apply_to'] == 'product') {
                    $prompt .= "   📌 Áp dụng: Sản phẩm cụ thể\n";
                }
                
                // Mức giảm
                if ($promo['discount_type'] == 'percentage') {
                    $prompt .= "   💰 Giảm: {$promo['discount_value']}%";
                    if (!empty($promo['max_discount']) && $promo['max_discount'] > 0) {
                        $prompt .= " (Tối đa " . number_format($promo['max_discount']) . "đ)";
                    }
                } else {
                    $prompt .= "   💰 Giảm: " . number_format($promo['discount_value']) . "đ";
                }
                $prompt .= "\n";
                
                // Thời gian
                $startDate = date('d/m/Y', strtotime($promo['start_date']));
                $endDate = date('d/m/Y', strtotime($promo['end_date']));
                $prompt .= "   ⏰ Thời gian: {$startDate} - {$endDate}\n";
                
                // Mô tả
                if (!empty($promo['description'])) {
                    $desc = strip_tags($promo['description']);
                    $desc = mb_substr($desc, 0, 100);
                    $prompt .= "   📝 {$desc}...\n";
                }
                
                $prompt .= "\n";
            }
        }
        
        // Thêm COUPON (Mã giảm giá nhập tay)
        if (isset($contextData['coupons'])) {
            $prompt .= "╔═══════════════════════════════════╗\n";
            $prompt .= "║  🎫 MÃ GIẢM GIÁ (COUPON)         ║\n";
            $prompt .= "║  (Nhập mã khi thanh toán)        ║\n";
            $prompt .= "╚═══════════════════════════════════╝\n\n";
            foreach ($contextData['coupons'] as $c) {
                $prompt .= "• Mã: {$c['code']}\n";
                if ($c['discount_type'] == 'percentage') {
                    $prompt .= "  Giảm: {$c['discount_value']}%";
                    if (!empty($c['max_discount']) && $c['max_discount'] > 0) {
                        $prompt .= " (Tối đa " . number_format($c['max_discount']) . "đ)";
                    }
                } else {
                    $prompt .= "  Giảm: " . number_format($c['discount_value']) . "đ";
                }
                $prompt .= "\n";
                $prompt .= "  Đơn tối thiểu: " . number_format($c['min_order_value']) . "đ\n";
                $prompt .= "  ⏰ Hạn dùng: " . date('d/m/Y', strtotime($c['valid_to'])) . "\n\n";
            }
        }
        
        // Quy tắc
        $prompt .= "⚠️ QUY TẮC TRẢ LỜI:\n";
        $prompt .= "✓ Gọi ĐÚNG TÊN + GIÁ sản phẩm từ danh sách\n";
        $prompt .= "✓ Gợi ý 2-3 sản phẩm CỤ THỂ khi khách hỏi\n";
        $prompt .= "✓ Phân biệt rõ:\n";
        $prompt .= "  • PROMOTION = Giảm giá TỰ ĐỘNG (không cần nhập mã)\n";
        $prompt .= "  • COUPON = Mã giảm giá (nhập khi thanh toán)\n";
        $prompt .= "✓ Khi khách hỏi 'khuyến mãi/chương trình' → trả lời PROMOTION\n";
        $prompt .= "✓ Khi khách hỏi 'mã giảm giá/coupon' → trả lời COUPON\n";
        $prompt .= "✓ Dùng emoji, thân thiện, ngắn gọn\n";
        $prompt .= "✗ KHÔNG trả lời chung chung\n";
        $prompt .= "✗ KHÔNG bịa giá\n\n";
        
        return $prompt;
    }
    
    private function getOrderStatusText($status) {
        $map = [
            'pending' => '⏳ Chờ xác nhận',
            'confirmed' => '✅ Đã xác nhận',
            'processing' => '📦 Đang chuẩn bị',
            'shipping' => '🚚 Đang giao',
            'delivered' => '✅ Đã giao',
            'cancelled' => '❌ Đã hủy'
        ];
        return $map[$status] ?? $status;
    }
    
    private function getPaymentStatusText($status) {
        $map = [
            'pending' => '⏳ Chưa thanh toán',
            'paid' => '✅ Đã thanh toán',
            'failed' => '❌ Thất bại',
            'refunded' => '🔄 Đã hoàn tiền'
        ];
        return $map[$status] ?? $status;
    }
    
    /**
     * Auto-response fallback
     */
    private function getAutoResponse($message) {
        $message = mb_strtolower($message, 'UTF-8');
        $normalized = $this->removeAccents($message);
        
        $responses = [
            [
                'keywords' => ['chao', 'hello', 'hi'],
                'replies' => ["Xin chào! 🌸 Tôi là trợ lý Flower Shop.\n\nTôi có thể giúp bạn:\n• Gợi ý sản phẩm\n• Tra cứu đơn hàng\n• Thông tin giao hàng\n• Mã giảm giá\n\nBạn cần gì ạ?"]
            ],
            [
                'keywords' => ['gio', 'mo cua'],
                'replies' => ['Shop mở cửa 8:00 - 22:00 hàng ngày! Đặt online 24/7 🕐']
            ],
            [
                'keywords' => ['giao hang', 'ship'],
                'replies' => ["🚚 GIAO HÀNG:\n• Nội thành: 2-4h\n• Tỉnh: 1-3 ngày\n• Phí: 30k (FREE từ 500k)"]
            ],
            [
                'keywords' => ['gia', 'bao nhieu'],
                'replies' => ["💐 Giá hoa:\n• Hoa bó: 150k-500k\n• Hoa giỏ: 300k-800k\n• Hoa hộp: 400k-1.2tr\n• Premium: 1tr-2tr\n\nXem chi tiết tại Sản phẩm!"]
            ],
            [
                'keywords' => ['thanh toan'],
                'replies' => ["💳 Thanh toán:\n• COD (Ship COD)\n• VNPay (Visa/ATM)\n• Chuyển khoản\n\nAn toàn 100%!"]
            ]
        ];
        
        foreach ($responses as $item) {
            foreach ($item['keywords'] as $keyword) {
                if (strpos($normalized, $this->removeAccents($keyword)) !== false) {
                    return $item['replies'][0];
                }
            }
        }
        
        return "Xin lỗi, tôi chưa hiểu câu hỏi. 😊\n\nBạn có thể hỏi về:\n• ⏰ Giờ mở cửa\n• 🚚 Giao hàng\n• 💰 Giá cả\n• 💳 Thanh toán\n• 🌸 Gợi ý hoa\n\nHoặc gọi 1900 1234!";
    }
    
    private function removeAccents($str) {
        $accents = [
            'à','á','ạ','ả','ã','â','ầ','ấ','ậ','ẩ','ẫ','ă','ằ','ắ','ặ','ẳ','ẵ',
            'è','é','ẹ','ẻ','ẽ','ê','ề','ế','ệ','ể','ễ',
            'ì','í','ị','ỉ','ĩ',
            'ò','ó','ọ','ỏ','õ','ô','ồ','ố','ộ','ổ','ỗ','ơ','ờ','ớ','ợ','ở','ỡ',
            'ù','ú','ụ','ủ','ũ','ư','ừ','ứ','ự','ử','ữ',
            'ỳ','ý','ỵ','ỷ','ỹ','đ'
        ];
        
        $noAccents = [
            'a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a',
            'e','e','e','e','e','e','e','e','e','e','e',
            'i','i','i','i','i',
            'o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o',
            'u','u','u','u','u','u','u','u','u','u','u',
            'y','y','y','y','y','d'
        ];
        
        return mb_strtolower(str_replace($accents, $noAccents, $str), 'UTF-8');
    }
    
    public function getSuggestions() {
        $this->json([
            'success' => true,
            'suggestions' => [
                'Giờ mở cửa?',
                'Giao hàng mất bao lâu?',
                'Có khuyến mãi gì?',
                'Gợi ý hoa sinh nhật',
                'Cách đặt hàng?'
            ]
        ]);
    }
}
