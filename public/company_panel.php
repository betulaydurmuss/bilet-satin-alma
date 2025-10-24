<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'firma_admin')) {
    header('Location: login.php'); exit;
}

$db = Database::getInstance();
$user_id = $_SESSION['user_id'];
$user = $db->queryOne('SELECT * FROM users WHERE id = ?', [$user_id]);
$company_id = $user['company_id'] ?? null;
$trips = $db->query('SELECT * FROM trips WHERE company_id = ?', [$company_id]);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biletly - Firma Paneli</title>
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
                    <div class="user-avatar" style="background: linear-gradient(135deg, #8E44AD 0%, #6C63FF 100%);">
                        🏢
                    </div>
                    <span style="font-weight: 600; color: var(--gray-700);">
                        <?php echo htmlspecialchars($_SESSION['username'] ?? 'Firma'); ?>
                    </span>
                </div>
                <a href="company_panel.php" class="btn btn-secondary btn-sm">🏢 Firma Panel</a>
                <a href="logout.php" class="btn btn-ghost btn-sm">Çıkış</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container section">
        <div class="card fade-in">
            <h2 style="font-size: 2rem; font-weight: 800; color: #FFFFFF; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.5rem;">
                🏢 Firma Paneli
            </h2>
            
            <!-- Quick Actions -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
                <a href="index.php" class="card" style="background: linear-gradient(135deg, #4FACFE 0%, #00F2FE 100%); text-decoration: none; padding: 2rem; text-align: center; transition: all 0.3s; border: none;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
                    <div style="font-size: 1.25rem; font-weight: 700; color: white;">Sefer Ara</div>
                    <div style="font-size: 0.875rem; color: rgba(255,255,255,0.8); margin-top: 0.5rem;">Seferleri ara ve listele</div>
                </a>
                <a href="manage_trips.php" class="card" style="background: linear-gradient(135deg, #8E44AD 0%, #6C63FF 100%); text-decoration: none; padding: 2rem; text-align: center; transition: all 0.3s; border: none;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🚌</div>
                    <div style="font-size: 1.25rem; font-weight: 700; color: white;">Sefer Yönetimi</div>
                    <div style="font-size: 0.875rem; color: rgba(255,255,255,0.8); margin-top: 0.5rem;">Seferlerinizi yönetin</div>
                </a>
                <a href="manage_coupons.php" class="card" style="background: linear-gradient(135deg, #48BB78 0%, #38A169 100%); text-decoration: none; padding: 2rem; text-align: center; transition: all 0.3s; border: none;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🎟️</div>
                    <div style="font-size: 1.25rem; font-weight: 700; color: white;">Kupon Yönetimi</div>
                    <div style="font-size: 0.875rem; color: rgba(255,255,255,0.8); margin-top: 0.5rem;">Kuponları yönetin</div>
                </a>
                <a href="my_account.php" class="card" style="background: linear-gradient(135deg, #E67E22 0%, #D35400 100%); text-decoration: none; padding: 2rem; text-align: center; transition: all 0.3s; border: none;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">💼</div>
                    <div style="font-size: 1.25rem; font-weight: 700; color: white;">Hesabım</div>
                    <div style="font-size: 0.875rem; color: rgba(255,255,255,0.8); margin-top: 0.5rem;">Profil ve biletlerim</div>
                </a>
            </div>
            
            <!-- Recent Trips -->
            <h3 style="font-size: 1.5rem; font-weight: 700; color: #FFFFFF; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                🚌 Son Seferleriniz
            </h3>
            
            <?php if (empty($trips)): ?>
                <div class="card" style="background: rgba(79, 172, 254, 0.2); border-color: rgba(79, 172, 254, 0.4); text-align: center; padding: 3rem;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🚌</div>
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #F7FAFC; margin-bottom: 0.5rem;">
                        Henüz sefer eklenmemiş
                    </h3>
                    <p style="color: #A0AEC0; margin-bottom: 1.5rem;">
                        Yeni sefer eklemek için Sefer Yönetimi sayfasına gidin.
                    </p>
                    <a href="manage_trips.php" class="btn btn-primary">
                        🚌 Sefer Ekle
                    </a>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto; background: rgba(45, 55, 72, 0.4); border-radius: var(--radius-xl); padding: 1.5rem; margin-bottom: 2rem;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid rgba(108, 99, 255, 0.3);">
                                <th style="padding: 1rem; text-align: left; font-weight: 700; color: #E2E8F0;">Rota</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 700; color: #E2E8F0;">Tarih</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 700; color: #E2E8F0;">Saat</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 700; color: #E2E8F0;">Fiyat</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 700; color: #E2E8F0;">Koltuk</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $recent_trips = array_slice($trips, 0, 10);
                            foreach ($recent_trips as $trip): 
                            ?>
                                <tr style="border-bottom: 1px solid rgba(108, 99, 255, 0.1); transition: all 0.2s;" onmouseover="this.style.background='rgba(108, 99, 255, 0.1)'" onmouseout="this.style.background='transparent'">
                                    <td style="padding: 1rem; color: #FFFFFF; font-weight: 600;">
                                        <?php echo htmlspecialchars($trip['departure_city']); ?> → <?php echo htmlspecialchars($trip['arrival_city']); ?>
                                    </td>
                                    <td style="padding: 1rem; color: #A0AEC0;"><?php echo date('d.m.Y', strtotime($trip['departure_date'])); ?></td>
                                    <td style="padding: 1rem; color: #A0AEC0;"><?php echo substr($trip['departure_time'], 0, 5); ?></td>
                                    <td style="padding: 1rem; color: #48BB78; font-weight: 700;"><?php echo number_format($trip['price'], 0); ?> ₺</td>
                                    <td style="padding: 1rem; color: #CBD5E0;"><?php echo $trip['available_seats']; ?>/<?php echo $trip['total_seats']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div style="text-align: center;">
                    <a href="manage_trips.php" class="btn btn-primary">
                        Tüm Seferleri Görüntüle
                    </a>
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
</body>
</html>