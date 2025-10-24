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


$user = $db->queryOne("SELECT * FROM users WHERE id = ?", [$user_id]);


if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}


$tickets = $db->query("SELECT 
    t.id,
    t.seat_number,
    t.passenger_name,
    t.passenger_tc,
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
WHERE t.user_id = ?
ORDER BY t.booking_date DESC", [$user_id]);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biletly - Hesabım</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/modern-style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .tab {
            display: none;
        }
        .tab.active {
            display: block;
        }
        .tab-button {
            background: rgba(45, 55, 72, 0.6);
            border: none;
            padding: 12px 24px;
            cursor: pointer;
            font-weight: 600;
            color: #CBD5E0;
            border-radius: 8px 8px 0 0;
            transition: all 0.2s;
        }
        .tab-button.active {
            background: rgba(108, 99, 255, 0.3);
            color: var(--primary);
        }
    </style>
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
            <h2 style="font-size: 2rem; font-weight: 800; color: #F7FAFC; margin-bottom: 2rem; text-align: center;">
                💼 Hesabım
            </h2>
            
            <!-- Tab Buttons -->
            <div class="flex border-b border-gray-200 mb-6">
                <button class="tab-button active" onclick="openTab('profile')">Profil Bilgileri</button>
                <button class="tab-button" onclick="openTab('tickets')">Biletlerim</button>
            </div>
            
            <!-- Profile Tab -->
            <div id="profile" class="tab active">
                <div style="background: rgba(45, 55, 72, 0.4); padding: 1.5rem; border-radius: var(--radius-xl); margin-bottom: 1.5rem;">
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #F7FAFC; margin-bottom: 1rem;">Profil Bilgileri</h3>
                    <div class="grid grid-4" style="gap: 1rem;">
                        <div class="form-group">
                            <label class="form-label">Ad Soyad</label>
                            <div class="form-control" style="background: rgba(26, 32, 44, 0.8); color: #E2E8F0;">
                                <?php echo htmlspecialchars($user['full_name']); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Kullanıcı Adı</label>
                            <div class="form-control" style="background: rgba(26, 32, 44, 0.8); color: #E2E8F0;">
                                <?php echo htmlspecialchars($user['username']); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">E-posta</label>
                            <div class="form-control" style="background: rgba(26, 32, 44, 0.8); color: #E2E8F0;">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Hesap Bakiyesi</label>
                            <div class="form-control" style="background: rgba(26, 32, 44, 0.8); font-weight: 700;">
                                <span style="background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                                    <?php echo number_format($user['credit'], 2); ?> ₺
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div style="text-align: center;">
                    <a href="index.php" class="btn btn-primary btn-lg">
                        🎫 Yeni Bilet Al
                    </a>
                </div>
            </div>
            
            <!-- Tickets Tab -->
            <div id="tickets" class="tab">
                <h3 style="font-size: 1.25rem; font-weight: 700; color: #F7FAFC; margin-bottom: 1.5rem;">🎫 Geçmiş Biletlerim</h3>
                
                <?php if (count($tickets) > 0): ?>
                    <div style="display: flex; flex-direction: column; gap: 1.5rem; max-width: 100%;">
                    <?php foreach ($tickets as $ticket): ?>
                        <div class="trip-card fade-in" style="width: 100%;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 2rem; flex-wrap: wrap;">
                                <!-- Sol Taraf: Bilet Bilgileri -->
                                <div style="flex: 1; min-width: 300px;">
                                    <div style="margin-bottom: 1rem;">
                                        <span class="trip-badge <?php echo $ticket['status'] === 'active' ? 'badge-today' : 'badge-tomorrow'; ?>" style="background: <?php echo $ticket['status'] === 'active' ? 'linear-gradient(135deg, #48BB78 0%, #38A169 100%)' : 'linear-gradient(135deg, #F56565 0%, #E53E3E 100%)'; ?>;">
                                            <?php echo $ticket['status'] === 'active' ? '✓ Aktif' : '✗ İptal'; ?>
                                        </span>
                                        <span style="font-size: 0.875rem; color: #A0AEC0; font-weight: 600; margin-left: 1rem;">
                                            <?php echo htmlspecialchars($ticket['company_name']); ?>
                                        </span>
                                    </div>
                                    
                                    <div style="display: flex; align-items: center; gap: 2rem; margin-bottom: 1rem;">
                                        <div style="text-align: center;">
                                            <div style="font-size: 2rem; font-weight: 800; color: var(--primary); margin-bottom: 0.25rem;">
                                                <?php echo substr($ticket['departure_time'], 0, 5); ?>
                                            </div>
                                            <div style="font-size: 1.125rem; font-weight: 700; color: #F7FAFC;">
                                                <?php echo htmlspecialchars($ticket['departure_city']); ?>
                                            </div>
                                        </div>
                                        
                                        <div style="flex: 1; text-align: center;">
                                            <div style="color: var(--primary); font-size: 2rem; margin-bottom: 0.5rem;">→</div>
                                            <div style="color: #A0AEC0; font-size: 0.875rem;">
                                                <?php echo date('d.m.Y', strtotime($ticket['departure_date'])); ?>
                                            </div>
                                        </div>
                                        
                                        <div style="text-align: center;">
                                            <div style="font-size: 1.5rem; font-weight: 700; color: #A0AEC0; margin-bottom: 0.25rem;">
                                                <?php echo substr($ticket['arrival_time'], 0, 5); ?>
                                            </div>
                                            <div style="font-size: 1.125rem; font-weight: 700; color: #F7FAFC;">
                                                <?php echo htmlspecialchars($ticket['arrival_city']); ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div style="display: flex; gap: 1.5rem; flex-wrap: wrap; color: #A0AEC0; font-size: 0.875rem;">
                                        <span>
                                            <span style="font-weight: 600;">Yolcu:</span> <?php echo htmlspecialchars($ticket['passenger_name']); ?>
                                        </span>
                                        <span>
                                            <span style="font-weight: 600;">Koltuk:</span> <?php echo $ticket['seat_number']; ?>
                                        </span>
                                        <span>
                                            <span style="font-weight: 600;">Plaka:</span> <?php echo $ticket['bus_plate']; ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Sağ Taraf: Fiyat ve Butonlar -->
                                <div style="display: flex; flex-direction: column; align-items: flex-end; justify-content: center; gap: 1rem; min-width: 200px;">
                                    <div style="text-align: right;">
                                        <div style="font-size: 0.875rem; color: #A0AEC0; margin-bottom: 0.5rem;">
                                            Ödenen Tutar
                                        </div>
                                        <div style="font-size: 2rem; font-weight: 800; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                                            <?php echo number_format($ticket['price'], 0); ?> ₺
                                        </div>
                                    </div>
                                    
                                    <div style="display: flex; gap: 0.5rem; width: 100%;">
                                        <a href="ticket_detail.php?id=<?php echo $ticket['id']; ?>" class="btn btn-secondary btn-sm" style="flex: 1;">
                                            Detay
                                        </a>
                                        <a href="download_ticket_pdf.php?id=<?php echo $ticket['id']; ?>&download=1" class="btn btn-primary btn-sm" style="flex: 1;">
                                            PDF
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="card" style="background: rgba(79, 172, 254, 0.2); border-color: rgba(79, 172, 254, 0.4); text-align: center; padding: 3rem;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">🎫</div>
                        <h3 style="font-size: 1.25rem; font-weight: 700; color: #F7FAFC; margin-bottom: 0.5rem;">
                            Biletiniz bulunmuyor
                        </h3>
                        <p style="color: #A0AEC0; margin-bottom: 1.5rem;">
                            Henüz hiç bilet almadınız. Yeni bir sefer için bilet almak ister misiniz?
                        </p>
                        <a href="index.php" class="btn btn-primary">
                            🎫 Bilet Al
                        </a>
                    </div>
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
    
    <script>
        function openTab(tabName) {
            // Hide all tabs
            var tabs = document.getElementsByClassName("tab");
            for (var i = 0; i < tabs.length; i++) {
                tabs[i].classList.remove("active");
            }
            
            // Remove active class from all buttons
            var tabButtons = document.getElementsByClassName("tab-button");
            for (var i = 0; i < tabButtons.length; i++) {
                tabButtons[i].classList.remove("active");
            }
            
            // Show the selected tab and mark button as active
            document.getElementById(tabName).classList.add("active");
            event.currentTarget.classList.add("active");
        }
    </script>
</body>
</html>