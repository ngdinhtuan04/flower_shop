<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Về chúng tôi - Flower Shop</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/home.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/notifications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/page-about.css">
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>
    
    <div class="about-container">
        <div class="about-header">
            <h1><i class="fas fa-store"></i> Về Flower Shop</h1>
            <p>Mang vẻ đẹp thiên nhiên đến mọi không gian sống</p>
        </div>
        
        <div class="about-section">
            <h2><i class="fas fa-info-circle"></i> Giới thiệu về chúng tôi</h2>
            <p>
                <strong>Flower Shop</strong> là cửa hàng hoa tươi uy tín hàng đầu tại Việt Nam, được thành lập với sứ mệnh mang đến những bó hoa tươi đẹp nhất, 
                chất lượng cao với giá cả hợp lý. Chúng tôi tự hào là địa chỉ tin cậy của hàng ngàn khách hàng trong suốt nhiều năm qua.
            </p>
            <p>
                Với đội ngũ florist chuyên nghiệp, giàu kinh nghiệm và đầy đam mê, chúng tôi luôn sáng tạo những thiết kế hoa độc đáo, 
                phù hợp với từng dịp lễ, sự kiện và cảm xúc mà bạn muốn gửi gắm. Mỗi bó hoa không chỉ là sản phẩm, 
                mà còn là thông điệp yêu thương, sự quan tâm và tình cảm chân thành.
            </p>
        </div>
        
        <div class="about-section">
            <h2><i class="fas fa-bullseye"></i> Sứ mệnh & Tầm nhìn</h2>
            <p>
                <strong>Sứ mệnh:</strong> Mang vẻ đẹp của thiên nhiên đến gần hơn với cuộc sống hàng ngày, 
                giúp mọi người thể hiện tình cảm và làm đẹp không gian sống một cách tinh tế và ý nghĩa nhất.
            </p>
            <p>
                <strong>Tầm nhìn:</strong> Trở thành thương hiệu hoa tươi hàng đầu Việt Nam, 
                được yêu thích và tin tưởng nhất bởi chất lượng sản phẩm vượt trội, dịch vụ chu đáo và sự sáng tạo không ngừng.
            </p>
        </div>
        
        <div class="about-section">
            <h2><i class="fas fa-gem"></i> Giá trị cốt lõi</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-certificate"></i>
                    </div>
                    <h3>Chất lượng</h3>
                    <p>Hoa tươi 100%, nhập khẩu từ Đà Lạt và các vùng trồng hoa uy tín</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-palette"></i>
                    </div>
                    <h3>Sáng tạo</h3>
                    <p>Thiết kế độc đáo, tinh tế, luôn cập nhật xu hướng mới nhất</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h3>Giao hàng nhanh</h3>
                    <p>Giao hàng trong 2-4h tại nội thành, toàn quốc trong 24h</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>Hỗ trợ 24/7</h3>
                    <p>Đội ngũ tư vấn nhiệt tình, sẵn sàng hỗ trợ mọi lúc mọi nơi</p>
                </div>
            </div>
        </div>
        
        <div class="about-section" style="background: linear-gradient(135deg, #ff6b9d 0%, #c44569 100%); color: white;">
            <h2 style="color: white; text-align: center;"><i class="fas fa-chart-line"></i> Thành tựu của chúng tôi</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">10,000+</div>
                    <div class="stat-label">Khách hàng hài lòng</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">500+</div>
                    <div class="stat-label">Thiết kế hoa độc đáo</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">50+</div>
                    <div class="stat-label">Đối tác tin cậy</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">5 năm</div>
                    <div class="stat-label">Kinh nghiệm</div>
                </div>
            </div>
        </div>
        
        <div class="about-section">
            <h2><i class="fas fa-phone-alt"></i> Liên hệ với chúng tôi</h2>
            <p><strong><i class="fas fa-map-marker-alt"></i> Địa chỉ:</strong> 123 Đường ABC, Quận 1, TP. Hồ Chí Minh</p>
            <p><strong><i class="fas fa-phone"></i> Hotline:</strong> 1900 1234 (Hỗ trợ 24/7)</p>
            <p><strong><i class="fas fa-envelope"></i> Email:</strong> contact@flowershop.vn</p>
            <p><strong><i class="fas fa-clock"></i> Giờ làm việc:</strong> 8:00 - 22:00 (Tất cả các ngày trong tuần)</p>
        </div>
    </div>
    
    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>
