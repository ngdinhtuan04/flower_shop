<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Duyệt Admin - Flower Shop</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin-pending-admins.css">
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
                <h2>Duyệt Admin</h2>
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <strong><?= htmlspecialchars($user['full_name']) ?></strong>
                </div>
            </div>

            <!-- Content -->
            <div class="content-wrapper">
                
                <div class="info-card">
                    <i class="fas fa-info-circle"></i>
                    <strong>Lưu ý:</strong> Chỉ SuperAdmin mới có quyền duyệt tài khoản admin mới. Hãy xem xét kỹ trước khi phê duyệt.
                </div>

                <?php if (!empty($admins)): ?>
                    <div class="pending-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tên đăng nhập</th>
                                    <th>Email</th>
                                    <th>Họ tên</th>
                                    <th>Số điện thoại</th>
                                    <th>Ngày đăng ký</th>
                                    <th style="text-align: center;">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($admins as $admin): ?>
                                    <tr>
                                        <td><strong>#<?= $admin['id'] ?></strong></td>
                                        <td><strong><?= htmlspecialchars($admin['username']) ?></strong></td>
                                        <td><?= htmlspecialchars($admin['email']) ?></td>
                                        <td><?= htmlspecialchars($admin['full_name']) ?></td>
                                        <td><?= htmlspecialchars($admin['phone'] ?? 'Chưa cập nhật') ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($admin['created_at'])) ?></td>
                                        <td style="text-align: center;">
                                            <form method="POST" action="<?= BASE_URL ?>/admin/approve-admin" style="display: inline;" id="approveForm<?= $admin['id'] ?>">
                                                <input type="hidden" name="admin_id" value="<?= $admin['id'] ?>">
                                                <input type="hidden" name="action" value="approve">
                                                <button type="button" class="action-btn btn-approve" onclick="confirmApproveAdmin(<?= $admin['id'] ?>, '<?= htmlspecialchars($admin['username'], ENT_QUOTES) ?>')">
                                                    <i class="fas fa-check"></i> Duyệt
                                                </button>
                                            </form>
                                            
                                            <form method="POST" action="<?= BASE_URL ?>/admin/approve-admin" style="display: inline;" id="rejectForm<?= $admin['id'] ?>">
                                                <input type="hidden" name="admin_id" value="<?= $admin['id'] ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button type="button" class="action-btn btn-reject" onclick="confirmRejectAdmin(<?= $admin['id'] ?>, '<?= htmlspecialchars($admin['username'], ENT_QUOTES) ?>')">
                                                    <i class="fas fa-times"></i> Từ chối
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <h3>Không có admin nào chờ duyệt</h3>
                        <p>Tất cả đăng ký admin đã được xử lý</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        // Confirm approve admin
        function confirmApproveAdmin(adminId, username) {
            confirmAction({
                title: 'Duyệt Admin',
                message: `Bạn có chắc chắn muốn duyệt tài khoản admin "<strong>${username}</strong>"?<br><br>Sau khi duyệt, người dùng này sẽ có quyền truy cập vào trang quản trị.`,
                type: 'success',
                confirmText: 'Duyệt',
                theme: 'admin'
            }).then(confirmed => {
                if (confirmed) {
                    document.getElementById('approveForm' + adminId).submit();
                }
            });
        }
        
        // Confirm reject admin
        function confirmRejectAdmin(adminId, username) {
            confirmDelete({
                title: 'Từ chối Admin',
                message: `Bạn có chắc chắn muốn từ chối tài khoản admin "<strong>${username}</strong>"?<br><br>⚠️ Tài khoản sẽ bị <strong>XÓA VĨNH VIỄN!</strong>`,
                confirmText: 'Từ chối & Xóa',
                theme: 'admin'
            }).then(confirmed => {
                if (confirmed) {
                    document.getElementById('rejectForm' + adminId).submit();
                }
            });
        }
    </script>
    <?php include APP_PATH . '/views/layouts/toast_notification.php'; ?>
</body>
</html>
