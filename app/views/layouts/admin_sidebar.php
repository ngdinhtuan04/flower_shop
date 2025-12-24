<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/layout-admin-sidebar.css">

<!-- Sidebar Overlay (for mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleMobileSidebar()"></div>

<!-- Admin Sidebar -->
<div class="sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <i class="fas fa-leaf sidebar-logo"></i>
        <h2>Admin Panel</h2>
        <p>ADMIN</p>
    </div>
    <div class="sidebar-menu">
        <a href="<?= BASE_URL ?>/admin/dashboard" class="<?= (strpos($_SERVER['REQUEST_URI'], '/admin/dashboard') !== false) ? 'active' : '' ?>">
            <i class="fas fa-home"></i> <span>Dashboard</span>
        </a>
        <a href="<?= BASE_URL ?>/admin/users" class="<?= (strpos($_SERVER['REQUEST_URI'], '/admin/users') !== false) ? 'active' : '' ?>">
            <i class="fas fa-users"></i> <span>Quản lý User</span>
        </a>
        <a href="<?= BASE_URL ?>/admin/products" class="<?= (strpos($_SERVER['REQUEST_URI'], '/admin/products') !== false) ? 'active' : '' ?>">
            <i class="fas fa-box"></i> <span>Quản lý Sản phẩm</span>
        </a>
        <a href="<?= BASE_URL ?>/admin/categories" class="<?= (strpos($_SERVER['REQUEST_URI'], '/admin/categories') !== false) ? 'active' : '' ?>">
            <i class="fas fa-tags"></i> <span>Quản lý Danh mục</span>
        </a>
        <a href="<?= BASE_URL ?>/admin/suppliers" class="<?= (strpos($_SERVER['REQUEST_URI'], '/admin/suppliers') !== false) ? 'active' : '' ?>">
            <i class="fas fa-truck"></i> <span>Quản lý Nhà cung cấp</span>
        </a>
        <a href="<?= BASE_URL ?>/admin/orders" class="<?= (strpos($_SERVER['REQUEST_URI'], '/admin/orders') !== false) ? 'active' : '' ?>">
            <i class="fas fa-shopping-cart"></i> <span>Quản lý Đơn hàng</span>
        </a>
        <a href="<?= BASE_URL ?>/promotions" class="<?= (strpos($_SERVER['REQUEST_URI'], '/promotions') !== false) ? 'active' : '' ?>">
            <i class="fas fa-percent"></i> <span>Quản lý Khuyến mãi</span>
        </a>
        <a href="<?= BASE_URL ?>/coupons" class="<?= (strpos($_SERVER['REQUEST_URI'], '/coupons') !== false) ? 'active' : '' ?>">
            <i class="fas fa-ticket-alt"></i> <span>Quản lý Mã giảm giá</span>
        </a>
        <a href="<?= BASE_URL ?>/sliders" class="<?= (strpos($_SERVER['REQUEST_URI'], '/sliders') !== false) ? 'active' : '' ?>">
            <i class="fas fa-images"></i> <span>Quản lý Slider</span>
        </a>
        <a href="<?= BASE_URL ?>/admin/news" class="<?= (strpos($_SERVER['REQUEST_URI'], '/admin/news') !== false && strpos($_SERVER['REQUEST_URI'], '/admin/comments') === false) ? 'active' : '' ?>">
            <i class="fas fa-newspaper"></i> <span>Quản lý Tin tức</span>
        </a>
        <a href="<?= BASE_URL ?>/admin/comments" class="<?= (strpos($_SERVER['REQUEST_URI'], '/admin/comments') !== false) ? 'active' : '' ?>">
            <i class="fas fa-comment-dots"></i> <span>Bình luận Tin tức</span>
        </a>
        <a href="<?= BASE_URL ?>/admin/feedback" class="<?= (strpos($_SERVER['REQUEST_URI'], '/admin/feedback') !== false) ? 'active' : '' ?>">
            <i class="fas fa-comments"></i> <span>Quản lý Góp ý</span>
        </a>
        <a href="<?= BASE_URL ?>/admin/revenue" class="<?= (strpos($_SERVER['REQUEST_URI'], '/admin/revenue') !== false) ? 'active' : '' ?>">
            <i class="fas fa-chart-line"></i> <span>Báo cáo Doanh thu</span>
        </a>
        <a href="<?= BASE_URL ?>/admin/reviews" class="<?= (strpos($_SERVER['REQUEST_URI'], '/admin/reviews') !== false) ? 'active' : '' ?>">
            <i class="fas fa-star"></i> <span>Đánh giá Sản phẩm</span>
        </a>
        <?php if (Session::isSuperAdmin()): ?>
        <a href="<?= BASE_URL ?>/admin/pending-admins" class="<?= (strpos($_SERVER['REQUEST_URI'], '/admin/pending-admins') !== false) ? 'active' : '' ?>">
            <i class="fas fa-user-check"></i> <span>Duyệt Admin</span>
        </a>
        <?php endif; ?>
    </div>
    <a href="<?= BASE_URL ?>/admin/logout" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i> <span>Đăng xuất</span>
    </a>
</div>

<script>
// Mobile Sidebar Toggle
function toggleMobileSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    sidebar.classList.toggle('mobile-active');
    overlay.classList.toggle('active');
}

// Close mobile sidebar when clicking menu item
document.querySelectorAll('.sidebar-menu a').forEach(link => {
    link.addEventListener('click', function() {
        if (window.innerWidth <= 768) {
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            sidebar.classList.remove('mobile-active');
            overlay.classList.remove('active');
        }
    });
});

// Handle window resize
window.addEventListener('resize', function() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    
    if (window.innerWidth > 768) {
        sidebar.classList.remove('mobile-active');
        overlay.classList.remove('active');
    }
});

// Clear collapsed state from localStorage
localStorage.removeItem('sidebarCollapsed');
</script>