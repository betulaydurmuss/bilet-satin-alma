<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance();

echo "Tickets table structure:\n";
$result = $db->query('PRAGMA table_info(tickets)');
foreach ($result as $row) {
    echo $row['name'] . ' (' . $row['type'] . ")\n";
}

echo "\nSample tickets data:\n";
$tickets = $db->query('SELECT * FROM tickets LIMIT 5');
if (count($tickets) > 0) {
    foreach ($tickets as $ticket) {
        echo "ID: " . $ticket['id'] . ", User: " . $ticket['user_id'] . ", Trip: " . $ticket['trip_id'] . ", Seat: " . $ticket['seat_number'] . ", Price: " . $ticket['price'] . ", Status: " . $ticket['status'] . "\n";
    }
} else {
    echo "No tickets found in the database.\n";
}
?>