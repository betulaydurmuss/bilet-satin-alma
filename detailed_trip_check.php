<?php
require_once 'src/config.php';
require_once 'src/Database.php';

$db = Database::getInstance();

$count = $db->queryOne('SELECT COUNT(*) as count FROM trips');
echo "Total trips: " . $count['count'] . "\n";

$routeCount = $db->queryOne('SELECT COUNT(*) as count FROM (SELECT DISTINCT departure_city, arrival_city FROM trips)');
echo "Distinct routes: " . $routeCount['count'] . "\n";

$departureCityCount = $db->queryOne('SELECT COUNT(DISTINCT departure_city) as count FROM trips');
echo "Departure cities: " . $departureCityCount['count'] . "\n";

$arrivalCityCount = $db->queryOne('SELECT COUNT(DISTINCT arrival_city) as count FROM trips');
echo "Arrival cities: " . $arrivalCityCount['count'] . "\n";

$dateCount = $db->queryOne('SELECT COUNT(DISTINCT departure_date) as count FROM trips');
echo "Distinct dates: " . $dateCount['count'] . "\n";

$timeCount = $db->queryOne('SELECT COUNT(DISTINCT departure_time) as count FROM trips');
echo "Distinct departure times: " . $timeCount['count'] . "\n";

echo "\nSample trips for Ankara -> İstanbul:\n";
$results = $db->query(
    "SELECT departure_date, departure_time, arrival_time, price, company_id, bus_plate 
     FROM trips 
     WHERE departure_city = 'Ankara' AND arrival_city = 'İstanbul' 
     ORDER BY departure_date, departure_time 
     LIMIT 10"
);

foreach ($results as $trip) {
    echo "- {$trip['departure_date']} {$trip['departure_time']} -> {$trip['arrival_time']} ({$trip['price']} TL) [Company: {$trip['company_id']}] [Plate: {$trip['bus_plate']}]\n";
}

echo "\nDeparture times for Ankara -> İstanbul on a specific date:\n";
$results = $db->query(
    "SELECT departure_time 
     FROM trips 
     WHERE departure_city = 'Ankara' AND arrival_city = 'İstanbul' AND departure_date = '2025-10-16'
     ORDER BY departure_time"
);

foreach ($results as $trip) {
    echo "- {$trip['departure_time']}\n";
}
?>