/**
 * OTP Verification JavaScript
 */

function resendOTP() {
    confirmDelete({
        title: "Gửi lại mã OTP",
        message: "Bạn muốn gửi lại mã OTP?",
        theme: "user",
        confirmText: "Gửi lại",
        cancelText: "Hủy",
        onConfirm: function () {
            fetch(`${BASE_URL}/user/resend-otp`, {
                method: "POST",
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
                    alert("✗ Có lỗi xảy ra. Vui lòng thử lại!");
                });
        },
    });
}

// Auto submit when 6 digits entered
document.addEventListener("DOMContentLoaded", function () {
    const otpInput = document.getElementById("otp");
    if (otpInput) {
        otpInput.addEventListener("input", function (e) {
            if (this.value.length === 6) {
                document.getElementById("otpForm").submit();
            }
        });
    }
});
