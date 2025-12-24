<?php
/**
 * TrackingController - Tra cứu đơn hàng
 */
class TrackingController extends Controller {
    private $orderModel;
    
    public function __construct() {
        $this->orderModel = $this->model('Order');
    }
    
    // Hiển thị form tra cứu
    public function index() {
        $data = [
            'order' => null,
            'orderItems' => null,
            'error' => null
        ];
        
        // Nếu có query string từ email
        if (isset($_GET['code'])) {
            $orderCode = trim($_GET['code']);
            $order = $this->orderModel->getOrderByCode($orderCode);
            
            if ($order) {
                $data['order'] = $order;
                $data['orderItems'] = $this->orderModel->getOrderItems($order['id']);
            }
        }
        
        $this->view('orders/tracking', $data);
    }
    
    // Xử lý tra cứu đơn hàng
    public function search() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/tracking');
            exit;
        }
        
        $orderCode = strtoupper(trim($_POST['order_code'] ?? ''));
        $phone = trim($_POST['phone'] ?? '');
        
        if (empty($orderCode) || empty($phone)) {
            $data = [
                'order' => null,
                'orderItems' => null,
                'error' => 'Vui lòng nhập đầy đủ mã đơn hàng và số điện thoại'
            ];
            $this->view('orders/tracking', $data);
            return;
        }
        
        // Tìm đơn hàng
        $order = $this->orderModel->getOrderByCodeAndPhone($orderCode, $phone);
        
        if (!$order) {
            $data = [
                'order' => null,
                'orderItems' => null,
                'error' => 'Không tìm thấy đơn hàng. Vui lòng kiểm tra lại mã đơn hàng và số điện thoại.'
            ];
            $this->view('orders/tracking', $data);
            return;
        }
        
        // Lấy chi tiết đơn hàng
        $orderItems = $this->orderModel->getOrderItems($order['id']);
        
        $data = [
            'order' => $order,
            'orderItems' => $orderItems,
            'error' => null
        ];
        
        $this->view('orders/tracking', $data);
    }
}