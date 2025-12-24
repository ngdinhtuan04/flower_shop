<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Danh mục - Admin</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin-categories.css">
    <script src="<?= ASSETS_URL ?>/js/confirm-dialog.js"></script>
</head>
<body>
    <?php
    $user = Session::getUser();
    ?>

    <?php include APP_PATH . '/views/layouts/admin_sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Topbar -->
            <div class="topbar">
                <h2><i class="fas fa-tags"></i> Quản lý Danh mục</h2>
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <strong><?= htmlspecialchars($user['full_name']) ?></strong>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <input type="text" name="search" id="searchInput" placeholder="🔍 Tìm kiếm danh mục theo tên..." value="<?= htmlspecialchars($search ?? '') ?>">
                <select id="sortOrder" onchange="handleSortChange()">
                    <option value="">📊 Sắp xếp theo...</option>
                    <option value="name_asc" <?= ($sort ?? '') === 'name_asc' ? 'selected' : '' ?>>Tên A → Z</option>
                    <option value="name_desc" <?= ($sort ?? '') === 'name_desc' ? 'selected' : '' ?>>Tên Z → A</option>
                    <option value="products_desc" <?= ($sort ?? '') === 'products_desc' ? 'selected' : '' ?>>Nhiều sản phẩm nhất</option>
                    <option value="products_asc" <?= ($sort ?? '') === 'products_asc' ? 'selected' : '' ?>>Ít sản phẩm nhất</option>
                    <option value="newest" <?= ($sort ?? '') === 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                    <option value="oldest" <?= ($sort ?? '') === 'oldest' ? 'selected' : '' ?>>Cũ nhất</option>
                </select>
                <button class="btn-add" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Thêm mới
                </button>
            </div>

            <!-- Categories Table -->
            <div class="category-table" id="tableContainer">
                <?php if (empty($categories)): ?>
                    <div class="empty-state">
                        <i class="fas fa-tags"></i>
                        <h3>Chưa có danh mục nào</h3>
                        <p>Nhấn nút "Thêm Danh mục" để bắt đầu</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên Danh mục</th>
                                <th>Slug</th>
                                <th>Mô tả</th>
                                <th>Số Sản phẩm</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                                <tr>
                                    <td><?= $category['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($category['name']) ?></strong></td>
                                    <td><code><?= htmlspecialchars($category['slug']) ?></code></td>
                                    <td><?= htmlspecialchars(mb_substr($category['description'] ?? '', 0, 50)) ?>...</td>
                                    <td>
                                        <span class="badge badge-count"><?= $category['product_count'] ?? 0 ?></span>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($category['created_at'])) ?></td>
                                    <td>
                                        <button class="action-btn btn-edit" onclick='openEditModal(<?= json_encode($category) ?>)'>
                                            <i class="fas fa-edit"></i> Sửa
                                        </button>
                                        <form method="POST" action="<?= BASE_URL ?>/admin/categories-delete" style="display: inline;" id="deleteForm<?= $category['id'] ?>">
                                            <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                                            <button type="button" class="action-btn btn-delete" onclick="confirmDeleteCategory(<?= $category['id'] ?>, '<?= htmlspecialchars($category['name'], ENT_QUOTES) ?>')">
                                                <i class="fas fa-trash"></i> Xóa
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-plus-circle"></i> Thêm Danh mục mới</h3>
                <button class="close-modal" onclick="closeAddModal()">&times;</button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/categories-store" id="addCategoryForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Tên Danh mục *</label>
                        <input type="text" name="name" id="add_name" placeholder="Ví dụ: Hoa Hồng">
                        <div class="error-message" id="add_name_error">Vui lòng nhập tên danh mục</div>
                    </div>
                    <div class="form-group">
                        <label>Mô tả</label>
                        <textarea name="description" placeholder="Mô tả về danh mục này..."></textarea>
                    </div>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn-cancel" onclick="closeAddModal()">Hủy</button>
                    <button type="submit" class="btn-submit">Thêm mới</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Chỉnh sửa Danh mục</h3>
                <button class="close-modal" onclick="closeEditModal()">&times;</button>
            </div>
            <form method="POST" action="<?= BASE_URL ?>/admin/categories-update" id="editCategoryForm">
                <input type="hidden" id="edit_category_id" name="category_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Tên Danh mục *</label>
                        <input type="text" id="edit_name" name="name">
                        <div class="error-message" id="edit_name_error">Vui lòng nhập tên danh mục</div>
                    </div>
                    <div class="form-group">
                        <label>Mô tả</label>
                        <textarea id="edit_description" name="description"></textarea>
                    </div>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn-cancel" onclick="closeEditModal()">Hủy</button>
                    <button type="submit" class="btn-submit">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Auto-search với AJAX (không reload trang)
        let searchTimeout;
        const searchInput = document.getElementById('searchInput');
        
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(performSearch, 300);
            });
        }
        
        function performSearch() {
            const search = searchInput.value;
            const sortOrder = document.getElementById('sortOrder').value;
            const params = new URLSearchParams();
            if (search) params.append('search', search);
            if (sortOrder) params.append('sort', sortOrder);
            
            const url = '<?= BASE_URL ?>/admin/categories?' + params.toString();
            
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
        
        function handleSortChange() {
            performSearch();
        }
        
        function openAddModal() {
            document.getElementById('addModal').style.display = 'flex';
            // Reset form và errors
            const form = document.getElementById('addCategoryForm');
            if (form) form.reset();
            clearFormErrors('add');
        }
        
        function closeAddModal() {
            document.getElementById('addModal').style.display = 'none';
            clearFormErrors('add');
        }
        
        function openEditModal(category) {
            document.getElementById('edit_category_id').value = category.id;
            document.getElementById('edit_name').value = category.name;
            document.getElementById('edit_description').value = category.description || '';
            clearFormErrors('edit');
            document.getElementById('editModal').style.display = 'flex';
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
            clearFormErrors('edit');
        }

        // Validation functions
        function clearFormErrors(type) {
            const prefix = type === 'add' ? 'add_' : 'edit_';
            const input = document.getElementById(prefix + 'name');
            const error = document.getElementById(prefix + 'name_error');
            
            if (input) {
                input.classList.remove('error');
            }
            if (error) {
                error.classList.remove('show');
            }
        }

        function validateForm(type) {
            const prefix = type === 'add' ? 'add_' : 'edit_';
            let isValid = true;
            
            // Clear previous errors
            clearFormErrors(type);
            
            // Validate name
            const name = document.getElementById(prefix + 'name');
            if (!name.value.trim()) {
                const input = document.getElementById(prefix + 'name');
                const error = document.getElementById(prefix + 'name_error');
                
                if (input) {
                    input.classList.add('error');
                }
                if (error) {
                    error.classList.add('show');
                }
                isValid = false;
            }
            
            return isValid;
        }

        // Form submit handlers
        document.addEventListener('DOMContentLoaded', function() {
            const addForm = document.getElementById('addCategoryForm');
            const editForm = document.getElementById('editCategoryForm');
            
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
        
        // Confirm delete category
        function confirmDeleteCategory(id, name) {
            confirmDelete({
                title: 'Xóa danh mục',
                message: `Bạn có chắc chắn muốn xóa danh mục "<strong>${name}</strong>"?<br><br>Hành động này không thể hoàn tác!`,
                confirmText: 'Xóa danh mục',
                theme: 'admin'
            }).then(confirmed => {
                if (confirmed) {
                    document.getElementById('deleteForm' + id).submit();
                }
            });
        }
    </script>
    <?php include APP_PATH . '/views/layouts/toast_notification.php'; ?>
</body>
</html>