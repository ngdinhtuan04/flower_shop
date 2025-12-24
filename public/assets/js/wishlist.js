/**
 * Wishlist JavaScript - Xử lý các chức năng wishlist
 */

// Khởi tạo khi document ready
document.addEventListener("DOMContentLoaded", function () {
    initWishlist();
});

/**
 * Khởi tạo wishlist
 */
function initWishlist() {
    // Load wishlist count
    updateWishlistCount();

    // Bind các nút wishlist trên trang sản phẩm
    bindWishlistButtons();

    // Load trạng thái wishlist cho các sản phẩm
    loadWishlistStatus();

    console.log("Wishlist initialized");
}

/**
 * Toggle wishlist (thêm/xóa)
 */
function toggleWishlist(productId, button = null) {
    fetch(BASE_URL + "/wishlist/toggle", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "product_id=" + productId,
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                // Cập nhật UI của button
                if (button) {
                    updateWishlistButton(button, data.in_wishlist);
                }

                // Cập nhật tất cả các button có cùng product_id
                updateAllWishlistButtons(productId, data.in_wishlist);

                // Hiển thị thông báo
                showNotification(data.message, "success");

                // Cập nhật badge
                if (window.updateWishlistBadge) {
                    window.updateWishlistBadge(data.wishlist_count);
                }
            } else {
                showNotification(data.message || "Có lỗi xảy ra", "error");

                // Nếu cần redirect (chưa đăng nhập)
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                }
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            showNotification("Có lỗi xảy ra, vui lòng thử lại", "error");
        });
}

/**
 * Thêm vào wishlist
 */
function addToWishlist(productId, button = null) {
    fetch(BASE_URL + "/wishlist/add", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "product_id=" + productId,
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                if (button) {
                    updateWishlistButton(button, true);
                }
                updateAllWishlistButtons(productId, true);
                showNotification(data.message, "success");
                if (window.updateWishlistBadge) {
                    window.updateWishlistBadge(data.wishlist_count);
                }
            } else {
                showNotification(data.message || "Có lỗi xảy ra", "error");
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1500);
                }
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            showNotification("Có lỗi xảy ra", "error");
        });
}

/**
 * Xóa khỏi wishlist
 */
function removeFromWishlist(productId, button = null) {
    fetch(BASE_URL + "/wishlist/remove", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "product_id=" + productId,
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                if (button) {
                    updateWishlistButton(button, false);
                }
                updateAllWishlistButtons(productId, false);
                showNotification(data.message, "success");
                if (window.updateWishlistBadge) {
                    window.updateWishlistBadge(data.wishlist_count);
                }
            } else {
                showNotification(data.message || "Có lỗi xảy ra", "error");
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            showNotification("Có lỗi xảy ra", "error");
        });
}

/**
 * Kiểm tra sản phẩm có trong wishlist không
 */
function checkWishlist(productId, callback) {
    fetch(BASE_URL + "/wishlist/check?product_id=" + productId)
        .then((response) => response.json())
        .then((data) => {
            if (data.success && callback) {
                callback(data.in_wishlist);
            }
        })
        .catch((error) => {
            console.error("Error:", error);
        });
}

/**
 * Cập nhật số lượng wishlist
 */
function updateWishlistCount() {
    fetch(BASE_URL + "/wishlist/count")
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                if (window.updateWishlistBadge) {
                    window.updateWishlistBadge(data.count);
                }
            }
        })
        .catch((error) => {
            console.error("Error:", error);
        });
}

/**
 * Cập nhật trạng thái button wishlist
 */
function updateWishlistButton(button, inWishlist) {
    const icon = button.querySelector("i");

    if (inWishlist) {
        button.classList.add("in-wishlist");
        button.setAttribute("title", "Xóa khỏi yêu thích");
        if (icon) {
            icon.classList.remove("far");
            icon.classList.add("fas");
        }
    } else {
        button.classList.remove("in-wishlist");
        button.setAttribute("title", "Thêm vào yêu thích");
        if (icon) {
            icon.classList.remove("fas");
            icon.classList.add("far");
        }
    }
}

/**
 * Cập nhật tất cả các button cùng product_id
 */
function updateAllWishlistButtons(productId, inWishlist) {
    const buttons = document.querySelectorAll(
        `[data-product-id="${productId}"].btn-wishlist, [data-product-id="${productId}"].btn-wishlist-icon`
    );
    buttons.forEach((button) => {
        updateWishlistButton(button, inWishlist);
    });
}

/**
 * Bind event cho các button wishlist
 */
function bindWishlistButtons() {
    const wishlistButtons = document.querySelectorAll(
        ".btn-wishlist, .btn-wishlist-icon"
    );

    wishlistButtons.forEach((button) => {
        // Xóa event cũ nếu có
        const newButton = button.cloneNode(true);
        button.parentNode.replaceChild(newButton, button);

        // Bind event mới
        newButton.addEventListener("click", function (e) {
            e.preventDefault();
            e.stopPropagation();

            const productId = this.getAttribute("data-product-id");
            if (productId) {
                toggleWishlist(productId, this);
            }
        });
    });
}

/**
 * Hiển thị thông báo
 */
function showNotification(message, type = "success") {
    // Xóa notification cũ nếu có
    const oldNotification = document.querySelector(".notification");
    if (oldNotification) {
        oldNotification.remove();
    }

    // Tạo notification mới
    const notification = document.createElement("div");
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${
            type === "success" ? "check-circle" : "exclamation-circle"
        }"></i>
        <span>${message}</span>
    `;

    document.body.appendChild(notification);

    // Hiển thị với animation
    setTimeout(() => {
        notification.classList.add("show");
    }, 10);

    // Tự động ẩn sau 3 giây
    setTimeout(() => {
        notification.classList.remove("show");
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

/**
 * Load wishlist status cho các sản phẩm trên trang
 */
function loadWishlistStatus() {
    const buttons = document.querySelectorAll(
        ".btn-wishlist[data-product-id], .btn-wishlist-icon[data-product-id]"
    );
    const productIds = Array.from(buttons).map((btn) =>
        btn.getAttribute("data-product-id")
    );

    // Loại bỏ duplicate
    const uniqueIds = [...new Set(productIds)];

    // Check từng sản phẩm
    uniqueIds.forEach((productId) => {
        checkWishlist(productId, (inWishlist) => {
            updateAllWishlistButtons(productId, inWishlist);
        });
    });
}

// Export functions cho global scope
window.toggleWishlist = toggleWishlist;
window.addToWishlist = addToWishlist;
window.removeFromWishlist = removeFromWishlist;
window.checkWishlist = checkWishlist;
window.updateWishlistCount = updateWishlistCount;
// KHÔNG export updateWishlistBadge vì header.php đã có rồi
// window.updateWishlistBadge = updateWishlistBadge;
window.showNotification = showNotification;
window.loadWishlistStatus = loadWishlistStatus;
