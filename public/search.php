<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = Database::getInstance();
$results = [];
$message = '';


$isLoggedIn = isset($_SESSION['user_id']);
$userRole = null;
if ($isLoggedIn) {
    $user = $db->queryOne('SELECT role FROM users WHERE id = ?', [$_SESSION['user_id']]);
    $userRole = $user['role'] ?? null;
}


$from = '';
$to = '';
$date = '';
$passengers = 1;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['from']) && isset($_GET['to']) && isset($_GET['date'])) {
    $from = trim($_GET['from']);
    $to = trim($_GET['to']);
    $date = trim($_GET['date']);
    $passengers = $_GET['passengers'] ?? 1;
    
    
    $from = mb_convert_encoding($from, 'UTF-8', 'UTF-8');
    $to = mb_convert_encoding($to, 'UTF-8', 'UTF-8');
    
    if (empty($from) || empty($to) || empty($date)) {
        $message = 'Lütfen tüm alanları doldurun!';
    } else {
        $results = $db->query(
            "SELECT t.*, c.name as company_name FROM trips t LEFT JOIN companies c ON t.company_id = c.id
            WHERE t.departure_city = ? AND t.arrival_city = ? AND t.departure_date = ?
            AND t.status = 'active' AND t.available_seats > 0
            ORDER BY t.departure_time",
            [$from, $to, $date]
        );
        
        if (empty($results)) {
            $message = 'Seçtiğiniz kriterlere uygun sefer bulunamadı.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biletly - Sefer Ara</title>
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
                <a href="index.php#seferler" class="navbar-link">Seferler</a>
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
                    <a href="my_account.php" class="btn btn-primary btn-sm">💼 Hesabım</a>
                    <a href="logout.php" class="btn btn-ghost btn-sm">Çıkış</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline btn-sm">Giriş Yap</a>
                    <a href="register.php" class="btn btn-primary btn-sm">Kayıt Ol</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container section">
        <!-- Search Section -->
        <div class="search-container fade-in">
            <h1 class="search-title">🚌 Otobüs Bileti Ara</h1>
            <form method="GET" action="search.php">
                <div class="search-grid">
                    <div class="form-group">
                        <label class="form-label">📍 Nereden</label>
                        <select name="from" class="form-control form-select" required>
                            <option value="">Kalkış noktası seçin</option>
                            <?php
                            
                            $cities = $db->query("SELECT name FROM cities ORDER BY name");
                            foreach ($cities as $city) {
                                $selected = ($from == $city['name']) ? 'selected' : '';
                                echo "<option value=\"" . htmlspecialchars($city['name']) . "\" $selected>" . htmlspecialchars($city['name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">📍 Nereye</label>
                        <select name="to" class="form-control form-select" required>
                            <option value="">Varış noktası seçin</option>
                            <?php
                            
                            $cities = $db->query("SELECT name FROM cities ORDER BY name");
                            foreach ($cities as $city) {
                                $selected = ($to == $city['name']) ? 'selected' : '';
                                echo "<option value=\"" . htmlspecialchars($city['name']) . "\" $selected>" . htmlspecialchars($city['name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">📅 Tarih</label>
                        <input type="date" name="date" class="form-control" required 
                               min="<?php echo date('Y-m-d'); ?>"
                               value="<?php echo htmlspecialchars($date ?: date('Y-m-d')); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">👥 Yolcu</label>
                        <select name="passengers" class="form-control form-select">
                            <option value="1" <?php echo ($passengers == 1) ? 'selected' : ''; ?>>1 Yolcu</option>
                            <option value="2" <?php echo ($passengers == 2) ? 'selected' : ''; ?>>2 Yolcu</option>
                            <option value="3" <?php echo ($passengers == 3) ? 'selected' : ''; ?>>3 Yolcu</option>
                            <option value="4" <?php echo ($passengers == 4) ? 'selected' : ''; ?>>4 Yolcu</option>
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

        <!-- Results Section -->
        <div id="sonuclar" class="scroll-mt-20" style="margin-top: 2rem;">
            <div style="text-align: center; margin-bottom: 2rem;">
                <h2 style="font-size: 2rem; font-weight: 800; color: #F7FAFC; margin-bottom: 0.5rem;">
                    🎫 Sefer Sonuçları
                </h2>
            </div>
            
            <?php if ($message): ?>
                <div class="card" style="background: rgba(79, 172, 254, 0.2); border-color: rgba(79, 172, 254, 0.4); margin-bottom: 2rem;">
                    <p style="color: #E2E8F0; text-align: center; margin: 0;">
                        <?php echo htmlspecialchars($message); ?>
                    </p>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($results)): ?>
                <div style="display: flex; flex-direction: column; gap: 1.5rem; max-width: 100%;">
                    <?php foreach ($results as $trip): ?>
                        <div class="trip-card fade-in" style="width: 100%;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 2rem; flex-wrap: wrap;">
                                <!-- Sol Taraf: Sefer Bilgileri -->
                                <div style="flex: 1; min-width: 300px;">
                                    <div class="trip-header" style="margin-bottom: 1rem;">
                                        <?php
                                        $trip_date = date('d.m.Y', strtotime($trip['departure_date']));
                                        $is_today = $trip['departure_date'] == date('Y-m-d');
                                        ?>
                                        <span class="trip-badge <?php echo $is_today ? 'badge-today' : 'badge-tomorrow'; ?>">
                                            <?php echo $is_today ? '🔥 Bugün' : '📅 ' . $trip_date; ?>
                                        </span>
                                        <span style="font-size: 0.875rem; color: #A0AEC0; font-weight: 600;">
                                            <?php echo htmlspecialchars($trip['company_name']); ?>
                                        </span>
                                    </div>
                                    
                                    <div style="display: flex; align-items: center; gap: 2rem; margin-bottom: 1rem;">
                                        <div style="text-align: center;">
                                            <div style="font-size: 2rem; font-weight: 800; color: var(--primary); margin-bottom: 0.25rem;">
                                                <?php echo substr($trip['departure_time'], 0, 5); ?>
                                            </div>
                                            <div style="font-size: 1.125rem; font-weight: 700; color: #F7FAFC;">
                                                <?php echo htmlspecialchars($trip['departure_city']); ?>
                                            </div>
                                        </div>
                                        
                                        <div style="flex: 1; text-align: center;">
                                            <div style="color: var(--primary); font-size: 2rem; margin-bottom: 0.5rem;">→</div>
                                            <div style="color: #A0AEC0; font-size: 0.875rem;">
                                                <?php 
                                                    $departure = strtotime($trip['departure_time']);
                                                    $arrival = strtotime($trip['arrival_time']);
                                                    $duration = ($arrival - $departure) / 3600;
                                                    echo number_format($duration, 1) . ' saat';
                                                ?>
                                            </div>
                                        </div>
                                        
                                        <div style="text-align: center;">
                                            <div style="font-size: 1.5rem; font-weight: 700; color: #A0AEC0; margin-bottom: 0.25rem;">
                                                <?php echo substr($trip['arrival_time'], 0, 5); ?>
                                            </div>
                                            <div style="font-size: 1.125rem; font-weight: 700; color: #F7FAFC;">
                                                <?php echo htmlspecialchars($trip['arrival_city']); ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="trip-info" style="margin-bottom: 0;">
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
                                </div>
                                
                                <!-- Sağ Taraf: Fiyat ve Buton -->
                                <div style="display: flex; flex-direction: column; align-items: flex-end; justify-content: center; gap: 1rem; min-width: 200px;">
                                    <div style="text-align: right;">
                                        <div style="font-size: 0.875rem; color: #A0AEC0; margin-bottom: 0.5rem;">
                                            Kişi Başı
                                        </div>
                                        <div style="font-size: 2.5rem; font-weight: 800; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                                            <?php echo number_format($trip['price'], 0); ?> ₺
                                        </div>
                                    </div>
                                    
                                    <?php if ($trip['available_seats'] > 0): ?>
                                        <?php if ($isLoggedIn && $userRole === 'user'): ?>
                                            <button type="button" class="btn btn-primary btn-lg" onclick="toggleSeatSelection(<?php echo $trip['id']; ?>)" style="width: 100%;">
                                                Koltuk Seç
                                            </button>
                                        <?php elseif ($isLoggedIn && ($userRole === 'admin' || $userRole === 'firma_admin')): ?>
                                            <button class="btn btn-ghost btn-lg" disabled style="cursor: not-allowed; opacity: 0.5; width: 100%;" title="Sadece kullanıcılar bilet alabilir">
                                                🚫 Yetkiniz Yok
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-primary btn-lg" onclick="showLoginNotification()" style="width: 100%;">
                                                Koltuk Seç
                                            </button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <button class="btn btn-ghost btn-lg" disabled style="cursor: not-allowed; opacity: 0.5; width: 100%;">
                                            Satıldı
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div> <!-- Flex wrapper kapanışı -->
                            
                            <!-- Seat Selection Container (Initially Hidden) -->
                            <div id="seat-selection-<?php echo $trip['id']; ?>" class="mt-4 hidden">
                                <?php
                                
                                $tickets = $db->query("SELECT seat_number FROM tickets WHERE trip_id = ? AND status = 'active'", [$trip['id']]);
                                $occupied = array_column($tickets, 'seat_number');
                                ?>
                                <div class="seat-container">
                                    <h3 style="font-size: 1.25rem; font-weight: 700; text-align: center; margin-bottom: 1.5rem;">
                                        Koltuk Seçimi
                                    </h3>
                                    
                                    <div class="seat-grid">
                                            <?php
                                            
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
                </div>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 4rem 2rem;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">🚌</div>
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: #F7FAFC; margin-bottom: 0.5rem;">
                        <?php echo (isset($_GET['from']) && isset($_GET['to']) && isset($_GET['date'])) ? 'Sefer Bulunamadı' : 'Sefer Ara'; ?>
                    </h3>
                    <p style="color: #A0AEC0;">
                        <?php echo (isset($_GET['from']) && isset($_GET['to']) && isset($_GET['date'])) ? 'Seçtiğiniz kriterlere uygun sefer bulunamadı.' : 'Yukarıdaki arama formunu kullanarak istediğiniz rotayı arayabilirsiniz.'; ?>
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
        // Toggle seat selection visibility
        function toggleSeatSelection(tripId) {
            const seatSelection = document.getElementById('seat-selection-' + tripId);
            seatSelection.classList.toggle('hidden');
            
            // Reset seat selection when closing
            if (seatSelection.classList.contains('hidden')) {
                resetSeatSelection(tripId);
            }
        }
        
        // Reset seat selection for a trip
        function resetSeatSelection(tripId) {
            // Clear selected seat
            document.getElementById('selected-seat-' + tripId).value = '';
            
            // Disable proceed button
            const proceedBtn = document.getElementById('proceed-btn-' + tripId);
            if (proceedBtn) {
                proceedBtn.disabled = true;
            }
            
            // Reset seat styling
            document.querySelectorAll('.seat-btn[data-trip="' + tripId + '"]').forEach(seat => {
                if (!seat.classList.contains('cursor-not-allowed')) {
                    seat.classList.remove('bg-primary', 'text-white', 'border-primary');
                    seat.classList.add('bg-gray-200', 'text-primary');
                }
            });
        }
        
        // Select a seat
        function selectSeat(seatNumber, tripId) {
            if (!isLoggedIn) {
                showLoginNotification();
                return;
            }
            
            // Check if user role is allowed to buy tickets
            if (userRole !== 'user') {
                showRoleRestrictionNotification();
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
        
        const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
        const userRole = '<?php echo $userRole ?? ''; ?>';
        
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
        
        function showRoleRestrictionNotification() {
            const existingToast = document.querySelector('.toast-notification');
            if (existingToast) existingToast.remove();
            
            const toast = document.createElement('div');
            toast.className = 'toast-notification toast-error';
            toast.innerHTML = `
                <button class="toast-close" onclick="closeToast(this)">&times;</button>
                <div class="toast-icon error">🚫</div>
                <div class="toast-content">
                    <div class="toast-title">Yetkiniz Yok</div>
                    <div class="toast-message">Sadece normal kullanıcılar bilet satın alabilir. Admin ve firma yöneticileri bilet alamaz.</div>
                </div>
            `;
            document.body.appendChild(toast);
            setTimeout(() => closeToast(toast.querySelector('.toast-close')), 5000);
        }
        
        function closeToast(button) {
            const toast = button.closest('.toast-notification');
            toast.classList.add('hiding');
            setTimeout(() => toast.remove(), 300);
        }
        
        function proceedToBooking(tripId) {
            if (!isLoggedIn) {
                showLoginNotification();
                return;
            }
            
            // Check if user role is allowed to buy tickets
            if (userRole !== 'user') {
                showRoleRestrictionNotification();
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