<?php
/**
 * AdminUserController
 * Xử lý các chức năng quản lý người dùng cho admin
 * - Danh sách người dùng
 * - Cập nhật trạng thái người dùng
 * - Cập nhật thông tin người dùng
 * - Xóa người dùng
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../helpers/validation.php';

class AdminUserController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = $this->model('User');
    }

    /**
     * Trang quản lý người dùng
     */
    public function manageUsers()
    {
        $this->requireAdmin();
        
        $filters = [
            'search' => $_GET['search'] ?? '',
            'sort' => $_GET['sort'] ?? ''
        ];
        
        $users = $this->userModel->getAllUsers($filters);
        $this->view('admin/manage_users', [
            'users' => $users,
            'search' => $filters['search'],
            'sort' => $filters['sort']
        ]);
    }

    /**
     * Cập nhật trạng thái người dùng (active/inactive/banned)
     */
    public function updateUserStatus()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['user_id'] ?? 0;
            $status = $_POST['status'] ?? '';
            $targetUser = $this->userModel->findById($userId);
            
            // Kiểm tra quyền
            $currentRole = Session::get('user_role');
            if ($currentRole === 'admin' && $targetUser['role'] !== 'user') {
                Session::setFlash('error', 'Admin chỉ có thể quản lý tài khoản user!');
                $this->redirect('/admin/users');
                return;
            }
            
            $result = $this->userModel->updateUserStatus($userId, $status);
            
            if ($result['success']) {
                Session::setFlash('success', 'Cập nhật trạng thái thành công!');
            } else {
                Session::setFlash('error', 'Cập nhật thất bại!');
            }
        }
        
        $this->redirect('/admin/users');
    }

    /**
     * Cập nhật thông tin user
     */
    public function updateUser()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['user_id'] ?? 0;
            $targetUser = $this->userModel->findById($userId);
            
            // Kiểm tra quyền
            $currentRole = Session::get('user_role');
            if ($currentRole === 'admin' && $targetUser['role'] !== 'user') {
                Session::setFlash('error', 'Admin chỉ có thể chỉnh sửa tài khoản user!');
                $this->redirect('/admin/users');
                return;
            }
            
            $data = [
                'full_name' => sanitize($_POST['full_name'] ?? ''),
                'phone' => sanitize($_POST['phone'] ?? ''),
                'address' => sanitize($_POST['address'] ?? '')
            ];
            
            $result = $this->userModel->updateUserInfo($userId, $data);
            
            if ($result['success']) {
                Session::setFlash('success', 'Cập nhật thông tin thành công!');
            } else {
                Session::setFlash('error', 'Cập nhật thất bại!');
            }
        }
        
        $this->redirect('/admin/users');
    }

    /**
     * Xóa user (chỉ SuperAdmin)
     */
    public function deleteUser()
    {
        $this->requireSuperAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['user_id'] ?? 0;
            $currentUserId = Session::get('user_id');
            
            // Không cho xóa chính mình
            if ($userId == $currentUserId) {
                Session::setFlash('error', 'Không thể xóa tài khoản của chính mình!');
                $this->redirect('/admin/users');
                return;
            }
            
            // Lấy thông tin user cần xóa
            $targetUser = $this->userModel->findById($userId);
            
            // Không cho xóa SuperAdmin khác
            if ($targetUser && $targetUser['role'] === 'superadmin') {
                Session::setFlash('error', 'Không thể xóa tài khoản SuperAdmin!');
                $this->redirect('/admin/users');
                return;
            }
            
            $result = $this->userModel->deleteUser($userId);
            
            if ($result['success']) {
                Session::setFlash('success', 'Xóa tài khoản thành công!');
            } else {
                Session::setFlash('error', $result['message'] ?? 'Xóa tài khoản thất bại!');
            }
        }
        
        $this->redirect('/admin/users');
    }
}
