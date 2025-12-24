<?php
/**
 * AdminNewsController
 * Xử lý các chức năng quản lý tin tức và bình luận cho admin
 * - Danh sách tin tức
 * - Thêm, sửa, xóa tin tức
 * - Upload ảnh cho tin tức
 * - Quản lý bình luận
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../models/News.php';

class AdminNewsController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = $this->model('User');
    }

    /**
     * Trang quản lý tin tức
     */
    public function manageNews()
    {
        $this->requireAdmin();
        
        $newsModel = $this->model('News');
        
        // Lấy tham số lọc và phân trang
        $page = intval($_GET['page'] ?? 1);
        $limit = 10;
        $filters = [
            'status' => $_GET['status'] ?? '',
            'category' => $_GET['category'] ?? '',
            'author_id' => $_GET['author_id'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        // Lấy dữ liệu tin tức
        $result = $newsModel->getAll($page, $limit, $filters);
        
        // Lấy danh sách tác giả
        $authors = $newsModel->getAuthors();

        $data = [
            'user' => $this->userModel->findById(Session::get('user_id')),
            'news' => $result['data'],
            'authors' => $authors,
            'pagination' => [
                'current_page' => $result['page'],
                'total_pages' => $result['total_pages'],
                'total' => $result['total']
            ],
            'filters' => $filters
        ];

        $this->view('admin/manage_news', $data);
    }

    /**
     * Trang quản lý bình luận tin tức
     */
    public function manageComments()
    {
        $this->requireAdmin();
        
        $commentModel = $this->model('NewsComment');
        
        // Lấy tham số lọc và phân trang
        $page = intval($_GET['page'] ?? 1);
        $limit = 20;
        $filters = [
            'status' => $_GET['status'] ?? '',
            'news_id' => $_GET['news_id'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        // Lấy dữ liệu bình luận
        $result = $commentModel->getAllForAdmin($page, $limit, $filters);
        
        // Lấy danh sách tin tức để filter
        $newsModel = $this->model('News');
        $newsResult = $newsModel->getAll(1, 100, ['status' => 'published']);

        $data = [
            'user' => $this->userModel->findById(Session::get('user_id')),
            'comments' => $result['data'],
            'newsList' => $newsResult['data'],
            'pagination' => [
                'current_page' => $result['page'],
                'total_pages' => $result['total_pages'],
                'total' => $result['total']
            ],
            'filters' => $filters,
            'stats' => $commentModel->getStats()
        ];

        $this->view('admin/manage_comments', $data);
    }

    /**
     * Ẩn bình luận với lý do
     */
    public function commentHide()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/comments');
            return;
        }

        $commentId = intval($_POST['comment_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        if (!$commentId || empty($reason)) {
            Session::setFlash('error', 'Vui lòng nhập lý do ẩn bình luận');
            $this->redirect('/admin/comments');
            return;
        }

        $commentModel = $this->model('NewsComment');
        
        if ($commentModel->hideComment($commentId, $reason, Session::get('user_id'))) {
            Session::setFlash('success', 'Đã ẩn bình luận và gửi thông báo đến người dùng');
        } else {
            Session::setFlash('error', 'Có lỗi xảy ra');
        }

        $this->redirect('/admin/comments');
    }

    /**
     * Đánh dấu spam
     */
    public function commentMarkSpam()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/comments');
            return;
        }

        $commentId = intval($_POST['comment_id'] ?? 0);

        if (!$commentId) {
            Session::setFlash('error', 'ID bình luận không hợp lệ');
            $this->redirect('/admin/comments');
            return;
        }

        $commentModel = $this->model('NewsComment');
        
        if ($commentModel->markAsSpam($commentId, Session::get('user_id'))) {
            Session::setFlash('success', 'Đã đánh dấu spam');
        } else {
            Session::setFlash('error', 'Có lỗi xảy ra');
        }

        $this->redirect('/admin/comments');
    }

    /**
     * Xóa bình luận
     */
    public function commentDelete()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/comments');
            return;
        }

        $commentId = intval($_POST['comment_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        if (!$commentId || empty($reason)) {
            Session::setFlash('error', 'Vui lòng nhập lý do xóa bình luận');
            $this->redirect('/admin/comments');
            return;
        }

        $commentModel = $this->model('NewsComment');
        
        if ($commentModel->deleteWithReason($commentId, $reason, Session::get('user_id'))) {
            Session::setFlash('success', 'Đã xóa bình luận và gửi thông báo đến người dùng');
        } else {
            Session::setFlash('error', 'Có lỗi xảy ra');
        }

        $this->redirect('/admin/comments');
    }

    /**
     * Thêm tin tức mới
     */
    public function newsStore()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/news');
            return;
        }

        $newsModel = $this->model('News');

        // Lấy dữ liệu từ form
        $title = sanitize($_POST['title'] ?? '');
        $excerpt = sanitize($_POST['excerpt'] ?? '');
        $content = $_POST['content'] ?? ''; // Không sanitize content vì có thể chứa HTML
        $category = sanitize($_POST['category'] ?? '');
        $status = sanitize($_POST['status'] ?? 'draft');
        $metaTitle = sanitize($_POST['meta_title'] ?? '');
        $metaDescription = sanitize($_POST['meta_description'] ?? '');

        // Validation
        $validator = validate([
            'title' => $title,
            'content' => $content
        ]);

        $validator->required('title', 'Tiêu đề không được để trống')
                  ->minLength('title', 5, 'Tiêu đề phải có ít nhất 5 ký tự')
                  ->required('content', 'Nội dung không được để trống');

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            $this->redirect('/admin/news');
            return;
        }

        // Tạo slug từ tiêu đề
        $slug = News::createSlug($title);

        // Xử lý upload ảnh
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = UPLOAD_PATH . '/news/';
            
            // Tạo thư mục nếu chưa tồn tại
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $imagePath = 'uploads/news/' . $fileName;
            }
        }

        // Auto publish nếu status là published
        $publishedAt = null;
        if ($status === 'published') {
            $publishedAt = date('Y-m-d H:i:s');
        }

        // Tạo tin tức
        $data = [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'content' => $content,
            'image' => $imagePath,
            'author_id' => Session::get('user_id'),
            'category' => $category,
            'status' => $status,
            'published_at' => $publishedAt,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription
        ];

        if ($newsModel->create($data)) {
            Session::setFlash('success', 'Thêm tin tức thành công!');
        } else {
            Session::setFlash('error', 'Thêm tin tức thất bại!');
        }

        $this->redirect('/admin/news');
    }

    /**
     * Cập nhật tin tức
     */
    public function newsUpdate()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/news');
            return;
        }

        $newsModel = $this->model('News');
        $newsId = intval($_POST['news_id'] ?? 0);

        // Kiểm tra tin tức tồn tại
        $news = $newsModel->getById($newsId);
        if (!$news) {
            Session::setFlash('error', 'Tin tức không tồn tại!');
            $this->redirect('/admin/news');
            return;
        }

        // Lấy dữ liệu từ form
        $title = sanitize($_POST['title'] ?? '');
        $excerpt = sanitize($_POST['excerpt'] ?? '');
        $content = $_POST['content'] ?? '';
        $category = sanitize($_POST['category'] ?? '');
        $status = sanitize($_POST['status'] ?? 'draft');
        $metaTitle = sanitize($_POST['meta_title'] ?? '');
        $metaDescription = sanitize($_POST['meta_description'] ?? '');

        // Validation
        $validator = validate([
            'title' => $title,
            'content' => $content
        ]);

        $validator->required('title', 'Tiêu đề không được để trống')
                  ->minLength('title', 5, 'Tiêu đề phải có ít nhất 5 ký tự')
                  ->required('content', 'Nội dung không được để trống');

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            $this->redirect('/admin/news');
            return;
        }

        // Tạo slug mới từ tiêu đề
        $slug = News::createSlug($title);

        // Xử lý upload ảnh mới
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = UPLOAD_PATH . '/news/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                // Xóa ảnh cũ nếu có
                if (!empty($news['image'])) {
                    $oldImagePath = ROOT_PATH . '/public/' . $news['image'];
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $imagePath = 'uploads/news/' . $fileName;
            }
        }

        // Auto publish nếu chuyển sang published
        $publishedAt = $news['published_at'];
        if ($status === 'published' && empty($publishedAt)) {
            $publishedAt = date('Y-m-d H:i:s');
        }

        // Cập nhật tin tức
        $data = [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'content' => $content,
            'category' => $category,
            'status' => $status,
            'published_at' => $publishedAt,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription
        ];

        // Chỉ thêm image nếu có upload mới
        if ($imagePath) {
            $data['image'] = $imagePath;
        }

        if ($newsModel->update($newsId, $data)) {
            Session::setFlash('success', 'Cập nhật tin tức thành công!');
        } else {
            Session::setFlash('error', 'Cập nhật tin tức thất bại!');
        }

        $this->redirect('/admin/news');
    }

    /**
     * Xóa tin tức
     */
    public function newsDelete()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/news');
            return;
        }

        $newsModel = $this->model('News');
        $newsId = intval($_POST['news_id'] ?? 0);

        if ($newsModel->delete($newsId)) {
            Session::setFlash('success', 'Xóa tin tức thành công!');
        } else {
            Session::setFlash('error', 'Xóa tin tức thất bại!');
        }

        $this->redirect('/admin/news');
    }

    /**
     * Upload ảnh cho CKEditor trong nội dung tin tức
     */
    public function newsUploadImage()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendCKEditorError('Invalid request method');
            return;
        }

        // CKEditor gửi file qua key 'upload'
        $fileKey = isset($_FILES['upload']) ? 'upload' : (isset($_FILES['file']) ? 'file' : null);
        
        if (!$fileKey || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
            $this->sendCKEditorError('No file uploaded or upload error');
            return;
        }

        // Kiểm tra loại file
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = $_FILES[$fileKey]['type'];
        
        if (!in_array($fileType, $allowedTypes)) {
            $this->sendCKEditorError('Invalid file type. Only JPG, PNG, GIF, WEBP allowed.');
            return;
        }

        $uploadDir = UPLOAD_PATH . '/news/';
        
        // Tạo thư mục nếu chưa tồn tại
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Tạo tên file an toàn
        $extension = pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION);
        $fileName = 'news_' . time() . '_' . uniqid() . '.' . $extension;
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $targetPath)) {
            $url = BASE_URL . '/uploads/news/' . $fileName;
            
            // Kiểm tra xem có phải request từ CKEditor file browser không
            $funcNum = $_GET['CKEditorFuncNum'] ?? null;
            
            if ($funcNum !== null) {
                // Response cho CKEditor file browser dialog (tab Upload)
                echo "<script type='text/javascript'>
                    window.parent.CKEDITOR.tools.callFunction({$funcNum}, '{$url}', '');
                </script>";
            } else {
                // Response JSON cho drag & drop hoặc uploadimage plugin
                header('Content-Type: application/json');
                echo json_encode([
                    'uploaded' => 1,
                    'fileName' => $fileName,
                    'url' => $url
                ]);
            }
        } else {
            $this->sendCKEditorError('Failed to move uploaded file');
        }
        exit;
    }
    
    /**
     * Gửi lỗi cho CKEditor
     */
    private function sendCKEditorError($message)
    {
        $funcNum = $_GET['CKEditorFuncNum'] ?? null;
        
        if ($funcNum !== null) {
            echo "<script type='text/javascript'>
                window.parent.CKEDITOR.tools.callFunction({$funcNum}, '', '{$message}');
            </script>";
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'uploaded' => 0,
                'error' => ['message' => $message]
            ]);
        }
        exit;
    }
}
