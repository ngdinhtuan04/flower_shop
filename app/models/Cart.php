<?php
/**
 * Cart Model - Quản lý giỏ hàng trong database
 */
class Cart {
    protected $table = 'cart';
    protected $db;
    
    public function __construct() {
        $this->db = DB::getInstance()->getConnection();
    }
    
    /**
     * Lấy giỏ hàng của user (từ database hoặc session)
     */
    public function getCartItems($userId = null) {
        if ($userId) {
            // User đã login - lấy từ database
            return $this->getByUserId($userId);
        } else {
            // Guest user - lấy từ session
            $sessionId = session_id();
            return $this->getBySessionId($sessionId);
        }
    }
    
    /**
     * Lấy giỏ hàng theo user_id
     */
    public function getByUserId($userId) {
        $query = "SELECT c.*, p.name, p.price, p.image, p.stock_quantity
                  FROM {$this->table} c
                  JOIN products p ON c.product_id = p.id
                  WHERE c.user_id = ?
                  ORDER BY c.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lấy giỏ hàng theo session_id (guest)
     */
    public function getBySessionId($sessionId) {
        $query = "SELECT c.*, p.name, p.price, p.image, p.stock_quantity
                  FROM {$this->table} c
                  JOIN products p ON c.product_id = p.id
                  WHERE c.session_id = ?
                  ORDER BY c.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$sessionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Kiểm tra sản phẩm đã có trong giỏ chưa
     */
    public function getItem($userId, $productId, $sessionId = null) {
        if ($userId) {
            $query = "SELECT * FROM {$this->table} WHERE user_id = ? AND product_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$userId, $productId]);
        } else {
            $query = "SELECT * FROM {$this->table} WHERE session_id = ? AND product_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$sessionId, $productId]);
        }
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Thêm sản phẩm vào giỏ (database)
     */
    public function addItem($userId, $productId, $quantity, $price) {
        try {
            $sessionId = $userId ? null : session_id();
            
            // Kiểm tra đã có chưa
            $existingItem = $this->getItem($userId, $productId, $sessionId);
            
            if ($existingItem) {
                // Cập nhật số lượng
                return $this->updateQuantity($existingItem['id'], $existingItem['quantity'] + $quantity);
            } else {
                // Thêm mới
                $query = "INSERT INTO {$this->table} (user_id, session_id, product_id, quantity, price) 
                          VALUES (?, ?, ?, ?, ?)";
                $stmt = $this->db->prepare($query);
                $result = $stmt->execute([$userId, $sessionId, $productId, $quantity, $price]);
                
                if (!$result) {
                    $error = $stmt->errorInfo();
                    throw new Exception("Database error: " . $error[2]);
                }
                
                return $result;
            }
        } catch (PDOException $e) {
            error_log("Cart addItem error: " . $e->getMessage());
            throw new Exception("Không thể thêm sản phẩm vào giỏ hàng. Vui lòng thử lại.");
        }
    }
    
    /**
     * Cập nhật số lượng sản phẩm trong giỏ
     */
    public function updateQuantity($cartId, $quantity) {
        if ($quantity <= 0) {
            // Xóa nếu số lượng <= 0
            $query = "DELETE FROM {$this->table} WHERE id = ?";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$cartId]);
        }
        
        $query = "UPDATE {$this->table} SET quantity = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$quantity, $cartId]);
    }
    
    /**
     * Xóa item khỏi giỏ hàng
     */
    public function removeItem($userId, $productId, $sessionId = null) {
        if ($userId) {
            $query = "DELETE FROM {$this->table} WHERE user_id = ? AND product_id = ?";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$userId, $productId]);
        } else {
            $query = "DELETE FROM {$this->table} WHERE session_id = ? AND product_id = ?";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$sessionId, $productId]);
        }
    }
    
    /**
     * Xóa toàn bộ giỏ hàng
     */
    public function clearCart($userId, $sessionId = null) {
        if ($userId) {
            $query = "DELETE FROM {$this->table} WHERE user_id = ?";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$userId]);
        } else {
            $query = "DELETE FROM {$this->table} WHERE session_id = ?";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$sessionId]);
        }
    }
    
    /**
     * Đếm số lượng item trong giỏ
     */
    public function getCartCount($userId = null) {
        $sessionId = $userId ? null : session_id();
        
        if ($userId) {
            $query = "SELECT SUM(quantity) as total FROM {$this->table} WHERE user_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$userId]);
        } else {
            $query = "SELECT SUM(quantity) as total FROM {$this->table} WHERE session_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$sessionId]);
        }
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
    /**
     * Tính tổng tiền giỏ hàng
     */
    public function getCartTotal($userId = null) {
        $cartItems = $this->getCartItems($userId);
        $total = 0;
        
        foreach ($cartItems as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        return $total;
    }
    
    /**
     * Chuyển giỏ hàng từ session sang user sau khi login
     */
    public function transferToUser($sessionId, $userId) {
        // Lấy giỏ hàng của session
        $sessionCart = $this->getBySessionId($sessionId);
        
        foreach ($sessionCart as $item) {
            // Kiểm tra user đã có sản phẩm này chưa
            $userItem = $this->getItem($userId, $item['product_id']);
            
            if ($userItem) {
                // Cộng thêm số lượng
                $this->updateQuantity($userItem['id'], $userItem['quantity'] + $item['quantity']);
            } else {
                // Chuyển sang user_id
                $query = "UPDATE {$this->table} SET user_id = ?, session_id = NULL WHERE id = ?";
                $stmt = $this->db->prepare($query);
                $stmt->execute([$userId, $item['id']]);
            }
        }
        
        // Xóa các item còn lại của session
        $this->clearCart(null, $sessionId);
        
        return true;
    }
}
