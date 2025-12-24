<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($slider) ? 'Sửa' : 'Thêm' ?> Slider - Admin</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin-slider-form.css">
</head>
<body>
    <?php include APP_PATH . '/views/layouts/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <h2>
                <i class="fas fa-<?= isset($slider) ? 'edit' : 'plus' ?>"></i> 
                <?= isset($slider) ? 'Sửa' : 'Thêm' ?> Slider
            </h2>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <strong><?php $user = Session::getUser(); echo htmlspecialchars($user['full_name'] ?? 'Admin'); ?></strong>
            </div>
        </div>
            
            <?php if (Session::hasFlash('error')): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= Session::getFlash('error') ?>
                </div>
            <?php endif; ?>
            
            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                <strong>Lưu ý:</strong> Kích thước ảnh khuyến nghị: 1920x600px (hoặc tỷ lệ 16:5). 
                Dung lượng tối đa: 5MB. Định dạng: JPG, PNG, GIF, WEBP.
            </div>
            
            <form method="POST" enctype="multipart/form-data" class="form-container">
                <!-- Thông tin cơ bản -->
                <div class="form-section">
                    <h3><i class="fas fa-info-circle"></i> Thông tin cơ bản</h3>
                    
                    <div class="form-group">
                        <label>Tiêu đề <span class="required">*</span></label>
                        <input type="text" 
                               name="title" 
                               placeholder="VD: Hoa Tươi Đẹp Cho Mọi Dịp"
                               value="<?= htmlspecialchars($slider['title'] ?? '') ?>"
                               required>
                        <small>Tiêu đề chính hiển thị trên slider</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Mô tả</label>
                        <textarea name="description" 
                                  placeholder="Nhập mô tả ngắn về slider..."><?= htmlspecialchars($slider['description'] ?? '') ?></textarea>
                        <small>Mô tả chi tiết về nội dung slider</small>
                    </div>
                </div>
                
                <!-- Hình ảnh -->
                <div class="form-section">
                    <h3><i class="fas fa-image"></i> Hình ảnh</h3>
                    
                    <?php if (isset($slider) && !empty($slider['image'])): ?>
                        <div class="current-image">
                            <p><strong>Ảnh hiện tại:</strong></p>
                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($slider['image']) ?>" 
                                 alt="Current image">
                            <p style="margin-top: 10px; color: #666;">
                                <i class="fas fa-info-circle"></i> 
                                Tải lên ảnh mới nếu muốn thay đổi
                            </p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>
                            <?= isset($slider) ? 'Ảnh mới (không bắt buộc)' : 'Ảnh slider' ?> 
                            <?= !isset($slider) ? '<span class="required">*</span>' : '' ?>
                        </label>
                        <div class="image-upload-area" id="uploadArea">
                            <div class="image-upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="image-upload-text">
                                <strong>Kéo thả ảnh vào đây</strong> hoặc click để chọn
                            </div>
                            <div class="image-upload-hint">
                                Hỗ trợ: JPG, PNG, GIF, WEBP (tối đa 5MB)
                            </div>
                        </div>
                        <input type="file" 
                               id="imageInput" 
                               name="image" 
                               accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                               <?= !isset($slider) ? 'required' : '' ?>>
                        
                        <div class="image-preview-container" id="previewContainer">
                            <img id="imagePreview" class="image-preview" alt="Preview">
                        </div>
                    </div>
                </div>
                
                <!-- Link và CTA -->
                <div class="form-section">
                    <h3><i class="fas fa-link"></i> Liên kết & CTA</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Đường dẫn</label>
                            <input type="text" 
                                   name="link" 
                                   placeholder="VD: /products hoặc https://example.com"
                                   value="<?= htmlspecialchars($slider['link'] ?? '') ?>">
                            <small>
                                <strong>Cách viết:</strong><br>
                                • Trang nội bộ: <code>/products</code>, <code>/news</code>, <code>/pages/lien-he</code><br>
                                • Link đầy đủ: <code>https://google.com</code><br>
                                • Để trống nếu không cần link
                            </small>
                        </div>
                        
                        <div class="form-group">
                            <label>Text nút CTA</label>
                            <input type="text" 
                                   name="button_text" 
                                   placeholder="VD: Xem Sản Phẩm"
                                   value="<?= htmlspecialchars($slider['button_text'] ?? '') ?>">
                            <small>Text hiển thị trên nút kêu gọi hành động</small>
                        </div>
                    </div>
                </div>
                
                <!-- Cài đặt hiển thị -->
                <div class="form-section">
                    <h3><i class="fas fa-cog"></i> Cài đặt hiển thị</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Thứ tự hiển thị</label>
                            <input type="number" 
                                   name="display_order" 
                                   min="0" 
                                   value="<?= htmlspecialchars($slider['display_order'] ?? 0) ?>">
                            <small>Số thứ tự càng nhỏ sẽ hiển thị trước</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Trạng thái</label>
                            <div class="checkbox-group">
                                <input type="checkbox" 
                                       name="is_active" 
                                       id="is_active"
                                       <?= (!isset($slider) || $slider['is_active']) ? 'checked' : '' ?>>
                                <label for="is_active">Hiển thị slider này</label>
                            </div>
                            <small>Bỏ chọn để tạm ẩn slider</small>
                        </div>
                    </div>
                </div>
                
                <!-- Buttons -->
                <div class="btn-group">
                    <a href="<?= BASE_URL ?>/sliders" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Hủy bỏ
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> 
                        <?= isset($slider) ? 'Cập nhật' : 'Tạo mới' ?>
                    </button>
                </div>
            </form>
        </main>
    </div>
    
    <script>
        // Image upload handling
        const uploadArea = document.getElementById('uploadArea');
        const imageInput = document.getElementById('imageInput');
        const previewContainer = document.getElementById('previewContainer');
        const imagePreview = document.getElementById('imagePreview');
        
        // Click to select file
        uploadArea.addEventListener('click', () => {
            imageInput.click();
        });
        
        // File input change
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                handleFile(file);
            }
        });
        
        // Drag and drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const file = e.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                // Create a new FileList with the dropped file
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                imageInput.files = dataTransfer.files;
                
                handleFile(file);
            } else {
                alert('Vui lòng chọn file ảnh!');
            }
        });
        
        function handleFile(file) {
            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('Kích thước file không được vượt quá 5MB!');
                imageInput.value = '';
                return;
            }
            
            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                alert('Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WEBP)!');
                imageInput.value = '';
                return;
            }
            
            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                previewContainer.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    </script>
</body>
</html>
