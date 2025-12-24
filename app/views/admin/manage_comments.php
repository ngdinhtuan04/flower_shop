<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Bình luận - Admin</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin-comments.css">
    <script src="<?= ASSETS_URL ?>/js/confirm-dialog.js"></script>
</head>
<body>
    <div class="admin-container">
        <?php include APP_PATH . '/views/layouts/admin_sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Topbar -->
            <div class="topbar">
                <h2>Quản lý Bình luận Tin tức</h2>
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <strong><?= htmlspecialchars(Session::getUser()['full_name'] ?? 'Admin') ?></strong>
                </div>
            </div>

            <?php if (Session::hasFlash('success')): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= Session::getFlash('success') ?>
                </div>
            <?php endif; ?>
            <?php if (Session::hasFlash('error')): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= Session::getFlash('error') ?>
                </div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="comment-stats">
                <div class="stat-card total">
                    <h3>Tổng bình luận</h3>
                    <p class="stat-number"><?= number_format($stats['total']) ?></p>
                </div>
                <div class="stat-card visible">
                    <h3>Đang hiện</h3>
                    <p class="stat-number"><?= number_format($stats['visible']) ?></p>
                </div>
                <div class="stat-card hidden">
                    <h3>Đã ẩn</h3>
                    <p class="stat-number"><?= number_format($stats['hidden']) ?></p>
                </div>
                <div class="stat-card deleted">
                    <h3>Đã xóa</h3>
                    <p class="stat-number"><?= number_format($stats['deleted']) ?></p>
                </div>
                <div class="stat-card spam">
                    <h3>Spam</h3>
                    <p class="stat-number"><?= number_format($stats['spam']) ?></p>
                </div>
            </div>

            <!-- Filters -->
            <div class="filter-section">
                <form method="GET" action="<?= BASE_URL ?>/admin/comments" class="filter-grid">
                    <div class="filter-group">
                        <label>Trạng thái</label>
                        <select name="status">
                            <option value="">Tất cả</option>
                            <option value="visible" <?= ($filters['status'] ?? '') === 'visible' ? 'selected' : '' ?>>Đang hiện</option>
                            <option value="hidden" <?= ($filters['status'] ?? '') === 'hidden' ? 'selected' : '' ?>>Đã ẩn</option>
                            <option value="deleted" <?= ($filters['status'] ?? '') === 'deleted' ? 'selected' : '' ?>>Đã xóa</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Tin tức</label>
                        <select name="news_id">
                            <option value="">Tất cả tin tức</option>
                            <?php foreach ($newsList as $news): ?>
                                <option value="<?= $news['id'] ?>" <?= ($filters['news_id'] ?? '') == $news['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(mb_substr($news['title'], 0, 45)) ?><?= mb_strlen($news['title']) > 45 ? '...' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Tìm kiếm</label>
                        <input type="text" name="search" placeholder="Nội dung, người dùng..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                    </div>
                    <div class="filter-group" style="display: flex; align-items: flex-end;">
                        <button type="submit" class="btn-primary" style="width: 100%; padding: 11px 20px;">
                            <i class="fas fa-search"></i> Lọc
                        </button>
                    </div>
                </form>
            </div>

            <!-- Comments Table -->
            <div class="comment-table">
                <table class="news-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th>Tin tức</th>
                            <th>Người dùng</th>
                            <th style="width: 300px;">Nội dung</th>
                            <th>Thời gian</th>
                            <th>Trạng thái</th>
                            <th style="width: 200px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($comments)): ?>
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="fas fa-comments"></i>
                                        <p>Chưa có bình luận nào</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($comments as $comment): ?>
                                <tr>
                                    <td><strong>#<?= $comment['id'] ?></strong></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/news/detail/<?= $comment['news_slug'] ?>" target="_blank" style="color: #667eea; text-decoration: none; font-weight: 500;">
                                            <?= htmlspecialchars(mb_substr($comment['news_title'], 0, 35)) ?><?= mb_strlen($comment['news_title']) > 35 ? '...' : '' ?>
                                            <i class="fas fa-external-link-alt" style="font-size: 10px; margin-left: 4px;"></i>
                                        </a>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: #1f2937; margin-bottom: 2px;">
                                            <?= htmlspecialchars($comment['username']) ?>
                                        </div>
                                        <div style="font-size: 12px; color: #9ca3af;">
                                            <?= htmlspecialchars($comment['email']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($comment['parent_id']): ?>
                                            <div class="parent-comment">
                                                <div style="color: #6b7280; font-size: 12px; margin-bottom: 4px;">
                                                    <i class="fas fa-reply"></i> Trả lời 
                                                    <strong><?= htmlspecialchars($comment['parent_username']) ?></strong>
                                                </div>
                                                <div style="color: #4b5563;">
                                                    <?= htmlspecialchars(mb_substr($comment['parent_content'], 0, 45)) ?><?= mb_strlen($comment['parent_content']) > 45 ? '...' : '' ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="comment-content">
                                            <?= nl2br(htmlspecialchars($comment['content'])) ?>
                                        </div>
                                        <?php if ($comment['is_spam']): ?>
                                            <div style="margin-top: 8px;">
                                                <span class="badge badge-spam">
                                                    <i class="fas fa-exclamation-triangle"></i> Spam
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($comment['admin_reason']): ?>
                                            <div class="reason-box">
                                                <strong>Lý do:</strong> <?= htmlspecialchars($comment['admin_reason']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        <div style="font-size: 13px; color: #4b5563;">
                                            <?= date('d/m/Y', strtotime($comment['created_at'])) ?>
                                        </div>
                                        <div style="font-size: 12px; color: #9ca3af; margin-top: 2px;">
                                            <?= date('H:i', strtotime($comment['created_at'])) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $statusMap = [
                                            'visible' => '<span class="badge badge-visible"><i class="fas fa-eye"></i> Hiện</span>',
                                            'hidden' => '<span class="badge badge-hidden"><i class="fas fa-eye-slash"></i> Ẩn</span>',
                                            'deleted' => '<span class="badge badge-deleted"><i class="fas fa-trash-alt"></i> Xóa</span>'
                                        ];
                                        echo $statusMap[$comment['status']] ?? htmlspecialchars($comment['status']);
                                        ?>
                                    </td>
                                    <td>
                                        <div class="action-btns">
                                            <?php if ($comment['status'] === 'visible'): ?>
                                                <button type="button" class="btn-hide" onclick="showReasonModal('hide', <?= $comment['id'] ?>)" title="Ẩn bình luận">
                                                    <i class="fas fa-eye-slash"></i> Ẩn
                                                </button>
                                                <form method="POST" action="<?= BASE_URL ?>/admin/comment-mark-spam" style="display: inline; margin: 0;" id="spamForm<?= $comment['id'] ?>">
                                                    <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                                    <button type="button" class="btn-spam" onclick="confirmMarkSpam(<?= $comment['id'] ?>)" title="Đánh dấu spam">
                                                        <i class="fas fa-flag"></i> Spam
                                                    </button>
                                                </form>
                                            <?php elseif ($comment['status'] === 'hidden'): ?>
                                                <form method="POST" action="<?= BASE_URL ?>/admin/comment-update-status" style="display: inline; margin: 0;">
                                                    <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                                    <input type="hidden" name="status" value="visible">
                                                    <button type="submit" class="btn-show" title="Hiện lại bình luận">
                                                        <i class="fas fa-eye"></i> Hiện
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <?php if ($comment['status'] !== 'deleted'): ?>
                                                <button type="button" class="btn-delete" onclick="showReasonModal('delete', <?= $comment['id'] ?>)" title="Xóa bình luận">
                                                    <i class="fas fa-trash-alt"></i> Xóa
                                                </button>
                                            <?php endif; ?>
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
                    <?php
                    $params = http_build_query([
                        'status' => $filters['status'] ?? '',
                        'news_id' => $filters['news_id'] ?? '',
                        'search' => $filters['search'] ?? ''
                    ]);
                    ?>
                    
                    <?php if ($pagination['current_page'] > 1): ?>
                        <a href="?page=<?= $pagination['current_page'] - 1 ?>&<?= $params ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                        <a href="?page=<?= $i ?>&<?= $params ?>" class="<?= $i == $pagination['current_page'] ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                        <a href="?page=<?= $pagination['current_page'] + 1 ?>&<?= $params ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Reason Modal -->
    <div id="reasonModal" class="modal">
        <div class="modal-content">
            <div class="modal-header" id="modalTitle"></div>
            <form id="reasonForm" method="POST">
                <input type="hidden" name="comment_id" id="modalCommentId">
                <div style="margin-bottom: 15px;">
                    <label style="display:block;margin-bottom:8px;font-weight:600;">Lý do:</label>
                    <textarea name="reason" required style="width:100%;padding:10px;border:2px solid #e5e7eb;border-radius:8px;font-size:14px;resize:vertical;" rows="4" placeholder="Nhập lý do..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closeModal()" style="padding:10px 20px;border:none;background:#f3f4f6;border-radius:6px;font-weight:600;cursor:pointer;">
                        Hủy
                    </button>
                    <button type="submit" class="btn-primary" style="padding:10px 20px;">
                        Xác nhận
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showReasonModal(action, commentId) {
            const modal = document.getElementById('reasonModal');
            const form = document.getElementById('reasonForm');
            const title = document.getElementById('modalTitle');
            
            if (action === 'hide') {
                title.textContent = 'Ẩn bình luận';
                form.action = '<?= BASE_URL ?>/admin/comment-hide';
            } else {
                title.textContent = 'Xóa bình luận';
                form.action = '<?= BASE_URL ?>/admin/comment-delete';
            }
            
            document.getElementById('modalCommentId').value = commentId;
            modal.classList.add('active');
        }
        
        function closeModal() {
            document.getElementById('reasonModal').classList.remove('active');
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('reasonModal');
            if (event.target === modal) {
                closeModal();
            }
        }
        
        // Confirm mark spam
        function confirmMarkSpam(commentId) {
            confirmAction({
                title: 'Đánh dấu Spam',
                message: 'Bạn có chắc chắn muốn đánh dấu bình luận này là <strong>SPAM</strong>?<br><br>Bình luận sẽ bị ẩn ngay lập tức.',
                type: 'warning',
                confirmText: 'Đánh dấu Spam',
                theme: 'admin'
            }).then(confirmed => {
                if (confirmed) {
                    document.getElementById('spamForm' + commentId).submit();
                }
            });
        }
    </script>
</body>
</html>
