<?php
require_once __DIR__ . '/src/config.php';
require_once __DIR__ . '/src/Database.php';
require_once __DIR__ . '/libs/SimplePDF.php';

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
    die("Hiç bilet bulunamadı.");
}

echo "Modern PDF oluşturuluyor...\n";
echo "Bilet ID: {$ticket['id']}\n";
echo "Yolcu: {$ticket['passenger_name']}\n";
echo "Rota: {$ticket['departure_city']} → {$ticket['arrival_city']}\n\n";

$pnr = 'PNR' . str_pad($ticket['id'], 9, '0', STR_PAD_LEFT);
$departure_date = date('d.m.Y', strtotime($ticket['departure_date']));
$booking_date = date('d.m.Y H:i', strtotime($ticket['booking_date']));

$pdf = new SimplePDF();
$pdf->addPage();

$pdf->addHeader('BILETLY - Otobus Bileti');
$pdf->addSpace(5);

$pdf->addPNRBox($pnr);
$pdf->addSpace(5);

$pdf->addSectionBox('SEFER BILGILERI');
$pdf->addInfoLine('Kalkis:', $ticket['departure_city'], true);
$pdf->addInfoLine('Varis:', $ticket['arrival_city'], false);
$pdf->addInfoLine('Tarih:', $departure_date, true);
$pdf->addInfoLine('Kalkis Saati:', substr($ticket['departure_time'], 0, 5), false);
$pdf->addInfoLine('Varis Saati:', substr($ticket['arrival_time'], 0, 5), true);
$pdf->addInfoLine('Firma:', $ticket['company_name'], false);
$pdf->addInfoLine('Otobus Plakasi:', $ticket['bus_plate'], true);
$pdf->addSpace(10);

$pdf->addSectionBox('BILET DETAYLARI');
$pdf->addInfoLine('Koltuk Numarasi:', $ticket['seat_number'], true);
$pdf->addInfoLine('Fiyat:', number_format($ticket['price'], 2, ',', '.') . ' TL', false);
$pdf->addInfoLine('Durum:', $ticket['status'] === 'active' ? 'Aktif' : 'Iptal', true);
$pdf->addInfoLine('Rezervasyon Tarihi:', $booking_date, false);
$pdf->addSpace(10);

$pdf->addSectionBox('YOLCU BILGILERI');
$pdf->addInfoLine('Ad Soyad:', $ticket['passenger_name'], true);
$pdf->addInfoLine('TC Kimlik No:', $ticket['passenger_tc'], false);
$pdf->addInfoLine('Telefon:', $ticket['passenger_phone'], true);
$pdf->addInfoLine('E-posta:', $ticket['passenger_email'], false);
$pdf->addSpace(15);

$pdf->addDivider();
$pdf->addSpace(10);

$footerLines = [
    'Bu bilet BILETLY sistemi uzerinden elektronik olarak olusturulmustur.',
    'Herhangi bir imza veya kase gerektirmez.',
    'Yazdirma Tarihi: ' . date('d.m.Y H:i') . ' | Bilet ID: ' . $ticket['id'],
    'Iyi yolculuklar dileriz!'
];
$pdf->addFooterBox($footerLines);

$filename = 'modern_bilet_' . $pnr . '.pdf';
file_put_contents($filename, $pdf->output());

echo "✅ Modern PDF olusturuldu: $filename\n";
echo "📊 Dosya boyutu: " . filesize($filename) . " bytes\n";
echo "\n🎨 Modern Ozellikler:\n";
echo "  - Turuncu header (arka plan)\n";
echo "  - Mavi PNR kutusu\n";
echo "  - Mor bolum basliklari\n";
echo "  - Zebra cizgili satırlar\n";
echo "  - Turuncu footer kutusu\n";
echo "  - Ayırıcı cizgiler\n";
echo "\nPDF'i acarak modern tasarimi gorun!\n";
?>
