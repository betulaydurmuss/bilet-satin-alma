<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';

try {
    $db = Database::getInstance();
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
    
    echo "Database tables:\n";
    foreach ($tables as $table) {
        echo "- " . $table['name'] . "\n";
    }
    
    $ticketCount = $db->query("SELECT COUNT(*) as count FROM tickets");
    if (count($ticketCount) > 0) {
        echo "\nTickets in database: " . $ticketCount[0]['count'] . "\n";
    } else {
        echo "\nTickets table is empty or doesn't exist\n";
    }
    
    $tripCount = $db->query("SELECT COUNT(*) as count FROM trips");
    if (count($tripCount) > 0) {
        echo "Trips in database: " . $tripCount[0]['count'] . "\n";
    }
    
    $companyCount = $db->query("SELECT COUNT(*) as count FROM companies");
    if (count($companyCount) > 0) {
        echo "Companies in database: " . $companyCount[0]['count'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>