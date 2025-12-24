<?php 
$headerUser = Session::getUser();

// Lấy số lượng giỏ hàng từ database
$cartCount = 0;
$unreadNotifications = 0;
if (Session::isLoggedIn()) {
    require_once APP_PATH . '/models/Cart.php';
    $cartModel = new Cart();
    $cartCount = $cartModel->getCartCount(Session::get('user_id'));
    
    require_once APP_PATH . '/models/Notification.php';
    $notificationModel = new Notification();
    $unreadNotifications = $notificationModel->countUnread(Session::get('user_id'));
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/notifications.css">

<!-- Header + Navigation -->
<header class="site-header">
    <div class="header-container">
        <div class="logo">
            <i class="fas fa-gifts"></i>
            <div class="logo-text">
                <h1>Flower Shop</h1>
                <span>Hoa & Quà Tặng</span>
            </div>
        </div>

        <nav class="nav-menu">
            <a href="<?= BASE_URL ?>" class="nav-item <?= ($_SERVER['REQUEST_URI'] == '/' || $_SERVER['REQUEST_URI'] == BASE_URL) ? 'active' : '' ?>">
                <i class="fas fa-home"></i>
                <span>Trang chủ</span>
            </a>
            <a href="<?= BASE_URL ?>/products" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/products') !== false || strpos($_SERVER['REQUEST_URI'], '/product') !== false ? 'active' : '' ?>">
                <i class="fas fa-spa"></i>
                <span>Sản phẩm</span>
            </a>
            <a href="<?= BASE_URL ?>/tracking" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/tracking') !== false ? 'active' : '' ?>">
                <i class="fas fa-search"></i>
                <span>Tra cứu đơn</span>
            </a>
            <a href="<?= BASE_URL ?>/news" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/news') !== false ? 'active' : '' ?>">
                <i class="fas fa-newspaper"></i>
                <span>Tin tức</span>
            </a>
            <a href="<?= BASE_URL ?>/feedback" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], '/feedback') !== false ? 'active' : '' ?>">
                <i class="fas fa-comment-dots"></i>
                <span>Góp ý</span>
            </a>
        </nav>

        <div class="header-actions">
            <a href="<?= BASE_URL ?>/wishlist" class="action-btn" title="Danh sách yêu thích">
                <i class="fas fa-heart"></i>
                <?php if (Session::isLoggedIn()): ?>
                    <span class="badge wishlist-count">0</span>
                <?php endif; ?>
            </a>
            
            <a href="<?= BASE_URL ?>/cart" class="action-btn">
                <i class="fas fa-shopping-bag"></i>
                <span class="badge cart-count"><?= $cartCount ?></span>
            </a>
            
            <?php if ($headerUser): ?>
            <!-- Notification Bell -->
            <div class="notification-menu">
                <button class="action-btn notification-btn" title="Thông báo">
                    <i class="fas fa-bell"></i>
                    <?php if ($unreadNotifications > 0): ?>
                        <span class="badge notification-count"><?= $unreadNotifications ?></span>
                    <?php endif; ?>
                </button>
                <div class="notification-dropdown">
                    <div class="notification-header">
                        <h4>Thông báo</h4>
                        <button class="mark-all-read" onclick="markAllNotificationsRead()">
                            <i class="fas fa-check-double"></i> Đánh dấu tất cả đã đọc
                        </button>
                    </div>
                    <div class="notification-list" id="notificationList">
                        <div class="loading-notifications">
                            <i class="fas fa-spinner fa-spin"></i> Đang tải...
                        </div>
                    </div>
                    <div class="notification-footer">
                        <a href="<?= BASE_URL ?>/notifications">Xem tất cả thông báo</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($headerUser): ?>
            <div class="user-menu">
                <button class="user-btn">
                    <?php if (!empty($headerUser['avatar'])): ?>
                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($headerUser['avatar']) ?>" 
                             alt="Avatar" 
                             style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 2px solid white;">
                    <?php else: ?>
                        <i class="fas fa-user-circle"></i>
                    <?php endif; ?>
                </button>
                <div class="user-dropdown">
                    <a href="<?= BASE_URL ?>/user/profile"><i class="fas fa-user"></i> Tài khoản</a>
                    <a href="<?= BASE_URL ?>/address"><i class="fas fa-map-marker-alt"></i> Địa chỉ của tôi</a>
                    <a href="<?= BASE_URL ?>/orders"><i class="fas fa-box"></i> Đơn hàng</a>
                    <a href="<?= BASE_URL ?>/wishlist"><i class="fas fa-heart"></i> Yêu thích</a>
                    <a href="<?= BASE_URL ?>/feedback/my-feedback"><i class="fas fa-comment-dots"></i> Góp ý của tôi</a>
                    <a href="<?= BASE_URL ?>/user/logout"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
                </div>
            </div>
            <?php else: ?>
            <a href="<?= BASE_URL ?>/user/login" class="btn-login">
                <i class="fas fa-sign-in-alt"></i>
                <span>Đăng nhập</span>
            </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<script>
    const BASE_URL = '<?= BASE_URL ?>';
    
    // Export global functions first
    window.updateWishlistBadge = function(count) {
        const badge = document.querySelector('.wishlist-count');
        if (badge) {
            badge.textContent = count;
            badge.style.transform = 'scale(1.3)';
            setTimeout(() => {
                badge.style.transform = 'scale(1)';
            }, 200);
        }
    };
    
    // User dropdown toggle - chỉ chạy 1 lần
    if (!window.userDropdownInitialized) {
        window.userDropdownInitialized = true;
        
        document.addEventListener('DOMContentLoaded', function() {
            const userBtn = document.querySelector('.user-btn');
            const userDropdown = document.querySelector('.user-dropdown');
            
            if (userBtn && userDropdown) {
                userBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('Toggle dropdown');
                    userDropdown.classList.toggle('show');
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!e.target.closest('.user-menu')) {
                        userDropdown.classList.remove('show');
                    }
                });
            }
            
            // Load wishlist count
            <?php if (Session::isLoggedIn()): ?>
            loadWishlistCount();
            loadNotifications();
            refreshNotificationBadge(); // Load badge count immediately
            
            // Setup notification dropdown
            const notificationBtn = document.querySelector('.notification-btn');
            const notificationDropdown = document.querySelector('.notification-dropdown');
            
            if (notificationBtn && notificationDropdown) {
                notificationBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    notificationDropdown.classList.toggle('show');
                    if (notificationDropdown.classList.contains('show')) {
                        loadNotifications();
                    }
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!e.target.closest('.notification-menu')) {
                        notificationDropdown.classList.remove('show');
                    }
                });
            }
            <?php endif; ?>
        });
    }
    
    // Function to load wishlist count
    function loadWishlistCount() {
        fetch(BASE_URL + '/wishlist/count')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const badge = document.querySelector('.wishlist-count');
                if (badge) {
                    badge.textContent = data.count;
                }
            }
        })
        .catch(error => {
            console.error('Error loading wishlist count:', error);
        });
    }
    
    // Load notifications
    function loadNotifications() {
        const listEl = document.getElementById('notificationList');
        if (!listEl) return;
        
        listEl.innerHTML = '<div class="loading-notifications"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>';
        
        fetch(BASE_URL + '/notifications/get-recent', {
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP error! status: ' + response.status);
                }
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Response is not valid JSON:', text);
                        throw new Error('Invalid JSON response');
                    }
                });
            })
            .then(data => {
                if (data.success && data.notifications && data.notifications.length > 0) {
                    listEl.innerHTML = data.notifications.map(n => `
                        <div class="notification-item ${n.is_read ? '' : 'unread'}" data-id="${n.id}" onclick="markNotificationRead(${n.id}, '${n.link || ''}')">
                            <div class="notification-icon ${n.type}">
                                <i class="fas ${getNotificationIcon(n.type)}"></i>
                            </div>
                            <div class="notification-content">
                                <div class="notification-title">${n.title}</div>
                                <div class="notification-message">${n.message}</div>
                                <div class="notification-time">${timeAgo(n.created_at)}</div>
                            </div>
                        </div>
                    `).join('');
                    
                    // Update badge count
                    const unreadCount = data.notifications.filter(n => !n.is_read).length;
                    updateNotificationBadge(unreadCount);
                } else {
                    listEl.innerHTML = '<div class="no-notifications"><i class="fas fa-inbox"></i><p>Không có thông báo</p></div>';
                    updateNotificationBadge(0);
                }
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
                listEl.innerHTML = '<div class="error-notifications">Không thể tải thông báo</div>';
            });
    }
    
    // Mark notification as read
    function markNotificationRead(id, link) {
        fetch(BASE_URL + '/notifications/mark-as-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'id=' + id
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateNotificationBadge(data.unreadCount);
                if (link) {
                    window.location.href = BASE_URL + link;
                }
            }
        })
        .catch(error => console.error('Error marking notification read:', error));
    }
    
    // Mark all notifications as read
    function markAllNotificationsRead() {
        fetch(BASE_URL + '/notifications/mark-all-read', {
            method: 'POST',
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateNotificationBadge(0);
                loadNotifications();
            }
        })
        .catch(error => console.error('Error marking all notifications read:', error));
    }
    
    // Update notification badge
    function updateNotificationBadge(count) {
        const badge = document.querySelector('.notification-count');
        if (count > 0) {
            if (!badge) {
                const btn = document.querySelector('.notification-btn');
                const newBadge = document.createElement('span');
                newBadge.className = 'badge notification-count';
                newBadge.textContent = count;
                btn.appendChild(newBadge);
            } else {
                badge.textContent = count;
            }
        } else {
            if (badge) badge.remove();
        }
    }
    
    // Get icon for notification type
    function getNotificationIcon(type) {
        const icons = {
            'review_approved': 'fa-check-circle',
            'review_rejected': 'fa-times-circle',
            'order_status': 'fa-box',
            'promotion': 'fa-gift',
            'feedback_reply': 'fa-comment-dots',
            'system': 'fa-info-circle'
        };
        return icons[type] || 'fa-bell';
    }
    
    // Time ago helper
    function timeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const seconds = Math.floor((now - date) / 1000);
        
        if (seconds < 60) return 'Vừa xong';
        const minutes = Math.floor(seconds / 60);
        if (minutes < 60) return minutes + ' phút trước';
        const hours = Math.floor(minutes / 60);
        if (hours < 24) return hours + ' giờ trước';
        const days = Math.floor(hours / 24);
        if (days < 7) return days + ' ngày trước';
        return date.toLocaleDateString('vi-VN');
    }
    
    // Function to refresh notification badge count
    function refreshNotificationBadge() {
        fetch(BASE_URL + '/notifications/get-unread-count', {
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        })
        .then(response => response.json())
        .then(data => {
            if (data.unreadCount !== undefined) {
                updateNotificationBadge(data.unreadCount);
            }
        })
        .catch(error => console.error('Error refreshing notification count:', error));
    }
    
    // Auto-refresh notification badge every 10 seconds
    <?php if (Session::isLoggedIn()): ?>
    setInterval(() => {
        refreshNotificationBadge();
    }, 10000); // Every 10 seconds
    <?php endif; ?>
</script>