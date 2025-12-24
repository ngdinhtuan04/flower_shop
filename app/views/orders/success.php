<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt hàng thành công - Flower Shop</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/home.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/notifications.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/order-success.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php $user = Session::getUser(); ?>
    
    <!-- Header -->
    <?php include APP_PATH . '/views/layouts/header.php'; ?>
    
    <div class="success-container">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        
        <div class="success-content">
            <h1><i class="fas fa-party-horn"></i> Đặt hàng thành công!</h1>
            <p>Cảm ơn bạn đã tin tưởng và đặt hàng tại Flower Shop</p>
            
            <div class="order-code">
                <i class="fas fa-receipt"></i> <?= htmlspecialchars($order['order_code']) ?>
            </div>
            
            <div class="order-info">
                <div class="info-row">
                    <label><i class="fas fa-user"></i> Người nhận:</label>
                    <span><?= htmlspecialchars($order['customer_name']) ?></span>
                </div>
                <div class="info-row">
                    <label><i class="fas fa-phone"></i> Số điện thoại:</label>
                    <span><?= htmlspecialchars($order['customer_phone']) ?></span>
                </div>
                <div class="info-row">
                    <label><i class="fas fa-map-marker-alt"></i> Địa chỉ:</label>
                    <span><?= htmlspecialchars($order['shipping_address']) ?></span>
                </div>
                <div class="info-row">
                    <label><i class="fas fa-shipping-fast"></i> Hình thức giao hàng:</label>
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
                <div class="info-row">
                    <label><i class="fas fa-credit-card"></i> Thanh toán:</label>
                    <span>
                        <?php if ($order['payment_method'] === 'cod'): ?>
                            Thanh toán khi nhận hàng
                        <?php elseif ($order['payment_method'] === 'vnpay'): ?>
                            VNPay
                        <?php endif; ?>
                    </span>
                </div>
                <div class="info-row">
                    <label><i class="fas fa-info-circle"></i> Trạng thái thanh toán:</label>
                    <span>
                        <?php if ($order['payment_status'] === 'paid'): ?>
                            <span class="status-badge status-paid">Đã thanh toán</span>
                        <?php else: ?>
                            <span class="status-badge status-pending">Chưa thanh toán</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="info-row">
                    <label><i class="fas fa-box"></i> Trạng thái đơn hàng:</label>
                    <span><span class="status-badge status-pending">Chờ xác nhận</span></span>
                </div>
                <div class="info-row">
                    <label><i class="fas fa-money-bill-wave"></i> Tổng tiền:</label>
                    <span style="font-size: 20px; font-weight: bold; color: #e91e63;">
                        <?= number_format($order['total'], 0, ',', '.') ?>đ
                    </span>
                </div>
            </div>
            
            <p style="margin-top: 20px;">
                <i class="fas fa-envelope"></i> 
                Chúng tôi đã gửi thông tin đơn hàng đến email của bạn. 
                Vui lòng kiểm tra email để biết thêm chi tiết.
            </p>
            
            <div class="action-buttons">
                <a href="<?= BASE_URL ?>/orders/detail/<?= $order['id'] ?>" class="btn btn-primary">
                    <i class="fas fa-eye"></i> Xem chi tiết đơn hàng
                </a>
                <a href="<?= BASE_URL ?>/products" class="btn btn-outline">
                    <i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm
                </a>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include APP_PATH . '/views/layouts/footer.php'; ?>
    <?php include APP_PATH . '/views/layouts/toast_notification.php'; ?>
</body>
</html>
