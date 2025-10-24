<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance();

$departure_city = "Ankara";
$arrival_city = "İstanbul";
$date = date('Y-m-d');

echo "Searching for trips from $departure_city to $arrival_city on $date...\n";

$trips = $db->query(
    "SELECT t.*, c.name as company_name 
     FROM trips t 
     JOIN companies c ON t.company_id = c.id 
     WHERE t.departure_city = ? 
     AND t.arrival_city = ? 
     AND t.departure_date = ? 
     AND t.status = 'active' 
     ORDER BY t.departure_time",
    [$departure_city, $arrival_city, $date]
);

echo "Found " . count($trips) . " trips:\n";

foreach ($trips as $trip) {
    echo "- " . $trip['departure_time'] . " (" . $trip['company_name'] . ") - " . $trip['price'] . " TL - Plate: " . $trip['bus_plate'] . "\n";
}

$departure_city = "İstanbul";
$arrival_city = "Ankara";
echo "\nSearching for trips from $departure_city to $arrival_city on $date...\n";

$trips = $db->query(
    "SELECT t.*, c.name as company_name 
     FROM trips t 
     JOIN companies c ON t.company_id = c.id 
     WHERE t.departure_city = ? 
     AND t.arrival_city = ? 
     AND t.departure_date = ? 
     AND t.status = 'active' 
     ORDER BY t.departure_time",
    [$departure_city, $arrival_city, $date]
);

echo "Found " . count($trips) . " trips:\n";

foreach ($trips as $trip) {
    echo "- " . $trip['departure_time'] . " (" . $trip['company_name'] . ") - " . $trip['price'] . " TL - Plate: " . $trip['bus_plate'] . "\n";
}
?>