<?php
/**
 * OpenAI Helper for Chatbot
 * Xử lý OpenAI GPT API
 */

function getOpenAIResponse($message, $apiKey) {
    try {
        // Context về shop
        $systemPrompt = "Bạn là trợ lý AI của Flower Shop - cửa hàng hoa tươi tại Việt Nam.\n\n";
        $systemPrompt .= "THÔNG TIN SHOP:\n";
        $systemPrompt .= "- Tên: Flower Shop\n";
        $systemPrompt .= "- Giờ mở cửa: 8:00 - 22:00 hàng ngày\n";
        $systemPrompt .= "- Địa chỉ: 123 Đường ABC, Quận 1, TP.HCM\n";
        $systemPrompt .= "- Hotline: 1900 1234\n";
        $systemPrompt .= "- Email: contact@flowershop.vn\n\n";
        $systemPrompt .= "SẢN PHẨM & GIÁ:\n";
        $systemPrompt .= "- Hoa bó: 150.000đ - 500.000đ\n";
        $systemPrompt .= "- Hoa giỏ: 300.000đ - 800.000đ\n";
        $systemPrompt .= "- Hoa hộp: 400.000đ - 1.200.000đ\n";
        $systemPrompt .= "- Premium: 1.000.000đ - 2.000.000đ\n\n";
        $systemPrompt .= "GIAO HÀNG: Nội thành 2-4h, tỉnh 1-3 ngày, miễn phí đơn từ 500k\n";
        $systemPrompt .= "THANH TOÁN: COD, VNPay, Chuyển khoản\n\n";
        $systemPrompt .= "Trả lời ngắn gọn (3-5 câu), thân thiện, dùng emoji phù hợp. Nếu không biết, gợi ý liên hệ hotline.";
        
        $url = 'https://api.openai.com/v1/chat/completions';
        
        $data = [
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $message]
            ],
            'max_tokens' => 300,
            'temperature' => 0.7
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $response) {
            $result = json_decode($response, true);
            if (isset($result['choices'][0]['message']['content'])) {
                return trim($result['choices'][0]['message']['content']);
            }
        }
        
        error_log("OpenAI API Error - HTTP $httpCode: $response");
        return false;
        
    } catch (Exception $e) {
        error_log('OpenAI Error: ' . $e->getMessage());
        return false;
    }
}
