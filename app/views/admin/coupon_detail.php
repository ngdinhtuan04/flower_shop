<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết Mã giảm giá - Admin</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin-coupon-detail.css">
    <script src="<?= ASSETS_URL ?>/js/confirm-dialog.js"></script>
</head>
<body>
    <?php 
    $user = Session::getUser();
    
    // Safe variable extraction with isset checks - match actual DB columns
    $couponId = isset($coupon['id']) ? $coupon['id'] : 0;
    $code = isset($coupon['code']) ? $coupon['code'] : '';
    $description = isset($coupon['description']) ? $coupon['description'] : '';
    $status = isset($coupon['status']) ? $coupon['status'] : 'inactive';
    $isActive = ($status === 'active') ? 1 : 0;
    $discountType = isset($coupon['discount_type']) ? $coupon['discount_type'] : 'percent';
    $discountValue = isset($coupon['discount_value']) ? $coupon['discount_value'] : 0;
    $minOrderValue = isset($coupon['min_order_value']) ? $coupon['min_order_value'] : 0;
    $maxDiscount = isset($coupon['max_discount']) ? $coupon['max_discount'] : null;
    $usedCount = isset($coupon['used_count']) ? $coupon['used_count'] : 0;
    $usageLimit = isset($coupon['usage_limit']) ? $coupon['usage_limit'] : null;
    $startDate = isset($coupon['valid_from']) ? $coupon['valid_from'] : date('Y-m-d H:i:s');
    $endDate = isset($coupon['valid_to']) ? $coupon['valid_to'] : date('Y-m-d H:i:s');
    $createdAt = isset($coupon['created_at']) ? $coupon['created_at'] : date('Y-m-d H:i:s');
    
    // Time status calculation
    $now = time();
    $startTime = strtotime($startDate);
    $endTime = strtotime($endDate);
    
    if ($now < $startTime) {
        $timeStatus = '<span class="badge badge-upcoming">Sắp diễn ra</span>';
    } elseif ($now > $endTime) {
        $timeStatus = '<span class="badge badge-expired">Đã hết hạn</span>';
    } else {
        $timeStatus = '<span class="badge badge-valid">Đang diễn ra</span>';
    }
    ?>
    <?php include APP_PATH . '/views/layouts/admin_sidebar.php'; ?>
    
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <h2>Chi tiết Mã giảm giá</h2>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <strong><?= htmlspecialchars($user['full_name']) ?></strong>
            </div>
        </div>

        <div class="content-wrapper">


            <div class="detail-container">
                <!-- Header -->
                <div class="detail-header">
                    <div class="detail-title">
                        <div class="coupon-code-badge">
                            <?= htmlspecialchars($code) ?>
                        </div>
                        <span class="badge badge-<?= $isActive ? 'active' : 'inactive' ?>">
                            <?= $isActive ? 'Hoạt động' : 'Đã tắt' ?>
                        </span>
                        <?= $timeStatus ?>
                    </div>
                    <div class="action-buttons">
                        <a href="<?= BASE_URL ?>/coupons" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                        <a href="<?= BASE_URL ?>/coupons/edit/<?= $couponId ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Sửa
                        </a>
                        <a href="<?= BASE_URL ?>/coupons/toggle-active/<?= $couponId ?>" 
                           class="btn btn-<?= $isActive ? 'danger' : 'success' ?>"
                           onclick="return confirmToggleCouponDetail(event, <?= $isActive ? 'true' : 'false' ?>)">
                            <i class="fas fa-<?= $isActive ? 'pause' : 'play' ?>"></i>
                            <?= $isActive ? 'Tắt' : 'Bật' ?>
                        </a>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <i class="fas fa-chart-line" style="font-size: 24px;"></i>
                        <div class="stat-number"><?= $usedCount ?></div>
                        <div class="stat-label">Đã sử dụng</div>
                    </div>
                    <div class="stat-card green">
                        <i class="fas fa-check-circle" style="font-size: 24px;"></i>
                        <div class="stat-number">
                            <?= $usageLimit ? $usageLimit - $usedCount : '∞' ?>
                        </div>
                        <div class="stat-label">Còn lại</div>
                    </div>
                    <div class="stat-card orange">
                        <i class="fas fa-percentage" style="font-size: 24px;"></i>
                        <div class="stat-number">
                            <?php 
                            if ($usageLimit && $usageLimit > 0) {
                                echo round(($usedCount / $usageLimit) * 100);
                            } else {
                                echo '0';
                            }
                            ?>%
                        </div>
                        <div class="stat-label">Tỷ lệ sử dụng</div>
                    </div>
                </div>

                <!-- Info Grid -->
                <div class="info-grid">
                    <!-- Thông tin cơ bản -->
                    <div class="info-section">
                        <h3><i class="fas fa-info-circle"></i> Thông tin cơ bản</h3>
                        
                        <div class="info-item">
                            <div class="info-label">ID:</div>
                            <div class="info-value">#<?= $couponId ?></div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Mã giảm giá:</div>
                            <div class="info-value"><?= htmlspecialchars($code) ?></div>
                        </div>

                        <?php if ($description): ?>
                        <div class="info-item">
                            <div class="info-label">Mô tả:</div>
                            <div class="info-value" style="text-align: left; max-width: 300px;">
                                <?= nl2br(htmlspecialchars($description)) ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="info-item">
                            <div class="info-label">Trạng thái:</div>
                            <div class="info-value">
                                <span class="badge badge-<?= $isActive ? 'active' : 'inactive' ?>">
                                    <?= $isActive ? 'Hoạt động' : 'Đã tắt' ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Thiết lập giảm giá -->
                    <div class="info-section">
                        <h3><i class="fas fa-percent"></i> Thiết lập giảm giá</h3>
                        
                        <div class="info-item">
                            <div class="info-label">Áp dụng cho:</div>
                            <div class="info-value">
                                <span class="badge badge-info">
                                    <?php 
                                    $applyTo = $coupon['apply_to'] ?? 'product';
                                    $applyToLabels = [
                                        'product' => '🛍️ Giảm giá sản phẩm',
                                        'shipping' => '🚚 Giảm phí vận chuyển',
                                        'all' => '🎁 Cả hai (Sản phẩm + Ship)'
                                    ];
                                    echo $applyToLabels[$applyTo] ?? 'Sản phẩm';
                                    ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Loại giảm:</div>
                            <div class="info-value">
                                <span class="badge badge-<?= $discountType ?>">
                                    <?= ($discountType == 'percentage' || $discountType == 'percent') ? 'Phần trăm' : 'Cố định' ?>
                                </span>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Giá trị giảm:</div>
                            <div class="info-value" style="color: #e74c3c; font-size: 18px;">
                                <?php if ($discountType == 'percentage' || $discountType == 'percent'): ?>
                                    <?= $discountValue ?>%
                                <?php else: ?>
                                    <?= number_format($discountValue) ?>đ
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Đơn tối thiểu:</div>
                            <div class="info-value">
                                <?= $minOrderValue > 0 ? number_format($minOrderValue) . 'đ' : 'Không giới hạn' ?>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Giảm tối đa:</div>
                            <div class="info-value">
                                <?= $maxDiscount ? number_format($maxDiscount) . 'đ' : 'Không giới hạn' ?>
                            </div>
                        </div>
                    </div>

                    <!-- Giới hạn sử dụng -->
                    <div class="info-section">
                        <h3><i class="fas fa-users"></i> Giới hạn sử dụng</h3>
                        
                        <div class="info-item">
                            <div class="info-label">Đã sử dụng:</div>
                            <div class="info-value">
                                <strong style="font-size: 20px; color: #667eea;">
                                    <?= $usedCount ?>
                                </strong>
                                <?php if ($usageLimit): ?>
                                    / <?= $usageLimit ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Giới hạn tổng:</div>
                            <div class="info-value">
                                <?= $usageLimit ? number_format($usageLimit) . ' lần' : 'Không giới hạn' ?>
                            </div>
                        </div>

                        <?php if ($usageLimit): ?>
                        <div class="info-item">
                            <div class="info-label">Còn lại:</div>
                            <div class="info-value" style="color: #10b981;">
                                <?= max(0, $usageLimit - $usedCount) ?> lần
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Thời gian -->
                    <div class="info-section">
                        <h3><i class="fas fa-calendar-alt"></i> Thời gian</h3>
                        
                        <div class="info-item">
                            <div class="info-label">Ngày bắt đầu:</div>
                            <div class="info-value">
                                <?= date('d/m/Y H:i', strtotime($startDate)) ?>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Ngày kết thúc:</div>
                            <div class="info-value">
                                <?= date('d/m/Y H:i', strtotime($endDate)) ?>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Trạng thái thời gian:</div>
                            <div class="info-value">
                                <?= $timeStatus ?>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Ngày tạo:</div>
                            <div class="info-value">
                                <?= date('d/m/Y H:i', strtotime($createdAt)) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function confirmToggleCouponDetail(event, isActive) {
            event.preventDefault();
            const link = event.currentTarget;
            confirmAction({
                title: isActive ? 'Tắt mã giảm giá' : 'Bật mã giảm giá',
                message: isActive ? 'Bạn có chắc chắn muốn tắt mã giảm giá này?' : 'Bạn có chắc chắn muốn bật mã giảm giá này?',
                type: isActive ? 'warning' : 'success',
                confirmText: isActive ? 'Tắt' : 'Bật',
                theme: 'admin'
            }).then(confirmed => {
                if (confirmed) window.location.href = link.href;
            });
            return false;
        }
    </script>
</body>
</html>
