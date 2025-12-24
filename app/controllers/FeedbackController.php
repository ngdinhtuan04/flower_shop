<?php
/**
 * FeedbackController - Xử lý góp ý người dùng
 */
class FeedbackController extends Controller {
    private $feedbackModel;
    
    public function __construct() {
        $this->feedbackModel = $this->model('Feedback');
    }
    
    /**
     * Hiển thị form góp ý
     */
    public function index() {
        $user = Session::getUser();
        
        $data = [
            'user' => $user
        ];
        
        $this->view('feedback/index', $data);
    }
    
    /**
     * Xử lý gửi góp ý
     */
    public function submit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Session::setFlash('error', 'Phương thức không hợp lệ');
            header('Location: ' . BASE_URL . '/feedback');
            exit;
        }
        
        // Lấy thông tin người dùng (nếu đã đăng nhập)
        $userId = Session::isLoggedIn() ? Session::getUser()['id'] : null;
        
        // Validate dữ liệu
        $productId = !empty($_POST['product_id']) ? (int)$_POST['product_id'] : null;
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $type = $_POST['type'] ?? 'other';
        
        // Xác định redirect URL
        $redirectUrl = $productId ? BASE_URL . '/product/detail/' . $productId : BASE_URL . '/feedback';
        
        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            Session::setFlash('error', 'Vui lòng điền đầy đủ thông tin');
            header('Location: ' . $redirectUrl);
            exit;
        }
        
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::setFlash('error', 'Email không hợp lệ');
            header('Location: ' . $redirectUrl);
            exit;
        }
        
        // Tạo feedback
        $data = [
            'user_id' => $userId,
            'product_id' => $productId,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'subject' => $subject,
            'message' => $message,
            'type' => $type
        ];
        
        if ($this->feedbackModel->create($data)) {
            Session::setFlash('success', 'Cảm ơn bạn đã gửi góp ý! Chúng tôi sẽ phản hồi sớm nhất có thể.');
            // Nếu có product_id, redirect về trang sản phẩm
            if ($productId) {
                header('Location: ' . BASE_URL . '/product/detail/' . $productId);
            } else {
                header('Location: ' . BASE_URL . '/feedback');
            }
        } else {
            Session::setFlash('error', 'Gửi góp ý thất bại. Vui lòng thử lại');
            if ($productId) {
                header('Location: ' . BASE_URL . '/product/detail/' . $productId);
            } else {
                header('Location: ' . BASE_URL . '/feedback');
            }
        }
        exit;
    }
    
    /**
     * Lịch sử góp ý của người dùng
     */
    public function myFeedback() {
        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'Vui lòng đăng nhập để xem lịch sử góp ý');
            header('Location: ' . BASE_URL . '/user/login');
            exit;
        }
        
        $user = Session::getUser();
        $feedbacks = $this->feedbackModel->getByUserId($user['id']);
        
        $data = [
            'user' => $user,
            'feedbacks' => $feedbacks
        ];
        
        $this->view('feedback/my-feedback', $data);
    }
    
    /**
     * Chi tiết góp ý
     */
    public function detail($id) {
        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'Vui lòng đăng nhập');
            header('Location: ' . BASE_URL . '/user/login');
            exit;
        }
        
        $user = Session::getUser();
        $feedback = $this->feedbackModel->getById($id);
        
        // Kiểm tra quyền truy cập
        if (!$feedback || ($feedback['user_id'] != $user['id'] && !Session::isAdmin())) {
            Session::setFlash('error', 'Không tìm thấy góp ý');
            header('Location: ' . BASE_URL . '/feedback/my-feedback');
            exit;
        }
        
        $data = [
            'user' => $user,
            'feedback' => $feedback
        ];
        
        $this->view('feedback/detail', $data);
    }
}