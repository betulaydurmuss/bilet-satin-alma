<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = Database::getInstance();

$from = 'Ankara';
$to = 'İstanbul';
$date = '2025-10-16';

echo "<h2>Debug Information</h2>";
echo "<p>From: '$from' (length: " . strlen($from) . ")</p>";
echo "<p>To: '$to' (length: " . strlen($to) . ")</p>";
echo "<p>Date: '$date'</p>";

echo "<p>From (UTF-8): '" . mb_convert_encoding($from, 'UTF-8', 'UTF-8') . "'</p>";
echo "<p>To (UTF-8): '" . mb_convert_encoding($to, 'UTF-8', 'UTF-8') . "'</p>";

echo "<h3>Direct Database Query Test</h3>";
$results = $db->query(
    "SELECT t.*, c.name as company_name FROM trips t LEFT JOIN companies c ON t.company_id = c.id
    WHERE t.departure_city = ? AND t.arrival_city = ? AND t.departure_date = ?
    AND t.status = 'active' AND t.available_seats > 0
    ORDER BY t.departure_time",
    [$from, $to, $date]
);

echo "<p>Results found: " . count($results) . "</p>";

if (!empty($results)) {
    echo "<ul>";
    foreach (array_slice($results, 0, 5) as $trip) {
        echo "<li>{$trip['departure_time']} to {$trip['arrival_time']} - {$trip['company_name']} - {$trip['price']} TL</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No results found</p>";
    
    echo "<h3>Checking database for this route (any date)</h3>";
    $allResults = $db->query(
        "SELECT t.*, c.name as company_name FROM trips t LEFT JOIN companies c ON t.company_id = c.id
        WHERE t.departure_city = ? AND t.arrival_city = ?
        AND t.status = 'active' AND t.available_seats > 0
        ORDER BY t.departure_date, t.departure_time LIMIT 5",
        [$from, $to]
    );
    
    echo "<p>Total trips for this route (any date): " . count($allResults) . "</p>";
    
    if (!empty($allResults)) {
        echo "<ul>";
        foreach ($allResults as $trip) {
            echo "<li>{$trip['departure_date']} {$trip['departure_time']} to {$trip['arrival_time']} - {$trip['company_name']} - {$trip['price']} TL</li>";
        }
        echo "</ul>";
    }
}
?>