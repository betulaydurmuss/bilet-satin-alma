<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance();

$user_id = 5; 
$trip_id = 1; 
$seat_number = 15;
$passenger_name = "Test User";
$passenger_tc = "12345678901";
$passenger_phone = "5551234567";
$passenger_email = "test@example.com";
$price = 350.00;
$final_price = 350.00;

echo "Inserting test ticket...\n";

try {
    $ok = $db->execute('INSERT INTO tickets (user_id, trip_id, seat_number, passenger_name, passenger_tc, passenger_phone, passenger_email, price, final_price, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
        [$user_id, $trip_id, $seat_number, $passenger_name, $passenger_tc, $passenger_phone, $passenger_email, $price, $final_price, 'active']);
    
    if ($ok) {
        echo "Ticket inserted successfully!\n";
        echo "Ticket ID: " . $db->lastInsertId() . "\n";
        
        $ticket = $db->queryOne("SELECT * FROM tickets WHERE user_id = ? AND trip_id = ? AND seat_number = ?", 
            [$user_id, $trip_id, $seat_number]);
        
        if ($ticket) {
            echo "Ticket found in database:\n";
            print_r($ticket);
        } else {
            echo "Ticket not found in database!\n";
        }
    } else {
        echo "Failed to insert ticket!\n";
    }
} catch (Exception $e) {
    echo "Error inserting ticket: " . $e->getMessage() . "\n";
}
?>