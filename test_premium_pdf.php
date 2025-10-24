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
    die("Hic bilet bulunamadi.");
}

echo "Premium PDF olusturuluyor...\n";
echo "Bilet ID: {$ticket['id']}\n";
echo "Yolcu: {$ticket['passenger_name']}\n";
echo "Rota: {$ticket['departure_city']} -> {$ticket['arrival_city']}\n\n";

$pnr = 'PNR' . str_pad($ticket['id'], 9, '0', STR_PAD_LEFT);
$departure_date = date('d.m.Y', strtotime($ticket['departure_date']));
$booking_date = date('d.m.Y H:i', strtotime($ticket['booking_date']));

$pdf = new SimplePDF();
$pdf->addPage();

$pdf->addHeader('BILETLY - Otobus Bileti');
$pdf->addSpace(8);

$pdf->addPNRBox($pnr);
$pdf->addSpace(8);

$pdf->addSectionBox('SEFER BILGILERI');
$pdf->addInfoLine('Kalkis Noktasi:', $ticket['departure_city'], true);
$pdf->addInfoLine('Varis Noktasi:', $ticket['arrival_city'], false);
$pdf->addInfoLine('Seyahat Tarihi:', $departure_date, true);
$pdf->addInfoLine('Kalkis Saati:', substr($ticket['departure_time'], 0, 5), false);
$pdf->addInfoLine('Varis Saati:', substr($ticket['arrival_time'], 0, 5), true);
$pdf->addInfoLine('Otobus Firmasi:', $ticket['company_name'], false);
$pdf->addInfoLine('Arac Plakasi:', $ticket['bus_plate'], true);
$pdf->addSpace(12);

$pdf->addSectionBox('BILET DETAYLARI');
$pdf->addInfoLine('Koltuk No:', $ticket['seat_number'], true);
$pdf->addInfoLine('Bilet Ucreti:', number_format($ticket['price'], 2, ',', '.') . ' TL', false);
$pdf->addInfoLine('Bilet Durumu:', $ticket['status'] === 'active' ? 'Aktif' : 'Iptal Edildi', true);
$pdf->addInfoLine('Rezervasyon:', $booking_date, false);
$pdf->addSpace(12);

$pdf->addSectionBox('YOLCU BILGILERI');
$pdf->addInfoLine('Yolcu Adi:', $ticket['passenger_name'], true);
$pdf->addInfoLine('TC Kimlik:', $ticket['passenger_tc'], false);
$pdf->addInfoLine('Telefon:', $ticket['passenger_phone'], true);
$pdf->addInfoLine('E-posta:', $ticket['passenger_email'], false);
$pdf->addSpace(18);

$pdf->addDivider();
$pdf->addSpace(12);

$footerLines = [
    'Bu bilet BILETLY sistemi uzerinden elektronik olarak olusturulmustur.',
    'Seyahat sirasinda yaninizda bulundurmaniz gerekmektedir.',
    'Yazdirma: ' . date('d.m.Y H:i') . ' | Bilet ID: #' . $ticket['id'],
    'Guvenli ve keyifli yolculuklar dileriz!'
];
$pdf->addFooterBox($footerLines);

$filename = 'premium_bilet_' . $pnr . '.pdf';
file_put_contents($filename, $pdf->output());

echo "\n=== BASARILI ===\n";
echo "PDF olusturuldu: $filename\n";
echo "Dosya boyutu: " . number_format(filesize($filename)) . " bytes\n";
echo "\n=== PREMIUM OZELLIKLER ===\n";
echo "- Gradient efektler\n";
echo "- Dekoratif cizgiler\n";
echo "- Renkli aksan cubuklar\n";
echo "- Bullet noktalar\n";
echo "- Kose susleri\n";
echo "- Gelismis tipografi\n";
echo "- Turkcesiz karakter (C, G, I, O, S, U)\n";
echo "\nPDF'i acarak premium tasarimi gorun!\n";
?>
