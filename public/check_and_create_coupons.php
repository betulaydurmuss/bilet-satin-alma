<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';

$db = Database::getInstance();

echo "<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <title>Kupon Tablosu Kontrolü</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .success { background: #d4edda; color: #155724; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; margin: 10px 0; border-radius: 5px; }
        h1 { color: #333; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .btn { display: inline-block; padding: 12px 24px; margin: 5px; text-decoration: none; border-radius: 8px; font-weight: bold; color: white; }
        .btn-primary { background: #6C63FF; }
        .btn-success { background: #48BB78; }
    </style>
</head>
<body>
    <h1>🔍 Kupon Tablosu Kontrolü ve Oluşturma</h1>";


try {
    $tableCheck = $db->query("SHOW TABLES LIKE 'coupons'");
    
    if (empty($tableCheck)) {
        echo "<div class='warning'>⚠️ 'coupons' tablosu bulunamadı. Tablo oluşturuluyor...</div>";
        
        
        $createTableSQL = "CREATE TABLE IF NOT EXISTS `coupons` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `code` varchar(50) NOT NULL,
            `discount_type` enum('percentage','fixed') NOT NULL,
            `discount_value` decimal(10,2) NOT NULL,
            `description` text,
            `status` enum('active','inactive') DEFAULT 'active',
            `expiry_date` date DEFAULT NULL,
            `usage_limit` int(11) DEFAULT NULL,
            `usage_count` int(11) DEFAULT 0,
            `company_id` int(11) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `code` (`code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        $db->execute($createTableSQL);
        echo "<div class='success'>✅ 'coupons' tablosu başarıyla oluşturuldu!</div>";
    } else {
        echo "<div class='success'>✅ 'coupons' tablosu mevcut.</div>";
    }
    
    
    $columns = $db->query("DESCRIBE coupons");
    echo "<h2>📋 Tablo Yapısı:</h2>";
    echo "<pre>";
    foreach ($columns as $col) {
        echo "{$col['Field']} - {$col['Type']} - {$col['Null']} - {$col['Key']}\n";
    }
    echo "</pre>";
    
    
    $existingCoupons = $db->query("SELECT COUNT(*) as count FROM coupons");
    $couponCount = $existingCoupons[0]['count'];
    
    echo "<div class='info'>📊 Mevcut kupon sayısı: <strong>$couponCount</strong></div>";
    
    if ($couponCount == 0) {
        echo "<div class='warning'>⚠️ Veritabanında hiç kupon yok. Örnek kuponlar ekleniyor...</div>";
        
        
        $coupons = [
            ['HOSGELDIN20', 'percentage', 20, 'Hoş geldin indirimi - %20 indirim', 'active', 30, 100],
            ['YILBASI25', 'percentage', 25, 'Yılbaşı özel - %25 indirim', 'active', 60, 50],
            ['ERKEN15', 'percentage', 15, 'Erken rezervasyon - %15 indirim', 'active', 90, 200],
            ['VIP30', 'percentage', 30, 'VIP müşteriler için - %30 indirim', 'active', 45, 20],
            ['INDIRIM50', 'fixed', 50, '50 TL indirim kuponu', 'active', 30, 150],
            ['KAMPANYA100', 'fixed', 100, '100 TL özel kampanya', 'active', 60, 75],
            ['YENI25', 'fixed', 25, 'Yeni üyeler için 25 TL', 'active', 90, 300],
            ['SUPER200', 'fixed', 200, 'Süper indirim - 200 TL', 'active', 15, 10],
            ['DAIMI10', 'percentage', 10, 'Daimi %10 indirim', 'active', null, null],
            ['OGRENCI', 'percentage', 20, 'Öğrenci indirimi - %20', 'active', null, null],
        ];
        
        $successCount = 0;
        foreach ($coupons as $coupon) {
            list($code, $type, $value, $desc, $status, $expiryDays, $limit) = $coupon;
            
            try {
                $expiryDate = $expiryDays ? date('Y-m-d', strtotime("+$expiryDays days")) : null;
                
                $sql = "INSERT INTO coupons (code, discount_type, discount_value, description, status, expiry_date, usage_limit, usage_count, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())";
                
                $result = $db->execute($sql, [$code, $type, $value, $desc, $status, $expiryDate, $limit]);
                
                if ($result) {
                    echo "<div class='success'>✅ Kupon eklendi: <strong>$code</strong></div>";
                    $successCount++;
                }
            } catch (Exception $e) {
                echo "<div class='error'>❌ Hata ($code): " . $e->getMessage() . "</div>";
            }
        }
        
        echo "<div class='success'>🎉 Toplam <strong>$successCount</strong> kupon başarıyla eklendi!</div>";
    }
    
    
    $allCoupons = $db->query("SELECT * FROM coupons ORDER BY created_at DESC");
    
    echo "<h2>📋 Tüm Kuponlar:</h2>";
    echo "<table style='width: 100%; background: white; border-collapse: collapse;'>
        <thead>
            <tr style='background: #6C63FF; color: white;'>
                <th style='padding: 10px; border: 1px solid #ddd;'>Kod</th>
                <th style='padding: 10px; border: 1px solid #ddd;'>Tip</th>
                <th style='padding: 10px; border: 1px solid #ddd;'>İndirim</th>
                <th style='padding: 10px; border: 1px solid #ddd;'>Açıklama</th>
                <th style='padding: 10px; border: 1px solid #ddd;'>Durum</th>
                <th style='padding: 10px; border: 1px solid #ddd;'>Son Kullanma</th>
            </tr>
        </thead>
        <tbody>";
    
    foreach ($allCoupons as $c) {
        $discount = $c['discount_type'] === 'percentage' 
            ? '%' . $c['discount_value'] 
            : number_format($c['discount_value'], 0) . ' ₺';
        
        $expiry = $c['expiry_date'] ? date('d.m.Y', strtotime($c['expiry_date'])) : 'Sınırsız';
        
        echo "<tr>
            <td style='padding: 10px; border: 1px solid #ddd;'><strong>{$c['code']}</strong></td>
            <td style='padding: 10px; border: 1px solid #ddd;'>{$c['discount_type']}</td>
            <td style='padding: 10px; border: 1px solid #ddd;'><strong>$discount</strong></td>
            <td style='padding: 10px; border: 1px solid #ddd;'>{$c['description']}</td>
            <td style='padding: 10px; border: 1px solid #ddd;'>{$c['status']}</td>
            <td style='padding: 10px; border: 1px solid #ddd;'>$expiry</td>
        </tr>";
    }
    
    echo "</tbody></table>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Hata: " . $e->getMessage() . "</div>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<br><br>
<div style='text-align: center;'>
    <a href='test_coupons.php' class='btn btn-primary'>🔍 Kuponları Test Et</a>
    <a href='buy_ticket.php?trip_id=1&seat=1' class='btn btn-success'>🎫 Bilet Satın Al</a>
</div>

</body>
</html>";
?>
