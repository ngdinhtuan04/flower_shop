<?php
/**
 * ReviewAdminController - Quản lý đánh giá (Admin)
 * Hỗ trợ cả hide/show và approve/reject
 */
class ReviewAdminController extends Controller
{
    private $reviewModel;

    public function __construct()
    {
        // Kiểm tra quyền admin
        if (!Session::isLoggedIn() || !Session::isAdmin()) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }
        
        $this->reviewModel = $this->model('Review');
    }

    /**
     * Trang quản lý đánh giá
     */
    public function index()
    {
        // Lấy tham số lọc
        $filters = [
            'search' => $_GET['search'] ?? '',
            'rating' => $_GET['rating'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // Lấy danh sách đánh giá với filter
        $reviews = $this->reviewModel->getAllWithFilters($filters, $limit, $offset);
        $totalReviews = $this->reviewModel->countWithFilters($filters);
        
        // Thống kê
        $ratingStats = $this->reviewModel->getRatingStatistics();
        
        $totalPages = ceil($totalReviews / $limit);
        
        $data = [
            'reviews' => $reviews,
            'filters' => $filters,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalReviews' => $totalReviews,
            'ratingStats' => $ratingStats,
            'user' => Session::getUser()
        ];
        
        $this->view('admin/reviews/index', $data);
    }

    /**
     * Duyệt đánh giá (approve)
     */
    public function approve()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/reviews');
            exit;
        }
        
        $reviewId = $_POST['review_id'] ?? 0;
        $adminId = Session::getUser()['id'];
        
        if ($reviewId) {
            // Sử dụng approveReview
            $result = $this->reviewModel->approveReview($reviewId, $adminId);
            
            if ($result) {
                Session::setFlash('success', '✅ Đã duyệt đánh giá - Hiển thị công khai');
            } else {
                Session::setFlash('error', '❌ Có lỗi xảy ra khi duyệt đánh giá');
            }
        }
        
        header('Location: ' . BASE_URL . '/admin/reviews');
        exit;
    }

    /**
     * Từ chối đánh giá (reject)
     */
    public function reject()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/reviews');
            exit;
        }
        
        $reviewId = $_POST['review_id'] ?? 0;
        $adminNote = $_POST['admin_note'] ?? 'Đánh giá không phù hợp với tiêu chuẩn cộng đồng';
        $adminId = Session::getUser()['id'];
        
        if ($reviewId) {
            // Sử dụng rejectReview
            $result = $this->reviewModel->rejectReview($reviewId, $adminId, $adminNote);
            
            if ($result) {
                Session::setFlash('success', '🚫 Đã từ chối đánh giá với lý do: ' . $adminNote);
            } else {
                Session::setFlash('error', '❌ Có lỗi xảy ra khi từ chối đánh giá');
            }
        }
        
        header('Location: ' . BASE_URL . '/admin/reviews');
        exit;
    }

    /**
     * Ẩn đánh giá với lý do cụ thể
     */
    public function hide($reviewId = null)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request']);
            return;
        }
        
        $reviewId = $reviewId ?? ($_POST['review_id'] ?? 0);
        $adminNote = sanitize($_POST['admin_note'] ?? 'Đánh giá không phù hợp');
        $adminId = Session::getUser()['id'];
        
        if (!$reviewId) {
            $this->jsonResponse(['success' => false, 'message' => 'Không tìm thấy đánh giá']);
            return;
        }
        
        $result = $this->reviewModel->hideReview($reviewId, $adminId, $adminNote);
        
        if ($result) {
            $this->jsonResponse([
                'success' => true, 
                'message' => 'Đã ẩn đánh giá thành công'
            ]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    /**
     * Hiển thị đánh giá
     */
    public function show($reviewId = null)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request']);
            return;
        }
        
        $reviewId = $reviewId ?? ($_POST['review_id'] ?? 0);
        $adminId = Session::getUser()['id'];
        
        if (!$reviewId) {
            $this->jsonResponse(['success' => false, 'message' => 'Không tìm thấy đánh giá']);
            return;
        }
        
        $result = $this->reviewModel->showReview($reviewId, $adminId);
        
        if ($result) {
            $this->jsonResponse([
                'success' => true, 
                'message' => 'Đã hiển thị đánh giá'
            ]);
        } else {
            $this->jsonResponse(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    /**
     * Xóa đánh giá vĩnh viễn
     */
    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/reviews');
            exit;
        }
        
        $reviewId = $_POST['review_id'] ?? 0;
        
        if ($reviewId) {
            $result = $this->reviewModel->delete($reviewId);
            
            if ($result) {
                Session::setFlash('success', 'Đã xóa đánh giá thành công');
            } else {
                Session::setFlash('error', 'Có lỗi xảy ra khi xóa đánh giá');
            }
        }
        
        header('Location: ' . BASE_URL . '/admin/reviews');
        exit;
    }

    /**
     * Helper function for JSON response
     */
    private function jsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
