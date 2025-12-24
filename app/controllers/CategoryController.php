<?php
/**
 * CategoryController - Quản lý danh mục (Admin)
 */
class CategoryController extends Controller
{
    private $categoryModel;

    public function __construct()
    {
        $this->categoryModel = $this->model('Category');
    }

    /**
     * Trang danh sách danh mục
     */
    public function manageCategories()
    {
        $this->requireAdmin();
        
        $search = $_GET['search'] ?? '';
        
        if ($search) {
            $categories = $this->categoryModel->search($search);
        } else {
            $categories = $this->categoryModel->getAll();
        }
        
        $this->view('admin/manage_categories', [
            'categories' => $categories,
            'search' => $search
        ]);
    }

    /**
     * Thêm danh mục mới
     */
    public function store()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/manage-categories');
            return;
        }

        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'description' => sanitize($_POST['description'] ?? '')
        ];

        // Validation
        $validator = validate($data);
        $validator->required('name', 'Tên danh mục không được để trống')
                  ->minLength('name', 2, 'Tên danh mục phải có ít nhất 2 ký tự');

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            $this->redirect('/admin/manage-categories');
            return;
        }

        $result = $this->categoryModel->create($data);

        if ($result['success']) {
            Session::setFlash('success', 'Thêm danh mục thành công!');
        } else {
            Session::setFlash('error', 'Thêm danh mục thất bại!');
        }

        $this->redirect('/admin/manage-categories');
    }

    /**
     * Cập nhật danh mục
     */
    public function update()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/manage-categories');
            return;
        }

        $categoryId = $_POST['category_id'] ?? 0;
        $data = [
            'name' => sanitize($_POST['name'] ?? ''),
            'description' => sanitize($_POST['description'] ?? '')
        ];

        // Validation
        $validator = validate($data);
        $validator->required('name', 'Tên danh mục không được để trống')
                  ->minLength('name', 2, 'Tên danh mục phải có ít nhất 2 ký tự');

        if ($validator->fails()) {
            Session::setFlash('error', $validator->firstError());
            $this->redirect('/admin/manage-categories');
            return;
        }

        $result = $this->categoryModel->update($categoryId, $data);

        if ($result['success']) {
            Session::setFlash('success', 'Cập nhật danh mục thành công!');
        } else {
            Session::setFlash('error', 'Cập nhật danh mục thất bại!');
        }

        $this->redirect('/admin/manage-categories');
    }

    /**
     * Xóa danh mục
     */
    public function delete()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/manage-categories');
            return;
        }

        $categoryId = $_POST['category_id'] ?? 0;
        $result = $this->categoryModel->delete($categoryId);

        if ($result['success']) {
            Session::setFlash('success', 'Xóa danh mục thành công!');
        } else {
            Session::setFlash('error', $result['message'] ?? 'Xóa danh mục thất bại!');
        }

        $this->redirect('/admin/manage-categories');
    }
}