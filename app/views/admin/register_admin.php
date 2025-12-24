<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký Admin - Flower Shop</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="admin-login">
    <div class="auth-container admin">
        <div class="auth-header">
            <div class="logo">
                <i class="fas fa-user-shield"></i>
            </div>
            <h1>Đăng ký Admin</h1>
            <p>Tài khoản cần được SuperAdmin phê duyệt</p>
        </div>

        <div class="auth-body">

            <?php
            $errors = Session::getFlash('errors') ?? [];
            $old = Session::getFlash('old') ?? [];
            ?>

            <form action="<?= BASE_URL ?>/admin/register-admin" method="POST" class="auth-form">
                <!-- Username -->
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i> Tên đăng nhập <span class="required">*</span>
                    </label>
                    <div class="input-group">
                        <i class="fas fa-user icon"></i>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="form-control"
                            value="<?= htmlspecialchars($old['username'] ?? '') ?>"
                            placeholder="Nhập tên đăng nhập"
                            required
                        >
                    </div>
                    <?php if (isset($errors['username'])): ?>
                        <span class="error-message"><?= htmlspecialchars($errors['username']) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email <span class="required">*</span>
                    </label>
                    <div class="input-group">
                        <i class="fas fa-envelope icon"></i>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-control"
                            value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                            placeholder="Nhập email"
                            required
                        >
                    </div>
                    <?php if (isset($errors['email'])): ?>
                        <span class="error-message"><?= htmlspecialchars($errors['email']) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Full Name -->
                <div class="form-group">
                    <label for="full_name">
                        <i class="fas fa-id-card"></i> Họ và tên <span class="required">*</span>
                    </label>
                    <div class="input-group">
                        <i class="fas fa-id-card icon"></i>
                        <input 
                            type="text" 
                            id="full_name" 
                            name="full_name" 
                            class="form-control"
                            value="<?= htmlspecialchars($old['full_name'] ?? '') ?>"
                            placeholder="Nhập họ và tên"
                            required
                        >
                    </div>
                    <?php if (isset($errors['full_name'])): ?>
                        <span class="error-message"><?= htmlspecialchars($errors['full_name']) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Phone -->
                <div class="form-group">
                    <label for="phone">
                        <i class="fas fa-phone"></i> Số điện thoại
                    </label>
                    <div class="input-group">
                        <i class="fas fa-phone icon"></i>
                        <input 
                            type="tel" 
                            id="phone" 
                            name="phone" 
                            class="form-control"
                            value="<?= htmlspecialchars($old['phone'] ?? '') ?>"
                            placeholder="Nhập số điện thoại"
                        >
                    </div>
                    <?php if (isset($errors['phone'])): ?>
                        <span class="error-message"><?= htmlspecialchars($errors['phone']) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Mật khẩu <span class="required">*</span>
                    </label>
                    <div class="input-group">
                        <i class="fas fa-lock icon"></i>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control"
                            placeholder="Nhập mật khẩu (tối thiểu 6 ký tự)"
                            required
                        >
                        <i class="fas fa-eye toggle-password" onclick="togglePassword('password')"></i>
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <span class="error-message"><?= htmlspecialchars($errors['password']) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="password_confirm">
                        <i class="fas fa-lock"></i> Xác nhận mật khẩu <span class="required">*</span>
                    </label>
                    <div class="input-group">
                        <i class="fas fa-lock icon"></i>
                        <input 
                            type="password" 
                            id="password_confirm" 
                            name="password_confirm" 
                            class="form-control"
                            placeholder="Nhập lại mật khẩu"
                            required
                        >
                        <i class="fas fa-eye toggle-password" onclick="togglePassword('password_confirm')"></i>
                    </div>
                    <?php if (isset($errors['password_confirm'])): ?>
                        <span class="error-message"><?= htmlspecialchars($errors['password_confirm']) ?></span>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn">
                    <i class="fas fa-user-plus"></i>
                    <span>Đăng ký Admin</span>
                </button>
            </form>
        </div>

        <div class="auth-footer">
            Đã có tài khoản? <a href="<?= BASE_URL ?>/admin/login">Đăng nhập ngay</a>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const toggleIcon = field.parentElement.querySelector('.toggle-password');
            
            if (field.type === 'password') {
                field.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Form validation
        document.querySelector('.auth-form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const passwordConfirm = document.getElementById('password_confirm').value;
            
            if (password !== passwordConfirm) {
                e.preventDefault();
                alert('Mật khẩu xác nhận không khớp!');
                return false;
            }
        });
    </script>
    <?php include APP_PATH . '/views/layouts/toast_notification.php'; ?>
</body>
</html>
