<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance();

echo "Listing all coupons...\n\n";

$coupons = $db->query("SELECT * FROM coupons ORDER BY status, discount_value DESC");

if (empty($coupons)) {
    echo "No coupons found.\n";
} else {
    foreach ($coupons as $coupon) {
        echo "Code: {$coupon['code']}\n";
        echo "  Type: {$coupon['discount_type']}\n";
        echo "  Value: {$coupon['discount_value']}\n";
        echo "  Status: {$coupon['status']}\n";
        echo "  Valid From: " . ($coupon['valid_from'] ?? 'N/A') . "\n";
        echo "  Valid Until: " . ($coupon['valid_until'] ?? 'N/A') . "\n";
        echo "  Max Uses: " . ($coupon['max_uses'] ?? 'Unlimited') . "\n";
        echo "  Current Uses: {$coupon['current_uses']}\n";
        echo "  Company ID: " . ($coupon['company_id'] ?? 'All') . "\n";
        echo "\n";
    }
    
    echo "Total coupons: " . count($coupons) . "\n";
    
    $activeCoupons = $db->query("SELECT COUNT(*) as count FROM coupons WHERE status = 'active' AND (valid_until IS NULL OR valid_until >= date('now'))");
    echo "Active coupons: " . $activeCoupons[0]['count'] . "\n";
}
?>
