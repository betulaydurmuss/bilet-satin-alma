<?php

if (!isset($_GET['run']) && php_sapi_name() !== 'cli') {
    die('Access denied. This script can only be run from the command line or with ?run=1 parameter.');
}

require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';

$db = Database::getInstance();

echo "<pre>";
try {
    $trips = $db->query("SELECT id, total_seats, available_seats FROM trips");
    
    foreach ($trips as $trip) {
        if ($trip['total_seats'] == 40) {
            $newAvailable = min(39, $trip['available_seats']);
            
            $db->execute(
                "UPDATE trips SET total_seats = 39, available_seats = ? WHERE id = ?", 
                [39, $trip['id']]
            );
            
            echo "✓ Updated trip ID {$trip['id']} from 40 to 39 seats\n";
        } elseif ($trip['total_seats'] == 45) {
            $newAvailable = min(39, $trip['available_seats']);
            
            $db->execute(
                "UPDATE trips SET total_seats = 39, available_seats = ? WHERE id = ?", 
                [$newAvailable, $trip['id']]
            );
            
            echo "✓ Updated trip ID {$trip['id']} from 45 to 39 seats\n";
        } else {
            if ($trip['available_seats'] > 39) {
                $db->execute(
                    "UPDATE trips SET available_seats = 39 WHERE id = ?", 
                    [$trip['id']]
                );
                echo "✓ Adjusted available seats for trip ID {$trip['id']} to 39\n";
            } else {
                echo "- Trip ID {$trip['id']} already has {$trip['total_seats']} seats, no change needed\n";
            }
        }
    }
    
    echo "\n==============================================\n";
    echo "ALL TRIPS UPDATED SUCCESSFULLY!\n";
    echo "==============================================\n";
    
} catch (PDOException $e) {
    die("✗ Error: " . $e->getMessage() . "\n");
}
echo "</pre>";
?>