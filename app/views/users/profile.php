<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin cá nhân - Flower Shop</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/home.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/profile.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/notifications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>
    
    <div class="main-content-wrapper">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="<?= BASE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a>
            <span class="separator">/</span>
            <span class="current">Thông tin cá nhân</span>
        </div>
        
        <div class="profile-container">
        <div class="profile-grid">
            <!-- Sidebar -->
            <div class="profile-sidebar">
                <div class="avatar-section">
                    <div class="avatar-wrapper">
                        <?php if (!empty($userProfile['avatar'])): ?>
                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($userProfile['avatar']) ?>" 
                                 alt="Avatar" class="avatar-image" id="avatarPreview">
                        <?php else: ?>
                            <div class="avatar-placeholder" id="avatarPreview">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                        <form method="POST" action="<?= BASE_URL ?>/user/profile" enctype="multipart/form-data" id="avatarForm">
                            <label class="avatar-upload-btn" title="Thay đổi ảnh đại diện">
                                <i class="fas fa-camera"></i>
                                <input type="file" name="avatar" accept="image/*" onchange="previewAndUploadAvatar(event)">
                            </label>
                        </form>
                    </div>
                </div>
                
                <div class="user-info">
                    <h2><?= htmlspecialchars($userProfile['full_name']) ?></h2>
                    <div class="user-email"><?= htmlspecialchars($userProfile['email']) ?></div>
                </div>
                
                <div class="user-stats">
                    <div class="stat-item">
                        <span class="stat-label">Tên đăng nhập</span>
                        <span class="stat-value"><?= htmlspecialchars($userProfile['username']) ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Ngày tham gia</span>
                        <span class="stat-value">
                            <?php 
                            if (!empty($userProfile['created_at'])) {
                                echo date('d/m/Y', strtotime($userProfile['created_at']));
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Trạng thái</span>
                        <span class="stat-value" style="color: #2e7d32;">
                            <i class="fas fa-check-circle"></i> Hoạt động
                        </span>
                    </div>
                </div>
                
                <div class="quick-links">
                    <a href="<?= BASE_URL ?>/address" class="quick-link">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Địa chỉ giao hàng</span>
                    </a>
                    <a href="<?= BASE_URL ?>/orders" class="quick-link">
                        <i class="fas fa-box"></i>
                        <span>Đơn hàng của tôi</span>
                    </a>
                    <a href="<?= BASE_URL ?>/wishlist" class="quick-link">
                        <i class="fas fa-heart"></i>
                        <span>Danh sách yêu thích</span>
                    </a>
                    <a href="<?= BASE_URL ?>/feedback/my-feedback" class="quick-link">
                        <i class="fas fa-comment-dots"></i>
                        <span>Góp ý của tôi</span>
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="profile-main">
                <h2 class="section-title"><i class="fas fa-user-edit"></i> Chỉnh sửa thông tin</h2>
                
                <form method="POST" action="<?= BASE_URL ?>/user/profile">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Họ và tên <span style="color: #e91e63;">*</span></label>
                            <input type="text" name="full_name" required 
                                   value="<?= htmlspecialchars($userProfile['full_name']) ?>"
                                   placeholder="Nhập họ và tên">
                        </div>
                        
                        <div class="form-group">
                            <label>Số điện thoại</label>
                            <input type="tel" 
                            name="phone" 
                                   value="<?= isset($userProfile['phone']) ? htmlspecialchars($userProfile['phone']) : '' ?>" 
                                   placeholder="Nhập số điện thoại">
                        </div>
                        
                        <div class="form-group form-group-full">
                            <label>Email</label>
                            <input type="email" value="<?= htmlspecialchars($userProfile['email']) ?>" disabled>
                            <small style="color: #999; display: block; margin-top: 5px;">Email không thể thay đổi</small>
                        </div>
                        
                        <div class="form-group form-group-full">
                            <label>Địa chỉ</label>
                            <textarea name="address" placeholder="Nhập địa chỉ" style="min-height: 80px;"><?= isset($userProfile['address']) ? htmlspecialchars($userProfile['address']) : '' ?></textarea>
                            <small style="color: #999; display: block; margin-top: 5px;">
                                <i class="fas fa-lightbulb"></i> 
                                Quản lý nhiều địa chỉ giao hàng tại 
                                <a href="<?= BASE_URL ?>/address" style="color: #ff6b9d; font-weight: 600;">Địa chỉ của tôi</a>
                            </small>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-update">
                        <i class="fas fa-save"></i> Cập nhật thông tin
                    </button>
                </form>
            </div>
        </div>
    </div>
    </div><!-- End main-content-wrapper -->
    
    
    <script src="<?= ASSETS_URL ?>/js/profile.js"></script>
    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
    <?php include APP_PATH . '/views/layouts/toast_notification.php'; ?>
</body>
</html>