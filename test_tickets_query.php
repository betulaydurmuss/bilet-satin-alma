<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance();
$user_id = 5; // Using existing user ID

echo "Testing tickets query for user ID: $user_id\n";

$tickets = $db->query("SELECT 
    t.id,
    t.seat_number,
    t.passenger_name,
    t.passenger_tc,
    t.price,
    t.status,
    t.booking_date,
    tr.departure_time,
    tr.arrival_time,
    tr.departure_city,
    tr.arrival_city,
    tr.departure_date,
    tr.bus_plate,
    c.name as company_name
FROM tickets t
JOIN trips tr ON t.trip_id = tr.id
JOIN companies c ON tr.company_id = c.id
WHERE t.user_id = ?
ORDER BY t.booking_date DESC", [$user_id]);

echo "Number of tickets found: " . count($tickets) . "\n";

if (count($tickets) > 0) {
    foreach ($tickets as $ticket) {
        echo "Ticket ID: " . $ticket['id'] . ", Seat: " . $ticket['seat_number'] . ", Route: " . $ticket['departure_city'] . " → " . $ticket['arrival_city'] . ", Price: " . $ticket['price'] . "\n";
    }
} else {
    echo "No tickets found for this user.\n";
    
    $all_tickets = $db->query("SELECT * FROM tickets WHERE user_id = ?", [$user_id]);
    echo "Total tickets for user (without joins): " . count($all_tickets) . "\n";
    
    if (count($all_tickets) > 0) {
        echo "There might be an issue with the JOIN queries.\n";
        foreach ($all_tickets as $ticket) {
            echo "Ticket ID: " . $ticket['id'] . ", Trip ID: " . $ticket['trip_id'] . "\n";
            
            $trip = $db->queryOne("SELECT * FROM trips WHERE id = ?", [$ticket['trip_id']]);
            if ($trip) {
                echo "  Trip found: " . $trip['departure_city'] . " → " . $trip['arrival_city'] . "\n";
                
                $company = $db->queryOne("SELECT * FROM companies WHERE id = ?", [$trip['company_id']]);
                if ($company) {
                    echo "  Company found: " . $company['name'] . "\n";
                } else {
                    echo "  Company NOT found for company_id: " . $trip['company_id'] . "\n";
                }
            } else {
                echo "  Trip NOT found for trip_id: " . $ticket['trip_id'] . "\n";
            }
        }
    }
}
?>