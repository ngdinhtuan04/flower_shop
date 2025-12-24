<?php
/**
 * Helper functions cho Promotion System
 */

/**
 * Lấy giá sau khuyến mãi cho một sản phẩm
 * 
 * @param int $productId ID sản phẩm
 * @param float $originalPrice Giá gốc
 * @param int|null $categoryId ID danh mục (optional)
 * @return array Thông tin giá và khuyến mãi
 */
function getPromotionPrice($productId, $originalPrice, $categoryId = null) {
    static $promotionModel = null;
    
    if ($promotionModel === null) {
        require_once APP_PATH . '/models/Promotion.php';
        $promotionModel = new Promotion();
    }
    
    return $promotionModel->calculateDiscountedPrice($productId, $originalPrice, $categoryId);
}

/**
 * Format giá hiển thị với khuyến mãi
 * 
 * @param array $priceInfo Thông tin giá từ getPromotionPrice()
 * @param bool $showOriginal Hiển thị giá gốc hay không
 * @return string HTML formatted price
 */
function formatPromotionPrice($priceInfo, $showOriginal = true) {
    $html = '';
    
    if ($priceInfo['discount_amount'] > 0) {
        $html .= '<span class="price-current">' . number_format($priceInfo['discounted_price']) . 'đ</span>';
        
        if ($showOriginal) {
            $html .= ' <span class="price-old">' . number_format($priceInfo['original_price']) . 'đ</span>';
            $html .= ' <span class="discount-badge">-' . $priceInfo['discount_percentage'] . '%</span>';
        }
    } else {
        $html .= '<span class="price-current">' . number_format($priceInfo['original_price']) . 'đ</span>';
    }
    
    return $html;
}

/**
 * Kiểm tra xem sản phẩm có đang được khuyến mãi không
 * 
 * @param int $productId ID sản phẩm
 * @param int|null $categoryId ID danh mục
 * @return bool
 */
function hasPromotion($productId, $categoryId = null) {
    static $promotionModel = null;
    
    if ($promotionModel === null) {
        require_once APP_PATH . '/models/Promotion.php';
        $promotionModel = new Promotion();
    }
    
    $promotion = $promotionModel->getActivePromotionsForProduct($productId, $categoryId);
    return $promotion !== null;
}

/**
 * Lấy thông tin khuyến mãi đang áp dụng cho sản phẩm
 * 
 * @param int $productId ID sản phẩm
 * @param int|null $categoryId ID danh mục
 * @return array|null Thông tin khuyến mãi hoặc null
 */
function getActivePromotion($productId, $categoryId = null) {
    static $promotionModel = null;
    
    if ($promotionModel === null) {
        require_once APP_PATH . '/models/Promotion.php';
        $promotionModel = new Promotion();
    }
    
    return $promotionModel->getActivePromotionsForProduct($productId, $categoryId);
}

/**
 * Áp dụng khuyến mãi cho một mảng sản phẩm
 * 
 * @param array $products Mảng sản phẩm
 * @return array Mảng sản phẩm đã được thêm thông tin khuyến mãi
 */
function applyPromotionsToProducts($products) {
    static $promotionModel = null;
    
    if ($promotionModel === null) {
        require_once APP_PATH . '/models/Promotion.php';
        $promotionModel = new Promotion();
    }
    
    foreach ($products as &$product) {
        $priceInfo = $promotionModel->calculateDiscountedPrice(
            $product['id'],
            $product['price'],
            $product['category_id'] ?? null
        );
        
        $product['promotion_info'] = $priceInfo;
        $product['final_price'] = $priceInfo['discounted_price'];
        $product['has_promotion'] = $priceInfo['discount_amount'] > 0;
    }
    
    return $products;
}

/**
 * Tính tổng tiền đơn hàng với khuyến mãi
 * 
 * @param array $items Mảng items [{product_id, quantity, price, category_id}, ...]
 * @return array ['subtotal', 'discount', 'total']
 */
function calculateOrderWithPromotions($items) {
    static $promotionModel = null;
    
    if ($promotionModel === null) {
        require_once APP_PATH . '/models/Promotion.php';
        $promotionModel = new Promotion();
    }
    
    $subtotal = 0;
    $discount = 0;
    
    foreach ($items as $item) {
        $originalPrice = $item['price'];
        $quantity = $item['quantity'];
        
        $priceInfo = $promotionModel->calculateDiscountedPrice(
            $item['product_id'],
            $originalPrice,
            $item['category_id'] ?? null
        );
        
        $subtotal += $originalPrice * $quantity;
        $discount += $priceInfo['discount_amount'] * $quantity;
    }
    
    $total = $subtotal - $discount;
    
    return [
        'subtotal' => $subtotal,
        'discount' => $discount,
        'total' => max(0, $total)
    ];
}

/**
 * Lấy danh sách khuyến mãi đang hoạt động
 * 
 * @return array Danh sách khuyến mãi
 */
function getActivePromotions() {
    static $promotionModel = null;
    
    if ($promotionModel === null) {
        require_once APP_PATH . '/models/Promotion.php';
        $promotionModel = new Promotion();
    }
    
    return $promotionModel->getAll([
        'is_active' => 1,
        'valid_only' => true
    ]);
}

/**
 * Format số tiền tiết kiệm
 * 
 * @param float $amount Số tiền
 * @return string Chuỗi đã format
 */
function formatSavings($amount) {
    if ($amount <= 0) {
        return '';
    }
    
    return 'Tiết kiệm ' . number_format($amount) . 'đ';
}

/**
 * Lấy badge HTML cho discount percentage
 * 
 * @param float $percentage Phần trăm giảm
 * @return string HTML badge
 */
function getDiscountBadge($percentage) {
    if ($percentage <= 0) {
        return '';
    }
    
    return '<span class="discount-badge">-' . round($percentage) . '%</span>';
}

/**
 * Kiểm tra xem khuyến mãi có còn hiệu lực không
 * 
 * @param array $promotion Thông tin khuyến mãi
 * @return bool
 */
function isPromotionValid($promotion) {
    if (!$promotion['is_active']) {
        return false;
    }
    
    $now = time();
    $start = strtotime($promotion['start_date']);
    $end = strtotime($promotion['end_date']);
    
    if ($now < $start || $now > $end) {
        return false;
    }
    
    if ($promotion['usage_limit'] && $promotion['used_count'] >= $promotion['usage_limit']) {
        return false;
    }
    
    return true;
}

/**
 * Lấy status text cho khuyến mãi
 * 
 * @param array $promotion Thông tin khuyến mãi
 * @return string Status text
 */
function getPromotionStatus($promotion) {
    if (!$promotion['is_active']) {
        return 'Đã tắt';
    }
    
    $now = time();
    $start = strtotime($promotion['start_date']);
    $end = strtotime($promotion['end_date']);
    
    if ($now < $start) {
        return 'Sắp diễn ra';
    } elseif ($now > $end) {
        return 'Đã hết hạn';
    } else {
        return 'Đang diễn ra';
    }
}

/**
 * Lấy số ngày còn lại của khuyến mãi
 * 
 * @param array $promotion Thông tin khuyến mãi
 * @return int Số ngày còn lại (0 nếu đã hết hạn)
 */
function getDaysRemaining($promotion) {
    $now = time();
    $end = strtotime($promotion['end_date']);
    
    if ($end <= $now) {
        return 0;
    }
    
    return ceil(($end - $now) / 86400);
}
