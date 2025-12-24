<?php
/**
 * ProductController - Quản lý sản phẩm (Admin)
 */
class ProductController extends Controller
{
    private $productModel;
    private $categoryModel;
    private $promotionModel;

    public function __construct()
    {
        $this->productModel = $this->model('Product');
        $this->categoryModel = $this->model('Category');
        $this->promotionModel = $this->model('Promotion');
    }

    /**
     * Trang danh sách sản phẩm cho người dùng
     */
    public function index()
    {
        // Get filters
        $search = $_GET['search'] ?? '';
        $category = $_GET['category'] ?? '';
        $priceRange = $_GET['price_range'] ?? '';
        $minPrice = $_GET['min_price'] ?? '';
        $maxPrice = $_GET['max_price'] ?? '';
        $rating = $_GET['rating'] ?? '';
        $saleOnly = isset($_GET['sale_only']);
        $inStock = isset($_GET['in_stock']);
        $sort = $_GET['sort'] ?? '';

        // Build query - chỉ lấy sản phẩm active
        $products = [];
        
        if ($category) {
            $products = $this->productModel->getByCategory($category);
        } else {
            $products = $this->productModel->getAll();
        }

        // Filter chỉ lấy active products
        $products = array_filter($products, function($p) {
            return isset($p['status']) && $p['status'] === 'active';
        });

        // Filter by search
        if ($search) {
            $products = array_filter($products, function($p) use ($search) {
                return stripos($p['name'], $search) !== false || 
                       stripos($p['description'] ?? '', $search) !== false;
            });
        }

        // Filter by price range preset
        if ($priceRange && strpos($priceRange, '-') !== false) {
            list($min, $max) = explode('-', $priceRange);
            $products = array_filter($products, function($p) use ($min, $max) {
                return $p['price'] >= floatval($min) && $p['price'] <= floatval($max);
            });
        }
        // Filter by custom price range
        elseif ($minPrice !== '' || $maxPrice !== '') {
            if ($minPrice !== '') {
                $products = array_filter($products, function($p) use ($minPrice) {
                    return $p['price'] >= floatval($minPrice);
                });
            }
            if ($maxPrice !== '') {
                $products = array_filter($products, function($p) use ($maxPrice) {
                    return $p['price'] <= floatval($maxPrice);
                });
            }
        }

        // Filter by rating
        if ($rating !== '') {
            $products = array_filter($products, function($p) use ($rating) {
                $avgRating = floatval($p['avg_rating'] ?? 0);
                return $avgRating >= floatval($rating);
            });
        }
        
        // Filter by stock
        if ($inStock) {
            $products = array_filter($products, function($p) {
                return isset($p['stock']) && $p['stock'] > 0;
            });
        }

        // Sort products
        switch ($sort) {
            case 'price_asc':
                usort($products, function($a, $b) {
                    return $a['final_price'] <=> $b['final_price'];
                });
                break;
            case 'price_desc':
                usort($products, function($a, $b) {
                    return $b['final_price'] <=> $a['final_price'];
                });
                break;
            case 'name_asc':
                usort($products, function($a, $b) {
                    return strcmp($a['name'], $b['name']);
                });
                break;
            case 'name_desc':
                usort($products, function($a, $b) {
                    return strcmp($b['name'], $a['name']);
                });
                break;
            case 'newest':
                usort($products, function($a, $b) {
                    return strtotime($b['created_at'] ?? 'now') <=> strtotime($a['created_at'] ?? 'now');
                });
                break;
            case 'rating':
                usort($products, function($a, $b) {
                    $ratingA = floatval($a['avg_rating'] ?? 0);
                    $ratingB = floatval($b['avg_rating'] ?? 0);
                    return $ratingB <=> $ratingA;
                });
                break;
            case 'popular':
                usort($products, function($a, $b) {
                    $soldA = intval($a['sold_count'] ?? 0);
                    $soldB = intval($b['sold_count'] ?? 0);
                    return $soldB <=> $soldA;
                });
                break;
        }

        // Get categories for filter
        $categories = $this->categoryModel->getAll();

        // Apply promotions to all products
        foreach ($products as &$product) {
            $priceInfo = $this->promotionModel->calculateDiscountedPrice(
                $product['id'],
                $product['price'],
                $product['category_id'] ?? null
            );
            $product['promotion_info'] = $priceInfo;
            $product['final_price'] = $priceInfo['discounted_price'];
            $product['has_promotion'] = $priceInfo['discount_amount'] > 0;
            $product['discount_percent'] = $priceInfo['discount_percentage'] ?? 0;
        }

        // Filter by sale only (after calculating promotions)
        if ($saleOnly) {
            $products = array_filter($products, function($p) {
                return !empty($p['has_promotion']);
            });
        }

        $this->view('products/index', [
            'products' => array_values($products),
            'categories' => $categories
        ]);
    }

    /**
     * Trang chi tiết sản phẩm cho người dùng
     */
    public function detail($id = null)
    {
        if (!$id) {
            $this->redirect('/products');
            return;
        }

        $product = $this->productModel->getById($id);
        
        if (!$product || $product['status'] !== 'active') {
            $this->redirect('/products');
            return;
        }

        // Get related products (same category)
        $relatedProducts = [];
        if ($product['category_id']) {
            $categoryProducts = $this->productModel->getByCategory($product['category_id']);
            $relatedProducts = array_filter($categoryProducts, function($p) use ($id) {
                return $p['id'] != $id && $p['status'] === 'active';
            });
            $relatedProducts = array_slice($relatedProducts, 0, 4);
        }

        // Get reviews for this product
        $reviewModel = $this->model('Review');
        
        // Nếu user đã login, lấy cả reviews pending của họ
        $userId = Session::isLoggedIn() ? Session::getUser()['id'] : null;
        $reviews = $reviewModel->getByProductWithUserReviews($id, $userId, 10);
        
        $ratingStats = $reviewModel->getProductRatingStats($id);
        
        // Kiểm tra user có thể đánh giá không
        $canReview = false;
        if (Session::isLoggedIn()) {
            $user = Session::getUser();
            $canReview = $reviewModel->canReview($user['id'], $id);
        }

        // Apply promotion to product
        $priceInfo = $this->promotionModel->calculateDiscountedPrice(
            $product['id'],
            $product['price'],
            $product['category_id'] ?? null
        );
        $product['promotion_info'] = $priceInfo;
        $product['final_price'] = $priceInfo['discounted_price'];

        // Apply promotion to related products
        foreach ($relatedProducts as &$relProd) {
            $relPriceInfo = $this->promotionModel->calculateDiscountedPrice(
                $relProd['id'],
                $relProd['price'],
                $relProd['category_id'] ?? null
            );
            $relProd['promotion_info'] = $relPriceInfo;
            $relProd['final_price'] = $relPriceInfo['discounted_price'];
        }

        $this->view('products/detail', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'reviews' => $reviews,
            'ratingStats' => $ratingStats,
            'canReview' => $canReview
        ]);
    }

    /**
     * Trang danh sách sản phẩm (Admin)
     */
    public function manageProducts()
    {
        $this->requireAdmin();
        
        $search = $_GET['search'] ?? '';
        $categoryFilter = $_GET['category'] ?? '';
        $statusFilter = $_GET['status'] ?? '';
        
        if ($search || $categoryFilter || $statusFilter) {
            $products = $this->productModel->search($search, $categoryFilter, $statusFilter);
        } else {
            $products = $this->productModel->getAll();
        }
        
        $categories = $this->categoryModel->getAll();
        
        $this->view('admin/manage_products', [
            'products' => $products,
            'categories' => $categories,
            'search' => $search,
            'categoryFilter' => $categoryFilter,
            'statusFilter' => $statusFilter
        ]);
    }

    /**
     * Thêm sản phẩm mới
     */
    public function store()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/manage-products');
            return;
        }

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
                  ->minLength('name', 3, 'Tên sản phẩm phải có ít nhất 3 ký tự')
                  ->required('category_id', 'Vui lòng chọn danh mục');

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            $this->redirect('/admin/manage-products');
            return;
        }

        // Kiểm tra giá
        if ($data['price'] < 0) {
            Session::setFlash('error', 'Giá sản phẩm không hợp lệ!');
            $this->redirect('/admin/manage-products');
            return;
        }

        // Upload ảnh
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'public/uploads/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $data['image'] = 'uploads/products/' . $fileName;
            } else {
                Session::setFlash('error', 'Upload ảnh thất bại!');
                $this->redirect('/admin/manage-products');
                return;
            }
        }

        $result = $this->productModel->create($data);

        if ($result['success']) {
            Session::setFlash('success', 'Thêm sản phẩm thành công!');
        } else {
            Session::setFlash('error', 'Thêm sản phẩm thất bại!');
        }

        $this->redirect('/admin/manage-products');
    }

    /**
     * Cập nhật sản phẩm
     */
    public function update()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/manage-products');
            return;
        }

        $productId = intval($_POST['product_id'] ?? 0);
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
                  ->minLength('name', 3, 'Tên sản phẩm phải có ít nhất 3 ký tự')
                  ->required('category_id', 'Vui lòng chọn danh mục');

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            $this->redirect('/admin/manage-products');
            return;
        }

        // Kiểm tra giá
        if ($data['price'] < 0) {
            Session::setFlash('error', 'Giá sản phẩm không hợp lệ!');
            $this->redirect('/admin/manage-products');
            return;
        }

        // Upload ảnh mới (nếu có)
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'public/uploads/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                // Xóa ảnh cũ
                $oldProduct = $this->productModel->getById($productId);
                if ($oldProduct && !empty($oldProduct['image']) && file_exists('public/' . $oldProduct['image'])) {
                    unlink('public/' . $oldProduct['image']);
                }
                
                $data['image'] = 'uploads/products/' . $fileName;
            }
        }

        $result = $this->productModel->update($productId, $data);

        if ($result['success']) {
            Session::setFlash('success', 'Cập nhật sản phẩm thành công!');
        } else {
            Session::setFlash('error', 'Cập nhật sản phẩm thất bại!');
        }

        $this->redirect('/admin/manage-products');
    }

    /**
     * Xóa sản phẩm
     */
    public function delete()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/manage-products');
            return;
        }

        $productId = intval($_POST['product_id'] ?? 0);
        $result = $this->productModel->delete($productId);

        if ($result['success']) {
            Session::setFlash('success', 'Xóa sản phẩm thành công!');
        } else {
            Session::setFlash('error', $result['message'] ?? 'Xóa sản phẩm thất bại!');
        }

        $this->redirect('/admin/manage-products');
    }
}