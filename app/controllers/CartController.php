<?php
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/Product.php';
require_once APP_PATH . '/models/Cart.php';
require_once APP_PATH . '/models/Coupon.php';
require_once APP_PATH . '/models/Promotion.php';

class CartController extends Controller {
    private $productModel;
    private $cartModel;
    private $couponModel;
    private $promotionModel;
    
    public function __construct() {
        $this->productModel = new Product();
        $this->cartModel = new Cart();
        $this->couponModel = new Coupon();
        $this->promotionModel = new Promotion();
    }
    
    /**
     * Hiển thị giỏ hàng
     */
    public function index() {
        $userId = Session::get('user_id');
        $cartItems = $this->cartModel->getCartItems($userId);
        $subtotal = 0;
        
        // Tính subtotal với promotion
        foreach ($cartItems as &$item) {
            $product = $this->productModel->getById($item['product_id']);
            if ($product) {
                // Lấy giá thực tế (ưu tiên: promotion > price)
                $actualPrice = $product['price'];
                
                // Kiểm tra promotion
                $priceInfo = $this->promotionModel->calculateDiscountedPrice(
                    $product['id'],
                    $product['price'],
                    $product['category_id'] ?? null
                );
                if ($priceInfo['discount_amount'] > 0) {
                    $actualPrice = $priceInfo['discounted_price'];
                }
                
                $item['actual_price'] = $actualPrice;
                $item['product'] = $product;
                $subtotal += $actualPrice * $item['quantity'];
            }
        }
        
        // Lấy sản phẩm đề xuất dựa trên giỏ hàng
        $recommendedProducts = $this->getRecommendedProducts($cartItems);
        
        $this->view('cart/index', [
            'cartItems' => $cartItems,
            'subtotal' => $subtotal,
            'recommendedProducts' => $recommendedProducts
        ]);
    }
    
    /**
     * Lấy sản phẩm đề xuất dựa trên giỏ hàng
     */
    private function getRecommendedProducts($cartItems) {
        if (empty($cartItems)) {
            return [];
        }
        
        // Lấy category_id từ các sản phẩm trong giỏ
        $categoryIds = array_unique(array_filter(array_map(function($item) {
            return $item['product']['category_id'] ?? null;
        }, $cartItems)));
        
        // Lấy product_id đã có trong giỏ để exclude
        $excludeIds = array_map(function($item) {
            return $item['product_id'];
        }, $cartItems);
        
        // Lấy sản phẩm cùng category, sắp xếp theo rating
        $recommended = [];
        $addedIds = []; // Track đã thêm để tránh trùng
        
        if (!empty($categoryIds)) {
            foreach ($categoryIds as $catId) {
                $categoryProducts = $this->productModel->getByCategory($catId, 10);
                foreach ($categoryProducts as $product) {
                    // Check không trùng với giỏ hàng VÀ không trùng trong recommended
                    if (!in_array($product['id'], $excludeIds) 
                        && !in_array($product['id'], $addedIds)
                        && $product['status'] === 'active') {
                        // Tính promotion
                        $priceInfo = $this->promotionModel->calculateDiscountedPrice(
                            $product['id'],
                            $product['price'],
                            $product['category_id'] ?? null
                        );
                        $product['has_promotion'] = $priceInfo['discount_amount'] > 0;
                        $product['discounted_price'] = $priceInfo['discounted_price'];
                        $product['discount_percent'] = $priceInfo['discount_percentage'] ?? 0;
                        
                        $recommended[] = $product;
                        $addedIds[] = $product['id']; // Đánh dấu đã thêm
                    }
                }
            }
            
            // Sắp xếp theo rating và giới hạn 3 sản phẩm
            usort($recommended, function($a, $b) {
                return ($b['avg_rating'] ?? 0) <=> ($a['avg_rating'] ?? 0);
            });
            $recommended = array_slice($recommended, 0, 3);
            
            // Cập nhật lại addedIds sau khi slice
            $addedIds = array_map(function($p) { return $p['id']; }, $recommended);
        }
        
        // Nếu không đủ 3 sản phẩm, lấy thêm featured products
        if (count($recommended) < 3) {
            $featuredProducts = $this->productModel->getFeaturedProducts(10);
            foreach ($featuredProducts as $product) {
                // Check không trùng với giỏ hàng VÀ không trùng trong recommended
                if (!in_array($product['id'], $excludeIds) 
                    && !in_array($product['id'], $addedIds)
                    && count($recommended) < 3) {
                    // Tính promotion
                    $priceInfo = $this->promotionModel->calculateDiscountedPrice(
                        $product['id'],
                        $product['price'],
                        $product['category_id'] ?? null
                    );
                    $product['has_promotion'] = $priceInfo['discount_amount'] > 0;
                    $product['discounted_price'] = $priceInfo['discounted_price'];
                    $product['discount_percent'] = $priceInfo['discount_percentage'] ?? 0;
                    
                    $recommended[] = $product;
                    $addedIds[] = $product['id'];
                }
            }
        }
        
        return $recommended;
    }
    
    /**
     * Thêm sản phẩm vào giỏ
     */
    public function add() {
        // Tắt display errors để tránh output không mong muốn trong JSON response
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
        
        // Kiểm tra đăng nhập
        if (!Session::isLoggedIn()) {
            if ($isAjax) {
                // Clear any output
                while (ob_get_level()) {
                    ob_end_clean();
                }
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng', 'require_login' => true]);
                exit;
            }
            Session::setFlash('error', 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng');
            header('Location: ' . BASE_URL . '/user/login');
            exit;
        }
        
        // Bắt đầu output buffering để tránh output không mong muốn
        if ($isAjax) {
            ob_start();
        }
        
        // Lấy user_id
        $userId = Session::get('user_id');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($isAjax) {
                while (ob_get_level()) {
                    ob_end_clean();
                }
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
                exit;
            }
            header('Location: ' . BASE_URL . '/products');
            exit;
        }
        
        $productId = intval($_POST['product_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 1);
        
        if ($productId <= 0 || $quantity <= 0) {
            if ($isAjax) {
                while (ob_get_level()) {
                    ob_end_clean();
                }
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
                exit;
            }
            Session::setFlash('error', 'Dữ liệu không hợp lệ');
            header('Location: ' . $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/products');
            exit;
        }
        
        // Kiểm tra sản phẩm tồn tại
        $product = $this->productModel->getById($productId);
        if (!$product) {
            if ($isAjax) {
                while (ob_get_level()) {
                    ob_end_clean();
                }
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
                exit;
            }
            Session::setFlash('error', 'Sản phẩm không tồn tại');
            header('Location: ' . BASE_URL . '/products');
            exit;
        }
        
        // Kiểm tra tồn kho
        $cart = $this->cartModel->getCartItems($userId);
        $currentQty = 0;
        foreach ($cart as $item) {
            if ($item['product_id'] == $productId) {
                $currentQty = $item['quantity'];
                break;
            }
        }
        $newQty = $currentQty + $quantity;
        
        if ($newQty > $product['stock_quantity']) {
            if ($isAjax) {
                while (ob_get_level()) {
                    ob_end_clean();
                }
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Số lượng vượt quá tồn kho']);
                exit;
            }
            Session::setFlash('error', 'Số lượng vượt quá tồn kho');
            header('Location: ' . $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/products');
            exit;
        }
        
        // Thêm vào giỏ (database)
        try {
            $this->cartModel->addItem($userId, $productId, $quantity, $product['price']);
            
            if ($isAjax) {
                while (ob_get_level()) {
                    ob_end_clean();
                }
                $cartCount = $this->cartModel->getCartCount($userId);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => 'Đã thêm "' . $product['name'] . '" vào giỏ hàng',
                    'cart_count' => $cartCount
                ]);
                exit;
            }
        } catch (Exception $e) {
            if ($isAjax) {
                while (ob_get_level()) {
                    ob_end_clean();
                }
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false, 
                    'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
                ]);
                exit;
            }
            Session::setFlash('error', 'Có lỗi xảy ra khi thêm vào giỏ hàng');
            header('Location: ' . $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/products');
            exit;
        }
        
        Session::setFlash('success', 'Đã thêm "' . $product['name'] . '" vào giỏ hàng');
        
        // Redirect về trang trước
        $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/products';
        header('Location: ' . $referer);
        exit;
    }
    
    /**
     * Cập nhật số lượng
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/cart');
            exit;
        }
        
        $userId = Session::get('user_id');
        $cartId = intval($_POST['cart_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 0);
        
        if ($cartId <= 0) {
            Session::setFlash('error', 'Dữ liệu không hợp lệ');
            header('Location: ' . BASE_URL . '/cart');
            exit;
        }
        
        if ($quantity > 0) {
            // Kiểm tra tồn kho
            $cartItems = $this->cartModel->getCartItems($userId);
            foreach ($cartItems as $item) {
                if ($item['id'] == $cartId) {
                    $product = $this->productModel->getById($item['product_id']);
                    if ($product && $quantity > $product['stock_quantity']) {
                        Session::setFlash('error', 'Số lượng vượt quá tồn kho');
                        header('Location: ' . BASE_URL . '/cart');
                        exit;
                    }
                    break;
                }
            }
        }
        
        // Cập nhật số lượng (hoặc xóa nếu quantity <= 0)
        $this->cartModel->updateQuantity($cartId, $quantity);
        
        // Kiểm tra lại coupon sau khi cập nhật giỏ hàng
        $this->revalidateCoupon();
        
        Session::setFlash('success', 'Đã cập nhật giỏ hàng');
        header('Location: ' . BASE_URL . '/cart');
        exit;
    }
    
    /**
     * Xóa sản phẩm
     */
    public function remove($productId) {
        $userId = Session::get('user_id');
        $sessionId = $userId ? null : session_id();
        
        $this->cartModel->removeItem($userId, $productId, $sessionId);
        
        // Kiểm tra lại coupon
        $this->revalidateCoupon();
        
        Session::setFlash('success', 'Đã xóa sản phẩm khỏi giỏ hàng');
        header('Location: ' . BASE_URL . '/cart');
        exit;
    }
    
    /**
     * Xóa toàn bộ giỏ hàng
     */
    public function clear() {
        $userId = Session::get('user_id');
        $sessionId = $userId ? null : session_id();
        
        $this->cartModel->clearCart($userId, $sessionId);
        Session::delete('applied_coupon');
        
        Session::setFlash('success', 'Đã xóa toàn bộ giỏ hàng');
        header('Location: ' . BASE_URL . '/cart');
        exit;
    }
    
    /**
     * Áp dụng mã giảm giá
     */
    public function applyCoupon() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/cart');
            exit;
        }
        
        $code = strtoupper(trim($_POST['coupon_code'] ?? ''));
        
        if (empty($code)) {
            Session::setFlash('error', 'Vui lòng nhập mã giảm giá');
            header('Location: ' . BASE_URL . '/cart');
            exit;
        }
        
        // Tính tổng giỏ hàng (sau khuyến mãi)
        $userId = Session::get('user_id');
        $cartItems = $this->cartModel->getCartItems($userId);
        $subtotal = 0;
        
        foreach ($cartItems as $item) {
            $product = $this->productModel->getById($item['product_id']);
            if ($product) {
                // Lấy giá thực tế (ưu tiên: promotion > price)
                $actualPrice = $product['price'];
                
                // Kiểm tra promotion
                $priceInfo = $this->promotionModel->calculateDiscountedPrice(
                    $product['id'],
                    $product['price'],
                    $product['category_id'] ?? null
                );
                if ($priceInfo['discount_amount'] > 0) {
                    $actualPrice = $priceInfo['discounted_price'];
                }
                
                $subtotal += $actualPrice * $item['quantity'];
            }
        }
        
        // Validate coupon
        $result = $this->couponModel->validateCoupon($code, $userId, $subtotal);
        
        if ($result['valid']) {
            Session::set('applied_coupon', $result['coupon']);
            Session::setFlash('success', 'Áp dụng mã giảm giá thành công!');
        } else {
            Session::setFlash('error', $result['message']);
        }
        
        header('Location: ' . BASE_URL . '/cart');
        exit;
    }
    
    /**
     * Xóa mã giảm giá
     */
    public function removeCoupon() {
        Session::delete('applied_coupon');
        Session::setFlash('success', 'Đã xóa mã giảm giá');
        header('Location: ' . BASE_URL . '/cart');
        exit;
    }
    
    /**
     * Kiểm tra lại mã giảm giá sau khi thay đổi giỏ hàng
     */
    private function revalidateCoupon() {
        $appliedCoupon = Session::get('applied_coupon');
        
        if (!$appliedCoupon) {
            return;
        }
        
        // Tính lại tổng (sau khuyến mãi)
        $userId = Session::get('user_id');
        $cartItems = $this->cartModel->getCartItems($userId);
        $subtotal = 0;
        
        foreach ($cartItems as $item) {
            $product = $this->productModel->getById($item['product_id']);
            if ($product) {
                // Lấy giá thực tế (ưu tiên: promotion > price)
                $actualPrice = $product['price'];
                
                // Kiểm tra promotion
                $priceInfo = $this->promotionModel->calculateDiscountedPrice(
                    $product['id'],
                    $product['price'],
                    $product['category_id'] ?? null
                );
                if ($priceInfo['discount_amount'] > 0) {
                    $actualPrice = $priceInfo['discounted_price'];
                }
                
                $subtotal += $actualPrice * $item['quantity'];
            }
        }
        
        // Kiểm tra lại điều kiện tối thiểu
        if ($subtotal < $appliedCoupon['min_order_value']) {
            Session::delete('applied_coupon');
            Session::setFlash('error', 'Mã giảm giá đã bị xóa do giá trị đơn hàng thấp hơn yêu cầu');
        }
    }
    
    /**
     * Lấy số lượng sản phẩm trong giỏ (API)
     */
    public function count() {
        header('Content-Type: application/json');
        $userId = Session::get('user_id');
        $count = $this->cartModel->getCartCount($userId);
        echo json_encode(['count' => $count]);
        exit;
    }
}