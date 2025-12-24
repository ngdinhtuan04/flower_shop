<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($promotion) ? 'Sửa' : 'Thêm' ?> Khuyến mãi - Admin</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin-promotion-form.css">
</head>
<body>
    <?php 
    $user = Session::getUser();
    ?>
    <?php include APP_PATH . '/views/layouts/admin_sidebar.php'; ?>
    
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <h2><?= isset($promotion) ? 'Sửa' : 'Thêm' ?> Khuyến mãi</h2>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <strong><?= htmlspecialchars($user['full_name']) ?></strong>
            </div>
        </div>

        <div class="content-wrapper">


            <div class="form-container">
                <form method="POST" id="promotionForm">
                    <!-- Thông tin cơ bản -->
                    <div class="form-section">
                        <h3><i class="fas fa-info-circle"></i> Thông tin cơ bản</h3>
                        
                        <div class="form-group">
                            <label>Tên khuyến mãi <span class="required">*</span></label>
                            <input type="text" 
                                   name="name" 
                                   id="name"
                                   value="<?= htmlspecialchars($promotion['name'] ?? $old['name'] ?? '') ?>" 
                                   placeholder="VD: Giảm giá 20% cho tất cả sản phẩm">
                            <div class="error-message" id="name-error"></div>
                        </div>

                        <div class="form-group">
                            <label>Mô tả</label>
                            <textarea name="description" 
                                      placeholder="Mô tả chi tiết về chương trình khuyến mãi..."><?= htmlspecialchars($promotion['description'] ?? $old['description'] ?? '') ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>
                                    Độ ưu tiên <span class="required">*</span>
                                    <i class="fas fa-info-circle" title="Số càng lớn = ưu tiên càng cao. Dùng khi có nhiều khuyến mãi cùng lúc" style="color: #667eea; cursor: help;"></i>
                                </label>
                                <input type="number" 
                                       name="priority" 
                                       id="priority"
                                       value="<?= $promotion['priority'] ?? $old['priority'] ?? 0 ?>" 
                                       min="0">
                                <small>Mặc định = 0. Số càng lớn = ưu tiên càng cao khi có nhiều khuyến mãi áp dụng cho cùng sản phẩm</small>
                                <div class="error-message" id="priority-error"></div>
                            </div>

                            <div class="form-group">
                                <div class="checkbox-group">
                                    <input type="checkbox" 
                                           name="is_active" 
                                           id="is_active" 
                                           <?= (isset($promotion) ? $promotion['is_active'] : ($old['is_active'] ?? true)) ? 'checked' : '' ?>>
                                    <label for="is_active">Kích hoạt ngay</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cấu hình giảm giá -->
                    <div class="form-section">
                        <h3><i class="fas fa-percent"></i> Cấu hình giảm giá</h3>
                        
                        <div class="form-group">
                            <label>Loại giảm giá <span class="required">*</span></label>
                            <div class="radio-group">
                                <div class="radio-item">
                                    <input type="radio" 
                                           name="discount_type" 
                                           id="type_percentage" 
                                           value="percentage" 
                                           <?= (isset($promotion) ? $promotion['discount_type'] : ($old['discount_type'] ?? 'percentage')) == 'percentage' ? 'checked' : '' ?>
                                           onchange="toggleDiscountType()">
                                    <label for="type_percentage">Phần trăm (%)</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" 
                                           name="discount_type" 
                                           id="type_fixed" 
                                           value="fixed" 
                                           <?= (isset($promotion) ? $promotion['discount_type'] : ($old['discount_type'] ?? 'percentage')) == 'fixed' ? 'checked' : '' ?>
                                           onchange="toggleDiscountType()">
                                    <label for="type_fixed">Số tiền cố định (đ)</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Giá trị giảm <span class="required">*</span></label>
                                <input type="number" 
                                       name="discount_value" 
                                       id="discount_value"
                                       value="<?= $promotion['discount_value'] ?? $old['discount_value'] ?? '' ?>" 
                                       step="0.01"
                                       min="0">
                                <small id="discount_hint">Nhập giá trị phần trăm (0-100)</small>
                                <div class="error-message" id="discount_value-error"></div>
                            </div>

                            <div class="form-group" id="max_discount_group">
                                <label>Số tiền giảm tối đa (đ)</label>
                                <input type="number" 
                                       name="max_discount_amount" 
                                       value="<?= $promotion['max_discount_amount'] ?? $old['max_discount_amount'] ?? '' ?>" 
                                       min="0">
                                <small>Để trống nếu không giới hạn</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Giá trị đơn hàng tối thiểu (đ)</label>
                            <input type="number" 
                                   name="min_order_amount" 
                                   value="<?= $promotion['min_order_amount'] ?? $old['min_order_amount'] ?? 0 ?>" 
                                   min="0">
                            <small>Đơn hàng phải đạt giá trị tối thiểu này để áp dụng khuyến mãi</small>
                        </div>
                    </div>

                    <!-- Phạm vi áp dụng -->
                    <div class="form-section">
                        <h3><i class="fas fa-bullseye"></i> Phạm vi áp dụng</h3>
                        
                        <div class="form-group">
                            <label>Áp dụng cho <span class="required">*</span></label>
                            <div class="radio-group">
                                <div class="radio-item">
                                    <input type="radio" 
                                           name="apply_to" 
                                           id="apply_all" 
                                           value="all" 
                                           <?= (isset($promotion) ? $promotion['apply_to'] : ($old['apply_to'] ?? 'all')) == 'all' ? 'checked' : '' ?>
                                           onchange="toggleApplyTo()">
                                    <label for="apply_all">Tất cả sản phẩm</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" 
                                           name="apply_to" 
                                           id="apply_category" 
                                           value="category" 
                                           <?= (isset($promotion) ? $promotion['apply_to'] : ($old['apply_to'] ?? 'all')) == 'category' ? 'checked' : '' ?>
                                           onchange="toggleApplyTo()">
                                    <label for="apply_category">Theo danh mục</label>
                                </div>
                                <div class="radio-item">
                                    <input type="radio" 
                                           name="apply_to" 
                                           id="apply_product" 
                                           value="product" 
                                           <?= (isset($promotion) ? $promotion['apply_to'] : ($old['apply_to'] ?? 'all')) == 'product' ? 'checked' : '' ?>
                                           onchange="toggleApplyTo()">
                                    <label for="apply_product">Sản phẩm cụ thể</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group hidden" id="category_select_group">
                            <label>Chọn danh mục <span class="required">*</span></label>
                            <select name="category_id" id="category_select">
                                <option value="">-- Chọn danh mục --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" 
                                            <?= (isset($promotion) && $promotion['category_id'] == $cat['id']) || (isset($old['category_id']) && $old['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group hidden" id="product_select_group">
                            <label>Chọn sản phẩm <span class="required">*</span></label>
                            <select name="product_ids[]" id="product_select" multiple="multiple" class="select2">
                                <?php foreach ($products as $prod): ?>
                                    <?php 
                                        $selected = false;
                                        if (isset($promotion_products)) {
                                            foreach ($promotion_products as $pp) {
                                                if ($pp['product_id'] == $prod['id']) {
                                                    $selected = true;
                                                    break;
                                                }
                                            }
                                        }
                                    ?>
                                    <option value="<?= $prod['id'] ?>" <?= $selected ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($prod['name']) ?> - <?= number_format($prod['price']) ?>đ
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small>Giữ Ctrl (hoặc Cmd) để chọn nhiều sản phẩm</small>
                        </div>
                    </div>

                    <!-- Thời gian và giới hạn -->
                    <div class="form-section">
                        <h3><i class="fas fa-clock"></i> Thời gian và giới hạn</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Ngày bắt đầu <span class="required">*</span></label>
                                <input type="datetime-local" 
                                       name="start_date" 
                                       value="<?= isset($promotion['start_date']) ? date('Y-m-d\TH:i', strtotime($promotion['start_date'])) : ($old['start_date'] ?? '') ?>" 
                                       required>
                            </div>

                            <div class="form-group">
                                <label>Ngày kết thúc <span class="required">*</span></label>
                                <input type="datetime-local" 
                                       name="end_date" 
                                       value="<?= isset($promotion['end_date']) ? date('Y-m-d\TH:i', strtotime($promotion['end_date'])) : ($old['end_date'] ?? '') ?>" 
                                       required>
                            </div>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="btn-group">
                        <a href="<?= BASE_URL ?>/promotions" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?= isset($promotion) ? 'Cập nhật' : 'Thêm mới' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('#product_select').select2({
                placeholder: 'Tìm và chọn sản phẩm...',
                allowClear: true
            });

            // Initialize visibility
            toggleDiscountType();
            toggleApplyTo();
        });

        function toggleDiscountType() {
            const isPercentage = document.getElementById('type_percentage').checked;
            const maxDiscountGroup = document.getElementById('max_discount_group');
            const discountHint = document.getElementById('discount_hint');
            const discountValue = document.getElementById('discount_value');

            if (isPercentage) {
                maxDiscountGroup.style.display = 'block';
                discountHint.textContent = 'Nhập giá trị phần trăm (0-100)';
                discountValue.max = 100;
            } else {
                maxDiscountGroup.style.display = 'none';
                discountHint.textContent = 'Nhập số tiền giảm (VNĐ)';
                discountValue.removeAttribute('max');
            }
        }

        function toggleApplyTo() {
            const applyTo = document.querySelector('input[name="apply_to"]:checked').value;
            const categoryGroup = document.getElementById('category_select_group');
            const productGroup = document.getElementById('product_select_group');
            const categorySelect = document.getElementById('category_select');
            const productSelect = document.getElementById('product_select');

            // Hide all
            categoryGroup.classList.add('hidden');
            productGroup.classList.add('hidden');
            
            // Remove required
            categorySelect.removeAttribute('required');
            productSelect.removeAttribute('required');

            if (applyTo === 'category') {
                categoryGroup.classList.remove('hidden');
                categorySelect.setAttribute('required', 'required');
            } else if (applyTo === 'product') {
                productGroup.classList.remove('hidden');
                productSelect.setAttribute('required', 'required');
            }
        }

        // Clear form errors
        function clearFormErrors() {
            document.querySelectorAll('.error-message').forEach(el => {
                el.classList.remove('show');
                el.textContent = '';
            });
            document.querySelectorAll('.error').forEach(el => {
                el.classList.remove('error');
            });
        }

        // Show error message
        function showError(fieldId, message) {
            const field = document.getElementById(fieldId);
            const errorEl = document.getElementById(fieldId + '-error');
            if (field) field.classList.add('error');
            if (errorEl) {
                errorEl.textContent = message;
                errorEl.classList.add('show');
            }
        }

        // Form validation
        function validateForm() {
            clearFormErrors();
            let isValid = true;

            // Validate name
            const name = document.getElementById('name');
            if (!name.value.trim()) {
                showError('name', 'Vui lòng nhập tên khuyến mãi');
                isValid = false;
            }

            // Validate priority
            const priority = document.getElementById('priority');
            if (priority.value === '' || priority.value < 0) {
                showError('priority', 'Vui lòng nhập độ ưu tiên (>= 0)');
                isValid = false;
            }

            // Validate discount value
            const discountValue = document.getElementById('discount_value');
            const discountType = document.querySelector('input[name="discount_type"]:checked').value;
            
            if (!discountValue.value || discountValue.value <= 0) {
                showError('discount_value', 'Vui lòng nhập giá trị giảm');
                isValid = false;
            } else if (discountType === 'percentage' && parseFloat(discountValue.value) > 100) {
                showError('discount_value', 'Giá trị phần trăm không được vượt quá 100%');
                isValid = false;
            }

            // Validate dates
            const startDate = new Date(document.querySelector('input[name="start_date"]').value);
            const endDate = new Date(document.querySelector('input[name="end_date"]').value);
            
            if (!document.querySelector('input[name="start_date"]').value) {
                alert('Vui lòng chọn ngày bắt đầu');
                isValid = false;
            } else if (!document.querySelector('input[name="end_date"]').value) {
                alert('Vui lòng chọn ngày kết thúc');
                isValid = false;
            } else if (endDate <= startDate) {
                alert('Ngày kết thúc phải sau ngày bắt đầu');
                isValid = false;
            }

            // Validate apply to
            const applyTo = document.querySelector('input[name="apply_to"]:checked').value;
            
            if (applyTo === 'category') {
                const categoryId = document.getElementById('category_select').value;
                if (!categoryId) {
                    alert('Vui lòng chọn danh mục sản phẩm');
                    isValid = false;
                }
            } else if (applyTo === 'product') {
                const productIds = $('#product_select').val();
                if (!productIds || productIds.length === 0) {
                    alert('Vui lòng chọn ít nhất một sản phẩm');
                    isValid = false;
                }
            }

            return isValid;
        }

        // Form submit handler
        document.getElementById('promotionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateForm()) {
                this.submit();
            }
        });
    </script>
    <?php include APP_PATH . '/views/layouts/toast_notification.php'; ?>
</body>
</html>
