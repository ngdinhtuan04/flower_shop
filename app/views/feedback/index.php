<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Góp ý - Flower Shop</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/home.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/notifications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/feedback-index.css">
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>
    
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="<?= BASE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a>
        <span class="separator">/</span>
        <span class="current">Góp ý</span>
    </div>
    
    <div class="feedback-container">
        <div class="info-box">
            <h3><i class="fas fa-info-circle"></i> Chúng tôi có thể giúp gì cho bạn?</h3>
            <ul>
                <li><i class="fas fa-check"></i> Góp ý về sản phẩm và dịch vụ</li>
                <li><i class="fas fa-check"></i> Khiếu nại về chất lượng đơn hàng</li>
                <li><i class="fas fa-check"></i> Hỏi đáp về chính sách và quy định</li>
                <li><i class="fas fa-check"></i> Các yêu cầu hỗ trợ khác</li>
            </ul>
        </div>
        
        <div class="feedback-form">
            <form method="POST" action="<?= BASE_URL ?>/feedback/submit">
                <div class="form-row">
                    <div class="form-group">
                        <label>Họ tên <span class="required">*</span></label>
                        <input type="text" name="name" required 
                               value="<?= $user ? htmlspecialchars($user['full_name']) : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Email <span class="required">*</span></label>
                        <input type="email" name="email" required 
                               value="<?= $user ? htmlspecialchars($user['email']) : '' ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Số điện thoại</label>
                        <input type="tel" name="phone" 
                               value="<?= $user ? htmlspecialchars($user['phone'] ?? '') : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Loại góp ý <span class="required">*</span></label>
                        <select name="type" required>
                            <option value="suggestion">Góp ý</option>
                            <option value="complaint">Khiếu nại</option>
                            <option value="question">Câu hỏi</option>
                            <option value="other">Khác</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Tiêu đề <span class="required">*</span></label>
                    <input type="text" name="subject" required 
                           placeholder="Nhập tiêu đề góp ý của bạn">
                </div>
                
                <div class="form-group">
                    <label>Nội dung <span class="required">*</span></label>
                    <textarea name="message" required 
                              placeholder="Mô tả chi tiết vấn đề hoặc góp ý của bạn..."></textarea>
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Gửi góp ý
                </button>
            </form>
            
            <?php if ($user): ?>
            <div class="feedback-link">
                <a href="<?= BASE_URL ?>/feedback/my-feedback">
                    <i class="fas fa-history"></i> Xem lịch sử góp ý của tôi
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

    <?php include APP_PATH . '/views/layouts/toast_notification.php'; ?>
</body>
</html>
