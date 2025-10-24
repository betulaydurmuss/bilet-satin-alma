<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';

$db = Database::getInstance();

echo "<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <title>Kupon Test</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .info { background: #d1ecf1; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .error { background: #f8d7da; padding: 15px; margin: 10px 0; border-radius: 5px; }
        table { width: 100%; background: white; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #6C63FF; color: white; }
    </style>
</head>
<body>
    <h1>🎟️ Kupon Testi</h1>";


$allCoupons = $db->query("SELECT * FROM coupons");
echo "<div class='info'>Toplam Kupon Sayısı: <strong>" . count($allCoupons) . "</strong></div>";

if (empty($allCoupons)) {
    echo "<div class='error'>❌ Veritabanında hiç kupon yok! Önce kupon eklemeniz gerekiyor.</div>";
    echo "<p><a href='insert_coupons.php' style='padding: 10px 20px; background: #6C63FF; color: white; text-decoration: none; border-radius: 5px;'>Kupon Ekle</a></p>";
} else {
    echo "<h2>Tüm Kuponlar:</h2>";
    echo "<table>
        <tr>
            <th>ID</th>
            <th>Kod</th>
            <th>Tip</th>
            <th>İndirim</th>
            <th>Durum</th>
            <th>Son Kullanma</th>
            <th>Limit</th>
            <th>Kullanım</th>
        </tr>";
    
    foreach ($allCoupons as $c) {
        echo "<tr>
            <td>{$c['id']}</td>
            <td><strong>{$c['code']}</strong></td>
            <td>{$c['discount_type']}</td>
            <td>{$c['discount_value']}</td>
            <td>{$c['status']}</td>
            <td>" . ($c['expiry_date'] ?? 'Sınırsız') . "</td>
            <td>" . ($c['usage_limit'] ?? 'Sınırsız') . "</td>
            <td>{$c['usage_count']}</td>
        </tr>";
    }
    echo "</table>";
}


$activeCoupons = $db->query("SELECT code, discount_type, discount_value, description FROM coupons WHERE status = 'active' AND (expiry_date IS NULL OR expiry_date >= CURDATE()) AND (usage_limit IS NULL OR usage_count < usage_limit) ORDER BY discount_value DESC");

echo "<h2>Aktif Kuponlar (buy_ticket.php'de gösterilecekler):</h2>";
echo "<div class='info'>Aktif Kupon Sayısı: <strong>" . count($activeCoupons) . "</strong></div>";

if (empty($activeCoupons)) {
    echo "<div class='error'>❌ Aktif kupon bulunamadı!</div>";
} else {
    echo "<table>
        <tr>
            <th>Kod</th>
            <th>Tip</th>
            <th>İndirim</th>
            <th>Açıklama</th>
        </tr>";
    
    foreach ($activeCoupons as $c) {
        $discount = $c['discount_type'] === 'percentage' 
            ? '%' . $c['discount_value'] 
            : number_format($c['discount_value'], 0) . ' ₺';
        
        echo "<tr>
            <td><strong>{$c['code']}</strong></td>
            <td>{$c['discount_type']}</td>
            <td><strong>$discount</strong></td>
            <td>{$c['description']}</td>
        </tr>";
    }
    echo "</table>";
}

echo "<br><br>
<div style='text-align: center;'>
    <a href='insert_coupons.php' style='display: inline-block; padding: 12px 24px; background: #6C63FF; color: white; text-decoration: none; border-radius: 8px; margin: 5px;'>
        ➕ Kupon Ekle
    </a>
    <a href='buy_ticket.php?trip_id=1&seat=1' style='display: inline-block; padding: 12px 24px; background: #48BB78; color: white; text-decoration: none; border-radius: 8px; margin: 5px;'>
        🎫 Bilet Satın Al
    </a>
</div>

</body>
</html>";
?>
