<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance();

echo "Testing search functionality...\n";

$departure_city = "Ankara";
$arrival_city = "İstanbul";
$date = date('Y-m-d');

echo "Searching for trips from $departure_city to $arrival_city on $date...\n";

$results = $db->query(
    "SELECT t.*, c.name as company_name FROM trips t 
     LEFT JOIN companies c ON t.company_id = c.id 
     WHERE t.departure_city = ? AND t.arrival_city = ? AND t.departure_date = ? AND t.status = 'active'
     ORDER BY t.departure_time",
    [$departure_city, $arrival_city, $date]
);

echo "Found " . count($results) . " trips:\n";

if (!empty($results)) {
    foreach (array_slice($results, 0, 5) as $trip) { // Show first 5 trips
        echo "- " . $trip['departure_time'] . " (" . $trip['company_name'] . ") - " . $trip['price'] . " TL - " . $trip['bus_plate'] . "\n";
    }
    if (count($results) > 5) {
        echo "... and " . (count($results) - 5) . " more trips\n";
    }
} else {
    echo "No trips found for this route.\n";
    
    $total_trips = $db->queryOne("SELECT COUNT(*) as count FROM trips");
    echo "Total trips in database: " . $total_trips['count'] . "\n";
    
    if ($total_trips['count'] == 0) {
        echo "No trips found in database. Please run generate_dynamic_trips.php first.\n";
    } else {
        echo "Sample trips from database:\n";
        $sample_trips = $db->query("SELECT departure_city, arrival_city, COUNT(*) as count FROM trips GROUP BY departure_city, arrival_city LIMIT 10");
        foreach ($sample_trips as $trip) {
            echo "- " . $trip['departure_city'] . " → " . $trip['arrival_city'] . " (" . $trip['count'] . " trips)\n";
        }
    }
}

echo "Search test completed.\n";
?>