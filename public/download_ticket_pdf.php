<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../libs/SimplePDF.php';



$download = isset($_GET['download']) && $_GET['download'] == '1';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


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
    tr.price as trip_price,
    c.name as company_name
FROM tickets t
JOIN trips tr ON t.trip_id = tr.id
JOIN companies c ON tr.company_id = c.id
WHERE t.id = ? AND t.user_id = ?", [$ticketId, $user_id]);

if (!$ticket) {
    die('Bilet bulunamadı!');
}


$pnr = 'PNR' . str_pad($ticket['id'], 9, '0', STR_PAD_LEFT);


$route = (!empty($ticket['departure_city']) && !empty($ticket['arrival_city'])) ? 
    $ticket['departure_city'] . ' → ' . $ticket['arrival_city'] : 'Belirtilmemiş';
$departure_date = !empty($ticket['departure_date']) ? 
    date('d.m.Y', strtotime($ticket['departure_date'])) : 'Belirtilmemiş';
$booking_date = !empty($ticket['booking_date']) ? 
    date('d.m.Y H:i', strtotime($ticket['booking_date'])) : 'Belirtilmemiş';
$price_formatted = !empty($ticket['price']) ? 
    number_format($ticket['price'], 2, ',', '.') . ' TL' : 'Belirtilmemiş';
$status_text = $ticket['status'] === 'active' ? 'Aktif' : ($ticket['status'] === 'cancelled' ? 'İptal' : 'Belirtilmemiş');


if ($download) {
    
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="bilet_' . $pnr . '.pdf"');
    
    
    $pdf = new SimplePDF();
    $pdf->addPage();
    
    
    $pdf->addHeader('BILETLY');
    $pdf->addPNRBox($pnr);
    $pdf->addSpace(15);
    
    
    $pdf->addDivider();
    $pdf->addSpace(10);
    
    
    $pdf->addSectionBox('SEFER BILGILERI');
    $pdf->addSpace(5);
    $pdf->addInfoLine('Otobus Firmasi:', $ticket['company_name'] ?? 'Belirtilmemis', false);
    $pdf->addInfoLine('Arac Plakasi:', $ticket['bus_plate'] ?? 'Belirtilmemis', true);
    $pdf->addSpace(35);
    $pdf->addInfoLine('Seyahat Tarihi:', $departure_date, false);
    $pdf->addInfoLine('Varis Saati:', substr($ticket['arrival_time'] ?? '00:00:00', 0, 5), true);
    $pdf->addSpace(35);
    $pdf->addInfoLine('Bilet Ucreti:', number_format($ticket['price'] ?? 0, 2, ',', '.') . ' TL', false);
    $pdf->addSpace(35);
    
    
    $pdf->addDivider();
    $pdf->addSpace(10);
    
    
    $pdf->addSectionBox('BILET DETAYLARI');
    $pdf->addSpace(5);
    $pdf->addInfoLine('Bilet ID:', '#' . $ticket['id'], false);
    $pdf->addInfoLine('Ad Soyad:', $ticket['passenger_name'] ?? 'Belirtilmemis', true);
    $pdf->addSpace(35);
    $pdf->addInfoLine('Koltuk Numarasi:', $ticket['seat_number'] ?? '-', false);
    $pdf->addInfoLine('T.C. Kimlik No:', $ticket['passenger_tc'] ?? 'Belirtilmemis', true);
    $pdf->addSpace(35);
    $pdf->addInfoLine('Rezervasyon Tarihi:', $booking_date, false);
    $pdf->addInfoLine('Telefon:', $ticket['passenger_phone'] ?? 'Belirtilmemis', true);
    $pdf->addSpace(35);
    $pdf->addInfoLine('Bilet Durumu:', $ticket['status'] === 'active' ? 'Aktif' : 'Iptal Edildi', false);
    $pdf->addInfoLine('E-posta:', $ticket['passenger_email'] ?? 'Belirtilmemis', true);
    $pdf->addSpace(35);
    
    
    $pdf->addDivider();
    $pdf->addSpace(10);
    
    
    $footerLines = [
        'Bu bilet BILETLY sistemi tarafindan elektronik olarak olusturulmustur.',
        'Seyahat sirasinda yanınızda bulundurmaniz gerekmektedir.',
    ];
    $pdf->addFooterBox($footerLines);
    $pdf->addSpace(5);
    $pdf->addDivider();
    
    
    echo $pdf->output();
    exit;
}


$route = $ticket['departure_city'] . ' → ' . $ticket['arrival_city'];
$departure_date = date('d.m.Y', strtotime($ticket['departure_date']));
$booking_date = date('d.m.Y H:i', strtotime($ticket['booking_date']));
$price_formatted = number_format($ticket['price'], 2, ',', '.') . ' TL';
$status_text = $ticket['status'] === 'active' ? 'Aktif' : 'İptal';
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Bilet - <?php echo $pnr; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .ticket-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }
        
        .ticket-header {
            background: linear-gradient(135deg, #6C63FF 0%, #5548E6 100%);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }
        
        .ticket-header::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 0;
            right: 0;
            height: 40px;
            background: white;
            border-radius: 50% 50% 0 0 / 100% 100% 0 0;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .logo-icon {
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 800;
            color: #6C63FF;
        }
        
        .ticket-title {
            font-size: 32px;
            font-weight: 800;
            letter-spacing: 2px;
        }
        
        .ticket-subtitle {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 5px;
        }
        
        .pnr-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 30px;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        
        .pnr-label {
            color: white;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.9;
            margin-bottom: 8px;
        }
        
        .pnr-code {
            color: white;
            font-size: 32px;
            font-weight: 800;
            letter-spacing: 4px;
            font-family: 'Courier New', monospace;
        }
        
        .ticket-body {
            padding: 0 30px 30px;
        }
        
        .ticket-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            border: 2px solid #e9ecef;
        }
        
        .section-title {
            color: #6C63FF;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 3px solid #6C63FF;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .info-label {
            font-size: 12px;
            font-weight: 600;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            font-size: 16px;
            font-weight: 600;
            color: #212529;
        }
        
        .info-value.highlight {
            color: #6C63FF;
            font-size: 20px;
            font-weight: 800;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 700;
        }
        
        .status-active {
            background: linear-gradient(135deg, #48BB78 0%, #38A169 100%);
            color: white;
        }
        
        .status-cancelled {
            background: linear-gradient(135deg, #F56565 0%, #E53E3E 100%);
            color: white;
        }
        
        .route-display {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            padding: 20px;
            background: white;
            border-radius: 12px;
            margin: 20px 0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        
        .route-city {
            font-size: 24px;
            font-weight: 800;
            color: #212529;
        }
        
        .route-arrow {
            font-size: 32px;
            color: #6C63FF;
        }
        
        .ticket-footer {
            background: #f8f9fa;
            padding: 25px 30px;
            text-align: center;
            border-top: 2px dashed #dee2e6;
        }
        
        .footer-text {
            color: #6c757d;
            font-size: 13px;
            line-height: 1.8;
            margin-bottom: 8px;
        }
        
        .footer-highlight {
            color: #6C63FF;
            font-weight: 600;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        
        .btn {
            padding: 14px 32px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Inter', sans-serif;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #6C63FF 0%, #5548E6 100%);
            color: white;
            box-shadow: 0 8px 20px rgba(108, 99, 255, 0.4);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(108, 99, 255, 0.5);
        }
        
        .btn-secondary {
            background: #e9ecef;
            color: #495057;
        }
        
        .btn-secondary:hover {
            background: #dee2e6;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .ticket-container {
                box-shadow: none;
            }
        }
        
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            .action-buttons {
                flex-direction: column;
            }
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="ticket-container">
        <!-- Header -->
        <div class="ticket-header">
            <div class="logo-section">
                <div class="logo-icon">B</div>
                <div>
                    <div class="ticket-title">BİLETLY</div>
                    <div class="ticket-subtitle">Modern Otobüs Bileti Sistemi</div>
                </div>
            </div>
        </div>
        
        <!-- PNR Section -->
        <div class="pnr-section">
            <div class="pnr-label">PNR Kodu</div>
            <div class="pnr-code"><?php echo $pnr; ?></div>
        </div>
        
        <!-- Body -->
        <div class="ticket-body">
            <!-- Route Display -->
            <div class="route-display">
                <div class="route-city"><?php echo htmlspecialchars($ticket['departure_city']); ?></div>
                <div class="route-arrow">→</div>
                <div class="route-city"><?php echo htmlspecialchars($ticket['arrival_city']); ?></div>
            </div>
            
            <!-- Trip Information -->
            <div class="ticket-section">
                <div class="section-title">
                    <span>🚌</span> SEFER BİLGİLERİ
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Otobüs Firması</div>
                        <div class="info-value"><?php echo htmlspecialchars($ticket['company_name']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Araç Plakası</div>
                        <div class="info-value"><?php echo htmlspecialchars($ticket['bus_plate']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Seyahat Tarihi</div>
                        <div class="info-value"><?php echo $departure_date; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Kalkış Saati</div>
                        <div class="info-value highlight"><?php echo substr($ticket['departure_time'], 0, 5); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Varış Saati</div>
                        <div class="info-value"><?php echo substr($ticket['arrival_time'], 0, 5); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Bilet Ücreti</div>
                        <div class="info-value highlight"><?php echo $price_formatted; ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Ticket Details -->
            <div class="ticket-section">
                <div class="section-title">
                    <span>🎫</span> BİLET DETAYLARI
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Bilet ID</div>
                        <div class="info-value">#<?php echo $ticket['id']; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Koltuk Numarası</div>
                        <div class="info-value highlight"><?php echo $ticket['seat_number']; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Rezervasyon Tarihi</div>
                        <div class="info-value"><?php echo $booking_date; ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Bilet Durumu</div>
                        <div class="info-value">
                            <span class="status-badge <?php echo $ticket['status'] === 'active' ? 'status-active' : 'status-cancelled'; ?>">
                                <?php echo $status_text; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Passenger Information -->
            <div class="ticket-section">
                <div class="section-title">
                    <span>👤</span> YOLCU BİLGİLERİ
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Ad Soyad</div>
                        <div class="info-value"><?php echo htmlspecialchars($ticket['passenger_name']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">T.C. Kimlik No</div>
                        <div class="info-value"><?php echo htmlspecialchars($ticket['passenger_tc']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Telefon</div>
                        <div class="info-value"><?php echo htmlspecialchars($ticket['passenger_phone']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">E-posta</div>
                        <div class="info-value"><?php echo htmlspecialchars($ticket['passenger_email']); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="ticket-footer">
            <div class="footer-text">
                ✨ Bu bilet <span class="footer-highlight">Biletly</span> sistemi tarafından elektronik olarak oluşturulmuştur.
            </div>
            <div class="footer-text">
                🎉 Güvenli ve keyifli yolculuklar dileriz!
            </div>
            <div class="footer-text">
                📅 Yazdırma Tarihi: <?php echo date('d.m.Y H:i'); ?>
            </div>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="action-buttons no-print">
        <button onclick="window.location='download_ticket_pdf.php?id=<?php echo $ticketId; ?>&download=1'" class="btn btn-primary">
            📥 PDF Olarak İndir
        </button>
        <button onclick="window.print()" class="btn btn-secondary">
            🖨️ Yazdır
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            ✕ Kapat
        </button>
    </div>
</body>
</html>