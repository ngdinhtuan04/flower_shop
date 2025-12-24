<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết Khuyến mãi - Admin</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin-promotion-detail.css">
    <script src="<?= ASSETS_URL ?>/js/confirm-dialog.js"></script>
</head>
<body>
    <?php 
    $user = Session::getUser();
    ?>
    <?php include APP_PATH . '/views/layouts/admin_sidebar.php'; ?>
    
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <h2>Chi tiết Khuyến mãi</h2>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <strong><?= htmlspecialchars($user['full_name']) ?></strong>
            </div>
        </div>

        <div class="content-wrapper">
            <div class="detail-container">
                <div class="detail-header">
                    <div class="detail-title">
                        <h2><?= htmlspecialchars($promotion['name']) ?></h2>
                        <?php if ($promotion['description']): ?>
                            <p><?= htmlspecialchars($promotion['description']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="action-buttons">
                        <a href="<?= BASE_URL ?>/promotions/edit/<?= $promotion['id'] ?>" class="btn btn-edit">
                            <i class="fas fa-edit"></i> Sửa
                        </a>
                        <a href="<?= BASE_URL ?>/promotions/delete/<?= $promotion['id'] ?>" 
                           class="btn btn-delete"
                           onclick="return confirmDeletePromotion(event)">
                            <i class="fas fa-trash"></i> Xóa
                        </a>
                        <a href="<?= BASE_URL ?>/promotions" class="btn btn-back">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>

                <div class="info-grid">
                    <!-- Thông tin giảm giá -->
                    <div class="info-section">
                        <h3><i class="fas fa-percent"></i> Thông tin giảm giá</h3>
                        
                        <div class="info-item">
                            <div class="info-label">Giá trị giảm:</div>
                            <div class="info-value">
                                <div class="discount-highlight">
                                    <?php if ($promotion['discount_type'] == 'percentage'): ?>
                                        <?= $promotion['discount_value'] ?>%
                                    <?php else: ?>
                                        <?= number_format($promotion['discount_value']) ?>đ
                                    <?php endif; ?>
                                </div>
                                <span class="badge badge-<?= $promotion['discount_type'] ?>">
                                    <?= $promotion['discount_type'] == 'percentage' ? 'Giảm theo %' : 'Giảm cố định' ?>
                                </span>
                            </div>
                        </div>

                        <?php if ($promotion['max_discount_amount']): ?>
                            <div class="info-item">
                                <div class="info-label">Giảm tối đa:</div>
                                <div class="info-value">
                                    <?= number_format($promotion['max_discount_amount']) ?>đ
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($promotion['min_order_amount'] > 0): ?>
                            <div class="info-item">
                                <div class="info-label">Đơn tối thiểu:</div>
                                <div class="info-value">
                                    <?= number_format($promotion['min_order_amount']) ?>đ
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="info-item">
                            <div class="info-label">Độ ưu tiên:</div>
                            <div class="info-value">
                                <strong><?= $promotion['priority'] ?></strong>
                            </div>
                        </div>
                    </div>

                    <!-- Phạm vi áp dụng -->
                    <div class="info-section">
                        <h3><i class="fas fa-bullseye"></i> Phạm vi áp dụng</h3>
                        
                        <div class="info-item">
                            <div class="info-label">Áp dụng cho:</div>
                            <div class="info-value">
                                <?php if ($promotion['apply_to'] == 'all'): ?>
                                    <span class="badge badge-all">Toàn bộ sản phẩm</span>
                                <?php elseif ($promotion['apply_to'] == 'category'): ?>
                                    <span class="badge badge-category">Danh mục sản phẩm</span>
                                    <div style="margin-top: 8px; font-weight: 600;">
                                        <?= htmlspecialchars($promotion['category_name'] ?? 'N/A') ?>
                                    </div>
                                <?php else: ?>
                                    <span class="badge badge-product">Sản phẩm cụ thể</span>
                                    <div style="margin-top: 8px; font-weight: 600;">
                                        <?= count($promotion_products) ?> sản phẩm
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Trạng thái:</div>
                            <div class="info-value">
                                <span class="badge badge-<?= $promotion['is_active'] ? 'active' : 'inactive' ?>">
                                    <?= $promotion['is_active'] ? 'Đang hoạt động' : 'Đã tắt' ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Thời gian -->
                    <div class="info-section">
                        <h3><i class="fas fa-clock"></i> Thời gian</h3>
                        
                        <div class="timeline">
                            <div class="timeline-item">
                                <div class="timeline-date">
                                    <?= date('d/m/Y H:i', strtotime($promotion['start_date'])) ?>
                                </div>
                                <div class="timeline-label">Ngày bắt đầu</div>
                            </div>
                            <div class="timeline-item">
                                <div class="timeline-date">
                                    <?= date('d/m/Y H:i', strtotime($promotion['end_date'])) ?>
                                </div>
                                <div class="timeline-label">Ngày kết thúc</div>
                            </div>
                        </div>

                        <?php
                            $now = time();
                            $startTime = strtotime($promotion['start_date']);
                            $endTime = strtotime($promotion['end_date']);
                            
                            if ($now < $startTime) {
                                $daysUntil = ceil(($startTime - $now) / 86400);
                                echo '<div style="margin-top: 15px; padding: 10px; background: #fff3cd; border-radius: 6px; color: #856404;">';
                                echo '<i class="fas fa-hourglass-start"></i> Còn ' . $daysUntil . ' ngày nữa bắt đầu';
                                echo '</div>';
                            } elseif ($now > $endTime) {
                                echo '<div style="margin-top: 15px; padding: 10px; background: #f8d7da; border-radius: 6px; color: #721c24;">';
                                echo '<i class="fas fa-times-circle"></i> Đã hết hạn';
                                echo '</div>';
                            } else {
                                $daysLeft = ceil(($endTime - $now) / 86400);
                                echo '<div style="margin-top: 15px; padding: 10px; background: #d4edda; border-radius: 6px; color: #155724;">';
                                echo '<i class="fas fa-check-circle"></i> Đang diễn ra - Còn ' . $daysLeft . ' ngày';
                                echo '</div>';
                            }
                        ?>
                    </div>

                    <!-- Thống kê -->
                    <div class="info-section">
                        <h3><i class="fas fa-chart-bar"></i> Thống kê</h3>

                        <div class="info-item">
                            <div class="info-label">Ngày tạo:</div>
                            <div class="info-value">
                                <?= date('d/m/Y H:i', strtotime($promotion['created_at'])) ?>
                            </div>
                        </div>

                        <div class="info-item">
                            <div class="info-label">Cập nhật lần cuối:</div>
                            <div class="info-value">
                                <?= date('d/m/Y H:i', strtotime($promotion['updated_at'])) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Danh sách sản phẩm (nếu apply_to = product) -->
                <?php if ($promotion['apply_to'] == 'product' && !empty($promotion_products)): ?>
                    <div class="product-list">
                        <h3><i class="fas fa-box-open"></i> Sản phẩm áp dụng (<?= count($promotion_products) ?>)</h3>
                        <div class="product-grid">
                            <?php foreach ($promotion_products as $prod): ?>
                                <div class="product-card">
                                    <?php 
                                        // Xử lý đường dẫn hình ảnh
                                        if (!empty($prod['image'])) {
                                            // Nếu image chứa đường dẫn, lấy basename
                                            $imageName = basename($prod['image']);
                                            $imagePath = UPLOAD_URL . '/products/' . $imageName;
                                        } else {
                                            $imagePath = ASSETS_URL . '/images/no-image.jpg';
                                        }
                                    ?>
                                    <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($prod['product_name']) ?>">
                                    <div class="product-card-name"><?= htmlspecialchars($prod['product_name']) ?></div>
                                    <div class="product-card-price"><?= number_format($prod['price']) ?>đ</div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        function confirmDeletePromotion(event) {
            event.preventDefault();
            const link = event.currentTarget;
            confirmDelete({
                title: 'Xóa khuyến mãi',
                message: 'Bạn có chắc chắn muốn xóa khuyến mãi này?<br><br>Hành động này không thể hoàn tác!',
                confirmText: 'Xóa',
                theme: 'admin'
            }).then(confirmed => {
                if (confirmed) window.location.href = link.href;
            });
            return false;
        }
    </script>
</body>
</html>
