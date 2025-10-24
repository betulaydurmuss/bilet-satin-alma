<?php
require_once __DIR__ . '/Database.php';

class TripService {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    






    public function getCompanyTrips(int $companyId, int $page = 1, int $perPage = 20): array {
        $offset = ($page - 1) * $perPage;
        
        return $this->db->query(
            "SELECT t.*, c.name as company_name 
             FROM trips t
             JOIN companies c ON t.company_id = c.id
             WHERE t.company_id = ? AND t.status != 'deleted'
             ORDER BY t.departure_date DESC, t.departure_time DESC
             LIMIT ? OFFSET ?",
            [$companyId, $perPage, $offset]
        );
    }
    
    




    public function getCompanyTripCount(int $companyId): int {
        $result = $this->db->queryOne(
            "SELECT COUNT(*) as count FROM trips WHERE company_id = ? AND status != 'deleted'",
            [$companyId]
        );
        
        return $result ? (int)$result['count'] : 0;
    }
    
    





    public function create(array $data, int $companyId): int {
        $this->db->execute(
            "INSERT INTO trips (company_id, departure_city, arrival_city, departure_date, departure_time, arrival_time, price, total_seats, available_seats, bus_plate, status) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $companyId,
                $data['departure_city'],
                $data['arrival_city'],
                $data['departure_date'],
                $data['departure_time'],
                $data['arrival_time'],
                $data['price'],
                $data['total_seats'] ?? 39,
                $data['available_seats'] ?? 39,
                $data['bus_plate'],
                'active'
            ]
        );
        
        return $this->db->lastInsertId();
    }
    
    






    public function update(int $id, array $data, int $companyId): bool {
        
        $trip = $this->getById($id);
        if (!$trip || $trip['company_id'] != $companyId) {
            return false;
        }
        
        return $this->db->execute(
            "UPDATE trips SET 
             departure_city = ?, arrival_city = ?, departure_date = ?, 
             departure_time = ?, arrival_time = ?, price = ?, bus_plate = ?
             WHERE id = ? AND company_id = ?",
            [
                $data['departure_city'],
                $data['arrival_city'],
                $data['departure_date'],
                $data['departure_time'],
                $data['arrival_time'],
                $data['price'],
                $data['bus_plate'],
                $id,
                $companyId
            ]
        );
    }
    
    





    public function delete(int $id, int $companyId): bool {
        
        $trip = $this->getById($id);
        if (!$trip || $trip['company_id'] != $companyId) {
            return false;
        }
        
        
        if ($this->hasTickets($id)) {
            return false;
        }
        
        return $this->db->execute(
            "UPDATE trips SET status = 'cancelled' WHERE id = ? AND company_id = ?",
            [$id, $companyId]
        );
    }
    
    




    public function hasTickets(int $tripId): bool {
        $result = $this->db->queryOne(
            "SELECT COUNT(*) as count FROM tickets WHERE trip_id = ?",
            [$tripId]
        );
        
        return $result && $result['count'] > 0;
    }
    
    




    public function getById(int $id): ?array {
        return $this->db->queryOne("SELECT * FROM trips WHERE id = ?", [$id]);
    }
    
    



    public function getCities(): array {
        return $this->db->query("SELECT name FROM cities ORDER BY name");
    }
}
?>
