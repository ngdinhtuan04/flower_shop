<?php

class NewsComment {
    private $db;
    
    public function __construct() {
        $this->db = DB::getInstance();
    }
    
    // Lấy tất cả bình luận của tin tức (kèm replies)
    public function getByNewsId($news_id) {
        try {
            // Lấy bình luận cha (parent_id = NULL)
            $sql = "SELECT nc.*, u.username, u.email 
                    FROM news_comments nc
                    LEFT JOIN users u ON nc.user_id = u.id
                    WHERE nc.news_id = :news_id 
                    AND nc.parent_id IS NULL 
                    AND nc.status = 'visible'
                    ORDER BY nc.created_at DESC";
            
            $stmt = $this->db->query($sql, ['news_id' => $news_id]);
            
            if (!$stmt) {
                return [];
            }
            
            $comments = $stmt->fetchAll();
            
            // Kiểm tra nếu không có kết quả
            if (!$comments || !is_array($comments)) {
                return [];
            }
            
            // Lấy replies cho mỗi comment
            foreach ($comments as &$comment) {
                $comment['replies'] = $this->getReplies($comment['id']);
            }
            
            return $comments;
        } catch (Exception $e) {
            error_log("Error in NewsComment::getByNewsId: " . $e->getMessage());
            return [];
        }
    }
    
    // Lấy replies của một comment
    private function getReplies($parent_id) {
        try {
            $sql = "SELECT nc.*, u.username, u.email 
                    FROM news_comments nc
                    LEFT JOIN users u ON nc.user_id = u.id
                    WHERE nc.parent_id = :parent_id 
                    AND nc.status = 'visible'
                    ORDER BY nc.created_at ASC";
            
            $stmt = $this->db->query($sql, ['parent_id' => $parent_id]);
            
            if (!$stmt) {
                return [];
            }
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error in NewsComment::getReplies: " . $e->getMessage());
            return [];
        }
    }
    
    // Thêm bình luận mới
    public function create($data) {
        try {
            // Phát hiện spam
            $is_spam = $this->detectSpam($data['content']);
            $status = $is_spam ? 'hidden' : 'visible';
            
            $sql = "INSERT INTO news_comments (news_id, user_id, parent_id, content, status, is_spam) 
                    VALUES (:news_id, :user_id, :parent_id, :content, :status, :is_spam)";
            
            $params = [
                'news_id' => $data['news_id'],
                'user_id' => $data['user_id'],
                'parent_id' => $data['parent_id'] ?? null,
                'content' => $data['content'],
                'status' => $status,
                'is_spam' => $is_spam ? 1 : 0
            ];
            
            $result = $this->db->execute($sql, $params);
            
            // Gửi thông báo nếu bị phát hiện spam
            if ($result && $is_spam) {
                $this->notifyUser($data['user_id'], 'spam_detected', 
                    'Bình luận của bạn bị ẩn do chứa nội dung spam. Vui lòng kiểm tra lại.');
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error in NewsComment::create: " . $e->getMessage());
            return false;
        }
    }
    
    // Phát hiện spam đơn giản
    private function detectSpam($content) {
        $spam_keywords = [
            'casino', 'viagra', 'porn', 'xxx', 'loan', 'credit card',
            'click here', 'buy now', 'limited offer', 'congratulations',
            'winner', 'free money', 'work from home'
        ];
        
        $content_lower = mb_strtolower($content, 'UTF-8');
        
        foreach ($spam_keywords as $keyword) {
            if (strpos($content_lower, $keyword) !== false) {
                return true;
            }
        }
        
        // Phát hiện quá nhiều link
        if (substr_count($content, 'http') > 2) {
            return true;
        }
        
        // Phát hiện text quá ngắn hoặc chỉ toàn ký tự đặc biệt
        if (strlen(trim($content)) < 5) {
            return true;
        }
        
        return false;
    }
    
    // Đếm số bình luận của tin tức
    public function countByNewsId($news_id) {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM news_comments 
                    WHERE news_id = :news_id 
                    AND status = 'visible'";
            
            $stmt = $this->db->query($sql, ['news_id' => $news_id]);
            
            if (!$stmt) {
                return 0;
            }
            
            $result = $stmt->fetch();
            return $result['total'] ?? 0;
        } catch (Exception $e) {
            error_log("Error in NewsComment::countByNewsId: " . $e->getMessage());
            return 0;
        }
    }
    
    // Lấy bình luận theo ID
    public function getById($id) {
        try {
            $sql = "SELECT nc.*, u.username, u.email 
                    FROM news_comments nc
                    LEFT JOIN users u ON nc.user_id = u.id
                    WHERE nc.id = :id";
            
            $stmt = $this->db->query($sql, ['id' => $id]);
            
            if (!$stmt) {
                return null;
            }
            
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error in NewsComment::getById: " . $e->getMessage());
            return null;
        }
    }
    
    // Ẩn bình luận với lý do
    public function hideComment($id, $reason, $admin_id) {
        try {
            // Lấy thông tin comment và user
            $comment = $this->getById($id);
            if (!$comment) return false;
            
            $sql = "UPDATE news_comments SET status = 'hidden', admin_reason = :reason WHERE id = :id";
            $result = $this->db->execute($sql, ['id' => $id, 'reason' => $reason]);
            
            if ($result) {
                // Gửi thông báo đến người dùng
                $this->notifyUser($comment['user_id'], 'comment_hidden', 
                    "Bình luận của bạn đã bị ẩn. Lý do: {$reason}");
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error in NewsComment::hideComment: " . $e->getMessage());
            return false;
        }
    }
    
    // Xóa bình luận với lý do
    public function deleteWithReason($id, $reason, $admin_id) {
        try {
            // Lấy thông tin comment và user
            $comment = $this->getById($id);
            if (!$comment) return false;
            
            // Đánh dấu deleted thay vì xóa hẳn
            $sql = "UPDATE news_comments SET status = 'deleted', admin_reason = :reason WHERE id = :id";
            $result = $this->db->execute($sql, ['id' => $id, 'reason' => $reason]);
            
            if ($result) {
                // Gửi thông báo đến người dùng
                $this->notifyUser($comment['user_id'], 'comment_deleted', 
                    "Bình luận của bạn đã bị xóa. Lý do: {$reason}");
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error in NewsComment::deleteWithReason: " . $e->getMessage());
            return false;
        }
    }
    
    // Gửi thông báo đến người dùng
    private function notifyUser($user_id, $type, $message) {
        try {
            $notificationModel = new Notification();
            $data = [
                'user_id' => $user_id,
                'type' => $type,
                'title' => 'Thông báo về bình luận',
                'message' => $message,
                'link' => BASE_URL . '/users/profile'
            ];
            return $notificationModel->create($data);
        } catch (Exception $e) {
            error_log("Error in NewsComment::notifyUser: " . $e->getMessage());
            return false;
        }
    }
    
    // Xóa bình luận
    public function delete($id) {
        try {
            $sql = "DELETE FROM news_comments WHERE id = :id";
            return $this->db->execute($sql, ['id' => $id]);
        } catch (Exception $e) {
            error_log("Error in NewsComment::delete: " . $e->getMessage());
            return false;
        }
    }
    
    // Cập nhật trạng thái bình luận
    public function updateStatus($id, $status) {
        try {
            $sql = "UPDATE news_comments SET status = :status WHERE id = :id";
            return $this->db->execute($sql, ['id' => $id, 'status' => $status]);
        } catch (Exception $e) {
            error_log("Error in NewsComment::updateStatus: " . $e->getMessage());
            return false;
        }
    }
    
    // Đánh dấu spam
    public function markAsSpam($id, $admin_id) {
        try {
            $comment = $this->getById($id);
            if (!$comment) return false;
            
            $sql = "UPDATE news_comments SET is_spam = 1, status = 'hidden', admin_reason = 'Nội dung spam' WHERE id = :id";
            $result = $this->db->execute($sql, ['id' => $id]);
            
            if ($result) {
                $this->notifyUser($comment['user_id'], 'spam_warning', 
                    'Bình luận của bạn bị đánh dấu spam. Vui lòng không spam để tránh bị khóa tài khoản.');
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Error in NewsComment::markAsSpam: " . $e->getMessage());
            return false;
        }
    }

    // Lấy tất cả bình luận cho admin (có phân trang và filter)
    public function getAllForAdmin($page = 1, $limit = 20, $filters = [])
    {
        try {
            $offset = ($page - 1) * $limit;
            $conditions = [];
            $params = [];

            // Lọc theo trạng thái
            if (isset($filters['status']) && $filters['status'] !== '') {
                $conditions[] = "nc.status = :status";
                $params['status'] = $filters['status'];
            }

            // Lọc theo tin tức
            if (isset($filters['news_id']) && $filters['news_id'] !== '') {
                $conditions[] = "nc.news_id = :news_id";
                $params['news_id'] = $filters['news_id'];
            }

            // Tìm kiếm
            if (isset($filters['search']) && $filters['search'] !== '') {
                $conditions[] = "(nc.content LIKE :search OR u.username LIKE :search OR n.title LIKE :search)";
                $params['search'] = '%' . $filters['search'] . '%';
            }

            $where = count($conditions) > 0 ? 'WHERE ' . implode(' AND ', $conditions) : '';

            // Đếm tổng số
            $countSql = "SELECT COUNT(*) as total 
                        FROM news_comments nc
                        LEFT JOIN users u ON nc.user_id = u.id
                        LEFT JOIN news n ON nc.news_id = n.id
                        $where";
            
            $stmt = $this->db->query($countSql, $params);
            if (!$stmt) {
                return ['data' => [], 'total' => 0, 'page' => 1, 'total_pages' => 0];
            }
            $total = $stmt->fetch()['total'];

            // Lấy dữ liệu
            $sql = "SELECT nc.*, 
                    u.username, u.email,
                    n.title as news_title, n.slug as news_slug,
                    parent.content as parent_content,
                    parent_user.username as parent_username
                    FROM news_comments nc
                    LEFT JOIN users u ON nc.user_id = u.id
                    LEFT JOIN news n ON nc.news_id = n.id
                    LEFT JOIN news_comments parent ON nc.parent_id = parent.id
                    LEFT JOIN users parent_user ON parent.user_id = parent_user.id
                    $where
                    ORDER BY nc.created_at DESC
                    LIMIT :limit OFFSET :offset";
            
            $params['limit'] = $limit;
            $params['offset'] = $offset;
            
            $stmt = $this->db->query($sql, $params);
            
            if (!$stmt) {
                return ['data' => [], 'total' => 0, 'page' => 1, 'total_pages' => 0];
            }
            
            $data = $stmt->fetchAll();

            return [
                'data' => $data,
                'total' => $total,
                'page' => $page,
                'total_pages' => ceil($total / $limit)
            ];
        } catch (Exception $e) {
            error_log("Error in NewsComment::getAllForAdmin: " . $e->getMessage());
            return ['data' => [], 'total' => 0, 'page' => 1, 'total_pages' => 0];
        }
    }

    // Lấy thống kê bình luận
    public function getStats()
    {
        try {
            $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'visible' THEN 1 ELSE 0 END) as visible,
                    SUM(CASE WHEN status = 'hidden' THEN 1 ELSE 0 END) as hidden,
                    SUM(CASE WHEN status = 'deleted' THEN 1 ELSE 0 END) as deleted,
                    SUM(CASE WHEN is_spam = 1 THEN 1 ELSE 0 END) as spam
                    FROM news_comments";
            
            $stmt = $this->db->query($sql);
            
            if (!$stmt) {
                return ['total' => 0, 'visible' => 0, 'hidden' => 0, 'deleted' => 0, 'spam' => 0];
            }
            
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error in NewsComment::getStats: " . $e->getMessage());
            return ['total' => 0, 'visible' => 0, 'hidden' => 0, 'deleted' => 0, 'spam' => 0];
        }
    }
}
