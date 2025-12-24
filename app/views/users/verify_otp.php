<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác Thực OTP - Flower Shop</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <div class="logo">🔐</div>
            <h1>Xác Nhận Email</h1>
            <p>Mã OTP đã được gửi đến<br><strong><?= htmlspecialchars($email) ?></strong></p>
        </div>

        <div class="auth-body">
            <form action="<?= BASE_URL ?>/user/verify-otp" method="POST" id="otpForm">
                <div class="form-group">
                    <label for="otp">Mã OTP (6 chữ số) <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-key icon"></i>
                        <input type="text" id="otp" name="otp" class="form-control" 
                               placeholder="Nhập mã OTP" maxlength="6" pattern="[0-9]{6}" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i> Xác Nhận
                </button>

                <div class="divider">
                    <span>Không nhận được mã?</span>
                </div>

                <button type="button" class="btn btn-secondary" onclick="resendOTP()">
                    <i class="fas fa-redo"></i> Gửi Lại Mã OTP
                </button>
            </form>
        </div>

        <div class="auth-footer">
            <a href="<?= BASE_URL ?>/user/login">Quay lại đăng nhập</a>
        </div>
    </div>

    <script src="<?= ASSETS_URL ?>/js/confirm-dialog.js"></script>
    <script src="<?= ASSETS_URL ?>/js/otp.js"></script>
    <?php include APP_PATH . '/views/layouts/toast_notification.php'; ?>
</body>
</html>
