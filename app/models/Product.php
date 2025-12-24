<?php
/**
 * Product Model - Quản lý sản phẩm
 */
class Product
{
    protected $table = 'products';
    protected $db;

    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    /**
     * Lấy tất cả sản phẩm với thông tin danh mục
     */
    public function getAll($limit = null, $offset = 0)
    {
        $sql = "SELECT p.*, p.stock_quantity as stock, c.name as category_name,
                COALESCE(AVG(r.rating), 0) as avg_rating,
                COUNT(DISTINCT r.id) as review_count
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN reviews r ON p.id = r.product_id AND r.status = 'approved'
                GROUP BY p.id
                ORDER BY p.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Lấy sản phẩm theo ID
     */
    public function getById($id)
    {
        $sql = "SELECT p.*, p.stock_quantity as stock, c.name as category_name
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = ? LIMIT 1";
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Lấy sản phẩm theo danh mục
     */
    public function getByCategory($categoryId, $limit = null)
    {
        $sql = "SELECT p.*, p.stock_quantity as stock, c.name as category_name,
                COALESCE(AVG(r.rating), 0) as avg_rating,
                COUNT(DISTINCT r.id) as review_count
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN reviews r ON p.id = r.product_id AND r.status = 'approved'
                WHERE p.category_id = ? AND p.status = 'active' 
                GROUP BY p.id
                ORDER BY p.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->fetchAll($sql, [$categoryId]);
    }

    /**
     * Lấy sản phẩm nổi bật (có đánh giá cao hoặc bán chạy)
     */
    public function getFeaturedProducts($limit = 8)
    {
        $sql = "SELECT p.id, p.name, p.slug, p.description, p.price, p.image, 
                p.category_id, p.stock_quantity as stock, p.status, p.created_at, p.updated_at,
                c.name as category_name,
                COALESCE(AVG(r.rating), 0) as avg_rating,
                COUNT(DISTINCT r.id) as review_count
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN reviews r ON p.id = r.product_id AND r.status = 'approved'
                WHERE p.status = 'active'
                GROUP BY p.id, p.name, p.slug, p.description, p.price, p.image, 
                         p.category_id, p.stock_quantity, p.status, p.created_at, p.updated_at, 
                         c.name
                ORDER BY avg_rating DESC, p.created_at DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$limit]);
    }

    /**
     * Tạo sản phẩm mới
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (name, slug, description, price, image, category_id, stock_quantity, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $slug = $this->generateSlug($data['name']);
        
        $insertId = $this->db->insert($sql, [
            $data['name'],
            $slug,
            $data['description'] ?? null,
            $data['price'],
            $data['image'] ?? null,
            $data['category_id'],
            $data['stock'] ?? 0,
            $data['status'] ?? 'active'
        ]);

        return $insertId ? ['success' => true, 'id' => $insertId] : ['success' => false];
    }

    /**
     * Cập nhật sản phẩm
     */
    public function update($id, $data)
    {
        // Nếu không có image mới, không update trường image
        if (!isset($data['image'])) {
            $sql = "UPDATE {$this->table} 
                    SET name = ?, slug = ?, description = ?, price = ?, 
                        category_id = ?, status = ?, updated_at = NOW() 
                    WHERE id = ?";
            
            $slug = $this->generateSlug($data['name']);
            
            $result = $this->db->execute($sql, [
                $data['name'],
                $slug,
                $data['description'] ?? null,
                $data['price'],
                $data['category_id'],
                $data['status'] ?? 'active',
                $id
            ]);
        } else {
            $sql = "UPDATE {$this->table} 
                    SET name = ?, slug = ?, description = ?, price = ?, 
                        image = ?, category_id = ?, status = ?, updated_at = NOW() 
                    WHERE id = ?";
            
            $slug = $this->generateSlug($data['name']);
            
            $result = $this->db->execute($sql, [
                $data['name'],
                $slug,
                $data['description'] ?? null,
                $data['price'],
                $data['image'],
                $data['category_id'],
                $data['status'] ?? 'active',
                $id
            ]);
        }

        return $result ? ['success' => true] : ['success' => false];
    }

    /**
     * Cập nhật chỉ tồn kho (riêng biệt)
     */
    public function updateStock($id, $stock)
    {
        $sql = "UPDATE {$this->table} 
                SET stock_quantity = ?, updated_at = NOW() 
                WHERE id = ?";
        
        $result = $this->db->execute($sql, [$stock, $id]);
        return $result ? ['success' => true] : ['success' => false];
    }

    /**
     * Cộng thêm tồn kho (cho nhập kho)
     */
    public function addStock($id, $quantity)
    {
        $sql = "UPDATE {$this->table} 
                SET stock_quantity = stock_quantity + ?, updated_at = NOW() 
                WHERE id = ?";
        
        $result = $this->db->execute($sql, [$quantity, $id]);
        return $result ? true : false;
    }

    /**
     * Xóa sản phẩm
     */
    public function delete($id)
    {
        // Xóa ảnh nếu có
        $product = $this->getById($id);
        if ($product && !empty($product['image'])) {
            $imagePath = PUBLIC_PATH . '/' . $product['image'];
            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }

        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $result = $this->db->execute($sql, [$id]);

        return $result ? ['success' => true] : ['success' => false];
    }

    /**
     * Tìm kiếm sản phẩm
     */
    public function search($keyword, $categoryId = null, $status = null)
    {
        $sql = "SELECT p.*, p.stock_quantity as stock, c.name as category_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE (p.name LIKE ? OR p.description LIKE ?)";
        
        $params = ["%{$keyword}%", "%{$keyword}%"];
        
        if ($categoryId) {
            $sql .= " AND p.category_id = ?";
            $params[] = $categoryId;
        }
        
        if ($status) {
            $sql .= " AND p.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Lọc sản phẩm theo điều kiện
     */
    public function filter($filters = [])
    {
        $sql = "SELECT p.*, p.stock_quantity as stock, c.name as category_name 
                FROM {$this->table} p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['category_id'])) {
            $sql .= " AND p.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND p.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['min_price'])) {
            $sql .= " AND p.price >= ?";
            $params[] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $sql .= " AND p.price <= ?";
            $params[] = $filters['max_price'];
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Đếm tổng sản phẩm
     */
    public function count($status = null)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $params = [];
        
        if ($status) {
            $sql .= " WHERE status = ?";
            $params[] = $status;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] ?? 0;
    }

    /**
     * Tạo slug từ tên
     */
    private function generateSlug($name)
    {
        $slug = strtolower($name);
        $slug = $this->removeVietnameseTones($slug);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }

    /**
     * Giảm số lượng tồn kho khi có đơn hàng
     */
    public function decreaseStock($productId, $quantity)
    {
        $sql = "UPDATE {$this->table} 
                SET stock_quantity = stock_quantity - ? 
                WHERE id = ? AND stock_quantity >= ?";
        
        $result = $this->db->execute($sql, [$quantity, $productId, $quantity]);
        
        if (!$result) {
            throw new Exception("Không đủ hàng trong kho cho sản phẩm ID: " . $productId);
        }
        
        return $result;
    }

    /**
     * Tăng số lượng tồn kho (khi hủy đơn, hoàn hàng)
     */
    public function increaseStock($productId, $quantity)
    {
        $sql = "UPDATE {$this->table} 
                SET stock_quantity = stock_quantity + ? 
                WHERE id = ?";
        
        return $this->db->execute($sql, [$quantity, $productId]);
    }

    /**
     * Lấy tất cả sản phẩm với thông tin tồn kho và doanh số
     * Dùng cho báo cáo tồn kho
     */
    public function getAllWithStock($filters = [])
    {
        $where = "WHERE 1=1";
        $params = [];
        
        // Lọc theo category
        if (!empty($filters['category_id'])) {
            $where .= " AND p.category_id = ?";
            $params[] = $filters['category_id'];
        }
        
        // Lọc theo trạng thái tồn kho
        if (!empty($filters['stock_status'])) {
            switch ($filters['stock_status']) {
                case 'out':
                    $where .= " AND p.stock_quantity = 0";
                    break;
                case 'low':
                    $threshold = $filters['low_stock_threshold'] ?? 10;
                    $where .= " AND p.stock_quantity > 0 AND p.stock_quantity <= ?";
                    $params[] = $threshold;
                    break;
                case 'normal':
                    $threshold = $filters['low_stock_threshold'] ?? 10;
                    $where .= " AND p.stock_quantity > ? AND p.stock_quantity <= 100";
                    $params[] = $threshold;
                    break;
                case 'overstock':
                    $where .= " AND p.stock_quantity > 100";
                    break;
            }
        }
        
        $sql = "SELECT 
                    p.id,
                    p.name,
                    p.price,
                    p.stock_quantity,
                    p.status,
                    c.name as category_name,
                    COALESCE(cp.import_price, 0) as import_price,
                    COALESCE(sales.total_sold, 0) as total_sold,
                    COALESCE(sales.last_30_days_sold, 0) as last_30_days_sold
                FROM {$this->table} p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN (
                    SELECT product_id, MAX(import_price) as import_price 
                    FROM contract_products 
                    GROUP BY product_id
                ) cp ON p.id = cp.product_id
                LEFT JOIN (
                    SELECT 
                        oi.product_id,
                        SUM(oi.quantity) as total_sold,
                        SUM(CASE WHEN o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN oi.quantity ELSE 0 END) as last_30_days_sold
                    FROM order_items oi
                    JOIN orders o ON oi.order_id = o.id
                    WHERE o.status IN ('delivered', 'completed')
                    GROUP BY oi.product_id
                ) sales ON p.id = sales.product_id
                {$where}
                ORDER BY p.stock_quantity ASC, p.name ASC";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Loại bỏ dấu tiếng Việt
     */
    private function removeVietnameseTones($str)
    {
        $marTViet = array("à","á","ạ","ả","ã","â","ầ","ấ","ậ","ẩ","ẫ","ă","ằ","ắ","ặ","ẳ","ẵ","è","é","ẹ","ẻ","ẽ","ê","ề","ế","ệ","ể","ễ","ì","í","ị","ỉ","ĩ","ò","ó","ọ","ỏ","õ","ô","ồ","ố","ộ","ổ","ỗ","ơ","ờ","ớ","ợ","ở","ỡ","ù","ú","ụ","ủ","ũ","ư","ừ","ứ","ự","ử","ữ","ỳ","ý","ỵ","ỷ","ỹ","đ","À","Á","Ạ","Ả","Ã","Â","Ầ","Ấ","Ậ","Ẩ","Ẫ","Ă","Ằ","Ắ","Ặ","Ẳ","Ẵ","È","É","Ẹ","Ẻ","Ẽ","Ê","Ề","Ế","Ệ","Ể","Ễ","Ì","Í","Ị","Ỉ","Ĩ","Ò","Ó","Ọ","Ỏ","Õ","Ô","Ồ","Ố","Ộ","Ổ","Ỗ","Ơ","Ờ","Ớ","Ợ","Ở","Ỡ","Ù","Ú","Ụ","Ủ","Ũ","Ư","Ừ","Ứ","Ự","Ử","Ữ","Ỳ","Ý","Ỵ","Ỷ","Ỹ","Đ");
        $marKoDau = array("a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","e","e","e","e","e","e","e","e","e","e","e","i","i","i","i","i","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","u","u","u","u","u","u","u","u","u","u","u","y","y","y","y","y","d","A","A","A","A","A","A","A","A","A","A","A","A","A","A","A","A","A","E","E","E","E","E","E","E","E","E","E","E","I","I","I","I","I","O","O","O","O","O","O","O","O","O","O","O","O","O","O","O","O","O","U","U","U","U","U","U","U","U","U","U","U","Y","Y","Y","Y","Y","D");
        return str_replace($marTViet, $marKoDau, $str);
    }
}