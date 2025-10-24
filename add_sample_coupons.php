<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance();

echo "Adding sample coupons...\n\n";

$coupons = [
    [
        'code' => 'YILBASI2025',
        'discount_type' => 'fixed',
        'discount_value' => 50,
        'description' => 'Yılbaşı özel 50 TL indirim kuponu',
        'expiry_date' => '2025-12-31',
        'usage_limit' => 100,
        'status' => 'active'
    ],
    [
        'code' => 'OGRENCI20',
        'discount_type' => 'percentage',
        'discount_value' => 20,
        'description' => 'Öğrencilere özel %20 indirim',
        'expiry_date' => '2025-12-31',
        'usage_limit' => 200,
        'status' => 'active'
    ],
    [
        'code' => 'ILKSEFER',
        'discount_type' => 'fixed',
        'discount_value' => 30,
        'description' => 'İlk seferinize özel 30 TL indirim',
        'expiry_date' => '2025-12-31',
        'usage_limit' => 50,
        'status' => 'active'
    ],
    [
        'code' => 'YAZTATILI',
        'discount_type' => 'percentage',
        'discount_value' => 15,
        'description' => 'Yaz tatili kampanyası %15 indirim',
        'expiry_date' => '2025-08-31',
        'usage_limit' => 150,
        'status' => 'active'
    ],
    [
        'code' => 'SUPER50',
        'discount_type' => 'fixed',
        'discount_value' => 75,
        'description' => 'Süper kampanya! 75 TL indirim',
        'expiry_date' => '2025-12-31',
        'usage_limit' => 30,
        'status' => 'active'
    ]
];

$added = 0;
foreach ($coupons as $coupon) {
    $existing = $db->queryOne("SELECT id FROM coupons WHERE code = ?", [$coupon['code']]);
    
    if ($existing) {
        echo "⚠️  Kupon zaten mevcut: {$coupon['code']}\n";
        continue;
    }
    
    $result = $db->execute(
        "INSERT INTO coupons (code, discount_type, discount_value, description, expiry_date, usage_limit, usage_count, status, company_id) 
         VALUES (?, ?, ?, ?, ?, ?, 0, ?, NULL)",
        [
            $coupon['code'],
            $coupon['discount_type'],
            $coupon['discount_value'],
            $coupon['description'],
            $coupon['expiry_date'],
            $coupon['usage_limit'],
            $coupon['status']
        ]
    );
    
    if ($result) {
        echo "✅ Kupon eklendi: {$coupon['code']} - ";
        if ($coupon['discount_type'] === 'percentage') {
            echo "%{$coupon['discount_value']} indirim\n";
        } else {
            echo "{$coupon['discount_value']} TL indirim\n";
        }
        $added++;
    } else {
        echo "❌ Kupon eklenemedi: {$coupon['code']}\n";
    }
}

echo "\n✨ Toplam {$added} kupon eklendi!\n";
echo "\n🎉 Kampanyalar sayfasını ziyaret edin: http://localhost/Bilet-satın-alma/public/campaigns.php\n";
?>
