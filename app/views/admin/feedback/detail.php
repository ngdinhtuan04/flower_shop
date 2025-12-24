<?php
$currentRole = Session::get('user_role');
$user = Session::getUser();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết góp ý - Admin</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin-feedback-detail.css">
</head>
<body>
    <!-- Toast Container -->
    <div id="toastContainer" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>
    
    <div class="admin-container">
        <?php require_once APP_PATH . '/views/layouts/admin_sidebar.php'; ?>
        
        <div class="main-content">
            <a href="<?= BASE_URL ?>/admin/feedback" class="btn-back">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            
            <div class="feedback-detail">
                <div class="feedback-header">
                    <h2><i class="fas fa-comment-dots"></i> <?= htmlspecialchars($feedback['subject']) ?></h2>
                    <p style="color: #6b7280;">Ngày gửi: <?= date('d/m/Y H:i', strtotime($feedback['created_at'])) ?></p>
                </div>
                

                
                <div class="info-section">
                    <h3><i class="fas fa-info-circle"></i> Thông tin góp ý</h3>
                    <div class="info-row">
                        <div class="info-label">Người gửi:</div>
                        <div class="info-value"><?= htmlspecialchars($feedback['name']) ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Email:</div>
                        <div class="info-value"><?= htmlspecialchars($feedback['email']) ?></div>
                    </div>
                    <?php if (!empty($feedback['phone'])): ?>
                    <div class="info-row">
                        <div class="info-label">Số điện thoại:</div>
                        <div class="info-value"><?= htmlspecialchars($feedback['phone']) ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="info-row">
                        <div class="info-label">Loại góp ý:</div>
                        <div class="info-value">
                            <span class="badge badge-<?= $feedback['type'] ?>">
                                <?php
                                $typeLabels = [
                                    'complaint' => 'Khiếu nại',
                                    'suggestion' => 'Góp ý',
                                    'question' => 'Thắc mắc',
                                    'other' => 'Khác'
                                ];
                                echo $typeLabels[$feedback['type']] ?? $feedback['type'];
                                ?>
                            </span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Trạng thái:</div>
                        <div class="info-value">
                            <span class="badge badge-<?= $feedback['status'] ?>">
                                <?php
                                $statusLabels = [
                                    'new' => 'Mới',
                                    'processing' => 'Đang xử lý',
                                    'resolved' => 'Đã giải quyết',
                                    'closed' => 'Đã đóng'
                                ];
                                echo $statusLabels[$feedback['status']] ?? $feedback['status'];
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="message-box">
                    <h4><i class="fas fa-envelope"></i> Nội dung góp ý</h4>
                    <p><?= nl2br(htmlspecialchars($feedback['message'])) ?></p>
                </div>
                
                <?php if (!empty($feedback['admin_reply'])): ?>
                    <div class="reply-box">
                        <h4><i class="fas fa-reply"></i> Phản hồi từ <?= htmlspecialchars($feedback['admin_name'] ?? 'Admin') ?></h4>
                        <p><?= nl2br(htmlspecialchars($feedback['admin_reply'])) ?></p>
                        <small style="color: #065f46; margin-top: 10px; display: block;">
                            Phản hồi lúc: <?= date('d/m/Y H:i', strtotime($feedback['replied_at'])) ?>
                        </small>
                    </div>
                <?php endif; ?>
                
                <?php if ($feedback['status'] != 'closed'): ?>
                    <?php if (empty($feedback['admin_reply'])): ?>
                    <div class="form-section">
                        <h3><i class="fas fa-reply"></i> Gửi phản hồi</h3>
                        <form method="POST" action="<?= BASE_URL ?>/admin/feedback/reply">
                            <input type="hidden" name="feedback_id" value="<?= $feedback['id'] ?>">
                            <div class="form-group">
                                <label>Nội dung phản hồi: <span style="color: #e91e63;">*</span></label>
                                <textarea name="reply" required placeholder="Nhập nội dung phản hồi..."></textarea>
                            </div>
                            <button type="submit" class="btn-submit">
                                <i class="fas fa-paper-plane"></i> Gửi phản hồi
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-section" style="margin-top: 20px;">
                        <h3><i class="fas fa-edit"></i> Cập nhật trạng thái</h3>
                        <form method="POST" action="<?= BASE_URL ?>/admin/feedback/update-status">
                            <input type="hidden" name="feedback_id" value="<?= $feedback['id'] ?>">
                            <div class="form-group">
                                <label>Trạng thái mới:</label>
                                <select name="status" required>
                                    <option value="new" <?= $feedback['status'] == 'new' ? 'selected' : '' ?>>Mới</option>
                                    <option value="processing" <?= $feedback['status'] == 'processing' ? 'selected' : '' ?>>Đang xử lý</option>
                                    <option value="resolved" <?= $feedback['status'] == 'resolved' ? 'selected' : '' ?>>Đã giải quyết</option>
                                    <option value="closed" <?= $feedback['status'] == 'closed' ? 'selected' : '' ?>>Đã đóng</option>
                                </select>
                            </div>
                            <button type="submit" class="btn-submit">
                                <i class="fas fa-save"></i> Cập nhật trạng thái
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Toast notification
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.style.cssText = `
                background: white;
                padding: 16px 20px;
                border-radius: 10px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                margin-bottom: 10px;
                display: flex;
                align-items: center;
                gap: 12px;
                min-width: 300px;
                animation: slideIn 0.3s ease-out;
                border-left: 4px solid ${type === 'success' ? '#10b981' : '#ef4444'};
            `;
            
            const icon = type === 'success' ? 
                '<i class="fas fa-check-circle" style="color: #10b981; font-size: 20px;"></i>' :
                '<i class="fas fa-exclamation-circle" style="color: #ef4444; font-size: 20px;"></i>';
            
            toast.innerHTML = `
                ${icon}
                <span style="flex: 1; color: #1f2937; font-weight: 500;">${message}</span>
                <button onclick="this.parentElement.remove()" style="background: none; border: none; color: #9ca3af; cursor: pointer; font-size: 18px;">×</button>
            `;
            
            document.getElementById('toastContainer').appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease-in';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
        
        // Show flash messages
        <?php if (Session::hasFlash('success')): ?>
            showToast('<?= addslashes(Session::getFlash('success')) ?>', 'success');
        <?php endif; ?>
        <?php if (Session::hasFlash('error')): ?>
            showToast('<?= addslashes(Session::getFlash('error')) ?>', 'error');
        <?php endif; ?>
    </script>
</body>
</html>
