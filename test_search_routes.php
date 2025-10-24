<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance();

echo "=== SEFER ARAMA TESTİ ===\n\n";

$test_routes = [
    ['from' => 'İstanbul', 'to' => 'Ankara'],
    ['from' => 'İzmir', 'to' => 'Antalya'],
    ['from' => 'Adana', 'to' => 'Van'],
    ['from' => 'Trabzon', 'to' => 'Diyarbakır'],
    ['from' => 'Bursa', 'to' => 'Erzurum']
];

$today = date('Y-m-d');

foreach ($test_routes as $route) {
    $from = $route['from'];
    $to = $route['to'];
    
    echo "Rota: $from → $to (Tarih: $today)\n";
    echo str_repeat("-", 60) . "\n";
    
    $trips = $db->query(
        "SELECT t.*, c.name as company_name 
         FROM trips t 
         LEFT JOIN companies c ON t.company_id = c.id
         WHERE t.departure_city = ? 
         AND t.arrival_city = ? 
         AND t.departure_date = ?
         AND t.status = 'active'
         ORDER BY t.departure_time",
        [$from, $to, $today]
    );
    
    if (count($trips) > 0) {
        echo "✓ " . count($trips) . " sefer bulundu:\n";
        foreach ($trips as $trip) {
            echo sprintf(
                "  • %s - %s | %s | %s TL | Koltuk: %d/%d\n",
                $trip['departure_time'],
                $trip['arrival_time'],
                $trip['company_name'],
                $trip['price'],
                $trip['available_seats'],
                $trip['total_seats']
            );
        }
    } else {
        echo "✗ Sefer bulunamadı!\n";
    }
    
    echo "\n";
}

echo "=== GENEL İSTATİSTİKLER ===\n";
$total = $db->queryOne("SELECT COUNT(*) as count FROM trips");
echo "Toplam sefer sayısı: " . number_format($total['count']) . "\n";

$routes = $db->queryOne("SELECT COUNT(DISTINCT departure_city || '-' || arrival_city) as count FROM trips");
echo "Toplam rota sayısı: " . number_format($routes['count']) . "\n";

$cities = $db->queryOne("SELECT COUNT(DISTINCT departure_city) as count FROM trips");
echo "Kalkış yapan şehir sayısı: " . $cities['count'] . "\n";

$today_trips = $db->queryOne("SELECT COUNT(*) as count FROM trips WHERE departure_date = ?", [$today]);
echo "Bugün için sefer sayısı: " . number_format($today_trips['count']) . "\n";

echo "\n✅ Test tamamlandı!\n";
?>
