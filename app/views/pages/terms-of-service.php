<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Điều khoản dịch vụ - Flower Shop</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/home.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/notifications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/page-terms.css">
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>
    
    <div class="policy-container">
        <div class="policy-header">
            <h1><i class="fas fa-file-contract"></i> Điều khoản dịch vụ</h1>
            <p>Quy định sử dụng dịch vụ Flower Shop</p>
        </div>
        
        <div class="policy-content">
            <h2>1. Chấp nhận điều khoản</h2>
            <p>Khi truy cập và sử dụng website flowershop.vn, bạn đồng ý tuân thủ các điều khoản và điều kiện sau đây. 
            Nếu không đồng ý, vui lòng không sử dụng dịch vụ của chúng tôi.</p>
            
            <h2>2. Tài khoản người dùng</h2>
            <ul>
                <li>Bạn phải cung cấp thông tin chính xác khi đăng ký</li>
                <li>Bạn chịu trách nhiệm bảo mật thông tin đăng nhập</li>
                <li>Không chia sẻ tài khoản cho người khác</li>
                <li>Thông báo ngay nếu phát hiện tài khoản bị xâm nhập</li>
                <li>Chúng tôi có quyền khóa tài khoản vi phạm điều khoản</li>
            </ul>
            
            <h2>3. Đặt hàng và thanh toán</h2>
            <ul>
                <li>Đơn hàng chỉ được xác nhận sau khi thanh toán thành công</li>
                <li>Giá sản phẩm có thể thay đổi mà không cần báo trước</li>
                <li>Chúng tôi có quyền từ chối đơn hàng bất thường</li>
                <li>Khách hàng chịu trách nhiệm kiểm tra thông tin đơn hàng trước khi thanh toán</li>
            </ul>
            
            <div class="highlight-box">
                <strong><i class="fas fa-info-circle"></i> Lưu ý:</strong> 
                Hình ảnh sản phẩm chỉ mang tính chất minh họa. Sản phẩm thực tế có thể khác 5-10% do tính chất tự nhiên của hoa tươi.
            </div>
            
            <h2>4. Giao hàng</h2>
            <ul>
                <li>Thời gian giao hàng: 2-4h nội thành, 24-48h ngoại thành</li>
                <li>Chúng tôi không chịu trách nhiệm về sự chậm trễ do nguyên nhân khách quan</li>
                <li>Khách hàng cần cung cấp địa chỉ chính xác</li>
                <li>Phí giao hàng tính theo khu vực</li>
            </ul>
            
            <h2>5. Hủy đơn hàng</h2>
            <ul>
                <li>Cho phép hủy miễn phí trước 4h so với giờ giao hàng</li>
                <li>Hủy trong vòng 2-4h: phí 30% giá trị đơn</li>
                <li>Hủy dưới 2h: phí 50% giá trị đơn</li>
                <li>Đơn đang giao không được hủy</li>
            </ul>
            
            <h2>6. Quyền sở hữu trí tuệ</h2>
            <ul>
                <li>Mọi nội dung trên website thuộc bản quyền của Flower Shop</li>
                <li>Không sao chép, phân phối nội dung khi chưa có sự cho phép</li>
                <li>Thiết kế hoa là sản phẩm sáng tạo độc quyền</li>
            </ul>
            
            <h2>7. Trách nhiệm của khách hàng</h2>
            <ul>
                <li>Không sử dụng dịch vụ cho mục đích bất hợp pháp</li>
                <li>Không spam, gửi nội dung xấu</li>
                <li>Tôn trọng nhân viên và các khách hàng khác</li>
                <li>Bảo quản hoa đúng cách sau khi nhận</li>
            </ul>
            
            <h2>8. Giới hạn trách nhiệm</h2>
            <p>Flower Shop không chịu trách nhiệm về:</p>
            <ul>
                <li>Thiệt hại do hoa không được bảo quản đúng cách</li>
                <li>Sự cố ngoài tầm kiểm soát (thiên tai, dịch bệnh, ...)</li>
                <li>Lỗi do thông tin khách hàng cung cấp không chính xác</li>
            </ul>
            
            <h2>9. Thay đổi điều khoản</h2>
            <p>Chúng tôi có quyền cập nhật điều khoản bất kỳ lúc nào. Việc bạn tiếp tục sử dụng dịch vụ sau khi có thay đổi 
            đồng nghĩa với việc chấp nhận điều khoản mới.</p>
            
            <h2>10. Liên hệ và khiếu nại</h2>
            <p>Mọi thắc mắc về điều khoản dịch vụ, vui lòng liên hệ:</p>
            <ul>
                <li>Email: legal@flowershop.vn</li>
                <li>Hotline: 1900 1234</li>
                <li>Địa chỉ: 123 Đường ABC, Quận 1, TP.HCM</li>
            </ul>
        </div>
    </div>
    
    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>
