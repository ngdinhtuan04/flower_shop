<?php
$currentRole = Session::get('user_role');
$currentUserId = Session::get('user_id');
$user = Session::getUser();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý người dùng - Admin</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin-users.css">
    <script src="<?= ASSETS_URL ?>/js/confirm-dialog.js"></script>
</head>
<body>
    <?php include APP_PATH . '/views/layouts/admin_sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Topbar -->
            <div class="topbar">
                <h2><i class="fas fa-users"></i> Quản lý người dùng</h2>
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <strong><?= htmlspecialchars($user['full_name']) ?></strong>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="filter-bar">
                <input type="text" id="searchInput" placeholder="🔍 Tìm kiếm theo tên, email, tên đăng nhập..." value="<?= htmlspecialchars($search ?? '') ?>">
                <label><i class="fas fa-filter"></i> Lọc theo:</label>
                <select id="roleFilter" onchange="filterUsers()">
                    <option value="">Tất cả vai trò</option>
                    <option value="superadmin">SuperAdmin</option>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>
                <select id="statusFilter" onchange="filterUsers()">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active">Active</option>
                    <option value="pending">Pending</option>
                    <option value="inactive">Inactive</option>
                    <option value="banned">Banned</option>
                </select>
                <select id="sortOrder" onchange="handleSortChange()">
                    <option value="">📊 Sắp xếp theo...</option>
                    <option value="orders_desc" <?= ($sort ?? '') === 'orders_desc' ? 'selected' : '' ?>>Mua nhiều nhất</option>
                    <option value="orders_asc" <?= ($sort ?? '') === 'orders_asc' ? 'selected' : '' ?>>Mua ít nhất</option>
                    <option value="spent_desc" <?= ($sort ?? '') === 'spent_desc' ? 'selected' : '' ?>>Chi tiêu nhiều nhất</option>
                    <option value="spent_asc" <?= ($sort ?? '') === 'spent_asc' ? 'selected' : '' ?>>Chi tiêu ít nhất</option>
                    <option value="name_asc" <?= ($sort ?? '') === 'name_asc' ? 'selected' : '' ?>>Tên A → Z</option>
                    <option value="name_desc" <?= ($sort ?? '') === 'name_desc' ? 'selected' : '' ?>>Tên Z → A</option>
                    <option value="newest" <?= ($sort ?? '') === 'newest' ? 'selected' : '' ?>>Mới nhất</option>
                    <option value="oldest" <?= ($sort ?? '') === 'oldest' ? 'selected' : '' ?>>Cũ nhất</option>
                </select>
            </div>

            <!-- User Table -->
            <div class="user-table">
                <table id="userTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên đăng nhập</th>
                            <th>Email</th>
                            <th>Họ tên</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th>Tổng lượt mua</th>
                            <th>Tổng chi tiêu</th>
                            <th>Ngày tạo</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $u): ?>
                                <tr data-role="<?= $u['role'] ?>" data-status="<?= $u['status'] ?>">
                                    <td><?= $u['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td><?= htmlspecialchars($u['full_name']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $u['role'] ?>">
                                            <?php
                                            switch($u['role']) {
                                                case 'superadmin': echo 'SUPERADMIN'; break;
                                                case 'admin': echo 'ADMIN'; break;
                                                default: echo 'USER';
                                            }
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $u['status'] ?>">
                                            <?php
                                            switch($u['status']) {
                                                case 'active': echo 'Hoạt động'; break;
                                                case 'pending': echo 'Chờ duyệt'; break;
                                                case 'inactive': echo 'Khóa'; break;
                                                case 'banned': echo 'Cấm'; break;
                                            }
                                            ?>
                                        </span>
                                    </td>
                                    <td><strong><?= $u['total_orders'] ?></strong> đơn</td>
                                    <td><strong style="color: #10b981;"><?= number_format($u['total_spent'], 0, ',', '.') ?>₫</strong></td>
                                    <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                                    <td>
                                        <?php
                                        // Không cho thao tác với chính mình
                                        if ($u['id'] == $currentUserId) {
                                            echo '<span style="color: #999;">Tài khoản hiện tại</span>';
                                        }
                                        // SuperAdmin có thể quản lý tất cả (trừ chính mình)
                                        elseif ($currentRole === 'superadmin') {
                                            ?>
                                            <?php if ($u['status'] === 'active'): ?>
                                                <button class="action-btn btn-lock" onclick="updateStatus(<?= $u['id'] ?>, 'inactive')">
                                                    <i class="fas fa-lock"></i> Khóa
                                                </button>
                                            <?php elseif ($u['status'] === 'inactive'): ?>
                                                <button class="action-btn btn-unlock" onclick="updateStatus(<?= $u['id'] ?>, 'active')">
                                                    <i class="fas fa-unlock"></i> Mở
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($u['role'] !== 'superadmin'): ?>
                                                <button class="action-btn btn-delete" onclick="deleteUser(<?= $u['id'] ?>, '<?= htmlspecialchars($u['username']) ?>')">
                                                    <i class="fas fa-trash"></i> Xóa
                                                </button>
                                            <?php endif; ?>
                                            <?php
                                        }
                                        // Admin chỉ quản lý được User
                                        elseif ($currentRole === 'admin' && $u['role'] === 'user') {
                                            ?>
                                            <?php if ($u['status'] === 'active'): ?>
                                                <button class="action-btn btn-lock" onclick="updateStatus(<?= $u['id'] ?>, 'inactive')">
                                                    <i class="fas fa-lock"></i> Khóa
                                                </button>
                                            <?php elseif ($u['status'] === 'inactive'): ?>
                                                <button class="action-btn btn-unlock" onclick="updateStatus(<?= $u['id'] ?>, 'active')">
                                                    <i class="fas fa-unlock"></i> Mở
                                                </button>
                                            <?php endif; ?>
                                            <button class="action-btn btn-delete" onclick="deleteUser(<?= $u['id'] ?>, '<?= htmlspecialchars($u['username']) ?>')">
                                                <i class="fas fa-trash"></i> Xóa
                                            </button>
                                            <?php
                                        } else {
                                            echo '<span style="color: #999;">Không có quyền</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <p>Chưa có người dùng nào</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Edit User -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Chỉnh sửa thông tin người dùng</h3>
            </div>
            <form id="editForm" action="<?= BASE_URL ?>/admin/update-user" method="POST">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="modal-body">
                
                <div class="form-group">
                    <label>Họ và tên</label>
                    <input type="text" name="full_name" id="edit_full_name" required>
                </div>
                
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="tel" name="phone" id="edit_phone">
                </div>
                
                <div class="form-group">
                    <label>Địa chỉ</label>
                    <input type="text" name="address" id="edit_address">
                </div>
                </div>
                
                <div class="modal-buttons">
                    <button type="button" class="action-btn btn-cancel" onclick="closeModal()">Hủy</button>
                    <button type="submit" class="action-btn btn-edit">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Auto-search với AJAX
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
            const sort = document.getElementById('sortOrder').value;
            const params = new URLSearchParams();
            if (search) params.append('search', search);
            if (sort) params.append('sort', sort);
            
            window.location.href = '<?= BASE_URL ?>/admin/users?' + params.toString();
        }
        
        function handleSortChange() {
            performSearch();
        }
        
        function filterUsers() {
            const roleFilter = document.getElementById('roleFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            const rows = document.querySelectorAll('#userTable tbody tr');
            
            rows.forEach(row => {
                const role = row.dataset.role;
                const status = row.dataset.status;
                
                const roleMatch = !roleFilter || role === roleFilter;
                const statusMatch = !statusFilter || status === statusFilter;
                
                row.style.display = (roleMatch && statusMatch) ? '' : 'none';
            });
        }

        function editUser(userId, fullName, phone, address) {
            document.getElementById('edit_user_id').value = userId;
            document.getElementById('edit_full_name').value = fullName;
            document.getElementById('edit_phone').value = phone;
            document.getElementById('edit_address').value = address;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function updateStatus(userId, status) {
            const statusText = {
                'active': 'mở khóa',
                'inactive': 'khóa'
            };
            const statusIcon = status === 'active' ? 'success' : 'warning';
            
            confirmAction({
                title: status === 'active' ? 'Mở khóa tài khoản' : 'Khóa tài khoản',
                message: `Bạn có chắc chắn muốn <strong>${statusText[status]}</strong> tài khoản này?`,
                type: statusIcon,
                confirmText: statusText[status].charAt(0).toUpperCase() + statusText[status].slice(1),
                theme: 'admin'
            }).then(confirmed => {
                if (confirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '<?= BASE_URL ?>/admin/update-user-status';
                    
                    const userIdInput = document.createElement('input');
                    userIdInput.type = 'hidden';
                    userIdInput.name = 'user_id';
                    userIdInput.value = userId;
                    
                    const statusInput = document.createElement('input');
                    statusInput.type = 'hidden';
                    statusInput.name = 'status';
                    statusInput.value = status;
                    
                    form.appendChild(userIdInput);
                    form.appendChild(statusInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        function deleteUser(userId, username) {
            confirmDelete({
                title: 'Xóa tài khoản',
                message: `Bạn có chắc chắn muốn <strong>XÓA VĨNH VIỄN</strong> tài khoản "<strong>${username}</strong>"?<br><br>Hành động này <strong>KHÔNG THỂ HOÀN TÁC!</strong>`,
                confirmText: 'Xóa tài khoản',
                theme: 'admin'
            }).then(confirmed => {
                if (confirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '<?= BASE_URL ?>/admin/delete-user';
                    
                    const userIdInput = document.createElement('input');
                    userIdInput.type = 'hidden';
                    userIdInput.name = 'user_id';
                    userIdInput.value = userId;
                    
                    form.appendChild(userIdInput);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
    <?php include APP_PATH . '/views/layouts/toast_notification.php'; ?>
</body>
</html>
