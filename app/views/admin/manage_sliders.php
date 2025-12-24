<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Sliders - Admin</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin-sliders.css">
    <script src="<?= ASSETS_URL ?>/js/confirm-dialog.js"></script>
</head>
<body>
    <?php include APP_PATH . '/views/layouts/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <h2><i class="fas fa-images"></i> Quản lý Slider</h2>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <strong><?= htmlspecialchars($user['full_name'] ?? 'Admin') ?></strong>
            </div>
        </div>
            
            <?php if (Session::hasFlash('success')): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= Session::getFlash('success') ?>
                </div>
            <?php endif; ?>
            
            <?php if (Session::hasFlash('error')): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= Session::getFlash('error') ?>
                </div>
            <?php endif; ?>
            
            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-images" style="font-size: 24px; opacity: 0.8;"></i>
                    <div class="stat-number"><?= $stats['total'] ?? 0 ?></div>
                    <div class="stat-label">Tổng Slider</div>
                </div>
                <div class="stat-card green">
                    <i class="fas fa-eye" style="font-size: 24px; opacity: 0.8;"></i>
                    <div class="stat-number"><?= $stats['active'] ?? 0 ?></div>
                    <div class="stat-label">Đang Hiển Thị</div>
                </div>
                <div class="stat-card orange">
                    <i class="fas fa-eye-slash" style="font-size: 24px; opacity: 0.8;"></i>
                    <div class="stat-number"><?= $stats['inactive'] ?? 0 ?></div>
                    <div class="stat-label">Đang Ẩn</div>
                </div>
            </div>
            
            <!-- Filter Bar -->
            <div class="filter-bar">
                <a href="<?= BASE_URL ?>/sliders/create" class="btn-primary">
                    <i class="fas fa-plus"></i> Thêm Slider
                </a>
                
                <form method="GET" style="display: flex; gap: 15px; flex: 1; align-items: center;">
                <input type="text" 
                       name="search" 
                       placeholder="🔍 Tìm kiếm slider..." 
                       value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                
                <select name="status">
                    <option value="">Tất cả trạng thái</option>
                    <option value="1" <?= ($filters['is_active'] ?? '') === '1' ? 'selected' : '' ?>>
                        Đang hiển thị
                    </option>
                    <option value="0" <?= ($filters['is_active'] ?? '') === '0' ? 'selected' : '' ?>>
                        Đang ẩn
                    </option>
                </select>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-filter"></i> Lọc
                    </button>
                    
                    <?php if (!empty($filters['search']) || $filters['is_active'] !== ''): ?>
                        <a href="<?= BASE_URL ?>/sliders" class="btn-primary" style="background: #6c757d;">
                            <i class="fas fa-times"></i> Xóa lọc
                        </a>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Slider Table -->
            <?php if (!empty($sliders)): ?>
                <div class="slider-table">
                    <table>
                        <thead>
                            <tr>
                                <th width="50"><i class="fas fa-grip-vertical"></i></th>
                                <th width="60">STT</th>
                                <th width="180">Hình ảnh</th>
                                <th>Tiêu đề</th>
                                <th>Mô tả</th>
                                <th width="120">Trạng thái</th>
                                <th width="200">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="sortable-sliders">
                            <?php foreach ($sliders as $index => $slider): ?>
                                <tr class="slider-row" data-id="<?= $slider['id'] ?>">
                                    <td class="drag-handle">
                                        <i class="fas fa-grip-vertical"></i>
                                    </td>
                                    <td>
                                        <strong><?= $slider['display_order'] ?></strong>
                                    </td>
                                    <td>
                                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($slider['image']) ?>" 
                                             alt="<?= htmlspecialchars($slider['title']) ?>"
                                             class="slider-image"
                                             onerror="this.src='<?= ASSETS_URL ?>/images/placeholder.jpg'">
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($slider['title']) ?></strong>
                                        <?php if (!empty($slider['button_text'])): ?>
                                            <br><small style="color: #666;">
                                                <i class="fas fa-mouse-pointer"></i> 
                                                <?= htmlspecialchars($slider['button_text']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            <?= htmlspecialchars($slider['description'] ?? '') ?>
                                        </div>
                                        <?php if (!empty($slider['link'])): ?>
                                            <small style="color: #999;">
                                                <i class="fas fa-link"></i> 
                                                <?= htmlspecialchars($slider['link']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($slider['is_active']): ?>
                                            <span class="badge badge-success">
                                                <i class="fas fa-eye"></i> Hiển thị
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">
                                                <i class="fas fa-eye-slash"></i> Ẩn
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="<?= BASE_URL ?>/sliders/edit/<?= $slider['id'] ?>" 
                                               class="btn-sm btn-warning" 
                                               title="Sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/sliders/toggle-active/<?= $slider['id'] ?>" 
                                               class="btn-sm <?= $slider['is_active'] ? 'btn-danger' : 'btn-success' ?>" 
                                               title="<?= $slider['is_active'] ? 'Ẩn' : 'Hiển thị' ?>"
                                               onclick="return confirmToggleSlider(event, <?= $slider['is_active'] ? 'true' : 'false' ?>)">
                                                <i class="fas fa-<?= $slider['is_active'] ? 'eye-slash' : 'eye' ?>"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/sliders/delete/<?= $slider['id'] ?>" 
                                               class="btn-sm btn-danger" 
                                               title="Xóa"
                                               onclick="return confirmDeleteSlider(event)">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-data">
                    <i class="fas fa-images"></i>
                    <h3>Chưa có slider nào</h3>
                    <p>Hãy thêm slider đầu tiên cho website của bạn</p>
                    <a href="<?= BASE_URL ?>/sliders" class="btn-primary">
                        <i class="fas fa-plus"></i> Thêm Slider
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <script>
        // Drag and drop to reorder
        const tbody = document.getElementById('sortable-sliders');
        if (tbody) {
            let draggedRow = null;
            
            tbody.querySelectorAll('.slider-row').forEach(row => {
                row.setAttribute('draggable', true);
                
                row.addEventListener('dragstart', function(e) {
                    draggedRow = this;
                    this.classList.add('dragging');
                    e.dataTransfer.effectAllowed = 'move';
                });
                
                row.addEventListener('dragend', function() {
                    this.classList.remove('dragging');
                    draggedRow = null;
                });
                
                row.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    if (this === draggedRow) return;
                    
                    const rect = this.getBoundingClientRect();
                    const offset = e.clientY - rect.top;
                    
                    if (offset > rect.height / 2) {
                        this.parentNode.insertBefore(draggedRow, this.nextSibling);
                    } else {
                        this.parentNode.insertBefore(draggedRow, this);
                    }
                });
            });
            
            tbody.addEventListener('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Update order in database
                const rows = tbody.querySelectorAll('.slider-row');
                const orderData = [];
                
                rows.forEach((row, index) => {
                    const id = row.getAttribute('data-id');
                    orderData.push({
                        id: parseInt(id),
                        order: index + 1
                    });
                    
                    // Update display order in UI
                    row.querySelector('td:nth-child(2) strong').textContent = index + 1;
                });
                
                // Send AJAX request
                fetch('<?= BASE_URL ?>/sliders/update-order', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(orderData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('success', 'Cập nhật thứ tự thành công!');
                    } else {
                        showNotification('error', data.message || 'Có lỗi xảy ra');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('error', 'Không thể cập nhật thứ tự');
                });
            });
        }
        
        // Show notification
        function showNotification(type, message) {
            const notification = document.createElement('div');
            notification.className = 'alert alert-' + type;
            notification.innerHTML = '<i class="fas fa-' + (type === 'success' ? 'check' : 'exclamation') + '-circle"></i> ' + message;
            notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; animation: slideInRight 0.3s;';
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
        
        // Confirm toggle slider
        function confirmToggleSlider(event, isActive) {
            event.preventDefault();
            const link = event.currentTarget;
            confirmAction({
                title: isActive ? 'Ẩn slider' : 'Hiển thị slider',
                message: isActive ? 'Bạn có chắc chắn muốn ẩn slider này?' : 'Bạn có chắc chắn muốn hiển thị slider này?',
                type: isActive ? 'warning' : 'success',
                confirmText: isActive ? 'Ẩn slider' : 'Hiển thị',
                theme: 'admin'
            }).then(confirmed => {
                if (confirmed) window.location.href = link.href;
            });
            return false;
        }
        
        // Confirm delete slider
        function confirmDeleteSlider(event) {
            event.preventDefault();
            const link = event.currentTarget;
            confirmDelete({
                title: 'Xóa slider',
                message: 'Bạn có chắc chắn muốn xóa slider này?<br><br>Hành động này không thể hoàn tác!',
                confirmText: 'Xóa slider',
                theme: 'admin'
            }).then(confirmed => {
                if (confirmed) window.location.href = link.href;
            });
            return false;
        }
    </script>
    </div>
</body>
</html>
