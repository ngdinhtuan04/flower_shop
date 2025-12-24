<?php
/**
 * PurchaseOrder Model - Quản lý đơn nhập hàng
 * 
 * Mô hình nghiệp vụ:
 * - Đơn nhập hàng được tạo khi cần nhập hàng từ NCC
 * - Có thể liên kết với hợp đồng hoặc không
 * - Khi nhận hàng thực tế, tồn kho sản phẩm mới được cập nhật
 */
class PurchaseOrder
{
    private $db;
    private $table = 'purchase_orders';
    private $itemsTable = 'purchase_order_items';

    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    /**
     * Lấy tất cả đơn nhập hàng
     */
    public function getAll($filters = [])
    {
        $sql = "SELECT po.*, s.name as supplier_name, sc.contract_code,
                       u1.full_name as created_by_name, u2.full_name as approved_by_name,
                       (SELECT COUNT(*) FROM {$this->itemsTable} WHERE purchase_order_id = po.id) as item_count
                FROM {$this->table} po
                LEFT JOIN suppliers s ON po.supplier_id = s.id
                LEFT JOIN supplier_contracts sc ON po.contract_id = sc.id
                LEFT JOIN users u1 ON po.created_by = u1.id
                LEFT JOIN users u2 ON po.approved_by = u2.id
                WHERE 1=1";
        
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND po.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['supplier_id'])) {
            $sql .= " AND po.supplier_id = ?";
            $params[] = $filters['supplier_id'];
        }

        if (!empty($filters['contract_id'])) {
            $sql .= " AND po.contract_id = ?";
            $params[] = $filters['contract_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND po.order_code LIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['from_date'])) {
            $sql .= " AND po.order_date >= ?";
            $params[] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $sql .= " AND po.order_date <= ?";
            $params[] = $filters['to_date'];
        }

        $sql .= " ORDER BY po.created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Lấy chi tiết đơn nhập hàng
     */
    public function getById($id)
    {
        $sql = "SELECT po.*, s.name as supplier_name, s.phone as supplier_phone,
                       s.email as supplier_email, s.address as supplier_address,
                       sc.contract_code, sc.contract_name,
                       u1.full_name as created_by_name, u2.full_name as approved_by_name
                FROM {$this->table} po
                LEFT JOIN suppliers s ON po.supplier_id = s.id
                LEFT JOIN supplier_contracts sc ON po.contract_id = sc.id
                LEFT JOIN users u1 ON po.created_by = u1.id
                LEFT JOIN users u2 ON po.approved_by = u2.id
                WHERE po.id = ?";
        
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Lấy chi tiết đơn nhập hàng kèm sản phẩm
     */
    public function getByIdWithItems($id)
    {
        $order = $this->getById($id);
        
        if ($order) {
            $order['items'] = $this->getItems($id);
        }
        
        return $order;
    }

    /**
     * Lấy các sản phẩm trong đơn nhập hàng
     */
    public function getItems($purchaseOrderId)
    {
        $sql = "SELECT poi.*, p.name as product_name, p.image as product_image, 
                       p.stock_quantity as current_stock
                FROM {$this->itemsTable} poi
                LEFT JOIN products p ON poi.product_id = p.id
                WHERE poi.purchase_order_id = ?
                ORDER BY poi.id";
        
        return $this->db->fetchAll($sql, [$purchaseOrderId]);
    }

    /**
     * Tạo đơn nhập hàng mới
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} 
                (order_code, contract_id, supplier_id, total_amount, status, 
                 order_date, expected_date, notes, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        try {
            $result = $this->db->execute($sql, [
                $data['order_code'],
                $data['contract_id'] ?? null,
                $data['supplier_id'],
                $data['total_amount'] ?? 0,
                $data['status'] ?? 'draft',
                $data['order_date'],
                $data['expected_date'] ?? null,
                $data['notes'] ?? null,
                $data['created_by'] ?? null
            ]);
            
            return ['success' => $result !== false, 'id' => $this->db->lastInsertId()];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Thêm sản phẩm vào đơn nhập hàng
     */
    public function addItem($data)
    {
        $sql = "INSERT INTO {$this->itemsTable} 
                (purchase_order_id, product_id, quantity, unit_price, total_price, notes)
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $totalPrice = $data['quantity'] * $data['unit_price'];
        
        try {
            $result = $this->db->execute($sql, [
                $data['purchase_order_id'],
                $data['product_id'],
                $data['quantity'],
                $data['unit_price'],
                $totalPrice,
                $data['notes'] ?? null
            ]);
            
            // Cập nhật tổng tiền đơn hàng
            if ($result) {
                $this->updateTotalAmount($data['purchase_order_id']);
            }
            
            return ['success' => $result !== false];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Cập nhật sản phẩm trong đơn nhập hàng
     */
    public function updateItem($itemId, $data)
    {
        $totalPrice = $data['quantity'] * $data['unit_price'];
        
        $sql = "UPDATE {$this->itemsTable} 
                SET quantity = ?, unit_price = ?, total_price = ?, notes = ?
                WHERE id = ?";
        
        try {
            $result = $this->db->execute($sql, [
                $data['quantity'],
                $data['unit_price'],
                $totalPrice,
                $data['notes'] ?? null,
                $itemId
            ]);
            
            // Lấy purchase_order_id và cập nhật tổng tiền
            if ($result) {
                $item = $this->db->fetchOne("SELECT purchase_order_id FROM {$this->itemsTable} WHERE id = ?", [$itemId]);
                if ($item) {
                    $this->updateTotalAmount($item['purchase_order_id']);
                }
            }
            
            return ['success' => $result !== false];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Xóa sản phẩm khỏi đơn nhập hàng
     */
    public function deleteItem($itemId)
    {
        // Lấy purchase_order_id trước khi xóa
        $item = $this->db->fetchOne("SELECT purchase_order_id FROM {$this->itemsTable} WHERE id = ?", [$itemId]);
        
        $sql = "DELETE FROM {$this->itemsTable} WHERE id = ?";
        $result = $this->db->execute($sql, [$itemId]);
        
        // Cập nhật tổng tiền
        if ($result && $item) {
            $this->updateTotalAmount($item['purchase_order_id']);
        }
        
        return $result;
    }

    /**
     * Cập nhật tổng tiền đơn nhập hàng
     */
    private function updateTotalAmount($purchaseOrderId)
    {
        $sql = "UPDATE {$this->table} 
                SET total_amount = (
                    SELECT COALESCE(SUM(total_price), 0) 
                    FROM {$this->itemsTable} 
                    WHERE purchase_order_id = ?
                )
                WHERE id = ?";
        
        return $this->db->execute($sql, [$purchaseOrderId, $purchaseOrderId]);
    }

    /**
     * Cập nhật trạng thái đơn nhập hàng
     */
    public function updateStatus($id, $status, $userId = null)
    {
        $sql = "UPDATE {$this->table} SET status = ?";
        $params = [$status];
        
        if ($status === 'approved' && $userId) {
            $sql .= ", approved_by = ?";
            $params[] = $userId;
        }
        
        if ($status === 'received') {
            $sql .= ", received_date = CURDATE()";
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $id;
        
        return $this->db->execute($sql, $params);
    }

    /**
     * Nhận hàng - cập nhật tồn kho sản phẩm
     */
    public function receiveItems($purchaseOrderId, $receivedItems)
    {
        try {
            // Bắt đầu transaction
            $this->db->beginTransaction();
            
            foreach ($receivedItems as $item) {
                // Cập nhật số lượng đã nhận trong đơn hàng
                $sql = "UPDATE {$this->itemsTable} 
                        SET received_quantity = ? 
                        WHERE id = ?";
                $this->db->execute($sql, [$item['received_quantity'], $item['item_id']]);
                
                // Cập nhật tồn kho sản phẩm
                $sql = "UPDATE products 
                        SET stock_quantity = stock_quantity + ? 
                        WHERE id = ?";
                $this->db->execute($sql, [$item['received_quantity'], $item['product_id']]);
            }
            
            // Cập nhật trạng thái đơn hàng
            $this->updateStatus($purchaseOrderId, 'received');
            
            // Cập nhật delivered_quantity trong contract_products nếu có liên kết hợp đồng
            $order = $this->getById($purchaseOrderId);
            if ($order && $order['contract_id']) {
                foreach ($receivedItems as $item) {
                    $sql = "UPDATE contract_products 
                            SET delivered_quantity = delivered_quantity + ? 
                            WHERE contract_id = ? AND product_id = ?";
                    $this->db->execute($sql, [
                        $item['received_quantity'], 
                        $order['contract_id'], 
                        $item['product_id']
                    ]);
                }
            }
            
            $this->db->commit();
            return ['success' => true];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Cập nhật đơn nhập hàng
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} 
                SET contract_id = ?, supplier_id = ?, order_date = ?, 
                    expected_date = ?, notes = ?, updated_at = NOW()
                WHERE id = ?";
        
        return $this->db->execute($sql, [
            $data['contract_id'] ?? null,
            $data['supplier_id'],
            $data['order_date'],
            $data['expected_date'] ?? null,
            $data['notes'] ?? null,
            $id
        ]);
    }

    /**
     * Xóa đơn nhập hàng (chỉ cho phép xóa draft)
     */
    public function delete($id)
    {
        // Kiểm tra trạng thái
        $order = $this->getById($id);
        if ($order && $order['status'] !== 'draft') {
            return ['success' => false, 'error' => 'Chỉ có thể xóa đơn hàng ở trạng thái Nháp'];
        }
        
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $result = $this->db->execute($sql, [$id]);
        
        return ['success' => $result !== false];
    }

    /**
     * Sinh mã đơn nhập hàng tự động
     */
    public function generateOrderCode()
    {
        $year = date('Y');
        $sql = "SELECT MAX(CAST(SUBSTRING(order_code, 9) AS UNSIGNED)) as max_num 
                FROM {$this->table} 
                WHERE order_code LIKE ?";
        
        $result = $this->db->fetchOne($sql, ["PO-{$year}-%"]);
        $nextNum = ($result['max_num'] ?? 0) + 1;
        
        return sprintf("PO-%s-%03d", $year, $nextNum);
    }

    /**
     * Thống kê đơn nhập hàng
     */
    public function getStats($filters = [])
    {
        $sql = "SELECT 
                    COUNT(*) as total_orders,
                    SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_count,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count,
                    SUM(CASE WHEN status = 'received' THEN 1 ELSE 0 END) as received_count,
                    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count,
                    SUM(CASE WHEN status = 'received' THEN total_amount ELSE 0 END) as total_received_value
                FROM {$this->table}
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['from_date'])) {
            $sql .= " AND order_date >= ?";
            $params[] = $filters['from_date'];
        }
        
        if (!empty($filters['to_date'])) {
            $sql .= " AND order_date <= ?";
            $params[] = $filters['to_date'];
        }
        
        return $this->db->fetchOne($sql, $params);
    }
}
