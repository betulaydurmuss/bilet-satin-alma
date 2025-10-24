<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user_id']);
$role = $_SESSION['role'] ?? 'user';

$db = Database::getInstance();


$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));

$popular_routes = [
    ['from' => 'İstanbul', 'to' => 'Ankara'],
    ['from' => 'İstanbul', 'to' => 'İzmir'],
    ['from' => 'Ankara', 'to' => 'İstanbul'],
    ['from' => 'İzmir', 'to' => 'İstanbul'],
    ['from' => 'Ankara', 'to' => 'Antalya'],
    ['from' => 'İstanbul', 'to' => 'Antalya']
];

$trips = [];
foreach ($popular_routes as $route) {
    $route_trips = $db->query(
        "SELECT t.*, c.name as company_name 
         FROM trips t 
         LEFT JOIN companies c ON t.company_id = c.id
         WHERE t.departure_city = ? 
         AND t.arrival_city = ? 
         AND t.departure_date IN (?, ?)
         AND t.status = 'active'
         AND t.available_seats > 0
         ORDER BY t.departure_date, t.departure_time
         LIMIT 2",
        [$route['from'], $route['to'], $today, $tomorrow]
    );
    
    if (!empty($route_trips)) {
        $trips = array_merge($trips, $route_trips);
    }
    
    if (count($trips) >= 12) {
        $trips = array_slice($trips, 0, 12);
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biletly - Modern Otobüs Bileti Sistemi</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/modern-style.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <!-- Modern Navbar -->
    <nav class="modern-navbar">
        <div class="navbar-container">
            <a href="index_modern.php" class="navbar-brand">
                <div class="logo-icon">B</div>
                <span class="logo-text">Biletly</span>
            </a>
            
            <div class="navbar-menu">
                <a href="#seferler" class="navbar-link">Seferler</a>
                <a href="campaigns.php" class="navbar-link">Kampanyalar</a>
            </div>
            
            <div class="navbar-actions">
                <?php if ($isLoggedIn): ?>
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
                        </div>
                        <span style="font-weight: 600; color: var(--gray-700);">
                            <?php echo htmlspecialchars($_SESSION['username'] ?? 'Kullanıcı'); ?>
                        </span>
                    </div>
                    
                    <?php if ($role === 'admin'): ?>
                        <a href="admin_panel.php" class="btn btn-secondary btn-sm">
                            👑 Admin Panel
                        </a>
                    <?php elseif ($role === 'company'): ?>
                        <a href="company_panel.php" class="btn btn-secondary btn-sm">
                            🏢 Firma Panel
                        </a>
                    <?php endif; ?>
                    
                    <a href="my_account.php" class="btn btn-primary btn-sm">
                        💼 Hesabım
                    </a>
                    <a href="logout.php" class="btn btn-ghost btn-sm">Çıkış</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline btn-sm">Giriş Yap</a>
                    <a href="register.php" class="btn btn-primary btn-sm">Kayıt Ol</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section with Search -->
    <div class="container section">
        <div class="search-container fade-in">
            <h1 class="search-title">🚌 Otobüs Bileti Ara</h1>
            <p style="text-align: center; color: var(--gray-600); margin-bottom: 2rem;">
                Türkiye'nin her yerine konforlu ve güvenli yolculuk
            </p>
            
            <form method="GET" action="search.php">
                <div class="search-grid">
                    <div class="form-group">
                        <label class="form-label">📍 Nereden</label>
                        <select name="from" class="form-control form-select" required>
                            <option value="">Kalkış noktası seçin</option>
                            <?php
                            $cities = $db->query("SELECT name FROM cities ORDER BY name");
                            foreach ($cities as $city) {
                                echo "<option value=\"" . htmlspecialchars($city['name']) . "\">" . htmlspecialchars($city['name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">📍 Nereye</label>
                        <select name="to" class="form-control form-select" required>
                            <option value="">Varış noktası seçin</option>
                            <?php
                            foreach ($cities as $city) {
                                echo "<option value=\"" . htmlspecialchars($city['name']) . "\">" . htmlspecialchars($city['name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">📅 Tarih</label>
                        <input type="date" name="date" class="form-control" required 
                               min="<?php echo date('Y-m-d'); ?>"
                               value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">👥 Yolcu</label>
                        <select name="passengers" class="form-control form-select">
                            <option value="1">1 Yolcu</option>
                            <option value="2">2 Yolcu</option>
                            <option value="3">3 Yolcu</option>
                            <option value="4">4 Yolcu</option>
                            <option value="5">5 Yolcu</option>
                        </select>
                    </div>
                </div>
                
                <div style="text-align: center; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary btn-lg">
                        🔍 Sefer Ara
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Popular Trips Section -->
    <div class="container section" id="seferler">
        <div style="text-align: center; margin-bottom: 3rem;">
            <h2 style="font-size: 2.5rem; font-weight: 800; color: var(--dark); margin-bottom: 0.5rem;">
                🔥 Popüler Seferler
            </h2>
            <p style="color: var(--gray-600); font-size: 1.125rem;">
                En çok tercih edilen rotalar
            </p>
        </div>
        
        <div class="grid grid-2" style="gap: 2rem;">
            <?php if (!empty($trips)): ?>
                <?php foreach ($trips as $trip): ?>
                    <div class="trip-card fade-in">
                        <div class="trip-header">
                            <?php
                            $trip_date = date('d.m.Y', strtotime($trip['departure_date']));
                            $is_today = $trip['departure_date'] == date('Y-m-d');
                            ?>
                            <span class="trip-badge <?php echo $is_today ? 'badge-today' : 'badge-tomorrow'; ?>">
                                <?php echo $is_today ? '🔥 Bugün' : '📅 ' . $trip_date; ?>
                            </span>
                            <span style="font-size: 0.875rem; color: var(--gray-600); font-weight: 600;">
                                <?php echo htmlspecialchars($trip['company_name']); ?>
                            </span>
                        </div>
                        
                        <div class="trip-route">
                            <span class="trip-city"><?php echo htmlspecialchars($trip['departure_city']); ?></span>
                            <span class="trip-arrow">→</span>
                            <span class="trip-city"><?php echo htmlspecialchars($trip['arrival_city']); ?></span>
                        </div>
                        
                        <div class="trip-time">
                            <span class="time-departure"><?php echo substr($trip['departure_time'], 0, 5); ?></span>
                            <span style="color: var(--gray-400);">→</span>
                            <span class="time-arrival"><?php echo substr($trip['arrival_time'], 0, 5); ?></span>
                        </div>
                        
                        <div class="trip-info">
                            <span class="info-item">
                                <span>📶</span> WiFi
                            </span>
                            <span class="info-item">
                                <span>📺</span> TV
                            </span>
                            <span class="info-item">
                                <span>🔌</span> Priz
                            </span>
                            <span class="info-item">
                                <span>💺</span> <?php echo $trip['available_seats']; ?>/<?php echo $trip['total_seats']; ?> Boş
                            </span>
                        </div>
                        
                        <div class="trip-footer">
                            <div>
                                <div style="font-size: 0.875rem; color: var(--gray-600); margin-bottom: 0.25rem;">
                                    Kişi Başı
                                </div>
                                <div class="trip-price">
                                    <?php echo number_format($trip['price'], 0); ?> ₺
                                </div>
                            </div>
                            
                            <?php if ($trip['available_seats'] > 0): ?>
                                <button type="button" class="btn btn-primary" onclick="selectTrip(<?php echo $trip['id']; ?>)">
                                    Koltuk Seç
                                </button>
                            <?php else: ?>
                                <button class="btn btn-ghost" disabled style="cursor: not-allowed; opacity: 0.5;">
                                    Satıldı
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Seat Selection (Hidden by default) -->
                        <div id="seat-selection-<?php echo $trip['id']; ?>" style="display: none; margin-top: 1.5rem;">
                            <div class="seat-container">
                                <h3 style="font-size: 1.25rem; font-weight: 700; text-align: center; margin-bottom: 1.5rem;">
                                    Koltuk Seçimi
                                </h3>
                                
                                <div class="seat-grid">
                                    <?php
                                    $tickets = $db->query("SELECT seat_number FROM tickets WHERE trip_id = ? AND status = 'active'", [$trip['id']]);
                                    $occupied = array_column($tickets, 'seat_number');
                                    
                                    
                                    echo '<div class="seat-row">';
                                    for ($i = 1; $i <= 13; $i++) {
                                        $isOccupied = in_array($i, $occupied);
                                        $class = $isOccupied ? 'seat seat-occupied' : 'seat seat-available';
                                        $onclick = $isOccupied ? '' : 'onclick="selectSeat('.$i.', '.$trip['id'].')"';
                                        echo '<div class="'.$class.'" data-seat="'.$i.'" data-trip="'.$trip['id'].'" '.$onclick.'>'.$i.'</div>';
                                    }
                                    echo '</div>';
                                    
                                    echo '<div style="text-align: center; color: var(--gray-500); font-size: 0.875rem; font-style: italic;">KORİDOR</div>';
                                    
                                    
                                    echo '<div class="seat-row">';
                                    for ($i = 14; $i <= 26; $i++) {
                                        $isOccupied = in_array($i, $occupied);
                                        $class = $isOccupied ? 'seat seat-occupied' : 'seat seat-available';
                                        $onclick = $isOccupied ? '' : 'onclick="selectSeat('.$i.', '.$trip['id'].')"';
                                        echo '<div class="'.$class.'" data-seat="'.$i.'" data-trip="'.$trip['id'].'" '.$onclick.'>'.$i.'</div>';
                                    }
                                    echo '</div>';
                                    
                                    
                                    echo '<div class="seat-row">';
                                    for ($i = 27; $i <= 39; $i++) {
                                        $isOccupied = in_array($i, $occupied);
                                        $class = $isOccupied ? 'seat seat-occupied' : 'seat seat-available';
                                        $onclick = $isOccupied ? '' : 'onclick="selectSeat('.$i.', '.$trip['id'].')"';
                                        echo '<div class="'.$class.'" data-seat="'.$i.'" data-trip="'.$trip['id'].'" '.$onclick.'>'.$i.'</div>';
                                    }
                                    echo '</div>';
                                    ?>
                                </div>
                                
                                <div class="seat-legend">
                                    <div class="legend-item">
                                        <div class="seat seat-available" style="width: 24px; height: 24px; font-size: 0.75rem;">1</div>
                                        <span>Müsait</span>
                                    </div>
                                    <div class="legend-item">
                                        <div class="seat seat-selected" style="width: 24px; height: 24px; font-size: 0.75rem;">2</div>
                                        <span>Seçili</span>
                                    </div>
                                    <div class="legend-item">
                                        <div class="seat seat-occupied" style="width: 24px; height: 24px; font-size: 0.75rem;">3</div>
                                        <span>Dolu</span>
                                    </div>
                                </div>
                                
                                <div style="text-align: center; margin-top: 1.5rem;">
                                    <button type="button" class="btn btn-primary btn-lg" onclick="proceedToBooking(<?php echo $trip['id']; ?>)">
                                        Bileti Satın Al
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 4rem 2rem;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">🚌</div>
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: var(--dark); margin-bottom: 0.5rem;">
                        Seferler Yükleniyor...
                    </h3>
                    <p style="color: var(--gray-600);">
                        Yukarıdaki arama formunu kullanarak istediğiniz rotayı arayabilirsiniz.
                    </p>
                </div>
            <?php endif; ?>
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
        const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
        
        function showLoginNotification() {
            const existingToast = document.querySelector('.toast-notification');
            if (existingToast) existingToast.remove();
            
            const toast = document.createElement('div');
            toast.className = 'toast-notification toast-warning';
            toast.innerHTML = `
                <button class="toast-close" onclick="closeToast(this)">&times;</button>
                <div class="toast-icon warning">🔐</div>
                <div class="toast-content">
                    <div class="toast-title">Giriş Yapmanız Gerekiyor</div>
                    <div class="toast-message">Bilet satın almak için lütfen giriş yapın veya yeni bir hesap oluşturun.</div>
                    <div class="toast-actions">
                        <button class="toast-btn toast-btn-primary" onclick="window.location.href='login.php'">Giriş Yap</button>
                        <button class="toast-btn toast-btn-secondary" onclick="window.location.href='register.php'">Kayıt Ol</button>
                    </div>
                </div>
            `;
            document.body.appendChild(toast);
            setTimeout(() => closeToast(toast.querySelector('.toast-close')), 8000);
        }
        
        function closeToast(button) {
            const toast = button.closest('.toast-notification');
            toast.classList.add('hiding');
            setTimeout(() => toast.remove(), 300);
        }
        
        function selectTrip(tripId) {
            if (!isLoggedIn) {
                showLoginNotification();
                return;
            }
            const container = document.getElementById('seat-selection-' + tripId);
            container.style.display = container.style.display === 'none' ? 'block' : 'none';
        }
        
        function selectSeat(seatNumber, tripId) {
            if (!isLoggedIn) {
                showLoginNotification();
                return;
            }
            
            // Deselect all seats for this trip
            document.querySelectorAll('.seat[data-trip="'+tripId+'"]').forEach(seat => {
                if (!seat.classList.contains('seat-occupied')) {
                    seat.classList.remove('seat-selected');
                    seat.classList.add('seat-available');
                }
            });
            
            // Select clicked seat
            const seat = document.querySelector('.seat[data-seat="'+seatNumber+'"][data-trip="'+tripId+'"]');
            seat.classList.remove('seat-available');
            seat.classList.add('seat-selected');
        }
        
        function proceedToBooking(tripId) {
            if (!isLoggedIn) {
                showLoginNotification();
                return;
            }
            
            const selectedSeat = document.querySelector('.seat-selected[data-trip="'+tripId+'"]');
            if (!selectedSeat) {
                const toast = document.createElement('div');
                toast.className = 'toast-notification toast-warning';
                toast.innerHTML = `
                    <button class="toast-close" onclick="closeToast(this)">&times;</button>
                    <div class="toast-icon warning">⚠️</div>
                    <div class="toast-content">
                        <div class="toast-title">Koltuk Seçimi Gerekli</div>
                        <div class="toast-message">Lütfen devam etmek için bir koltuk seçin.</div>
                    </div>
                `;
                document.body.appendChild(toast);
                setTimeout(() => closeToast(toast.querySelector('.toast-close')), 3000);
                return;
            }
            
            const seatNumber = selectedSeat.getAttribute('data-seat');
            window.location.href = 'buy_ticket.php?trip_id=' + tripId + '&seat=' + seatNumber;
        }
        
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
</body>
</html>
