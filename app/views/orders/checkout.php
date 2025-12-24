<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán - Flower Shop</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/home.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/order/checkout.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/notifications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <?php include APP_PATH . '/views/layouts/header.php'; ?>
    
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="<?= BASE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a>
        <span class="separator">/</span>
        <a href="<?= BASE_URL ?>/cart">Giỏ hàng</a>
        <span class="separator">/</span>
        <span class="current">Thanh toán</span>
    </div>
    
    <div class="checkout-container">
        <form action="<?= BASE_URL ?>/orders/place-order" method="POST" id="checkoutForm">
            <div class="checkout-content">
                <!-- PHẦN 1: Thông tin người nhận & Địa chỉ -->
                <div class="section-container">
                    <div class="form-section">
                        <h3><i class="fas fa-user"></i> 1. Thông tin người nhận & Địa chỉ giao hàng</h3>
                        <div class="form-group">
                            <label>Họ và tên <span>*</span></label>
                            <input type="text" name="customer_name" 
                                   value="<?php 
                                       if ($defaultAddress) {
                                           echo htmlspecialchars($defaultAddress['recipient_name']);
                                       } elseif ($user) {
                                           echo htmlspecialchars($user['full_name']);
                                       }
                                   ?>" 
                                   required>
                        </div>
                        
                        <div class="section-row">
                            <div class="form-group">
                                <label>Số điện thoại <span>*</span></label>
                                <input type="tel" name="customer_phone" 
                                       value="<?php 
                                           if ($defaultAddress) {
                                               echo htmlspecialchars($defaultAddress['phone']);
                                           } elseif ($user) {
                                               echo htmlspecialchars($user['phone'] ?? '');
                                           }
                                       ?>" 
                                       required>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="customer_email" 
                                       value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                            </div>
                        </div>
                        </div>
                        
                        <?php if (!empty($addresses)): ?>
                        <!-- Chọn từ địa chỉ có sẵn -->
                        <div class="form-group">
                            <label>Chọn địa chỉ giao hàng</label>
                            <select id="savedAddressSelect" class="form-control" onchange="fillAddressFromSaved(this.value)">
                                <option value="">-- Nhập địa chỉ mới --</option>
                                <?php foreach ($addresses as $addr): ?>
                                    <option value="<?= $addr['id'] ?>" 
                                            data-name="<?= htmlspecialchars($addr['recipient_name']) ?>"
                                            data-phone="<?= htmlspecialchars($addr['phone']) ?>"
                                            data-province="<?= htmlspecialchars($addr['province']) ?>"
                                            data-district="<?= htmlspecialchars($addr['district']) ?>"
                                            data-ward="<?= htmlspecialchars($addr['ward']) ?>"
                                            data-detail="<?= htmlspecialchars($addr['address_detail']) ?>"
                                            data-full="<?= htmlspecialchars(UserAddress::formatFullAddress($addr)) ?>"
                                            <?= ($defaultAddress && $addr['id'] == $defaultAddress['id']) ? 'selected' : '' ?>>
                                        <?php if ($addr['is_default']): ?>⭐ <?php endif; ?>
                                        <?= htmlspecialchars($addr['recipient_name']) ?> - <?= htmlspecialchars($addr['phone']) ?> - 
                                        <?= UserAddress::getTypeLabel($addr['address_type']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div style="margin: 15px 0; text-align: center;">
                            <button type="button" class="btn-add-address-checkout" onclick="openAddAddressModal()">
                                <i class="fas fa-plus"></i> Thêm địa chỉ mới
                            </button>
                            <a href="<?= BASE_URL ?>/address" target="_blank" class="btn-manage-address">
                                <i class="fas fa-cog"></i> Quản lý địa chỉ
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <div class="form-group" id="shippingAddressSection">
                            <label>Địa chỉ chi tiết <span>*</span></label>
                            <textarea name="shipping_address" id="shippingAddressInput" required><?php 
                                if ($defaultAddress) {
                                    echo htmlspecialchars(UserAddress::formatFullAddress($defaultAddress));
                                } elseif (!empty($user['address'])) {
                                    echo htmlspecialchars($user['address']);
                                }
                            ?></textarea>
                            <small style="color: #718096; font-size: 13px;">
                                <i class="fas fa-info-circle"></i> 
                                Địa chỉ đầy đủ: Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành phố
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label>Ghi chú đơn hàng</label>
                            <textarea name="shipping_note" placeholder="Ghi chú về đơn hàng, ví dụ: thời gian hay chỉ dẫn địa điểm giao hàng chi tiết hơn"></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- PHẦN 2: Hình thức giao hàng & Phương thức thanh toán -->
                <div class="section-container">
                    <div class="section-row">
                        <!-- Hình thức giao hàng -->
                        <div class="shipping-section">
                            <h3 class="sub-heading">
                                <i class="fas fa-shipping-fast" style="color: #48bb78;"></i> 2. Hình thức giao hàng
                            </h3>
                            <div class="shipping-methods">
                                <label class="shipping-method selected">
                                    <input type="radio" name="shipping_method" value="standard" data-fee="30000" checked>
                                    <div class="shipping-content">
                                        <div class="shipping-icon">
                                            <i class="fas fa-truck"></i>
                                        </div>
                                        <div class="shipping-info">
                                            <h4>Tiêu chuẩn</h4>
                                            <p>2-3 ngày - 30,000đ</p>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="shipping-method">
                                    <input type="radio" name="shipping_method" value="express" data-fee="50000">
                                    <div class="shipping-content">
                                        <div class="shipping-icon">
                                            <i class="fas fa-rocket"></i>
                                        </div>
                                        <div class="shipping-info">
                                            <h4>Nhanh</h4>
                                            <p>24 giờ - 50,000đ</p>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="shipping-method">
                                    <input type="radio" name="shipping_method" value="same_day" data-fee="80000">
                                    <div class="shipping-content">
                                        <div class="shipping-icon">
                                            <i class="fas fa-shipping-fast"></i>
                                        </div>
                                        <div class="shipping-info">
                                            <h4>Trong ngày</h4>
                                            <p>2-4 giờ - 80,000đ</p>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="shipping-method">
                                    <input type="radio" name="shipping_method" value="pickup" data-fee="0">
                                    <div class="shipping-content">
                                        <div class="shipping-icon">
                                            <i class="fas fa-store"></i>
                                        </div>
                                        <div class="shipping-info">
                                            <h4>Nhận tại cửa hàng</h4>
                                            <p>Miễn phí</p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Phương thức thanh toán -->
                        <div class="payment-section">
                            <h3 class="sub-heading">
                                <i class="fas fa-credit-card" style="color: #667eea;"></i> 3. Phương thức thanh toán
                            </h3>
                            <div class="payment-methods">
                                <label class="payment-method selected">
                                    <input type="radio" name="payment_method" value="cod" checked>
                                    <div class="payment-content">
                                        <div class="payment-icon">
                                            <i class="fas fa-money-bill-wave"></i>
                                        </div>
                                        <div class="payment-info">
                                            <h4>COD</h4>
                                            <p>Thanh toán khi nhận hàng</p>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="payment-method">
                                    <input type="radio" name="payment_method" value="vnpay">
                                    <div class="payment-content">
                                        <div class="payment-icon">
                                            <i class="fas fa-wallet"></i>
                                        </div>
                                        <div class="payment-info">
                                            <h4>VNPay</h4>
                                            <p>Thanh toán trực tuyến</p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- PHẦN 3: Đơn hàng của bạn -->
                <div class="order-summary">
                    <h3><i class="fas fa-shopping-bag"></i> 4. Đơn hàng của bạn</h3>
                    
                    <div class="order-items-grid">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="order-item">
                            <div class="order-item-image">
                                <?php 
                                // Kiểm tra cấu trúc dữ liệu (hỗ trợ cả 2 format)
                                $imagePath = isset($item['product']['image']) ? $item['product']['image'] : $item['image'];
                                $productName = isset($item['product']['name']) ? $item['product']['name'] : $item['name'];
                                
                                if (!preg_match('/^(http|\/)/i', $imagePath)) {
                                    $imagePath = BASE_URL . '/' . $imagePath;
                                }
                                ?>
                                <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($productName) ?>">
                            </div>
                            <div class="order-item-info">
                                <h4><?= htmlspecialchars($productName) ?></h4>
                                <p>SL: <?= $item['quantity'] ?></p>
                                <?php if (isset($item['has_promotion']) && $item['has_promotion']): ?>
                                    <p>
                                        <span style="text-decoration: line-through; color: #999; font-size: 12px;">
                                            <?= number_format($item['original_price'], 0, ',', '.') ?>đ
                                        </span>
                                        <br>
                                        <span style="color: #e91e63; font-weight: bold;">
                                            <?= number_format($item['actual_price'], 0, ',', '.') ?>đ
                                        </span>
                                    </p>
                                <?php else: ?>
                                    <p style="font-weight: 600;"><?= number_format($item['actual_price'], 0, ',', '.') ?>đ</p>
                                <?php endif; ?>
                                <p style="color: #ff6b9d; font-weight: bold; font-size: 14px;">
                                    <?= number_format($item['subtotal'], 0, ',', '.') ?>đ
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                    
                    <div class="order-total">
                        <!-- Form mã giảm giá -->
                        <div style="margin-bottom: 25px; padding: 20px; background: #f8f9fa; border-radius: 10px; border: 1px solid #e9ecef;">
                            <!-- Mã giảm giá sản phẩm -->
                            <div style="margin-bottom: 18px;">
                                <label style="display: block; margin-bottom: 10px; color: #2d3748; font-size: 14px; font-weight: 600;">
                                    🛍️ Mã giảm giá sản phẩm
                                </label>
                                <?php if (isset($productCoupon) && $productCoupon): ?>
                                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px;">
                                        <div>
                                            <strong style="color: #155724;">
                                                <i class="fas fa-tag"></i> <?= htmlspecialchars($productCoupon['code']) ?>
                                            </strong>
                                            <p style="margin: 5px 0 0 0; font-size: 13px; color: #155724;">
                                                <?= htmlspecialchars($productCoupon['description']) ?>
                                            </p>
                                        </div>
                                        <form method="POST" action="<?= BASE_URL ?>/orders/remove-coupon" style="display: inline;">
                                            <input type="hidden" name="type" value="product">
                                            <button type="button" 
                                                    class="delete-coupon-btn"
                                                    data-coupon-type="sản phẩm"
                                                    style="background: none; border: none; color: #dc3545; cursor: pointer; font-size: 20px; transition: all 0.3s;">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <div style="display: flex; gap: 10px;">
                                        <input type="text" 
                                               id="productCouponInput"
                                               placeholder="Nhập mã giảm giá sản phẩm" 
                                               style="flex: 1; padding: 12px 15px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; text-transform: uppercase; transition: border 0.3s;">
                                        <button type="button" 
                                                class="apply-coupon-btn"
                                                data-input="productCouponInput"
                                                data-type="product"
                                                style="padding: 12px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.3s; box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);"
                                                onmouseover="this.style.background='#5568d3'" 
                                                onmouseout="this.style.background='#667eea'">
                                            Áp dụng
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Mã giảm phí vận chuyển -->
                            <div id="shippingCouponSection">
                                <label style="display: block; margin-bottom: 10px; color: #2d3748; font-size: 14px; font-weight: 600;">
                                    🚚 Mã giảm phí vận chuyển
                                </label>
                                <?php if (isset($shippingCoupon) && $shippingCoupon): ?>
                                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 8px;">
                                        <div>
                                            <strong style="color: #0c5460;">
                                                <i class="fas fa-tag"></i> <?= htmlspecialchars($shippingCoupon['code']) ?>
                                            </strong>
                                            <p style="margin: 5px 0 0 0; font-size: 13px; color: #0c5460;">
                                                <?= htmlspecialchars($shippingCoupon['description']) ?>
                                            </p>
                                        </div>
                                        <form method="POST" action="<?= BASE_URL ?>/orders/remove-coupon" style="display: inline;">
                                            <input type="hidden" name="type" value="shipping">
                                            <button type="button" 
                                                    class="delete-coupon-btn"
                                                    data-coupon-type="phí vận chuyển"
                                                    style="background: none; border: none; color: #dc3545; cursor: pointer; font-size: 20px; transition: all 0.3s;">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <div style="display: flex; gap: 10px;">
                                        <input type="text" 
                                               id="shippingCouponInput"
                                               placeholder="Nhập mã giảm phí vận chuyển" 
                                               style="flex: 1; padding: 12px 15px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; text-transform: uppercase; transition: border 0.3s;">
                                        <button type="button" 
                                                class="apply-coupon-btn"
                                                data-input="shippingCouponInput"
                                                data-type="shipping"
                                                style="padding: 12px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.3s; box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);"
                                                onmouseover="this.style.background='#5568d3'" 
                                                onmouseout="this.style.background='#667eea'">
                                            Áp dụng
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="total-row">
                            <span>Tạm tính:</span>
                            <span><?= number_format($subtotal, 0, ',', '.') ?>đ</span>
                        </div>
                        <div class="total-row">
                            <span>Phí vận chuyển:</span>
                            <span><?= number_format($shippingFee, 0, ',', '.') ?>đ</span>
                        </div>
                        <?php if ($productDiscount > 0): ?>
                            <div class="total-row discount">
                                <span><i class="fas fa-tag"></i> Giảm giá sản phẩm:</span>
                                <span>-<?= number_format($productDiscount, 0, ',', '.') ?>đ</span>
                            </div>
                        <?php endif; ?>
                        <?php if ($shippingDiscount > 0): ?>
                            <div class="total-row discount">
                                <span><i class="fas fa-shipping-fast"></i> Giảm phí ship:</span>
                                <span>-<?= number_format($shippingDiscount, 0, ',', '.') ?>đ</span>
                            </div>
                        <?php endif; ?>
                        <div class="total-row highlight">
                            <span>Tổng cộng:</span>
                            <span><?= number_format($total, 0, ',', '.') ?>đ</span>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-check-circle"></i> Đặt hàng
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Footer -->
    <?php include APP_PATH . '/views/layouts/footer.php'; ?>
    
    <script>
        // Custom confirm dialog function
        function confirmDeleteCoupon(type) {
            return new Promise((resolve) => {
                const overlay = document.createElement('div');
                overlay.className = 'custom-confirm-overlay';
                
                const dialog = document.createElement('div');
                dialog.className = 'custom-confirm-dialog';
                dialog.innerHTML = `
                    <div class="custom-confirm-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="custom-confirm-title">Xác nhận xóa mã giảm giá</div>
                    <div class="custom-confirm-message">
                        Bạn có chắc muốn xóa mã giảm giá ${type}?<br>
                        Hành động này không thể hoàn tác.
                    </div>
                    <div class="custom-confirm-buttons">
                        <button class="custom-confirm-btn custom-confirm-btn-cancel">
                            <i class="fas fa-times"></i> Hủy
                        </button>
                        <button class="custom-confirm-btn custom-confirm-btn-confirm">
                            <i class="fas fa-trash-alt"></i> Xóa
                        </button>
                    </div>
                `;
                
                overlay.appendChild(dialog);
                document.body.appendChild(overlay);
                
                const cancelBtn = dialog.querySelector('.custom-confirm-btn-cancel');
                const confirmBtn = dialog.querySelector('.custom-confirm-btn-confirm');
                
                cancelBtn.onclick = () => {
                    overlay.remove();
                    resolve(false);
                };
                
                confirmBtn.onclick = () => {
                    overlay.remove();
                    resolve(true);
                };
                
                overlay.onclick = (e) => {
                    if (e.target === overlay) {
                        overlay.remove();
                        resolve(false);
                    }
                };
            });
        }
        
        // Custom alert function
        function showCustomAlert(icon, title, message) {
            const overlay = document.createElement('div');
            overlay.className = 'custom-confirm-overlay';
            
            const dialog = document.createElement('div');
            dialog.className = 'custom-confirm-dialog';
            dialog.innerHTML = `
                <div class="custom-confirm-icon" style="font-size: 48px;">
                    ${icon}
                </div>
                <div class="custom-confirm-title">${title}</div>
                <div class="custom-confirm-message">${message}</div>
                <div class="custom-confirm-buttons">
                    <button class="custom-confirm-btn custom-confirm-btn-confirm" style="width: 100%;">
                        <i class="fas fa-check"></i> Đóng
                    </button>
                </div>
            `;
            
            overlay.appendChild(dialog);
            document.body.appendChild(overlay);
            
            const closeBtn = dialog.querySelector('.custom-confirm-btn-confirm');
            closeBtn.onclick = () => overlay.remove();
            overlay.onclick = (e) => {
                if (e.target === overlay) overlay.remove();
            };
        }
        
        // Hiển thị popup thông báo coupon
        <?php 
        $couponAlert = Session::get('coupon_alert');
        if ($couponAlert): 
            Session::delete('coupon_alert');
        ?>
        window.addEventListener('DOMContentLoaded', function() {
            const alertType = '<?= $couponAlert['type'] ?>';
            const alertMessage = '<?= addslashes($couponAlert['message']) ?>';
            
            // Tạo custom popup đẹp
            const popup = document.createElement('div');
            popup.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: white;
                padding: 30px 40px;
                border-radius: 12px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.3);
                z-index: 10000;
                min-width: 400px;
                text-align: center;
                animation: popupShow 0.3s ease-out;
            `;
            
            const icon = alertType === 'success' 
                ? '<i class="fas fa-check-circle" style="font-size: 48px; color: #10b981; margin-bottom: 15px;"></i>'
                : '<i class="fas fa-times-circle" style="font-size: 48px; color: #ef4444; margin-bottom: 15px;"></i>';
            
            popup.innerHTML = `
                <style>
                    @keyframes popupShow {
                        from {
                            opacity: 0;
                            transform: translate(-50%, -60%);
                        }
                        to {
                            opacity: 1;
                            transform: translate(-50%, -50%);
                        }
                    }
                </style>
                ${icon}
                <h3 style="margin: 0 0 10px 0; color: #2d3748; font-size: 20px;">
                    ${alertType === 'success' ? 'Thành công!' : 'Thông báo'}
                </h3>
                <p style="margin: 0 0 20px 0; color: #718096; font-size: 15px; line-height: 1.5;">
                    ${alertMessage}
                </p>
                <button onclick="this.parentElement.remove(); document.getElementById('popupOverlay').remove();" 
                        style="background: ${alertType === 'success' ? '#10b981' : '#ef4444'}; 
                               color: white; border: none; padding: 10px 30px; 
                               border-radius: 6px; cursor: pointer; font-size: 15px; font-weight: 600;
                               transition: all 0.3s;">
                    Đóng
                </button>
            `;
            
            // Tạo overlay
            const overlay = document.createElement('div');
            overlay.id = 'popupOverlay';
            overlay.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 9999;
            `;
            overlay.onclick = function() {
                popup.remove();
                overlay.remove();
            };
            
            document.body.appendChild(overlay);
            document.body.appendChild(popup);
            
            // Auto close sau 5 giây
            setTimeout(() => {
                if (popup.parentElement) {
                    popup.remove();
                    overlay.remove();
                }
            }, 5000);
        });
        <?php endif; ?>
        
        // Xử lý áp dụng coupon cho cả 2 loại
        document.querySelectorAll('.apply-coupon-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const inputId = this.getAttribute('data-input');
                const couponType = this.getAttribute('data-type'); // product hoặc shipping
                const couponInput = document.getElementById(inputId);
                const couponCode = couponInput.value.trim().toUpperCase();
                
                if (!couponCode) {
                    showCustomAlert('⚠️', 'Thông báo', 'Vui lòng nhập mã giảm giá');
                    return;
                }
                
                // Lưu vị trí hiện tại
                const currentScrollY = window.scrollY;
                
                // Tạo form tạm để submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?= BASE_URL ?>/orders/apply-coupon';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'coupon_code';
                input.value = couponCode;
                
                // Thêm loại coupon để backend kiểm tra
                const typeInput = document.createElement('input');
                typeInput.type = 'hidden';
                typeInput.name = 'coupon_type';
                typeInput.value = couponType;
                
                // Thêm input để lưu scroll position
                const scrollInput = document.createElement('input');
                scrollInput.type = 'hidden';
                scrollInput.name = 'scroll_position';
                scrollInput.value = currentScrollY;
                
                form.appendChild(input);
                form.appendChild(typeInput);
                form.appendChild(scrollInput);
                document.body.appendChild(form);
                form.submit();
            });
        });
        
        // Auto uppercase cho các input mã giảm giá
        document.querySelectorAll('#productCouponInput, #shippingCouponInput').forEach(input => {
            input.addEventListener('input', function(e) {
                const cursorPosition = this.selectionStart;
                const oldLength = this.value.length;
                this.value = this.value.toUpperCase();
                const newLength = this.value.length;
                // Giữ nguyên vị trí con trỏ
                this.setSelectionRange(cursorPosition, cursorPosition);
            });
        });
        
        // Cuộn về vị trí coupon sau khi áp dụng (nếu có thông báo)
        window.addEventListener('load', function() {
            // Khôi phục vị trí scroll nếu có coupon alert hoặc removed
            <?php if ($couponAlert): ?>
            // Scroll to order summary section smoothly
            setTimeout(() => {
                const orderSummary = document.querySelector('.order-summary');
                if (orderSummary) {
                    orderSummary.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }, 500);
            <?php endif; ?>
            
            // Scroll to order summary nếu vừa xóa coupon
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('removed') === '1') {
                setTimeout(() => {
                    const orderSummary = document.querySelector('.order-summary');
                    if (orderSummary) {
                        orderSummary.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                    // Xóa param khỏi URL
                    window.history.replaceState({}, document.title, '<?= BASE_URL ?>/orders/checkout');
                }, 300);
            }
        });
        
        // Xử lý chọn phương thức thanh toán
        document.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', function() {
                document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
                this.classList.add('selected');
                this.querySelector('input[type="radio"]').checked = true;
            });
        });
        
        // Xử lý chọn hình thức giao hàng
        const shippingMethods = document.querySelectorAll('.shipping-method');
        const totalRows = document.querySelectorAll('.total-row');
        const subtotalElement = totalRows[0].querySelector('span:last-child');
        const shippingFeeElement = totalRows[1].querySelector('span:last-child');
        
        // Find discount row (if exists)
        const discountRow = document.querySelector('.total-row.discount');
        const discountElement = discountRow ? discountRow.querySelector('span:last-child') : null;
        
        const totalElement = document.querySelector('.total-row.highlight span:last-child');
        const shippingAddressSection = document.getElementById('shippingAddressSection');
        const shippingAddressInput = document.getElementById('shippingAddressInput');
        const shippingCouponSection = document.getElementById('shippingCouponSection');
        
        // Get coupon info
        const originalProductDiscount = <?= $productDiscount ?>;
        const originalShippingDiscount = <?= $shippingDiscount ?>;
        const originalDiscount = <?= $couponDiscount ?>;
        const hasShippingCoupon = <?= !empty($shippingCoupon) ? 'true' : 'false' ?>;
        const hasProductCoupon = <?= !empty($productCoupon) ? 'true' : 'false' ?>;
        
        // Hàm xóa shipping coupon
        function removeShippingCouponIfPickup() {
            if (hasShippingCoupon) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?= BASE_URL ?>/orders/remove-coupon';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'type';
                input.value = 'shipping';
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        shippingMethods.forEach(method => {
            method.addEventListener('click', function() {
                // Update selected state
                shippingMethods.forEach(m => m.classList.remove('selected'));
                this.classList.add('selected');
                
                const radio = this.querySelector('input[type="radio"]');
                radio.checked = true;
                
                // Get shipping fee
                const shippingFee = parseInt(radio.getAttribute('data-fee'));
                const shippingMethod = radio.value;
                
                // Hide/show address section based on method
                if (shippingMethod === 'pickup') {
                    const addressSection = document.querySelector('.section-container:first-child');
                    const addressTextarea = addressSection.querySelector('#shippingAddressInput');
                    addressTextarea.removeAttribute('required');
                    addressTextarea.closest('.form-group').style.opacity = '0.5';
                    addressTextarea.disabled = true;
                    
                    // Ẩn phần mã giảm phí ship
                    shippingCouponSection.style.display = 'none';
                    // Xóa shipping coupon nếu đã áp dụng
                    if (hasShippingCoupon) {
                        removeShippingCouponIfPickup();
                        return; // Dừng lại để page reload
                    }
                } else {
                    const addressSection = document.querySelector('.section-container:first-child');
                    const addressTextarea = addressSection.querySelector('#shippingAddressInput');
                    addressTextarea.setAttribute('required', 'required');
                    addressTextarea.closest('.form-group').style.opacity = '1';
                    addressTextarea.disabled = false;
                    
                    // Hiện phần mã giảm phí ship
                    shippingCouponSection.style.display = 'block';
                }
                
                // Update shipping fee display
                shippingFeeElement.textContent = new Intl.NumberFormat('vi-VN').format(shippingFee) + 'đ';
                
                // Calculate discount based on shipping fee
                let productDiscount = originalProductDiscount;
                let shippingDiscount = originalShippingDiscount;
                
                // If pickup (no shipping fee), shipping discount = 0
                if (shippingFee === 0 && hasShippingCoupon) {
                    shippingDiscount = 0;
                }
                
                let totalDiscount = productDiscount + shippingDiscount;
                
                // Update discount display
                const productDiscountRow = document.querySelector('.total-row.discount:nth-of-type(3)');
                const shippingDiscountRow = document.querySelector('.total-row.discount:nth-of-type(4)');
                
                if (productDiscountRow && productDiscount > 0) {
                    productDiscountRow.style.display = 'flex';
                }
                
                if (shippingDiscountRow) {
                    if (shippingDiscount > 0) {
                        shippingDiscountRow.style.display = 'flex';
                        const shippingDiscountElement = shippingDiscountRow.querySelector('span:last-child');
                        if (shippingDiscountElement) {
                            shippingDiscountElement.textContent = '-' + new Intl.NumberFormat('vi-VN').format(shippingDiscount) + 'đ';
                        }
                    } else {
                        shippingDiscountRow.style.display = 'none';
                    }
                }
                
                // Recalculate total
                const subtotal = <?= $subtotal ?>;
                const newTotal = subtotal + shippingFee - totalDiscount;
                totalElement.textContent = new Intl.NumberFormat('vi-VN').format(newTotal) + 'đ';
            });
        });
        
        // Validate form trước khi submit
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            const name = document.querySelector('input[name="customer_name"]').value.trim();
            const phone = document.querySelector('input[name="customer_phone"]').value.trim();
            const shippingMethod = document.querySelector('input[name="shipping_method"]:checked').value;
            
            if (!name || !phone) {
                e.preventDefault();
                alert('Vui lòng điền đầy đủ thông tin bắt buộc!');
                return false;
            }
            
            // Validate address only if not pickup
            if (shippingMethod !== 'pickup') {
                const address = document.querySelector('textarea[name="shipping_address"]').value.trim();
                if (!address) {
                    e.preventDefault();
                    alert('Vui lòng nhập địa chỉ giao hàng!');
                    return false;
                }
            }
        });
        
        // ============ ADDRESS MANAGEMENT ============
        
        // Handle delete coupon button với custom confirm dialog
        document.querySelectorAll('.delete-coupon-btn').forEach(btn => {
            btn.addEventListener('click', async function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const couponType = this.getAttribute('data-coupon-type') || 'mã giảm giá';
                const confirmed = await confirmDeleteCoupon(couponType);
                
                if (confirmed) {
                    // Lấy type từ form
                    const form = this.closest('form');
                    const type = form.querySelector('input[name="type"]').value;
                    
                    // Tạo form AJAX để xóa coupon
                    const formData = new FormData();
                    formData.append('type', type);
                    formData.append('ajax', '1'); // Đánh dấu là AJAX request
                    
                    // Submit AJAX với header
                    fetch('<?= BASE_URL ?>/orders/remove-coupon', {
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
                            // Reload page và scroll to order summary
                            window.location.href = '<?= BASE_URL ?>/orders/checkout?removed=1';
                        } else {
                            showCustomAlert('❌', 'Lỗi', data.message || 'Có lỗi xảy ra');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showCustomAlert('❌', 'Lỗi', 'Không thể xóa mã giảm giá. Vui lòng thử lại.');
                    });
                }
            });
        });
        
        // Fill address from saved addresses dropdown
        function fillAddressFromSaved(addressId) {
            if (!addressId) {
                // Reset form if "Nhập địa chỉ mới" selected
                document.querySelector('input[name="customer_name"]').value = '<?= htmlspecialchars($user['full_name'] ?? '') ?>';
                document.querySelector('input[name="customer_phone"]').value = '<?= htmlspecialchars($user['phone'] ?? '') ?>';
                document.getElementById('shippingAddressInput').value = '';
                return;
            }
            
            const option = document.querySelector(`#savedAddressSelect option[value="${addressId}"]`);
            if (option) {
                document.querySelector('input[name="customer_name"]').value = option.dataset.name || '';
                document.querySelector('input[name="customer_phone"]').value = option.dataset.phone || '';
                document.getElementById('shippingAddressInput').value = option.dataset.full || '';
            }
        }
        
        // Auto-fill default address on page load
        window.addEventListener('DOMContentLoaded', function() {
            const savedAddressSelect = document.getElementById('savedAddressSelect');
            if (savedAddressSelect && savedAddressSelect.value) {
                fillAddressFromSaved(savedAddressSelect.value);
            }
        });
        
        // Open add address modal
        function openAddAddressModal() {
            document.getElementById('addAddressModal').classList.add('show');
        }
        
        // Close modal
        function closeAddAddressModal() {
            document.getElementById('addAddressModal').classList.remove('show');
            document.getElementById('addAddressForm').reset();
            // Reset type option
            document.querySelectorAll('.type-option').forEach(o => o.classList.remove('active'));
            document.querySelectorAll('.type-option')[0].classList.add('active');
        }
        
        // Close modal when clicking outside
        document.getElementById('addAddressModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddAddressModal();
            }
        });
        
        // Province change - populate districts
        document.getElementById('modalProvince')?.addEventListener('change', function() {
            const province = this.value;
            const districtSelect = document.getElementById('modalDistrict');
            const wardSelect = document.getElementById('modalWard');
            
            districtSelect.innerHTML = '<option value="">-- Chọn Quận/Huyện --</option>';
            wardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
            
            const locationData = {
                'Hà Nội': ['Quận Ba Đình', 'Quận Hoàn Kiếm', 'Quận Cầu Giấy', 'Quận Đống Đa', 'Quận Hai Bà Trưng', 'Quận Hoàng Mai', 'Quận Long Biên', 'Quận Tây Hồ', 'Quận Thanh Xuân'],
                'TP. Hồ Chí Minh': ['Quận 1', 'Quận 2', 'Quận 3', 'Quận 4', 'Quận 5', 'Quận 6', 'Quận 7', 'Quận 8', 'Quận 9', 'Quận 10', 'Quận 11', 'Quận 12'],
                'Đà Nẵng': ['Quận Hải Châu', 'Quận Thanh Khê', 'Quận Sơn Trà', 'Quận Ngũ Hành Sơn', 'Quận Liên Chiểu', 'Quận Cẩm Lệ'],
                'Hải Phòng': ['Quận Hồng Bàng', 'Quận Ngô Quyền', 'Quận Lê Chân', 'Quận Hải An', 'Quận Kiến An', 'Quận Đồ Sơn'],
                'Cần Thơ': ['Quận Ninh Kiều', 'Quận Ô Môn', 'Quận Bình Thủy', 'Quận Cái Răng', 'Quận Thốt Nốt']
            };
            
            if (province && locationData[province]) {
                locationData[province].forEach(district => {
                    districtSelect.innerHTML += `<option value="${district}">${district}</option>`;
                });
            }
        });
        
        // District change - populate wards
        document.getElementById('modalDistrict')?.addEventListener('change', function() {
            const wardSelect = document.getElementById('modalWard');
            wardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';
            
            // Simplified ward list
            for (let i = 1; i <= 15; i++) {
                wardSelect.innerHTML += `<option value="Phường ${i}">Phường ${i}</option>`;
            }
        });
        
        // Address type selector
        document.querySelectorAll('.type-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.type-option').forEach(o => o.classList.remove('active'));
                this.classList.add('active');
                this.querySelector('input').checked = true;
            });
        });
        
        // Submit add address form
        document.getElementById('addAddressForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('.btn-submit-modal');
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';
            
            try {
                const response = await fetch('<?= BASE_URL ?>/address/add', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('✅ ' + data.message + '\n\nTrang sẽ tải lại để cập nhật địa chỉ mới.');
                    location.reload();
                } else {
                    alert('❌ ' + data.message);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-save"></i> Lưu địa chỉ';
                }
            } catch (error) {
                alert('❌ Có lỗi xảy ra, vui lòng thử lại!');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save"></i> Lưu địa chỉ';
            }
        });
    </script>
    
    <!-- Modal Add Address -->
    <div class="modal-overlay" id="addAddressModal">
        <div class="modal-content-checkout">
            <div class="modal-header-checkout">
                <h3><i class="fas fa-map-marker-alt"></i> Thêm địa chỉ mới</h3>
                <button class="modal-close" onclick="closeAddAddressModal()">&times;</button>
            </div>
            <form id="addAddressForm">
                <div class="modal-body-checkout">
                    <div class="form-row-modal">
                        <div class="form-group-modal">
                            <label>Tên người nhận <span class="required">*</span></label>
                            <input type="text" name="recipient_name" placeholder="Nhập tên người nhận" required>
                        </div>
                        <div class="form-group-modal">
                            <label>Số điện thoại <span class="required">*</span></label>
                            <input type="tel" name="phone" placeholder="Nhập số điện thoại" pattern="0[0-9]{9}" required>
                        </div>
                    </div>
                    
                    <div class="form-group-modal">
                        <label>Tỉnh/Thành phố <span class="required">*</span></label>
                        <select id="modalProvince" name="province" required>
                            <option value="">-- Chọn Tỉnh/Thành phố --</option>
                            <option value="Hà Nội">Hà Nội</option>
                            <option value="TP. Hồ Chí Minh">TP. Hồ Chí Minh</option>
                            <option value="Đà Nẵng">Đà Nẵng</option>
                            <option value="Hải Phòng">Hải Phòng</option>
                            <option value="Cần Thơ">Cần Thơ</option>
                        </select>
                    </div>
                    
                    <div class="form-row-modal">
                        <div class="form-group-modal">
                            <label>Quận/Huyện <span class="required">*</span></label>
                            <select id="modalDistrict" name="district" required>
                                <option value="">-- Chọn Quận/Huyện --</option>
                            </select>
                        </div>
                        <div class="form-group-modal">
                            <label>Phường/Xã <span class="required">*</span></label>
                            <select id="modalWard" name="ward" required>
                                <option value="">-- Chọn Phường/Xã --</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group-modal">
                        <label>Địa chỉ chi tiết <span class="required">*</span></label>
                        <textarea name="address_detail" rows="3" placeholder="Số nhà, tên đường..." required></textarea>
                    </div>
                    
                    <div class="form-group-modal">
                        <label>Loại địa chỉ</label>
                        <div class="address-type-selector">
                            <label class="type-option active">
                                <input type="radio" name="address_type" value="home" checked>
                                <i class="fas fa-home"></i> Nhà riêng
                            </label>
                            <label class="type-option">
                                <input type="radio" name="address_type" value="office">
                                <i class="fas fa-building"></i> Văn phòng
                            </label>
                        </div>
                    </div>
                    
                    <div class="form-group-modal">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="is_default" style="width: auto;">
                            <span>Đặt làm địa chỉ mặc định</span>
                        </label>
                    </div>
                </div>
                <div class="modal-footer-checkout">
                    <button type="button" class="btn-cancel-modal" onclick="closeAddAddressModal()">Hủy</button>
                    <button type="submit" class="btn-submit-modal">
                        <i class="fas fa-save"></i> Lưu địa chỉ
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>