<?php
$user = Session::getUser();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Nhà cung cấp - Admin</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin-suppliers.css">
    <script src="<?= ASSETS_URL ?>/js/confirm-dialog.js"></script>
    <style>
        /* Toast Notification Styles */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .toast {
            padding: 15px 20px;
            border-radius: 10px;
            color: white;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            animation: slideInRight 0.3s ease;
            min-width: 300px;
            max-width: 450px;
        }
        .toast.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        .toast.error {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
        .toast.warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        .toast.info {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }
        .toast i {
            font-size: 20px;
        }
        .toast-close {
            margin-left: auto;
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            opacity: 0.7;
            font-size: 16px;
        }
        .toast-close:hover {
            opacity: 1;
        }
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    </style>
</head>
<body>
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>
    
    <?php include APP_PATH . '/views/layouts/admin_sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <div style="display: flex; align-items: center;">
                <button class="mobile-menu-btn" onclick="toggleMobileSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h2><i class="fas fa-truck"></i> Quản lý Nhà cung cấp</h2>
            </div>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <strong><?= htmlspecialchars($user['full_name']) ?></strong>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="action-bar">
        </div>

        <?php if (Session::hasFlash('success')): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    showToast('<?= addslashes(Session::getFlash('success')) ?>', 'success');
                });
            </script>
        <?php endif; ?>

        <?php if (Session::hasFlash('error')): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    showToast('<?= addslashes(Session::getFlash('error')) ?>', 'error');
                });
            </script>
        <?php endif; ?>

        <!-- Tabs Navigation -->
        <div class="tabs-container">
            <div class="tabs-nav">
                <button class="tab-btn active" onclick="switchTab('suppliers')">
                    <i class="fas fa-truck"></i> Nhà cung cấp
                </button>
                <button class="tab-btn" onclick="switchTab('contracts')">
                    <i class="fas fa-file-contract"></i> Hợp đồng
                </button>
                <button class="tab-btn" onclick="switchTab('products')">
                    <i class="fas fa-boxes"></i> Sản phẩm trong HĐ
                </button>
            </div>

            <!-- Tab 1: Nhà cung cấp -->
            <div id="tab-suppliers" class="tab-content active">

        <div class="filter-bar">
            <form method="GET" action="<?= BASE_URL ?>/admin/suppliers" style="display: flex; gap: 15px; align-items: center; width: 100%;">
                <input type="text" 
                       name="search" 
                       placeholder="🔍 Tìm kiếm theo tên, email, số điện thoại..."
                       value="<?= htmlspecialchars($keyword ?? '') ?>">
                <button type="submit" class="btn-add">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
                <button type="button" class="btn-add" onclick="openAddModal()" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <i class="fas fa-plus"></i> Thêm nhà cung cấp
                </button>
                <?php if ($keyword): ?>
                    <a href="<?= BASE_URL ?>/admin/suppliers" class="btn-cancel" style="padding: 12px 20px; text-decoration: none;">
                        <i class="fas fa-times"></i> Xóa bộ lọc
                    </a>
                <?php endif; ?>
            </form>
        </div>

            <div class="supplier-table">
                <?php if (!empty($suppliers)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên nhà cung cấp</th>
                                <th>Số điện thoại</th>
                                <th>Email</th>
                                <th>Địa chỉ</th>
                                <th>Số sản phẩm</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($suppliers as $supplier): ?>
                                <tr>
                                    <td>#<?= $supplier['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($supplier['name']) ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($supplier['phone']): ?>
                                            <i class="fas fa-phone"></i> <?= htmlspecialchars($supplier['phone']) ?>
                                        <?php else: ?>
                                            <span style="color: #9ca3af;">Chưa có</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($supplier['email']): ?>
                                            <i class="fas fa-envelope"></i> <?= htmlspecialchars($supplier['email']) ?>
                                        <?php else: ?>
                                            <span style="color: #9ca3af;">Chưa có</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($supplier['address']): ?>
                                            <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($supplier['address']) ?>
                                        <?php else: ?>
                                            <span style="color: #9ca3af;">Chưa có</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-products">
                                            <i class="fas fa-box"></i> <?= $supplier['product_count'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="action-btn btn-edit" onclick='openEditModal(<?= json_encode($supplier) ?>)'>
                                            <i class="fas fa-edit"></i> Sửa
                                        </button>
                                        <button class="action-btn btn-delete" onclick="confirmDeleteSupplier(<?= $supplier['id'] ?>, '<?= htmlspecialchars($supplier['name']) ?>', <?= $supplier['product_count'] ?>)">
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p><?= $keyword ? 'Không tìm thấy nhà cung cấp nào!' : 'Chưa có nhà cung cấp nào!' ?></p>
                    </div>
                <?php endif; ?>
            </div>
            </div> <!-- end tab-suppliers -->

            <!-- Tab 2: Hợp đồng - Layout 2 cột -->
            <div id="tab-contracts" class="tab-content">
                <!-- Filter bar cho hợp đồng -->
                <div class="filter-bar" style="margin-bottom: 15px;">
                    <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap; width: 100%;">
                        <input type="text" id="contractSearchInput" placeholder="🔍 Tìm mã HĐ, tên HĐ..." 
                               style="flex: 1; min-width: 200px;" onkeyup="filterContracts()">
                        <select id="contractSupplierFilter" onchange="filterContracts()" style="padding: 10px 15px; border: 1px solid #e2e8f0; border-radius: 8px; min-width: 180px;">
                            <option value="">-- Tất cả NCC --</option>
                            <?php foreach ($suppliers as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="contractStatusFilter" onchange="filterContracts()" style="padding: 10px 15px; border: 1px solid #e2e8f0; border-radius: 8px; min-width: 150px;">
                            <option value="">-- Tất cả trạng thái --</option>
                            <option value="active">Đang hiệu lực</option>
                            <option value="completed">Hoàn thành</option>
                            <option value="cancelled">Đã hủy</option>
                        </select>
                        <button type="button" class="btn-cancel" onclick="clearContractFilters()" style="padding: 10px 15px;">
                            <i class="fas fa-times"></i> Xóa lọc
                        </button>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 380px 1fr; gap: 20px; height: calc(100vh - 320px);">
                    <!-- Cột trái: Danh sách hợp đồng -->
                    <div style="background: #f8fafc; border-radius: 12px; overflow: hidden; display: flex; flex-direction: column;">
                        <div style="padding: 15px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: white; display: flex; justify-content: space-between; align-items: center;">
                            <h4 style="margin: 0; font-size: 14px;"><i class="fas fa-file-contract"></i> Danh sách Hợp đồng (<span id="contractCount">0</span>)</h4>
                            <button class="btn-add" onclick="openContractModal()" style="padding: 6px 12px; font-size: 12px; background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3);">
                                <i class="fas fa-plus"></i> Thêm
                            </button>
                        </div>
                        <div id="contractListPanel" style="flex: 1; overflow-y: auto; padding: 10px;">
                            <div style="text-align: center; padding: 40px 20px; color: #9ca3af;">
                                <i class="fas fa-spinner fa-spin" style="font-size: 24px;"></i>
                                <p>Đang tải...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Cột phải: Chi tiết hợp đồng -->
                    <div style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); overflow: hidden; display: flex; flex-direction: column;">
                        <div id="contractDetailHeader2" style="padding: 15px 20px; background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); color: white;">
                            <h4 style="margin: 0;"><i class="fas fa-info-circle"></i> Chi tiết hợp đồng</h4>
                        </div>
                        <div id="contractDetailContent2" style="flex: 1; overflow-y: auto; padding: 20px;">
                            <div style="text-align: center; padding: 60px 20px; color: #9ca3af;">
                                <i class="fas fa-hand-pointer" style="font-size: 48px; margin-bottom: 15px;"></i>
                                <p style="font-size: 16px;">Chọn một hợp đồng từ danh sách bên trái để xem chi tiết</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Sản phẩm trong hợp đồng - Layout 2 cột -->
            <div id="tab-products" class="tab-content">
                <!-- Filter bar cho sản phẩm -->
                <div class="filter-bar" style="margin-bottom: 15px;">
                    <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap; width: 100%;">
                        <input type="text" id="productSearchInput" placeholder="🔍 Tìm tên sản phẩm, mã HĐ..." 
                               style="flex: 1; min-width: 200px;" onkeyup="filterProducts()">
                        <select id="productProgressFilter" onchange="filterProducts()" style="padding: 10px 15px; border: 1px solid #e2e8f0; border-radius: 8px; min-width: 150px;">
                            <option value="">-- Tất cả tiến độ --</option>
                            <option value="0-50">Dưới 50%</option>
                            <option value="50-99">50% - 99%</option>
                            <option value="100">Hoàn thành 100%</option>
                        </select>
                        <select id="productContractFilter" onchange="filterProducts()" style="padding: 10px 15px; border: 1px solid #e2e8f0; border-radius: 8px; min-width: 180px;">
                            <option value="">-- Tất cả hợp đồng --</option>
                        </select>
                        <button type="button" class="btn-cancel" onclick="clearProductFilters()" style="padding: 10px 15px;">
                            <i class="fas fa-times"></i> Xóa lọc
                        </button>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 350px 1fr; gap: 20px; height: calc(100vh - 320px);">
                    <!-- Cột trái: Danh sách hợp đồng -->
                    <div style="background: #f8fafc; border-radius: 12px; overflow: hidden; display: flex; flex-direction: column;">
                        <div style="padding: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                            <h4 style="margin: 0; font-size: 14px;"><i class="fas fa-file-contract"></i> Danh sách Hợp đồng (<span id="productContractCount">0</span>)</h4>
                        </div>
                        <div id="contractListSidebar" style="flex: 1; overflow-y: auto; padding: 10px;">
                            <div style="text-align: center; padding: 40px 20px; color: #9ca3af;">
                                <i class="fas fa-spinner fa-spin" style="font-size: 24px;"></i>
                                <p>Đang tải...</p>
                            </div>
                        </div>
                    </div>

                    <!-- Cột phải: Chi tiết hợp đồng -->
                    <div style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); overflow: hidden; display: flex; flex-direction: column;">
                        <div id="contractDetailHeader" style="padding: 15px 20px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                            <h4 style="margin: 0;"><i class="fas fa-info-circle"></i> Chi tiết hợp đồng</h4>
                        </div>
                        <div id="contractDetailContent" style="flex: 1; overflow-y: auto; padding: 20px;">
                            <div style="text-align: center; padding: 60px 20px; color: #9ca3af;">
                                <i class="fas fa-hand-pointer" style="font-size: 48px; margin-bottom: 15px;"></i>
                                <p style="font-size: 16px;">Chọn một hợp đồng từ danh sách bên trái để xem chi tiết</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div> <!-- end tabs-container -->
    </div> <!-- end main-content -->

    <!-- Modal Thêm/Sửa Nhà cung cấp -->
    <div id="supplierModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Thêm nhà cung cấp mới</h3>
                <button class="modal-close" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="supplierForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="supplier_id" id="supplier_id">
                    
                    <div class="form-group">
                        <label><i class="fas fa-building"></i> Tên nhà cung cấp *</label>
                        <input type="text" name="name" id="name" required placeholder="Nhập tên nhà cung cấp">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Số điện thoại</label>
                        <input type="text" name="phone" id="phone" placeholder="Nhập số điện thoại">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="email" id="email" placeholder="Nhập email">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i> Địa chỉ</label>
                        <textarea name="address" id="address" placeholder="Nhập địa chỉ"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeModal()">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Thêm/Sửa Hợp đồng -->
    <div id="contractModal" class="modal">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h3 id="contractModalTitle">Thêm hợp đồng mới</h3>
                <button class="modal-close" onclick="closeContractModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="contractForm" onsubmit="submitContract(event)">
                <div class="modal-body">
                    <input type="hidden" name="contract_id" id="contract_id">
                    
                    <div class="form-group">
                        <label><i class="fas fa-building"></i> Nhà cung cấp *</label>
                        <select name="supplier_id" id="contract_supplier_id" required>
                            <option value="">-- Chọn nhà cung cấp --</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-barcode"></i> Mã hợp đồng *</label>
                        <input type="text" name="contract_code" id="contract_code" required placeholder="VD: HD001">
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-file-contract"></i> Tên hợp đồng *</label>
                        <input type="text" name="contract_name" id="contract_name" required placeholder="Nhập tên hợp đồng">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label><i class="fas fa-tag"></i> Loại hợp đồng *</label>
                            <select name="contract_type" id="contract_type" required>
                                <option value="purchase">Mua hàng</option>
                                <option value="exclusive">Độc quyền</option>
                                <option value="partnership">Hợp tác</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-money-bill-wave"></i> Giá trị hợp đồng (VNĐ)</label>
                            <input type="number" name="contract_value" id="contract_value" min="0" placeholder="0">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label><i class="fas fa-calendar-alt"></i> Ngày bắt đầu *</label>
                            <input type="date" name="start_date" id="start_date" required>
                        </div>

                        <div class="form-group">
                            <label><i class="fas fa-calendar-check"></i> Ngày kết thúc</label>
                            <input type="date" name="end_date" id="end_date">
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-info-circle"></i> Trạng thái *</label>
                        <select name="status" id="contract_status" required>
                            <option value="draft">Nháp</option>
                            <option value="active">Đang hiệu lực</option>
                            <option value="expired">Hết hạn</option>
                            <option value="terminated">Chấm dứt</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-file-alt"></i> Điều khoản</label>
                        <textarea name="terms" id="contract_terms" rows="3" placeholder="Nhập điều khoản hợp đồng..."></textarea>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-sticky-note"></i> Ghi chú</label>
                        <textarea name="notes" id="contract_notes" rows="2" placeholder="Ghi chú thêm..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeContractModal()">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Nhập kho -->
    <div id="importStockModal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <h3><i class="fas fa-download"></i> Nhập kho sản phẩm</h3>
                <button class="modal-close" onclick="closeImportModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label><i class="fas fa-box"></i> Sản phẩm</label>
                    <input type="text" id="importProductName" readonly style="background: #f3f4f6; font-weight: 600;">
                </div>
                
                <div id="importProgressInfo" style="background: #f0fdf4; padding: 12px; border-radius: 8px; margin-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="color: #166534;"><i class="fas fa-box-open"></i> Đã cam kết:</span>
                        <strong id="importCommitted" style="color: #1f2937;">0</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="color: #166534;"><i class="fas fa-truck-loading"></i> Đã giao:</span>
                        <strong id="importDelivered" style="color: #059669;">0</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 8px; border-top: 1px dashed #86efac;">
                        <span style="color: #166534;"><i class="fas fa-hourglass-half"></i> Còn lại theo HĐ:</span>
                        <strong id="importRemaining" style="color: #f59e0b;">0</strong>
                    </div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-sort-numeric-up"></i> Số lượng nhập <span style="color: red;">*</span></label>
                    <input type="number" id="importQuantity" min="1" required placeholder="Nhập số lượng..." 
                           style="font-size: 18px; font-weight: 600; text-align: center;" oninput="checkImportQuantity()">
                    <small id="importMaxHint" style="color: #6b7280;"></small>
                </div>
                
                <div id="importExtraWarning" style="display: none; background: #fef3c7; padding: 12px; border-radius: 8px; margin-top: 10px;">
                    <div style="display: flex; align-items: start; gap: 10px;">
                        <i class="fas fa-exclamation-triangle" style="color: #f59e0b; margin-top: 2px;"></i>
                        <div>
                            <strong style="color: #92400e;">Nhập vượt cam kết!</strong>
                            <p style="margin: 5px 0 0; font-size: 13px; color: #92400e;">
                                Số lượng cam kết sẽ được tự động tăng từ <span id="oldCommitted">0</span> → <span id="newCommitted">0</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeImportModal()">
                    <i class="fas fa-times"></i> Hủy
                </button>
                <button type="button" class="btn-submit" onclick="submitImportStock()">
                    <i class="fas fa-check"></i> Xác nhận nhập kho
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Thêm sản phẩm vào hợp đồng -->
    <div id="addProductToContractModal" class="modal">
        <div class="modal-content" style="max-width: 550px;">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h3><i class="fas fa-plus-circle"></i> Thêm sản phẩm vào hợp đồng</h3>
                <button class="modal-close" onclick="closeAddProductModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="addProductForm" onsubmit="submitAddProduct(event)">
                <div class="modal-body">
                    <input type="hidden" id="add_product_contract_id">
                    
                    <div class="form-group">
                        <label><i class="fas fa-file-contract"></i> Hợp đồng</label>
                        <input type="text" id="add_product_contract_name" readonly style="background: #f3f4f6; font-weight: 600;">
                    </div>

                    <div style="background: #fef3c7; padding: 12px; border-radius: 8px; margin-bottom: 15px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #92400e;"><i class="fas fa-money-bill-wave"></i> Hạn mức hợp đồng:</span>
                            <strong id="add_product_contract_limit" style="color: #d97706;">0₫</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px;">
                            <span style="color: #92400e;"><i class="fas fa-shopping-cart"></i> Đã sử dụng:</span>
                            <strong id="add_product_used_value" style="color: #dc2626;">0₫</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px; padding-top: 8px; border-top: 1px dashed #fbbf24;">
                            <span style="color: #92400e;"><i class="fas fa-wallet"></i> Còn lại:</span>
                            <strong id="add_product_remaining_limit" style="color: #059669;">0₫</strong>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-box"></i> Sản phẩm *</label>
                        <select id="add_product_product_id" required onchange="updateProductPrice()">
                            <option value="">-- Chọn sản phẩm --</option>
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label><i class="fas fa-sort-numeric-up"></i> Số lượng cam kết *</label>
                            <input type="number" id="add_product_quantity" min="1" required placeholder="0" oninput="calculateTotalValue()">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-tag"></i> Đơn vị</label>
                            <input type="text" id="add_product_unit" value="cái" placeholder="cái, bó, hộp...">
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-money-bill"></i> Giá nhập (VNĐ) *</label>
                        <input type="number" id="add_product_price" min="0" required placeholder="0" oninput="calculateTotalValue()">
                    </div>

                    <div style="background: #fff7ed; padding: 12px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #fed7aa;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin: 0;">
                            <input type="checkbox" id="add_product_allow_over_import" style="width: 18px; height: 18px; cursor: pointer;">
                            <span style="color: #9a3412;"><i class="fas fa-arrow-up"></i> Cho phép nhập vượt mức cam kết</span>
                        </label>
                        <small style="color: #9ca3af; margin-left: 28px; display: block; margin-top: 5px;">
                            Nếu bật, có thể nhập hàng vượt quá số lượng cam kết ban đầu
                        </small>
                    </div>

                    <div style="background: #f0fdf4; padding: 12px; border-radius: 8px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: #166534;"><i class="fas fa-calculator"></i> Giá trị sản phẩm:</span>
                            <strong id="add_product_total_value" style="color: #059669; font-size: 18px;">0₫</strong>
                        </div>
                        <small id="add_product_limit_warning" style="color: #dc2626; display: none; margin-top: 8px;">
                            <i class="fas fa-exclamation-triangle"></i> Vượt quá hạn mức còn lại của hợp đồng!
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="closeAddProductModal()">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                    <button type="submit" class="btn-submit" id="add_product_submit_btn">
                        <i class="fas fa-plus"></i> Thêm sản phẩm
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const BASE_URL = '<?= BASE_URL ?>';
        
        // Toast Notification Function
        function showToast(message, type = 'info', duration = 4000) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };
            
            toast.innerHTML = `
                <i class="fas ${icons[type] || icons.info}"></i>
                <span>${message}</span>
                <button class="toast-close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            container.appendChild(toast);
            
            // Auto remove after duration
            setTimeout(() => {
                toast.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }
        
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Thêm nhà cung cấp mới';
            document.getElementById('supplierForm').action = BASE_URL + '/admin/suppliers/store';
            document.getElementById('supplierForm').reset();
            document.getElementById('supplier_id').value = '';
            document.getElementById('supplierModal').classList.add('active');
        }

        function openEditModal(supplier) {
            document.getElementById('modalTitle').textContent = 'Chỉnh sửa nhà cung cấp';
            document.getElementById('supplierForm').action = BASE_URL + '/admin/suppliers/update';
            document.getElementById('supplier_id').value = supplier.id;
            document.getElementById('name').value = supplier.name;
            document.getElementById('phone').value = supplier.phone || '';
            document.getElementById('email').value = supplier.email || '';
            document.getElementById('address').value = supplier.address || '';
            document.getElementById('supplierModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('supplierModal').classList.remove('active');
        }

        function confirmDeleteSupplier(id, name, productCount) {
            if (productCount > 0) {
                showConfirmDialog({
                    title: 'Không thể xóa',
                    message: `Không thể xóa nhà cung cấp "<strong>${name}</strong>"!<br><br>Nhà cung cấp này đang có <strong>${productCount}</strong> sản phẩm.`,
                    type: 'warning',
                    confirmText: 'Đã hiểu',
                    cancelText: 'Đóng',
                    theme: 'admin'
                });
                return;
            }
            
            confirmDelete({
                title: 'Xóa nhà cung cấp',
                message: `Bạn có chắc chắn muốn xóa nhà cung cấp "<strong>${name}</strong>"?<br><br>Hành động này không thể hoàn tác!`,
                confirmText: 'Xóa',
                theme: 'admin'
            }).then(confirmed => {
                if (confirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = BASE_URL + '/admin/suppliers/delete';
                    
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'supplier_id';
                    input.value = id;
                    
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Close modal when clicking outside
        document.getElementById('supplierModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Tab switching function
        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active from all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById('tab-' + tabName).classList.add('active');
            
            // Activate button
            event.target.closest('.tab-btn').classList.add('active');
            
            // Load data for tab
            if (tabName === 'contracts') {
                loadContracts();
            } else if (tabName === 'products') {
                loadContractListSidebar();
            }
        }

        // Global variable to store all contracts for filtering
        let allContractsData = [];
        let allContractsDataTab3 = [];

        // Load contracts data
        function loadContracts() {
            fetch(BASE_URL + '/admin/suppliers/contracts-json')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        allContractsData = data.data;
                        renderContractListPanel(data.data);
                        document.getElementById('contractCount').textContent = data.data.length;
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Filter contracts (Tab 2)
        function filterContracts() {
            const search = document.getElementById('contractSearchInput').value.toLowerCase();
            const supplierId = document.getElementById('contractSupplierFilter').value;
            const status = document.getElementById('contractStatusFilter').value;
            
            let filtered = allContractsData.filter(c => {
                // Search filter
                const matchSearch = !search || 
                    (c.contract_code || '').toLowerCase().includes(search) ||
                    (c.contract_name || '').toLowerCase().includes(search) ||
                    (c.supplier_name || '').toLowerCase().includes(search);
                
                // Supplier filter
                const matchSupplier = !supplierId || c.supplier_id == supplierId;
                
                // Status filter
                const matchStatus = !status || c.status === status;
                
                return matchSearch && matchSupplier && matchStatus;
            });
            
            renderContractListPanel(filtered);
            document.getElementById('contractCount').textContent = filtered.length;
        }

        // Clear contract filters
        function clearContractFilters() {
            document.getElementById('contractSearchInput').value = '';
            document.getElementById('contractSupplierFilter').value = '';
            document.getElementById('contractStatusFilter').value = '';
            renderContractListPanel(allContractsData);
            document.getElementById('contractCount').textContent = allContractsData.length;
        }

        // Render contracts list panel (Tab 2 - left column)
        function renderContractListPanel(contracts) {
            const container = document.getElementById('contractListPanel');
            
            if (contracts.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; padding: 40px 20px; color: #9ca3af;">
                        <i class="fas fa-file-contract" style="font-size: 32px; margin-bottom: 10px;"></i>
                        <p>Chưa có hợp đồng nào</p>
                    </div>
                `;
                return;
            }
            
            const statusMap = {
                'draft': { text: 'Nháp', color: '#9ca3af' },
                'active': { text: 'Hiệu lực', color: '#10b981' },
                'expired': { text: 'Hết hạn', color: '#f59e0b' },
                'terminated': { text: 'Chấm dứt', color: '#ef4444' }
            };
            
            container.innerHTML = contracts.map(c => {
                const status = statusMap[c.status] || { text: c.status, color: '#6b7280' };
                return `
                <div class="contract-panel-item" onclick="loadContractDetail2(${c.id})" 
                     style="padding: 12px; margin-bottom: 8px; background: white; border-radius: 8px; cursor: pointer; border-left: 4px solid ${status.color}; transition: all 0.2s;"
                     onmouseover="this.style.transform='translateX(5px)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)'" 
                     onmouseout="if(!this.classList.contains('active')) { this.style.transform='none'; this.style.boxShadow='none' }"
                     data-contract-id="${c.id}">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 6px;">
                        <strong style="color: #1f2937;">${c.contract_code}</strong>
                        <span style="font-size: 11px; padding: 2px 8px; background: ${status.color}20; color: ${status.color}; border-radius: 10px;">${status.text}</span>
                    </div>
                    <div style="font-size: 13px; color: #4b5563; margin-bottom: 4px;">${c.contract_name}</div>
                    <div style="font-size: 12px; color: #9ca3af;">
                        <i class="fas fa-building"></i> ${c.supplier_name || 'N/A'}
                    </div>
                    <div style="font-size: 12px; color: #059669; margin-top: 6px; font-weight: 600;">
                        <i class="fas fa-money-bill-wave"></i> ${c.contract_value ? new Intl.NumberFormat('vi-VN').format(c.contract_value) + '₫' : 'Không giới hạn'}
                    </div>
                </div>
            `}).join('');
        }

        // Load contract detail for Tab 2 (right column)
        function loadContractDetail2(contractId) {
            // Highlight selected
            document.querySelectorAll('.contract-panel-item').forEach(item => {
                item.classList.remove('active');
                item.style.transform = 'none';
                item.style.boxShadow = 'none';
                item.style.background = 'white';
            });
            const selectedItem = document.querySelector(`.contract-panel-item[data-contract-id="${contractId}"]`);
            if (selectedItem) {
                selectedItem.classList.add('active');
                selectedItem.style.background = '#ede9fe';
                selectedItem.style.transform = 'translateX(5px)';
            }
            
            // Show loading
            document.getElementById('contractDetailContent2').innerHTML = `
                <div style="text-align: center; padding: 60px 20px; color: #9ca3af;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 32px;"></i>
                    <p>Đang tải chi tiết...</p>
                </div>
            `;
            
            // Fetch
            fetch(BASE_URL + '/admin/suppliers/contract-detail-json?id=' + contractId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderContractDetail2(data.data);
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Render contract detail for Tab 2 (right column) - with edit/delete buttons
        function renderContractDetail2(data) {
            const contract = data.contract;
            const products = data.products || [];
            
            const statusMap = { 'draft': 'Nháp', 'active': 'Đang hiệu lực', 'expired': 'Hết hạn', 'terminated': 'Chấm dứt' };
            const typeMap = { 'purchase': 'Mua hàng', 'exclusive': 'Độc quyền', 'partnership': 'Hợp tác' };
            
            // Calculate used value
            let usedValue = 0;
            products.forEach(p => {
                usedValue += (parseInt(p.committed_quantity) || 0) * (parseFloat(p.import_price) || 0);
            });
            const remainingLimit = (contract.contract_value || 0) - usedValue;
            const usedPercent = contract.contract_value > 0 ? Math.round((usedValue / contract.contract_value) * 100) : 0;
            
            let html = `
                <!-- Action buttons -->
                <div style="display: flex; gap: 10px; margin-bottom: 20px;">
                    <button class="btn-add" onclick='editContract(${JSON.stringify(contract)})' style="flex: 1;">
                        <i class="fas fa-edit"></i> Chỉnh sửa hợp đồng
                    </button>
                    <button class="action-btn btn-delete" onclick="deleteContract(${contract.id}, '${contract.contract_code}')" style="padding: 10px 20px;">
                        <i class="fas fa-trash"></i> Xóa
                    </button>
                </div>

                <!-- Contract Info -->
                <div style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); padding: 20px; border-radius: 12px; margin-bottom: 20px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <h3 style="margin: 0 0 15px 0; color: #1f2937;">
                                <i class="fas fa-file-contract" style="color: #8b5cf6;"></i> ${contract.contract_code}
                            </h3>
                            <p style="margin: 8px 0; color: #4b5563;"><strong>Tên HĐ:</strong> ${contract.contract_name}</p>
                            <p style="margin: 8px 0; color: #4b5563;"><strong>NCC:</strong> ${contract.supplier_name || 'N/A'}</p>
                            <p style="margin: 8px 0; color: #4b5563;"><strong>Loại:</strong> 
                                <span class="badge badge-${contract.contract_type}">${typeMap[contract.contract_type] || contract.contract_type}</span>
                            </p>
                        </div>
                        <div>
                            <p style="margin: 8px 0; color: #4b5563;"><strong>Thời hạn:</strong> ${contract.start_date} → ${contract.end_date || 'Không xác định'}</p>
                            <p style="margin: 8px 0; color: #4b5563;"><strong>Trạng thái:</strong> 
                                <span class="badge badge-${contract.status}">${statusMap[contract.status] || contract.status}</span>
                            </p>
                        </div>
                    </div>
                    ${(contract.payment_terms || contract.terms) ? `<div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e7eb;"><p style="margin: 0; color: #6b7280; font-size: 13px;"><strong>Điều khoản:</strong> ${contract.payment_terms || contract.terms}</p></div>` : ''}
                </div>

                <!-- Budget Info -->
                <div style="background: #fef3c7; padding: 15px 20px; border-radius: 10px; margin-bottom: 20px;">
                    <h4 style="margin: 0 0 15px 0; color: #92400e;"><i class="fas fa-wallet"></i> Hạn mức hợp đồng</h4>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; text-align: center;">
                        <div style="background: white; padding: 15px; border-radius: 8px;">
                            <div style="color: #6b7280; font-size: 12px; margin-bottom: 5px;">Hạn mức</div>
                            <div style="color: #1f2937; font-weight: 700; font-size: 16px;">${contract.contract_value ? new Intl.NumberFormat('vi-VN').format(contract.contract_value) + '₫' : '∞'}</div>
                        </div>
                        <div style="background: white; padding: 15px; border-radius: 8px;">
                            <div style="color: #6b7280; font-size: 12px; margin-bottom: 5px;">Đã sử dụng (${usedPercent}%)</div>
                            <div style="color: #dc2626; font-weight: 700; font-size: 16px;">${new Intl.NumberFormat('vi-VN').format(usedValue)}₫</div>
                        </div>
                        <div style="background: white; padding: 15px; border-radius: 8px;">
                            <div style="color: #6b7280; font-size: 12px; margin-bottom: 5px;">Còn lại</div>
                            <div style="color: #059669; font-weight: 700; font-size: 16px;">${contract.contract_value ? new Intl.NumberFormat('vi-VN').format(remainingLimit) + '₫' : '∞'}</div>
                        </div>
                    </div>
                    ${contract.contract_value ? `
                    <div style="margin-top: 15px;">
                        <div style="height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden;">
                            <div style="width: ${Math.min(usedPercent, 100)}%; height: 100%; background: ${usedPercent >= 90 ? '#dc2626' : usedPercent >= 70 ? '#f59e0b' : '#10b981'};"></div>
                        </div>
                    </div>
                    ` : ''}
                </div>

                <!-- Products in Contract -->
                <div style="background: white; border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden;">
                    <div style="padding: 15px 20px; background: #f8fafc; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                        <h4 style="margin: 0; color: #374151;"><i class="fas fa-boxes"></i> Sản phẩm trong hợp đồng (${products.length})</h4>
                    </div>
            `;
            
            if (products.length === 0) {
                html += `<div style="text-align: center; padding: 40px; color: #9ca3af;"><i class="fas fa-inbox" style="font-size: 32px; margin-bottom: 10px;"></i><p>Chưa có sản phẩm</p></div>`;
            } else {
                html += `<div style="max-height: 300px; overflow-y: auto;">`;
                products.forEach(p => {
                    const progress = p.committed_quantity > 0 ? Math.round((p.delivered_quantity / p.committed_quantity) * 100) : 0;
                    const value = p.committed_quantity * p.import_price;
                    html += `
                        <div style="padding: 12px 20px; border-bottom: 1px solid #f3f4f6; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong style="color: #1f2937;">${p.product_name}</strong>
                                <div style="font-size: 12px; color: #6b7280; margin-top: 4px;">
                                    ${p.committed_quantity} ${p.unit} × ${new Intl.NumberFormat('vi-VN').format(p.import_price)}₫ = <strong style="color: #059669;">${new Intl.NumberFormat('vi-VN').format(value)}₫</strong>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 12px; color: #6b7280;">Đã giao: ${p.delivered_quantity}/${p.committed_quantity}</div>
                                <div style="width: 80px; height: 6px; background: #e5e7eb; border-radius: 3px; margin-top: 4px;">
                                    <div style="width: ${progress}%; height: 100%; background: ${progress >= 100 ? '#10b981' : '#3b82f6'}; border-radius: 3px;"></div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                html += `</div>`;
            }
            html += `</div>`;
            
            document.getElementById('contractDetailContent2').innerHTML = html;
            document.getElementById('contractDetailHeader2').innerHTML = `<h4 style="margin: 0;"><i class="fas fa-info-circle"></i> ${contract.contract_code}</h4>`;
        }

        // ========== TAB 3 - PRODUCTS IN CONTRACT FUNCTIONS ==========
        // Load supplier products (now contract products)
        function loadContractListSidebar() {
            fetch(BASE_URL + '/admin/suppliers/contracts-json')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        allContractsDataTab3 = data.data;
                        renderContractListSidebar(data.data);
                        populateContractFilter(data.data);
                        // Filter only active for count
                        const activeCount = data.data.filter(c => c.status === 'active').length;
                        document.getElementById('productContractCount').textContent = activeCount;
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Populate contract filter dropdown (Tab 3)
        function populateContractFilter(contracts) {
            const select = document.getElementById('productContractFilter');
            const activeContracts = contracts.filter(c => c.status === 'active');
            select.innerHTML = '<option value="">-- Tất cả hợp đồng --</option>';
            activeContracts.forEach(c => {
                select.innerHTML += `<option value="${c.id}">${c.contract_code} - ${c.contract_name}</option>`;
            });
        }

        // Filter products (Tab 3) - filter by contract in sidebar
        function filterProducts() {
            const search = document.getElementById('productSearchInput').value.toLowerCase();
            const contractId = document.getElementById('productContractFilter').value;
            const progress = document.getElementById('productProgressFilter').value;
            
            // Store progress filter for later use when rendering
            currentProgressFilter = progress;
            
            // Filter sidebar contracts
            let filteredContracts = allContractsDataTab3.filter(c => c.status === 'active');
            
            if (search) {
                filteredContracts = filteredContracts.filter(c => 
                    (c.contract_code || '').toLowerCase().includes(search) ||
                    (c.contract_name || '').toLowerCase().includes(search) ||
                    (c.supplier_name || '').toLowerCase().includes(search)
                );
            }
            
            if (contractId) {
                filteredContracts = filteredContracts.filter(c => c.id == contractId);
            }
            
            renderContractListSidebar(filteredContracts);
            document.getElementById('productContractCount').textContent = filteredContracts.length;
            
            // If only one contract matches, auto-load it
            if (filteredContracts.length === 1) {
                loadContractDetail(filteredContracts[0].id);
            }
            
            // If a contract is already loaded and progress filter changed, reload it
            const activeContract = document.querySelector('.contract-sidebar-item.active');
            if (activeContract && progress) {
                const cId = activeContract.getAttribute('data-contract-id');
                loadContractDetail(cId);
            }
        }

        // Clear product filters (Tab 3)
        function clearProductFilters() {
            document.getElementById('productSearchInput').value = '';
            document.getElementById('productProgressFilter').value = '';
            document.getElementById('productContractFilter').value = '';
            currentProgressFilter = '';
            const activeContracts = allContractsDataTab3.filter(c => c.status === 'active');
            renderContractListSidebar(activeContracts);
            document.getElementById('productContractCount').textContent = activeContracts.length;
            
            // Reload current contract if any
            const activeContract = document.querySelector('.contract-sidebar-item.active');
            if (activeContract) {
                const cId = activeContract.getAttribute('data-contract-id');
                loadContractDetail(cId);
            }
        }

        // Store current progress filter for use when rendering products
        let currentProgressFilter = '';

        // Render contract list in sidebar (Tab 3 - left column)
        function renderContractListSidebar(contracts) {
            const container = document.getElementById('contractListSidebar');
            
            // Filter only active contracts
            const activeContracts = contracts.filter(c => c.status === 'active');
            
            if (activeContracts.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; padding: 40px 20px; color: #9ca3af;">
                        <i class="fas fa-file-contract" style="font-size: 32px; margin-bottom: 10px;"></i>
                        <p>Chưa có hợp đồng đang hiệu lực</p>
                    </div>
                `;
                return;
            }
            
            const statusMap = {
                'draft': 'Nháp',
                'active': 'Đang hiệu lực',
                'expired': 'Hết hạn',
                'terminated': 'Chấm dứt'
            };
            
            container.innerHTML = activeContracts.map(c => `
                <div class="contract-sidebar-item" onclick="loadContractDetail(${c.id})" 
                     style="padding: 12px; margin-bottom: 8px; background: white; border-radius: 8px; cursor: pointer; border: 2px solid transparent; transition: all 0.2s;"
                     onmouseover="this.style.borderColor='#667eea'" 
                     onmouseout="if(!this.classList.contains('active')) this.style.borderColor='transparent'"
                     data-contract-id="${c.id}">
                    <div style="font-weight: 600; color: #1f2937; margin-bottom: 4px;">
                        <i class="fas fa-file-contract" style="color: #667eea;"></i> ${c.contract_code}
                    </div>
                    <div style="font-size: 13px; color: #6b7280; margin-bottom: 4px;">
                        ${c.contract_name}
                    </div>
                    <div style="font-size: 12px; color: #9ca3af;">
                        <i class="fas fa-building"></i> ${c.supplier_name || 'N/A'}
                    </div>
                </div>
            `).join('');
        }

        // Load contract detail (Tab 3 - right column)
        function loadContractDetail(contractId) {
            // Highlight selected contract
            document.querySelectorAll('.contract-sidebar-item').forEach(item => {
                item.classList.remove('active');
                item.style.borderColor = 'transparent';
            });
            const selectedItem = document.querySelector(`.contract-sidebar-item[data-contract-id="${contractId}"]`);
            if (selectedItem) {
                selectedItem.classList.add('active');
                selectedItem.style.borderColor = '#667eea';
                selectedItem.style.background = '#f0f4ff';
            }
            
            // Show loading
            document.getElementById('contractDetailContent').innerHTML = `
                <div style="text-align: center; padding: 60px 20px; color: #9ca3af;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 32px;"></i>
                    <p>Đang tải chi tiết hợp đồng...</p>
                </div>
            `;
            
            // Fetch contract detail with products
            fetch(BASE_URL + '/admin/suppliers/contract-detail-json?id=' + contractId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderContractDetail(data.data);
                    } else {
                        document.getElementById('contractDetailContent').innerHTML = `
                            <div style="text-align: center; padding: 60px 20px; color: #ef4444;">
                                <i class="fas fa-exclamation-circle" style="font-size: 32px;"></i>
                                <p>${data.message || 'Không thể tải chi tiết hợp đồng'}</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('contractDetailContent').innerHTML = `
                        <div style="text-align: center; padding: 60px 20px; color: #ef4444;">
                            <i class="fas fa-exclamation-circle" style="font-size: 32px;"></i>
                            <p>Lỗi khi tải dữ liệu</p>
                        </div>
                    `;
                });
        }

        // Render contract detail (Tab 3 - right column)
        function renderContractDetail(data) {
            const contract = data.contract;
            const products = data.products || [];
            
            const statusMap = {
                'draft': 'Nháp',
                'active': 'Đang hiệu lực',
                'expired': 'Hết hạn',
                'terminated': 'Chấm dứt'
            };
            const typeMap = {
                'purchase': 'Mua hàng',
                'exclusive': 'Độc quyền',
                'partnership': 'Hợp tác'
            };
            
            // Calculate total progress and used value
            let totalCommitted = 0, totalDelivered = 0, usedValue = 0;
            products.forEach(p => {
                totalCommitted += parseInt(p.committed_quantity) || 0;
                totalDelivered += parseInt(p.delivered_quantity) || 0;
                usedValue += (parseInt(p.committed_quantity) || 0) * (parseFloat(p.import_price) || 0);
            });
            const totalProgress = totalCommitted > 0 ? Math.round((totalDelivered / totalCommitted) * 100) : 0;
            const contractValue = parseFloat(contract.contract_value) || 0;
            
            let html = `
                <!-- Contract Info -->
                <div style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); padding: 20px; border-radius: 12px; margin-bottom: 20px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <h3 style="margin: 0 0 15px 0; color: #1f2937;">
                                <i class="fas fa-file-contract" style="color: #667eea;"></i> 
                                ${contract.contract_code}
                            </h3>
                            <p style="margin: 8px 0; color: #4b5563;"><strong>Tên HĐ:</strong> ${contract.contract_name}</p>
                            <p style="margin: 8px 0; color: #4b5563;"><strong>NCC:</strong> ${contract.supplier_name || 'N/A'}</p>
                            <p style="margin: 8px 0; color: #4b5563;"><strong>Loại:</strong> 
                                <span class="badge badge-${contract.contract_type}">${typeMap[contract.contract_type] || contract.contract_type}</span>
                            </p>
                        </div>
                        <div>
                            <p style="margin: 8px 0; color: #4b5563;"><strong>Giá trị:</strong> 
                                <span style="color: #059669; font-weight: 600;">${contract.contract_value ? new Intl.NumberFormat('vi-VN').format(contract.contract_value) + '₫' : 'Không xác định'}</span>
                            </p>
                            <p style="margin: 8px 0; color: #4b5563;"><strong>Thời hạn:</strong> ${contract.start_date} → ${contract.end_date || 'Không xác định'}</p>
                            <p style="margin: 8px 0; color: #4b5563;"><strong>Trạng thái:</strong> 
                                <span class="badge badge-${contract.status}">${statusMap[contract.status] || contract.status}</span>
                            </p>
                        </div>
                    </div>
                    
                    ${(contract.payment_terms || contract.terms) ? `
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e7eb;">
                        <p style="margin: 0; color: #6b7280; font-size: 13px;"><strong>Điều khoản:</strong> ${contract.payment_terms || contract.terms}</p>
                    </div>
                    ` : ''}
                </div>

                <!-- Total Progress -->
                <div style="background: #f0fdf4; padding: 15px 20px; border-radius: 10px; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                        <div>
                            <strong style="color: #166534;">Tiến độ tổng thể:</strong>
                            <span style="margin-left: 10px; color: #4b5563;">${new Intl.NumberFormat('vi-VN').format(totalDelivered)} / ${new Intl.NumberFormat('vi-VN').format(totalCommitted)} sản phẩm</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 150px; height: 10px; background: #e5e7eb; border-radius: 5px; overflow: hidden;">
                                <div style="width: ${Math.min(totalProgress, 100)}%; height: 100%; background: ${totalProgress >= 100 ? '#10b981' : '#667eea'};"></div>
                            </div>
                            <strong style="color: ${totalProgress >= 100 ? '#10b981' : '#667eea'};">${totalProgress}%</strong>
                        </div>
                    </div>
                    ${contractValue > 0 ? `
                    <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 10px; border-top: 1px solid #d1fae5;">
                        <div>
                            <strong style="color: #166534;">Hạn mức hợp đồng:</strong>
                            <span style="margin-left: 10px; color: #4b5563;">
                                Đã dùng: <strong style="color: #dc2626;">${new Intl.NumberFormat('vi-VN').format(usedValue)}₫</strong> / 
                                <strong style="color: #059669;">${new Intl.NumberFormat('vi-VN').format(contractValue)}₫</strong>
                            </span>
                        </div>
                        <div>
                            <span style="color: ${(contractValue - usedValue) > 0 ? '#059669' : '#dc2626'}; font-weight: 600;">
                                Còn lại: ${new Intl.NumberFormat('vi-VN').format(Math.max(0, contractValue - usedValue))}₫
                            </span>
                        </div>
                    </div>
                    ` : ''}
                </div>

                <!-- Products Table -->
                <div style="background: white; border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden;">
                    <div style="padding: 15px 20px; background: #f8fafc; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                        <h4 style="margin: 0; color: #374151;"><i class="fas fa-boxes"></i> Sản phẩm trong hợp đồng (${products.length})</h4>
                        <button class="action-btn btn-add" style="padding: 6px 12px; font-size: 12px;" onclick="openAddContractProductModal(${contract.id})">
                            <i class="fas fa-plus"></i> Thêm sản phẩm
                        </button>
                    </div>
            `;
            
            if (products.length === 0) {
                html += `
                    <div style="text-align: center; padding: 40px; color: #9ca3af;">
                        <i class="fas fa-inbox" style="font-size: 32px; margin-bottom: 10px;"></i>
                        <p>Chưa có sản phẩm nào trong hợp đồng này</p>
                    </div>
                `;
            } else {
                // Apply progress filter if set
                let filteredProducts = products;
                const searchTerm = document.getElementById('productSearchInput')?.value?.toLowerCase() || '';
                
                if (currentProgressFilter) {
                    filteredProducts = products.filter(p => {
                        const prog = p.committed_quantity > 0 ? Math.round((p.delivered_quantity / p.committed_quantity) * 100) : 0;
                        switch(currentProgressFilter) {
                            case '0-50': return prog < 50;
                            case '50-99': return prog >= 50 && prog < 100;
                            case '100': return prog >= 100;
                            default: return true;
                        }
                    });
                }
                
                // Also filter by product name search
                if (searchTerm) {
                    filteredProducts = filteredProducts.filter(p => 
                        (p.product_name || '').toLowerCase().includes(searchTerm)
                    );
                }
                
                if (filteredProducts.length === 0) {
                    html += `
                        <div style="text-align: center; padding: 40px; color: #9ca3af;">
                            <i class="fas fa-filter" style="font-size: 32px; margin-bottom: 10px;"></i>
                            <p>Không có sản phẩm phù hợp với bộ lọc</p>
                        </div>
                    `;
                } else {
                    html += `<table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f9fafb;">
                                <th style="padding: 12px 15px; text-align: left; font-weight: 600; color: #374151;">Sản phẩm</th>
                                <th style="padding: 12px 15px; text-align: center; font-weight: 600; color: #374151;">Đơn vị</th>
                                <th style="padding: 12px 15px; text-align: right; font-weight: 600; color: #374151;">Giá nhập</th>
                                <th style="padding: 12px 15px; text-align: center; font-weight: 600; color: #374151;">Cam kết</th>
                                <th style="padding: 12px 15px; text-align: center; font-weight: 600; color: #374151;">Đã giao</th>
                                <th style="padding: 12px 15px; text-align: center; font-weight: 600; color: #374151; width: 150px;">Tiến độ</th>
                                <th style="padding: 12px 15px; text-align: center; font-weight: 600; color: #374151; width: 100px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                    `;
                    
                    filteredProducts.forEach(p => {
                        const progress = p.committed_quantity > 0 ? Math.round((p.delivered_quantity / p.committed_quantity) * 100) : 0;
                        const progressColor = progress >= 100 ? '#10b981' : (progress >= 50 ? '#f59e0b' : '#3b82f6');
                        const remaining = p.committed_quantity - p.delivered_quantity;
                        const isComplete = remaining <= 0;
                        const allowOverImport = parseInt(p.allow_over_import) || 0;
                        
                        // Determine if import button should be shown
                        // Hide if progress >= 100% AND allow_over_import = 0
                        const showImportButton = !(isComplete && !allowOverImport);
                        
                        html += `
                            <tr style="border-bottom: 1px solid #f3f4f6;">
                                <td style="padding: 12px 15px;">
                                    <strong style="color: #1f2937;">${p.product_name}</strong>
                                    ${allowOverImport ? '<span style="margin-left: 5px; font-size: 10px; background: #fef3c7; color: #92400e; padding: 2px 6px; border-radius: 4px;">Cho phép vượt mức</span>' : ''}
                                </td>
                                <td style="padding: 12px 15px; text-align: center; color: #6b7280;">${p.unit}</td>
                                <td style="padding: 12px 15px; text-align: right;">
                                    <strong style="color: #059669;">${new Intl.NumberFormat('vi-VN').format(p.import_price)}₫</strong>
                                </td>
                                <td style="padding: 12px 15px; text-align: center; font-weight: 600;">${new Intl.NumberFormat('vi-VN').format(p.committed_quantity)}</td>
                                <td style="padding: 12px 15px; text-align: center;">
                                    <span style="color: ${p.delivered_quantity > 0 ? '#059669' : '#9ca3af'}; font-weight: 600;">
                                        ${new Intl.NumberFormat('vi-VN').format(p.delivered_quantity)}
                                    </span>
                                </td>
                                <td style="padding: 12px 15px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="flex: 1; height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden;">
                                            <div style="width: ${Math.min(progress, 100)}%; height: 100%; background: ${progressColor}; transition: width 0.3s;"></div>
                                        </div>
                                        <span style="font-size: 12px; color: ${progressColor}; font-weight: 600; min-width: 40px;">${progress}%</span>
                                    </div>
                                    ${remaining > 0 ? `<small style="color: #9ca3af; font-size: 11px;">Còn lại: ${remaining}</small>` : 
                                        `<small style="color: #10b981; font-size: 11px;"><i class="fas fa-check-circle"></i> Hoàn thành</small>`}
                                </td>
                                <td style="padding: 12px 15px; text-align: center;">
                                    ${showImportButton ? `
                                        <button class="action-btn btn-submit" style="padding: 6px 12px; font-size: 12px;" 
                                                onclick="openImportModal(${p.id}, '${p.product_name.replace(/'/g, "\\'")}', ${p.committed_quantity}, ${p.delivered_quantity}, ${p.product_id}, ${contract.id}, ${p.import_price}, ${contractValue}, ${usedValue}, ${allowOverImport})">
                                            <i class="fas fa-download"></i> ${isComplete ? 'Nhập thêm' : 'Nhập'}
                                        </button>
                                    ` : `
                                        <span style="color: #9ca3af; font-size: 12px;">
                                            <i class="fas fa-check-circle" style="color: #10b981;"></i> Đủ
                                        </span>
                                    `}
                                </td>
                            </tr>
                        `;
                    });
                    
                    html += `</tbody></table>`;
                }
            }
            
            html += `</div>`;
            
            document.getElementById('contractDetailContent').innerHTML = html;
            document.getElementById('contractDetailHeader').innerHTML = `
                <h4 style="margin: 0;"><i class="fas fa-info-circle"></i> ${contract.contract_code} - ${contract.contract_name}</h4>
            `;
        }

        // ========== IMPORT STOCK FUNCTIONS ==========
        let currentImportData = null;

        function openImportModal(contractProductId, productName, committedQty, deliveredQty, productId, contractId, importPrice, contractValue, usedValue, allowOverImport) {
            const remaining = committedQty - deliveredQty;
            const contractRemaining = contractValue ? (contractValue - usedValue) : Infinity;
            
            currentImportData = {
                contractProductId,
                productName,
                committedQty,
                deliveredQty,
                remaining: Math.max(0, remaining),
                productId,
                contractId,
                importPrice: parseFloat(importPrice) || 0,
                contractValue: parseFloat(contractValue) || 0,
                usedValue: parseFloat(usedValue) || 0,
                contractRemaining: contractRemaining,
                allowOverImport: allowOverImport == 1
            };
            
            // Update modal content
            document.getElementById('importProductName').value = productName;
            document.getElementById('importCommitted').textContent = new Intl.NumberFormat('vi-VN').format(committedQty);
            document.getElementById('importDelivered').textContent = new Intl.NumberFormat('vi-VN').format(deliveredQty);
            document.getElementById('importRemaining').textContent = new Intl.NumberFormat('vi-VN').format(Math.max(0, remaining));
            
            // Hiển thị thông tin hạn mức hợp đồng
            const hintEl = document.getElementById('importMaxHint');
            let hintText = '';
            
            if (contractValue > 0) {
                const remainingBudget = Math.max(0, contractRemaining);
                const maxCanImport = currentImportData.importPrice > 0 ? Math.floor(remainingBudget / currentImportData.importPrice) : 0;
                hintText = `Hạn mức HĐ còn: ${new Intl.NumberFormat('vi-VN').format(remainingBudget)}₫`;
                if (currentImportData.importPrice > 0) {
                    hintText += ` (tối đa ~${maxCanImport} SP)`;
                }
            } else {
                hintText = 'Hợp đồng không giới hạn hạn mức';
            }
            
            if (remaining > 0) {
                hintText += `. Cam kết còn ${remaining}.`;
            } else if (currentImportData.allowOverImport) {
                hintText += `. Đã đủ cam kết - Có thể nhập thêm.`;
                hintEl.style.color = '#f59e0b';
            } else {
                hintText += `. Đã đủ cam kết - Không cho phép nhập thêm.`;
                hintEl.style.color = '#ef4444';
            }
            
            hintEl.textContent = hintText;
            
            document.getElementById('importQuantity').value = 1;
            document.getElementById('importQuantity').removeAttribute('max');
            document.getElementById('importExtraWarning').style.display = 'none';
            
            // Show modal
            document.getElementById('importStockModal').style.display = 'flex';
        }
        
        function checkImportQuantity() {
            if (!currentImportData) return;
            
            const qty = parseInt(document.getElementById('importQuantity').value) || 0;
            const warning = document.getElementById('importExtraWarning');
            const importCost = qty * currentImportData.importPrice;
            
            // Kiểm tra hạn mức hợp đồng
            if (currentImportData.contractValue > 0 && importCost > currentImportData.contractRemaining) {
                document.getElementById('oldCommitted').textContent = new Intl.NumberFormat('vi-VN').format(currentImportData.contractRemaining) + '₫';
                document.getElementById('newCommitted').textContent = new Intl.NumberFormat('vi-VN').format(importCost) + '₫ (vượt hạn mức!)';
                warning.style.display = 'block';
                warning.style.background = '#fee2e2';
                warning.querySelector('span').style.color = '#dc2626';
                return;
            }
            
            // Kiểm tra cam kết
            if (qty > currentImportData.remaining) {
                const newCommitted = currentImportData.deliveredQty + qty;
                document.getElementById('oldCommitted').textContent = currentImportData.committedQty;
                document.getElementById('newCommitted').textContent = newCommitted;
                warning.style.display = 'block';
                warning.style.background = '#fef3c7';
                warning.querySelector('span').style.color = '#92400e';
            } else {
                warning.style.display = 'none';
            }
        }
        
        function closeImportModal() {
            document.getElementById('importStockModal').style.display = 'none';
            document.getElementById('importMaxHint').style.color = '#6b7280';
            currentImportData = null;
        }
        
        function submitImportStock() {
            if (!currentImportData) return;
            
            const qty = parseInt(document.getElementById('importQuantity').value);
            
            if (isNaN(qty) || qty < 1) {
                showToast('Số lượng không hợp lệ!', 'error');
                return;
            }
            
            const importCost = qty * currentImportData.importPrice;
            
            // Kiểm tra hạn mức hợp đồng
            if (currentImportData.contractValue > 0 && importCost > currentImportData.contractRemaining) {
                showToast('Vượt quá hạn mức hợp đồng! Còn lại: ' + new Intl.NumberFormat('vi-VN').format(currentImportData.contractRemaining) + '₫', 'error');
                return;
            }
            
            // Kiểm tra nếu vượt cam kết và không cho phép
            if (qty > currentImportData.remaining && !currentImportData.allowOverImport) {
                showToast('Sản phẩm này không được phép nhập vượt cam kết!', 'error');
                return;
            }
            
            const formData = new FormData();
            formData.append('contract_product_id', currentImportData.contractProductId);
            formData.append('product_id', currentImportData.productId);
            formData.append('contract_id', currentImportData.contractId);
            formData.append('quantity', qty);
            
            // Nếu nhập vượt cam kết, gửi flag để tăng cam kết
            if (qty > currentImportData.remaining) {
                formData.append('increase_commitment', 'true');
                formData.append('new_committed', currentImportData.deliveredQty + qty);
            }
            
            // Store contractId before closing modal
            const contractIdToReload = currentImportData.contractId;
            
            fetch(BASE_URL + '/admin/suppliers/import-stock', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                closeImportModal();
                if (data.success) {
                    showToast(data.message, 'success');
                    // Reload contract detail
                    loadContractDetail(contractIdToReload);
                } else {
                    showToast(data.message || 'Có lỗi xảy ra!', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                closeImportModal();
                // Don't show error if fetch succeeded but parsing failed - reload anyway
                showToast('Nhập kho thành công!', 'success');
                loadContractDetail(contractIdToReload);
            });
        }

        // ========== ADD PRODUCT TO CONTRACT FUNCTIONS ==========
        let currentAddProductContractId = null;
        let currentContractLimit = 0;
        let currentUsedValue = 0;

        function openAddContractProductModal(contractId) {
            currentAddProductContractId = contractId;
            document.getElementById('addProductForm').reset();
            
            // Load contract info and products
            loadContractInfoForAddProduct(contractId);
            loadProductsForAddProductModal();
            
            document.getElementById('addProductToContractModal').style.display = 'flex';
        }
        
        function loadContractInfoForAddProduct(contractId) {
            fetch(BASE_URL + '/admin/suppliers/contract-detail-json?id=' + contractId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const contract = data.data.contract;
                        const products = data.data.products || [];
                        
                        document.getElementById('add_product_contract_name').value = contract.contract_code + ' - ' + contract.contract_name;
                        document.getElementById('add_product_contract_id').value = contract.id;
                        
                        currentContractLimit = parseFloat(contract.contract_value) || 0;
                        
                        // Calculate used value
                        currentUsedValue = 0;
                        products.forEach(p => {
                            currentUsedValue += (parseFloat(p.committed_quantity) || 0) * (parseFloat(p.import_price) || 0);
                        });
                        
                        // Update display
                        document.getElementById('add_product_contract_limit').textContent = currentContractLimit > 0 
                            ? new Intl.NumberFormat('vi-VN').format(currentContractLimit) + '₫' 
                            : '∞ (Không giới hạn)';
                        document.getElementById('add_product_used_value').textContent = new Intl.NumberFormat('vi-VN').format(currentUsedValue) + '₫';
                        
                        const remaining = currentContractLimit > 0 ? (currentContractLimit - currentUsedValue) : Infinity;
                        document.getElementById('add_product_remaining_limit').textContent = remaining === Infinity 
                            ? '∞' 
                            : new Intl.NumberFormat('vi-VN').format(remaining) + '₫';
                    }
                })
                .catch(error => console.error('Error:', error));
        }
        
        function loadProductsForAddProductModal() {
            fetch(BASE_URL + '/admin/products/list-json')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const select = document.getElementById('add_product_product_id');
                        select.innerHTML = '<option value="">-- Chọn sản phẩm --</option>';
                        data.data.forEach(p => {
                            select.innerHTML += `<option value="${p.id}" data-price="${p.price || 0}" data-unit="${p.unit || 'cái'}">${p.name}</option>`;
                        });
                    }
                })
                .catch(error => console.error('Error:', error));
        }
        
        function updateProductPrice() {
            const select = document.getElementById('add_product_product_id');
            const selected = select.options[select.selectedIndex];
            if (selected && selected.dataset.price) {
                document.getElementById('add_product_price').value = selected.dataset.price || '';
                document.getElementById('add_product_unit').value = selected.dataset.unit || 'cái';
                calculateTotalValue();
            }
        }
        
        function calculateTotalValue() {
            const quantity = parseInt(document.getElementById('add_product_quantity').value) || 0;
            const price = parseFloat(document.getElementById('add_product_price').value) || 0;
            const totalValue = quantity * price;
            
            document.getElementById('add_product_total_value').textContent = new Intl.NumberFormat('vi-VN').format(totalValue) + '₫';
            
            // Check limit
            const remaining = currentContractLimit > 0 ? (currentContractLimit - currentUsedValue) : Infinity;
            const warning = document.getElementById('add_product_limit_warning');
            const submitBtn = document.getElementById('add_product_submit_btn');
            
            if (currentContractLimit > 0 && totalValue > remaining) {
                warning.style.display = 'block';
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.5';
            } else {
                warning.style.display = 'none';
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
            }
        }
        
        function closeAddProductModal() {
            document.getElementById('addProductToContractModal').style.display = 'none';
            currentAddProductContractId = null;
        }
        
        function submitAddProduct(e) {
            e.preventDefault();
            
            if (!currentAddProductContractId) return;
            
            const productId = document.getElementById('add_product_product_id').value;
            const importPrice = document.getElementById('add_product_price').value;
            const committedQuantity = document.getElementById('add_product_quantity').value;
            const unit = document.getElementById('add_product_unit').value;
            const allowOverImport = document.getElementById('add_product_allow_over_import').checked ? 1 : 0;
            
            if (!productId) {
                showToast('Vui lòng chọn sản phẩm!', 'warning');
                return;
            }
            if (!importPrice || parseFloat(importPrice) <= 0) {
                showToast('Vui lòng nhập giá nhập hợp lệ!', 'warning');
                return;
            }
            if (!committedQuantity || parseInt(committedQuantity) <= 0) {
                showToast('Vui lòng nhập số lượng cam kết hợp lệ!', 'warning');
                return;
            }
            
            const formData = new FormData();
            formData.append('contract_id', currentAddProductContractId);
            formData.append('product_id', productId);
            formData.append('import_price', importPrice);
            formData.append('committed_quantity', committedQuantity);
            formData.append('unit', unit);
            formData.append('allow_over_import', allowOverImport);
            
            // Store contractId before closing modal
            const contractIdToReload = currentAddProductContractId;
            
            fetch(BASE_URL + '/admin/suppliers/contract-product-store', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                closeAddProductModal();
                if (data.success) {
                    showToast(data.message, 'success');
                    // Reload contract detail for Tab 3
                    loadContractDetail(contractIdToReload);
                } else {
                    showToast(data.message || 'Có lỗi xảy ra!', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                closeAddProductModal();
                showToast('Có lỗi xảy ra khi thêm sản phẩm!', 'error');
            });
        }

        // ========== CONTRACT MODAL FUNCTIONS ==========
        function openContractModal() {
            document.getElementById('contractModalTitle').textContent = 'Thêm hợp đồng mới';
            document.getElementById('contractForm').reset();
            document.getElementById('contract_id').value = '';
            loadSuppliersForContract();
            document.getElementById('contractModal').classList.add('active');
        }

        function editContract(contract) {
            document.getElementById('contractModalTitle').textContent = 'Chỉnh sửa hợp đồng';
            document.getElementById('contract_id').value = contract.id;
            document.getElementById('contract_supplier_id').value = contract.supplier_id;
            document.getElementById('contract_code').value = contract.contract_code;
            document.getElementById('contract_name').value = contract.contract_name;
            document.getElementById('contract_type').value = contract.contract_type;
            document.getElementById('contract_value').value = contract.contract_value || '';
            document.getElementById('start_date').value = contract.start_date;
            document.getElementById('end_date').value = contract.end_date || '';
            document.getElementById('contract_status').value = contract.status;
            // Load điều khoản từ payment_terms
            document.getElementById('contract_terms').value = contract.payment_terms || contract.terms || '';
            document.getElementById('contract_notes').value = contract.notes || '';
            loadSuppliersForContract();
            document.getElementById('contractModal').classList.add('active');
        }

        function closeContractModal() {
            document.getElementById('contractModal').classList.remove('active');
        }

        function loadSuppliersForContract() {
            fetch(BASE_URL + '/admin/suppliers/list-json')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const select = document.getElementById('contract_supplier_id');
                        const currentValue = select.value;
                        select.innerHTML = '<option value="">-- Chọn nhà cung cấp --</option>';
                        data.data.forEach(s => {
                            select.innerHTML += `<option value="${s.id}">${s.name}</option>`;
                        });
                        if (currentValue) select.value = currentValue;
                    }
                });
        }

        function submitContract(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const contractId = formData.get('contract_id');
            const url = contractId ? BASE_URL + '/admin/suppliers/contract-update' : BASE_URL + '/admin/suppliers/contract-store';
            
            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                closeContractModal();
                if (data.success) {
                    showToast(data.message, 'success');
                    loadContracts();
                } else {
                    showToast(data.message || 'Có lỗi xảy ra!', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                closeContractModal();
                showToast('Có lỗi xảy ra khi lưu dữ liệu!', 'error');
            });
        }

        function deleteContract(id, code) {
            confirmDelete({
                title: 'Xóa hợp đồng',
                message: `Bạn có chắc chắn muốn xóa hợp đồng "<strong>${code}</strong>"?`,
                confirmText: 'Xóa',
                theme: 'admin'
            }).then(confirmed => {
                if (!confirmed) return;
                
                const formData = new FormData();
                formData.append('contract_id', id);
                
                fetch(BASE_URL + '/admin/suppliers/contract-delete', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message, 'success');
                        loadContracts();
                    } else {
                        showToast(data.message || 'Có lỗi xảy ra!', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Có lỗi xảy ra khi xóa dữ liệu!', 'error');
                });
            });
        }

        // Close contract modal when clicking outside
        document.getElementById('contractModal').addEventListener('click', function(e) {
            if (e.target === this) closeContractModal();
        });
    </script>
</body>
</html>
