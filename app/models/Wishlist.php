<?php
/**
 * Wishlist Model - Quản lý danh sách yêu thích
 */
class Wishlist
{
    protected $table = 'wishlists';
    protected $db;

    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    /**
     * Thêm sản phẩm vào wishlist
     * @param int $userId
     * @param int $productId
     * @return bool|int
     */
    public function add($userId, $productId)
    {
        // Kiểm tra xem đã tồn tại chưa
        if ($this->isInWishlist($userId, $productId)) {
            return false;
        }

        $sql = "INSERT INTO {$this->table} (user_id, product_id, created_at) 
                VALUES (?, ?, NOW())";
        
        return $this->db->insert($sql, [$userId, $productId]);
    }

    /**
     * Xóa sản phẩm khỏi wishlist
     * @param int $userId
     * @param int $productId
     * @return int Number of rows affected
     */
    public function remove($userId, $productId)
    {
        $sql = "DELETE FROM {$this->table} 
                WHERE user_id = ? AND product_id = ?";
        
        return $this->db->execute($sql, [$userId, $productId]);
    }

    /**
     * Lấy tất cả sản phẩm trong wishlist của user
     * @param int $userId
     * @return array
     */
    public function getByUserId($userId)
    {
        $sql = "SELECT w.*, 
                       p.id as product_id, 
                       p.name as product_name, 
                       p.slug as product_slug,
                       p.description as product_description,
                       p.price as product_price, 
                       p.image as product_image,
                       p.stock_quantity as stock,
                       p.status as product_status,
                       p.category_id as category_id,
                       c.name as category_name,
                       COALESCE(AVG(r.rating), 0) as avg_rating,
                       COUNT(DISTINCT r.id) as review_count
                FROM {$this->table} w
                INNER JOIN products p ON w.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN reviews r ON p.id = r.product_id AND r.status = 'approved'
                WHERE w.user_id = ?
                GROUP BY w.id, w.user_id, w.product_id, w.created_at,
                         p.id, p.name, p.slug, p.description, p.price, p.image, 
                         p.stock_quantity, p.status, p.category_id, c.name
                ORDER BY w.created_at DESC";
        
        return $this->db->fetchAll($sql, [$userId]);
    }

    /**
     * Kiểm tra sản phẩm có trong wishlist không
     * @param int $userId
     * @param int $productId
     * @return bool
     */
    public function isInWishlist($userId, $productId)
    {
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} 
                WHERE user_id = ? AND product_id = ?";
        
        $result = $this->db->fetchOne($sql, [$userId, $productId]);
        return $result && $result['count'] > 0;
    }

    /**
     * Đếm số lượng sản phẩm trong wishlist của user
     * @param int $userId
     * @return int
     */
    public function count($userId)
    {
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} 
                WHERE user_id = ?";
        
        $result = $this->db->fetchOne($sql, [$userId]);
        return $result ? (int)$result['count'] : 0;
    }

    /**
     * Lấy danh sách product_id trong wishlist của user
     * @param int $userId
     * @return array Array of product IDs
     */
    public function getProductIds($userId)
    {
        $sql = "SELECT product_id 
                FROM {$this->table} 
                WHERE user_id = ?";
        
        $results = $this->db->fetchAll($sql, [$userId]);
        return array_column($results, 'product_id');
    }

    /**
     * Xóa tất cả wishlist của user
     * @param int $userId
     * @return int
     */
    public function clearByUserId($userId)
    {
        $sql = "DELETE FROM {$this->table} WHERE user_id = ?";
        return $this->db->execute($sql, [$userId]);
    }

    /**
     * Lấy thông tin wishlist item
     * @param int $wishlistId
     * @return array|null
     */
    public function getById($wishlistId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->db->fetchOne($sql, [$wishlistId]);
    }
}
