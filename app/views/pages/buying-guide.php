<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hướng dẫn mua hàng - Flower Shop</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/home.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/notifications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/page-buying-guide.css">
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>
    
    <div class="policy-container">
        <div class="policy-header">
            <h1><i class="fas fa-shopping-cart"></i> Hướng dẫn mua hàng</h1>
            <p>Đặt hoa online dễ dàng chỉ với vài bước đơn giản</p>
        </div>
        
        <div class="policy-content">
            <h2>Quy trình đặt hàng online</h2>
            
            <div class="step-box">
                <h3><i class="fas fa-search"></i> Bước 1: Tìm kiếm và chọn sản phẩm</h3>
                <ul>
                    <li>Truy cập website flowershop.vn</li>
                    <li>Duyệt danh mục sản phẩm hoặc tìm kiếm theo tên</li>
                    <li>Xem chi tiết sản phẩm: hình ảnh, giá, mô tả</li>
                    <li>Chọn số lượng và nhấn "Thêm vào giỏ hàng"</li>
                </ul>
            </div>
            
            <div class="step-box">
                <h3><i class="fas fa-shopping-bag"></i> Bước 2: Kiểm tra giỏ hàng</h3>
                <ul>
                    <li>Nhấn vào biểu tượng giỏ hàng ở góc trên phải</li>
                    <li>Kiểm tra lại sản phẩm, số lượng, giá</li>
                    <li>Áp dụng mã giảm giá nếu có</li>
                    <li>Nhấn "Tiến hành thanh toán"</li>
                </ul>
            </div>
            
            <div class="step-box">
                <h3><i class="fas fa-user-edit"></i> Bước 3: Điền thông tin</h3>
                <ul>
                    <li>Nhập họ tên người nhận</li>
                    <li>Số điện thoại (bắt buộc)</li>
                    <li>Email để nhận xác nhận</li>
                    <li>Địa chỉ giao hàng chi tiết</li>
                    <li>Ghi chú đặc biệt (nếu có)</li>
                </ul>
            </div>
            
            <div class="step-box">
                <h3><i class="fas fa-credit-card"></i> Bước 4: Chọn phương thức thanh toán</h3>
                <ul>
                    <li><strong>COD:</strong> Thanh toán tiền mặt khi nhận hàng</li>
                    <li><strong>VNPay:</strong> Thanh toán online qua ATM, Visa, MasterCard</li>
                </ul>
            </div>
            
            <div class="step-box">
                <h3><i class="fas fa-check-circle"></i> Bước 5: Xác nhận đơn hàng</h3>
                <ul>
                    <li>Kiểm tra lại toàn bộ thông tin</li>
                    <li>Nhấn "Đặt hàng" để hoàn tất</li>
                    <li>Nhận mã đơn hàng và email xác nhận</li>
                    <li>Bạn có thể tra cứu đơn hàng bằng mã đơn + số điện thoại</li>
                </ul>
            </div>
            
            <h2>Các cách đặt hàng khác</h2>
            <ul>
                <li><strong>Hotline:</strong> Gọi 1900 1234 (24/7) để đặt hàng qua điện thoại</li>
                <li><strong>Facebook:</strong> Nhắn tin trực tiếp fanpage Flower Shop</li>
                <li><strong>Zalo:</strong> Kết bạn Zalo 0123456789 để được tư vấn</li>
            </ul>
            
            <h2>Thời gian giao hàng</h2>
            <ul>
                <li><strong>Nội thành TP.HCM:</strong> 2-4 giờ</li>
                <li><strong>Ngoại thành:</strong> 4-6 giờ</li>
                <li><strong>Tỉnh thành khác:</strong> 24-48 giờ</li>
                <li><strong>Giao hàng theo giờ:</strong> Liên hệ để đặt lịch cụ thể</li>
            </ul>
            
            <h2>Phí giao hàng</h2>
            <ul>
                <li>Nội thành: 30,000đ</li>
                <li>Ngoại thành: 50,000đ - 100,000đ tùy khoảng cách</li>
                <li>Miễn phí ship cho đơn hàng từ 500,000đ trở lên (nội thành)</li>
            </ul>
            
            <div class="tip-box">
                <strong><i class="fas fa-lightbulb"></i> Mẹo đặt hàng:</strong>
                <ul>
                    <li>Đặt hàng trước 4 giờ để đảm bảo giao đúng giờ</li>
                    <li>Lưu mã giảm giá từ email/SMS khuyến mãi</li>
                    <li>Đăng ký tài khoản để theo dõi đơn hàng dễ dàng</li>
                    <li>Liên hệ hotline nếu cần tư vấn chọn hoa</li>
                </ul>
            </div>
            
            <h2>Câu hỏi thường gặp</h2>
            <p><strong>Q: Có được đổi địa chỉ giao hàng không?</strong></p>
            <p>A: Có, liên hệ ngay hotline trước 2 giờ so với giờ giao để thay đổi.</p>
            
            <p><strong>Q: Thanh toán online có an toàn không?</strong></p>
            <p>A: Hoàn toàn an toàn qua cổng VNPay được mã hóa SSL.</p>
            
            <p><strong>Q: Làm sao biết đơn hàng đã được xác nhận?</strong></p>
            <p>A: Bạn sẽ nhận email + SMS xác nhận ngay sau khi đặt hàng thành công.</p>
            
            <h2>Hỗ trợ</h2>
            <p>Cần hỗ trợ? Liên hệ ngay:</p>
            <ul>
                <li>Hotline: 1900 1234 (24/7)</li>
                <li>Email: support@flowershop.vn</li>
                <li>Live Chat: Góc dưới phải màn hình</li>
            </ul>
        </div>
    </div>
    
    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>
