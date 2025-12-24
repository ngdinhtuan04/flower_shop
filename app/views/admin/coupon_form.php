<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($coupon) ? 'Sửa' : 'Thêm' ?> Mã giảm giá - Admin</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin-coupon-form.css">
</head>
<body>
    <?php 
    $user = Session::getUser();
    ?>
    <?php include APP_PATH . '/views/layouts/admin_sidebar.php'; ?>
    
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <h2><?= isset($coupon) ? 'Sửa' : 'Thêm' ?> Mã giảm giá</h2>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <strong><?= htmlspecialchars($user['full_name']) ?></strong>
            </div>
        </div>

        <div class="content-wrapper">


            <div class="form-container">
                <form method="POST" action="<?= BASE_URL ?>/coupons/<?= isset($coupon) ? 'edit/' . $coupon['id'] : 'create' ?>">
                    
                    <!-- Thông tin cơ bản -->
                    <div class="form-section">
                        <h3><i class="fas fa-info-circle"></i> Thông tin cơ bản</h3>
                        
                        <div class="form-group">
                            <label>Mã giảm giá <span class="required">*</span></label>
                            <input type="text" name="code" required 
                                   value="<?= isset($coupon) ? htmlspecialchars($coupon['code']) : '' ?>"
                                   placeholder="VD: SUMMER2025"
                                   style="text-transform: uppercase;">
                            <small>Mã phải là chữ in hoa, không dấu, có thể có số và gạch ngang</small>
                        </div>

                        <div class="form-group">
                            <label>Mô tả</label>
                            <textarea name="description" placeholder="Mô tả chi tiết về mã giảm giá..."><?= isset($coupon) ? htmlspecialchars($coupon['description']) : '' ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="checkbox-group">
                                <?php 
                                // Map status to is_active for checkbox
                                $isActiveChecked = false;
                                if (isset($coupon)) {
                                    $isActiveChecked = (isset($coupon['status']) && $coupon['status'] === 'active');
                                } else {
                                    $isActiveChecked = true; // Default checked for new coupon
                                }
                                ?>
                                <input type="checkbox" name="is_active" id="is_active" 
                                       <?= $isActiveChecked ? 'checked' : '' ?>>
                                <label for="is_active">Kích hoạt ngay</label>
                            </div>
                        </div>
                    </div>

                    <!-- Thiết lập giảm giá -->
                    <div class="form-section">
                        <h3><i class="fas fa-percent"></i> Thiết lập giảm giá</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Áp dụng cho <span class="required">*</span></label>
                                <select name="apply_to" id="apply_to" required>
                                    <?php 
                                    $applyTo = isset($coupon['apply_to']) ? $coupon['apply_to'] : 'product';
                                    ?>
                                    <option value="product" <?= $applyTo == 'product' ? 'selected' : '' ?>>
                                        🛍️ Giảm giá sản phẩm
                                    </option>
                                    <option value="shipping" <?= $applyTo == 'shipping' ? 'selected' : '' ?>>
                                        🚚 Giảm phí vận chuyển
                                    </option>
                                    <option value="all" <?= $applyTo == 'all' ? 'selected' : '' ?>>
                                        🎁 Cả hai (Sản phẩm + Ship)
                                    </option>
                                </select>
                                <small>Chọn loại giảm giá muốn áp dụng</small>
                            </div>
                            
                            <div class="form-group">
                                <label>Loại giảm giá <span class="required">*</span></label>
                                <select name="discount_type" id="discount_type" required>
                                    <?php 
                                    $discountType = '';
                                    if (isset($coupon) && isset($coupon['discount_type'])) {
                                        // Map 'percent' to 'percentage' for consistency
                                        $discountType = ($coupon['discount_type'] === 'percent') ? 'percentage' : $coupon['discount_type'];
                                    }
                                    ?>
                                    <option value="percentage" <?= $discountType == 'percentage' ? 'selected' : '' ?>>
                                        Phần trăm (%)
                                    </option>
                                    <option value="fixed" <?= $discountType == 'fixed' ? 'selected' : '' ?>>
                                        Số tiền cố định (đ)
                                    </option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Giá trị giảm <span class="required">*</span></label>
                                <input type="number" name="discount_value" id="discount_value" required 
                                       min="0" step="0.01"
                                       value="<?= isset($coupon) && isset($coupon['discount_value']) ? $coupon['discount_value'] : '' ?>"
                                       placeholder="VD: 20">
                                <small id="discount_help">Nhập % giảm (1-100)</small>
                            </div>
                            
                            <div class="form-group">
                                <label>Giá trị đơn hàng tối thiểu</label>
                                <input type="number" name="min_order_value" 
                                       min="0" step="1000"
                                       value="<?= isset($coupon) && isset($coupon['min_order_value']) ? $coupon['min_order_value'] : 0 ?>"
                                       placeholder="0">
                                <small>Đơn hàng phải đạt giá trị tối thiểu này. 0 = không giới hạn</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Giảm tối đa</label>
                                <input type="number" name="max_discount" 
                                       min="0" step="1000"
                                       value="<?= isset($coupon) && isset($coupon['max_discount']) && $coupon['max_discount'] ? $coupon['max_discount'] : '' ?>"
                                       placeholder="Không giới hạn">
                                <small>Áp dụng cho loại %. Để trống = không giới hạn</small>
                            </div>
                        </div>
                    </div>

                    <!-- Giới hạn sử dụng -->
                    <div class="form-section">
                        <h3><i class="fas fa-users"></i> Giới hạn sử dụng</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Tổng số lần sử dụng</label>
                                <input type="number" name="usage_limit" 
                                       min="1" 
                                       value="<?= isset($coupon) && isset($coupon['usage_limit']) && $coupon['usage_limit'] ? $coupon['usage_limit'] : '' ?>"
                                       placeholder="Không giới hạn">
                                <small>Tổng số lần mã có thể được sử dụng. Để trống = không giới hạn</small>
                            </div>
                        </div>

                        <?php if (isset($coupon) && isset($coupon['used_count'])): ?>
                        <div class="info-box">
                            <i class="fas fa-info-circle"></i>
                            Đã sử dụng: <strong><?= $coupon['used_count'] ?></strong> lần
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Thời gian áp dụng -->
                    <div class="form-section">
                        <h3><i class="fas fa-calendar-alt"></i> Thời gian áp dụng</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Ngày bắt đầu <span class="required">*</span></label>
                                <input type="datetime-local" name="start_date" required
                                       value="<?= isset($coupon) && isset($coupon['valid_from']) ? date('Y-m-d\TH:i', strtotime($coupon['valid_from'])) : '' ?>">
                            </div>

                            <div class="form-group">
                                <label>Ngày kết thúc <span class="required">*</span></label>
                                <input type="datetime-local" name="end_date" required
                                       value="<?= isset($coupon) && isset($coupon['valid_to']) ? date('Y-m-d\TH:i', strtotime($coupon['valid_to'])) : '' ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="btn-group">
                        <a href="<?= BASE_URL ?>/coupons" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?= isset($coupon) ? 'Cập nhật' : 'Tạo mã' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Update discount help text based on type
        document.getElementById('discount_type').addEventListener('change', function() {
            const helpText = document.getElementById('discount_help');
            const valueInput = document.getElementById('discount_value');
            
            if (this.value === 'percentage') {
                helpText.textContent = 'Nhập % giảm (1-100)';
                valueInput.max = 100;
                valueInput.placeholder = 'VD: 20';
            } else {
                helpText.textContent = 'Nhập số tiền giảm (đồng)';
                valueInput.removeAttribute('max');
                valueInput.placeholder = 'VD: 100000';
            }
        });

        // Trigger on load
        document.getElementById('discount_type').dispatchEvent(new Event('change'));

        // Auto uppercase code
        document.querySelector('input[name="code"]').addEventListener('input', function() {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9-]/g, '');
        });
    </script>
    <?php include APP_PATH . '/views/layouts/toast_notification.php'; ?>
</body>
</html>
