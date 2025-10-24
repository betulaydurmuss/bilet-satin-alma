<?php
require_once 'src/config.php';
require_once 'src/Database.php';

$db = Database::getInstance();

$results = $db->query('SELECT DISTINCT departure_city FROM trips ORDER BY departure_city');
echo "Departure cities in database:\n";
foreach ($results as $row) {
    echo "- " . $row['departure_city'] . "\n";
}

echo "\nTotal departure cities: " . count($results) . "\n";

$results = $db->query('SELECT DISTINCT arrival_city FROM trips ORDER BY arrival_city');
echo "\nArrival cities in database:\n";
foreach ($results as $row) {
    echo "- " . $row['arrival_city'] . "\n";
}

echo "\nTotal arrival cities: " . count($results) . "\n";
?>