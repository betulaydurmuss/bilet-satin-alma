<?php

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';

$db = Database::getInstance();
$message = '';

if (isset($_POST['update_seats'])) {
    try {
        $new_seat_count = intval($_POST['seat_count']);
        
        
        if ($new_seat_count < 1 || $new_seat_count > 100) {
            $message = "Hata: Koltuk sayısı 1 ile 100 arasında olmalıdır.";
        } else {
            
            $trips = $db->query("SELECT id, total_seats, available_seats FROM trips");
            $updated = 0;
            
            foreach ($trips as $trip) {
                if ($trip['total_seats'] != $new_seat_count) {
                    
                    $db->execute(
                        "UPDATE trips SET total_seats = ?, available_seats = MIN(?, available_seats) WHERE id = ?", 
                        [$new_seat_count, $new_seat_count, $trip['id']]
                    );
                    $updated++;
                } elseif ($trip['available_seats'] > $new_seat_count) {
                    
                    $db->execute(
                        "UPDATE trips SET available_seats = ? WHERE id = ?", 
                        [$new_seat_count, $trip['id']]
                    );
                    $updated++;
                }
            }
            
            $message = "$updated sefer başarıyla $new_seat_count koltuk sayısına güncellendi.";
        }
    } catch (Exception $e) {
        $message = "Hata: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biletly - Koltuk Sayısını Güncelle</title>
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
                <a href="admin_panel.php" class="navbar-link">Admin Panel</a>
            </div>
            
            <div class="navbar-actions">
                <div class="user-info">
                    <div class="user-avatar" style="background: linear-gradient(135deg, #F56565 0%, #E53E3E 100%);">
                        👑
                    </div>
                    <span style="font-weight: 600; color: var(--gray-700);">
                        <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>
                    </span>
                </div>
                <a href="logout.php" class="btn btn-ghost btn-sm">Çıkış</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container section" style="min-height: 70vh; display: flex; align-items: center; justify-content: center;">
        <div class="card fade-in" style="max-width: 500px; width: 100%;">
            <h2 style="font-size: 2rem; font-weight: 800; color: #FFFFFF; margin-bottom: 2rem; text-align: center; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                💺 Koltuk Sayısını Güncelle
            </h2>
            
            <?php if ($message): ?>
                <div class="card" style="background: rgba(72, 187, 120, 0.2); border-color: rgba(72, 187, 120, 0.4); margin-bottom: 2rem;">
                    <p style="color: #E2E8F0; text-align: center; margin: 0; font-weight: 600;">
                        ✅ <?php echo htmlspecialchars($message); ?>
                    </p>
                </div>
            <?php endif; ?>
            
            <!-- Warning Box -->
            <div style="background: rgba(237, 137, 54, 0.2); border: 2px solid rgba(237, 137, 54, 0.4); border-radius: var(--radius-xl); padding: 2rem; margin-bottom: 2rem;">
                <div style="text-align: center; margin-bottom: 1rem;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">⚠️</div>
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #FFFFFF; margin-bottom: 1rem;">
                        Dikkat!
                    </h3>
                    <p style="color: #E2E8F0; line-height: 1.6;">
                        Bu işlem <strong style="color: #FFFFFF;">tüm seferlerin</strong> koltuk sayısını güncelleyecektir.
                        <br><br>
                        Mevcut rezervasyonlar etkilenmeyecek, sadece toplam koltuk sayısı değişecektir.
                    </p>
                </div>
            </div>
            
            <!-- Action Form -->
            <form method="post" style="text-align: center;">
                <div style="margin-bottom: 2rem;">
                    <label for="seat_count" style="display: block; color: #E2E8F0; font-weight: 600; margin-bottom: 0.75rem; font-size: 1rem;">
                        Yeni Koltuk Sayısı
                    </label>
                    <input 
                        type="number" 
                        id="seat_count" 
                        name="seat_count" 
                        min="1" 
                        max="100" 
                        value="39" 
                        required
                        style="width: 100%; max-width: 200px; padding: 0.75rem 1rem; background: rgba(255, 255, 255, 0.1); border: 2px solid rgba(255, 255, 255, 0.2); border-radius: var(--radius-lg); color: #FFFFFF; font-size: 1.125rem; font-weight: 600; text-align: center;"
                    >
                    <p style="color: #94A3B8; font-size: 0.875rem; margin-top: 0.5rem;">
                        (1-100 arası bir değer girin)
                    </p>
                </div>
                
                <button type="submit" name="update_seats" class="btn btn-primary btn-lg" onclick="return confirm('Tüm seferlerin koltuk sayısını güncellemek istediğinizden emin misiniz?')">
                    🔄 Koltuk Sayılarını Güncelle
                </button>
            </form>
            
            <div style="text-align: center; margin-top: 2rem;">
                <a href="admin_panel.php" class="btn btn-ghost">
                    ← Admin Paneline Dön
                </a>
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