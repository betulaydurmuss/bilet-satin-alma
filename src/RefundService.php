<?php
require_once __DIR__ . '/Database.php';

class RefundService {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    






    public function process(int $ticketId, int $userId, float $amount): bool {
        try {
            
            $this->addCredit($userId, $amount);
            
            
            $this->record($ticketId, $userId, $amount);
            
            return true;
        } catch (Exception $e) {
            error_log("Refund process error: " . $e->getMessage());
            return false;
        }
    }
    
    






    public function record(int $ticketId, int $userId, float $amount): int {
        $this->db->execute(
            "INSERT INTO refunds (ticket_id, user_id, amount) VALUES (?, ?, ?)",
            [$ticketId, $userId, $amount]
        );
        
        return $this->db->lastInsertId();
    }
    
    





    public function addCredit(int $userId, float $amount): bool {
        return $this->db->execute(
            "UPDATE users SET credit = credit + ? WHERE id = ?",
            [$amount, $userId]
        );
    }
    
    




    public function getHistory(int $userId): array {
        return $this->db->query(
            "SELECT r.*, t.seat_number, tr.departure_city, tr.arrival_city, tr.departure_date
             FROM refunds r
             JOIN tickets t ON r.ticket_id = t.id
             JOIN trips tr ON t.trip_id = tr.id
             WHERE r.user_id = ?
             ORDER BY r.refund_date DESC",
            [$userId]
        );
    }
    
    




    public function exists(int $ticketId): bool {
        $refund = $this->db->queryOne(
            "SELECT id FROM refunds WHERE ticket_id = ?",
            [$ticketId]
        );
        
        return $refund !== null;
    }
    
    




    public function getUserBalance(int $userId): float {
        $user = $this->db->queryOne("SELECT credit FROM users WHERE id = ?", [$userId]);
        return $user ? (float)$user['credit'] : 0.0;
    }
}
?>
