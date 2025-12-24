<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Admin - Flower Shop</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-login">
    <div class="auth-container admin">
        <div class="auth-header">
            <div class="logo">
                <i class="fas fa-user-shield"></i>
            </div>
            <h1>Admin Portal</h1>
            <p>Đăng nhập vào trang quản trị</p>
        </div>

        <div class="auth-body">
            <?php
            $old = Session::getFlash('old') ?? [];
            ?>

            <form action="<?= BASE_URL ?>/admin/login" method="POST" id="adminLoginForm">
                <div class="form-group">
                    <label for="identifier">Email hoặc Tên đăng nhập <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-user-shield icon"></i>
                        <input type="text" id="identifier" name="identifier" class="form-control" 
                               placeholder="Nhập email hoặc username"
                               value="<?= htmlspecialchars($old['identifier'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Mật khẩu <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-lock icon"></i>
                        <input type="password" id="password" name="password" class="form-control" 
                               placeholder="Nhập mật khẩu" required>
                        <i class="fas fa-eye toggle-password" onclick="togglePassword('password')"></i>
                    </div>
                </div>

                <div class="form-links">
                    <label>
                        <input type="checkbox" name="remember"> Ghi nhớ đăng nhập
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Đăng Nhập Admin
                </button>
            </form>
        </div>

        <!-- Footer Links -->
        <div class="auth-footer">
            Chưa có tài khoản Admin? <a href="<?= BASE_URL ?>/admin/register-admin">Đăng ký ngay</a>
        </div>
        
        <div class="auth-footer" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
            <a href="<?= BASE_URL ?>" class="home-link">
                <i class="fas fa-home"></i> Quay về trang chủ
            </a>
            <span style="margin: 0 10px; color: #9ca3af;">|</span>
            <a href="<?= BASE_URL ?>/user/login" class="user-link">
                <i class="fas fa-user"></i> Đăng nhập Người dùng
            </a>
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
    </script>
    <?php include APP_PATH . '/views/layouts/toast_notification.php'; ?>
</body>
</html>
