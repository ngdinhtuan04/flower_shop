<?php
$user = Session::getUser();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Sản phẩm - Admin</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin-products.css">
    <script src="<?= ASSETS_URL ?>/js/confirm-dialog.js"></script>
</head>
<body>
    <?php include APP_PATH . '/views/layouts/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <h2><i class="fas fa-box"></i> Quản lý Sản phẩm</h2>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <strong><?= htmlspecialchars($user['full_name']) ?></strong>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <button class="btn-add" onclick="openAddModal()">
                <i class="fas fa-plus"></i> Thêm Sản phẩm
            </button>
            
            <form method="GET" action="<?= BASE_URL ?>/admin/products" id="filterForm" style="display: flex; gap: 15px; flex: 1; align-items: center;">
                <input type="text" name="search" placeholder="🔍 Tìm kiếm sản phẩm..." value="<?= htmlspecialchars($search ?? '') ?>" style="flex: 1;">
                
                <select name="category">
                    <option value="">Tất cả danh mục</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($categoryFilter == $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <select name="status">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active" <?= ($statusFilter == 'active') ? 'selected' : '' ?>>Đang bán</option>
                    <option value="inactive" <?= ($statusFilter == 'inactive') ? 'selected' : '' ?>>Ngừng bán</option>
                </select>
            </form>
        </div>

        <!-- Products Table -->
        <div class="product-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Ảnh</th>
                        <th>Tên Sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Tồn kho</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= $product['id'] ?></td>
                                <td>
                                    <?php if (!empty($product['image'])): ?>
                                        <img src="<?= BASE_URL ?>/<?= htmlspecialchars($product['image']) ?>" alt="" class="product-image" onerror="this.src='<?= BASE_URL ?>/assets/images/no-image.png'">
                                    <?php else: ?>
                                        <div class="product-image" style="background: #f3f4f6; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-image" style="color: #d1d5db;"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($product['name']) ?></strong></td>
                                <td><?= htmlspecialchars($product['category_name'] ?? 'N/A') ?></td>
                                <td class="price-cell"><?= number_format($product['price']) ?>₫</td>
                                <td class="stock-cell <?= ($product['stock_quantity'] ?? 0) < 10 ? 'stock-low' : 'stock-ok' ?>">
                                    <?= $product['stock_quantity'] ?? 0 ?>
                                </td>
                                <td>
                                    <span class="badge <?= ($product['status'] ?? 'inactive') === 'active' ? 'badge-active' : 'badge-inactive' ?>">
                                        <?= ($product['status'] ?? 'inactive') === 'active' ? 'Đang bán' : 'Ngừng bán' ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="action-btn" onclick='openDetailModal(<?= json_encode($product, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' style="background: #3b82f6; color: white;">
                                        <i class="fas fa-eye"></i> Chi tiết
                                    </button>
                                    <button class="action-btn btn-edit" onclick='openEditModal(<?= json_encode($product, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                        <i class="fas fa-edit"></i> Sửa
                                    </button>
                                    <button class="action-btn btn-delete" onclick="deleteProduct(<?= $product['id'] ?>, '<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>')">
                                        <i class="fas fa-trash"></i> Xóa
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="empty-state">
                                <i class="fas fa-box-open"></i>
                                <h3>Chưa có sản phẩm nào</h3>
                                <p>Nhấn nút "Thêm Sản phẩm" để bắt đầu</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-plus-circle"></i> Thêm Sản phẩm mới</h3>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/products-store" enctype="multipart/form-data" id="addProductForm">
                <div class="modal-body">
                <div class="form-group">
                    <label>Tên Sản phẩm <span style="color: red;">*</span></label>
                    <input type="text" name="name" id="add_name" placeholder="Ví dụ: Hoa Hồng Đỏ Ecuador">
                    <div class="error-message" id="add_name_error">Vui lòng nhập tên sản phẩm</div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Danh mục <span style="color: red;">*</span></label>
                        <select name="category_id" id="add_category_id">
                            <option value="">-- Chọn danh mục --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="error-message" id="add_category_id_error">Vui lòng chọn danh mục</div>
                    </div>
                    <div class="form-group">
                        <label>Trạng thái <span style="color: red;">*</span></label>
                        <select name="status" id="add_status">
                            <option value="active">Đang bán</option>
                            <option value="inactive">Ngừng bán</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Giá (VNĐ) <span style="color: red;">*</span></label>
                        <input type="number" name="price" id="add_price" min="0" step="1000" placeholder="500000">
                        <div class="error-message" id="add_price_error">Vui lòng nhập giá hợp lệ (≥ 0)</div>
                    </div>
                    <div class="form-group">
                        <label>Số lượng <span style="color: red;">*</span></label>
                        <input type="number" name="stock" id="add_stock" min="0" placeholder="100">
                        <div class="error-message" id="add_stock_error">Vui lòng nhập số lượng hợp lệ (≥ 0)</div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Ảnh sản phẩm</label>
                    <input type="file" name="image" accept="image/*" onchange="previewAddImage(event)">
                    <div class="image-preview" id="addImagePreview">
                        <img id="addPreviewImg" src="" alt="Preview">
                    </div>
                </div>

                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea name="description" placeholder="Mô tả chi tiết về sản phẩm..."></textarea>
                </div>
                </div>

                <div class="modal-buttons">
                    <button type="button" class="action-btn btn-cancel" onclick="closeAddModal()">Hủy</button>
                    <button type="submit" class="action-btn btn-submit">Thêm Sản phẩm</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Chỉnh sửa Sản phẩm</h3>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/products-update" enctype="multipart/form-data" id="editProductForm">
                <input type="hidden" id="edit_product_id" name="product_id">
                <div class="modal-body">
                
                <div class="form-group">
                    <label>Tên Sản phẩm <span style="color: red;">*</span></label>
                    <input type="text" id="edit_name" name="name">
                    <div class="error-message" id="edit_name_error">Vui lòng nhập tên sản phẩm</div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Danh mục <span style="color: red;">*</span></label>
                        <select id="edit_category_id" name="category_id">
                            <option value="">-- Chọn danh mục --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="error-message" id="edit_category_id_error">Vui lòng chọn danh mục</div>
                    </div>
                    <div class="form-group">
                        <label>Trạng thái <span style="color: red;">*</span></label>
                        <select id="edit_status" name="status">
                            <option value="active">Đang bán</option>
                            <option value="inactive">Ngừng bán</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Giá (VNĐ) <span style="color: red;">*</span></label>
                    <input type="number" id="edit_price" name="price" min="0" step="1000">
                    <div class="error-message" id="edit_price_error">Vui lòng nhập giá hợp lệ (≥ 0)</div>
                </div>

                <div class="form-group">
                    <label>Ảnh mới (để trống nếu giữ ảnh cũ)</label>
                    <input type="file" name="image" accept="image/*" onchange="previewEditImage(event)">
                    <div class="image-preview" id="editImagePreview">
                        <img id="editPreviewImg" src="" alt="Preview">
                    </div>
                </div>

                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea id="edit_description" name="description"></textarea>
                </div>
                </div>

                <div class="modal-buttons">
                    <button type="button" class="action-btn btn-cancel" onclick="closeEditModal()">Hủy</button>
                    <button type="submit" class="action-btn btn-submit">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Chi tiết Sản phẩm -->
    <div id="detailModal" class="modal">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h3><i class="fas fa-info-circle"></i> Chi tiết Sản phẩm</h3>
            </div>
            <div class="modal-body">
                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 20px;">
                    <div>
                        <img id="detail_image" src="" alt="Product" style="width: 100%; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    </div>
                    <div>
                        <h3 id="detail_name" style="margin: 0 0 15px 0; color: #1f2937;"></h3>
                        <div style="display: grid; gap: 12px;">
                            <div><strong>ID:</strong> <span id="detail_id"></span></div>
                            <div><strong>Danh mục:</strong> <span id="detail_category"></span></div>
                            <div><strong>Giá:</strong> <span id="detail_price" style="color: #ef4444; font-weight: 700; font-size: 18px;"></span></div>
                            <div><strong>Tồn kho:</strong> <span id="detail_stock"></span></div>
                            <div><strong>Trạng thái:</strong> <span id="detail_status"></span></div>
                            <div><strong>Mô tả:</strong><br><span id="detail_description" style="color: #6b7280; line-height: 1.6;"></span></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-buttons">
                <button type="button" class="action-btn btn-cancel" onclick="closeDetailModal()">Đóng</button>
            </div>
        </div>
    </div>

    <script>
        // Auto-search với AJAX (không reload trang)
        let searchTimeout;
        const searchInput = document.querySelector('input[name="search"]');
        const categorySelect = document.querySelector('select[name="category"]');
        const statusSelect = document.querySelector('select[name="status"]');
        
        function performSearch() {
            const search = searchInput.value;
            const category = categorySelect.value;
            const status = statusSelect.value;
            
            // Build URL với params
            const params = new URLSearchParams();
            if (search) params.append('search', search);
            if (category) params.append('category', category);
            if (status) params.append('status', status);
            
            const url = '<?= BASE_URL ?>/admin/products?' + params.toString();
            
            // Dùng fetch API để load nội dung mới
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    // Parse HTML response
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    // Cập nhật bảng sản phẩm
                    const newTable = doc.querySelector('.product-table');
                    const currentTable = document.querySelector('.product-table');
                    if (newTable && currentTable) {
                        currentTable.innerHTML = newTable.innerHTML;
                    }
                })
                .catch(error => console.error('Error:', error));
        }
        
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(performSearch, 300);
            });
        }
        
        if (categorySelect) {
            categorySelect.addEventListener('change', performSearch);
        }
        
        if (statusSelect) {
            statusSelect.addEventListener('change', performSearch);
        }

        function openAddModal() {
            document.getElementById('addModal').classList.add('active');
            // Reset form và errors
            document.getElementById('addProductForm').reset();
            clearFormErrors('add');
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.remove('active');
            clearFormErrors('add');
        }

        function openDetailModal(product) {
            document.getElementById('detail_id').textContent = product.id;
            document.getElementById('detail_name').textContent = product.name;
            document.getElementById('detail_category').textContent = product.category_name || 'N/A';
            document.getElementById('detail_price').textContent = new Intl.NumberFormat('vi-VN').format(product.price) + '₫';
            document.getElementById('detail_stock').textContent = product.stock_quantity || 0;
            document.getElementById('detail_status').innerHTML = product.status === 'active' 
                ? '<span class="badge badge-active">Đang bán</span>' 
                : '<span class="badge badge-inactive">Ngừng bán</span>';
            document.getElementById('detail_description').textContent = product.description || 'Không có mô tả';
            
            const detailImage = document.getElementById('detail_image');
            if (product.image) {
                detailImage.src = '<?= BASE_URL ?>/' + product.image;
            } else {
                detailImage.src = '<?= BASE_URL ?>/assets/images/no-image.png';
            }
            
            document.getElementById('detailModal').classList.add('active');
        }

        function closeDetailModal() {
            document.getElementById('detailModal').classList.remove('active');
        }

        function openEditModal(product) {
            try {
                document.getElementById('edit_product_id').value = product.id;
                document.getElementById('edit_name').value = product.name;
                document.getElementById('edit_category_id').value = product.category_id;
                document.getElementById('edit_status').value = product.status || 'active';
                document.getElementById('edit_price').value = product.price;
                document.getElementById('edit_description').value = product.description || '';
                
                const editPreview = document.getElementById('editImagePreview');
                const editPreviewImg = document.getElementById('editPreviewImg');
                if (product.image) {
                    editPreviewImg.src = '<?= BASE_URL ?>/' + product.image;
                    editPreview.style.display = 'block';
                } else {
                    editPreview.style.display = 'none';
                }
                
                clearFormErrors('edit');
                document.getElementById('editModal').classList.add('active');
            } catch (error) {
                console.error('Error opening edit modal:', error);
                console.log('Product data:', product);
            }
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
            clearFormErrors('edit');
        }

        // Validation functions
        function clearFormErrors(type) {
            const prefix = type === 'add' ? 'add_' : 'edit_';
            
            ['name', 'category_id', 'price', 'stock'].forEach(field => {
                const input = document.getElementById(prefix + field);
                const error = document.getElementById(prefix + field + '_error');
                if (input) {
                    input.classList.remove('error');
                }
                if (error) {
                    error.classList.remove('show');
                }
            });
        }

        function validateForm(type) {
            const prefix = type === 'add' ? 'add_' : 'edit_';
            let isValid = true;
            
            // Clear previous errors
            clearFormErrors(type);
            
            // Validate name
            const name = document.getElementById(prefix + 'name');
            if (!name.value.trim()) {
                showError(prefix + 'name');
                isValid = false;
            }
            
            // Validate category
            const category = document.getElementById(prefix + 'category_id');
            if (!category.value) {
                showError(prefix + 'category_id');
                isValid = false;
            }
            
            // Validate price
            const price = document.getElementById(prefix + 'price');
            if (price.value === '' || parseFloat(price.value) < 0) {
                showError(prefix + 'price');
                isValid = false;
            }
            
            // Validate stock (only for add form)
            if (type === 'add') {
                const stock = document.getElementById(prefix + 'stock');
                if (stock && (stock.value === '' || parseInt(stock.value) < 0)) {
                    showError(prefix + 'stock');
                    isValid = false;
                }
            }
            
            return isValid;
        }

        function showError(fieldId) {
            const input = document.getElementById(fieldId);
            const error = document.getElementById(fieldId + '_error');
            
            if (input) {
                input.classList.add('error');
            }
            if (error) {
                error.classList.add('show');
            }
        }

        // Form submit handlers
        document.addEventListener('DOMContentLoaded', function() {
            const addForm = document.getElementById('addProductForm');
            const editForm = document.getElementById('editProductForm');
            
            if (addForm) {
                addForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    if (validateForm('add')) {
                        this.submit();
                    }
                });
            }
            
            if (editForm) {
                editForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    if (validateForm('edit')) {
                        this.submit();
                    }
                });
            }
        });

        function deleteProduct(productId, productName) {
            confirmDelete({
                title: 'Xóa sản phẩm',
                message: `Bạn có chắc chắn muốn xóa sản phẩm "<strong>${productName}</strong>"?<br><br>Hành động này <strong>KHÔNG THỂ HOÀN TÁC!</strong>`,
                confirmText: 'Xóa sản phẩm',
                theme: 'admin'
            }).then(confirmed => {
                if (confirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '<?= BASE_URL ?>/admin/products-delete';
                    
                    const productIdInput = document.createElement('input');
                    productIdInput.type = 'hidden';
                    productIdInput.name = 'product_id';
                    productIdInput.value = productId;
                    
                    form.appendChild(productIdInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function previewAddImage(event) {
            const preview = document.getElementById('addImagePreview');
            const previewImg = document.getElementById('addPreviewImg');
            const file = event.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        }

        function previewEditImage(event) {
            const preview = document.getElementById('editImagePreview');
            const previewImg = document.getElementById('editPreviewImg');
            const file = event.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        }

        // Close modal on outside click
        window.onclick = function(event) {
            const addModal = document.getElementById('addModal');
            const editModal = document.getElementById('editModal');
            if (event.target === addModal) {
                closeAddModal();
            }
            if (event.target === editModal) {
                closeEditModal();
            }
        }
    </script>
    <?php include APP_PATH . '/views/layouts/toast_notification.php'; ?>
</body>
</html>
