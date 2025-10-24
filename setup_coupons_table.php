<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';

$db = Database::getInstance();

echo "Kupon sistemi kuruluyor...\n\n";

try {
    $db->execute("DROP TABLE IF EXISTS coupons");
    echo "✓ Eski kupon tablosu silindi\n";
    
    $db->execute("CREATE TABLE coupons (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        code VARCHAR(50) UNIQUE NOT NULL,
        discount_type VARCHAR(20) NOT NULL,
        discount_value DECIMAL(10,2) NOT NULL,
        company_id INTEGER,
        valid_from DATE NOT NULL,
        valid_until DATE NOT NULL,
        max_uses INTEGER,
        current_uses INTEGER DEFAULT 0,
        status VARCHAR(20) DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (company_id) REFERENCES companies(id)
    )");
    echo "✓ Yeni kupon tablosu olusturuldu\n";
} catch (Exception $e) {
    echo "✗ Hata: " . $e->getMessage() . "\n";
    exit(1);
}

try {
    $db->execute("CREATE TABLE IF NOT EXISTS refunds (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ticket_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        refund_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (ticket_id) REFERENCES tickets(id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");
    echo "✓ Iade tablosu olusturuldu\n";
} catch (Exception $e) {
    echo "✗ Hata: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nOrnek kuponlar ekleniyor...\n";

$sampleCoupons = [
    [
        'code' => 'HOSGELDIN',
        'discount_type' => 'percentage',
        'discount_value' => 10,
        'company_id' => null, // Genel kupon
        'valid_from' => date('Y-m-d'),
        'valid_until' => date('Y-m-d', strtotime('+30 days')),
        'max_uses' => 100,
        'status' => 'active'
    ],
    [
        'code' => 'YILBASI2025',
        'discount_type' => 'fixed',
        'discount_value' => 50,
        'company_id' => null,
        'valid_from' => date('Y-m-d'),
        'valid_until' => date('Y-m-d', strtotime('+60 days')),
        'max_uses' => 50,
        'status' => 'active'
    ],
    [
        'code' => 'OGRENCI20',
        'discount_type' => 'percentage',
        'discount_value' => 20,
        'company_id' => null,
        'valid_from' => date('Y-m-d'),
        'valid_until' => date('Y-m-d', strtotime('+90 days')),
        'max_uses' => 200,
        'status' => 'active'
    ]
];

foreach ($sampleCoupons as $coupon) {
    try {
        $db->execute(
            "INSERT OR IGNORE INTO coupons (code, discount_type, discount_value, company_id, valid_from, valid_until, max_uses, status) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $coupon['code'],
                $coupon['discount_type'],
                $coupon['discount_value'],
                $coupon['company_id'],
                $coupon['valid_from'],
                $coupon['valid_until'],
                $coupon['max_uses'],
                $coupon['status']
            ]
        );
        echo "  ✓ {$coupon['code']} kuponu eklendi\n";
    } catch (Exception $e) {
        echo "  ✗ {$coupon['code']} eklenemedi: " . $e->getMessage() . "\n";
    }
}

$count = $db->queryOne("SELECT COUNT(*) as count FROM coupons");
echo "\nToplam kupon sayisi: " . $count['count'] . "\n";

echo "\n=== KUPON SISTEMI KURULUMU TAMAMLANDI ===\n";
echo "\nOrnek Kuponlar:\n";
echo "- HOSGELDIN: %10 indirim (Genel)\n";
echo "- YILBASI2025: 50 TL indirim (Genel)\n";
echo "- OGRENCI20: %20 indirim (Genel)\n";
echo "\nKuponlar buy_ticket.php sayfasinda kullanilabilir.\n";
?>
