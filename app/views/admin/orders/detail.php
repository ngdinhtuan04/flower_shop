<?php
$currentRole = Session::get('user_role');
$user = Session::getUser();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết đơn hàng #<?= htmlspecialchars($order['order_code']) ?> - Admin</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin-orders-detail.css">
</head>
<body>
    <div class="admin-container">
        <?php require_once APP_PATH . '/views/layouts/admin_sidebar.php'; ?>
        
        <div class="main-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <a href="<?= BASE_URL ?>/admin/orders" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                <button onclick="printInvoice()" class="btn-print" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-print"></i> In hóa đơn
                </button>
            </div>
            
            <div class="order-detail" id="invoiceContent">
                <div class="order-header">
                    <h2><i class="fas fa-file-invoice"></i> Chi tiết đơn hàng #<?= htmlspecialchars($order['order_code'] ?? 'N/A') ?></h2>
                    <p style="color: #6b7280;">Ngày đặt: <?= !empty($order['created_at']) ? date('d/m/Y H:i', strtotime($order['created_at'])) : 'N/A' ?></p>
                </div>
                
                <div class="info-grid">
                    <div class="info-section">
                        <h3><i class="fas fa-user"></i> Thông tin khách hàng</h3>
                        <div class="info-row">
                            <div class="info-label">Họ tên:</div>
                            <div class="info-value"><?= htmlspecialchars($order['customer_name'] ?? $order['full_name'] ?? 'N/A') ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Email:</div>
                            <div class="info-value"><?= htmlspecialchars($order['customer_email'] ?? $order['email'] ?? 'N/A') ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Số điện thoại:</div>
                            <div class="info-value"><?= htmlspecialchars($order['customer_phone'] ?? $order['phone'] ?? 'N/A') ?></div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Địa chỉ:</div>
                            <div class="info-value"><?= htmlspecialchars($order['shipping_address'] ?? 'N/A') ?></div>
                        </div>
                    </div>
                    
                    <div class="info-section">
                        <h3><i class="fas fa-info-circle"></i> Thông tin đơn hàng</h3>
                        <div class="info-row">
                            <div class="info-label">Mã đơn:</div>
                            <div class="info-value"><strong><?= htmlspecialchars($order['order_code'] ?? 'N/A') ?></strong></div>
                        </div>
                        <div class="info-row no-print">
                            <div class="info-label">Trạng thái:</div>
                            <div class="info-value">
                                <span class="badge badge-<?= $order['order_status'] ?? 'pending' ?>">
                                    <?php
                                    $statusLabels = [
                                        'pending' => 'Chờ xử lý',
                                        'confirmed' => 'Đã xác nhận',
                                        'processing' => 'Đang xử lý',
                                        'shipping' => 'Đang giao',
                                        'delivered' => 'Đã giao',
                                        'cancelled' => 'Đã hủy'
                                    ];
                                    echo $statusLabels[$order['order_status'] ?? 'pending'] ?? 'N/A';
                                    ?>
                                </span>
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Thanh toán:</div>
                            <div class="info-value"><?= htmlspecialchars($order['payment_method'] ?? 'N/A') ?></div>
                        </div>
                        <div class="info-row no-print">
                            <div class="info-label">Trạng thái TT:</div>
                            <div class="info-value">
                                <?php if (($order['payment_status'] ?? 'unpaid') == 'paid'): ?>
                                    <span style="color: #10b981; font-weight: 600;">✓ Đã thanh toán</span>
                                <?php else: ?>
                                    <span style="color: #f59e0b; font-weight: 600;">⊗ Chưa thanh toán</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if (!empty($order['notes'])): ?>
                        <div class="info-row no-print">
                            <div class="info-label">Ghi chú:</div>
                            <div class="info-value"><?= nl2br(htmlspecialchars($order['notes'])) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <h3><i class="fas fa-shopping-bag"></i> Sản phẩm</h3>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Hình ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($orderItems)): ?>
                            <?php foreach ($orderItems as $item): ?>
                                <tr>
                                    <td style="text-align: center;">
                                        <?php if (!empty($item['product_image'])): ?>
                                            <img src="<?= UPLOAD_URL ?>/products/<?= htmlspecialchars($item['product_image']) ?>" 
                                                 alt="<?= htmlspecialchars($item['product_name'] ?? 'Product') ?>" 
                                                 class="product-img">
                                        <?php else: ?>
                                            <div style="width: 50px; height: 50px; background: #e5e7eb; border-radius: 5px; display: flex; align-items: center; justify-content: center; color: #9ca3af; font-size: 11px;">No img</div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong class="product-name-text"><?= htmlspecialchars($item['product_name'] ?? 'N/A') ?></strong>
                                    </td>
                                    <td style="text-align: center;"><?= number_format($item['price'] ?? 0, 0, ',', '.') ?>đ</td>
                                    <td style="text-align: center;"><?= intval($item['quantity'] ?? 0) ?></td>
                                    <td style="text-align: center;"><strong><?= number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 0), 0, ',', '.') ?>đ</strong></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 20px; color: #6b7280;">Không có sản phẩm</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <!-- Tổng kết đơn hàng -->
                <div class="order-summary">
                    <div class="summary-row">
                        <span class="summary-label"><i class="fas fa-shopping-cart"></i> Tạm tính:</span>
                        <span class="summary-value"><?= number_format($order['subtotal'] ?? 0, 0, ',', '.') ?>đ</span>
                    </div>
                    <?php if (!empty($order['shipping_fee']) && $order['shipping_fee'] > 0): ?>
                    <div class="summary-row">
                        <span class="summary-label"><i class="fas fa-shipping-fast"></i> Phí vận chuyển:</span>
                        <span class="summary-value"><?= number_format($order['shipping_fee'], 0, ',', '.') ?>đ</span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($order['coupon_code'])): ?>
                    <div class="summary-row" style="color: #10b981;">
                        <span class="summary-label"><i class="fas fa-tag"></i> Mã giảm giá (<?= htmlspecialchars($order['coupon_code']) ?>):</span>
                        <span class="summary-value">-<?= number_format($order['coupon_discount'] ?? 0, 0, ',', '.') ?>đ</span>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($order['discount']) && $order['discount'] > 0): ?>
                    <div class="summary-row" style="color: #10b981;">
                        <span class="summary-label"><i class="fas fa-percentage"></i> Giảm giá khuyến mãi:</span>
                        <span class="summary-value">-<?= number_format($order['discount'], 0, ',', '.') ?>đ</span>
                    </div>
                    <?php endif; ?>
                    <div class="summary-row" style="border-top: 2px solid #3498db; padding-top: 15px; margin-top: 10px; font-size: 18px; font-weight: bold;">
                        <span class="summary-label"><i class="fas fa-money-bill-wave"></i> Tổng cộng:</span>
                        <span class="summary-value" style="color: #e74c3c; font-size: 20px;"><?= number_format($order['total'] ?? 0, 0, ',', '.') ?>đ</span>
                    </div>
                </div>
                
                <?php if (($order['order_status'] ?? '') != 'cancelled' && ($order['order_status'] ?? '') != 'delivered'): ?>
                <div class="status-form no-print">
                    <h3><i class="fas fa-edit"></i> Cập nhật trạng thái đơn hàng</h3>
                    <form method="POST" action="<?= BASE_URL ?>/admin/orders/update-status">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?? '' ?>">
                        <div class="form-group">
                            <label>Trạng thái mới:</label>
                            <select name="status" required>
                                <option value="pending" <?= ($order['order_status'] ?? '') == 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                                <option value="confirmed" <?= ($order['order_status'] ?? '') == 'confirmed' ? 'selected' : '' ?>>Đã xác nhận</option>
                                <option value="processing" <?= ($order['order_status'] ?? '') == 'processing' ? 'selected' : '' ?>>Đang xử lý</option>
                                <option value="shipping" <?= ($order['order_status'] ?? '') == 'shipping' ? 'selected' : '' ?>>Đang giao</option>
                                <option value="delivered" <?= ($order['order_status'] ?? '') == 'delivered' ? 'selected' : '' ?>>Đã giao</option>
                                <option value="cancelled" <?= ($order['order_status'] ?? '') == 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-update">
                            <i class="fas fa-save"></i> Cập nhật trạng thái
                        </button>
                    </form>
                </div>
                <?php endif; ?>
                
                <?php if (($order['payment_method'] ?? '') == 'cod' && ($order['payment_status'] ?? '') != 'paid'): ?>
                <div class="status-form no-print" style="margin-top: 20px;">
                    <h3><i class="fas fa-money-bill-wave"></i> Xác nhận thanh toán COD</h3>
                    <form method="POST" action="<?= BASE_URL ?>/admin/orders/update-payment-status">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?? '' ?>">
                        <input type="hidden" name="payment_status" value="paid">
                        <p style="color: #6b7280; margin-bottom: 15px;">
                            <i class="fas fa-info-circle"></i> Xác nhận khi đã nhận tiền từ khách hàng (COD)
                        </p>
                        <button type="submit" class="btn-update" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                            <i class="fas fa-check-circle"></i> Xác nhận đã thanh toán
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Toast notification function
        function showToast(type, title, message) {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            let icon = '';
            switch(type) {
                case 'success':
                    icon = '<i class="fas fa-check-circle"></i>';
                    break;
                case 'error':
                    icon = '<i class="fas fa-exclamation-circle"></i>';
                    break;
                case 'warning':
                    icon = '<i class="fas fa-exclamation-triangle"></i>';
                    break;
                case 'info':
                    icon = '<i class="fas fa-info-circle"></i>';
                    break;
            }
            
            toast.innerHTML = `
                <div class="toast-icon">${icon}</div>
                <div class="toast-content">
                    <div class="toast-title">${title}</div>
                    <div class="toast-message">${message}</div>
                </div>
                <div class="toast-close" onclick="this.parentElement.remove()">×</div>
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => toast.classList.add('show'), 100);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 400);
            }, 4000);
        }
        
        // Check for flash messages and show toast
        <?php if (Session::hasFlash('success')): ?>
            showToast('success', 'Thành công!', <?= json_encode(Session::getFlash('success')) ?>);
        <?php endif; ?>
        
        <?php if (Session::hasFlash('error')): ?>
            showToast('error', 'Lỗi!', <?= json_encode(Session::getFlash('error')) ?>);
        <?php endif; ?>
        
        <?php if (Session::hasFlash('info')): ?>
            showToast('info', 'Thông báo!', <?= json_encode(Session::getFlash('info')) ?>);
        <?php endif; ?>
        
        // In hóa đơn
        function printInvoice() {
            // Tạo window mới cho việc in
            const printWindow = window.open('', '_blank', 'width=800,height=600');
            
            if (!printWindow) {
                alert('Vui lòng bật popup để in hóa đơn!');
                return;
            }
            
            // Lấy nội dung cần in
            const invoiceContent = document.getElementById('invoiceContent');
            if (!invoiceContent) {
                alert('Không tìm thấy nội dung hóa đơn!');
                return;
            }
            
            const contentClone = invoiceContent.cloneNode(true);
            
            // Xóa các nút action không cần in
            const actionButtons = contentClone.querySelectorAll('.action-buttons, .btn-back, .btn-print, button');
            actionButtons.forEach(btn => btn.remove());
            
            // Lấy thông tin đơn hàng
            const orderCode = '<?= htmlspecialchars($order["order_code"] ?? "N/A") ?>';
            const orderDate = '<?= !empty($order["created_at"]) ? date("d/m/Y H:i", strtotime($order["created_at"])) : "N/A" ?>';
            
            // CSS cho trang in
            const printStyles = `
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body { 
                        font-family: 'Arial', sans-serif; 
                        padding: 20px;
                        background: white;
                    }
                    .company-header { 
                        text-align: center; 
                        margin-bottom: 15px; 
                        border-bottom: 2px solid #3498db; 
                        padding-bottom: 12px; 
                    }
                    .company-header h1 { 
                        margin: 0 0 8px 0; 
                        color: #2c3e50; 
                        font-size: 22px; 
                        font-weight: bold;
                    }
                    .company-header p { 
                        margin: 3px 0; 
                        color: #7f8c8d; 
                        font-size: 11px;
                    }
                    .invoice-title { 
                        text-align: center; 
                        font-size: 18px; 
                        font-weight: bold; 
                        color: #e74c3c; 
                        margin: 12px 0 15px 0;
                        letter-spacing: 1px;
                    }
                    .order-detail { 
                        max-width: 100%; 
                    }
                    .order-header {
                        margin-bottom: 12px;
                        padding-bottom: 10px;
                        border-bottom: 1px solid #ecf0f1;
                    }
                    .order-header h2 {
                        font-size: 16px;
                        color: #2c3e50;
                        margin-bottom: 4px;
                    }
                    .order-header p {
                        color: #7f8c8d;
                        font-size: 11px;
                    }
                    .info-grid { 
                        display: grid; 
                        grid-template-columns: 1fr 1fr; 
                        gap: 15px; 
                        margin-bottom: 15px;
                    }
                    .info-section { 
                        background: #f9fafb; 
                        padding: 12px; 
                        border-radius: 5px;
                        border: 1px solid #e5e7eb;
                    }
                    .info-section h3 { 
                        margin: 0 0 10px 0; 
                        color: #2c3e50; 
                        border-bottom: 1px solid #3498db; 
                        padding-bottom: 8px;
                        font-size: 14px;
                    }
                    .info-row { 
                        display: flex; 
                        padding: 6px 0; 
                        border-bottom: 1px solid #e5e7eb; 
                        font-size: 12px;
                    }
                    .info-row:last-child {
                        border-bottom: none;
                    }
                    .info-label { 
                        font-weight: bold; 
                        width: 110px; 
                        color: #555;
                        font-size: 12px;
                    }
                    .info-value { 
                        flex: 1; 
                        color: #2c3e50; 
                        font-size: 12px;
                    }
                    .products-section { 
                        margin-top: 20px; 
                    }
                    .products-section h3 {
                        font-size: 16px;
                        color: #2c3e50;
                        margin-bottom: 10px;
                        padding-bottom: 8px;
                        border-bottom: 1px solid #3498db;
                    }
                    .products-table { 
                        width: 100%; 
                        border-collapse: collapse; 
                        margin-top: 10px;
                        border: 1px solid #ccc;
                    }
                    .products-table th { 
                        background: #3498db; 
                        color: white; 
                        padding: 8px 6px; 
                        text-align: left;
                        font-size: 13px;
                        font-weight: 600;
                        border-right: 1px solid #2980b9;
                    }
                    .products-table th:last-child {
                        border-right: none;
                    }
                    .products-table th:nth-child(1) {
                        width: 10%;
                        text-align: center;
                    }
                    .products-table th:nth-child(2) {
                        width: 40%;
                    }
                    .products-table th:nth-child(3),
                    .products-table th:nth-child(4),
                    .products-table th:nth-child(5) {
                        width: 16.66%;
                        text-align: center;
                    }
                    .products-table td { 
                        padding: 8px 6px; 
                        border-bottom: 1px solid #ccc;
                        font-size: 13px;
                        vertical-align: middle;
                    }
                    .products-table td:nth-child(1) {
                        text-align: center;
                    }
                    .products-table td:nth-child(3),
                    .products-table td:nth-child(4),
                    .products-table td:nth-child(5) {
                        text-align: center;
                    }
                    .products-table tbody tr:last-child td {
                        border-bottom: 2px solid #3498db;
                    }
                    .products-table tbody tr:hover {
                        background: #f9fafb;
                    }
                    .product-img {
                        width: 40px;
                        height: 40px;
                        object-fit: cover;
                        border-radius: 4px;
                        border: 1px solid #ccc;
                    }
                    .product-name-text {
                        font-weight: 600;
                        color: #2c3e50;
                        text-align: left;
                    }
                    .total-row td {
                        background: #ecf0f1 !important;
                        font-weight: bold !important;
                        font-size: 16px !important;
                        padding: 15px 10px !important;
                        border-top: 3px solid #3498db !important;
                        border-bottom: none !important;
                    }
                    .total-row td:last-child {
                        color: #e74c3c;
                        font-size: 18px !important;
                    }
                    .order-summary { 
                        margin-top: 15px; 
                        padding: 15px 20px;
                        background: #f8f9fa;
                        border: 1px solid #3498db;
                        border-radius: 5px;
                    }
                    .summary-row {
                        display: grid;
                        grid-template-columns: 1fr auto;
                        gap: 30px;
                        padding: 8px 0;
                        border-bottom: 1px solid #e5e7eb;
                        font-size: 13px;
                        align-items: center;
                    }
                    .summary-row:last-child {
                        border-bottom: none;
                        padding-top: 12px;
                        margin-top: 8px;
                        border-top: 2px solid #3498db;
                        font-size: 15px;
                        font-weight: bold;
                    }
                    .summary-row:last-child .summary-value {
                        color: #e74c3c;
                        font-size: 17px;
                    }
                    .summary-label {
                        font-weight: 600;
                        color: #2c3e50;
                        display: flex;
                        align-items: center;
                    }
                    .summary-label i {
                        margin-right: 8px;
                        width: 18px;
                        text-align: center;
                        font-size: 13px;
                    }
                    .summary-value {
                        text-align: right;
                        font-weight: bold;
                        color: #555;
                        font-size: 13px;
                    }
                    .badge { 
                        display: inline-block; 
                        padding: 5px 12px; 
                        border-radius: 12px; 
                        font-size: 12px; 
                        font-weight: 600; 
                    }
                    .badge-pending { background: #fff3cd; color: #856404; }
                    .badge-confirmed { background: #d1ecf1; color: #0c5460; }
                    .badge-processing { background: #cfe2ff; color: #084298; }
                    .badge-shipping { background: #e7f3ff; color: #004085; }
                    .badge-delivered { background: #d4edda; color: #155724; }
                    .badge-cancelled { background: #f8d7da; color: #721c24; }
                    .footer-note {
                        margin-top: 20px;
                        text-align: center;
                        color: #7f8c8d;
                        border-top: 1px solid #ecf0f1;
                        padding-top: 12px;
                        font-size: 11px;
                    }
                    .footer-note strong {
                        color: #2c3e50;
                        font-size: 12px;
                    }
                    @media print {
                        body { padding: 0; margin: 0; }
                        .no-print { display: none !important; }
                        @page { size: A4; margin: 0.8cm; }
                    }
                </style>
            `;
            
            // Tạo HTML cho trang in
            const htmlContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <title>Hóa đơn - ${orderCode}</title>
                    ${printStyles}
                </head>
                <body>
                    <div class="company-header">
                        <h1>🌸 FLOWER SHOP 🌸</h1>
                        <p><strong>Địa chỉ:</strong> 123 Đường ABC, Quận 1, TP. Hồ Chí Minh</p>
                        <p><strong>Hotline:</strong> 1900 1234 | <strong>Email:</strong> info@flowershop.com</p>
                        <p><strong>Website:</strong> www.flowershop.com</p>
                    </div>
                    
                    <div class="invoice-title">HÓA ĐƠN BÁN HÀNG</div>
                    
                    ${contentClone.innerHTML}
                    
                    <div class="footer-note">
                        <p><strong>❤️ Cảm ơn quý khách đã mua hàng! ❤️</strong></p>
                        <p style="margin-top: 10px;">Vui lòng liên hệ hotline 1900 1234 nếu cần hỗ trợ</p>
                        <p style="margin-top: 15px; font-size: 11px; color: #95a5a6;">
                            In lúc: ${new Date().toLocaleString('vi-VN', {day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'})}
                        </p>
                    </div>
                </body>
                </html>
            `;
            
            // Ghi nội dung vào window mới
            printWindow.document.write(htmlContent);
            printWindow.document.close();
            
            // Đợi load xong rồi in
            printWindow.onload = function() {
                setTimeout(() => {
                    printWindow.focus();
                    printWindow.print();
                    // Không tự đóng để user có thể xem lại
                    // printWindow.close();
                }, 300);
            };
        }
    </script>
</body>
</html>
