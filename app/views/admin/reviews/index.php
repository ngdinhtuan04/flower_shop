<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đánh giá - Admin</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin-reviews.css">
    <script src="<?= ASSETS_URL ?>/js/confirm-dialog.js"></script>
</head>
<body>
    <?php include APP_PATH . '/views/layouts/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <h2>Quản lý đánh giá</h2>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <strong><?= htmlspecialchars($user['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
        </div>

        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success" style="margin: 20px 0; padding: 15px; background: #d1fae5; color: #065f46; border-radius: 8px;">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger" style="margin: 20px 0; padding: 15px; background: #fee2e2; color: #991b1b; border-radius: 8px;">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['flash_error'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>


        <!-- Statistics -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon yellow">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $totalReviews ?></h3>
                    <p>Tổng đánh giá</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-info">
                    <?php 
                    $avgRating = 0;
                    if (isset($ratingStats['totals']) && isset($ratingStats['totals']['approved_avg_rating'])) {
                        $avgRating = $ratingStats['totals']['approved_avg_rating'];
                    } elseif (isset($ratingStats['average_rating'])) {
                        $avgRating = $ratingStats['average_rating'];
                    }
                    ?>
                    <h3><?= number_format($avgRating, 1) ?> ⭐</h3>
                    <p>Đánh giá trung bình (đã duyệt)</p>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <form method="GET" action="<?= BASE_URL ?>/admin/reviews" class="filter-bar" id="filterForm">
            <input type="text" 
                   name="search"
                   id="searchInput"
                   placeholder="🔍 Tìm kiếm theo tên sản phẩm, người đánh giá..." 
                   value="<?= htmlspecialchars($filters['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            
            <select name="rating" id="ratingSelect">
                <option value="">Tất cả đánh giá</option>
                <option value="5" <?= ($filters['rating'] ?? '') == '5' ? 'selected' : '' ?>>⭐⭐⭐⭐⭐ 5 sao</option>
                <option value="4" <?= ($filters['rating'] ?? '') == '4' ? 'selected' : '' ?>>⭐⭐⭐⭐ 4 sao</option>
                <option value="3" <?= ($filters['rating'] ?? '') == '3' ? 'selected' : '' ?>>⭐⭐⭐ 3 sao</option>
                <option value="2" <?= ($filters['rating'] ?? '') == '2' ? 'selected' : '' ?>>⭐⭐ 2 sao</option>
                <option value="1" <?= ($filters['rating'] ?? '') == '1' ? 'selected' : '' ?>>⭐ 1 sao</option>
            </select>
        </form>

        <!-- Reviews Table -->
        <div class="reviews-table-container" id="tableContainer">
            <?php if (empty($reviews)): ?>
                <div class="empty-state">
                    <i class="fas fa-star"></i>
                    <h3>Chưa có đánh giá nào</h3>
                    <p>Các đánh giá từ khách hàng sẽ hiển thị ở đây</p>
                </div>
            <?php else: ?>
                <table class="reviews-table">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Sản phẩm</th>
                            <th style="width: 200px;">Tên SP</th>
                            <th style="width: 100px;">Đánh giá</th>
                            <th style="width: 150px;">Người đánh giá</th>
                            <th>Nội dung</th>
                            <th style="width: 120px;">Ngày</th>
                            <th style="width: 100px;">Trạng thái</th>
                            <th style="width: 180px;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                <?php foreach ($reviews as $review): ?>
                    <?php 
                        $productImage = $review['product_image'] ?? '';
                        $imagePath = $productImage ? (BASE_URL . '/' . $productImage) : (ASSETS_URL . '/images/no-image.jpg');
                        $rating = isset($review['rating']) ? (int)$review['rating'] : 0;
                        $status = $review['status'] ?? 'pending';
                        $createdAt = $review['created_at'] ?? '';
                    ?>
                    <tr>
                        <td>
                            <img src="<?= htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8') ?>" 
                                 alt="<?= htmlspecialchars($review['product_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                 class="review-product-img"
                                 onerror="this.src='<?= ASSETS_URL ?>/images/no-image.jpg'">
                        </td>
                        <td>
                            <strong class="product-name-cell"><?= htmlspecialchars($review['product_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                        </td>
                        <td>
                            <div class="rating-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= $rating): ?>
                                        <i class="fas fa-star"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                            <span class="rating-text"><?= $rating ?>/5</span>
                        </td>
                        <td>
                            <span class="reviewer-name"><?= htmlspecialchars($review['user_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                        </td>
                        <td>
                            <div class="review-comment"><?= nl2br(htmlspecialchars(mb_substr($review['comment'] ?? '', 0, 100), ENT_QUOTES, 'UTF-8')) ?><?= mb_strlen($review['comment'] ?? '') > 100 ? '...' : '' ?></div>
                        </td>
                        <td>
                            <span class="review-date"><?= $createdAt ? date('d/m/Y', strtotime($createdAt)) : 'N/A' ?></span>
                            <br>
                            <small style="color: #94a3b8;"><?= $createdAt ? date('H:i', strtotime($createdAt)) : '' ?></small>
                        </td>
                        <td>
                            <span class="status-badge status-<?= htmlspecialchars($status) ?>">
                                <?php 
                                $statusText = [
                                    'pending' => '⏳ Chờ duyệt',
                                    'approved' => '✅ Đã duyệt',
                                    'rejected' => '🚫 Từ chối',
                                    'visible' => 'Hiển thị',
                                    'hidden' => 'Đã ẩn'
                                ];
                                echo $statusText[$status] ?? ucfirst($status);
                                ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <?php if (in_array($status, ['pending', 'rejected'])): ?>
                                    <form method="POST" action="<?= BASE_URL ?>/admin/reviews/approve" style="display: inline;">
                                        <input type="hidden" name="review_id" value="<?= htmlspecialchars($review['id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                        <button type="submit" class="btn-approve" title="Duyệt đánh giá">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if (in_array($status, ['pending', 'approved'])): ?>
                                    <button type="button" 
                                            class="btn-reject" 
                                            title="Từ chối đánh giá"
                                            onclick="showRejectModal(<?= $review['id'] ?>, '<?= htmlspecialchars($review['product_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                <?php endif; ?>
                                
                                <form method="POST" action="<?= BASE_URL ?>/admin/reviews/delete" style="display: inline;" id="deleteReviewForm<?= $review['id'] ?>">
                                    <input type="hidden" name="review_id" value="<?= htmlspecialchars($review['id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="button" 
                                            class="btn-delete"
                                            title="Xóa đánh giá"
                                            onclick="confirmDeleteReview(<?= $review['id'] ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($currentPage > 1): ?>
                            <a href="?page=<?= $currentPage - 1 ?><?= !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : '' ?><?= !empty($filters['rating']) ? '&rating=' . htmlspecialchars($filters['rating'], ENT_QUOTES, 'UTF-8') : '' ?>">
                                <i class="fas fa-chevron-left"></i> Trước
                            </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i == $currentPage): ?>
                                <span class="active"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?page=<?= $i ?><?= !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : '' ?><?= !empty($filters['rating']) ? '&rating=' . htmlspecialchars($filters['rating'], ENT_QUOTES, 'UTF-8') : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($currentPage < $totalPages): ?>
                            <a href="?page=<?= $currentPage + 1 ?><?= !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : '' ?><?= !empty($filters['rating']) ? '&rating=' . htmlspecialchars($filters['rating'], ENT_QUOTES, 'UTF-8') : '' ?>">
                                Sau <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal Từ chối đánh giá -->
    <div id="rejectModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: white; border-radius: 12px; padding: 30px; max-width: 500px; width: 90%; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
            <h3 style="margin: 0 0 20px; color: #2d3748; font-size: 20px;">
                <i class="fas fa-times-circle" style="color: #ef4444;"></i> Từ chối đánh giá
            </h3>
            <p style="color: #666; margin-bottom: 15px;">
                Sản phẩm: <strong id="rejectProductName"></strong>
            </p>
            <form method="POST" action="<?= BASE_URL ?>/admin/reviews/reject" id="rejectForm">
                <input type="hidden" name="review_id" id="rejectReviewId">
                <label style="display: block; margin-bottom: 8px; color: #4a5568; font-weight: 600;">
                    Lý do từ chối: <span style="color: red;">*</span>
                </label>
                <textarea name="admin_note" 
                          id="rejectReason"
                          rows="4" 
                          style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; resize: vertical;"
                          placeholder="Ví dụ: Ngôn từ không phù hợp, spam, vi phạm tiêu chuẩn cộng đồng..."
                          required></textarea>
                <div style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" 
                            onclick="closeRejectModal()"
                            style="padding: 10px 20px; border: 1px solid #ddd; background: white; border-radius: 6px; cursor: pointer;">
                        Hủy
                    </button>
                    <button type="submit" 
                            style="padding: 10px 20px; background: #ef4444; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                        <i class="fas fa-times-circle"></i> Xác nhận từ chối
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let searchTimeout;
        const searchInput = document.getElementById('searchInput');
        const ratingSelect = document.getElementById('ratingSelect');
        
        function showRejectModal(reviewId, productName) {
            document.getElementById('rejectReviewId').value = reviewId;
            document.getElementById('rejectProductName').textContent = productName;
            document.getElementById('rejectReason').value = '';
            document.getElementById('rejectModal').style.display = 'flex';
        }
        
        function closeRejectModal() {
            document.getElementById('rejectModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        document.getElementById('rejectModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRejectModal();
            }
        });
        
        function performSearch() {
            const search = searchInput ? searchInput.value : '';
            const rating = ratingSelect ? ratingSelect.value : '';
            
            const params = new URLSearchParams();
            if (search) params.append('search', search);
            if (rating) params.append('rating', rating);
            
            const url = '<?= BASE_URL ?>/admin/reviews?' + params.toString();
            
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    
                    const newTable = doc.getElementById('tableContainer');
                    const currentTable = document.getElementById('tableContainer');
                    if (newTable && currentTable) {
                        currentTable.innerHTML = newTable.innerHTML;
                    }
                })
                .catch(error => console.error('Error:', error));
        }
        
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(performSearch, 300);
            });
        }
        
        if (ratingSelect) {
            ratingSelect.addEventListener('change', performSearch);
        }
        
        // Confirm delete review
        function confirmDeleteReview(reviewId) {
            confirmDelete({
                title: 'Xóa đánh giá',
                message: '⚠️ Bạn có chắc muốn <strong>XÓA VĨNH VIỄN</strong> đánh giá này?<br><br>Hành động này <strong>KHÔNG THỂ HOÀN TÁC!</strong>',
                confirmText: 'Xóa đánh giá',
                theme: 'admin'
            }).then(confirmed => {
                if (confirmed) {
                    document.getElementById('deleteReviewForm' + reviewId).submit();
                }
            });
        }
    </script>
</body>
</html>
