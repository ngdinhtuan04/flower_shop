/**
 * Custom Confirm Dialog
 * Thay thế window.confirm() bằng popup đẹp hơn
 * Hỗ trợ 2 theme: admin (tím) và user (hồng)
 */

// Tạo CSS cho confirm dialog
(function () {
    if (document.getElementById("confirm-dialog-styles")) return;

    const style = document.createElement("style");
    style.id = "confirm-dialog-styles";
    style.textContent = `
        .confirm-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .confirm-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        
        .confirm-dialog {
            background: white;
            border-radius: 16px;
            padding: 0;
            max-width: 420px;
            width: 90%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            transform: scale(0.9) translateY(-20px);
            transition: all 0.3s ease;
        }
        
        .confirm-overlay.active .confirm-dialog {
            transform: scale(1) translateY(0);
        }
        
        .confirm-header {
            padding: 25px 25px 0;
            text-align: center;
        }
        
        .confirm-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 32px;
        }
        
        /* Admin theme - Dark Slate */
        .confirm-dialog.admin .confirm-icon {
            background: linear-gradient(135deg, rgba(51, 65, 85, 0.15) 0%, rgba(71, 85, 105, 0.15) 100%);
            color: #334155;
        }
        
        .confirm-dialog.admin .confirm-icon.danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(220, 38, 38, 0.15) 100%);
            color: #ef4444;
        }
        
        .confirm-dialog.admin .confirm-icon.warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.15) 0%, rgba(217, 119, 6, 0.15) 100%);
            color: #f59e0b;
        }
        
        .confirm-dialog.admin .confirm-icon.success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(5, 150, 105, 0.15) 100%);
            color: #10b981;
        }
        
        /* User theme - Pink */
        .confirm-dialog.user .confirm-icon {
            background: linear-gradient(135deg, rgba(255, 107, 157, 0.15) 0%, rgba(255, 154, 158, 0.15) 100%);
            color: #ff6b9d;
        }
        
        .confirm-dialog.user .confirm-icon.danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(220, 38, 38, 0.15) 100%);
            color: #ef4444;
        }
        
        .confirm-dialog.user .confirm-icon.warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.15) 0%, rgba(217, 119, 6, 0.15) 100%);
            color: #f59e0b;
        }
        
        .confirm-dialog.user .confirm-icon.success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(5, 150, 105, 0.15) 100%);
            color: #10b981;
        }
        
        .confirm-title {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            margin: 0 0 8px;
        }
        
        .confirm-message {
            font-size: 15px;
            color: #6b7280;
            line-height: 1.6;
            margin: 0;
            padding: 0 25px 25px;
            text-align: center;
        }
        
        .confirm-buttons {
            display: flex;
            gap: 12px;
            padding: 20px 25px;
            background: #f9fafb;
            border-radius: 0 0 16px 16px;
            border-top: 1px solid #e5e7eb;
        }
        
        .confirm-btn {
            flex: 1;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .confirm-btn-cancel {
            background: white;
            color: #6b7280;
            border: 2px solid #e5e7eb;
        }
        
        .confirm-btn-cancel:hover {
            background: #f3f4f6;
            border-color: #d1d5db;
        }
        
        /* Admin confirm button */
        .confirm-dialog.admin .confirm-btn-confirm {
            background: linear-gradient(135deg, #334155 0%, #475569 100%);
            color: white;
        }
        
        .confirm-dialog.admin .confirm-btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(51, 65, 85, 0.4);
        }
        
        .confirm-dialog.admin .confirm-btn-confirm.danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
        
        .confirm-dialog.admin .confirm-btn-confirm.danger:hover {
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }
        
        .confirm-dialog.admin .confirm-btn-confirm.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        .confirm-dialog.admin .confirm-btn-confirm.success:hover {
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }
        
        /* User confirm button */
        .confirm-dialog.user .confirm-btn-confirm {
            background: linear-gradient(135deg, #ff6b9d 0%, #c44569 100%);
            color: white;
        }
        
        .confirm-dialog.user .confirm-btn-confirm:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 107, 157, 0.4);
        }
        
        .confirm-dialog.user .confirm-btn-confirm.danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }
        
        .confirm-dialog.user .confirm-btn-confirm.danger:hover {
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }
        
        .confirm-dialog.user .confirm-btn-confirm.success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        .confirm-dialog.user .confirm-btn-confirm.success:hover {
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }
        
        @media (max-width: 480px) {
            .confirm-dialog {
                width: 95%;
                margin: 10px;
            }
            
            .confirm-buttons {
                flex-direction: column-reverse;
            }
        }
    `;
    document.head.appendChild(style);
})();

/**
 * Show confirm dialog
 * @param {Object} options - Configuration options
 * @param {string} options.title - Dialog title
 * @param {string} options.message - Dialog message
 * @param {string} options.type - Icon type: 'warning', 'danger', 'success', 'info' (default: 'warning')
 * @param {string} options.theme - Theme: 'admin' or 'user' (default: auto-detect)
 * @param {string} options.confirmText - Confirm button text (default: 'Xác nhận')
 * @param {string} options.cancelText - Cancel button text (default: 'Hủy')
 * @param {Function} options.onConfirm - Callback when confirmed
 * @param {Function} options.onCancel - Callback when cancelled
 * @returns {Promise<boolean>} - Resolves true if confirmed, false if cancelled
 */
function showConfirmDialog(options) {
    return new Promise((resolve) => {
        const {
            title = "Xác nhận",
            message = "Bạn có chắc chắn muốn thực hiện hành động này?",
            type = "warning",
            theme = detectTheme(),
            confirmText = "Xác nhận",
            cancelText = "Hủy",
            onConfirm = null,
            onCancel = null,
        } = options;

        // Icon based on type
        const icons = {
            warning: '<i class="fas fa-exclamation-triangle"></i>',
            danger: '<i class="fas fa-trash-alt"></i>',
            success: '<i class="fas fa-check-circle"></i>',
            info: '<i class="fas fa-info-circle"></i>',
            question: '<i class="fas fa-question-circle"></i>',
        };

        // Create overlay
        const overlay = document.createElement("div");
        overlay.className = "confirm-overlay";
        overlay.innerHTML = `
            <div class="confirm-dialog ${theme}">
                <div class="confirm-header">
                    <div class="confirm-icon ${type}">
                        ${icons[type] || icons.warning}
                    </div>
                    <h3 class="confirm-title">${title}</h3>
                </div>
                <p class="confirm-message">${message}</p>
                <div class="confirm-buttons">
                    <button class="confirm-btn confirm-btn-cancel">
                        <i class="fas fa-times"></i>
                        ${cancelText}
                    </button>
                    <button class="confirm-btn confirm-btn-confirm ${type}">
                        <i class="fas fa-check"></i>
                        ${confirmText}
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);

        // Trigger animation
        requestAnimationFrame(() => {
            overlay.classList.add("active");
        });

        // Handle button clicks
        const cancelBtn = overlay.querySelector(".confirm-btn-cancel");
        const confirmBtn = overlay.querySelector(".confirm-btn-confirm");

        function closeDialog(confirmed) {
            overlay.classList.remove("active");
            setTimeout(() => {
                overlay.remove();
                if (confirmed) {
                    if (onConfirm) onConfirm();
                    resolve(true);
                } else {
                    if (onCancel) onCancel();
                    resolve(false);
                }
            }, 300);
        }

        cancelBtn.addEventListener("click", () => closeDialog(false));
        confirmBtn.addEventListener("click", () => closeDialog(true));

        // Close on overlay click
        overlay.addEventListener("click", (e) => {
            if (e.target === overlay) {
                closeDialog(false);
            }
        });

        // Close on Escape key
        function handleEscape(e) {
            if (e.key === "Escape") {
                closeDialog(false);
                document.removeEventListener("keydown", handleEscape);
            }
        }
        document.addEventListener("keydown", handleEscape);
    });
}

/**
 * Auto-detect theme based on current page
 */
function detectTheme() {
    const url = window.location.pathname;
    if (url.includes("/admin")) {
        return "admin";
    }
    return "user";
}

/**
 * Shorthand for delete confirmation
 */
function confirmDelete(options) {
    return showConfirmDialog({
        title: options.title || "Xác nhận xóa",
        message:
            options.message ||
            "Bạn có chắc chắn muốn xóa? Hành động này không thể hoàn tác!",
        type: "danger",
        confirmText: options.confirmText || "Xóa",
        ...options,
    });
}

/**
 * Shorthand for action confirmation
 */
function confirmAction(options) {
    return showConfirmDialog({
        title: options.title || "Xác nhận",
        message:
            options.message || "Bạn có chắc chắn muốn thực hiện hành động này?",
        type: "warning",
        ...options,
    });
}

/**
 * Handle form submit with confirmation
 * Usage: <form onsubmit="return confirmSubmit(event, {...options})">
 */
function confirmSubmit(event, options) {
    event.preventDefault();
    const form = event.target;

    showConfirmDialog(options).then((confirmed) => {
        if (confirmed) {
            // Remove the onsubmit to prevent infinite loop
            form.onsubmit = null;
            form.submit();
        }
    });

    return false;
}

/**
 * Handle link click with confirmation
 * Usage: <a href="..." onclick="return confirmLink(event, {...options})">
 */
function confirmLink(event, options) {
    event.preventDefault();
    const link = event.currentTarget;

    showConfirmDialog(options).then((confirmed) => {
        if (confirmed) {
            window.location.href = link.href;
        }
    });

    return false;
}
