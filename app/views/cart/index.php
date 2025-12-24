<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ hàng - Flower Shop</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/home.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/notifications.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/cart-index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="<?= BASE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a>
        <span class="separator">/</span>
        <span class="current">Giỏ hàng</span>
    </div>
    
    <div class="cart-container">
        <?php if (empty($cartItems)): ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h2>Giỏ hàng trống</h2>
                <p>Bạn chưa có sản phẩm nào trong giỏ hàng</p>
                <a href="<?= BASE_URL ?>/products" class="btn-checkout">Mua sắm ngay</a>
            </div>
        <?php else: ?>
            <div class="cart-content">
                <!-- Left Column: Cart Items + Summary -->
                <div class="cart-left">
                    <!-- Cart Items -->
                    <div class="cart-items">
                        <h3 class="cart-items-title">
                            <i class="fas fa-shopping-cart"></i> Sản phẩm trong giỏ hàng
                        </h3>
                        <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item">
                            <div class="cart-item-image">
                                <?php 
                                $imagePath = $item['image'];
                                // Nếu path không bắt đầu bằng http hoặc /, thêm BASE_URL
                                if (!preg_match('/^(http|\/)/i', $imagePath)) {
                                    $imagePath = BASE_URL . '/' . $imagePath;
                                } else if (strpos($imagePath, 'public/') === 0 || strpos($imagePath, '/public/') === 0) {
                                    // Nếu có public/ trong path, thay bằng BASE_URL
                                    $imagePath = BASE_URL . '/' . ltrim(str_replace('public/', '', $imagePath), '/');
                                }
                                ?>
                                <img src="<?= $imagePath ?>" 
                                     alt="<?= htmlspecialchars($item['name']) ?>"
                                     onerror="this.src='<?= BASE_URL ?>/public/assets/images/no-image.png'">
                            </div>
                            <div class="cart-item-info">
                                <h3><?= htmlspecialchars($item['name']) ?></h3>
                                <div class="cart-item-price">
                                    <?php if (isset($item['actual_price']) && $item['actual_price'] < $item['price']): ?>
                                        <!-- Có giảm giá -->
                                        <span style="color: #e91e63; font-weight: bold; margin-right: 10px;">
                                            <?= number_format($item['actual_price'], 0, ',', '.') ?>đ
                                        </span>
                                        <span style="text-decoration: line-through; color: #999; font-size: 14px;">
                                            <?= number_format($item['price'], 0, ',', '.') ?>đ
                                        </span>
                                    <?php else: ?>
                                        <!-- Giá gốc -->
                                        <?= number_format($item['price'], 0, ',', '.') ?>đ
                                    <?php endif; ?>
                                </div>
                                <div class="cart-item-quantity">
                                    <form method="POST" action="<?= BASE_URL ?>/cart/update" style="display: flex; gap: 10px; align-items: center;">
                                        <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">
                                        <label>Số lượng:</label>
                                        <input type="number" name="quantity" value="<?= $item['quantity'] ?>" 
                                               min="1" max="<?= $item['stock_quantity'] ?>" 
                                               class="quantity-input" onchange="this.form.submit()">
                                    </form>
                                    <span>Tồn kho: <?= $item['stock_quantity'] ?></span>
                                </div>
                                <div style="margin-top: 10px;">
                                    <strong>Thành tiền: <?= number_format($item['actual_price'] * $item['quantity'], 0, ',', '.') ?>đ</strong>
                                </div>
                            </div>
                            <div>
                                <button class="btn-remove" onclick="showConfirm('<?= BASE_URL ?>/cart/remove/<?= $item['product_id'] ?>')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Cart Summary -->
                    <div class="cart-summary">
                        <h2 class="summary-title">Tổng đơn hàng</h2>
                    
                    <!-- Summary Details -->
                    <div class="summary-row">
                        <span>Tạm tính:</span>
                        <span><?= number_format($subtotal, 0, ',', '.') ?>đ</span>
                    </div>
                    
                    <div class="summary-row total">
                        <span>Tổng cộng:</span>
                        <span><?= number_format($subtotal, 0, ',', '.') ?>đ</span>
                    </div>
                    
                    <a href="<?= BASE_URL ?>/orders/checkout" class="btn-checkout">
                        <i class="fas fa-credit-card"></i> Thanh toán
                    </a>
                    
                    <?php if (Session::isLoggedIn()): ?>
                    <a href="<?= BASE_URL ?>/address" 
                       style="display: flex; align-items: center; justify-content: center; gap: 8px; padding: 12px; background: white; border: 2px solid #ff6b9d; color: #ff6b9d; border-radius: 8px; text-decoration: none; font-weight: 600; margin: 10px 0; transition: all 0.3s;">
                        <i class="fas fa-map-marker-alt"></i> Quản lý địa chỉ giao hàng
                    </a>
                    <?php endif; ?>
                    
                        <a href="<?= BASE_URL ?>/products" class="continue-shopping">
                            <i class="fas fa-arrow-left"></i> Tiếp tục mua sắm
                        </a>
                    </div>
                </div>
                
                <!-- Right Column: Recommended Products -->
                <?php if (!empty($recommendedProducts)): ?>
                <div class="recommended-products">
                    <h3 class="recommended-title">
                        <i class="fas fa-heart"></i>
                        Có thể bạn cũng thích
                    </h3>
                    <div class="recommended-grid">
                        <?php foreach ($recommendedProducts as $product): ?>
                            <div class="recommended-item" onclick="window.location.href='<?= BASE_URL ?>/products/detail/<?= $product['id'] ?>'">
                                <div class="recommended-image">
                                    <?php 
                                    $image = ASSETS_URL . '/images/default-product.jpg';
                                    if (!empty($product['image'])) {
                                        if (strpos($product['image'], 'http') === 0) {
                                            $image = $product['image'];
                                        } elseif (strpos($product['image'], 'uploads/') === 0) {
                                            $image = BASE_URL . '/' . $product['image'];
                                        } else {
                                            $image = BASE_URL . '/uploads/products/' . $product['image'];
                                        }
                                    }
                                    ?>
                                    <img src="<?= $image ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                                </div>
                                <div class="recommended-info">
                                    <div class="recommended-name"><?= htmlspecialchars($product['name']) ?></div>
                                    <div class="recommended-price">
                                        <?php if ($product['has_promotion']): ?>
                                            <?= number_format($product['discounted_price']) ?>đ
                                            <span class="old-price"><?= number_format($product['price']) ?>đ</span>
                                        <?php else: ?>
                                            <?= number_format($product['price']) ?>đ
                                        <?php endif; ?>
                                    </div>
                                    <button class="recommended-add" onclick="event.stopPropagation(); addToCart(<?= $product['id'] ?>, event)">
                                        <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Custom Confirm Dialog -->
    <div class="confirm-overlay" id="confirmOverlay">
        <div class="confirm-dialog">
            <div class="confirm-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="confirm-title">Xóa sản phẩm?</div>
            <div class="confirm-message">Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?</div>
            <div class="confirm-actions">
                <button class="confirm-btn confirm-btn-cancel" onclick="closeConfirm()">Hủy</button>
                <button class="confirm-btn confirm-btn-confirm" id="confirmButton">Xóa</button>
            </div>
        </div>
    </div>
    
    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

    <script>
        // Toast notification function
        function showToast(type, title, message) {
            // Xóa toast cũ nếu có
            const existingToast = document.querySelector('.toast');
            if (existingToast) {
                existingToast.remove();
            }

            // Tạo toast mới
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            const iconMap = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };
            
            toast.innerHTML = `
                <div class="toast-icon">
                    <i class="fas ${iconMap[type]}"></i>
                </div>
                <div class="toast-content">
                    <div class="toast-title">${title}</div>
                    <div class="toast-message">${message}</div>
                </div>
                <div class="toast-close">
                    <i class="fas fa-times"></i>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            // Hiển thị toast
            setTimeout(() => toast.classList.add('show'), 10);
            
            // Xử lý nút đóng
            toast.querySelector('.toast-close').addEventListener('click', () => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 400);
            });
            
            // Tự động ẩn sau 5 giây
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 400);
            }, 5000);
        }

        // Hiển thị flash messages từ PHP session
        <?php if (Session::hasFlash('success')): ?>
            showToast('success', 'Thành công!', '<?= addslashes(Session::getFlash('success')) ?>');
        <?php endif; ?>

        <?php if (Session::hasFlash('error')): ?>
            showToast('error', 'Lỗi!', '<?= addslashes(Session::getFlash('error')) ?>');
        <?php endif; ?>

        // Custom Confirm Dialog
        let confirmCallback = null;
        
        function showConfirm(url) {
            confirmCallback = url;
            document.getElementById('confirmOverlay').classList.add('show');
        }
        
        function closeConfirm() {
            document.getElementById('confirmOverlay').classList.remove('show');
            confirmCallback = null;
        }
        
        document.getElementById('confirmButton').addEventListener('click', function() {
            if (confirmCallback) {
                window.location.href = confirmCallback;
            }
        });
        
        // Close on overlay click
        document.getElementById('confirmOverlay').addEventListener('click', function(e) {
            if (e.target === this) {
                closeConfirm();
            }
        });
        
        // Add to cart from recommended products
        function addToCart(productId, event) {
            // Prevent event bubbling and default action
            if (event) {
                event.preventDefault();
                event.stopPropagation();
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
                    showToast('success', 'Thành công!', data.message);
                    // Reload trang để cập nhật giỏ hàng
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showToast('error', 'Lỗi!', data.message || 'Có lỗi xảy ra');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('error', 'Lỗi!', 'Có lỗi xảy ra, vui lòng thử lại');
            });
        }
        
        // Xử lý nút thanh toán (chỉ có khi giỏ hàng có sản phẩm)
        const checkoutBtn = document.querySelector('.cart-summary .btn-checkout');
        if (checkoutBtn) {
            checkoutBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Hiển thị thông báo đang xử lý
                showToast('info', 'Đang xử lý...', 'Vui lòng chờ trong giây lát.');
                
                // Chuyển trang sau 1 giây
                setTimeout(() => {
                    window.location.href = '<?= BASE_URL ?>/orders/checkout';
                }, 1000);
            });
        }
    </script>
</body>
</html>
