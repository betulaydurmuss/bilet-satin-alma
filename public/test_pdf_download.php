<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = Database::getInstance();


$ticket = $db->queryOne("SELECT 
    t.id,
    t.user_id,
    t.seat_number,
    t.passenger_name,
    t.passenger_tc,
    t.passenger_phone,
    t.passenger_email,
    t.price,
    t.status,
    t.booking_date,
    tr.departure_time,
    tr.arrival_time,
    tr.departure_city,
    tr.arrival_city,
    tr.departure_date,
    tr.bus_plate,
    c.name as company_name
FROM tickets t
JOIN trips tr ON t.trip_id = tr.id
JOIN companies c ON tr.company_id = c.id
LIMIT 1");

if (!$ticket) {
    die("Hiç bilet bulunamadı. Önce bir bilet oluşturun.");
}


$_SESSION['user_id'] = $ticket['user_id'];
$_SESSION['username'] = 'Test User';

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold mb-6">PDF İndirme Testi</h1>
        
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h2 class="font-bold mb-2">Test Bileti Bilgileri:</h2>
            <p><strong>Bilet ID:</strong> <?php echo $ticket['id']; ?></p>
            <p><strong>Yolcu:</strong> <?php echo htmlspecialchars($ticket['passenger_name']); ?></p>
            <p><strong>Rota:</strong> <?php echo $ticket['departure_city']; ?> → <?php echo $ticket['arrival_city']; ?></p>
            <p><strong>Tarih:</strong> <?php echo date('d.m.Y', strtotime($ticket['departure_date'])); ?></p>
            <p><strong>Koltuk:</strong> <?php echo $ticket['seat_number']; ?></p>
            <p><strong>Fiyat:</strong> <?php echo number_format($ticket['price'], 2); ?> TL</p>
        </div>
        
        <div class="space-y-4">
            <a href="download_ticket_pdf.php?id=<?php echo $ticket['id']; ?>&download=1" 
               class="block w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-6 rounded-lg text-center"
               target="_blank">
                📥 PDF İndir (Yeni Sekme)
            </a>
            
            <a href="download_ticket_pdf.php?id=<?php echo $ticket['id']; ?>&download=1" 
               class="block w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-lg text-center"
               download="bilet_<?php echo $ticket['id']; ?>.pdf">
                💾 PDF İndir (Direkt)
            </a>
            
            <a href="download_ticket_pdf.php?id=<?php echo $ticket['id']; ?>" 
               target="_blank"
               class="block w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-lg text-center">
                👁️ PDF Önizleme (HTML)
            </a>
            
            <a href="my_account.php" 
               class="block w-full bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-6 rounded-lg text-center">
                ← Hesabıma Dön
            </a>
        </div>
        
        <div class="mt-6 bg-green-50 border border-green-200 rounded-lg p-4">
            <h3 class="font-bold text-green-800 mb-2">✅ PDF İçeriği:</h3>
            <ul class="text-sm text-green-700 space-y-1">
                <li>✓ Bilet bilgileri (PNR, rota, tarih, saat)</li>
                <li>✓ Yolcu bilgileri (ad, TC, telefon, e-posta)</li>
                <li>✓ Firma ve otobüs bilgileri</li>
                <li>✓ Koltuk numarası ve fiyat</li>
                <li>✓ Rezervasyon tarihi</li>
                <li>✓ Yazdırma tarihi</li>
            </ul>
        </div>
    </div>
</body>
</html>
