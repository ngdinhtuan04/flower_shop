<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Khuyến mãi - Admin</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin-promotions.css">
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
            <h2>Quản lý Khuyến mãi</h2>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <strong><?= htmlspecialchars($user['full_name']) ?></strong>
            </div>
        </div>

        <div class="content-wrapper">


            <!-- Thống kê -->
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-icon total">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $statistics['total'] ?></h3>
                        <p>Tổng khuyến mãi</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon active">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $statistics['active'] ?></h3>
                        <p>Đang hoạt động</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon inactive">
                        <i class="fas fa-pause-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $statistics['inactive'] ?></h3>
                        <p>Đã tắt</p>
                    </div>
                </div>
            </div>

            <!-- Tìm kiếm và filter -->
            <div class="filter-bar">
                <input type="text" id="searchInput" placeholder="Tìm kiếm khuyến mãi..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                
                <select id="statusSelect">
                    <option value="">Tất cả trạng thái</option>
                    <option value="1" <?= isset($filters['is_active']) && $filters['is_active'] == '1' ? 'selected' : '' ?>>Đang hoạt động</option>
                    <option value="0" <?= isset($filters['is_active']) && $filters['is_active'] == '0' ? 'selected' : '' ?>>Đã tắt</option>
                </select>
                
                <select id="applyToSelect">
                    <option value="">Tất cả loại áp dụng</option>
                    <option value="all" <?= isset($filters['apply_to']) && $filters['apply_to'] == 'all' ? 'selected' : '' ?>>Toàn bộ sản phẩm</option>
                    <option value="category" <?= isset($filters['apply_to']) && $filters['apply_to'] == 'category' ? 'selected' : '' ?>>Theo danh mục</option>
                    <option value="product" <?= isset($filters['apply_to']) && $filters['apply_to'] == 'product' ? 'selected' : '' ?>>Sản phẩm cụ thể</option>
                </select>
                
                <a href="<?= BASE_URL ?>/promotions/create" class="btn-add">
                    <i class="fas fa-plus"></i> Thêm mới
                </a>
            </div>

            <!-- Bảng danh sách -->
            <div class="promotion-table" id="tableContainer">
                <table id="promotionTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th style="min-width: 250px;">Tên khuyến mãi</th>
                            <th>Giảm giá</th>
                            <th>Áp dụng</th>
                            <th>Thời gian</th>
                            <th>Ưu tiên</th>
                            <th>Trạng thái</th>
                            <th style="min-width: 180px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($promotions)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px;">
                                    <i class="fas fa-inbox" style="font-size: 48px; color: #ddd;"></i>
                                    <p style="color: #888; margin-top: 10px;">Chưa có khuyến mãi nào</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($promotions as $promo): ?>
                                <?php
                                    $now = time();
                                    $startTime = strtotime($promo['start_date']);
                                    $endTime = strtotime($promo['end_date']);
                                    
                                    if ($now < $startTime) {
                                        $timeStatus = '<span class="badge badge-upcoming">Sắp diễn ra</span>';
                                    } elseif ($now > $endTime) {
                                        $timeStatus = '<span class="badge badge-expired">Đã hết hạn</span>';
                                    } else {
                                        $timeStatus = '<span class="badge badge-valid">Đang diễn ra</span>';
                                    }
                                ?>
                                <tr>
                                    <td><?= $promo['id'] ?></td>
                                    <td>
                                        <div class="promotion-name"><?= htmlspecialchars($promo['name']) ?></div>
                                        <?php if ($promo['description']): ?>
                                            <p class="promotion-desc"><?= htmlspecialchars(substr($promo['description'], 0, 80)) ?><?= strlen($promo['description']) > 80 ? '...' : '' ?></p>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="discount-value">
                                            <?php if ($promo['discount_type'] == 'percentage'): ?>
                                                <?= $promo['discount_value'] ?>%
                                            <?php else: ?>
                                                <?= number_format($promo['discount_value']) ?>đ
                                            <?php endif; ?>
                                        </div>
                                        <span class="badge badge-<?= $promo['discount_type'] ?>">
                                            <?= $promo['discount_type'] == 'percentage' ? 'Phần trăm' : 'Cố định' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($promo['apply_to'] == 'all'): ?>
                                            <span class="badge badge-all">Toàn bộ</span>
                                        <?php elseif ($promo['apply_to'] == 'category'): ?>
                                            <span class="badge badge-category">Danh mục</span>
                                            <div style="font-size: 12px; color: #888; margin-top: 5px;">
                                                <?= htmlspecialchars($promo['category_name'] ?? 'N/A') ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="badge badge-product">Sản phẩm</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="date-range">
                                            <div><i class="fas fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($promo['start_date'])) ?></div>
                                            <div><i class="fas fa-calendar-check"></i> <?= date('d/m/Y', strtotime($promo['end_date'])) ?></div>
                                        </div>
                                        <div style="margin-top: 5px;">
                                            <?= $timeStatus ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($promo['priority'] > 0): ?>
                                            <span class="priority-badge"><?= $promo['priority'] ?></span>
                                        <?php else: ?>
                                            <span style="color: #aaa;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $promo['is_active'] ? 'active' : 'inactive' ?>">
                                            <?= $promo['is_active'] ? 'Hoạt động' : 'Đã tắt' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-btns">
                                            <a href="<?= BASE_URL ?>/promotions/detail/<?= $promo['id'] ?>" class="btn-action btn-view" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/promotions/edit/<?= $promo['id'] ?>" class="btn-action btn-edit" title="Sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/promotions/toggle-active/<?= $promo['id'] ?>" 
                                               class="btn-action btn-toggle" 
                                               title="<?= $promo['is_active'] ? 'Tắt' : 'Bật' ?>"
                                               onclick="return confirmTogglePromo(event, <?= $promo['is_active'] ? 'true' : 'false' ?>)">
                                                <i class="fas fa-<?= $promo['is_active'] ? 'pause' : 'play' ?>"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/promotions/delete/<?= $promo['id'] ?>" 
                                               class="btn-action btn-delete" 
                                               title="Xóa"
                                               onclick="return confirmDeletePromo(event)">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        let searchTimeout;
        const searchInput = document.getElementById('searchInput');
        const statusSelect = document.getElementById('statusSelect');
        const applyToSelect = document.getElementById('applyToSelect');
        
        function performSearch() {
            const search = searchInput ? searchInput.value : '';
            const status = statusSelect ? statusSelect.value : '';
            const applyTo = applyToSelect ? applyToSelect.value : '';
            
            const params = new URLSearchParams();
            if (search) params.append('search', search);
            if (status) params.append('status', status);
            if (applyTo) params.append('apply_to', applyTo);
            
            const url = '<?= BASE_URL ?>/promotions?' + params.toString();
            
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    const newTable = doc.querySelector('#tableContainer table tbody');
                    const currentTable = document.querySelector('#tableContainer table tbody');
                    
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
        
        if (statusSelect) statusSelect.addEventListener('change', performSearch);
        if (applyToSelect) applyToSelect.addEventListener('change', performSearch);
        
        // Confirm toggle promotion
        function confirmTogglePromo(event, isActive) {
            event.preventDefault();
            const link = event.currentTarget;
            confirmAction({
                title: isActive ? 'Tắt khuyến mãi' : 'Bật khuyến mãi',
                message: isActive ? 'Bạn có chắc chắn muốn tắt khuyến mãi này?' : 'Bạn có chắc chắn muốn bật khuyến mãi này?',
                type: isActive ? 'warning' : 'success',
                confirmText: isActive ? 'Tắt' : 'Bật',
                theme: 'admin'
            }).then(confirmed => {
                if (confirmed) window.location.href = link.href;
            });
            return false;
        }
        
        // Confirm delete promotion
        function confirmDeletePromo(event) {
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
    <?php include APP_PATH . '/views/layouts/toast_notification.php'; ?>
</body>
</html>
