<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo - Flower Shop</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/home.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/notifications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .breadcrumb {
            max-width: 1400px;
            margin: 20px auto 15px;
            padding: 0 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        .breadcrumb a {
            color: #718096;
            text-decoration: none;
            transition: color 0.3s;
        }
        .breadcrumb a:hover {
            color: #ff6b9d;
        }
        .breadcrumb .separator {
            color: #cbd5e0;
        }
        .breadcrumb .current {
            color: #2d3748;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="<?= BASE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a>
        <span class="separator">/</span>
        <a href="<?= BASE_URL ?>/user/profile">Tài khoản</a>
        <span class="separator">/</span>
        <span class="current">Thông báo</span>
    </div>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; display: flex; justify-content: space-between; align-items: center; padding: 20px 25px;">
                    <h4 style="margin: 0; display: flex; align-items: center; gap: 12px; font-weight: 700; font-size: 24px;">
                        <i class="fas fa-bell"></i> Thông báo của tôi
                    </h4>
                    <button class="btn-mark-all-read" onclick="markAllRead()">
                        <i class="fas fa-check-double"></i> Đánh dấu tất cả đã đọc
                    </button>
                </div>
                
                <div class="card-body p-0">
                    <?php 
                    // Debug: Check notifications data
                    error_log("Notifications count: " . count($notifications));
                    if (!empty($notifications)) {
                        error_log("First notification: " . print_r($notifications[0], true));
                    }
                    ?>
                    
                    <?php if (!empty($notifications)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($notifications as $notification): ?>
                                <div class="notification-item-full <?= $notification['is_read'] ? '' : 'unread-item' ?>" 
                                     data-id="<?= $notification['id'] ?>"
                                     data-read="<?= $notification['is_read'] ?>"
                                     onclick="viewNotificationDetailData(this, <?= htmlspecialchars(json_encode([
                                         'id' => $notification['id'],
                                         'title' => $notification['title'],
                                         'message' => $notification['message'],
                                         'type' => $notification['type'],
                                         'link' => $notification['link'] ?? '',
                                         'created_at' => date('d/m/Y H:i', strtotime($notification['created_at'])),
                                         'is_read' => (bool)$notification['is_read']
                                     ]), ENT_QUOTES, 'UTF-8') ?>)">
                                    <div class="notification-icon-large <?= $notification['type'] ?>">
                                        <i class="fas <?= getNotificationIcon($notification['type']) ?>"></i>
                                    </div>
                                    <div class="notification-content">
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                                            <h6><?= htmlspecialchars($notification['title']) ?></h6>
                                            <?php if (!$notification['is_read']): ?>
                                                <span class="badge bg-primary">Mới</span>
                                            <?php endif; ?>
                                        </div>
                                        <small>
                                            <i class="far fa-clock"></i> 
                                            <?= date('d/m/Y H:i', strtotime($notification['created_at'])) ?>
                                        </small>
                                    </div>
                                    <div style="margin-left: auto;">
                                        <button class="btn-link" onclick="event.stopPropagation(); showDeleteConfirm(<?= $notification['id'] ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <div class="p-3">
                                <nav>
                                    <ul class="pagination justify-content-center mb-0">
                                        <?php if ($currentPage > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?= $currentPage - 1 ?>">
                                                    <i class="fas fa-chevron-left"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                                            <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($currentPage < $totalPages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?= $currentPage + 1 ?>">
                                                    <i class="fas fa-chevron-right"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-bell-slash"></i>
                            </div>
                            <h3 class="empty-state-title">Không có thông báo</h3>
                            <p class="empty-state-text">Bạn chưa có thông báo nào</p>
                            <p class="empty-state-subtext">Các thông báo về đơn hàng, đánh giá và khuyến mãi sẽ hiển thị tại đây</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Chi Tiết Thông Báo -->
<div id="notificationModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 16px; max-width: 500px; width: 90%; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); position: relative;">
        <button onclick="closeModal()" style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; color: #999; cursor: pointer; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: all 0.2s;">
            <i class="fas fa-times"></i>
        </button>
        
        <div id="modalContent">
            <div style="text-align: center; margin-bottom: 20px;">
                <div id="modalIcon" style="width: 70px; height: 70px; margin: 0 auto 15px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; color: white;"></div>
                <h4 id="modalTitle" style="margin: 0 0 10px 0; color: #333;"></h4>
                <small id="modalTime" style="color: #999;"></small>
            </div>
            
            <div id="modalMessage" style="padding: 20px; background: #f8f9fa; border-radius: 12px; color: #666; line-height: 1.6; margin-bottom: 20px;"></div>
            
            <div style="display: flex; gap: 10px;">
                <button onclick="closeModal()" style="flex: 1; padding: 12px; border: 1px solid #e8e8e8; background: white; color: #666; border-radius: 8px; cursor: pointer; font-weight: 500; transition: all 0.2s;">
                    Đóng
                </button>
                <button id="modalActionBtn" onclick="navigateFromModal()" style="flex: 1; padding: 12px; border: none; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border-radius: 8px; cursor: pointer; font-weight: 500; transition: all 0.2s;">
                    Xem chi tiết
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Xác Nhận Xóa -->
<div id="deleteConfirmModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10001; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 16px; max-width: 400px; width: 90%; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); text-align: center;">
        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 28px; color: white;">
            <i class="fas fa-trash-alt"></i>
        </div>
        
        <h4 style="margin: 0 0 10px 0; color: #333; font-size: 20px;">Xóa thông báo này?</h4>
        <p style="color: #666; margin: 0 0 25px 0; line-height: 1.5;">
            Bạn có chắc chắn muốn xóa thông báo này không? Hành động này không thể hoàn tác.
        </p>
        
        <form id="deleteForm" method="POST" action="<?= BASE_URL ?>/notifications/delete" style="display: flex; gap: 10px;">
            <input type="hidden" name="id" id="deleteNotificationId">
            <button type="button" onclick="closeDeleteConfirm()" style="flex: 1; padding: 12px 20px; border: 2px solid #e8e8e8; background: white; color: #666; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.2s; font-size: 15px;">
                Hủy
            </button>
            <button type="submit" style="flex: 1; padding: 12px 20px; border: none; background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%); color: white; border-radius: 8px; cursor: pointer; font-weight: 600; transition: all 0.2s; font-size: 15px; box-shadow: 0 4px 12px rgba(238, 90, 111, 0.3);">
                Xóa
            </button>
        </form>
    </div>
</div>

<script src="<?= ASSETS_URL ?>/js/notifications_full.js"></script>

<?php
function getNotificationIcon($type) {
    $icons = [
        'review_approved' => 'fa-check-circle',
        'review_rejected' => 'fa-times-circle',
        'order_status' => 'fa-box',
        'promotion' => 'fa-gift',
        'system' => 'fa-info-circle'
    ];
    return $icons[$type] ?? 'fa-bell';
}
?>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>
