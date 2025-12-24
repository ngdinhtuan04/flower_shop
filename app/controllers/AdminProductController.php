<?php
/**
 * AdminProductController
 * Xử lý các chức năng quản lý sản phẩm cho admin
 * - Danh sách sản phẩm
 * - Thêm sản phẩm
 * - Cập nhật sản phẩm
 * - Cập nhật tồn kho
 * - Xóa sản phẩm
 * - API lấy danh sách sản phẩm
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../helpers/validation.php';

class AdminProductController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = $this->model('User');
    }

    /**
     * Trang danh sách sản phẩm
     */
    public function manageProducts()
    {
        $this->requireAdmin();
        
        $productModel = $this->model('Product');
        $categoryModel = $this->model('Category');
        
        $search = $_GET['search'] ?? '';
        $categoryFilter = $_GET['category'] ?? '';
        $statusFilter = $_GET['status'] ?? '';
        
        if ($search || $categoryFilter || $statusFilter) {
            $products = $productModel->search($search, $categoryFilter, $statusFilter);
        } else {
            $products = $productModel->getAll();
        }
        
        $categories = $categoryModel->getAll();
        
        $this->view('admin/manage_products', [
            'products' => $products,
            'categories' => $categories,
            'search' => $search,
            'categoryFilter' => $categoryFilter,
            'statusFilter' => $statusFilter
        ]);
    }

    /**
     * Thêm sản phẩm mới (products/store)
     */
    public function productsStore()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/products');
            return;
        }

        $productModel = $this->model('Product');
        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'description' => sanitize($_POST['description'] ?? ''),
            'price' => floatval($_POST['price'] ?? 0),
            'category_id' => intval($_POST['category_id'] ?? 0),
            'stock' => intval($_POST['stock'] ?? 0),
            'status' => sanitize($_POST['status'] ?? 'active')
        ];

        // Validation
        $validator = validate($data);
        $validator->required('name', 'Tên sản phẩm không được để trống')
                  ->minLength('name', 3, 'Tên sản phẩm phải có ít nhất 3 ký tự');

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            $this->redirect('/admin/products');
            return;
        }

        // Kiểm tra category_id
        if ($data['category_id'] <= 0) {
            Session::setFlash('error', 'Vui lòng chọn danh mục!');
            $this->redirect('/admin/products');
            return;
        }

        // Kiểm tra giá
        if ($data['price'] <= 0) {
            Session::setFlash('error', 'Giá sản phẩm phải lớn hơn 0!');
            $this->redirect('/admin/products');
            return;
        }

        // Upload ảnh
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = UPLOAD_PATH . '/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $data['image'] = 'uploads/products/' . $fileName;
            } else {
                Session::setFlash('error', 'Upload ảnh thất bại! Lỗi: ' . $_FILES['image']['error']);
                $this->redirect('/admin/products');
                return;
            }
        }

        try {
            $result = $productModel->create($data);

            if ($result['success']) {
                Session::setFlash('success', 'Thêm sản phẩm thành công!');
            } else {
                Session::setFlash('error', 'Thêm sản phẩm thất bại! Vui lòng thử lại.');
            }
        } catch (Exception $e) {
            Session::setFlash('error', 'Lỗi: ' . $e->getMessage());
        }

        $this->redirect('/admin/products');
    }

    /**
     * Cập nhật sản phẩm (products/update)
     */
    public function productsUpdate()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/products');
            return;
        }

        $productModel = $this->model('Product');
        $productId = intval($_POST['product_id'] ?? 0);
        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'description' => sanitize($_POST['description'] ?? ''),
            'price' => floatval($_POST['price'] ?? 0),
            'category_id' => intval($_POST['category_id'] ?? 0),
            'status' => sanitize($_POST['status'] ?? 'active')
        ];

        // Validation
        $validator = validate($data);
        $validator->required('name', 'Tên sản phẩm không được để trống')
                  ->minLength('name', 3, 'Tên sản phẩm phải có ít nhất 3 ký tự');

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            $this->redirect('/admin/products');
            return;
        }

        // Kiểm tra category_id
        if ($data['category_id'] <= 0) {
            Session::setFlash('error', 'Vui lòng chọn danh mục!');
            $this->redirect('/admin/products');
            return;
        }

        // Kiểm tra giá
        if ($data['price'] <= 0) {
            Session::setFlash('error', 'Giá sản phẩm phải lớn hơn 0!');
            $this->redirect('/admin/products');
            return;
        }

        // Upload ảnh mới (nếu có)
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = UPLOAD_PATH . '/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                // Xóa ảnh cũ
                $oldProduct = $productModel->getById($productId);
                if ($oldProduct && !empty($oldProduct['image'])) {
                    $oldImagePath = PUBLIC_PATH . '/' . $oldProduct['image'];
                    if (file_exists($oldImagePath)) {
                        @unlink($oldImagePath);
                    }
                }
                
                $data['image'] = 'uploads/products/' . $fileName;
            }
        }

        try {
            $result = $productModel->update($productId, $data);

            if ($result['success']) {
                Session::setFlash('success', 'Cập nhật sản phẩm thành công!');
            } else {
                Session::setFlash('error', 'Cập nhật sản phẩm thất bại! Vui lòng thử lại.');
            }
        } catch (Exception $e) {
            Session::setFlash('error', 'Lỗi: ' . $e->getMessage());
        }

        $this->redirect('/admin/products');
    }

    /**
     * Cập nhật tồn kho sản phẩm - ĐÃ BỊ VÔ HIỆU HÓA
     * Tồn kho chỉ được cập nhật thông qua Đơn nhập hàng (Purchase Order)
     */
    public function productsUpdateStock()
    {
        $this->requireAdmin();
        
        // Chức năng này đã bị vô hiệu hóa
        // Tồn kho chỉ được cập nhật thông qua Đơn nhập hàng từ nhà cung cấp
        Session::setFlash('error', 'Chức năng cập nhật tồn kho trực tiếp đã bị vô hiệu hóa. Vui lòng sử dụng Đơn nhập hàng để nhập kho.');
        $this->redirect('/admin/products');
    }

    /**
     * Xóa sản phẩm (products/delete)
     */
    public function productsDelete()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/products');
            return;
        }

        $productModel = $this->model('Product');
        $productId = intval($_POST['product_id'] ?? 0);
        $result = $productModel->delete($productId);

        if ($result['success']) {
            Session::setFlash('success', 'Xóa sản phẩm thành công!');
        } else {
            Session::setFlash('error', $result['message'] ?? 'Xóa sản phẩm thất bại!');
        }

        $this->redirect('/admin/products');
    }

    /**
     * API: Lấy danh sách sản phẩm cho dropdown
     */
    public function productsListJson() {
        if (!Session::isLoggedIn() || !Session::isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        header('Content-Type: application/json');
        $productModel = $this->model('Product');
        $products = $productModel->getAll();
        
        $result = array_map(function($p) {
            return [
                'id' => $p['id'],
                'name' => $p['name']
            ];
        }, $products);
        
        echo json_encode(['success' => true, 'data' => $result]);
        exit;
    }
}
