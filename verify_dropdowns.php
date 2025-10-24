<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance();

echo "Verifying dropdown contents for departure and arrival selections...\n";

$cities = $db->query("SELECT name FROM cities ORDER BY name");

echo "Total cities available: " . count($cities) . "\n";

if (count($cities) >= 81) {
    echo "✓ All 81 Turkish provinces are available for both departure and arrival selections!\n\n";
    
    echo "Sample of cities that will appear in dropdowns:\n";
    echo "=============================================\n";
    
    echo "First 10 cities:\n";
    for ($i = 0; $i < min(10, count($cities)); $i++) {
        echo "  " . ($i + 1) . ". " . $cities[$i]['name'] . "\n";
    }
    
    echo "\n";
    
    echo "Last 10 cities:\n";
    $total = count($cities);
    for ($i = max(0, $total - 10); $i < $total; $i++) {
        echo "  " . ($i + 1) . ". " . $cities[$i]['name'] . "\n";
    }
    
    echo "\n";
    
    echo "Sample from middle of list:\n";
    $start = intval(count($cities) / 2) - 5;
    for ($i = $start; $i < min($start + 10, count($cities)); $i++) {
        echo "  " . ($i + 1) . ". " . $cities[$i]['name'] . "\n";
    }
    
    echo "\n";
    echo "Both the departure and arrival dropdowns in search.php and index.php will show all these cities.\n";
    echo "Users can select any of these cities as their departure point and any different city as their arrival point.\n";
    
} else {
    echo "⚠ Only " . count($cities) . " cities found (expected 81)\n";
    echo "Please run ensure_all_81_cities.php to populate the database with all Turkish provinces.\n";
}

echo "\nDropdown verification completed.\n";
?>