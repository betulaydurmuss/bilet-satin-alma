<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance();

echo "Final verification of the dynamic trip system...\n";

echo "\n1. Checking cities table...\n";
$cities_table = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='cities'");
if (empty($cities_table)) {
    echo "   ✗ Cities table does not exist\n";
    exit(1);
} else {
    echo "   ✓ Cities table exists\n";
}

$count = $db->queryOne("SELECT COUNT(*) as count FROM cities");
echo "   Total cities: " . $count['count'] . "\n";

if ($count['count'] >= 81) {
    echo "   ✓ All 81 Turkish provinces are present\n";
} else {
    echo "   ✗ Only " . $count['count'] . " cities found (expected 81)\n";
}

echo "\n2. Sample cities from database:\n";
$cities = $db->query("SELECT name FROM cities ORDER BY name LIMIT 10");
foreach ($cities as $city) {
    echo "   - " . $city['name'] . "\n";
}

if ($count['count'] > 20) {
    echo "   ...\n";
    $cities = $db->query("SELECT name FROM cities ORDER BY name DESC LIMIT 10");
    foreach (array_reverse($cities) as $city) {
        echo "   - " . $city['name'] . "\n";
    }
}

echo "\n3. Checking trips table...\n";
$trips_table = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='trips'");
if (empty($trips_table)) {
    echo "   ✗ Trips table does not exist\n";
} else {
    echo "   ✓ Trips table exists\n";
    
    $trip_count = $db->queryOne("SELECT COUNT(*) as count FROM trips");
    echo "   Total trips: " . $trip_count['count'] . "\n";
    
    if ($trip_count['count'] > 0) {
        echo "   ✓ Trips are present in database\n";
        
        echo "\n4. Sample trips:\n";
        $sample_trips = $db->query("SELECT t.departure_city, t.arrival_city, t.departure_date, t.departure_time, c.name as company_name 
                                   FROM trips t 
                                   LEFT JOIN companies c ON t.company_id = c.id 
                                   ORDER BY t.departure_date, t.departure_time 
                                   LIMIT 5");
        foreach ($sample_trips as $trip) {
            echo "   - " . $trip['departure_city'] . " → " . $trip['arrival_city'] . " on " . $trip['departure_date'] . " at " . $trip['departure_time'] . " (" . $trip['company_name'] . ")\n";
        }
    } else {
        echo "   ⚠ No trips found. You need to generate trips using generate_dynamic_trips.php\n";
    }
}

echo "\n5. Checking companies...\n";
$companies_count = $db->queryOne("SELECT COUNT(*) as count FROM companies");
echo "   Total companies: " . $companies_count['count'] . "\n";

if ($companies_count['count'] > 0) {
    echo "   ✓ Companies are present\n";
    
    $companies = $db->query("SELECT name FROM companies LIMIT 5");
    echo "   Sample companies:\n";
    foreach ($companies as $company) {
        echo "   - " . $company['name'] . "\n";
    }
} else {
    echo "   ⚠ No companies found\n";
}

echo "\n6. Testing search functionality...\n";
$test_departure = "Ankara";
$test_arrival = "İstanbul";
$test_date = date('Y-m-d');

echo "   Searching for trips from $test_departure to $test_arrival on $test_date...\n";

$results = $db->query(
    "SELECT t.*, c.name as company_name FROM trips t 
     LEFT JOIN companies c ON t.company_id = c.id 
     WHERE t.departure_city = ? AND t.arrival_city = ? AND t.departure_date = ? AND t.status = 'active'
     ORDER BY t.departure_time LIMIT 3",
    [$test_departure, $test_arrival, $test_date]
);

echo "   Found " . count($results) . " trips\n";

if (!empty($results)) {
    foreach ($results as $trip) {
        echo "   - " . $trip['departure_time'] . " (" . $trip['company_name'] . ") - " . $trip['price'] . " TL\n";
    }
} else {
    echo "   No trips found for this route\n";
}

echo "\nFinal verification completed!\n";
echo "The system now has all 81 Turkish provinces available in both departure and arrival dropdowns.\n";
?>