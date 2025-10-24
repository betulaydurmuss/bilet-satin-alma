<?php
require_once __DIR__ . '/Database.php';

class CouponService {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    





    public function validate(string $code, ?int $companyId = null): ?array {
        $coupon = $this->db->queryOne(
            "SELECT * FROM coupons WHERE code = ? AND status = 'active'",
            [$code]
        );
        
        if (!$coupon) {
            return null; 
        }
        
        
        $today = date('Y-m-d');
        if ($today < $coupon['valid_from'] || $today > $coupon['valid_until']) {
            return null; 
        }
        
        
        if ($coupon['max_uses'] && $coupon['current_uses'] >= $coupon['max_uses']) {
            return null; 
        }
        
        
        if ($coupon['company_id'] && $companyId && $coupon['company_id'] != $companyId) {
            return null; 
        }
        
        return $coupon;
    }
    
    





    public function calculateDiscount(float $price, array $coupon): float {
        if ($coupon['discount_type'] === 'percentage') {
            return $price * ($coupon['discount_value'] / 100);
        } else {
            
            return min($coupon['discount_value'], $price);
        }
    }
    
    




    public function recordUsage(int $couponId): bool {
        return $this->db->execute(
            "UPDATE coupons SET current_uses = current_uses + 1 WHERE id = ?",
            [$couponId]
        );
    }
    
    




    public function getAll(?int $companyId = null): array {
        if ($companyId === null) {
            
            return $this->db->query(
                "SELECT c.*, co.name as company_name 
                 FROM coupons c 
                 LEFT JOIN companies co ON c.company_id = co.id 
                 ORDER BY c.created_at DESC"
            );
        } else {
            
            return $this->db->query(
                "SELECT * FROM coupons WHERE company_id = ? OR company_id IS NULL ORDER BY created_at DESC",
                [$companyId]
            );
        }
    }
    
    




    public function create(array $data): int {
        $this->db->execute(
            "INSERT INTO coupons (code, discount_type, discount_value, company_id, valid_from, valid_until, max_uses, status) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['code'],
                $data['discount_type'],
                $data['discount_value'],
                $data['company_id'] ?? null,
                $data['valid_from'],
                $data['valid_until'],
                $data['max_uses'] ?? null,
                $data['status'] ?? 'active'
            ]
        );
        
        return $this->db->lastInsertId();
    }
    
    





    public function update(int $id, array $data): bool {
        return $this->db->execute(
            "UPDATE coupons SET 
             code = ?, discount_type = ?, discount_value = ?, 
             valid_from = ?, valid_until = ?, max_uses = ?, status = ?
             WHERE id = ?",
            [
                $data['code'],
                $data['discount_type'],
                $data['discount_value'],
                $data['valid_from'],
                $data['valid_until'],
                $data['max_uses'] ?? null,
                $data['status'] ?? 'active',
                $id
            ]
        );
    }
    
    




    public function delete(int $id): bool {
        return $this->db->execute(
            "UPDATE coupons SET status = 'inactive' WHERE id = ?",
            [$id]
        );
    }
    
    




    public function getById(int $id): ?array {
        return $this->db->queryOne("SELECT * FROM coupons WHERE id = ?", [$id]);
    }
}
?>
