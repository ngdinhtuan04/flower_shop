<?php
/**
 * AdminCategoryController
 * Xử lý các chức năng quản lý danh mục cho admin
 * - Danh sách danh mục
 * - Thêm danh mục
 * - Cập nhật danh mục
 * - Xóa danh mục
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Session.php';
require_once __DIR__ . '/../helpers/validation.php';

class AdminCategoryController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = $this->model('User');
    }

    /**
     * Trang danh sách danh mục
     */
    public function manageCategories()
    {
        $this->requireAdmin();
        
        $categoryModel = $this->model('Category');
        $search = $_GET['search'] ?? '';
        $sort = $_GET['sort'] ?? '';
        
        if ($search) {
            $categories = $categoryModel->search($search);
        } else {
            $categories = $categoryModel->getAll();
        }
        
        // Sắp xếp
        if ($sort && !empty($categories)) {
            switch ($sort) {
                case 'name_asc':
                    usort($categories, function($a, $b) {
                        return strcmp($a['name'], $b['name']);
                    });
                    break;
                case 'name_desc':
                    usort($categories, function($a, $b) {
                        return strcmp($b['name'], $a['name']);
                    });
                    break;
                case 'products_desc':
                    usort($categories, function($a, $b) {
                        return $b['product_count'] - $a['product_count'];
                    });
                    break;
                case 'products_asc':
                    usort($categories, function($a, $b) {
                        return $a['product_count'] - $b['product_count'];
                    });
                    break;
                case 'newest':
                    usort($categories, function($a, $b) {
                        return strtotime($b['created_at']) - strtotime($a['created_at']);
                    });
                    break;
                case 'oldest':
                    usort($categories, function($a, $b) {
                        return strtotime($a['created_at']) - strtotime($b['created_at']);
                    });
                    break;
            }
        }
        
        $this->view('admin/manage_categories', [
            'categories' => $categories,
            'search' => $search,
            'sort' => $sort
        ]);
    }

    /**
     * Thêm danh mục mới (categories/store)
     */
    public function categoriesStore()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/categories');
            return;
        }

        $categoryModel = $this->model('Category');
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
            $this->redirect('/admin/categories');
            return;
        }

        $result = $categoryModel->create($data);

        if ($result['success']) {
            Session::setFlash('success', 'Thêm danh mục thành công!');
        } else {
            Session::setFlash('error', 'Thêm danh mục thất bại!');
        }

        $this->redirect('/admin/categories');
    }

    /**
     * Cập nhật danh mục (categories/update)
     */
    public function categoriesUpdate()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/categories');
            return;
        }

        $categoryModel = $this->model('Category');
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
            $this->redirect('/admin/categories');
            return;
        }

        $result = $categoryModel->update($categoryId, $data);

        if ($result['success']) {
            Session::setFlash('success', 'Cập nhật danh mục thành công!');
        } else {
            Session::setFlash('error', 'Cập nhật danh mục thất bại!');
        }

        $this->redirect('/admin/categories');
    }

    /**
     * Xóa danh mục (categories/delete)
     */
    public function categoriesDelete()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/categories');
            return;
        }

        $categoryModel = $this->model('Category');
        $categoryId = $_POST['category_id'] ?? 0;
        $result = $categoryModel->delete($categoryId);

        if ($result['success']) {
            Session::setFlash('success', 'Xóa danh mục thành công!');
        } else {
            Session::setFlash('error', $result['message'] ?? 'Xóa danh mục thất bại!');
        }

        $this->redirect('/admin/categories');
    }
}
