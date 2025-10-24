<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';

$db = Database::getInstance();

try {
    $db->execute('ALTER TABLE tickets ADD COLUMN passenger_phone VARCHAR(20)');
} catch (Exception $e) {
    echo "passenger_phone column: " . $e->getMessage() . "\n";
}

try {
    $db->execute('ALTER TABLE tickets ADD COLUMN passenger_email VARCHAR(100)');
} catch (Exception $e) {
    echo "passenger_email column: " . $e->getMessage() . "\n";
}

echo "Tickets table updated successfully!\n";
?>