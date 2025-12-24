<?php
$currentRole = Session::get('user_role');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo Tồn kho - Admin</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
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
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
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
        .kpi-value {
            font-size: 26px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 5px;
        }
        .kpi-label {
            font-size: 13px;
            color: #6b7280;
        }
        .tabs {
            display: flex;
            gap: 5px;
            background: white;
            padding: 10px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .tab-btn {
            padding: 12px 24px;
            border: none;
            background: transparent;
            color: #6b7280;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .tab-btn:hover { background: #f3f4f6; }
        .tab-btn.active {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
        }
        .tab-btn .badge-count {
            background: rgba(255,255,255,0.2);
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
        }
        .tab-btn.active .badge-count {
            background: rgba(255,255,255,0.3);
        }
        .content-grid {
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
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        .product-table {
            width: 100%;
            border-collapse: collapse;
        }
        .product-table th, .product-table td {
            padding: 12px 10px;
            text-align: left;
            border-bottom: 1px solid #f3f4f6;
        }
        .product-table th {
            background: #f8fafc;
            font-weight: 600;
            color: #374151;
            font-size: 12px;
            text-transform: uppercase;
            position: sticky;
            top: 0;
        }
        .product-table tr:hover { background: #f8fafc; }
        .product-img {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            object-fit: cover;
        }
        .product-name {
            font-weight: 600;
            color: #1f2937;
        }
        .product-category {
            font-size: 11px;
            color: #9ca3af;
        }
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .badge-danger { background: #fee2e2; color: #dc2626; }
        .badge-warning { background: #fef3c7; color: #b45309; }
        .badge-success { background: #d1fae5; color: #047857; }
        .badge-info { background: #dbeafe; color: #1d4ed8; }
        .stock-alert {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        .stock-alert.danger {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
        }
        .stock-alert.warning {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
        }
        .stock-alert.success {
            background: #f0fdf4;
            border-left: 4px solid #22c55e;
        }
        .category-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            border-bottom: 1px solid #f3f4f6;
        }
        .category-item:last-child { border-bottom: none; }
        .category-name { flex: 1; font-weight: 500; }
        .category-stats { text-align: right; }
        .category-stock {
            font-weight: 700;
            color: #374151;
        }
        .category-value {
            font-size: 12px;
            color: #6b7280;
        }
        .btn-export {
            padding: 8px 16px;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .chart-container {
            height: 300px;
            padding: 15px;
        }
    </style>
</head>
<body>
    <?php include APP_PATH . '/views/layouts/admin_sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <h2><i class="fas fa-warehouse"></i> Báo cáo Tồn kho</h2>
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
            <a href="<?= BASE_URL ?>/admin/revenue/customers" class="nav-link">
                <i class="fas fa-users"></i> Thống kê khách hàng
            </a>
            <a href="<?= BASE_URL ?>/admin/revenue/inventory" class="nav-link active">
                <i class="fas fa-warehouse"></i> Báo cáo tồn kho
            </a>
        </div>

        <!-- KPI Cards -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8);"><i class="fas fa-boxes"></i></div>
                <div class="kpi-value"><?= number_format($inventoryStats['total_products'] ?? 0) ?></div>
                <div class="kpi-label">Tổng sản phẩm</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg, #10b981, #059669);"><i class="fas fa-layer-group"></i></div>
                <div class="kpi-value"><?= number_format($inventoryStats['total_stock'] ?? 0) ?></div>
                <div class="kpi-label">Tổng tồn kho</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg, #ef4444, #dc2626);"><i class="fas fa-exclamation-circle"></i></div>
                <div class="kpi-value"><?= number_format($inventoryStats['out_of_stock'] ?? 0) ?></div>
                <div class="kpi-label">Hết hàng</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="kpi-value"><?= number_format($inventoryStats['low_stock'] ?? 0) ?></div>
                <div class="kpi-label">Sắp hết (≤10)</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);"><i class="fas fa-dollar-sign"></i></div>
                <div class="kpi-value" style="font-size: 20px;"><?= number_format($inventoryStats['total_stock_value'] ?? 0, 0, ',', '.') ?>₫</div>
                <div class="kpi-label">Giá trị tồn kho</div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <a href="<?= BASE_URL ?>/admin/revenue/inventory?tab=overview" class="tab-btn <?= $tab == 'overview' ? 'active' : '' ?>">
                <i class="fas fa-chart-pie"></i> Tổng quan
            </a>
            <a href="<?= BASE_URL ?>/admin/revenue/inventory?tab=out" class="tab-btn <?= $tab == 'out' ? 'active' : '' ?>">
                <i class="fas fa-times-circle"></i> Hết hàng
                <span class="badge-count"><?= $inventoryStats['out_of_stock'] ?? 0 ?></span>
            </a>
            <a href="<?= BASE_URL ?>/admin/revenue/inventory?tab=low" class="tab-btn <?= $tab == 'low' ? 'active' : '' ?>">
                <i class="fas fa-exclamation-triangle"></i> Sắp hết
                <span class="badge-count"><?= $inventoryStats['low_stock'] ?? 0 ?></span>
            </a>
            <a href="<?= BASE_URL ?>/admin/revenue/inventory?tab=high" class="tab-btn <?= $tab == 'high' ? 'active' : '' ?>">
                <i class="fas fa-archive"></i> Tồn kho cao
                <span class="badge-count"><?= $inventoryStats['high_stock'] ?? 0 ?></span>
            </a>
        </div>

        <div class="content-grid">
            <!-- Main Content -->
            <div class="card">
                <div class="card-header">
                    <h3>
                        <?php if ($tab == 'out'): ?>
                            <i class="fas fa-times-circle" style="color: #ef4444;"></i> Sản phẩm hết hàng
                        <?php elseif ($tab == 'low'): ?>
                            <i class="fas fa-exclamation-triangle" style="color: #f59e0b;"></i> Sản phẩm sắp hết hàng
                        <?php elseif ($tab == 'high'): ?>
                            <i class="fas fa-archive" style="color: #8b5cf6;"></i> Sản phẩm tồn kho cao
                        <?php else: ?>
                            <i class="fas fa-chart-pie" style="color: #3b82f6;"></i> Tổng quan tồn kho
                        <?php endif; ?>
                    </h3>
                    <a href="<?= BASE_URL ?>/admin/revenue/export-inventory" class="btn-export">
                        <i class="fas fa-download"></i> Export CSV
                    </a>
                </div>
                <div class="card-body">
                    <?php if ($tab == 'overview'): ?>
                        <!-- Overview Tab -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <div class="stock-alert danger">
                                <i class="fas fa-times-circle" style="font-size: 24px; color: #ef4444;"></i>
                                <div>
                                    <div style="font-weight: 700; font-size: 18px;"><?= $inventoryStats['out_of_stock'] ?? 0 ?> sản phẩm</div>
                                    <div style="color: #6b7280; font-size: 13px;">Đã hết hàng, cần nhập thêm</div>
                                </div>
                            </div>
                            <div class="stock-alert warning">
                                <i class="fas fa-exclamation-triangle" style="font-size: 24px; color: #f59e0b;"></i>
                                <div>
                                    <div style="font-weight: 700; font-size: 18px;"><?= $inventoryStats['low_stock'] ?? 0 ?> sản phẩm</div>
                                    <div style="color: #6b7280; font-size: 13px;">Còn ≤10 sản phẩm, sắp hết</div>
                                </div>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div style="background: #f8fafc; padding: 20px; border-radius: 10px;">
                                <div style="color: #6b7280; font-size: 13px; margin-bottom: 5px;">Giá trị tồn (giá bán)</div>
                                <div style="font-size: 22px; font-weight: 700; color: #059669;">
                                    <?= number_format($inventoryStats['total_stock_value'] ?? 0, 0, ',', '.') ?>₫
                                </div>
                            </div>
                            <div style="background: #f8fafc; padding: 20px; border-radius: 10px;">
                                <div style="color: #6b7280; font-size: 13px; margin-bottom: 5px;">Giá trị tồn (giá nhập)</div>
                                <div style="font-size: 22px; font-weight: 700; color: #3b82f6;">
                                    <?= number_format($inventoryStats['total_cost_value'] ?? 0, 0, ',', '.') ?>₫
                                </div>
                            </div>
                        </div>

                        <div style="margin-top: 20px; padding: 15px; background: #f0fdf4; border-radius: 10px; border-left: 4px solid #22c55e;">
                            <div style="color: #166534; font-weight: 600;">
                                <i class="fas fa-coins"></i> Lợi nhuận tiềm năng từ tồn kho
                            </div>
                            <div style="font-size: 24px; font-weight: 700; color: #047857; margin-top: 5px;">
                                <?= number_format($inventoryStats['potential_profit'] ?? 0, 0, ',', '.') ?>₫
                            </div>
                        </div>

                    <?php elseif (!empty($products)): ?>
                        <!-- Products Table -->
                        <table class="product-table">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th style="text-align: center;">Tồn kho</th>
                                    <th style="text-align: right;">Giá bán</th>
                                    <th style="text-align: right;">Giá nhập</th>
                                    <?php if ($tab == 'high'): ?>
                                    <th style="text-align: center;">Bán/30 ngày</th>
                                    <th style="text-align: center;">Ngày tồn</th>
                                    <?php else: ?>
                                    <th style="text-align: center;">Bán/30 ngày</th>
                                    <th>NCC</th>
                                    <?php endif; ?>
                                    <th style="text-align: center;">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $p): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <img src="<?= BASE_URL ?>/uploads/products/<?= htmlspecialchars($p['image']) ?>" 
                                                 class="product-img" 
                                                 onerror="this.src='<?= BASE_URL ?>/assets/images/no-image.png'">
                                            <div>
                                                <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
                                                <div class="product-category"><?= $p['category_name'] ?? 'N/A' ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <span style="font-weight: 700; font-size: 16px; 
                                            color: <?= $p['stock'] == 0 ? '#ef4444' : ($p['stock'] <= 10 ? '#f59e0b' : '#374151') ?>;">
                                            <?= $p['stock'] ?>
                                        </span>
                                    </td>
                                    <td style="text-align: right;"><?= number_format($p['price'], 0, ',', '.') ?>₫</td>
                                    <td style="text-align: right;">
                                        <?php if ($p['import_price'] > 0): ?>
                                            <?= number_format($p['import_price'], 0, ',', '.') ?>₫
                                        <?php else: ?>
                                            <span style="color: #9ca3af;">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <span style="font-weight: 600; color: <?= $p['sold_last_30_days'] > 0 ? '#059669' : '#9ca3af' ?>;">
                                            <?= $p['sold_last_30_days'] ?? 0 ?>
                                        </span>
                                    </td>
                                    <?php if ($tab == 'high'): ?>
                                    <td style="text-align: center;">
                                        <?php if (isset($p['days_of_stock'])): ?>
                                            <span style="font-weight: 600; color: <?= $p['days_of_stock'] > 90 ? '#ef4444' : '#6b7280' ?>;">
                                                <?= $p['days_of_stock'] > 365 ? '365+' : $p['days_of_stock'] ?> ngày
                                            </span>
                                        <?php else: ?>
                                            <span style="color: #9ca3af;">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <?php else: ?>
                                    <td style="font-size: 12px;">
                                        <?= htmlspecialchars($p['supplier_name'] ?? 'Chưa có NCC') ?>
                                    </td>
                                    <?php endif; ?>
                                    <td style="text-align: center;">
                                        <?php if ($p['stock'] == 0): ?>
                                            <span class="badge badge-danger">Hết hàng</span>
                                        <?php elseif ($p['stock'] <= 10): ?>
                                            <span class="badge badge-warning">Sắp hết</span>
                                        <?php elseif ($p['stock'] > 100): ?>
                                            <span class="badge badge-info">Tồn cao</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">Bình thường</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div style="text-align: center; padding: 60px; color: #9ca3af;">
                            <i class="fas fa-box-open" style="font-size: 50px; margin-bottom: 15px;"></i>
                            <p>Không có sản phẩm nào trong danh mục này</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div>
                <!-- Inventory by Category -->
                <div class="card" style="margin-bottom: 20px;">
                    <div class="card-header">
                        <h3><i class="fas fa-layer-group"></i> Tồn kho theo danh mục</h3>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <?php if (!empty($inventoryByCategory)): ?>
                            <?php foreach ($inventoryByCategory as $cat): ?>
                            <div class="category-item">
                                <div class="category-name">
                                    <?= htmlspecialchars($cat['category_name']) ?>
                                    <?php if ($cat['out_of_stock_count'] > 0): ?>
                                        <span class="badge badge-danger" style="font-size: 10px; margin-left: 5px;">
                                            <?= $cat['out_of_stock_count'] ?> hết
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="category-stats">
                                    <div class="category-stock"><?= number_format($cat['total_stock']) ?> SP</div>
                                    <div class="category-value"><?= number_format($cat['stock_value'], 0, ',', '.') ?>₫</div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="text-align: center; padding: 30px; color: #9ca3af;">
                                Không có dữ liệu
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-bolt"></i> Hành động nhanh</h3>
                    </div>
                    <div class="card-body">
                        <a href="<?= BASE_URL ?>/admin/suppliers" style="display: flex; align-items: center; gap: 12px; padding: 12px; background: #f8fafc; border-radius: 8px; text-decoration: none; color: #374151; margin-bottom: 10px; transition: all 0.2s;" onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f8fafc'">
                            <i class="fas fa-truck" style="color: #3b82f6;"></i>
                            <div>
                                <div style="font-weight: 600;">Quản lý NCC</div>
                                <div style="font-size: 12px; color: #6b7280;">Tạo đơn nhập hàng</div>
                            </div>
                        </a>
                        <a href="<?= BASE_URL ?>/admin/products" style="display: flex; align-items: center; gap: 12px; padding: 12px; background: #f8fafc; border-radius: 8px; text-decoration: none; color: #374151; margin-bottom: 10px; transition: all 0.2s;" onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f8fafc'">
                            <i class="fas fa-box" style="color: #10b981;"></i>
                            <div>
                                <div style="font-weight: 600;">Quản lý sản phẩm</div>
                                <div style="font-size: 12px; color: #6b7280;">Cập nhật tồn kho</div>
                            </div>
                        </a>
                        <a href="<?= BASE_URL ?>/admin/revenue/export-inventory" style="display: flex; align-items: center; gap: 12px; padding: 12px; background: #f8fafc; border-radius: 8px; text-decoration: none; color: #374151; transition: all 0.2s;" onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f8fafc'">
                            <i class="fas fa-file-excel" style="color: #059669;"></i>
                            <div>
                                <div style="font-weight: 600;">Xuất báo cáo</div>
                                <div style="font-size: 12px; color: #6b7280;">Export CSV tồn kho</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
