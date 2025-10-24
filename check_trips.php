<?php
require_once 'src/config.php';
require_once 'src/Database.php';

$db = Database::getInstance();

echo "=== Sefer Kontrolü ===\n\n";

$trips = $db->query("SELECT t.*, c.name as company_name FROM trips t LEFT JOIN companies c ON t.company_id = c.id ORDER BY t.departure_city, t.arrival_city, t.departure_date, t.departure_time");

echo "Toplam sefer sayısı: " . count($trips) . "\n\n";

$routes = [];
foreach ($trips as $trip) {
    $route = $trip['departure_city'] . ' → ' . $trip['arrival_city'];
    if (!isset($routes[$route])) {
        $routes[$route] = [];
    }
    $routes[$route][] = $trip;
}

echo "=== Rota Bazında Sefer Sayıları ===\n\n";
foreach ($routes as $route => $route_trips) {
    echo "$route: " . count($route_trips) . " sefer\n";
    foreach ($route_trips as $trip) {
        echo "  - {$trip['company_name']} | {$trip['departure_date']} {$trip['departure_time']} | Fiyat: {$trip['price']} TL\n";
    }
    echo "\n";
}

echo "=== Popüler Rotalar Kontrolü ===\n\n";
$popular_routes = [
    ['from' => 'İstanbul', 'to' => 'Ankara'],
    ['from' => 'İstanbul', 'to' => 'İzmir'],
    ['from' => 'Ankara', 'to' => 'İstanbul'],
];

$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));

foreach ($popular_routes as $route) {
    $route_trips = $db->query(
        "SELECT t.*, c.name as company_name 
         FROM trips t 
         LEFT JOIN companies c ON t.company_id = c.id
         WHERE t.departure_city = ? 
         AND t.arrival_city = ? 
         AND t.departure_date IN (?, ?)
         AND t.status = 'active'
         ORDER BY t.departure_date, t.departure_time",
        [$route['from'], $route['to'], $today, $tomorrow]
    );
    
    echo "{$route['from']} → {$route['to']}: " . count($route_trips) . " sefer (bugün ve yarın)\n";
    foreach ($route_trips as $trip) {
        echo "  - {$trip['company_name']} | {$trip['departure_date']} {$trip['departure_time']}\n";
    }
    echo "\n";
}
?>
