<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực OTP Admin - Flower Shop</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="admin-login">
    <div class="auth-container admin">
        <div class="auth-header">
            <div class="logo">
                <i class="fas fa-envelope-open-text"></i>
            </div>
            <h1>Xác nhận Email</h1>
            <p>Nhập mã OTP đã gửi đến email của bạn</p>
        </div>

        <div class="auth-body">
            <div class="alert alert-warning">
                <i class="fas fa-info-circle"></i>
                <span><strong>Kiểm tra email của bạn!</strong><br>
                Chúng tôi đã gửi mã OTP 6 chữ số đến <strong><?= htmlspecialchars($email) ?></strong>. 
                Sau khi xác nhận email, tài khoản admin của bạn sẽ chờ SuperAdmin phê duyệt.</span>
            </div>

            <form action="<?= BASE_URL ?>/admin/verify-otp-admin" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="otp">
                        <i class="fas fa-key"></i> Mã OTP <span class="required">*</span>
                    </label>
                    <div class="input-group">
                        <i class="fas fa-key icon"></i>
                        <input 
                            type="text" 
                            id="otp" 
                            name="otp" 
                            class="form-control"
                            placeholder="Nhập mã OTP 6 chữ số"
                            maxlength="6"
                            pattern="[0-9]{6}"
                            required
                            autofocus
                        >
                    </div>
                    <small style="color: #6b7280; margin-top: 5px; display: block;">
                        Mã OTP có hiệu lực trong <?= OTP_EXPIRY_MINUTES ?> phút
                    </small>
                </div>

                <button type="submit" class="btn">
                    <i class="fas fa-check-circle"></i>
                    <span>Xác nhận OTP</span>
                </button>
            </form>

            <div class="divider">
                <span>Không nhận được mã?</span>
            </div>

            <button type="button" class="btn" onclick="resendOTP()" style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);">
                <i class="fas fa-redo"></i>
                <span>Gửi lại mã OTP</span>
            </button>
        </div>

        <div class="auth-footer">
            <a href="<?= BASE_URL ?>/admin/login" class="home-link">
                <i class="fas fa-arrow-left"></i> Quay lại đăng nhập
            </a>
        </div>
    </div>

    <script>
        function resendOTP() {
            if (!confirm('Bạn có chắc muốn gửi lại mã OTP?')) {
                return;
            }

            fetch('<?= BASE_URL ?>/admin/resend-otp-admin', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Đã gửi lại mã OTP! Vui lòng kiểm tra email.');
                    document.getElementById('otp').value = '';
                    document.getElementById('otp').focus();
                } else {
                    alert(data.message || 'Có lỗi xảy ra. Vui lòng thử lại!');
                }
            })
            .catch(error => {
                alert('Có lỗi xảy ra. Vui lòng thử lại!');
                console.error('Error:', error);
            });
        }

        // Auto focus on OTP input
        document.getElementById('otp').focus();

        // Only allow numbers
        document.getElementById('otp').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
    <?php include APP_PATH . '/views/layouts/toast_notification.php'; ?>
</body>
</html>
