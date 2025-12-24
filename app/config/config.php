<?php
/**
 * File cấu hình chính của ứng dụng
 */

// Cấu hình Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'flower_shop');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Cấu hình đường dẫn
define('ROOT_PATH', dirname(dirname(__DIR__)));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');

// Cấu hình URL
define('BASE_URL', 'https://imputedly-lamellose-marni.ngrok-free.dev/flower_shop/public');
define('ASSETS_URL', BASE_URL . '/assets');
define('UPLOAD_URL', BASE_URL . '/uploads');

// Load mail config
require_once __DIR__ . '/mail_config.php';

// Cấu hình Session
define('SESSION_NAME', 'FLOWER_SHOP_SESSION');
define('SESSION_LIFETIME', 7200); // 2 hours

// Cấu hình bảo mật
define('PASSWORD_MIN_LENGTH', 6);
define('OTP_LENGTH', 6);
define('OTP_EXPIRY_MINUTES', 15);
define('RESET_TOKEN_EXPIRY_HOURS', 1);

// Cấu hình khác
define('ITEMS_PER_PAGE', 12);
define('TIMEZONE', 'Asia/Ho_Chi_Minh');

// Set timezone
date_default_timezone_set(TIMEZONE);

// Bật hiển thị lỗi (chỉ dùng trong development)
error_reporting(E_ALL);
ini_set('display_errors', 1);