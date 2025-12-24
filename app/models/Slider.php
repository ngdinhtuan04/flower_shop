<?php
require_once APP_PATH . '/core/DB.php';

class Slider {
    private $db;
    private $table = 'sliders';
    
    public function __construct() {
        $this->db = DB::getInstance();
    }
    
    /**
     * Lấy tất cả slider (dành cho admin)
     */
    public function getAll($filters = []) {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        $conditions = [];
        
        // Filter by status
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $conditions[] = "is_active = ?";
            $params[] = $filters['is_active'];
        }
        
        // Search by title
        if (!empty($filters['search'])) {
            $conditions[] = "(title LIKE ? OR description LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $sql .= " ORDER BY display_order ASC, created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Lấy slider đang active (cho frontend)
     */
    public function getActive() {
        $sql = "SELECT * FROM {$this->table} 
                WHERE is_active = 1 
                ORDER BY display_order ASC";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Lấy slider theo ID
     */
    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Tạo slider mới
     */
    public function create($data) {
        // Lấy order lớn nhất hiện tại
        $maxOrder = $this->db->fetchOne("SELECT MAX(display_order) as max_order FROM {$this->table}");
        $nextOrder = ($maxOrder['max_order'] ?? 0) + 1;
        
        $sql = "INSERT INTO {$this->table} 
                (title, description, image, link, button_text, display_order, is_active, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $params = [
            $data['title'],
            $data['description'] ?? null,
            $data['image'],
            $data['link'] ?? null,
            $data['button_text'] ?? null,
            $data['display_order'] ?? $nextOrder,
            $data['is_active'] ?? 1
        ];
        
        return $this->db->execute($sql, $params);
    }
    
    /**
     * Cập nhật slider
     */
    public function update($id, $data) {
        $sql = "UPDATE {$this->table} 
                SET title = ?, 
                    description = ?, 
                    image = ?, 
                    link = ?, 
                    button_text = ?,
                    display_order = ?,
                    is_active = ?,
                    updated_at = NOW()
                WHERE id = ?";
        
        $params = [
            $data['title'],
            $data['description'] ?? null,
            $data['image'],
            $data['link'] ?? null,
            $data['button_text'] ?? null,
            $data['display_order'],
            $data['is_active'] ?? 1,
            $id
        ];
        
        return $this->db->execute($sql, $params);
    }
    
    /**
     * Xóa slider
     */
    public function delete($id) {
        // Lấy thông tin slider để xóa file ảnh
        $slider = $this->getById($id);
        
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $result = $this->db->execute($sql, [$id]);
        
        // Xóa file ảnh nếu xóa slider thành công
        if ($result && $slider && !empty($slider['image'])) {
            $imagePath = PUBLIC_PATH . '/' . $slider['image'];
            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }
        
        return $result;
    }
    
    /**
     * Bật/tắt slider
     */
    public function toggleActive($id) {
        $sql = "UPDATE {$this->table} 
                SET is_active = NOT is_active, 
                    updated_at = NOW() 
                WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }
    
    /**
     * Cập nhật thứ tự hiển thị
     */
    public function updateOrder($id, $order) {
        $sql = "UPDATE {$this->table} 
                SET display_order = ?, 
                    updated_at = NOW() 
                WHERE id = ?";
        return $this->db->execute($sql, [$order, $id]);
    }
    
    /**
     * Cập nhật thứ tự hàng loạt
     */
    public function updateOrderBatch($orderData) {
        try {
            foreach ($orderData as $item) {
                $this->updateOrder($item['id'], $item['order']);
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Đếm tổng số slider
     */
    public function count($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $params = [];
        $conditions = [];
        
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $conditions[] = "is_active = ?";
            $params[] = $filters['is_active'];
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    /**
     * Lấy thống kê
     */
    public function getStatistics() {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive
                FROM {$this->table}";
        
        return $this->db->fetchOne($sql) ?? [
            'total' => 0,
            'active' => 0,
            'inactive' => 0
        ];
    }
}
