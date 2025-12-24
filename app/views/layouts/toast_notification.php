<!-- Toast Notification Style & Script -->
<link rel="stylesheet" href="<?= ASSETS_URL ?>/css/layout-toast.css">

<script>
    function showToast(type, title, message) {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        let icon = '';
        switch(type) {
            case 'success':
                icon = '<i class="fas fa-check-circle"></i>';
                break;
            case 'error':
                icon = '<i class="fas fa-exclamation-circle"></i>';
                break;
            case 'warning':
                icon = '<i class="fas fa-exclamation-triangle"></i>';
                break;
            case 'info':
                icon = '<i class="fas fa-info-circle"></i>';
                break;
        }
        
        toast.innerHTML = `
            <div class="toast-icon">${icon}</div>
            <div class="toast-content">
                <div class="toast-title">${title}</div>
                <div class="toast-message">${message}</div>
            </div>
            <div class="toast-close" onclick="this.parentElement.remove()">×</div>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => toast.classList.add('show'), 100);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 400);
        }, 4000);
    }
    
    // Auto-show toast from flash messages
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (Session::hasFlash('success')): ?>
            showToast('success', 'Thành công!', <?= json_encode(Session::getFlash('success')) ?>);
        <?php endif; ?>
        
        <?php if (Session::hasFlash('error')): ?>
            showToast('error', 'Lỗi!', <?= json_encode(Session::getFlash('error')) ?>);
        <?php endif; ?>
        
        <?php if (Session::hasFlash('info')): ?>
            showToast('info', 'Thông báo!', <?= json_encode(Session::getFlash('info')) ?>);
        <?php endif; ?>
        
        <?php if (Session::hasFlash('warning')): ?>
            showToast('warning', 'Cảnh báo!', <?= json_encode(Session::getFlash('warning')) ?>);
        <?php endif; ?>
    });
</script>
