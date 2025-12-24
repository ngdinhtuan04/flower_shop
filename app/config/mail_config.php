<?php
/**
 * Cấu hình Email - PHPMailer
 * Hướng dẫn lấy App Password từ Gmail:
 * 1. Đăng nhập Gmail
 * 2. Vào: https://myaccount.google.com/security
 * 3. Bật "2-Step Verification"
 * 4. Vào "App passwords" → Tạo password cho app
 * 5. Copy password và paste vào MAIL_PASSWORD bên dưới
 */

// Cấu hình SMTP Gmail
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587); // 587 cho TLS, 465 cho SSL
define('MAIL_USERNAME', 'xmeosadx@gmail.com'); // Email Gmail của bạn
define('MAIL_PASSWORD', 'kxsp eewd mjgd wdhm'); // App Password từ Gmail (16 ký tự)
define('MAIL_FROM_EMAIL', 'noreply@flowershop.com'); // Email hiển thị cho người nhận
define('MAIL_FROM_NAME', 'Flower Shop'); // Tên hiển thị

// Hoặc dùng SMTP khác (Mailtrap, SendGrid, etc.)
/*
define('MAIL_HOST', 'smtp.mailtrap.io');
define('MAIL_PORT', 2525);
define('MAIL_USERNAME', 'your-mailtrap-username');
define('MAIL_PASSWORD', 'your-mailtrap-password');
*/

// Test với Mailtrap (cho development)
// Đăng ký free tại: https://mailtrap.io
/*
define('MAIL_HOST', 'sandbox.smtp.mailtrap.io');
define('MAIL_PORT', 2525);
define('MAIL_USERNAME', 'your-mailtrap-username');
define('MAIL_PASSWORD', 'your-mailtrap-password');
define('MAIL_FROM_EMAIL', 'noreply@flowershop.test');
define('MAIL_FROM_NAME', 'Flower Shop Dev');
*/