<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên Mật Khẩu - Flower Shop</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <div class="logo">🔑</div>
            <h1>Quên Mật Khẩu?</h1>
            <p>Nhập email để nhận mã OTP xác thực</p>
        </div>

        <div class="auth-body">
            <form action="<?= BASE_URL ?>/user/forgot-password" method="POST">
                <div class="form-group">
                    <label for="email">Địa chỉ Email <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-envelope icon"></i>
                        <input type="email" id="email" name="email" class="form-control" 
                               placeholder="Nhập email đã đăng ký" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Gửi Mã OTP
                </button>
            </form>
        </div>

        <div class="auth-footer">
            Nhớ mật khẩu rồi? <a href="<?= BASE_URL ?>/user/login">Đăng nhập</a>
        </div>
    </div>
    <?php include APP_PATH . '/views/layouts/toast_notification.php'; ?>
</body>
</html>