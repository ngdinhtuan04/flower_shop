<?php
/**
 * AddressController - Quản lý địa chỉ giao hàng của user
 * CRUD cho nhiều địa chỉ, set mặc định
 */
class AddressController extends Controller
{
    private $addressModel;

    public function __construct()
    {
        $this->addressModel = $this->model('UserAddress');
    }

    /**
     * Kiểm tra đăng nhập
     */
    private function requireLogin()
    {
        if (!Session::isLoggedIn()) {
            if ($this->isAjax()) {
                echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập']);
                exit;
            }
            Session::setFlash('error', 'Vui lòng đăng nhập để quản lý địa chỉ');
            $this->redirect('/user/login');
        }
    }

    /**
     * Check if request is AJAX
     */
    private function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    /**
     * Trang danh sách địa chỉ
     * Route: /address hoặc /user/addresses
     */
    public function index()
    {
        $this->requireLogin();

        $userId = Session::get('user_id');
        $addresses = $this->addressModel->getByUserId($userId);

        $this->view('users/addresses', [
            'addresses' => $addresses,
            'totalAddresses' => count($addresses)
        ]);
    }

    /**
     * API: Lấy danh sách địa chỉ (JSON)
     * Route: /address/list (AJAX)
     */
    public function getList()
    {
        $this->requireLogin();

        $userId = Session::get('user_id');
        $addresses = $this->addressModel->getByUserId($userId);

        echo json_encode([
            'success' => true,
            'addresses' => $addresses
        ]);
    }

    /**
     * API: Lấy địa chỉ mặc định (JSON)
     * Route: /address/default (AJAX)
     */
    public function getDefault()
    {
        $this->requireLogin();

        $userId = Session::get('user_id');
        $address = $this->addressModel->getDefaultAddress($userId);

        if ($address) {
            echo json_encode([
                'success' => true,
                'address' => $address,
                'full_address' => UserAddress::formatFullAddress($address)
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Chưa có địa chỉ mặc định'
            ]);
        }
    }

    /**
     * API: Lấy chi tiết 1 địa chỉ (JSON)
     * Route: /address/detail/{id} (AJAX)
     */
    public function detail($addressId)
    {
        $this->requireLogin();

        if (!is_numeric($addressId)) {
            echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
            return;
        }

        $userId = Session::get('user_id');
        $address = $this->addressModel->getById($addressId, $userId);

        if ($address) {
            echo json_encode([
                'success' => true,
                'address' => $address
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy địa chỉ'
            ]);
        }
    }

    /**
     * Thêm địa chỉ mới
     * Route: /address/add (POST)
     */
    public function add()
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        $data = [
            'recipient_name' => trim($_POST['recipient_name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'province' => trim($_POST['province'] ?? ''),
            'district' => trim($_POST['district'] ?? ''),
            'ward' => trim($_POST['ward'] ?? ''),
            'address_detail' => trim($_POST['address_detail'] ?? ''),
            'address_type' => $_POST['address_type'] ?? 'home',
            'is_default' => isset($_POST['is_default']) ? 1 : 0
        ];

        // Validation
        $errors = [];

        if (empty($data['recipient_name'])) {
            $errors[] = 'Vui lòng nhập tên người nhận';
        }

        if (empty($data['phone']) || !preg_match('/^0[0-9]{9}$/', $data['phone'])) {
            $errors[] = 'Số điện thoại không hợp lệ (phải có 10 số, bắt đầu bằng 0)';
        }

        if (empty($data['province'])) {
            $errors[] = 'Vui lòng chọn Tỉnh/Thành phố';
        }

        if (empty($data['district'])) {
            $errors[] = 'Vui lòng chọn Quận/Huyện';
        }

        if (empty($data['ward'])) {
            $errors[] = 'Vui lòng chọn Phường/Xã';
        }

        if (empty($data['address_detail'])) {
            $errors[] = 'Vui lòng nhập địa chỉ chi tiết';
        }

        if (!in_array($data['address_type'], ['home', 'office'])) {
            $data['address_type'] = 'home';
        }

        if (!empty($errors)) {
            echo json_encode([
                'success' => false,
                'message' => implode('<br>', $errors)
            ]);
            return;
        }

        // Thêm địa chỉ
        $userId = Session::get('user_id');
        $addressId = $this->addressModel->create($userId, $data);

        if ($addressId) {
            echo json_encode([
                'success' => true,
                'message' => 'Thêm địa chỉ thành công',
                'address_id' => $addressId
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Có lỗi xảy ra, vui lòng thử lại'
            ]);
        }
    }

    /**
     * Cập nhật địa chỉ
     * Route: /address/update/{id} (POST)
     */
    public function update($addressId)
    {
        $this->requireLogin();

        if (!is_numeric($addressId) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }

        $data = [
            'recipient_name' => trim($_POST['recipient_name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'province' => trim($_POST['province'] ?? ''),
            'district' => trim($_POST['district'] ?? ''),
            'ward' => trim($_POST['ward'] ?? ''),
            'address_detail' => trim($_POST['address_detail'] ?? ''),
            'address_type' => $_POST['address_type'] ?? 'home',
            'is_default' => isset($_POST['is_default']) ? 1 : 0
        ];

        // Validation (tương tự add)
        $errors = [];

        if (empty($data['recipient_name'])) {
            $errors[] = 'Vui lòng nhập tên người nhận';
        }

        if (empty($data['phone']) || !preg_match('/^0[0-9]{9}$/', $data['phone'])) {
            $errors[] = 'Số điện thoại không hợp lệ';
        }

        if (empty($data['province']) || empty($data['district']) || empty($data['ward'])) {
            $errors[] = 'Vui lòng chọn đầy đủ Tỉnh/Quận/Phường';
        }

        if (empty($data['address_detail'])) {
            $errors[] = 'Vui lòng nhập địa chỉ chi tiết';
        }

        if (!empty($errors)) {
            echo json_encode([
                'success' => false,
                'message' => implode('<br>', $errors)
            ]);
            return;
        }

        // Cập nhật
        $userId = Session::get('user_id');
        $result = $this->addressModel->update($addressId, $userId, $data);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật địa chỉ thành công'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy địa chỉ hoặc bạn không có quyền'
            ]);
        }
    }

    /**
     * Đặt địa chỉ làm mặc định
     * Route: /address/set-default/{id} (POST)
     */
    public function setDefault($addressId)
    {
        $this->requireLogin();

        if (!is_numeric($addressId)) {
            echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
            return;
        }

        $userId = Session::get('user_id');
        $result = $this->addressModel->setDefault($addressId, $userId);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Đã đặt làm địa chỉ mặc định'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy địa chỉ'
            ]);
        }
    }

    /**
     * Xóa địa chỉ
     * Route: /address/delete/{id} (POST)
     */
    public function delete($addressId)
    {
        $this->requireLogin();

        if (!is_numeric($addressId)) {
            echo json_encode(['success' => false, 'message' => 'ID không hợp lệ']);
            return;
        }

        $userId = Session::get('user_id');
        $result = $this->addressModel->delete($addressId, $userId);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Xóa địa chỉ thành công'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Không tìm thấy địa chỉ hoặc bạn không có quyền'
            ]);
        }
    }
}
