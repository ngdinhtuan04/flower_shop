<?php
/**
 * HomeController - Trang chủ và các trang chung
 */
class HomeController extends Controller
{
    /**
     * Trang chủ
     */
    public function index()
    {
        // Lấy slider
        $sliderModel = $this->model('Slider');
        $sliders = $sliderModel->getActive();
        
        // Lấy sản phẩm nổi bật
        $productModel = $this->model('Product');
        $products = $productModel->getFeaturedProducts(8);
        
        // Lấy danh mục
        $categoryModel = $this->model('Category');
        $categories = $categoryModel->getAll();
        
        // Lấy đánh giá mới nhất
        $reviewModel = $this->model('Review');
        $reviews = $reviewModel->getLatestReviews(6);
        
        // Lấy thông tin khuyến mãi
        $promotionModel = $this->model('Promotion');
        
        // Tính giá khuyến mãi cho sản phẩm
        foreach ($products as &$product) {
            $priceInfo = $promotionModel->calculateDiscountedPrice(
                $product['id'],
                $product['price'],
                $product['category_id']
            );
            
            $product['has_promotion'] = $priceInfo['discount_amount'] > 0;
            $product['discounted_price'] = $priceInfo['discounted_price'];
            $product['discount_percent'] = $priceInfo['discount_percentage'] ?? 0;
            $product['original_price'] = $priceInfo['original_price'];
        }
        
        $data = [
            'sliders' => $sliders,
            'products' => $products,
            'categories' => $categories,
            'reviews' => $reviews
        ];
        
        $this->view('home/index', $data);
    }

    /**
     * Trang giới thiệu
     */
    public function about()
    {
        $this->view('home/about');
    }
}