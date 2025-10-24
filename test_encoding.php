<?php
require_once 'src/config.php';
require_once 'src/Database.php';

$db = Database::getInstance();

$cities = ['İstanbul', 'Istanbul', 'Ankara', 'İzmir', 'Izmir'];

echo "Testing different city name variations:\n";

foreach ($cities as $city) {
    $count = $db->queryOne("SELECT COUNT(*) as count FROM trips WHERE departure_city = ?", [$city]);
    echo "$city: {$count['count']} trips\n";
}

echo "\nActual departure cities in DB (first 10):\n";
$results = $db->query("SELECT DISTINCT departure_city FROM trips LIMIT 10");
foreach ($results as $row) {
    echo "- " . $row['departure_city'] . "\n";
}
?>