<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';

$db = Database::getInstance();

echo "<!DOCTYPE html>
<html lang='tr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Kupon Ekleme</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border: 1px solid #f5c6cb;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border: 1px solid #bee5eb;
        }
        h1 {
            color: #333;
        }
        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #6C63FF;
            color: white;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-success {
            background: #28a745;
            color: white;
        }
        .badge-danger {
            background: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <h1>🎟️ Kupon Ekleme İşlemi</h1>";


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
$errorCount = 0;

foreach ($coupons as $coupon) {
    list($code, $type, $value, $desc, $status, $expiryDays, $limit) = $coupon;
    
    
    $existing = $db->queryOne("SELECT id FROM coupons WHERE code = ?", [$code]);
    
    if ($existing) {
        echo "<div class='info'>ℹ️ Kupon zaten mevcut: <strong>$code</strong></div>";
        continue;
    }
    
    try {
        $expiryDate = $expiryDays ? date('Y-m-d', strtotime("+$expiryDays days")) : null;
        
        $sql = "INSERT INTO coupons (code, discount_type, discount_value, description, status, expiry_date, usage_limit, usage_count, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())";
        
        $result = $db->execute($sql, [$code, $type, $value, $desc, $status, $expiryDate, $limit]);
        
        if ($result) {
            echo "<div class='success'>✅ Kupon eklendi: <strong>$code</strong> - $desc</div>";
            $successCount++;
        } else {
            echo "<div class='error'>❌ Kupon eklenemedi: <strong>$code</strong></div>";
            $errorCount++;
        }
    } catch (Exception $e) {
        echo "<div class='error'>❌ Hata: <strong>$code</strong> - " . $e->getMessage() . "</div>";
        $errorCount++;
    }
}

echo "<div class='info'>
    <strong>İşlem Özeti:</strong><br>
    ✅ Başarılı: $successCount<br>
    ❌ Hatalı: $errorCount
</div>";


echo "<h2>📋 Mevcut Kuponlar</h2>";

$allCoupons = $db->query("SELECT * FROM coupons ORDER BY created_at DESC");

if (!empty($allCoupons)) {
    echo "<table>
        <thead>
            <tr>
                <th>Kod</th>
                <th>Tip</th>
                <th>İndirim</th>
                <th>Açıklama</th>
                <th>Durum</th>
                <th>Son Kullanma</th>
                <th>Limit</th>
                <th>Kullanım</th>
            </tr>
        </thead>
        <tbody>";
    
    foreach ($allCoupons as $c) {
        $statusBadge = $c['status'] === 'active' ? 'badge-success' : 'badge-danger';
        $statusText = $c['status'] === 'active' ? 'Aktif' : 'Pasif';
        
        $discountText = $c['discount_type'] === 'percentage' 
            ? '%' . $c['discount_value'] 
            : number_format($c['discount_value'], 0) . ' ₺';
        
        $expiryText = $c['expiry_date'] 
            ? date('d.m.Y', strtotime($c['expiry_date']))
            : 'Sınırsız';
        
        $limitText = $c['usage_limit'] ? $c['usage_limit'] : 'Sınırsız';
        
        echo "<tr>
            <td><strong>{$c['code']}</strong></td>
            <td>{$c['discount_type']}</td>
            <td><strong>$discountText</strong></td>
            <td>{$c['description']}</td>
            <td><span class='badge $statusBadge'>$statusText</span></td>
            <td>$expiryText</td>
            <td>$limitText</td>
            <td>{$c['usage_count']}</td>
        </tr>";
    }
    
    echo "</tbody></table>";
} else {
    echo "<div class='info'>Henüz kupon bulunmuyor.</div>";
}

echo "<br><br>
<div style='text-align: center;'>
    <a href='buy_ticket.php?trip_id=1&seat=1' style='display: inline-block; padding: 12px 24px; background: #6C63FF; color: white; text-decoration: none; border-radius: 8px; font-weight: bold;'>
        🎫 Bilet Satın Alma Sayfasına Git
    </a>
</div>

</body>
</html>";
?>
