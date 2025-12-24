<?php
/**
 * User Model - Xử lý đăng ký, đăng nhập, OTP, forgot password
 */
class User
{
    private $db;

    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    /**
     * Tìm user theo email
     */
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
        return $this->db->fetchOne($sql, [$email]);
    }

    /**
     * Tìm user theo username
     */
    public function findByUsername($username)
    {
        $sql = "SELECT * FROM users WHERE username = ? LIMIT 1";
        return $this->db->fetchOne($sql, [$username]);
    }

    /**
     * Tìm user theo ID
     */
    public function findById($id)
    {
        $sql = "SELECT id, username, email, password, full_name, phone, address, avatar, role, status, created_at, updated_at FROM users WHERE id = ? LIMIT 1";
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Đăng ký user mới (cần xác nhận OTP)
     */
    public function register($data)
    {
        $otp = generateOTP(OTP_LENGTH);
        $otpExpiry = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));

        $sql = "INSERT INTO users (username, email, password, full_name, phone, address, otp_code, otp_expiry, role, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'user', 'pending', NOW())";

        $params = [
            $data['username'],
            $data['email'],
            password_hash($data['password'], PASSWORD_BCRYPT),
            $data['full_name'],
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $otp,
            $otpExpiry
        ];

        $userId = $this->db->insert($sql, $params);
        
        if ($userId) {
            // Gửi OTP qua email
            require_once APP_PATH . '/helpers/mail_helper.php';
            $emailSent = sendOTPEmail($data['email'], $otp, $data['full_name']);
            
            return [
                'success' => true,
                'user_id' => $userId,
                'email_sent' => $emailSent,
                'otp' => $otp // Chỉ return OTP trong development để test
            ];
        }

        return ['success' => false];
    }

    /**
     * Đăng ký admin (cần xác nhận OTP + superadmin duyệt)
     */
    public function registerAdmin($data)
    {
        $otp = generateOTP(OTP_LENGTH);
        $otpExpiry = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));

        $sql = "INSERT INTO users (username, email, password, full_name, phone, otp_code, otp_expiry, role, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'admin', 'pending', NOW())";

        $params = [
            $data['username'],
            $data['email'],
            password_hash($data['password'], PASSWORD_BCRYPT),
            $data['full_name'],
            $data['phone'] ?? null,
            $otp,
            $otpExpiry
        ];

        $userId = $this->db->insert($sql, $params);
        
        if ($userId) {
            // Gửi OTP qua email
            require_once APP_PATH . '/helpers/mail_helper.php';
            $emailSent = sendOTPEmail($data['email'], $otp, $data['full_name']);
            
            return [
                'success' => true,
                'user_id' => $userId,
                'email_sent' => $emailSent
            ];
        }

        return ['success' => false];
    }

    /**
     * Xác nhận OTP
     */
    public function verifyOTP($email, $otp)
    {
        $sql = "SELECT * FROM users WHERE email = ? AND otp_code = ? AND otp_expiry > NOW() AND status = 'pending' LIMIT 1";
        $user = $this->db->fetchOne($sql, [$email, $otp]);

        if ($user) {
            // User thường: active ngay
            // Admin: giữ pending, chờ SuperAdmin duyệt
            $newStatus = ($user['role'] === 'admin') ? 'pending' : 'active';
            
            $updateSql = "UPDATE users SET status = ?, email_verified = 1, otp_code = NULL, otp_expiry = NULL WHERE id = ?";
            $this->db->execute($updateSql, [$newStatus, $user['id']]);
            
            return ['success' => true, 'user' => $this->findById($user['id'])];
        }

        return ['success' => false, 'message' => 'Mã OTP không hợp lệ hoặc đã hết hạn'];
    }

    /**
     * Gửi lại OTP
     */
    public function resendOTP($email)
    {
        $user = $this->findByEmail($email);
        
        if (!$user || $user['status'] !== 'pending') {
            return ['success' => false, 'message' => 'Email không tồn tại hoặc đã được kích hoạt'];
        }

        $otp = generateOTP(OTP_LENGTH);
        $otpExpiry = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));

        $sql = "UPDATE users SET otp_code = ?, otp_expiry = ? WHERE id = ?";
        $this->db->execute($sql, [$otp, $otpExpiry, $user['id']]);

        // Gửi OTP qua email
        require_once APP_PATH . '/helpers/mail_helper.php';
        $emailSent = sendOTPEmail($email, $otp, $user['full_name']);
        
        return ['success' => true, 'otp' => $otp, 'email_sent' => $emailSent];
    }

    /**
     * Đăng nhập
     */
    public function login($identifier, $password)
    {
        // Identifier có thể là email hoặc username
        $sql = "SELECT * FROM users WHERE (email = ? OR username = ?) LIMIT 1";
        $user = $this->db->fetchOne($sql, [$identifier, $identifier]);

        if (!$user) {
            return ['success' => false, 'message' => 'Tài khoản không tồn tại'];
        }

        if ($user['status'] === 'pending') {
            // Phân biệt 2 trường hợp pending
            if ($user['email_verified'] == 0) {
                // Chưa xác thực OTP
                return [
                    'success' => false, 
                    'pending' => true,
                    'email' => $user['email'],
                    'role' => $user['role'],
                    'message' => 'Tài khoản chưa được xác nhận. Vui lòng kiểm tra email để lấy mã OTP.'
                ];
            } else {
                // Admin đã xác thực OTP nhưng chưa được SuperAdmin duyệt
                return [
                    'success' => false, 
                    'message' => 'Tài khoản Admin của bạn đã xác thực email thành công và đang chờ SuperAdmin phê duyệt. Vui lòng liên hệ quản trị viên để được kích hoạt.'
                ];
            }
        }

        if ($user['status'] === 'inactive') {
            return ['success' => false, 'message' => 'Tài khoản đã bị khóa'];
        }

        if ($user['status'] === 'banned') {
            return ['success' => false, 'message' => 'Tài khoản đã bị cấm'];
        }

        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Mật khẩu không đúng'];
        }

        // Cập nhật last_login
        $this->db->execute("UPDATE users SET last_login = NOW() WHERE id = ?", [$user['id']]);

        return ['success' => true, 'user' => $user];
    }

    /**
     * Gửi OTP cho forgot password - Bước 1
     */
    public function sendForgotPasswordOTP($email)
    {
        $user = $this->findByEmail($email);

        if (!$user) {
            return ['success' => false, 'message' => 'Email không tồn tại'];
        }

        // Tạo OTP mới
        $otp = generateOTP(OTP_LENGTH);
        $otpExpiry = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));

        // Lưu OTP vào database
        $sql = "UPDATE users SET otp_code = ?, otp_expiry = ? WHERE id = ?";
        $this->db->execute($sql, [$otp, $otpExpiry, $user['id']]);

        // Gửi OTP qua email cho forgot password
        require_once APP_PATH . '/helpers/mail_helper.php';
        $emailSent = sendForgotPasswordOTPEmail($email, $otp, $user['full_name']);
        
        return [
            'success' => true, 
            'otp' => $otp, 
            'email_sent' => $emailSent,
            'message' => 'Mã OTP đã được gửi đến email của bạn'
        ];
    }

    /**
     * Xác thực OTP cho forgot password - Bước 2
     */
    public function verifyForgotPasswordOTP($email, $otp)
    {
        $sql = "SELECT * FROM users WHERE email = ? AND otp_code = ? AND otp_expiry > NOW() LIMIT 1";
        $user = $this->db->fetchOne($sql, [$email, $otp]);

        if ($user) {
            // Xóa OTP sau khi xác thực thành công
            $updateSql = "UPDATE users SET otp_code = NULL, otp_expiry = NULL WHERE id = ?";
            $this->db->execute($updateSql, [$user['id']]);
            
            return ['success' => true, 'message' => 'Xác thực OTP thành công'];
        }

        return ['success' => false, 'message' => 'Mã OTP không hợp lệ hoặc đã hết hạn'];
    }

    /**
     * Quên mật khẩu - Gửi token reset (sau khi xác thực OTP) - Bước 3
     */
    public function forgotPassword($email)
    {
        $user = $this->findByEmail($email);

        if (!$user) {
            return ['success' => false, 'message' => 'Email không tồn tại'];
        }

        $resetToken = generateToken();
        $tokenExpiry = date('Y-m-d H:i:s', strtotime('+' . RESET_TOKEN_EXPIRY_HOURS . ' hours'));

        $sql = "UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?";
        $this->db->execute($sql, [$resetToken, $tokenExpiry, $user['id']]);

        // Gửi email với link reset password
        require_once APP_PATH . '/helpers/mail_helper.php';
        $emailSent = sendResetPasswordEmail($email, $resetToken, $user['full_name']);
        
        $resetLink = BASE_URL . "/user/reset-password?token={$resetToken}";
        
        return [
            'success' => true, 
            'reset_link' => $resetLink, 
            'token' => $resetToken,
            'email_sent' => $emailSent
        ];
    }

    /**
     * Reset password với token
     */
    public function resetPassword($token, $newPassword)
    {
        $sql = "SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry > NOW() LIMIT 1";
        $user = $this->db->fetchOne($sql, [$token]);

        if (!$user) {
            return ['success' => false, 'message' => 'Token không hợp lệ hoặc đã hết hạn'];
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $updateSql = "UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?";
        $this->db->execute($updateSql, [$hashedPassword, $user['id']]);

        return ['success' => true, 'message' => 'Đặt lại mật khẩu thành công'];
    }

    /**
     * Đổi mật khẩu (khi đã đăng nhập)
     */
    public function changePassword($userId, $oldPassword, $newPassword)
    {
        $user = $this->findById($userId);

        if (!$user) {
            return ['success' => false, 'message' => 'Người dùng không tồn tại'];
        }

        if (!password_verify($oldPassword, $user['password'])) {
            return ['success' => false, 'message' => 'Mật khẩu cũ không đúng'];
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $this->db->execute($sql, [$hashedPassword, $userId]);

        return ['success' => true, 'message' => 'Đổi mật khẩu thành công'];
    }

    /**
     * Cập nhật profile
     */
    public function updateProfile($userId, $data)
    {
        $fields = [];
        $params = [];
        
        if (isset($data['full_name'])) {
            $fields[] = "full_name = ?";
            $params[] = $data['full_name'];
        }
        
        if (isset($data['phone'])) {
            $fields[] = "phone = ?";
            $params[] = $data['phone'] ?: null;
        }
        
        if (isset($data['address'])) {
            $fields[] = "address = ?";
            $params[] = $data['address'] ?: null;
        }
        
        if (isset($data['avatar'])) {
            $fields[] = "avatar = ?";
            $params[] = $data['avatar'];
        }
        
        if (empty($fields)) {
            return ['success' => false];
        }
        
        $fields[] = "updated_at = NOW()";
        $params[] = $userId;
        
        $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
        $result = $this->db->execute($sql, $params);
        
        return $result ? ['success' => true] : ['success' => false];
    }

    /**
     * Duyệt admin
     */
    public function approveAdmin($adminId, $superAdminId = null)
    {
        $admin = $this->findById($adminId);
        
        if (!$admin || $admin['role'] !== 'admin') {
            return ['success' => false, 'message' => 'Admin không tồn tại'];
        }

        $sql = "UPDATE users SET status = 'active', admin_approved_by = ?, admin_approved_at = NOW() WHERE id = ? AND role = 'admin'";
        $result = $this->db->execute($sql, [$superAdminId, $adminId]);
        
        if ($result) {
            // Gửi email thông báo
            require_once APP_PATH . '/helpers/mail_helper.php';
            sendAdminApprovalEmail($admin['email'], $admin['full_name']);
            
            return ['success' => true];
        }
        
        return ['success' => false];
    }

    /**
     * Từ chối đăng ký admin
     */
    public function rejectAdmin($adminId)
    {
        $admin = $this->findById($adminId);
        
        if (!$admin || $admin['role'] !== 'admin') {
            return ['success' => false, 'message' => 'Admin không tồn tại'];
        }

        // Xóa tài khoản bị từ chối
        $sql = "DELETE FROM users WHERE id = ? AND role = 'admin' AND status = 'pending'";
        $result = $this->db->execute($sql, [$adminId]);
        
        return $result ? ['success' => true] : ['success' => false];
    }

    /**
     * Cập nhật trạng thái user
     */
    public function updateUserStatus($userId, $status)
    {
        $validStatuses = ['active', 'inactive', 'banned'];
        
        if (!in_array($status, $validStatuses)) {
            return ['success' => false, 'message' => 'Trạng thái không hợp lệ'];
        }

        $sql = "UPDATE users SET status = ? WHERE id = ?";
        $result = $this->db->execute($sql, [$status, $userId]);
        
        return $result ? ['success' => true] : ['success' => false];
    }

    /**
     * Cập nhật thông tin user
     */
    public function updateUserInfo($userId, $data)
    {
        $sql = "UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?";
        $params = [
            $data['full_name'],
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $userId
        ];
        
        $result = $this->db->execute($sql, $params);
        
        return $result ? ['success' => true] : ['success' => false];
    }

    /**
     * Xóa user (không dùng cho superadmin/admin)
     */
    public function deleteUser($userId)
    {
        $user = $this->findById($userId);
        
        if (!$user) {
            return ['success' => false, 'message' => 'User không tồn tại'];
        }

        // Chỉ không cho xóa superadmin (admin thì được xóa bởi superadmin)
        if ($user['role'] === 'superadmin') {
            return ['success' => false, 'message' => 'Không thể xóa tài khoản SuperAdmin'];
        }

        $sql = "DELETE FROM users WHERE id = ?";
        $result = $this->db->execute($sql, [$userId]);
        
        return $result ? ['success' => true] : ['success' => false];
    }

    /**
     * Lấy danh sách admin chờ duyệt (đã verify email)
     */
    public function getPendingAdmins()
    {
        $sql = "SELECT id, username, email, full_name, phone, created_at 
                FROM users 
                WHERE role = 'admin' 
                AND status = 'pending' 
                AND email_verified = 1 
                ORDER BY created_at DESC";
        return $this->db->fetchAll($sql);
    }

    /**
     * Lấy tất cả users (cho admin quản lý)
     */
    public function getAllUsers($filters = [])
    {
        $sql = "SELECT u.id, u.username, u.email, u.full_name, u.phone, u.address, 
                u.role, u.status, u.created_at, u.updated_at,
                COUNT(DISTINCT o.id) as total_orders,
                COALESCE(SUM(CASE WHEN o.order_status NOT IN ('cancelled') AND o.payment_status NOT IN ('failed', 'refunded') THEN o.total ELSE 0 END), 0) as total_spent
                FROM users u
                LEFT JOIN orders o ON u.id = o.user_id
                WHERE 1=1";
        $params = [];

        if (isset($filters['role'])) {
            $sql .= " AND u.role = ?";
            $params[] = $filters['role'];
        }

        if (isset($filters['status'])) {
            $sql .= " AND u.status = ?";
            $params[] = $filters['status'];
        }

        if (isset($filters['search']) && !empty($filters['search'])) {
            $sql .= " AND (u.username LIKE ? OR u.email LIKE ? OR u.full_name LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " GROUP BY u.id, u.username, u.email, u.full_name, u.phone, u.address, u.role, u.status, u.created_at, u.updated_at";
        
        // Sắp xếp
        if (isset($filters['sort'])) {
            switch ($filters['sort']) {
                case 'orders_desc':
                    $sql .= " ORDER BY total_orders DESC";
                    break;
                case 'orders_asc':
                    $sql .= " ORDER BY total_orders ASC";
                    break;
                case 'spent_desc':
                    $sql .= " ORDER BY total_spent DESC";
                    break;
                case 'spent_asc':
                    $sql .= " ORDER BY total_spent ASC";
                    break;
                case 'name_asc':
                    $sql .= " ORDER BY u.full_name ASC";
                    break;
                case 'name_desc':
                    $sql .= " ORDER BY u.full_name DESC";
                    break;
                case 'newest':
                    $sql .= " ORDER BY u.created_at DESC";
                    break;
                case 'oldest':
                    $sql .= " ORDER BY u.created_at ASC";
                    break;
                default:
                    $sql .= " ORDER BY u.created_at DESC";
            }
        } else {
            $sql .= " ORDER BY u.created_at DESC";
        }

        return $this->db->fetchAll($sql, $params);
    }
}