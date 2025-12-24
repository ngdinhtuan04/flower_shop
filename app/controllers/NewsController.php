<?php
/**
 * NewsController - Xử lý hiển thị tin tức cho người dùng
 */
class NewsController extends Controller
{
    private $newsModel;
    private $commentModel;

    public function __construct()
    {
        $this->newsModel = $this->model('News');
        $this->commentModel = $this->model('NewsComment');
    }

    /**
     * Trang danh sách tin tức
     */
    public function index()
    {
        // Lấy tham số lọc và phân trang
        $page = intval($_GET['page'] ?? 1);
        $limit = 12; // Hiển thị 12 tin tức mỗi trang
        
        $filters = [
            'status' => 'published', // Chỉ lấy tin tức đã xuất bản
            'category' => $_GET['category'] ?? '',
            'search' => $_GET['search'] ?? '',
            'sort' => $_GET['sort'] ?? 'latest'
        ];

        // Lấy dữ liệu tin tức
        $result = $this->newsModel->getAll($page, $limit, $filters);

        $data = [
            'news' => $result['data'],
            'pagination' => [
                'current_page' => $result['page'],
                'total_pages' => $result['total_pages'],
                'total' => $result['total']
            ],
            'filters' => $filters
        ];

        $this->view('news/index', $data);
    }

    /**
     * Trang chi tiết tin tức
     */
    public function detail($slug = null)
    {
        if (!$slug) {
            $this->redirect('/news');
            return;
        }

        // Lấy tin tức theo slug
        $news = $this->newsModel->getBySlug($slug);

        if (!$news || $news['status'] !== 'published') {
            $this->view('errors/404');
            return;
        }

        // Tăng lượt xem
        $this->newsModel->incrementViews($news['id']);

        // Lấy tin tức liên quan (cùng danh mục)
        $relatedNews = [];
        if ($news['category']) {
            $result = $this->newsModel->getAll(1, 4, [
                'status' => 'published',
                'category' => $news['category']
            ]);
            $relatedNews = array_filter($result['data'], function($item) use ($news) {
                return $item['id'] !== $news['id'];
            });
            $relatedNews = array_slice($relatedNews, 0, 3);
        }

        // Lấy bình luận
        $comments = $this->commentModel->getByNewsId($news['id']);

        $data = [
            'news' => $news,
            'related_news' => $relatedNews,
            'comments' => $comments
        ];

        $this->view('news/detail', $data);
    }

    /**
     * Xử lý thêm bình luận
     */
    public function comment($news_id = null)
    {
        if (!$news_id) {
            $this->redirect('/news');
            return;
        }

        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Vui lòng đăng nhập để bình luận';
            $this->redirect('/users/login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $content = trim($_POST['content'] ?? '');
            $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
            
            if (empty($content)) {
                $_SESSION['error'] = 'Nội dung bình luận không được để trống';
                // Lấy slug từ referer để redirect về đúng trang
                $news = $this->newsModel->getById($news_id);
                $slug = $news['slug'] ?? '';
                $this->redirect('/news/detail/' . $slug);
                return;
            }

            $data = [
                'news_id' => $news_id,
                'user_id' => $_SESSION['user_id'],
                'parent_id' => $parent_id,
                'content' => $content,
                'status' => 'approved' // Tự động duyệt
            ];

            if ($this->commentModel->create($data)) {
                $_SESSION['success'] = $parent_id ? 'Trả lời của bạn đã được gửi!' : 'Bình luận của bạn đã được gửi thành công!';
            } else {
                $_SESSION['error'] = 'Có lỗi xảy ra, vui lòng thử lại';
            }

            // Lấy slug để redirect về đúng trang chi tiết
            $news = $this->newsModel->getById($news_id);
            $slug = $news['slug'] ?? '';
            $this->redirect('/news/detail/' . $slug);
        }
    }

    /**
     * Xóa bình luận
     */
    public function deleteComment($comment_id = null)
    {
        header('Content-Type: application/json');
        
        if (!$comment_id) {
            echo json_encode(['success' => false, 'message' => 'ID bình luận không hợp lệ']);
            exit;
        }

        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            exit;
        }

        // Kiểm tra quyền sở hữu bình luận
        $comment = $this->commentModel->getById($comment_id);
        
        if (!$comment || $comment['user_id'] != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền xóa bình luận này']);
            exit;
        }

        if ($this->commentModel->delete($comment_id)) {
            echo json_encode(['success' => true, 'message' => 'Đã xóa bình luận']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
        exit;
    }

    /**
     * Toggle like tin tức
     */
    public function toggleLike($news_id = null)
    {
        header('Content-Type: application/json');
        
        if (!$news_id) {
            echo json_encode(['success' => false, 'message' => 'ID tin tức không hợp lệ']);
            exit;
        }

        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
            exit;
        }

        $result = $this->newsModel->toggleLike($news_id, $_SESSION['user_id']);

        if ($result) {
            echo json_encode([
                'success' => true,
                'liked' => $result['liked'],
                'likes_count' => $result['likes_count']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
        exit;
    }

    /**
     * API lấy tin tức mới nhất (dùng cho AJAX)
     */
    public function latest($limit = 5)
    {
        $result = $this->newsModel->getAll(1, intval($limit), [
            'status' => 'published'
        ]);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $result['data']
        ]);
        exit;
    }

    /**
     * Trang theo danh mục
     */
    public function category($categorySlug = null)
    {
        $categoryMap = [
            'tips' => 'Mẹo hay',
            'events' => 'Sự kiện',
            'promotion' => 'Khuyến mãi'
        ];

        if (!$categorySlug || !isset($categoryMap[$categorySlug])) {
            $this->redirect('/news');
            return;
        }

        $page = intval($_GET['page'] ?? 1);
        $limit = 12;
        
        $filters = [
            'status' => 'published',
            'category' => $categorySlug,
            'search' => $_GET['search'] ?? ''
        ];

        $result = $this->newsModel->getAll($page, $limit, $filters);

        $data = [
            'news' => $result['data'],
            'category_name' => $categoryMap[$categorySlug],
            'category_slug' => $categorySlug,
            'pagination' => [
                'current_page' => $result['page'],
                'total_pages' => $result['total_pages'],
                'total' => $result['total']
            ],
            'filters' => $filters
        ];

        $this->view('news/category', $data);
    }
}