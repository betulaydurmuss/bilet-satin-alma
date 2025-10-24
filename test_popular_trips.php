<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance();

echo "=== POPÜLER SEFERLER TESTİ ===\n\n";

$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));

$popular_routes = [
    ['from' => 'İstanbul', 'to' => 'Ankara'],
    ['from' => 'İstanbul', 'to' => 'İzmir'],
    ['from' => 'Ankara', 'to' => 'İstanbul'],
    ['from' => 'İzmir', 'to' => 'İstanbul'],
    ['from' => 'Ankara', 'to' => 'Antalya'],
    ['from' => 'İstanbul', 'to' => 'Antalya']
];

$trips = [];
foreach ($popular_routes as $route) {
    $route_trips = $db->query(
        "SELECT t.*, c.name as company_name 
         FROM trips t 
         LEFT JOIN companies c ON t.company_id = c.id
         WHERE t.departure_city = ? 
         AND t.arrival_city = ? 
         AND t.departure_date IN (?, ?)
         AND t.status = 'active'
         AND t.available_seats > 0
         ORDER BY t.departure_date, t.departure_time
         LIMIT 2",
        [$route['from'], $route['to'], $today, $tomorrow]
    );
    
    if (!empty($route_trips)) {
        echo "✓ {$route['from']} → {$route['to']}: " . count($route_trips) . " sefer bulundu\n";
        foreach ($route_trips as $trip) {
            $date_label = $trip['departure_date'] == $today ? 'Bugün' : 'Yarın';
            echo "  • $date_label {$trip['departure_time']} | {$trip['company_name']} | {$trip['price']} TL\n";
        }
        $trips = array_merge($trips, $route_trips);
    } else {
        echo "✗ {$route['from']} → {$route['to']}: Sefer bulunamadı\n";
    }
    
    if (count($trips) >= 12) {
        $trips = array_slice($trips, 0, 12);
        break;
    }
    echo "\n";
}

echo "\n=== ÖZET ===\n";
echo "Toplam popüler sefer: " . count($trips) . "\n";
echo "Anasayfada gösterilecek sefer sayısı: " . min(count($trips), 12) . "\n";

echo "\n✅ Test tamamlandı!\n";
echo "Anasayfayı görmek için: http://localhost/Bilet-satın-alma/public/index.php\n";
?>
