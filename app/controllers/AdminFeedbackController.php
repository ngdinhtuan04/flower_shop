<?php
/**
 * AdminFeedbackController
 * Xử lý các chức năng quản lý góp ý cho admin
 * - Danh sách góp ý
 * - Chi tiết góp ý
 * - Trả lời góp ý
 * - Cập nhật trạng thái góp ý
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Session.php';

class AdminFeedbackController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = $this->model('User');
    }
    
    /**
     * Quản lý góp ý
     */
    public function feedback()
    {
        $this->requireAdmin();
        
        $feedbackModel = $this->model('Feedback');
        
        // Lọc
        $filters = [
            'status' => $_GET['status'] ?? '',
            'type' => $_GET['type'] ?? ''
        ];
        
        $feedbacks = $feedbackModel->getAll($filters);
        $statusCounts = $feedbackModel->countByStatus();
        
        $data = [
            'user' => Session::getUser(),
            'feedbacks' => $feedbacks,
            'filters' => $filters,
            'statusCounts' => $statusCounts
        ];
        
        $this->view('admin/feedback/index', $data);
    }
    
    /**
     * Chi tiết góp ý
     */
    public function feedbackDetail($id)
    {
        $this->requireAdmin();
        
        $feedbackModel = $this->model('Feedback');
        $feedback = $feedbackModel->getById($id);
        
        if (!$feedback) {
            Session::setFlash('error', 'Không tìm thấy góp ý');
            $this->redirect('/admin/feedback');
            return;
        }
        
        $data = [
            'user' => Session::getUser(),
            'feedback' => $feedback
        ];
        
        $this->view('admin/feedback/detail', $data);
    }
    
    /**
     * Trả lời góp ý
     */
    public function feedbackReply()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/feedback');
            return;
        }
        
        $feedbackId = $_POST['feedback_id'] ?? 0;
        $reply = trim($_POST['reply'] ?? '');
        
        if (empty($reply)) {
            Session::setFlash('error', 'Nội dung phản hồi không được để trống');
            $this->redirect('/admin/feedback/detail/' . $feedbackId);
            return;
        }
        
        $feedbackModel = $this->model('Feedback');
        $adminId = Session::get('user_id');
        
        $result = $feedbackModel->addReply($feedbackId, $reply, $adminId);
        
        if ($result) {
            // Tạo notification cho user (chỉ khi user đã đăng nhập)
            $feedback = $feedbackModel->getById($feedbackId);
            if ($feedback && !empty($feedback['user_id'])) {
                try {
                    $notificationModel = $this->model('Notification');
                    $created = $notificationModel->create([
                        'user_id' => $feedback['user_id'],
                        'type' => 'feedback_reply',
                        'title' => 'Admin đã phản hồi góp ý của bạn',
                        'message' => 'Góp ý "' . htmlspecialchars($feedback['subject']) . '" đã nhận được phản hồi từ admin.',
                        'link' => '/feedback/my-feedback'
                    ]);
                    error_log("Notification created: " . ($created ? "YES" : "NO") . " for user_id: " . $feedback['user_id']);
                } catch (Exception $e) {
                    error_log("Failed to create notification: " . $e->getMessage());
                }
            } else {
                error_log("No user_id for feedback: " . $feedbackId);
            }
            
            Session::setFlash('success', 'Gửi phản hồi thành công');
        } else {
            Session::setFlash('error', 'Gửi phản hồi thất bại');
        }
        
        $this->redirect('/admin/feedback/detail/' . $feedbackId);
    }
    
    /**
     * Cập nhật trạng thái góp ý
     */
    public function updateFeedbackStatus()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/feedback');
            return;
        }
        
        $feedbackId = $_POST['feedback_id'] ?? 0;
        $status = $_POST['status'] ?? '';
        
        $feedbackModel = $this->model('Feedback');
        $result = $feedbackModel->updateStatus($feedbackId, $status);
        
        if ($result) {
            Session::setFlash('success', 'Cập nhật trạng thái thành công');
        } else {
            Session::setFlash('error', 'Cập nhật trạng thái thất bại');
        }
        
        $this->redirect('/admin/feedback/detail/' . $feedbackId);
    }
}
