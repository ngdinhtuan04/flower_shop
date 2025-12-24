<?php
/**
 * AdminAuthController
 * Xử lý các chức năng xác thực cho admin
 * - Đăng nhập, đăng xuất
 * - Dashboard
 * - Đăng ký admin, OTP
 * - Phê duyệt admin
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../helpers/mail_helper.php';

class AdminAuthController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = $this->model('User');
    }

    /**
     * Trang đăng nhập admin
     */
    public function login()
    {
        // Nếu đã login và là admin thì redirect về dashboard
        if (Session::isLoggedIn() && Session::isAdmin()) {
            $this->redirect('/admin/dashboard');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleAdminLogin();
        } else {
            $this->view('admin/login');
        }
    }

    /**
     * Xử lý đăng nhập admin
     */
    private function handleAdminLogin()
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
            $this->redirect('/admin/login');
            return;
        }

        // Đăng nhập
        $result = $this->userModel->login($data['identifier'], $data['password']);

        if ($result['success']) {
            $user = $result['user'];
            
            // Kiểm tra phải là admin hoặc superadmin
            if ($user['role'] !== 'admin' && $user['role'] !== 'superadmin') {
                Session::setFlash('error', 'Bạn không có quyền truy cập vào trang quản trị');
                $this->redirect('/admin/login');
                return;
            }

            Session::login($user);
            Session::setFlash('success', 'Đăng nhập thành công! Chào mừng ' . $user['full_name']);
            $this->redirect('/admin/dashboard');
        } else {
            // Kiểm tra nếu là admin chưa xác thực OTP
            if (isset($result['pending']) && $result['pending'] === true) {
                // Chỉ xử lý nếu là admin
                if (isset($result['role']) && $result['role'] === 'admin') {
                    // Lưu thông tin để chuyển đến trang OTP admin
                    Session::set('register_email', $result['email']);
                    Session::set('register_role', 'admin');
                    Session::setFlash('info', 'Vui lòng xác thực OTP để hoàn tất đăng ký Admin.');
                    $this->redirect('/admin/verify-otp-admin');
                    return;
                }
            }
            
            Session::setFlash('error', $result['message']);
            Session::setFlash('old', $data);
            $this->redirect('/admin/login');
        }
    }

    /**
     * Đăng xuất
     */
    public function logout()
    {
        Session::logout();
        Session::setFlash('success', 'Đăng xuất thành công!');
        $this->redirect('/admin/login');
    }

    /**
     * Trang Dashboard admin - Quick Overview
     */
    public function dashboard()
    {
        $this->requireAdmin();
        
        $db = \DB::getInstance();
        
        // ============ THỐNG KÊ CƠ BẢN ============
        $totalUsers = $db->fetchOne("SELECT COUNT(*) as total FROM users WHERE role != 'admin' AND role != 'superadmin'")['total'] ?? 0;
        $totalOrders = $db->fetchOne("SELECT COUNT(*) as total FROM orders")['total'] ?? 0;
        $totalProducts = $db->fetchOne("SELECT COUNT(*) as total FROM products")['total'] ?? 0;
        $totalSuppliers = $db->fetchOne("SELECT COUNT(*) as total FROM suppliers WHERE deleted_at IS NULL")['total'] ?? 0;
        $totalRevenue = $db->fetchOne("SELECT COALESCE(SUM(total), 0) as total FROM orders WHERE order_status IN ('delivered')")['total'] ?? 0;
        
        // ============ SO SÁNH VỚI THÁNG TRƯỚC ============
        // Doanh thu tháng này
        $thisMonthRevenue = $db->fetchOne("
            SELECT COALESCE(SUM(total), 0) as total 
            FROM orders 
            WHERE order_status IN ('delivered') 
            AND MONTH(created_at) = MONTH(CURRENT_DATE()) 
            AND YEAR(created_at) = YEAR(CURRENT_DATE())
        ")['total'] ?? 0;
        
        // Doanh thu tháng trước
        $lastMonthRevenue = $db->fetchOne("
            SELECT COALESCE(SUM(total), 0) as total 
            FROM orders 
            WHERE order_status IN ('delivered') 
            AND MONTH(created_at) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)) 
            AND YEAR(created_at) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))
        ")['total'] ?? 0;
        
        // % thay đổi doanh thu
        $revenueChange = $lastMonthRevenue > 0 ? (($thisMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 : 0;
        
        // Đơn hàng tháng này vs tháng trước
        $thisMonthOrders = $db->fetchOne("
            SELECT COUNT(*) as total FROM orders 
            WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
            AND YEAR(created_at) = YEAR(CURRENT_DATE())
        ")['total'] ?? 0;
        
        $lastMonthOrders = $db->fetchOne("
            SELECT COUNT(*) as total FROM orders 
            WHERE MONTH(created_at) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)) 
            AND YEAR(created_at) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))
        ")['total'] ?? 0;
        
        $ordersChange = $lastMonthOrders > 0 ? (($thisMonthOrders - $lastMonthOrders) / $lastMonthOrders) * 100 : 0;
        
        // ============ CẢNH BÁO / ALERTS ============
        $alerts = [];
        
        // Sản phẩm sắp hết hàng (stock < 10)
        $lowStockCount = $db->fetchOne("SELECT COUNT(*) as total FROM products WHERE stock < 10 AND stock > 0")['total'] ?? 0;
        if ($lowStockCount > 0) {
            $alerts[] = ['type' => 'warning', 'icon' => 'fas fa-exclamation-triangle', 'message' => $lowStockCount . ' sản phẩm sắp hết hàng', 'link' => BASE_URL . '/admin/products?filter=low_stock'];
        }
        
        // Đơn hàng chờ xử lý
        $pendingOrders = $db->fetchOne("SELECT COUNT(*) as total FROM orders WHERE order_status = 'pending'")['total'] ?? 0;
        if ($pendingOrders > 0) {
            $alerts[] = ['type' => 'info', 'icon' => 'fas fa-clock', 'message' => $pendingOrders . ' đơn hàng chờ xử lý', 'link' => BASE_URL . '/admin/orders?status=pending'];
        }
        
        // Sản phẩm hết hàng
        $outOfStock = $db->fetchOne("SELECT COUNT(*) as total FROM products WHERE stock = 0")['total'] ?? 0;
        if ($outOfStock > 0) {
            $alerts[] = ['type' => 'danger', 'icon' => 'fas fa-times-circle', 'message' => $outOfStock . ' sản phẩm đã hết hàng', 'link' => BASE_URL . '/admin/products?filter=out_of_stock'];
        }
        
        // Góp ý mới
        $newFeedbackCount = $db->fetchOne("SELECT COUNT(*) as total FROM feedback WHERE status = 'new'")['total'] ?? 0;
        if ($newFeedbackCount > 0) {
            $alerts[] = ['type' => 'success', 'icon' => 'fas fa-comments', 'message' => $newFeedbackCount . ' góp ý mới cần xem', 'link' => BASE_URL . '/admin/feedback?status=new'];
        }
        
        // ============ ĐƠN HÀNG THEO TRẠNG THÁI ============
        $orderStats = $db->fetchAll("
            SELECT order_status, COUNT(*) as count 
            FROM orders 
            GROUP BY order_status
        ");
        
        $statusCounts = [
            'pending' => 0,
            'confirmed' => 0,
            'processing' => 0,
            'shipping' => 0,
            'delivered' => 0,
            'cancelled' => 0
        ];
        
        foreach ($orderStats as $stat) {
            if (isset($statusCounts[$stat['order_status']])) {
                $statusCounts[$stat['order_status']] = $stat['count'];
            }
        }
        
        // ============ MINI CHART DATA (7 ngày gần nhất) ============
        $weeklyRevenue = $db->fetchAll("
            SELECT 
                DATE(created_at) as date,
                COALESCE(SUM(total), 0) as revenue
            FROM orders
            WHERE order_status IN ('delivered')
                AND created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 7 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");
        
        // ============ TOP 3 SẢN PHẨM (Compact) ============
        $topProducts = $db->fetchAll("
            SELECT p.id, p.name, p.image, 
                   COALESCE(SUM(oi.quantity), 0) as total_sold
            FROM products p
            LEFT JOIN order_items oi ON p.id = oi.product_id
            LEFT JOIN orders o ON oi.order_id = o.id AND o.order_status IN ('delivered')
            GROUP BY p.id, p.name, p.image
            HAVING total_sold > 0
            ORDER BY total_sold DESC
            LIMIT 3
        ");
        
        // ============ DOANH THU HÔM NAY ============
        $todayRevenue = $db->fetchOne("
            SELECT COALESCE(SUM(total), 0) as total 
            FROM orders 
            WHERE order_status IN ('delivered') 
            AND DATE(created_at) = CURRENT_DATE()
        ")['total'] ?? 0;
        
        $todayOrders = $db->fetchOne("
            SELECT COUNT(*) as total FROM orders 
            WHERE DATE(created_at) = CURRENT_DATE()
        ")['total'] ?? 0;
        
        $data = [
            'user' => Session::getUser(),
            'stats' => [
                'total_users' => $totalUsers,
                'total_orders' => $totalOrders,
                'total_products' => $totalProducts,
                'total_suppliers' => $totalSuppliers,
                'total_revenue' => $totalRevenue
            ],
            'comparison' => [
                'this_month_revenue' => $thisMonthRevenue,
                'last_month_revenue' => $lastMonthRevenue,
                'revenue_change' => round($revenueChange, 1),
                'this_month_orders' => $thisMonthOrders,
                'last_month_orders' => $lastMonthOrders,
                'orders_change' => round($ordersChange, 1),
                'today_revenue' => $todayRevenue,
                'today_orders' => $todayOrders
            ],
            'alerts' => $alerts,
            'orderStats' => $statusCounts,
            'topProducts' => $topProducts,
            'weeklyRevenue' => $weeklyRevenue,
            'newFeedbackCount' => $newFeedbackCount
        ];

        $this->view('admin/dashboard', $data);
    }

    /**
     * Đăng ký tài khoản admin mới
     */
    public function registerAdmin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->view('admin/register');
            return;
        }

        $fullName = sanitize($_POST['full_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validation
        $validator = validate([
            'full_name' => $fullName,
            'email' => $email,
            'password' => $password,
            'confirm_password' => $confirmPassword
        ]);

        $validator->required('full_name', 'Họ tên không được để trống')
            ->required('email', 'Email không được để trống')
            ->email('email', 'Email không hợp lệ')
            ->required('password', 'Mật khẩu không được để trống')
            ->minLength('password', 6, 'Mật khẩu phải có ít nhất 6 ký tự')
            ->match('password', 'confirm_password', 'Mật khẩu xác nhận không khớp');

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            $this->redirect('/admin/register-admin');
            return;
        }

        // Kiểm tra email đã tồn tại
        if ($this->userModel->findByEmail($email)) {
            Session::setFlash('error', 'Email này đã được sử dụng');
            $this->redirect('/admin/register-admin');
            return;
        }

        // Tạo OTP
        $otp = sprintf('%06d', mt_rand(0, 999999));
        $otpExpiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        // Tạo user với role admin, chưa xác thực
        $userData = [
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'admin',
            'is_verified' => 0,
            'otp_code' => $otp,
            'otp_expiry' => $otpExpiry,
            'admin_status' => 'pending' // Chờ super admin phê duyệt
        ];

        $userId = $this->userModel->createAdmin($userData);

        if ($userId) {
            // Gửi email OTP
            $emailSent = sendOTPEmail($email, $fullName, $otp);

            if ($emailSent) {
                // Lưu email vào session để verify
                Session::set('pending_admin_email', $email);
                Session::setFlash('success', 'Đăng ký thành công! Vui lòng kiểm tra email để xác thực.');
                $this->redirect('/admin/verify-otp-admin');
            } else {
                Session::setFlash('error', 'Không thể gửi email xác thực. Vui lòng thử lại.');
                $this->redirect('/admin/register-admin');
            }
        } else {
            Session::setFlash('error', 'Đăng ký thất bại. Vui lòng thử lại.');
            $this->redirect('/admin/register-admin');
        }
    }

    /**
     * Xác thực OTP cho admin mới
     */
    public function verifyOTPAdmin()
    {
        $pendingEmail = Session::get('pending_admin_email');

        if (!$pendingEmail) {
            Session::setFlash('error', 'Phiên đăng ký đã hết hạn. Vui lòng đăng ký lại.');
            $this->redirect('/admin/register-admin');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->view('admin/verify_otp', ['email' => $pendingEmail]);
            return;
        }

        $otp = sanitize($_POST['otp'] ?? '');

        if (empty($otp)) {
            Session::setFlash('error', 'Vui lòng nhập mã OTP');
            $this->redirect('/admin/verify-otp-admin');
            return;
        }

        // Kiểm tra OTP
        $user = $this->userModel->findByEmail($pendingEmail);

        if (!$user) {
            Session::setFlash('error', 'Không tìm thấy tài khoản');
            $this->redirect('/admin/register-admin');
            return;
        }

        if ($user['otp_code'] !== $otp) {
            Session::setFlash('error', 'Mã OTP không chính xác');
            $this->redirect('/admin/verify-otp-admin');
            return;
        }

        if (strtotime($user['otp_expiry']) < time()) {
            Session::setFlash('error', 'Mã OTP đã hết hạn. Vui lòng yêu cầu mã mới.');
            $this->redirect('/admin/verify-otp-admin');
            return;
        }

        // Xác thực thành công
        $this->userModel->verifyUser($user['id']);

        // Xóa session
        Session::delete('pending_admin_email');

        Session::setFlash('success', 'Xác thực email thành công! Tài khoản của bạn đang chờ Super Admin phê duyệt.');
        $this->redirect('/admin/login');
    }

    /**
     * Gửi lại mã OTP cho admin
     */
    public function resendOTPAdmin()
    {
        $pendingEmail = Session::get('pending_admin_email');

        if (!$pendingEmail) {
            Session::setFlash('error', 'Phiên đăng ký đã hết hạn. Vui lòng đăng ký lại.');
            $this->redirect('/admin/register-admin');
            return;
        }

        $user = $this->userModel->findByEmail($pendingEmail);

        if (!$user) {
            Session::setFlash('error', 'Không tìm thấy tài khoản');
            $this->redirect('/admin/register-admin');
            return;
        }

        // Tạo OTP mới
        $otp = sprintf('%06d', mt_rand(0, 999999));
        $otpExpiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        // Cập nhật OTP
        $this->userModel->updateOTP($user['id'], $otp, $otpExpiry);

        // Gửi email
        $emailSent = sendOTPEmail($pendingEmail, $user['full_name'], $otp);

        if ($emailSent) {
            Session::setFlash('success', 'Đã gửi lại mã OTP. Vui lòng kiểm tra email.');
        } else {
            Session::setFlash('error', 'Không thể gửi email. Vui lòng thử lại.');
        }

        $this->redirect('/admin/verify-otp-admin');
    }

    /**
     * Danh sách admin chờ phê duyệt
     */
    public function pendingAdmins()
    {
        $this->requireAdmin();

        // Chỉ super admin mới có quyền
        $currentUser = Session::getUser();
        if ($currentUser['email'] !== 'admin@flower.com') {
            Session::setFlash('error', 'Bạn không có quyền truy cập trang này');
            $this->redirect('/admin/dashboard');
            return;
        }

        $pendingAdmins = $this->userModel->getPendingAdmins();

        $this->view('admin/pending_admins', [
            'pendingAdmins' => $pendingAdmins
        ]);
    }

    /**
     * Phê duyệt admin
     */
    public function approveAdmin()
    {
        $this->requireAdmin();

        // Chỉ super admin mới có quyền
        $currentUser = Session::getUser();
        if ($currentUser['email'] !== 'admin@flower.com') {
            Session::setFlash('error', 'Bạn không có quyền thực hiện hành động này');
            $this->redirect('/admin/dashboard');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/pending-admins');
            return;
        }

        $userId = intval($_POST['user_id'] ?? 0);
        $action = $_POST['action'] ?? ''; // approve hoặc reject

        $user = $this->userModel->findById($userId);

        if (!$user || $user['role'] !== 'admin') {
            Session::setFlash('error', 'Không tìm thấy tài khoản admin');
            $this->redirect('/admin/pending-admins');
            return;
        }

        if ($action === 'approve') {
            $this->userModel->updateAdminStatus($userId, 'approved');
            // TODO: Thêm function sendApprovalEmail() vào mail_helper.php nếu cần gửi email thông báo
            Session::setFlash('success', 'Đã phê duyệt tài khoản admin: ' . $user['full_name']);
        } elseif ($action === 'reject') {
            $this->userModel->updateAdminStatus($userId, 'rejected');
            // TODO: Thêm function sendApprovalEmail() vào mail_helper.php nếu cần gửi email thông báo
            Session::setFlash('success', 'Đã từ chối tài khoản admin: ' . $user['full_name']);
        }

        $this->redirect('/admin/pending-admins');
    }
}
