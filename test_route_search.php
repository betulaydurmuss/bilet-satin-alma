<?php
require_once 'src/config.php';
require_once 'src/Database.php';

$db = Database::getInstance();

$from = 'Ankara';
$to = 'İstanbul';
$date = date('Y-m-d');

echo "Searching for trips from $from to $to on $date\n";

$results = $db->query(
    "SELECT t.*, c.name as company_name FROM trips t LEFT JOIN companies c ON t.company_id = c.id
    WHERE t.departure_city = ? AND t.arrival_city = ? AND t.departure_date = ?
    AND t.status = 'active' AND t.available_seats > 0
    ORDER BY t.departure_time",
    [$from, $to, $date]
);

echo "Found " . count($results) . " trips\n";

if (!empty($results)) {
    foreach ($results as $trip) {
        echo "- {$trip['departure_time']} to {$trip['arrival_time']} - {$trip['company_name']} - {$trip['price']} TL\n";
    }
} else {
    echo "No trips found for this route\n";
    
    $allResults = $db->query(
        "SELECT t.*, c.name as company_name FROM trips t LEFT JOIN companies c ON t.company_id = c.id
        WHERE t.departure_city = ? AND t.arrival_city = ?
        AND t.status = 'active' AND t.available_seats > 0
        ORDER BY t.departure_date, t.departure_time LIMIT 5",
        [$from, $to]
    );
    
    echo "Total trips for this route (any date): " . count($allResults) . "\n";
    
    if (!empty($allResults)) {
        echo "Sample trips:\n";
        foreach ($allResults as $trip) {
            echo "- {$trip['departure_date']} {$trip['departure_time']} to {$trip['arrival_time']} - {$trip['company_name']} - {$trip['price']} TL\n";
        }
    }
}
?>