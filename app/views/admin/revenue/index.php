<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo doanh thu - Admin</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin-revenue.css">
</head>
<body>
    <?php include APP_PATH . '/views/layouts/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <h2><i class="fas fa-chart-line"></i> Báo cáo Doanh thu</h2>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <strong><?= htmlspecialchars($user['full_name']) ?></strong>
            </div>
        </div>

        <!-- Enhanced Filters -->
        <div class="filter-section">
            <form method="GET" action="<?= BASE_URL ?>/admin/revenue" class="filter-form">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label><i class="fas fa-calendar"></i> Khoảng thời gian</label>
                        <select name="period" id="periodSelect" onchange="updateQuickDates()">
                            <option value="day" <?= $filters['period'] == 'day' ? 'selected' : '' ?>>Theo ngày</option>
                            <option value="week" <?= $filters['period'] == 'week' ? 'selected' : '' ?>>Theo tuần</option>
                            <option value="month" <?= $filters['period'] == 'month' ? 'selected' : '' ?>>Theo tháng</option>
                            <option value="year" <?= $filters['period'] == 'year' ? 'selected' : '' ?>>Theo năm</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label><i class="fas fa-calendar-day"></i> Từ ngày</label>
                        <input type="date" name="from_date" value="<?= htmlspecialchars($filters['from_date']) ?>" required>
                    </div>
                    
                    <div class="filter-group">
                        <label><i class="fas fa-calendar-day"></i> Đến ngày</label>
                        <input type="date" name="to_date" value="<?= htmlspecialchars($filters['to_date']) ?>" required>
                    </div>
                    
                    <div class="filter-group">
                        <label><i class="fas fa-filter"></i> Trạng thái</label>
                        <select name="status">
                            <option value="paid" <?= $filters['status'] == 'paid' ? 'selected' : '' ?>>Đã thanh toán</option>
                            <option value="pending" <?= $filters['status'] == 'pending' ? 'selected' : '' ?>>Chờ thanh toán</option>
                            <option value="all" <?= $filters['status'] == 'all' ? 'selected' : '' ?>>Tất cả</option>
                        </select>
                    </div>
                </div>
                
                <div class="filter-actions">
                    <div class="quick-filters">
                        <button type="button" class="quick-btn" onclick="setQuickDate('today')">Hôm nay</button>
                        <button type="button" class="quick-btn" onclick="setQuickDate('week')">7 ngày</button>
                        <button type="button" class="quick-btn" onclick="setQuickDate('month')">30 ngày</button>
                        <button type="button" class="quick-btn" onclick="setQuickDate('quarter')">Quý này</button>
                    </div>
                    <div class="action-btns">
                        <button type="submit" class="btn-filter">
                            <i class="fas fa-search"></i> Lọc dữ liệu
                        </button>
                        <a href="<?= BASE_URL ?>/admin/revenue/export?<?= http_build_query($filters) ?>" class="btn-export">
                            <i class="fas fa-download"></i> Export CSV
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Quick Links -->
        <div style="display: flex; gap: 10px; margin-bottom: 20px;">
            <a href="<?= BASE_URL ?>/admin/revenue" class="btn-filter" style="text-decoration: none;">
                <i class="fas fa-chart-line"></i> Doanh thu & Lợi nhuận
            </a>
            <a href="<?= BASE_URL ?>/admin/revenue/customers" class="btn-export" style="text-decoration: none;">
                <i class="fas fa-users"></i> Thống kê khách hàng
            </a>
            <a href="<?= BASE_URL ?>/admin/revenue/inventory" class="btn-export" style="text-decoration: none;">
                <i class="fas fa-warehouse"></i> Báo cáo tồn kho
            </a>
        </div>

        <!-- KPI Cards with Comparison -->
        <div class="kpi-grid" style="grid-template-columns: repeat(6, 1fr);">
            <div class="kpi-card">
                <div class="kpi-header">
                    <div class="kpi-icon revenue"><i class="fas fa-dollar-sign"></i></div>
                    <span class="kpi-change <?= ($stats['revenue_change'] ?? 0) >= 0 ? 'positive' : 'negative' ?>">
                        <i class="fas fa-<?= ($stats['revenue_change'] ?? 0) >= 0 ? 'arrow-up' : 'arrow-down' ?>"></i>
                        <?= abs(round($stats['revenue_change'] ?? 0, 1)) ?>%
                    </span>
                </div>
                <div class="kpi-value"><?= number_format($stats['total_revenue'], 0, ',', '.') ?>₫</div>
                <div class="kpi-label">Tổng doanh thu</div>
            </div>
            
            <!-- Chi phí nhập -->
            <div class="kpi-card">
                <div class="kpi-header">
                    <div class="kpi-icon" style="background: linear-gradient(135deg, #ef4444, #dc2626);"><i class="fas fa-truck-loading"></i></div>
                </div>
                <div class="kpi-value" style="color: #dc2626;"><?= number_format($stats['total_cost'] ?? 0, 0, ',', '.') ?>₫</div>
                <div class="kpi-label">Chi phí nhập</div>
            </div>
            
            <!-- Lợi nhuận -->
            <div class="kpi-card">
                <div class="kpi-header">
                    <div class="kpi-icon" style="background: linear-gradient(135deg, #10b981, #059669);"><i class="fas fa-coins"></i></div>
                    <span class="kpi-change <?= ($stats['profit_change'] ?? 0) >= 0 ? 'positive' : 'negative' ?>">
                        <i class="fas fa-<?= ($stats['profit_change'] ?? 0) >= 0 ? 'arrow-up' : 'arrow-down' ?>"></i>
                        <?= abs(round($stats['profit_change'] ?? 0, 1)) ?>%
                    </span>
                </div>
                <div class="kpi-value" style="color: #059669;"><?= number_format($stats['total_profit'] ?? 0, 0, ',', '.') ?>₫</div>
                <div class="kpi-label">Lợi nhuận</div>
            </div>
            
            <!-- % Margin -->
            <div class="kpi-card">
                <div class="kpi-header">
                    <div class="kpi-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);"><i class="fas fa-percentage"></i></div>
                </div>
                <div class="kpi-value" style="color: #7c3aed;"><?= $stats['profit_margin'] ?? 0 ?>%</div>
                <div class="kpi-label">Tỷ suất LN</div>
            </div>
            
            <div class="kpi-card">
                <div class="kpi-header">
                    <div class="kpi-icon orders"><i class="fas fa-shopping-cart"></i></div>
                    <span class="kpi-change <?= ($stats['orders_change'] ?? 0) >= 0 ? 'positive' : 'negative' ?>">
                        <i class="fas fa-<?= ($stats['orders_change'] ?? 0) >= 0 ? 'arrow-up' : 'arrow-down' ?>"></i>
                        <?= abs(round($stats['orders_change'] ?? 0, 1)) ?>%
                    </span>
                </div>
                <div class="kpi-value"><?= number_format($stats['total_orders']) ?></div>
                <div class="kpi-label">Tổng đơn hàng</div>
            </div>
            
            <div class="kpi-card">
                <div class="kpi-header">
                    <div class="kpi-icon products"><i class="fas fa-box"></i></div>
                    <span class="kpi-change <?= ($stats['products_change'] ?? 0) >= 0 ? 'positive' : 'negative' ?>">
                        <i class="fas fa-<?= ($stats['products_change'] ?? 0) >= 0 ? 'arrow-up' : 'arrow-down' ?>"></i>
                        <?= abs(round($stats['products_change'] ?? 0, 1)) ?>%
                    </span>
                </div>
                <div class="kpi-value"><?= number_format($stats['total_products_sold']) ?></div>
                <div class="kpi-label">SP đã bán</div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="charts-row">
            <!-- Main Revenue Chart -->
            <div class="chart-card main-chart">
                <div class="card-header">
                    <h3><i class="fas fa-chart-line"></i> Biểu đồ doanh thu vs Lợi nhuận</h3>
                </div>
                <div class="chart-wrapper">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Category Revenue Pie Chart -->
            <div class="chart-card side-chart">
                <div class="card-header">
                    <h3><i class="fas fa-chart-pie"></i> Doanh thu theo danh mục</h3>
                </div>
                <div class="chart-wrapper">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Payment & Cancelled Stats -->
        <div class="stats-row">
            <!-- Payment Methods -->
            <div class="stats-card">
                <div class="card-header">
                    <h3><i class="fas fa-credit-card"></i> Phương thức thanh toán</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($paymentStats)): ?>
                        <div class="payment-stats">
                            <?php foreach ($paymentStats as $payment): ?>
                                <div class="payment-item">
                                    <div class="payment-info">
                                        <span class="payment-icon">
                                            <?php if ($payment['payment_method'] == 'cod'): ?>
                                                <i class="fas fa-money-bill"></i>
                                            <?php elseif ($payment['payment_method'] == 'vnpay'): ?>
                                                <i class="fas fa-university"></i>
                                            <?php else: ?>
                                                <i class="fas fa-wallet"></i>
                                            <?php endif; ?>
                                        </span>
                                        <span class="payment-name">
                                            <?php 
                                            $methodNames = [
                                                'cod' => 'Tiền mặt (COD)',
                                                'vnpay' => 'VNPay',
                                                'bank_transfer' => 'Chuyển khoản'
                                            ];
                                            echo $methodNames[$payment['payment_method']] ?? $payment['payment_method'];
                                            ?>
                                        </span>
                                    </div>
                                    <div class="payment-stats-values">
                                        <span class="payment-orders"><?= $payment['order_count'] ?> đơn</span>
                                        <span class="payment-revenue"><?= number_format($payment['total_revenue'], 0, ',', '.') ?>₫</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-data">Không có dữ liệu</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Cancelled Orders -->
            <div class="stats-card cancelled">
                <div class="card-header">
                    <h3><i class="fas fa-times-circle"></i> Đơn hàng đã hủy</h3>
                </div>
                <div class="card-body">
                    <div class="cancelled-stats">
                        <div class="cancelled-item">
                            <span class="cancelled-label">Số đơn hủy</span>
                            <span class="cancelled-value"><?= $cancelledStats['cancelled_orders'] ?></span>
                        </div>
                        <div class="cancelled-item">
                            <span class="cancelled-label">Giá trị mất</span>
                            <span class="cancelled-value negative"><?= number_format($cancelledStats['cancelled_value'], 0, ',', '.') ?>₫</span>
                        </div>
                        <div class="cancelled-item">
                            <span class="cancelled-label">Tỷ lệ hủy</span>
                            <span class="cancelled-value <?= $cancelledStats['cancelled_rate'] > 10 ? 'danger' : '' ?>"><?= $cancelledStats['cancelled_rate'] ?>%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profit Analysis Section -->
        <div class="stats-row" style="margin-bottom: 25px;">
            <!-- Lợi nhuận theo sản phẩm -->
            <div class="stats-card" style="flex: 2;">
                <div class="card-header">
                    <h3><i class="fas fa-coins"></i> Top sản phẩm theo lợi nhuận</h3>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <?php if (!empty($profitByProduct)): ?>
                        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                            <thead>
                                <tr style="background: #f8fafc; border-bottom: 2px solid #e5e7eb;">
                                    <th style="padding: 10px; text-align: left;">Sản phẩm</th>
                                    <th style="padding: 10px; text-align: right;">SL bán</th>
                                    <th style="padding: 10px; text-align: right;">Doanh thu</th>
                                    <th style="padding: 10px; text-align: right;">Chi phí</th>
                                    <th style="padding: 10px; text-align: right;">Lợi nhuận</th>
                                    <th style="padding: 10px; text-align: center;">Margin</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($profitByProduct as $p): ?>
                                <tr style="border-bottom: 1px solid #f3f4f6;">
                                    <td style="padding: 10px;">
                                        <strong><?= htmlspecialchars($p['product_name']) ?></strong>
                                        <small style="color: #9ca3af; display: block;"><?= $p['category_name'] ?? '' ?></small>
                                    </td>
                                    <td style="padding: 10px; text-align: right;"><?= number_format($p['total_sold']) ?></td>
                                    <td style="padding: 10px; text-align: right; color: #3b82f6; font-weight: 600;"><?= number_format($p['total_revenue'], 0, ',', '.') ?>₫</td>
                                    <td style="padding: 10px; text-align: right; color: #ef4444;"><?= number_format($p['total_cost'], 0, ',', '.') ?>₫</td>
                                    <td style="padding: 10px; text-align: right; color: <?= $p['profit'] >= 0 ? '#10b981' : '#ef4444' ?>; font-weight: 700;">
                                        <?= $p['profit'] >= 0 ? '+' : '' ?><?= number_format($p['profit'], 0, ',', '.') ?>₫
                                    </td>
                                    <td style="padding: 10px; text-align: center;">
                                        <span style="padding: 3px 8px; border-radius: 20px; font-size: 11px; font-weight: 600;
                                            background: <?= $p['margin'] >= 30 ? '#d1fae5' : ($p['margin'] >= 15 ? '#fef3c7' : '#fee2e2') ?>;
                                            color: <?= $p['margin'] >= 30 ? '#047857' : ($p['margin'] >= 15 ? '#b45309' : '#dc2626') ?>;">
                                            <?= $p['margin'] ?>%
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-data">Chưa có dữ liệu lợi nhuận</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Lợi nhuận theo NCC -->
            <div class="stats-card">
                <div class="card-header">
                    <h3><i class="fas fa-truck"></i> Lợi nhuận theo NCC</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($profitBySupplier)): ?>
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <?php foreach ($profitBySupplier as $s): ?>
                            <div style="background: #f8fafc; padding: 12px; border-radius: 8px; border-left: 4px solid #667eea;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                    <strong style="color: #1f2937;"><?= htmlspecialchars($s['supplier_name']) ?></strong>
                                    <span style="background: <?= $s['margin'] >= 30 ? '#d1fae5' : '#fef3c7' ?>; 
                                        color: <?= $s['margin'] >= 30 ? '#047857' : '#b45309' ?>; 
                                        padding: 3px 8px; border-radius: 20px; font-size: 11px; font-weight: 600;">
                                        <?= $s['margin'] ?>%
                                    </span>
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; font-size: 12px;">
                                    <div>
                                        <div style="color: #9ca3af;">Doanh thu</div>
                                        <div style="color: #3b82f6; font-weight: 600;"><?= number_format($s['total_revenue'], 0, ',', '.') ?>₫</div>
                                    </div>
                                    <div>
                                        <div style="color: #9ca3af;">Chi phí</div>
                                        <div style="color: #ef4444; font-weight: 600;"><?= number_format($s['total_cost'], 0, ',', '.') ?>₫</div>
                                    </div>
                                    <div>
                                        <div style="color: #9ca3af;">Lợi nhuận</div>
                                        <div style="color: #10b981; font-weight: 700;"><?= number_format($s['profit'], 0, ',', '.') ?>₫</div>
                                    </div>
                                </div>
                                <div style="margin-top: 8px; color: #6b7280; font-size: 11px;">
                                    <?= $s['total_orders'] ?> đơn | <?= number_format($s['total_sold']) ?> SP
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-data">Chưa có dữ liệu NCC</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Category Revenue List -->
        <?php if (!empty($categoryRevenue)): ?>
        <div class="category-section">
            <div class="card-header">
                <h3><i class="fas fa-layer-group"></i> Doanh thu chi tiết theo danh mục</h3>
            </div>
            <div class="category-grid">
                <?php foreach ($categoryRevenue as $index => $cat): ?>
                    <div class="category-item">
                        <div class="category-rank">#<?= $index + 1 ?></div>
                        <div class="category-info">
                            <span class="category-name"><?= htmlspecialchars($cat['category_name']) ?></span>
                            <div class="category-progress">
                                <div class="progress-bar" style="width: <?= $cat['percentage'] ?>%"></div>
                            </div>
                        </div>
                        <div class="category-stats">
                            <span class="stat-value"><?= number_format($cat['total_revenue'], 0, ',', '.') ?>₫</span>
                            <span class="stat-percent"><?= $cat['percentage'] ?>%</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Top Lists Row -->
        <div class="top-lists-row">
            <!-- Top Products -->
            <div class="top-list-card">
                <div class="card-header">
                    <h3><i class="fas fa-trophy"></i> Top 10 sản phẩm bán chạy</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($topProducts)): ?>
                        <div class="top-items">
                            <?php foreach ($topProducts as $index => $product): ?>
                                <div class="top-item">
                                    <span class="item-rank rank-<?= $index + 1 ?>"><?= $index + 1 ?></span>
                                    <img src="<?= BASE_URL ?>/uploads/products/<?= htmlspecialchars($product['product_image']) ?>" 
                                         alt="<?= htmlspecialchars($product['product_name']) ?>" 
                                         class="item-image"
                                         onerror="this.src='<?= BASE_URL ?>/assets/images/no-image.png'">
                                    <div class="item-info">
                                        <span class="item-name"><?= htmlspecialchars($product['product_name']) ?></span>
                                        <span class="item-stat">Đã bán: <?= number_format($product['total_sold']) ?></span>
                                    </div>
                                    <span class="item-value"><?= number_format($product['total_revenue'], 0, ',', '.') ?>₫</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-data"><i class="fas fa-info-circle"></i> Chưa có dữ liệu</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Top Customers -->
            <div class="top-list-card">
                <div class="card-header">
                    <h3><i class="fas fa-users"></i> Top 10 khách hàng thân thiết</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($topCustomers)): ?>
                        <div class="top-items">
                            <?php foreach ($topCustomers as $index => $customer): ?>
                                <div class="top-item">
                                    <span class="item-rank rank-<?= $index + 1 ?>"><?= $index + 1 ?></span>
                                    <div class="item-avatar">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                    <div class="item-info">
                                        <span class="item-name"><?= htmlspecialchars($customer['customer_name']) ?></span>
                                        <span class="item-stat"><?= $customer['order_count'] ?> đơn hàng</span>
                                    </div>
                                    <span class="item-value"><?= number_format($customer['total_spent'], 0, ',', '.') ?>₫</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-data"><i class="fas fa-info-circle"></i> Chưa có dữ liệu</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Quick Date Filters
        function setQuickDate(type) {
            const today = new Date();
            let fromDate, toDate;
            
            switch(type) {
                case 'today':
                    fromDate = toDate = today.toISOString().split('T')[0];
                    break;
                case 'week':
                    fromDate = new Date(today.setDate(today.getDate() - 7)).toISOString().split('T')[0];
                    toDate = new Date().toISOString().split('T')[0];
                    break;
                case 'month':
                    fromDate = new Date(today.setDate(today.getDate() - 30)).toISOString().split('T')[0];
                    toDate = new Date().toISOString().split('T')[0];
                    break;
                case 'quarter':
                    const quarter = Math.floor(new Date().getMonth() / 3);
                    fromDate = new Date(new Date().getFullYear(), quarter * 3, 1).toISOString().split('T')[0];
                    toDate = new Date().toISOString().split('T')[0];
                    break;
            }
            
            document.querySelector('input[name="from_date"]').value = fromDate;
            document.querySelector('input[name="to_date"]').value = toDate;
        }

        // Revenue Chart
        const revenueData = <?= json_encode($revenueData) ?>;
        const labels = revenueData.map(item => item.date);
        const revenues = revenueData.map(item => parseFloat(item.revenue));
        const orders = revenueData.map(item => parseInt(item.orders));

        const ctx = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Doanh thu (VNĐ)',
                        data: revenues,
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Số đơn hàng',
                        data: orders,
                        borderColor: '#f5576c',
                        backgroundColor: 'rgba(245, 87, 108, 0.1)',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (context.datasetIndex === 0) {
                                    return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(context.parsed.y) + '₫';
                                }
                                return 'Đơn hàng: ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        position: 'left',
                        title: { display: true, text: 'Doanh thu (VNĐ)' },
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M';
                                if (value >= 1000) return (value / 1000).toFixed(0) + 'K';
                                return value;
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        position: 'right',
                        title: { display: true, text: 'Số đơn hàng' },
                        grid: { drawOnChartArea: false }
                    }
                }
            }
        });

        // Category Pie Chart
        const categoryData = <?= json_encode($categoryRevenue ?? []) ?>;
        if (categoryData.length > 0) {
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            const colors = ['#667eea', '#f5576c', '#4facfe', '#43e97b', '#fa709a', '#fee140', '#a8edea', '#fed6e3'];
            
            new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: categoryData.map(c => c.category_name),
                    datasets: [{
                        data: categoryData.map(c => c.total_revenue),
                        backgroundColor: colors.slice(0, categoryData.length),
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { padding: 15, font: { size: 12 } }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = new Intl.NumberFormat('vi-VN').format(context.parsed);
                                    const percentage = categoryData[context.dataIndex].percentage;
                                    return context.label + ': ' + value + '₫ (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }
    </script>
    <?php include APP_PATH . '/views/layouts/toast_notification.php'; ?>
</body>
</html>
