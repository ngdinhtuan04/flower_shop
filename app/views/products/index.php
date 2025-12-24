<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản phẩm - Flower Shop</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/home.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/wishlist.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/notifications.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/products-index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php $user = Session::getUser(); ?>
    
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="<?= BASE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a>
        <span class="separator">/</span>
        <span class="current">Sản phẩm</span>
    </div>

    <div class="products-container">
        <!-- Sidebar Filter -->
        <aside class="filter-sidebar">
            <form method="GET" action="<?= BASE_URL ?>/products" id="filterForm">
                <!-- Search Box -->
                <div class="search-box">
                    <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </div>

                <!-- Category Filter -->
                <div class="filter-section">
                    <h3><i class="fas fa-list"></i> Danh mục</h3>
                    <div class="filter-option">
                        <input type="radio" name="category" value="" id="cat_all" <?= empty($_GET['category']) ? 'checked' : '' ?> onchange="this.form.submit()">
                        <label for="cat_all">Tất cả sản phẩm</label>
                    </div>
                    <?php if (!empty($categories)): ?>
                        <?php foreach ($categories as $cat): ?>
                            <div class="filter-option">
                                <input type="radio" name="category" value="<?= $cat['id'] ?>" id="cat_<?= $cat['id'] ?>" 
                                    <?= (($_GET['category'] ?? '') == $cat['id']) ? 'checked' : '' ?> 
                                    onchange="this.form.submit()">
                                <label for="cat_<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></label>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Price Range Filter -->
                <div class="filter-section">
                    <h3><i class="fas fa-dollar-sign"></i> Khoảng giá</h3>
                    <div class="filter-option">
                        <input type="radio" name="price_range" value="" id="price_all" 
                            <?= empty($_GET['price_range']) && empty($_GET['min_price']) && empty($_GET['max_price']) ? 'checked' : '' ?> 
                            onchange="this.form.submit()">
                        <label for="price_all">Tất cả</label>
                    </div>
                    <div class="filter-option">
                        <input type="radio" name="price_range" value="0-100000" id="price_1" 
                            <?= ($_GET['price_range'] ?? '') == '0-100000' ? 'checked' : '' ?> 
                            onchange="this.form.submit()">
                        <label for="price_1">Dưới 100.000đ</label>
                    </div>
                    <div class="filter-option">
                        <input type="radio" name="price_range" value="100000-300000" id="price_2" 
                            <?= ($_GET['price_range'] ?? '') == '100000-300000' ? 'checked' : '' ?> 
                            onchange="this.form.submit()">
                        <label for="price_2">100.000đ - 300.000đ</label>
                    </div>
                    <div class="filter-option">
                        <input type="radio" name="price_range" value="300000-500000" id="price_3" 
                            <?= ($_GET['price_range'] ?? '') == '300000-500000' ? 'checked' : '' ?> 
                            onchange="this.form.submit()">
                        <label for="price_3">300.000đ - 500.000đ</label>
                    </div>
                    <div class="filter-option">
                        <input type="radio" name="price_range" value="500000-1000000" id="price_4" 
                            <?= ($_GET['price_range'] ?? '') == '500000-1000000' ? 'checked' : '' ?> 
                            onchange="this.form.submit()">
                        <label for="price_4">500.000đ - 1.000.000đ</label>
                    </div>
                    <div class="filter-option">
                        <input type="radio" name="price_range" value="1000000-99999999" id="price_5" 
                            <?= ($_GET['price_range'] ?? '') == '1000000-99999999' ? 'checked' : '' ?> 
                            onchange="this.form.submit()">
                        <label for="price_5">Trên 1.000.000đ</label>
                    </div>
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e2e8f0;">
                        <p style="font-size: 13px; color: #718096; margin-bottom: 10px;">Hoặc tùy chỉnh:</p>
                        <div class="price-range">
                            <div class="price-inputs">
                                <input type="number" name="min_price" placeholder="Từ" value="<?= $_GET['min_price'] ?? '' ?>">
                                <input type="number" name="max_price" placeholder="Đến" value="<?= $_GET['max_price'] ?? '' ?>">
                            </div>
                            <button type="submit" class="btn-add-cart" style="width: 100%;">
                                <i class="fas fa-filter"></i> Lọc giá
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Rating Filter -->
                <div class="filter-section">
                    <h3><i class="fas fa-star"></i> Đánh giá</h3>
                    <div class="filter-option">
                        <input type="radio" name="rating" value="" id="rating_all" 
                            <?= empty($_GET['rating']) ? 'checked' : '' ?> 
                            onchange="this.form.submit()">
                        <label for="rating_all">Tất cả</label>
                    </div>
                    <div class="filter-option">
                        <input type="radio" name="rating" value="5" id="rating_5" 
                            <?= ($_GET['rating'] ?? '') == '5' ? 'checked' : '' ?> 
                            onchange="this.form.submit()">
                        <label for="rating_5">
                            <i class="fas fa-star" style="color: #fbbf24;"></i>
                            <i class="fas fa-star" style="color: #fbbf24;"></i>
                            <i class="fas fa-star" style="color: #fbbf24;"></i>
                            <i class="fas fa-star" style="color: #fbbf24;"></i>
                            <i class="fas fa-star" style="color: #fbbf24;"></i>
                        </label>
                    </div>
                    <div class="filter-option">
                        <input type="radio" name="rating" value="4" id="rating_4" 
                            <?= ($_GET['rating'] ?? '') == '4' ? 'checked' : '' ?> 
                            onchange="this.form.submit()">
                        <label for="rating_4">
                            <i class="fas fa-star" style="color: #fbbf24;"></i>
                            <i class="fas fa-star" style="color: #fbbf24;"></i>
                            <i class="fas fa-star" style="color: #fbbf24;"></i>
                            <i class="fas fa-star" style="color: #fbbf24;"></i>
                            <i class="far fa-star" style="color: #e2e8f0;"></i> trở lên
                        </label>
                    </div>
                    <div class="filter-option">
                        <input type="radio" name="rating" value="3" id="rating_3" 
                            <?= ($_GET['rating'] ?? '') == '3' ? 'checked' : '' ?> 
                            onchange="this.form.submit()">
                        <label for="rating_3">
                            <i class="fas fa-star" style="color: #fbbf24;"></i>
                            <i class="fas fa-star" style="color: #fbbf24;"></i>
                            <i class="fas fa-star" style="color: #fbbf24;"></i>
                            <i class="far fa-star" style="color: #e2e8f0;"></i>
                            <i class="far fa-star" style="color: #e2e8f0;"></i> trở lên
                        </label>
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="filter-section">
                    <h3><i class="fas fa-tag"></i> Trạng thái</h3>
                    <div class="filter-option">
                        <input type="checkbox" name="sale_only" id="sale_only" value="1" 
                            <?= !empty($_GET['sale_only']) ? 'checked' : '' ?> 
                            onchange="this.form.submit()">
                        <label for="sale_only">Đang giảm giá</label>
                    </div>
                    <div class="filter-option">
                        <input type="checkbox" name="in_stock" id="in_stock" value="1" 
                            <?= !empty($_GET['in_stock']) ? 'checked' : '' ?> 
                            onchange="this.form.submit()">
                        <label for="in_stock">Còn hàng</label>
                    </div>
                </div>

                <button type="button" onclick="window.location.href='<?= BASE_URL ?>/products'" 
                    style="width: 100%; padding: 12px; background: #f7fafc; border: 2px solid #e2e8f0; border-radius: 8px; font-weight: 600; cursor: pointer;">
                    <i class="fas fa-redo"></i> Xóa bộ lọc
                </button>
            </form>
        </aside>

        <!-- Products Area -->
        <div class="products-area">
            <!-- Sort Bar -->
            <div class="sort-bar">
                <div class="results-count">
                    <strong><?= count($products) ?></strong> sản phẩm được tìm thấy
                </div>
                <form method="GET" action="<?= BASE_URL ?>/products" style="display: flex; gap: 10px;">
                    <!-- Keep existing filters -->
                    <?php foreach ($_GET as $key => $value): ?>
                        <?php if ($key !== 'sort'): ?>
                            <input type="hidden" name="<?= $key ?>" value="<?= htmlspecialchars($value) ?>">
                        <?php endif; ?>
                    <?php endforeach; ?>
                    
                    <select name="sort" onchange="this.form.submit()">
                        <option value="">Sắp xếp theo</option>
                        <option value="newest" <?= ($_GET['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                        <option value="popular" <?= ($_GET['sort'] ?? '') === 'popular' ? 'selected' : '' ?>>Bán chạy</option>
                        <option value="rating" <?= ($_GET['sort'] ?? '') === 'rating' ? 'selected' : '' ?>>Đánh giá cao</option>
                        <option value="price_asc" <?= ($_GET['sort'] ?? '') === 'price_asc' ? 'selected' : '' ?>>Giá: Thấp → Cao</option>
                        <option value="price_desc" <?= ($_GET['sort'] ?? '') === 'price_desc' ? 'selected' : '' ?>>Giá: Cao → Thấp</option>
                        <option value="name_asc" <?= ($_GET['sort'] ?? '') === 'name_asc' ? 'selected' : '' ?>>Tên: A → Z</option>
                        <option value="name_desc" <?= ($_GET['sort'] ?? '') === 'name_desc' ? 'selected' : '' ?>>Tên: Z → A</option>
                    </select>
                </form>
            </div>

            <!-- Products Grid -->
            <div class="products-grid">
                <?php if (empty($products)): ?>
                    <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px;">
                        <i class="fas fa-box-open" style="font-size: 72px; color: #cbd5e0; margin-bottom: 20px;"></i>
                        <h3 style="color: #718096; font-size: 24px;">Không tìm thấy sản phẩm</h3>
                        <p style="color: #a0aec0;">Vui lòng thử lại với bộ lọc khác!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <?php 
                            // Ưu tiên hiển thị promotion discount nếu có
                            if (isset($product['promotion_info']) && $product['promotion_info']['discount_amount'] > 0): 
                                $discountPercent = $product['promotion_info']['discount_percentage'];
                            ?>
                                <div class="product-badge sale">
                                    -<?= $discountPercent ?>%
                                </div>
                            <?php elseif ($product['stock'] > 0 && $product['stock'] <= 10): ?>
                                <div class="product-badge">Còn <?= $product['stock'] ?></div>
                            <?php endif; ?>

                            <div class="product-image-wrapper">
                                <?php if ($product['image']): ?>
                                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($product['image']) ?>" 
                                        alt="<?= htmlspecialchars($product['name']) ?>" 
                                        class="product-image">
                                <?php else: ?>
                                    <div class="product-image" style="background: linear-gradient(135deg, #ff6b9d 0%, #c44569 100%); display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-flower" style="font-size: 72px; color: rgba(255,255,255,0.3);"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Wishlist button -->
                                <button class="btn-wishlist-icon" 
                                        data-product-id="<?= $product['id'] ?>"
                                        title="Thêm vào yêu thích">
                                    <i class="far fa-heart"></i>
                                </button>
                                
                                <div class="product-overlay">
                                    <button class="btn-quick-view" onclick="window.location.href='<?= BASE_URL ?>/product/detail/<?= $product['id'] ?>'">
                                        <i class="fas fa-eye"></i> Xem nhanh
                                    </button>
                                </div>
                            </div>

                            <div class="product-info">
                                <?php if ($product['category_name']): ?>
                                    <div class="product-category"><?= htmlspecialchars($product['category_name']) ?></div>
                                <?php endif; ?>

                                <h3 class="product-title"><?= htmlspecialchars($product['name']) ?></h3>

                                <!-- Rating -->
                                <div class="product-rating">
                                    <?php 
                                    $avgRating = $product['avg_rating'] ?? 0;
                                    $reviewCount = $product['review_count'] ?? 0;
                                    $fullStars = floor($avgRating);
                                    $hasHalfStar = ($avgRating - $fullStars) >= 0.5;
                                    ?>
                                    <div class="stars">
                                        <?php for ($i = 0; $i < $fullStars; $i++): ?>
                                            <i class="fas fa-star"></i>
                                        <?php endfor; ?>
                                        <?php if ($hasHalfStar): ?>
                                            <i class="fas fa-star-half-alt"></i>
                                        <?php endif; ?>
                                        <?php for ($i = $fullStars + ($hasHalfStar ? 1 : 0); $i < 5; $i++): ?>
                                            <i class="far fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="rating-text">(<?= number_format($avgRating, 1) ?>)</span>
                                </div>

                                <div class="product-price">
                                    <?php if (isset($product['promotion_info']) && $product['promotion_info']['discount_amount'] > 0): ?>
                                        <!-- Có khuyến mãi -->
                                        <span class="price-current"><?= number_format($product['promotion_info']['discounted_price']) ?>đ</span>
                                        <span class="price-old"><?= number_format($product['promotion_info']['original_price']) ?>đ</span>
                                        <span class="discount-badge">-<?= $product['promotion_info']['discount_percentage'] ?>%</span>
                                    <?php else: ?>
                                        <!-- Giá gốc -->
                                        <span class="price-current"><?= number_format($product['price']) ?>đ</span>
                                    <?php endif; ?>
                                </div>

                                <?php if ($product['stock'] > 10): ?>
                                    <div class="stock-status in-stock">
                                        <i class="fas fa-check-circle"></i> Còn hàng
                                    </div>
                                <?php elseif ($product['stock'] > 0): ?>
                                    <div class="stock-status low-stock">
                                        <i class="fas fa-exclamation-triangle"></i> Sắp hết hàng
                                    </div>
                                <?php else: ?>
                                    <div class="stock-status out-stock">
                                        <i class="fas fa-times-circle"></i> Hết hàng
                                    </div>
                                <?php endif; ?>

                                <div class="product-actions">
                                    <?php if ($product['stock'] > 0): ?>
                                        <button type="button" 
                                                class="btn-add-cart" 
                                                onclick="addToCart(<?= $product['id'] ?>, '<?= htmlspecialchars(addslashes($product['name'])) ?>', event)">
                                            <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
                                        </button>
                                    <?php else: ?>
                                        <button class="btn-add-cart" disabled>
                                            <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

    <script>
        // Toast Notification System
        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            
            toast.innerHTML = `
                <i class="fas ${icon} toast-icon"></i>
                <div class="toast-content">
                    <p class="toast-message">${message}</p>
                </div>
                <button class="toast-close" onclick="closeToast(this)">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            container.appendChild(toast);
            
            // KHÔNG tự động cập nhật cart badge vì có thể là wishlist toast
            // updateCartBadge();
            
            // Tự động ẩn sau 3 giây
            setTimeout(() => {
                closeToast(toast.querySelector('.toast-close'));
            }, 3000);
        }
        
        function closeToast(button) {
            const toast = button.closest('.toast');
            toast.classList.add('hiding');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }
        
        function updateCartBadge() {
            // Gọi API để lấy số lượng giỏ hàng mới
            fetch('<?= BASE_URL ?>/cart/count')
                .then(response => response.json())
                .then(data => {
                    // CHỈ cập nhật badge giỏ hàng, KHÔNG phải wishlist
                    const cartBadge = document.querySelector('.cart-count');
                    if (cartBadge) {
                        cartBadge.textContent = data.count || 0;
                    }
                })
                .catch(error => console.error('Error updating cart:', error));
        }
        
        // Thêm sản phẩm vào giỏ hàng (AJAX)
        function addToCart(productId, productName, event) {
            // Stop event propagation
            if (event) {
                event.preventDefault();
                event.stopPropagation();
                event.stopImmediatePropagation();
            }
            
            // Kiểm tra đăng nhập
            <?php if (!Session::isLoggedIn()): ?>
                showToast('Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng', 'error');
                setTimeout(() => {
                    window.location.href = '<?= BASE_URL ?>/user/login';
                }, 1500);
                return;
            <?php endif; ?>
            const button = event ? event.target.closest('.btn-add-cart') : null;
            const originalText = button.innerHTML;
            
            // Disable button và hiển thị loading
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang thêm...';
            
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', 1);
            
            fetch('<?= BASE_URL ?>/cart/add', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    // Cập nhật CHỈ badge giỏ hàng (không phải wishlist)
                    const cartBadge = document.querySelector('.cart-count');
                    if (cartBadge && data.cart_count !== undefined) {
                        console.log('Updating cart count to:', data.cart_count);
                        cartBadge.textContent = data.cart_count;
                        // Animation
                        cartBadge.style.transform = 'scale(1.3)';
                        setTimeout(() => {
                            cartBadge.style.transform = 'scale(1)';
                        }, 200);
                    }
                    
                    // Không cập nhật wishlist badge
                    console.log('Cart response data:', data);
                } else {
                    showToast(data.message, 'error');
                }
                
                // Khôi phục button
                button.disabled = false;
                button.innerHTML = originalText;
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Có lỗi xảy ra, vui lòng thử lại', 'error');
                button.disabled = false;
                button.innerHTML = originalText;
            });
        }
        
        // Kiểm tra flash message từ PHP
        <?php if (Session::hasFlash('success')): ?>
            showToast('<?= addslashes(Session::getFlash('success')) ?>', 'success');
        <?php endif; ?>
        
        <?php if (Session::hasFlash('error')): ?>
            showToast('<?= addslashes(Session::getFlash('error')) ?>', 'error');
        <?php endif; ?>
    </script>
    
    <!-- Wishlist JavaScript -->
    <script src="<?= ASSETS_URL ?>/js/wishlist.js"></script>
    <script>
        // Load wishlist status when page loads
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (Session::isLoggedIn()): ?>
            loadWishlistStatus();
            <?php endif; ?>
        });
    </script>
</body>
</html>
