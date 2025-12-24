/**
 * Reset Password JavaScript
 */

function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = event.target;
    if (field.type === "password") {
        field.type = "text";
        icon.classList.replace("fa-eye", "fa-eye-slash");
    } else {
        field.type = "password";
        icon.classList.replace("fa-eye-slash", "fa-eye");
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const resetForm = document.getElementById("resetForm");
    if (!resetForm) return;

    resetForm.addEventListener("submit", function (e) {
        const password = document.getElementById("password").value;
        const confirm = document.getElementById("password_confirm").value;

        if (password !== confirm) {
            e.preventDefault();
            alert("Mật khẩu xác nhận không khớp!");
            return false;
        }

        if (password.length < 6) {
            e.preventDefault();
            alert("Mật khẩu phải có ít nhất 6 ký tự!");
            return false;
        }
    });
});
