/**
 * Notifications Page JavaScript
 */

let currentNotificationLink = "";
let lastNotificationCheck = new Date();
let hasNewNotifications = false;

function markAllRead() {
    fetch(`${BASE_URL}/notifications/mark-all-as-read`, {
        method: "POST",
        headers: { "X-Requested-With": "XMLHttpRequest" },
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                location.reload();
            }
        });
}

// View notification detail - mark as read then show modal
function viewNotificationDetailData(element, notificationData) {
    showModal(notificationData, notificationData.is_read);

    // Mark as read if not already
    if (!notificationData.is_read) {
        markAsRead(notificationData.id, element);
    }
}

function viewNotificationDetail(id, isRead) {
    // Get notification data from data attributes
    const item = document.querySelector(`[data-id="${id}"]`);
    if (!item) {
        console.error("Notification item not found");
        return;
    }

    // Get title from h6 text content
    const titleElement = item.querySelector("h6");
    const timeElement = item.querySelector("small");

    const notification = {
        id: id,
        title:
            item.dataset.title ||
            (titleElement
                ? titleElement.textContent.trim()
                : "Không có tiêu đề"),
        message: item.dataset.message || "Không có nội dung",
        type: item.dataset.type || "system",
        link: item.dataset.link || "",
        created_at:
            item.dataset.time ||
            (timeElement
                ? timeElement.textContent.trim().replace(/.*\s+/, "")
                : ""),
    };

    showModal(notification, isRead);

    // Mark as read if not already
    if (!isRead) {
        markAsRead(id, item);
    }
}

function showModal(notification, isRead) {
    const modal = document.getElementById("notificationModal");
    const modalIcon = document.getElementById("modalIcon");
    const modalTitle = document.getElementById("modalTitle");
    const modalTime = document.getElementById("modalTime");
    const modalMessage = document.getElementById("modalMessage");
    const actionBtn = document.getElementById("modalActionBtn");

    if (
        !modal ||
        !modalIcon ||
        !modalTitle ||
        !modalTime ||
        !modalMessage ||
        !actionBtn
    ) {
        return;
    }

    // Set icon background
    const iconClass = notification.type;
    modalIcon.className = "notification-icon-large " + iconClass;
    modalIcon.innerHTML = `<i class="fas ${getIconForType(
        notification.type
    )}"></i>`;

    // Set content
    modalTitle.textContent = notification.title || "Không có tiêu đề";
    modalTime.innerHTML = `<i class="far fa-clock"></i> ${
        notification.created_at || "Không rõ thời gian"
    }`;
    modalMessage.textContent = notification.message || "Không có nội dung";

    // Show/hide action button based on link
    if (notification.link && notification.link !== "") {
        actionBtn.style.display = "block";
        currentNotificationLink = notification.link;
    } else {
        actionBtn.style.display = "none";
    }

    modal.style.display = "flex";
}

function closeModal() {
    document.getElementById("notificationModal").style.display = "none";
}

function navigateFromModal() {
    if (currentNotificationLink) {
        window.location.href = BASE_URL + currentNotificationLink;
    }
}

function markAsRead(id, item) {
    fetch(`${BASE_URL}/notifications/mark-as-read`, {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
            "X-Requested-With": "XMLHttpRequest",
        },
        body: "id=" + id,
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error("HTTP error! status: " + response.status);
            }
            return response.text();
        })
        .then((text) => {
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    // Update badge count in header
                    if (window.updateNotificationBadge) {
                        window.updateNotificationBadge(data.unreadCount);
                    }

                    // Update UI
                    item.classList.remove("unread-item");
                    item.setAttribute("data-read", "true");
                    const badge = item.querySelector(".badge");
                    if (badge) badge.remove();
                }
            } catch (e) {
                // Silently ignore JSON parse errors
            }
        })
        .catch((error) => {
            // Silently ignore errors
        });
}

function getIconForType(type) {
    const icons = {
        review_approved: "fa-check-circle",
        review_rejected: "fa-times-circle",
        order_status: "fa-box",
        promotion: "fa-gift",
        system: "fa-info-circle",
    };
    return icons[type] || "fa-bell";
}

// Delete confirm functions
function showDeleteConfirm(notificationId) {
    document.getElementById("deleteNotificationId").value = notificationId;
    document.getElementById("deleteConfirmModal").style.display = "flex";
}

function closeDeleteConfirm() {
    document.getElementById("deleteConfirmModal").style.display = "none";
}

// Close modal when clicking outside
document.addEventListener("click", function (e) {
    const modal = document.getElementById("notificationModal");
    const deleteModal = document.getElementById("deleteConfirmModal");

    if (e.target === modal) {
        closeModal();
    }

    if (e.target === deleteModal) {
        closeDeleteConfirm();
    }
});

// Auto-refresh notifications every 10 seconds
function checkNewNotifications() {
    fetch(`${BASE_URL}/notifications/get-recent`, {
        headers: { "X-Requested-With": "XMLHttpRequest" },
    })
        .then((response) => response.json())
        .then((data) => {
            if (
                data.success &&
                data.notifications &&
                data.notifications.length > 0
            ) {
                // Check if there are new notifications
                const latestNotification = data.notifications[0];
                const notificationTime = new Date(
                    latestNotification.created_at
                );

                if (notificationTime > lastNotificationCheck) {
                    hasNewNotifications = true;
                    showNewNotificationBanner();
                }
            }
        })
        .catch((error) =>
            console.error("Error checking notifications:", error)
        );
}

function showNewNotificationBanner() {
    if (document.querySelector(".new-notification-banner")) return;

    const banner = document.createElement("div");
    banner.className = "new-notification-banner";
    banner.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 25px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        z-index: 9999;
        animation: slideInRight 0.5s ease-out;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
    `;
    banner.innerHTML = `
        <i class="fas fa-bell" style="font-size: 20px;"></i>
        <div>
            <div style="font-weight: 600;">Bạn có thông báo mới!</div>
            <div style="font-size: 12px; opacity: 0.9;">Click để tải lại trang</div>
        </div>
    `;
    banner.onclick = () => location.reload();

    document.body.appendChild(banner);

    // Auto hide after 5 seconds - also auto reload list
    setTimeout(() => {
        banner.style.animation = "slideOutRight 0.5s ease-out";
        setTimeout(() => {
            banner.remove();
            // Auto reload notification list
            reloadNotificationList();
        }, 500);
    }, 5000);
}

function reloadNotificationList() {
    fetch(window.location.href, {
        headers: { "X-Requested-With": "XMLHttpRequest" },
    })
        .then((response) => response.text())
        .then((html) => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, "text/html");
            const newList = doc.querySelector(".list-group-flush");
            const currentList = document.querySelector(".list-group-flush");

            if (newList && currentList) {
                // Smooth transition
                currentList.style.opacity = "0.5";
                setTimeout(() => {
                    currentList.innerHTML = newList.innerHTML;
                    currentList.style.opacity = "1";
                }, 200);
            }
        })
        .catch((error) => {
            // Fallback to full reload if AJAX fails
            location.reload();
        });
}

// Check for new notifications every 10 seconds
setInterval(checkNewNotifications, 10000);

// Update last check time
lastNotificationCheck = new Date();
