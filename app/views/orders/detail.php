<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng #<?= htmlspecialchars($order['order_code']) ?> - Flower Shop</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/home.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/order-detail.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/notifications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php $user = Session::getUser(); ?>
    
    <!-- Header -->
    <?php include APP_PATH . '/views/layouts/header.php'; ?>
    
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="<?= BASE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a>
        <span class="separator">/</span>
        <a href="<?= BASE_URL ?>/orders/history">Đơn hàng của tôi</a>
        <span class="separator">/</span>
        <span class="current"><?= htmlspecialchars($order['order_code']) ?></span>
    </div>
    
    <div class="order-detail-container">
        <!-- Thông tin đơn hàng -->
        <div class="order-info-grid">
            <div>
                <div class="info-card">
                    <h3><i class="fas fa-info-circle"></i> Thông tin đơn hàng</h3>
                    <div class="info-row">
                        <label>Mã đơn hàng:</label>
                        <span><strong><?= htmlspecialchars($order['order_code']) ?></strong></span>
                    </div>
                    <div class="info-row">
                        <label>Ngày đặt:</label>
                        <span><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
                    </div>
                    <div class="info-row">
                        <label>Trạng thái đơn hàng:</label>
                        <span>
                            <?php
                            $statusText = [
                                'pending' => 'Chờ xác nhận',
                                'confirmed' => 'Đã xác nhận',
                                'processing' => 'Đang xử lý',
                                'shipping' => 'Đang giao hàng',
                                'delivered' => 'Đã giao hàng',
                                'cancelled' => 'Đã hủy'
                            ];
                            $statusClass = 'status-' . $order['order_status'];
                            ?>
                            <span class="status-badge <?= $statusClass ?>">
                                <?= $statusText[$order['order_status']] ?? $order['order_status'] ?>
                            </span>
                        </span>
                    </div>
                    <div class="info-row">
                        <label>Trạng thái thanh toán:</label>
                        <span>
                            <?php
                            $paymentText = [
                                'pending' => 'Chưa thanh toán',
                                'paid' => 'Đã thanh toán',
                                'failed' => 'Thanh toán thất bại',
                                'refunded' => 'Đã hoàn tiền'
                            ];
                            $paymentClass = 'payment-' . $order['payment_status'];
                            ?>
                            <span class="status-badge <?= $paymentClass ?>">
                                <?= $paymentText[$order['payment_status']] ?? $order['payment_status'] ?>
                            </span>
                        </span>
                    </div>
                    <div class="info-row">
                        <label>Phương thức thanh toán:</label>
                        <span>
                            <?php if ($order['payment_method'] === 'cod'): ?>
                                <i class="fas fa-money-bill-wave"></i> Thanh toán khi nhận hàng
                            <?php elseif ($order['payment_method'] === 'vnpay'): ?>
                                <i class="fas fa-credit-card"></i> VNPay
                            <?php else: ?>
                                <?= htmlspecialchars($order['payment_method']) ?>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
                
                <div class="info-card" style="margin-top: 20px;">
                    <h3><i class="fas fa-map-marker-alt"></i> Thông tin người nhận</h3>
                    <div class="info-row">
                        <label>Họ và tên:</label>
                        <span><?= htmlspecialchars($order['customer_name']) ?></span>
                    </div>
                    <div class="info-row">
                        <label>Số điện thoại:</label>
                        <span><?= htmlspecialchars($order['customer_phone']) ?></span>
                    </div>
                    <div class="info-row">
                        <label>Email:</label>
                        <span><?= htmlspecialchars($order['customer_email']) ?></span>
                    </div>
                    <div class="info-row">
                        <label>Địa chỉ giao hàng:</label>
                        <span><?= htmlspecialchars($order['shipping_address']) ?></span>
                    </div>
                    <div class="info-row">
                        <label>Hình thức giao hàng:</label>
                        <span>
                            <?php 
                                $shippingLabels = [
                                    'standard' => '🚚 Giao hàng tiêu chuẩn (2-3 ngày)',
                                    'express' => '🚀 Giao hàng nhanh (24 giờ)',
                                    'same_day' => '⚡ Giao hàng trong ngày (2-4 giờ)',
                                    'pickup' => '🏪 Nhận tại cửa hàng'
                                ];
                                echo $shippingLabels[$order['shipping_method']] ?? 'Tiêu chuẩn';
                            ?>
                        </span>
                    </div>
                    <?php if (!empty($order['shipping_note'])): ?>
                    <div class="info-row">
                        <label>Ghi chú:</label>
                        <span><?= htmlspecialchars($order['shipping_note']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Timeline trạng thái -->
            <div class="info-card">
                <h3><i class="fas fa-history"></i> Lịch sử đơn hàng</h3>
                <div class="timeline">
                    <div class="timeline-item <?= in_array($order['order_status'], ['pending', 'confirmed', 'processing', 'shipping', 'delivered']) ? 'active' : '' ?>">
                        <div class="timeline-content">
                            <div class="title">Đơn hàng đã đặt</div>
                            <div class="time"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></div>
                        </div>
                    </div>
                    <div class="timeline-item <?= in_array($order['order_status'], ['confirmed', 'processing', 'shipping', 'delivered']) ? 'active' : '' ?>">
                        <div class="timeline-content">
                            <div class="title">Đã xác nhận</div>
                            <div class="time">Chờ xác nhận</div>
                        </div>
                    </div>
                    <div class="timeline-item <?= in_array($order['order_status'], ['processing', 'shipping', 'delivered']) ? 'active' : '' ?>">
                        <div class="timeline-content">
                            <div class="title">Đang xử lý</div>
                            <div class="time">Đang chuẩn bị hàng</div>
                        </div>
                    </div>
                    <div class="timeline-item <?= in_array($order['order_status'], ['shipping', 'delivered']) ? 'active' : '' ?>">
                        <div class="timeline-content">
                            <div class="title">Đang giao hàng</div>
                            <div class="time">Đang vận chuyển</div>
                        </div>
                    </div>
                    <div class="timeline-item <?= $order['order_status'] === 'delivered' ? 'active' : '' ?>">
                        <div class="timeline-content">
                            <div class="title">Đã giao hàng</div>
                            <?php if (!empty($order['delivered_at'])): ?>
                            <div class="time"><?= date('d/m/Y H:i', strtotime($order['delivered_at'])) ?></div>
                            <?php else: ?>
                            <div class="time">Chờ giao hàng</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($order['order_status'] === 'cancelled'): ?>
                    <div class="timeline-item active">
                        <div class="timeline-content">
                            <div class="title" style="color: #dc3545;">Đã hủy</div>
                            <?php if (!empty($order['cancelled_at'])): ?>
                            <div class="time"><?= date('d/m/Y H:i', strtotime($order['cancelled_at'])) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($order['cancel_reason'])): ?>
                            <div class="time" style="margin-top: 5px;">Lý do: <?= htmlspecialchars($order['cancel_reason']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Danh sách sản phẩm -->
        <div class="order-items">
            <h3><i class="fas fa-shopping-bag"></i> Sản phẩm đã đặt</h3>
            
            <?php foreach ($orderItems as $item): ?>
            <div class="order-item">
                <div class="item-image">
                    <?php 
                    // Hình ảnh được lưu dưới dạng filename trong database
                    $imagePath = UPLOAD_URL . '/products/' . htmlspecialchars($item['product_image']);
                    ?>
                    <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($item['product_name']) ?>">
                </div>
                <div class="item-info">
                    <h4><?= htmlspecialchars($item['product_name']) ?></h4>
                    <p>Đơn giá: <?= number_format($item['price'], 0, ',', '.') ?>đ</p>
                    <p>Số lượng: <?= $item['quantity'] ?></p>
                    
                    <?php if ($order['order_status'] === 'delivered'): ?>
                        <?php if (isset($reviewedProducts[$item['product_id']]) && $reviewedProducts[$item['product_id']]): ?>
                            <p style="color: #48bb78; margin-top: 10px;">
                                <i class="fas fa-check-circle"></i> Đã đánh giá
                            </p>
                        <?php else: ?>
                            <a href="<?= BASE_URL ?>/review/create/<?= $item['product_id'] ?>/<?= $order['id'] ?>" 
                               style="display: inline-block; margin-top: 10px; padding: 8px 16px; background: linear-gradient(135deg, #ff6b9d 0%, #c44569 100%); color: white; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 600;">
                                <i class="fas fa-star"></i> Đánh giá
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="item-price">
                    <div class="price"><?= number_format($item['subtotal'], 0, ',', '.') ?>đ</div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <!-- Tổng kết -->
            <div class="order-summary">
                <div class="summary-row">
                    <span>Tạm tính:</span>
                    <span><?= number_format($order['subtotal'], 0, ',', '.') ?>đ</span>
                </div>
                <div class="summary-row">
                    <span>Phí vận chuyển:</span>
                    <span><?= number_format($order['shipping_fee'], 0, ',', '.') ?>đ</span>
                </div>
                <?php if (isset($order['product_discount']) && $order['product_discount'] > 0): ?>
                <div class="summary-row discount">
                    <span>
                        <i class="fas fa-tag"></i> Giảm giá sản phẩm
                        <?php if (!empty($order['coupon_code'])): ?>
                            <?php 
                            $codes = explode(', ', $order['coupon_code']);
                            if (count($codes) > 0) echo '(' . htmlspecialchars($codes[0]) . ')';
                            ?>
                        <?php endif; ?>
                    </span>
                    <span>-<?= number_format($order['product_discount'], 0, ',', '.') ?>đ</span>
                </div>
                <?php endif; ?>
                <?php if (isset($order['shipping_discount']) && $order['shipping_discount'] > 0): ?>
                <div class="summary-row discount">
                    <span>
                        <i class="fas fa-shipping-fast"></i> Giảm phí ship
                        <?php if (!empty($order['coupon_code'])): ?>
                            <?php 
                            $codes = explode(', ', $order['coupon_code']);
                            if (count($codes) > 1) echo '(' . htmlspecialchars($codes[1]) . ')';
                            elseif (count($codes) == 1) echo '(' . htmlspecialchars($codes[0]) . ')';
                            ?>
                        <?php endif; ?>
                    </span>
                    <span>-<?= number_format($order['shipping_discount'], 0, ',', '.') ?>đ</span>
                </div>
                <?php endif; ?>
                <div class="summary-row total">
                    <span>Tổng cộng:</span>
                    <span><?= number_format($order['total'], 0, ',', '.') ?>đ</span>
                </div>
            </div>
        </div>
        
        <!-- Action buttons -->
        <div class="action-buttons">
            <a href="<?= BASE_URL ?>/orders/history" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
            
            <?php if ($order['order_status'] === 'pending'): ?>
            <button class="btn btn-danger" onclick="cancelOrder(<?= $order['id'] ?>)">
                <i class="fas fa-times"></i> Hủy đơn hàng
            </button>
            <?php endif; ?>
            
            <?php if ($order['order_status'] === 'delivered'): ?>
            <a href="<?= BASE_URL ?>/products" class="btn btn-primary">
                <i class="fas fa-shopping-cart"></i> Mua lại
            </a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include APP_PATH . '/views/layouts/footer.php'; ?>
    
    <script src="<?= ASSETS_URL ?>/js/confirm-dialog.js"></script>
    <script src="<?= ASSETS_URL ?>/js/order-detail.js"></script>
</body>
</html>
