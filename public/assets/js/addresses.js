/**
 * Addresses Page JavaScript
 */

// BASE_URL already declared in header.php
let editingAddressId = null;
let provincesData = [];
let districtsData = [];
let wardsData = [];

// Load provinces from API
async function loadProvinces() {
    try {
        const response = await fetch("https://provinces.open-api.vn/api/p/");
        provincesData = await response.json();

        const provinceSelect = document.getElementById("province");
        provinceSelect.innerHTML =
            '<option value="">-- Chọn Tỉnh/Thành phố --</option>';

        provincesData.forEach((province) => {
            provinceSelect.innerHTML += `<option value="${province.code}" data-name="${province.name}">${province.name}</option>`;
        });
    } catch (error) {
        console.error("Error loading provinces:", error);
        provinceSelect.innerHTML = '<option value="">Lỗi tải dữ liệu</option>';
    }
}

// Handle province change
document
    .getElementById("province")
    .addEventListener("change", async function () {
        const provinceCode = this.value;
        const districtSelect = document.getElementById("district");
        const wardSelect = document.getElementById("ward");

        districtSelect.innerHTML =
            '<option value="">-- Chọn Quận/Huyện --</option>';
        wardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';

        if (!provinceCode) return;

        try {
            districtSelect.innerHTML = '<option value="">Đang tải...</option>';
            const response = await fetch(
                `https://provinces.open-api.vn/api/p/${provinceCode}?depth=2`
            );
            const data = await response.json();
            districtsData = data.districts;

            districtSelect.innerHTML =
                '<option value="">-- Chọn Quận/Huyện --</option>';
            districtsData.forEach((district) => {
                districtSelect.innerHTML += `<option value="${district.code}" data-name="${district.name}">${district.name}</option>`;
            });
        } catch (error) {
            console.error("Error loading districts:", error);
            districtSelect.innerHTML =
                '<option value="">Lỗi tải dữ liệu</option>';
        }
    });

// Handle district change
document
    .getElementById("district")
    .addEventListener("change", async function () {
        const districtCode = this.value;
        const wardSelect = document.getElementById("ward");

        wardSelect.innerHTML = '<option value="">-- Chọn Phường/Xã --</option>';

        if (!districtCode) return;

        try {
            wardSelect.innerHTML = '<option value="">Đang tải...</option>';
            const response = await fetch(
                `https://provinces.open-api.vn/api/d/${districtCode}?depth=2`
            );
            const data = await response.json();
            wardsData = data.wards;

            wardSelect.innerHTML =
                '<option value="">-- Chọn Phường/Xã --</option>';
            wardsData.forEach((ward) => {
                wardSelect.innerHTML += `<option value="${ward.code}" data-name="${ward.name}">${ward.name}</option>`;
            });
        } catch (error) {
            console.error("Error loading wards:", error);
            wardSelect.innerHTML = '<option value="">Lỗi tải dữ liệu</option>';
        }
    });

// Load provinces when page loads
loadProvinces();

// Handle address type radio
document.querySelectorAll(".type-option").forEach((option) => {
    option.addEventListener("click", function () {
        document
            .querySelectorAll(".type-option")
            .forEach((o) => o.classList.remove("active"));
        this.classList.add("active");
        this.querySelector("input").checked = true;
    });
});

// Open Add Modal
function openAddModal() {
    editingAddressId = null;
    document.getElementById("modalTitle").textContent = "Thêm địa chỉ mới";
    document.getElementById("addressForm").reset();
    document.getElementById("addressId").value = "";
    document.querySelectorAll(".type-option")[0].classList.add("active");
    document.querySelectorAll(".type-option")[1].classList.remove("active");
    document.getElementById("addressModal").classList.add("show");
}

// Edit Address
async function editAddress(id) {
    try {
        const response = await fetch(`${BASE_URL}/address/detail/${id}`);
        const data = await response.json();

        if (data.success) {
            editingAddressId = id;
            const addr = data.address;

            document.getElementById("modalTitle").textContent =
                "Chỉnh sửa địa chỉ";
            document.getElementById("addressId").value = id;
            document.getElementById("recipientName").value =
                addr.recipient_name;
            document.getElementById("phone").value = addr.phone;
            document.getElementById("addressDetail").value =
                addr.address_detail;

            // Set address type
            document.querySelector(
                `input[name="address_type"][value="${addr.address_type}"]`
            ).checked = true;
            document
                .querySelectorAll(".type-option")
                .forEach((o) => o.classList.remove("active"));
            document
                .querySelector(
                    `input[name="address_type"][value="${addr.address_type}"]`
                )
                .parentElement.classList.add("active");
            document.getElementById("isDefault").checked = addr.is_default == 1;

            // Find province code by name
            const province = provincesData.find(
                (p) => p.name === addr.province
            );
            if (province) {
                document.getElementById("province").value = province.code;

                // Load districts for this province
                const districtResponse = await fetch(
                    `https://provinces.open-api.vn/api/p/${province.code}?depth=2`
                );
                const districtData = await districtResponse.json();
                districtsData = districtData.districts;

                const districtSelect = document.getElementById("district");
                districtSelect.innerHTML =
                    '<option value="">-- Chọn Quận/Huyện --</option>';
                districtsData.forEach((district) => {
                    districtSelect.innerHTML += `<option value="${district.code}" data-name="${district.name}">${district.name}</option>`;
                });

                // Find district code by name
                const district = districtsData.find(
                    (d) => d.name === addr.district
                );
                if (district) {
                    districtSelect.value = district.code;

                    // Load wards for this district
                    const wardResponse = await fetch(
                        `https://provinces.open-api.vn/api/d/${district.code}?depth=2`
                    );
                    const wardData = await wardResponse.json();
                    wardsData = wardData.wards;

                    const wardSelect = document.getElementById("ward");
                    wardSelect.innerHTML =
                        '<option value="">-- Chọn Phường/Xã --</option>';
                    wardsData.forEach((ward) => {
                        wardSelect.innerHTML += `<option value="${ward.code}" data-name="${ward.name}">${ward.name}</option>`;
                    });

                    // Find ward code by name
                    const ward = wardsData.find((w) => w.name === addr.ward);
                    if (ward) {
                        wardSelect.value = ward.code;
                    }
                }
            }

            document.getElementById("addressModal").classList.add("show");
        } else {
            showToast("error", "Lỗi!", data.message);
        }
    } catch (error) {
        console.error("Edit error:", error);
        showToast("error", "Lỗi!", "Không thể tải thông tin địa chỉ");
    }
}

// Close Modal
function closeModal() {
    document.getElementById("addressModal").classList.remove("show");
    document.getElementById("addressForm").reset();
    editingAddressId = null;
}

// Submit Form
document
    .getElementById("addressForm")
    .addEventListener("submit", async function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        // Get text values from selected options (not codes)
        const provinceSelect = document.getElementById("province");
        const districtSelect = document.getElementById("district");
        const wardSelect = document.getElementById("ward");

        const provinceName =
            provinceSelect.options[provinceSelect.selectedIndex].dataset.name;
        const districtName =
            districtSelect.options[districtSelect.selectedIndex].dataset.name;
        const wardName =
            wardSelect.options[wardSelect.selectedIndex].dataset.name;

        // Replace codes with names
        formData.set("province", provinceName);
        formData.set("district", districtName);
        formData.set("ward", wardName);

        const url = editingAddressId
            ? `${BASE_URL}/address/update/${editingAddressId}`
            : `${BASE_URL}/address/add`;

        try {
            const response = await fetch(url, {
                method: "POST",
                body: formData,
            });
            const data = await response.json();

            if (data.success) {
                showToast("success", "Thành công!", data.message);
                closeModal();
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast("error", "Lỗi!", data.message || "Có lỗi xảy ra");
            }
        } catch (error) {
            showToast("error", "Lỗi!", "Có lỗi xảy ra khi lưu địa chỉ");
        }
    });

// Set Default
async function setDefault(id) {
    showConfirm(
        "Xác nhận đặt mặc định",
        "Bạn có chắc muốn đặt địa chỉ này làm mặc định?",
        async () => {
            try {
                const formData = new FormData();
                const response = await fetch(
                    `${BASE_URL}/address/set-default/${id}`,
                    {
                        method: "POST",
                        body: formData,
                    }
                );
                const data = await response.json();

                if (data.success) {
                    showToast("success", "Thành công!", data.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast("error", "Lỗi!", data.message);
                }
            } catch (error) {
                showToast("error", "Lỗi!", "Có lỗi xảy ra");
            }
        }
    );
}

// Delete Address
async function deleteAddress(id) {
    showConfirm(
        "Xác nhận xóa",
        "Bạn có chắc muốn xóa địa chỉ này? Hành động này không thể hoàn tác.",
        async () => {
            try {
                const formData = new FormData();
                const response = await fetch(
                    `${BASE_URL}/address/delete/${id}`,
                    {
                        method: "POST",
                        body: formData,
                    }
                );
                const data = await response.json();

                if (data.success) {
                    showToast("success", "Thành công!", data.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast("error", "Lỗi!", data.message);
                }
            } catch (error) {
                showToast("error", "Lỗi!", "Có lỗi xảy ra");
            }
        }
    );
}

// Confirmation Modal Functions
let confirmCallback = null;

function showConfirm(title, message, callback) {
    document.getElementById("confirmTitle").textContent = title;
    document.getElementById("confirmMessage").textContent = message;
    confirmCallback = callback;
    document.getElementById("confirmModal").classList.add("show");
}

function closeConfirmModal() {
    document.getElementById("confirmModal").classList.remove("show");
    confirmCallback = null;
}

document.getElementById("confirmButton").addEventListener("click", function () {
    if (confirmCallback) {
        confirmCallback();
        closeConfirmModal();
    }
});

// Close modal when clicking outside
document.getElementById("addressModal").addEventListener("click", function (e) {
    if (e.target === this) {
        closeModal();
    }
});
