<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flower Shop - Cửa Hàng Hoa Tươi Đẹp Nhất Việt Nam</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/home.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/wishlist.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/notifications.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/home-index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <!-- Hero Slider -->
    <?php if (!empty($sliders)): ?>
    <section class="hero-slider">
        <div class="slider-container">
            <?php foreach ($sliders as $index => $slider): ?>
            <div class="slider-slide <?= $index === 0 ? 'active' : '' ?>" 
                 style="background-image: url('<?= BASE_URL ?>/<?= htmlspecialchars($slider['image']) ?>');">
                <div class="slider-content">
                    <h1 class="slider-title"><?= htmlspecialchars($slider['title']) ?></h1>
                    <?php if (!empty($slider['description'])): ?>
                        <p class="slider-description"><?= htmlspecialchars($slider['description']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($slider['link']) && !empty($slider['button_text'])): ?>
                        <a href="<?= htmlspecialchars($slider['link']) ?>" class="slider-button">
                            <i class="fas fa-arrow-right"></i>
                            <?= htmlspecialchars($slider['button_text']) ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            
            <!-- Navigation Dots -->
            <?php if (count($sliders) > 1): ?>
            <div class="slider-nav">
                <?php foreach ($sliders as $index => $slider): ?>
                    <span class="slider-dot <?= $index === 0 ? 'active' : '' ?>" 
                          onclick="goToSlide(<?= $index ?>)"></span>
                <?php endforeach; ?>
            </div>
            
            <!-- Arrow Controls -->
            <div class="slider-arrow prev" onclick="changeSlide(-1)">
                <i class="fas fa-chevron-left"></i>
            </div>
            <div class="slider-arrow next" onclick="changeSlide(1)">
                <i class="fas fa-chevron-right"></i>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <?php else: ?>
    <!-- Fallback nếu không có slider -->
    <section class="hero-section">
        <div class="hero-container">
            <div class="hero-content">
                <span class="hero-badge">🌺 Ưu đãi đặc biệt</span>
                <h1 class="hero-title">Hoa Tươi Đẹp<br>Cho Mọi Dịp</h1>
                <p class="hero-description">
                    Giao hoa tận nơi trong 2 giờ. Đa dạng mẫu mã từ cổ điển đến hiện đại.
                    Cam kết chất lượng, tươi mới 100%.
                </p>
                <div class="hero-buttons">
                    <a href="<?= BASE_URL ?>/products" class="btn btn-primary">
                        <i class="fas fa-spa"></i> Xem sản phẩm
                    </a>
                    <a class="btn btn-outline" style="cursor: pointer;">
                        <i class="fas fa-phone"></i> Liên hệ: 1900 1234
                    </a>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Categories Section -->
    <section class="categories-section">
        <div class="container">
            <div class="section-header">
                <h2>Danh Mục Hoa</h2>
                <p>Lựa chọn hoàn hảo cho mọi dịp đặc biệt</p>
            </div>
            <div class="categories-grid">
                <?php if (!empty($categories)): ?>
                    <?php 
                    $categoryIcons = [
                        'Hoa Cưới' => '💐',
                        'Hoa Sinh Nhật' => '🎂',
                        'Hoa Chúc Mừng' => '🎉',
                        'Hoa Tình Yêu' => '💕',
                        'Hoa Chia Buồn' => '🕯️',
                        'Hoa Văn Phòng' => '🏢',
                    ];
                    foreach (array_slice($categories, 0, 4) as $category): 
                        $icon = $categoryIcons[$category['name']] ?? '🌸';
                        $productCount = $category['product_count'] ?? 0;
                    ?>
                    <a href="<?= BASE_URL ?>/products?category=<?= $category['id'] ?>" class="category-card">
                        <div class="category-icon"><?= $icon ?></div>
                        <h3><?= htmlspecialchars($category['name']) ?></h3>
                        <p><?= htmlspecialchars($category['description'] ?? 'Đa dạng mẫu mã') ?></p>
                        <span class="category-count"><?= $productCount ?>+ sản phẩm</span>
                    </a>
                    <?php endforeach; ?>
                <?php else: ?>
                <a href="<?= BASE_URL ?>/products?category=wedding" class="category-card">
                    <div class="category-icon">💐</div>
                    <h3>Hoa Cưới</h3>
                    <p>Lãng mạn & sang trọng</p>
                    <span class="category-count">120+ sản phẩm</span>
                </a>
                <a href="<?= BASE_URL ?>/products?category=birthday" class="category-card">
                    <div class="category-icon">🎂</div>
                    <h3>Hoa Sinh Nhật</h3>
                    <p>Tươi vui & đầy màu sắc</p>
                    <span class="category-count">85+ sản phẩm</span>
                </a>
                <a href="<?= BASE_URL ?>/products?category=congratulation" class="category-card">
                    <div class="category-icon">🎉</div>
                    <h3>Hoa Chúc Mừng</h3>
                    <p>Thành công & phát triển</p>
                    <span class="category-count">95+ sản phẩm</span>
                </a>
                <a href="<?= BASE_URL ?>/products?category=love" class="category-card">
                    <div class="category-icon">💕</div>
                    <h3>Hoa Tình Yêu</h3>
                    <p>Ngọt ngào & chân thành</p>
                    <span class="category-count">150+ sản phẩm</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="products-section">
        <div class="container">
            <div class="section-header">
                <h2>Sản Phẩm Nổi Bật</h2>
                <p>Những mẫu hoa được yêu thích nhất</p>
            </div>
            <div class="products-grid">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): 
                        // Xử lý đường dẫn hình ảnh
                        if (!empty($product['image'])) {
                            // Nếu đã có đường dẫn đầy đủ
                            if (strpos($product['image'], 'http') === 0) {
                                $image = $product['image'];
                            } 
                            // Nếu là đường dẫn tương đối
                            elseif (strpos($product['image'], 'uploads/') === 0) {
                                $image = BASE_URL . '/' . $product['image'];
                            }
                            // Nếu chỉ là tên file
                            else {
                                $image = BASE_URL . '/uploads/products/' . $product['image'];
                            }
                        } else {
                            $image = ASSETS_URL . '/images/default-product.jpg';
                        }
                        
                        $avgRating = $product['avg_rating'] ?? 0;
                        $reviewCount = $product['review_count'] ?? 0;
                        $fullStars = floor($avgRating);
                        $hasHalfStar = ($avgRating - $fullStars) >= 0.5;
                        $discountPercent = $product['discount_percent'] ?? 0;
                    ?>
                    <div class="product-card" onclick="window.location.href='<?= BASE_URL ?>/products/detail/<?= $product['id'] ?>'" style="cursor: pointer;">
                        <div class="product-image">
                            <img src="<?= $image ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="width:100%;height:100%;object-fit:cover;">
                            <?php if ($product['has_promotion'] && $discountPercent > 0): ?>
                                <div class="product-badge">-<?= round($discountPercent) ?>%</div>
                            <?php endif; ?>
                            
                            <!-- Wishlist button -->
                            <button class="btn-wishlist-icon" 
                                    data-product-id="<?= $product['id'] ?>"
                                    onclick="event.stopPropagation();"
                                    title="Thêm vào yêu thích">
                                <i class="far fa-heart"></i>
                            </button>
                        </div>
                        <div class="product-info">
                            <h3><?= htmlspecialchars($product['name']) ?></h3>
                            <div class="product-rating">
                                <?php for ($i = 0; $i < $fullStars; $i++): ?>
                                    <i class="fas fa-star"></i>
                                <?php endfor; ?>
                                <?php if ($hasHalfStar): ?>
                                    <i class="fas fa-star-half-alt"></i>
                                <?php endif; ?>
                                <?php for ($i = $fullStars + ($hasHalfStar ? 1 : 0); $i < 5; $i++): ?>
                                    <i class="far fa-star"></i>
                                <?php endfor; ?>
                                <span>(<?= number_format($avgRating, 1) ?>)</span>
                            </div>
                            <div class="product-price">
                                <?php if ($product['has_promotion']): ?>
                                    <span class="price-old"><?= number_format($product['price']) ?>đ</span>
                                    <span class="price-new"><?= number_format($product['discounted_price']) ?>đ</span>
                                <?php else: ?>
                                    <span class="price-new"><?= number_format($product['price']) ?>đ</span>
                                <?php endif; ?>
                            </div>
                            <button class="btn-add-cart" onclick="addToCart(<?= $product['id'] ?>, event)">
                                <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Chưa có sản phẩm nào.</p>
                <?php endif; ?>
            </div>
            <div class="section-footer">
                <a href="<?= BASE_URL ?>/products" class="btn btn-outline-large">
                    Xem tất cả sản phẩm <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <h3>Giao Hàng Nhanh</h3>
                    <p>Giao trong 2 giờ tại nội thành. Miễn phí ship đơn từ 500k.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h3>Chất Lượng Đảm Bảo</h3>
                    <p>Hoa tươi 100%. Hoàn tiền nếu không hài lòng.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>Hỗ Trợ 24/7</h3>
                    <p>Tư vấn miễn phí, nhiệt tình. Hotline: 1900 1234.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-gift"></i>
                    </div>
                    <h3>Ưu Đãi Hấp Dẫn</h3>
                    <p>Giảm giá đến 30% cho khách hàng thân thiết.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section">
        <div class="container">
            <div class="section-header">
                <h2>Khách Hàng Nói Gì</h2>
                <p>Những đánh giá chân thực từ khách hàng</p>
            </div>
            <!-- Debug: <?= count($reviews ?? []) ?> reviews found -->
            <div class="testimonials-grid">
                <?php if (!empty($reviews) && count($reviews) > 0): ?>
                    <?php foreach (array_slice($reviews, 0, 3) as $review): 
                        $fullStars = $review['rating'];
                        $firstLetter = mb_substr($review['full_name'], 0, 1, 'UTF-8');
                        $timeAgo = '';
                        if (!empty($review['created_at'])) {
                            $time = strtotime($review['created_at']);
                            $diff = time() - $time;
                            if ($diff < 3600) {
                                $timeAgo = floor($diff / 60) . ' phút trước';
                            } elseif ($diff < 86400) {
                                $timeAgo = floor($diff / 3600) . ' giờ trước';
                            } elseif ($diff < 2592000) {
                                $timeAgo = floor($diff / 86400) . ' ngày trước';
                            } else {
                                $timeAgo = date('d/m/Y', $time);
                            }
                        }
                    ?>
                    <div class="testimonial-card">
                        <div class="testimonial-header">
                            <div class="testimonial-avatar">
                                <?php if (!empty($review['avatar'])): ?>
                                    <img src="<?= BASE_URL ?>/<?= htmlspecialchars($review['avatar']) ?>" 
                                         alt="<?= htmlspecialchars($review['full_name']) ?>"
                                         style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                <?php else: ?>
                                    <?= $firstLetter ?>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h4><?= htmlspecialchars($review['full_name']) ?></h4>
                                <div class="rating">
                                    <?php for ($i = 0; $i < $fullStars; $i++): ?>
                                        <i class="fas fa-star"></i>
                                    <?php endfor; ?>
                                    <?php for ($i = $fullStars; $i < 5; $i++): ?>
                                        <i class="far fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                        <p>"<?= htmlspecialchars($review['comment']) ?>"</p>
                        <?php if ($timeAgo): ?>
                            <span class="testimonial-date"><?= $timeAgo ?></span>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="testimonial-avatar">👨</div>
                        <div>
                            <h4>Nguyễn Văn A</h4>
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <p>"Hoa đẹp lắm, giao đúng giờ. Bạn gái mình rất thích. Sẽ ủng hộ shop lâu dài!"</p>
                    <span class="testimonial-date">2 ngày trước</span>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="testimonial-avatar">👩</div>
                        <div>
                            <h4>Trần Thị B</h4>
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <p>"Dịch vụ tuyệt vời! Nhân viên tư vấn nhiệt tình. Hoa tươi và đóng gói rất đẹp."</p>
                    <span class="testimonial-date">1 tuần trước</span>
                </div>
                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="testimonial-avatar">👨</div>
                        <div>
                            <h4>Lê Văn C</h4>
                            <div class="rating">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                        </div>
                    </div>
                    <p>"Đặt hoa online rất tiện. Giá cả hợp lý, chất lượng tốt. Highly recommended!"</p>
                    <span class="testimonial-date">2 tuần trước</span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

    <?php include APP_PATH . '/views/layouts/toast_notification.php'; ?>

    <script>
        // Add to cart function
        function addToCart(productId, event) {
            // Stop event propagation
            if (event) {
                event.preventDefault();
                event.stopPropagation();
                event.stopImmediatePropagation();
            }
            
            fetch('<?= BASE_URL ?>/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId + '&quantity=1'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart badge - chọn đúng badge giỏ hàng
                    const cartBadge = document.querySelector('.cart-count');
                    if (cartBadge && (data.cartCount !== undefined || data.cart_count !== undefined)) {
                        cartBadge.textContent = data.cartCount || data.cart_count;
                        // Animation
                        cartBadge.style.transform = 'scale(1.3)';
                        setTimeout(() => {
                            cartBadge.style.transform = 'scale(1)';
                        }, 200);
                    }
                    
                    // Show success message
                    alert('Đã thêm sản phẩm vào giỏ hàng!');
                } else {
                    alert(data.message || 'Có lỗi xảy ra');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra');
            });
        }
    </script>
    
    <!-- Slider JavaScript -->
    <script>
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slider-slide');
        const dots = document.querySelectorAll('.slider-dot');
        const totalSlides = slides.length;
        
        // Auto-play slider
        let autoplayInterval;
        
        function showSlide(index) {
            // Remove active class from all slides and dots
            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));
            
            // Wrap around
            if (index >= totalSlides) {
                currentSlide = 0;
            } else if (index < 0) {
                currentSlide = totalSlides - 1;
            } else {
                currentSlide = index;
            }
            
            // Add active class to current slide and dot
            slides[currentSlide].classList.add('active');
            if (dots[currentSlide]) {
                dots[currentSlide].classList.add('active');
            }
        }
        
        function changeSlide(direction) {
            showSlide(currentSlide + direction);
            resetAutoplay();
        }
        
        function goToSlide(index) {
            showSlide(index);
            resetAutoplay();
        }
        
        function startAutoplay() {
            if (totalSlides > 1) {
                autoplayInterval = setInterval(() => {
                    showSlide(currentSlide + 1);
                }, 5000); // Change slide every 5 seconds
            }
        }
        
        function stopAutoplay() {
            clearInterval(autoplayInterval);
        }
        
        function resetAutoplay() {
            stopAutoplay();
            startAutoplay();
        }
        
        // Pause autoplay on hover
        const sliderContainer = document.querySelector('.slider-container');
        if (sliderContainer) {
            sliderContainer.addEventListener('mouseenter', stopAutoplay);
            sliderContainer.addEventListener('mouseleave', startAutoplay);
        }
        
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                changeSlide(-1);
            } else if (e.key === 'ArrowRight') {
                changeSlide(1);
            }
        });
        
        // Touch/Swipe support for mobile
        let touchStartX = 0;
        let touchEndX = 0;
        
        if (sliderContainer) {
            sliderContainer.addEventListener('touchstart', (e) => {
                touchStartX = e.changedTouches[0].screenX;
            });
            
            sliderContainer.addEventListener('touchend', (e) => {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe();
            });
        }
        
        function handleSwipe() {
            const swipeThreshold = 50;
            const diff = touchStartX - touchEndX;
            
            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    // Swipe left - next slide
                    changeSlide(1);
                } else {
                    // Swipe right - previous slide
                    changeSlide(-1);
                }
            }
        }
        
        // Start autoplay when page loads
        if (totalSlides > 1) {
            startAutoplay();
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
        });
    </script>
</body>
</html>
