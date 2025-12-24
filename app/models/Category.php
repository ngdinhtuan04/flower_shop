<?php
/**
 * Category Model - Quản lý danh mục sản phẩm
 */
class Category
{
    protected $table = 'categories';
    protected $db;

    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    /**
     * Lấy tất cả danh mục
     */
    public function getAll()
    {
        $sql = "SELECT c.*, 
                (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count 
                FROM {$this->table} c 
                ORDER BY c.name ASC";
        return $this->db->fetchAll($sql);
    }

    /**
     * Lấy danh mục theo ID
     */
    public function getById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Tạo danh mục mới
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (name, description, slug, created_at) 
                VALUES (?, ?, ?, NOW())";
        
        $slug = $this->generateSlug($data['name']);
        
        $insertId = $this->db->insert($sql, [
            $data['name'],
            $data['description'] ?? null,
            $slug
        ]);

        return $insertId ? ['success' => true, 'id' => $insertId] : ['success' => false];
    }

    /**
     * Cập nhật danh mục
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} 
                SET name = ?, description = ?, slug = ?, updated_at = NOW() 
                WHERE id = ?";
        
        $slug = $this->generateSlug($data['name']);
        
        $result = $this->db->execute($sql, [
            $data['name'],
            $data['description'] ?? null,
            $slug,
            $id
        ]);

        return $result ? ['success' => true] : ['success' => false];
    }

    /**
     * Xóa danh mục
     */
    public function delete($id)
    {
        // Kiểm tra xem có sản phẩm nào đang dùng danh mục này không
        $sql = "SELECT COUNT(*) as count FROM products WHERE category_id = ?";
        $result = $this->db->fetchOne($sql, [$id]);
        
        if ($result && $result['count'] > 0) {
            return ['success' => false, 'message' => 'Không thể xóa danh mục đang có ' . $result['count'] . ' sản phẩm'];
        }

        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $result = $this->db->execute($sql, [$id]);

        return $result ? ['success' => true] : ['success' => false, 'message' => 'Lỗi khi xóa danh mục'];
    }

    /**
     * Tìm kiếm danh mục
     */
    public function search($keyword)
    {
        $sql = "SELECT c.*, 
                (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count 
                FROM {$this->table} c 
                WHERE c.name LIKE ? OR c.description LIKE ? 
                ORDER BY c.name ASC";
        
        $searchTerm = "%{$keyword}%";
        return $this->db->fetchAll($sql, [$searchTerm, $searchTerm]);
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
     * Loại bỏ dấu tiếng Việt
     */
    private function removeVietnameseTones($str)
    {
        $marTViet = array("à","á","ạ","ả","ã","â","ầ","ấ","ậ","ẩ","ẫ","ă","ằ","ắ","ặ","ẳ","ẵ","è","é","ẹ","ẻ","ẽ","ê","ề","ế","ệ","ể","ễ","ì","í","ị","ỉ","ĩ","ò","ó","ọ","ỏ","õ","ô","ồ","ố","ộ","ổ","ỗ","ơ","ờ","ớ","ợ","ở","ỡ","ù","ú","ụ","ủ","ũ","ư","ừ","ứ","ự","ử","ữ","ỳ","ý","ỵ","ỷ","ỹ","đ","À","Á","Ạ","Ả","Ã","Â","Ầ","Ấ","Ậ","Ẩ","Ẫ","Ă","Ằ","Ắ","Ặ","Ẳ","Ẵ","È","É","Ẹ","Ẻ","Ẽ","Ê","Ề","Ế","Ệ","Ể","Ễ","Ì","Í","Ị","Ỉ","Ĩ","Ò","Ó","Ọ","Ỏ","Õ","Ô","Ồ","Ố","Ộ","Ổ","Ỗ","Ơ","Ờ","Ớ","Ợ","Ở","Ỡ","Ù","Ú","Ụ","Ủ","Ũ","Ư","Ừ","Ứ","Ự","Ử","Ữ","Ỳ","Ý","Ỵ","Ỷ","Ỹ","Đ");
        $marKoDau = array("a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","a","e","e","e","e","e","e","e","e","e","e","e","i","i","i","i","i","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","o","u","u","u","u","u","u","u","u","u","u","u","y","y","y","y","y","d","A","A","A","A","A","A","A","A","A","A","A","A","A","A","A","A","A","E","E","E","E","E","E","E","E","E","E","E","I","I","I","I","I","O","O","O","O","O","O","O","O","O","O","O","O","O","O","O","O","O","U","U","U","U","U","U","U","U","U","U","U","Y","Y","Y","Y","Y","D");
        return str_replace($marTViet, $marKoDau, $str);
    }
}