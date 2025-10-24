<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$db = Database::getInstance();
$message = '';
$trip_id = $_GET['trip_id'] ?? null;
$selected_seat = $_GET['seat'] ?? null;

if (!$trip_id) {
    die('Sefer ID eksik!');
}

$trip = $db->queryOne('SELECT t.*, c.name as company_name FROM trips t LEFT JOIN companies c ON t.company_id = c.id WHERE t.id = ?', [$trip_id]);

if (!$trip) {
    die('Sefer bulunamadı!');
}


$isLoggedIn = isset($_SESSION['user_id']);
$user = null;
if ($isLoggedIn) {
    $user = $db->queryOne('SELECT * FROM users WHERE id = ?', [$_SESSION['user_id']]);
    
    
    if ($user && $user['role'] !== 'user') {
        header('Location: index.php');
        exit;
    }
}


$activeCoupons = $db->query("SELECT code, discount_type, discount_value FROM coupons WHERE status = 'active' AND (valid_until IS NULL OR valid_until >= date('now')) AND (max_uses IS NULL OR current_uses < max_uses) ORDER BY discount_value DESC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$isLoggedIn) {
        $message = 'Bilet almak için giriş yapmalısınız!';
    } else {
        $passenger_name = $_POST['passenger_name'] ?? '';
        $passenger_tc = $_POST['passenger_tc'] ?? '';
        $passenger_phone = $_POST['passenger_phone'] ?? '';
        $passenger_email = $_POST['passenger_email'] ?? '';
        $seat_number = $_POST['seat_number'] ?? $selected_seat ?? 1;
        $price = $trip['price'];
        $final_price = $price;
        $user_id = $_SESSION['user_id'] ?? null;
        
        
        $couponDiscount = 0;
        $couponId = null;
        if (isset($_SESSION['applied_coupon'])) {
            $couponDiscount = $_SESSION['applied_coupon']['discount'];
            $couponId = $_SESSION['applied_coupon']['id'];
            $final_price = $price - $couponDiscount;
        }
        
        if (empty($passenger_name) || empty($passenger_tc) || empty($passenger_phone) || empty($passenger_email)) {
            $message = 'Lütfen tüm alanları doldurun!';
        } else {
            
            $existing_ticket = $db->queryOne("SELECT id FROM tickets WHERE trip_id = ? AND seat_number = ? AND status = 'active'", [$trip_id, $seat_number]);
            
            if ($existing_ticket) {
                $message = 'Seçilen koltuk başka bir kullanıcı tarafından alınmıştır. Lütfen başka bir koltuk seçin.';
            } else {
                
                $user_credit = $db->queryOne('SELECT credit FROM users WHERE id = ?', [$user_id]);
                if ($user_credit && $user_credit['credit'] < $final_price) {
                    $message = 'Yetersiz bakiye! Lütfen bakiyenizi yükleyin.';
                } else {
                    
                    $db->beginTransaction();
                    
                    try {
                        
                        $ok = $db->execute('INSERT INTO tickets (user_id, trip_id, seat_number, passenger_name, passenger_tc, passenger_phone, passenger_email, price, final_price, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [$user_id, $trip_id, $seat_number, $passenger_name, $passenger_tc, $passenger_phone, $passenger_email, $price, $final_price, 'active']);
                        
                        if ($ok) {
                            
                            $db->execute('UPDATE trips SET available_seats = available_seats - 1 WHERE id = ?', [$trip_id]);
                            
                            
                            if ($user_credit) {
                                $db->execute('UPDATE users SET credit = credit - ? WHERE id = ?', [$final_price, $user_id]);
                            }
                            
                            
                            if ($couponId) {
                                require_once __DIR__ . '/../src/CouponService.php';
                                $couponService = new CouponService();
                                $couponService->recordUsage($couponId);
                                unset($_SESSION['applied_coupon']); 
                            }
                            
                            
                            $db->commit();
                            
                            $message = 'Bilet başarıyla alındı!';
                        } else {
                            
                            $db->rollback();
                            $message = 'Bilet alınırken hata oluştu.';
                        }
                    } catch (Exception $e) {
                        
                        $db->rollback();
                        $message = 'Bilet alınırken hata oluştu: ' . $e->getMessage();
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biletly - Bilet Al</title>
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
        <?php if ($message): ?>
            <div class="card fade-in" style="background: <?php echo strpos($message, 'başarıyla') !== false ? 'rgba(72, 187, 120, 0.2)' : 'rgba(245, 101, 101, 0.2)'; ?>; border-color: <?php echo strpos($message, 'başarıyla') !== false ? 'rgba(72, 187, 120, 0.4)' : 'rgba(245, 101, 101, 0.4)'; ?>; margin-bottom: 2rem;">
                <p style="color: #E2E8F0; text-align: center; margin: 0; font-weight: 600;">
                    <?php echo htmlspecialchars($message); ?>
                </p>
            </div>
        <?php endif; ?>
        
        <?php if (!$isLoggedIn): ?>
            <section class="bg-white rounded-xl shadow-md p-6">
                <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg mb-6 text-center">
                    <h3 class="text-lg font-bold mb-2">Giriş Yapmanız Gerekiyor</h3>
                    <p>Bilet satın almak için giriş yapmanız gerekmektedir.</p>
                </div>
                <div class="flex justify-center space-x-4">
                    <a href="login.php" class="bg-primary hover:bg-orange-600 text-white font-semibold py-2 px-6 rounded-lg transition duration-300">
                        Giriş Yap
                    </a>
                    <a href="register.php" class="bg-secondary hover:bg-purple-600 text-white font-semibold py-2 px-6 rounded-lg transition duration-300">
                        Kayıt Ol
                    </a>
                </div>
                <a href="index.php" class="block text-center mt-6 text-primary hover:text-orange-600 font-medium">
                    Ana Sayfaya Dön
                </a>
            </section>
        <?php else: ?>
            <div class="card fade-in">
                <style>
                    .ticket-grid {
                        display: grid;
                        grid-template-columns: 350px 1fr;
                        gap: 2rem;
                    }
                    @media (max-width: 1024px) {
                        .ticket-grid { grid-template-columns: 1fr; }
                    }
                </style>
                <div class="ticket-grid">
                    <!-- Left Column: Trip Information -->
                    <div>
                        <div style="background: rgba(45, 55, 72, 0.4); padding: 1.5rem; border-radius: var(--radius-xl); height: 100%;">
                            <h3 style="font-size: 1.25rem; font-weight: 700; color: #FFFFFF; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid rgba(108, 99, 255, 0.2);">
                                🎫 Sefer Bilgileri
                            </h3>
                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <div>
                                    <span style="font-weight: 600; color: #E2E8F0;">Firma:</span>
                                    <span style="margin-left: 0.5rem; color: #FFFFFF; font-weight: 500;"><?php echo $trip['company_name']; ?></span>
                                </div>
                                <div>
                                    <span style="font-weight: 600; color: #E2E8F0;">Rota:</span>
                                    <span style="margin-left: 0.5rem; color: #FFFFFF; font-weight: 500;"><?php echo $trip['departure_city']; ?> → <?php echo $trip['arrival_city']; ?></span>
                                </div>
                                <div>
                                    <span style="font-weight: 600; color: #E2E8F0;">Tarih:</span>
                                    <span style="margin-left: 0.5rem; color: #FFFFFF; font-weight: 500;"><?php echo $trip['departure_date']; ?></span>
                                </div>
                                <div>
                                    <span style="font-weight: 600; color: #E2E8F0;">Saat:</span>
                                    <span style="margin-left: 0.5rem; color: #FFFFFF; font-weight: 500;"><?php echo $trip['departure_time']; ?></span>
                                </div>
                                <div>
                                    <span style="font-weight: 600; color: #E2E8F0;">Bilet Fiyatı:</span>
                                    <span style="margin-left: 0.5rem; font-weight: 700; color: #FFFFFF;" id="original-price"><?php echo number_format($trip['price'],2); ?> TL</span>
                                </div>
                                <div id="coupon-section" style="border-top: 2px solid rgba(108, 99, 255, 0.2); padding-top: 1rem; margin-top: 1rem;">
                                    <label style="display: block; font-size: 0.875rem; font-weight: 600; color: #E2E8F0; margin-bottom: 0.5rem;">
                                        🎟️ Kupon Kodu
                                    </label>
                                    <div style="position: relative;">
                                        <div style="display: flex; gap: 0.5rem;">
                                            <input type="text" id="coupon-code" class="form-control" style="flex: 1; padding: 0.5rem; background: rgba(26, 32, 44, 0.8); color: #FFFFFF;" placeholder="Kupon kodunu girin" autocomplete="off">
                                            <button type="button" onclick="applyCoupon()" class="btn btn-secondary">
                                                Uygula
                                            </button>
                                        </div>
                                    </div>
                                    <div id="coupon-message" style="margin-top: 0.5rem; font-size: 0.875rem;"></div>
                                    
                                    <!-- Aktif Kuponlar Listesi -->
                                    <?php if (!empty($activeCoupons)): ?>
                                        <div style="margin-top: 1rem; padding: 1rem; background: rgba(108, 99, 255, 0.1); border-radius: var(--radius-lg); border: 1px solid rgba(108, 99, 255, 0.2);">
                                            <div style="font-size: 0.75rem; font-weight: 700; color: #A0AEC0; margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                                ✨ Aktif Kuponlar (<?php echo count($activeCoupons); ?>)
                                            </div>
                                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                                <?php foreach ($activeCoupons as $coupon): ?>
                                                    <div onclick="selectCoupon('<?php echo htmlspecialchars($coupon['code']); ?>')" style="padding: 0.75rem; background: rgba(26, 32, 44, 0.6); border-radius: var(--radius-md); cursor: pointer; transition: all 0.2s; border: 1px solid rgba(108, 99, 255, 0.2);" onmouseover="this.style.background='rgba(108, 99, 255, 0.2)'; this.style.transform='translateX(4px)'" onmouseout="this.style.background='rgba(26, 32, 44, 0.6)'; this.style.transform='translateX(0)'">
                                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                                            <div style="flex: 1;">
                                                                <div style="font-weight: 700; color: #FFFFFF; font-size: 0.875rem;">
                                                                    <?php echo htmlspecialchars($coupon['code']); ?>
                                                                </div>
                                                            </div>
                                                            <div style="background: linear-gradient(135deg, #48BB78 0%, #38A169 100%); color: white; padding: 0.25rem 0.75rem; border-radius: var(--radius-md); font-weight: 700; font-size: 0.75rem; white-space: nowrap; margin-left: 0.5rem;">
                                                                <?php 
                                                                    if ($coupon['discount_type'] === 'percentage') {
                                                                        echo '%' . $coupon['discount_value'];
                                                                    } else {
                                                                        echo number_format($coupon['discount_value'], 0) . ' ₺';
                                                                    }
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <div id="discount-info" style="margin-top: 1rem; padding-top: 1rem; border-top: 2px solid rgba(108, 99, 255, 0.2); display: none;">
                                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                            <span style="color: #E2E8F0; font-weight: 600;">İndirim:</span>
                                            <span style="color: #48BB78; font-weight: 700;">-<span id="discount-amount">0</span></span>
                                        </div>
                                        <div style="display: flex; justify-content: space-between; padding: 0.75rem; background: rgba(108, 99, 255, 0.2); border-radius: var(--radius-md);">
                                            <span style="color: #FFFFFF; font-weight: 700; font-size: 1.125rem;">Ödenecek Tutar:</span>
                                            <span style="font-size: 1.5rem; font-weight: 800; background: linear-gradient(135deg, #48BB78 0%, #38A169 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;" id="final-price"><?php echo number_format($trip['price'],2); ?> TL</span>
                                        </div>
                                    </div>
                                </div>
                                

                                <div>
                                    <span style="font-weight: 600; color: #E2E8F0;">Seçilen Koltuk:</span>
                                    <span style="margin-left: 0.5rem; font-weight: 700; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"><?php echo !empty($selected_seat) ? $selected_seat : 'Henüz seçilmedi'; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Passenger Information and Seat Selection -->
                    <div style="display: flex; flex-direction: column; gap: 2rem;">
                        <!-- Top Section: Passenger Information Form -->
                        <div style="background: rgba(45, 55, 72, 0.4); padding: 1.5rem; border-radius: var(--radius-xl); margin-bottom: 2rem;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                                <h3 style="font-size: 1.25rem; font-weight: 700; color: #FFFFFF;">👤 Yolcu Bilgileri</h3>
                            </div>
                            <form method="post" style="display: flex; flex-direction: column; gap: 1rem;">
                                <!-- Üst Satır: Ad Soyad ve TC Kimlik -->
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                    <div class="form-group">
                                        <label class="form-label" style="color: #E2E8F0;">Yolcu Adı Soyadı:</label>
                                        <input type="text" name="passenger_name" required class="form-control" style="background: rgba(26, 32, 44, 0.8); color: #FFFFFF; font-weight: 500;" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" style="color: #E2E8F0;">TC Kimlik No:</label>
                                        <input type="text" name="passenger_tc" required maxlength="11" class="form-control" style="background: rgba(26, 32, 44, 0.8); color: #FFFFFF; font-weight: 500;" placeholder="11 haneli TC kimlik numarası">
                                    </div>
                                </div>
                                
                                <!-- Alt Satır: Telefon ve E-posta -->
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                    <div class="form-group">
                                        <label class="form-label" style="color: #E2E8F0;">Cep Telefonu:</label>
                                        <input type="tel" name="passenger_phone" required class="form-control" style="background: rgba(26, 32, 44, 0.8); color: #FFFFFF; font-weight: 500;" placeholder="05XX XXX XX XX">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" style="color: #E2E8F0;">E-posta:</label>
                                        <input type="email" name="passenger_email" required class="form-control" style="background: rgba(26, 32, 44, 0.8); color: #FFFFFF; font-weight: 500;" placeholder="ornek@email.com">
                                    </div>
                                </div>
                                
                                <?php
                                    $tickets = $db->query("SELECT seat_number FROM tickets WHERE trip_id = ? AND status = 'active'", [$trip['id']]);
                                    $occupied = array_column($tickets, 'seat_number');
                                    $total_seats = $trip['total_seats'];
                                    $selected = isset($_POST['seat_number']) ? intval($_POST['seat_number']) : (isset($selected_seat) ? intval($selected_seat) : null);
                                ?>
                                <input type="hidden" name="seat_number" id="seat_number" value="<?php echo $selected; ?>">
                                
                                <div style="display: flex; justify-content: flex-end;">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        💳 Satın Al
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Bottom: Seat Selection -->
                        <div style="background: rgba(45, 55, 72, 0.4); padding: 2rem; border-radius: var(--radius-xl);">
                            <h3 style="font-size: 1.25rem; font-weight: 700; color: #FFFFFF; margin-bottom: 1.5rem; text-align: center;">💺 Koltuk Seçimi</h3>
                            <div class="seat-container">
                                <div class="seat-grid">
                                    <?php
                                        
                                        echo '<div class="seat-row">';
                                        for ($i = 1; $i <= 13; $i++) {
                                            $isOccupied = in_array($i, $occupied);
                                            $isSelected = ($selected === $i);
                                            $class = $isOccupied ? 'seat seat-occupied' : ($isSelected ? 'seat seat-selected' : 'seat seat-available');
                                            $onclick = ($isOccupied || !empty($selected)) ? '' : 'onclick="selectSeat('.$i.')"';
                                            echo '<div class="'.$class.'" data-seat="'.$i.'" '.$onclick.'>'.$i.'</div>';
                                        }
                                        echo '</div>';
                                        
                                        echo '<div style="text-align: center; color: #E2E8F0; font-size: 0.875rem; font-style: italic; margin: 0.5rem 0;">KORİDOR</div>';
                                        
                                        
                                        echo '<div class="seat-row">';
                                        for ($i = 14; $i <= 26; $i++) {
                                            $isOccupied = in_array($i, $occupied);
                                            $isSelected = ($selected === $i);
                                            $class = $isOccupied ? 'seat seat-occupied' : ($isSelected ? 'seat seat-selected' : 'seat seat-available');
                                            $onclick = ($isOccupied || !empty($selected)) ? '' : 'onclick="selectSeat('.$i.')"';
                                            echo '<div class="'.$class.'" data-seat="'.$i.'" '.$onclick.'>'.$i.'</div>';
                                        }
                                        echo '</div>';
                                        
                                        
                                        echo '<div class="seat-row">';
                                        for ($i = 27; $i <= 39; $i++) {
                                            $isOccupied = in_array($i, $occupied);
                                            $isSelected = ($selected === $i);
                                            $class = $isOccupied ? 'seat seat-occupied' : ($isSelected ? 'seat seat-selected' : 'seat seat-available');
                                            $onclick = ($isOccupied || !empty($selected)) ? '' : 'onclick="selectSeat('.$i.')"';
                                            echo '<div class="'.$class.'" data-seat="'.$i.'" '.$onclick.'>'.$i.'</div>';
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
                            </div>
                        </div>
                    </div>
                </div>
                
                <script>
                    // Pre-select the seat if it was passed in the URL
                    window.addEventListener('DOMContentLoaded', function() {
                        const urlParams = new URLSearchParams(window.location.search);
                        const seatParam = urlParams.get('seat');
                        if (seatParam) {
                            selectSeat(parseInt(seatParam));
                        }
                    });
                    
                    // Kupon seçme fonksiyonu
                    function selectCoupon(code) {
                        console.log('Kupon seçildi:', code);
                        const input = document.getElementById('coupon-code');
                        if (input) {
                            input.value = code;
                        }
                        // Kuponu otomatik uygula
                        setTimeout(() => {
                            applyCoupon();
                        }, 100);
                    }
                    
                    function applyCoupon() {
                        const code = document.getElementById('coupon-code').value.trim();
                        const messageEl = document.getElementById('coupon-message');
                        
                        console.log('applyCoupon çağrıldı, kod:', code);
                        
                        if (!code) {
                            messageEl.style.color = '#F56565';
                            messageEl.style.fontWeight = '600';
                            messageEl.textContent = '⚠️ Lütfen kupon kodu girin';
                            return;
                        }
                        
                        // Show loading
                        messageEl.style.color = '#A0AEC0';
                        messageEl.style.fontWeight = '500';
                        messageEl.textContent = '⏳ Kupon kontrol ediliyor...';
                        
                        // AJAX request
                        const formData = new FormData();
                        formData.append('code', code);
                        formData.append('price', <?php echo $trip['price']; ?>);
                        formData.append('company_id', <?php echo $trip['company_id'] ?? 0; ?>);
                        
                        console.log('Kupon isteği gönderiliyor:', {
                            code: code,
                            price: <?php echo $trip['price']; ?>,
                            company_id: <?php echo $trip['company_id'] ?? 0; ?>
                        });
                        
                        fetch('apply_coupon.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => {
                            console.log('Response status:', response.status);
                            return response.json();
                        })
                        .then(data => {
                            console.log('Kupon yanıtı:', data);
                            
                            if (data.success) {
                                messageEl.style.color = '#48BB78';
                                messageEl.style.fontWeight = '600';
                                messageEl.textContent = '✅ ' + data.message;
                                
                                // Show discount info
                                document.getElementById('discount-amount').textContent = data.discount_formatted;
                                document.getElementById('final-price').textContent = data.final_price_formatted;
                                document.getElementById('discount-info').style.display = 'block';
                                
                                // Disable coupon input and button
                                const couponInput = document.getElementById('coupon-code');
                                const applyBtn = document.querySelector('button[onclick="applyCoupon()"]');
                                
                                if (couponInput) {
                                    couponInput.disabled = true;
                                    couponInput.style.opacity = '0.6';
                                }
                                if (applyBtn) {
                                    applyBtn.disabled = true;
                                    applyBtn.textContent = '✓ Uygulandı';
                                    applyBtn.style.opacity = '0.6';
                                }
                            } else {
                                messageEl.style.color = '#F56565';
                                messageEl.style.fontWeight = '600';
                                messageEl.textContent = '❌ ' + data.message;
                            }
                        })
                        .catch(error => {
                            console.error('Kupon hatası detayı:', error);
                            messageEl.style.color = '#F56565';
                            messageEl.style.fontWeight = '600';
                            messageEl.textContent = '❌ Bir hata oluştu: ' + error.message;
                        });
                    }
                            
                    function selectSeat(num) {
                        // If a seat is already selected, don't allow changing it
                        const currentSelected = document.getElementById('seat_number').value;
                        if (currentSelected) {
                            return;
                        }
                        
                        var el = document.querySelector('.seat-btn[data-seat="'+num+'"]');
                        if (el.classList.contains('cursor-not-allowed')) return;
                        document.getElementById('seat_number').value = num;
                        document.querySelectorAll('.seat-btn').forEach(function(btn){
                            var seatNum = parseInt(btn.getAttribute('data-seat'));
                            if (seatNum === num) {
                                btn.classList.remove('bg-gray-200', 'text-primary', 'bg-gray-300', 'text-gray-500');
                                btn.classList.add('bg-primary', 'text-white', 'border-primary');
                            } else {
                                var isOccupied = btn.classList.contains('cursor-not-allowed');
                                btn.classList.remove('bg-primary', 'text-white', 'border-primary', 'bg-gray-300', 'text-gray-500');
                                if (isOccupied) {
                                    btn.classList.add('bg-gray-300', 'text-gray-500', 'border-gray-500');
                                } else {
                                    btn.classList.add('bg-gray-200', 'text-primary', 'border-primary');
                                }
                            }
                        });
                    }
                </script>
                </div>
                
                <div style="text-align: center; margin-top: 2rem;">
                    <a href="search.php" class="btn btn-ghost">
                        ← Sefer Aramaya Dön
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </main>

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