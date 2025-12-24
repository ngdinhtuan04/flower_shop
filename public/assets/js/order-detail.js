/**
 * Order Detail Page Scripts
 */

function cancelOrder(orderId) {
    const reason = prompt("Vui lòng nhập lý do hủy đơn hàng:");
    if (reason && reason.trim()) {
        confirmDelete({
            title: "Hủy đơn hàng",
            message: "Bạn có chắc chắn muốn hủy đơn hàng này?",
            theme: "user",
            confirmText: "Hủy đơn hàng",
            cancelText: "Không",
            onConfirm: function () {
                // TODO: Implement cancel order API
                alert("Chức năng hủy đơn hàng đang được phát triển");
            },
        });
    }
}
