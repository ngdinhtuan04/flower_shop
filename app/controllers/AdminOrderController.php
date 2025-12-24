<?php
/**
 * AdminOrderController
 * Xử lý các chức năng quản lý đơn hàng cho admin
 * - Danh sách đơn hàng
 * - Chi tiết đơn hàng
 * - Cập nhật trạng thái đơn hàng
 * - Cập nhật trạng thái thanh toán
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Session.php';

class AdminOrderController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = $this->model('User');
    }
    
    /**
     * Quản lý đơn hàng
     */
    public function orders()
    {
        $this->requireAdmin();
        
        $orderModel = $this->model('Order');
        
        // Lọc
        $filters = [
            'status' => $_GET['status'] ?? '',
            'payment_method' => $_GET['payment_method'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];
        
        $orders = $orderModel->getAllOrders($filters);
        
        $data = [
            'user' => Session::getUser(),
            'orders' => $orders,
            'filters' => $filters
        ];
        
        $this->view('admin/orders/index', $data);
    }
    
    /**
     * Chi tiết đơn hàng
     */
    public function orderDetail($id)
    {
        $this->requireAdmin();
        
        $orderModel = $this->model('Order');
        $order = $orderModel->getOrderById($id);
        
        if (!$order) {
            Session::setFlash('error', 'Không tìm thấy đơn hàng');
            $this->redirect('/admin/orders');
            return;
        }
        
        $orderItems = $orderModel->getOrderItems($id);
        
        $data = [
            'user' => Session::getUser(),
            'order' => $order,
            'orderItems' => $orderItems
        ];
        
        $this->view('admin/orders/detail', $data);
    }
    
    /**
     * Chi tiết đơn hàng (JSON API)
     */
    public function orderDetailJson($id)
    {
        $this->requireAdmin();
        
        header('Content-Type: application/json');
        
        $orderModel = $this->model('Order');
        $order = $orderModel->getOrderById($id);
        
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy đơn hàng']);
            exit;
        }
        
        $orderItems = $orderModel->getOrderItems($id);
        
        echo json_encode([
            'success' => true,
            'order' => $order,
            'items' => $orderItems
        ]);
        exit;
    }

    /**
     * Cập nhật trạng thái đơn hàng
     */
    public function updateOrderStatus()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/orders');
            return;
        }
        
        $orderId = $_POST['order_id'] ?? 0;
        $newStatus = $_POST['status'] ?? '';
        
        $orderModel = $this->model('Order');
        $order = $orderModel->getOrderById($orderId);
        
        if (!$order) {
            $this->sendJsonOrRedirect(false, 'Không tìm thấy đơn hàng', $orderId);
            return;
        }
        
        $currentStatus = $order['order_status'];
        $isPickup = $order['shipping_method'] === 'pickup';
        
        // Định nghĩa flow trạng thái hợp lệ
        $statusFlow = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['processing', 'cancelled'],
            'processing' => $isPickup ? ['ready', 'cancelled'] : ['shipping', 'cancelled'],
            'shipping' => ['delivered', 'cancelled'],
            'ready' => ['picked_up', 'cancelled'],
            'delivered' => [],
            'picked_up' => [],
            'cancelled' => []
        ];
        
        // Kiểm tra trạng thái mới có hợp lệ không
        $allowedStatuses = $statusFlow[$currentStatus] ?? [];
        if ($newStatus !== $currentStatus && !in_array($newStatus, $allowedStatuses)) {
            $this->sendJsonOrRedirect(false, 'Không thể chuyển từ trạng thái "' . $currentStatus . '" sang "' . $newStatus . '"', $orderId);
            return;
        }
        
        // Nếu không thay đổi gì thì không cần update
        if ($newStatus === $currentStatus) {
            $this->sendJsonOrRedirect(true, 'Trạng thái không thay đổi', $orderId);
            return;
        }
        
        $result = $orderModel->updateOrderStatus($orderId, $newStatus);
        
        // Check if AJAX request
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        $isAjax = $isAjax || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
        
        if ($result) {
            // Gửi thông báo cho user
            $notificationModel = $this->model('Notification');
            $notificationModel->notifyOrderStatus($order['user_id'], $orderId, $newStatus);
            
            // Nếu đơn hàng được chuyển sang "Đã giao" hoặc "Đã lấy hàng" và là COD, tự động cập nhật trạng thái thanh toán
            if (in_array($newStatus, ['delivered', 'picked_up'])) {
                if ($order && strtolower($order['payment_method']) === 'cod' && $order['payment_status'] !== 'paid') {
                    $orderModel->updatePaymentStatus($orderId, 'paid', 'Auto-confirmed on delivery/pickup');
                    $message = 'Cập nhật trạng thái đơn hàng và thanh toán thành công';
                } else {
                    $message = 'Cập nhật trạng thái đơn hàng thành công';
                }
            } else {
                $message = 'Cập nhật trạng thái đơn hàng thành công';
            }
            
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => $message]);
                exit;
            }
            
            Session::setFlash('success', $message);
        } else {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Cập nhật trạng thái thất bại']);
                exit;
            }
            Session::setFlash('error', 'Cập nhật trạng thái thất bại');
        }
        
        $this->redirect('/admin/orders/detail/' . $orderId);
    }
    
    /**
     * Helper: Gửi JSON hoặc redirect tùy loại request
     */
    private function sendJsonOrRedirect($success, $message, $orderId = null)
    {
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        $isAjax = $isAjax || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
        
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => $success, 'message' => $message]);
            exit;
        }
        
        if ($success) {
            Session::setFlash('success', $message);
        } else {
            Session::setFlash('error', $message);
        }
        
        if ($orderId) {
            $this->redirect('/admin/orders/detail/' . $orderId);
        } else {
            $this->redirect('/admin/orders');
        }
    }
    
    /**
     * Cập nhật trạng thái thanh toán
     */
    public function updatePaymentStatus()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/orders');
            return;
        }
        
        $orderId = $_POST['order_id'] ?? 0;
        $paymentStatus = $_POST['payment_status'] ?? '';
        
        $orderModel = $this->model('Order');
        $paymentInfo = 'Manual confirmation by admin: ' . Session::getUser()['full_name'] . ' at ' . date('Y-m-d H:i:s');
        
        $result = $orderModel->updatePaymentStatus($orderId, $paymentStatus, $paymentInfo);
        
        if ($result) {
            Session::setFlash('success', 'Cập nhật trạng thái thanh toán thành công');
        } else {
            Session::setFlash('error', 'Cập nhật trạng thái thanh toán thất bại');
        }
        
        $this->redirect('/admin/orders/detail/' . $orderId);
    }
}
