<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance();

echo "Debugging trip search functionality...\n";

echo "1. Checking cities table...\n";
$cities_table = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='cities'");
if (empty($cities_table)) {
    echo "   ✗ Cities table does not exist\n";
} else {
    echo "   ✓ Cities table exists\n";
    $cities_count = $db->queryOne("SELECT COUNT(*) as count FROM cities");
    echo "   Cities in database: " . $cities_count['count'] . "\n";
}

echo "2. Checking trips table...\n";
$trips_table = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='trips'");
if (empty($trips_table)) {
    echo "   ✗ Trips table does not exist\n";
} else {
    echo "   ✓ Trips table exists\n";
    $trips_count = $db->queryOne("SELECT COUNT(*) as count FROM trips");
    echo "   Trips in database: " . $trips_count['count'] . "\n";
}

echo "3. Checking companies table...\n";
$companies_table = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='companies'");
if (empty($companies_table)) {
    echo "   ✗ Companies table does not exist\n";
} else {
    echo "   ✓ Companies table exists\n";
    $companies_count = $db->queryOne("SELECT COUNT(*) as count FROM companies");
    echo "   Companies in database: " . $companies_count['count'] . "\n";
}

echo "4. Testing sample search...\n";
$departure = "Ankara";
$arrival = "İstanbul";
$date = date('Y-m-d');

echo "   Searching for trips from $departure to $arrival on $date...\n";
$results = $db->query(
    "SELECT t.*, c.name as company_name FROM trips t 
     LEFT JOIN companies c ON t.company_id = c.id 
     WHERE t.departure_city = ? AND t.arrival_city = ? AND t.departure_date = ? AND t.status = 'active'
     ORDER BY t.departure_time",
    [$departure, $arrival, $date]
);

echo "   Found " . count($results) . " trips\n";

if (!empty($results)) {
    echo "   All trips for this route:\n";
    foreach ($results as $trip) {
        echo "     - " . $trip['departure_time'] . " | " . $trip['company_name'] . " | " . $trip['price'] . " TL | Plate: " . $trip['bus_plate'] . "\n";
    }
} else {
    echo "   No trips found for this route\n";
}

echo "\n4b. Checking trip distribution by departure time...\n";
$time_distribution = $db->query(
    "SELECT departure_time, COUNT(*) as count FROM trips 
     WHERE departure_city = ? AND arrival_city = ? AND departure_date = ? AND status = 'active'
     GROUP BY departure_time
     ORDER BY departure_time",
    [$departure, $arrival, $date]
);

if (!empty($time_distribution)) {
    echo "   Time distribution:\n";
    foreach ($time_distribution as $dist) {
        echo "     - " . $dist['departure_time'] . ": " . $dist['count'] . " trip(s)\n";
    }
} else {
    echo "   No time distribution data\n";
}

echo "5. Available cities (first 10):\n";
$cities = $db->query("SELECT name FROM cities ORDER BY name LIMIT 10");
foreach ($cities as $city) {
    echo "   - " . $city['name'] . "\n";
}

echo "Debug completed.\n";
?>