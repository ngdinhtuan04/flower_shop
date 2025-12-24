<?php
class ReviewController extends Controller {
    private $reviewModel;
    private $orderModel;
    private $productModel;
    
    public function __construct() {
        $this->reviewModel = $this->model('Review');
        $this->orderModel = $this->model('Order');
        $this->productModel = $this->model('Product');
    }
    
    /**
     * Hiển thị form đánh giá sản phẩm
     */
    public function create($productId, $orderId) {
        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'Vui lòng đăng nhập để đánh giá sản phẩm');
            header('Location: ' . BASE_URL . '/user/login');
            exit;
        }
        
        $user = Session::getUser();
        
        // Kiểm tra đã đánh giá chưa
        if ($this->reviewModel->hasReviewed($user['id'], $productId, $orderId)) {
            Session::setFlash('error', 'Bạn đã đánh giá sản phẩm này rồi');
            header('Location: ' . BASE_URL . '/orders/detail/' . $orderId);
            exit;
        }
        
        // Kiểm tra quyền đánh giá
        $order = $this->orderModel->getOrderById($orderId);
        if (!$order || $order['user_id'] != $user['id'] || $order['order_status'] != 'delivered') {
            Session::setFlash('error', 'Bạn chỉ có thể đánh giá sản phẩm trong đơn hàng đã giao thành công');
            header('Location: ' . BASE_URL . '/orders/history');
            exit;
        }
        
        $product = $this->productModel->getById($productId);
        
        if (!$product) {
            Session::setFlash('error', 'Sản phẩm không tồn tại');
            header('Location: ' . BASE_URL . '/orders/history');
            exit;
        }
        
        $data = [
            'user' => $user,
            'product' => $product,
            'order' => $order
        ];
        
        $this->view('reviews/create', $data);
    }
    
    /**
     * Xử lý submit đánh giá
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/orders/history');
            exit;
        }
        
        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'Vui lòng đăng nhập');
            header('Location: ' . BASE_URL . '/user/login');
            exit;
        }
        
        $user = Session::getUser();
        $productId = (int)($_POST['product_id'] ?? 0);
        $orderId = (int)($_POST['order_id'] ?? 0);
        $rating = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');
        
        // Validation
        if ($rating < 1 || $rating > 5) {
            Session::setFlash('error', 'Vui lòng chọn số sao đánh giá');
            header('Location: ' . BASE_URL . '/review/create/' . $productId . '/' . $orderId);
            exit;
        }
        
        if (empty($comment)) {
            Session::setFlash('error', 'Vui lòng nhập nội dung đánh giá');
            header('Location: ' . BASE_URL . '/review/create/' . $productId . '/' . $orderId);
            exit;
        }
        
        // Kiểm tra đã đánh giá chưa
        if ($this->reviewModel->hasReviewed($user['id'], $productId, $orderId)) {
            Session::setFlash('error', 'Bạn đã đánh giá sản phẩm này rồi');
            header('Location: ' . BASE_URL . '/orders/detail/' . $orderId);
            exit;
        }
        
        // Tạo đánh giá
        $reviewData = [
            'user_id' => $user['id'],
            'product_id' => $productId,
            'order_id' => $orderId,
            'rating' => $rating,
            'comment' => $comment
        ];
        
        $result = $this->reviewModel->create($reviewData);
        
        if ($result) {
            Session::setFlash('success', 'Cảm ơn bạn đã đánh giá sản phẩm!');
            header('Location: ' . BASE_URL . '/product/detail/' . $productId);
        } else {
            Session::setFlash('error', 'Có lỗi xảy ra, vui lòng thử lại');
            header('Location: ' . BASE_URL . '/review/create/' . $productId . '/' . $orderId);
        }
        exit;
    }
}
