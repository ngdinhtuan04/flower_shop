<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký - Flower Shop</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="auth-header">
                <div class="logo">🌸</div>
                <h1>Tạo Tài Khoản Mới</h1>
                <p>Đăng ký để bắt đầu mua sắm</p>
            </div>

            <div class="auth-body">
                <?php
                $old = Session::getFlash('old') ?? [];
                $errors = Session::getFlash('errors') ?? [];
                ?>

            <form action="<?= BASE_URL ?>/user/register" method="POST" id="registerForm">
                <div class="form-group">
                    <label for="username">Tên đăng nhập <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-user icon"></i>
                        <input type="text" id="username" name="username" class="form-control <?= isset($errors['username']) ? 'error' : '' ?>"
                               placeholder="Nhập tên đăng nhập" value="<?= htmlspecialchars($old['username'] ?? '') ?>" required>
                    </div>
                    <?php if (isset($errors['username'])): ?>
                        <div class="error-message"><i class="fas fa-times-circle"></i><?= $errors['username'] ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-envelope icon"></i>
                        <input type="email" id="email" name="email" class="form-control <?= isset($errors['email']) ? 'error' : '' ?>"
                               placeholder="Nhập địa chỉ email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
                    </div>
                    <?php if (isset($errors['email'])): ?>
                        <div class="error-message"><i class="fas fa-times-circle"></i><?= $errors['email'] ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="full_name">Họ và tên <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-id-card icon"></i>
                        <input type="text" id="full_name" name="full_name" class="form-control"
                               placeholder="Nhập họ và tên" value="<?= htmlspecialchars($old['full_name'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="phone">Số điện thoại</label>
                    <div class="input-group">
                        <i class="fas fa-phone icon"></i>
                        <input type="tel" id="phone" name="phone" class="form-control"
                               placeholder="Nhập số điện thoại" value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Mật khẩu <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-lock icon"></i>
                        <input type="password" id="password" name="password" class="form-control"
                               placeholder="Nhập mật khẩu (tối thiểu 6 ký tự)" required>
                        <i class="fas fa-eye toggle-password" onclick="togglePassword('password')"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password_confirm">Xác nhận mật khẩu <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-lock icon"></i>
                        <input type="password" id="password_confirm" name="password_confirm" class="form-control"
                               placeholder="Nhập lại mật khẩu" required>
                        <i class="fas fa-eye toggle-password" onclick="togglePassword('password_confirm')"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" required> Tôi đồng ý với <a href="#">Điều khoản sử dụng</a>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Đăng Ký
                </button>
            </form>
        </div>

        <div class="auth-footer">
            Đã có tài khoản? <a href="<?= BASE_URL ?>/user/login">Đăng nhập ngay</a>
        </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = event.target;
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('password_confirm').value;

            if (password !== confirm) {
                e.preventDefault();
                alert('Mật khẩu xác nhận không khớp!');
                return false;
            }

            if (password.length < 6) {
                e.preventDefault();
                alert('Mật khẩu phải có ít nhất 6 ký tự!');
                return false;
            }
        });
    </script>
    <?php include APP_PATH . '/views/layouts/toast_notification.php'; ?>
</body>
</html>