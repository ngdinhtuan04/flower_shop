<?php
/**
 * Order Model - Quản lý đơn hàng
 */
class Order {
    protected $table = 'orders';
    protected $db;
    
    public function __construct() {
        $this->db = DB::getInstance();
    }
    
    // Tạo đơn hàng mới
    public function createOrder($orderData, $cartItems) {
        try {
            $this->db->beginTransaction();
            
            // Insert order
            $sql = "INSERT INTO orders (
                user_id, order_code, customer_name, customer_email, customer_phone,
                shipping_address, shipping_note, shipping_method, subtotal, shipping_fee, 
                product_discount, shipping_discount, discount,
                total, payment_method, coupon_code, coupon_discount,
                payment_status, order_status, created_at, updated_at
            ) VALUES (
                :user_id, :order_code, :customer_name, :customer_email, :customer_phone,
                :shipping_address, :shipping_note, :shipping_method, :subtotal, :shipping_fee, 
                :product_discount, :shipping_discount, :discount,
                :total, :payment_method, :coupon_code, :coupon_discount,
                :payment_status, :order_status, NOW(), NOW()
            )";
            
            $this->db->query($sql, $orderData);
            $orderId = $this->db->lastInsertId();
            
            // Insert order items
            $sql = "INSERT INTO order_items (
                order_id, product_id, product_name, product_image,
                price, quantity, subtotal
            ) VALUES (
                :order_id, :product_id, :product_name, :product_image,
                :price, :quantity, :subtotal
            )";
            
            foreach ($cartItems as $item) {
                $product = $item['product'];
                $actualPrice = $product['price'];
                
                // Nếu có actual_price từ checkout (đã tính promotion)
                if (isset($item['actual_price'])) {
                    $actualPrice = $item['actual_price'];
                }
                
                // Chỉ lưu tên file, không lưu full path
                $imageName = $product['image'];
                if (strpos($imageName, 'uploads/') !== false) {
                    $imageName = basename($imageName);
                }
                
                $itemData = [
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'product_name' => $product['name'],
                    'product_image' => $imageName,
                    'price' => $actualPrice,
                    'quantity' => $item['quantity'],
                    'subtotal' => $actualPrice * $item['quantity']
                ];
                
                $this->db->query($sql, $itemData);
                
                // Giảm số lượng sản phẩm trong kho
                $updateStock = "UPDATE products SET stock_quantity = stock_quantity - :quantity WHERE id = :product_id";
                $this->db->query($updateStock, [
                    'quantity' => $item['quantity'],
                    'product_id' => $item['product_id']
                ]);
            }
            
            $this->db->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Create order error: " . $e->getMessage());
            return false;
        }
    }
    
    // Lấy đơn hàng theo ID
    public function getOrderById($orderId) {
        $sql = "SELECT * FROM orders WHERE id = :id";
        return $this->db->fetchOne($sql, ['id' => $orderId]);
    }
    
    // Lấy đơn hàng theo mã
    public function getOrderByCode($orderCode) {
        $sql = "SELECT * FROM orders WHERE order_code = :order_code";
        return $this->db->fetchOne($sql, ['order_code' => $orderCode]);
    }
    
    // Lấy đơn hàng theo mã và số điện thoại (cho khách vãng lai tra cứu)
    public function getOrderByCodeAndPhone($orderCode, $phone) {
        $sql = "SELECT * FROM orders 
                WHERE order_code = :order_code 
                AND customer_phone = :phone";
        return $this->db->fetchOne($sql, [
            'order_code' => $orderCode,
            'phone' => $phone
        ]);
    }
    
    // Lấy các sản phẩm trong đơn hàng
    public function getOrderItems($orderId) {
        $sql = "SELECT oi.*, p.name as product_name, p.image 
                FROM order_items oi
                LEFT JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = :order_id";
        return $this->db->fetchAll($sql, ['order_id' => $orderId]);
    }
    
    // Lấy danh sách đơn hàng của user
    public function getUserOrders($userId, $limit = 50) {
        $sql = "SELECT * FROM orders 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC 
                LIMIT " . (int)$limit;
        return $this->db->fetchAll($sql, ['user_id' => $userId]);
    }
    
    // Cập nhật trạng thái thanh toán
    public function updatePaymentStatus($orderId, $status, $paymentInfo = null) {
        $sql = "UPDATE orders 
                SET payment_status = :status, 
                    payment_info = :payment_info,
                    updated_at = NOW()
                WHERE id = :id";
        
        return $this->db->execute($sql, [
            'id' => $orderId,
            'status' => $status,
            'payment_info' => $paymentInfo
        ]);
    }
    
    // Cập nhật trạng thái đơn hàng
    public function updateOrderStatus($orderId, $status) {
        $sql = "UPDATE orders 
                SET order_status = :order_status,
                    updated_at = NOW()
                WHERE id = :id";
        
        return $this->db->execute($sql, [
            'id' => $orderId,
            'order_status' => $status
        ]);
    }
    
    // Hủy đơn hàng
    public function cancelOrder($orderId, $reason) {
        $sql = "UPDATE orders 
                SET order_status = 'cancelled',
                    cancel_reason = :reason,
                    cancelled_at = NOW(),
                    updated_at = NOW()
                WHERE id = :id";
        
        return $this->db->execute($sql, [
            'id' => $orderId,
            'reason' => $reason
        ]);
    }
    
    // Đánh dấu đã giao hàng
    public function markAsDelivered($orderId) {
        $sql = "UPDATE orders 
                SET order_status = 'delivered',
                    delivered_at = NOW(),
                    updated_at = NOW()
                WHERE id = :id";
        
        return $this->db->execute($sql, ['id' => $orderId]);
    }
    
    // Lấy tổng số đơn hàng
    public function getTotalOrders($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM orders WHERE 1=1";
        $params = [];
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }
        
        if (!empty($filters['order_status'])) {
            $sql .= " AND order_status = :order_status";
            $params['order_status'] = $filters['order_status'];
        }
        
        if (!empty($filters['payment_status'])) {
            $sql .= " AND payment_status = :payment_status";
            $params['payment_status'] = $filters['payment_status'];
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    // Lấy tổng doanh thu
    public function getTotalRevenue($filters = []) {
        $sql = "SELECT SUM(total) as revenue FROM orders WHERE 1=1";
        $params = [];
        
        // Xử lý filter theo trạng thái
        $status = $filters['status'] ?? 'paid';
        if ($status !== 'all') {
            $sql .= " AND payment_status = :status";
            $params['status'] = $status;
        }
        
        if (!empty($filters['from_date'])) {
            $sql .= " AND DATE(created_at) >= :from_date";
            $params['from_date'] = $filters['from_date'];
        }
        
        if (!empty($filters['to_date'])) {
            $sql .= " AND DATE(created_at) <= :to_date";
            $params['to_date'] = $filters['to_date'];
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['revenue'] ?? 0;
    }
    
    // Lấy tất cả đơn hàng (cho admin)
    public function getAllOrders($filters = []) {
        $sql = "SELECT o.*, u.full_name, u.email
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND o.order_status = :status";
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['payment_method'])) {
            $sql .= " AND o.payment_method = :payment_method";
            $params['payment_method'] = $filters['payment_method'];
        }
        
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $sql .= " AND (
                o.order_code LIKE :search1 
                OR o.customer_name LIKE :search2 
                OR o.customer_email LIKE :search3
                OR u.full_name LIKE :search4
                OR u.email LIKE :search5
            )";
            $params['search1'] = $searchTerm;
            $params['search2'] = $searchTerm;
            $params['search3'] = $searchTerm;
            $params['search4'] = $searchTerm;
            $params['search5'] = $searchTerm;
        }
        
        $sql .= " ORDER BY o.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Lấy doanh thu theo khoảng thời gian (cho báo cáo)
     */
    public function getRevenueByPeriod($filters = []) {
        $period = $filters['period'] ?? 'day';
        $fromDate = $filters['from_date'] ?? date('Y-m-01');
        $toDate = $filters['to_date'] ?? date('Y-m-d');
        $status = $filters['status'] ?? 'paid';
        
        // Định dạng ngày theo period
        $dateFormat = match($period) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-W%u',
            'month' => '%Y-%m',
            'year' => '%Y',
            default => '%Y-%m-%d'
        };
        
        $sql = "SELECT 
                    DATE_FORMAT(created_at, '{$dateFormat}') as date,
                    SUM(total) as revenue,
                    COUNT(*) as orders,
                    AVG(total) as avg_order
                FROM orders
                WHERE 1=1";
        
        $params = [
            'from_date' => $fromDate,
            'to_date' => $toDate
        ];
        
        // Xử lý filter theo trạng thái
        if ($status !== 'all') {
            $sql .= " AND payment_status = :status";
            $params['status'] = $status;
        }
        
        $sql .= " AND DATE(created_at) >= :from_date
                    AND DATE(created_at) <= :to_date
                GROUP BY DATE_FORMAT(created_at, '{$dateFormat}')
                ORDER BY date ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Đếm số đơn hàng theo điều kiện
     */
    public function countOrders($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM orders WHERE 1=1";
        $params = [];
        
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $sql .= " AND payment_status = :status";
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['from_date'])) {
            $sql .= " AND DATE(created_at) >= :from_date";
            $params['from_date'] = $filters['from_date'];
        }
        
        if (!empty($filters['to_date'])) {
            $sql .= " AND DATE(created_at) <= :to_date";
            $params['to_date'] = $filters['to_date'];
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    /**
     * Lấy tổng số sản phẩm đã bán
     */
    public function getTotalProductsSold($filters = []) {
        $sql = "SELECT SUM(oi.quantity) as total
                FROM order_items oi
                INNER JOIN orders o ON oi.order_id = o.id
                WHERE 1=1";
        $params = [];
        
        // Xử lý filter theo trạng thái
        $status = $filters['status'] ?? 'paid';
        if ($status !== 'all') {
            $sql .= " AND o.payment_status = :status";
            $params['status'] = $status;
        }
        
        if (!empty($filters['from_date'])) {
            $sql .= " AND DATE(o.created_at) >= :from_date";
            $params['from_date'] = $filters['from_date'];
        }
        
        if (!empty($filters['to_date'])) {
            $sql .= " AND DATE(o.created_at) <= :to_date";
            $params['to_date'] = $filters['to_date'];
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }
    
    /**
     * Lấy top sản phẩm bán chạy
     */
    public function getTopSellingProducts($filters = [], $limit = 5) {
        $sql = "SELECT 
                    oi.product_id,
                    oi.product_name,
                    oi.product_image,
                    SUM(oi.quantity) as total_sold,
                    SUM(oi.subtotal) as total_revenue,
                    COUNT(DISTINCT oi.order_id) as order_count
                FROM order_items oi
                INNER JOIN orders o ON oi.order_id = o.id
                WHERE 1=1";
        $params = [];
        
        // Xử lý filter theo trạng thái
        $status = $filters['status'] ?? 'paid';
        if ($status !== 'all') {
            $sql .= " AND o.payment_status = :status";
            $params['status'] = $status;
        }
        
        if (!empty($filters['from_date'])) {
            $sql .= " AND DATE(o.created_at) >= :from_date";
            $params['from_date'] = $filters['from_date'];
        }
        
        if (!empty($filters['to_date'])) {
            $sql .= " AND DATE(o.created_at) <= :to_date";
            $params['to_date'] = $filters['to_date'];
        }
        
        $sql .= " GROUP BY oi.product_id, oi.product_name, oi.product_image
                  ORDER BY total_sold DESC
                  LIMIT " . (int)$limit;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Lấy top khách hàng mua nhiều nhất
     */
    public function getTopCustomers($filters = [], $limit = 5) {
        $sql = "SELECT 
                    o.customer_name,
                    o.customer_email,
                    o.customer_phone,
                    COUNT(*) as order_count,
                    SUM(o.total) as total_spent
                FROM orders o
                WHERE 1=1";
        $params = [];
        
        // Xử lý filter theo trạng thái
        $status = $filters['status'] ?? 'paid';
        if ($status !== 'all') {
            $sql .= " AND o.payment_status = :status";
            $params['status'] = $status;
        }
        
        if (!empty($filters['from_date'])) {
            $sql .= " AND DATE(o.created_at) >= :from_date";
            $params['from_date'] = $filters['from_date'];
        }
        
        if (!empty($filters['to_date'])) {
            $sql .= " AND DATE(o.created_at) <= :to_date";
            $params['to_date'] = $filters['to_date'];
        }
        
        $sql .= " GROUP BY o.customer_email, o.customer_name, o.customer_phone
                  ORDER BY total_spent DESC
                  LIMIT " . (int)$limit;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Lấy top danh mục bán chạy
     */
    public function getTopCategories($filters = [], $limit = 5) {
        $sql = "SELECT 
                    c.id,
                    c.name as category_name,
                    COUNT(DISTINCT o.id) as order_count,
                    SUM(oi.quantity) as total_sold,
                    SUM(oi.subtotal) as total_revenue
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                JOIN products p ON oi.product_id = p.id
                JOIN categories c ON p.category_id = c.id
                WHERE 1=1";
        $params = [];
        
        // Xử lý filter theo trạng thái
        $status = $filters['status'] ?? 'paid';
        if ($status !== 'all') {
            $sql .= " AND o.payment_status = :status";
            $params['status'] = $status;
        }
        
        if (!empty($filters['from_date'])) {
            $sql .= " AND DATE(o.created_at) >= :from_date";
            $params['from_date'] = $filters['from_date'];
        }
        
        if (!empty($filters['to_date'])) {
            $sql .= " AND DATE(o.created_at) <= :to_date";
            $params['to_date'] = $filters['to_date'];
        }
        
        $sql .= " GROUP BY c.id, c.name
                  ORDER BY total_revenue DESC
                  LIMIT " . (int)$limit;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Lấy sản phẩm bán chậm (tồn kho cao, bán ít)
     * Định nghĩa bán chậm:
     * 1. Còn tồn kho (stock > 0)
     * 2. Bán ít nhất trong khoảng thời gian đã chọn
     * 3. Tồn kho cao (stock > 10) hoặc bán < 5 sản phẩm
     * 4. Sắp xếp theo: Bán ít nhất trước, tồn kho cao trước
     */
    public function getSlowestProducts($filters = [], $limit = 5) {
        $sql = "SELECT 
                    p.id,
                    p.name as product_name,
                    p.image as product_image,
                    p.stock,
                    COALESCE(SUM(oi.quantity), 0) as total_sold,
                    COALESCE(SUM(oi.subtotal), 0) as total_revenue
                FROM products p
                LEFT JOIN order_items oi ON p.id = oi.product_id
                LEFT JOIN orders o ON oi.order_id = o.id";
        
        $whereConditions = [];
        $params = [];
        
        // Filter theo trạng thái thanh toán
        $status = $filters['status'] ?? 'paid';
        if ($status !== 'all') {
            $whereConditions[] = "(o.payment_status = :status OR o.payment_status IS NULL)";
            $params['status'] = $status;
        }
        
        // Filter theo thời gian
        if (!empty($filters['from_date'])) {
            $whereConditions[] = "(DATE(o.created_at) >= :from_date OR o.created_at IS NULL)";
            $params['from_date'] = $filters['from_date'];
        }
        
        if (!empty($filters['to_date'])) {
            $whereConditions[] = "(DATE(o.created_at) <= :to_date OR o.created_at IS NULL)";
            $params['to_date'] = $filters['to_date'];
        }
        
        // Thêm WHERE conditions nếu có
        if (!empty($whereConditions)) {
            $sql .= " WHERE " . implode(' AND ', $whereConditions);
        }
        
        // GROUP BY và filter sản phẩm bán chậm
        $sql .= " GROUP BY p.id, p.name, p.image, p.stock
                  HAVING p.stock > 0 AND (p.stock > 10 OR total_sold < 5)
                  ORDER BY total_sold ASC, p.stock DESC
                  LIMIT " . (int)$limit;
        
        return $this->db->fetchAll($sql, $params);
    }

    // ==================== BÁO CÁO LỢI NHUẬN ====================
    
    /**
     * Lấy thống kê lợi nhuận với giá nhập từ NCC
     */
    public function getProfitStats($filters = []) {
        $sql = "SELECT 
                    COALESCE(SUM(oi.quantity * oi.price), 0) as total_revenue,
                    COALESCE(SUM(oi.quantity * COALESCE(
                        (SELECT cp.import_price 
                         FROM contract_products cp 
                         WHERE cp.product_id = oi.product_id 
                         ORDER BY cp.created_at DESC LIMIT 1), 
                        oi.price * 0.6
                    )), 0) as total_cost,
                    COUNT(DISTINCT o.id) as total_orders
                FROM orders o
                JOIN order_items oi ON o.id = oi.order_id
                WHERE o.order_status IN ('delivered', 'picked_up')";
        
        $params = [];
        
        if (!empty($filters['from_date'])) {
            $sql .= " AND DATE(o.created_at) >= ?";
            $params[] = $filters['from_date'];
        }
        if (!empty($filters['to_date'])) {
            $sql .= " AND DATE(o.created_at) <= ?";
            $params[] = $filters['to_date'];
        }
        
        $result = $this->db->fetchOne($sql, $params);
        
        $revenue = floatval($result['total_revenue'] ?? 0);
        $cost = floatval($result['total_cost'] ?? 0);
        $profit = $revenue - $cost;
        $margin = $revenue > 0 ? ($profit / $revenue) * 100 : 0;
        
        return [
            'total_revenue' => $revenue,
            'total_cost' => $cost,
            'total_profit' => $profit,
            'profit_margin' => round($margin, 2),
            'total_orders' => intval($result['total_orders'] ?? 0)
        ];
    }

    /**
     * Lợi nhuận theo sản phẩm
     */
    public function getProfitByProduct($filters = [], $limit = 20) {
        $sql = "SELECT 
                    p.id,
                    p.name as product_name,
                    p.image as product_image,
                    c.name as category_name,
                    SUM(oi.quantity) as total_sold,
                    SUM(oi.quantity * oi.price) as total_revenue,
                    COALESCE(
                        (SELECT cp.import_price 
                         FROM contract_products cp 
                         WHERE cp.product_id = p.id 
                         ORDER BY cp.created_at DESC LIMIT 1), 
                        0
                    ) as import_price,
                    SUM(oi.quantity * COALESCE(
                        (SELECT cp.import_price 
                         FROM contract_products cp 
                         WHERE cp.product_id = p.id 
                         ORDER BY cp.created_at DESC LIMIT 1), 
                        oi.price * 0.6
                    )) as total_cost
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                JOIN products p ON oi.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE o.order_status IN ('delivered', 'picked_up')";
        
        $params = [];
        
        if (!empty($filters['from_date'])) {
            $sql .= " AND DATE(o.created_at) >= ?";
            $params[] = $filters['from_date'];
        }
        if (!empty($filters['to_date'])) {
            $sql .= " AND DATE(o.created_at) <= ?";
            $params[] = $filters['to_date'];
        }
        
        $sql .= " GROUP BY p.id, p.name, p.image, c.name
                  ORDER BY (SUM(oi.quantity * oi.price) - SUM(oi.quantity * COALESCE(
                        (SELECT cp.import_price 
                         FROM contract_products cp 
                         WHERE cp.product_id = p.id 
                         ORDER BY cp.created_at DESC LIMIT 1), 
                        oi.price * 0.6
                    ))) DESC
                  LIMIT ?";
        $params[] = $limit;
        
        $products = $this->db->fetchAll($sql, $params);
        
        // Tính lợi nhuận và margin cho mỗi sản phẩm
        foreach ($products as &$product) {
            $product['profit'] = $product['total_revenue'] - $product['total_cost'];
            $product['margin'] = $product['total_revenue'] > 0 
                ? round(($product['profit'] / $product['total_revenue']) * 100, 1) 
                : 0;
        }
        
        return $products;
    }

    /**
     * Lợi nhuận theo nhà cung cấp
     */
    public function getProfitBySupplier($filters = [], $limit = 10) {
        $sql = "SELECT 
                    s.id as supplier_id,
                    s.name as supplier_name,
                    s.phone as supplier_phone,
                    COUNT(DISTINCT o.id) as total_orders,
                    SUM(oi.quantity) as total_sold,
                    SUM(oi.quantity * oi.price) as total_revenue,
                    SUM(oi.quantity * cp.import_price) as total_cost
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.id
                JOIN contract_products cp ON oi.product_id = cp.product_id
                JOIN supplier_contracts sc ON cp.contract_id = sc.id
                JOIN suppliers s ON sc.supplier_id = s.id
                WHERE o.order_status IN ('delivered', 'picked_up')";
        
        $params = [];
        
        if (!empty($filters['from_date'])) {
            $sql .= " AND DATE(o.created_at) >= ?";
            $params[] = $filters['from_date'];
        }
        if (!empty($filters['to_date'])) {
            $sql .= " AND DATE(o.created_at) <= ?";
            $params[] = $filters['to_date'];
        }
        
        $sql .= " GROUP BY s.id, s.name, s.phone
                  ORDER BY (SUM(oi.quantity * oi.price) - SUM(oi.quantity * cp.import_price)) DESC
                  LIMIT ?";
        $params[] = $limit;
        
        $suppliers = $this->db->fetchAll($sql, $params);
        
        foreach ($suppliers as &$supplier) {
            $supplier['profit'] = $supplier['total_revenue'] - $supplier['total_cost'];
            $supplier['margin'] = $supplier['total_revenue'] > 0 
                ? round(($supplier['profit'] / $supplier['total_revenue']) * 100, 1) 
                : 0;
        }
        
        return $suppliers;
    }

    /**
     * Xu hướng lợi nhuận theo thời gian
     */
    public function getProfitTrend($filters = []) {
        $period = $filters['period'] ?? 'day';
        
        switch ($period) {
            case 'month':
                $dateFormat = '%Y-%m';
                break;
            case 'week':
                $dateFormat = '%Y-%u';
                break;
            default:
                $dateFormat = '%Y-%m-%d';
        }
        
        $sql = "SELECT 
                    DATE_FORMAT(o.created_at, '{$dateFormat}') as period_date,
                    SUM(oi.quantity * oi.price) as revenue,
                    SUM(oi.quantity * COALESCE(
                        (SELECT cp.import_price 
                         FROM contract_products cp 
                         WHERE cp.product_id = oi.product_id 
                         ORDER BY cp.created_at DESC LIMIT 1), 
                        oi.price * 0.6
                    )) as cost
                FROM orders o
                JOIN order_items oi ON o.id = oi.order_id
                WHERE o.order_status IN ('delivered', 'picked_up')";
        
        $params = [];
        
        if (!empty($filters['from_date'])) {
            $sql .= " AND DATE(o.created_at) >= ?";
            $params[] = $filters['from_date'];
        }
        if (!empty($filters['to_date'])) {
            $sql .= " AND DATE(o.created_at) <= ?";
            $params[] = $filters['to_date'];
        }
        
        $sql .= " GROUP BY period_date ORDER BY period_date ASC";
        
        $data = $this->db->fetchAll($sql, $params);
        
        foreach ($data as &$row) {
            $row['profit'] = $row['revenue'] - $row['cost'];
            $row['margin'] = $row['revenue'] > 0 
                ? round(($row['profit'] / $row['revenue']) * 100, 1) 
                : 0;
        }
        
        return $data;
    }

    // ==================== THỐNG KÊ KHÁCH HÀNG CHI TIẾT ====================
    
    /**
     * Thống kê tổng quan khách hàng
     */
    public function getCustomerStats($filters = []) {
        $sql = "SELECT 
                    COUNT(DISTINCT u.id) as total_customers,
                    COUNT(DISTINCT CASE WHEN o.id IS NOT NULL THEN u.id END) as buying_customers,
                    COALESCE(SUM(o.total), 0) as total_spent,
                    COUNT(o.id) as total_orders
                FROM users u
                LEFT JOIN orders o ON u.id = o.user_id 
                    AND o.order_status IN ('delivered', 'picked_up')";
        
        $params = [];
        
        if (!empty($filters['from_date'])) {
            $sql .= " AND DATE(o.created_at) >= ?";
            $params[] = $filters['from_date'];
        }
        if (!empty($filters['to_date'])) {
            $sql .= " AND DATE(o.created_at) <= ?";
            $params[] = $filters['to_date'];
        }
        
        $sql .= " WHERE u.role = 'user'";
        
        $result = $this->db->fetchOne($sql, $params);
        
        $totalCustomers = intval($result['total_customers'] ?? 0);
        $buyingCustomers = intval($result['buying_customers'] ?? 0);
        $totalSpent = floatval($result['total_spent'] ?? 0);
        $totalOrders = intval($result['total_orders'] ?? 0);
        
        return [
            'total_customers' => $totalCustomers,
            'buying_customers' => $buyingCustomers,
            'non_buying_customers' => $totalCustomers - $buyingCustomers,
            'total_spent' => $totalSpent,
            'avg_spent_per_customer' => $buyingCustomers > 0 ? $totalSpent / $buyingCustomers : 0,
            'avg_orders_per_customer' => $buyingCustomers > 0 ? $totalOrders / $buyingCustomers : 0
        ];
    }

    /**
     * Danh sách khách hàng chi tiết với thống kê
     */
    public function getCustomerDetails($filters = [], $limit = 50) {
        $sql = "SELECT 
                    u.id,
                    u.full_name,
                    u.email,
                    u.phone,
                    u.created_at as registered_at,
                    COUNT(o.id) as total_orders,
                    COALESCE(SUM(o.total), 0) as total_spent,
                    MAX(o.created_at) as last_order_date,
                    MIN(o.created_at) as first_order_date
                FROM users u
                LEFT JOIN orders o ON u.id = o.user_id 
                    AND o.order_status IN ('delivered', 'picked_up')
                WHERE u.role = 'user'";
        
        $params = [];
        
        if (!empty($filters['from_date'])) {
            $sql .= " AND (o.created_at IS NULL OR DATE(o.created_at) >= ?)";
            $params[] = $filters['from_date'];
        }
        if (!empty($filters['to_date'])) {
            $sql .= " AND (o.created_at IS NULL OR DATE(o.created_at) <= ?)";
            $params[] = $filters['to_date'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }
        
        $orderBy = $filters['sort'] ?? 'total_spent';
        $orderDir = $filters['order'] ?? 'DESC';
        
        $sql .= " GROUP BY u.id, u.full_name, u.email, u.phone, u.created_at
                  ORDER BY {$orderBy} {$orderDir}
                  LIMIT ?";
        $params[] = $limit;
        
        $customers = $this->db->fetchAll($sql, $params);
        
        // Tính thêm các chỉ số
        foreach ($customers as &$customer) {
            $customer['avg_order_value'] = $customer['total_orders'] > 0 
                ? $customer['total_spent'] / $customer['total_orders'] 
                : 0;
            
            // Tính số ngày từ lần mua cuối
            if ($customer['last_order_date']) {
                $lastOrder = new DateTime($customer['last_order_date']);
                $now = new DateTime();
                $customer['days_since_last_order'] = $now->diff($lastOrder)->days;
            } else {
                $customer['days_since_last_order'] = null;
            }
        }
        
        return $customers;
    }

    /**
     * Chi tiết một khách hàng
     */
    public function getCustomerOrderHistory($userId, $limit = 20) {
        $sql = "SELECT 
                    o.id,
                    o.order_code,
                    o.total,
                    o.order_status,
                    o.payment_status,
                    o.payment_method,
                    o.created_at,
                    COUNT(oi.id) as item_count
                FROM orders o
                LEFT JOIN order_items oi ON o.id = oi.order_id
                WHERE o.user_id = ?
                GROUP BY o.id
                ORDER BY o.created_at DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$userId, $limit]);
    }

    /**
     * Top khách hàng mua nhiều nhất
     */
    public function getTopCustomersDetailed($filters = [], $limit = 10) {
        $sql = "SELECT 
                    u.id,
                    u.full_name,
                    u.email,
                    u.phone,
                    u.avatar,
                    COUNT(o.id) as total_orders,
                    COALESCE(SUM(o.total), 0) as total_spent,
                    MAX(o.created_at) as last_order_date,
                    COALESCE(SUM(oi.quantity), 0) as total_items
                FROM users u
                JOIN orders o ON u.id = o.user_id
                JOIN order_items oi ON o.id = oi.order_id
                WHERE o.order_status IN ('delivered', 'picked_up')
                    AND u.role = 'user'";
        
        $params = [];
        
        if (!empty($filters['from_date'])) {
            $sql .= " AND DATE(o.created_at) >= ?";
            $params[] = $filters['from_date'];
        }
        if (!empty($filters['to_date'])) {
            $sql .= " AND DATE(o.created_at) <= ?";
            $params[] = $filters['to_date'];
        }
        
        $sql .= " GROUP BY u.id, u.full_name, u.email, u.phone, u.avatar
                  ORDER BY total_spent DESC
                  LIMIT ?";
        $params[] = $limit;
        
        return $this->db->fetchAll($sql, $params);
    }

    // ==================== BÁO CÁO TỒN KHO ====================
    
    /**
     * Tổng quan tồn kho
     */
    public function getInventoryStats() {
        $sql = "SELECT 
                    COUNT(*) as total_products,
                    SUM(stock_quantity) as total_stock,
                    SUM(CASE WHEN stock_quantity = 0 THEN 1 ELSE 0 END) as out_of_stock,
                    SUM(CASE WHEN stock_quantity > 0 AND stock_quantity <= 10 THEN 1 ELSE 0 END) as low_stock,
                    SUM(CASE WHEN stock_quantity > 100 THEN 1 ELSE 0 END) as high_stock,
                    SUM(stock_quantity * price) as total_stock_value
                FROM products
                WHERE status = 'active'";
        
        $result = $this->db->fetchOne($sql, []);
        
        // Lấy giá trị tồn kho theo giá nhập
        $costValue = $this->db->fetchOne("
            SELECT COALESCE(SUM(p.stock_quantity * COALESCE(
                (SELECT cp.import_price 
                 FROM contract_products cp 
                 WHERE cp.product_id = p.id 
                 ORDER BY cp.created_at DESC LIMIT 1), 
                p.price * 0.6
            )), 0) as total_cost_value
            FROM products p
            WHERE p.status = 'active'
        ", []);
        
        $result['total_cost_value'] = floatval($costValue['total_cost_value'] ?? 0);
        $result['potential_profit'] = floatval($result['total_stock_value'] ?? 0) - $result['total_cost_value'];
        
        return $result;
    }

    /**
     * Sản phẩm sắp hết hàng (cảnh báo)
     */
    public function getLowStockProducts($threshold = 10, $limit = 50) {
        $sql = "SELECT 
                    p.id,
                    p.name,
                    p.image,
                    p.stock_quantity as stock,
                    p.price,
                    c.name as category_name,
                    COALESCE(
                        (SELECT cp.import_price 
                         FROM contract_products cp 
                         WHERE cp.product_id = p.id 
                         ORDER BY cp.created_at DESC LIMIT 1), 
                        0
                    ) as import_price,
                    COALESCE(
                        (SELECT s.name 
                         FROM contract_products cp 
                         JOIN supplier_contracts sc ON cp.contract_id = sc.id
                         JOIN suppliers s ON sc.supplier_id = s.id
                         WHERE cp.product_id = p.id 
                         ORDER BY cp.created_at DESC LIMIT 1), 
                        'Chưa có NCC'
                    ) as supplier_name,
                    (SELECT COALESCE(SUM(oi.quantity), 0) 
                     FROM order_items oi 
                     JOIN orders o ON oi.order_id = o.id 
                     WHERE oi.product_id = p.id 
                        AND o.order_status IN ('delivered', 'picked_up')
                        AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    ) as sold_last_30_days
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.status = 'active' AND p.stock_quantity <= ?
                ORDER BY p.stock_quantity ASC, sold_last_30_days DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$threshold, $limit]);
    }

    /**
     * Sản phẩm hết hàng
     */
    public function getOutOfStockProducts($limit = 50) {
        return $this->getLowStockProducts(0, $limit);
    }

    /**
     * Sản phẩm tồn kho cao (bán chậm)
     */
    public function getHighStockProducts($threshold = 100, $limit = 50) {
        $sql = "SELECT 
                    p.id,
                    p.name,
                    p.image,
                    p.stock_quantity as stock,
                    p.price,
                    c.name as category_name,
                    p.stock_quantity * p.price as stock_value,
                    COALESCE(
                        (SELECT cp.import_price 
                         FROM contract_products cp 
                         WHERE cp.product_id = p.id 
                         ORDER BY cp.created_at DESC LIMIT 1), 
                        p.price * 0.6
                    ) as import_price,
                    (SELECT COALESCE(SUM(oi.quantity), 0) 
                     FROM order_items oi 
                     JOIN orders o ON oi.order_id = o.id 
                     WHERE oi.product_id = p.id 
                        AND o.order_status IN ('delivered', 'picked_up')
                        AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    ) as sold_last_30_days
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.status = 'active' AND p.stock_quantity >= ?
                ORDER BY p.stock_quantity DESC
                LIMIT ?";
        
        $products = $this->db->fetchAll($sql, [$threshold, $limit]);
        
        // Tính số ngày tồn kho dự kiến
        foreach ($products as &$product) {
            $avgDailySales = $product['sold_last_30_days'] / 30;
            $product['days_of_stock'] = $avgDailySales > 0 
                ? round($product['stock'] / $avgDailySales) 
                : 999;
            $product['cost_value'] = $product['stock'] * $product['import_price'];
        }
        
        return $products;
    }

    /**
     * Lịch sử nhập/xuất kho
     */
    public function getStockMovements($filters = [], $limit = 100) {
        // Xuất kho (đơn hàng thành công)
        $exports = $this->db->fetchAll("
            SELECT 
                'export' as movement_type,
                o.created_at as movement_date,
                p.id as product_id,
                p.name as product_name,
                oi.quantity as quantity,
                o.order_code as reference,
                'Đơn hàng: ' || o.order_code as note
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            JOIN products p ON oi.product_id = p.id
            WHERE o.order_status IN ('delivered', 'picked_up')
            ORDER BY o.created_at DESC
            LIMIT ?
        ", [$limit]);
        
        // Nhập kho (từ contract_products - delivered)
        $imports = $this->db->fetchAll("
            SELECT 
                'import' as movement_type,
                cp.updated_at as movement_date,
                p.id as product_id,
                p.name as product_name,
                cp.delivered_quantity as quantity,
                sc.contract_code as reference,
                CONCAT('Hợp đồng: ', sc.contract_code) as note
            FROM contract_products cp
            JOIN products p ON cp.product_id = p.id
            JOIN supplier_contracts sc ON cp.contract_id = sc.id
            WHERE cp.delivered_quantity > 0
            ORDER BY cp.updated_at DESC
            LIMIT ?
        ", [$limit]);
        
        // Merge và sắp xếp
        $movements = array_merge($exports, $imports);
        usort($movements, function($a, $b) {
            return strtotime($b['movement_date']) - strtotime($a['movement_date']);
        });
        
        return array_slice($movements, 0, $limit);
    }

    /**
     * Báo cáo tồn kho theo danh mục
     */
    public function getInventoryByCategory() {
        $sql = "SELECT 
                    c.id,
                    c.name as category_name,
                    COUNT(p.id) as product_count,
                    COALESCE(SUM(p.stock_quantity), 0) as total_stock,
                    COALESCE(SUM(p.stock_quantity * p.price), 0) as stock_value,
                    SUM(CASE WHEN p.stock_quantity = 0 THEN 1 ELSE 0 END) as out_of_stock_count,
                    SUM(CASE WHEN p.stock_quantity > 0 AND p.stock_quantity <= 10 THEN 1 ELSE 0 END) as low_stock_count
                FROM categories c
                LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
                GROUP BY c.id, c.name
                ORDER BY stock_value DESC";
        
        return $this->db->fetchAll($sql, []);
    }
}
