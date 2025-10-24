<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance();

$tickets = $db->query('SELECT t.*, tr.departure_city, tr.arrival_city, tr.departure_date, tr.departure_time FROM tickets t LEFT JOIN trips tr ON t.trip_id = tr.id LIMIT 5');

if (count($tickets) > 0) {
    echo "Found " . count($tickets) . " tickets in the database:\n";
    foreach ($tickets as $ticket) {
        echo "ID: " . $ticket['id'] . ", Route: " . $ticket['departure_city'] . " to " . $ticket['arrival_city'] . ", Date: " . $ticket['departure_date'] . ", Seat: " . $ticket['seat_number'] . "\n";
    }
} else {
    echo "No tickets found in the database.\n";
}
?>