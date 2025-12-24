<?php
/**
 * Supplier Model - Quản lý nhà cung cấp
 */
class Supplier
{
    protected $table = 'suppliers';
    protected $db;

    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    /**
     * Lấy tất cả nhà cung cấp (không bao gồm đã xóa)
     */
    public function getAll($includeDeleted = false)
    {
        $sql = "SELECT * FROM {$this->table}";
        
        if (!$includeDeleted) {
            $sql .= " WHERE deleted_at IS NULL";
        }
        
        $sql .= " ORDER BY name ASC";
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Lấy nhà cung cấp theo ID
     */
    public function getById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? AND deleted_at IS NULL LIMIT 1";
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Tìm kiếm nhà cung cấp
     */
    public function search($keyword)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE deleted_at IS NULL 
                AND (name LIKE ? OR phone LIKE ? OR email LIKE ? OR address LIKE ?)
                ORDER BY name ASC";
        
        $searchTerm = "%{$keyword}%";
        return $this->db->fetchAll($sql, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    }

    /**
     * Tạo nhà cung cấp mới
     */
    public function create($data)
    {
        $sql = "INSERT INTO {$this->table} (name, phone, email, address, created_at, updated_at) 
                VALUES (?, ?, ?, ?, NOW(), NOW())";
        
        try {
            $result = $this->db->execute($sql, [
                $data['name'],
                $data['phone'] ?? null,
                $data['email'] ?? null,
                $data['address'] ?? null
            ]);
            
            return [
                'success' => $result !== false,
                'id' => $this->db->lastInsertId()
            ];
        } catch (Exception $e) {
            error_log("Error creating supplier: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Cập nhật nhà cung cấp
     */
    public function update($id, $data)
    {
        $sql = "UPDATE {$this->table} 
                SET name = ?, 
                    phone = ?, 
                    email = ?, 
                    address = ?,
                    updated_at = NOW()
                WHERE id = ? AND deleted_at IS NULL";
        
        try {
            $result = $this->db->execute($sql, [
                $data['name'],
                $data['phone'] ?? null,
                $data['email'] ?? null,
                $data['address'] ?? null,
                $id
            ]);
            
            return ['success' => $result !== false];
        } catch (Exception $e) {
            error_log("Error updating supplier: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Xóa mềm nhà cung cấp (soft delete)
     */
    public function delete($id)
    {
        $sql = "UPDATE {$this->table} 
                SET deleted_at = NOW() 
                WHERE id = ? AND deleted_at IS NULL";
        
        try {
            $result = $this->db->execute($sql, [$id]);
            return ['success' => $result !== false];
        } catch (Exception $e) {
            error_log("Error deleting supplier: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Đếm số sản phẩm của nhà cung cấp (qua hợp đồng)
     * Đếm số sản phẩm duy nhất trong tất cả hợp đồng của nhà cung cấp
     */
    public function countProducts($supplierId)
    {
        $sql = "SELECT COUNT(DISTINCT cp.product_id) as count 
                FROM contract_products cp
                INNER JOIN supplier_contracts sc ON cp.contract_id = sc.id
                WHERE sc.supplier_id = ?";
        $result = $this->db->fetchOne($sql, [$supplierId]);
        return $result['count'] ?? 0;
    }

    /**
     * Kiểm tra email đã tồn tại chưa
     */
    public function emailExists($email, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE email = ? AND deleted_at IS NULL";
        
        $params = [$email];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return ($result['count'] ?? 0) > 0;
    }

    /**
     * Lấy tổng số nhà cung cấp
     */
    public function count()
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE deleted_at IS NULL";
        $result = $this->db->fetchOne($sql);
        return $result['count'] ?? 0;
    }
}
