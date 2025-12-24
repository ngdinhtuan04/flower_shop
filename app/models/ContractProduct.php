<?php
/**
 * Model ContractProduct - Quản lý chi tiết sản phẩm trong hợp đồng
 * 
 * Mô hình nghiệp vụ:
 * - Shop tạo và quản lý sản phẩm
 * - NCC chỉ cung ứng các sản phẩm do shop quản lý qua hợp đồng
 * - Mỗi hợp đồng có thể chứa nhiều sản phẩm với số lượng cam kết và giá nhập
 */
class ContractProduct
{
    private $db;
    private $table = 'contract_products';

    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    /**
     * Lấy tất cả sản phẩm trong một hợp đồng
     */
    public function getByContractId($contractId)
    {
        $sql = "SELECT cp.*, p.name as product_name, p.image as product_image, 
                       p.price as selling_price, p.stock_quantity,
                       (cp.committed_quantity - cp.delivered_quantity) as remaining_quantity
                FROM {$this->table} cp
                LEFT JOIN products p ON cp.product_id = p.id
                WHERE cp.contract_id = ?
                ORDER BY cp.created_at DESC";
        
        return $this->db->fetchAll($sql, [$contractId]);
    }

    /**
     * Lấy tất cả sản phẩm trong tất cả hợp đồng với đầy đủ thông tin
     */
    public function getAllWithDetails()
    {
        $sql = "SELECT cp.*, 
                       p.name as product_name, p.image as product_image, 
                       p.price as selling_price, p.stock_quantity,
                       sc.contract_code, sc.contract_name, sc.status as contract_status,
                       s.name as supplier_name,
                       (cp.committed_quantity - cp.delivered_quantity) as remaining_quantity
                FROM {$this->table} cp
                LEFT JOIN products p ON cp.product_id = p.id
                LEFT JOIN supplier_contracts sc ON cp.contract_id = sc.id
                LEFT JOIN suppliers s ON sc.supplier_id = s.id
                ORDER BY sc.contract_code ASC, p.name ASC";
        
        return $this->db->fetchAll($sql, []);
    }

    /**
     * Lấy chi tiết một sản phẩm trong hợp đồng
     */
    public function getById($id)
    {
        $sql = "SELECT cp.*, p.name as product_name, p.image as product_image,
                       p.price as selling_price, p.stock_quantity,
                       sc.contract_code, sc.contract_name, s.name as supplier_name
                FROM {$this->table} cp
                LEFT JOIN products p ON cp.product_id = p.id
                LEFT JOIN supplier_contracts sc ON cp.contract_id = sc.id
                LEFT JOIN suppliers s ON sc.supplier_id = s.id
                WHERE cp.id = ?";
        
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Kiểm tra sản phẩm đã tồn tại trong hợp đồng chưa
     */
    public function existsInContract($contractId, $productId, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE contract_id = ? AND product_id = ?";
        $params = [$contractId, $productId];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] > 0;
    }

    /**
     * Thêm sản phẩm vào hợp đồng
     */
    public function create($data)
    {
        // Kiểm tra đã tồn tại chưa
        if ($this->existsInContract($data['contract_id'], $data['product_id'])) {
            return ['success' => false, 'error' => 'Sản phẩm đã tồn tại trong hợp đồng này'];
        }

        $sql = "INSERT INTO {$this->table} 
                (contract_id, product_id, committed_quantity, import_price, unit, notes, allow_over_import)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        try {
            $result = $this->db->execute($sql, [
                $data['contract_id'],
                $data['product_id'],
                $data['committed_quantity'],
                $data['import_price'],
                $data['unit'] ?? 'cái',
                $data['notes'] ?? null,
                $data['allow_over_import'] ?? 0
            ]);
            
            return ['success' => $result !== false, 'id' => $this->db->lastInsertId()];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Cập nhật sản phẩm trong hợp đồng
     */
    public function update($id, $data)
    {
        // Kiểm tra trùng lặp
        if (isset($data['product_id']) && isset($data['contract_id'])) {
            if ($this->existsInContract($data['contract_id'], $data['product_id'], $id)) {
                return ['success' => false, 'error' => 'Sản phẩm đã tồn tại trong hợp đồng này'];
            }
        }

        $sql = "UPDATE {$this->table} 
                SET committed_quantity = ?, delivered_quantity = ?, import_price = ?, unit = ?, notes = ?, allow_over_import = ?, updated_at = NOW()
                WHERE id = ?";
        
        try {
            $result = $this->db->execute($sql, [
                $data['committed_quantity'],
                $data['delivered_quantity'] ?? 0,
                $data['import_price'],
                $data['unit'] ?? 'cái',
                $data['notes'] ?? null,
                $data['allow_over_import'] ?? 0,
                $id
            ]);
            
            return ['success' => $result !== false];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Cập nhật số lượng đã giao
     */
    public function updateDeliveredQuantity($id, $quantity)
    {
        $sql = "UPDATE {$this->table} 
                SET delivered_quantity = delivered_quantity + ?, updated_at = NOW()
                WHERE id = ?";
        
        return $this->db->execute($sql, [$quantity, $id]);
    }

    /**
     * Cập nhật số lượng cam kết (tăng cam kết khi nhập thêm vượt quota)
     */
    public function updateCommittedQuantity($id, $newCommittedQuantity)
    {
        $sql = "UPDATE {$this->table} 
                SET committed_quantity = ?, updated_at = NOW()
                WHERE id = ?";
        
        return $this->db->execute($sql, [$newCommittedQuantity, $id]);
    }

    /**
     * Xóa sản phẩm khỏi hợp đồng
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }

    /**
     * Lấy tất cả hợp đồng đang cung cấp một sản phẩm
     */
    public function getContractsByProductId($productId, $activeOnly = true)
    {
        $sql = "SELECT cp.*, sc.contract_code, sc.contract_name, sc.start_date, sc.end_date, sc.status,
                       s.name as supplier_name, s.phone as supplier_phone
                FROM {$this->table} cp
                LEFT JOIN supplier_contracts sc ON cp.contract_id = sc.id
                LEFT JOIN suppliers s ON sc.supplier_id = s.id
                WHERE cp.product_id = ?";
        
        if ($activeOnly) {
            $sql .= " AND sc.status = 'active' AND (sc.end_date IS NULL OR sc.end_date >= CURDATE())";
        }
        
        $sql .= " ORDER BY cp.import_price ASC"; // Sắp xếp theo giá nhập thấp nhất
        
        return $this->db->fetchAll($sql, [$productId]);
    }

    /**
     * Lấy giá nhập tốt nhất cho một sản phẩm (từ các hợp đồng đang active)
     */
    public function getBestImportPrice($productId)
    {
        $sql = "SELECT MIN(cp.import_price) as best_price, s.name as supplier_name
                FROM {$this->table} cp
                LEFT JOIN supplier_contracts sc ON cp.contract_id = sc.id
                LEFT JOIN suppliers s ON sc.supplier_id = s.id
                WHERE cp.product_id = ? 
                AND sc.status = 'active' 
                AND (sc.end_date IS NULL OR sc.end_date >= CURDATE())
                AND (cp.committed_quantity - cp.delivered_quantity) > 0
                GROUP BY s.id
                ORDER BY best_price ASC
                LIMIT 1";
        
        return $this->db->fetchOne($sql, [$productId]);
    }

    /**
     * Thống kê tổng quan hợp đồng
     */
    public function getContractStats($contractId)
    {
        $sql = "SELECT 
                    COUNT(*) as total_products,
                    SUM(committed_quantity) as total_committed,
                    SUM(delivered_quantity) as total_delivered,
                    SUM(committed_quantity * import_price) as total_contract_value,
                    SUM(delivered_quantity * import_price) as total_delivered_value
                FROM {$this->table}
                WHERE contract_id = ?";
        
        return $this->db->fetchOne($sql, [$contractId]);
    }

    /**
     * Lấy tổng giá trị đã cam kết trong hợp đồng
     */
    public function getTotalCommittedValue($contractId)
    {
        $sql = "SELECT COALESCE(SUM(committed_quantity * import_price), 0) as total_value
                FROM {$this->table}
                WHERE contract_id = ?";
        
        $result = $this->db->fetchOne($sql, [$contractId]);
        return $result ? floatval($result['total_value']) : 0;
    }

    /**
     * Lấy sản phẩm còn quota trong hợp đồng (chưa giao hết)
     */
    public function getAvailableProducts($contractId)
    {
        $sql = "SELECT cp.*, p.name as product_name, p.image as product_image,
                       (cp.committed_quantity - cp.delivered_quantity) as available_quantity
                FROM {$this->table} cp
                LEFT JOIN products p ON cp.product_id = p.id
                WHERE cp.contract_id = ? 
                AND (cp.committed_quantity - cp.delivered_quantity) > 0
                ORDER BY p.name ASC";
        
        return $this->db->fetchAll($sql, [$contractId]);
    }
}
