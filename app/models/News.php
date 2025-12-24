<?php
/**
 * News Model - Quản lý tin tức
 */
class News
{
    protected $table = 'news';
    protected $db;

    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    /**
     * Lấy tất cả tin tức với phân trang, lọc và tìm kiếm
     */
    public function getAll($page = 1, $limit = 10, $filters = [])
    {
        $offset = ($page - 1) * $limit;
        $conditions = [];
        $params = [];
        $userId = $_SESSION['user_id'] ?? null;

        // Lọc theo trạng thái
        if (isset($filters['status']) && $filters['status'] !== '') {
            $conditions[] = "n.status = ?";
            $params[] = $filters['status'];
        }

        // Lọc theo danh mục
        if (isset($filters['category']) && $filters['category'] !== '') {
            $conditions[] = "n.category = ?";
            $params[] = $filters['category'];
        }

        // Lọc theo tác giả
        if (isset($filters['author_id']) && $filters['author_id'] !== '') {
            $conditions[] = "n.author_id = ?";
            $params[] = $filters['author_id'];
        }

        // Tìm kiếm
        if (isset($filters['search']) && $filters['search'] !== '') {
            $search = '%' . $filters['search'] . '%';
            $conditions[] = "(n.title LIKE ? OR n.content LIKE ? OR n.excerpt LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        // Xây dựng câu query
        $where = count($conditions) > 0 ? 'WHERE ' . implode(' AND ', $conditions) : '';

        // Xác định ORDER BY
        $orderBy = "ORDER BY n.created_at DESC";
        $sort = $filters['sort'] ?? 'latest';
        switch ($sort) {
            case 'views':
                $orderBy = "ORDER BY n.views DESC";
                break;
            case 'comments':
                $orderBy = "ORDER BY comments_count DESC";
                break;
            case 'likes':
                $orderBy = "ORDER BY likes_count DESC";
                break;
            default:
                $orderBy = "ORDER BY n.created_at DESC";
        }

        // Đếm tổng số bản ghi
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} n $where";
        $stmt = $this->db->getConnection()->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Lấy dữ liệu với JOIN
        $sql = "SELECT n.*, 
                u.full_name as author_name,
                COUNT(DISTINCT nc.id) as comments_count,
                COUNT(DISTINCT nl.id) as likes_count" .
                ($userId ? ", MAX(CASE WHEN nl.user_id = ? THEN 1 ELSE 0 END) as is_liked" : "") .
                " FROM {$this->table} n
                LEFT JOIN users u ON n.author_id = u.id
                LEFT JOIN news_comments nc ON n.id = nc.news_id AND nc.status = 'visible'
                LEFT JOIN news_likes nl ON n.id = nl.news_id
                $where
                GROUP BY n.id
                $orderBy
                LIMIT ? OFFSET ?";
        
        if ($userId) {
            array_unshift($params, $userId);
        }
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }

    /**
     * Lấy tin tức theo ID
     */
    public function getById($id)
    {
        $sql = "SELECT n.*, u.full_name as author_name 
                FROM {$this->table} n
                LEFT JOIN users u ON n.author_id = u.id
                WHERE n.id = ?";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy tin tức theo slug
     */
    public function getBySlug($slug)
    {
        $sql = "SELECT n.*, u.full_name as author_name 
                FROM {$this->table} n
                LEFT JOIN users u ON n.author_id = u.id
                WHERE n.slug = ?";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Tạo tin tức mới
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (title, slug, excerpt, content, image, author_id, category, status, published_at, meta_title, meta_description) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->getConnection()->prepare($sql);
        return $stmt->execute([
            $data['title'],
            $data['slug'],
            $data['excerpt'] ?? null,
            $data['content'],
            $data['image'] ?? null,
            $data['author_id'],
            $data['category'] ?? null,
            $data['status'] ?? 'draft',
            $data['published_at'] ?? null,
            $data['meta_title'] ?? null,
            $data['meta_description'] ?? null
        ]);
    }

    /**
     * Cập nhật tin tức
     */
    public function update($id, $data)
    {
        // Nếu không có ảnh mới, giữ nguyên ảnh cũ
        if (isset($data['image']) && !empty($data['image'])) {
            $sql = "UPDATE {$this->table} 
                    SET title = ?, slug = ?, excerpt = ?, content = ?, image = ?, 
                        category = ?, status = ?, published_at = ?, 
                        meta_title = ?, meta_description = ?
                    WHERE id = ?";
            
            $params = [
                $data['title'],
                $data['slug'],
                $data['excerpt'] ?? null,
                $data['content'],
                $data['image'],
                $data['category'] ?? null,
                $data['status'] ?? 'draft',
                $data['published_at'] ?? null,
                $data['meta_title'] ?? null,
                $data['meta_description'] ?? null,
                $id
            ];
        } else {
            $sql = "UPDATE {$this->table} 
                    SET title = ?, slug = ?, excerpt = ?, content = ?, 
                        category = ?, status = ?, published_at = ?, 
                        meta_title = ?, meta_description = ?
                    WHERE id = ?";
            
            $params = [
                $data['title'],
                $data['slug'],
                $data['excerpt'] ?? null,
                $data['content'],
                $data['category'] ?? null,
                $data['status'] ?? 'draft',
                $data['published_at'] ?? null,
                $data['meta_title'] ?? null,
                $data['meta_description'] ?? null,
                $id
            ];
        }

        $stmt = $this->db->getConnection()->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Xóa tin tức và ảnh liên quan
     */
    public function delete($id)
    {
        // Lấy thông tin tin tức để xóa ảnh
        $news = $this->getById($id);
        
        if ($news) {
            // Xóa ảnh nếu có
            if (!empty($news['image'])) {
                $imagePath = ROOT_PATH . '/public/' . $news['image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }

            // Xóa tin tức
            $sql = "DELETE FROM {$this->table} WHERE id = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            return $stmt->execute([$id]);
        }

        return false;
    }

    /**
     * Tăng lượt xem
     */
    public function incrementViews($id)
    {
        $sql = "UPDATE {$this->table} SET views = views + 1 WHERE id = ?";
        $stmt = $this->db->getConnection()->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Lấy danh sách tác giả (admin users)
     */
    public function getAuthors()
    {
        $sql = "SELECT id, full_name, email FROM users WHERE role IN ('admin', 'superadmin') ORDER BY full_name";
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Toggle like tin tức
     */
    public function toggleLike($news_id, $user_id)
    {
        try {
            // Kiểm tra đã like chưa
            $sql = "SELECT id FROM news_likes WHERE news_id = ? AND user_id = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$news_id, $user_id]);
            $existing = $stmt->fetch();

            if ($existing) {
                // Unlike
                $sql = "DELETE FROM news_likes WHERE news_id = ? AND user_id = ?";
                $stmt = $this->db->getConnection()->prepare($sql);
                $stmt->execute([$news_id, $user_id]);
                $liked = false;
            } else {
                // Like
                $sql = "INSERT INTO news_likes (news_id, user_id) VALUES (?, ?)";
                $stmt = $this->db->getConnection()->prepare($sql);
                $stmt->execute([$news_id, $user_id]);
                $liked = true;
            }

            // Đếm tổng likes
            $sql = "SELECT COUNT(*) as total FROM news_likes WHERE news_id = ?";
            $stmt = $this->db->getConnection()->prepare($sql);
            $stmt->execute([$news_id]);
            $likes_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

            return [
                'liked' => $liked,
                'likes_count' => $likes_count
            ];
        } catch (Exception $e) {
            error_log("Error in News::toggleLike: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Tạo slug từ tiêu đề
     */
    public static function createSlug($title)
    {
        // Chuyển chữ hoa thành chữ thường
        $slug = mb_strtolower($title, 'UTF-8');

        // Mảng chuyển đổi ký tự có dấu sang không dấu
        $vietnameseMap = array(
            'á' => 'a', 'à' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a',
            'ă' => 'a', 'ắ' => 'a', 'ằ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a',
            'â' => 'a', 'ấ' => 'a', 'ầ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a',
            'đ' => 'd',
            'é' => 'e', 'è' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e',
            'ê' => 'e', 'ế' => 'e', 'ề' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e',
            'í' => 'i', 'ì' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ị' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ọ' => 'o',
            'ô' => 'o', 'ố' => 'o', 'ồ' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ộ' => 'o',
            'ơ' => 'o', 'ớ' => 'o', 'ờ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'ợ' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ụ' => 'u',
            'ư' => 'u', 'ứ' => 'u', 'ừ' => 'u', 'ử' => 'u', 'ữ' => 'u', 'ự' => 'u',
            'ý' => 'y', 'ỳ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'ỵ' => 'y',
        );

        $slug = strtr($slug, $vietnameseMap);

        // Thay thế ký tự đặc biệt và khoảng trắng bằng dấu gạch ngang
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');

        return $slug;
    }
}