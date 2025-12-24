<?php
/**
 * File khởi động chính của website
 * Entry point - Nạp tất cả config, core, helpers và khởi chạy ứng dụng
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Require config
require_once __DIR__ . '/../app/config/config.php';

// Require core classes
require_once APP_PATH . '/core/DB.php';
require_once APP_PATH . '/core/Session.php';
require_once APP_PATH . '/core/Controller.php';

// Require helpers
require_once APP_PATH . '/helpers/validation.php';

// Khởi tạo session
Session::start();

// Load routes
$webRoutes = require_once APP_PATH . '/routers/web.php';
$adminRoutes = require_once APP_PATH . '/routers/admin.php';

// Simple Router
$request = $_SERVER['REQUEST_URI'];
// Remove base path và query string
$request = str_replace('/flower_shop/public', '', $request);
$request = parse_url($request, PHP_URL_PATH);
$request = trim($request, '/');

// Xử lý các trường hợp đặc biệt - redirect về home
if (empty($request) || $request === 'index' || $request === 'index.php') {
    $request = 'home/index';
}

// Parse URL: controller/method/param
$parts = explode('/', $request);

// Check if this is a special route
if (isset($webRoutes['special'][$parts[0]])) {
    $controllerBase = $webRoutes['special'][$parts[0]][0];
    $controllerName = $controllerBase . 'Controller';
    $method = $webRoutes['special'][$parts[0]][1];
    $params = array_slice($parts, 1);
} else {
    // Special handling for admin routes
    if ($parts[0] === 'admin') {
        // Nếu chỉ /admin thì redirect về dashboard
        if (!isset($parts[1]) || empty($parts[1])) {
            header('Location: ' . BASE_URL . '/admin/dashboard');
            exit;
        }
        
        $subRoute = $parts[1]; // orders, feedback, login, reviews, etc
        $action = isset($parts[2]) ? $parts[2] : 'index';
        
        // Check if this route exists and has a custom controller
        if (!isset($adminRoutes[$subRoute])) {
            // Route không tồn tại - redirect về dashboard
            header('Location: ' . BASE_URL . '/admin/dashboard');
            exit;
        }
        
        // Lấy controller name từ routes (bắt buộc phải có)
        if (isset($adminRoutes[$subRoute]['controller'])) {
            $controllerName = $adminRoutes[$subRoute]['controller'] . 'Controller';
        } else {
            // Fallback cho các routes chưa được cập nhật
            die("Route '{$subRoute}' không có controller được định nghĩa trong admin.php");
        }
        
        if (isset($adminRoutes[$subRoute][$action])) {
            $method = $adminRoutes[$subRoute][$action];
            $params = array_slice($parts, 3);
        } elseif (isset($adminRoutes[$subRoute]['index'])) {
            $method = $adminRoutes[$subRoute]['index'];
            $params = array_slice($parts, 2);
        } else {
            // Convert action to camelCase
            $method = lcfirst(str_replace('-', '', ucwords($action, '-')));
            $params = array_slice($parts, 3);
        }
    } else {
        // Map routes to controllers
        $controllerKey = strtolower($parts[0]);
        $controllerBase = isset($webRoutes['map'][$controllerKey]) ? $webRoutes['map'][$controllerKey] : ucfirst($parts[0]);
        $controllerName = $controllerBase . 'Controller';
        $methodRaw = isset($parts[1]) && !empty($parts[1]) ? $parts[1] : 'index';

        // Convert kebab-case to camelCase
        // Special cases: otp -> OTP, id -> ID
        $method = str_replace(['-otp', '-id'], ['OTP', 'ID'], $methodRaw);
        $method = lcfirst(str_replace('-', '', ucwords($method, '-')));
        // Fix special cases that got lowercased
        $method = str_replace(['Otp', 'Id'], ['OTP', 'ID'], $method);

        $params = array_slice($parts, 2);
    }
}

// Autoload models
spl_autoload_register(function($className) {
    $modelFile = APP_PATH . '/models/' . $className . '.php';
    if (file_exists($modelFile)) {
        require_once $modelFile;
    }
});

// Load controller
$controllerFile = APP_PATH . '/controllers/' . $controllerName . '.php';

if (file_exists($controllerFile)) {
    require_once $controllerFile;
    
    // Load required models based on controller
    $modelName = str_replace('Controller', '', $controllerName);
    $modelFile = APP_PATH . '/models/' . $modelName . '.php';
    if (file_exists($modelFile)) {
        require_once $modelFile;
    }
    
    $controller = new $controllerName();
    
    if (method_exists($controller, $method)) {
        call_user_func_array([$controller, $method], $params);
    } else {
        die("Method not found: {$method}");
    }
} else {
    die("Controller not found: {$controllerName}");
}
