<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử góp ý - Flower Shop</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/home.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/notifications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/feedback-my-feedback.css">
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>
    
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="<?= BASE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a>
        <span class="separator">/</span>
        <a href="<?= BASE_URL ?>/feedback">Góp ý</a>
        <span class="separator">/</span>
        <span class="current">Lịch sử góp ý</span>
    </div>
    
    <div class="feedback-history-container">
        <div class="feedback-actions">
            <h2>Danh sách góp ý (<?= count($feedbacks) ?>)</h2>
            <a href="<?= BASE_URL ?>/feedback" class="btn-new-feedback">
                <i class="fas fa-plus"></i> Gửi góp ý mới
            </a>
        </div>
        
        <div class="feedback-list">
            <?php if (empty($feedbacks)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>Chưa có góp ý nào</h3>
                    <p>Bạn chưa gửi góp ý nào. Hãy chia sẻ ý kiến của bạn với chúng tôi!</p>
                </div>
            <?php else: ?>
                <?php foreach ($feedbacks as $feedback): ?>
                    <div class="feedback-item">
                        <div class="feedback-header-row">
                            <div>
                                <div class="feedback-title"><?= htmlspecialchars($feedback['subject']) ?></div>
                                <div class="feedback-meta">
                                    <span class="feedback-type type-<?= $feedback['type'] ?>">
                                        <?php 
                                        $typeLabels = [
                                            'suggestion' => 'Góp ý',
                                            'complaint' => 'Khiếu nại',
                                            'question' => 'Câu hỏi',
                                            'product_inquiry' => 'Hỏi về sản phẩm',
                                            'other' => 'Khác'
                                        ];
                                        echo $typeLabels[$feedback['type']] ?? 'Khác';
                                        ?>
                                    </span>
                                    <span><i class="far fa-clock"></i> <?= date('d/m/Y H:i', strtotime($feedback['created_at'])) ?></span>
                                </div>
                            </div>
                            <span class="feedback-status status-<?= $feedback['status'] ?>">
                                <?php 
                                $statusLabels = [
                                    'new' => 'Mới',
                                    'processing' => 'Đang xử lý',
                                    'resolved' => 'Đã giải quyết',
                                    'closed' => 'Đã đóng'
                                ];
                                echo $statusLabels[$feedback['status']];
                                ?>
                            </span>
                        </div>
                        
                        <div class="feedback-message">
                            <?= nl2br(htmlspecialchars($feedback['message'])) ?>
                        </div>
                        
                        <?php if ($feedback['admin_reply']): ?>
                            <div class="feedback-reply">
                                <div class="reply-header">
                                    <i class="fas fa-reply"></i> Phản hồi từ admin
                                    <?php if ($feedback['replied_at']): ?>
                                        - <?= date('d/m/Y H:i', strtotime($feedback['replied_at'])) ?>
                                    <?php endif; ?>
                                </div>
                                <div class="reply-content">
                                    <?= nl2br(htmlspecialchars($feedback['admin_reply'])) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>
