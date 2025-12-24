<?php
/**
 * Feedback Model - Quản lý góp ý người dùng
 */
class Feedback {
    protected $table = 'feedback';
    protected $db;
    
    public function __construct() {
        $this->db = DB::getInstance();
    }
    
    /**
     * Tạo góp ý mới (có thể gắn với sản phẩm)
     */
    public function create($data) {
        $sql = "INSERT INTO {$this->table} (user_id, product_id, name, email, phone, subject, message, type, status) 
                VALUES (:user_id, :product_id, :name, :email, :phone, :subject, :message, :type, 'new')";
        
        $params = [
            ':user_id' => $data['user_id'] ?? null,
            ':product_id' => $data['product_id'] ?? null,
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':phone' => $data['phone'] ?? null,
            ':subject' => $data['subject'],
            ':message' => $data['message'],
            ':type' => $data['type'] ?? 'other'
        ];
        
        return $this->db->execute($sql, $params);
    }
    
    /**
     * Lấy tất cả feedback (cho admin)
     */
    public function getAll($filters = []) {
        $sql = "SELECT f.*, u.full_name as user_name, 
                       a.full_name as admin_name,
                       p.name as product_name, p.image as product_image
                FROM {$this->table} f
                LEFT JOIN users u ON f.user_id = u.id
                LEFT JOIN users a ON f.replied_by = a.id
                LEFT JOIN products p ON f.product_id = p.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND f.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['type'])) {
            $sql .= " AND f.type = :type";
            $params[':type'] = $filters['type'];
        }
        
        if (!empty($filters['product_id'])) {
            $sql .= " AND f.product_id = :product_id";
            $params[':product_id'] = $filters['product_id'];
        }
        
        $sql .= " ORDER BY f.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Lấy feedback theo user
     */
    public function getByUserId($userId) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC";
        
        return $this->db->fetchAll($sql, [':user_id' => $userId]);
    }
    
    /**
     * Lấy feedback theo sản phẩm
     */
    public function getByProductId($productId) {
        $sql = "SELECT f.*, u.full_name as user_name
                FROM {$this->table} f
                LEFT JOIN users u ON f.user_id = u.id
                WHERE f.product_id = :product_id
                ORDER BY f.created_at DESC";
        
        return $this->db->fetchAll($sql, [':product_id' => $productId]);
    }
    
    /**
     * Đếm feedback theo sản phẩm
     */
    public function countByProductId($productId) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE product_id = :product_id";
        $result = $this->db->fetchOne($sql, [':product_id' => $productId]);
        return $result['total'] ?? 0;
    }
    
    /**
     * Lấy feedback theo ID
     */
    public function getById($id) {
        $sql = "SELECT f.*, u.full_name as user_name, 
                       a.full_name as admin_name
                FROM {$this->table} f
                LEFT JOIN users u ON f.user_id = u.id
                LEFT JOIN users a ON f.replied_by = a.id
                WHERE f.id = :id";
        
        return $this->db->fetchOne($sql, [':id' => $id]);
    }
    
    /**
     * Cập nhật trạng thái
     */
    public function updateStatus($id, $status) {
        $sql = "UPDATE {$this->table} 
                SET status = :status 
                WHERE id = :id";
        
        return $this->db->execute($sql, [
            ':id' => $id,
            ':status' => $status
        ]);
    }
    
    /**
     * Thêm phản hồi từ admin
     */
    public function addReply($id, $reply, $adminId) {
        $sql = "UPDATE {$this->table} 
                SET admin_reply = :reply, 
                    replied_by = :admin_id, 
                    replied_at = NOW(),
                    status = 'resolved'
                WHERE id = :id";
        
        return $this->db->execute($sql, [
            ':id' => $id,
            ':reply' => $reply,
            ':admin_id' => $adminId
        ]);
    }
    
    /**
     * Xóa feedback
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        return $this->db->execute($sql, [':id' => $id]);
    }
    
    /**
     * Đếm feedback theo trạng thái
     */
    public function countByStatus() {
        $sql = "SELECT status, COUNT(*) as count 
                FROM {$this->table} 
                GROUP BY status";
        
        $results = $this->db->fetchAll($sql);
        $counts = [
            'new' => 0,
            'processing' => 0,
            'resolved' => 0,
            'closed' => 0
        ];
        
        foreach ($results as $row) {
            $counts[$row['status']] = $row['count'];
        }
        
        return $counts;
    }
}