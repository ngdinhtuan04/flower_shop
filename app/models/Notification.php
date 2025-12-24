<?php
/**
 * Notification Model
 * Quản lý thông báo cho user
 */
class Notification {
    protected $table = 'notifications';
    protected $db;
    
    public function __construct()
    {
        $this->db = DB::getInstance();
    }
    
    /**
     * Tạo thông báo mới
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} 
                (user_id, type, title, message, link, created_at) 
                VALUES 
                (:user_id, :type, :title, :message, :link, NOW())";
        
        return $this->db->execute($sql, $data);
    }
    
    /**
     * Lấy thông báo của user (chưa đọc trước)
     */
    public function getByUser($userId, $limit = 20, $offset = 0) {
        $sql = "SELECT * FROM {$this->table}
                WHERE user_id = :user_id
                ORDER BY is_read ASC, created_at DESC
                LIMIT :limit OFFSET :offset";
        
        return $this->db->fetchAll($sql, [
            'user_id' => $userId,
            'limit' => (int)$limit,
            'offset' => (int)$offset
        ]);
    }
    
    /**
     * Đếm tổng số thông báo của user
     */
    public function countByUser($userId) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE user_id = :user_id";
        
        $result = $this->db->fetchOne($sql, ['user_id' => $userId]);
        return $result['total'] ?? 0;
    }
    
    /**
     * Đếm thông báo chưa đọc
     */
    public function countUnread($userId) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE user_id = :user_id AND is_read = FALSE";
        
        $result = $this->db->fetchOne($sql, ['user_id' => $userId]);
        return $result['total'] ?? 0;
    }
    
    /**
     * Đánh dấu đã đọc
     */
    public function markAsRead($notificationId, $userId) {
        $sql = "UPDATE {$this->table} 
                SET is_read = TRUE 
                WHERE id = :id AND user_id = :user_id";
        
        return $this->db->execute($sql, [
            'id' => $notificationId,
            'user_id' => $userId
        ]);
    }
    
    /**
     * Đánh dấu tất cả đã đọc
     */
    public function markAllAsRead($userId) {
        $sql = "UPDATE {$this->table} 
                SET is_read = TRUE 
                WHERE user_id = :user_id AND is_read = FALSE";
        
        return $this->db->execute($sql, ['user_id' => $userId]);
    }
    
    /**
     * Xóa thông báo
     */
    public function delete($notificationId, $userId) {
        $sql = "DELETE FROM {$this->table} 
                WHERE id = :id AND user_id = :user_id";
        
        return $this->db->execute($sql, [
            'id' => $notificationId,
            'user_id' => $userId
        ]);
    }
    
    /**
     * Xóa thông báo cũ (quá 30 ngày)
     */
    public function deleteOld($days = 30) {
        $sql = "DELETE FROM {$this->table} 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";
        
        return $this->db->execute($sql, ['days' => $days]);
    }
    
    /**
     * Gửi thông báo review được duyệt
     */
    public function notifyReviewApproved($userId, $productId, $productName) {
        return $this->create([
            'user_id' => $userId,
            'type' => 'review_approved',
            'title' => '✅ Đánh giá đã được duyệt',
            'message' => "Đánh giá của bạn cho sản phẩm \"$productName\" đã được phê duyệt và hiển thị công khai.",
            'link' => "/product/detail/$productId"
        ]);
    }
    
    /**
     * Gửi thông báo review bị từ chối
     */
    public function notifyReviewRejected($userId, $productName, $reason) {
        return $this->create([
            'user_id' => $userId,
            'type' => 'review_rejected',
            'title' => '🚫 Đánh giá bị từ chối',
            'message' => "Đánh giá của bạn cho sản phẩm \"$productName\" đã bị từ chối. Lý do: $reason",
            'link' => null
        ]);
    }
    
    /**
     * Lấy thông báo gần đây (cho dropdown)
     */
    public function getRecent($userId, $limit = 10) {
        $sql = "SELECT * FROM {$this->table}
                WHERE user_id = :user_id
                ORDER BY is_read ASC, created_at DESC
                LIMIT :limit";
        
        return $this->db->fetchAll($sql, [
            'user_id' => $userId,
            'limit' => (int)$limit
        ]);
    }
    
    /**
     * Gửi thông báo cập nhật đơn hàng
     */
    public function notifyOrderStatus($userId, $orderId, $status) {
        $statusText = [
            'pending' => '⏳ Đơn hàng đang chờ xác nhận',
            'confirmed' => '✅ Đơn hàng đã được xác nhận',
            'shipping' => '🚚 Đơn hàng đang được giao',
            'delivered' => '📦 Đơn hàng đã giao thành công',
            'cancelled' => '❌ Đơn hàng đã bị hủy'
        ];
        
        return $this->create([
            'user_id' => $userId,
            'type' => 'order_status',
            'title' => 'Cập nhật đơn hàng #' . $orderId,
            'message' => $statusText[$status] ?? 'Đơn hàng có cập nhật mới',
            'link' => "/orders/detail/$orderId"
        ]);
    }
    
    /**
     * Gửi thông báo khuyến mãi cho tất cả users
     */
    public function notifyAllUsersPromotion($title, $message, $link = '/promotions') {
        $sql = "SELECT id FROM users WHERE role = 'user'";
        $users = $this->db->fetchAll($sql);
        
        $success = 0;
        foreach ($users as $user) {
            $result = $this->create([
                'user_id' => $user['id'],
                'type' => 'promotion',
                'title' => $title,
                'message' => $message,
                'link' => $link
            ]);
            if ($result) $success++;
        }
        
        return $success;
    }
}

