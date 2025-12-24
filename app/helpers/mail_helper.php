<?php
/**
 * Mail Helper - Gửi email bằng PHPMailer
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Khởi tạo PHPMailer instance
 */
function getMailer()
{
    require_once ROOT_PATH . '/vendor/phpmailer/phpmailer/src/Exception.php';
    require_once ROOT_PATH . '/vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once ROOT_PATH . '/vendor/phpmailer/phpmailer/src/SMTP.php';

    $mail = new PHPMailer(true);
    
    try {
        // Cấu hình SMTP
        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = MAIL_PORT;
        $mail->CharSet = 'UTF-8';

        // Người gửi
        $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
        
        return $mail;
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Gửi email OTP cho user mới đăng ký
 */
function sendOTPEmail($email, $otp, $fullName = 'Bạn')
{
    $mail = getMailer();
    if (!$mail) return false;

    try {
        // Người nhận
        $mail->addAddress($email, $fullName);

        // Nội dung email
        $mail->isHTML(true);
        $mail->Subject = '🌸 Mã xác nhận OTP - Flower Shop';
        
        $htmlBody = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #e91e63, #9c27b0); color: white; padding: 40px; text-align: center; }
                .header h1 { margin: 0; font-size: 28px; }
                .body { padding: 40px 30px; }
                .otp-box { background: #f0f0f0; padding: 20px; border-radius: 10px; text-align: center; margin: 30px 0; border: 2px dashed #e91e63; }
                .otp-code { font-size: 42px; font-weight: bold; color: #e91e63; letter-spacing: 8px; margin: 10px 0; }
                .info { color: #666; font-size: 14px; line-height: 1.8; }
                .footer { background: #f5f5f5; padding: 20px; text-align: center; color: #666; font-size: 13px; }
                .btn { display: inline-block; background: linear-gradient(135deg, #e91e63, #9c27b0); color: white; padding: 14px 30px; text-decoration: none; border-radius: 10px; margin: 20px 0; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <div style="font-size: 50px; margin-bottom: 10px;">🌸</div>
                    <h1>Flower Shop</h1>
                    <p>Xác nhận đăng ký tài khoản</p>
                </div>
                <div class="body">
                    <p>Xin chào <strong>' . htmlspecialchars($fullName) . '</strong>,</p>
                    <p class="info">Cảm ơn bạn đã đăng ký tài khoản tại <strong>Flower Shop</strong>!</p>
                    <p class="info">Để hoàn tất quá trình đăng ký, vui lòng nhập mã OTP bên dưới:</p>
                    
                    <div class="otp-box">
                        <p style="margin: 0; color: #666; font-size: 14px;">Mã xác nhận của bạn:</p>
                        <div class="otp-code">' . $otp . '</div>
                        <p style="margin: 0; color: #999; font-size: 13px;">Mã có hiệu lực trong ' . OTP_EXPIRY_MINUTES . ' phút</p>
                    </div>

                    <p class="info">
                        ⚠️ <strong>Lưu ý:</strong><br>
                        • Không chia sẻ mã OTP với bất kỳ ai<br>
                        • Nếu bạn không thực hiện đăng ký này, vui lòng bỏ qua email<br>
                        • Mã OTP chỉ sử dụng được 1 lần
                    </p>

                    <p class="info">Trân trọng,<br><strong>Đội ngũ Flower Shop</strong></p>
                </div>
                <div class="footer">
                    <p>Email này được gửi tự động, vui lòng không trả lời.</p>
                    <p>© 2025 Flower Shop. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ';

        $mail->Body = $htmlBody;
        $mail->AltBody = "Mã OTP của bạn là: $otp\n\nMã có hiệu lực trong " . OTP_EXPIRY_MINUTES . " phút.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Send OTP Email Error: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Gửi email OTP cho forgot password (Quên mật khẩu)
 */
function sendForgotPasswordOTPEmail($email, $otp, $fullName = 'Bạn')
{
    $mail = getMailer();
    if (!$mail) return false;

    try {
        // Người nhận
        $mail->addAddress($email, $fullName);

        // Nội dung email
        $mail->isHTML(true);
        $mail->Subject = '🔐 Mã xác nhận OTP - Đặt lại mật khẩu - Flower Shop';
        
        $htmlBody = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #ff6b6b, #ee5a6f); color: white; padding: 40px; text-align: center; }
                .header h1 { margin: 0; font-size: 28px; }
                .body { padding: 40px 30px; }
                .otp-box { background: #fff3f3; padding: 20px; border-radius: 10px; text-align: center; margin: 30px 0; border: 2px dashed #ff6b6b; }
                .otp-code { font-size: 42px; font-weight: bold; color: #ff6b6b; letter-spacing: 8px; margin: 10px 0; }
                .info { color: #666; font-size: 14px; line-height: 1.8; }
                .footer { background: #f5f5f5; padding: 20px; text-align: center; color: #666; font-size: 13px; }
                .warning { background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 8px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <div style="font-size: 50px; margin-bottom: 10px;">🔐</div>
                    <h1>Flower Shop</h1>
                    <p>Đặt lại mật khẩu</p>
                </div>
                <div class="body">
                    <p>Xin chào <strong>' . htmlspecialchars($fullName) . '</strong>,</p>
                    <p class="info">Bạn đang thực hiện đặt lại mật khẩu cho tài khoản tại <strong>Flower Shop</strong>.</p>
                    <p class="info">Vui lòng nhập mã OTP bên dưới để tiếp tục:</p>
                    
                    <div class="otp-box">
                        <p style="margin: 0; color: #666; font-size: 14px;">Mã xác nhận của bạn:</p>
                        <div class="otp-code">' . $otp . '</div>
                        <p style="margin: 0; color: #999; font-size: 13px;">Mã có hiệu lực trong ' . OTP_EXPIRY_MINUTES . ' phút</p>
                    </div>

                    <div class="warning">
                        <p style="margin: 0; color: #856404;"><strong>⚠️ Lưu ý quan trọng:</strong></p>
                        <ul style="margin: 10px 0 0 0; padding-left: 20px; color: #856404;">
                            <li>Không chia sẻ mã OTP với bất kỳ ai</li>
                            <li>Nếu bạn không thực hiện thao tác này, vui lòng bỏ qua email và liên hệ với chúng tôi ngay</li>
                            <li>Mã OTP chỉ sử dụng được 1 lần</li>
                        </ul>
                    </div>

                    <p class="info">Sau khi xác thực OTP, bạn sẽ nhận được link đặt lại mật khẩu qua email.</p>

                    <p class="info">Trân trọng,<br><strong>Đội ngũ Flower Shop</strong></p>
                </div>
                <div class="footer">
                    <p>Email này được gửi tự động, vui lòng không trả lời.</p>
                    <p>© 2025 Flower Shop. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ';

        $mail->Body = $htmlBody;
        $mail->AltBody = "Mã OTP đặt lại mật khẩu của bạn là: $otp\n\nMã có hiệu lực trong " . OTP_EXPIRY_MINUTES . " phút.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Send Forgot Password OTP Email Error: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Gửi email reset password
 */
function sendResetPasswordEmail($email, $resetToken, $fullName = 'Bạn')
{
    $mail = getMailer();
    if (!$mail) return false;

    try {
        $mail->addAddress($email, $fullName);
        
        $resetLink = BASE_URL . "/user/reset-password?token=" . $resetToken;

        $mail->isHTML(true);
        $mail->Subject = '🔐 Đặt lại mật khẩu - Flower Shop';
        
        $htmlBody = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #e91e63, #9c27b0); color: white; padding: 40px; text-align: center; }
                .header h1 { margin: 0; font-size: 28px; }
                .body { padding: 40px 30px; }
                .info { color: #666; font-size: 14px; line-height: 1.8; }
                .btn { display: inline-block; background: linear-gradient(135deg, #e91e63, #9c27b0); color: white; padding: 14px 30px; text-decoration: none; border-radius: 10px; margin: 20px 0; font-weight: bold; }
                .token-box { background: #f9f9f9; padding: 15px; border-radius: 8px; border-left: 4px solid #e91e63; margin: 20px 0; }
                .footer { background: #f5f5f5; padding: 20px; text-align: center; color: #666; font-size: 13px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <div style="font-size: 50px; margin-bottom: 10px;">🔑</div>
                    <h1>Đặt Lại Mật Khẩu</h1>
                </div>
                <div class="body">
                    <p>Xin chào <strong>' . htmlspecialchars($fullName) . '</strong>,</p>
                    <p class="info">Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn tại <strong>Flower Shop</strong>.</p>
                    
                    <p class="info">Click vào nút bên dưới để đặt lại mật khẩu:</p>
                    
                    <div style="text-align: center;">
                        <a href="' . $resetLink . '" class="btn">🔐 Đặt Lại Mật Khẩu</a>
                    </div>

                    <div class="token-box">
                        <p style="margin: 0; font-size: 13px; color: #666;">
                            Hoặc copy link sau vào trình duyệt:<br>
                            <span style="color: #e91e63; word-break: break-all;">' . $resetLink . '</span>
                        </p>
                    </div>

                    <p class="info">
                        ⚠️ <strong>Lưu ý quan trọng:</strong><br>
                        • Link có hiệu lực trong <strong>' . RESET_TOKEN_EXPIRY_HOURS . ' giờ</strong><br>
                        • Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này<br>
                        • Không chia sẻ link này với bất kỳ ai
                    </p>

                    <p class="info">Trân trọng,<br><strong>Đội ngũ Flower Shop</strong></p>
                </div>
                <div class="footer">
                    <p>Email này được gửi tự động, vui lòng không trả lời.</p>
                    <p>© 2025 Flower Shop. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ';

        $mail->Body = $htmlBody;
        $mail->AltBody = "Đặt lại mật khẩu tại: $resetLink\n\nLink có hiệu lực trong " . RESET_TOKEN_EXPIRY_HOURS . " giờ.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Send Reset Password Email Error: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Gửi email thông báo đơn hàng
 */
function sendOrderConfirmationEmail($email, $orderData, $orderItems = [])
{
    $mail = getMailer();
    if (!$mail) return false;

    try {
        $mail->addAddress($email, $orderData['customer_name']);

        $mail->isHTML(true);
        $mail->Subject = '✅ Xác nhận đơn hàng #' . $orderData['order_code'] . ' - Flower Shop';
        
        // Build items HTML
        $itemsHTML = '';
        foreach ($orderItems as $item) {
            $imagePath = $item['product_image'];
            if (!preg_match('/^http/i', $imagePath)) {
                $imagePath = BASE_URL . '/' . $imagePath;
            }
            
            $itemsHTML .= '
            <tr>
                <td style="padding: 15px; border-bottom: 1px solid #eee;">
                    <table cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td width="80" style="padding-right: 15px;">
                                <img src="' . $imagePath . '" width="70" height="70" style="border-radius: 8px; object-fit: cover;">
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #2d3748; margin-bottom: 5px;">' . htmlspecialchars($item['product_name']) . '</div>
                                <div style="color: #718096; font-size: 13px;">Số lượng: x' . $item['quantity'] . '</div>
                                <div style="color: #718096; font-size: 13px;">Đơn giá: ' . number_format($item['price']) . 'đ</div>
                            </td>
                            <td align="right" style="font-weight: 700; color: #e53e3e; font-size: 15px;">
                                ' . number_format($item['subtotal']) . 'đ
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>';
        }
        
        $discountHTML = '';
        if (!empty($orderData['discount']) && $orderData['discount'] > 0) {
            $couponText = !empty($orderData['coupon_code']) ? ' (' . $orderData['coupon_code'] . ')' : '';
            $discountHTML = '
            <tr>
                <td style="padding: 10px 0; color: #48bb78;">
                    <strong>Giảm giá' . $couponText . ':</strong>
                </td>
                <td align="right" style="padding: 10px 0; color: #48bb78; font-weight: 600;">
                    -' . number_format($orderData['discount']) . 'đ
                </td>
            </tr>';
        }
        
        $trackingLink = BASE_URL . '/tracking?code=' . $orderData['order_code'];
        
        $htmlBody = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; margin: 0; }
                .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #ff6b9d 0%, #c44569 100%); color: white; padding: 40px; text-align: center; }
                .header h1 { margin: 0; font-size: 28px; }
                .body { padding: 40px 30px; }
                .order-code { background: #fff5f7; padding: 20px; border-radius: 10px; text-align: center; margin: 25px 0; border-left: 4px solid #ff6b9d; }
                .order-code-text { font-size: 24px; font-weight: bold; color: #ff6b9d; letter-spacing: 2px; }
                .info-box { background: #f7fafc; padding: 20px; border-radius: 10px; margin: 20px 0; }
                .info-row { display: table; width: 100%; padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
                .info-row:last-child { border-bottom: none; }
                .info-label { display: table-cell; color: #718096; width: 140px; }
                .info-value { display: table-cell; color: #2d3748; font-weight: 600; }
                .btn { display: inline-block; background: linear-gradient(135deg, #ff6b9d 0%, #c44569 100%); color: white !important; padding: 14px 30px; text-decoration: none; border-radius: 10px; margin: 20px 0; font-weight: bold; }
                .footer { background: #f5f5f5; padding: 20px; text-align: center; color: #666; font-size: 13px; }
                .summary-table { width: 100%; margin-top: 20px; }
                .summary-table td { padding: 10px 0; border-bottom: 1px solid #e2e8f0; }
                .summary-total { font-size: 20px; font-weight: bold; color: #e53e3e; padding-top: 15px; border-top: 2px solid #e2e8f0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <div style="font-size: 50px; margin-bottom: 10px;">🌸</div>
                    <h1>Đặt Hàng Thành Công!</h1>
                    <p style="margin: 10px 0 0 0; opacity: 0.9;">Cảm ơn bạn đã tin tưởng Flower Shop</p>
                </div>
                <div class="body">
                    <p>Xin chào <strong>' . htmlspecialchars($orderData['customer_name']) . '</strong>,</p>
                    <p>Đơn hàng của bạn đã được đặt <strong>thành công</strong>! Chúng tôi sẽ xử lý và giao hàng trong thời gian sớm nhất.</p>
                    
                    <div class="order-code">
                        <p style="margin: 0 0 10px 0; color: #718096; font-size: 14px;">Mã đơn hàng của bạn:</p>
                        <div class="order-code-text">' . $orderData['order_code'] . '</div>
                        <p style="margin: 15px 0 0 0; color: #718096; font-size: 13px;">
                            <strong>Lưu lại mã này để tra cứu đơn hàng!</strong>
                        </p>
                    </div>

                    <div class="info-box">
                        <h3 style="margin-top: 0; color: #2d3748; font-size: 18px;">
                            <span style="color: #ff6b9d;">📋</span> Thông tin đơn hàng
                        </h3>
                        <div class="info-row">
                            <span class="info-label">Ngày đặt:</span>
                            <span class="info-value">' . date('d/m/Y H:i') . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Người nhận:</span>
                            <span class="info-value">' . htmlspecialchars($orderData['customer_name']) . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Số điện thoại:</span>
                            <span class="info-value">' . htmlspecialchars($orderData['customer_phone']) . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Địa chỉ giao:</span>
                            <span class="info-value">' . htmlspecialchars($orderData['shipping_address']) . '</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Thanh toán:</span>
                            <span class="info-value">' . ($orderData['payment_method'] === 'cod' ? 'COD (Thanh toán khi nhận hàng)' : 'VNPay') . '</span>
                        </div>
                    </div>

                    <h3 style="color: #2d3748; font-size: 18px; margin-top: 30px;">
                        <span style="color: #ff6b9d;">🛍️</span> Sản phẩm đã đặt
                    </h3>
                    <table cellpadding="0" cellspacing="0" width="100%" style="border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;">
                        ' . $itemsHTML . '
                    </table>

                    <table class="summary-table">
                        <tr>
                            <td><strong>Tạm tính:</strong></td>
                            <td align="right">' . number_format($orderData['subtotal']) . 'đ</td>
                        </tr>
                        <tr>
                            <td><strong>Phí vận chuyển:</strong></td>
                            <td align="right">' . number_format($orderData['shipping_fee']) . 'đ</td>
                        </tr>
                        ' . $discountHTML . '
                        <tr class="summary-total">
                            <td><strong>Tổng cộng:</strong></td>
                            <td align="right">' . number_format($orderData['total']) . 'đ</td>
                        </tr>
                    </table>

                    <div style="text-align: center; margin: 30px 0;">
                        <a href="' . $trackingLink . '" class="btn">
                            🔍 Tra cứu đơn hàng
                        </a>
                    </div>

                    <div style="background: #fff5f7; padding: 20px; border-radius: 10px; margin-top: 30px; border-left: 4px solid #ff6b9d;">
                        <h4 style="margin-top: 0; color: #2d3748;">📞 Hỗ trợ khách hàng</h4>
                        <p style="margin: 5px 0; color: #718096; font-size: 14px;">
                            • Hotline: <strong>1900 1234</strong> (8:00 - 22:00)<br>
                            • Email: <strong>support@flowershop.vn</strong><br>
                            • Tra cứu đơn hàng: Mã đơn + Số điện thoại
                        </p>
                    </div>

                    <p style="margin-top: 30px; color: #718096; font-size: 14px;">
                        Cảm ơn bạn đã mua sắm tại <strong>Flower Shop</strong>! 🌸
                    </p>
                    <p style="color: #718096; font-size: 14px;">
                        Trân trọng,<br><strong style="color: #ff6b9d;">Đội ngũ Flower Shop</strong>
                    </p>
                </div>
                <div class="footer">
                    <p>Email này được gửi tự động, vui lòng không trả lời.</p>
                    <p>© 2025 Flower Shop. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ';

        $mail->Body = $htmlBody;
        $mail->AltBody = "Đơn hàng #" . $orderData['order_code'] . " đã được xác nhận.\n" .
                        "Tổng tiền: " . number_format($orderData['total']) . "đ\n" .
                        "Tra cứu đơn hàng tại: " . $trackingLink;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Send Order Email Error: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Gửi email thông báo admin được duyệt
 */
function sendAdminApprovalEmail($email, $fullName)
{
    $mail = getMailer();
    if (!$mail) return false;

    try {
        $mail->addAddress($email, $fullName);

        $mail->isHTML(true);
        $mail->Subject = '🎉 Tài khoản Admin đã được phê duyệt - Flower Shop';
        
        $htmlBody = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; background: #f1f5f9; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 40px rgba(15,23,42,0.1); }
                .header { background: linear-gradient(135deg, #1e293b, #334155); color: white; padding: 40px; text-align: center; }
                .body { padding: 40px 30px; }
                .btn { display: inline-block; background: linear-gradient(135deg, #1e293b, #334155); color: white; padding: 14px 30px; text-decoration: none; border-radius: 10px; margin: 20px 0; font-weight: bold; }
                .footer { background: #f1f5f9; padding: 20px; text-align: center; color: #64748b; font-size: 13px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <div style="font-size: 50px; margin-bottom: 10px;">🎉</div>
                    <h1>Chúc Mừng!</h1>
                </div>
                <div class="body">
                    <p>Xin chào <strong>' . htmlspecialchars($fullName) . '</strong>,</p>
                    <p>Tài khoản Admin của bạn đã được <strong>phê duyệt</strong> bởi SuperAdmin!</p>
                    <p>Bạn có thể đăng nhập vào hệ thống quản trị ngay bây giờ:</p>
                    
                    <div style="text-align: center;">
                        <a href="' . BASE_URL . '/admin/login" class="btn">🛡️ Đăng Nhập Admin</a>
                    </div>

                    <p>Trân trọng,<br><strong>Đội ngũ Flower Shop</strong></p>
                </div>
                <div class="footer">
                    <p>© 2025 Flower Shop. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ';

        $mail->Body = $htmlBody;
        $mail->AltBody = "Tài khoản Admin của bạn đã được phê duyệt! Đăng nhập tại: " . BASE_URL . "/admin/login";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Send Admin Approval Email Error: " . $mail->ErrorInfo);
        return false;
    }
}