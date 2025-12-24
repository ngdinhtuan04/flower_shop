<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - Flower Shop</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/home.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/wishlist.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/notifications.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/product-detail.css">
</head>
<body>
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>
    
    <!-- Header -->
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="<?= BASE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a>
        <span class="separator">/</span>
        <a href="<?= BASE_URL ?>/products">Sản phẩm</a>
        <span class="separator">/</span>
        <?php if ($product['category_name']): ?>
            <a href="<?= BASE_URL ?>/products?category=<?= $product['category_id'] ?>"><?= htmlspecialchars($product['category_name']) ?></a>
            <span class="separator">/</span>
        <?php endif; ?>
        <span class="current"><?= htmlspecialchars($product['name']) ?></span>
    </div>

    <!-- Product Detail -->
    <div class="product-detail-container">
        <div class="product-detail-grid">
            <!-- Product Image -->
            <div class="product-image-section">
                <?php if (isset($product['promotion_info']) && $product['promotion_info']['discount_percentage'] > 0): ?>
                    <div class="product-badge">
                        -<?= $product['promotion_info']['discount_percentage'] ?>% OFF
                    </div>
                <?php endif; ?>
                
                <?php if ($product['image']): ?>
                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($product['image']) ?>" 
                        alt="<?= htmlspecialchars($product['name']) ?>" 
                        class="main-product-image">
                <?php else: ?>
                    <div class="main-product-image" style="background: linear-gradient(135deg, #ff6b9d 0%, #c44569 100%); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-flower" style="font-size: 120px; color: rgba(255,255,255,0.3);"></i>
                    </div>
                <?php endif; ?>
                
                <!-- Gallery Info Grid -->
                <div class="product-gallery-info">
                    <div class="gallery-info-item">
                        <div class="gallery-info-icon">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <div class="gallery-info-title">Chính hãng 100%</div>
                        <div class="gallery-info-desc">Chất lượng đảm bảo</div>
                    </div>
                    <div class="gallery-info-item">
                        <div class="gallery-info-icon">
                            <i class="fas fa-shield-check"></i>
                        </div>
                        <div class="gallery-info-title">Đổi trả 7 ngày</div>
                        <div class="gallery-info-desc">Nếu có lỗi</div>
                    </div>
                    <div class="gallery-info-item">
                        <div class="gallery-info-icon">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <div class="gallery-info-title">Giao hàng nhanh</div>
                        <div class="gallery-info-desc">Miễn phí từ 500k</div>
                    </div>
                </div>
            </div>

            <!-- Product Info -->
            <div class="product-info-section">
                <?php if ($product['category_name']): ?>
                    <div class="product-category"><?= htmlspecialchars($product['category_name']) ?></div>
                <?php endif; ?>

                <h1 class="product-name"><?= htmlspecialchars($product['name']) ?></h1>

                <div class="product-rating">
                    <div class="stars">
                        <?php 
                        $avgRating = isset($ratingStats['average_rating']) ? (float)$ratingStats['average_rating'] : 0;
                        $fullStars = floor($avgRating);
                        $hasHalfStar = ($avgRating - $fullStars) >= 0.5;
                        $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
                        
                        // Hiển thị sao đầy
                        for ($i = 0; $i < $fullStars; $i++): 
                        ?>
                            <i class="fas fa-star"></i>
                        <?php endfor; ?>
                        
                        <?php if ($hasHalfStar): ?>
                            <i class="fas fa-star-half-alt"></i>
                        <?php endif; ?>
                        
                        <?php 
                        // Hiển thị sao rỗng
                        for ($i = 0; $i < $emptyStars; $i++): 
                        ?>
                            <i class="far fa-star"></i>
                        <?php endfor; ?>
                    </div>
                    <span class="rating-text"><?= number_format($avgRating, 1) ?> (<?= count($reviews) ?> đánh giá)</span>
                </div>

                <!-- Price -->
                <div class="product-price-section">
                    <div class="price-label">Giá sản phẩm:</div>
                    <div class="price-display">
                        <?php if (isset($product['promotion_info']) && $product['promotion_info']['discount_amount'] > 0): ?>
                            <!-- Có khuyến mãi -->
                            <span class="price-current"><?= number_format($product['promotion_info']['discounted_price']) ?>đ</span>
                            <span class="price-old"><?= number_format($product['promotion_info']['original_price']) ?>đ</span>
                            <span class="discount-badge">
                                <i class="fas fa-tag"></i> Giảm <?= $product['promotion_info']['discount_percentage'] ?>% 
                                (Tiết kiệm <?= number_format($product['promotion_info']['discount_amount']) ?>đ)
                            </span>
                            <?php if ($product['promotion_info']['promotion']): ?>
                                <div class="promotion-name">
                                    <i class="fas fa-gift"></i> <?= htmlspecialchars($product['promotion_info']['promotion']['name']) ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <!-- Giá gốc -->
                            <span class="price-current"><?= number_format($product['price']) ?>đ</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Stock Status -->
                <?php if ($product['stock'] > 10): ?>
                    <div class="stock-info in-stock">
                        <i class="fas fa-check-circle stock-icon"></i>
                        <span class="stock-text">Còn hàng (<?= $product['stock'] ?> sản phẩm)</span>
                    </div>
                <?php elseif ($product['stock'] > 0): ?>
                    <div class="stock-info low-stock">
                        <i class="fas fa-exclamation-triangle stock-icon"></i>
                        <span class="stock-text">Chỉ còn <?= $product['stock'] ?> sản phẩm!</span>
                    </div>
                <?php else: ?>
                    <div class="stock-info out-stock">
                        <i class="fas fa-times-circle stock-icon"></i>
                        <span class="stock-text">Tạm hết hàng</span>
                    </div>
                <?php endif; ?>

                <!-- Description -->
                <?php if ($product['description']): ?>
                    <div class="product-description">
                        <h3 class="description-title">Mô tả sản phẩm</h3>
                        <p class="description-text"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                    </div>
                <?php endif; ?>

                <!-- Quantity -->
                <div class="quantity-selector">
                    <span class="quantity-label">Số lượng:</span>
                    <div class="quantity-controls">
                        <button class="quantity-btn" onclick="decreaseQuantity()">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" id="quantity" class="quantity-input" value="1" min="1" max="<?= $product['stock'] ?>">
                        <button class="quantity-btn" onclick="increaseQuantity()">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button class="btn-add-to-cart" <?= $product['stock'] <= 0 ? 'disabled' : '' ?> onclick="addToCart(<?= $product['id'] ?>)">
                        <i class="fas fa-shopping-cart"></i>
                        Thêm vào giỏ hàng
                    </button>
                    <button class="btn-wishlist btn-wishlist-large" 
                            data-product-id="<?= $product['id'] ?>">
                        <i class="far fa-heart"></i>
                        Yêu thích
                    </button>
                    <button class="btn-buy-now" <?= $product['stock'] <= 0 ? 'disabled' : '' ?>>
                        <i class="fas fa-bolt"></i>
                    </button>
                </div>
                
                <!-- Product Feedback Button -->
                <div style="margin-top: 20px;">
                    <button class="btn-product-feedback" onclick="openProductFeedbackModal()">
                        <i class="fas fa-comment-dots"></i>
                        Phản hồi về sản phẩm
                    </button>
                </div>

                <!-- Features -->
                <div class="product-features">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <div class="feature-text">
                            <div class="feature-title">Giao hàng nhanh</div>
                            <div class="feature-desc">2-3 giờ trong nội thành</div>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="feature-text">
                            <div class="feature-title">Đảm bảo chất lượng</div>
                            <div class="feature-desc">Hàng chính hãng 100%</div>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-undo"></i>
                        </div>
                        <div class="feature-text">
                            <div class="feature-title">Đổi trả dễ dàng</div>
                            <div class="feature-desc">Trong vòng 7 ngày</div>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div class="feature-text">
                            <div class="feature-title">Hỗ trợ 24/7</div>
                            <div class="feature-desc">Hotline: 1900 1234</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="reviews-section" style="margin-top: 60px;">
            <h2 class="section-title">Đánh giá sản phẩm</h2>
            
            <?php if (!empty($ratingStats) && $ratingStats['total_reviews'] > 0): ?>
                <!-- Rating Overview -->
                <div class="rating-overview" style="background: white; border-radius: 15px; padding: 30px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <div style="display: grid; grid-template-columns: 200px 1fr; gap: 40px;">
                        <!-- Average Rating -->
                        <div style="text-align: center; border-right: 2px solid #e2e8f0; padding-right: 40px;">
                            <div style="font-size: 56px; font-weight: 800; color: #ff6b9d; line-height: 1;">
                                <?= number_format($ratingStats['average_rating'], 1) ?>
                            </div>
                            <div style="color: #fbbf24; font-size: 24px; margin: 10px 0;">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= round($ratingStats['average_rating'])): ?>
                                        <i class="fas fa-star"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                            <div style="color: #718096; font-size: 14px;">
                                <?= $ratingStats['total_reviews'] ?> đánh giá
                            </div>
                        </div>
                        
                        <!-- Rating Distribution -->
                        <div>
                            <?php for ($star = 5; $star >= 1; $star--): ?>
                                <?php 
                                    $count = $ratingStats['star_' . $star] ?? 0;
                                    $percentage = $ratingStats['total_reviews'] > 0 ? ($count / $ratingStats['total_reviews'] * 100) : 0;
                                ?>
                                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 12px;">
                                    <div style="width: 80px; display: flex; align-items: center; gap: 5px; color: #4a5568; font-size: 14px;">
                                        <?= $star ?> <i class="fas fa-star" style="color: #fbbf24; font-size: 12px;"></i>
                                    </div>
                                    <div style="flex: 1; height: 8px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                                        <div style="height: 100%; background: linear-gradient(135deg, #ff6b9d 0%, #c44569 100%); width: <?= $percentage ?>%; transition: width 0.3s;"></div>
                                    </div>
                                    <div style="width: 60px; text-align: right; color: #718096; font-size: 14px;">
                                        <?= $count ?> 
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Reviews List -->
            <!-- Debug: <?= count($reviews ?? []) ?> reviews, Status filter: visible/approved -->
            <?php if (!empty($reviews) && count($reviews) > 0): ?>
                <div class="reviews-list">
                    <?php foreach ($reviews as $review): ?>
                        <div style="background: white; border-radius: 12px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                            <div style="display: flex; gap: 15px;">
                                <!-- User Avatar -->
                                <div>
                                    <?php if (!empty($review['avatar'])): ?>
                                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($review['avatar']) ?>" 
                                             alt="<?= htmlspecialchars($review['full_name']) ?>"
                                             style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid #ff6b9d;">
                                    <?php else: ?>
                                        <div style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #ff6b9d 0%, #c44569 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 20px;">
                                            <?= strtoupper(mb_substr($review['full_name'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Review Content -->
                                <div style="flex: 1;">
                                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                                        <div>
                                            <div style="font-weight: 700; color: #2d3748; font-size: 16px; margin-bottom: 5px;">
                                                <?= htmlspecialchars($review['full_name']) ?>
                                                <?php if (isset($review['status']) && $review['status'] === 'pending'): ?>
                                                    <span style="display: inline-block; padding: 2px 8px; background: #fef3c7; color: #92400e; border-radius: 12px; font-size: 11px; font-weight: 600; margin-left: 8px;">
                                                        ⏳ Chờ duyệt
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <div style="color: #fbbf24; font-size: 16px;">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <?php if ($i <= $review['rating']): ?>
                                                        <i class="fas fa-star"></i>
                                                    <?php else: ?>
                                                        <i class="far fa-star"></i>
                                                    <?php endif; ?>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <div style="color: #a0aec0; font-size: 13px;">
                                            <?= date('d/m/Y', strtotime($review['created_at'])) ?>
                                        </div>
                                    </div>
                                    <div style="color: #4a5568; line-height: 1.6;">
                                        <?= nl2br(htmlspecialchars($review['comment'])) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.08);">
                    <i class="fas fa-star" style="font-size: 72px; color: #e2e8f0; margin-bottom: 20px;"></i>
                    <h3 style="color: #718096; font-size: 20px; margin-bottom: 10px;">Chưa có đánh giá</h3>
                    <p style="color: #a0aec0; font-size: 15px;">Hãy là người đầu tiên đánh giá sản phẩm này</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Related Products -->
        <?php if (!empty($relatedProducts)): ?>
            <div class="related-products">
                <h2 class="section-title">Sản phẩm liên quan</h2>
                <div class="products-grid">
                    <?php foreach ($relatedProducts as $relatedProduct): ?>
                        <a href="<?= BASE_URL ?>/product/detail/<?= $relatedProduct['id'] ?>" class="product-card">
                            <?php 
                                $hasPromotion = isset($relatedProduct['promotion_info']) && $relatedProduct['promotion_info']['discount_amount'] > 0;
                            ?>
                            <?php if ($hasPromotion): ?>
                                <div class="product-card-badge">
                                    -<?= $relatedProduct['promotion_info']['discount_percentage'] ?>%
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($relatedProduct['image']): ?>
                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($relatedProduct['image']) ?>" 
                                    alt="<?= htmlspecialchars($relatedProduct['name']) ?>" 
                                    class="product-card-image">
                            <?php else: ?>
                                <div class="product-card-image" style="background: linear-gradient(135deg, #ff6b9d 0%, #c44569 100%); display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-gifts" style="font-size: 48px; color: rgba(255,255,255,0.3);"></i>
                                </div>
                            <?php endif; ?>
                            <div class="product-card-info">
                                <h3 class="product-card-title"><?= htmlspecialchars($relatedProduct['name']) ?></h3>
                                <div class="product-card-price">
                                    <?php if (isset($relatedProduct['promotion_info']) && $relatedProduct['promotion_info']['discount_amount'] > 0): ?>
                                        <!-- Có khuyến mãi -->
                                        <span class="price-current"><?= number_format($relatedProduct['promotion_info']['discounted_price']) ?>đ</span>
                                        <span class="price-old"><?= number_format($relatedProduct['promotion_info']['original_price']) ?>đ</span>
                                    <?php else: ?>
                                        <!-- Giá gốc -->
                                        <span class="price-current"><?= number_format($relatedProduct['price']) ?>đ</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <?php include __DIR__ . '/../layouts/footer.php'; ?>

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
            
            // Cập nhật badge giỏ hàng
            updateCartBadge();
            
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
            fetch('<?= BASE_URL ?>/cart/count')
                .then(response => response.json())
                .then(data => {
                    const badge = document.querySelector('.badge');
                    if (badge) {
                        badge.textContent = data.count || 0;
                    }
                })
                .catch(error => console.error('Error updating cart:', error));
        }
    
        const maxStock = <?= $product['stock'] ?>;

        function increaseQuantity() {
            const input = document.getElementById('quantity');
            const currentValue = parseInt(input.value) || 1;
            if (currentValue < maxStock) {
                input.value = currentValue + 1;
            }
        }

        function decreaseQuantity() {
            const input = document.getElementById('quantity');
            const currentValue = parseInt(input.value) || 1;
            if (currentValue > 1) {
                input.value = currentValue - 1;
            }
        }

        function addToCart(productId) {
            // Kiểm tra đăng nhập
            <?php if (!Session::isLoggedIn()): ?>
                showToast('Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng', 'error');
                setTimeout(() => {
                    window.location.href = '<?= BASE_URL ?>/user/login';
                }, 1500);
                return;
            <?php endif; ?>
            
            const quantity = parseInt(document.getElementById('quantity').value) || 1;
            const button = event.target.closest('.btn-add-to-cart');
            const originalText = button.innerHTML;
            
            // Disable button và hiển thị loading
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang thêm...';
            
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', quantity);
            
            fetch('<?= BASE_URL ?>/cart/add', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => {
                // Log response để debug
                const contentType = response.headers.get('content-type');
                console.log('Response status:', response.status);
                console.log('Content-Type:', contentType);
                
                return response.text().then(text => {
                    console.log('Response text:', text);
                    
                    // Thử parse JSON
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('JSON parse error:', e);
                        console.error('Response was:', text);
                        throw new Error('Invalid JSON response: ' + text.substring(0, 100));
                    }
                });
            })
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    // Cập nhật số lượng giỏ hàng nếu có
                    if (data.cart_count !== undefined) {
                        const cartBadge = document.querySelector('.cart-count');
                        if (cartBadge) {
                            cartBadge.textContent = data.cart_count;
                        }
                    }
                } else {
                    showToast(data.message, 'error');
                }
                
                // Khôi phục button
                button.disabled = false;
                button.innerHTML = originalText;
            })
            .catch(error => {
                console.error('Error:', error);
                showToast(error.message || 'Có lỗi xảy ra, vui lòng thử lại', 'error');
                button.disabled = false;
                button.innerHTML = originalText;
            });
        }
    </script>
    
    <!-- Product Feedback Modal -->
    <div class="modal fade" id="productFeedbackModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; border: none; overflow: hidden;">
                <div class="modal-header" style="background: linear-gradient(135deg, #ff6b9d 0%, #c44569 100%); border: none; padding: 20px 25px;">
                    <h5 class="modal-title" style="font-weight: 700; font-size: 20px; color: white;">
                        <i class="fas fa-comment-dots"></i> Phản hồi sản phẩm
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>/feedback/submit" method="POST" id="productFeedbackForm">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="type" value="product_inquiry">
                    
                    <div class="modal-body" style="padding: 25px;">
                        <?php if (Session::isLoggedIn()): ?>
                            <?php $user = Session::getUser(); ?>
                            <input type="hidden" name="name" value="<?= htmlspecialchars($user['full_name']) ?>">
                            <input type="hidden" name="email" value="<?= htmlspecialchars($user['email']) ?>">
                            <input type="hidden" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        <?php else: ?>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Họ tên <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required placeholder="Nhập họ tên của bạn">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required placeholder="email@example.com">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Số điện thoại</label>
                                <input type="tel" name="phone" class="form-control" placeholder="0987654321">
                            </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tiêu đề <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control" required 
                                placeholder="VD: Sản phẩm có ship toàn quốc không?">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nội dung <span class="text-danger">*</span></label>
                            <textarea name="message" class="form-control" rows="4" required 
                                placeholder="Hỏi về sản phẩm: kích thước, màu sắc, giao hàng..."></textarea>
                        </div>
                    </div>
                    
                    <div class="modal-footer" style="border: none; padding: 15px 25px; background: #fff;">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 10px; padding: 10px 20px;">
                            Hủy
                        </button>
                        <button type="submit" class="btn" style="background: linear-gradient(135deg, #ff6b9d 0%, #c44569 100%); color: white; border: none; border-radius: 10px; padding: 10px 24px; font-weight: 600;">
                            <i class="fas fa-paper-plane"></i> Gửi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/notifications.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Open Product Feedback Modal
        function openProductFeedbackModal() {
            const modal = new bootstrap.Modal(document.getElementById('productFeedbackModal'));
            modal.show();
        }
    </script>
    
    <!-- Wishlist JavaScript -->
    <script src="<?= ASSETS_URL ?>/js/wishlist.js"></script>
    <script>
        // Load wishlist status when page loads
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (Session::isLoggedIn()): ?>
            loadWishlistStatus();
            <?php endif; ?>
            
            // Show flash messages
            <?php if (Session::hasFlash('success')): ?>
            showToast('<?= addslashes(Session::getFlash('success')) ?>', 'success');
            <?php endif; ?>
            <?php if (Session::hasFlash('error')): ?>
            showToast('<?= addslashes(Session::getFlash('error')) ?>', 'error');
            <?php endif; ?>
        });
    </script>
</body>
</html>