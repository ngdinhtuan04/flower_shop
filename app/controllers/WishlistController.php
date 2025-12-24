<?php
/**
 * WishlistController - Quản lý danh sách yêu thích
 */
class WishlistController extends Controller
{
    private $wishlistModel;
    private $productModel;

    public function __construct()
    {
        $this->wishlistModel = $this->model('Wishlist');
        $this->productModel = $this->model('Product');
    }

    /**
     * Hiển thị trang danh sách yêu thích
     */
    public function index()
    {
        // Yêu cầu đăng nhập
        $this->requireAuth();

        $userId = Session::get('user_id');
        
        // Lấy danh sách wishlist
        $wishlistItems = $this->wishlistModel->getByUserId($userId);
        
        // Tính giá giảm cho từng sản phẩm
        $promotionModel = $this->model('Promotion');
        foreach ($wishlistItems as &$item) {
            $priceInfo = $promotionModel->calculateDiscountedPrice(
                $item['product_id'],
                $item['product_price'],
                $item['category_id'] ?? null
            );
            $item['promotion_info'] = $priceInfo;
            $item['final_price'] = $priceInfo['discounted_price'];
            $item['discount_percent'] = $priceInfo['discount_percentage'];
            $item['has_discount'] = $priceInfo['discount_amount'] > 0;
        }

        // Render view
        $this->view('wishlist/index', [
            'title' => 'Danh sách yêu thích',
            'wishlistItems' => $wishlistItems,
            'wishlistCount' => count($wishlistItems)
        ]);
    }

    /**
     * Thêm sản phẩm vào wishlist (AJAX)
     */
    public function add()
    {
        // Chỉ chấp nhận POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        // Yêu cầu đăng nhập
        if (!Session::isLoggedIn()) {
            $this->json(['success' => false, 'message' => 'Vui lòng đăng nhập để thêm vào danh sách yêu thích', 'redirect' => BASE_URL . '/user/login'], 401);
        }

        // Lấy data
        $productId = $_POST['product_id'] ?? null;
        
        if (!$productId) {
            $this->json(['success' => false, 'message' => 'Thiếu thông tin sản phẩm'], 400);
        }

        // Kiểm tra sản phẩm tồn tại
        $product = $this->productModel->getById($productId);
        if (!$product) {
            $this->json(['success' => false, 'message' => 'Sản phẩm không tồn tại'], 404);
        }

        $userId = Session::get('user_id');

        // Kiểm tra đã có trong wishlist chưa
        if ($this->wishlistModel->isInWishlist($userId, $productId)) {
            $this->json(['success' => false, 'message' => 'Sản phẩm đã có trong danh sách yêu thích'], 400);
        }

        // Thêm vào wishlist
        $result = $this->wishlistModel->add($userId, $productId);

        if ($result) {
            $count = $this->wishlistModel->count($userId);
            $this->json([
                'success' => true, 
                'message' => 'Đã thêm vào danh sách yêu thích',
                'wishlist_count' => $count
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Không thể thêm vào danh sách yêu thích'], 500);
        }
    }

    /**
     * Xóa sản phẩm khỏi wishlist (AJAX)
     */
    public function remove()
    {
        // Chỉ chấp nhận POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        // Yêu cầu đăng nhập
        if (!Session::isLoggedIn()) {
            $this->json(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        // Lấy data
        $productId = $_POST['product_id'] ?? null;
        
        if (!$productId) {
            $this->json(['success' => false, 'message' => 'Thiếu thông tin sản phẩm'], 400);
        }

        $userId = Session::get('user_id');

        // Xóa khỏi wishlist
        $result = $this->wishlistModel->remove($userId, $productId);

        if ($result > 0) {
            $count = $this->wishlistModel->count($userId);
            $this->json([
                'success' => true, 
                'message' => 'Đã xóa khỏi danh sách yêu thích',
                'wishlist_count' => $count
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Không thể xóa khỏi danh sách yêu thích'], 500);
        }
    }

    /**
     * Toggle wishlist (thêm nếu chưa có, xóa nếu đã có) - AJAX
     */
    public function toggle()
    {
        // Chỉ chấp nhận POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
        }

        // Yêu cầu đăng nhập
        if (!Session::isLoggedIn()) {
            $this->json(['success' => false, 'message' => 'Vui lòng đăng nhập để sử dụng danh sách yêu thích', 'redirect' => BASE_URL . '/user/login'], 401);
        }

        // Lấy data
        $productId = $_POST['product_id'] ?? null;
        
        if (!$productId) {
            $this->json(['success' => false, 'message' => 'Thiếu thông tin sản phẩm'], 400);
        }

        // Kiểm tra sản phẩm tồn tại
        $product = $this->productModel->getById($productId);
        if (!$product) {
            $this->json(['success' => false, 'message' => 'Sản phẩm không tồn tại'], 404);
        }

        $userId = Session::get('user_id');

        // Kiểm tra đã có trong wishlist chưa
        $isInWishlist = $this->wishlistModel->isInWishlist($userId, $productId);

        if ($isInWishlist) {
            // Xóa khỏi wishlist
            $result = $this->wishlistModel->remove($userId, $productId);
            $action = 'removed';
            $message = 'Đã xóa khỏi danh sách yêu thích';
        } else {
            // Thêm vào wishlist
            $result = $this->wishlistModel->add($userId, $productId);
            $action = 'added';
            $message = 'Đã thêm vào danh sách yêu thích';
        }

        if ($result) {
            $count = $this->wishlistModel->count($userId);
            $this->json([
                'success' => true, 
                'message' => $message,
                'action' => $action,
                'in_wishlist' => !$isInWishlist,
                'wishlist_count' => $count
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Có lỗi xảy ra'], 500);
        }
    }

    /**
     * Lấy số lượng sản phẩm trong wishlist (AJAX)
     */
    public function count()
    {
        if (!Session::isLoggedIn()) {
            $this->json(['success' => true, 'count' => 0]);
        }

        $userId = Session::get('user_id');
        $count = $this->wishlistModel->count($userId);
        
        $this->json(['success' => true, 'count' => $count]);
    }

    /**
     * Kiểm tra sản phẩm có trong wishlist không (AJAX)
     */
    public function check()
    {
        if (!Session::isLoggedIn()) {
            $this->json(['success' => true, 'in_wishlist' => false]);
            return;
        }

        $productId = $_GET['product_id'] ?? null;
        if (!$productId) {
            $this->json(['success' => false, 'message' => 'Thiếu thông tin sản phẩm'], 400);
            return;
        }

        $userId = Session::get('user_id');
        $isInWishlist = $this->wishlistModel->isInWishlist($userId, $productId);
        
        $this->json(['success' => true, 'in_wishlist' => $isInWishlist]);
    }

    /**
     * Xóa tất cả wishlist
     */
    public function clear()
    {
        // Yêu cầu đăng nhập
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = Session::get('user_id');
            $result = $this->wishlistModel->clearByUserId($userId);
            
            if ($result !== false) {
                Session::setFlash('success', 'Đã xóa tất cả sản phẩm khỏi danh sách yêu thích');
            } else {
                Session::setFlash('error', 'Có lỗi xảy ra khi xóa danh sách yêu thích');
            }
        }

        $this->redirect('/wishlist');
    }
}
