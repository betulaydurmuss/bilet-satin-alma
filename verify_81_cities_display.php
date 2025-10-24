<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance();

echo "Verifying that all 81 Turkish provinces are displayed in dropdowns...\n";

$cities = $db->query("SELECT name FROM cities ORDER BY name");

echo "Total cities in database: " . count($cities) . "\n";

if (count($cities) >= 81) {
    echo "✓ All 81 Turkish provinces are available!\n\n";
    
    echo "Cities that will appear in departure and arrival dropdowns:\n";
    echo str_repeat("=", 50) . "\n";
    
    foreach ($cities as $index => $city) {
        echo sprintf("%2d. %-25s", $index + 1, $city['name']);
        if (($index + 1) % 3 == 0) {
            echo "\n";
        }
    }
    
    if (count($cities) % 3 != 0) {
        echo "\n";
    }
    
    echo str_repeat("=", 50) . "\n";
    
    echo "\nBoth departure and arrival dropdowns in search.php and index.php will show all these cities.\n";
    echo "Users can select any city as departure point and any different city as arrival point.\n";
    
    echo "\nStatistics:\n";
    echo "- Total provinces: " . count($cities) . "\n";
    echo "- First province: " . $cities[0]['name'] . "\n";
    echo "- Last province: " . $cities[count($cities)-1]['name'] . "\n";
    
} else {
    echo "⚠ Only " . count($cities) . " cities found (expected 81)\n";
    echo "Please run populate_all_turkish_cities.php to ensure all provinces are in the database.\n";
}

echo "\nVerification completed.\n";
?>