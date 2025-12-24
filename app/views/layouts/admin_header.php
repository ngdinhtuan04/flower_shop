<?php
$currentUser = Session::getUser();
$currentRole = Session::get('user_role');
?>
<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/layout-admin-header.css">
<header class="admin-header">
    <div class="admin-header-content">
        <div class="admin-logo">
            <i class="fas fa-flower"></i>
            <span>Admin Panel</span>
        </div>
        
        <nav class="admin-nav">
            <a href="<?= BASE_URL ?>/admin/dashboard">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="<?= BASE_URL ?>/admin/manage-users">
                <i class="fas fa-users"></i> Quản lý Users
            </a>
            <?php if ($currentRole === 'superadmin'): ?>
                <a href="<?= BASE_URL ?>/admin/pending-admins">
                    <i class="fas fa-user-shield"></i> Duyệt Admin
                </a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/admin/manage-products">
                <i class="fas fa-box"></i> Sản phẩm
            </a>
            <a href="<?= BASE_URL ?>/admin/manage-orders">
                <i class="fas fa-shopping-cart"></i> Đơn hàng
            </a>
            <a href="<?= BASE_URL ?>/admin/manage-categories">
                <i class="fas fa-tags"></i> Danh mục
            </a>
        </nav>
        
        <div class="admin-user-menu">
            <div class="admin-user-info">
                <div class="admin-user-name"><?= htmlspecialchars($currentUser['full_name']) ?></div>
                <div class="admin-user-role">
                    <?php
                    switch($currentRole) {
                        case 'superadmin': echo 'SuperAdmin'; break;
                        case 'admin': echo 'Admin'; break;
                        default: echo 'User';
                    }
                    ?>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/admin/logout" class="admin-logout-btn">
                <i class="fas fa-sign-out-alt"></i> Đăng xuất
            </a>
        </div>
    </div>
</header>
