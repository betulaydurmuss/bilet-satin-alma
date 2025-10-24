<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/libs/MiniPDF.php';

$db = Database::getInstance();

$ticket = $db->queryOne("SELECT 
    t.id,
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

echo "Bilet bulundu:\n";
echo "ID: " . $ticket['id'] . "\n";
echo "Yolcu: " . $ticket['passenger_name'] . "\n";
echo "Rota: " . $ticket['departure_city'] . " → " . $ticket['arrival_city'] . "\n";
echo "\nPDF oluşturuluyor...\n\n";

$pnr = 'PNR' . str_pad($ticket['id'], 9, '0', STR_PAD_LEFT);

$pdf = new MiniPDF();
$pdf->addPage();

$y = 50;
$pdf->addTitle(100, $y, 'BILETLY - Bilet Rezervasyon Sistemi');
$y += 50;

$pdf->addText(50, $y, 'Bilet Bilgileri:', 14);
$y += 25;
$pdf->addText(70, $y, 'PNR Kodu: ' . $pnr);
$y += 20;
$pdf->addText(70, $y, 'Kalkis Noktasi: ' . $ticket['departure_city']);
$y += 20;
$pdf->addText(70, $y, 'Varis Noktasi: ' . $ticket['arrival_city']);
$y += 20;
$pdf->addText(70, $y, 'Tarih: ' . date('d.m.Y', strtotime($ticket['departure_date'])));
$y += 20;
$pdf->addText(70, $y, 'Saat: ' . $ticket['departure_time']);
$y += 20;
$pdf->addText(70, $y, 'Firma: ' . $ticket['company_name']);
$y += 20;
$pdf->addText(70, $y, 'Koltuk Numarasi: ' . $ticket['seat_number']);
$y += 20;
$pdf->addText(70, $y, 'Fiyat: ' . number_format($ticket['price'], 2, ',', '.') . ' TL');
$y += 35;

$pdf->addText(50, $y, 'Yolcu Bilgileri:', 14);
$y += 25;
$pdf->addText(70, $y, 'Ad Soyad: ' . $ticket['passenger_name']);
$y += 20;
$pdf->addText(70, $y, 'TC Kimlik No: ' . $ticket['passenger_tc']);
$y += 20;
$pdf->addText(70, $y, 'Telefon: ' . $ticket['passenger_phone']);
$y += 20;
$pdf->addText(70, $y, 'E-posta: ' . $ticket['passenger_email']);
$y += 40;

$pdf->addText(50, $y, 'Bu bilet BILETLY sistemi uzerinden elektronik olarak olusturulmustur.', 10);
$y += 20;
$pdf->addText(50, $y, 'Herhangi bir imza veya kase gerektirmez.', 10);
$y += 20;
$pdf->addText(50, $y, 'Yazdirma Tarihi: ' . date('d.m.Y H:i'), 10);

$pdfContent = $pdf->output();
$filename = 'test_bilet_' . $pnr . '.pdf';
file_put_contents($filename, $pdfContent);

echo "PDF dosyasi olusturuldu: $filename\n";
echo "Dosya boyutu: " . strlen($pdfContent) . " bytes\n";
echo "\nPDF icerigi:\n";
echo "- PNR: $pnr\n";
echo "- Yolcu: {$ticket['passenger_name']}\n";
echo "- Rota: {$ticket['departure_city']} → {$ticket['arrival_city']}\n";
echo "- Tarih: " . date('d.m.Y', strtotime($ticket['departure_date'])) . "\n";
echo "- Koltuk: {$ticket['seat_number']}\n";
echo "- Fiyat: " . number_format($ticket['price'], 2, ',', '.') . " TL\n";
?>
