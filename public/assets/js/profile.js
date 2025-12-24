/**
 * Profile Page JavaScript
 */

function previewAndUploadAvatar(event) {
    const file = event.target.files[0];
    if (!file) return;

    // Validate file type
    if (!file.type.match("image.*")) {
        showToast("Vui lòng chọn file ảnh!", "error");
        return;
    }

    // Validate file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
        showToast("Kích thước ảnh không được vượt quá 5MB!", "error");
        return;
    }

    // Preview
    const reader = new FileReader();
    reader.onload = function (e) {
        const preview = document.getElementById("avatarPreview");
        if (preview.tagName === "IMG") {
            preview.src = e.target.result;
        } else {
            const img = document.createElement("img");
            img.src = e.target.result;
            img.className = "avatar-image";
            img.id = "avatarPreview";
            preview.parentNode.replaceChild(img, preview);
        }

        // Update header avatar immediately
        const headerAvatar = document.querySelector(
            ".user-btn img, .user-btn i.fa-user-circle"
        );
        if (headerAvatar) {
            if (headerAvatar.tagName === "IMG") {
                headerAvatar.src = e.target.result;
            } else {
                const newImg = document.createElement("img");
                newImg.src = e.target.result;
                newImg.style.cssText =
                    "width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 2px solid white;";
                headerAvatar.parentNode.replaceChild(newImg, headerAvatar);
            }
        }
    };
    reader.readAsDataURL(file);

    // Show loading toast
    showToast("Đang tải ảnh lên...", "info");

    // Auto submit form
    document.getElementById("avatarForm").submit();
}
