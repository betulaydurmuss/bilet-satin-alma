<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin')) {
    header('Location: login.php'); exit;
}

$db = Database::getInstance();
$users = $db->query('SELECT * FROM users ORDER BY id');
$companies = $db->query('SELECT DISTINCT id, name, phone, email FROM companies ORDER BY name');
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biletly - Admin Paneli</title>
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
                    <div class="user-avatar" style="background: linear-gradient(135deg, #F56565 0%, #E53E3E 100%);">
                        👑
                    </div>
                    <span style="font-weight: 600; color: var(--gray-700);">
                        <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>
                    </span>
                </div>
                <a href="admin_panel.php" class="btn btn-secondary btn-sm">👑 Admin Panel</a>
                <a href="logout.php" class="btn btn-ghost btn-sm">Çıkış</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container section">
        <div class="card fade-in">
            <h2 style="font-size: 2rem; font-weight: 800; color: #FFFFFF; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.5rem;">
                👑 Admin Paneli
            </h2>
            
            <!-- Quick Actions -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
                <a href="manage_companies.php" class="card" style="background: linear-gradient(135deg, #8E44AD 0%, #6C63FF 100%); text-decoration: none; padding: 2rem; text-align: center; transition: all 0.3s; border: none;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🏢</div>
                    <div style="font-size: 1.25rem; font-weight: 700; color: white;">Firma Yönetimi</div>
                    <div style="font-size: 0.875rem; color: rgba(255,255,255,0.8); margin-top: 0.5rem;">Firmaları yönet</div>
                </a>
                <a href="manage_firma_admins.php" class="card" style="background: linear-gradient(135deg, #3182CE 0%, #2C5282 100%); text-decoration: none; padding: 2rem; text-align: center; transition: all 0.3s; border: none;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">👤</div>
                    <div style="font-size: 1.25rem; font-weight: 700; color: white;">Firma Admin</div>
                    <div style="font-size: 0.875rem; color: rgba(255,255,255,0.8); margin-top: 0.5rem;">Firma adminlerini yönet</div>
                </a>
                <a href="manage_coupons.php" class="card" style="background: linear-gradient(135deg, #48BB78 0%, #38A169 100%); text-decoration: none; padding: 2rem; text-align: center; transition: all 0.3s; border: none;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🎟️</div>
                    <div style="font-size: 1.25rem; font-weight: 700; color: white;">Kupon Yönetimi</div>
                    <div style="font-size: 0.875rem; color: rgba(255,255,255,0.8); margin-top: 0.5rem;">Kuponları yönet</div>
                </a>
                <a href="update_seats.php" class="card" style="background: linear-gradient(135deg, #E67E22 0%, #D35400 100%); text-decoration: none; padding: 2rem; text-align: center; transition: all 0.3s; border: none;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">💺</div>
                    <div style="font-size: 1.25rem; font-weight: 700; color: white;">Koltuk Güncelle</div>
                    <div style="font-size: 0.875rem; color: rgba(255,255,255,0.8); margin-top: 0.5rem;">Koltukları güncelle</div>
                </a>
            </div>
            
            <!-- Users Table -->
            <h3 style="font-size: 1.5rem; font-weight: 700; color: #FFFFFF; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                👥 Kullanıcılar
            </h3>
            <div style="overflow-x: auto; background: rgba(45, 55, 72, 0.4); border-radius: var(--radius-xl); padding: 1.5rem; margin-bottom: 3rem;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid rgba(108, 99, 255, 0.3);">
                            <th style="padding: 1rem; text-align: left; font-weight: 700; color: #E2E8F0;">ID</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 700; color: #E2E8F0;">Kullanıcı Adı</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 700; color: #E2E8F0;">Rol</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 700; color: #E2E8F0;">Kredi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr style="border-bottom: 1px solid rgba(108, 99, 255, 0.1); transition: all 0.2s;" onmouseover="this.style.background='rgba(108, 99, 255, 0.1)'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 1rem; color: #CBD5E0;"><?php echo $user['id']; ?></td>
                                <td style="padding: 1rem; color: #FFFFFF; font-weight: 600;"><?php echo htmlspecialchars($user['username']); ?></td>
                                <td style="padding: 1rem;">
                                    <span style="padding: 0.25rem 0.75rem; border-radius: var(--radius-md); font-size: 0.875rem; font-weight: 600; 
                                        <?php if($user['role'] === 'admin'): ?>
                                            background: linear-gradient(135deg, #F56565 0%, #E53E3E 100%); color: white;
                                        <?php elseif($user['role'] === 'company'): ?>
                                            background: linear-gradient(135deg, #8E44AD 0%, #6C63FF 100%); color: white;
                                        <?php else: ?>
                                            background: rgba(108, 99, 255, 0.2); color: #E2E8F0;
                                        <?php endif; ?>
                                    ">
                                        <?php echo $user['role']; ?>
                                    </span>
                                </td>
                                <td style="padding: 1rem; color: #48BB78; font-weight: 700;"><?php echo number_format($user['credit'],2); ?> ₺</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Companies Table -->
            <h3 style="font-size: 1.5rem; font-weight: 700; color: #FFFFFF; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                🏢 Firmalar
            </h3>
            <div style="overflow-x: auto; background: rgba(45, 55, 72, 0.4); border-radius: var(--radius-xl); padding: 1.5rem; margin-bottom: 2rem;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid rgba(108, 99, 255, 0.3);">
                            <th style="padding: 1rem; text-align: left; font-weight: 700; color: #E2E8F0;">ID</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 700; color: #E2E8F0;">Firma Adı</th>
                            <th style="padding: 1rem; text-align: left; font-weight: 700; color: #E2E8F0;">Telefon</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($companies as $company): ?>
                            <tr style="border-bottom: 1px solid rgba(108, 99, 255, 0.1); transition: all 0.2s;" onmouseover="this.style.background='rgba(108, 99, 255, 0.1)'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 1rem; color: #CBD5E0;"><?php echo $company['id']; ?></td>
                                <td style="padding: 1rem; color: #FFFFFF; font-weight: 600;"><?php echo htmlspecialchars($company['name']); ?></td>
                                <td style="padding: 1rem; color: #A0AEC0;"><?php echo htmlspecialchars($company['phone']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div style="text-align: center;">
                <a href="index.php" class="btn btn-ghost">
                    ← Ana Sayfaya Dön
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