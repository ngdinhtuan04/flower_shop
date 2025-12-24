<?php
$currentRole = Session::get('user_role');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống kê khách hàng - Admin</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .filter-form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .filter-group label {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
        }
        .filter-group input, .filter-group select {
            padding: 10px 15px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
        }
        .btn-filter {
            padding: 10px 20px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-export {
            padding: 10px 20px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }
        .kpi-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .kpi-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            margin-bottom: 15px;
        }
        .kpi-icon.users { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
        .kpi-icon.buyers { background: linear-gradient(135deg, #10b981, #059669); }
        .kpi-icon.money { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .kpi-icon.avg { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .kpi-value {
            font-size: 28px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 5px;
        }
        .kpi-label {
            font-size: 13px;
            color: #6b7280;
        }
        .content-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .card-header {
            padding: 15px 20px;
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            border-bottom: 1px solid #e5e7eb;
        }
        .card-header h3 {
            margin: 0;
            font-size: 16px;
            color: #374151;
        }
        .card-body {
            padding: 20px;
            max-height: 600px;
            overflow-y: auto;
        }
        .customer-table {
            width: 100%;
            border-collapse: collapse;
        }
        .customer-table th, .customer-table td {
            padding: 12px 10px;
            text-align: left;
            border-bottom: 1px solid #f3f4f6;
        }
        .customer-table th {
            background: #f8fafc;
            font-weight: 600;
            color: #374151;
            font-size: 12px;
            text-transform: uppercase;
        }
        .customer-table tr:hover {
            background: #f8fafc;
        }
        .customer-name {
            font-weight: 600;
            color: #1f2937;
        }
        .customer-email {
            font-size: 12px;
            color: #6b7280;
        }
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-success { background: #d1fae5; color: #047857; }
        .badge-warning { background: #fef3c7; color: #b45309; }
        .badge-danger { background: #fee2e2; color: #dc2626; }
        .badge-info { background: #dbeafe; color: #1d4ed8; }
        .top-customer {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            border-bottom: 1px solid #f3f4f6;
        }
        .top-customer:last-child { border-bottom: none; }
        .customer-rank {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
        }
        .rank-1 { background: #fef3c7; color: #d97706; }
        .rank-2 { background: #e5e7eb; color: #6b7280; }
        .rank-3 { background: #fed7aa; color: #c2410c; }
        .customer-info { flex: 1; }
        .customer-stats {
            text-align: right;
        }
        .customer-spent {
            font-weight: 700;
            color: #059669;
            font-size: 16px;
        }
        .customer-orders {
            font-size: 12px;
            color: #6b7280;
        }
        .nav-links {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .nav-link {
            padding: 10px 20px;
            background: #f3f4f6;
            color: #374151;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
        }
        .nav-link:hover { background: #e5e7eb; }
        .nav-link.active {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
        }
    </style>
</head>
<body>
    <?php include APP_PATH . '/views/layouts/admin_sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <h2><i class="fas fa-users"></i> Thống kê Khách hàng</h2>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <strong><?= htmlspecialchars($user['full_name']) ?></strong>
            </div>
        </div>

        <!-- Navigation Links -->
        <div class="nav-links">
            <a href="<?= BASE_URL ?>/admin/revenue" class="nav-link">
                <i class="fas fa-chart-line"></i> Doanh thu & Lợi nhuận
            </a>
            <a href="<?= BASE_URL ?>/admin/revenue/customers" class="nav-link active">
                <i class="fas fa-users"></i> Thống kê khách hàng
            </a>
            <a href="<?= BASE_URL ?>/admin/revenue/inventory" class="nav-link">
                <i class="fas fa-warehouse"></i> Báo cáo tồn kho
            </a>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="<?= BASE_URL ?>/admin/revenue/customers" class="filter-form">
                <div class="filter-group">
                    <label><i class="fas fa-calendar"></i> Từ ngày</label>
                    <input type="date" name="from_date" value="<?= htmlspecialchars($filters['from_date']) ?>">
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-calendar"></i> Đến ngày</label>
                    <input type="date" name="to_date" value="<?= htmlspecialchars($filters['to_date']) ?>">
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-search"></i> Tìm kiếm</label>
                    <input type="text" name="search" placeholder="Tên, email, SĐT..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                </div>
                <div class="filter-group">
                    <label><i class="fas fa-sort"></i> Sắp xếp</label>
                    <select name="sort">
                        <option value="total_spent" <?= ($filters['sort'] ?? '') == 'total_spent' ? 'selected' : '' ?>>Tổng chi tiêu</option>
                        <option value="total_orders" <?= ($filters['sort'] ?? '') == 'total_orders' ? 'selected' : '' ?>>Số đơn hàng</option>
                        <option value="last_order_date" <?= ($filters['sort'] ?? '') == 'last_order_date' ? 'selected' : '' ?>>Lần mua cuối</option>
                        <option value="registered_at" <?= ($filters['sort'] ?? '') == 'registered_at' ? 'selected' : '' ?>>Ngày đăng ký</option>
                    </select>
                </div>
                <button type="submit" class="btn-filter">
                    <i class="fas fa-search"></i> Lọc
                </button>
                <a href="<?= BASE_URL ?>/admin/revenue/export-customers?<?= http_build_query($filters) ?>" class="btn-export">
                    <i class="fas fa-download"></i> Export CSV
                </a>
            </form>
        </div>

        <!-- KPI Cards -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-icon users"><i class="fas fa-users"></i></div>
                <div class="kpi-value"><?= number_format($customerStats['total_customers']) ?></div>
                <div class="kpi-label">Tổng khách hàng</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon buyers"><i class="fas fa-shopping-bag"></i></div>
                <div class="kpi-value"><?= number_format($customerStats['buying_customers']) ?></div>
                <div class="kpi-label">Khách đã mua hàng</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon money"><i class="fas fa-money-bill-wave"></i></div>
                <div class="kpi-value"><?= number_format($customerStats['total_spent'], 0, ',', '.') ?>₫</div>
                <div class="kpi-label">Tổng chi tiêu</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon avg"><i class="fas fa-chart-bar"></i></div>
                <div class="kpi-value"><?= number_format($customerStats['avg_spent_per_customer'], 0, ',', '.') ?>₫</div>
                <div class="kpi-label">Trung bình/khách</div>
            </div>
        </div>

        <!-- Content Row -->
        <div class="content-row">
            <!-- Customer List -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> Danh sách khách hàng (<?= count($customers) ?>)</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($customers)): ?>
                    <table class="customer-table">
                        <thead>
                            <tr>
                                <th>Khách hàng</th>
                                <th>SĐT</th>
                                <th style="text-align: center;">Đơn hàng</th>
                                <th style="text-align: right;">Tổng chi tiêu</th>
                                <th style="text-align: right;">TB/đơn</th>
                                <th style="text-align: center;">Lần mua cuối</th>
                                <th style="text-align: center;">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $c): ?>
                            <tr>
                                <td>
                                    <div class="customer-name"><?= htmlspecialchars($c['full_name']) ?></div>
                                    <div class="customer-email"><?= htmlspecialchars($c['email']) ?></div>
                                </td>
                                <td><?= htmlspecialchars($c['phone'] ?? 'N/A') ?></td>
                                <td style="text-align: center; font-weight: 600;"><?= $c['total_orders'] ?></td>
                                <td style="text-align: right; color: #059669; font-weight: 700;">
                                    <?= number_format($c['total_spent'], 0, ',', '.') ?>₫
                                </td>
                                <td style="text-align: right; color: #6b7280;">
                                    <?= number_format($c['avg_order_value'], 0, ',', '.') ?>₫
                                </td>
                                <td style="text-align: center; font-size: 12px;">
                                    <?php if ($c['last_order_date']): ?>
                                        <?= date('d/m/Y', strtotime($c['last_order_date'])) ?>
                                    <?php else: ?>
                                        <span style="color: #9ca3af;">Chưa mua</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($c['total_orders'] == 0): ?>
                                        <span class="badge badge-warning">Chưa mua</span>
                                    <?php elseif ($c['days_since_last_order'] !== null && $c['days_since_last_order'] > 90): ?>
                                        <span class="badge badge-danger">Không hoạt động</span>
                                    <?php elseif ($c['total_spent'] > 5000000): ?>
                                        <span class="badge badge-success">VIP</span>
                                    <?php else: ?>
                                        <span class="badge badge-info">Thường</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: #9ca3af;">
                        <i class="fas fa-users" style="font-size: 40px; margin-bottom: 10px;"></i>
                        <p>Không tìm thấy khách hàng nào</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Top Customers -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-trophy"></i> Top 10 khách VIP</h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    <?php if (!empty($topCustomers)): ?>
                        <?php foreach ($topCustomers as $index => $c): ?>
                        <div class="top-customer">
                            <div class="customer-rank rank-<?= $index + 1 ?>"><?= $index + 1 ?></div>
                            <div class="customer-info">
                                <div class="customer-name"><?= htmlspecialchars($c['full_name']) ?></div>
                                <div class="customer-email"><?= htmlspecialchars($c['email']) ?></div>
                            </div>
                            <div class="customer-stats">
                                <div class="customer-spent"><?= number_format($c['total_spent'], 0, ',', '.') ?>₫</div>
                                <div class="customer-orders"><?= $c['total_orders'] ?> đơn | <?= number_format($c['total_items']) ?> SP</div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: #9ca3af;">
                        <i class="fas fa-trophy" style="font-size: 40px; margin-bottom: 10px;"></i>
                        <p>Chưa có dữ liệu</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
