<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tin tức - Flower Shop</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/home.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/notifications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/news-index.css">
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="<?= BASE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a>
        <span class="separator">/</span>
        <span class="current">Tin tức</span>
    </div>

    <div class="news-container">
        <!-- Filter Sidebar -->
        <aside class="filter-sidebar">
            <h3><i class="fas fa-filter"></i> Bộ lọc</h3>
            
            <form method="GET" action="<?= BASE_URL ?>/news">
                <!-- Search Box -->
                <div class="filter-section">
                    <h4>Tìm kiếm</h4>
                    <div class="search-box">
                        <input type="text" name="search" placeholder="Tìm kiếm tin tức..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </div>
                </div>

                <!-- Category Filters -->
                <div class="filter-section">
                    <h4>Danh mục</h4>
                    <button type="submit" name="category" value="" class="filter-btn <?= empty($filters['category']) ? 'active' : '' ?>">
                        <i class="fas fa-th"></i> Tất cả
                    </button>
                    <button type="submit" name="category" value="tips" class="filter-btn <?= ($filters['category'] ?? '') === 'tips' ? 'active' : '' ?>">
                        <i class="fas fa-lightbulb"></i> Mẹo hay
                    </button>
                    <button type="submit" name="category" value="events" class="filter-btn <?= ($filters['category'] ?? '') === 'events' ? 'active' : '' ?>">
                        <i class="fas fa-calendar-alt"></i> Sự kiện
                    </button>
                    <button type="submit" name="category" value="promotion" class="filter-btn <?= ($filters['category'] ?? '') === 'promotion' ? 'active' : '' ?>">
                        <i class="fas fa-gift"></i> Khuyến mãi
                    </button>
                </div>

                <!-- Sort Options -->
                <div class="filter-section">
                    <h4>Sắp xếp</h4>
                    <button type="submit" name="sort" value="latest" class="filter-btn <?= ($filters['sort'] ?? 'latest') === 'latest' ? 'active' : '' ?>">
                        <i class="fas fa-clock"></i> Mới nhất
                    </button>
                    <button type="submit" name="sort" value="views" class="filter-btn <?= ($filters['sort'] ?? '') === 'views' ? 'active' : '' ?>">
                        <i class="fas fa-eye"></i> Xem nhiều nhất
                    </button>
                    <button type="submit" name="sort" value="comments" class="filter-btn <?= ($filters['sort'] ?? '') === 'comments' ? 'active' : '' ?>">
                        <i class="fas fa-comments"></i> Nhiều bình luận
                    </button>
                    <button type="submit" name="sort" value="likes" class="filter-btn <?= ($filters['sort'] ?? '') === 'likes' ? 'active' : '' ?>">
                        <i class="fas fa-heart"></i> Yêu thích nhất
                    </button>
                </div>
            </form>
        </aside>

        <!-- News Content Area -->
        <div class="news-content-area">
            <?php if (empty($news)): ?>
                <div style="text-align: center; padding: 60px 20px;">
                    <i class="fas fa-inbox" style="font-size: 72px; color: #cbd5e0; margin-bottom: 20px;"></i>
                    <h3 style="color: #718096; font-size: 24px;">Chưa có tin tức nào</h3>
                    <p style="color: #a0aec0;">Vui lòng quay lại sau!</p>
                </div>
            <?php else: ?>
                <?php foreach ($news as $item): ?>
                    <a href="<?= BASE_URL ?>/news/detail/<?= htmlspecialchars($item['slug']) ?>" class="news-item">
                        <!-- Action Buttons -->
                        <div class="news-actions" onclick="event.preventDefault(); event.stopPropagation();">
                            <button class="news-action-btn <?= isset($item['is_liked']) && $item['is_liked'] ? 'liked' : '' ?>" 
                                    onclick="toggleLike(<?= $item['id'] ?>, this)" 
                                    title="Yêu thích">
                                <i class="fas fa-heart"></i>
                            </button>
                        </div>

                        <?php if ($item['image']): ?>
                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="news-image">
                        <?php else: ?>
                            <div class="news-image" style="background: linear-gradient(135deg, #ff6b9d 0%, #c44569 100%); display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-newspaper" style="font-size: 72px; color: rgba(255,255,255,0.3);"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="news-content">
                            <div>
                                <?php if ($item['category']): ?>
                                    <span class="news-category">
                                        <?php 
                                        $categories = ['tips' => '💡 Mẹo hay', 'events' => '📅 Sự kiện', 'promotion' => '🎁 Khuyến mãi'];
                                        echo $categories[$item['category']] ?? $item['category'];
                                        ?>
                                    </span>
                                <?php endif; ?>
                                
                                <h3 class="news-title"><?= htmlspecialchars($item['title']) ?></h3>
                                
                                <?php if ($item['excerpt']): ?>
                                    <p class="news-excerpt"><?= htmlspecialchars($item['excerpt']) ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="news-meta">
                                <span><i class="fas fa-user"></i> <?= htmlspecialchars($item['author_name'] ?? 'Admin') ?></span>
                                <span><i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($item['created_at'])) ?></span>
                                <span><i class="fas fa-eye"></i> <?= number_format($item['views']) ?></span>
                                <span><i class="fas fa-comments"></i> <?= number_format($item['comments_count'] ?? 0) ?></span>
                                <span><i class="fas fa-heart"></i> <?= number_format($item['likes_count'] ?? 0) ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($pagination['total_pages'] > 1): ?>
            <div class="pagination">
                <?php if ($pagination['current_page'] > 1): ?>
                    <a href="?page=<?= $pagination['current_page'] - 1 ?>&category=<?= $filters['category'] ?? '' ?>&search=<?= $filters['search'] ?? '' ?>&sort=<?= $filters['sort'] ?? '' ?>">
                        <i class="fas fa-chevron-left"></i> Trước
                    </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                    <?php if ($i == $pagination['current_page']): ?>
                        <span class="active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?page=<?= $i ?>&category=<?= $filters['category'] ?? '' ?>&search=<?= $filters['search'] ?? '' ?>&sort=<?= $filters['sort'] ?? '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                    <a href="?page=<?= $pagination['current_page'] + 1 ?>&category=<?= $filters['category'] ?? '' ?>&search=<?= $filters['search'] ?? '' ?>&sort=<?= $filters['sort'] ?? '' ?>">
                        Sau <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        </div>
    </div>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

    <script>
        // Toggle like for news
        function toggleLike(newsId, button) {
            <?php if (!isset($_SESSION['user_id'])): ?>
                alert('Vui lòng đăng nhập để yêu thích tin tức!');
                window.location.href = '<?= BASE_URL ?>/user/login';
                return;
            <?php endif; ?>

            fetch('<?= BASE_URL ?>/news/toggle-like/' + newsId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    button.classList.toggle('liked');
                    // Cập nhật số lượng likes
                    const newsItem = button.closest('.news-item');
                    const likesSpan = newsItem.querySelector('.news-meta span:last-child');
                    if (likesSpan) {
                        const icon = likesSpan.querySelector('i');
                        likesSpan.innerHTML = icon.outerHTML + ' ' + data.likes_count.toLocaleString();
                    }
                } else {
                    alert(data.message || 'Có lỗi xảy ra!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra!');
            });
        }
    </script>
</body>
</html>
