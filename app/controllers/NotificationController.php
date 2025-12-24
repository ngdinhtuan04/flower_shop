<?php
/**
 * NotificationController
 * Quản lý thông báo của user
 */
class NotificationController extends Controller {
    
    protected $notificationModel;
    
    public function __construct() {
        $this->notificationModel = new Notification();
        
        // Require login for non-AJAX requests
        // AJAX methods handle authentication themselves
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        if (!isset($_SESSION['user_id']) && !$isAjax) {
            $_SESSION['flash_error'] = 'Vui lòng đăng nhập để xem thông báo';
            header('Location: /user/login');
            exit;
        }
    }
    
    /**
     * Trang danh sách thông báo
     */
    public function index() {
        $userId = $_SESSION['user_id'];
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $notifications = $this->notificationModel->getByUser($userId, $limit, $offset);
        $totalNotifications = $this->notificationModel->countByUser($userId);
        $totalPages = ceil($totalNotifications / $limit);
        
        $this->view('users/notifications', [
            'pageTitle' => 'Thông báo',
            'notifications' => $notifications,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ]);
    }
    
    /**
     * Đánh dấu đã đọc (AJAX)
     */
    public function markAsRead() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        
        // Check authentication for AJAX
        if (!isset($_SESSION['user_id'])) {
            $this->json(['success' => false, 'message' => 'Vui lòng đăng nhập']);
        }
        
        $userId = $_SESSION['user_id'];
        $notificationId = $_POST['id'] ?? null;
        
        if (!$notificationId) {
            $this->json(['success' => false, 'message' => 'Thiếu ID thông báo'], 400);
        }
        
        $result = $this->notificationModel->markAsRead($notificationId, $userId);
        
        $this->json([
            'success' => $result,
            'unreadCount' => $this->notificationModel->countUnread($userId)
        ]);
    }
    
    /**
     * Đánh dấu tất cả đã đọc (AJAX)
     */
    public function markAllAsRead() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        
        // Check authentication for AJAX
        if (!isset($_SESSION['user_id'])) {
            $this->json(['success' => false, 'message' => 'Vui lòng đăng nhập']);
        }
        
        $userId = $_SESSION['user_id'];
        $result = $this->notificationModel->markAllAsRead($userId);
        
        $this->json([
            'success' => $result,
            'unreadCount' => 0
        ]);
    }
    
    /**
     * Xóa thông báo
     */
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['flash_error'] = 'Invalid request';
            header('Location: ' . BASE_URL . '/notifications');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $notificationId = $_POST['id'] ?? null;
        
        if (!$notificationId) {
            $_SESSION['flash_error'] = 'Thiếu ID thông báo';
            header('Location: ' . BASE_URL . '/notifications');
            exit;
        }
        
        $result = $this->notificationModel->delete($notificationId, $userId);
        
        if ($result) {
            $_SESSION['flash_success'] = '✅ Đã xóa thông báo';
        } else {
            $_SESSION['flash_error'] = '❌ Không thể xóa thông báo';
        }
        
        header('Location: ' . BASE_URL . '/notifications');
        exit;
    }
    
    /**
     * Lấy số lượng chưa đọc (AJAX)
     */
    public function getUnreadCount() {
        // Check authentication for AJAX
        if (!isset($_SESSION['user_id'])) {
            $this->json(['unreadCount' => 0]);
        }
        
        $userId = $_SESSION['user_id'];
        $count = $this->notificationModel->countUnread($userId);
        
        $this->json(['unreadCount' => $count]);
    }
    
    /**
     * Lấy thông báo gần đây cho dropdown (AJAX)
     */
    public function getRecent() {
        // Check authentication for AJAX
        if (!isset($_SESSION['user_id'])) {
            $this->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập'
            ]);
        }
        
        $userId = $_SESSION['user_id'];
        $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 100) : 10;
        $notifications = $this->notificationModel->getRecent($userId, $limit);
        $unreadCount = $this->notificationModel->countUnread($userId);
        
        $this->json([
            'success' => true,
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
    }
    
    /**
     * Alias for markAllAsRead (kebab-case routing)
     */
    public function markAllRead() {
        return $this->markAllAsRead();
    }
}
