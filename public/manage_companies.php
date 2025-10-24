<?php
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/Database.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$db = Database::getInstance();
$message = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $name = trim($_POST['name']);
            $phone = trim($_POST['phone']);
            $email = trim($_POST['email']);
            
            if (!empty($name) && !empty($phone)) {
                
                $existing = $db->queryOne("SELECT id FROM companies WHERE name = ?", [$name]);
                
                if ($existing) {
                    $message = 'Bu firma adı zaten mevcut!';
                } else {
                    $result = $db->execute(
                        "INSERT INTO companies (name, phone, email) VALUES (?, ?, ?)",
                        [$name, $phone, $email]
                    );
                    $message = $result ? 'Firma başarıyla eklendi!' : 'Firma eklenirken hata oluştu.';
                }
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = (int)$_POST['id'];
            $result = $db->execute("DELETE FROM companies WHERE id = ?", [$id]);
            $message = $result ? 'Firma başarıyla silindi!' : 'Firma silinirken hata oluştu.';
        }
    }
}


$companies = $db->query("SELECT * FROM companies ORDER BY name");
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biletly - Firma Yönetimi</title>
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
    <div class="container section">
        <div class="card fade-in">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2 style="font-size: 2rem; font-weight: 800; color: #FFFFFF; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                    🏢 Firma Yönetimi
                </h2>
                <a href="admin_panel.php" class="btn btn-ghost btn-sm">
                    ← Geri
                </a>
            </div>

            <?php if ($message): ?>
                <div class="card" style="background: <?php echo strpos($message, 'başarıyla') !== false ? 'rgba(72, 187, 120, 0.2)' : 'rgba(245, 101, 101, 0.2)'; ?>; border-color: <?php echo strpos($message, 'başarıyla') !== false ? 'rgba(72, 187, 120, 0.4)' : 'rgba(245, 101, 101, 0.4)'; ?>; margin-bottom: 2rem;">
                    <p style="color: #E2E8F0; text-align: center; margin: 0; font-weight: 600;">
                        <?php echo htmlspecialchars($message); ?>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Add Company Form -->
            <div style="background: rgba(45, 55, 72, 0.4); border-radius: var(--radius-xl); padding: 2rem; margin-bottom: 3rem;">
                <h3 style="font-size: 1.25rem; font-weight: 700; color: #FFFFFF; margin-bottom: 1.5rem;">
                    ➕ Yeni Firma Ekle
                </h3>
                <form method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label class="form-label" style="color: #E2E8F0;">Firma Adı:</label>
                        <input type="text" name="name" required class="form-control" style="background: rgba(26, 32, 44, 0.8); color: #FFFFFF;">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="color: #E2E8F0;">Telefon:</label>
                        <input type="text" name="phone" required class="form-control" style="background: rgba(26, 32, 44, 0.8); color: #FFFFFF;">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="color: #E2E8F0;">E-posta:</label>
                        <input type="email" name="email" class="form-control" style="background: rgba(26, 32, 44, 0.8); color: #FFFFFF;">
                    </div>
                    <div style="display: flex; align-items: flex-end;">
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            ➕ Ekle
                        </button>
                    </div>
                </form>
            </div>

            <!-- Companies List -->
            <h3 style="font-size: 1.5rem; font-weight: 700; color: #FFFFFF; margin-bottom: 1.5rem;">
                📋 Firmalar (<?php echo count($companies); ?>)
            </h3>
            
            <?php if (empty($companies)): ?>
                <div class="card" style="background: rgba(79, 172, 254, 0.2); border-color: rgba(79, 172, 254, 0.4); text-align: center; padding: 3rem;">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🏢</div>
                    <p style="color: #E2E8F0; font-size: 1.125rem;">Henüz firma eklenmemiş</p>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto; background: rgba(45, 55, 72, 0.4); border-radius: var(--radius-xl); padding: 1.5rem;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid rgba(108, 99, 255, 0.3);">
                                <th style="padding: 1rem; text-align: left; font-weight: 700; color: #E2E8F0;">ID</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 700; color: #E2E8F0;">Firma Adı</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 700; color: #E2E8F0;">Telefon</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 700; color: #E2E8F0;">E-posta</th>
                                <th style="padding: 1rem; text-align: center; font-weight: 700; color: #E2E8F0;">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($companies as $company): ?>
                                <tr style="border-bottom: 1px solid rgba(108, 99, 255, 0.1); transition: all 0.2s;" onmouseover="this.style.background='rgba(108, 99, 255, 0.1)'" onmouseout="this.style.background='transparent'">
                                    <td style="padding: 1rem; color: #CBD5E0;"><?php echo $company['id']; ?></td>
                                    <td style="padding: 1rem; color: #FFFFFF; font-weight: 600;"><?php echo htmlspecialchars($company['name']); ?></td>
                                    <td style="padding: 1rem; color: #A0AEC0;"><?php echo htmlspecialchars($company['phone']); ?></td>
                                    <td style="padding: 1rem; color: #A0AEC0;"><?php echo htmlspecialchars($company['email'] ?? '-'); ?></td>
                                    <td style="padding: 1rem; text-align: center;">
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Bu firmayı silmek istediğinizden emin misiniz?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $company['id']; ?>">
                                            <button type="submit" class="btn btn-sm" style="background: linear-gradient(135deg, #F56565 0%, #E53E3E 100%); color: white;">
                                                🗑️ Sil
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
