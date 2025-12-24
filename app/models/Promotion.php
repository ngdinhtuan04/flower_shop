<?php
/**
 * Model xử lý dữ liệu Promotions
 */

require_once APP_PATH . '/core/DB.php';

class Promotion {
    private $db;
    
    public function __construct() {
        $this->db = DB::getInstance();
    }
    
    /**
     * Lấy tất cả khuyến mãi với filter
     */
    public function getAll($filters = []) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM promotions p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE 1=1";
        $params = [];
        
        if (isset($filters['is_active'])) {
            $sql .= " AND p.is_active = ?";
            $params[] = $filters['is_active'];
        }
        
        if (isset($filters['apply_to'])) {
            $sql .= " AND p.apply_to = ?";
            $params[] = $filters['apply_to'];
        }
        
        if (isset($filters['search']) && !empty($filters['search'])) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Chỉ lấy các khuyến mãi còn hiệu lực
        if (isset($filters['valid_only']) && $filters['valid_only']) {
            $sql .= " AND p.start_date <= NOW() AND p.end_date >= NOW()";
        }
        
        $sql .= " ORDER BY p.priority DESC, p.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Lấy một khuyến mãi theo ID
     */
    public function getById($id) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM promotions p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Tạo khuyến mãi mới
     */
    public function create($data) {
        $sql = "INSERT INTO promotions (
                    name, description, discount_type, discount_value, 
                    apply_to, category_id, start_date, end_date, 
                    is_active, max_discount_amount, min_order_amount,
                    usage_limit, priority
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['name'],
            $data['description'] ?? null,
            $data['discount_type'],
            $data['discount_value'],
            $data['apply_to'],
            $data['category_id'] ?? null,
            $data['start_date'],
            $data['end_date'],
            $data['is_active'] ?? 1,
            $data['max_discount_amount'] ?? null,
            $data['min_order_amount'] ?? 0,
            $data['usage_limit'] ?? null,
            $data['priority'] ?? 0
        ];
        
        return $this->db->execute($sql, $params);
    }
    
    /**
     * Cập nhật khuyến mãi
     */
    public function update($id, $data) {
        $sql = "UPDATE promotions SET 
                    name = ?, description = ?, discount_type = ?, 
                    discount_value = ?, apply_to = ?, category_id = ?,
                    start_date = ?, end_date = ?, is_active = ?,
                    max_discount_amount = ?, min_order_amount = ?,
                    usage_limit = ?, priority = ?
                WHERE id = ?";
        
        $params = [
            $data['name'],
            $data['description'] ?? null,
            $data['discount_type'],
            $data['discount_value'],
            $data['apply_to'],
            $data['category_id'] ?? null,
            $data['start_date'],
            $data['end_date'],
            $data['is_active'] ?? 1,
            $data['max_discount_amount'] ?? null,
            $data['min_order_amount'] ?? 0,
            $data['usage_limit'] ?? null,
            $data['priority'] ?? 0,
            $id
        ];
        
        return $this->db->execute($sql, $params);
    }
    
    /**
     * Xóa khuyến mãi
     */
    public function delete($id) {
        // Xóa các liên kết sản phẩm trước (cascade sẽ tự động xóa)
        $sql = "DELETE FROM promotions WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    /**
     * Toggle trạng thái active
     */
    public function toggleActive($id) {
        $sql = "UPDATE promotions SET is_active = NOT is_active WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    /**
     * Thêm sản phẩm vào khuyến mãi
     */
    public function addProducts($promotionId, $productIds) {
        if (empty($productIds)) {
            return true;
        }
        
        // Xóa các sản phẩm cũ trước
        $this->removeAllProducts($promotionId);
        
        // Thêm sản phẩm mới
        $sql = "INSERT INTO promotion_products (promotion_id, product_id) VALUES (?, ?)";
        
        foreach ($productIds as $productId) {
            $this->db->execute($sql, [$promotionId, $productId]);
        }
        
        return true;
    }
    
    /**
     * Xóa tất cả sản phẩm khỏi khuyến mãi
     */
    public function removeAllProducts($promotionId) {
        $sql = "DELETE FROM promotion_products WHERE promotion_id = ?";
        return $this->db->execute($sql, [$promotionId]);
    }
    
    /**
     * Lấy danh sách sản phẩm của một khuyến mãi
     */
    public function getPromotionProducts($promotionId) {
        $sql = "SELECT pp.*, p.name as product_name, p.price, p.image
                FROM promotion_products pp
                INNER JOIN products p ON pp.product_id = p.id
                WHERE pp.promotion_id = ?
                ORDER BY p.name";
        
        return $this->db->fetchAll($sql, [$promotionId]);
    }
    
    /**
     * Lấy các khuyến mãi áp dụng cho một sản phẩm
     * @return array Danh sách các khuyến mãi có hiệu lực
     */
    public function getActivePromotionsForProduct($productId, $categoryId = null) {
        $sql = "SELECT DISTINCT p.*
                FROM promotions p
                LEFT JOIN promotion_products pp ON p.id = pp.promotion_id
                WHERE p.is_active = 1
                AND p.start_date <= NOW()
                AND p.end_date >= NOW()
                AND (
                    p.apply_to = 'all'
                    OR (p.apply_to = 'category' AND p.category_id = ?)
                    OR (p.apply_to = 'product' AND pp.product_id = ?)
                )
                ORDER BY p.priority DESC, p.discount_value DESC
                LIMIT 1";
        
        return $this->db->fetchOne($sql, [$categoryId, $productId]);
    }
    
    /**
     * Tính giá sau khuyến mãi cho một sản phẩm
     */
    public function calculateDiscountedPrice($productId, $originalPrice, $categoryId = null) {
        $promotion = $this->getActivePromotionsForProduct($productId, $categoryId);
        
        if (!$promotion) {
            return [
                'original_price' => $originalPrice,
                'discounted_price' => $originalPrice,
                'discount_amount' => 0,
                'promotion' => null
            ];
        }
        
        $discountAmount = 0;
        
        if ($promotion['discount_type'] == 'percentage') {
            $discountAmount = ($originalPrice * $promotion['discount_value']) / 100;
            
            // Giới hạn số tiền giảm tối đa
            if ($promotion['max_discount_amount'] && $discountAmount > $promotion['max_discount_amount']) {
                $discountAmount = $promotion['max_discount_amount'];
            }
        } else {
            // Fixed discount
            $discountAmount = $promotion['discount_value'];
            
            // Không giảm quá giá gốc
            if ($discountAmount > $originalPrice) {
                $discountAmount = $originalPrice;
            }
        }
        
        $discountedPrice = $originalPrice - $discountAmount;
        
        return [
            'original_price' => $originalPrice,
            'discounted_price' => max(0, $discountedPrice),
            'discount_amount' => $discountAmount,
            'discount_percentage' => ($originalPrice > 0) ? round(($discountAmount / $originalPrice) * 100) : 0,
            'promotion' => $promotion
        ];
    }
    
    /**
     * Lấy thống kê khuyến mãi
     */
    public function getStatistics() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive,
                    SUM(CASE WHEN start_date <= NOW() AND end_date >= NOW() THEN 1 ELSE 0 END) as valid,
                    SUM(used_count) as total_usage
                FROM promotions";
        
        return $this->db->fetchOne($sql);
    }
    
    /**
     * Kiểm tra xem có khuyến mãi trùng lặp không
     */
    public function checkDuplicate($data, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count
                FROM promotions
                WHERE apply_to = ?
                AND is_active = 1
                AND start_date <= ?
                AND end_date >= ?";
        
        $params = [$data['apply_to'], $data['end_date'], $data['start_date']];
        
        if ($data['apply_to'] == 'category' && isset($data['category_id'])) {
            $sql .= " AND category_id = ?";
            $params[] = $data['category_id'];
        }
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] > 0;
    }
}
