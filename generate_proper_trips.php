<?php
require_once 'src/config.php';
require_once 'src/Database.php';

$db = Database::getInstance();

echo "Generating proper trips with different times...\n";

$cityPairs = [
    ['Ankara', 'İstanbul'],
    ['İstanbul', 'Ankara'],
    ['Ankara', 'İzmir'],
    ['İzmir', 'Ankara'],
    ['İstanbul', 'İzmir'],
    ['İzmir', 'İstanbul']
];

$companyIds = [1, 2, 3, 4, 5];

$departureTimes = [
    '06:00:00', '09:00:00', '12:00:00', '15:00:00', '18:00:00', '21:00:00'
];

$dates = [];
for ($i = 0; $i <= 7; $i++) {
    $dates[] = date('Y-m-d', strtotime("+$i days"));
}

$platePrefixes = ['06', '34', '35', '16', '41'];

$tripCount = 0;

foreach ($cityPairs as $pair) {
    $departureCity = $pair[0];
    $arrivalCity = $pair[1];
    
    foreach ($dates as $date) {
        foreach ($departureTimes as $time) {
            $exists = $db->queryOne(
                "SELECT id FROM trips WHERE departure_city = ? AND arrival_city = ? AND departure_date = ? AND departure_time = ?",
                [$departureCity, $arrivalCity, $date, $time]
            );
            
            if (!$exists) {
                $companyId = $companyIds[array_rand($companyIds)];
                $travelDuration = rand(5, 10);
                $arrivalTime = date('H:i:s', strtotime($time) + ($travelDuration * 3600));
                $price = rand(200, 800);
                $platePrefix = $platePrefixes[array_rand($platePrefixes)];
                $plateSuffix = chr(rand(65, 90)) . chr(rand(65, 90)) . rand(100, 999);
                $busPlate = "$platePrefix $plateSuffix";
                
                $db->execute(
                    "INSERT INTO trips (company_id, departure_city, arrival_city, departure_date, departure_time, arrival_time, price, total_seats, available_seats, bus_plate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [$companyId, $departureCity, $arrivalCity, $date, $time, $arrivalTime, $price, 39, 39, $busPlate]
                );
                
                echo "✓ $departureCity → $arrivalCity on $date at $time created\n";
                $tripCount++;
            }
        }
    }
}

echo "\n✅ Generated $tripCount new trips!\n";

echo "\nVerifying results:\n";
$verification = $db->query("SELECT departure_city, arrival_city, departure_date, COUNT(*) as trip_count FROM trips GROUP BY departure_city, arrival_city, departure_date HAVING trip_count > 1 ORDER BY departure_date LIMIT 10");

if (!empty($verification)) {
    echo "Routes with multiple trips per day:\n";
    foreach ($verification as $route) {
        echo "- {$route['departure_city']} → {$route['arrival_city']} on {$route['departure_date']} ({$route['trip_count']} trips)\n";
    }
} else {
    echo "Need to generate more trips to have multiple times per day.\n";
}
?>