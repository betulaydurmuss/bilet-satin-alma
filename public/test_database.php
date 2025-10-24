<?php

require_once __DIR__ . '/../src/config.php';


require_once __DIR__ . '/../src/Database.php';

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veritabanı Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
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
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #007bff;
            color: white;
        }
        tr:hover {
            background: #f5f5f5;
        }
        h2 {
            color: #333;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
    </style>
</head>
<body>
    <h1>🔍 Veritabanı Bağlantı Testi</h1>
    
    <?php
    try {
        
        $db = Database::getInstance();
        echo '<div class="success">✓ Veritabanı bağlantısı başarılı!</div>';
        
        
        echo '<h2>📋 Veritabanı Tabloları</h2>';
        $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
        
        if (count($tables) > 0) {
            echo '<div class="info">';
            echo 'Toplam ' . count($tables) . ' tablo bulundu:<br>';
            foreach ($tables as $table) {
                echo '• ' . $table['name'] . '<br>';
            }
            echo '</div>';
        }
        
        
        echo '<h2>👥 Kullanıcılar</h2>';
        $users = $db->query("SELECT id, username, email, full_name, role, credit FROM users");
        
        if (count($users) > 0) {
            echo '<table>';
            echo '<tr><th>ID</th><th>Kullanıcı Adı</th><th>E-posta</th><th>Ad Soyad</th><th>Rol</th><th>Kredi</th></tr>';
            foreach ($users as $user) {
                echo '<tr>';
                echo '<td>' . $user['id'] . '</td>';
                echo '<td>' . $user['username'] . '</td>';
                echo '<td>' . $user['email'] . '</td>';
                echo '<td>' . $user['full_name'] . '</td>';
                echo '<td><strong>' . $user['role'] . '</strong></td>';
                echo '<td>' . number_format($user['credit'], 2) . ' TL</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<div class="info">Henüz kullanıcı kaydı bulunmuyor.</div>';
        }
        
        
        echo '<h2>🚌 Otobüs Firmaları</h2>';
        $companies = $db->query("SELECT * FROM companies");
        
        if (count($companies) > 0) {
            echo '<table>';
            echo '<tr><th>ID</th><th>Firma Adı</th><th>Telefon</th><th>E-posta</th></tr>';
            foreach ($companies as $company) {
                echo '<tr>';
                echo '<td>' . $company['id'] . '</td>';
                echo '<td><strong>' . $company['name'] . '</strong></td>';
                echo '<td>' . $company['phone'] . '</td>';
                echo '<td>' . $company['email'] . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<div class="info">Henüz firma kaydı bulunmuyor.</div>';
        }
        
        
        echo '<h2>🚍 Seferler</h2>';
        $trips = $db->query("
            SELECT t.*, c.name as company_name 
            FROM trips t 
            LEFT JOIN companies c ON t.company_id = c.id
            ORDER BY t.departure_date, t.departure_time
        ");
        
        if (count($trips) > 0) {
            echo '<table>';
            echo '<tr><th>ID</th><th>Firma</th><th>Güzergah</th><th>Tarih</th><th>Saat</th><th>Fiyat</th><th>Koltuk</th></tr>';
            foreach ($trips as $trip) {
                echo '<tr>';
                echo '<td>' . $trip['id'] . '</td>';
                echo '<td>' . $trip['company_name'] . '</td>';
                echo '<td><strong>' . $trip['departure_city'] . ' → ' . $trip['arrival_city'] . '</strong></td>';
                echo '<td>' . date('d.m.Y', strtotime($trip['departure_date'])) . '</td>';
                echo '<td>' . date('H:i', strtotime($trip['departure_time'])) . '</td>';
                echo '<td>' . number_format($trip['price'], 2) . ' TL</td>';
                echo '<td>' . $trip['available_seats'] . '/' . $trip['total_seats'] . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<div class="info">Henüz sefer kaydı bulunmuyor.</div>';
        }
        
        
        echo '<h2>🎫 Biletler</h2>';
        $tickets = $db->query("
            SELECT t.*, u.username, tr.departure_city, tr.arrival_city, tr.departure_date
            FROM tickets t
            LEFT JOIN users u ON t.user_id = u.id
            LEFT JOIN trips tr ON t.trip_id = tr.id
            ORDER BY t.booking_date DESC
        ");
        
        if (count($tickets) > 0) {
            echo '<table>';
            echo '<tr><th>ID</th><th>Kullanıcı</th><th>Güzergah</th><th>Tarih</th><th>Koltuk</th><th>Fiyat</th><th>Durum</th></tr>';
            foreach ($tickets as $ticket) {
                echo '<tr>';
                echo '<td>' . $ticket['id'] . '</td>';
                echo '<td>' . $ticket['username'] . '</td>';
                echo '<td><strong>' . $ticket['departure_city'] . ' → ' . $ticket['arrival_city'] . '</strong></td>';
                echo '<td>' . date('d.m.Y', strtotime($ticket['departure_date'])) . '</td>';
                echo '<td>' . $ticket['seat_number'] . '</td>';
                echo '<td>' . number_format($ticket['price'], 2) . ' TL</td>';
                echo '<td>' . $ticket['status'] . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<div class="info">Henüz bilet kaydı bulunmuyor.</div>';
        }
        
        
        echo '<h2>🎟️ İndirim Kuponları</h2>';
        $coupons = $db->query("SELECT * FROM coupons WHERE status = 'active'");
        
        if (count($coupons) > 0) {
            echo '<table>';
            echo '<tr><th>Kod</th><th>İndirim</th><th>Kullanım</th><th>Son Kullanma</th><th>Durum</th></tr>';
            foreach ($coupons as $coupon) {
                echo '<tr>';
                echo '<td><strong>' . $coupon['code'] . '</strong></td>';
                echo '<td>%' . $coupon['discount_rate'] . '</td>';
                echo '<td>' . $coupon['used_count'] . '/' . $coupon['usage_limit'] . '</td>';
                echo '<td>' . date('d.m.Y', strtotime($coupon['expiry_date'])) . '</td>';
                echo '<td>' . $coupon['status'] . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<div class="info">Henüz kupon kaydı bulunmuyor.</div>';
        }
        
        echo '<div class="success">';
        echo '<strong>✓ Tüm testler başarılı!</strong><br>';
        echo 'Veritabanı düzgün çalışıyor ve veriler doğru şekilde yüklendi.';
        echo '</div>';
        
    } catch (Exception $e) {
        echo '<div class="error">✗ Hata: ' . $e->getMessage() . '</div>';
    }
    ?>
    
    <div style="margin-top: 30px; padding: 20px; background: white; border-radius: 5px;">
        <h3>📝 Sonraki Adımlar:</h3>
        <ol>
            <li>Kullanıcı giriş sistemi (login/register)</li>
            <li>Ana sayfa ve sefer arama</li>
            <li>Bilet satın alma</li>
            <li>Admin panelleri</li>
        </ol>
    </div>
</body>
</html>