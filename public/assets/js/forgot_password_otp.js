/**
 * Forgot Password OTP JavaScript
 */

// Only allow numbers
document.addEventListener("DOMContentLoaded", function () {
    const otpInput = document.getElementById("otp");
    if (!otpInput) return;

    otpInput.addEventListener("input", function (e) {
        this.value = this.value.replace(/[^0-9]/g, "");

        // Auto submit when 6 digits entered
        if (this.value.length === 6) {
            document.getElementById("otpForm").submit();
        }
    });

    // Paste OTP from clipboard
    otpInput.addEventListener("paste", function (e) {
        e.preventDefault();
        const pastedText = (e.clipboardData || window.clipboardData).getData(
            "text"
        );
        const numbers = pastedText.replace(/[^0-9]/g, "").substring(0, 6);
        this.value = numbers;

        if (numbers.length === 6) {
            document.getElementById("otpForm").submit();
        }
    });
});

// Resend OTP
function resendOTP() {
    confirmDelete({
        title: "Gửi lại mã OTP",
        message: "Bạn muốn gửi lại mã OTP?",
        theme: "user",
        confirmText: "Gửi lại",
        cancelText: "Hủy",
        onConfirm: function () {
            fetch(`${BASE_URL}/user/resend-forgot-password-otp`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        alert("✓ Mã OTP mới đã được gửi đến email của bạn!");
                        location.reload();
                    } else {
                        alert("✗ " + (data.message || "Gửi lại OTP thất bại!"));
                    }
                })
                .catch((error) => {
                    console.error("Error:", error);
                    alert("✗ Có lỗi xảy ra. Vui lòng thử lại!");
                });
        },
    });
}
