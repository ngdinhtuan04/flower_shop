<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($news['title']) ?> - Flower Shop</title>
    <meta name="description" content="<?= htmlspecialchars($news['meta_description'] ?? $news['excerpt'] ?? '') ?>">
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/home.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/notifications.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/news-detail.css">
    <script src="<?= ASSETS_URL ?>/js/confirm-dialog.js"></script>
</head>
<body>
    <?php require_once APP_PATH . '/views/layouts/header.php'; ?>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="<?= BASE_URL ?>"><i class="fas fa-home"></i> Trang chủ</a>
        <span class="separator">/</span>
        <a href="<?= BASE_URL ?>/news">Tin tức</a>
        <span class="separator">/</span>
        <span class="current"><?= htmlspecialchars($news['title']) ?></span>
    </div>

    <!-- News Detail Content -->
    <div class="news-detail-container">
        <!-- Main Content -->
        <div class="news-main">

            <div class="news-header">
                <?php if ($news['category']): ?>
                    <span class="news-category-badge">
                        <?php 
                        $categories = ['tips' => '💡 Mẹo hay', 'events' => '📅 Sự kiện', 'promotion' => '🎁 Khuyến mãi'];
                        echo $categories[$news['category']] ?? $news['category'];
                        ?>
                    </span>
                <?php endif; ?>

                <h1 class="news-detail-title"><?= htmlspecialchars($news['title']) ?></h1>

                <div class="news-meta-info">
                    <span><i class="fas fa-user"></i> <?= htmlspecialchars($news['author_name'] ?? 'Admin') ?></span>
                    <span><i class="fas fa-calendar"></i> <?= date('d/m/Y H:i', strtotime($news['created_at'])) ?></span>
                    <span><i class="fas fa-eye"></i> <?= number_format($news['views']) ?> lượt xem</span>
                </div>
            </div>

            <?php if ($news['image']): ?>
                <img src="<?= BASE_URL ?>/<?= htmlspecialchars($news['image']) ?>" alt="<?= htmlspecialchars($news['title']) ?>" class="news-featured-image">
            <?php endif; ?>

            <?php if ($news['excerpt']): ?>
                <div style="background: #f7fafc; padding: 20px; border-left: 4px solid #ff6b9d; border-radius: 8px; margin: 30px 0;">
                    <p style="font-size: 18px; font-weight: 600; color: #4a5568; margin: 0;">
                        <?= htmlspecialchars($news['excerpt']) ?>
                    </p>
                </div>
            <?php endif; ?>

            <div class="news-content">
                <?= $news['content'] ?>
            </div>
        </div>

        <!-- Sidebar -->
        <aside class="news-sidebar">
            <!-- Share Widget -->
            <div class="sidebar-widget">
                <h3><i class="fas fa-share-alt"></i> Chia sẻ</h3>
                <div class="share-buttons">
                    <button class="share-btn facebook" onclick="shareFacebook()">
                        <i class="fab fa-facebook-f"></i>
                    </button>
                    <button class="share-btn twitter" onclick="shareTwitter()">
                        <i class="fab fa-twitter"></i>
                    </button>
                    <button class="share-btn copy" onclick="copyLink()">
                        <i class="fas fa-link"></i>
                    </button>
                </div>
            </div>

            <!-- Related News Widget -->
            <?php if (!empty($related_news)): ?>
            <div class="sidebar-widget">
                <h3><i class="fas fa-newspaper"></i> Tin liên quan</h3>
                <?php foreach ($related_news as $related): ?>
                    <a href="<?= BASE_URL ?>/news/detail/<?= htmlspecialchars($related['slug']) ?>" class="related-news-item" style="text-decoration: none; color: inherit;">
                        <?php if ($related['image']): ?>
                            <img src="<?= BASE_URL ?>/<?= htmlspecialchars($related['image']) ?>" alt="<?= htmlspecialchars($related['title']) ?>" class="related-news-thumb">
                        <?php else: ?>
                            <div class="related-news-thumb" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-newspaper" style="color: rgba(255,255,255,0.6); font-size: 24px;"></i>
                            </div>
                        <?php endif; ?>
                        <div class="related-news-info">
                            <h4><?= htmlspecialchars($related['title']) ?></h4>
                            <div class="related-news-date">
                                <i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($related['created_at'])) ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Comments Widget -->
            <div class="sidebar-widget">
                <h3><i class="fas fa-comments"></i> Bình luận (<?= count($comments ?? []) ?>)</h3>
                
                <div class="comments-section">
                    <?php if (!empty($comments)): ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment-item" data-comment-id="<?= $comment['id'] ?>">
                                <div class="comment-header">
                                    <div class="comment-avatar">
                                        <?= strtoupper(substr($comment['username'] ?? 'U', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="comment-author"><?= htmlspecialchars($comment['username'] ?? 'User') ?></div>
                                        <div class="comment-date">
                                            <i class="fas fa-clock"></i> <?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="comment-content">
                                    <?= nl2br(htmlspecialchars($comment['content'])) ?>
                                </div>
                                
                                <!-- Comment Actions -->
                                <div class="comment-actions">
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <button class="comment-action-btn reply-btn" onclick="toggleReplyForm(<?= $comment['id'] ?>)">
                                            <i class="fas fa-reply"></i> Trả lời
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $comment['user_id']): ?>
                                        <button class="comment-action-btn delete" onclick="deleteComment(<?= $comment['id'] ?>)">
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Reply Form -->
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <div class="reply-form" id="reply-form-<?= $comment['id'] ?>">
                                        <form method="POST" action="<?= BASE_URL ?>/news/comment/<?= $news['id'] ?>">
                                            <input type="hidden" name="parent_id" value="<?= $comment['id'] ?>">
                                            <textarea class="reply-textarea" name="content" placeholder="Viết trả lời của bạn..." required></textarea>
                                            <div class="reply-actions">
                                                <button type="submit" class="reply-submit">
                                                    <i class="fas fa-paper-plane"></i> Gửi
                                                </button>
                                                <button type="button" class="reply-cancel" onclick="toggleReplyForm(<?= $comment['id'] ?>)">
                                                    Hủy
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($comment['replies'])): ?>
                                    <?php foreach ($comment['replies'] as $reply): ?>
                                        <div class="comment-reply">
                                            <div class="comment-item" data-comment-id="<?= $reply['id'] ?>">
                                                <div class="comment-header">
                                                    <div class="comment-avatar">
                                        <?= strtoupper(substr($reply['username'] ?? 'U', 0, 1)) ?>
                                                    </div>
                                                    <div>
                                                        <div class="comment-author"><?= htmlspecialchars($reply['username'] ?? 'User') ?></div>
                                                        <div class="comment-date">
                                                            <i class="fas fa-clock"></i> <?= date('d/m/Y H:i', strtotime($reply['created_at'])) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="comment-content">
                                                    <?= nl2br(htmlspecialchars($reply['content'])) ?>
                                                </div>
                                                
                                                <!-- Reply Actions -->
                                                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $reply['user_id']): ?>
                                                    <div class="comment-actions">
                                                        <button class="comment-action-btn delete" onclick="deleteComment(<?= $reply['id'] ?>)">
                                                            <i class="fas fa-trash"></i> Xóa
                                                        </button>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #a0aec0; padding: 20px 0;">
                            <i class="fas fa-comment-slash"></i><br>
                            Chưa có bình luận nào
                        </p>
                    <?php endif; ?>
                </div>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <form class="comment-form" method="POST" action="<?= BASE_URL ?>/news/comment/<?= $news['id'] ?>">
                        <textarea class="comment-textarea" name="content" placeholder="Viết bình luận của bạn..." required></textarea>
                        <button type="submit" class="comment-submit">
                            <i class="fas fa-paper-plane"></i> Gửi bình luận
                        </button>
                    </form>
                <?php else: ?>
                    <div class="login-required">
                        <i class="fas fa-lock"></i><br>
                        <a href="<?= BASE_URL ?>/user/login">Đăng nhập</a> để bình luận
                    </div>
                <?php endif; ?>
            </div>
        </aside>
    </div>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-grid">
                <div class="footer-col">
                    <div class="footer-logo">
                        <i class="fas fa-flower-tulip"></i>
                        <h3>Flower Shop</h3>
                    </div>
                    <p>Cửa hàng hoa tươi uy tín, chất lượng cao tại Việt Nam. Chúng tôi cam kết mang đến những bó hoa đẹp nhất cho mọi dịp đặc biệt.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                        <a href="#"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
                
                <div class="footer-col">
                    <h4>Liên Kết</h4>
                    <ul>
                        <li><a href="<?= BASE_URL ?>"><i class="fas fa-chevron-right"></i> Trang chủ</a></li>
                        <li><a href="<?= BASE_URL ?>/products"><i class="fas fa-chevron-right"></i> Sản phẩm</a></li>
                        <li><a href="<?= BASE_URL ?>/news"><i class="fas fa-chevron-right"></i> Tin tức</a></li>
                        <li><a href="<?= BASE_URL ?>/about"><i class="fas fa-chevron-right"></i> Về chúng tôi</a></li>
                        <li><a href="<?= BASE_URL ?>/contact"><i class="fas fa-chevron-right"></i> Liên hệ</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4>Chính Sách</h4>
                    <ul>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Chính sách đổi trả</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Chính sách bảo mật</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Điều khoản dịch vụ</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Hướng dẫn mua hàng</a></li>
                    </ul>
                </div>
                
                <div class="footer-col">
                    <h4>Liên Hệ</h4>
                    <ul class="contact-info">
                        <li><i class="fas fa-map-marker-alt"></i> 123 Đường ABC, Quận 1, TP.HCM</li>
                        <li><i class="fas fa-phone"></i> 1900 1234</li>
                        <li><i class="fas fa-envelope"></i> contact@flowershop.vn</li>
                        <li><i class="fas fa-clock"></i> 8:00 - 22:00 (Hàng ngày)</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 Flower Shop. All rights reserved.</p>
                <p>Made with <i class="fas fa-heart"></i> by Flower Shop Team</p>
            </div>
        </div>
    </footer>

    <script>
        // Toast Notification System
        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            
            toast.innerHTML = `
                <i class="fas ${icon} toast-icon"></i>
                <div class="toast-content">
                    <p class="toast-message">${message}</p>
                </div>
                <button class="toast-close" onclick="closeToast(this)">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            container.appendChild(toast);
            
            // Cập nhật badge giỏ hàng
            updateCartBadge();
            
            // Tự động ẩn sau 3 giây
            setTimeout(() => {
                closeToast(toast.querySelector('.toast-close'));
            }, 3000);
        }
        
        function closeToast(button) {
            const toast = button.closest('.toast');
            toast.classList.add('hiding');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }
        
        function updateCartBadge() {
            fetch('<?= BASE_URL ?>/cart/count')
                .then(response => response.json())
                .then(data => {
                    const badge = document.querySelector('.badge');
                    if (badge) {
                        badge.textContent = data.count || 0;
                    }
                })
                .catch(error => console.error('Error updating cart:', error));
        }
        
        // Kiểm tra flash message từ PHP
        <?php if (Session::hasFlash('success')): ?>
            showToast('<?= addslashes(Session::getFlash('success')) ?>', 'success');
        <?php endif; ?>
        
        <?php if (Session::hasFlash('error')): ?>
            showToast('<?= addslashes(Session::getFlash('error')) ?>', 'error');
        <?php endif; ?>

        // Share functions
        function shareFacebook() {
            window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(window.location.href), '_blank');
        }

        function shareTwitter() {
            window.open('https://twitter.com/intent/tweet?url=' + encodeURIComponent(window.location.href) + '&text=<?= urlencode($news['title']) ?>', '_blank');
        }

        function copyLink() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                alert('Đã sao chép link!');
            });
        }
        
        // Toggle Reply Form
        function toggleReplyForm(commentId) {
            const replyForm = document.getElementById(`reply-form-${commentId}`);
            if (replyForm) {
                replyForm.classList.toggle('active');
                if (replyForm.classList.contains('active')) {
                    replyForm.querySelector('textarea').focus();
                }
            }
        }
        
        // Delete Comment
        function deleteComment(commentId) {
            confirmDelete({
                title: 'Xóa bình luận',
                message: 'Bạn có chắc muốn xóa bình luận này?',
                theme: 'user',
                confirmText: 'Xóa',
                cancelText: 'Hủy',
                onConfirm: function() {
                    fetch('<?= BASE_URL ?>/news/delete-comment/' + commentId, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Đã xóa bình luận!');
                            // Reload trang để cập nhật
                            location.reload();
                        } else {
                            alert(data.message || 'Có lỗi xảy ra!');
                        }
                    })
                    .catch(error => {
                        alert('Có lỗi xảy ra!');
                        console.error('Error:', error);
                    });
                }
            });
        }
    </script>

<?php require_once APP_PATH . '/views/layouts/footer.php'; ?>