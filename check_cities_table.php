<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance();

$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='cities'");

if (count($tables) > 0) {
    echo "Cities table exists.\n";
    $count = $db->queryOne("SELECT COUNT(*) as count FROM cities");
    echo "Number of cities: " . $count['count'] . "\n";
} else {
    echo "Cities table does not exist.\n";
}

$cities = $db->query("SELECT DISTINCT departure_city FROM trips UNION SELECT DISTINCT arrival_city FROM trips ORDER BY departure_city");
echo "Cities found in trips table: " . count($cities) . "\n";
foreach ($cities as $city) {
    echo "- " . $city['departure_city'] . "\n";
}
?>