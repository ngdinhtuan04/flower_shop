<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Tin tức - Admin</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin-news.css">
    <script src="<?= ASSETS_URL ?>/js/confirm-dialog.js"></script>
</head>
<body>
    <div class="admin-container">
        <?php include APP_PATH . '/views/layouts/admin_sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Topbar -->
            <div class="topbar">
                <h2>Quản lý Tin tức</h2>
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <strong><?= htmlspecialchars($user['full_name']) ?></strong>
                </div>
            </div>

            <!-- Content -->
            <div class="content">


                <!-- Search and Filter Bar -->
                <form method="GET" action="<?= BASE_URL ?>/admin/news" id="filterForm">
                    <div class="filter-bar">
                        <input type="text" name="search" id="searchInput" placeholder="Tìm kiếm theo tiêu đề, nội dung..." 
                               value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                        
                        <select name="status">
                            <option value="">Tất cả trạng thái</option>
                            <option value="draft" <?= ($filters['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Nháp</option>
                            <option value="published" <?= ($filters['status'] ?? '') === 'published' ? 'selected' : '' ?>>Đã xuất bản</option>
                            <option value="archived" <?= ($filters['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Đã lưu trữ</option>
                        </select>
                        <select name="category">
                            <option value="">Tất cả danh mục</option>
                            <option value="tips" <?= ($filters['category'] ?? '') === 'tips' ? 'selected' : '' ?>>Mẹo hay</option>
                            <option value="events" <?= ($filters['category'] ?? '') === 'events' ? 'selected' : '' ?>>Sự kiện</option>
                            <option value="promotion" <?= ($filters['category'] ?? '') === 'promotion' ? 'selected' : '' ?>>Khuyến mãi</option>
                        </select>
                        <select name="author_id">
                            <option value="">Tất cả tác giả</option>
                            <?php foreach ($authors as $author): ?>
                                <option value="<?= $author['id'] ?>" <?= ($filters['author_id'] ?? '') == $author['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($author['full_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="btn-add" onclick="openAddModal()">
                            <i class="fas fa-plus"></i> Thêm tin tức
                        </button>
                    </div>
                </form>

                <!-- Table -->
                <div class="news-table" id="tableContainer">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Hình ảnh</th>
                                <th>Tiêu đề</th>
                                <th>Danh mục</th>
                                <th>Tác giả</th>
                                <th>Trạng thái</th>
                                <th>Lượt xem</th>
                                <th>Ngày tạo</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($news)): ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 30px; color: #9ca3af;">
                                        <i class="fas fa-inbox" style="font-size: 48px; display: block; margin-bottom: 10px;"></i>
                                        Chưa có tin tức nào
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($news as $item): ?>
                                    <tr>
                                        <td><?= $item['id'] ?></td>
                                        <td>
                                            <?php if ($item['image']): ?>
                                                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($item['image']) ?>" 
                                                     alt="News Image" class="news-image">
                                            <?php else: ?>
                                                <div style="width: 80px; height: 60px; background: #e5e7eb; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-image" style="color: #9ca3af;"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="news-title"><?= htmlspecialchars($item['title']) ?></div>
                                            <?php if ($item['excerpt']): ?>
                                                <div class="news-excerpt"><?= htmlspecialchars($item['excerpt']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($item['category']): ?>
                                                <span class="badge-category">
                                                    <?php 
                                                    $categories = [
                                                        'tips' => 'Mẹo hay',
                                                        'events' => 'Sự kiện',
                                                        'promotion' => 'Khuyến mãi'
                                                    ];
                                                    echo $categories[$item['category']] ?? $item['category'];
                                                    ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($item['author_name'] ?? 'N/A') ?></td>
                                        <td>
                                            <span class="badge-status badge-<?= $item['status'] ?>">
                                                <?php 
                                                $statuses = [
                                                    'draft' => 'Nháp',
                                                    'published' => 'Đã xuất bản',
                                                    'archived' => 'Đã lưu trữ'
                                                ];
                                                echo $statuses[$item['status']] ?? $item['status'];
                                                ?>
                                            </span>
                                        </td>
                                        <td><?= number_format($item['views']) ?></td>
                                        <td><?= date('d/m/Y', strtotime($item['created_at'])) ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn-edit" onclick='openEditModal(<?= json_encode($item) ?>)'>
                                                    <i class="fas fa-edit"></i> Sửa
                                                </button>
                                                <form method="POST" action="<?= BASE_URL ?>/admin/news-delete" 
                                                      style="display: inline;"
                                                      id="deleteNewsForm<?= $item['id'] ?>">
                                                    <input type="hidden" name="news_id" value="<?= $item['id'] ?>">
                                                    <button type="button" class="btn-delete" onclick="confirmDeleteNews(<?= $item['id'] ?>, '<?= htmlspecialchars($item['title'], ENT_QUOTES) ?>')">
                                                        <i class="fas fa-trash"></i> Xóa
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($pagination['total_pages'] > 1): ?>
                    <div class="pagination">
                        <?php if ($pagination['current_page'] > 1): ?>
                            <a href="?page=<?= $pagination['current_page'] - 1 ?>&status=<?= $filters['status'] ?? '' ?>&category=<?= $filters['category'] ?? '' ?>&author_id=<?= $filters['author_id'] ?? '' ?>&search=<?= $filters['search'] ?? '' ?>">
                                <i class="fas fa-chevron-left"></i> Trước
                            </a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                            <?php if ($i == $pagination['current_page']): ?>
                                <span class="active"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?page=<?= $i ?>&status=<?= $filters['status'] ?? '' ?>&category=<?= $filters['category'] ?? '' ?>&author_id=<?= $filters['author_id'] ?? '' ?>&search=<?= $filters['search'] ?? '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                            <a href="?page=<?= $pagination['current_page'] + 1 ?>&status=<?= $filters['status'] ?? '' ?>&category=<?= $filters['category'] ?? '' ?>&author_id=<?= $filters['author_id'] ?? '' ?>&search=<?= $filters['search'] ?? '' ?>">
                                Sau <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Thêm Tin tức -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-newspaper"></i> Thêm Tin tức mới</h2>
                <span class="close" onclick="closeAddModal()">&times;</span>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/news-store" enctype="multipart/form-data">
                <div class="modal-body">
                <div class="form-group">
                    <label><i class="fas fa-heading"></i> Tiêu đề <span style="color: red;">*</span></label>
                    <input type="text" name="title" required placeholder="Nhập tiêu đề tin tức">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Tóm tắt</label>
                    <textarea name="excerpt" rows="3" placeholder="Nhập tóm tắt ngắn gọn về tin tức"></textarea>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-file-alt"></i> Nội dung <span style="color: red;">*</span></label>
                    <textarea name="content" id="content" required placeholder="Nhập nội dung chi tiết"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-folder"></i> Danh mục</label>
                        <select name="category">
                            <option value="">-- Chọn danh mục --</option>
                            <option value="tips">Mẹo hay</option>
                            <option value="events">Sự kiện</option>
                            <option value="promotion">Khuyến mãi</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-toggle-on"></i> Trạng thái</label>
                        <select name="status">
                            <option value="draft">Nháp</option>
                            <option value="published">Đã xuất bản</option>
                            <option value="archived">Đã lưu trữ</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-image"></i> Hình ảnh</label>
                    <input type="file" name="image" accept="image/*" onchange="previewAddImage(event)">
                    <img id="addImagePreview" class="image-preview" style="display: none;">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-tag"></i> Meta Title (SEO)</label>
                    <input type="text" name="meta_title" placeholder="Tiêu đề SEO">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-tags"></i> Meta Description (SEO)</label>
                    <textarea name="meta_description" rows="2" placeholder="Mô tả SEO"></textarea>
                </div>
                </div>

                <div class="modal-buttons">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Thêm tin tức
                </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Sửa Tin tức -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Cập nhật Tin tức</h2>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/news-update" enctype="multipart/form-data">
                <input type="hidden" name="news_id" id="edit_news_id">
                <div class="modal-body">
                
                <div class="form-group">
                    <label><i class="fas fa-heading"></i> Tiêu đề <span style="color: red;">*</span></label>
                    <input type="text" name="title" id="edit_title" required>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Tóm tắt</label>
                    <textarea name="excerpt" id="edit_excerpt" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-file-alt"></i> Nội dung <span style="color: red;">*</span></label>
                    <textarea name="content" id="edit_content" required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-folder"></i> Danh mục</label>
                        <select name="category" id="edit_category">
                            <option value="">-- Chọn danh mục --</option>
                            <option value="tips">Mẹo hay</option>
                            <option value="events">Sự kiện</option>
                            <option value="promotion">Khuyến mãi</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-toggle-on"></i> Trạng thái</label>
                        <select name="status" id="edit_status">
                            <option value="draft">Nháp</option>
                            <option value="published">Đã xuất bản</option>
                            <option value="archived">Đã lưu trữ</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-image"></i> Hình ảnh mới (để trống nếu không đổi)</label>
                    <input type="file" name="image" accept="image/*" onchange="previewEditImage(event)">
                    <div class="current-image" id="edit_current_image"></div>
                    <img id="editImagePreview" class="image-preview" style="display: none;">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-tag"></i> Meta Title (SEO)</label>
                    <input type="text" name="meta_title" id="edit_meta_title">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-tags"></i> Meta Description (SEO)</label>
                    <textarea name="meta_description" id="edit_meta_description" rows="2"></textarea>
                </div>
                </div>

                <div class="modal-buttons">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Cập nhật tin tức
                </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Auto-search với AJAX
        let searchTimeout;
        const searchInput = document.getElementById('searchInput');
        const statusSelect = document.querySelector('select[name="status"]');
        const categorySelect = document.querySelector('select[name="category"]');
        const authorSelect = document.querySelector('select[name="author_id"]');
        
        function performSearch() {
            const search = searchInput ? searchInput.value : '';
            const status = statusSelect ? statusSelect.value : '';
            const category = categorySelect ? categorySelect.value : '';
            const author = authorSelect ? authorSelect.value : '';
            
            const params = new URLSearchParams();
            if (search) params.append('search', search);
            if (status) params.append('status', status);
            if (category) params.append('category', category);
            if (author) params.append('author_id', author);
            
            const url = '<?= BASE_URL ?>/admin/news?' + params.toString();
            
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    const newTable = doc.getElementById('tableContainer');
                    const currentTable = document.getElementById('tableContainer');
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
        
        if (statusSelect) statusSelect.addEventListener('change', performSearch);
        if (categorySelect) categorySelect.addEventListener('change', performSearch);
        if (authorSelect) authorSelect.addEventListener('change', performSearch);
        
        // Modal functions
        function openAddModal() {
            document.getElementById('addModal').classList.add('active');
            // Reset scroll về đầu modal
            const modalBody = document.querySelector('#addModal .modal-body');
            if (modalBody) {
                modalBody.scrollTop = 0;
            }
            // Clear CKEditor content
            if (CKEDITOR.instances.content) {
                CKEDITOR.instances.content.setData('');
            }
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.remove('active');
        }

        function openEditModal(news) {
            document.getElementById('edit_news_id').value = news.id;
            document.getElementById('edit_title').value = news.title;
            document.getElementById('edit_excerpt').value = news.excerpt || '';
            document.getElementById('edit_category').value = news.category || '';
            document.getElementById('edit_status').value = news.status;
            document.getElementById('edit_meta_title').value = news.meta_title || '';
            document.getElementById('edit_meta_description').value = news.meta_description || '';
            
            // Set nội dung cho CKEditor (quan trọng!)
            if (CKEDITOR.instances.edit_content) {
                CKEDITOR.instances.edit_content.setData(news.content || '');
            } else {
                document.getElementById('edit_content').value = news.content || '';
            }
            
            // Hiển thị ảnh hiện tại
            const currentImageDiv = document.getElementById('edit_current_image');
            if (news.image) {
                currentImageDiv.innerHTML = '<p>Ảnh hiện tại:</p><img src="<?= BASE_URL ?>/' + news.image + '" style="max-width: 200px; max-height: 150px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">';
            } else {
                currentImageDiv.innerHTML = '';
            }
            
            document.getElementById('editImagePreview').style.display = 'none';
            document.getElementById('editModal').classList.add('active');
            
            // Reset scroll về đầu modal
            const modalBody = document.querySelector('#editModal .modal-body');
            if (modalBody) {
                modalBody.scrollTop = 0;
            }
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
        }

        // Preview images
        function previewAddImage(event) {
            const preview = document.getElementById('addImagePreview');
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }

        function previewEditImage(event) {
            const preview = document.getElementById('editImagePreview');
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addModal');
            const editModal = document.getElementById('editModal');
            if (event.target === addModal) {
                closeAddModal();
            }
            if (event.target === editModal) {
                closeEditModal();
            }
        };

        // Initialize CKEditor for content fields
        CKEDITOR.replace('content', {
            height: 400,
            removePlugins: 'about',
            versionCheck: false,
            filebrowserUploadUrl: '<?= BASE_URL ?>/admin/news-upload-image',
            filebrowserUploadMethod: 'form',
            extraPlugins: 'uploadimage',
            uploadUrl: '<?= BASE_URL ?>/admin/news-upload-image',
            // Toolbar configuration
            toolbar: [
                { name: 'document', items: ['Source', '-', 'Preview'] },
                { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'] },
                { name: 'editing', items: ['Find', 'Replace', '-', 'SelectAll'] },
                '/',
                { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat'] },
                { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv'] },
                { name: 'links', items: ['Link', 'Unlink'] },
                { name: 'insert', items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar'] },
                '/',
                { name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize'] },
                { name: 'colors', items: ['TextColor', 'BGColor'] },
                { name: 'tools', items: ['Maximize'] }
            ],
            // Image upload settings
            imageUploadUrl: '<?= BASE_URL ?>/admin/news-upload-image'
        });

        CKEDITOR.replace('edit_content', {
            height: 400,
            removePlugins: 'about',
            versionCheck: false,
            filebrowserUploadUrl: '<?= BASE_URL ?>/admin/news-upload-image',
            filebrowserUploadMethod: 'form',
            extraPlugins: 'uploadimage',
            uploadUrl: '<?= BASE_URL ?>/admin/news-upload-image',
            toolbar: [
                { name: 'document', items: ['Source', '-', 'Preview'] },
                { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'] },
                { name: 'editing', items: ['Find', 'Replace', '-', 'SelectAll'] },
                '/',
                { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat'] },
                { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv'] },
                { name: 'links', items: ['Link', 'Unlink'] },
                { name: 'insert', items: ['Image', 'Table', 'HorizontalRule', 'SpecialChar'] },
                '/',
                { name: 'styles', items: ['Styles', 'Format', 'Font', 'FontSize'] },
                { name: 'colors', items: ['TextColor', 'BGColor'] },
                { name: 'tools', items: ['Maximize'] }
            ],
            imageUploadUrl: '<?= BASE_URL ?>/admin/news-upload-image'
        });
        
        // Confirm delete news
        function confirmDeleteNews(id, title) {
            confirmDelete({
                title: 'Xóa tin tức',
                message: `Bạn có chắc chắn muốn xóa tin tức "<strong>${title}</strong>"?<br><br>Hành động này không thể hoàn tác!`,
                confirmText: 'Xóa tin tức',
                theme: 'admin'
            }).then(confirmed => {
                if (confirmed) {
                    document.getElementById('deleteNewsForm' + id).submit();
                }
            });
        }
    </script>
    <?php include APP_PATH . '/views/layouts/toast_notification.php'; ?>
</body>
</html>
