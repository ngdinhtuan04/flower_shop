<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - Flower Shop</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-container">
            <!-- Header -->
            <div class="auth-header">
                <div class="logo">🌸</div>
                <h1>Chào Mừng Trở Lại!</h1>
                <p>Đăng nhập để tiếp tục mua sắm</p>
            </div>

            <!-- Body -->
            <div class="auth-body">
                <?php
                $old = Session::getFlash('old') ?? [];
                ?>

            <!-- Form đăng nhập -->
            <form action="<?= BASE_URL ?>/user/login" method="POST" id="loginForm">
                <!-- Email hoặc Username -->
                <div class="form-group">
                    <label for="identifier">Email hoặc Tên đăng nhập <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-user icon"></i>
                        <input type="text" 
                               id="identifier" 
                               name="identifier" 
                               class="form-control" 
                               placeholder="Nhập email hoặc tên đăng nhập"
                               value="<?= htmlspecialchars($old['identifier'] ?? '') ?>"
                               required>
                    </div>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Mật khẩu <span class="required">*</span></label>
                    <div class="input-group">
                        <i class="fas fa-lock icon"></i>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-control" 
                               placeholder="Nhập mật khẩu"
                               required>
                        <i class="fas fa-eye toggle-password" onclick="togglePassword('password')"></i>
                    </div>
                </div>

                <!-- Remember & Forgot Password -->
                <div class="form-links">
                    <label>
                        <input type="checkbox" name="remember"> Ghi nhớ đăng nhập
                    </label>
                    <a href="<?= BASE_URL ?>/user/forgot-password">Quên mật khẩu?</a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Đăng Nhập
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="auth-footer">
            Chưa có tài khoản? <a href="<?= BASE_URL ?>/user/register">Đăng ký ngay</a>
        </div>

        <!-- Additional Links -->
        <div class="auth-footer" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
            <a href="<?= BASE_URL ?>" class="home-link">
                <i class="fas fa-home"></i> Quay về trang chủ
            </a>
            <span style="margin: 0 10px;">|</span>
            <a href="<?= BASE_URL ?>/admin/login" class="admin-link">
                <i class="fas fa-user-shield"></i> Đăng nhập Admin
            </a>
        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = event.target;
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const identifier = document.getElementById('identifier').value.trim();
            const password = document.getElementById('password').value;

            if (!identifier) {
                e.preventDefault();
                alert('Vui lòng nhập email hoặc tên đăng nhập');
                return false;
            }

            if (!password) {
                e.preventDefault();
                alert('Vui lòng nhập mật khẩu');
                return false;
            }
        });
    </script>
    <?php include APP_PATH . '/views/layouts/toast_notification.php'; ?>
</body>
</html>