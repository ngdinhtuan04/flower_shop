<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác Thực OTP - Quên Mật Khẩu - Flower Shop</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/auth.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/otp.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <div class="logo">🔐</div>
            <h1>Xác Thực OTP</h1>
            <p>Nhập mã OTP để tiếp tục đặt lại mật khẩu</p>
        </div>

        <div class="auth-body">
            <div class="email-info">
                <i class="fas fa-envelope"></i>
                <p>Mã OTP đã được gửi đến:</p>
                <p><strong><?= htmlspecialchars($email) ?></strong></p>
                <p style="font-size: 13px; color: #7f8c8d; margin-top: 10px;">
                    <i class="fas fa-clock"></i> Mã có hiệu lực trong <?= OTP_EXPIRY_MINUTES ?> phút
                </p>
            </div>

            <form action="<?= BASE_URL ?>/user/verify-forgot-password-otp" method="POST" id="otpForm">
                <div class="form-group">
                    <label for="otp">Mã OTP (6 số) <span class="required">*</span></label>
                    <input type="text" 
                           id="otp" 
                           name="otp" 
                           class="otp-input" 
                           placeholder="000000"
                           maxlength="6"
                           pattern="[0-9]{6}"
                           inputmode="numeric"
                           autocomplete="off"
                           required
                           autofocus>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i> Xác Nhận OTP
                </button>
            </form>

            <div class="resend-otp">
                <p style="color: #7f8c8d; font-size: 14px;">Không nhận được mã?</p>
                <button type="button" onclick="resendOTP()">
                    <i class="fas fa-redo"></i> Gửi lại mã OTP
                </button>
            </div>
        </div>

        <div class="auth-footer">
            <a href="<?= BASE_URL ?>/user/forgot-password" class="home-link">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>
    
    <script src="<?= ASSETS_URL ?>/js/confirm-dialog.js"></script>
    <script src="<?= ASSETS_URL ?>/js/forgot_password_otp.js"></script>
    <?php include APP_PATH . '/views/layouts/toast_notification.php'; ?>
</body>
</html>
