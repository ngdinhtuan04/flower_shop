<?php
/**
 * Coupon Model - Quản lý mã giảm giá
 */
class Coupon {
    protected $table = 'coupons';
    protected $db;
    
    public function __construct() {
        $this->db = DB::getInstance();
    }
    
    /**
     * Lấy tất cả mã giảm giá với bộ lọc
     */
    public function getAll($filters = []) {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        
        // Filter by status
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $statusValue = $filters['is_active'] == '1' ? 'active' : 'inactive';
            $sql .= " AND status = ?";
            $params[] = $statusValue;
        }
        
        // Filter by date validity
        if (isset($filters['valid_now']) && $filters['valid_now']) {
            $sql .= " AND valid_from <= NOW() AND valid_to >= NOW()";
        }
        
        // Search by code or description
        if (!empty($filters['search'])) {
            $sql .= " AND (code LIKE ? OR description LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Lấy coupon theo code
     */
    public function getByCode($code) {
        $sql = "SELECT * FROM {$this->table} WHERE code = ? LIMIT 1";
        return $this->db->fetchOne($sql, [$code]);
    }
    
    /**
     * Validate và lấy thông tin coupon
     */
    public function validateCoupon($code, $userId = null, $orderValue = 0) {
        $coupon = $this->getByCode($code);
        
        if (!$coupon) {
            return ['valid' => false, 'message' => 'Mã giảm giá không tồn tại'];
        }
        
        // Check if active
        if ($coupon['status'] !== 'active') {
            return ['valid' => false, 'message' => 'Mã giảm giá đã bị vô hiệu hóa'];
        }
        
        // Check date validity
        $now = date('Y-m-d H:i:s');
        if ($now < $coupon['valid_from']) {
            return ['valid' => false, 'message' => 'Mã giảm giá chưa có hiệu lực'];
        }
        if ($now > $coupon['valid_to']) {
            return ['valid' => false, 'message' => 'Mã giảm giá đã hết hạn'];
        }
        
        // Check usage limit
        if ($coupon['usage_limit'] !== null && $coupon['used_count'] >= $coupon['usage_limit']) {
            return ['valid' => false, 'message' => 'Mã giảm giá đã hết lượt sử dụng'];
        }
        
        // Check minimum order value
        if ($orderValue < $coupon['min_order_value']) {
            $minValue = number_format($coupon['min_order_value'], 0, ',', '.');
            return ['valid' => false, 'message' => "Đơn hàng tối thiểu {$minValue}đ để sử dụng mã này"];
        }
        
        // Check user usage limit - note: usage_per_user column doesn't exist in DB
        // Skipping this validation for now
        
        return ['valid' => true, 'coupon' => $coupon];
    }
    
    /**
     * Tính số tiền giảm
     * @param array $coupon Thông tin coupon
     * @param float $subtotal Tổng tiền sản phẩm
     * @param float $shippingFee Phí vận chuyển
     * @return array ['product_discount' => float, 'shipping_discount' => float, 'total_discount' => float]
     */
    public function calculateDiscount($coupon, $subtotal, $shippingFee = 0) {
        $applyTo = $coupon['apply_to'] ?? 'product';
        $productDiscount = 0;
        $shippingDiscount = 0;
        
        // Tính giảm giá theo loại áp dụng
        if ($applyTo === 'product') {
            // Chỉ giảm giá sản phẩm
            $productDiscount = $this->calculateDiscountValue($coupon, $subtotal);
        } elseif ($applyTo === 'shipping') {
            // Chỉ giảm phí ship
            $shippingDiscount = $this->calculateDiscountValue($coupon, $shippingFee);
        } elseif ($applyTo === 'all') {
            // Giảm cả sản phẩm và ship
            $totalValue = $subtotal + $shippingFee;
            $totalDiscount = $this->calculateDiscountValue($coupon, $totalValue);
            
            // Phân bổ giảm giá theo tỷ lệ
            if ($totalValue > 0) {
                $productDiscount = ($totalDiscount * $subtotal) / $totalValue;
                $shippingDiscount = ($totalDiscount * $shippingFee) / $totalValue;
            }
        }
        
        return [
            'product_discount' => round($productDiscount, 2),
            'shipping_discount' => round($shippingDiscount, 2),
            'total_discount' => round($productDiscount + $shippingDiscount, 2)
        ];
    }
    
    /**
     * Tính giá trị giảm cho một giá trị cụ thể
     */
    private function calculateDiscountValue($coupon, $value) {
        if ($value <= 0) return 0;
        
        // Handle both 'percentage' and 'percent' for discount_type
        if ($coupon['discount_type'] === 'percentage' || $coupon['discount_type'] === 'percent') {
            $discount = ($value * $coupon['discount_value']) / 100;
            
            // Apply max discount if set
            if ($coupon['max_discount'] !== null && $discount > $coupon['max_discount']) {
                $discount = $coupon['max_discount'];
            }
        } else {
            // Fixed discount
            $discount = $coupon['discount_value'];
        }
        
        // Discount cannot exceed value
        if ($discount > $value) {
            $discount = $value;
        }
        
        return $discount;
    }
    
    /**
     * Đếm số lần user đã dùng coupon
     */
    public function getUserUsageCount($couponId, $userId) {
        $sql = "SELECT COUNT(*) as count FROM coupon_usage WHERE coupon_id = ? AND user_id = ?";
        $result = $this->db->fetchOne($sql, [$couponId, $userId]);
        return $result['count'] ?? 0;
    }
    
    /**
     * Ghi nhận việc sử dụng coupon
     */
    public function recordUsage($couponId, $userId, $orderId, $discountAmount) {
        // Insert usage record
        $sql = "INSERT INTO coupon_usage (coupon_id, user_id, order_id, discount_amount) 
                VALUES (?, ?, ?, ?)";
        $this->db->insert($sql, [$couponId, $userId, $orderId, $discountAmount]);
        
        // Increment used_count
        $sql = "UPDATE {$this->table} SET used_count = used_count + 1 WHERE id = ?";
        $this->db->execute($sql, [$couponId]);
        
        return true;
    }
    
    /**
     * Tạo mã giảm giá mới
     */
    public function create($data) {
        // Map is_active to status
        $status = (isset($data['is_active']) && $data['is_active']) ? 'active' : 'inactive';
        
        // Map discount_type: 'percentage' to 'percent'
        $discountType = ($data['discount_type'] === 'percentage') ? 'percent' : $data['discount_type'];
        
        $sql = "INSERT INTO {$this->table} 
                (code, description, apply_to, discount_type, discount_value, min_order_value, 
                 max_discount, usage_limit, valid_from, valid_to, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        return $this->db->insert($sql, [
            strtoupper($data['code']),
            $data['description'] ?? null,
            $data['apply_to'] ?? 'product',
            $discountType,
            $data['discount_value'],
            $data['min_order_value'] ?? 0,
            $data['max_discount'] ?? null,
            $data['usage_limit'] ?? 1,
            $data['start_date'],
            $data['end_date'],
            $status
        ]);
    }
    
    /**
     * Cập nhật mã giảm giá
     */
    public function update($id, $data) {
        // Map is_active to status
        $status = (isset($data['is_active']) && $data['is_active']) ? 'active' : 'inactive';
        
        // Map discount_type: 'percentage' to 'percent'
        $discountType = ($data['discount_type'] === 'percentage') ? 'percent' : $data['discount_type'];
        
        $sql = "UPDATE {$this->table} SET 
                code = ?, description = ?, apply_to = ?, discount_type = ?, discount_value = ?, 
                min_order_value = ?, max_discount = ?, usage_limit = ?, 
                valid_from = ?, valid_to = ?, status = ?
                WHERE id = ?";
        
        return $this->db->execute($sql, [
            strtoupper($data['code']),
            $data['description'] ?? null,
            $data['apply_to'] ?? 'product',
            $discountType,
            $data['discount_value'],
            $data['min_order_value'] ?? 0,
            $data['max_discount'] ?? null,
            $data['usage_limit'] ?? 1,
            $data['start_date'],
            $data['end_date'],
            $status,
            $id
        ]);
    }
    
    /**
     * Bật/tắt mã giảm giá
     */
    public function toggleActive($id) {
        $sql = "UPDATE {$this->table} SET status = IF(status = 'active', 'inactive', 'active') WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    /**
     * Lấy thống kê
     */
    public function getStatistics() {
        $stats = [
            'total' => 0,
            'active' => 0,
            'expired' => 0,
            'used' => 0,
            'total_discount' => 0
        ];
        
        // Total coupons
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $result = $this->db->fetchOne($sql);
        $stats['total'] = isset($result['total']) ? $result['total'] : 0;
        
        // Active coupons (valid now)
        $sql = "SELECT COUNT(*) as active FROM {$this->table} 
                WHERE status = 'active' AND valid_from <= NOW() AND valid_to >= NOW()";
        $result = $this->db->fetchOne($sql);
        $stats['active'] = isset($result['active']) ? $result['active'] : 0;
        
        // Expired coupons
        $sql = "SELECT COUNT(*) as expired FROM {$this->table} WHERE valid_to < NOW()";
        $result = $this->db->fetchOne($sql);
        $stats['expired'] = isset($result['expired']) ? $result['expired'] : 0;
        
        // Total usage count
        $sql = "SELECT SUM(used_count) as used FROM {$this->table}";
        $result = $this->db->fetchOne($sql);
        $stats['used'] = isset($result['used']) && $result['used'] !== null ? $result['used'] : 0;
        
        // Total discount amount
        $sql = "SELECT SUM(discount_amount) as total_discount FROM coupon_usage";
        $result = $this->db->fetchOne($sql);
        $stats['total_discount'] = isset($result['total_discount']) && $result['total_discount'] !== null ? $result['total_discount'] : 0;
        
        return $stats;
    }
    
    /**
     * Lấy lịch sử sử dụng của một coupon
     */
    public function getUsageHistory($couponId, $limit = 50) {
        $sql = "SELECT cu.*, u.full_name, u.email, cu.used_at as order_date
                FROM coupon_usage cu
                JOIN users u ON cu.user_id = u.id
                WHERE cu.coupon_id = ?
                ORDER BY cu.used_at DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$couponId, $limit]);
    }
    
    /**
     * Lấy coupon theo ID với thông tin chi tiết
     */
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Xóa mã giảm giá
     */
    public function delete($id) {
        // Check if coupon has been used
        $sql = "SELECT COUNT(*) as count FROM coupon_usage WHERE coupon_id = ?";
        $result = $this->db->fetchOne($sql, [$id]);
        
        if (isset($result['count']) && $result['count'] > 0) {
            // Soft delete - just deactivate
            $sql = "UPDATE {$this->table} SET status = 'inactive' WHERE id = ?";
            return $this->db->execute($sql, [$id]);
        } else {
            // Hard delete if never used
            $sql = "DELETE FROM {$this->table} WHERE id = ?";
            return $this->db->execute($sql, [$id]);
        }
    }
    
    /**
     * Tăng số lần sử dụng coupon
     */
    public function incrementUsageCount($couponId) {
        $sql = "UPDATE {$this->table} SET used_count = used_count + 1 WHERE id = ?";
        return $this->db->execute($sql, [$couponId]);
    }
}
