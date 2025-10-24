<?php
require_once 'src/config.php';
require_once 'src/Database.php';

$db = Database::getInstance();

$cities = ['Ankara', 'İstanbul', 'İzmir', 'Bursa', 'Antalya'];

echo "Checking trips between major cities:\n";

foreach ($cities as $departure) {
    foreach ($cities as $arrival) {
        if ($departure !== $arrival) {
            echo "\n--- Trips from $departure to $arrival ---\n";
            
            $times = $db->query(
                "SELECT DISTINCT departure_time, departure_date 
                 FROM trips 
                 WHERE departure_city = ? AND arrival_city = ? 
                 ORDER BY departure_date, departure_time 
                 LIMIT 10",
                [$departure, $arrival]
            );
            
            foreach ($times as $time) {
                echo "  Date: {$time['departure_date']} Time: {$time['departure_time']}\n";
            }
            
            if (empty($times)) {
                echo "  No trips found\n";
            }
        }
    }
}

echo "\n\nChecking trip count per day for Ankara → İstanbul:\n";
$dayCounts = $db->query(
    "SELECT departure_date, COUNT(*) as trip_count 
     FROM trips 
     WHERE departure_city = 'Ankara' AND arrival_city = 'İstanbul'
     GROUP BY departure_date 
     ORDER BY departure_date"
);

foreach ($dayCounts as $day) {
    echo "  {$day['departure_date']}: {$day['trip_count']} trips\n";
}

echo "\n\nDistinct departure times for Ankara → İstanbul:\n";
$distinctTimes = $db->query(
    "SELECT DISTINCT departure_time 
     FROM trips 
     WHERE departure_city = 'Ankara' AND arrival_city = 'İstanbul'
     ORDER BY departure_time"
);

foreach ($distinctTimes as $time) {
    echo "  Time: {$time['departure_time']}\n";
}
?>