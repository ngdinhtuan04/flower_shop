<?php
/**
 * Base Controller Class
 */
class Controller
{
    protected function view($view, $data = [])
    {
        extract($data);
        $viewFile = APP_PATH . '/views/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("View not found: {$view}");
        }
    }

    protected function model($model)
    {
        $modelFile = APP_PATH . '/models/' . $model . '.php';
        
        if (file_exists($modelFile)) {
            require_once $modelFile;
            return new $model();
        } else {
            die("Model not found: {$model}");
        }
    }

    protected function redirect($url)
    {
        if (!headers_sent()) {
            header("Location: " . BASE_URL . '/' . ltrim($url, '/'));
            exit();
        }
    }

    protected function json($data, $statusCode = 200)
    {
        // Clear any output buffer to prevent HTML/whitespace before JSON
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }

    protected function requireAuth()
    {
        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'Vui lòng đăng nhập để tiếp tục');
            $this->redirect('/user/login');
        }
    }

    protected function requireAdmin()
    {
        if (!Session::isLoggedIn() || !Session::isAdmin()) {
            Session::setFlash('error', 'Bạn không có quyền truy cập');
            $this->redirect('/admin/login');
        }
    }

    protected function requireSuperAdmin()
    {
        if (!Session::isLoggedIn() || !Session::isSuperAdmin()) {
            Session::setFlash('error', 'Chỉ SuperAdmin mới có quyền truy cập');
            $this->redirect('/admin/login');
        }
    }
}