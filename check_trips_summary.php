<?php
require_once 'src/config.php';
require_once 'src/Database.php';

$db = Database::getInstance();

echo "=== Sefer Özeti ===\n\n";

$total = $db->queryOne("SELECT COUNT(*) as count FROM trips");
echo "Toplam sefer: {$total['count']}\n\n";

echo "=== Firmaya Göre Sefer Sayıları ===\n";
$by_company = $db->query("
    SELECT c.name, COUNT(t.id) as trip_count 
    FROM companies c 
    LEFT JOIN trips t ON c.id = t.company_id 
    GROUP BY c.id, c.name 
    ORDER BY trip_count DESC
");

foreach ($by_company as $row) {
    echo "{$row['name']}: {$row['trip_count']} sefer\n";
}

echo "\n=== Popüler Rotalar (Bugün ve Yarın) ===\n";
$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));

$popular_routes = [
    ['from' => 'İstanbul', 'to' => 'Ankara'],
    ['from' => 'İstanbul', 'to' => 'İzmir'],
    ['from' => 'Ankara', 'to' => 'İstanbul'],
];

foreach ($popular_routes as $route) {
    $count = $db->queryOne(
        "SELECT COUNT(*) as count 
         FROM trips 
         WHERE departure_city = ? 
         AND arrival_city = ? 
         AND departure_date IN (?, ?)
         AND status = 'active'",
        [$route['from'], $route['to'], $today, $tomorrow]
    );
    
    echo "{$route['from']} → {$route['to']}: {$count['count']} sefer\n";
}

echo "\n=== Örnek Seferler (İstanbul → Ankara) ===\n";
$sample = $db->query(
    "SELECT t.*, c.name as company_name 
     FROM trips t 
     LEFT JOIN companies c ON t.company_id = c.id
     WHERE t.departure_city = 'İstanbul' 
     AND t.arrival_city = 'Ankara' 
     AND t.departure_date = ?
     ORDER BY t.departure_time
     LIMIT 10",
    [$today]
);

foreach ($sample as $trip) {
    echo "{$trip['company_name']} | {$trip['departure_time']} | {$trip['price']} TL | Koltuk: {$trip['available_seats']}/{$trip['total_seats']}\n";
}
?>
