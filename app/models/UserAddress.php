<?php
/**
 * UserAddress Model - Quản lý nhiều địa chỉ giao hàng
 * Giống Shopee/Lazada: User có thể lưu nhiều địa chỉ, chọn mặc định
 */
class UserAddress
{
    private $db;

    public function __construct()
    {
        $this->db = DB::getInstance();
    }

    /**
     * Lấy tất cả địa chỉ của user
     * @param int $userId
     * @return array
     */
    public function getByUserId($userId)
    {
        $sql = "SELECT * FROM user_addresses 
                WHERE user_id = ? 
                ORDER BY is_default DESC, created_at DESC";
        return $this->db->fetchAll($sql, [$userId]);
    }

    /**
     * Lấy địa chỉ mặc định của user
     * @param int $userId
     * @return array|null
     */
    public function getDefaultAddress($userId)
    {
        $sql = "SELECT * FROM user_addresses 
                WHERE user_id = ? AND is_default = 1 
                LIMIT 1";
        return $this->db->fetchOne($sql, [$userId]);
    }

    /**
     * Lấy 1 địa chỉ theo ID (kiểm tra quyền sở hữu)
     * @param int $addressId
     * @param int $userId
     * @return array|null
     */
    public function getById($addressId, $userId)
    {
        $sql = "SELECT * FROM user_addresses 
                WHERE id = ? AND user_id = ? 
                LIMIT 1";
        return $this->db->fetchOne($sql, [$addressId, $userId]);
    }

    /**
     * Thêm địa chỉ mới
     * @param int $userId
     * @param array $data
     * @return int|false ID địa chỉ mới hoặc false
     */
    public function create($userId, $data)
    {
        // Kiểm tra nếu chưa có địa chỉ nào, tự động set làm mặc định
        $existingCount = $this->db->fetchOne(
            "SELECT COUNT(*) as total FROM user_addresses WHERE user_id = ?",
            [$userId]
        )['total'];

        $isDefault = isset($data['is_default']) ? (int)$data['is_default'] : 0;
        
        // Nếu chưa có địa chỉ, bắt buộc làm mặc định
        if ($existingCount == 0) {
            $isDefault = 1;
        }

        // Nếu set làm mặc định, bỏ mặc định các địa chỉ khác
        if ($isDefault == 1) {
            $this->unsetAllDefault($userId);
        }

        $sql = "INSERT INTO user_addresses 
                (user_id, recipient_name, phone, province, district, ward, 
                 address_detail, address_type, is_default, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $params = [
            $userId,
            $data['recipient_name'],
            $data['phone'],
            $data['province'],
            $data['district'],
            $data['ward'],
            $data['address_detail'],
            $data['address_type'] ?? 'home',
            $isDefault
        ];

        return $this->db->insert($sql, $params);
    }

    /**
     * Cập nhật địa chỉ
     * @param int $addressId
     * @param int $userId
     * @param array $data
     * @return bool
     */
    public function update($addressId, $userId, $data)
    {
        // Kiểm tra quyền sở hữu
        $address = $this->getById($addressId, $userId);
        if (!$address) {
            return false;
        }

        $isDefault = isset($data['is_default']) ? (int)$data['is_default'] : $address['is_default'];

        // Nếu set làm mặc định, bỏ mặc định các địa chỉ khác
        if ($isDefault == 1 && $address['is_default'] == 0) {
            $this->unsetAllDefault($userId);
        }

        $sql = "UPDATE user_addresses 
                SET recipient_name = ?, 
                    phone = ?, 
                    province = ?, 
                    district = ?, 
                    ward = ?, 
                    address_detail = ?, 
                    address_type = ?, 
                    is_default = ?,
                    updated_at = NOW()
                WHERE id = ? AND user_id = ?";

        $params = [
            $data['recipient_name'],
            $data['phone'],
            $data['province'],
            $data['district'],
            $data['ward'],
            $data['address_detail'],
            $data['address_type'] ?? 'home',
            $isDefault,
            $addressId,
            $userId
        ];

        return $this->db->execute($sql, $params);
    }

    /**
     * Đặt địa chỉ làm mặc định
     * @param int $addressId
     * @param int $userId
     * @return bool
     */
    public function setDefault($addressId, $userId)
    {
        // Kiểm tra quyền sở hữu
        $address = $this->getById($addressId, $userId);
        if (!$address) {
            return false;
        }

        // Bỏ mặc định tất cả địa chỉ khác
        $this->unsetAllDefault($userId);

        // Set địa chỉ này làm mặc định
        $sql = "UPDATE user_addresses 
                SET is_default = 1, updated_at = NOW() 
                WHERE id = ? AND user_id = ?";
        return $this->db->execute($sql, [$addressId, $userId]);
    }

    /**
     * Xóa địa chỉ
     * @param int $addressId
     * @param int $userId
     * @return bool
     */
    public function delete($addressId, $userId)
    {
        // Kiểm tra quyền sở hữu
        $address = $this->getById($addressId, $userId);
        if (!$address) {
            return false;
        }

        // Đếm tổng số địa chỉ
        $totalAddresses = $this->db->fetchOne(
            "SELECT COUNT(*) as total FROM user_addresses WHERE user_id = ?",
            [$userId]
        )['total'];

        // Nếu là địa chỉ mặc định và còn địa chỉ khác, set địa chỉ khác làm mặc định
        if ($address['is_default'] == 1 && $totalAddresses > 1) {
            // Lấy địa chỉ khác mới nhất
            $newDefault = $this->db->fetchOne(
                "SELECT id FROM user_addresses 
                 WHERE user_id = ? AND id != ? 
                 ORDER BY created_at DESC LIMIT 1",
                [$userId, $addressId]
            );

            if ($newDefault) {
                $this->setDefault($newDefault['id'], $userId);
            }
        }

        // Xóa địa chỉ
        $sql = "DELETE FROM user_addresses WHERE id = ? AND user_id = ?";
        return $this->db->execute($sql, [$addressId, $userId]);
    }

    /**
     * Bỏ mặc định tất cả địa chỉ của user
     * @param int $userId
     * @return bool
     */
    private function unsetAllDefault($userId)
    {
        $sql = "UPDATE user_addresses SET is_default = 0 WHERE user_id = ?";
        return $this->db->execute($sql, [$userId]);
    }

    /**
     * Đếm số địa chỉ của user
     * @param int $userId
     * @return int
     */
    public function countByUserId($userId)
    {
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as total FROM user_addresses WHERE user_id = ?",
            [$userId]
        );
        return (int)$result['total'];
    }

    /**
     * Format địa chỉ đầy đủ để hiển thị
     * @param array $address
     * @return string
     */
    public static function formatFullAddress($address)
    {
        $parts = [
            $address['address_detail'],
            $address['ward'],
            $address['district'],
            $address['province']
        ];
        
        return implode(', ', array_filter($parts));
    }

    /**
     * Lấy icon theo loại địa chỉ
     * @param string $type
     * @return string
     */
    public static function getTypeIcon($type)
    {
        return $type === 'office' ? 'fa-building' : 'fa-home';
    }

    /**
     * Lấy label theo loại địa chỉ
     * @param string $type
     * @return string
     */
    public static function getTypeLabel($type)
    {
        return $type === 'office' ? 'Văn phòng' : 'Nhà riêng';
    }
}
