<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tra cứu đơn hàng - Flower Shop</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/home.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/order/tracking.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/notifications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="<?= BASE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a>
        <span class="separator">/</span>
        <span class="current">Tra cứu đơn hàng</span>
    </div>

    <div class="tracking-container">
        <!-- Form tra cứu -->
        <div class="tracking-form">
            <h3><i class="fas fa-search"></i> Tra Cứu Đơn Hàng</h3>
            <p>Vui lòng nhập thông tin để kiểm tra trạng thái đơn hàng của bạn</p>

            <form method="POST" action="<?= BASE_URL ?>/tracking/search">
                <div class="form-group">
                    <label for="order_code">
                        <i class="fas fa-barcode"></i> Mã đơn hàng
                    </label>
                    <input type="text" 
                           id="order_code" 
                           name="order_code" 
                           placeholder="Ví dụ: ORD20251113..." 
                           value="<?= isset($_POST['order_code']) ? htmlspecialchars($_POST['order_code']) : '' ?>"
                           required>
                </div>

                <div class="form-group">
                    <label for="phone">
                        <i class="fas fa-phone"></i> Số điện thoại đặt hàng
                    </label>
                    <input type="tel" 
                           id="phone" 
                           name="phone" 
                           placeholder="Nhập số điện thoại" 
                           value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>"
                           required>
                </div>

                <button type="submit" class="btn-search">
                    <i class="fas fa-search"></i> Tra cứu đơn hàng
                </button>
            </form>

            <div class="help-box">
                <h5><i class="fas fa-info-circle"></i> Hướng dẫn</h5>
                <p>• Mã đơn hàng được gửi qua email sau khi đặt hàng thành công</p>
                <p>• Nhập đúng số điện thoại bạn đã dùng khi đặt hàng</p>
                <p>• Liên hệ hotline <strong>1900 1234</strong> nếu cần hỗ trợ</p>
            </div>
        </div>

        <!-- Kết quả tra cứu -->
        <?php if ($order): ?>
            <div class="order-result">
                <div class="order-header">
                    <h2><i class="fas fa-check-circle" style="color: #48bb78;"></i> Tìm thấy đơn hàng!</h2>
                    <p class="order-code-display"><?= htmlspecialchars($order['order_code']) ?></p>
                </div>

                <!-- Thông tin đơn hàng -->
                <div class="info-grid">
                    <div class="info-card">
                        <h4><i class="fas fa-info-circle"></i> Thông tin đơn hàng</h4>
                        <div class="info-row">
                            <label>Ngày đặt:</label>
                            <span><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
                        </div>
                        <div class="info-row">
                            <label>Trạng thái:</label>
                            <span>
                                <?php
                                $statusText = [
                                    'pending' => 'Chờ xác nhận',
                                    'confirmed' => 'Đã xác nhận',
                                    'processing' => 'Đang xử lý',
                                    'shipping' => 'Đang giao',
                                    'delivered' => 'Đã giao',
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
                            <label>Thanh toán:</label>
                            <span>
                                <?php
                                $paymentText = [
                                    'pending' => 'Chưa thanh toán',
                                    'paid' => 'Đã thanh toán',
                                    'failed' => 'Thất bại',
                                    'refunded' => 'Hoàn tiền'
                                ];
                                $paymentClass = 'payment-' . $order['payment_status'];
                                ?>
                                <span class="status-badge <?= $paymentClass ?>">
                                    <?= $paymentText[$order['payment_status']] ?? $order['payment_status'] ?>
                                </span>
                            </span>
                        </div>
                    </div>

                    <div class="info-card">
                        <h4><i class="fas fa-user"></i> Người nhận</h4>
                        <div class="info-row">
                            <label>Họ tên:</label>
                            <span><?= htmlspecialchars($order['customer_name']) ?></span>
                        </div>
                        <div class="info-row">
                            <label>SĐT:</label>
                            <span><?= htmlspecialchars($order['customer_phone']) ?></span>
                        </div>
                        <div class="info-row">
                            <label>Địa chỉ:</label>
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
                    </div>
                </div>

                <!-- Timeline -->
                <div class="info-card">
                    <h4><i class="fas fa-history"></i> Lịch sử đơn hàng</h4>
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
                                <div class="time">Chờ giao hàng</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sản phẩm -->
                <div class="order-items">
                    <h4><i class="fas fa-shopping-bag"></i> Sản phẩm đã đặt</h4>
                    
                    <?php foreach ($orderItems as $item): ?>
                        <div class="order-item">
                            <div class="item-image">
                                <?php 
                                $imagePath = isset($item['image']) ? $item['image'] : '';
                                
                                // Xử lý đường dẫn hình ảnh
                                if (empty($imagePath)) {
                                    $imagePath = ASSETS_URL . '/images/no-image.png';
                                } elseif (strpos($imagePath, 'http') === 0) {
                                    // Đã là URL đầy đủ, giữ nguyên
                                } elseif (strpos($imagePath, 'uploads/') === 0) {
                                    // Đường dẫn bắt đầu bằng uploads/ - dùng BASE_URL
                                    $imagePath = BASE_URL . '/' . $imagePath;
                                } elseif (strpos($imagePath, '/') === 0) {
                                    // Đường dẫn tuyệt đối
                                    $imagePath = BASE_URL . $imagePath;
                                } else {
                                    // Đường dẫn tương đối - tên file
                                    $imagePath = BASE_URL . '/uploads/products/' . $imagePath;
                                }
                                ?>
                                <img src="<?= htmlspecialchars($imagePath) ?>" alt="<?= htmlspecialchars($item['product_name'] ?? 'Product') ?>" onerror="this.src='<?= ASSETS_URL ?>/images/no-image.png'">
                            </div>
                            <div class="item-info">
                                <h5><?= htmlspecialchars($item['product_name'] ?? 'Sản phẩm') ?></h5>
                                <p>Đơn giá: <?= number_format($item['price'] ?? 0) ?>đ</p>
                                <p>Số lượng: x<?= $item['quantity'] ?? 0 ?></p>
                            </div>
                            <div class="item-price">
                                <?= number_format($item['subtotal'] ?? 0) ?>đ
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Tổng kết -->
                    <div class="order-summary">
                        <div class="summary-row">
                            <span>Tạm tính:</span>
                            <span><?= number_format($order['subtotal']) ?>đ</span>
                        </div>
                        <div class="summary-row">
                            <span>Phí vận chuyển:</span>
                            <span><?= number_format($order['shipping_fee']) ?>đ</span>
                        </div>
                        <?php if (isset($order['product_discount']) && $order['product_discount'] > 0): ?>
                            <div class="summary-row discount">
                                <span><i class="fas fa-tag"></i> Giảm giá sản phẩm</span>
                                <span>-<?= number_format($order['product_discount']) ?>đ</span>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($order['shipping_discount']) && $order['shipping_discount'] > 0): ?>
                            <div class="summary-row discount">
                                <span><i class="fas fa-shipping-fast"></i> Giảm phí ship</span>
                                <span>-<?= number_format($order['shipping_discount']) ?>đ</span>
                            </div>
                        <?php endif; ?>
                        <div class="summary-row total">
                            <span>Tổng cộng:</span>
                            <span><?= number_format($order['total']) ?>đ</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php elseif (isset($_POST['order_code'])): ?>
            <!-- Không tìm thấy đơn hàng -->
            <div class="empty-result">
                <i class="fas fa-search"></i>
                <h3>Không tìm thấy đơn hàng</h3>
                <p>Vui lòng kiểm tra lại mã đơn hàng và số điện thoại.<br>Hoặc liên hệ hotline <strong>1900 1234</strong> để được hỗ trợ.</p>
            </div>
        <?php else: ?>
            <!-- Chưa tra cứu -->
            <div class="empty-result">
                <i class="fas fa-box-open"></i>
                <h3>Nhập thông tin để tra cứu</h3>
                <p>Vui lòng điền mã đơn hàng và số điện thoại<br>vào form bên trái để kiểm tra trạng thái đơn hàng.</p>
            </div>
        <?php endif; ?>
    </div>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

    <?php include APP_PATH . '/views/layouts/toast_notification.php'; ?>

</body>
</html>
