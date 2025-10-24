<?php
require_once 'src/config.php';
require_once 'src/Database.php';

$db = Database::getInstance();

$from = 'Ankara';
$to = 'İstanbul';
$date = '2025-10-16';

echo "Testing search with:\n";
echo "From: $from\n";
echo "To: $to\n";
echo "Date: $date\n\n";

$results = $db->query(
    "SELECT t.*, c.name as company_name FROM trips t LEFT JOIN companies c ON t.company_id = c.id
    WHERE t.departure_city = ? AND t.arrival_city = ? AND t.departure_date = ?
    AND t.status = 'active' AND t.available_seats > 0
    ORDER BY t.departure_time",
    [$from, $to, $date]
);

echo "Found " . count($results) . " trips\n";

if (!empty($results)) {
    echo "First few results:\n";
    $count = 0;
    foreach ($results as $trip) {
        if ($count++ >= 3) break;
        echo "- {$trip['departure_time']} to {$trip['arrival_time']} - {$trip['company_name']} - {$trip['price']} TL\n";
    }
} else {
    echo "No trips found. Let's check what's in the database for this route:\n";
    
    $anyDateResults = $db->query(
        "SELECT t.*, c.name as company_name FROM trips t LEFT JOIN companies c ON t.company_id = c.id
        WHERE t.departure_city = ? AND t.arrival_city = ?
        AND t.status = 'active' AND t.available_seats > 0
        ORDER BY t.departure_date, t.departure_time LIMIT 5",
        [$from, $to]
    );
    
    echo "Total trips for this route (any date): " . count($anyDateResults) . "\n";
    
    if (!empty($anyDateResults)) {
        echo "Sample trips:\n";
        foreach ($anyDateResults as $trip) {
            echo "- {$trip['departure_date']} {$trip['departure_time']} to {$trip['arrival_time']} - {$trip['company_name']} - {$trip['price']} TL\n";
        }
    }
    
    $dateCheck = $db->query(
        "SELECT t.*, c.name as company_name FROM trips t LEFT JOIN companies c ON t.company_id = c.id
        WHERE t.departure_date = ?
        AND t.status = 'active' AND t.available_seats > 0
        ORDER BY t.departure_city, t.arrival_city LIMIT 5",
        [$date]
    );
    
    echo "\nTrips on $date (any route):\n";
    echo "Found " . count($dateCheck) . " trips\n";
    
    if (!empty($dateCheck)) {
        echo "Sample trips:\n";
        foreach ($dateCheck as $trip) {
            echo "- {$trip['departure_city']} to {$trip['arrival_city']} at {$trip['departure_time']} - {$trip['company_name']} - {$trip['price']} TL\n";
        }
    }
}
?>