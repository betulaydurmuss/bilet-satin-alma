<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];
$ticket_id = $_GET['id'] ?? null;

if (!$ticket_id) {
    header('Location: my_account.php');
    exit;
}


$ticket = $db->queryOne("SELECT 
    t.id,
    t.seat_number,
    t.passenger_name,
    t.passenger_tc,
    t.passenger_phone,
    t.passenger_email,
    t.price,
    t.final_price,
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
WHERE t.id = ? AND t.user_id = ?", [$ticket_id, $user_id]);

if (!$ticket) {
    header('Location: my_account.php');
    exit;
}


$ticket['formatted_departure_date'] = date('d.m.Y', strtotime($ticket['departure_date']));
$ticket['formatted_booking_date'] = date('d.m.Y H:i', strtotime($ticket['booking_date']));
$ticket['formatted_price'] = number_format($ticket['price'], 2, ',', '.') . ' TL';
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biletly - Bilet Detayları</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/modern-style.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <!-- Modern Navbar -->
    <nav class="modern-navbar">
        <div class="navbar-container">
            <a href="index.php" class="navbar-brand">
                <div class="logo-icon">B</div>
                <span class="logo-text">Biletly</span>
            </a>
            
            <div class="navbar-menu">
                <a href="index.php" class="navbar-link">Seferler</a>
                <a href="campaigns.php" class="navbar-link">Kampanyalar</a>
            </div>
            
            <div class="navbar-actions">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
                    </div>
                    <span style="font-weight: 600; color: var(--gray-700);">
                        <?php echo htmlspecialchars($_SESSION['username'] ?? 'Kullanıcı'); ?>
                    </span>
                </div>
                <a href="my_account.php" class="btn btn-primary btn-sm">💼 Hesabım</a>
                <a href="logout.php" class="btn btn-ghost btn-sm">Çıkış</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container section">
        <div class="card fade-in">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
                <h2 style="font-size: 2rem; font-weight: 800; color: #F7FAFC; margin: 0;">
                    🎫 Bilet Detayları
                </h2>
                <a href="my_account.php" class="btn btn-ghost btn-sm">
                    ← Hesabım'a Dön
                </a>
            </div>
            
            <!-- Bilet Kartı - Büyük Görünüm -->
            <div style="background: linear-gradient(135deg, rgba(108, 99, 255, 0.1) 0%, rgba(142, 68, 173, 0.1) 100%); border: 2px solid rgba(108, 99, 255, 0.3); border-radius: var(--radius-xl); padding: 2rem; margin-bottom: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 2rem; flex-wrap: wrap;">
                    <!-- Sol: Sefer Bilgileri -->
                    <div style="flex: 1; min-width: 300px;">
                        <div style="margin-bottom: 1.5rem;">
                            <span class="trip-badge <?php echo $ticket['status'] === 'active' ? 'badge-today' : 'badge-tomorrow'; ?>" style="background: <?php echo $ticket['status'] === 'active' ? 'linear-gradient(135deg, #48BB78 0%, #38A169 100%)' : 'linear-gradient(135deg, #F56565 0%, #E53E3E 100%)'; ?>;">
                                <?php echo $ticket['status'] === 'active' ? '✓ Aktif Bilet' : '✗ İptal Edildi'; ?>
                            </span>
                            <span style="font-size: 1rem; color: #FFFFFF; font-weight: 600; margin-left: 1rem;">
                                🏢 <?php echo htmlspecialchars($ticket['company_name']); ?>
                            </span>
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 2rem; margin-bottom: 1.5rem;">
                            <div style="text-align: center;">
                                <div style="font-size: 2.5rem; font-weight: 800; color: var(--primary); margin-bottom: 0.5rem;">
                                    <?php echo substr($ticket['departure_time'], 0, 5); ?>
                                </div>
                                <div style="font-size: 1.25rem; font-weight: 700; color: #FFFFFF; margin-bottom: 0.25rem;">
                                    <?php echo htmlspecialchars($ticket['departure_city']); ?>
                                </div>
                                <div style="font-size: 0.875rem; color: #E2E8F0;">
                                    Kalkış
                                </div>
                            </div>
                            
                            <div style="flex: 1; text-align: center;">
                                <div style="color: var(--primary); font-size: 2.5rem; margin-bottom: 0.5rem;">→</div>
                                <div style="color: #FFFFFF; font-size: 0.875rem; font-weight: 600;">
                                    📅 <?php echo $ticket['formatted_departure_date']; ?>
                                </div>
                                <div style="color: #E2E8F0; font-size: 0.875rem; margin-top: 0.25rem;">
                                    🚌 <?php echo $ticket['bus_plate']; ?>
                                </div>
                            </div>
                            
                            <div style="text-align: center;">
                                <div style="font-size: 2rem; font-weight: 700; color: #E2E8F0; margin-bottom: 0.5rem;">
                                    <?php echo substr($ticket['arrival_time'], 0, 5); ?>
                                </div>
                                <div style="font-size: 1.25rem; font-weight: 700; color: #FFFFFF; margin-bottom: 0.25rem;">
                                    <?php echo htmlspecialchars($ticket['arrival_city']); ?>
                                </div>
                                <div style="font-size: 0.875rem; color: #E2E8F0;">
                                    Varış
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sağ: Bilet Bilgileri -->
                    <div style="display: flex; flex-direction: column; align-items: flex-end; justify-content: center; gap: 1rem; min-width: 250px;">
                        <div style="text-align: right;">
                            <div style="font-size: 0.875rem; color: #E2E8F0; margin-bottom: 0.5rem;">
                                Koltuk Numarası
                            </div>
                            <div style="font-size: 3rem; font-weight: 800; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                                <?php echo $ticket['seat_number']; ?>
                            </div>
                        </div>
                        
                        <div style="text-align: right; width: 100%;">
                            <div style="font-size: 0.875rem; color: #E2E8F0; margin-bottom: 0.5rem;">
                                Ödenen Tutar
                            </div>
                            <div style="font-size: 2rem; font-weight: 800; color: #FFFFFF;">
                                <?php echo $ticket['formatted_price']; ?>
                            </div>
                        </div>
                        
                        <a href="download_ticket_pdf.php?id=<?php echo $ticket['id']; ?>&download=1" class="btn btn-primary btn-lg" style="width: 100%;">
                            📄 PDF İndir
                        </a>
                    </div>
                </div>
                
                <!-- Alt Bilgiler -->
                <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 2px solid rgba(108, 99, 255, 0.2); display: flex; gap: 2rem; flex-wrap: wrap; color: #E2E8F0; font-size: 0.875rem;">
                    <div>
                        <span style="font-weight: 600;">Bilet ID:</span> #<?php echo $ticket['id']; ?>
                    </div>
                    <div>
                        <span style="font-weight: 600;">Rezervasyon:</span> <?php echo $ticket['formatted_booking_date']; ?>
                    </div>
                </div>
            </div>
            
            <!-- Yolcu Bilgileri -->
            <div style="background: rgba(45, 55, 72, 0.4); border-radius: var(--radius-xl); padding: 2rem; margin-bottom: 2rem;">
                <h3 style="font-size: 1.5rem; font-weight: 700; color: #FFFFFF; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid rgba(108, 99, 255, 0.2);">
                    👤 Yolcu Bilgileri
                </h3>
                <div class="grid grid-4" style="gap: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label" style="color: #E2E8F0;">Ad Soyad</label>
                        <div class="form-control" style="background: rgba(26, 32, 44, 0.8); color: #FFFFFF; font-weight: 500;">
                            <?php echo htmlspecialchars($ticket['passenger_name']); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="color: #E2E8F0;">T.C. Kimlik No</label>
                        <div class="form-control" style="background: rgba(26, 32, 44, 0.8); color: #FFFFFF; font-weight: 500;">
                            <?php echo $ticket['passenger_tc']; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="color: #E2E8F0;">Telefon</label>
                        <div class="form-control" style="background: rgba(26, 32, 44, 0.8); color: #FFFFFF; font-weight: 500;">
                            <?php echo $ticket['passenger_phone']; ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="color: #E2E8F0;">E-posta</label>
                        <div class="form-control" style="background: rgba(26, 32, 44, 0.8); color: #FFFFFF; font-weight: 500;">
                            <?php echo $ticket['passenger_email']; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div style="display: flex; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
                <a href="my_account.php" class="btn btn-ghost btn-lg">
                    ← Geri
                </a>
                <?php if ($ticket['status'] === 'active'): ?>
                    <a href="cancel_ticket.php?ticket_id=<?php echo $ticket['id']; ?>" class="btn btn-lg" style="background: linear-gradient(135deg, #F56565 0%, #E53E3E 100%); color: white; box-shadow: 0 4px 15px rgba(245, 101, 101, 0.4);">
                        ✗ Bileti İptal Et
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modern Footer -->
    <footer class="modern-footer">
        <div class="footer-content">
            <p class="footer-text">
                © 2025 Biletly. Tüm hakları saklıdır. | Modern ve güvenli otobüs bileti sistemi
            </p>
        </div>
    </footer>
</body>
</html>