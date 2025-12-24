<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Mã giảm giá - Admin</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin-coupons.css">
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
            <h2>Quản lý Mã giảm giá</h2>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <strong><?= htmlspecialchars($user['full_name']) ?></strong>
            </div>
        </div>

        <div class="content-wrapper">


            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-ticket-alt" style="font-size: 24px;"></i>
                    <div class="stat-number"><?= $stats['total'] ?></div>
                    <div class="stat-label">Tổng số mã</div>
                </div>
                <div class="stat-card green">
                    <i class="fas fa-check-circle" style="font-size: 24px;"></i>
                    <div class="stat-number"><?= $stats['active'] ?></div>
                    <div class="stat-label">Đang hoạt động</div>
                </div>
                <div class="stat-card orange">
                    <i class="fas fa-clock" style="font-size: 24px;"></i>
                    <div class="stat-number"><?= $stats['expired'] ?></div>
                    <div class="stat-label">Đã hết hạn</div>
                </div>
                <div class="stat-card blue">
                    <i class="fas fa-shopping-cart" style="font-size: 24px;"></i>
                    <div class="stat-number"><?= $stats['used'] ?></div>
                    <div class="stat-label">Lượt sử dụng</div>
                </div>
                <div class="stat-card red">
                    <i class="fas fa-money-bill-wave" style="font-size: 24px;"></i>
                    <div class="stat-number"><?= number_format($stats['total_discount'], 0, ',', '.') ?>đ</div>
                    <div class="stat-label">Tổng giảm giá</div>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <input type="text" name="search" id="searchInput" placeholder="Tìm theo mã hoặc mô tả..." 
                       value="<?= htmlspecialchars($filters['search']) ?>">
                
                <select name="status" id="statusSelect">
                    <option value="">Tất cả trạng thái</option>
                    <option value="1" <?= $filters['is_active'] === '1' ? 'selected' : '' ?>>Đang hoạt động</option>
                    <option value="0" <?= $filters['is_active'] === '0' ? 'selected' : '' ?>>Đã tắt</option>
                </select>
                
                <a href="<?= BASE_URL ?>/coupons/create" class="btn-primary">
                    <i class="fas fa-plus"></i> Tạo mã mới
                </a>
            </div>

            <!-- Coupons Table -->
            <div class="coupon-table" id="tableContainer">
                <?php if (empty($coupons)): ?>
                    <div class="empty-state">
                        <i class="fas fa-ticket-alt"></i>
                        <h3>Chưa có mã giảm giá nào</h3>
                        <p>Tạo mã giảm giá đầu tiên cho khách hàng của bạn</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Mã</th>
                                <th>Áp dụng cho</th>
                                <th>Giá trị</th>
                                <th>Sử dụng</th>
                                <th>Thời gian</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($coupons as $coupon): 
                                $now = date('Y-m-d H:i:s');
                                $endDate = isset($coupon['valid_to']) ? $coupon['valid_to'] : '';
                                $startDate = isset($coupon['valid_from']) ? $coupon['valid_from'] : '';
                                $status = isset($coupon['status']) ? $coupon['status'] : 'inactive';
                                $isActive = ($status === 'active') ? 1 : 0;
                                $isExpired = $endDate && $now > $endDate;
                                $isValid = $isActive && !$isExpired && $startDate && $now >= $startDate;
                                $discountType = isset($coupon['discount_type']) ? $coupon['discount_type'] : 'percent';
                            ?>
                                <tr>
                                    <td>
                                        <span class="coupon-code"><?= htmlspecialchars($coupon['code'] ?? '') ?></span>
                                        <br><small style="color: #666;"><?= htmlspecialchars($coupon['description'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <?php 
                                        $applyTo = $coupon['apply_to'] ?? 'product';
                                        $applyToIcons = [
                                            'product' => '🛍️',
                                            'shipping' => '🚚',
                                            'all' => '🎁'
                                        ];
                                        $applyToLabels = [
                                            'product' => 'Sản phẩm',
                                            'shipping' => 'Phí ship',
                                            'all' => 'Cả hai'
                                        ];
                                        echo $applyToIcons[$applyTo] . ' ' . $applyToLabels[$applyTo];
                                        ?>
                                    </td>
                                    <td>
                                        <strong style="color: #e74c3c;">
                                            <?php if ($discountType === 'percent' || $discountType === 'percentage'): ?>
                                                <?= $coupon['discount_value'] ?? 0 ?>%
                                            <?php else: ?>
                                                <?= number_format($coupon['discount_value'] ?? 0, 0, ',', '.') ?>đ
                                            <?php endif; ?>
                                        </strong>
                                        <?php if ($coupon['min_order_value'] > 0): ?>
                                            <br><small>Đơn từ <?= number_format($coupon['min_order_value'], 0, ',', '.') ?>đ</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $coupon['used_count'] ?? 0 ?> 
                                        <?php if (isset($coupon['usage_limit']) && $coupon['usage_limit']): ?>
                                            / <?= $coupon['usage_limit'] ?>
                                        <?php else: ?>
                                            <small>(∞)</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small>
                                            <?= $startDate ? date('d/m/Y', strtotime($startDate)) : '-' ?><br>
                                            → <?= $endDate ? date('d/m/Y', strtotime($endDate)) : '-' ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($isExpired): ?>
                                            <span class="badge expired">Hết hạn</span>
                                        <?php elseif ($isValid): ?>
                                            <span class="badge active">Hoạt động</span>
                                        <?php else: ?>
                                            <span class="badge inactive">Tắt</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="<?= BASE_URL ?>/coupons/detail/<?= isset($coupon['id']) ? $coupon['id'] : '' ?>" 
                                               class="btn-sm btn-info" title="Chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/coupons/edit/<?= isset($coupon['id']) ? $coupon['id'] : '' ?>" 
                                               class="btn-sm btn-warning" title="Sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/coupons/toggle-active/<?= isset($coupon['id']) ? $coupon['id'] : '' ?>" 
                                               class="btn-sm <?= $isActive ? 'btn-danger' : 'btn-success' ?>" 
                                               title="<?= $isActive ? 'Tắt' : 'Bật' ?>"
                                               onclick="return confirmToggleCoupon(event, <?= $isActive ? 'true' : 'false' ?>)">
                                                <i class="fas fa-power-off"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/coupons/delete/<?= isset($coupon['id']) ? $coupon['id'] : '' ?>" 
                                               class="btn-sm btn-danger" title="Xóa"
                                               onclick="return confirmDeleteCoupon(event)">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        let searchTimeout;
        const searchInput = document.getElementById('searchInput');
        const statusSelect = document.getElementById('statusSelect');
        
        function performSearch() {
            const search = searchInput ? searchInput.value : '';
            const status = statusSelect ? statusSelect.value : '';
            
            const params = new URLSearchParams();
            if (search) params.append('search', search);
            if (status) params.append('status', status);
            
            const url = '<?= BASE_URL ?>/coupons?' + params.toString();
            
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    const newTable = doc.getElementById('tableContainer');
                    const currentTable = document.getElementById('tableContainer');
                    if (newTable && currentTable) {
                        currentTable.innerHTML = newTable.innerHTML;
                    }
                })
                .catch(error => console.error('Error:', error));
        }
        
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(performSearch, 300);
            });
        }
        
        if (statusSelect) {
            statusSelect.addEventListener('change', performSearch);
        }
        
        // Confirm toggle coupon
        function confirmToggleCoupon(event, isActive) {
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
        
        // Confirm delete coupon
        function confirmDeleteCoupon(event) {
            event.preventDefault();
            const link = event.currentTarget;
            confirmDelete({
                title: 'Xóa mã giảm giá',
                message: 'Bạn có chắc chắn muốn xóa mã giảm giá này?<br><br>Hành động này không thể hoàn tác!',
                confirmText: 'Xóa',
                theme: 'admin'
            }).then(confirmed => {
                if (confirmed) window.location.href = link.href;
            });
            return false;
        }
    </script>
    <?php include APP_PATH . '/views/layouts/toast_notification.php'; ?>
</body>
</html>
