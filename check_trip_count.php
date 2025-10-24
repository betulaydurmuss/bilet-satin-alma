<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance();

$count = $db->queryOne("SELECT COUNT(*) as count FROM trips");
echo "Mevcut sefer sayısı: " . $count['count'] . "\n";

$sample = $db->query("SELECT departure_city, arrival_city, COUNT(*) as trip_count 
                      FROM trips 
                      GROUP BY departure_city, arrival_city 
                      LIMIT 5");

echo "\nÖrnek rotalar:\n";
foreach ($sample as $route) {
    echo "- {$route['departure_city']} → {$route['arrival_city']}: {$route['trip_count']} sefer\n";
}
?>
