<?php
class OrderController extends Controller {
    private $orderModel;
    private $cartModel;
    private $productModel;
    private $promotionModel;
    private $couponModel;
    
    public function __construct() {
        $this->orderModel = $this->model('Order');
        $this->cartModel = $this->model('Cart');
        $this->productModel = $this->model('Product');
        $this->promotionModel = $this->model('Promotion');
        $this->couponModel = $this->model('Coupon');
    }
    
    // Route mặc định /orders -> chuyển sang lịch sử đơn hàng
    public function index() {
        $this->history();
    }
    
    // Hiển thị trang checkout
    public function checkout() {
        // Kiểm tra giỏ hàng từ session (cho phép guest checkout)
        $userId = Session::isLoggedIn() ? Session::getUser()['id'] : null;
        
        // Lấy thông tin đầy đủ từ database nếu đã đăng nhập
        $user = null;
        $addresses = [];
        $defaultAddress = null;
        
        if ($userId) {
            $userModel = $this->model('User');
            $user = $userModel->findById($userId);
            
            // Lấy danh sách địa chỉ của user
            $addressModel = $this->model('UserAddress');
            $addresses = $addressModel->getByUserId($userId);
            $defaultAddress = $addressModel->getDefaultAddress($userId);
        }
        
        $cartItems = $this->cartModel->getCartItems($userId);
        
        if (empty($cartItems)) {
            Session::setFlash('error', 'Giỏ hàng trống');
            header('Location: ' . BASE_URL . '/cart');
            exit;
        }
        
        // Tính tổng tiền với giá khuyến mãi
        $subtotal = 0;
        $cartItemsWithPrice = [];
        foreach ($cartItems as $item) {
            // Lấy thông tin sản phẩm đầy đủ
            $product = $this->productModel->getById($item['product_id']);
            $actualPrice = $item['price'];
            
            // Kiểm tra khuyến mãi
            $priceInfo = $this->promotionModel->calculateDiscountedPrice(
                $item['product_id'],
                $item['price'],
                $product['category_id'] ?? null
            );
            
            if ($priceInfo['discount_amount'] > 0) {
                $actualPrice = $priceInfo['discounted_price'];
            }
            
            $itemTotal = $actualPrice * $item['quantity'];
            $cartItemsWithPrice[] = [
                'product_id' => $item['product_id'],
                'product' => $product,
                'name' => $item['name'],
                'image' => $item['image'],
                'quantity' => $item['quantity'],
                'actual_price' => $actualPrice,
                'original_price' => $item['price'],
                'subtotal' => $itemTotal,
                'has_promotion' => $priceInfo['discount_amount'] > 0
            ];
            
            $subtotal += $itemTotal;
        }
        
        // Lấy thông tin 2 mã giảm giá (nếu có)
        $productCoupon = Session::get('product_coupon');
        $shippingCoupon = Session::get('shipping_coupon');
        
        // Phí ship mặc định (sẽ được JS cập nhật khi user chọn)
        $shippingFee = 30000;
        
        // Tính discount riêng cho từng loại
        $productDiscount = 0;
        $shippingDiscount = 0;
        
        if ($productCoupon) {
            $result = $this->couponModel->calculateDiscount($productCoupon, $subtotal, 0);
            $productDiscount = $result['product_discount'];
        }
        
        if ($shippingCoupon) {
            $result = $this->couponModel->calculateDiscount($shippingCoupon, 0, $shippingFee);
            $shippingDiscount = $result['shipping_discount'];
        }
        
        $couponDiscount = $productDiscount + $shippingDiscount;
        $total = $subtotal + $shippingFee - $couponDiscount;
        
        $data = [
            'user' => $user,
            'addresses' => $addresses,
            'defaultAddress' => $defaultAddress,
            'cartItems' => $cartItemsWithPrice,
            'subtotal' => $subtotal,
            'shippingFee' => $shippingFee,
            'productDiscount' => $productDiscount,
            'shippingDiscount' => $shippingDiscount,
            'couponDiscount' => $couponDiscount,
            'productCoupon' => $productCoupon,
            'shippingCoupon' => $shippingCoupon,
            'total' => $total
        ];
        
        $this->view('orders/checkout', $data);
    }
    
    // Xử lý đặt hàng
    public function placeOrder() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/cart');
            exit;
        }
        
        // Cho phép khách vãng lai đặt hàng
        $userId = Session::isLoggedIn() ? Session::getUser()['id'] : null;
        $user = Session::isLoggedIn() ? Session::getUser() : null;
        
        $cartItems = $this->cartModel->getCartItems($userId);
        
        if (empty($cartItems)) {
            Session::setFlash('error', 'Giỏ hàng trống');
            header('Location: ' . BASE_URL . '/cart');
            exit;
        }
        
        // Validate dữ liệu
        $customerName = trim($_POST['customer_name'] ?? '');
        $customerPhone = trim($_POST['customer_phone'] ?? '');
        $customerEmail = trim($_POST['customer_email'] ?? ($user['email'] ?? ''));
        $shippingAddress = trim($_POST['shipping_address'] ?? '');
        $shippingNote = trim($_POST['shipping_note'] ?? '');
        $paymentMethod = $_POST['payment_method'] ?? 'cod';
        $shippingMethod = $_POST['shipping_method'] ?? 'standard';
        
        // Validate shipping address (không bắt buộc nếu là nhận tại cửa hàng)
        if ($shippingMethod !== 'pickup' && empty($shippingAddress)) {
            Session::setFlash('error', 'Vui lòng nhập địa chỉ giao hàng');
            header('Location: ' . BASE_URL . '/orders/checkout');
            exit;
        }
        
        if (empty($customerName) || empty($customerPhone) || empty($customerEmail)) {
            Session::setFlash('error', 'Vui lòng điền đầy đủ thông tin');
            header('Location: ' . BASE_URL . '/orders/checkout');
            exit;
        }
        
        // Tính tổng tiền với giá khuyến mãi
        $subtotal = 0;
        $cartItemsWithPrice = [];
        foreach ($cartItems as $item) {
            // Lấy thông tin sản phẩm đầy đủ
            $product = $this->productModel->getById($item['product_id']);
            $actualPrice = $item['price'];
            
            // Kiểm tra khuyến mãi
            $priceInfo = $this->promotionModel->calculateDiscountedPrice(
                $item['product_id'],
                $item['price'],
                $product['category_id'] ?? null
            );
            
            if ($priceInfo['discount_amount'] > 0) {
                $actualPrice = $priceInfo['discounted_price'];
            }
            
            $cartItemsWithPrice[] = [
                'product_id' => $item['product_id'],
                'product' => $product,
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'actual_price' => $actualPrice
            ];
            
            $subtotal += $actualPrice * $item['quantity'];
        }
        
        // Tính phí ship theo hình thức giao hàng
        $shippingFee = 30000; // Mặc định tiêu chuẩn
        switch ($shippingMethod) {
            case 'express':
                $shippingFee = 50000;
                break;
            case 'same_day':
                $shippingFee = 80000;
                break;
            case 'pickup':
                $shippingFee = 0;
                break;
            default:
                $shippingFee = 30000;
        }
        
        // Lấy thông tin 2 mã giảm giá
        $productCoupon = Session::get('product_coupon');
        $shippingCoupon = Session::get('shipping_coupon');
        $productDiscount = 0;
        $shippingDiscount = 0;
        $couponCodes = [];
        
        if ($productCoupon) {
            $result = $this->couponModel->calculateDiscount($productCoupon, $subtotal, 0);
            $productDiscount = $result['product_discount'];
            $couponCodes[] = $productCoupon['code'];
        }
        
        if ($shippingCoupon && $shippingFee > 0) {
            $result = $this->couponModel->calculateDiscount($shippingCoupon, 0, $shippingFee);
            $shippingDiscount = $result['shipping_discount'];
            // Chỉ thêm code nếu khác với product coupon
            if (!$productCoupon || $shippingCoupon['code'] !== $productCoupon['code']) {
                $couponCodes[] = $shippingCoupon['code'];
            }
        }
        
        $couponDiscount = $productDiscount + $shippingDiscount;
        $couponCode = implode(', ', $couponCodes);
        $total = $subtotal + $shippingFee - $couponDiscount;
        
        // Tạo mã đơn hàng
        $orderCode = 'ORD' . date('YmdHis') . rand(1000, 9999);
        
        // Tạo đơn hàng (user_id có thể NULL cho khách vãng lai)
        $orderData = [
            'user_id' => $userId,
            'order_code' => $orderCode,
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'customer_phone' => $customerPhone,
            'shipping_address' => $shippingAddress,
            'shipping_note' => $shippingNote,
            'shipping_method' => $shippingMethod,
            'subtotal' => $subtotal,
            'shipping_fee' => $shippingFee,
            'product_discount' => $productDiscount,
            'shipping_discount' => $shippingDiscount,
            'discount' => $couponDiscount,
            'total' => $total,
            'payment_method' => $paymentMethod,
            'coupon_code' => $couponCode,
            'coupon_discount' => $couponDiscount,
            'payment_status' => 'pending',
            'order_status' => 'pending'
        ];
        
        $orderId = $this->orderModel->createOrder($orderData, $cartItemsWithPrice);
        
        if ($orderId) {
            // Gửi thông báo cho user (nếu đã đăng nhập)
            if ($userId) {
                $notificationModel = $this->model('Notification');
                $notificationModel->create([
                    'user_id' => $userId,
                    'type' => 'order_status',
                    'title' => '🎉 Đặt hàng thành công #' . $orderCode,
                    'message' => 'Đơn hàng của bạn đã được tiếp nhận. Chúng tôi sẽ xử lý trong thời gian sớm nhất.',
                    'link' => '/orders/detail/' . $orderId
                ]);
            }
            
            // Giảm số lượng sản phẩm trong kho
            foreach ($cartItemsWithPrice as $item) {
                $this->productModel->decreaseStock($item['product_id'], $item['quantity']);
            }
            
            // Tăng lượt sử dụng coupon nếu có
            if ($productCoupon && !empty($couponCode)) {
                $this->couponModel->incrementUsageCount($productCoupon['id']);
            }
            if ($shippingCoupon && !empty($couponCode) && (!$productCoupon || $shippingCoupon['id'] !== $productCoupon['id'])) {
                $this->couponModel->incrementUsageCount($shippingCoupon['id']);
            }
            
            // Lấy chi tiết đơn hàng để gửi email
            $orderItems = $this->orderModel->getOrderItems($orderId);
            
            // Gửi email xác nhận đơn hàng
            require_once APP_PATH . '/helpers/mail_helper.php';
            sendOrderConfirmationEmail($customerEmail, $orderData, $orderItems);
            
            // Xóa giỏ hàng từ session (legacy)
            Session::set('cart', []);
            
            // Xóa giỏ hàng từ database
            $this->cartModel->clearCart($userId);
            
            // Xóa coupon khỏi session
            Session::delete('product_coupon');
            Session::delete('shipping_coupon');
            Session::delete('applied_coupon');
            
            // Xử lý theo phương thức thanh toán
            if ($paymentMethod === 'vnpay') {
                // Chuyển sang VNPay
                $this->processVNPay($orderId, $orderCode, $total);
            } else {
                // COD - chuyển sang trang thành công
                Session::setFlash('success', 'Đặt hàng thành công! Mã đơn hàng: ' . $orderCode);
                header('Location: ' . BASE_URL . '/orders/success/' . $orderId);
                exit;
            }
        } else {
            Session::setFlash('error', 'Đặt hàng thất bại. Vui lòng thử lại');
            header('Location: ' . BASE_URL . '/orders/checkout');
            exit;
        }
    }
    
    // Xử lý thanh toán VNPay
    private function processVNPay($orderId, $orderCode, $amount) {
        require_once APP_PATH . '/../vnpay_php/config.php';
        
        $vnp_TxnRef = $orderCode;
        $vnp_OrderInfo = 'Thanh toan don hang ' . $orderCode;
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $amount * 100; // VNPay tính theo đơn vị VNĐ * 100
        $vnp_Locale = 'vn';
        $vnp_BankCode = '';
        $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
        
        // Sử dụng $vnp_Returnurl từ config.php
        
        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef,
        );
        
        if (isset($vnp_BankCode) && $vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }
        
        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }
        
        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }
        
        header('Location: ' . $vnp_Url);
        exit;
    }
    
    // Xử lý callback từ VNPay
    public function vnpayReturn() {
        require_once APP_PATH . '/../vnpay_php/config.php';
        
        $vnp_SecureHash = $_GET['vnp_SecureHash'] ?? '';
        $inputData = array();
        foreach ($_GET as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $hashData = "";
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }
        
        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        
        $vnp_ResponseCode = $_GET['vnp_ResponseCode'] ?? '';
        $vnp_TxnRef = $_GET['vnp_TxnRef'] ?? '';
        $vnp_Amount = $_GET['vnp_Amount'] ?? 0;
        $vnp_TransactionNo = $_GET['vnp_TransactionNo'] ?? '';
        
        // Lấy thông tin đơn hàng
        $order = $this->orderModel->getOrderByCode($vnp_TxnRef);
        
        if ($secureHash == $vnp_SecureHash) {
            if ($order) {
                if ($vnp_ResponseCode == '00') {
                    // Thanh toán thành công
                    $paymentInfo = json_encode($_GET);
                    $this->orderModel->updatePaymentStatus($order['id'], 'paid', $paymentInfo);
                    
                    Session::setFlash('success', 'Thanh toán thành công! Mã đơn hàng: ' . $vnp_TxnRef);
                    header('Location: ' . BASE_URL . '/orders/success/' . $order['id']);
                } else {
                    // Thanh toán thất bại
                    $paymentInfo = json_encode($_GET);
                    $this->orderModel->updatePaymentStatus($order['id'], 'failed', $paymentInfo);
                    
                    Session::setFlash('error', 'Thanh toán thất bại. Vui lòng thử lại');
                    header('Location: ' . BASE_URL . '/orders/checkout');
                }
            } else {
                Session::setFlash('error', 'Không tìm thấy đơn hàng');
                header('Location: ' . BASE_URL . '/cart');
            }
        } else {
            Session::setFlash('error', 'Chữ ký không hợp lệ');
            header('Location: ' . BASE_URL . '/cart');
        }
        exit;
    }
    
    // Trang thành công
    public function success($orderId) {
        if (!Session::isLoggedIn()) {
            header('Location: ' . BASE_URL . '/users/login');
            exit;
        }
        
        $user = Session::getUser();
        $order = $this->orderModel->getOrderById($orderId);
        
        if (!$order || $order['user_id'] != $user['id']) {
            Session::setFlash('error', 'Không tìm thấy đơn hàng');
            header('Location: ' . BASE_URL);
            exit;
        }
        
        $orderItems = $this->orderModel->getOrderItems($orderId);
        
        $data = [
            'user' => $user,
            'order' => $order,
            'orderItems' => $orderItems
        ];
        
        $this->view('orders/success', $data);
    }
    
    // Lịch sử đơn hàng
    public function history() {
        if (!Session::isLoggedIn()) {
            header('Location: ' . BASE_URL . '/users/login');
            exit;
        }
        
        $user = Session::getUser();
        $orders = $this->orderModel->getUserOrders($user['id']);
        
        $data = [
            'user' => $user,
            'orders' => $orders
        ];
        
        $this->view('orders/history', $data);
    }
    
    // Chi tiết đơn hàng
    public function detail($orderId) {
        if (!Session::isLoggedIn()) {
            header('Location: ' . BASE_URL . '/users/login');
            exit;
        }
        
        $user = Session::getUser();
        $order = $this->orderModel->getOrderById($orderId);
        
        if (!$order || $order['user_id'] != $user['id']) {
            Session::setFlash('error', 'Không tìm thấy đơn hàng');
            header('Location: ' . BASE_URL . '/orders/history');
            exit;
        }
        
        $orderItems = $this->orderModel->getOrderItems($orderId);
        
        // Kiểm tra đã đánh giá chưa cho từng sản phẩm
        $reviewModel = $this->model('Review');
        $reviewedProducts = [];
        if ($order['order_status'] === 'delivered') {
            foreach ($orderItems as $item) {
                $hasReviewed = $reviewModel->hasReviewed($user['id'], $item['product_id'], $orderId);
                $reviewedProducts[$item['product_id']] = $hasReviewed;
            }
        }
        
        $data = [
            'user' => $user,
            'order' => $order,
            'orderItems' => $orderItems,
            'reviewedProducts' => $reviewedProducts
        ];
        
        $this->view('orders/detail', $data);
    }
    
    // Áp dụng mã giảm giá
    public function applyCoupon() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/orders/checkout');
            exit;
        }
        
        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'Vui lòng đăng nhập');
            header('Location: ' . BASE_URL . '/users/login');
            exit;
        }
        
        $code = strtoupper(trim($_POST['coupon_code'] ?? ''));
        
        if (empty($code)) {
            Session::setFlash('error', 'Vui lòng nhập mã giảm giá');
            header('Location: ' . BASE_URL . '/orders/checkout');
            exit;
        }
        
        $user = Session::getUser();
        $cartItems = $this->cartModel->getCartItems($user['id']);
        
        // Tính subtotal với giá khuyến mãi
        $subtotal = 0;
        foreach ($cartItems as $item) {
            // Lấy thông tin sản phẩm đầy đủ
            $product = $this->productModel->getById($item['product_id']);
            $actualPrice = $item['price'];
            
            $priceInfo = $this->promotionModel->calculateDiscountedPrice(
                $item['product_id'],
                $item['price'],
                $product['category_id'] ?? null
            );
            
            if ($priceInfo['discount_amount'] > 0) {
                $actualPrice = $priceInfo['discounted_price'];
            }
            
            $subtotal += $actualPrice * $item['quantity'];
        }
        
        // Validate coupon
        $userId = Session::get('user_id');
        $result = $this->couponModel->validateCoupon($code, $userId, $subtotal);
        
        if ($result['valid']) {
            $coupon = $result['coupon'];
            $applyTo = $coupon['apply_to'];
            
            // Lưu mã theo loại áp dụng
            if ($applyTo === 'product') {
                Session::set('product_coupon', $coupon);
                Session::set('coupon_alert', ['type' => 'success', 'message' => 'Áp dụng mã giảm giá sản phẩm thành công!']);
            } elseif ($applyTo === 'shipping') {
                Session::set('shipping_coupon', $coupon);
                Session::set('coupon_alert', ['type' => 'success', 'message' => 'Áp dụng mã giảm phí vận chuyển thành công!']);
            } elseif ($applyTo === 'all') {
                // Mã 'all' có thể thay thế cả 2 loại
                Session::set('product_coupon', $coupon);
                Session::set('shipping_coupon', $coupon);
                Session::set('coupon_alert', ['type' => 'success', 'message' => 'Áp dụng mã giảm giá toàn bộ đơn hàng thành công!']);
            }
            
            // Xóa applied_coupon cũ nếu có (để tương thích với code cũ)
            Session::delete('applied_coupon');
        } else {
            Session::set('coupon_alert', ['type' => 'error', 'message' => $result['message']]);
        }
        
        header('Location: ' . BASE_URL . '/orders/checkout');
        exit;
    }
    
    // Xóa mã giảm giá
    public function removeCoupon() {
        $type = $_POST['type'] ?? 'all';
        $message = '';
        
        if ($type === 'product') {
            Session::delete('product_coupon');
            $message = 'Đã xóa mã giảm giá sản phẩm';
        } elseif ($type === 'shipping') {
            Session::delete('shipping_coupon');
            $message = 'Đã xóa mã giảm phí vận chuyển';
        } else {
            Session::delete('product_coupon');
            Session::delete('shipping_coupon');
            $message = 'Đã xóa tất cả mã giảm giá';
        }
        
        // Xóa applied_coupon cũ
        Session::delete('applied_coupon');
        
        // Kiểm tra nếu là AJAX request
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        
        if ($isAjax || isset($_POST['ajax'])) {
            // Trả về JSON cho AJAX
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => $message
            ]);
            exit;
        } else {
            // Redirect cho form submit thông thường
            Session::setFlash('success', $message);
            header('Location: ' . BASE_URL . '/orders/checkout');
            exit;
        }
    }
}
