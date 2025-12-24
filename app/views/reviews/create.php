<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đánh giá sản phẩm - Flower Shop</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/home.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/notifications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/review-create.css">
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="<?= BASE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a>
        <span class="separator">/</span>
        <a href="<?= BASE_URL ?>/orders/history">Đơn hàng</a>
        <span class="separator">/</span>
        <span class="current">Đánh giá</span>
    </div>

    <div class="review-container">
        <div class="review-card">
            <!-- Thông tin sản phẩm -->
            <div class="product-info">
                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($product['image']) ?>" 
                     alt="<?= htmlspecialchars($product['name']) ?>">
                <div class="product-details">
                    <h3><?= htmlspecialchars($product['name']) ?></h3>
                    <div class="price"><?= number_format($product['price'], 0, ',', '.') ?>đ</div>
                </div>
            </div>

            <!-- Form đánh giá -->
            <form method="POST" action="<?= BASE_URL ?>/review/store" id="reviewForm">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                
                <!-- Rating -->
                <div class="form-group">
                    <label>Đánh giá của bạn <span style="color: #e53e3e;">*</span></label>
                    <div class="rating-input" id="ratingInput">
                        <input type="radio" name="rating" value="5" id="star5" required>
                        <label for="star5"><i class="fas fa-star"></i></label>
                        
                        <input type="radio" name="rating" value="4" id="star4">
                        <label for="star4"><i class="fas fa-star"></i></label>
                        
                        <input type="radio" name="rating" value="3" id="star3">
                        <label for="star3"><i class="fas fa-star"></i></label>
                        
                        <input type="radio" name="rating" value="2" id="star2">
                        <label for="star2"><i class="fas fa-star"></i></label>
                        
                        <input type="radio" name="rating" value="1" id="star1">
                        <label for="star1"><i class="fas fa-star"></i></label>
                    </div>
                    <div class="rating-desc" id="ratingDesc">Chọn số sao để đánh giá</div>
                </div>

                <!-- Comment -->
                <div class="form-group">
                    <label for="comment">Nhận xét của bạn <span style="color: #e53e3e;">*</span></label>
                    <textarea name="comment" 
                              id="comment" 
                              placeholder="Hãy chia sẻ cảm nhận của bạn về sản phẩm này..." 
                              required
                              maxlength="1000"></textarea>
                    <div class="char-count">
                        <span id="charCount">0</span>/1000 ký tự
                    </div>
                </div>

                <!-- Buttons -->
                <div class="btn-group">
                    <a href="<?= BASE_URL ?>/orders/detail/<?= $order['id'] ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Hủy
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Gửi đánh giá
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

    <script>
        // Rating hover effect
        const ratingDesc = {
            1: '⭐ Rất tệ',
            2: '⭐⭐ Tệ',
            3: '⭐⭐⭐ Bình thường',
            4: '⭐⭐⭐⭐ Tốt',
            5: '⭐⭐⭐⭐⭐ Tuyệt vời'
        };

        document.querySelectorAll('.rating-input label').forEach(label => {
            label.addEventListener('mouseenter', function() {
                const rating = this.getAttribute('for').replace('star', '');
                document.getElementById('ratingDesc').textContent = ratingDesc[rating];
            });
        });

        document.getElementById('ratingInput').addEventListener('mouseleave', function() {
            const checked = document.querySelector('.rating-input input:checked');
            if (checked) {
                const rating = checked.value;
                document.getElementById('ratingDesc').textContent = ratingDesc[rating];
            } else {
                document.getElementById('ratingDesc').textContent = 'Chọn số sao để đánh giá';
            }
        });

        document.querySelectorAll('.rating-input input').forEach(input => {
            input.addEventListener('change', function() {
                document.getElementById('ratingDesc').textContent = ratingDesc[this.value];
            });
        });

        // Character counter
        const textarea = document.getElementById('comment');
        const charCount = document.getElementById('charCount');

        textarea.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });

        // Form validation
        document.getElementById('reviewForm').addEventListener('submit', function(e) {
            const rating = document.querySelector('.rating-input input:checked');
            const comment = document.getElementById('comment').value.trim();

            if (!rating) {
                e.preventDefault();
                alert('Vui lòng chọn số sao đánh giá');
                return;
            }

            if (!comment) {
                e.preventDefault();
                alert('Vui lòng nhập nội dung đánh giá');
                return;
            }
        });
    </script>
</body>
</html>
