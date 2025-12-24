<?php
/**
 * Model SupplierContract - Quản lý hợp đồng với nhà cung cấp
 */
class SupplierContract
{
    private $db;

    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    /**
     * Lấy tất cả hợp đồng
     */
    public function getAll($filters = [])
    {
        $sql = "SELECT sc.*, s.name as supplier_name, u.full_name as created_by_name
                FROM supplier_contracts sc
                LEFT JOIN suppliers s ON sc.supplier_id = s.id
                LEFT JOIN users u ON sc.created_by = u.id
                WHERE 1=1";
        $params = [];

        if (isset($filters['supplier_id'])) {
            $sql .= " AND sc.supplier_id = ?";
            $params[] = $filters['supplier_id'];
        }

        if (isset($filters['status'])) {
            $sql .= " AND sc.status = ?";
            $params[] = $filters['status'];
        }

        if (isset($filters['search']) && !empty($filters['search'])) {
            $sql .= " AND (sc.contract_code LIKE ? OR sc.contract_name LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY sc.created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Lấy hợp đồng theo ID
     */
    public function getById($id)
    {
        $sql = "SELECT sc.*, s.name as supplier_name
                FROM supplier_contracts sc
                LEFT JOIN suppliers s ON sc.supplier_id = s.id
                WHERE sc.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Tạo hợp đồng mới
     */
    public function create($data)
    {
        $sql = "INSERT INTO supplier_contracts 
                (supplier_id, contract_code, contract_name, contract_type, contract_value, 
                start_date, end_date, payment_terms, delivery_terms, status, notes, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        return $this->db->execute($sql, [
            $data['supplier_id'],
            $data['contract_code'],
            $data['contract_name'],
            $data['contract_type'],
            $data['contract_value'] ?? null,
            $data['start_date'],
            $data['end_date'] ?? null,
            $data['payment_terms'] ?? null,
            $data['delivery_terms'] ?? null,
            $data['status'] ?? 'draft',
            $data['notes'] ?? null,
            $data['created_by'] ?? null
        ]);
    }

    /**
     * Cập nhật hợp đồng
     */
    public function update($id, $data)
    {
        $sql = "UPDATE supplier_contracts 
                SET supplier_id = ?, contract_code = ?, contract_name = ?, contract_type = ?, 
                    contract_value = ?, start_date = ?, end_date = ?, payment_terms = ?, 
                    delivery_terms = ?, status = ?, notes = ?
                WHERE id = ?";
        
        return $this->db->execute($sql, [
            $data['supplier_id'],
            $data['contract_code'],
            $data['contract_name'],
            $data['contract_type'],
            $data['contract_value'] ?? null,
            $data['start_date'],
            $data['end_date'] ?? null,
            $data['payment_terms'] ?? null,
            $data['delivery_terms'] ?? null,
            $data['status'],
            $data['notes'] ?? null,
            $id
        ]);
    }

    /**
     * Xóa hợp đồng
     */
    public function delete($id)
    {
        $sql = "DELETE FROM supplier_contracts WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }

    /**
     * Kiểm tra mã hợp đồng đã tồn tại
     */
    public function contractCodeExists($code, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM supplier_contracts WHERE contract_code = ?";
        $params = [$code];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] > 0;
    }

    /**
     * Lấy chi tiết hợp đồng kèm sản phẩm
     */
    public function getByIdWithProducts($id)
    {
        $contract = $this->getById($id);
        
        if ($contract) {
            $sql = "SELECT cp.*, p.name as product_name, p.image as product_image, 
                           p.price as selling_price, p.stock_quantity,
                           (cp.committed_quantity - cp.delivered_quantity) as remaining_quantity
                    FROM contract_products cp
                    LEFT JOIN products p ON cp.product_id = p.id
                    WHERE cp.contract_id = ?
                    ORDER BY p.name ASC";
            
            $contract['products'] = $this->db->fetchAll($sql, [$id]);
        }
        
        return $contract;
    }

    /**
     * Lấy thống kê hợp đồng
     */
    public function getStats($id)
    {
        $sql = "SELECT 
                    COUNT(cp.id) as total_products,
                    COALESCE(SUM(cp.committed_quantity), 0) as total_committed,
                    COALESCE(SUM(cp.delivered_quantity), 0) as total_delivered,
                    COALESCE(SUM(cp.committed_quantity * cp.import_price), 0) as total_value,
                    COALESCE(SUM(cp.delivered_quantity * cp.import_price), 0) as delivered_value
                FROM supplier_contracts sc
                LEFT JOIN contract_products cp ON sc.id = cp.contract_id
                WHERE sc.id = ?
                GROUP BY sc.id";
        
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Lấy hợp đồng đang active của nhà cung cấp
     */
    public function getActiveBySupplier($supplierId)
    {
        $sql = "SELECT sc.*, 
                    (SELECT COUNT(*) FROM contract_products WHERE contract_id = sc.id) as product_count
                FROM supplier_contracts sc
                WHERE sc.supplier_id = ? 
                AND sc.status = 'active'
                AND (sc.end_date IS NULL OR sc.end_date >= CURDATE())
                ORDER BY sc.start_date DESC";
        
        return $this->db->fetchAll($sql, [$supplierId]);
    }

    /**
     * Tự động cập nhật trạng thái hợp đồng hết hạn
     */
    public function updateExpiredContracts()
    {
        $sql = "UPDATE supplier_contracts 
                SET status = 'expired' 
                WHERE status = 'active' 
                AND end_date IS NOT NULL 
                AND end_date < CURDATE()";
        
        return $this->db->execute($sql);
    }

    /**
     * Sinh mã hợp đồng tự động
     */
    public function generateContractCode()
    {
        $year = date('Y');
        $sql = "SELECT MAX(CAST(SUBSTRING(contract_code, 9) AS UNSIGNED)) as max_num 
                FROM supplier_contracts 
                WHERE contract_code LIKE ?";
        
        $result = $this->db->fetchOne($sql, ["HD-{$year}-%"]);
        $nextNum = ($result['max_num'] ?? 0) + 1;
        
        return sprintf("HD-%s-%03d", $year, $nextNum);
    }
}

