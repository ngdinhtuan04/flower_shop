<?php
/**
 * AdminRevenueController
 * Xử lý các chức năng báo cáo doanh thu cho admin
 * - Xem báo cáo doanh thu chi tiết
 * - Báo cáo lợi nhuận (với giá nhập từ NCC)
 * - Thống kê khách hàng chi tiết
 * - Báo cáo tồn kho
 * - KPI với % so sánh
 * - Biểu đồ doanh thu
 * - Doanh thu theo danh mục
 * - Top sản phẩm / khách hàng
 * - Thanh toán / đơn hủy
 * - Xuất báo cáo CSV
 */

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Session.php';

class AdminRevenueController extends Controller
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = $this->model('User');
    }
    
    /**
     * Báo cáo doanh thu - Full Details
     */
    public function revenue()
    {
        $this->requireAdmin();
        
        $orderModel = $this->model('Order');
        $productModel = $this->model('Product');
        $categoryModel = $this->model('Category');
        $db = \DB::getInstance();
        
        // Lấy filters từ request
        $filters = [
            'period' => $_GET['period'] ?? 'day',
            'from_date' => $_GET['from_date'] ?? date('Y-m-01'),
            'to_date' => $_GET['to_date'] ?? date('Y-m-d'),
            'status' => $_GET['status'] ?? 'paid',
            'category_id' => $_GET['category_id'] ?? '',
            'payment_method' => $_GET['payment_method'] ?? ''
        ];
        
        // Lấy dữ liệu doanh thu theo khoảng thời gian
        $revenueData = $orderModel->getRevenueByPeriod($filters);
        
        // ============ TÍNH TỔNG THỐNG KÊ ============
        $stats = [
            'total_revenue' => $orderModel->getTotalRevenue($filters),
            'total_orders' => $orderModel->countOrders($filters),
            'avg_order_value' => 0,
            'total_products_sold' => $orderModel->getTotalProductsSold($filters)
        ];
        
        if ($stats['total_orders'] > 0) {
            $stats['avg_order_value'] = $stats['total_revenue'] / $stats['total_orders'];
        }
        
        // ============ BÁO CÁO LỢI NHUẬN ============
        $profitStats = $orderModel->getProfitStats($filters);
        $stats['total_cost'] = $profitStats['total_cost'];
        $stats['total_profit'] = $profitStats['total_profit'];
        $stats['profit_margin'] = $profitStats['profit_margin'];
        
        // Lợi nhuận theo thời gian (cho chart)
        $profitTrend = $orderModel->getProfitTrend($filters);
        
        // Top sản phẩm theo lợi nhuận
        $profitByProduct = $orderModel->getProfitByProduct($filters, 10);
        
        // Lợi nhuận theo NCC
        $profitBySupplier = $orderModel->getProfitBySupplier($filters, 10);
        
        // ============ SO SÁNH KỲ TRƯỚC ============
        $previousPeriod = $this->getPreviousPeriod($filters);
        $previousRevenue = $orderModel->getTotalRevenue($previousPeriod);
        $previousOrders = $orderModel->countOrders($previousPeriod);
        $previousProductsSold = $orderModel->getTotalProductsSold($previousPeriod);
        $previousProfitStats = $orderModel->getProfitStats($previousPeriod);
        
        // Tính % thay đổi
        $stats['revenue_change'] = $previousRevenue > 0 ? (($stats['total_revenue'] - $previousRevenue) / $previousRevenue) * 100 : ($stats['total_revenue'] > 0 ? 100 : 0);
        $stats['orders_change'] = $previousOrders > 0 ? (($stats['total_orders'] - $previousOrders) / $previousOrders) * 100 : ($stats['total_orders'] > 0 ? 100 : 0);
        $stats['products_change'] = $previousProductsSold > 0 ? (($stats['total_products_sold'] - $previousProductsSold) / $previousProductsSold) * 100 : ($stats['total_products_sold'] > 0 ? 100 : 0);
        $stats['profit_change'] = $previousProfitStats['total_profit'] > 0 ? (($stats['total_profit'] - $previousProfitStats['total_profit']) / $previousProfitStats['total_profit']) * 100 : ($stats['total_profit'] > 0 ? 100 : 0);
        
        // Giá trị trung bình kỳ trước
        $previousAvg = $previousOrders > 0 ? $previousRevenue / $previousOrders : 0;
        $stats['avg_change'] = $previousAvg > 0 ? (($stats['avg_order_value'] - $previousAvg) / $previousAvg) * 100 : 0;
        
        // ============ THỐNG KÊ THANH TOÁN ============
        $paymentStats = $db->fetchAll("
            SELECT 
                payment_method,
                COUNT(*) as order_count,
                COALESCE(SUM(total), 0) as total_revenue
            FROM orders 
            WHERE order_status IN ('delivered', 'shipping', 'processing', 'confirmed')
                AND DATE(created_at) BETWEEN ? AND ?
            GROUP BY payment_method
            ORDER BY total_revenue DESC
        ", [$filters['from_date'], $filters['to_date']]);
        
        // ============ THỐNG KÊ ĐƠN HỦY ============
        $cancelledStats = $db->fetchOne("
            SELECT 
                COUNT(*) as cancelled_orders,
                COALESCE(SUM(total), 0) as cancelled_value
            FROM orders 
            WHERE order_status = 'cancelled'
                AND DATE(created_at) BETWEEN ? AND ?
        ", [$filters['from_date'], $filters['to_date']]);
        
        // Tỷ lệ hủy đơn
        $totalOrdersInPeriod = $db->fetchOne("
            SELECT COUNT(*) as total FROM orders 
            WHERE DATE(created_at) BETWEEN ? AND ?
        ", [$filters['from_date'], $filters['to_date']])['total'] ?? 0;
        
        $cancelledRate = $totalOrdersInPeriod > 0 ? ($cancelledStats['cancelled_orders'] / $totalOrdersInPeriod) * 100 : 0;
        
        // ============ DOANH THU THEO DANH MỤC ============
        $categoryRevenue = $db->fetchAll("
            SELECT 
                c.id,
                c.name as category_name,
                COUNT(DISTINCT o.id) as order_count,
                COALESCE(SUM(oi.quantity), 0) as total_sold,
                COALESCE(SUM(oi.quantity * oi.price), 0) as total_revenue
            FROM categories c
            LEFT JOIN products p ON c.id = p.category_id
            LEFT JOIN order_items oi ON p.id = oi.product_id
            LEFT JOIN orders o ON oi.order_id = o.id 
                AND o.order_status IN ('delivered', 'shipping', 'processing', 'confirmed')
                AND DATE(o.created_at) BETWEEN ? AND ?
            GROUP BY c.id, c.name
            HAVING total_revenue > 0
            ORDER BY total_revenue DESC
        ", [$filters['from_date'], $filters['to_date']]);
        
        // Tính % cho mỗi danh mục
        $totalCategoryRevenue = array_sum(array_column($categoryRevenue, 'total_revenue'));
        foreach ($categoryRevenue as &$cat) {
            $cat['percentage'] = $totalCategoryRevenue > 0 ? round(($cat['total_revenue'] / $totalCategoryRevenue) * 100, 1) : 0;
        }
        
        // ============ TOP SẢN PHẨM BÁN CHẠY ============
        $topProducts = $orderModel->getTopSellingProducts($filters, 10);
        
        // ============ TOP KHÁCH HÀNG ============
        $topCustomers = $orderModel->getTopCustomers($filters, 10);
        
        // ============ LẤY DANH MỤC CHO FILTER ============
        $categories = $categoryModel->getAll();
        
        $data = [
            'user' => Session::getUser(),
            'revenueData' => $revenueData,
            'stats' => $stats,
            'profitTrend' => $profitTrend,
            'profitByProduct' => $profitByProduct,
            'profitBySupplier' => $profitBySupplier,
            'paymentStats' => $paymentStats,
            'cancelledStats' => [
                'cancelled_orders' => $cancelledStats['cancelled_orders'] ?? 0,
                'cancelled_value' => $cancelledStats['cancelled_value'] ?? 0,
                'cancelled_rate' => round($cancelledRate, 1)
            ],
            'categoryRevenue' => $categoryRevenue,
            'topProducts' => $topProducts,
            'topCustomers' => $topCustomers,
            'categories' => $categories,
            'filters' => $filters
        ];
        
        $this->view('admin/revenue/index', $data);
    }
    
    /**
     * Lấy kỳ trước để so sánh tăng trưởng
     */
    private function getPreviousPeriod($filters) {
        $from = new DateTime($filters['from_date']);
        $to = new DateTime($filters['to_date']);
        $diff = $from->diff($to)->days;
        
        $previousFrom = clone $from;
        $previousFrom->modify('-' . ($diff + 1) . ' days');
        
        $previousTo = clone $to;
        $previousTo->modify('-' . ($diff + 1) . ' days');
        
        return [
            'from_date' => $previousFrom->format('Y-m-d'),
            'to_date' => $previousTo->format('Y-m-d'),
            'status' => $filters['status']
        ];
    }
    
    /**
     * Export báo cáo doanh thu sang CSV
     */
    public function exportRevenue()
    {
        $this->requireAdmin();
        
        $orderModel = $this->model('Order');
        
        $filters = [
            'period' => $_GET['period'] ?? 'day',
            'from_date' => $_GET['from_date'] ?? date('Y-m-01'),
            'to_date' => $_GET['to_date'] ?? date('Y-m-d'),
            'status' => $_GET['status'] ?? 'paid'
        ];
        
        // Lấy dữ liệu với lợi nhuận
        $profitByProduct = $orderModel->getProfitByProduct($filters, 1000);
        
        // Set headers cho CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=bao_cao_loi_nhuan_' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        
        // BOM cho UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Headers
        fputcsv($output, ['Sản phẩm', 'Danh mục', 'Số lượng bán', 'Giá nhập', 'Doanh thu', 'Chi phí', 'Lợi nhuận', '% Margin']);
        
        // Data
        foreach ($profitByProduct as $row) {
            fputcsv($output, [
                $row['product_name'],
                $row['category_name'] ?? 'N/A',
                $row['total_sold'],
                number_format($row['import_price'], 0, ',', '.'),
                number_format($row['total_revenue'], 0, ',', '.'),
                number_format($row['total_cost'], 0, ',', '.'),
                number_format($row['profit'], 0, ',', '.'),
                $row['margin'] . '%'
            ]);
        }
        
        fclose($output);
        exit;
    }

    // ==================== THỐNG KÊ KHÁCH HÀNG ====================
    
    /**
     * Trang thống kê khách hàng chi tiết
     */
    public function customers()
    {
        $this->requireAdmin();
        
        $orderModel = $this->model('Order');
        
        $filters = [
            'from_date' => $_GET['from_date'] ?? date('Y-01-01'),
            'to_date' => $_GET['to_date'] ?? date('Y-m-d'),
            'search' => $_GET['search'] ?? '',
            'sort' => $_GET['sort'] ?? 'total_spent',
            'order' => $_GET['order'] ?? 'DESC'
        ];
        
        // Thống kê tổng quan
        $customerStats = $orderModel->getCustomerStats($filters);
        
        // Danh sách khách hàng chi tiết
        $customers = $orderModel->getCustomerDetails($filters, 100);
        
        // Top khách hàng
        $topCustomers = $orderModel->getTopCustomersDetailed($filters, 10);
        
        $data = [
            'user' => Session::getUser(),
            'customerStats' => $customerStats,
            'customers' => $customers,
            'topCustomers' => $topCustomers,
            'filters' => $filters
        ];
        
        $this->view('admin/revenue/customers', $data);
    }

    /**
     * API: Chi tiết khách hàng
     */
    public function customerDetail()
    {
        $this->requireAdmin();
        header('Content-Type: application/json');
        
        $userId = $_GET['id'] ?? null;
        if (!$userId) {
            echo json_encode(['success' => false, 'message' => 'User ID không hợp lệ']);
            exit;
        }
        
        $orderModel = $this->model('Order');
        $userModel = $this->model('User');
        
        $user = $userModel->getUserById($userId);
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy khách hàng']);
            exit;
        }
        
        $orderHistory = $orderModel->getCustomerOrderHistory($userId, 20);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'user' => $user,
                'orders' => $orderHistory
            ]
        ]);
        exit;
    }

    /**
     * Export thống kê khách hàng
     */
    public function exportCustomers()
    {
        $this->requireAdmin();
        
        $orderModel = $this->model('Order');
        
        $filters = [
            'from_date' => $_GET['from_date'] ?? date('Y-01-01'),
            'to_date' => $_GET['to_date'] ?? date('Y-m-d'),
            'sort' => 'total_spent',
            'order' => 'DESC'
        ];
        
        $customers = $orderModel->getCustomerDetails($filters, 1000);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=thong_ke_khach_hang_' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, ['Họ tên', 'Email', 'SĐT', 'Ngày đăng ký', 'Số đơn hàng', 'Tổng chi tiêu', 'TB/đơn', 'Lần mua cuối', 'Ngày không mua']);
        
        foreach ($customers as $c) {
            fputcsv($output, [
                $c['full_name'],
                $c['email'],
                $c['phone'],
                $c['registered_at'],
                $c['total_orders'],
                number_format($c['total_spent'], 0, ',', '.'),
                number_format($c['avg_order_value'], 0, ',', '.'),
                $c['last_order_date'] ?? 'Chưa mua',
                $c['days_since_last_order'] ?? 'N/A'
            ]);
        }
        
        fclose($output);
        exit;
    }

    // ==================== BÁO CÁO TỒN KHO ====================
    
    /**
     * Trang báo cáo tồn kho
     */
    public function inventory()
    {
        $this->requireAdmin();
        
        $orderModel = $this->model('Order');
        
        $tab = $_GET['tab'] ?? 'overview';
        $threshold = intval($_GET['threshold'] ?? 10);
        
        // Thống kê tổng quan
        $inventoryStats = $orderModel->getInventoryStats();
        
        // Sản phẩm theo tab
        switch ($tab) {
            case 'low':
                $products = $orderModel->getLowStockProducts($threshold, 100);
                break;
            case 'out':
                $products = $orderModel->getOutOfStockProducts(100);
                break;
            case 'high':
                $products = $orderModel->getHighStockProducts(100, 100);
                break;
            default:
                $products = [];
        }
        
        // Tồn kho theo danh mục
        $inventoryByCategory = $orderModel->getInventoryByCategory();
        
        // Lịch sử nhập/xuất (chỉ hiển thị ở tab movements)
        $stockMovements = ($tab === 'movements') ? $orderModel->getStockMovements([], 100) : [];
        
        $data = [
            'user' => Session::getUser(),
            'inventoryStats' => $inventoryStats,
            'products' => $products,
            'inventoryByCategory' => $inventoryByCategory,
            'stockMovements' => $stockMovements,
            'tab' => $tab,
            'threshold' => $threshold
        ];
        
        $this->view('admin/revenue/inventory', $data);
    }

    /**
     * Export báo cáo tồn kho
     */
    public function exportInventory()
    {
        $this->requireAdmin();
        
        $orderModel = $this->model('Order');
        $productModel = $this->model('Product');
        
        // Lấy tất cả sản phẩm với thông tin tồn kho
        $products = $productModel->getAllWithStock();
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=bao_cao_ton_kho_' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, ['Mã SP', 'Tên sản phẩm', 'Danh mục', 'Tồn kho', 'Giá bán', 'Giá nhập', 'Giá trị tồn (bán)', 'Giá trị tồn (nhập)', 'Trạng thái']);
        
        foreach ($products as $p) {
            $stock = $p['stock_quantity'] ?? $p['stock'] ?? 0;
            $status = $stock == 0 ? 'Hết hàng' : ($stock <= 10 ? 'Sắp hết' : 'Còn hàng');
            fputcsv($output, [
                $p['id'],
                $p['name'],
                $p['category_name'] ?? 'N/A',
                $stock,
                number_format($p['price'], 0, ',', '.'),
                number_format($p['import_price'] ?? 0, 0, ',', '.'),
                number_format($stock * $p['price'], 0, ',', '.'),
                number_format($stock * ($p['import_price'] ?? 0), 0, ',', '.'),
                $status
            ]);
        }
        
        fclose($output);
        exit;
    }
}
