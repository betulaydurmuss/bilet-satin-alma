<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance();

echo "Checking coupons table structure...\n\n";

$tableInfo = $db->query("PRAGMA table_info(coupons)");

echo "Columns in coupons table:\n";
foreach ($tableInfo as $column) {
    echo "  - {$column['name']} ({$column['type']})\n";
}

echo "\n";

$coupons = $db->query("SELECT * FROM coupons LIMIT 5");
echo "Sample coupons:\n";
foreach ($coupons as $coupon) {
    echo "  - {$coupon['code']}\n";
}
?>
