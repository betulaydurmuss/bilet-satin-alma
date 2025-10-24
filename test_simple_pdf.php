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

echo "Bilet bulundu: ID {$ticket['id']}\n";
echo "Yolcu: {$ticket['passenger_name']}\n";
echo "Rota: {$ticket['departure_city']} → {$ticket['arrival_city']}\n\n";

$pnr = 'PNR' . str_pad($ticket['id'], 9, '0', STR_PAD_LEFT);
$departure_date = date('d.m.Y', strtotime($ticket['departure_date']));
$booking_date = date('d.m.Y H:i', strtotime($ticket['booking_date']));

$pdf = new SimplePDF();
$pdf->addPage();

$pdf->addTitle('BILETLY - Bilet Rezervasyon Sistemi');
$pdf->addSpace(10);

$pdf->addSectionTitle('BILET BILGILERI');
$pdf->addLine('PNR Kodu:', $pnr);
$pdf->addLine('Kalkis Noktasi:', $ticket['departure_city']);
$pdf->addLine('Varis Noktasi:', $ticket['arrival_city']);
$pdf->addLine('Tarih:', $departure_date);
$pdf->addLine('Kalkis Saati:', substr($ticket['departure_time'], 0, 5));
$pdf->addLine('Varis Saati:', substr($ticket['arrival_time'], 0, 5));
$pdf->addLine('Firma:', $ticket['company_name']);
$pdf->addLine('Otobus Plakasi:', $ticket['bus_plate']);
$pdf->addLine('Koltuk Numarasi:', $ticket['seat_number']);
$pdf->addLine('Fiyat:', number_format($ticket['price'], 2, ',', '.') . ' TL');
$pdf->addLine('Durum:', $ticket['status'] === 'active' ? 'Aktif' : 'Iptal');
$pdf->addSpace(15);

$pdf->addSectionTitle('YOLCU BILGILERI');
$pdf->addLine('Ad Soyad:', $ticket['passenger_name']);
$pdf->addLine('TC Kimlik No:', $ticket['passenger_tc']);
$pdf->addLine('Telefon:', $ticket['passenger_phone']);
$pdf->addLine('E-posta:', $ticket['passenger_email']);
$pdf->addSpace(15);

$pdf->addSectionTitle('REZERVASYON BILGILERI');
$pdf->addLine('Rezervasyon Tarihi:', $booking_date);
$pdf->addLine('Bilet ID:', $ticket['id']);
$pdf->addSpace(30);

$pdf->addFooter('Bu bilet BILETLY sistemi uzerinden elektronik olarak olusturulmustur.');
$pdf->addFooter('Herhangi bir imza veya kase gerektirmez.');
$pdf->addFooter('Yazdirma Tarihi: ' . date('d.m.Y H:i'));
$pdf->addFooter('Iyi yolculuklar dileriz!');

$filename = 'test_simple_bilet_' . $pnr . '.pdf';
file_put_contents($filename, $pdf->output());

echo "PDF olusturuldu: $filename\n";
echo "Dosya boyutu: " . filesize($filename) . " bytes\n";
echo "\nPDF'i acarak icerigini kontrol edin.\n";
?>
