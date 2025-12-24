<?php
class Review {
    protected $table = 'reviews';
    protected $db;
    
    public function __construct()
    {
        $this->db = DB::getInstance();
    }
    
    /**
     * Tạo đánh giá mới (trạng thái chờ duyệt)
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} 
                (user_id, product_id, order_id, rating, comment, status, created_at) 
                VALUES 
                (:user_id, :product_id, :order_id, :rating, :comment, 'pending', NOW())";
        
        return $this->db->execute($sql, $data);
    }
    
    /**
     * Lấy danh sách đánh giá theo sản phẩm (chỉ đã duyệt)
     */
    public function getByProduct($productId, $limit = 10, $offset = 0) {
        $sql = "SELECT r.*, u.full_name, u.avatar
                FROM {$this->table} r
                LEFT JOIN users u ON r.user_id = u.id
                WHERE r.product_id = :product_id
                AND r.status = 'approved'
                ORDER BY r.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        return $this->db->fetchAll($sql, [
            'product_id' => $productId,
            'limit' => (int)$limit,
            'offset' => (int)$offset
        ]);
    }
    
    /**
     * Đếm số lượng đánh giá theo sản phẩm (chỉ đã duyệt)
     */
    public function countByProduct($productId) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE product_id = :product_id AND status = 'approved'";
        $result = $this->db->fetchOne($sql, ['product_id' => $productId]);
        return $result['total'] ?? 0;
    }
    
    /**
     * Lấy thống kê đánh giá theo sản phẩm (chỉ đã duyệt)
     */
    public function getProductRatingStats($productId) {
        $sql = "SELECT 
                    COUNT(*) as total_reviews,
                    AVG(rating) as average_rating,
                    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as star_5,
                    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as star_4,
                    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as star_3,
                    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as star_2,
                    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as star_1
                FROM {$this->table}
                WHERE product_id = :product_id
                AND status = 'approved'";
        
        return $this->db->fetchOne($sql, ['product_id' => $productId]);
    }
    
    /**
     * Kiểm tra user đã đánh giá sản phẩm trong đơn hàng chưa
     */
    public function hasReviewed($userId, $productId, $orderId) {
        $sql = "SELECT id FROM {$this->table} 
                WHERE user_id = :user_id 
                AND product_id = :product_id 
                AND order_id = :order_id";
        
        $result = $this->db->fetchOne($sql, [
            'user_id' => $userId,
            'product_id' => $productId,
            'order_id' => $orderId
        ]);
        
        return !empty($result);
    }
    
    /**
     * Lấy đánh giá mới nhất (chỉ đã duyệt)
     */
    public function getLatestReviews($limit = 6) {
        $sql = "SELECT r.*, u.full_name, u.avatar, p.name as product_name
                FROM {$this->table} r
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN products p ON r.product_id = p.id
                WHERE r.status = 'approved'
                ORDER BY r.created_at DESC
                LIMIT :limit";
        
        return $this->db->fetchAll($sql, ['limit' => (int)$limit]);
    }
    
    /**
     * Kiểm tra user có quyền đánh giá sản phẩm không (đã mua và chưa review)
     */
    public function canReview($userId, $productId) {
        // Kiểm tra user đã mua sản phẩm và đơn hàng đã giao thành công
        $sql = "SELECT o.id as order_id
                FROM orders o
                JOIN order_items oi ON o.id = oi.order_id
                WHERE o.user_id = :user_id 
                AND oi.product_id = :product_id
                AND o.order_status = 'delivered'
                AND NOT EXISTS (
                    SELECT 1 FROM {$this->table} r 
                    WHERE r.user_id = :user_id 
                    AND r.product_id = :product_id 
                    AND r.order_id = o.id
                )
                LIMIT 1";
        
        $result = $this->db->fetchOne($sql, [
            'user_id' => $userId,
            'product_id' => $productId
        ]);
        
        return $result;
    }
    
    /**
     * Lấy reviews của user theo sản phẩm (bao gồm cả pending - để user thấy review của mình)
     */
    public function getUserReviewsByProduct($userId, $productId) {
        $sql = "SELECT r.*, u.full_name, u.avatar
                FROM {$this->table} r
                LEFT JOIN users u ON r.user_id = u.id
                WHERE r.product_id = :product_id
                AND r.user_id = :user_id
                ORDER BY r.created_at DESC";
        
        return $this->db->fetchAll($sql, [
            'product_id' => $productId,
            'user_id' => $userId
        ]);
    }
    
    /**
     * Lấy tất cả reviews theo sản phẩm (approved + user's own pending)
     * Dùng cho trang chi tiết: User thấy review của mình + reviews công khai
     */
    public function getByProductWithUserReviews($productId, $userId = null, $limit = 10, $offset = 0) {
        if ($userId) {
            // Lấy reviews đã duyệt + reviews của chính user
            $sql = "SELECT r.*, u.full_name, u.avatar
                    FROM {$this->table} r
                    LEFT JOIN users u ON r.user_id = u.id
                    WHERE r.product_id = :product_id
                    AND (r.status = 'approved' OR r.user_id = :user_id)
                    ORDER BY r.created_at DESC
                    LIMIT :limit OFFSET :offset";
            
            return $this->db->fetchAll($sql, [
                'product_id' => $productId,
                'user_id' => $userId,
                'limit' => (int)$limit,
                'offset' => (int)$offset
            ]);
        } else {
            // Chỉ lấy reviews đã duyệt
            return $this->getByProduct($productId, $limit, $offset);
        }
    }
    
    /**
     * Lấy tất cả đánh giá (cho admin)
     */
    public function getAll($filters = [], $limit = 20, $offset = 0) {
        $sql = "SELECT r.*, 
                       u.full_name as user_name, u.email,
                       p.name as product_name, p.image as product_image
                FROM {$this->table} r
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN products p ON r.product_id = p.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['product_id'])) {
            $sql .= " AND r.product_id = :product_id";
            $params['product_id'] = $filters['product_id'];
        }
        
        if (!empty($filters['rating'])) {
            $sql .= " AND r.rating = :rating";
            $params['rating'] = $filters['rating'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE :search OR u.full_name LIKE :search OR r.comment LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        $sql .= " ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset";
        $params['limit'] = (int)$limit;
        $params['offset'] = (int)$offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Đếm tổng số đánh giá (cho admin)
     */
    public function countAll($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} r
                LEFT JOIN products p ON r.product_id = p.id
                LEFT JOIN users u ON r.user_id = u.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['product_id'])) {
            $sql .= " AND r.product_id = :product_id";
            $params['product_id'] = $filters['product_id'];
        }
        
        if (!empty($filters['rating'])) {
            $sql .= " AND r.rating = :rating";
            $params['rating'] = $filters['rating'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE :search OR u.full_name LIKE :search OR r.comment LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    /**
     * Cập nhật trạng thái đánh giá
     */
    public function updateStatus($id, $status) {
        $sql = "UPDATE {$this->table} SET status = :status WHERE id = :id";
        return $this->db->execute($sql, [
            'id' => $id,
            'status' => $status
        ]);
    }
    
    /**
     * Lấy thống kê theo rating
     */
    public function getRatingStats() {
        $sql = "SELECT 
                    rating,
                    COUNT(*) as count,
                    AVG(rating) as avg_rating,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count
                FROM {$this->table}
                GROUP BY rating
                ORDER BY rating DESC";
        
        $stats = $this->db->fetchAll($sql);
        
        // Tính tổng đánh giá và trung bình
        $totalSql = "SELECT 
                        COUNT(*) as total_reviews,
                        AVG(rating) as avg_rating,
                        SUM(CASE WHEN status = 'visible' THEN 1 ELSE 0 END) as visible_reviews,
                        AVG(CASE WHEN status = 'visible' THEN rating ELSE NULL END) as visible_avg_rating
                    FROM {$this->table}";
        
        $totals = $this->db->fetchOne($totalSql);
        
        return [
            'by_rating' => $stats,
            'totals' => $totals
        ];
    }
    
    /**
     * Từ chối đánh giá (Admin)
     */
    public function rejectReview($reviewId, $adminId, $adminNote = null) {
        $sql = "UPDATE {$this->table} 
                SET status = 'rejected',
                    admin_note = :admin_note,
                    moderated_by = :moderated_by,
                    moderated_at = NOW()
                WHERE id = :review_id";
        
        return $this->db->execute($sql, [
            'review_id' => $reviewId,
            'admin_note' => $adminNote,
            'moderated_by' => $adminId
        ]);
    }
    
    /**
     * Duyệt đánh giá (Admin)
     */
    public function approveReview($reviewId, $adminId) {
        $sql = "UPDATE {$this->table} 
                SET status = 'approved',
                    admin_note = NULL,
                    moderated_by = :moderated_by,
                    moderated_at = NOW()
                WHERE id = :review_id";
        
        return $this->db->execute($sql, [
            'review_id' => $reviewId,
            'moderated_by' => $adminId
        ]);
    }
    
    /**
     * Lấy đánh giá theo ID (cho admin) - phiên bản đầy đủ
     */
    public function getById($reviewId) {
        $sql = "SELECT r.*, 
                       u.full_name as user_name, u.email,
                       p.name as product_name, p.image as product_image,
                       a.full_name as moderator_name
                FROM {$this->table} r
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN products p ON r.product_id = p.id
                LEFT JOIN users a ON r.moderated_by = a.id
                WHERE r.id = :review_id";
        
        return $this->db->fetchOne($sql, ['review_id' => $reviewId]);
    }
    
    /**
     * Xóa đánh giá vĩnh viễn (Admin)
     */
    public function delete($reviewId) {
        $sql = "DELETE FROM {$this->table} WHERE id = :review_id";
        return $this->db->execute($sql, ['review_id' => $reviewId]);
    }
    
    /**
     * Lấy tất cả đánh giá theo trạng thái (Admin)
     */
    public function getAllByStatus($status = null, $limit = 20, $offset = 0) {
        $sql = "SELECT r.*, 
                       u.full_name as user_name, u.email,
                       p.name as product_name, p.image as product_image
                FROM {$this->table} r
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN products p ON r.product_id = p.id
                WHERE 1=1";
        
        $params = [];
        
        if ($status !== null) {
            $sql .= " AND r.status = :status";
            $params['status'] = $status;
        }
        
        $sql .= " ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset";
        $params['limit'] = (int)$limit;
        $params['offset'] = (int)$offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Đếm đánh giá theo trạng thái (Admin)
     */
    public function countByStatus($status = null) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";
        $params = [];
        
        if ($status !== null) {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    /**
     * Lấy tất cả đánh giá với filters (search, rating, status)
     */
    public function getAllWithFilters($filters = [], $limit = 20, $offset = 0) {
        $sql = "SELECT r.*, 
                       u.full_name as user_name, u.email,
                       p.name as product_name, p.image as product_image
                FROM {$this->table} r
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN products p ON r.product_id = p.id
                WHERE 1=1";
        
        $params = [];
        
        // Filter by search (product name or user name)
        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE :search OR u.full_name LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        // Filter by rating
        if (!empty($filters['rating'])) {
            $sql .= " AND r.rating = :rating";
            $params['rating'] = (int)$filters['rating'];
        }
        
        // Filter by status
        if (!empty($filters['status'])) {
            $sql .= " AND r.status = :status";
            $params['status'] = $filters['status'];
        }
        
        $sql .= " ORDER BY r.created_at DESC LIMIT :limit OFFSET :offset";
        $params['limit'] = (int)$limit;
        $params['offset'] = (int)$offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Đếm tổng đánh giá với filters
     */
    public function countWithFilters($filters = []) {
        $sql = "SELECT COUNT(*) as total 
                FROM {$this->table} r
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN products p ON r.product_id = p.id
                WHERE 1=1";
        
        $params = [];
        
        // Filter by search
        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE :search OR u.full_name LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        // Filter by rating
        if (!empty($filters['rating'])) {
            $sql .= " AND r.rating = :rating";
            $params['rating'] = (int)$filters['rating'];
        }
        
        // Filter by status
        if (!empty($filters['status'])) {
            $sql .= " AND r.status = :status";
            $params['status'] = $filters['status'];
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    /**
     * Lấy thống kê rating tổng quan
     */
    public function getRatingStatistics() {
        $sql = "SELECT 
                    COUNT(*) as total_reviews,
                    COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_reviews,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_reviews,
                    COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_reviews,
                    AVG(CASE WHEN status = 'approved' THEN rating END) as approved_avg_rating,
                    AVG(rating) as overall_avg_rating,
                    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as star_5,
                    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as star_4,
                    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as star_3,
                    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as star_2,
                    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as star_1
                FROM {$this->table}";
        
        return $this->db->fetchOne($sql);
    }
}


