<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chính sách bảo mật - Flower Shop</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/home.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/notifications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/page-privacy.css">
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>
    
    <div class="policy-container">
        <div class="policy-header">
            <h1><i class="fas fa-shield-alt"></i> Chính sách bảo mật</h1>
            <p>Bảo vệ thông tin cá nhân của bạn</p>
        </div>
        
        <div class="policy-content">
            <h2>1. Thu thập thông tin</h2>
            <p>Flower Shop thu thập các thông tin sau khi bạn đăng ký và sử dụng dịch vụ:</p>
            <ul>
                <li>Thông tin cá nhân: Họ tên, email, số điện thoại, địa chỉ</li>
                <li>Thông tin đơn hàng: Sản phẩm, số lượng, giá trị</li>
                <li>Thông tin thanh toán: Phương thức thanh toán (không lưu thông tin thẻ)</li>
                <li>Thông tin kỹ thuật: IP, trình duyệt, thiết bị truy cập</li>
            </ul>
            
            <h2>2. Mục đích sử dụng thông tin</h2>
            <ul>
                <li>Xử lý và giao hàng đơn hàng</li>
                <li>Liên hệ xác nhận, hỗ trợ khách hàng</li>
                <li>Gửi thông tin khuyến mãi, sản phẩm mới (nếu đồng ý nhận)</li>
                <li>Cải thiện chất lượng dịch vụ</li>
                <li>Phân tích hành vi người dùng để tối ưu trải nghiệm</li>
            </ul>
            
            <h2>3. Bảo mật thông tin</h2>
            <p>Chúng tôi cam kết bảo mật thông tin khách hàng bằng:</p>
            <ul>
                <li>Mã hóa SSL/TLS cho mọi giao dịch</li>
                <li>Hệ thống firewall và bảo mật đa lớp</li>
                <li>Chỉ nhân viên được ủy quyền mới truy cập dữ liệu</li>
                <li>Không chia sẻ thông tin cho bên thứ ba khi chưa có đồng ý</li>
            </ul>
            
            <div class="highlight-box">
                <strong><i class="fas fa-lock"></i> Cam kết:</strong> 
                Flower Shop cam kết KHÔNG bán, cho thuê hay trao đổi thông tin khách hàng với bất kỳ đối tác nào vì mục đích thương mại.
            </div>
            
            <h2>4. Chia sẻ thông tin</h2>
            <p>Thông tin chỉ được chia sẻ trong các trường hợp:</p>
            <ul>
                <li>Đối tác vận chuyển (để giao hàng)</li>
                <li>Cơ quan pháp luật khi có yêu cầu hợp pháp</li>
                <li>Bạn đồng ý cho phép chia sẻ</li>
            </ul>
            
            <h2>5. Quyền của khách hàng</h2>
            <p>Bạn có quyền:</p>
            <ul>
                <li>Truy cập và xem thông tin cá nhân</li>
                <li>Yêu cầu chỉnh sửa thông tin không chính xác</li>
                <li>Yêu cầu xóa tài khoản và dữ liệu</li>
                <li>Từ chối nhận email marketing</li>
            </ul>
            
            <h2>6. Cookie và công nghệ theo dõi</h2>
            <p>Website sử dụng cookie để:</p>
            <ul>
                <li>Ghi nhớ phiên đăng nhập</li>
                <li>Lưu giỏ hàng</li>
                <li>Phân tích lưu lượng truy cập</li>
            </ul>
            <p>Bạn có thể tắt cookie trong cài đặt trình duyệt nhưng một số tính năng có thể bị ảnh hưởng.</p>
            
            <h2>7. Liên hệ</h2>
            <p>Mọi thắc mắc về chính sách bảo mật, vui lòng liên hệ:</p>
            <ul>
                <li>Email: privacy@flowershop.vn</li>
                <li>Hotline: 1900 1234</li>
            </ul>
        </div>
    </div>
    
    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>
