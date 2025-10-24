<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance();

echo "Verifying dynamic trip system...\n";

echo "1. Checking cities table...\n";
$cities_count = $db->queryOne("SELECT COUNT(*) as count FROM cities");
echo "   Cities in database: " . $cities_count['count'] . "\n";

if ($cities_count['count'] < 81) {
    echo "   WARNING: Expected 81 cities, found " . $cities_count['count'] . "\n";
} else {
    echo "   ✓ All 81 Turkish provinces are present\n";
}

echo "2. Checking companies...\n";
$companies_count = $db->queryOne("SELECT COUNT(*) as count FROM companies");
echo "   Companies in database: " . $companies_count['count'] . "\n";

if ($companies_count['count'] == 0) {
    echo "   ✗ No companies found\n";
} else {
    echo "   ✓ Companies are present\n";
}

echo "3. Checking trips...\n";
$trips_count = $db->queryOne("SELECT COUNT(*) as count FROM trips");
echo "   Total trips in database: " . $trips_count['count'] . "\n";

if ($trips_count['count'] == 0) {
    echo "   ✗ No trips found\n";
} else {
    echo "   ✓ Trips are present\n";
    
    $sample_trips = $db->query("SELECT t.*, c.name as company_name FROM trips t JOIN companies c ON t.company_id = c.id LIMIT 5");
    echo "   Sample trips:\n";
    foreach ($sample_trips as $trip) {
        echo "     - " . $trip['departure_city'] . " → " . $trip['arrival_city'] . " on " . $trip['departure_date'] . " at " . $trip['departure_time'] . " (" . $trip['company_name'] . ") - " . $trip['price'] . " TL - " . $trip['bus_plate'] . "\n";
    }
}

echo "4. Testing search functionality...\n";
$test_departure = "Ankara";
$test_arrival = "İstanbul";
$test_date = date('Y-m-d');

echo "   Searching for trips from $test_departure to $test_arrival on $test_date...\n";
$search_results = $db->query(
    "SELECT t.*, c.name as company_name FROM trips t JOIN companies c ON t.company_id = c.id 
     WHERE t.departure_city = ? AND t.arrival_city = ? AND t.departure_date = ? AND t.status = 'active'
     ORDER BY t.departure_time LIMIT 3",
    [$test_departure, $test_arrival, $test_date]
);

echo "   Found " . count($search_results) . " trips:\n";
foreach ($search_results as $trip) {
    echo "     - " . $trip['departure_time'] . " (" . $trip['company_name'] . ") - " . $trip['price'] . " TL\n";
}

echo "Verification completed.\n";
?>