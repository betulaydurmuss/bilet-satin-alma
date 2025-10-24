<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}


$ticketId = $_GET['id'] ?? null;

if (!$ticketId) {
    die('Bilet ID eksik!');
}

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];


$user = $db->queryOne("SELECT full_name FROM users WHERE id = ?", [$user_id]);


$ticket = $db->queryOne("SELECT 
    t.id,
    t.seat_number as seat,
    t.passenger_name,
    t.passenger_tc,
    t.price,
    t.status,
    t.booking_date,
    tr.departure_time,
    tr.arrival_time,
    tr.departure_city,
    tr.arrival_city,
    tr.departure_date as date,
    tr.bus_plate,
    tr.price as trip_price,
    c.name as company
FROM tickets t
JOIN trips tr ON t.trip_id = tr.id
JOIN companies c ON tr.company_id = c.id
WHERE t.id = ? AND t.user_id = ?", [$ticketId, $user_id]);

if (!$ticket) {
    die('Bilet bulunamadı!');
}


$ticket['route'] = $ticket['departure_city'] . ' – ' . $ticket['arrival_city'];
$ticket['pnr'] = 'PNR' . str_pad($ticket['id'], 9, '0', STR_PAD_LEFT);
$ticket['price_formatted'] = number_format($ticket['price'], 2, ',', '.') . ' TL';


header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="bilet_' . $ticket['pnr'] . '.pdf"');


$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Bilet - ' . $ticket['pnr'] . '</title>
    <style>
        body {
            font-family: "Inter", Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .ticket-header {
            text-align: center;
            border-bottom: 2px solid #E67E22;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .ticket-title {
            color: #E67E22;
            font-size: 24px;
            font-weight: bold;
        }
        .ticket-info {
            margin-bottom: 15px;
        }
        .info-label {
            font-weight: bold;
            color: #555;
            width: 150px;
            display: inline-block;
        }
        .info-value {
            font-weight: normal;
        }
        .ticket-details {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="ticket-header">
        <div class="ticket-title">BİLETLY</div>
        <div>Bilet Rezervasyon Sistemi</div>
    </div>
    
    <div class="ticket-details">
        <h2>BİLET DETAYLARI</h2>
        
        <div class="ticket-info">
            <span class="info-label">PNR Kodu:</span>
            <span class="info-value">' . $ticket['pnr'] . '</span>
        </div>
        
        <div class="ticket-info">
            <span class="info-label">Yolcu Adı:</span>
            <span class="info-value">' . $ticket['passenger_name'] . '</span>
        </div>
        
        <div class="ticket-info">
            <span class="info-label">T.C. Kimlik No:</span>
            <span class="info-value">' . $ticket['passenger_tc'] . '</span>
        </div>
        
        <div class="ticket-info">
            <span class="info-label">Sefer Güzergahı:</span>
            <span class="info-value">' . htmlspecialchars($ticket['route']) . '</span>
        </div>
        
        <div class="ticket-info">
            <span class="info-label">Firma:</span>
            <span class="info-value">' . $ticket['company'] . '</span>
        </div>
        
        <div class="ticket-info">
            <span class="info-label">Tarih:</span>
            <span class="info-value">' . date('d.m.Y', strtotime($ticket['date'])) . '</span>
        </div>
        
        <div class="ticket-info">
            <span class="info-label">Kalkış Saati:</span>
            <span class="info-value">' . $ticket['departure_time'] . '</span>
        </div>
        
        <div class="ticket-info">
            <span class="info-label">Varış Saati:</span>
            <span class="info-value">' . $ticket['arrival_time'] . '</span>
        </div>
        
        <div class="ticket-info">
            <span class="info-label">Koltuk Numarası:</span>
            <span class="info-value">' . $ticket['seat'] . '</span>
        </div>
        
        <div class="ticket-info">
            <span class="info-label">Plaka:</span>
            <span class="info-value">' . $ticket['bus_plate'] . '</span>
        </div>
        
        <div class="ticket-info">
            <span class="info-label">Fiyat:</span>
            <span class="info-value">' . $ticket['price_formatted'] . '</span>
        </div>
        
        <div class="ticket-info">
            <span class="info-label">Rezervasyon Tarihi:</span>
            <span class="info-value">' . date('d.m.Y H:i', strtotime($ticket['booking_date'])) . '</span>
        </div>
        
        <div class="ticket-info">
            <span class="info-label">Durum:</span>
            <span class="info-value">' . ($ticket['status'] == 'active' ? 'Aktif' : 'İptal') . '</span>
        </div>
    </div>
    
    <div class="footer">
        <p>Bu bilet Biletly Rezervasyon Sistemi tarafından oluşturulmuştur.</p>
        <p>İyi yolculuklar dileriz!</p>
    </div>
</body>
</html>
';



echo $html;
?>