<?php
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/models/Slider.php';

class SliderController extends Controller {
    private $sliderModel;
    
    public function __construct() {
        $this->sliderModel = new Slider();
    }
    
    /**
     * Danh sách slider
     */
    public function index() {
        // Check admin authentication
        if (!Session::isAdmin()) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }
        
        $filters = [
            'search' => $_GET['search'] ?? '',
            'is_active' => $_GET['status'] ?? ''
        ];
        
        $sliders = $this->sliderModel->getAll($filters);
        $stats = $this->sliderModel->getStatistics();
        $user = Session::getUser();
        
        $this->view('admin/manage_sliders', [
            'sliders' => $sliders,
            'stats' => $stats,
            'filters' => $filters,
            'user' => $user
        ]);
    }
    
    /**
     * Form tạo slider mới
     */
    public function create() {
        if (!Session::isAdmin()) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];
            
            // Validate
            if (empty($_POST['title'])) {
                $errors[] = 'Tiêu đề không được để trống';
            }
            
            // Handle image upload
            $imagePath = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->uploadImage($_FILES['image']);
                if ($uploadResult['success']) {
                    $imagePath = $uploadResult['path'];
                } else {
                    $errors[] = $uploadResult['message'];
                }
            } else {
                $errors[] = 'Vui lòng chọn ảnh slider';
            }
            
            if (empty($errors)) {
                $data = [
                    'title' => trim($_POST['title']),
                    'description' => trim($_POST['description'] ?? ''),
                    'image' => $imagePath,
                    'link' => trim($_POST['link'] ?? ''),
                    'button_text' => trim($_POST['button_text'] ?? ''),
                    'display_order' => intval($_POST['display_order'] ?? 0),
                    'is_active' => isset($_POST['is_active']) ? 1 : 0
                ];
                
                if ($this->sliderModel->create($data)) {
                    Session::setFlash('success', 'Tạo slider thành công');
                    header('Location: ' . BASE_URL . '/sliders');
                    exit;
                } else {
                    $errors[] = 'Có lỗi xảy ra khi tạo slider';
                }
            }
            
            Session::setFlash('error', implode('<br>', $errors));
        }
        
        $this->view('admin/slider_form', [
            'slider' => null
        ]);
    }
    
    /**
     * Form sửa slider
     */
    public function edit($id) {
        if (!Session::isAdmin()) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }
        
        $slider = $this->sliderModel->getById($id);
        
        if (!$slider) {
            Session::setFlash('error', 'Slider không tồn tại');
            header('Location: ' . BASE_URL . '/sliders');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];
            
            if (empty($_POST['title'])) {
                $errors[] = 'Tiêu đề không được để trống';
            }
            
            // Handle image upload (optional for edit)
            $imagePath = $slider['image']; // Keep old image by default
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->uploadImage($_FILES['image']);
                if ($uploadResult['success']) {
                    // Delete old image
                    $oldImagePath = PUBLIC_PATH . '/' . $slider['image'];
                    if (file_exists($oldImagePath)) {
                        @unlink($oldImagePath);
                    }
                    $imagePath = $uploadResult['path'];
                } else {
                    $errors[] = $uploadResult['message'];
                }
            }
            
            if (empty($errors)) {
                $data = [
                    'title' => trim($_POST['title']),
                    'description' => trim($_POST['description'] ?? ''),
                    'image' => $imagePath,
                    'link' => trim($_POST['link'] ?? ''),
                    'button_text' => trim($_POST['button_text'] ?? ''),
                    'display_order' => intval($_POST['display_order'] ?? 0),
                    'is_active' => isset($_POST['is_active']) ? 1 : 0
                ];
                
                if ($this->sliderModel->update($id, $data)) {
                    Session::setFlash('success', 'Cập nhật slider thành công');
                    header('Location: ' . BASE_URL . '/sliders');
                    exit;
                } else {
                    $errors[] = 'Có lỗi xảy ra khi cập nhật slider';
                }
            }
            
            Session::setFlash('error', implode('<br>', $errors));
        }
        
        $this->view('admin/slider_form', [
            'slider' => $slider
        ]);
    }
    
    /**
     * Xóa slider
     */
    public function delete($id) {
        if (!Session::isAdmin()) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }
        
        if ($this->sliderModel->delete($id)) {
            Session::setFlash('success', 'Xóa slider thành công');
        } else {
            Session::setFlash('error', 'Có lỗi xảy ra khi xóa slider');
        }
        
        header('Location: ' . BASE_URL . '/sliders');
        exit;
    }
    
    /**
     * Bật/tắt trạng thái slider
     */
    public function toggleActive($id) {
        if (!Session::isAdmin()) {
            header('Location: ' . BASE_URL . '/admin/login');
            exit;
        }
        
        if ($this->sliderModel->toggleActive($id)) {
            Session::setFlash('success', 'Cập nhật trạng thái thành công');
        } else {
            Session::setFlash('error', 'Có lỗi xảy ra');
        }
        
        header('Location: ' . BASE_URL . '/sliders');
        exit;
    }
    
    /**
     * Cập nhật thứ tự slider (AJAX)
     */
    public function updateOrder() {
        if (!Session::isAdmin()) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
        
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }
        
        $orderData = json_decode(file_get_contents('php://input'), true);
        
        if (!$orderData || !is_array($orderData)) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            exit;
        }
        
        if ($this->sliderModel->updateOrderBatch($orderData)) {
            echo json_encode(['success' => true, 'message' => 'Cập nhật thứ tự thành công']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
        exit;
    }
    
    /**
     * Upload ảnh slider
     */
    private function uploadImage($file) {
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        // Validate file type
        if (!in_array($file['type'], $allowedTypes)) {
            return [
                'success' => false,
                'message' => 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WEBP)'
            ];
        }
        
        // Validate file size
        if ($file['size'] > $maxSize) {
            return [
                'success' => false,
                'message' => 'Kích thước file không được vượt quá 5MB'
            ];
        }
        
        // Create upload directory
        $uploadDir = PUBLIC_PATH . '/uploads/sliders';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'slider_' . time() . '_' . uniqid() . '.' . $extension;
        $filepath = $uploadDir . '/' . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'success' => true,
                'path' => 'uploads/sliders/' . $filename
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Không thể upload file'
        ];
    }
}
