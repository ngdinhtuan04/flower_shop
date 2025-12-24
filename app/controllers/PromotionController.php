<?php
/**
 * PromotionController - Quản lý khuyến mãi
 */
class PromotionController extends Controller
{
    private $promotionModel;
    private $productModel;
    private $categoryModel;

    public function __construct()
    {
        $this->promotionModel = $this->model('Promotion');
        $this->productModel = $this->model('Product');
        $this->categoryModel = $this->model('Category');
    }

    /**
     * Trang quản lý khuyến mãi (Admin only)
     */
    public function index()
    {
        // Kiểm tra quyền admin
        if (!Session::isLoggedIn() || !Session::isAdmin()) {
            Session::setFlash('error', 'Bạn không có quyền truy cập trang này');
            $this->redirect('/admin/login');
            return;
        }

        // Lấy danh sách khuyến mãi
        $filters = [];
        if (isset($_GET['status'])) {
            $filters['is_active'] = $_GET['status'];
        }
        if (isset($_GET['apply_to'])) {
            $filters['apply_to'] = $_GET['apply_to'];
        }
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }

        $promotions = $this->promotionModel->getAll($filters);
        $statistics = $this->promotionModel->getStatistics();

        $data = [
            'promotions' => $promotions,
            'statistics' => $statistics,
            'filters' => $filters
        ];

        $this->view('admin/manage_promotions', $data);
    }

    /**
     * Hiển thị form thêm khuyến mãi
     */
    public function create()
    {
        // Kiểm tra quyền admin
        if (!Session::isLoggedIn() || !Session::isAdmin()) {
            Session::setFlash('error', 'Bạn không có quyền truy cập');
            $this->redirect('/admin/login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreate();
        } else {
            // Lấy danh sách categories và products
            $categories = $this->categoryModel->getAll();
            $products = $this->productModel->getAll();

            $data = [
                'categories' => $categories,
                'products' => $products,
                'old' => Session::getFlash('old') ?? []
            ];

            $this->view('admin/promotion_form', $data);
        }
    }

    /**
     * Xử lý thêm khuyến mãi mới
     */
    private function handleCreate()
    {
        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'description' => sanitize($_POST['description'] ?? ''),
            'discount_type' => $_POST['discount_type'] ?? 'percentage',
            'discount_value' => floatval($_POST['discount_value'] ?? 0),
            'apply_to' => $_POST['apply_to'] ?? 'all',
            'category_id' => !empty($_POST['category_id']) ? intval($_POST['category_id']) : null,
            'start_date' => $_POST['start_date'] ?? '',
            'end_date' => $_POST['end_date'] ?? '',
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'max_discount_amount' => !empty($_POST['max_discount_amount']) ? floatval($_POST['max_discount_amount']) : null,
            'min_order_amount' => floatval($_POST['min_order_amount'] ?? 0),
            'priority' => intval($_POST['priority'] ?? 0)
        ];

        // Validation
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = 'Tên khuyến mãi không được để trống';
        }

        if ($data['discount_value'] <= 0) {
            $errors[] = 'Giá trị giảm giá phải lớn hơn 0';
        }

        if ($data['discount_type'] == 'percentage' && $data['discount_value'] > 100) {
            $errors[] = 'Giá trị giảm giá phần trăm không được vượt quá 100%';
        }

        if (empty($data['start_date']) || empty($data['end_date'])) {
            $errors[] = 'Ngày bắt đầu và kết thúc không được để trống';
        }

        if (strtotime($data['start_date']) > strtotime($data['end_date'])) {
            $errors[] = 'Ngày kết thúc phải sau ngày bắt đầu';
        }

        if ($data['apply_to'] == 'category' && empty($data['category_id'])) {
            $errors[] = 'Vui lòng chọn danh mục sản phẩm';
        }

        if (!empty($errors)) {
            Session::setFlash('error', implode('<br>', $errors));
            Session::setFlash('old', $data);
            $this->redirect('/promotions/create');
            return;
        }

        // Tạo khuyến mãi
        if ($this->promotionModel->create($data)) {
            $promotionId = $this->promotionModel->db->lastInsertId();

            // Nếu apply_to = 'product', thêm các sản phẩm được chọn
            if ($data['apply_to'] == 'product' && !empty($_POST['product_ids'])) {
                $productIds = array_map('intval', $_POST['product_ids']);
                $this->promotionModel->addProducts($promotionId, $productIds);
            }

            Session::setFlash('success', 'Thêm khuyến mãi thành công');
            $this->redirect('/promotions');
        } else {
            Session::setFlash('error', 'Có lỗi xảy ra. Vui lòng thử lại');
            Session::setFlash('old', $data);
            $this->redirect('/promotions/create');
        }
    }

    /**
     * Hiển thị form sửa khuyến mãi
     */
    public function edit($id)
    {
        // Kiểm tra quyền admin
        if (!Session::isLoggedIn() || !Session::isAdmin()) {
            Session::setFlash('error', 'Bạn không có quyền truy cập');
            $this->redirect('/admin/login');
            return;
        }

        $promotion = $this->promotionModel->getById($id);

        if (!$promotion) {
            Session::setFlash('error', 'Không tìm thấy khuyến mãi');
            $this->redirect('/promotions');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleEdit($id);
        } else {
            // Lấy danh sách categories và products
            $categories = $this->categoryModel->getAll();
            $products = $this->productModel->getAll();
            $promotionProducts = $this->promotionModel->getPromotionProducts($id);

            $data = [
                'promotion' => $promotion,
                'categories' => $categories,
                'products' => $products,
                'promotion_products' => $promotionProducts,
                'old' => Session::getFlash('old') ?? []
            ];

            $this->view('admin/promotion_form', $data);
        }
    }

    /**
     * Xử lý cập nhật khuyến mãi
     */
    private function handleEdit($id)
    {
        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'description' => sanitize($_POST['description'] ?? ''),
            'discount_type' => $_POST['discount_type'] ?? 'percentage',
            'discount_value' => floatval($_POST['discount_value'] ?? 0),
            'apply_to' => $_POST['apply_to'] ?? 'all',
            'category_id' => !empty($_POST['category_id']) ? intval($_POST['category_id']) : null,
            'start_date' => $_POST['start_date'] ?? '',
            'end_date' => $_POST['end_date'] ?? '',
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'max_discount_amount' => !empty($_POST['max_discount_amount']) ? floatval($_POST['max_discount_amount']) : null,
            'min_order_amount' => floatval($_POST['min_order_amount'] ?? 0),
            'priority' => intval($_POST['priority'] ?? 0)
        ];

        // Validation (tương tự create)
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = 'Tên khuyến mãi không được để trống';
        }

        if ($data['discount_value'] <= 0) {
            $errors[] = 'Giá trị giảm giá phải lớn hơn 0';
        }

        if ($data['discount_type'] == 'percentage' && $data['discount_value'] > 100) {
            $errors[] = 'Giá trị giảm giá phần trăm không được vượt quá 100%';
        }

        if (empty($data['start_date']) || empty($data['end_date'])) {
            $errors[] = 'Ngày bắt đầu và kết thúc không được để trống';
        }

        if (strtotime($data['start_date']) > strtotime($data['end_date'])) {
            $errors[] = 'Ngày kết thúc phải sau ngày bắt đầu';
        }

        if ($data['apply_to'] == 'category' && empty($data['category_id'])) {
            $errors[] = 'Vui lòng chọn danh mục sản phẩm';
        }

        if (!empty($errors)) {
            Session::setFlash('error', implode('<br>', $errors));
            Session::setFlash('old', $data);
            $this->redirect('/promotions/edit/' . $id);
            return;
        }

        // Cập nhật khuyến mãi
        if ($this->promotionModel->update($id, $data)) {
            // Nếu apply_to = 'product', cập nhật các sản phẩm
            if ($data['apply_to'] == 'product') {
                $productIds = !empty($_POST['product_ids']) ? array_map('intval', $_POST['product_ids']) : [];
                $this->promotionModel->addProducts($id, $productIds);
            } else {
                // Xóa tất cả sản phẩm nếu không phải apply_to = 'product'
                $this->promotionModel->removeAllProducts($id);
            }

            Session::setFlash('success', 'Cập nhật khuyến mãi thành công');
            $this->redirect('/promotions');
        } else {
            Session::setFlash('error', 'Có lỗi xảy ra. Vui lòng thử lại');
            Session::setFlash('old', $data);
            $this->redirect('/promotions/edit/' . $id);
        }
    }

    /**
     * Xóa khuyến mãi
     */
    public function delete($id)
    {
        // Kiểm tra quyền admin
        if (!Session::isLoggedIn() || !Session::isAdmin()) {
            Session::setFlash('error', 'Bạn không có quyền thực hiện thao tác này');
            $this->redirect('/admin/login');
            return;
        }

        $promotion = $this->promotionModel->getById($id);

        if (!$promotion) {
            Session::setFlash('error', 'Không tìm thấy khuyến mãi');
            $this->redirect('/promotions');
            return;
        }

        if ($this->promotionModel->delete($id)) {
            Session::setFlash('success', 'Xóa khuyến mãi thành công');
        } else {
            Session::setFlash('error', 'Có lỗi xảy ra. Vui lòng thử lại');
        }

        $this->redirect('/promotions');
    }

    /**
     * Toggle trạng thái active/inactive
     */
    public function toggleActive($id)
    {
        // Kiểm tra quyền admin
        if (!Session::isLoggedIn() || !Session::isAdmin()) {
            Session::setFlash('error', 'Bạn không có quyền thực hiện thao tác này');
            $this->redirect('/admin/login');
            return;
        }

        if ($this->promotionModel->toggleActive($id)) {
            Session::setFlash('success', 'Cập nhật trạng thái thành công');
        } else {
            Session::setFlash('error', 'Có lỗi xảy ra. Vui lòng thử lại');
        }

        $this->redirect('/promotions');
    }

    /**
     * Xem chi tiết khuyến mãi
     */
    public function detail($id)
    {
        // Kiểm tra quyền admin
        if (!Session::isLoggedIn() || !Session::isAdmin()) {
            Session::setFlash('error', 'Bạn không có quyền truy cập');
            $this->redirect('/admin/login');
            return;
        }

        $promotion = $this->promotionModel->getById($id);

        if (!$promotion) {
            Session::setFlash('error', 'Không tìm thấy khuyến mãi');
            $this->redirect('/promotions');
            return;
        }

        $promotionProducts = [];
        if ($promotion['apply_to'] == 'product') {
            $promotionProducts = $this->promotionModel->getPromotionProducts($id);
        }

        $data = [
            'promotion' => $promotion,
            'promotion_products' => $promotionProducts
        ];

        $this->view('admin/promotion_detail', $data);
    }
}
