<?php
$currentRole = Session::get('user_role');
$user = Session::getUser();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng - Admin</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Badge styles */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-confirmed { background: #ede9fe; color: #6d28d9; }
        .badge-processing { background: #dbeafe; color: #1d4ed8; }
        .badge-shipping { background: #cffafe; color: #0891b2; }
        .badge-delivered { background: #d1fae5; color: #047857; }
        .badge-cancelled { background: #fee2e2; color: #dc2626; }
        .badge-ready { background: #fef3c7; color: #b45309; }
        .badge-picked_up { background: #d1fae5; color: #047857; }
        
        .badge-paid { background: #d1fae5; color: #047857; }
        .badge-unpaid { background: #fee2e2; color: #dc2626; }
        
        .badge-cod { background: #fef3c7; color: #92400e; }
        .badge-vnpay { background: #dbeafe; color: #1d4ed8; }
        .badge-bank_transfer { background: #ede9fe; color: #6d28d9; }
        
        .badge-pickup { background: #fce7f3; color: #be185d; }
        .badge-standard { background: #e0e7ff; color: #3730a3; }
        .badge-express { background: #fef3c7; color: #b45309; }
        .badge-same_day { background: #ccfbf1; color: #0f766e; }
        
        /* Filter Bar Styles */
        .filter-bar {
            display: flex;
            gap: 12px;
            padding: 15px 20px;
            background: white;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filter-bar input[type="text"] {
            padding: 10px 15px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            min-width: 250px;
            transition: all 0.2s;
        }
        
        .filter-bar input[type="text"]:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .filter-bar select {
            padding: 10px 35px 10px 15px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            background: white;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px;
            min-width: 160px;
            transition: all 0.2s;
        }
        
        .filter-bar select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .filter-bar select:hover {
            border-color: #9ca3af;
        }
        
        .filter-bar button,
        .filter-bar .btn-clear {
            padding: 10px 16px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #f9fafb;
            color: #374151;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        
        .filter-bar button:hover,
        .filter-bar .btn-clear:hover {
            background: #f3f4f6;
            border-color: #d1d5db;
        }
        
        .filter-bar .btn-clear {
            color: #dc2626;
            border-color: #fecaca;
            background: #fef2f2;
        }
        
        .filter-bar .btn-clear:hover {
            background: #fee2e2;
            border-color: #fca5a5;
        }
        
        .orders-container {
            display: grid;
            grid-template-columns: 420px 1fr;
            gap: 20px;
            height: calc(100vh - 200px);
        }
        
        /* Order List Panel */
        .order-list-panel {
            background: #f8fafc;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .order-list-header {
            padding: 15px 20px;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .order-list-header h4 {
            margin: 0;
            font-size: 14px;
        }
        
        .order-list-body {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
        }
        
        /* Order Card */
        .order-card {
            padding: 15px;
            margin-bottom: 10px;
            background: white;
            border-radius: 10px;
            cursor: pointer;
            border-left: 4px solid #e5e7eb;
            transition: all 0.2s ease;
        }
        
        .order-card:hover {
            transform: translateX(5px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .order-card.active {
            background: #eff6ff;
            border-left-color: #3b82f6;
        }
        
        .order-card.status-pending { border-left-color: #f59e0b; }
        .order-card.status-confirmed { border-left-color: #8b5cf6; }
        .order-card.status-processing { border-left-color: #3b82f6; }
        .order-card.status-shipping { border-left-color: #06b6d4; }
        .order-card.status-delivered { border-left-color: #10b981; }
        .order-card.status-cancelled { border-left-color: #ef4444; }
        
        .order-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }
        
        .order-code {
            font-weight: 700;
            color: #1f2937;
            font-size: 14px;
        }
        
        .order-customer {
            color: #4b5563;
            font-size: 13px;
            margin-bottom: 8px;
        }
        
        .order-customer i {
            color: #9ca3af;
            width: 16px;
        }
        
        .order-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .order-total {
            color: #059669;
            font-weight: 700;
            font-size: 15px;
        }
        
        .order-date {
            color: #9ca3af;
            font-size: 12px;
        }
        
        /* Detail Panel */
        .order-detail-panel {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .order-detail-header {
            padding: 15px 20px;
            background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
            color: white;
        }
        
        .order-detail-header h4 {
            margin: 0;
        }
        
        .order-detail-body {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }
        
        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .info-card {
            background: #f8fafc;
            border-radius: 10px;
            padding: 15px;
        }
        
        .info-card h5 {
            margin: 0 0 12px 0;
            color: #374151;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-card h5 i {
            color: #6b7280;
        }
        
        .info-card p {
            margin: 6px 0;
            color: #4b5563;
            font-size: 13px;
        }
        
        .info-card p strong {
            color: #1f2937;
        }
        
        /* Products Table */
        .products-section {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .products-header {
            padding: 12px 15px;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .products-header h5 {
            margin: 0;
            color: #374151;
            font-size: 14px;
        }
        
        .products-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .products-table th {
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            background: #f9fafb;
            font-size: 13px;
        }
        
        .products-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 13px;
        }
        
        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        /* Status Select */
        .status-action {
            margin-top: 20px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 10px;
        }
        
        .status-action h5 {
            margin: 0 0 15px 0;
            color: #374151;
        }
        
        .status-select-group {
            display: flex;
            gap: 10px;
        }
        
        .status-select-group select {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .status-select-group button {
            padding: 12px 25px;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }
        
        .status-select-group button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59,130,246,0.4);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        /* Summary Box */
        .order-summary {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
        }
        
        .summary-row.total {
            border-top: 1px solid #86efac;
            margin-top: 8px;
            padding-top: 12px;
            font-size: 18px;
            font-weight: 700;
        }
        
        .summary-row.total span:last-child {
            color: #059669;
        }
        
        /* Toast notification */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
        }
        
        .toast {
            padding: 15px 20px;
            border-radius: 10px;
            color: white;
            font-weight: 500;
            margin-bottom: 10px;
            animation: slideIn 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 300px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .toast.success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .toast.error { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
        .toast.warning { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php require_once APP_PATH . '/views/layouts/admin_sidebar.php'; ?>
        
        <div class="main-content">
            <!-- Toast container -->
            <div id="toastContainer" class="toast-container"></div>
            
            <!-- Topbar -->
            <div class="topbar">
                <h2><i class="fas fa-shopping-cart"></i> Quản lý đơn hàng</h2>
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <strong><?= htmlspecialchars($user['full_name']) ?></strong>
                </div>
            </div>
            
            <!-- Filter Bar -->
            <div class="filter-bar">
                <input type="text" id="searchInput" placeholder="🔍 Tìm mã đơn, tên KH, email..." style="flex: 1;">
                
                <select id="statusFilter">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="pending">Chờ xử lý</option>
                    <option value="confirmed">Đã xác nhận</option>
                    <option value="processing">Đang xử lý</option>
                    <option value="shipping">Đang giao</option>
                    <option value="delivered">Đã giao</option>
                    <option value="cancelled">Đã hủy</option>
                </select>
                
                <select id="paymentFilter">
                    <option value="">-- Tất cả thanh toán --</option>
                    <option value="COD">COD</option>
                    <option value="VNPAY">VNPAY</option>
                </select>
                
                <button type="button" class="btn-cancel" onclick="clearFilters()" style="padding: 12px 20px;">
                    <i class="fas fa-times"></i> Xóa lọc
                </button>
            </div>
            
            <!-- Main 2-column layout -->
            <div class="orders-container">
                <!-- Left: Order List -->
                <div class="order-list-panel">
                    <div class="order-list-header">
                        <h4><i class="fas fa-list"></i> Danh sách đơn hàng (<span id="orderCount">0</span>)</h4>
                    </div>
                    <div class="order-list-body" id="orderListBody">
                        <!-- Orders will be rendered here -->
                    </div>
                </div>
                
                <!-- Right: Order Detail -->
                <div class="order-detail-panel">
                    <div class="order-detail-header" id="orderDetailHeader">
                        <h4><i class="fas fa-info-circle"></i> Chi tiết đơn hàng</h4>
                    </div>
                    <div class="order-detail-body" id="orderDetailBody">
                        <div class="empty-state">
                            <i class="fas fa-hand-pointer"></i>
                            <p>Chọn một đơn hàng từ danh sách bên trái để xem chi tiết</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="<?= ASSETS_URL ?>/js/confirm-delete.js"></script>
    <script>
        const BASE_URL = '<?= BASE_URL ?>';
        
        // Toast notification
        function showToast(message, type = 'success', duration = 3000) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            const icon = type === 'success' ? 'check-circle' : (type === 'error' ? 'exclamation-circle' : 'exclamation-triangle');
            toast.innerHTML = `<i class="fas fa-${icon}"></i> ${message}`;
            
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease forwards';
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }
        
        // All orders data
        let allOrders = <?= json_encode($orders ?? []) ?>;
        let currentOrderId = null;
        
        // Status labels
        const statusLabels = {
            'pending': 'Chờ xử lý',
            'confirmed': 'Đã xác nhận',
            'processing': 'Đang xử lý',
            'shipping': 'Đang giao',
            'delivered': 'Đã giao',
            'cancelled': 'Đã hủy',
            'ready': 'Sẵn sàng lấy',
            'picked_up': 'Đã lấy hàng'
        };
        
        // Status flow - các trạng thái có thể chuyển đến
        const statusFlow = {
            'pending': ['confirmed', 'cancelled'],
            'confirmed': ['processing', 'cancelled'],
            'processing': ['shipping', 'ready', 'cancelled'], // ready cho pickup
            'shipping': ['delivered', 'cancelled'],
            'ready': ['picked_up', 'cancelled'], // cho pickup
            'delivered': [],
            'picked_up': [],
            'cancelled': []
        };
        
        // Shipping method labels
        const shippingMethodLabels = {
            'standard': 'Tiêu chuẩn',
            'express': 'Nhanh',
            'same_day': 'Trong ngày',
            'pickup': 'Nhận tại cửa hàng'
        };
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            renderOrderList(allOrders);
        });
        
        // Render order list
        function renderOrderList(orders) {
            const container = document.getElementById('orderListBody');
            document.getElementById('orderCount').textContent = orders.length;
            
            if (orders.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Không tìm thấy đơn hàng nào</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = orders.map(order => `
                <div class="order-card status-${order.order_status}" 
                     onclick="loadOrderDetail(${order.id})" 
                     data-order-id="${order.id}">
                    <div class="order-card-header">
                        <span class="order-code">#${order.order_code || order.id}</span>
                        <span class="badge badge-${order.order_status}">${statusLabels[order.order_status] || order.order_status}</span>
                    </div>
                    <div class="order-customer">
                        <i class="fas fa-user"></i> ${order.customer_name || 'N/A'}
                    </div>
                    <div class="order-card-footer">
                        <span class="order-total">${formatCurrency(order.total)}₫</span>
                        <span class="order-date">${formatDate(order.created_at)}</span>
                    </div>
                </div>
            `).join('');
            
            // If we have a current order selected, re-highlight it
            if (currentOrderId) {
                const activeCard = document.querySelector(`.order-card[data-order-id="${currentOrderId}"]`);
                if (activeCard) activeCard.classList.add('active');
            }
        }
        
        // Load order detail
        function loadOrderDetail(orderId) {
            currentOrderId = orderId;
            
            // Highlight selected
            document.querySelectorAll('.order-card').forEach(card => {
                card.classList.remove('active');
            });
            const selectedCard = document.querySelector(`.order-card[data-order-id="${orderId}"]`);
            if (selectedCard) selectedCard.classList.add('active');
            
            // Show loading
            document.getElementById('orderDetailBody').innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-spinner fa-spin"></i>
                    <p>Đang tải chi tiết...</p>
                </div>
            `;
            
            // Fetch order detail
            fetch(BASE_URL + '/admin/orders/detail-json/' + orderId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderOrderDetail(data.order, data.items);
                    } else {
                        showToast(data.message || 'Không thể tải chi tiết đơn hàng', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Lỗi khi tải dữ liệu', 'error');
                });
        }
        
        // Render order detail
        function renderOrderDetail(order, items) {
            const detailBody = document.getElementById('orderDetailBody');
            
            // Calculate subtotal
            let subtotal = 0;
            items.forEach(item => {
                subtotal += (item.quantity * item.price);
            });
            
            let html = `
                <!-- Info Grid -->
                <div class="info-grid">
                    <div class="info-card">
                        <h5><i class="fas fa-user"></i> Thông tin khách hàng</h5>
                        <p><strong>${order.customer_name || 'N/A'}</strong></p>
                        <p><i class="fas fa-envelope" style="color: #9ca3af; width: 16px;"></i> ${order.customer_email || 'N/A'}</p>
                        <p><i class="fas fa-phone" style="color: #9ca3af; width: 16px;"></i> ${order.customer_phone || 'N/A'}</p>
                    </div>
                    <div class="info-card">
                        <h5><i class="fas fa-${order.shipping_method === 'pickup' ? 'store' : 'map-marker-alt'}"></i> ${order.shipping_method === 'pickup' ? 'Nhận tại cửa hàng' : 'Địa chỉ giao hàng'}</h5>
                        ${order.shipping_method === 'pickup' 
                            ? `<p><i class="fas fa-store" style="color: #be185d;"></i> <strong>Khách hàng sẽ đến lấy tại cửa hàng</strong></p>
                               <p style="margin-top: 8px;"><span class="badge badge-pickup">${shippingMethodLabels['pickup']}</span></p>`
                            : `<p>${order.shipping_address || 'Chưa có địa chỉ'}</p>
                               <p style="margin-top: 8px;"><span class="badge badge-${order.shipping_method}">${shippingMethodLabels[order.shipping_method] || order.shipping_method}</span></p>`
                        }
                        ${order.shipping_note ? `<p style="margin-top: 10px; color: #f59e0b;"><i class="fas fa-sticky-note"></i> <em>${order.shipping_note}</em></p>` : ''}
                    </div>
                </div>
                
                <div class="info-grid">
                    <div class="info-card">
                        <h5><i class="fas fa-credit-card"></i> Thanh toán</h5>
                        <p><strong>Phương thức:</strong> <span class="badge badge-${(order.payment_method || '').toLowerCase()}">${(order.payment_method || 'N/A').toUpperCase()}</span></p>
                        <p><strong>Trạng thái:</strong> <span class="badge badge-${order.payment_status === 'paid' ? 'paid' : 'unpaid'}">${order.payment_status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán'}</span></p>
                    </div>
                    <div class="info-card">
                        <h5><i class="fas fa-clock"></i> Thời gian</h5>
                        <p><strong>Ngày đặt:</strong> ${formatDateTime(order.created_at)}</p>
                        ${order.updated_at ? `<p><strong>Cập nhật:</strong> ${formatDateTime(order.updated_at)}</p>` : ''}
                    </div>
                </div>
                
                <!-- Products -->
                <div class="products-section">
                    <div class="products-header">
                        <h5><i class="fas fa-boxes"></i> Sản phẩm đặt hàng (${items.length})</h5>
                    </div>
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th style="text-align: center;">Số lượng</th>
                                <th style="text-align: right;">Đơn giá</th>
                                <th style="text-align: right;">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            items.forEach(item => {
                const imgSrc = item.product_image ? BASE_URL + '/uploads/products/' + item.product_image : BASE_URL + '/assets/images/no-image.png';
                html += `
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <img src="${imgSrc}" class="product-img" alt="">
                                <strong>${item.product_name}</strong>
                            </div>
                        </td>
                        <td style="text-align: center;">${item.quantity}</td>
                        <td style="text-align: right;">${formatCurrency(item.price)}₫</td>
                        <td style="text-align: right; font-weight: 600; color: #059669;">${formatCurrency(item.quantity * item.price)}₫</td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                    </table>
                </div>
                
                <!-- Order Summary -->
                <div class="order-summary">
                    <div class="summary-row">
                        <span>Tạm tính:</span>
                        <span>${formatCurrency(subtotal)}₫</span>
                    </div>
                    <div class="summary-row">
                        <span>Giảm giá:</span>
                        <span style="color: #ef4444;">-${formatCurrency(order.discount_amount || order.discount || 0)}₫</span>
                    </div>
                    <div class="summary-row">
                        <span>Phí vận chuyển:</span>
                        <span>${formatCurrency(order.shipping_fee || 0)}₫</span>
                    </div>
                    <div class="summary-row total">
                        <span>Tổng cộng:</span>
                        <span>${formatCurrency(order.total)}₫</span>
                    </div>
                </div>
            `;
            
            // Kiểm tra xem có thể cập nhật trạng thái không
            const currentStatus = order.order_status;
            const isPickup = order.shipping_method === 'pickup';
            const allowedStatuses = statusFlow[currentStatus] || [];
            const canUpdate = allowedStatuses.length > 0;
            
            if (canUpdate) {
                // Tạo danh sách trạng thái có thể chuyển đến
                let statusOptions = '';
                
                // Thêm trạng thái hiện tại
                statusOptions += `<option value="${currentStatus}" selected>${statusLabels[currentStatus]}</option>`;
                
                // Thêm các trạng thái được phép
                allowedStatuses.forEach(status => {
                    // Nếu là pickup, không hiển thị shipping
                    if (isPickup && status === 'shipping') return;
                    // Nếu không phải pickup, không hiển thị ready và picked_up
                    if (!isPickup && (status === 'ready' || status === 'picked_up')) return;
                    
                    statusOptions += `<option value="${status}">${statusLabels[status]}</option>`;
                });
                
                html += `
                    <!-- Status Update -->
                    <div class="status-action">
                        <h5><i class="fas fa-edit"></i> Cập nhật trạng thái đơn hàng</h5>
                        <div class="status-select-group">
                            <select id="statusSelect">
                                ${statusOptions}
                            </select>
                            <button onclick="updateOrderStatus(${order.id})">
                                <i class="fas fa-save"></i> Cập nhật
                            </button>
                        </div>
                    </div>
                `;
            } else {
                // Hiển thị thông báo đã hoàn thành/hủy
                const statusColor = currentStatus === 'cancelled' ? '#ef4444' : '#10b981';
                const statusIcon = currentStatus === 'cancelled' ? 'times-circle' : 'check-circle';
                html += `
                    <div style="padding: 20px; background: ${currentStatus === 'cancelled' ? '#fee2e2' : '#d1fae5'}; border-radius: 10px; text-align: center;">
                        <i class="fas fa-${statusIcon}" style="font-size: 32px; color: ${statusColor}; margin-bottom: 10px;"></i>
                        <p style="margin: 0; color: ${statusColor}; font-weight: 600;">
                            Đơn hàng đã ${currentStatus === 'cancelled' ? 'bị hủy' : (isPickup ? 'được lấy' : 'giao thành công')}
                        </p>
                    </div>
                `;
            }
            
            detailBody.innerHTML = html;
            
            // Update header
            document.getElementById('orderDetailHeader').innerHTML = `
                <h4><i class="fas fa-receipt"></i> Đơn hàng #${order.order_code || order.id}</h4>
            `;
        }
        
        // Update order status
        function updateOrderStatus(orderId) {
            const status = document.getElementById('statusSelect').value;
            
            const formData = new FormData();
            formData.append('order_id', orderId);
            formData.append('status', status);
            
            fetch(BASE_URL + '/admin/orders/update-status', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Cập nhật trạng thái thành công!', 'success');
                    
                    // Update the order in allOrders
                    const orderIndex = allOrders.findIndex(o => o.id == orderId);
                    if (orderIndex !== -1) {
                        allOrders[orderIndex].order_status = status;
                    }
                    
                    // Re-render with current filters
                    filterOrders();
                    
                    // Update the card styling
                    const card = document.querySelector(`.order-card[data-order-id="${orderId}"]`);
                    if (card) {
                        card.className = `order-card status-${status} active`;
                        card.querySelector('.badge').className = `badge badge-${status}`;
                        card.querySelector('.badge').textContent = statusLabels[status];
                    }
                } else {
                    showToast(data.message || 'Có lỗi xảy ra!', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Lỗi khi cập nhật trạng thái!', 'error');
            });
        }
        
        // Filter orders
        function filterOrders() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            const status = document.getElementById('statusFilter').value;
            const payment = document.getElementById('paymentFilter').value;
            
            let filtered = allOrders.filter(order => {
                // Search filter
                const matchSearch = !search || 
                    (order.order_code || '').toLowerCase().includes(search) ||
                    (order.customer_name || '').toLowerCase().includes(search) ||
                    (order.customer_email || '').toLowerCase().includes(search);
                
                // Status filter
                const matchStatus = !status || order.order_status === status;
                
                // Payment filter  
                const matchPayment = !payment || order.payment_method === payment;
                
                return matchSearch && matchStatus && matchPayment;
            });
            
            renderOrderList(filtered);
        }
        
        // Clear filters
        function clearFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('paymentFilter').value = '';
            renderOrderList(allOrders);
        }
        
        // Add event listeners
        document.getElementById('searchInput').addEventListener('input', debounce(filterOrders, 300));
        document.getElementById('statusFilter').addEventListener('change', filterOrders);
        document.getElementById('paymentFilter').addEventListener('change', filterOrders);
        
        // Utility functions
        function formatCurrency(num) {
            return new Intl.NumberFormat('vi-VN').format(num || 0);
        }
        
        function formatDate(dateStr) {
            if (!dateStr) return 'N/A';
            const date = new Date(dateStr);
            return date.toLocaleDateString('vi-VN');
        }
        
        function formatDateTime(dateStr) {
            if (!dateStr) return 'N/A';
            const date = new Date(dateStr);
            return date.toLocaleString('vi-VN');
        }
        
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    </script>
</body>
</html>
