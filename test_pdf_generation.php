<?php
require_once __DIR__ . '/libs/MiniPDF.php';

$pdf = new MiniPDF();
$pdf->addPage();

$pdf->addTitle(100, 50, 'BİLETLY - Bilet Rezervasyon Sistemi');

$pdf->addText(50, 100, '🚌 Bilet Bilgileri:', 14);
$pdf->addText(70, 125, 'PNR Kodu: PNR000000001');
$pdf->addText(70, 145, 'Kalkış Noktası: Istanbul');
$pdf->addText(70, 165, 'Varış Noktası: Ankara');
$pdf->addText(70, 185, 'Tarih: 01.01.2025');
$pdf->addText(70, 205, 'Saat: 10:00');
$pdf->addText(70, 225, 'Firma: Metro Turizm');
$pdf->addText(70, 245, 'Koltuk Numarası: 15');
$pdf->addText(70, 265, 'Fiyat: 150,00 ₺');

$pdf->addText(50, 300, '👤 Yolcu Bilgileri:', 14);
$pdf->addText(70, 325, 'Ad Soyad: Ahmet Yılmaz');
$pdf->addText(70, 345, 'TC Kimlik No: 12345678901');
$pdf->addText(70, 365, 'Telefon: 5551234567');
$pdf->addText(70, 385, 'E-posta: ahmet@example.com');

$pdf->addText(50, 425, 'Bu bilet BİLETLY sistemi üzerinden elektronik olarak oluşturulmuştur. Herhangi bir imza veya kaşe gerektirmez.', 10);
$pdf->addText(50, 455, 'Yazdırma Tarihi: 17.10.2025 10:30', 10);

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="test_bilet.pdf"');

echo $pdf->output();
exit;
?>