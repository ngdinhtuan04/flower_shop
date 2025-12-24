<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Danh sách yêu thích' ?> - Flower Shop</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/home.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/wishlist.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/notifications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="<?= ASSETS_URL ?>/js/confirm-dialog.js"></script>
</head>
<body>
    <?php require_once APP_PATH . '/views/layouts/header.php'; ?>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="<?= BASE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a>
        <span class="separator">/</span>
        <span class="current">Danh sách yêu thích</span>
    </div>

    <!-- Main Content -->
    <div class="wishlist-container">
        <?php if (Session::hasFlash('success')): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= Session::getFlash('success') ?>
            </div>
        <?php endif; ?>

        <?php if (Session::hasFlash('error')): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= Session::getFlash('error') ?>
            </div>
        <?php endif; ?>

        <?php if (empty($wishlistItems)): ?>
            <!-- Empty Wishlist -->
            <div class="empty-wishlist">
                <div class="empty-icon">
                    <i class="fas fa-heart-broken"></i>
                </div>
                <h2>Danh sách yêu thích trống</h2>
                <p>Bạn chưa thêm sản phẩm nào vào danh sách yêu thích</p>
                <a href="<?= BASE_URL ?>/products" class="btn btn-primary">
                    <i class="fas fa-spa"></i> Khám phá sản phẩm
                </a>
            </div>
        <?php else: ?>
            <!-- Wishlist Header -->
            <div class="wishlist-header">
                <h2>
                    <i class="fas fa-heart"></i> 
                    Danh sách yêu thích 
                    <span class="count">(<?= $wishlistCount ?> sản phẩm)</span>
                </h2>
                <button class="btn btn-outline" onclick="clearWishlist()">
                    <i class="fas fa-trash"></i> Xóa tất cả
                </button>
            </div>

            <!-- Wishlist Grid -->
            <div class="wishlist-grid">
                <?php foreach ($wishlistItems as $item): 
                    // Xử lý đường dẫn hình ảnh
                    if (!empty($item['product_image'])) {
                        // Nếu đã có đường dẫn đầy đủ
                        if (strpos($item['product_image'], 'http') === 0) {
                            $image = $item['product_image'];
                        } 
                        // Nếu là đường dẫn tương đối
                        elseif (strpos($item['product_image'], 'uploads/') === 0) {
                            $image = BASE_URL . '/' . $item['product_image'];
                        }
                        // Nếu chỉ là tên file
                        else {
                            $image = BASE_URL . '/uploads/products/' . $item['product_image'];
                        }
                    } else {
                        $image = ASSETS_URL . '/images/default-product.jpg';
                    }
                ?>
                    <div class="wishlist-item" data-product-id="<?= $item['product_id'] ?>">
                        <!-- Remove Button -->
                        <button class="btn-remove" onclick="removeFromWishlist(<?= $item['product_id'] ?>)" title="Xóa khỏi danh sách">
                            <i class="fas fa-times"></i>
                        </button>

                        <!-- Product Image -->
                        <div class="product-image">
                            <a href="<?= BASE_URL ?>/products/detail/<?= $item['product_id'] ?>">
                                <img src="<?= $image ?>" 
                                     alt="<?= htmlspecialchars($item['product_name']) ?>"
                                     style="width:100%;height:100%;object-fit:cover;">
                            </a>
                            
                            <!-- Badges -->
                            <?php if (isset($item['has_discount']) && $item['has_discount']): ?>
                                <span class="badge badge-sale">
                                    -<?= round($item['discount_percent']) ?>%
                                </span>
                            <?php endif; ?>
                            
                            <?php if ($item['stock'] <= 0): ?>
                                <span class="badge badge-out-of-stock">Hết hàng</span>
                            <?php elseif ($item['stock'] <= 5): ?>
                                <span class="badge badge-low-stock">Còn <?= $item['stock'] ?></span>
                            <?php endif; ?>
                        </div>

                        <!-- Product Info -->
                        <div class="product-info">
                            <div class="product-category">
                                <?= htmlspecialchars($item['category_name'] ?? 'Chưa phân loại') ?>
                            </div>
                            
                            <h3 class="product-name">
                                <a href="<?= BASE_URL ?>/products/detail/<?= $item['product_id'] ?>">
                                    <?= htmlspecialchars($item['product_name']) ?>
                                </a>
                            </h3>

                            <!-- Rating -->
                            <?php if ($item['review_count'] > 0): ?>
                                <div class="product-rating">
                                    <div class="stars">
                                        <?php 
                                        $rating = round($item['avg_rating'], 1);
                                        $fullStars = floor($rating);
                                        $halfStar = ($rating - $fullStars) >= 0.5;
                                        $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
                                        
                                        for ($i = 0; $i < $fullStars; $i++): ?>
                                            <i class="fas fa-star"></i>
                                        <?php endfor; ?>
                                        
                                        <?php if ($halfStar): ?>
                                            <i class="fas fa-star-half-alt"></i>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = 0; $i < $emptyStars; $i++): ?>
                                            <i class="far fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="rating-text"><?= number_format($rating, 1) ?> (<?= $item['review_count'] ?>)</span>
                                </div>
                            <?php endif; ?>

                            <!-- Price -->
                            <div class="product-price">
                                <?php if (isset($item['has_discount']) && $item['has_discount']): ?>
                                    <span class="price-old"><?= number_format($item['product_price']) ?>₫</span>
                                    <span class="price-current"><?= number_format($item['final_price']) ?>₫</span>
                                <?php else: ?>
                                    <span class="price-current"><?= number_format($item['product_price']) ?>₫</span>
                                <?php endif; ?>
                            </div>

                            <!-- Actions -->
                            <div class="product-actions">
                                <?php if ($item['stock'] > 0): ?>
                                    <button class="btn btn-primary btn-add-cart" 
                                            onclick="addToCart(<?= $item['product_id'] ?>)">
                                        <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-disabled" disabled>
                                        <i class="fas fa-ban"></i> Hết hàng
                                    </button>
                                <?php endif; ?>
                                
                                <a href="<?= BASE_URL ?>/products/detail/<?= $item['product_id'] ?>" 
                                   class="btn btn-outline">
                                    <i class="fas fa-eye"></i> Chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php require_once APP_PATH . '/views/layouts/footer.php'; ?>

    <!-- Wishlist JavaScript -->
    <script src="<?= ASSETS_URL ?>/js/wishlist.js"></script>
    <script>
        // Hàm xóa khỏi wishlist
        function removeFromWishlist(productId) {
            confirmDelete({
                title: 'Xóa khỏi yêu thích',
                message: 'Bạn có chắc muốn xóa sản phẩm này khỏi danh sách yêu thích?',
                confirmText: 'Xóa',
                theme: 'user'
            }).then(confirmed => {
                if (!confirmed) return;

                fetch(window.BASE_URL + '/wishlist/remove', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'product_id=' + productId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Xóa item khỏi DOM
                        const item = document.querySelector(`.wishlist-item[data-product-id="${productId}"]`);
                        if (item) {
                            item.style.animation = 'fadeOut 0.3s ease';
                            setTimeout(() => {
                                item.remove();
                                
                                // Kiểm tra nếu không còn item nào
                                const remainingItems = document.querySelectorAll('.wishlist-item');
                                if (remainingItems.length === 0) {
                                    location.reload();
                                } else {
                                    // Cập nhật số lượng
                                    const countElement = document.querySelector('.wishlist-header .count');
                                    if (countElement) {
                                        countElement.textContent = `(${remainingItems.length} sản phẩm)`;
                                    }
                                }
                            }, 300);
                        }
                        
                        // Hiển thị thông báo
                        showNotification(data.message, 'success');
                        
                        // Cập nhật badge ở header
                        window.updateWishlistBadge(data.wishlist_count);
                    } else {
                        showNotification(data.message || 'Có lỗi xảy ra', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Có lỗi xảy ra, vui lòng thử lại', 'error');
                });
            });
        }

        // Hàm xóa tất cả
        function clearWishlist() {
            confirmDelete({
                title: 'Xóa tất cả',
                message: 'Bạn có chắc muốn xóa <strong>tất cả</strong> sản phẩm khỏi danh sách yêu thích?',
                confirmText: 'Xóa tất cả',
                theme: 'user'
            }).then(confirmed => {
                if (!confirmed) return;

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = window.BASE_URL + '/wishlist/clear';
                document.body.appendChild(form);
                form.submit();
            });
        }

        // Hàm thêm vào giỏ hàng
        function addToCart(productId) {
            fetch(window.BASE_URL + '/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId + '&quantity=1'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message || 'Đã thêm vào giỏ hàng', 'success');
                    
                    // Cập nhật badge giỏ hàng
                    const cartBadge = document.querySelector('.cart-count');
                    if (cartBadge && data.cart_count) {
                        cartBadge.textContent = data.cart_count;
                    }
                } else {
                    showNotification(data.message || 'Không thể thêm vào giỏ hàng', 'error');
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1500);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Có lỗi xảy ra', 'error');
            });
        }

        // Hàm hiển thị thông báo
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            `;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.add('show');
            }, 10);
            
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // CSS cho animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeOut {
                from { opacity: 1; transform: scale(1); }
                to { opacity: 0; transform: scale(0.9); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
