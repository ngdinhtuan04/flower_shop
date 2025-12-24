<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Địa chỉ của tôi - Flower Shop</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/home.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/addresses.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/notifications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include APP_PATH . '/views/layouts/header.php'; ?>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="<?= BASE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a>
        <span class="separator">/</span>
        <a href="<?= BASE_URL ?>/user/profile">Tài khoản</a>
        <span class="separator">/</span>
        <span class="current">Địa chỉ của tôi</span>
    </div>

<div class="address-page">
    <div class="address-header">
        <h1><i class="fas fa-map-marker-alt"></i> Địa chỉ của tôi</h1>
        <button class="btn-add-address" onclick="openAddModal()">
            <i class="fas fa-plus"></i> Thêm địa chỉ mới
        </button>
    </div>

    <?php if (empty($addresses)): ?>
        <div class="empty-state">
            <i class="fas fa-map-marked-alt"></i>
            <h3>Chưa có địa chỉ nào</h3>
            <p>Thêm địa chỉ để việc đặt hàng trở nên nhanh chóng và thuận tiện hơn</p>
            <button class="btn-add-address" onclick="openAddModal()">
                <i class="fas fa-plus"></i> Thêm địa chỉ đầu tiên
            </button>
        </div>
    <?php else: ?>
        <div class="address-list" id="addressList">
            <?php foreach ($addresses as $addr): ?>
                <div class="address-card <?= $addr['is_default'] ? 'default' : '' ?>" data-id="<?= $addr['id'] ?>">
                    <div class="address-header">
                        <span class="address-type <?= $addr['address_type'] ?>">
                            <i class="fas <?= UserAddress::getTypeIcon($addr['address_type']) ?>"></i>
                            <?= UserAddress::getTypeLabel($addr['address_type']) ?>
                        </span>
                        <?php if ($addr['is_default']): ?>
                            <span class="default-badge">
                                <i class="fas fa-check-circle"></i> Mặc định
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="address-info">
                        <div class="recipient-info">
                            <div class="recipient-name">
                                <i class="fas fa-user"></i>
                                <?= htmlspecialchars($addr['recipient_name']) ?>
                            </div>
                            <div class="recipient-phone">
                                <i class="fas fa-phone"></i>
                                <?= htmlspecialchars($addr['phone']) ?>
                            </div>
                        </div>
                        <div class="full-address">
                            <i class="fas fa-map-marker-alt" style="color: #ff69b4;"></i>
                            <?= htmlspecialchars(UserAddress::formatFullAddress($addr)) ?>
                        </div>
                    </div>

                    <div class="address-actions">
                        <?php if (!$addr['is_default']): ?>
                            <button class="btn-action btn-set-default" onclick="setDefault(<?= $addr['id'] ?>)">
                                <i class="fas fa-check"></i> Đặt mặc định
                            </button>
                        <?php endif; ?>
                        <button class="btn-action" onclick="editAddress(<?= $addr['id'] ?>)">
                            <i class="fas fa-edit"></i> Sửa
                        </button>
                        <button class="btn-action btn-delete" onclick="deleteAddress(<?= $addr['id'] ?>)">
                            <i class="fas fa-trash"></i> Xóa
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Thêm/Sửa Địa Chỉ -->
<div class="modal" id="addressModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Thêm địa chỉ mới</h2>
            <button class="close-modal" onclick="closeModal()">&times;</button>
        </div>
        <form id="addressForm">
            <input type="hidden" id="addressId" name="address_id">
            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Tên người nhận <span class="required">*</span></label>
                        <input type="text" id="recipientName" name="recipient_name" placeholder="Nhập tên người nhận" required>
                    </div>
                    <div class="form-group">
                        <label>Số điện thoại <span class="required">*</span></label>
                        <input type="tel" id="phone" name="phone" placeholder="Nhập số điện thoại" pattern="0[0-9]{9}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Tỉnh/Thành phố <span class="required">*</span></label>
                    <select id="province" name="province" required>
                        <option value="">Đang tải...</option>
                    </select>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Quận/Huyện <span class="required">*</span></label>
                        <select id="district" name="district" required>
                            <option value="">-- Chọn Quận/Huyện --</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Phường/Xã <span class="required">*</span></label>
                        <select id="ward" name="ward" required>
                            <option value="">-- Chọn Phường/Xã --</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Địa chỉ chi tiết <span class="required">*</span></label>
                    <textarea id="addressDetail" name="address_detail" rows="3" placeholder="Số nhà, tên đường..." required></textarea>
                </div>

                <div class="form-group">
                    <label>Loại địa chỉ</label>
                    <div class="address-type-selector">
                        <label class="type-option active">
                            <input type="radio" name="address_type" value="home" checked>
                            <i class="fas fa-home"></i> Nhà riêng
                        </label>
                        <label class="type-option">
                            <input type="radio" name="address_type" value="office">
                            <i class="fas fa-building"></i> Văn phòng
                        </label>
                    </div>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="isDefault" name="is_default">
                    <label for="isDefault" style="margin: 0; font-weight: normal;">Đặt làm địa chỉ mặc định</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal()">Hủy</button>
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Lưu địa chỉ
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal" id="confirmModal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h2 id="confirmTitle">Xác nhận</h2>
            <button class="close-modal" onclick="closeConfirmModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p id="confirmMessage" style="margin: 0; color: #666; line-height: 1.6;"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeConfirmModal()">Hủy</button>
            <button type="button" class="btn-submit" id="confirmButton">Xác nhận</button>
        </div>
    </div>
</div>

<script src="<?= ASSETS_URL ?>/js/addresses.js"></script>

<?php include APP_PATH . '/views/layouts/footer.php'; ?>
<?php include APP_PATH . '/views/layouts/toast_notification.php'; ?>
</body>
</html>
