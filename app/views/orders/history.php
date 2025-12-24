<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử đơn hàng - Flower Shop</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/home.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/notifications.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/order-history.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="<?= BASE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a>
        <span class="separator">/</span>
        <span class="current">Đơn hàng</span>
    </div>

    <div class="order-history-container">
        <?php if (!empty($orders)): ?>
            <!-- Stats Cards -->
            <div class="orders-stats">
                <div class="stat-card">
                    <i class="fas fa-shopping-bag"></i>
                    <div class="stat-number"><?= count($orders) ?></div>
                    <div class="stat-label">Tổng đơn hàng</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <div class="stat-number">
                        <?= count(array_filter($orders, fn($o) => in_array($o['order_status'], ['pending', 'confirmed', 'processing']))) ?>
                    </div>
                    <div class="stat-label">Đang xử lý</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-truck"></i>
                    <div class="stat-number">
                        <?= count(array_filter($orders, fn($o) => $o['order_status'] === 'shipping')) ?>
                    </div>
                    <div class="stat-label">Đang giao</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-check-circle"></i>
                    <div class="stat-number">
                        <?= count(array_filter($orders, fn($o) => $o['order_status'] === 'delivered')) ?>
                    </div>
                    <div class="stat-label">Hoàn thành</div>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="orders-table-container">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Ngày đặt</th>
                                <th>Giao hàng</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Thanh toán</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td class="order-code"><?= htmlspecialchars($order['order_code']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                <td style="text-align: center; font-size: 20px;">
                                    <?php 
                                        $shippingIcons = [
                                            'standard' => '🚚',
                                            'express' => '🚀',
                                            'same_day' => '⚡',
                                            'pickup' => '🏪'
                                        ];
                                        echo $shippingIcons[$order['shipping_method']] ?? '🚚';
                                    ?>
                                </td>
                                <td style="font-weight: 700; color: #e53e3e;"><?= number_format($order['total'], 0, ',', '.') ?>₫</td>
                                <td>
                                    <?php
                                    $statusMap = [
                                        'pending' => ['text' => 'Chờ xác nhận', 'class' => 'status-pending'],
                                        'confirmed' => ['text' => 'Đã xác nhận', 'class' => 'status-confirmed'],
                                        'processing' => ['text' => 'Đang xử lý', 'class' => 'status-processing'],
                                        'shipping' => ['text' => 'Đang giao', 'class' => 'status-shipping'],
                                        'delivered' => ['text' => 'Đã giao', 'class' => 'status-delivered'],
                                        'cancelled' => ['text' => 'Đã hủy', 'class' => 'status-cancelled'],
                                    ];
                                    $status = $statusMap[$order['order_status']] ?? ['text' => $order['order_status'], 'class' => 'status-pending'];
                                    ?>
                                    <span class="status-badge <?= $status['class'] ?>"><?= $status['text'] ?></span>
                                </td>
                                <td>
                                    <?php
                                    $payMap = [
                                        'pending' => ['text' => 'Chưa thanh toán', 'class' => 'payment-pending'],
                                        'paid' => ['text' => 'Đã thanh toán', 'class' => 'payment-paid'],
                                        'failed' => ['text' => 'Thất bại', 'class' => 'payment-failed'],
                                        'refunded' => ['text' => 'Hoàn tiền', 'class' => 'payment-refunded'],
                                    ];
                                    $payment = $payMap[$order['payment_status']] ?? ['text' => $order['payment_status'], 'class' => 'payment-pending'];
                                    ?>
                                    <span class="status-badge <?= $payment['class'] ?>"><?= $payment['text'] ?></span>
                                </td>
                                <td>
                                    <a href="<?= BASE_URL ?>/orders/detail/<?= $order['id'] ?>" class="btn-view-detail">
                                        <i class="fas fa-eye"></i> Xem chi tiết
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="orders-table-container">
                <div class="empty-orders">
                    <i class="fas fa-box-open"></i>
                    <h3>Bạn chưa có đơn hàng nào</h3>
                    <p>Hãy khám phá các sản phẩm tuyệt vời của chúng tôi!</p>
                    <a href="<?= BASE_URL ?>/products" class="btn-shop-now">
                        <i class="fas fa-shopping-bag"></i> Mua sắm ngay
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

    <script>
    </script>
</body>
</html>