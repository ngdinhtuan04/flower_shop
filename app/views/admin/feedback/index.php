<?php
$currentRole = Session::get('user_role');
$user = Session::getUser();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý góp ý - Admin</title>
    <?php include APP_PATH . '/views/layouts/favicon.php'; ?>
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/admin-feedback-index.css">
</head>
<body>
    <!-- Toast Container -->
    <div id="toastContainer" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>
    
    <div class="admin-container">
        <?php require_once APP_PATH . '/views/layouts/admin_sidebar.php'; ?>
        
        <div class="main-content">
            <!-- Topbar -->
            <div class="topbar">
                <h2>Quản lý Góp ý</h2>
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <strong><?= htmlspecialchars($user['full_name']) ?></strong>
                </div>
            </div>
            
            <div class="content">
            

            
            <!-- Stats Cards -->
            <div class="stats-cards">
                <div class="stat-card new">
                    <h4><i class="fas fa-inbox"></i> Mới</h4>
                    <div class="number"><?= $statusCounts['new'] ?? 0 ?></div>
                </div>
                <div class="stat-card processing">
                    <h4><i class="fas fa-spinner"></i> Đang xử lý</h4>
                    <div class="number"><?= $statusCounts['processing'] ?? 0 ?></div>
                </div>
                <div class="stat-card resolved">
                    <h4><i class="fas fa-check-circle"></i> Đã giải quyết</h4>
                    <div class="number"><?= $statusCounts['resolved'] ?? 0 ?></div>
                </div>
                <div class="stat-card">
                    <h4><i class="fas fa-times-circle"></i> Đã đóng</h4>
                    <div class="number"><?= $statusCounts['closed'] ?? 0 ?></div>
                </div>
            </div>
            
            <!-- Filter Bar -->
            <form method="GET" action="<?= BASE_URL ?>/admin/feedback" id="filterForm">
                <div class="filter-bar">
                    <select name="status" onchange="this.form.submit()">
                        <option value="">Tất cả trạng thái</option>
                        <option value="new" <?= (isset($_GET['status']) && $_GET['status'] == 'new') ? 'selected' : '' ?>>Mới</option>
                        <option value="processing" <?= (isset($_GET['status']) && $_GET['status'] == 'processing') ? 'selected' : '' ?>>Đang xử lý</option>
                        <option value="resolved" <?= (isset($_GET['status']) && $_GET['status'] == 'resolved') ? 'selected' : '' ?>>Đã giải quyết</option>
                        <option value="closed" <?= (isset($_GET['status']) && $_GET['status'] == 'closed') ? 'selected' : '' ?>>Đã đóng</option>
                    </select>
                    
                    <select name="type" onchange="this.form.submit()">
                        <option value="">Tất cả loại góp ý</option>
                        <option value="complaint" <?= (isset($_GET['type']) && $_GET['type'] == 'complaint') ? 'selected' : '' ?>>Khiếu nại</option>
                        <option value="suggestion" <?= (isset($_GET['type']) && $_GET['type'] == 'suggestion') ? 'selected' : '' ?>>Góp ý</option>
                        <option value="question" <?= (isset($_GET['type']) && $_GET['type'] == 'question') ? 'selected' : '' ?>>Thắc mắc</option>
                        <option value="product_inquiry" <?= (isset($_GET['type']) && $_GET['type'] == 'product_inquiry') ? 'selected' : '' ?>>Hỏi về sản phẩm</option>
                        <option value="other" <?= (isset($_GET['type']) && $_GET['type'] == 'other') ? 'selected' : '' ?>>Khác</option>
                    </select>
                    
                    <?php if (!empty($_GET['status']) || !empty($_GET['type'])): ?>
                        <a href="<?= BASE_URL ?>/admin/feedback" class="btn-filter" style="background: #6b7280; text-decoration: none;">
                            <i class="fas fa-times"></i> Xóa lọc
                        </a>
                    <?php endif; ?>
                </div>
            </form>
            
            <!-- Feedback Table -->
            <div class="feedback-table" id="tableContainer">
                <table>
                    <thead>
                        <tr>
                            <th>Tiêu đề</th>
                            <th>Người gửi</th>
                            <th>Loại</th>
                            <th>Trạng thái</th>
                            <th>Ngày gửi</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($feedbacks)): ?>
                            <?php foreach ($feedbacks as $feedback): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($feedback['subject']) ?></strong>
                                        <?php if (!empty($feedback['product_name'])): ?>
                                            <br><small style="color: #8b5cf6; font-weight: 600;">
                                                <i class="fas fa-box"></i> <?= htmlspecialchars($feedback['product_name']) ?>
                                            </small>
                                        <?php endif; ?>
                                        <br>
                                        <small style="color: #6b7280;">
                                            <?= mb_substr(strip_tags($feedback['message']), 0, 60) ?>...
                                        </small>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($feedback['name']) ?><br>
                                        <small style="color: #6b7280;"><?= htmlspecialchars($feedback['email']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $feedback['type'] ?>">
                                            <?php
                                            $typeLabels = [
                                                'complaint' => 'Khiếu nại',
                                                'suggestion' => 'Góp ý',
                                                'question' => 'Thắc mắc',
                                                'product_inquiry' => 'Hỏi về sản phẩm',
                                                'other' => 'Khác'
                                            ];
                                            echo $typeLabels[$feedback['type']] ?? $feedback['type'];
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $feedback['status'] ?>">
                                            <?php
                                            $statusLabels = [
                                                'new' => 'Mới',
                                                'processing' => 'Đang xử lý',
                                                'resolved' => 'Đã giải quyết',
                                                'closed' => 'Đã đóng'
                                            ];
                                            echo $statusLabels[$feedback['status']] ?? $feedback['status'];
                                            ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($feedback['created_at'])) ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/feedback/detail/<?= $feedback['id'] ?>" class="action-btn btn-view">
                                            <i class="fas fa-eye"></i> Xem
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 40px; color: #6b7280;">
                                    <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
                                    Không có góp ý nào
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            </div>
        </div>
    </div>
    
    <script>
        // Toast notification function
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.style.cssText = `
                background: white;
                padding: 16px 20px;
                border-radius: 10px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                margin-bottom: 10px;
                display: flex;
                align-items: center;
                gap: 12px;
                min-width: 300px;
                animation: slideIn 0.3s ease-out;
                border-left: 4px solid ${type === 'success' ? '#10b981' : '#ef4444'};
            `;
            
            const icon = type === 'success' ? 
                '<i class="fas fa-check-circle" style="color: #10b981; font-size: 20px;"></i>' :
                '<i class="fas fa-exclamation-circle" style="color: #ef4444; font-size: 20px;"></i>';
            
            toast.innerHTML = `
                ${icon}
                <span style="flex: 1; color: #1f2937; font-weight: 500;">${message}</span>
                <button onclick="this.parentElement.remove()" style="background: none; border: none; color: #9ca3af; cursor: pointer; font-size: 18px;">×</button>
            `;
            
            document.getElementById('toastContainer').appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease-in';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
        
        // Show flash messages
        <?php if (Session::hasFlash('success')): ?>
            showToast('<?= addslashes(Session::getFlash('success')) ?>', 'success');
        <?php endif; ?>
        <?php if (Session::hasFlash('error')): ?>
            showToast('<?= addslashes(Session::getFlash('error')) ?>', 'error');
        <?php endif; ?>
        
        const statusSelect = document.querySelector('select[name="status"]');
        const typeSelect = document.querySelector('select[name="type"]');
        
        function performSearch() {
            const status = statusSelect ? statusSelect.value : '';
            const type = typeSelect ? typeSelect.value : '';
            
            const params = new URLSearchParams();
            if (status) params.append('status', status);
            if (type) params.append('type', type);
            
            const url = '<?= BASE_URL ?>/admin/feedback?' + params.toString();
            
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
        
        if (statusSelect) statusSelect.addEventListener('change', performSearch);
        if (typeSelect) typeSelect.addEventListener('change', performSearch);
    </script>
</body>
</html>
