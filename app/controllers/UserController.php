<?php
/**
 * UserController - Xử lý đăng ký, đăng nhập, OTP, quên mật khẩu cho USER
 */
class UserController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = $this->model('User');
    }

    /**
     * Trang đăng nhập User
     */
    public function login()
    {
        // Nếu đã login thì redirect về home
        if (Session::isLoggedIn() && !Session::isAdmin()) {
            $this->redirect('/home');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleLogin();
        } else {
            $this->view('users/login');
        }
    }

    /**
     * Xử lý đăng nhập
     */
    private function handleLogin()
    {
        $data = [
            'identifier' => sanitize($_POST['identifier'] ?? ''),
            'password' => $_POST['password'] ?? ''
        ];

        // Validation
        $validator = validate($data);
        $validator->required('identifier', 'Email hoặc tên đăng nhập không được để trống')
                  ->required('password', 'Mật khẩu không được để trống');

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            Session::setFlash('old', $data);
            $this->redirect('/user/login');
            return;
        }

        // Đăng nhập
        $result = $this->userModel->login($data['identifier'], $data['password']);

        if ($result['success']) {
            $user = $result['user'];
            
            // Kiểm tra chỉ cho phép user đăng nhập tại trang này
            if ($user['role'] === 'admin' || $user['role'] === 'superadmin') {
                Session::setFlash('error', 'Tài khoản quản trị vui lòng đăng nhập tại trang Admin');
                $this->redirect('/user/login');
                return;
            }
            
            Session::login($user);
            Session::setFlash('success', 'Đăng nhập thành công!');
            $this->redirect('/home');
        } else {
            // Nếu là pending, cho phép verify lại
            if (isset($result['pending']) && $result['pending']) {
                Session::set('register_email', $result['email']);
                Session::setFlash('error', $result['message']);
                Session::setFlash('verify_link', true);
            } else {
                Session::setFlash('error', $result['message']);
            }
            Session::setFlash('old', $data);
            $this->redirect('/user/login');
        }
    }

    /**
     * Trang đăng ký User
     */
    public function register()
    {
        if (Session::isLoggedIn()) {
            $this->redirect('/home');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleRegister();
        } else {
            $this->view('users/register');
        }
    }

    /**
     * Xử lý đăng ký
     */
    private function handleRegister()
    {
        $data = [
            'username' => sanitize($_POST['username'] ?? ''),
            'email' => sanitize($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'password_confirm' => $_POST['password_confirm'] ?? '',
            'full_name' => sanitize($_POST['full_name'] ?? ''),
            'phone' => sanitize($_POST['phone'] ?? ''),
            'address' => sanitize($_POST['address'] ?? '')
        ];

        // Validation
        $validator = validate($data);
        $validator->required('username', 'Tên đăng nhập không được để trống')
                  ->minLength('username', 3, 'Tên đăng nhập phải có ít nhất 3 ký tự')
                  ->unique('username', 'users', 'username', null, 'Tên đăng nhập đã tồn tại')
                  ->required('email', 'Email không được để trống')
                  ->email('email', 'Email không hợp lệ')
                  ->unique('email', 'users', 'email', null, 'Email đã được sử dụng')
                  ->required('password', 'Mật khẩu không được để trống')
                  ->minLength('password', PASSWORD_MIN_LENGTH, 'Mật khẩu phải có ít nhất ' . PASSWORD_MIN_LENGTH . ' ký tự')
                  ->required('password_confirm', 'Xác nhận mật khẩu không được để trống')
                  ->match('password_confirm', 'password', 'Xác nhận mật khẩu không khớp')
                  ->required('full_name', 'Họ tên không được để trống');

        if (isset($data['phone']) && !empty($data['phone'])) {
            $validator->phone('phone', 'Số điện thoại không hợp lệ');
        }

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            Session::setFlash('old', $data);
            Session::setFlash('errors', $validator->errors());
            $this->redirect('/user/register');
            return;
        }

        // Đăng ký
        $result = $this->userModel->register($data);

        if ($result['success']) {
            // Lưu email vào session để verify
            Session::set('register_email', $data['email']);
            Session::set('register_name', $data['full_name']);
            // Không set flash message ở đây, sẽ hiển thị trên trang verify
            $this->redirect('/user/verify-otp');
        } else {
            Session::setFlash('error', 'Đăng ký thất bại. Vui lòng thử lại!');
            Session::setFlash('old', $data);
            $this->redirect('/user/register');
        }
    }

    /**
     * Trang xác nhận OTP
     */
    public function verifyOTP()
    {
        $email = Session::get('register_email');
        
        if (!$email) {
            $this->redirect('/user/register');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleVerifyOTP();
        } else {
            $this->view('users/verify_otp', ['email' => $email]);
        }
    }

    /**
     * Xử lý xác nhận OTP
     */
    private function handleVerifyOTP()
    {
        $email = Session::get('register_email');
        $otp = sanitize($_POST['otp'] ?? '');

        if (empty($otp)) {
            Session::setFlash('error', 'Vui lòng nhập mã OTP');
            $this->redirect('/user/verify-otp');
            return;
        }

        $result = $this->userModel->verifyOTP($email, $otp);

        if ($result['success']) {
            Session::delete('register_email');
            Session::delete('register_name');
            Session::setFlash('success', 'Xác nhận email thành công! Tài khoản của bạn đã được kích hoạt. Vui lòng đăng nhập.');
            $this->redirect('/user/login');
        } else {
            Session::setFlash('error', $result['message']);
            $this->redirect('/user/verify-otp');
        }
    }

    /**
     * Gửi lại OTP
     */
    public function resendOTP()
    {
        $email = Session::get('register_email');
        
        if (!$email) {
            $this->json(['success' => false, 'message' => 'Session không hợp lệ']);
            return;
        }

        $result = $this->userModel->resendOTP($email);
        $this->json($result);
    }

    /**
     * Trang quên mật khẩu
     */
    public function forgotPassword()
    {
        if (Session::isLoggedIn()) {
            $this->redirect('/home');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleForgotPassword();
        } else {
            $this->view('users/forgot_password');
        }
    }

    /**
     * Xử lý quên mật khẩu - Bước 1: Gửi OTP
     */
    private function handleForgotPassword()
    {
        $email = sanitize($_POST['email'] ?? '');

        $validator = validate(['email' => $email]);
        $validator->required('email', 'Email không được để trống')
                  ->email('email', 'Email không hợp lệ');

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            $this->redirect('/user/forgot-password');
            return;
        }

        // Gửi OTP thay vì gửi link reset ngay
        $result = $this->userModel->sendForgotPasswordOTP($email);

        if ($result['success']) {
            // Lưu email vào session để verify OTP
            Session::set('forgot_password_email', $email);
            Session::setFlash('success', 'Mã OTP đã được gửi đến email của bạn. Vui lòng kiểm tra hòm thư.');
            $this->redirect('/user/verify-forgot-password-otp');
        } else {
            Session::setFlash('error', $result['message']);
            $this->redirect('/user/forgot-password');
        }
    }

    /**
     * Trang xác thực OTP cho forgot password - Bước 2
     */
    public function verifyForgotPasswordOTP()
    {
        $email = Session::get('forgot_password_email');
        
        if (!$email) {
            Session::setFlash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
            $this->redirect('/user/forgot-password');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleVerifyForgotPasswordOTP();
        } else {
            $this->view('users/verify_forgot_password_otp', ['email' => $email]);
        }
    }

    /**
     * Xử lý xác thực OTP forgot password - Bước 3: Gửi link reset
     */
    private function handleVerifyForgotPasswordOTP()
    {
        $email = Session::get('forgot_password_email');
        $otp = sanitize($_POST['otp'] ?? '');

        if (empty($otp)) {
            Session::setFlash('error', 'Vui lòng nhập mã OTP');
            $this->redirect('/user/verify-forgot-password-otp');
            return;
        }

        // Xác thực OTP
        $result = $this->userModel->verifyForgotPasswordOTP($email, $otp);

        if ($result['success']) {
            // OTP đúng, gửi link reset password
            $resetResult = $this->userModel->forgotPassword($email);
            
            if ($resetResult['success']) {
                Session::delete('forgot_password_email');
                Session::setFlash('success', 'Xác thực thành công! Link đặt lại mật khẩu đã được gửi đến email của bạn.');
                Session::setFlash('reset_link_demo', $resetResult['reset_link']); // Development only
                $this->redirect('/user/login');
            } else {
                Session::setFlash('error', 'Có lỗi xảy ra. Vui lòng thử lại.');
                $this->redirect('/user/verify-forgot-password-otp');
            }
        } else {
            Session::setFlash('error', $result['message']);
            $this->redirect('/user/verify-forgot-password-otp');
        }
    }

    /**
     * Gửi lại OTP cho forgot password
     */
    public function resendForgotPasswordOTP()
    {
        $email = Session::get('forgot_password_email');
        
        if (!$email) {
            $this->json(['success' => false, 'message' => 'Phiên làm việc không hợp lệ']);
            return;
        }

        $result = $this->userModel->sendForgotPasswordOTP($email);
        $this->json($result);
    }

    /**
     * Trang reset password
     */
    public function resetPassword()
    {
        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            Session::setFlash('error', 'Token không hợp lệ');
            $this->redirect('/user/login');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleResetPassword($token);
        } else {
            $this->view('users/reset_password', ['token' => $token]);
        }
    }

    /**
     * Xử lý reset password
     */
    private function handleResetPassword($token)
    {
        $data = [
            'password' => $_POST['password'] ?? '',
            'password_confirm' => $_POST['password_confirm'] ?? ''
        ];

        $validator = validate($data);
        $validator->required('password', 'Mật khẩu không được để trống')
                  ->minLength('password', PASSWORD_MIN_LENGTH, 'Mật khẩu phải có ít nhất ' . PASSWORD_MIN_LENGTH . ' ký tự')
                  ->required('password_confirm', 'Xác nhận mật khẩu không được để trống')
                  ->match('password_confirm', 'password', 'Xác nhận mật khẩu không khớp');

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            $this->redirect('/user/reset-password?token=' . $token);
            return;
        }

        $result = $this->userModel->resetPassword($token, $data['password']);

        if ($result['success']) {
            Session::setFlash('success', $result['message']);
            $this->redirect('/user/login');
        } else {
            Session::setFlash('error', $result['message']);
            $this->redirect('/user/reset-password?token=' . $token);
        }
    }

    /**
     * Đăng xuất
     */
    public function logout()
    {
        Session::logout();
        Session::setFlash('success', 'Đăng xuất thành công!');
        $this->redirect('/user/login');
    }

    /**
     * Trang profile
     */
    public function profile()
    {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleUpdateProfile();
        } else {
            // Luôn lấy fresh data từ database
            $userId = Session::get('user_id');
            $user = $this->userModel->findById($userId);
            
            if (!$user) {
                Session::setFlash('error', 'Không tìm thấy thông tin người dùng');
                $this->redirect('/');
                return;
            }
            
            $this->view('users/profile', ['userProfile' => $user]);
        }
    }

    /**
     * Xử lý cập nhật profile
     */
    private function handleUpdateProfile()
    {
        // Xử lý upload avatar
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->uploadAvatar($_FILES['avatar']);
            if ($uploadResult['success']) {
                $avatarData = ['avatar' => $uploadResult['path']];
                $result = $this->userModel->updateProfile(Session::get('user_id'), $avatarData);
                
                if ($result['success']) {
                    // Cập nhật session ngay lập tức
                    $user = $this->userModel->findById(Session::get('user_id'));
                    Session::set('user', $user);
                    Session::set('user_avatar', $uploadResult['path']);
                    
                    // Load lại trang với dữ liệu mới
                    $this->view('users/profile', ['userProfile' => $user]);
                    return;
                }
            }
            Session::setFlash('error', $uploadResult['message'] ?? 'Không thể upload ảnh');
            $this->redirect('/user/profile');
            return;
        }
        
        // Xử lý cập nhật thông tin
        $data = [
            'full_name' => sanitize($_POST['full_name'] ?? ''),
            'phone' => sanitize($_POST['phone'] ?? ''),
            'address' => sanitize($_POST['address'] ?? '')
        ];

        $validator = validate($data);
        $validator->required('full_name', 'Họ tên không được để trống');

        if (isset($data['phone']) && !empty($data['phone'])) {
            $validator->phone('phone', 'Số điện thoại không hợp lệ');
        }

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            $this->redirect('/user/profile');
            return;
        }

        $result = $this->userModel->updateProfile(Session::get('user_id'), $data);

        if ($result['success']) {
            // Cập nhật session với dữ liệu mới
            Session::set('user_full_name', $data['full_name']);
            
            // Load lại user data và hiển thị ngay
            $user = $this->userModel->findById(Session::get('user_id'));
            Session::set('user', $user);
            Session::setFlash('success', 'Cập nhật thông tin thành công!');
            $this->view('users/profile', ['userProfile' => $user]);
            return;
        } else {
            Session::setFlash('error', 'Cập nhật thất bại. Vui lòng thử lại!');
            $this->redirect('/user/profile');
        }
    }
    
    /**
     * Upload avatar
     */
    private function uploadAvatar($file)
    {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        // Validate file type
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'message' => 'Chỉ chấp nhận file ảnh JPG, PNG, GIF hoặc WEBP'];
        }
        
        // Validate file size
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'Kích thước ảnh không được vượt quá 5MB'];
        }
        
        // Create upload directory
        $uploadDir = PUBLIC_PATH . '/uploads/avatars';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'avatar_' . Session::get('user_id') . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . '/' . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'success' => true,
                'path' => 'uploads/avatars/' . $filename
            ];
        }
        
        return ['success' => false, 'message' => 'Không thể upload ảnh'];
    }
}