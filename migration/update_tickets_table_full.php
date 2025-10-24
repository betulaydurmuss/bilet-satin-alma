<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';

$db = Database::getInstance();

try {
    $db->execute('ALTER TABLE tickets ADD COLUMN passenger_name VARCHAR(100)');
} catch (Exception $e) {
    echo "passenger_name column: " . $e->getMessage() . "\n";
}

try {
    $db->execute('ALTER TABLE tickets ADD COLUMN passenger_tc VARCHAR(11)');
} catch (Exception $e) {
    echo "passenger_tc column: " . $e->getMessage() . "\n";
}

try {
    $db->execute('ALTER TABLE tickets ADD COLUMN final_price DECIMAL(10,2)');
} catch (Exception $e) {
    echo "final_price column: " . $e->getMessage() . "\n";
}

try {
    $db->execute('ALTER TABLE tickets ADD COLUMN coupon_code VARCHAR(50)');
} catch (Exception $e) {
    echo "coupon_code column: " . $e->getMessage() . "\n";
}

try {
    $db->execute('ALTER TABLE tickets ADD COLUMN cancellation_date DATETIME');
} catch (Exception $e) {
    echo "cancellation_date column: " . $e->getMessage() . "\n";
}

echo "Tickets table updated successfully!\n";
?>