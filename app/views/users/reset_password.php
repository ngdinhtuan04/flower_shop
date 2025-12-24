<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Lại Mật Khẩu - Flower Shop</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <div class="logo">🔒</div>
            <h1>Đặt Lại Mật Khẩu</h1>
            <p>Nhập mật khẩu mới của bạn</p>
        </div>

        <div class="auth-body">
            <form action="<?= BASE_URL ?>/user/reset-password?token=<?= htmlspecialchars($token) ?>" method="POST" id="resetForm">
                <div class="form-group">
                    <label for="password">Mật khẩu mới <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-lock icon"></i>
                        <input type="password" id="password" name="password" class="form-control" 
                               placeholder="Nhập mật khẩu mới (tối thiểu 6 ký tự)" required>
                        <i class="fas fa-eye toggle-password" onclick="togglePassword('password')"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirm">Xác nhận mật khẩu <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-lock icon"></i>
                        <input type="password" id="password_confirm" name="password_confirm" class="form-control" 
                               placeholder="Nhập lại mật khẩu mới" required>
                        <i class="fas fa-eye toggle-password" onclick="togglePassword('password_confirm')"></i>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i> Đặt Lại Mật Khẩu
                </button>
            </form>
        </div>

        <div class="auth-footer">
            <a href="<?= BASE_URL ?>/user/login">Quay lại đăng nhập</a>
        </div>
    </div>

    <script src="<?= ASSETS_URL ?>/js/reset_password.js"></script>
    <?php include APP_PATH . '/views/layouts/toast_notification.php'; ?>
</body>
</html>
